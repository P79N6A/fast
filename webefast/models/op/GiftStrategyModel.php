<?php

require_model('tb/TbModel');

class GiftStrategyModel extends TbModel {

    var $sell_record;
    var $sell_record_detail;
    var $strategy_data;

    function get_table() {
        return 'op_gift_strategy';
    }

    function get_by_page($filter) {
        //$sql_join = "";
        /*
          $sql_main = "FROM {$this->table} rl
          LEFT JOIN op_gift_strategy_detail r2 on rl.record_code = r2.record_code
          LEFT JOIN base_goods r3 on r3.goods_code = r2.goods_code
          LEFT JOIN goods_barcode r4 on r4.sku = r2.sku
          WHERE 1";
         */
        $sql_main = "FROM {$this->table} rl
    	WHERE 1";
        $sql_values = array();
        //策略名称 
        if (isset($filter['strategy_name']) && $filter['strategy_name'] != '') {
            $sql_main .= " AND (rl.strategy_name LIKE :strategy_name )";
            $sql_values[':strategy_name'] = $filter['strategy_name'] . '%';
        }

        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] <> '') {
            $arr = explode(',', $filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $sql_main .= " AND rl.shop_code in ({$str}) ";
        }
        // 启用状态
        if (isset($filter['status']) && $filter['status'] != '') {
            $arr = explode(',', $filter['status']);
            $str = $this->arr_to_in_sql_value($arr, 'status', $sql_values);
            $sql_main .= " AND rl.status in ({$str}) ";
        }

        //业务日期
        if (isset($filter['start_time']) && $filter['start_time'] != '') {
            $sql_main .= " AND (rl.start_time >= :start_time )";
            $sql_values[':start_time'] = strtotime($filter['start_time']);
        }
        if (isset($filter['end_time']) && $filter['end_time'] != '') {
            $sql_main .= " AND (rl.end_time <= :end_time )";
            $sql_values[':end_time'] = strtotime($filter['end_time']);
        }
        //下单日期
        if (isset($filter['order_time_start']) && $filter['order_time_start'] != '') {
            $sql_main .= " AND (rl.order_time >= :order_time_start )";
            $sql_values[':order_time_start'] = $filter['order_time_start'] . " 00:00:00";
        }
        if (isset($filter['order_time_end']) && $filter['order_time_end'] != '') {
            $sql_main .= " AND (rl.order_time <= :order_time_end )";
            $sql_values[':order_time_end'] = $filter['order_time_end'] . " 23:59:59";
        }

        //$select = 'rl.*,r2.goods_name,r2.goods_name,r2.weight';
        $select = 'rl.*';
        $sql_main .= " order by op_gift_strategy_id desc";
        //$sql_main .= " group by rl.record_code order by record_time desc,record_code desc";
        // echo $sql_main;
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['start_time'] = date('Y-m-d H:i', $value['start_time']);
            $data['data'][$key]['end_time'] = date('Y-m-d H:i', $value['end_time']);
        }

        filter_fk_name($data['data'], array('shop_code|shop'));
        // print_r($data);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    //添加新纪录
    function insert(array $data) {
        $status = $this->valid($data);
        if ($status < 1) {
            return $this->format_ret('-1', '', $status);
        }

        $ret = $this->is_exists($data['strategy_code']);

        if (!empty($ret['data']))
            return $this->format_ret('-1', '', '该赠品策略代码已存在');
        //        $stock_adjus['is_add_time'] = date('Y-m-d H:i:s');

        return parent::insert($data);
    }

    function check_repeat($strategy_code) {
        $sql = "select strategy_name from op_gift_strategy where status=1  ";
        $ret = $this->get_by_code($strategy_code);
        $strategy_data = &$ret['data'];

        $sql.= " AND shop_code=:shop_code ";
        $sql_values[':shop_code'] = $strategy_data['shop_code'];

        $sql.= " AND ( (start_time<:start_time AND end_time>:start_time)  ";
        $sql.= "   OR  ( start_time<:end_time AND end_time>:end_time ) ) ";
        $sql_values[':start_time'] = $strategy_data['start_time'];
        $sql_values[':end_time'] = $strategy_data['end_time'];

        $data = $this->db->get_all($sql, $sql_values);
        $ret_data = array();
        $ret_status = -1;
        if (!empty($data)) {
            $ret_status = 1;
            foreach ($data as $val) {
                $ret_data[] = $val['strategy_name'];
            }
        }
        return $this->format_ret($ret_status, implode(",", $ret_data));
    }

    /*
     * 修改纪录
     */

    function update($data, $op_gift_strategy_id) {
        $status = $this->valid($data, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret = $this->get_row(array('op_gift_strategy_id' => $op_gift_strategy_id));
        if ($data['strategy_code'] != $ret['data']['strategy_code']) {
            $ret1 = $this->is_exists($data['strategy_code'], 'strategy_code');
            if (!empty($ret1['data']))
                return $this->format_ret('code已经被使用过');
        }

        if (isset($data['strategy_name']) && $data['strategy_name'] != $ret['data']['strategy_name']) {
            $ret = $this->is_exists($data['strategy_name'], 'strategy_name');
            if (!empty($ret['data']))
                return $this->format_ret('code已经被使用过');
        }
        $ret = parent::update($data, array('op_gift_strategy_id' => $op_gift_strategy_id));
        return $ret;
    }

    public function is_exists($value, $field_name = 'strategy_code') {

        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    private function valid($data, $is_edit = false) {

        if (!$is_edit && (!isset($data['strategy_code']) || !valid_input($data['strategy_code'], 'required')))
            return 'code不能为空';
        return 1;
    }

    function get_by_id($id) {
        $data = $this->get_row(array('op_gift_strategy_id' => $id));
        return $data;
    }

    /**
     * 生成单据号
     */
    function create_fast_bill_sn() {
        $sql = "select  op_gift_strategy_id from {$this->table}   order by op_gift_strategy_id desc limit 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $djh = intval($data[0]['op_gift_strategy_id']) + 1;
        } else {
            $djh = 1;
        }
        require_lib('comm_util', true);
        $jdh = "ZPCL" . date("Ymd") . add_zero($djh, 3);
        return $jdh;
    }

    function update_active($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        $action_name = ($active == 1) ? '策略启用' : '策略停用';
        $ret_strategy = $this->get_by_id($id);
        $data = array(
            'strategy_code' => $ret_strategy['data']['strategy_code'],
            'action_name' => $action_name,
        );
        load_model('op/GiftStrategyLogModel')->insert($data);


        $ret = parent :: update(array('status' => $active), array('op_gift_strategy_id' => $id));
        return $ret;
    }

    function get_by_code($code) {
        $data = $this->get_row(array('strategy_code' => $code));
        filter_fk_name($data['data'], array('shop_code|shop'));
        return $data;
    }

    function update_check($active, $field, $data) {
        $id = $data['id'];
        //查看是否可以审核
        $check_strategy_code = $this->check_strategy_code($data['strategy_code']);
        if($check_strategy_code['status'] < 0){
            return $check_strategy_code;
        }
        $ret = parent:: update(array($field => $active), array('op_gift_strategy_id' => $id));
        $ret_strategy = $this->get_by_id($id);
        $data = array(
            'strategy_code' => $ret_strategy['data']['strategy_code'],
            'action_name' => '审核',
        );
        load_model('op/GiftStrategyLogModel')->insert($data);
        return $ret;
    }
    
    //赠品策略规格启用增加校验逻辑
    //1.排名送校验指定时间、排名范围、赠品
    //2.满送校验金额范围设置、赠品
    //3买送校验数量范围设置、赠品、活动商品
    function check_strategy_code($strategy_code){
        if(empty($strategy_code)){
            return $this->format_ret(-1,'','策略代码不存在');
        }
        $sql = "select strategy_code,type,op_gift_strategy_detail_id,ranking_hour from op_gift_strategy_detail where strategy_code = :strategy_code";
        $strategy_code_detail = $this->db->get_all($sql,array(':strategy_code' => $strategy_code));
        if(empty($strategy_code_detail)){
            return $this->format_ret(-1,'','赠品策略明细不存在');
        }
//        $status = 1;
//        $message = '';
//        foreach ($strategy_code_detail as $detail){
//            if($detail['type'] == 0){
//                $mansong_money = $this->db->get_all("select * from op_gift_strategy_range where op_gift_strategy_detail_id = :op_gift_strategy_detail_id",array(":op_gift_strategy_detail_id" => $detail['op_gift_strategy_detail_id']));
//                if(empty($mansong_money)){
//                    $status = -1;
//                    $message = "满送规则金额范围不能为空";
//                    break;
//                }
//                $mansong_goods = $this->db->get_all("select * from op_gift_strategy_goods where is_gift = 1 and op_gift_strategy_detail_id = :op_gift_strategy_detail_id",array(":op_gift_strategy_detail_id" => $detail['op_gift_strategy_detail_id']));
//                if(empty($mansong_goods)){
//                    $status = -1;
//                    $message = "满送规则赠品不能为空";
//                    break;
//                }
//            }
//            if($detail['type'] == 1){
//                $paiming_goods = $this->db->get_all("select * from op_gift_strategy_goods where is_gift = 1 and op_gift_strategy_detail_id = :op_gift_strategy_detail_id",array(":op_gift_strategy_detail_id" => $detail['op_gift_strategy_detail_id']));
//                if(empty($paiming_goods)){
//                    $status = -1;
//                    $message = "买送规则赠品不能为空";
//                    break;
//                }
//            }
//            if($detail['type'] == 2){
//                if(empty($detail['ranking_hour'])){
//                    $status = -1;
//                    $message = "排名送没有指定时间";
//                    break; 
//                }
//                $paiming_rank = $this->db->get_all("select * from op_gift_strategy_range where op_gift_strategy_detail_id = :op_gift_strategy_detail_id",array(":op_gift_strategy_detail_id" => $detail['op_gift_strategy_detail_id']));
//                if(empty($paiming_rank)){
//                    $status = -1;
//                    $message = "排名送没有指定范围";
//                    break; 
//                }
//                $paiming_goods = $this->db->get_all("select * from op_gift_strategy_goods where is_gift = 1 and op_gift_strategy_detail_id = :op_gift_strategy_detail_id",array(":op_gift_strategy_detail_id" => $detail['op_gift_strategy_detail_id']));
//                if(empty($paiming_goods)){
//                    $status = -1;
//                    $message = "排名送赠品不能为空";
//                    break;
//                }
//            }
//        }
        return $this->format_ret($status,'',$message);
    }

    /**
     * @todo 赠品策略复制
     */
    function opt_copy($strategy_code) {
        $new_strategy_code = $this->create_fast_bill_sn(); //生成新策略代码
        $new_record = $this->create_new_strategy($strategy_code, $new_strategy_code);
        if (empty($new_record)) {
            return $new_record;
        }

        $this->begin_trans();
        try {
            $ret_record = $this->insert($new_record);
            $new_shop = $this->create_new_shop($strategy_code, $new_strategy_code);
            if (!empty($new_shop)) {
                $ret_shop = load_model('op/GiftStrategyShopModel')->insert($new_shop);
            }
            $ret_detail = $this->create_new_detail($strategy_code, $new_strategy_code);
            $flg = parent::update_exp('op_gift_strategy_goods',array('send_gifts_num'=> 0),array('strategy_code'=>$new_strategy_code));
            if (!empty($ret_record)) {
                $log_data['strategy_code'] = $new_strategy_code;
                $log_data['action_name'] = "从{$strategy_code}策略复制生成";
                $ret_log = load_model('op/GiftStrategyLogModel')->insert($log_data);
            }
            $this->commit();
            return $this->format_ret(1, $ret_record['data']);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    /**
     * @todo 根据原赠品策略主单据单据生成新单据信息:op_gift_strategy
     * @param string $strategy_code 策略代码
     * @param string $new_strategy_code 新策略代码
     */
    function create_new_strategy($strategy_code, $new_strategy_code) {
        $record = $this->get_by_code($strategy_code);
        if (!empty($record['data'])) {
            //设置新赠品策略主单据信息
            $record = $record['data'];
            $record['strategy_code'] = $new_strategy_code;
            $record['status'] = 0;
            $record['is_check'] = 0;
            unset($record['op_gift_strategy_id']);
        }
        return $record;
    }

    /**
     * @todo 生成活动店铺数据：op_gift_strategy_shop
     * @param string $strategy_code 策略代码
     * @param string $new_strategy_code 新策略代码
     */
    function create_new_shop($strategy_code, $new_strategy_code) {
        $shop = load_model('op/GiftStrategyShopModel')->get_shop_by_code($strategy_code);
        //设置新策略详情信息
        if (!empty($shop['data'])) {
            $shop = $shop['data'];
            foreach ($shop as $key => $val) {
                $shop[$key]['strategy_code'] = $new_strategy_code;
                unset($shop[$key]['id']);
            }
        } else {
            $shop = array();
        }
        return $shop;
    }

    /**
     * @todo 根据原赠品策略明细生成新单据明细:op_gift_strategy_detail
     * @param string $strategy_code 策略代码
     * @param string $new_strategy_code 新策略代码
     */
    function create_new_detail($strategy_code, $new_strategy_code) {
        $detail = load_model('op/GiftStrategyDetailModel')->get_detail_by_code($strategy_code);
        //设置新策略详情信息
        if (empty($detail['data'])) {
            return $detail;
        }
        $new_detail = $detail['data'];
        $new_customer_arr = array();
        $new_active_goods = array();
        foreach ($new_detail as $key => $val) {
            $new_detail[$key]['strategy_code'] = $new_strategy_code;
            unset($new_detail[$key]['op_gift_strategy_detail_id']);
            $ret_detail = load_model('op/GiftStrategyDetailModel')->insert($new_detail[$key]);

            $new_customer = $this->create_new_customer($new_strategy_code, $detail['data'][$key]['op_gift_strategy_detail_id'], $ret_detail['data']);
            if (!empty($new_customer)) {
                $new_customer_arr = array_merge($new_customer_arr, $new_customer);
            }

            $ret = $this->create_new_range($new_strategy_code, $detail['data'][$key]['op_gift_strategy_detail_id'], $ret_detail['data']);

            $active_goods = $this->create_new_active_goods($new_strategy_code, $detail['data'][$key]['op_gift_strategy_detail_id'], $ret_detail['data']);
            if (!empty($active_goods)) {
                $new_active_goods = array_merge($new_active_goods, $active_goods);
            }
        }
        $ret = load_model('op/GiftStrategyCustomerModel')->insert($new_customer_arr);
        $ret = load_model('op/GiftStrategyGoodsModel')->insert($new_active_goods);

        return $ret;
    }

    /**
     * @todo 生成新赠品策略固定买家信息:op_gift_strategy_customer
     * @param string $new_strategy_code 新策略代码
     * @param int $detail_id 明细ID
     * @param int $new_detail_id 新明细ID
     */
    function create_new_customer($new_strategy_code, $detail_id, $new_detail_id) {
        $customer = load_model('op/GiftStrategyCustomerModel')->get_customer_by_detail_id($detail_id);
        if (!empty($customer['data'])) {
            $customer = $customer['data'];
            foreach ($customer as $key => $val) {
                $customer[$key]['strategy_code'] = $new_strategy_code;
                $customer[$key]['op_gift_strategy_detail_id'] = $new_detail_id;
                unset($customer[$key]['op_gift_strategy_customer_id']);
            }
        } else {
            $customer = array();
        }
        return $customer;
    }

    /**
     * @todo 生成新赠品规则数据:op_gift_strategy_range
     * @param string $new_strategy_code 新策略代码
     * @param int $detail_id 明细ID
     * @param int $new_detail_id 新明细ID
     */
    function create_new_range($new_strategy_code, $detail_id, $new_detail_id) {
        $range = load_model('op/GiftStrategyRangeModel')->get_by_detail_id($detail_id);
        $new_goods_arr = array();
        if (!empty($range['data'])) {
            $new_range = $range['data'];
            foreach ($new_range as $key => $val) {
                $new_range[$key]['op_gift_strategy_detail_id'] = $new_detail_id;
                unset($new_range[$key]['id']);
                $ret_range = load_model('op/GiftStrategyRangeModel')->insert($new_range[$key]);

                $new_goods = $this->create_new_strategy_goods($new_strategy_code, $detail_id, $range['data'][$key]['id'], $new_detail_id, $ret_range['data']);
                if (!empty($new_goods)) {
                    $new_goods_arr = array_merge($new_goods_arr, $new_goods);
                }
            }
        }
        $ret_goods = load_model('op/GiftStrategyGoodsModel')->insert($new_goods_arr);

        return $ret_goods;
    }

    /**
     * @todo 生成赠品数据:op_gift_strategy_goods
     * @param string $new_strategy_code 新策略代码
     * @param int $detail_id 明细ID
     * @param int $new_detail_id 新明细ID
     * @param int $range_id 规则ID
     * @param int $new_range_id 新规则ID
     */
    function create_new_strategy_goods($new_strategy_code, $detail_id, $range_id, $new_detail_id, $new_range_id) {
        $strategy_goods = load_model('op/GiftStrategyGoodsModel')->get_goods_by_detail_range_id($detail_id, $range_id);
        if (!empty($strategy_goods['data'])) {
            $strategy_goods = $strategy_goods['data'];
            foreach ($strategy_goods as $key => $val) {
                $strategy_goods[$key]['op_gift_strategy_detail_id'] = $new_detail_id;
                $strategy_goods[$key]['strategy_code'] = $new_strategy_code;
                if ($val['op_gift_strategy_range_id'] != 0) {
                    $strategy_goods[$key]['op_gift_strategy_range_id'] = $new_range_id;
                }
                unset($strategy_goods[$key]['op_gift_strategy_goods_id']);
            }
        } else {
            $strategy_goods = array();
        }
        return $strategy_goods;
    }

    /**
     * @todo 生成活动商品数据:op_gift_strategy_goods
     * @param string $new_strategy_code 新策略代码
     * @param int $detail_id 明细ID
     * @param int $new_detail_id 新明细ID
     */
    function create_new_active_goods($new_strategy_code, $detail_id, $new_detail_id) {
        $active_goods = load_model('op/GiftStrategyGoodsModel')->get_goods_by_detail_range_id($detail_id, 0);
        if (!empty($active_goods['data'])) {
            $active_goods = $active_goods['data'];
            foreach ($active_goods as $key => $val) {
                $active_goods[$key]['op_gift_strategy_detail_id'] = $new_detail_id;
                $active_goods[$key]['strategy_code'] = $new_strategy_code;
                unset($active_goods[$key]['op_gift_strategy_goods_id']);
            }
        } else {
            $active_goods = array();
        }
        return $active_goods;
    }
    
    function update_end_time($id,$newtime,$endtime){
        if(!empty($newtime)){
            $time_end = strtotime($endtime);
            $new_time_end = strtotime($newtime);
            if($new_time_end > $time_end ){
                $ret = parent::update(array('end_time' => $new_time_end),array('op_gift_strategy_id' => $id));
                $user_code =  CTX()->get_session('user_code');
                $user_id =  CTX()->get_session('user_id');
                $add_time = date('Y-m-d H:i:s');
                $strategy_code = $this->db->get_value("select strategy_code from op_gift_strategy where op_gift_strategy_id= :id",array(':id' => $id));
                $sql_log = "insert into op_gift_strategy_log(strategy_code,user_id,user_code,action_name,add_time) values('$strategy_code','$user_id','$user_code','{$endtime}延长期限','$add_time')";
                $this->db->query($sql_log);
            }else{
               $ret['status'] = false; 
               $ret['message'] = '延长时间不能小于当前策略的结束日期';
            }
        }else{
               $ret['status'] = false;
               $ret['message'] = '请输入时间';
        }
            
            return $ret;
        }
        //删除策略
        function do_delete($new_strategy_code,$id){
            $this->delete_exp('op_gift_strategy_shop', array('strategy_code' =>  $new_strategy_code));
            $this->delete_exp('op_gift_strategy_goods', array('strategy_code' =>  $new_strategy_code));
            $this->delete_exp('op_gift_strategy_log', array('strategy_code' =>  $new_strategy_code));
            $this->delete_exp('op_gift_strategy_customer', array('strategy_code' =>  $new_strategy_code));
            $sql_detail_id = "select op_gift_strategy_detail_id from op_gift_strategy_detail where strategy_code='{$new_strategy_code}'";
            $data = $this->db->get_all($sql_detail_id);
            foreach($data as $val){
                $this->delete_exp('op_gift_strategy_range', array('op_gift_strategy_detail_id' => $val['op_gift_strategy_detail_id']));
            }
            $this->delete_exp('op_gift_strategy_detail', array('strategy_code' =>  $new_strategy_code));
            $this->delete_exp('op_gift_strategy', array('strategy_code' =>  $new_strategy_code));
            return $this->format_ret(1, '', '删除成功');
        }
}
