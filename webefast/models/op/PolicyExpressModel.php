<?php

require_model('tb/TbModel');

/**
 * Class PolicyExpressModel 快递策略
 */
class PolicyExpressModel extends TbModel {

    /**
     * 适配策略 , 根据订单号
     * @param $sellRecordCode
     * @return array
     */
    private $shop_expores_arr;

    public function parse_by_code($sellRecordCode) {
        $sql = "select * from oms_sell_record where sell_record_code = :code";
        $record = $this->db->get_row($sql, array('code' => $sellRecordCode));
        return $this->parse($record);
    }

    /**
     * 适配策略 , 根据订单
     * @param $record
     * @return array
     */
    public function parse(&$record, &$record_detail) {
        if (empty($record)) {
            return array('status' => '-1', 'message' => '订单不存在');
        }
        

        $area_city_arr = array('442000000000', '441900000000');
        // 441900000000:东莞   442000000000:中山市

        $area_id = $record['receiver_district'];
        if (empty($area_id)&&in_array($record['receiver_city'], $area_city_arr)) {
            $area_id = $record['receiver_city'];
        }
        
        if(!empty($area_id)){
            $policy = $this->get_reached_express_list($area_id);
        }else{
            $district_data = $this->get_district_by_city($record['receiver_city']);
            $policy =  $this->get_reached_express_list_by_area($district_data);
            
        }

    


        if (empty($policy)) {
            //适配失败, 取商店默认快递方式
            return $this->get_shop_default_express($record['shop_code']);
        }

        $policy['store_code'] = trim($policy['store_code']);
        if (!empty($policy['store_code'])) {
            $store_code_arr = json_decode($policy['store_code'], TRUE);
            //不是对应的仓库
            if (!in_array($record['store_code'], $store_code_arr)) {
                return $this->get_shop_default_express($record['shop_code']);
            }
        }

        $sql = "select express_data from base_shop where shop_code=:shop_code";
        $express_data = $this->db->get_value($sql, array(':shop_code' => $record['shop_code']));
        $this->shop_expores_arr = array();
        if (!empty($express_data)) {
            $this->shop_expores_arr = json_decode($express_data, true);
        }

        $ret = array();

        // 如果启用"最低运费优先", 计算所有快递方式的总运费, 取运费最便宜项
        if ($policy['is_fee_first'] == '1') {
            $expores_code = $this->get_express_code_by_fee_first($policy, $record_detail, $record);
            if(!empty($expores_code)){
                $ret = $this->format_ret(1, $expores_code, '按"最低运费优先"适配成功');
            }
            
            //return array('status'=>'1', 'data'=>$policy['express_code'], 'message'=>'按"最低运费优先"适配成功');
        } else {
            $expores_code = $this->get_express_code_by_priority($policy['pid']);
            if(!empty($expores_code)){
                $ret = $this->format_ret(1, $expores_code, '按"优先级"适配成功');
            }
        }
        
        if(empty($ret)){
          return $this->get_shop_default_express($record['shop_code']);
        }
        return $ret;
    }
    
    /**
     * 适配策略 , 根据经销订单
     * @param $record
     * @return array
     */
    public function fx_parse(&$record, &$record_detail) {
        if (empty($record)) {
            return array('status' => '-1', 'message' => '订单不存在');
        }
        
        $area_city_arr = array('442000000000', '441900000000');
        // 441900000000:东莞   442000000000:中山市

        $area_id = $record['district'];
        if (empty($area_id)&&in_array($record['city'], $area_city_arr)) {
            $area_id = $record['city'];
        }
        
        if(!empty($area_id)){
            $policy = $this->get_reached_express_list($area_id);
        }else{
            $district_data = $this->get_district_by_city($record['city']);
            $policy =  $this->get_reached_express_list_by_area($district_data);
            
        }
        
        $ret = array();

        // 如果启用"最低运费优先", 计算所有快递方式的总运费, 取运费最便宜项
        if ($policy['is_fee_first'] == '1') {
            $expores_code = $this->get_express_code_by_fee_first($policy, $record_detail, $record);
          //  var_dump($expores_code,1);die;
            if(!empty($expores_code)){
                $ret = $this->format_ret(1, $expores_code, '按"最低运费优先"适配成功');
            }
            
            //return array('status'=>'1', 'data'=>$policy['express_code'], 'message'=>'按"最低运费优先"适配成功');
        } else {
            $expores_code = $this->get_express_code_by_priority($policy['pid']);
            if(!empty($expores_code)){
                $ret = $this->format_ret(1, $expores_code, '按"优先级"适配成功');
            }
        }
        
        if(empty($ret)){
          return $this->format_ret(-1,'','适配失败');
        }
        return $ret;
    }
    
    private function set_shop_expores_arr($shop_code){
        static $expores_arr = NULL;
        $this->shop_expores_arr = array();
        if(!isset($expores_arr[$shop_code])){
            $sql = "select express_data from base_shop where shop_code=:shop_code";
            $express_data = $this->db->get_value($sql, array(':shop_code' => $shop_code));
            if (!empty($express_data)) {
                $this->shop_expores_arr = json_decode($express_data, true);
                $expores_arr[$shop_code] = $this->shop_expores_arr ;
            }
        }else{
               $this->shop_expores_arr = $expores_arr[$shop_code];
        }
    }







    private function get_express_code_by_priority($pid) {


        $sql = "select express_code from op_policy_express_rule where pid=:pid  ";
        if (!empty($this->shop_expores_arr)) {
            $shop_expores_str = "'" . implode("','", $this->shop_expores_arr) . "'";
            $sql .= " AND express_code  in({$shop_expores_str}) ";
        }
        $sql .= " order by priority desc ";


        return $this->db->get_value($sql, array(':pid' => $pid));
    }

    private function get_express_code_by_fee_first(&$policy, &$record_detail,$record) {
        $weight =(isset($record['goods_weight'])&& $record['goods_weight']!='')? $record['goods_weight']:$this->get_record_detail_goods_weigh($record_detail);
        $express_rule_data = $this->get_policy_express_rule_by_pid($policy['pid']);
        $weight =(float) $weight/1000;
        $express_code = '';
        $fee_min = -1;
        foreach ($express_rule_data as $express_rule) {
            if (!empty($this->shop_expores_arr) && !in_array($express_rule['express_code'], $this->shop_expores_arr)) {
                continue;
            }

            $express_fee = $this->get_fee_by_weight($weight, $express_rule);
            if ($fee_min < 0) {
                $fee_min = $express_fee;
                $express_code = $express_rule['express_code'];
            } else if ($express_fee < $fee_min) {
                $fee_min = $express_fee;
                $express_code = $express_rule['express_code'];
            }
        }
        return $express_code;
    }

    private function get_fee_by_weight($weight, $express_rule) {

        if ($express_rule['first_weight'] == 0 || $express_rule['added_weight'] == 0 || $express_rule['first_weight_price'] == 0 || $express_rule['added_weight_price'] == 0) {
            return 0;
        }
        //包裹重量小于首重
        if ($weight <= $express_rule['first_weight']) {
            $weigh_express_money = $express_rule['first_weight_price'];
        } else {
            $added_weight = $weight - $express_rule['first_weight'];
            //续重规则 0实重 1半重 2过重

            if ($express_rule['added_weight_type'] == 'g0') {
                //实重【超出首重的重量 * 续重单价】
                $added = $added_weight;
            } elseif ($express_rule['added_weight_type'] == 'g1') {
                //半重【超出首重的重量不足0.5Kg时讲按照0.5Kg进行收费,超过则按照1Kg的进行收费】
                $xiaoshu = $added_weight - floor($added_weight);
                //无小数,则
                if ($xiaoshu == 0) {
                    $added = $added_weight;
                } elseif ($xiaoshu >= 0.5) {
                    $added = floor($added_weight) + 1;
                } else {
                    $added = floor($added_weight) + 0.5;
                }
            } elseif ($express_rule['added_weight_type'] == 'g2') {
                //过重【无论超出首重多少都按照1Kg进行收费】
                $added = ceil($added_weight);
            }
            $weigh_express_money = $express_rule['first_weight_price'] + number_format(($added / $express_rule['added_weight']) * $express_rule['added_weight_price'], 2, '.', '');
        }
        return $weigh_express_money;
    }

    public function get_record_detail_goods_weigh(&$record_detail) {
        $weight = 0;
        foreach ($record_detail as $val) {
            //weight
           // $goods_info = load_model('goods/GoodsCModel')->get_goods_info($val['goods_code'], array('weight'));
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], array('weight'));
            $goods_weigh = empty($sku_info['weight']) ? 0 : $sku_info['weight'];
            $weight +=(float) $goods_weigh * $val['num'];
        }
        return $weight;
    }

    private function get_policy_express_rule_by_pid($pid) {
        static $policy_express_rule = null;
        
        if(!isset($policy_express_rule[$pid])){
            $sql = "select * from op_policy_express_rule where pid=:pid order by priority desc";
            $policy_express_rule[$pid] = $this->db->get_all($sql, array(':pid' => $pid));
        }
  
        return $policy_express_rule[$pid];
    }

    /**
     * 读取可达配送方式
     * @param $area_id
     * @return array|bool
     */
    public function get_reached_express_list($area_id) {
 
            $sql = "select a.*, c.* from op_policy_express a
                inner join op_policy_express_area b on b.pid = a.policy_express_id
                inner join op_policy_express_rule c on c.pid = a.policy_express_id
                where  a.status=1 AND b.area_id=:area_id
                order by c.priority desc";
            $data = $this->db->get_row($sql,array(':area_id'=>$area_id));
   
        return $data;
    }
    /**
     * 读取可达配送方式
     * @param $area_id_arr
     * @return array|bool
     */
    private function get_reached_express_list_by_area($area_id_arr) {
        
            $area_where = " b.area_id='" . implode("' OR b.area_id='", $area_id_arr) . "'";
            $sql = "select a.policy_express_id from op_policy_express a
                inner join op_policy_express_area b on b.pid = a.policy_express_id
                where  a.status=1 AND ({$area_where}) ";
            $data = $this->db->get_all($sql);
            
            $area_num = count($area_id_arr);
            $ret_data = array();
            $policy_express_id = 0;
            if(!empty($data)){
                $policy_num = count($data);
              
                if($area_num!=$policy_num){
                    return $ret_data;
                }
                    $policy_express_id = $data[0]['policy_express_id'];
                    foreach ($data as $val){
                        if($val['policy_express_id'] !=$policy_express_id){
                            $policy_express_id = 0;
                            break;
                        }
                    }
            }
            
            if($policy_express_id!=0){
                    $sql = "select a.*, c.* from op_policy_express a
                inner join op_policy_express_area b on b.pid = a.policy_express_id
                inner join op_policy_express_rule c on c.pid = a.policy_express_id
                where  a.status=1 AND a.policy_express_id=:policy_express_id
                order by c.priority desc";
                $ret_data = $this->db->get_row($sql,array(':policy_express_id'=>$policy_express_id)); 
            }
            return $ret_data;
    }
    private function get_district_by_city($city_id) {
        $sql = "select id from base_area where  parent_id = :parent_id   ";
        $data = $this->db->get_all($sql, array(':parent_id' => $city_id));
        $area_data = array();
        foreach ($data as &$val) {
            $area_data[] = $val['id'];
        }
        return $area_data;
    }

    /**
     * 读取商店默认配送方式
     * @param $shopCode
     * @return array
     */
    public function get_shop_default_express($shopCode) {
        $sql = "select * from base_shop where shop_code = :shop_code";
        $record = $this->db->get_row($sql, array('shop_code' => $shopCode));
        return array('status' => '1', 'data' => $record['express_code'], 'message' => '按"商店默认快递"适配成功');
    }

}
