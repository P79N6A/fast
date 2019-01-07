<?php

/**
 * Description of SysEncrypModel
 *
 * @author wq
 */
require_model('tb/TbModel');

class SysEncrypRecordModel extends TbModel {

    protected $task_max_num = 3;
    protected $record_max_num = 10;
    protected $task_data = array();

    function __construct() {

        parent::__construct('sys_encrypt_record');
        $this->check_tb();
    }

    function check_tb() {

        $version_no = load_model('sys/SysAuthModel')->product_version_no();
        if ($version_no == 0) { //共享一次最多1任务
            $this->task_max_num = 1;
        }

        $sql_check = " show tables like 'sys_encrypt_record' ";
        $check = $this->db->get_all($sql_check);
        if (empty($check)) {
            $sql_create = "CREATE  TABLE IF NOT EXISTS  `sys_encrypt_record` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `tb_name` varchar(128) DEFAULT NULL,
                        `min_id` bigint(20) DEFAULT NULL,
                        `max_id` bigint(20) DEFAULT NULL,
                        `sys_id` bigint(20) DEFAULT NULL,
                        `all_num` int(11) DEFAULT NULL,
                        `num` int(11) DEFAULT '0',
                        `is_over` tinyint(3) DEFAULT '0',
                        `error_num` int(11) NOT NULL DEFAULT '0',
                        PRIMARY KEY (`id`),
                        KEY `_key` (`tb_name`) USING BTREE
                      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";
            $this->db->query($sql_create);
        }
    }

    function get_tb_task($table) {
        $data = $this->db->get_all("select * from sys_encrypt_record where tb_name=:tb_name ", array(':tb_name' => $table));
        return $data;
    }

    function create_task() {
      //  $kh_id = CTX()->saas->get_saas_key();
        $kh_shop = require_conf('oms/security/kh_shop');

        
        $sql = " select shop_code,authorize_date,authorize_state,shop_user_nick from base_shop  where 
                (sale_channel_code='taobao' or sale_channel_code='fenxiao' ) ";
        $data = $this->db->get_all($sql);
        $shop_arr = array();
        $check_time = time()-7*24*3600;//15天
        foreach ($data as $v) {
            $authorize_date_int = empty($v['authorize_date'])?0:strtotime($v['authorize_date']);
            if ($check_time<$authorize_date_int && in_array($v['shop_user_nick'], $kh_shop)) {
                $shop_arr[] = $v['shop_code'];
            }
        }


        $shop_str = "'" . implode("','", $shop_arr) . "'";
        $sql = " select s.shop_code from base_shop s
            LEFT JOIN     sys_encrypt e on e.shop_code=s.shop_code
            where s.shop_code in ({$shop_str}) AND  e.shop_code is null ";
        $shop_data = $this->db->get_all($sql);
        foreach ($shop_data as $val) {
            $ret_c = load_model('sys/security/SysEncrypModel')->create_shop_encrypt($val['shop_code']);
            if ($ret_c['status'] < 1) {
                return $ret_c;
            }
        }
//        $encrypt_num = $this->db->get_value("select count(1) from sys_encrypt");
//        $shop_num = $this->db->get_value("select count(1) from base_shop where (sale_channel_code='taobao' or sale_channel_code='fenxiao') AND authorize_state=1");
//        if ($shop_num != $encrypt_num) {
//            return $this->format_ret(-1, '', '店铺未全部开启，暂时不开启加密');
//        }

        $table_arr = array(
            "oms_sell_record" => array(
                'id' => 'sell_record_id',
                'w' => "  (sale_channel_code ='taobao' OR sale_channel_code ='fenxiao')  ",
            ),
            "oms_sell_return" => array(
                'id' => 'sell_return_id',
                'w' => "   (sale_channel_code ='taobao' OR sale_channel_code ='fenxiao')  ",
            ),

            "api_order" => array(
                'id' => 'id',
                'w' => "  ( source ='taobao' OR source ='fenxiao' ) ",
            ),
            "api_taobao_fx_trade" => array(
                'id' => 'ttid',
                'w' => "  1  ",
            ),
            "crm_customer" => array(
                'id' => 'customer_id',
                'w' => "  ( source ='taobao' OR source ='fenxiao' ) ",
            ),
        );
        $shop_all = $this->db->get_all("select shop_code from base_shop where sale_channel_code ='taobao'");
        if (!empty($shop_all)) {
            $shop_code_arr = array_column($shop_all, 'shop_code');
            $shop_str = "'" . implode("','", $shop_code_arr) . "'";

            $table_arr['oms_return_package'] = array(
                'id' => 'return_package_id',
                'w' => " shop_code in ({$shop_str}) ",
            );
        }


        foreach ($table_arr as $tb => $config) {
            if (count($this->task_data) != $this->task_max_num) {
                $this->check_table_task($tb, $config);
            }
        }

        $this->create_sys_task();

        if (empty($this->task_data)) {
            //sql语句检查数据
            //结束自动主服务

            $sql = "update sys_schedule set `status`=0 where  code='cli_encrypt_task'";
            $this->db->query($sql);
        }
    }

    private function check_table_task($tb, $config) {
        $data = $this->get_tb_task($tb);

        if (!empty($data)) {
            //创建任务
            foreach ($data as $val) {
                if (count($this->task_data) == $this->task_max_num) {
                    break;
                }
                if ($val['max_id'] == $val['sys_id']) {
                    continue;
                }
                $this->task_data[] = $val;
            }
        } else {
            $this->create_record($tb, $config);
            $data = $this->get_tb_task($tb);
            foreach ($data as $val) {
                if (count($this->task_data) == $this->task_max_num) {
                    break;
                }
                if ($val['max_id'] == $val['sys_id']) {
                    continue;
                }
                $this->task_data[] = $val;
            }
        }
    }

    private function create_record($tb, $config) {
        $record_data = array(
            'tb_name' => $tb,
        );
        $sql = "select {$config['id']} from {$tb} where {$config['w']} ORDER BY   {$config['id']} ";
        $min_id = $this->db->get_value($sql);
        $record_data['min_id'] = $min_id - 1; //大约最小 等于小于最大

        $sql.=" DESC ";
        $max_id = $this->db->get_value($sql);
        $loop_num = ceil($max_id / $this->record_max_num);
        $i = 0;
        $record_all = array();
        $sql_count = " select count(1) from {$tb} where {$config['w']}   ";
        while ($i < $this->record_max_num) {
            $i++;
            $max_id = $i * $loop_num;
            $record_data['all_num'] = $this->db->get_value($sql_count . " AND {$config['id']}>{$record_data['min_id']} AND {$config['id']}<={$max_id} ");
            if ($record_data['all_num'] > 0) {//有数据才去生存任务刷数据
                $record_data['max_id'] = $max_id;
                $record_data['num'] = 0;
                $record_data['sys_id'] = $record_data['min_id'];
                $record_all[] = $record_data;
            }
            $record_data['min_id'] = $max_id;
        }
        $this->insert_multi_exp('sys_encrypt_record', $record_all);
        if ($tb == 'oms_sell_record') {
            $sql = "create table oms_sell_record_ebak like oms_sell_record";
        }
        //备份订单表数据
        if ($tb == 'oms_sell_record') {
            $sql = "select sell_record_id from  oms_sell_record order by sell_record_id  desc ";
            $max_id = $this->db->get_value($sql);
            while ($max_id > 0) {
                $sql = "select sell_record_id from  oms_sell_record_ebak order by sell_record_id  desc ";
                $id = $this->db->get_value($sql);
                $now_id = empty($id) ? 0 : $id;
                if ($now_id >= $max_id) {
                    break;
                }
                $sql_insert = "insert IGNORE into  oms_sell_record_ebak    select * from oms_sell_record where sell_record_id>{$now_id} AND sell_record_id<={$max_id} order by sell_record_id limit 10000 ";
                $this->db->query($sql_insert);
            }
        }
    }

    private function create_sys_task() {
        require_model('common/TaskModel');
        $task = new TaskModel();
        foreach ($this->task_data as $val) {
            $task_data = array();
            $request['app_act'] = 'cli/cli_encrypt_history';
            $request['app_fmt'] = 'json';
            $task_data['code'] = 'cli_encrypt_history_' . $val['id'];
            $request['record_id'] = $val['id'];
            $task_data['start_time'] = time();
            $task_data['request'] = $request;
            $task->save_task($task_data);
        }
    }

    function get_by_id($id) {
        $params = array('id' => $id);
        return $this->get_row($params);
    }

}
