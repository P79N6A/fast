<?php

/**
 * 淘宝商品相关业务
 *
 * @author hunter
 *
 */
require_model('tb/TbModel');
//require_lang('api');
require_lib('util/taobao_util', true);
require_lib('tb_util', true);

class TbBaseItemCatsModel extends TbModel {

    function __construct() {
        parent::__construct('api_tb_itemcats', 'id');
    }

    /**
     * 下载分类（淘宝）
     */
    function dl_itemcats($shop_code) {

        $ret = load_model('base/ShopApiModel')->get_shop($shop_code);
        $parameter['app'] = $ret['data']['api']['app_key'];
        $parameter['secret'] = $ret['data']['api']['app_secret'];
        $parameter['session'] = $ret['data']['api']['session'];
        $parameter['parent_cid'] = 0;
        $data = taobao_itemcats_get($parameter);

        $this->delete(array());

        if (1 == $data['status'] && $data['data']['item_cats']) {
            foreach ($data['data']['item_cats']['item_cat'] as $_val) {
                $this->insert($_val);
            }
        }
        return TRUE;
    }

    /**
     * 获取分类 by  parent_cid
     * @param $parent_id
     */
    function get_itemcats_by_parent_id($parent_cid) {

        return $this->get_all(array('parent_cid' => $parent_cid));
    }

    /**
     * 获取分类
     * @param $cid
     * @return array
     */
    function get_itemcats_by_cid($cid) {
        return $this->get_row(array('cid' => $cid));
    }

    /**
     * 获取分类
     * @param $cid
     * @return array
     */
    function get_itemcats_by_cids($cids) {
        $sql = "select * from {$this->table} where cid in({$cids})";
        $data = $this->db->get_all($sql);
        return $this->format_ret(1, $data);
    }

}
