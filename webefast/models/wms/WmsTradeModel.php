<?php

require_lib('util/oms_util', true);
require_model('wms/WmsBaseModel');

class WmsTradeModel extends WmsBaseModel {

    /**
     * @var string 表名
     */
    protected $table = 'wms_oms_trade';
    protected $detail_table = 'wms_oms_order';
    public $order_type_oms = array(
        'all' => '全部',
        'sell_record' => '销售订单',
        'sell_return' => '销售退单',
    );
    public $order_type_b2b = array(
        'all' => '全部',
        'pur_notice' => '采购通知单',
        'pur_return_notice' => '采购退货通知单',
        'wbm_notice' => '批发销货通知单',
        'wbm_return_notice' => '批发退货通知单',
        'shift_out' => '移仓出库',
        'shift_in' => '移仓入库',
     	'stm_diy' => '组装单',
     	'stm_split' => '拆分单'
    );
    public $order_type = array(
        'all' => '全部',
        'sell_record' => '销售订单',
        'sell_return' => '销售退单',
        'pur_notice' => '采购通知单',
        'pur_return_notice' => '采购退货通知单',
        'wbm_notice' => '批发销货通知单',
        'wbm_return_notice' => '批发退货通知单',
        'shift_out' => '移仓出库',
        'shift_in' => '移仓入库',
        'stm_diy' => '组装单',
        'stm_split' => '拆分单'
    );
    public $wms_sync = array(
        'all' => '全部',
        1 => '已更新',
        0 => '未更新',
    );

    function do_list_by_page($filter) {
        $filter['ref'] = 'do';

        if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] == 'select_all') {
            unset($filter['sale_channel_code']);
        }
        if (isset($filter['shop_code']) && $filter['shop_code'] == 'all') {
            unset($filter['shop_code']);
        }
        if (isset($filter['order_type']) && $filter['order_type'] == 'all') {
            unset($filter['order_type']);
        }
        if (isset($filter['wms_store_code']) && $filter['wms_store_code'] == 'all') {
            unset($filter['wms_store_code']);
        }
        if (isset($filter['cancel_request_flag']) && $filter['cancel_request_flag'] == 'all') {
            unset($filter['cancel_request_flag']);
        }
        //页面默认只查询显示的仓库的零售单
        if (empty($filter['efast_store_code'])) {
            $store_data = $this->get_wms_store();
            foreach ($store_data as $value) {
                $filter['efast_store_code'][] = $value['store_code'];
            }
            $filter['efast_store_code'] = implode(',', $filter['efast_store_code']);
        }
        return $this->get_by_page($filter);
    }

    /**
     * 根据条件查询数据<br>
     * 注意: 此方法为核心公共方法, 受多处调用, 请慎重修改.
     * @param $filter
     * @param $onlySql
     * @param $select
     * @return array
     */
    function get_by_page($filter, $onlySql = false, $select = 'rl.*') {
        foreach ($filter as $key => $v) {
            $filter[$key] = trim($v);
        }
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        $sql_values = array();
//        if(isset($filter['goods_barcode']) && $filter['goods_barcode'] !== ''){
//            $sql_barcode = $this->db->getOne("select count(1) from oms_sell_record_detail where barcode='".$filter['goods_barcode']."'");
//            if($sql_barcode >0){
//                $sql_join = "inner join oms_sell_record_detail rr on rl.record_code=rr.sell_record_code";
//            }else{
//                $sql_join = "inner join oms_sell_return_detail rr on rl.record_code=rr.sell_return_code";
//            }
//        }
//        elseif(isset($filter['goods_code']) && $filter['goods_code'] !== ''){
//            $sql_code = $this->db->getOne("select count(1) from oms_sell_record_detail where barcode='".$filter['goods_barcode']."'");
//            if($sql_code >0){
//                $sql_join = "inner join oms_sell_record_detail rr on rl.record_code=rr.sell_record_code";
//            }else{
//                $sql_join = "inner join oms_sell_return_detail rr on rl.record_code=rr.sell_return_code";
//            }
//        }else{
//
//        }
        $sql_join = "";
        $wmsId = empty($filter['wmsId']) ? 'oms' : $filter['wmsId'];
        if ($wmsId == 'b2b') {
            $sql_main = "FROM wms_b2b_trade rl $sql_join WHERE 1 ";
        } else {
            $sql_main = "FROM {$this->table} rl $sql_join WHERE 1 ";
        }


        $bak_sql_main = $sql_main;
        $sql_one_main_arr = array();
        $sql_one_values = array();

        $array_state = array();
        $tab = empty($filter['do_list_tab']) ? 'tabs_wait_upload' : $filter['do_list_tab'];

        switch ($tab) {
            case 'tabs_all'://全部
                ;
                break;
            case 'tabs_wait_upload'://待上传
                $array_state[] = ' rl.upload_request_flag=0'; //
                $array_state[] = 'rl.upload_response_flag=0';
                $array_state[] = 'rl.wms_order_flow_end_flag=0';
                $array_state[] = 'rl.cancel_response_flag<>10';
                break;
            case 'tabs_wait_order'://待发货/待收货
                $array_state[] = 'rl.upload_request_flag=10';
                $array_state[] = 'rl.upload_response_flag=10';
                $array_state[] = ' (  rl.cancel_request_flag=0  OR  rl.cancel_response_flag=20   ) ';
                $array_state[] = ' rl.wms_order_flow_end_flag=0 ';
                break;
            case 'tabs_wait_process'://待处理
                //rl.wms_order_flow_end_flag=1 AND rl.process_flag <30
                $array_state[] = ' rl.wms_order_flow_end_flag=1';
                $array_state[] = '( rl.process_flag = 0 or rl.process_flag = 20)';
                break;
            case 'tabs_ordered'://已发货/已收货
                $array_state[] = 'rl.wms_order_flow_end_flag=1';
                $array_state[] = 'rl.process_flag=30';
                break;
            case 'tabs_cancel':
                $array_state[] = 'rl.cancel_request_flag=10';
                $array_state[] = 'rl.cancel_response_flag=10';
                break;
            case 'tabs_fail'://操作失败：   取消失败  处理失败 上传失败
                $array_state[] = '((rl.cancel_response_flag=20) OR (rl.process_flag = 20) OR (rl.upload_response_flag = 20 AND rl.cancel_response_flag<>10))';
                break;
        }
        //取消状态
        if (isset($filter['cancel_request_flag']) && $filter['cancel_request_flag'] !== '') {

            $cancel_status = $filter['cancel_request_flag'];

            switch ($cancel_status) {
                case 'all'://全部
                    ;
                    break;
                case 'wait_cancel'://未取消
                    $array_state[] = 'rl.upload_request_flag in (0,20)';
                    $array_state[] = 'rl.upload_response_flag=10';
                    $array_state[] = 'rl.wms_order_flow_end_flag=0';

                    break;
                case 'canceling'://取消中
                    $array_state[] = 'cancel_request_flag=10';
                    $array_state[] = 'cancel_response_flag=0';
                    $array_state[] = 'wms_order_flow_end_flag=0';
                    break;
                case 'cancel_success'://取消成功
                    $array_state[] = 'cancel_request_flag=10';
                    $array_state[] = 'cancel_response_flag=10';
                    $array_state[] = 'wms_order_flow_end_flag=0';

                    break;
                case 'cancel_fail'://取消失败
                    $array_state[] = 'cancel_response_flag=20';
                    $array_state[] = 'wms_order_flow_end_flag=0';
                    break;
            }
        }

        $join_status_sql = '';

        if (!empty($array_state) && $array_state[0] != '') {
            $new_array_state = array_unique($array_state);
            $join_status_sql = implode(' and ', $new_array_state);
            $join_status_sql = ' AND ' . $join_status_sql;
        }


        if (isset($filter['deal_code']) && $filter['deal_code'] !== '') {
            $sql_one_main_arr['deal_code'] = " AND rl.deal_code = :deal_code ";
            $sql_one_values[':deal_code'] = $filter['deal_code'] ;
        }

        //eFAST订单号
        if (isset($filter['record_code']) && $filter['record_code'] !== '') {
            $ids_arr = $this->get_record_id($filter['record_code'],$wmsId);
            if (empty($ids_arr)){
                $sql_main .= " AND 1=2 ";
            }else{
                $ids_str = $this->arr_to_in_sql_value($ids_arr, 'id', $sql_values);
                $sql_main .= " AND id in ({$ids_str}) ";
            }
        }
        //WMS单据号
        if (isset($filter['wms_record_code']) && $filter['wms_record_code'] !== '') {
            $sql_main .= " AND rl.wms_record_code = :wms_record_code ";
            $sql_values[':wms_record_code'] = $filter['wms_record_code'] ;
        }
        //新订单号
        if (isset($filter['new_record_code']) && $filter['new_record_code'] !== '') {
            $sql_main .= " AND rl.new_record_code = :new_record_code ";
            $sql_values[':new_record_code'] = $filter['new_record_code'] ;
        }
        //买家昵称
        if (isset($filter['buyer_name']) && $filter['buyer_name'] !== '') {
            $sql_main .= " AND rl.buyer_name like :buyer_name ";
            $sql_values[':buyer_name'] = "%" . $filter['buyer_name'] . "%";
        }
        //商品条形码
        if (isset($filter['goods_barcode']) && $filter['goods_barcode'] !== '') {
            $sql_sku = $this->db->getOne("select sku from goods_sku where barcode=:goods_barcode",array(':goods_barcode'=>$filter['goods_barcode']));
            if (!empty($sql_sku)) {
                $sql_record = $this->db->getAll("select DISTINCT rl.sell_record_code
                                            FROM oms_sell_record_notice rl inner join
                                            oms_sell_record_notice_detail rr on rl.sell_record_code=rr.sell_record_code
                                            where rr.sku like :sql_sku ",array(':sql_sku'=>'%' . $sql_sku . '%'));
                $sql_return = $this->db->getAll("select DISTINCT rl.sell_return_code
                                                FROM oms_sell_return rl inner join
                                                oms_sell_return_detail rr on rl.sell_return_code=rr.sell_return_code
                                                where rr.sku like :sql_sku ",array(':sql_sku'=>'%' . $sql_sku . '%'));
                $sql_arr = array_merge($sql_record, $sql_return);
                foreach ($sql_arr as $val)
                    {
                        $sql_arr1[] = $val['sell_record_code'];
                    }
//                $sql_barcode = implode(",", $sql_arr1);
                $sql_arr1 = array_filter($sql_arr1);
//                $arr = explode(',', $sql_arr1);
//                print_r($sql_arr1);
                $sql_barcode = $this->arr_to_in_sql_value($sql_arr1, 'record_code', $sql_values);
                if (!empty($sql_barcode)) {
                    $sql_main .= " AND rl.record_code in ({$sql_barcode}) and wms_order_flow_end_flag=0 ";
                } else {
                    $sql_main .= " AND rl.record_code in ('') and wms_order_flow_end_flag=0 ";
                }
            } else {
                $sql_main .= " AND rl.record_code in ('') and wms_order_flow_end_flag=0 ";
            }
        }
        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
            $sql_record_value = array(':goods_code'=>$filter['goods_code'] );
            $sql_record = $this->db->getAll("select DISTINCT rl.sell_record_code
                                            FROM oms_sell_record_notice rl inner join
                                            oms_sell_record_notice_detail rr on rl.sell_record_code=rr.sell_record_code
                                             where rr.goods_code like :goods_code ",$sql_record_value);
            $sql_return = $this->db->getAll("select DISTINCT rl.sell_return_code
                                            FROM oms_sell_return rl inner join
                                            oms_sell_return_detail rr on rl.sell_return_code=rr.sell_return_code
                                             where rr.goods_code like :goods_code ",$sql_record_value);
            $sql_arr = array_merge($sql_record, $sql_return);
            foreach ($sql_arr as $val)
                {
                    $sql_arr1[] = $val['sell_record_code'];
                }
            $sql_arr1 = array_filter($sql_arr1);

                $sql_code = $this->arr_to_in_sql_value($sql_arr1, 'record_code', $sql_values);
            if (!empty($sql_code)) {
                $sql_main .= " AND rl.record_code in (" . $sql_code . ") and wms_order_flow_end_flag=0 ";
            } else {
                $sql_main .= " AND rl.record_code in ('') and wms_order_flow_end_flag=0 ";
            }
        }
        //仓库
        if (isset($filter['efast_store_code']) && $filter['efast_store_code'] !== '') {
             $arr = explode(',', $filter['efast_store_code']);
        $str = $this->arr_to_in_sql_value($arr, 'efast_store_code', $sql_values);
            $sql_main .= " AND rl.efast_store_code in ( " .$str. " ) ";
        }

        //销售平台

        if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] !== '') {

                        $arr = explode(',', $filter['sale_channel_code']);
        $str = $this->arr_to_in_sql_value($arr, 'sale_channel_code', $sql_values);
            $sql_main .= " AND rl.sale_channel_code in ( {$str} ) ";
        }
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] !== '') {
             $arr = explode(',', $filter['shop_code']);
        $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $sql_main .= " AND rl.shop_code in ( " . $str . " ) ";
        }

        //订单类型
        if (isset($filter['order_type']) && $filter['order_type'] !== '') {
            $sql_main .= " AND rl.record_type = :order_type ";
            $sql_values[':order_type'] = $filter['order_type'];
        }

        //时间处理
        if (isset($filter['time_type']) && $filter['time_type'] !== '') {
            if ($filter['time_type'] == 'wms_order_time') {
                if (isset($filter['time_start']) && $filter['time_start'] !== '') {
                    $time_start = strtotime($filter['time_start']);
                    $sql_main .= " AND rl.wms_order_time >= :time_start ";
                    $sql_values[':time_start'] = $time_start;
                }
                if (isset($filter['time_end']) && $filter['time_end'] !== '') {
                    $time_end = strtotime($filter['time_end']);
                    $sql_main .= " AND rl.wms_order_time <= :time_end ";
                    $sql_values[':time_end'] = $time_end;
                }
            } else {
                if (isset($filter['time_start']) && $filter['time_start'] !== '') {
                    $sql_main .= " AND rl." . $filter['time_type'] . " >= :time_start ";
                    $sql_values[':time_start'] = $filter['time_start'];
                }
                if (isset($filter['time_end']) && $filter['time_end'] !== '') {
                    $sql_main .= " AND rl." . $filter['time_type'] . " <= :time_end ";
                    $sql_values[':time_end'] = $filter['time_end'];
                }
            }
        }
        $changed_time = date('Y-m-d H:i:s', strtotime('-12 months'));
        $sql_main .= " AND lastchanged >=  '{$changed_time}'";
        $group_by = '';
        if (!empty($filter['group_by'])) {
            $group_by = " group by " . $filter['group_by'] . " ";
        }
        $order_by .= ' ORDER BY upload_request_time DESC,id DESC ';
//        $order_by .= " ORDER BY id DESC";
        //var_dump($filter,$select,$sql_main,$sql_values);

        if (!empty($sql_one_main_arr)) {
            foreach ($sql_one_main_arr as $k => $v) {
                $sql_one_main_first = $v;
                $sql_one_main_kk = $k;
                break;
            }

            $sql_main = $sql_main . $sql_one_main_first;
//             $sql_values = array();
            if (isset($sql_one_values[':' . $sql_one_main_kk])) {
                $sql_values[':' . $sql_one_main_kk] = $sql_one_values[':' . $sql_one_main_kk];
            }
            if (isset($filter['ex_list_tab']) && $filter['ex_list_tab'] !== '') {
                if ($filter['ex_list_tab'] == 'tabs_pay') {
                    $sql_main .= " and rl.order_status = 0 and rl.pay_status = 0";
                }
                if ($filter['ex_list_tab'] == 'tabs_confirm') {
                    $sql_main .= " and rl.order_status = 0 and rl.must_occupy_inv = 1";
                }
                if ($filter['ex_list_tab'] == 'tabs_notice_shipping') {
                    $sql_main .= " and rl.order_status = 1 and rl.shipping_status = 0";
                }
            }
        }

        $sql_main .= $join_status_sql;

        $sql_main .= $group_by;
        $sql_main .= $order_by;
        if (isset($filter['action_type']) && $filter['action_type'] !== '') {
            $select = 'rl.*,rr.* ';
            $sql = "select " . $select . $sql_main;
            $data = load_model('common/BaseModel')->get_all($sql, $sql_values);
        } else {
            $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        }
        //echo $sql_main.$sql_values.$select;
        $tbl_cfg = array(
            'base_sale_channel' => array('fld' => 'sale_channel_code,sale_channel_name', 'relation_fld' => 'sale_channel_code+sale_channel_code'),
            'base_shop' => array('fld' => 'shop_name', 'relation_fld' => 'shop_code+shop_code'),
            'base_store' => array('fld' => 'store_name', 'relation_fld' => 'store_code+efast_store_code'),
        );

        require_model('util/GetDataBySqlRelModel');
        $obj = new GetDataBySqlRelModel();
        $obj->tbl_cfg = $tbl_cfg;
        $data['data'] = $obj->get_data_by_cfg(null, $data['data']);
        // $sys_user = load_model("oms/SellRecordOptModel")->sys_user();
        $new_order_type = array_merge($this->order_type_oms, $this->order_type_b2b);
        $record_code_arr = array();
        $status_record_arr = array();
        foreach ($data['data'] as $key => &$value) {
            //整理wms订单状态
            $status = load_model('wms/WmsRecordModel')->get_status_exp($value);

            $value['status'] = str_replace('|', "<br>", $status['status_txt_ex']);
            $status_record_arr[$value['record_code']] = $status['status_txt_ex'];
            //单据类型
            $value['record_order_type'] = $new_order_type[$value['record_type']];

            //处理时间显示
            $value['upload_request_time'] = ($value['upload_request_time'] > 0) ? $value['upload_request_time'] : '';
            $value['cancel_request_time'] = ($value['cancel_request_time'] > 0) ? $value['cancel_request_time'] : '';
            $value['wms_order_time'] = ($value['wms_order_time'] > 0) ? date("Y-m-d H:i:s", $value['wms_order_time']) : '';
            $value['process_time'] = ($value['process_time'] > 0) ? $value['process_time'] : '';
            $value['log_err_msg'] = htmlspecialchars($this->getProcessEssMsg($value));
            if (isset($value['sale_channel_code'])) {
                $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
            }
            if (isset($value['shop_code'])) {
                $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            }
            $record_code_arr[$value['record_type']][] = $value['record_code'];
        }   
        //导出
        if ($filter['ctl_type'] == 'export' && isset($filter['ctl_export_conf']) && ($filter['ctl_export_conf'] == 'wms_trade_oms' || $filter['ctl_export_conf'] == 'wms_trade_b2b' )) {
            if ($filter['ctl_export_conf'] == 'wms_trade_oms' && !empty($filter['__t_user_code'])) {
                //解密
                $is_security_role = load_model('sys/UserModel')->is_security_role($filter['__t_user_code']);
                if ($is_security_role === true) {
                    if (!empty($record_code_arr['sell_return'])) {
                        //退单
                        $sql_val2 = array();
                        $return_str = $this->arr_to_in_sql_value($record_code_arr['sell_return'], 'return_code', $sql_val2);
                        $sql2 = " SELECT sell_return_code,customer_code,customer_address_id,buyer_name FROM oms_sell_return WHERE sell_return_code IN ({$return_str}) ";
                        $sell_return_data = $this->db->get_all($sql2, $sql_val2);
                        $sell_return_data_en = load_model('sys/security/OmsSecurityOptModel')->get_sell_return_decrypt_list($sell_return_data,'buyer_name');
                        $return_data_en = array();
                        foreach ($sell_return_data_en as $v) {
                            $return_data_en[$v['sell_return_code']] = $v['buyer_name'];
                        }
                        foreach ($data['data'] as &$values) {
                            if (!empty($return_data_en[$values['record_code']])) {
                                $values['buyer_name'] = $return_data_en[$values['record_code']];
                            }
                        }
                    }
                    if (!empty($record_code_arr['sell_record'])) {
                        //订单
                        $sql_val = array();
                        $record_str = $this->arr_to_in_sql_value($record_code_arr['sell_record'], 'record_code', $sql_val);
                        $sql = " SELECT sell_record_code,customer_code,customer_address_id,buyer_name FROM oms_sell_record WHERE sell_record_code IN ({$record_str}) ";
                        $sell_record_data = $this->db->get_all($sql, $sql_val);
                        $sell_record_data_en = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_list($sell_record_data,'buyer_name');
                        $record_data_en = array();
                        foreach ($sell_record_data_en as $v) {
                            $record_data_en[$v['sell_record_code']] = $v['buyer_name'];
                        }
                        foreach ($data['data'] as &$values) {
                            if (!empty($record_data_en[$values['record_code']])) {
                                $values['buyer_name'] = $record_data_en[$values['record_code']];
                            }
                        }
                    }
                    $log = array('user_id' =>0, 'user_code' => $filter['__t_user_code'], 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => '网络订单', 'yw_code' => '', 'operate_type' => '导出', 'operate_xq' => '外包仓零售单导出解密数据');
                    load_model('sys/OperateLogModel')->insert($log);
                }
            }
            foreach ($data['data'] as &$values) {
                $values['status'] = $status_record_arr[$values['record_code']];
            }
        }

        return $this->format_ret(1, $data);
    }

    function get_record_id($record_code,$wms_id){
        $id = 0;
        if($wms_id=='oms'){
            $sql = "select id from wms_oms_trade where record_code=:record_code";
            $id = $this->db->get_all_col($sql,array(':record_code'=>$record_code));
            if(empty($id)){
                $sql = "select id from wms_oms_trade where new_record_code=:record_code AND record_type in('sell_record','sell_return')";
                $id = $this->db->get_all_col($sql,array(':record_code'=>$record_code)); 
            }
        }else{
            $sql = "select id from wms_b2b_trade where record_code=:record_code";
            $id = $this->db->get_all_col($sql,array(':record_code'=>$record_code));
            if(empty($id)){
                $sql = "select id from wms_b2b_trade where new_record_code=:record_code ";
                $id = $this->db->get_all_col($sql,array(':record_code'=>$record_code)); 
            }   
        }
       return $id;
    }
            
    function getProcessEssMsg($value) {
        $log_err_msg = '';
        //上传失败 upload_response_flag = 20 and wms_order_flow_end_flag = 0
        if ($value['upload_response_flag'] == 20 && $value['wms_order_flow_end_flag'] == 0) {
            $log_err_msg .= $value['upload_response_err_msg'] . "<br>";
        }
        //取消失败 cancel_response_flag = 20 and wms_order_flow_end_flag = 0
        if ($value['cancel_response_flag'] == 20 && $value['wms_order_flow_end_flag'] == 0) {
            $log_err_msg .= $value['cancel_response_err_msg'] . "<br>";
        }
        // 处理失败 process_flag = 20
        if ($value['process_flag'] == 20) {
            $log_err_msg .= $value['process_err_msg'];
        }

        return $log_err_msg;
    }

    function getInfoById($id, $table) {
        $sql = "SELECT * FROM " . $table . " WHERE id= " . $id;
        $info = $this->db->get_row($sql);
        return $info;
    }

    function get_wms_oms_log($record_code, $table) {
        $sql = "SELECT * FROM " . $table . " WHERE record_code= '" . $record_code . "' ORDER BY action_time DESC";
        $log_info = $this->db->get_all($sql);
        return $log_info;
    }

    //获取外包仓库日志
    function get_api_log($record_code, $fileds = '*') {
        $sql = "SELECT {$fileds} FROM api_logs WHERE post_data like :record_code ORDER BY add_time DESC";
        $api_log = $this->db->get_all($sql,array(':record_code'=>'%'.$record_code.'%'));
        return $api_log;
    }
    
    //获取外包仓库日志
    function get_api_log_by_id($params, $fileds = '*') {
        $sql = "SELECT {$fileds} FROM api_logs WHERE post_data like :record_code AND id = :id";
        $api_log = $this->db->get_row($sql,array(':record_code'=>'%'.$params['record_code'].'%', ":id" => $params['id']));
        return $api_log;
    }

    function get_wms_store() {
        $sql = "SELECT s.shop_store_code,w.wms_system_code,b.store_name FROM  sys_api_shop_store s
                INNER JOIN wms_config w ON s.p_id=w.wms_config_id
                INNER JOIN base_store b ON b.store_code=s.shop_store_code
                WHERE s.shop_store_type = 1 AND s.p_type = 1 AND s.outside_type = 1 AND (s.store_type = 1 or s.store_type = 0)";
        $sql .= load_model('base/StoreModel')->get_sql_purview_store('b.store_code', NULL);
        $rs = $this->db->get_all($sql);

        $wms_conf = require_conf('sys/wms');
        $ret_data = array();
        foreach ($rs as $r) {
            $store = array('store_code' => $r['shop_store_code'], 'store_name' => $r['store_name']);
            if (isset($wms_conf[$r['wms_system_code']]['name'])) {
                $store['store_name'] .= '[' . $wms_conf[$r['wms_system_code']]['name'] . ']';
            }
            $ret_data[] = $store;
        }
        return $ret_data;
    }

    function getWmsShipping() {
        $wms_shipping = require_conf('sys/wms');
        $new_shipping = array();
        $i = 0;
        foreach ($wms_shipping as $key => $shipping) {
            $new_shipping[$i]['shipping_code'] = $key;
            $new_shipping[$i]['shipping_name'] = $shipping['name'];
            $i ++;
        }
        return $new_shipping;
    }

    function get_order_type_oms() {
        $order_type = $this->order_type_oms;
        $new_order_type = array();
        $i = 0;
        foreach ($order_type as $key => $type) {
            $new_order_type[$i]['order_code'] = $key;
            $new_order_type[$i]['order_name'] = $type;
            $i ++;
        }
        return $new_order_type;
    }

    function get_order_type_b2b() {
        $order_type = $this->order_type_b2b;
        $new_order_type = array();
        $i = 0;
        foreach ($order_type as $key => $type) {
            $new_order_type[$i]['order_code'] = $key;
            $new_order_type[$i]['order_name'] = $type;
            $i ++;
        }
        return $new_order_type;
    }

    function getWmsFlag($type) {
        $wms_flag = require_conf('wms/wms_status');
        return $wms_flag[$type];
    }

    function get_wms_sync() {
        $wms_sync_status = $this->wms_sync;
        $new_wms_sync = array();
        $i = 0;
        foreach ($wms_sync_status as $key => $status) {

            $new_wms_sync[$i]['code'] = $key;
            $new_wms_sync[$i]['value'] = $status;
            $i++;
        }
        return $new_wms_sync;
    }

    function inv_list_by_page($filter, $onlySql = false, $select = 'rl.*') {
        $sql_values = array();
        $sql_join = "INNER JOIN goods_sku gb ON rl.barcode = gb.barcode";
        $sql_main = "FROM wms_goods_inv rl $sql_join WHERE 1 ";

        //仓库
        if (isset($filter['efast_store_code']) && $filter['efast_store_code'] !== '') {
            $arr = explode(',', $filter['efast_store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'efast_store_code', $sql_values);
            $sql_main .= " AND rl.efast_store_code in ( " . $str. " ) ";
        }

        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
            $sql_main .= " AND gb.goods_code = :goods_code ";
            $sql_values[':goods_code'] = $filter['goods_code'];
        }
        
        //条形码
        if (isset($filter['barcode']) && $filter['barcode'] !== '') {
            $sql_main .= " AND rl.barcode = :barcode";
            $sql_values[':barcode'] = $filter['barcode'];
        }

        //库存状态
        if (isset($filter['is_sync']) && $filter['is_sync'] !== '' && $filter['is_sync'] != 'all') {
            // $filter['is_sync'] = deal_strs_with_quote($filter['is_sync']);
            if ($filter['is_sync'] == 1) {
                $sql_main .= " AND rl.is_success = 1 and rl.is_sync = 1 ";
            } elseif ($filter['is_sync'] == 0) {
                $sql_main .= " AND ((rl.is_sync = 1 and rl.is_success = 0) or rl.is_sync = 0) ";
            }
        }
        //库存获取时间
        if (isset($filter['down_time_start']) && $filter['down_time_start'] !== '') {
            $sql_main .= " AND rl.down_time >= :down_time_start ";
            $sql_values[':down_time_start'] = $filter['down_time_start'] . ' 00:00:00';
        }
        if (isset($filter['down_time_end']) && $filter['down_time_end'] !== '') {
            $sql_main .= " AND rl.down_time <= :down_time_end ";
            $sql_values[':down_time_end'] = $filter['down_time_end'] . ' 23:59:59';
        }

        $group_by = '';
        if (!empty($filter['group_by'])) {
            $group_by = " group by " . $filter['group_by'] . " ";
        }
        $order_by = " ORDER BY rl.down_time DESC,rl.barcode ASC";


        $sql_main .= $group_by;
        $sql_main .= $order_by;

        if (isset($filter['action_type']) && $filter['action_type'] !== '') {
            $select = ' rl.*,rr.* ';
            $sql = "select " . $select . $sql_main;
            $data = load_model('common/BaseModel')->get_all($sql, $sql_values);
        } else {
            $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        }

        $tbl_cfg = array(
            'base_store' => array('fld' => 'store_name', 'relation_fld' => 'store_code+efast_store_code')
        );

        require_model('util/GetDataBySqlRelModel');
        $obj = new GetDataBySqlRelModel();
        $obj->tbl_cfg = $tbl_cfg;
        $data['data'] = $obj->get_data_by_cfg(null, $data['data']);
// var_dump($data);

        foreach ($data['data'] as $key => &$value) {
            //整理wms订单状态
            //单据类型
            $value['record_order_type'] = $this->order_type[$value['record_type']];
            $value['sync_time'] = ($value['sync_time'] > 0) ? $value['sync_time'] : '';
            $value['sync_status'] = $this->getSyncStatus($value['is_success']);
            $value['efast_store_code_name'] = $this->getEfastStoreCodeName($value['efast_store_code']);
            // $value['outside_code'] =
            $wms_outside = $this->getWmsOutsideCode($value['efast_store_code']);
            if ($wms_outside && $wms_outside['out_name'] != '') {
                $value['wms_store_out_code'] = $wms_outside['outside_code'];
                $value['wms_store_out_name'] = $wms_outside['out_name'];
            } else {
                $value['wms_store_out_name'] = '';
                $value['wms_store_out_name'] = '';
            }
        }

        return $this->format_ret(1, $data);
    }

    //查询外部仓储信息
    function getWmsOutsideCode($efast_store_code) {
        $sql = "SELECT * FROM sys_api_shop_store WHERE shop_store_code = '" . $efast_store_code . "' AND shop_store_type = 1 AND outside_type = 1  ";
        $api_shop_store = $this->db->get_row($sql);
        $wms_outside_info = array();
        if ($api_shop_store) {
            $wms_outside_info['outside_code'] = $api_shop_store['outside_code'];
            $sql = "SELECT * FROM wms_config WHERE wms_config_id = " . $api_shop_store['p_id'];
            $wms_config = $this->db->get_row($sql);
            if ($wms_config) {
                $wms_shipping_config = $this->getWmsShipping();
                foreach ($wms_shipping_config as $key => $wms_shipping) {
                    if ($wms_shipping['shipping_code'] == $wms_config['wms_system_code']) {
                        $wms_outside_info['out_name'] = $wms_shipping['shipping_name'];
                        return $wms_outside_info;
                    }
                }
            }
        }
        return '';
    }

    function getEfastStoreCodeName($store_code) {
        $store = load_model('base/StoreModel')->get_purview_store();
        $new_store = array();
        foreach ($store as $key => $s) {
            if ($s['store_code'] == $store_code) {
                return $s['store_name'] . "[" . $s['store_code'] . "]";
            }
        }
        return '';
    }

    function getSyncStatus($status) {
        $new_status = '';
        if ($status == 1) {
            $new_status = '已更新';
        } elseif ($status == 0) {
            $new_status = '未更新';
        } else {
            $new_status = '';
        }
        return $new_status;
    }

    function update_express($filter) {
        //修改快递单号
        $sql1 = "select express_no from wms_oms_trade  where record_code = '{$filter['record_code']}'";
        $old_express = $this->db->get_row($sql1);
        if ($old_express['express_no'] != $filter['express_no']) {
            $sql = "UPDATE wms_oms_trade SET express_no = :express_no WHERE record_code = :record_code ";
            $ret = $this->query($sql, array(':express_no' => $filter['express_no'], ':record_code' => $filter['record_code']));
            if ($ret['status'] != 1) {
                return $ret;
            }
            //添加系统日志
            $yw_code = $filter['record_code'];
            $module = '网络订单'; //模块名称
            $operate_type = '编辑'; //操作类型
            $log_xq = '快递单号由' . $old_express['express_no'] . '修改为' . $filter['express_no'];
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_type' => $operate_type, 'operate_xq' => $log_xq);
            load_model('sys/OperateLogModel')->insert($log);
            return $this->format_ret(1, '', '更新完成');
        }
        return $this->format_ret(1, '', '更新完成');
    }

    function update_express_company($filter) {
        //修改快递公司
        $sql1 = "select express_code from wms_oms_trade  where record_code = '{$filter['record_code']}'";
        $old_express = $this->db->get_row($sql1);
        $new_express_name = oms_tb_val('base_express', 'express_name', array('express_code' => $filter['express_code']));
        $old_express_name = oms_tb_val('base_express', 'express_name', array('express_code' => $old_express['express_code']));
        if ($filter['express_code'] != $old_express['express_code']) {
            $sql = "UPDATE wms_oms_trade SET express_code = :express_code WHERE record_code = :record_code ";
            $ret = $this->query($sql, array(':express_code' => $filter['express_code'], ':record_code' => $filter['record_code']));
            if ($ret['status'] != 1) {
                return $ret;
            }
            //添加系统日志
            $yw_code = $filter['record_code'];
            $module = '网络订单'; //模块名称
            $operate_type = '编辑'; //操作类型
            $log_xq = '快递公司由' . $old_express_name . '修改为' . $new_express_name;
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_type' => $operate_type, 'operate_xq' => $log_xq);
            load_model('sys/OperateLogModel')->insert($log);
            return $this->format_ret(1, $new_express_name, '');
        }
        return $this->format_ret(1, $old_express_name, '');
    }

}
