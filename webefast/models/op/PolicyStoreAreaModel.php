<?php

require_model('tb/TbModel');

/**
 * Class PolicyExpressModel 快递策略
 */
class PolicyStoreAreaModel extends TbModel {

    
    function get_table() {
		return 'op_policy_store_area';
	}
    
    function get_area_name_by_store_code($store_code) {
        $sql = "select name from base_area  where id in (select a.parent_id from op_policy_store_area o"
                . " INNER JOIN base_area a ON o.area_id=a.id "
                . " WHERE  o.store_code=:store_code ) ";
   
       
        
        
        $data = $this->db->get_all($sql, array(':store_code' => $store_code));
        $name_arr = array();
        foreach ($data as $val) {
            $name_arr[] = $val['name'];
        }
        return $name_arr;
    }

    //取得子类
    function get_child($p_code, $store_code) {
        if ($p_code != '') {

            $no_select = array('820000', '810000', '710000');
            $other_area = array('441900000000','442000000000');//东莞市 ，中山市 广东440000
            $no_select_where = implode(',', $no_select);
            $sql = "select id,name,parent_id,type FROM base_area WHERE parent_id = '$p_code' AND id not in({$no_select_where}) ";

            $rs = $this->db->get_all($sql);
            $data = array();


            $type = $rs[0]['type'];
            $check_data = $this->get_no_checked_node($p_code, $type, $store_code);


            foreach ($rs as $k => $v) {
                $is_check = 0;
                if (isset($check_data['checked'][$v['id']])) {
                    $data[$k]['checked'] = true;
                    $is_check = 1;
                }

                if ($is_check == 0 && isset($check_data['no_checked'][$v['id']])) {
                    $is_check = 1;
                    $data[$k]['checked'] = false;
                }

                $data[$k]['id'] = $v['id'];
                $data[$k]['text'] = $v['name'];
                if ($v['type'] < 4) {
                    $data[$k]['leaf'] = false;
                }
                if(in_array($v['id'], $other_area)){
                     $data[$k]['checked'] = $this->get_other_node($v['id'],$store_code);
   
                    unset( $data[$k]['leaf']);
                }
            }
        }


        return $data;
    }

    function get_no_checked_node($area_id, $type, $store_code) {

        $area_sql[2] = array(
            'checked' => "SELECT DISTINCT parent_id from  base_area a
                                ,(select DISTINCT  parent_id as id from base_area  WHERE  id in(
                                select area_id from op_policy_store_area where store_code='{$store_code}'
                                ) AND type=4  ) as b
                    where a.id=b.id",
     
            'no_checked' => "SELECT DISTINCT a.parent_id from  base_area  a
                            ,( select  DISTINCT parent_id as id from base_area  WHERE  id not in(
                                                            select area_id from op_policy_store_area  where store_code='{$store_code}' 
                                                            ) AND type=4    ) b
                            where a.id=b.id ",
        );

        $area_sql[3] = array(
            'checked' => " select DISTINCT  parent_id from base_area  
                 WHERE parent_id in( select id from base_area where parent_id='{$area_id}'   )  
                   AND  id in(  select area_id from op_policy_store_area where store_code='{$store_code}' )   ",
            'no_checked' => " select DISTINCT  parent_id from base_area 
                   WHERE parent_id in( select id from base_area where parent_id='{$area_id}'  )  
                   AND  id not in( select area_id from op_policy_store_area where store_code='{$store_code}'   ) AND TYPE=4           ",
        );

        $area_sql[4] = array(
            'checked' => " select   id as parent_id  from base_area  
                 WHERE  parent_id='{$area_id}' 
                   AND  id in(  select area_id from op_policy_store_area where  store_code='{$store_code}'   )   ",
            'no_checked' => " select    id as parent_id  from base_area 
                   WHERE  parent_id='{$area_id}' 
                   AND  id not in( select area_id from op_policy_store_area where  store_code='{$store_code}'  ) AND TYPE=4           ",
        );
        $ret_data = array();

        foreach ($area_sql[$type] as $key => $sql) {
            $data = $this->db->get_all($sql);
            foreach ($data as $val) {
                $ret_data[$key][$val['parent_id']] = $val['parent_id'];
            }
        }
          $other_area = array('441900000000','442000000000');//东莞市 ，中山市 440000
           foreach($other_area as $key=>$val){
               //checked no_checked
               $checked = $this->get_other_node($val,$store_code);
               if($checked){
                    $ret_data['checked']['440000'] = '440000';
                   }else{
                    $ret_data['no_checked']['440000'] = '440000';
               }
           }
           
        
        return $ret_data;
    }
  function get_other_node($area_id,$store_code){
        $sql = "  select * from op_policy_store_area where  area_id=:area_id AND store_code=:store_code";
        $sql_values = array(':area_id'=>$area_id,':store_code'=>$store_code);
        $data = $this->db->get_row($sql,$sql_values);
        $checked = FALSE;
        if(!empty($data)){
             $checked = TRUE ;
        }
        return $checked;
        
    }
    function clear_area($store_code) {
        $ret =  $this->delete(array('store_code' => $store_code));
        load_model('op/PolicyStoreModel')->update_area_desc($store_code,array());
        
        return $ret;
    }

    function save_area($param) {
        $data = $this->get_area_data_by_type($param);

        if ($param['checked'] == 1) {
            $this->insert_multi($data);
        } else {
            $area_id_arr = array();
            foreach ($data as $val) {
                $area_id_arr[] = $val['area_id'];
            }
            $area_id_str = implode(",", $area_id_arr);
            $sql = "delete from op_policy_store_area  where area_id in ({$area_id_str}) AND store_code='{$param['store_code']}'";
            $this->db->query($sql);
        }
        $area_arr = $this->get_select_province($param['store_code']);
        
        
        load_model('op/PolicyStoreModel')->update_area_desc($param['store_code'],$area_arr);
        return $this->format_ret(1);
    }
    function get_select_province($store_code){
        $area_arr = array();
        $sql = "SELECT DISTINCT parent_id from  base_area a
                                ,(select DISTINCT  if(id=441900000000||id= 442000000000,id,parent_id) as id from base_area  WHERE  id in(
                                select area_id from op_policy_store_area where store_code='{$store_code}'
                                )) as b
                    where a.id=b.id";
       $data =  $this->db->get_all($sql);
        foreach($data as $val){
            $area_arr[] = $val['parent_id'];
        }
        return $area_arr;
    }

    
    

    function get_area_data_by_type($param) {

        $area_id = $param['id'];
        $type = $param['type'];
        if ($param['type'] == 4) {
            $data = array(array('area_id' => $area_id, 'store_code' => $param['store_code']));
            return $data;
        }
       $other_area = array('441900000000','442000000000');//东莞市 ，中山市 广东440000
        $no_select = array('820000', '810000', '710000');
        $no_select_where = implode(',', $no_select);

        $sql = "  1  ";
        switch ($type) {
            case 1:
                $sql.= " AND  parent_id in(select id from base_area where parent_id in(select id from base_area where parent_id='{$area_id}') AND id not in({$no_select_where}) ) ";
                break;
            case 2:
                $sql.= " AND   parent_id in(select id from base_area where parent_id='{$area_id}') ";
                break;
            case 3:
                $sql.= in_array($area_id,$other_area)?" AND id='{$area_id}' ":" AND parent_id='{$area_id}' ";
                break;
            default :
        }

       if($type==1||$area_id=='440000'){
            $sql = "( ({$sql}) OR ( id in('".implode("','", $other_area)."') ) )";
        } 


        if ($param['checked'] == 1) {
            $sql.= "AND id not in(select area_id from  op_policy_store_area where  store_code='{$param['store_code']}' )";
        } else {
            $sql.= "AND id  in(select area_id from  op_policy_store_area where  store_code='{$param['store_code']}')";
        }
        $sql = " select id as area_id, '{$param['store_code']}' as  store_code from base_area where ".$sql;
        $data = $this->db->get_all($sql);

        return $data;
    }

}
