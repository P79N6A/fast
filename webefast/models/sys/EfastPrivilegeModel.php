<?php
require_model('sys/PrivilegeModel');
class EfastPrivilegeModel extends PrivilegeModel {
    function get_menu_tree($check_priv=false,$code='') {
        $arr = array('goods_spec1','goods_spec2','open_multi_po');
        $arr_spec = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $res['goods_spec1_rename'] = isset($arr_spec['goods_spec1']) ? $arr_spec['goods_spec1'] : '';
        $res['goods_spec2_rename'] = isset($arr_spec['goods_spec2']) ? $arr_spec['goods_spec2'] : '';
        $res['open_multi_po'] = isset($arr_spec['open_multi_po']) ? (int)$arr_spec['open_multi_po'] : '';

        $login_type = CTX()->get_session('login_type');
        
        $menu_cote = parent::get_menu_tree($check_priv,$code);
        foreach($menu_cote as $k=>$sub_cote){
          $child1 = $sub_cote['_child'];
          foreach($child1 as $k1=>$sub_child1){
            $child2 = $sub_child1['_child'];
            foreach($child2 as $k2=>$sub_child2){
              if($sub_child2['action_id'] == '5010600'){
                $menu_cote[$k]['_child'][$k1]['_child'][$k2]['action_name'] = isset($res['goods_spec1_rename'])?'规格1('.$res['goods_spec1_rename'].')':'规格1';
              }
              if($sub_child2['action_id'] == '5010500'){
                $menu_cote[$k]['_child'][$k1]['_child'][$k2]['action_name'] = isset($res['goods_spec2_rename'])?'规格2('.$res['goods_spec2_rename'].')':'规格2';
              }
              if($sub_child2['action_id'] == '8040200' && ($res['open_multi_po'] === 1)){
                  unset($menu_cote[$k]['_child'][$k1]['_child'][$k2]);
              }
              if($sub_child2['action_id'] == '8040600'  && $res['open_multi_po'] === 0){
                  unset($menu_cote[$k]['_child'][$k1]['_child'][$k2]);
              }
              if($login_type == 2 && $sub_child2['action_id'] == '8080100') { // 分销商登录分销商品定义改为分销商品列表
                $menu_cote[$k]['_child'][$k1]['_child'][$k2]['action_name'] = '分销商品列表';
              }
            }
          }
        }
//        var_dump($menu_cote);exit;
        return $menu_cote;
    }
    function get_security_role_id(){
       $sql = " select role_id from  sys_role where role_code='security'";
       $role_id = $this->db->get_value($sql);
       return $role_id;
    }


}