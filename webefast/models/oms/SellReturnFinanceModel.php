<?php

require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');

class SellReturnFinanceModel extends TbModel {

    public $return_type = array(
        1 => '退款单',
        2 => '退货单',
        3 => '退款退货单',
    );
    public $return_order_status = array(
        0 => '未确认',
        1 => '已确认',
        3 => '已作废',
    );

    function get_table() {
        return 'oms_sell_return';
    }

    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        //print_r($filter);
        $sql_values = array();
        $sql_join = "";
        
        $is_distinct = false;
        //订单标签，组装表连接
        if (isset($filter['return_label_name']) && $filter['return_label_name'] !== '') {
            $is_distinct = TRUE;
            $tag_arr = explode(',', $filter['return_label_name']);
            $tag_join = in_array('none', $tag_arr) ? ' LEFT JOIN ' : ' INNER JOIN ';
            $sql_join .= $tag_join . " oms_sell_return_tag AS rt ON rl.sell_return_code = rt.sell_return_code AND rt.tag_type='return_tag' ";
        }
//        $sql_main = "FROM {$this->table} rl $sql_join WHERE (finance_check_status = '1'  or finance_check_status = '2')";
        $sql_main = "FROM {$this->table} rl $sql_join WHERE 1 and return_order_status = 1";
            //退单号
        if (isset($filter['sell_return_code']) && !empty($filter['sell_return_code'])) {
            $sql_main .= " AND rl.sell_return_code = :sell_return_code ";
            $sql_values[':sell_return_code'] = $filter['sell_return_code'];
        }
        //关联订单号
        if (isset($filter['sell_record_code']) && !empty($filter['sell_record_code'])) {
            $sql_main .= " AND rl.sell_record_code = :sell_record_code ";
            $sql_values[':sell_record_code'] = $filter['sell_record_code'];
        }
        //交易号
        if (isset($filter['deal_code']) && !empty($filter['deal_code'])) {
            $sql_main .= " AND rl.deal_code = :deal_code ";
            $sql_values[':deal_code'] = $filter['deal_code'];
        }
        //平台退单号
        if (isset($filter['refund_id']) && !empty($filter['refund_id'])) {
            $sql_main .= " AND rl.refund_id = :refund_id ";
            $sql_values[':refund_id'] = $filter['refund_id'];
        }
        //订单号
        if (isset($filter['sell_record_code']) && !empty($filter['sell_record_code'])) {
            $sql_main .= " AND rl.sell_record_code = :sell_record_code ";
            $sql_values[':sell_record_code'] = $filter['sell_record_code'];
        }
        //确认退款状态
        if (isset($filter['sub_return_status']) && $filter['sub_return_status'] <> '' && $filter['sub_return_status'] <> 'all') {
            if($filter['sub_return_status'] == 2){
                $sql_main .= " AND (rl.finance_check_status = 2 or rl.finance_check_status = 0)";
            } else {
                $sql_main .= " AND rl.finance_check_status = 1 ";
            }
            
        }
        //有无换货单
        if (isset($filter['is_change']) && $filter['is_change'] != '') {
            $sql_main .= " AND rl.is_exchange_goods = :is_change ";
            $sql_values[':is_change'] = $filter['is_change'];
        }
        //确认收货状态
        if (isset($filter['return_shipping_status']) && $filter['return_shipping_status'] <> '' && $filter['return_shipping_status'] <> 'all') {
            $sql_main .= " AND rl.return_shipping_status = :return_shipping_status ";
            $sql_values[':return_shipping_status'] = $filter['return_shipping_status'];
            }
        //买家昵称
        if (isset($filter['buyer_name']) && $filter['buyer_name'] != '') {
//            $sql_main .= " AND (rl.buyer_name LIKE :buyer_name )";
//            $sql_values[':buyer_name'] = $filter['buyer_name'] . '%';

         $customer_code_arr= load_model('crm/CustomerOptModel')->get_customer_code_with_search($filter['buyer_name']);
                if(!empty($customer_code_arr)){
                        $customer_code_str = "'".implode("','", $customer_code_arr)."'";
                        $sql_main .= " AND ( rl.customer_code in ({$customer_code_str}) ) ";  
                }else{
                        $sql_main .= " AND rl.buyer_name = :buyer_name ";
                        $sql_values[':buyer_name'] = $filter['buyer_name'];
                }    
        }
        //买家支付宝账号
        if (isset($filter['buyer_alipay_no']) && !empty($filter['buyer_alipay_no'])) {
            $sql_main .= " AND rl.buyer_alipay_no = :buyer_alipay_no ";
            $sql_values[':buyer_alipay_no'] = $filter['buyer_alipay_no'];
        }
        //买家电话
        if (isset($filter['return_mobile']) && $filter['return_mobile'] != '') {
            $sql_main .= " AND (rl.return_mobile LIKE :return_mobile )";
            $sql_values[':return_mobile'] = $filter['return_mobile'] . '%';
        }
        //销售平台
        if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] <> '') {
            $arr = explode(',', $filter['sale_channel_code']);
            $str = $this->arr_to_in_sql_value($arr, 'sale_channel_code', $sql_values);
            $sql_main .= " AND rl.sale_channel_code in ({$str}) ";
        }
        
        //订单标签的条件查询
        if (isset($filter['return_label_name']) && $filter['return_label_name'] !== '') {
            $tag_arr = explode(',', $filter['return_label_name']);
            $tag_sql = array();
            if (in_array('none', $tag_arr)) {
                $key = array_search('none', $tag_arr);
                unset($tag_arr[$key]);
                $tag_sql[] = " rt.tag_v IS NULL ";
            }
            if (!empty($tag_arr)) {
                $tag_str = $this->arr_to_in_sql_value($tag_arr, 'tag', $sql_values);
                $tag_sql[] = " rt.tag_v IN ({$tag_str}) ";
            }
            $tag_sql_main = implode(' OR ', $tag_sql);
            $sql_main .= " AND ({$tag_sql_main})";
        }
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] <> '') {
            $arr = explode(',', $filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $sql_main .= " AND rl.shop_code in ({$str}) ";
        }
        // 退款时间  财务审核时间
        if (isset($filter['check_time_start']) && !empty($filter['check_time_start'])) {
            $sql_main .= " AND rl.finance_confirm_time >= :check_time_start ";
            $sql_values[':check_time_start'] = $filter['check_time_start'] . ' 00:00:00';
        }
        if (isset($filter['check_time_end']) && !empty($filter['check_time_end'])) {
            $sql_main .= " AND rl.finance_confirm_time <= :check_time_end ";
            $sql_values[':check_time_end'] = $filter['check_time_end'] . ' 23:59:59';
        }
         //时间处理
   	if (isset($filter['time_type']) && $filter['time_type'] !== '') {
            if (isset($filter['time_start']) && $filter['time_start'] !== '') {
                    $sql_main .= " AND rl.".$filter['time_type']." >= :time_start ";
                    $sql_values[':time_start'] = $filter['time_start'] . ' 00:00:00';
            }
            if (isset($filter['time_end']) && $filter['time_end'] !== '') {
                    $sql_main .= " AND rl.".$filter['time_type']." <= :time_end ";
                    $sql_values[':time_end'] = $filter['time_end'] . ' 23:59:59';
            }
        }

        	
        
//        //退单创建时间
//        if (isset($filter['create_time_start']) && !empty($filter['create_time_start'])) {
//            $sql_main .= " AND rl.create_time >= :create_time_start ";
//            $sql_values[':create_time_start'] = $filter['create_time_start'] . ' 00:00:00';
//        }
//        if (isset($filter['create_time_end']) && !empty($filter['create_time_end'])) {
//            $sql_main .= " AND rl.create_time <= :create_time_end ";
//            $sql_values[':create_time_end'] = $filter['create_time_end'] . ' 23:59:59';
//        }
//        // 确认收货时间
//        if (isset($filter['receive_time_start']) && !empty($filter['receive_time_start'])) {
//            $sql_main .= " AND rl.receive_time >= :receive_time_start ";
//            $sql_values[':receive_time_start'] = $filter['receive_time_start'] . ' 00:00:00';
//        }
//        if (isset($filter['receive_time_end']) && !empty($filter['receive_time_end'])) {
//            $sql_main .= " AND rl.receive_time <= :receive_time_end ";
//            $sql_values[':receive_time_end'] = $filter['receive_time_end'] . ' 23:59:59';
//        }
        //退货原因
        if (isset($filter['return_reason_code']) && !empty($filter['return_reason_code'])) {
            $sql_main .= " AND  rl.return_reason_code in (:return_reason_code)";
            $sql_values[':return_reason_code'] =explode(",", $filter['return_reason_code']);
        }
        //退款方式tab页面
        if (isset($filter['do_list_tab']) && $filter['do_list_tab'] !== '') {
            //线下退款
            if ($filter['do_list_tab'] == 'tabs_offline') {
                $sql_main .= " and rl.return_pay_code='offline'";
            } else if ($filter['do_list_tab'] == 'tabs_online') {
                $sql_main .= " and rl.return_pay_code <> 'offline'";
            }else{
                
            }
        } else {
            //$sql_main .= " and rl.return_pay_code='offline'";
        }

        $select = 'rl.*';
        //增值服务
        $sql_main .= load_model('base/SaleChannelModel')->get_values_where('rl.sale_channel_code');
        //订单标签
        $group_by = '';
        if($is_distinct === TRUE){
            $select = " DISTINCT " . $select;
            $group_by = " GROUP BY rl.sell_return_code ";
            $sql_main.= $group_by;
        }
        $group_by_status = empty($group_by) ? FALSE : TRUE;
        
        if($filter['return_shipping_status'] == 1){
            $sql_main .= " ORDER BY receive_time asc";
        }else{
            $sql_main .= " ORDER BY create_time ASC";
        }
        //订单标签
        $sql_tag = " SELECT rl.sell_return_code,rt.tag_desc FROM {$this->table} rl INNER JOIN oms_sell_return_tag rt ON rl.sell_return_code = rt.sell_return_code WHERE rl.return_order_status = 1 ";
        $tag_data = $this->db->get_all($sql_tag);
        $tag_arr = array();
        foreach ($tag_data as $value) {
            $tag_arr[$value['sell_return_code']][] = $value['tag_desc'];
        }
        //$sql_main .= " ORDER BY receive_time asc,create_time ASC";
       
        
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select,$group_by_status);
        filter_fk_name($data['data'], array('store_code|store', 'shop_code|shop'));
        $tbl_cfg = array(
            'base_sale_channel' => array('fld' => 'sale_channel_code,sale_channel_name', 'relation_fld' => 'sale_channel_code+sale_channel_code'),
        );
        require_model('util/GetDataBySqlRelModel');
        $obj = new GetDataBySqlRelModel();
        $obj->tbl_cfg = $tbl_cfg;
        $data['data'] = $obj->get_data_by_cfg(null, $data['data']);
        $sell_return_mod = load_model('oms/SellReturnModel');
        //设置参数启动后必须
        $arr = array('order_return_huo','safety_control');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['order_return_huo'] = isset($ret_arr['order_return_huo']) ? $ret_arr['order_return_huo'] : '';
        foreach ($data['data'] as $key => &$value) {
            $value['sell_return_tag'] = implode(',', $tag_arr[$value['sell_return_code']]);
            $data['data'][$key]['finance_check_status_name'] = '通知财务审核';
            if ($data['data'][$key]['finance_confirm_time'] == '0000-00-00 00:00:00') {
                $data['data'][$key]['finance_confirm_time'] = '';
            }
            if ($data['data'][$key]['agreed_refund_time'] == '0000-00-00 00:00:00') {
                $data['data'][$key]['agreed_refund_time'] = '';
            }
            if ($data['data'][$key]['receive_time'] == '0000-00-00 00:00:00') {
                $data['data'][$key]['receive_time'] = '';
            }
            if ($data['data'][$key]['is_exchange_goods'] == 1) {
                $data['data'][$key]['change_record_txt'] = '是';
            } else {
                $data['data'][$key]['change_record_txt'] = '否';
            }


            $data['data'][$key]['return_status'] =$data['data'][$key]['return_order_status'];
            //提取退单状态
            $data['data'][$key]['return_order_status'] = $sell_return_mod->return_order_status_exp($value);
            if ($response['order_return_huo'] == '1' && $value['return_type'] == '3' && $value['return_shipping_status'] <> '1') {
                $data['data'][$key]['return_show'] = 2;
            }
            //退货原因
            $return_reason_row = load_model('base/ReturnReasonModel')->get_by_code($value['return_reason_code']);
            $data['data'][$key]['return_reason'] = isset($return_reason_row['return_reason_name'])?$return_reason_row['return_reason_name']:'';
            if($ret_arr['safety_control'] == 1 && $filter['ctl_type'] == 'view'){
                $value['buyer_name']=$this->name_hidden($value['buyer_name']);
                $value['return_mobile']=$this->phone_hidden($value['return_mobile']);
            }
        }
        return $this->format_ret(1, $data);
    }

    function get_by_id($id) {
        $data = $this->get_row(array('sell_return_id' => $id));
        filter_fk_name($data['data'], array('store_code|store', 'shop_code|shop'));
        return $data;
    }

    /*
     * 修改纪录
     */

    function update($data, $id) {
        $ret = parent::update($data, array('sell_return_id' => $id));
        return $ret;
    }

}
