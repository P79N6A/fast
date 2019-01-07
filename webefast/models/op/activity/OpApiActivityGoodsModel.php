<?php

require_model('tb/TbModel');

class OpApiActivityGoodsModel extends TbModel {

    function __construct() {
        parent::__construct('op_api_activity_goods');
    }

    function get_by_page($filter) {
        parent::get_by_page($filter);
    }

    function get_goods_by_shop_code($shop_code) {
        $params['shop_code'] = $shop_code;
        return $this->get_all($params);
    }

    function insert($data) {
        parent::insert($data);
    }

    function delete($where) {
        return parent::delete($where);
    }

}
