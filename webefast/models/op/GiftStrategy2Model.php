<?php

require_model('tb/TbModel');

class GiftStrategy2Model extends TbModel {

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
        //店铺权限
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_shop = load_model('base/ShopModel')->get_sql_purview_shop('shop_code', $filter_shop_code);
        $sql_strategy = "select strategy_code from op_gift_strategy_shop where 1 {$sql_shop}";
        $strategy_codes = $this->db->get_all_col($sql_strategy);
        if (empty($strategy_codes)) {
            $sql_main .= " AND 1=2 ";
        } else {
            $strategy_str = $this->arr_to_in_sql_value($strategy_codes, 'strategy_code', $sql_values);
            $sql_main .= " AND rl.strategy_code in ({$strategy_str}) ";
        }
        //策略名称 
        if (isset($filter['strategy_name']) && $filter['strategy_name'] != '') {
            $sql_main .= " AND (rl.strategy_name LIKE :strategy_name )";
            $sql_values[':strategy_name'] = $filter['strategy_name'] . '%';
        }
        //策略代码
        if (isset($filter['strategy_code']) && $filter['strategy_code'] != '') {
            $sql_main .= " AND (rl.strategy_code LIKE :strategy_code )";
            $sql_values[':strategy_code'] = '%' .$filter['strategy_code'] . '%';
        }
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] <> '') {
            $arr = explode(',', $filter['shop_code']);
            $str = "'" . join("','", $arr) . "'";
            $sql = "select strategy_code from op_gift_strategy_shop where shop_code in ({$str})";
            $strategy_codes = $this->db->get_all_col($sql);
            if (empty($strategy_codes)){
            	$sql_main .= " AND 1=2 ";
            } else {
            	$strategy_str = $this->arr_to_in_sql_value($strategy_codes, 'strategy_code', $sql_values);
            	$sql_main .= " AND rl.strategy_code in ({$strategy_str}) ";
            }
            
        }
        // 启用状态
        if (isset($filter['status']) && $filter['status'] != '') {
            $arr = explode(',', $filter['status']);
            $str = $this->arr_to_in_sql_value($arr, 'status', $sql_values);
            $sql_main .= " AND rl.status in ({$str}) ";
        }
      
        //活动开始时间
        if (isset($filter['activity_start_first_time']) && $filter['activity_start_first_time'] != '') {
            $sql_main .= " AND (rl.start_time >= :activity_start_first_time )";
            $sql_values[':activity_start_first_time'] = strtotime($filter['activity_start_first_time'] . ' 00:00:00');
        }
        if (isset($filter['activity_start_last_time']) && $filter['activity_start_last_time'] != '') {
            $sql_main .= " AND (rl.start_time <= :activity_start_last_time )";
            $sql_values[':activity_start_last_time'] = strtotime($filter['activity_start_last_time'] . ' 23:59:59');
        }
        //活动结束时间
        if (isset($filter['activity_end_first_time']) && $filter['activity_end_first_time'] != '') {
            $sql_main .= " AND (rl.end_time >= :activity_end_first_time )";
            $sql_values[':activity_end_first_time'] = strtotime($filter['activity_end_first_time'] . ' 00:00:00');
        }
        if (isset($filter['activity_end_last_time']) && $filter['activity_end_last_time'] != '') {
            $sql_main .= " AND (rl.end_time <= :activity_end_last_time )";
            $sql_values[':activity_end_last_time'] = strtotime($filter['activity_end_last_time'] . ' 23:59:59');
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
        $del_auth = load_model('sys/PrivilegeModel')->check_priv('op/op_gift_strategy/do_delete');
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        //审核权限
        $check_auth = load_model('sys/PrivilegeModel')->check_priv('op/op_gift_strategy/do_check');
        //启用/停用权限        
        $able_auth = load_model('sys/PrivilegeModel')->check_priv('op/op_gift_strategy/check_repeat');
        //现在的时间
        foreach ($data['data'] as $key => &$value) {
            $data['data'][$key]['test_status'] = $value['start_time'] > time() && $value['status'] == 0 ? 1 : 0;
        	$data['data'][$key]['start_time'] = date('Y-m-d H:i:s',$value['start_time']);
        	$data['data'][$key]['end_time'] = date('Y-m-d H:i:s',$value['end_time']);
        	$shop_ret = load_model('op/GiftStrategyShopModel')->get_shops_info_by_strategy_code($value['strategy_code']);
        	$data['data'][$key]['shop_code_name'] = isset($shop_ret['shop_name'])?implode(",", $shop_ret['shop_name']):"";
                $value['check_auth'] = $check_auth == '' ? 0 : 1;
                $value['able_auth'] = $able_auth == '' ? 0 : 1;
                $value['del_auth'] = $del_auth == '' ? 0 : 1;
                $time_now = time();
                $date_now = date('Y-m-d H:i',$time_now);
                if($date_now >= $value['start_time'] && $date_now < $value['end_time'] && $value['status'] == 1){
                    $value['date_auth'] = 1;
                }else{
                    $value['date_auth'] = 0;
                }
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    //添加新纪录
    function insert( $data) {
        $status = $this->valid($data);
        if ($status < 1) {
            return $this->format_ret('-1', '', $status);
        }

        $ret = $this->is_exists($data['strategy_code']);

        if (!empty($ret['data'])){
            return $this->format_ret('-1', '', '此code存在');
        }
       if (empty($data['shop_code'])){
            return $this->format_ret('-1', '', '店铺不能为空');
       }
        
        
        //        $stock_adjus['is_add_time'] = date('Y-m-d H:i:s');
        if (isset($data['shop_code'])){
        	$gift_shop = $data['shop_code'];
        	$data['shop_code'] = implode(",", $gift_shop);
        	//添加到赠品策略与店铺的关系表
        	load_model('op/GiftStrategyShopModel')->add($data['strategy_code'],$gift_shop);
        	 
        }
        return parent::insert($data);
    }
    
    function check_repeat($strategy_code){
        $sql = "select strategy_name from op_gift_strategy where status=1  ";
        $ret = $this->get_by_code($strategy_code);
        $strategy_data = &$ret['data'];
        
        $sql.= " AND shop_code=:shop_code ";
        $sql_values[':shop_code'] = $strategy_data['shop_code'];
        
        $sql.= " AND ( (start_time<:start_time AND end_time>:start_time)  ";
	$sql.= "   OR  ( start_time<:end_time AND end_time>:end_time ) ) ";
        $sql_values[':start_time'] = $strategy_data['start_time'];
        $sql_values[':end_time'] = $strategy_data['end_time'];

        $data = $this->db->get_all($sql,$sql_values);     
        $ret_data = array();
        $ret_status = -1;
        if(!empty($data)){
            $ret_status = 1;
            foreach($data as $val){
                $ret_data[] = $val['strategy_name'];
            }
        }
        return $this->format_ret($ret_status,  implode(",", $ret_data) );
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
        if (isset($data['shop_code'])){
        	$gift_shop = $data['shop_code'];
        	$data['shop_code'] = implode(",", $gift_shop);
        	//添加到赠品策略与店铺的关系表
        	load_model('op/GiftStrategyShopModel')->add($data['strategy_code'],$gift_shop);
        	
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
        //获取店铺名称
        $shop_ret = load_model('op/GiftStrategyShopModel')->get_shops_info_by_strategy_code($data['data']['strategy_code']);
        
        $data['data']['shop_code_name'] = isset($shop_ret['shop_name'])?implode(",", $shop_ret['shop_name']):"";
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
          $action_name = ($active==1)?'启用':'停用';
          $ret_strategy =  $this->get_by_id($id);
          $data = array(
                'strategy_code'=>$ret_strategy['data']['strategy_code'],
                'action_name'=>$action_name,
           );
          load_model('op/GiftStrategyLogModel')->insert($data);
        
        
    	$ret = parent :: update(array('status' => $active), array('op_gift_strategy_id' => $id));
    	return $ret;
    }




        function get_by_code($code) {
            $data = $this->get_row(array('strategy_code' => $code));
            //获取店铺名称
            $shop_ret = load_model('op/GiftStrategyShopModel')->get_shops_info_by_strategy_code($data['data']['strategy_code']);
            
            $data['data']['shop_code_name'] = isset($shop_ret['shop_name'])?implode(",", $shop_ret['shop_name']):"";
            return $data;
        }

        function update_check($active,$field, $id) {
        	
        	$ret = parent:: update(array($field => $active), array('op_gift_strategy_id' => $id));
               $ret_strategy =  $this->get_by_id($id);
                $data = array(
                     'strategy_code'=>$ret_strategy['data']['strategy_code'],
                     'action_name'=>'审核',
                );
                load_model('op/GiftStrategyLogModel')->insert($data);
        	return $ret;
        }
        
}