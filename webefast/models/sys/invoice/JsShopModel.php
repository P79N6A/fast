<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_model('tb/TbModel');
require_lib('tb_xml', true);

class JsShopModel extends TbModel{
    function get_table() {
		return 'js_shop';
	}
    //删除店铺记录
    function delete_shop_config($id) {
        $ret = parent :: delete(array('p_id' => $id));
        return $ret;
    }
    //获取所有的配置店铺
    function get_shop(){
        $sql = "select Distinct a.shop_code,b.shop_name from js_shop a inner join base_shop b on a.shop_code = b.shop_code;";
        $shop_data = $this->db->get_all($sql);
        return $shop_data;
    }
}