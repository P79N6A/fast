<?php

require_model('tb/TbModel');

class RoleManagePriceModel extends TbModel {
    public $manage_price_name = array(
        'cost_price' => '成本价',
        'purchase_price' => '进货价',
    );

    function get_table() {
        return 'sys_role_manage_price';
    }

    function get_list_by_role($data) {
        $role_code = isset($data['role_code'])?$data['role_code']:'';
        if (empty($role_code)) {
                return $this->format_ret(OP_ERROR);
        }
        $sql = "select * from sys_role_manage_price where role_code = :role_code";
        $result = $this->db->get_all($sql,array("role_code"=>$data['role_code']));
        foreach ($result as &$res){
            $res['manage_price_name'] = $this->manage_price_name[$res['manage_code']];
            if($res['status'] == 1){
                $res['status_html'] = "<button class='status_btn' style ='background:#1695ca; color:#FFF; border-color:#1695ca;'>开启</button><button class='status_btn' onclick = changeType('".$res['manage_code']."',0)>关闭</button>";
            }
            if($res['status'] == 0){
                $res['status_html'] = "<button class='status_btn' onclick = changeType('".$res['manage_code']."',1)>开启</button><button class='status_btn' style ='background:#1695ca; color:#FFF; border-color:#1695ca;' >关闭</button>";
            }
        }
        return $result;
    }


    function update_status($data){
        if (!in_array($data['type'], array(0, 1))) {
            return $this->format_ret('error_params');
        }
        $ret = parent::update(array('status' => $data['type']), array('manage_code' => $data['manage_code'],'role_code' => $data['role_code']));
        return $ret;
    }

    function get_user_permission_price($price_type,$userid = ''){
        $arr_manage_price = load_model('sys/SysParamsModel')->get_val_by_code(array('manage_price'));
        $manage_price = isset($arr_manage_price['manage_price']) ? $arr_manage_price['manage_price'] : '';
        if($manage_price != 1){
            return $this->format_ret(1);
        }
        $user_id = CTX()->get_session('user_id');
        $user_id = empty($user_id) ? $userid : $user_id;

        $sql = "select role_code from sys_role as r inner join sys_user_role as u on u.role_id = r.role_id where u.user_id = :user_id";
        $role_code_arr = $this->db->get_all($sql,array(":user_id" => $user_id));
        if(empty($role_code_arr)){
            return $this->format_ret(-1);
        }
        $role_arr = array();
        foreach($role_code_arr as $row){
            $role_arr[]=$row['role_code'];
        }
        if(in_array('manage', $role_arr)){
            return $this->format_ret(1);
        }
        $role_code = implode("','", $role_arr);
        $sql_manage = "select status from sys_role_manage_price where role_code in('{$role_code}') and manage_code = :price_code";
        $status_value = $this->db->get_all($sql_manage,array(":price_code"=> $price_type));
        $status = -1;
        foreach ($status_value as $val){
            if($val['status'] == 1 ){
                $status = 1;
                break;
            }
        }
        return $this->format_ret($status,$status_value);
    }

    function update_active($active, $param_code){
        $this->begin_trans();
        try {
            if($active == 1){
                $insert = $this->handle_manage_price();
                if($insert['status'] < 0){
                    $this->rollback();
                    return $this->format_ret(-1,'','启动失败');
               }
            }
            $ret = load_model('sys/ParamsModel')->update_active($active, $param_code);
            if($ret['status'] < 0){
                $this->rollback();
                return $ret;
            }
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', '保存失败:' . $e->getMessage());
        }
    }

    function handle_manage_price(){
        $sql = "select role_code,role_name,role_id from sys_role";
        $role_all = $this->db->get_all($sql);
        $conf = require_conf('sys/price_profession');
        $data = array();
        $i = 0;
        foreach ($role_all as $role) {
            foreach ($conf as $c) {
                $data[$i]['status'] = $c['status'];
                $data[$i]['manage_code'] = $c['manage_code'];
                $data[$i]['desc'] = $c['desc'];
                $data[$i]['role_code'] = $role['role_code'];
                $i ++;
            }
        }
        $ret = $this->insert_multi($data, true);
        return $ret;
    }
}
