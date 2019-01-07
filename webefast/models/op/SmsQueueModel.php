<?php
/**
* 短信发送队列
*/
require_model('tb/TbModel');

class SmsQueueModel extends TbModel {
    public function __construct() {
        $table = $this->get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'op_sms_queue';
    }
    //短信发送状态
    public $sms_status = array(
        '0' => '未发送',
        '4' => '发送中',
        '1' => '发送成功',
        '2' => '发送失败',
        '3' => '终止',
    );
    //短信类型
    public $sms_type = array(
        'deliver' => '发货通知',
        'send_test' => '发送测试',
    );
    public function getSmsStatusName($code) {
        return isset($this->sms_status[$code]) ? $this->sms_status[$code] : '';
    }
    public function getSmsTypeName($code) {
        return isset($this->sms_type[$code]) ? $this->sms_type[$code] : '';
    }
    function get_by_id($id) {
        $arr = $this->get_row(array('id' => $id));
        return $arr;
    }
    /**
     * 新增任务
     * @param type $params
     * @return type
     */
    function insert($params) {
        trim_array($params);
        $data = get_array_vars($params, array('buyer_name', 'tel', 'sms_info','sms_type', 'plan_send_time'));
        $data['sms_num'] = $this->countSmsNum($data['sms_info']);
        $data['plan_send_time'] = (empty($data['plan_send_time'])) ? date('Y-m-d H:i:s') : $data['plan_send_time'];
        $data['create_time'] = date('Y-m-d H:i:s');
        return parent::insert($data);
    }
    /**
     * 计算短信条数
     * @param type $content
     * @return type
     */
    public function countSmsNum($content) {
        $len = strlen_utf8($content);
        $num = (70 < $len) ? ceil($len/67) : 1;
        return $num;
    }
    /**
     * 根据条件查询数据
     * @param type $filter
     * @param type $only_sql true只返回sql和sql_value
     * @return type
     */
    function get_by_page($filter, $only_sql=false) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} WHERE 1";
        //页签
        if (isset($filter['list_tab']) && $filter['list_tab'] !== '') {
            switch ($filter['list_tab']) {
                case 'tabs_unsend':
                    $sql_main .= " AND status = 0";
                    break;
                case 'tabs_is_sending':
                    $sql_main .= " AND status = 4";
                    break;
                case 'tabs_send_success':
                    $sql_main .= " AND status = 1";
                    break;
                case 'tabs_send_fail':
                    $sql_main .= " AND status = 2";
                    break;
                case 'tabs_over':
                    $sql_main .= " AND status = 3";
                    break;
                default:
                    break;
            }
        }
        //任务状态
        if (isset($filter['status']) && $filter['status'] !== '') {
            $sql_main .= " AND status = :status";
            $sql_values[':status'] = $filter['status'];
        }
        //短信类型
        if (isset($filter['sms_type']) && $filter['sms_type'] !== '') {
            $sql_main .= " AND sms_type = :sms_type";
            $sql_values[':sms_type'] = $filter['sms_type'];
        }
        //任务ID
        if (isset($filter['task_id']) && $filter['task_id'] !== '') {
            $sql_main .= " AND id = :id";
            $sql_values[':id'] = $filter['task_id'];
        }
        //会员昵称
        if (isset($filter['buyer_name']) && $filter['buyer_name'] !== '') {
            $sql_main .= " AND buyer_name LIKE :buyer_name";
            $sql_values[':buyer_name'] = '%' . $filter['buyer_name'] . '%';
        }
        //手机号码
        if (isset($filter['tel']) && $filter['tel'] !== '') {
            $sql_main .= " AND tel = :tel";
            $sql_values[':tel'] = $filter['tel'];
        }
        //发送内容
        if (isset($filter['sms_info']) && $filter['sms_info'] !== '') {
             $sql_main .= " AND sms_info LIKE :sms_info";
            $sql_values[':sms_info'] = '%' . $filter['sms_info'] . '%';
        }
        //计划发送时间
        if (isset($filter['plan_send_time_start']) && $filter['plan_send_time_start'] !== '') {
            $sql_main .= " AND plan_send_time >= :plan_send_time_start";
            $sql_values[':plan_send_time_start'] = $filter['plan_send_time_start'];
        }
        if (isset($filter['plan_send_time_end']) && $filter['plan_send_time_end'] !== '') {
            $sql_main .= " AND plan_send_time <= :plan_send_time_end";
            $sql_values[':plan_send_time_end'] = $filter['plan_send_time_end'];
        }
        
        //仅返回sql和sql_value
        if ($only_sql){
            return array($sql_main,$sql_values);
        }
        
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as $key => &$val) {
            $val['status_name'] = $this->getSmsStatusName($val['status']);
            $val['sms_type_name'] = $this->getSmsTypeName($val['sms_type']);
            $val['sms_info_sub'] = mb_substr_replace($val['sms_info'], '...', 60);//替换字符串的子串
            $val['send_time_range'] = empty($val['send_start_time']) ? '' : $val['send_start_time'] . '-' . $val['send_end_time'];//替换字符串的子串
        }
        return $this->format_ret(1, $data);
    }
    /**
     * 获取统计数据
     * @param $filter
     * @return array
     */
    function get_statistical_data($filter) {
        list($sql_main,$sql_values) = $this->get_by_page($filter, true);
        $select = 'SELECT count(1) ';
        $data = array();
        //总任务数
        $sql = $select . $sql_main;
        $data['record_num_all'] = $this->db->get_value($sql, $sql_values);
        //成功任务数
        $sql = $select . $sql_main . ' AND status = 1';
        $data['record_num_success'] = $this->db->get_value($sql, $sql_values);
        //失败任务数
        $sql = $select . $sql_main . ' AND status = 2';
        $data['return_num_fail'] = $this->db->get_value($sql, $sql_values);
        //消费短信条数
        $sql = 'SELECT SUM(sms_num)' . $sql_main . ' AND status = 1';
        $record_num_used = $this->db->get_value($sql, $sql_values);
        $data['record_num_used'] = empty($record_num_used) ? 0 : $record_num_used;
        return $this->format_ret(1, $data);
    }

    /**
     * 发送短信
     * @param type $id
     * @return type
     */
    public function send_sms($id) {
        if (empty($id)){
            return $this->format_ret('error_params');
        }
        $ret = $this->get_by_id($id);
        if ($ret['status'] != 1){
            return $ret;
        }
        //推送短信到中间表
        $ret = $this->pushSmsOne($ret['data']);
        if ($ret['status'] < 0){
            return $ret;
        }
        return $this->format_ret(1,'','发送成功');
    }
    /**
     * 一键发送短信(获取所有记录id)
     * @param type $filter
     * @return type
     */
    public function get_all_sms_id($filter) {
        list($sql_main,$sql_values) = $this->get_by_page($filter, true);
        $sql = 'SELECT id ' . $sql_main;
        $data = $this->db->get_all_col($sql, $sql_values);
        if (empty($data)){
            return $this->format_ret(-1, '', '数据为空');
        }
        return $this->format_ret(1, $data);
    }
    /**
     * 终止短信任务
     * @param type $id
     * @return type
     */
    public function over_sms($id) {
        if (empty($id)){
            return $this->format_ret('error_params');
        }
        $ret = $this->get_by_id($id);
        if ($ret['status'] != 1){
            return $ret;
        }
        $ret = parent::update(array('status'=>3), array('id' => $id));
        return $ret;
    }
    /**
     * 自动服务: 生成订单发货通知短信
     * @return type
     */
    public function create_delivered_sms() {
        set_time_limit(0);
        //获取店铺短信设置及模板内容
        $sql = "SELECT r1.*,r2.tpl_name,r2.sms_sign,r2.sms_info FROM `op_sms_config_shop` AS r1"
                . " INNER JOIN `op_sms_tpl` r2 ON r2.id = r1.delivery_notice_tpl_id"
                . " WHERE r1.`is_active`=1 AND r1.`delivery_notice_status`=1 AND r2.tpl_type = 'delivery_notice'";
        $all_shop_config = $this->db->get_all($sql);
        if (empty($all_shop_config)) {
            return $this->format_ret(1, '', '未开启店铺短信设置');
        } 
        //生成短信任务
        $tbl_cfg = array('base_shop' => array('fld' => 'shop_name', 'relation_fld' => 'shop_code+shop_code'),'base_express' => array('fld' => 'express_name', 'relation_fld' => 'express_code+express_code'),);
        require_model('util/GetDataBySqlRelModel');
        $obj = new GetDataBySqlRelModel();
        $obj->tbl_cfg = $tbl_cfg;
        $end_time = date('Y-m-d H:i:s');
        foreach ($all_shop_config as $shop_config) {
            //获取已发货订单sql
            $select = "sell_record_code,shop_code,buyer_name,receiver_mobile,express_code,express_no,receiver_name,deal_code_list,customer_address_id";
            $where = " 1 AND shop_code = :shop_code AND shipping_status = 4 AND delivery_time > :delivery_time_start AND delivery_time <= :delivery_time_end";
            $sql_values = array(':shop_code' => $shop_config['shop_code']);
            if ($shop_config['order_type'] !== ''){//订单类型is_fenxiao: 0.普通1.淘分销2.网络分销
                $order_type_arr = explode(',', $shop_config['order_type']);
                $order_type_str = $this->arr_to_in_sql_value($order_type_arr, 'is_fenxiao', $sql_values);
                $where .= " AND is_fenxiao IN ({$order_type_str})";
            }else{
                $where .= ' AND 0=1';
            }
            $sql = "SELECT {$select} FROM oms_sell_record WHERE {$where} ORDER BY sell_record_id ASC";
            //获取最后生成发货通知短信时间
            $start_time = ($shop_config['delivery_notice_last_time'] < $shop_config['enable_time']) ? $shop_config['enable_time'] : $shop_config['delivery_notice_last_time'];
            //特殊处理: 推前120秒防发货延迟
            $pre_start_time_temp = strtotime($start_time) - 120;
            $pre_start_time = date('Y-m-d H:i:s', $pre_start_time_temp);
            $sql_values[':delivery_time_start'] = $pre_start_time;
            $sql_values[':delivery_time_end'] = $start_time;
            $delivery_order = $this->db->get_all($sql, $sql_values);
            if (!empty($delivery_order)){
                //批量获取订单解密数据
                $delivery_order = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_list($delivery_order);
                //订单数据加工
                $delivery_order = $obj->get_data_by_cfg(null, $delivery_order);
                //获取已生成发货短信订单号
                $arr_sell_record_code = array_column($delivery_order, 'sell_record_code');
                $sql_queue_values = array();
                $keywords_str = $this->arr_to_in_sql_value($arr_sell_record_code, 'keywords', $sql_queue_values);
                $sql_queue = "SELECT keywords FROM `op_sms_queue` WHERE `type`=1 AND keywords IN ({$keywords_str})";
                $exists_sms = $this->db->get_all_col($sql_queue, $sql_queue_values);
                $exists_sms_flip = array_flip($exists_sms);
                //插入短信任务(批量)
                $init_data = array();
                foreach ($delivery_order as $order) {
                    $key = $order['sell_record_code'];
                    if (isset($exists_sms_flip[$key])){
                        continue;
                    }
                    $init_data[] = $this->_delivered_sms_init_data($shop_config, $order, $end_time);
                }
                $this->insert_multi_exp('op_sms_queue', $init_data);
            }
            //正常计划
            $sql_values[':delivery_time_start'] = $start_time;
            $sql_values[':delivery_time_end'] = $end_time;
            $page = 1;
            $page_size = 3000;//循环生成短信任务: 每次3000条,再大就要gone away
            $w_flag = true;
            do{
                $start = ($page - 1) * $page_size;
                $new_sql = $sql . " LIMIT {$start},{$page_size}";
                $delivery_order = $this->db->get_all($new_sql, $sql_values);
                if (!empty($delivery_order)) {//有新发货单
                    //批量获取订单解密数据
                    $delivery_order = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_list($delivery_order);
                    //订单数据加工
                    $delivery_order = $obj->get_data_by_cfg(null, $delivery_order);
                    //插入短信任务(批量)
                    $init_data = array();
                    foreach ($delivery_order as $order) {
                        $init_data[] = $this->_delivered_sms_init_data($shop_config, $order, $end_time);
                    }
                    $this->insert_multi_exp('op_sms_queue', $init_data);
                } else {
                    $w_flag = false;
                }
                $page++;
            }while ($w_flag);
            //更新最后生成发货通知短信时间
            $shop_code = array_column($all_shop_config, 'shop_code');
            $sql_values = array(':delivery_notice_last_time'=>$end_time, ':shop_code'=>$shop_config['shop_code']);
            $sql = "UPDATE `op_sms_config_shop` SET `delivery_notice_last_time` = :delivery_notice_last_time WHERE `shop_code` = :shop_code";
            $this->db->query($sql, $sql_values);
        }
        
        return $this->format_ret(1);
    }
    /**
     * 发货通知短信数据初始化
     * @param type $shop_config
     * @param type $order
     * @param type $end_time
     * @return type
     */
    private function _delivered_sms_init_data($shop_config, $order, $end_time) {
        $SmsTplModel = load_model('op/SmsTplModel');
        //替换模板变量
        $sms_info = $SmsTplModel->replace_tpl_var($shop_config['sms_info'], $order);
        $new_sms_info = $SmsTplModel->join_sms_sign($shop_config['sms_sign'], $sms_info);
        //数据解密
        $sms_data = array(
            'sms_type' => 'deliver',//发货通知
            'keywords' => $order['sell_record_code'],//订单号
            'buyer_name' => $order['buyer_name'],
            'tel' => $order['receiver_mobile'],//收货人手机
            'sms_info' => $new_sms_info,
            'sms_num' => $this->countSmsNum($new_sms_info),
            'send_start_time' => $shop_config['send_start_time'],
            'send_end_time' => $shop_config['send_end_time'],
            'plan_send_time' => $end_time,
            'create_time' => $end_time
        );
        return $sms_data;
    }
    /**
     * 自动服务: 自动发送短信任务 (推送中间表)
     * @return type
     */
    public function autoSendSms() {
        set_time_limit(0);
        //循环发送短信任务
        $cur_time = date('H:i');
        $limit = 1000;//防止超内存
        $sql = "SELECT * FROM `op_sms_queue`"
                . " WHERE `status` = 0 AND ((`send_start_time` <= :cur_time AND `send_end_time` >= :cur_time) OR (`send_start_time` = '' AND `send_end_time`=''))"
                . " ORDER BY id LIMIT {$limit}";
        $sql_values = array(':cur_time' => $cur_time);
        $do_flag = true;
        do{
            $data = $this->db->get_all($sql, $sql_values);
            if (!empty($data)) {
                //短信任务推送中间表 (一批批推送)
                $ret = $this->pushSmsBatch($data);
                if (0 > $ret['status']){
                    $do_flag = false;
                }
            } else {
                $do_flag = false;
            }
        }while ($do_flag);
        return $this->format_ret(1);
    }
    /**
     * 短信任务推送中间表 (一条条推送)
     * @param array $sms
     * @return type
     */
    public function pushSmsOne($sms) {
        if (!isset($sms['id']) || !isset($sms['tel']) || !isset($sms['sms_info']) || !isset($sms['sms_num'])){
            return $this->format_ret(-1, '', '参数缺失');
        }
        $kh_id = CTX()->saas->get_saas_key();
        $SmsTaskModel = load_model('common/SmsTaskModel');
        $this->begin_trans();
        try {
            $sms_id = $sms['id'];
            $num = $sms['sms_num'];
            $cur_time = date('Y-m-d H:i:s');
            //锁定短信条数
            $up_account_sql = "UPDATE `op_sms_account` SET `lock_num` = `lock_num`+{$num} WHERE `id`=1 AND `num` >= `lock_num`+`used_num`+{$num}";
            $this->db->query($up_account_sql);
            if ($this->db->affected_rows() != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '短信不足');
            }
            //更新短信状态
            $up_queue_sql = "UPDATE `op_sms_queue` SET `status` = 4,`send_time`=:send_time WHERE `status` IN (0,2) AND `id`=:id";
            $up_queue_values = array('send_time' => $cur_time, ':id'=>$sms_id);
            $this->db->query($up_queue_sql, $up_queue_values);
            if ($this->db->affected_rows() != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '更新短信状态失败');
            }
            //推送短信到中间表
            $data = array(array(
                'sms_type' => $sms['sms_type'],
                'kh_id' => $kh_id,
                'sys_sms_id' => $sms_id,
                'phone' => $sms['tel'],
                'content' => $sms['sms_info'],
                'num' => $sms['sms_num'],
                'status' => 0,
                'is_push_report' => 0,
                'create_time' => $cur_time
            ));
            $update_str = 'sms_type,phone,content,num,status,is_push_report,create_time';
            $ret = $SmsTaskModel->insert_dup($data, 'UPDATE', $update_str);
            if ($ret['status'] < 0){
                $this->rollback();
                return $this->format_ret(-1, '', '推送中间表失败');
            }
            $this->commit();
        } catch (Exception $e) {
            return $this->format_ret(-1, '', $e->getMessage());
        }
        return $this->format_ret(1);
    }
    /**
     * 短信任务推送中间表 (一批批推送)
     * @param array $sms
     * @return type
     */
    public function pushSmsBatch($sms) {
        $kh_id = CTX()->saas->get_saas_key();
        $data = array();
        $sms_id_arr = array();
        $sms_num_total = 0;
        $cur_time = date('Y-m-d H:i:s');
        foreach ($sms as $val) {
            $sms_id_arr[] = $val['id'];
            $sms_num_total += $val['sms_num'];
            $arr = array(
                'sms_type' => $val['sms_type'],
                'kh_id' => $kh_id,
                'sys_sms_id' => $val['id'],
                'phone' => $val['tel'],
                'content' => $val['sms_info'],
                'num' => $sms['sms_num'],
                'status' => 0,
                'is_push_report' => 0,
                'create_time' => $cur_time
            );
            $data[] = $arr;
        }
        $SmsTaskModel = load_model('common/SmsTaskModel');
        $row_count = count($data);
        $this->begin_trans();
        try {
            //锁定短信条数
            $up_account_sql = "UPDATE `op_sms_account` SET `lock_num` = `lock_num`+{$sms_num_total} WHERE `id`=1 AND `num` >= `lock_num`+`used_num`+{$sms_num_total}";
            $this->db->query($up_account_sql);
            if ($this->db->affected_rows() != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '短信不足');
            }
            //更新短信状态
            $sql_values = array();
            $sms_id_str = $this->arr_to_in_sql_value($sms_id_arr, 'sms_id', $sql_values);
            $up_queue_sql = "UPDATE `op_sms_queue` SET `status` = 4,`send_time`=:send_time WHERE `status` = 0 AND `id` IN ({$sms_id_str})";
            $sql_values[':send_time'] = $cur_time;
            $this->db->query($up_queue_sql, $sql_values);
            if ($this->db->affected_rows() != $row_count) {
                $this->rollback();
                return $this->format_ret(-1, '', '更新短信状态失败');
            }
            
            //推送短信到中间表
            $update_str = 'sms_type,phone,content,num,status,is_push_report,create_time';
            $ret = $SmsTaskModel->insert_multi_duplicate('sms_task', $data, $update_str);
            if ($ret['status'] < 0){
                $this->rollback();
                return $this->format_ret(-1, '', '推送中间表失败');
            }
            $this->commit();
        } catch (Exception $e) {
            return $this->format_ret(-1, '', $e->getMessage());
        }
        return $this->format_ret(1);
    }
    /**
     * api: 更新短信发送结果 (这是接口方式接收客户短信表报告, 目前用的是切换数据库更新报告状态)
     * @param type $filter
     * @return type
     */
   /* public function update_sms_status_api($filter) {
        //参数校验
        trim_array($filter);
        if (!isset($filter['sms_id']) || !preg_match('/^[1-9]\d*$/',$filter['sms_id'])){
            return $this->format_ret(-1, '', '参数错误: sms_id是必传参数，且为正整数');
        }
        if (!isset($filter['sms_num']) || !preg_match('/^[1-9]\d*$/',$filter['sms_num'])){
            return $this->format_ret(-1, '', '参数错误: sms_num是必传参数，且为正整数');
        }
        if (!isset($filter['status']) || !in_array($filter['status'], array(1,2))){
            return $this->format_ret(-1, '', '参数错误: status是必传参数，且参数为1或2');
        }
        if (!isset($filter['send_time']) || empty($filter['send_time'])){
            return $this->format_ret(-1, '', '参数错误: send_time是必传参数');
        }
        
        //获取原数据
        $sms_id = $filter['sms_id'];
        $status = $filter['status'];
        $sql = 'SELECT * FROM `op_sms_queue` WHERE `id` = :id';
        $sql_values = array(':id' => $sms_id);
        $record = $this->db->get_row($sql, $sql_values);
        if (empty($record)){
            return $this->format_ret(-1, '', '未找到对应短信');
        }
        if ($record['status'] != 4){
            return $this->format_ret(-1, '', '非发送中短信');
        }
        
        //更新短信状态
        $this->begin_trans();
        $old_sms_num = $record['sms_num'];
        $sms_num = (1 === $status) ? $filter['sms_num'] : $old_sms_num;//失败无需更新
        $send_time = $filter['send_time'];
        $up_sms_sql = "UPDATE `op_sms_queue` SET `status` = :status, `sms_num` = :sms_num, `send_time` = :send_time WHERE `status` = 4 AND `id` = :id";
        $sms_sql_values = array(':status' => $status, ':sms_num' => $sms_num, ':send_time' => $send_time, ':id' => $sms_id);
        $this->db->query($up_sms_sql, $sms_sql_values);
        if ($this->db->affected_rows() != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '短信状态更新失败');
        }
        
        //更新短信总账
        $used_num = (1 === $status) ? $filter['sms_num'] : 0;
        $up_account_sql = "UPDATE `op_sms_account` SET `lock_num` = `lock_num` - :lock_num, `used_num` = `used_num` + :used_num WHERE `id` = 1";
        $account_sql_values = array(':lock_num' => $old_sms_num, ':used_num' => $used_num);
        $this->db->query($up_account_sql, $account_sql_values);
        if ($this->db->affected_rows() != 1) { //更新总账失败
            $this->rollback();
            return $this->format_ret(-1, '', '短信总账更新失败');
        }
        
        //操作日志
        $this->commit();
        
        return $this->format_ret(1);
    }*/
}
    