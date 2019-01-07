<?php
/**
 *

订单快递适配策略相关业务
 *
 * @author huanghy
 *
 */
require_model('tb/TbModel');
require_lang('crm');
require_lib('util/oms_util', true);

class ExpressStrategyModel extends TbModel {
    function get_table() {
        return 'op_policy_express';
    }

    /*
     * 根据条件查询数据
     */
    function get_by_page($filter) {

        $sql_join = "";
        $sql_main = "FROM {$this->table} oe  WHERE 1";
        $sql_values = array();

        $select = 'oe.*';

        $data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
        foreach($data['data'] as $k=>$sub_data){
            //可达快递
            $sql = "select express_code from op_policy_express_rule where pid = :pid";
            $expressCodeList = $this->db->get_all_col($sql, array('pid'=>$sub_data['policy_express_id']));
            $codes = implode("','", $expressCodeList);
            $sql = "select express_name from base_express where express_code in ('$codes') ";
            $expressList = $this->db->get_all_col($sql);
            $data['data'][$k]['express_range'] = implode(',', array_slice($expressList, 0, 3));
            $data['data'][$k]['express_range_all'] = implode(',', $expressList);
            if(count($expressList) > 3) $data['data'][$k]['express_range'] .= '...';

            //区域范围
            $sql = "select area_id from op_policy_express_area where pid = :pid";
            $areaIdList = $this->db->get_all_col($sql, array('pid'=>$sub_data['policy_express_id']));
            $ids = implode("','", $areaIdList);
            $sql = "select name from base_area where id in ('$ids') ";
            $areaList = $this->db->get_all_col($sql);
            $data['data'][$k]['area_range'] = implode(',', array_slice($areaList, 0, 3));
            $data['data'][$k]['area_range_all'] = implode(',', $areaList);
            if(count($areaList) > 3) $data['data'][$k]['area_range'] .= '...';

            if ($sub_data['status'] == 1)
                $data['data'][$k]['status_html'] = '已启用';
            else
                $data['data'][$k]['status_html'] = '未启用';
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }



    function get_express_by_page($filter) {

        $sql_join = "";
        $sql_main = "FROM base_express  WHERE status = 1";
        $sql_values = array();

        $select = 'express_id,express_code,express_name';

        $data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }


    /**
     * @param $id
     * @return array
     */
    function get_by_id($id) {

        return  $this->get_row(array('policy_express_id'=>$id));
    }


    /**
     * 生成策略代码
     */
    function create_code()
    {
        $sql = "select policy_express_id  from {$this->table}   order by policy_express_id desc limit 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $code = intval($data[0]['policy_express_id'])+1;
        } else {
            $code = 1;
        }
        require_lib ( 'comm_util', true );
        $code = "KDSPCL" . add_zero($code,3);
        return $code;
    }


    /**
     * 生成策略名称
     */
    function create_name()
    {
        $sql = "select policy_express_id  from {$this->table}   order by policy_express_id desc limit 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $code = intval($data[0]['policy_express_id'])+1;
        } else {
            $code = 1;
        }
        require_lib ( 'comm_util', true );
        $name = "策略" . add_zero($code,3);
        return $name;
    }



    /**
     * @param $code
     * @return array
     */
    function get_by_code($code) {
        return $this->get_row(array('customer_code'=>$code));
    }
    /**
     * 通过field_name查询
     *
     * @param  $ :查询field_name
     * @param  $select ：查询返回字段
     * @return array (status, data, message)
     */
    public function get_by_field($field_name,$value, $select = "*") {
        $sql = "select {$select} from {$this->table} where {$field_name} = :{$field_name}";
        $data = $this -> db -> get_row($sql, array(":{$field_name}" => $value));
        if ($data) {
            return $this -> format_ret('1', $data);
        } else {
            return $this -> format_ret('-1', '', 'get_data_fail');
        }
    }
    /*
     * 添加新纪录
     */
    function insert($express_strategy) {
//		$status = $this->valid($express_strategy);
//		if ($status < 1) {
//			return $this->format_ret($status);
//		}
//
//		$ret = $this->is_exists($customer['policy_express_code']);
//
//		if (!empty($ret['data'])) {
//			return $this->format_ret(CUSTOMER_ERROR_UNIQUE_NAME);
//		}
        $ret = parent::insert($express_strategy);
        $info['policy_express_id'] = $ret['data'];
        $action_name = '编辑';
        $data = array(
            'pid'=>$info['policy_express_id'],
            'action_name'=>$action_name,
            'desc' => '新增订单快递适配策略'
        );
        load_model('crm/ExpressStrategyLogModel')->insert($data);
        return   $this->format_ret(1,$info);
    }

    //转单时添加会员
    function add_express_strategy($info){
        $status = $this->valid($info);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $sql = "select customer_code from crm_customer where customer_name = :customer_name";
        $row = CTX()->db->getRow($sql,array('customer_name'=>$info['customer_name']));
        if (!empty($row)) {
            return $this->format_ret(1,$row);
        }
        $sql = "select max(customer_id) from crm_customer";
        $max_customer_id = CTX()->db->getOne($sql);
        $info['customer_code'] = (int)$max_customer_id + 1;
        $ret = $this->insert($info);
        $info['customer_id'] = $ret['data'];
        return   $this->format_ret(1,$info);
    }



    /*
     * 修改纪录
     */
    function update($express_strategy, $policy_express_id) {
        $ret = $this->get_row(array('policy_express_id'=>$policy_express_id));
        $desc = '';
        if ($ret['data']['policy_express_code'] != $express_strategy['policy_express_code']) {
            $desc .= "策略代码改为{$express_strategy['policy_express_code']}；";
        }
        if ($ret['data']['policy_express_name'] != $express_strategy['policy_express_name']) {
            $desc .= "策略名称改为{$express_strategy['policy_express_name']}；";
        }
        if ($ret['data']['is_fee_first'] != $express_strategy['is_fee_first']) {
            if ($express_strategy['is_fee_first'] == 1) {
                $desc .= "最低运费判断改为启用；";
            }else{
                $desc .= "最低运费判断改为停用；";
            }           
        }
        if ($ret['data']['store_code'] != $express_strategy['store_code']) {
            $store_code = ltrim($express_strategy['store_code'],'[');
            $store_code1 = rtrim($store_code,']');
            $store = $this->db->get_all("select store_name from base_store where store_code in ({$store_code1});");
            foreach ($store as $value) {
                $store_n .= $value['store_name'].',';
            }
            $store_name = rtrim($store_n, ',');
            $desc .= "适用仓库改为{$store_name}；";
        }
        $ret2 = parent::update($express_strategy, array('policy_express_id'=>$policy_express_id));
        if (!empty($desc)) {
            $action_name = '编辑';
            $desc_ = rtrim($desc, '；');
            $data = array(
                'pid'=>$policy_express_id,
                'action_name'=>$action_name,
                'desc' => $desc_
            );
            load_model('crm/ExpressStrategyLogModel')->insert($data);
        }      
        return $ret2;
    }


    /*
     * 服务器端验证
     */
    private function valid($data, $is_edit = false) {
        if (!isset($data['customer_name']) || !valid_input($data['customer_name'], 'required')) return CUSTOMER_ERROR_NAME;
        return 1;
    }

    function is_exists($value, $field_name='customer_name') {
        $ret = parent::get_row(array($field_name=>$value));
        return $ret;
    }






    //修改地址信息
    function update_customer_address($info,$wh){
        //echo '<hr/>info<xmp>'.var_export($info,true).'</xmp>';
        //echo '<hr/>wh<xmp>'.var_export($wh,true).'</xmp>';
        $ret = M('crm_customer_address')->update($info,$wh);
        return $ret;
    }

    //删除快递策略
    function delete_express_strategy($policy_express_id){
    	$this->begin_trans();
    	try {
    		$ret = parent :: delete(array('policy_express_id' => $policy_express_id));
    		if ($ret['status'] != 1) {
    			throw new Exception($ret['message']);
    		}
    		$is_express_area = load_model('crm/PolicyExpressAreaModel')->get_row(array('pid' => $policy_express_id));
    		if ($is_express_area['status'] == 1){
    			$ret = load_model('crm/PolicyExpressAreaModel')->delete(array('pid' => $policy_express_id));
    			if ($ret['status'] != 1) {
    				throw new Exception($ret['message']);
    			}
    		}
    		$is_express_rule = load_model('crm/PolicyExpressRuleModel')->get_row(array('pid' => $policy_express_id));
    		if ($is_express_rule['status'] == 1){
    			$ret = load_model('crm/PolicyExpressRuleModel')->delete(array('pid' => $policy_express_id));
    			if ($ret['status'] != 1) {
    				throw new Exception($ret['message']);
    			}
    		}
    	
    		$this->commit();
    		return array('status' => 1, 'message' => '删除成功');
    	} catch (Exception $e) {
    		$this->rollback();
    		return array('status' => -1, 'message' => $e->getMessage());
    	}
    }
    
    /**
     * 删除纪录
     */
    function delete($policy_express_id) {
        $ret = parent :: delete(array('policy_express_id' => $policy_express_id));
        return $ret;
    }

    function update_active($active, $policy_express_id) {
        if (!in_array($active, array(1, 2))) {
            return $this->format_ret('error_params');
        }
        $ret = parent::update(array('status' => $active), array('policy_express_id' => $policy_express_id));
        $action_name = ($active==1)?'启用':'停用';
        $ret_strategy =  $this->get_by_id($id);
        $data = array(
        		'pid'=>$policy_express_id,
        		'action_name'=>$action_name,
        );
        load_model('crm/ExpressStrategyLogModel')->insert($data);
        return $ret;
    }

    //取得子类
    function get_child($p_code, $policy_express_id) {
        if ($p_code != '') {

            $no_select = array('820000','810000','710000');
//            $other_area = array('441900000000','442000000000');//东莞市 ，中山市

            
            $no_select_where = implode(',', $no_select);
            $sql = "select id,name,parent_id,type FROM base_area WHERE parent_id = '$p_code' AND id not in({$no_select_where}) ";

            $rs = $this->db->get_all($sql);
            $data = array();
            $key_node = array();
            $checked_arr = array();
            $type  = $rs[0]['type'];
            $check_data = $this->get_no_checked_node($p_code,$type,$policy_express_id);
      
            
            foreach ($rs as $k => $v) {
                $is_check = 0;
                if(isset($check_data['checked'][$v['id']])){
                     $data[$k]['checked'] = true;
                     $is_check = 1;
                }
            
                if($is_check==0&&isset($check_data['no_checked'][$v['id']])){
                     $is_check = 1;
                      $data[$k]['checked'] = false;
                }
       
                $data[$k]['id'] = $v['id'];
                $data[$k]['text'] = $v['name'];
                if($v['type']<4){
                    $data[$k]['leaf'] = false;
                }
//                if(in_array($v['id'], $other_area)){
//                     $checked_arr = $this->get_other_node($v['id'],$policy_express_id);
//                     if(!empty($checked_arr)){
//                           $data[$k] = array_merge($data[$k], $checked_arr);
//                     }
//                    unset( $data[$k]['leaf']);
//                }
                
                

            }
        }
             
//        if(!empty($checked_arr)&&$type<4){
//           $this->set_area_checked($data,$key_node,$checked_arr,$type,$policy_express_id);
//        }
        //$type
 

        return $data;
    }
    
    function get_other_node($area_id,$policy_express_id){
        $sql = "  select pid from op_policy_express_area where  area_id=:area_id";
        $sql_values = array(':area_id'=>$area_id);
        $data = $this->db->get_row($sql,$sql_values);
        $check_arr = array();
        if(empty($data)){
             $check_arr['checked'] = FALSE ;
        }else{
            if($policy_express_id==$data['pid']){
                 $check_arr['checked'] = TRUE ;
            }
        }
                
        return $check_arr;
        
    }

    function get_no_checked_node($area_id,$type,$policy_express_id){
        
            $area_sql[2] = array(
                'checked'=>"SELECT DISTINCT parent_id from  base_area a
                                ,(select DISTINCT  parent_id as id from base_area  WHERE  id in(
                                select area_id from op_policy_express_area where pid='{$policy_express_id}'
                                ) AND type=4  ) as b
                    where a.id=b.id",            

                'no_checked'=>"SELECT DISTINCT a.parent_id from  base_area  a
                            ,( select  DISTINCT parent_id as id from base_area
                            WHERE  id not in(
                                     select area_id from op_policy_express_area 
                                                            ) AND type=4    ) b
                            where a.id=b.id ",      
            );
                        
           $area_sql[3] = array(
                'checked'=>" select DISTINCT  parent_id from base_area  
                 WHERE parent_id in( select id from base_area where parent_id='{$area_id}'  )  
                   AND  id in(  select area_id from op_policy_express_area where pid={$policy_express_id}  )   ",
                           
                'no_checked'=>" select DISTINCT  parent_id from base_area 
                   WHERE parent_id in( select id from base_area where parent_id='{$area_id}'  )  
                   AND  id not in( select area_id from op_policy_express_area   ) AND TYPE=4           ",      
            );                
                        
            $area_sql[4] = array(
                'checked'=>" select   id as parent_id  from base_area  
                 WHERE  parent_id='{$area_id}' 
                   AND  id in(  select area_id from op_policy_express_area where pid={$policy_express_id}  )   ",
                           
                'no_checked'=>" select    id as parent_id  from base_area 
                   WHERE  parent_id='{$area_id}' 
                   AND  id not in( select area_id from op_policy_express_area   ) AND TYPE=4           ",      
            );                   
           $ret_data = array();       
           
           foreach($area_sql[$type] as $key=>$sql){
               $data = $this->db->get_all($sql);
               foreach($data as $val){
                   $ret_data[$key][$val['parent_id']] = $val['parent_id'];
               }
           }
           
           //$area_id,$type,$policy_express_id)
           //get_other_node($area_id,$policy_express_id)
//           $other_area = array('441900000000','442000000000');//东莞市 ，中山市 440000
//           foreach($other_area as $key=>$val){
//               //checked no_checked
//               $check_arr = $this->get_other_node($val,$policy_express_id);
//               if(!empty($check_arr)){
//                   if($check_arr['checked']){
//                         $ret_data['checked']['440000'] = '440000';
//                   }else{
//                        $ret_data['no_checked']['440000'] = '440000';
//                   }
//               }
//           }
           
           
           return $ret_data;
    }
    
            
    
    
    
    
    function set_area_checked(&$data,$key_node,$checked_arr,$type,$policy_express_id){
             $new_key_node = array();
             $ids = implode(",", $checked_arr);
             $type = $type+1;
             $sql = "select DISTINCT b.parent_id FROM base_area b inner join
             op_policy_express_area   o  on b.id=o.area_id
             WHERE  o.pid='{$policy_express_id}' and b.type={$type} and b.parent_id in({$ids})  ";
             $parent_data = $this->db->get_all($sql);
      
             if(!empty($parent_data)){
                 foreach($parent_data as $val){
 
                   if(isset($key_node[$val['parent_id']])){
                       
                          $k =  $key_node[$val['parent_id']];
                          $data[$k]['checked'] = true;
                          unset($checked_arr);
                      }
                 }
             }
       
             
            if(!empty($checked_arr)&&$type<4){
                $type = $type+1;
                     $sql = " select parent_id from base_area where id in(
                    select DISTINCT b.parent_id FROM base_area b inner join
                 op_policy_express_area   o  on b.id=o.area_id
                 WHERE  o.pid='{$policy_express_id}' and b.type={$type} )";  
                   $parent_data = $this->db->get_all($sql);
                  //var_dump($parent_data);die;
                   if(!empty($parent_data)){
                     foreach($parent_data as $val){
                          $k =  $key_node[$val['parent_id']];
                          $data[$k]['checked'] = true;
                         // $data[$k]['expanded'] = true;
                          unset($checked_arr);
                     }   
                   }
            } 
  
             
    }
    
    
}


