<?php

require_model('tb/TbModel');
require_lib('comm_util', true);
class OmsSellInvoiceModel extends TbModel {

    function __construct() {
        parent::__construct('oms_sell_invoice');
    }

    function set_sell_invoice($sell_record_code) {

        $record_info = load_model('oms/SellRecordModel')->get_record_by_code($sell_record_code);
        if ($record_info['invoice_status'] == 0) {
            return $this->format_ret(1, '', '不开票');
        }
        if($record_info['invoice_money']==0){
            $invoice_amount = round($record_info['paid_money']-$record_info['point_fee']-$record_info['alipay_point_fee']-$record_info['coupon_fee'],2);
        }else{
            $invoice_amount = $record_info['invoice_money'];
        }
//        //金税
//        $invoice_shop = load_model('oms/invoice/JsFapiaoModel')->get_shop_invoice_info($record_info['shop_code']);
//        if (empty($invoice_shop)) {
//            return $this->format_ret(-1, '', '店铺未设置开票');
//        }

        $up_data = array(
            'status' => 1,
            'sell_record_code' => $record_info['sell_record_code'],
            'deal_code' => $record_info['deal_code'],
            'shop_code'=> $record_info['shop_code'],
            'deal_code_list' => $record_info['deal_code_list'],
            'customer_code' => $record_info['customer_code'],
            'buyer_name' => $record_info['buyer_name'],
            'receiver_name' => $record_info['receiver_name'],
            'payable_money' => $record_info['payable_money'], //需要减去折扣
            'discount_money' => 0,
            'invoice_amount' => $invoice_amount,
            'invoice_content' => $record_info['invoice_content'],
            'invoice_title' => $record_info['invoice_title'],
            'invoice_number' => $record_info['invoice_number'],
        );
        //没设置取店铺开票设置
//        if ($invoice_info['invoice_type'] == 0) {
//            $up_data['invoice_type'] = $invoice_shop['invoice_type'];
//        }
        $dup_update_fld = "status,discount_money,payable_money,invoice_amount,invoice_content,invoice_title,invoice_number";
        $this->insert_dup($up_data, 'update', $dup_update_fld);
//        $this->update($up_data, "sell_record_code= '{$sell_record_code}'");

        return $this->format_ret(1);
    }

    function get_sell_invoice($sell_record_code, $fld = '*') {
        $result = $this->db->get_row("select {$fld} from oms_sell_invoice where sell_record_code = :sell_record_code", array('sell_record_code' => $sell_record_code));
        return $result;
    }

    //根据条件查询
    function get_by_page($filter) {
        //去掉空格
        foreach ($filter as $key => $v) {
            $filter[$key] = trim($v);
        }
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        $sql_values = array();
        $sql_join = '';
        if(isset($filter['nsrmc']) && $filter['nsrmc'] !== ''){
            $sql_join .= " LEFT JOIN js_shop AS js ON r3.shop_code = js.shop_code 
                           LEFT JOIN js_fapiao AS jf ON js.p_id = jf.id ";
        }
        $sql_main = "FROM oms_sell_record r3 inner join {$this->table} r1 on r3.sell_record_code = r1.sell_record_code {$sql_join} WHERE 1";
        if (isset($filter['sell_record_code']) && $filter['sell_record_code'] != '') {
            $filter['sell_record_code']=trim($filter['sell_record_code'],',');
            $filter['sell_record_code']=trim($filter['sell_record_code'],'，');
            if(strpos($filter['sell_record_code'],',')||strpos($filter['sell_record_code'],'，')){
                $filter['sell_record_code']=str_replace('，',',',$filter['sell_record_code']);
                $arr=explode(',',$filter['sell_record_code']);
                foreach($arr as $key=>$val){
                    if(!empty($val)){
                        $notnullarr[]=$val;
                    }
                }
                $filter['sell_record_code']=implode(',',$notnullarr);
                $sql_main .= " AND r1.sell_record_code in ({$filter['sell_record_code']})";
                //$sql_values[':sell_record_code'] = '('. $filter['sell_record_code'] .')';
            }else{
                $sql_main .= " AND r1.sell_record_code LIKE :sell_record_code";
                $sql_values[':sell_record_code'] = '%'. $filter['sell_record_code'] .'%';
            }
        }
         //发货日期
        if (isset($filter['record_time_start']) && $filter['record_time_start'] !== '') {
            $sql_main .= " AND r3.delivery_date >= :record_time_start ";
            $record_time_start = strtotime(date("Y-m-d", strtotime($filter['record_time_start'])));
            if ($record_time_start == strtotime($filter['record_time_start'])) {
                $sql_values[':record_time_start'] = $filter['record_time_start'];
            } else {
                $sql_values[':record_time_start'] = $filter['record_time_start'];
            }
        }
        if (isset($filter['record_time_end']) && $filter['record_time_end'] !== '') {
            $sql_main .= " AND r3.delivery_date <= :record_time_end ";
            $record_time_end = strtotime(date("Y-m-d", strtotime($filter['record_time_end'])));
            if ($record_time_end == strtotime($filter['record_time_end'])) {
                $sql_values[':record_time_end'] = $filter['record_time_end'] . ' 23:59:59';
            } else {
                $sql_values[':record_time_end'] = $filter['record_time_end'];
            };
        }
        
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] !== '') {
            $arr = explode(',',$filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $sql_main .= " AND r3.shop_code in ( " . $str . " ) ";
        }
        //发票类型
        if (isset($filter['invoice_type']) && $filter['invoice_type'] != '') {
            $sql_main .= " AND r1.invoice_type = :invoice_type";
            $sql_values[':invoice_type'] = $filter['invoice_type'];
        }
        //企业名称
          if (isset($filter['nsrmc']) && $filter['nsrmc'] !== '') {
            $sql_arr = explode(',',$filter['nsrmc']);
            $str = $this->arr_to_in_sql_value($sql_arr, 'nsrmc', $sql_values);
            $sql_main .= " AND jf.nsrmc in ($str) ";
        }
        //开票列表tab页面
        //结案按钮控制
        $invoice_status = 0;
        if (isset($filter['do_list_tab']) && $filter['do_list_tab'] != '') {
            //待开票
            if ($filter['do_list_tab'] == 'tabs_wait_invoice') {
                $invoice_status = 1;
                $sql_main .= " and r1.status =1  and r1.is_invoice=0";
            }
            //已开票
            if ($filter['do_list_tab'] == 'tabs_yi_invoice') {
                $invoice_status = 1;
                $sql_main .= " and r1.is_invoice=2 and r1.is_red<>1";
            }
            //开票失败
            if ($filter['do_list_tab'] == 'tabs_error_invoice') {
                $sql_main .= " and r1.is_success = 2";
            }
            //全部
//            if ($filter['do_list_tab'] == 'tabs_all') {
//                $sql_main.=';';
//            }
        }
        $sql_main .= " and r1.status <> 0 and r1.invoice_amount>0 and r1.is_finish_invoice=0";
        $select = 'r1.*,r3.delivery_date';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        //获取所有店铺名称
        $shop_list = $this->get_shop_list();
       foreach($data['data'] as $key => &$value){
			$value['invoice_type'] = $value['invoice_type'] == 1?'电子':'普通纸质';
                        $value['invoice_title'] =  !empty($value['invoice_title']) ?$value['invoice_title']:'个人';
                        $value['shop_name'] = $shop_list[$value['shop_code']];
                        $value['invoice_status'] = $invoice_status;
                        if($value['is_invoice']=='0'){
                            $value['status'] = '待开票';
                        }

                        if($value['is_invoice']==2 || $value['is_red']==2){
                            $value['status'] = '已开票';
                        }
                         if($value['is_invoice']==1 || $value['is_red']==1){
                            $value['status'] = '开票中';
                        }
                        if($value['is_invoice']!=2 || ($value['is_red'] ==2 && $value['is_invoice'] == 2)){
                            $value['invoice_xz'] = '正票';
                        }
                          
                        if($value['is_red']=='0' && $value['is_invoice'] ==2){
                            $value['invoice_xz'] = '红票';
                        }

                        if($value['is_success']==2){
                            $value['status'] = '开票失败';
                        }
                        if($value['is_invoice'] == 0 || $value['is_red'] == 2){ //可以开票
                            $value['invoiced_money'] = 0;
                        }else{
                            $value['invoiced_money'] = $value['invoice_amount'];
                        }

		}
        $ret_status = OP_SUCCESS;
	$ret_data = $data;
	return $this->format_ret($ret_status, $ret_data);
    }
    
    //结案
    function update_finish_status($invoice_id) {
        $ret = $this->update_exp('oms_sell_invoice', array('is_finish_invoice'=>1), array('invoice_id'=>$invoice_id));
        $sell_record_code = $this->db->get_row("SELECT sell_record_code FROM oms_sell_invoice WHERE invoice_id = $invoice_id");
        if ($ret['status']==1) {
            load_model('oms/SellRecordModel')->add_action($sell_record_code['sell_record_code'], "开票结案");
        }
        $message = $ret['status'] == 1 ? '操作成功' : "操作失败";
        return $this->format_ret($ret['status'], $sell_record_code['sell_record_code'], $message);
    }
    
    //获取开票弹窗信息详情
    function get_sell_invoice_detail($data) {
        //取出is_invoice判断开的是什么票
        $is_invoice = array_column($data, 'is_invoice','sell_record_code');
        //如果正票红票一起开，提示报错
        if(in_array('0', $is_invoice) && in_array('2', $is_invoice)){
            return $this->format_ret('-1','','正票红票不能一起开');
        }
        $shop_list = $this->get_shop_list();//获取所有店铺
        foreach ($data as $k => &$v) { 
             $v['shop_name'] = $shop_list[$v['shop_code']];
        }
        if(in_array('0', $is_invoice)){ //正票
            return $data;
        }
        //获取开票详情记录
        $sql_value = [];
        $new_invoice = array();
        $red_data = array();
        $record_code_arr = array_column($data,'sell_record_code');
        $str = $this->arr_to_in_sql_value($record_code_arr, 'sell_record_code', $sql_value);
        $sql = "SELECT sell_record_code,fp_dm,invoice_no FROM oms_sell_invoice_record WHERE is_red = 0 AND `status` = 1 AND sell_record_code in ($str)";
        $invoice_data = $this->db->get_all($sql,$sql_value);
        foreach ($invoice_data as $value) {
            $new_invoice[$value['sell_record_code']] = $value;
        }
        foreach ($data as $key => &$val) {
            if(array_key_exists($val['sell_record_code'], $new_invoice)){
                $red_data[] = array_merge($val,$new_invoice[$val['sell_record_code']]);
            }
        }
        return $red_data;
    }
    /**
     * 获取所有店铺
     * @return type
     */
    function get_shop_list() {
        $sql = "SELECT shop_code,shop_name FROM `base_shop`";
        $data = $this->db->get_all($sql);
        $arr = array_column($data, 'shop_name','shop_code');
        return $arr;
    }
    
    //获取订单单据号
    function opt_sell_record($ids){
        $str = implode(',', $ids);
        $sql = "select sell_record_code from oms_sell_invoice where invoice_id in ({$str})";
        $data = $this->db->get_all($sql);
          if (empty($data)) {
            return array('status' => '-1', 'data' => '', 'message' => '开票信息不存在');
        }
        $List = array();
        foreach ($data as $row) {
            $List[] = $row['sell_record_code'];
        }
   
        return array('status' => '1', 'data' => $List, 'message' => '');
  
    }
    
       function invoice_again($sell_record_code){
            $this->begin_trans();

            $up_invoice = array(
                'status' => 1,
                'is_invoice' => 0,
                'is_red' => 0,
                'is_success' => 0,
                'invoice_time' => '',
            );
           $ret  = $this->update_exp('oms_sell_invoice', $up_invoice, array('sell_record_code' => $sell_record_code));
           if($ret['status']<0){
               $this->rollback();
                return $this->format_ret(-1, '', '重新开票失败');
           }
           $this->commit();
           return $this->format_ret(1);
    }
    /**
     * 获取发票表中的信息
     */
    function invoice_record($sell_record_code){
        $sql_value = [];
        $arr = explode(',',$sell_record_code);
        $str = $this->arr_to_in_sql_value($arr, 'sell_record_code', $sql_value);
        $sql = "select * from oms_sell_invoice where sell_record_code in ($str)";
        $result = $this->db->get_all($sql,$sql_value);
        return $result;
    }
    
    /**
     * 修改开票金额
     * 
     */
    function edit_pay_money($sell_record_code,$pay_money) {
        if(empty($sell_record_code) || empty($pay_money)){
            return $this->format_ret(-1, '', '请重新选择修改');
        }
        $sql = "SELECT * FROM oms_sell_invoice WHERE sell_record_code = :sell_record_code";
        $invoice_data = $this->db->getRow($sql, array('sell_record_code' => $sell_record_code));
        if(empty($invoice_data)){
           return $this->format_ret(-1, '', '修改的发票数据不存在'); 
        }
        if(!($invoice_data['is_invoice'] == 0 || $invoice_data['is_red'] == 2)){
            return $this->format_ret(-1, '', '该订单:'.$sell_record_code.'已开票或正在开票中'); 
        }
        $this->begin_trans();
        $param['invoice_amount'] = $pay_money;
        //更新发票表
        $ret = $this->update_exp('oms_sell_invoice', $param, array('sell_record_code' => $sell_record_code));
        if($ret['status'] < 0){
            $this->rollback();
            return $ret;
        }
        //更新订单表
        $data['invoice_money'] = $pay_money;
        $ret = $this->update_exp('oms_sell_record', $data, array('sell_record_code' => $sell_record_code));
        if($ret['status'] < 0){
            $this->rollback();
            return $ret;
        }
        $log_msg = '在开票列表中修改开票金额，由原来的 '.$invoice_data['invoice_amount'].' 元修改为：' . $pay_money . '元<br>';
        load_model('oms/SellRecordModel')->add_action($sell_record_code, "修改发票信息", $log_msg);
        $this->commit();
        return $ret;
    }
    
    /**
     * 订单开票信息查询接口
     * @param type $param
     * @return type
     */
     public function api_sell_invoice_get($param){
        //可选字段
        $key_option = array(
            's' => array(
                'page', 'page_size', 'sell_record_code','deal_code', 'start_delivery_time', 'end_delivery_time', 'sale_channel_code', 'shop_code', 'nsrmc', 'invoice_sell_status','invoice_nature'
            )
        );
        $arr_option = array();
        //提取可选字段中已赋值数据
        $ret_option = valid_assign_array($param, $key_option, $arr_option);
        //清空无用数据
        unset($param);
        //检查单页数据条数是否超限
        $check = $this->check_params($arr_option);
        if($check['status'] < 0){
            return $check;
        }
         $select = '
            sr.`sell_record_code`, sr.`deal_code_list`,sr.`sale_channel_code`, sr.`shop_code`,jf.`nsrmc`,si.`invoice_type`,
            si.`is_invoice`,si.`is_red`,sr.`paid_money`,sr.`point_fee`,sr.`alipay_point_fee`,sr.`coupon_fee`,sr.`invoice_money`,
            sr.`payable_money`,sr.`goods_money`,sr.`express_money`,sr.`delivery_money`,sr.`other_amount`,sr.`invoice_number`  
            ';
         //买家实付金额
         //$response['record']['paid_money']-$response['record']['point_fee']-$response['record']['alipay_point_fee']-$response['record']['coupon_fee'])
         //开票金额
         //round($record_info['paid_money']-$record_info['point_fee']-$record_info['alipay_point_fee']-$record_info['coupon_fee'],2);
         //查询SQL
         $sql_join = " INNER JOIN oms_sell_invoice AS si ON sr.sell_record_code = si.sell_record_code 
                        LEFT JOIN js_shop AS js ON si.shop_code = js.shop_code 
                        LEFT JOIN js_fapiao AS jf ON js.p_id = jf.id 
                    ";
        $sql_main = " FROM oms_sell_record AS sr {$sql_join} WHERE 1 and sr.invoice_status <> 0 ";
        
        //绑定数据
        $sql_values = array();
        if (isset($arr_option['sell_record_code']) && !empty($arr_option['sell_record_code'])) {
            $arr_option = array('sell_record_code' => $arr_option['sell_record_code']);
            $sql_main .= " AND sr.sell_record_code=:sell_record_code";
            $sql_values[":sell_record_code"] = $arr_option['sell_record_code'];
        } else if (isset($arr_option['deal_code']) && !empty($arr_option['deal_code'])) {
            $sql_detail = 'SELECT sell_record_code FROM oms_sell_record_detail WHERE deal_code=:deal_code';
            $sell_code_arr = $this->db->get_all_col($sql_detail,array(':deal_code'=>$arr_option['deal_code']));
            if(empty($sell_code_arr)){
                return $this->format_ret(-10004, '', '平台交易号不存在'); 
            } else {
                $sell_code_str = $this->arr_to_in_sql_value($sell_code_arr, 'sell_record_code', $sql_values);
                $sql_main .= " AND sr.sell_record_code IN({$sell_code_str})";
            }
        } else {
            unset($arr_option['sell_record_code']);
            unset($arr_option['deal_code']);
            $this->create_sql_where($arr_option, $sql_values, $sql_main);
        }
        $ret = $this->get_page_from_sql($arr_option, $sql_main, $sql_values, $select);
        $data = $ret['data'];
        if (empty($data)) {
            return $this->format_ret(-10002, (object) array(), '数据不存在');//数据不存在
        }
         filter_fk_name($data, array('shop_code|shop', 'sale_channel_code|source'));
         foreach ($data as $key => &$value) {
            if($value['invoice_money']==0){//开票金额
                $value['invoice_money'] = round($value['paid_money']-$value['point_fee']-$value['alipay_point_fee']-$value['coupon_fee'],2);
            }else{
                $value['invoice_money'] = $value['invoice_money'];
            }
             $value['real_pay_money'] = sprintf("%.2f", $value['paid_money']-$value['point_fee']-$value['alipay_point_fee']-$value['coupon_fee']);//买家实付金额
             //$value['discount_fee'] = $value['goods_money'] + $value['express_money'] + $value['delivery_money'] - $value['payable_money'];//平台优惠金额
             $value['discount_fee'] = $this->get_discount_fee($value['deal_code_list'],$value['sale_channel_code']);//获取平台优惠总金额
             if($value['is_invoice'] == 0){
                 $value['invoice_sell_status'] = 0;//未开票
             }
             if(($value['is_invoice'] == 2 && $value['is_red'] == 0) ||  ($value['is_invoice'] == 2 && $value['is_red'] == 2)){
                 $value['invoice_sell_status'] = 2;//已开票
             }
             if($value['is_invoice'] == 1 || ($value['is_invoice'] == 2 && $value['is_red'] == 1)){
                 $value['invoice_sell_status'] = 1;//开票中
             }
             //不开放字段
            $del_key = array(
                'paid_money','is_invoice','is_red','paid_money','point_fee','alipay_point_fee','coupon_fee','goods_money','express_money','delivery_money',
                'shop_code_code','sale_channel_code_code'
            );
            foreach ($del_key as $v) {
                 if (array_key_exists($v, $value)) {
                    unset($value[$v]);
                 }
              }
         }
         
           
        $filter = get_array_vars($ret['filter'], array('page', 'page_size', 'page_count', 'record_count'));

        $revert_data = array(
            'filter' => $filter,
            'data' => $data,
        );
        return $this->format_ret(1, $revert_data);
    }
    
    /**
     * 检验数据是否存在
     * @param type $data
     * @return type
     */
    private function check_params($data) {
       if (isset($data['page_size']) && $data['page_size'] > 100) {
            return $this->format_ret('-1', array('page_size' => $data['page_size']), API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE);
        }
        if(isset($data['sale_channel_code']) && !empty($data['sale_channel_code'])){
            $res = $this->check('sale_channel_code','base_sale_channel',$data['sale_channel_code']);
            if(empty($res)){
                return $this->format_ret(-10003, (object) array(), '平台不存在');
            }
        }
        if(isset($data['shop_code']) && !empty($data['shop_code'])){
            $res = $this->check('shop_code','base_shop',$data['shop_code']);
            if(empty($res)){
                return $this->format_ret(-10003, (object) array(), '店铺不存在');
            }
        }
        if(isset($data['nsrmc']) && !empty($data['nsrmc'])){
            $res = $this->check('nsrmc','js_fapiao',$data['nsrmc']);
            if(empty($res)){
                return $this->format_ret(-10003, (object) array(), '企业名称不存在');
            }
        }
       if(isset($data['invoice_sell_status']) && !empty($data['invoice_sell_status'])){
         $stauts_arr = [0,1,2];
            if(!in_array($data['invoice_sell_status'], $stauts_arr)){
                return $this->format_ret(-10005, (object) array(), '开票状态参数传入错误');
            }
        }
       if(isset($data['invoice_nature']) && !empty($data['invoice_nature'])){
         $nature_arr = [0,1];
            if(!in_array($data['invoice_nature'], $nature_arr)){
                return $this->format_ret(-10005, (object) array(), '开票性质参数传入错误');
            }
        }
        return $this->format_ret(1);
    }
    
    /**
     * 组件SQL查询语句条件
     * @param type $arr_deal
     * @param type $sql_values
     * @param type $sql_main
     */
    private function create_sql_where($arr_deal, &$sql_values, &$sql_main) {
        //时间字段映射关系
        $time_fld = array(
            'start_delivery_time' => 'delivery_time',
            'end_delivery_time' => 'delivery_time',
        );
        $start_time = date("Y-m-d", strtotime("-2 month"));
        $end_time = date("Y-m-d");
        foreach ($arr_deal as $key => $val) {
            if ($key == 'page' || $key == 'page_size') {
                continue;
            }
            if ($key == 'nsrmc') {
                $sql_main .=" AND jf.{$key}=:{$key}";
                $sql_values[":{$key}"] = $val;
                continue;
            }
            if($key == 'invoice_sell_status'){//开票状态
                $this->get_invoice_stutus($val,$sql_main, $sql_values);
                continue;
            }
            if($key =='invoice_nature'){//开票性质
                $this->get_invoice_nature($val,$sql_main, $sql_values);
                continue;
            }
            if (!array_key_exists($key, $time_fld)) {
                $sql_main .= " AND sr.{$key}=:{$key}";
                $sql_values[":{$key}"] = $val;
                continue;
            }
            if (strpos($key, 'start_') === 0) {
                $sql_main .= " AND sr.{$time_fld[$key]}>=:{$key}";
                $sql_values[":{$key}"] = $val;
                continue;
            }
            if (strpos($key, 'end_') === 0) {
                $sql_main .= " AND sr.{$time_fld[$key]}<=:{$key}";
                $sql_values[":{$key}"] = $val;
                continue;
            }
        }
        if (!isset($arr_deal['start_delivery_time'])) {
                $sql_main .= " AND sr.delivery_time >= :start_time";
                $sql_values[':start_time'] = $start_time;
            }
            if (!isset($arr_deal['end_delivery_time'])) {
                $sql_main .= " AND sr.delivery_time <= :end_time";
                $sql_values[':end_time'] = $end_time;
            }
    }
    
    /**
     * 条件查询
     * @param type $field
     * @param type $tb
     * @param type $val
     * @return type
     */
    private function check($field,$tb,$val){
         $sql = "SELECT {$field} from {$tb} where {$field} = :{$field}";
         $res = $this->db->getAll($sql, array(":{$field}" => $val));
         return $res;
     }
     
     /**
      * 过滤开票状态条件
      * @param type $field_val
      * @param type $sql_main
      * @param type $sql_values
      * @return type
      */
     private function get_invoice_stutus($field_val,&$sql_main, &$sql_values) {
         if($field_val == 0){ //未开票
             $sql_main .= " AND (si.is_invoice = 0) ";
             
         }
         if($field_val == 1){ //开票中
             $sql_main .= " AND (si.is_invoice = 1 or (si.is_invoice = 2 and si.is_red = 1)) ";
     
         }
         if($field_val == 2){//已开票
             $sql_main .= " AND ((si.is_invoice = 2 and si.is_red = 0) or (si.is_invoice = 2 and si.is_red = 2)) ";

         }
     }
     /**
      * 开票性质筛选
      * @param type $field_val
      * @param type $sql_main
      * @param type $sql_values
      * @return type
      */
     private function get_invoice_nature($field_val,&$sql_main, &$sql_values) {
         if($field_val == 0){ //正票
             $sql_main .= "  AND (si.is_invoice = 0 OR si.is_invoice = 1 OR (si.is_invoice = 2  and si.is_red = 2)) ";
         }
         if($field_val == 1){ //红票
              $sql_main .= " AND (si.is_invoice = 2 and si.is_red <> 2 )";
         }
     }
     
     /**
      * 获取平台优惠金额 
      * @param type $deal_code_list
      * @param type $sale_channel_code
      * @return type
      */
     private function get_discount_fee($deal_code_list,$sale_channel_code) {
         $type = $sale_channel_code;
         if($type == 'taobao'){ //淘宝
             $sql = "SELECT tb.coupon_fee,tb.alipay_point,tb.promotion_details FROM api_taobao_trade tb INNER JOIN oms_sell_record sr on tb.tid = sr.deal_code_list WHERE sr.invoice_status = 1 AND sr.sale_channel_code = :sale_channel_code AND sr.deal_code_list = :deal_code_list";
             $list = $this->db->get_row($sql, array('deal_code_list' => $deal_code_list,'sale_channel_code' => $sale_channel_code));
             if(empty($list)){
                 return $tb_discount_fee = 0;
             }
            $tabao_discount_fee = load_model('oms/SellReportModel')->get_tabao_discount_fee($list['promotion_details'],'天猫购物券');
            $list['coupon_fee'] = $list['coupon_fee'] /100;//红包
            $list['alipay_point'] = $list['alipay_point'] /100; //集分宝
            $tb_discount_fee = $list['coupon_fee'] + $list['alipay_point'] + $tabao_discount_fee;
            return $tb_discount_fee;
         }elseif ($type == 'jingdong'){ //京东
             $sql = "select r1.* FROM api_jingdong_trade AS r1 INNER JOIN oms_sell_record AS r2 ON r1.order_id=r2.deal_code_list WHERE 1 AND r2.invoice_status = 1 AND r2.sale_channel_code= :sale_channel_code AND r2.deal_code_list = :deal_code_list";
             $jd_list = $this->db->get_row($sql, array('deal_code_list' => $deal_code_list,'sale_channel_code' => $sale_channel_code));
             if(empty($jd_list)){
                 return $jd_discount_fee = 0;
             }
             $jd_data = array();
             //获取京东券，京豆
            $jingdong_discount = load_model('oms/SellReportModel')->get_jingdong_discount($jd_list['order_id']);
            $jd_data['jingdong_coupon'] = isset($jingdong_discount['jingdong_coupon']) ? $jingdong_discount['jingdong_coupon'] : 0;
            $jd_data['jingdong_bean'] = isset($jingdong_discount['jingdong_bean']) ? $jingdong_discount['jingdong_bean'] : 0;
            $jd_data['pay_courtesy'] = number_format(($jd_list['order_seller_price'] - $jd_list['order_payment']- $jd_data['jingdong_coupon']- $jd_data['jingdong_bean']- $jd_list['balance_used']), 2, '.', '');
            $jd_discount_fee = $jd_list['balance_used'] + $jd_data['jingdong_coupon'] + $jd_data['jingdong_bean'] + $jd_data['pay_courtesy'];
            return $jd_discount_fee;
          }
     }
     
     /**
      * 修改订单表中其他优惠金额
      * @param type $sell_record_code
      * @param type $other_amount
      */
     function edit_other_amount($sell_record_code,$other_amount) {
         $sql = "SELECT other_amount FROM `oms_sell_record` where sell_record_code = :sell_record_code";
         $sell_ret = $this->db->getRow($sql, array('sell_record_code' => $sell_record_code));
         if(empty($sell_ret['other_amount'])){
             $sell_ret['other_amount'] = '0.00';
         }
         $up_param = array(
            'other_amount' => $other_amount,
         );
         $log = '订单其他优惠金额：由 '.$sell_ret['other_amount'].' 元修改为：' . $other_amount . '元';
         $this->begin_trans();
         $ret = $this->update_exp('oms_sell_record',$up_param, array('sell_record_code' => $sell_record_code));
         if($ret['status'] < 0) {
            $this->rollback();
            return $ret;
        }
        load_model('oms/SellRecordModel')->add_action($sell_record_code, "修改其他优惠金额", $log);
        $this->commit();
        return $ret;
     }
}
