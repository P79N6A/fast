<?php

require_model('pubdata/BasePubModel');

class SyncPubModel extends BasePubModel {
    //产品版本
    public $product_version = array(
        '1' => '标准版',
        '2' => '企业版',
        '3' => '旗舰版',
    );

    function sync_to_kh_db($tb) {


        $conf = require_conf('pubdata/sync_kh_tb');
        $tb_conf = array();
        if ($conf[$tb]) {
            $tb_conf = $conf[$tb];
        } else {
            return $this->format_ret(-1, '', '未配置同步数据表');
        }

        $update_str = '';
        if (isset($tb_conf['update'])) {
            foreach ($tb_conf['update'] as $k => $v) {
                $update_str .= $v . " = VALUES(" . $v . "),";
            }
            $update_str = substr($update_str, 0, strlen($update_str) - 1);
        }


        $select = isset($tb_conf['column']) ? implode(',', $tb_conf['column']) : "*";
        $sql = "select {$select} from {$tb} ";
        
        $data = $this->db->get_all($sql);
        if (empty($data)) {
            return $this->format_ret(-1, '', '同步数据为空');
        }
        $kh_tb = isset($tb_conf['kh_tb'])?$tb_conf['kh_tb']:$tb;

        $error_log = $this->sync_data($kh_tb, $data, $update_str);

        if (empty($error_log)) {
            return $this->format_ret(1);
        }

        return $this->format_ret(-1, $error_log);
    }

    function sync_data($tb, &$data, $update_str = '') {
        $kh_all = $this->get_all_kh();
        $error_log = array();
        //$kh_all = array('2244'); //test
        foreach ($kh_all as $kh_id) {
            $ret = $this->sync_kh_data($kh_id, $tb, $data, $update_str);
            if ($ret['status'] < 1) {
                $error_log[$kh_id] = $ret['message'];
            }
        }
        return $error_log;
    }

    function sync_kh_data($kh_id, $tb, &$data, $update_str) {
        try{
        $db = $this->create_kh_db($kh_id);
        
        if ($db === false) {
            return $this->format_ret(-1, '', $kh_id . ':客户数据库绑定信息异常');
        }

        $tbmodel = new TbModel($tb, '', $db);
        $tb_data = array();
        if (count($data) > 500) {
            $tb_data = array_chunk($data, 500);
        } else {
            $tb_data[] = $data;
        }

        if (empty($update_str)) {

            $row = current($data);
            $key_arr = array_keys($row);
            foreach ($key_arr as $k => $v) {
                $update_str .= $v . " = VALUES(" . $v . "),";
            }
            $update_str = substr($update_str, 0, strlen($update_str) - 1);
        }

        foreach ($tb_data as $new_data) {
            $tbmodel->insert_multi_duplicate($tb, $new_data, $update_str);
        }
        }catch(Exception $ex){
            return $this->format_ret(-1,'',$ex->getMessage());
                    
        }
            return $this->format_ret(1);
            
            
            
    }


    /**
     * 产品信息回溯
     */
    function product_inform_back() {
        $kh_all = $this->get_all_kh();
        $error_log = array();
     //   $kh_all = array('2244'); //test
        //插入中间表
        foreach ($kh_all as $kh_id) {
            $ret = $this->back_kh_data($kh_id);
            if ($ret['status'] < 1) {
                $error_log[$kh_id] = $ret['message'];
            }
        }

        require_lib('apiclient/ProductInformBackClient');
        $obj = new ProductInformBackClient();
        //获取前天失败的数据,再回溯一次
        $fail_order_params = $this->get_all_record_info(1);
        if (!empty($fail_order_params)) {
            $obj->Order_information_traceback($fail_order_params);
        }

        //订单信息
        $api_order_params = $this->get_all_record_info();
        if (!empty($api_order_params)) {
            //调用接口
            $ret = $obj->Order_information_traceback($api_order_params);
            if ($ret['status'] != 1) {
                //回溯失败的数据,打上标识
                $id_arr = explode(',', $ret['data']);
                $sql_value = array();
                $id_str = $this->arr_to_in_sql_value($id_arr, 'id', $sql_value);
                $time = date("Y-m-d H:i:s");
                $remark = "时间:{$time},回溯失败！";
                $sql = "UPDATE osp_sell_record_back SET traceback_status=1,remark='{$remark}' WHERE id IN({$id_str}) ";
                $this->query($sql, $sql_value);
            }
        }
        return $error_log;
    }

    /**
     * 登录信息回溯
     */
    function login_inform_back() {
        $kh_all = $this->get_all_kh();
        $error_log = array();
        //$kh_all = array('2244'); //test
        //插入中间表
        foreach ($kh_all as $kh_id) {
            $ret = $this->back_login_data($kh_id);
            if ($ret['status'] < 1) {
                $error_log[$kh_id] = $ret['message'];
            }
        }
        require_lib('apiclient/ProductInformBackClient');
        $obj = new ProductInformBackClient();
        //前天失败的数据再次回溯
        $fail_login_params = $this->get_all_login_info(1);
        if (!empty($fail_login_params)) {
            $obj->login_information_traceback($fail_login_params);
        }

        //从中间表获取登录信息
        $api_login_params = $this->get_all_login_info(0);
        if (!empty($api_login_params)) {
            //调用接口
            $ret = $obj->login_information_traceback($api_login_params);
            if ($ret['status'] != 1) {
                //回溯失败的数据,打上标识
                $id_arr = $ret['data'];
                $sql_value = array();
                $id_str = $this->arr_to_in_sql_value($id_arr, 'id', $sql_value);
                $time = date("Y-m-d H:i:s");
                $remark = "时间:{$time},回溯失败！";
                $sql = "UPDATE osp_login_back SET traceback_status=1,remark='{$remark}' WHERE id IN({$id_str}) ";
                $this->query($sql, $sql_value);
            }
        }
        return $error_log;
    }

    /**
     * 插入中间表
     * @param $kh_id
     * @param $tb
     * @return array
     */
    function back_kh_data($kh_id) {
        try {
            $db = $this->create_kh_db($kh_id);
            if ($db === false) {
                throw new Exception($kh_id . ':客户数据库绑定信息异常', '-1');
            }
            //数据连接对象
            $tbmodel = new TbModel('', '', $db);
            //获取前一天的订单信息
            $record_date_start = date("Y-m-d", strtotime("-1 day")) . ' 00:00:00';
            $record_date_end = date("Y-m-d", strtotime("-1 day")) . ' 23:59:59';
            $sql = "SELECT IF(shipping_status<>4,0,1) AS record_status,sale_channel_code,DATE_FORMAT(record_time,'%Y-%m-%d')  AS record_date,payable_money AS order_money FROM oms_sell_record WHERE order_status<>3 AND record_time<='{$record_date_end}' AND record_time>='{$record_date_start}'";
            $sql_main = "SELECT count(1) AS order_num,SUM(order_money) AS order_money,t.sale_channel_code,t.record_status as order_status,record_date FROM ({$sql}) AS t GROUP BY t.sale_channel_code,t.record_date,t.record_status";
            $record = $tbmodel->db->get_all($sql_main);
            //销售平台
            $sql = "SELECT sale_channel_code,sale_channel_name FROM base_sale_channel";
            $sale_channel_info = $tbmodel->db->get_all($sql);
            $sale_channel = array();
            foreach ($sale_channel_info as $item) {
                $sale_channel[$item['sale_channel_code']] = $item['sale_channel_name'];
            }
            $order_back = array();
            if (!empty($record)) {
                foreach ($record as $value) {
                    $order_back[] = array(
                        "kh_id" => $kh_id,
                        "record_date" => $value['record_date'],
                        "order_status" => $value['order_status'],
                        "sale_channel_code" => $value['sale_channel_code'],
                        "sale_channel_name" => $sale_channel[$value['sale_channel_code']],
                        "order_num" => $value['order_num'],
                        "order_money" => $value['order_money'],
                    );
                }
            }
            if (!empty($order_back)) {
                $update_str = "order_num= VALUES(order_num),order_money= VALUES(order_money)";
                $ret = $this->insert_multi_duplicate('osp_sell_record_back', $order_back, $update_str);
                if ($ret['status'] != 1) {
                    throw new Exception($ret['message'], $ret['status']);
                }
            }
        } catch (Exception $e) {
            return array('status' => $e->getCode(), 'data' => '', 'message' => $e->getMessage());
        }
        return $this->format_ret(1);
    }


    /**
     * 从客户数据库获取登录信息
     * @param $kh_id
     * @return array|bool
     */
    function back_login_data($kh_id) {
        try {
            $db = $this->create_kh_db($kh_id);
            if ($db === false) {
                throw new Exception($kh_id . ':客户数据库绑定信息异常', '-1');
            }
            $tbmodel = new TbModel('', '', $db);
            //获取前一天的登录信息
            $date_start = date("Y-m-d", strtotime("-1 day")) . ' 00:00:00';
            $date_end = date("Y-m-d", strtotime("-1 day")) . ' 23:59:59';
            $sql = "SELECT * FROM sys_login_log WHERE add_time<='{$date_end}' AND add_time>='{$date_start}'";
            $login_info = $tbmodel->db->get_all($sql);
            //获取user_code，user_name
            $sql = "SELECT user_name,user_code FROM sys_user";
            $user_info = $tbmodel->db->get_all($sql);
            $user = array();
            foreach ($user_info as $item) {
                $user[$item['user_code']] = $item['user_name'];
            }
            $login_back = array();
            if (!empty($login_info)) {
                foreach ($login_info as $value) {
                    $login_back[] = array(
                        "kh_id" => $kh_id,
                        "login_ip" => $value['ip'],
                        "login_dm" => $value['user_code'],
                        "login_name" => $user[$value['user_code']],
                        "login_date" => $value['add_time'],
                        "login_company_name" => '',
                    );
                }
            }
            if (!empty($login_back)) {
                $ret = $this->insert_multi_exp('osp_login_back', $login_back, true);
                if ($ret['status'] != 1) {
                    throw new Exception($ret['message'], $ret['status']);
                }
            }
        } catch (Exception $e) {
            return array('status' => $e->getCode(), 'data' => '', 'message' => $e->getMessage());
        }
        return $this->format_ret(1);
    }

    /**
     * 获取所有客户的订单参数
     * @return array
     */
    function get_all_record_info($type = 0) {
        $kh_all = $this->get_all_kh();
      //  $kh_all = array('2244'); //test
        $all_kh_record = array();
        foreach ($kh_all as $kh_id) {
            $kh_record = $this->get_kh_record_back($kh_id, $type);
            if (!empty($kh_record)) {
                $order_info = $this->set_order_info($kh_record);
                $all_kh_record[$kh_id]['order'] = $order_info;//订单参数
                $action = $this->get_action_info($kh_id);
                $all_kh_record[$kh_id]['action'] = $action;//客户产品，授权，机器信息
            }
        }
        return $all_kh_record;
    }

    /**
     * 获取所有客户的登录信息
     * $type=1 取前天失败的数据
     *$type=0 取昨天天失败的数据
     * @return array
     */
    function get_all_login_info($type = 0) {
        $kh_all = $this->get_all_kh();
        //$kh_all = array('2244'); //test
        $all_kh_login = array();
        foreach ($kh_all as $kh_id) {
            $kh_login = $this->get_kh_login_back($kh_id, $type);
            if (!empty($kh_login)) {
                $login_info = $this->set_login_info($kh_login);
                $all_kh_login[$kh_id]['login'] = $login_info;//登录参数
                $action = $this->get_action_info($kh_id);
                $all_kh_login[$kh_id]['action'] = $action;//客户产品，授权信息
            }
        }
        return $all_kh_login;
    }

    /**
     * 获取
     * @param $kh_id
     * @return array
     */
    function get_action_info($kh_id) {
        $sql = "SELECT * FROM osp_kehu WHERE kh_id=:kh_id";
        $sql_value[':kh_id'] = $kh_id;
        $kh_info = $this->db->get_row($sql, $sql_value);
        $sql = "SELECT * FROM osp_productorder_auth WHERE pra_kh_id=:kh_id";
        $auth_info = $this->db->get_row($sql, $sql_value);

        $action = array();
        //IP地址_数据库名称
        $action['DatebaseName'] = '';
        //产品版本
        $action['ProductVer'] = isset($this->product_version[$auth_info['pra_product_version']]) ? $this->product_version[$auth_info['pra_product_version']] : '标准版';
        //授权类型
        $action['LicType'] = '';
        //授权号（haspid）
        $action['LicMark'] = $auth_info['pra_authkey'];
        //类型内容（类型为试用时，内容为截止时间）
        $action['TypeInfo'] = $auth_info['pra_enddate'];
        //序列号
        $action['LicSerial'] = '';
        $action['LicCompanyName'] = $kh_info['kh_name'];
        $action['LicCountPara'] = $auth_info['pra_authnum'];
        //授权模块信息
        $action['LicModulePara'] = '';
        return $action;
    }

    /**
     * 组装客户订单信息
     * @param $kh_record
     * @return array
     */
    function set_order_info($kh_record) {
        $order_info = array();
        foreach ($kh_record as $record) {
            $key = $record['sale_channel_code'] . '_' . $record['record_date'];
            $order_info[$key]['Date'] = $record['record_date'];
            $order_info[$key]['Platform'] = $record['sale_channel_name'];
            $order_info[$key]['App_nick'] = '';
            $order_info[$key]['Info'][$record['id']] = array(
                'Status' => $record['order_status'],
                'Orders' => $record['order_num'],
                'Price' => $record['order_money'],
            );
        }
        return $order_info;
    }

    /**
     * 组装登录信息
     * @param $kh_record
     * @return array
     */
    function set_login_info($kh_login) {
        $login_info = array();
        foreach ($kh_login as $login) {
            $login_info[] = array(
                'IpAddr' => $login['login_ip'],
                'MacAddr' => '',
                'HostName' => '',
                'LoginQD' => '000',
                'LoginDM' => $login['login_dm'],
                'LoginName' => $login['login_name'],
                'LoginCompanyName' => '000',
                'id' => $login['id'],
            );
        }
        return $login_info;
    }

    /**
     * 从中间表获取客户前一天的订单信息
     * @param $kh_id
     * @return array|bool
     */
    function get_kh_record_back($kh_id, $type) {
        $sql = "SELECT * FROM osp_sell_record_back WHERE kh_id=:kh_id AND record_date=:record_date ";
        $sql_value[':kh_id'] = $kh_id;
        if ($type == 0) {
            $sql_value[':record_date'] = date("Y-m-d", strtotime("-1 day"));
        } else {//前天失败的数据
            $sql_value[':record_date'] = date("Y-m-d", strtotime("-2 day"));
            $sql .= " AND traceback_status=1";
        }
        $ret = $this->db->get_all($sql, $sql_value);
        return $ret;
    }


    /**
     * 从中间表获取数据
     * @param $kh_id
     * @return array|bool
     */
    function get_kh_login_back($kh_id, $type) {
        $sql = "SELECT * FROM osp_login_back WHERE kh_id=:kh_id AND login_date>=:date_start AND login_date<=:date_end ";
        $sql_value[':kh_id'] = $kh_id;
        if ($type == 0) {
            $sql_value[':date_start'] = date("Y-m-d", strtotime("-1 day")) . ' 00:00:00';
            $sql_value[':date_end'] = date("Y-m-d", strtotime("-1 day")) . ' 23:59:59';
        } else {//获取前天回溯失败的数据，在回溯一次
            $sql_value[':date_start'] = date("Y-m-d", strtotime("-2 day")) . ' 00:00:00';
            $sql_value[':date_end'] = date("Y-m-d", strtotime("-2 day")) . ' 23:59:59';
            $sql .= " AND traceback_status=1";
        }
        $ret = $this->db->get_all($sql, $sql_value);
        return $ret;
    }
    
    function sync_auth($kh_id,$data){
        $tb = 'sys_auth';
        $key_arr = array('code','name','value');
        foreach($key_arr as $k){
            foreach($data as $val){
                if(empty($val[$k])){
                    return $this->format_ret(-1,'',$k.'不能为空');
                }
            }
        }
        $update_str = " `value` = VALUES(`value`)";
        return  $this->sync_kh_data($kh_id, $tb, $data, $update_str);
        
    }

}
