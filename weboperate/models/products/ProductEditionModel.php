<?php

/**
 * 产品版本业务
 *
 * @author WangShouChong
 *
 */
require_model('tb/TbModel');
class ProductEditionModel extends TbModel {
    
    function get_table() {
        return 'osp_chanpin_version';
    }

    /*
     * 获取产品版本列表
     */
    function get_by_page($filter) {
                $sql_values = array();
		$sql_join = "";
		$sql_main = "FROM {$this->table} t $sql_join WHERE 1";
	   
		//关键字
		if (isset($filter['keyword']) && $filter['keyword']!='' ) {
                        $sql_main .= " AND (t.pv_code LIKE '%" .$filter['keyword'].
                                "%' or t.pv_name LIKE '%".$filter['keyword']."%')";
		}
                //所属产品条件
                if (isset($filter['pv_cp_id']) && $filter['pv_cp_id']!='' ) {
			$sql_main .= " AND (t.pv_cp_id =". $filter['pv_cp_id'].")";
		}
                
                //版本类型
                if (isset($filter['pv_type']) && $filter['pv_type']!='' ) {
			$sql_main .= " AND (t.pv_type =". $filter['pv_type'].")";
		}
                
		if(isset($filter['__sort']) && $filter['__sort'] != '' ){
			$filter['__sort_order'] = $filter['__sort_order'] =='' ? 'asc':$filter['__sort_order'];
			$sql_main .= ' order by '.trim($filter['__sort']).' '.$filter['__sort_order'];
		}
		
		$select = 't.*';
		
		$data =  $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
		$ret_status = OP_SUCCESS;
		$ret_data = $data;
                
                //处理关联代码表
                filter_fk_name($ret_data['data'], array('pv_createuser|osp_user_id','pv_cp_id|osp_chanpin','pv_fbr|osp_user_id'));
                
		return $this->format_ret($ret_status, $ret_data);
    }
    
    function get_by_id($id) {
                $params=array('pv_id'=>$id);
                $data = $this->create_mapper($this->table)->where($params)->find_by();
                $ret_status = $data ? 1 : 'op_no_data';
                //处理关联代码表
                filter_fk_name($data, array('pv_createuser|osp_user_id','pv_updateuser|osp_user_id','pv_cp_id|osp_chanpin','pv_fbr|osp_user_id_p'));
                
                //获取附件明细
                $sql_fjmx="SELECT * FROM osp_cpversion_annex WHERE pv_fj_pid=:pv_fj_pid "; 
                $sql_valuesmx[':pv_fj_pid'] = $id;

                $retfj=$this->db->get_all($sql_fjmx, $sql_valuesmx);
//                $path = CTX()->get_app_conf('file_upload_path');
//                $path=  str_replace('web/', '', $_SERVER['HTTP_REFERER']);
//                foreach($retfj as & $datafj){
//                    $datafj['pv_fj_path']=$path.'uploads/'.$datafj['pv_fj_path'];
//                }
                $data['fjmx']=$retfj;
                return $this->format_ret($ret_status, $data);
    }
    
    /*
     * 添加产品版本
     */
    function insert($prouctversion,$filelist) {
        $status = $this->valid($prouctversion);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->is_exists($prouctversion['pv_code']);
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret('code_is_exist');

        $ret = $this->is_exists($prouctversion['pv_code'], 'pv_code');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret('name_is_exist');
        
        //添加创建人和创建日期
        $prouctversion['pv_createuser']=CTX()->get_session("user_id");
        $prouctversion['pv_createdate']=date("Y-m-d h:i:sa");
        $prouctversion['pv_updateuser']=CTX()->get_session("user_id");
        $prouctversion['pv_updatedate']=date("Y-m-d h:i:sa");
                
        $ret = parent::insert($prouctversion);
        
        //保存附件明细
        $arrayfile= json_decode($filelist);
        if(!empty($arrayfile))
        {
            $prouctversion_fj=array();
            foreach ($arrayfile as $fileindexlist){
                $prouctversion_fj[]=array(
                    'pv_fj_path'=>$fileindexlist[0],
                    'pv_fj_name'=>$fileindexlist[1],
                    'pv_fj_pid'=>$ret['data']);
            }
        }
        $data = $this -> db -> create_mapper('osp_cpversion_annex') -> insert($prouctversion_fj);
        return $ret;
    }
    
    /*
     * 修改产品版本信息。
     */
    function update($prouctversion, $id) {
        $status = $this->valid($prouctversion, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->get_row(array('pv_id' => $id));
        if ($prouctversion['pv_name'] != $ret['data']['pv_name']) {
            $ret = $this->is_exists($prouctversion['pv_name'], 'pv_name');
            if ($ret['status'] > 0 && !empty($ret['data']))
                return $this->format_ret('name_is_exist');
        }
        
        //添加修改人和修改日期
        $product['pv_updateuser']=CTX()->get_session("user_id");
        $product['pv_updatedate']=date("Y-m-d h:i:sa");
        if($ret['data']['pv_createdate']==""){
            $product['pv_createuser']=CTX()->get_session("user_id");
            $product['pv_createdate']=date("Y-m-d h:i:sa");
        }
                
        $ret = parent::update($prouctversion, array('pv_id' => $id));
        return $ret;
    }
    
    
    /*
     * 服务器端验证提交的数据是否重复
     */
    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['pv_code']) || !valid_input($data['pv_code'], 'required')))
            return PV_ERROR_CODE;
        if (!isset($data['pv_name']) || !valid_input($data['pv_name'], 'required'))
            return PV_ERROR_CODE;
            return 1;
    }

    private function is_exists($value, $field_name = 'pv_code') {
        $ret = parent::get_row(array($field_name => $value));

        return $ret;
    }
}