<?php

/**
 * 店铺监控
 *
 * @author wq
 *
 */
require_model('moniter/MoBaseModel');

class MoShopModel extends MoBaseModel {

    protected $record_time = '';

    function get_table() {
        return 'mo_shop';
    }

    function create_moniter() {
        //系统设置的通知邮箱地址同步至运营平台
        $this->sync_kh_notice_email();
        $this->create_shop_info();
        $this->create_shop_order_info();
        $this->create_shop_send_info();
        $this->create_shop_trans_order();
        
        $this->create_shop_api_order();
    }

    function moniter_type() {
        return array('order', 'trans_order', 'order_send');
    }

    function get_info_all() {
        $now_data = date('Y-m-d H:i:s');
        $info = array();
        $sql = "select count(1) from mo_shop where source = 'taobao' ";
        $info['shop_num'] = $this->db->get_value($sql);

        $sql = "select count(1) from mo_shop where source = 'taobao' and (expires_in = '' OR  expires_in<='{$now_data}')";
        $info['auth_fail_num'] = $this->db->get_value($sql);

        $month_date = date('Y-m-d', strtotime("-1 month")) . " 0:00:00";
        $sql = "select count(1) from mo_shop where source = 'taobao' and expires_in>'{$now_data}' and  expires_in<='{$month_date}'";

        $info['expires_num'] = $this->db->get_value($sql);


        $sql = "select count(1) from mo_shop where 1 ";
        $info['shop_all_num'] = $this->db->get_value($sql);


        $type_arr = $this->moniter_type();
        foreach ($type_arr as $key) {
            $info[$key] = $this->get_num_by_type($key);
        }

        return $this->format_ret(1, $info);
    }

    function get_num_by_type($type) {

        $sql = "select count(1)  from mo_shop s INNER JOIN mo_shop_info i ON s.id=i.p_id"
                . " where i.type='{$type}' ";

        if ($type == 'order') {
            $sql .=" AND source = 'taobao'  AND i.api_num>i.sys_num";
        } else {
            $sql .=" AND i.fail_num>0 ";
        }
        return $this->db->get_value($sql);
    }

    function get_filer(&$filter) {
        $param_arr['shop_fail'] = array('auth_type' => 'fail');
        $param_arr['shop_expires'] = array('auth_type' => 'expires');
        $param_arr['order'] = array('type' => 'order');
        $param_arr['trans_order'] = array('type' => 'trans_order');
        $param_arr['order_send'] = array('type' => 'order_send');
        if (isset($filter['mo_type']) && isset($param_arr[$filter['mo_type']])) {
            $filter = array_merge($param_arr[$filter['mo_type']], $filter);
        }
    }

    function get_info_by_page($filter) {
        $sql_join = "";
        $select = 'r1.*';

        $this->get_filer($filter);


        if (isset($filter['type'])) {
            $sql_join = " INNER JOIN mo_shop_info i ON r1.id=i.p_id ";
            $select .= ',i.start_time,i.end_time,i.sys_num,i.api_num,i.fail_num';
        }

        $sql_main = "FROM {$this->table} r1 $sql_join WHERE 1";

        $sql_values = array();

        if (isset($filter['type']) && $filter['type'] != '') {
            $sql_main .= " AND i.type = :type ";
            $sql_values[':type'] = $filter['type'];


            if ($filter['type'] == 'order') {
                $sql_main .=" AND i.api_num>i.sys_num";
            } else {
                $sql_main .=" AND i.fail_num>0 ";
            }
        }

        if (isset($filter['auth_type']) && $filter['auth_type'] != '') {
            $sql_main .=" AND  r1.source = 'taobao'  ";
            $now_data = date('Y-m-d H:i:s');
            if ($filter['auth_type'] == 'fail') {
                $sql_main .=" AND  (r1.expires_in = '' OR  r1.expires_in<='{$now_data}')";
            } else {
                $month_date = date('Y-m-d', strtotime("-1 month")) . " 0:00:00";
                $sql_main .=" AN   r1.expires_in>'{$now_data}' and  r1.expires_in<='{$month_date}')";
            }
        }
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        if ($filter['type'] == 'order') {
            foreach ($ret_data['data'] as &$val) {
                $val['moniter_time'] = $val['start_time'] . "~" . $val['end_time'];
                $val['rate'] = round(($val['api_num'] - $val['sys_num']) / $val['api_num'], 3);
            }
        }



        return $this->format_ret($ret_status, $ret_data);
    }

    function create_shop_info() {
        $sql = "select s.shop_id,s.shop_code,s.shop_name,s.sale_channel_code as source ,a.api from base_shop s "
                . " INNER JOIN base_shop_api a ON s.shop_code = a.shop_code "
                . " WHERE s.authorize_state=1 ";
        $ret_data = array();
        $this->get_moniter_data($sql, $ret_data, 'set_shop_info');
        if (!empty($ret_data)) {
            $updata_str = " api=VALUES(api),expires_in=VALUES(expires_in)";
            $this->insert_multi_duplicate('mo_shop', $ret_data, $updata_str);
        }
    }
    function sync_kh_notice_email() {
        $sql = "select value from sys_params where param_code='notice_email'";
        $ret_data = array();
        $this->get_moniter_data($sql, $ret_data, 'set_notice_email');
        if (!empty($ret_data)) {
            
            $updata_str = " kh_email=VALUES(kh_email)";
            $this->insert_multi_duplicate('osp_kehu', $ret_data, $updata_str);
        }
    }

    
    
    function create_shop_order_info() {
        $this->record_time = $this->get_start_time();

        $sql = "select  count(*) as num,r.shop_code,b.sale_channel_code as source  from  api_order r "
                . "INNER JOIN base_shop b ON r.shop_code=b.shop_code "
                . "WHERE b.sale_channel_code= 'taobao' AND r.order_first_insert_time>='{$this->record_time}' group by r.shop_code  ";
        $ret_data = array();
        $this->get_moniter_data($sql, $ret_data, 'set_order_info');

        if (!empty($ret_data)) {
            $updata_str = "start_time=VALUES(start_time),end_time=VALUES(end_time),update_time=VALUES(update_time),sys_num=VALUES(sys_num),api_num=VALUES(api_num)";
            $this->insert_multi_duplicate('mo_shop_info', $ret_data, $updata_str);
        }
    }

    function create_shop_trans_order() {
        $sql = "select count(*) as num,r.shop_code   from  api_order r 
           INNER JOIN base_shop b ON r.shop_code=b.shop_code 
           WHERE r.is_change<0 group by r.shop_code  ";
        $ret_data = array();
        $this->get_moniter_data($sql, $ret_data, 'set_trans_order');

        if (!empty($ret_data)) {
            $updata_str = "update_time=VALUES(update_time),sys_num=VALUES(sys_num),fail_num=VALUES(fail_num)";
            $this->insert_multi_duplicate('mo_shop_info', $ret_data, $updata_str);
        }
    }

    function create_shop_send_info() {
        $sql = "select count(*) as num,r.shop_code   from  api_order_send r 
           INNER JOIN base_shop b ON r.shop_code=b.shop_code 
           WHERE r.status<0 group by r.shop_code  ";
        $ret_data = array();
        $this->get_moniter_data($sql, $ret_data, 'set_send_info');

        if (!empty($ret_data)) {
            $updata_str = "update_time=VALUES(update_time),sys_num=VALUES(sys_num),fail_num=VALUES(fail_num)";
            $this->insert_multi_duplicate('mo_shop_info', $ret_data, $updata_str);
        }
    }

    
           function create_shop_api_order() {
        $sql = "select kh_id,shop_code,biz_date, order_shipping_count as o_num,order_shipping_goods_count as g_num from  report_base_order_collect r 
   
           WHERE  biz_date = '2015-11-11' OR biz_date = '2015-11-12'  ";
        $ret_data = array();
        $this->get_moniter_data($sql, $ret_data, 'shop_api_order');

        if (!empty($ret_data)) {
            $updata_str = "api_money=VALUES(api_num),sys_num=VALUES(sys_num)";
            $this->insert_multi_duplicate('mo_shop_info', $ret_data, $updata_str);
        }
    } 
    
        function shop_api_order($info){
            $order_info['api_num'] = $info['o_num'];
            $order_info['sys_num'] = $info['g_num'];
            $order_info['update_time'] = $info['biz_date']." 0:00:00";
            $order_info['type'] = 'shiping_order_goods_'.$info['biz_date'];
            $shop_info = $this->get_shop_info(array('kh_id' => $info['kh_id'], 'shop_code' => $info['shop_code']));

            if (empty($shop_info)) {
                return array();
            }

            $order_info['p_id'] = $shop_info['id'];
            return $order_info;
        }
    
    

    function set_send_info($info) {
        $order_info['fail_num'] = $info['num'];
        $order_info['update_time'] = date('Y-m-d H:i:s');
        $order_info['type'] = 'order_send';
        $shop_info = $this->get_shop_info(array('kh_id' => $info['kh_id'], 'shop_code' => $info['shop_code']));

        if (empty($shop_info)) {
            return array();
        }

        $order_info['p_id'] = $shop_info['id'];
        return $order_info;
    }

    function set_trans_order($info) {
        $order_info['fail_num'] = $info['num'];
        $order_info['update_time'] = date('Y-m-d H:i:s');
        $order_info['type'] = 'trans_order';
        $shop_info = $this->get_shop_info(array('kh_id' => $info['kh_id'], 'shop_code' => $info['shop_code']));
        if (empty($shop_info)) {
            return array();
        }
        $order_info['p_id'] = $shop_info['id'];

        return $order_info;
    }

    function set_order_info($info) {
        $order_info['sys_num'] = $info['num'];
        $order_info['start_time'] = $this->record_time;
        $order_info['end_time'] = date('Y-m-d H:i:s');
        $order_info['update_time'] = date('Y-m-d H:i:s');
        $order_info['api_num'] = 0;
        $order_info['type'] = 'order';
        $shop_info = $this->get_shop_info(array('kh_id' => $info['kh_id'], 'shop_code' => $info['shop_code']));
        if (empty($shop_info)) {
            return array();
        }
        $order_info['p_id'] = $shop_info['id'];
        if ($info['source'] == 'taobao') {
            $filer['shop_nick'] = $shop_info['shop_nick'];
            $filer['created'] = $this->record_time;
            $order_info['api_num'] = $this->get_rds_order_num($info['rds_id'], $filer);
        }
        return $order_info;
    }

    function get_rds_order_num($rds_id, $filer) {
        $db = $this->create_rds_db($rds_id, 'sys_info');
        $sql = "select count(1) from jdp_tb_trade where  1 ";
        if (isset($filer['shop_nick'])) {
            $sql.=" AND seller_nick = '{$filer['shop_nick']}'";
        }
        if (isset($filer['created'])) {
            $sql.=" AND created >= '{$filer['created']}'";
        }
        if (isset($filer['status'])) {
            $status_arr = explode(',', $filer['status']);
            $status_str = "status='" . implode("'  OR status='", $status_arr) . "'";
            $sql.=" AND ($status_str)";
        }

        return $db->get_value($sql);
    }

    function get_taobao_status() {
        //    WAIT_BUYER_PAY：等待买家付款
//WAIT_SELLER_SEND_GOODS：等待卖家发货
//SELLER_CONSIGNED_PART：卖家部分发货
//WAIT_BUYER_CONFIRM_GOODS：等待买家确认收货
//TRADE_BUYER_SIGNED：买家已签收（货到付款专用）
//TRADE_FINISHED：交易成功
//TRADE_CLOSED：交易关闭
//TRADE_CLOSED_BY_TAOBAO：交易被淘宝关闭
//TRADE_NO_CREATE_PAY：没有创建外部交易（支付宝交易）
//WAIT_PRE_AUTH_CONFIRM：余额宝0元购合约中
//PAY_PENDING：外卡支付付款确认中  
        return "WAIT_SELLER_SEND_GOODS,SELLER_CONSIGNED_PART,WAIT_BUYER_CONFIRM_GOODS,TRADE_BUYER_SIGNED,TRADE_FINISHED,TRADE_CLOSED";
    }

    function get_shop_info($filer) {
        $sql = "select * from mo_shop where 1";
        if (isset($filer['kh_id'])) {
            $sql.=" AND kh_id = '{$filer['kh_id']}'";
        }
        if (isset($filer['kh_id'])) {
            $sql.=" AND shop_code = '{$filer['shop_code']}'";
        }
        return $this->db->get_row($sql);
    }

    private function get_start_time() {
        return date('Y-m-d H', strtotime('-8 hours')) . ":00:00";
    }

    function set_shop_info(&$info) {
        $key_arr = array('kh_id', 'rds_id', 'kh_name', 'shop_id', 'shop_code', 'shop_name', 'source', 'api');
        $shop_info = $this->get_data_by_key($info, $key_arr);
        $api = json_decode($shop_info['api'], true);
        if ($shop_info['source'] == 'taobao') {
            $shop_info['expires_in'] = isset($api['expires_in']) ? $api['expires_in'] : '';
            $shop_info['shop_nick'] = isset($api['nick']) ? $api['nick'] : '';
        }
        return $shop_info;
    }
    function set_notice_email(&$info) {
        $param_info['kh_id'] = $info['kh_id'];
        $param_info['kh_name'] = $info['kh_name'];
        $param_info['kh_email'] = $info['value'];
        if(empty($param_info['kh_email'])){
            $sql = " select kh_email from osp_kehu where kh_id='{$info['kh_id']}' ";
            $old_mail_str = $this->db->get_value($sql);
            $old_mail  = implode(";",  $old_mail_str);
                $new_mail = implode(";",  $info['value']);
                $new_mail = array_merge($old_mail, $new_mail);
                $new_mail = array_unique($new_mail);
                $param_info['kh_email']  = implode(";", $new_mail);

        }
        return $param_info;
    }

    function get_data_by_key($info, $key_arr) {
        $new_info = array();
        foreach ($key_arr as $key) {
            $new_info[$key] = $info[$key];
        }
        return $new_info;
    }

}
