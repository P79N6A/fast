<?php

/**
 * 基础数据—行政区域对照
 *
 * @author WangShouChong
 *
 */
require_model('tb/TbModel');
class AreaModel extends TbModel {
    
    function get_table() {
        return 'base_area';
    }
    
    //行政区域级别
    public $area_level = array(
        1 => '国家',
        2 => '省/直辖市',
        3 => '地区/市',
        4 => '县/市',
        5 => '乡/镇',
    );
    
    //获取区域信息详细
    function  get_by_id($id){
        $params = array('id' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        //处理行政区域类别(级别)
        $data['type_name']=$this->area_level[$data['type']];
        //获取平台区域对照信息
        $sql_main="select pt.pt_code, pt.pt_name,ptc.pac_pt_area_name,ptc.pac_base_area_id from osp_platform  pt left join"
                . " osp_platform_area ptc ON pt.pt_code=ptc.pac_pt_code and ptc.pac_base_area_id =:pac_base_area_id "
                . "order by ptc.pac_pt_area_name desc,pt.pt_code asc"; 
        $sql_values[':pac_base_area_id'] = $id;
        $ret=$this->db->get_all($sql_main, $sql_values);
        $data['pt_arealist']=$ret;
        $ret_status = $data ? 1 : 'op_no_data';
        return $this->format_ret($ret_status, $data);
    }
        
    //获取平台区域对照信息
    function get_arealist_by_pt($areaid){
        $sql_main="select pt.pt_code, pt.pt_name,ptc.pac_pt_area_name,ptc.pac_base_area_id from osp_platform  pt left join"
                . " osp_platform_area ptc ON pt.pt_code=ptc.pac_pt_code and ptc.pac_base_area_id =:pac_base_area_id"; 
        $sql_values[':pac_base_area_id'] = $areaid;
        $ret=$this->db->get_all($sql_main, $sql_values);
        return $ret;
    }
            
    function getAreaListById($areaid){
        if($areaid=='0'){
            //表示国家级别的
            $sql_main="SELECT id as id,parent_id as pid,name as text,case when type='5' then '1' else '0' end as leaf, id as code FROM {$this->table} where type='1' order by id"; 
            $sql_values=array();
        }else{
            $sql_main="SELECT id as id,parent_id as pid,name as text,case when type='5' then '1' else '0' end as leaf, id as code FROM {$this->table} WHERE parent_id=:parent_id order by id"; 
            $sql_values[':parent_id'] = $areaid;
        }
        $ret=$this->db->get_all($sql_main, $sql_values);

        return $ret;
    }
    
    //保存操作
    function save_pt_area($arealist){
        if(isset($arealist)){
            $sql = "select pac_id from osp_platform_area where pac_pt_code=:pac_pt_code and pac_base_area_id=:pac_base_area_id";
            $pac_id = $this->db->get_row($sql,array(':pac_pt_code'=>$arealist['pac_pt_code'],':pac_base_area_id'=>$arealist['pac_base_area_id']));
            if($pac_id>0){  //存在先删除掉
                $sql = "delete from osp_platform_area where pac_pt_code=:pac_pt_code and pac_base_area_id=:pac_base_area_id";
                $this->db->query($sql,array(':pac_pt_code'=>$arealist['pac_pt_code'],':pac_base_area_id'=>$arealist['pac_base_area_id']));
            }
            //插入操作
            $data = $this -> db -> create_mapper('osp_platform_area') -> insert($arealist);
            if ($data) {
                return $this -> format_ret("1", '', 'insert_success');
            } else {
                return $this -> format_ret("-1", '', 'insert_error');
            }
        }
    }
}
