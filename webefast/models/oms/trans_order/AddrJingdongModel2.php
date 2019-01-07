<?php
require_model('tb/TbModel');
class AddrJingdongModel extends TbModel{

	//地址匹配
    function match_addr($api_data){
	    $c1 = $api_data['receiver_province'];
	    $c2 = $api_data['receiver_city'];
	    $c3 = $api_data['receiver_district'];
	    $sql = "select name,sys_area_id,sys_area_level,id,parent_id from api_jingdong_area where name in('{$c1}','{$c2}','{$c3}') and sys_area_level in(2,3,4) and sys_area_id>0 order by sys_area_level";
	    $db_addr_t = ctx()->db->get_all($sql);
	    if (empty($db_addr_t)){
		    return $this->format_ret(-30,'','地址匹配找不到省市区信息');
	    }
        //清理存在的重名项
        $_t_arr = array();
        $db_addr = array();
        foreach($db_addr_t as $sub_t){
            $_t_arr[$sub_t['id']] = $sub_t;
        }
        foreach($db_addr_t as $sub_t){
            if($sub_t['parent_id']>0 && !isset($_t_arr[$sub_t['parent_id']])){
                continue;
            }
            $db_addr[] = $sub_t;
        }
        //echo '<hr/>$db_addr<xmp>'.var_export($db_addr,true).'</xmp>';

	    $first_type = $db_addr[0]['sys_area_level'];
	    $level2_type = $db_addr[1]['sys_area_level'];
	    $level3_type = @$db_addr[2]['sys_area_level'];

	    if ($first_type > 2){
		    return $this->format_ret(-30,'','地址匹配找不到省信息');
	    }
	    $addr_map = load_model('util/ViewUtilModel')->get_map_arr($db_addr,'parent_id');

	    $addr_arr = array('receiver_country'=>array(1,'中国'));
	    $addr_arr['receiver_province'] = array($db_addr[0]['sys_area_id'],$db_addr[0]['name']);

		if ($level2_type>3){
			$sql = "select parent_id from base_area where id = {$db_addr[1]['sys_area_id']}";
			$_parent_id = ctx()->db->getOne($sql);
			$addr_arr['receiver_city'] = array($_parent_id,'--');
		}else{
		    $_t2 = $addr_map[$db_addr[0]['id']];
		    if (empty($_t2)){
			    return $this->format_ret(-30,'','地址匹配找不到市信息');
		    }
		    $addr_arr['receiver_city'] = array($_t2['sys_area_id'],$_t2['name']);
		}

		if ($level2_type > 3){
			$_t2 = $addr_map[$db_addr[0]['id']];
		    if (empty($_t2)){
			    //return $this->format_ret(-30,'','地址匹配找不到区信息');
		    }else{
                        $addr_arr['receiver_district'] = array($_t2['sys_area_id'],$_t2['name']);
                    }
		}else{
		    $_t3 = $addr_map[$_t2['id']];
		    //区的信息可以是没有的
		    if (empty($_t3)){
			    //return $this->format_ret(-30,'','地址匹配找不到区信息');
		    }else{
                        $addr_arr['receiver_district'] = array($_t3['sys_area_id'],$_t3['name']);
                    }
		}

	    $result = array();
	    $result['receiver_country'] = $addr_arr['receiver_country'][0];
	    $result['receiver_province'] = $addr_arr['receiver_province'][0];
	    $result['receiver_city'] = $addr_arr['receiver_city'][0];
	    $result['receiver_district'] = isset($addr_arr['receiver_district'][0]) ? $addr_arr['receiver_district'][0] : '';
	    $result['receiver_address'] = $api_data['receiver_address'];
	    $result['receiver_addr'] = $api_data['receiver_addr'];
		return $this->format_ret(1,$result);
    }

}
