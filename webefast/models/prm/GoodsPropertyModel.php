<?php
/**
 * 商品扩展属性相关业务
 *
 * @author hunter
 *
 */
require_model('tb/TbModel');
require_lang('prm');

class GoodsPropertyModel extends TbModel {
    /**
     * @var string 表名
     */
    protected $table = 'base_property_set';

    function get_all_rows()
    {
		$sql = "select * FROM  base_property_set ";
		$res = $this->db->get_all($sql);
		$id_array = array();
		foreach($res as $key => $value){
			array_push($id_array,$value['property_set_id']);
		}
	    for ($i=1;$i<11;$i++){
			if(in_array($i,$id_array)){
				$sql = "select property_val_title FROM  base_property_set  where property_set_id = ".$i;
				$result = $this->db->get_row($sql);	
				$data[$i] = array(
			   	'property_set_id' => $i,
			   	'property_val_title' => $result['property_val_title']
			   	);	
			}else{
			   	$data[$i] = array(
			   	'property_set_id' => $i,
			   	'property_val_title' => ''
			   	);
	  		}}
        return $this->format_ret(1, $data);
    }
    
    function opt_save_detail($property_set_id,$property_val_title)
    {
			
    		parent :: delete(array('property_set_id' => $property_set_id));
    		if($property_set_id == 10)
    		$property_code = "0".$property_set_id;
    		else
    		$property_code = "00".$property_set_id;
    		$property_val = "property_val".$property_set_id;
            $d = array(
            	'property_set_id' => $property_set_id,
            	'property_type' => 'goods',
            	'property_code' => $property_code,
            	'property_val' => $property_val,
            	'property_val_type' => 'text',
            	'is_condition' => 0,
            	'lastchanged' => date("Y-m-d H:i:s",time()),
                'property_val_title' => $property_val_title,
            	'property_val_desc' => $property_val_title,
            );

            $result = $this->db->insert('base_property_set', $d);
            if ($result !== true) {
                return $this->format_ret(-1, '', 'BASE_PROPERTY_SAVE_DETAIL_ERROR');
            }

        return array('status' => 1, 'message' => '');
    }

	function get_prop_data($goods_code,$type){
		$sql = "select property_val,property_val_title from base_property_set where property_type = 'goods'";
		$db_prop_map = ctx()->db->get_all($sql);
		$prop_map = array();
		foreach($db_prop_map as $sub_map){
			$prop_map[$sub_map['property_val']] = $sub_map['property_val_title'];
		}

		$sql = "select * from base_property where property_type = 'goods' and property_val_code = :property_val_code";
		$db_prop_row = ctx()->db->get_row($sql,array(':property_val_code'=>$goods_code));

		$html_arr = array();
		$html_arr[] = "<form id='form4'><ul>";
		foreach($prop_map as $property_val=>$_title){
			$_v = @$db_prop_row[$property_val];
			$html_arr[] = "<li><label>{$_title} :</label> <textarea name='prop_{$property_val}' style='width: 332px; height: 51px;'>{$_v}</textarea> </li>";
		}
		if($type=='goods'){
            $html_arr[] = '<li> <td><button id="form4_submit" class="button button-primary" type="button">保存</button>&nbsp;&nbsp;&nbsp;
<button class="button button-primary" type="reset">重置</button>&nbsp;&nbsp;&nbsp;
<input type="button" onclick="javascript:window.location=\'? app_act=prm/goods/do_list\';" value="返回" class="button button-primary bui-form-field" aria-disabled="false" aria-pressed="false">&nbsp;&nbsp;&nbsp; </li></ul></form>';
        }else{
            $html_arr[] = '<li> <td><button id="form4_submit" class="button button-primary" type="button">保存</button>&nbsp;&nbsp;&nbsp;
<button class="button button-primary" type="reset">重置</button>&nbsp;&nbsp;&nbsp;
<input type="button" onclick="javascript:window.location=\'? app_act=prm/goods/do_list_diy\';" value="返回" class="button button-primary bui-form-field" aria-disabled="false" aria-pressed="false">&nbsp;&nbsp;&nbsp; </li></ul></form>';
        }

		$html = join('',$html_arr);
		return $this->format_ret(1,$html);
	}

	function save_goods_prop($goods_code,$request){
              
		

		$prop_data_arr = array();
		foreach($request as $k=>$v){
			$prop_data_arr[$k] = urldecode($v);
		}
		$sql = "select property_val from base_property_set where property_type = 'goods'";
		$db_prop = ctx()->db->get_all($sql);
		$up = array();
		foreach($db_prop as $sub_prop){
			$property_val = $sub_prop['property_val'];
			$up[$property_val] = isset($prop_data_arr['prop_'.$property_val])?$prop_data_arr['prop_'.$property_val]:'';
		}
		$dup_update_fld = join(',',array_keys($up));
		$up['property_val_code'] = $goods_code;
		$up['property_type'] = 'goods';
		/*
		echo '<hr/>$up<xmp>'.var_export($up,true).'</xmp>';
		echo '<hr/>$dup_update_fld<xmp>'.var_export($dup_update_fld,true).'</xmp>';
		*/
		$ret = M('base_property')->insert_dup($up,'update',$dup_update_fld);
		//echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';die;
		return $ret;
	}
    
    /**
	 *
	 * 方法名       get_goods_prop_data
	 *
	 * 功能描述     获取产口的扩展属性
	 *
	 * @author      BaiSon PHP R&D
	 * @date        2015-07-28
	 */
    function get_goods_prop_data($goods_code){
		$sql = "select property_val,property_val_title from base_property_set where property_type = 'goods'";
		$db_prop_map = ctx()->db->get_all($sql);
		$prop_map = array();
		foreach($db_prop_map as $sub_map){
			$prop_map[$sub_map['property_val']] = $sub_map['property_val_title'];
		}

		$sql = "select * from base_property where property_type = 'goods' and property_val_code = :property_val_code";
		$db_prop_row = ctx()->db->get_row($sql,array(':property_val_code'=>$goods_code));
        $prop_arr = array();
		foreach($prop_map as $property_val => $_title){
			$prop_arr[$property_val] = @$db_prop_row[$property_val];
		}
		return $prop_arr;
	}
    /**
     * 获取扩展属性名称
     */
    function get_property_val($select = '*') {
        $sql = "SELECT {$select} FROM base_property_set WHERE property_val_title <> ''";
        $title = $this->db->get_all($sql);
        return $title;
    }
}


