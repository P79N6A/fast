<?php
/**
 * 客户店铺相关业务—临时
 *
 * @author WangShouChong
 *
 */
require_model('tb/TbModel');

class ApikehuinfoModel extends TbModel {
    
    function get_table() {
        return 'osp_kehu';
    }
    /*
     * 插入客户信息
     */
    function addclient($request) {
        //解析request
        $client = get_array_vars($request, 
                    array( 
                            'kh_name',
                            'kh_place',
                            'kh_address',
                            'kh_tel',
                            'kh_email',
                            'kh_web',
                            'kh_itphone',
                            'kh_itname',
                            'kh_memo',
                            'kh_shopinfo'));
        $client['kh_code'] = uniqid();
        $client['kh_createuser'] = "2";
        $client['kh_createdate'] = date('Y-m-d H:i:s');
        $client['kh_updateuser'] = "2";
        $client['kh_updatedate'] = date('Y-m-d H:i:s');
        if($client['kh_name']==""){
            $retkh=$this -> format_ret("-1", "", '客户代码不能为空');
            exit_json_response($retkh);
        }
        $shopinfo=$client['kh_shopinfo'];
        unset($client['kh_shopinfo']); 
        $retkhisexist = $this->is_exists('osp_kehu',$client['kh_name'], 'kh_name');
        if ($retkhisexist['status'] > 0 && !empty($retkhisexist['data'])){
            //客户已经存在，判断下属店铺
            if(isset($shopinfo)){
                foreach ($shopinfo as $shopkey){
                    if($shopkey['sd_name']!=""){
                        $shopkey['sd_code'] = uniqid();
                        $shopkey['sd_kh_id']=$retkhisexist['data']['kh_id'];
                        $shopkey['sd_pt_id']='6';
                        $shopkey['sd_createuser']= "2";
                        $shopkey['sd_createdate'] = date('Y-m-d H:i:s');
                        $shopkey['sd_updateuser']= "2";
                        $shopkey['sd_updatedate'] = date('Y-m-d H:i:s');
                        $this->addshop($shopkey);
                    }
                }
            }
            $retkh=$this -> format_ret("1", $retkhisexist['kh_id'], 'insert_success');
            exit_json_response($retkh);
        }else{
            $data = $this -> db -> create_mapper('osp_kehu') -> insert($client);
            if ($data) {
                if ($this -> db -> dbtype == 0) {
                    $id = $this -> db -> insert_id();
                } else {
                    $id = $this -> db -> insert_id(get_oracle_seq("osp_kehu"));
                }
                $retkh=$this -> format_ret("1", $id, 'insert_success');
            } else {
                $retkh=$this -> format_ret("-1", '', 'insert_error');
            }
            //exit_json_response($retkh);
            //客户已经存在，判断下属店铺
            if(isset($shopinfo)){
                foreach ($shopinfo as $shopkey){
                    if($shopkey['sd_name']!=""){
                        $shopkey['sd_code'] = uniqid();
                        $shopkey['sd_kh_id']=$id;
                        $shopkey['sd_pt_id']='6';
                        $shopkey['sd_createuser']= "2";
                        $shopkey['sd_createdate'] = date('Y-m-d H:i:s');
                        $shopkey['sd_updateuser']= "2";
                        $shopkey['sd_updatedate'] = date('Y-m-d H:i:s');
                        $this->addshop($shopkey);
                    }
                }
            }
            
            exit_json_response($retkh);
        }
    }
    
    //添加店铺信息
    function addshop($shopinfo) {
        $retsdisexists = $this->is_exists('osp_shangdian',$shopinfo['sd_name'], 'sd_name');
        if ($retsdisexists['status'] > 0 && !empty($retsdisexists['data']))
        {
        }else{
            $data = $this -> db -> create_mapper('osp_shangdian') -> insert($shopinfo);
            if ($data) {
                if ($this -> db -> dbtype == 0) {
                    $id = $this -> db -> insert_id();
                } else {
                    $id = $this -> db -> insert_id(get_oracle_seq('osp_shangdian'));
                }
                $retsd=$this -> format_ret("1", $id, 'insert_success');
            } else {
                $retsd=$this -> format_ret("-1", '', 'insert_error');
            }
        }
    }
    
    private function is_exists($tbname,$value, $field_name = 'kh_name') {
        $data = $this ->db-> create_mapper($tbname) -> where(array($field_name => $value)) -> find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        return $this -> format_ret($ret_status, $data);
    }
    
    
    /*
     * 获取客户信息和下属店铺信息
     */
    function getclient($request) {
        $sql_main = "FROM {$this->table}  WHERE 1";
        $select = '*';
        $data = $this->get_page_from_sql($request, $sql_main, $select);
        $ret_status = $data ? 1 : 'op_no_data';
        if($ret_status=="1"){
           //通过客户获取所属店铺信息
            foreach($data['data'] as & $khinfo){
                $sql_main_sd="SELECT * FROM osp_shangdian WHERE sd_kh_id=:sd_kh_id "; 
                $sql_values_sd[':sd_kh_id'] = $khinfo['kh_id'];
                $data_sd=$this->db->get_all($sql_main_sd, $sql_values_sd);
                $khinfo['shopinfo']=$data_sd;
            }
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    
    /*
     * 获取商户信息、RDS信息、授权信息
     */
    function get_kehu_mode($kh_id) {
        $db = CTX()->db;
        $sql_value = array(':kh_id' => $kh_id);
        //基本信息
        $kehu_sql = "select * from osp_kehu where kh_id = :kh_id";
        $kehu_ret = $db->get_row($kehu_sql,$sql_value);
        //RDS信息
        $rds_sql = "select * from osp_aliyun_rds where kh_id = :kh_id";
        $rds_ret = $db->get_row($rds_sql,$sql_value);
        //授权信息
        $auth_sql = "select * from osp_productorder_auth where pra_kh_id = :kh_id";
        $auth_ret = $db->get_row($auth_sql,$sql_value);
        //店铺信息
        $shops_sql = "select sd_kh_id,sd_code,sd_name,sd_nick,sd_top_session,sd_isauth from osp_shangdian where sd_kh_id = :kh_id";
        $shops_ret = $db->get_all($shops_sql,$sql_value);
        return array(
            'kehu' => $kehu_ret,
            'rds' => $rds_ret,
            'auth' => $auth_ret,
            'shops' => $shops_ret
        );
    }
}
