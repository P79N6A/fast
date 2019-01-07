<?php
require_model('wrm/InventoryBaseModel');

/**
 * 在库库存管理
 *
 * @author huanghy
 *
 */
class InventoryActualModel extends InventoryBaseModel {
    /**
     * @var string $field_name 确认锁定库存字段名
     */
    private $field_name = 'sl';

    /**
     * 实例化父类
     * @author huanghy
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * 获取当前的确认锁定库存
     */
    function get($kc_info) {
        $kc_info = $this->get_kc_info($kc_info);
        foreach ($kc_info as $k => $sub_kc) {
            $t_sl2 = (int) $sub_kc['sl2'] < 0 ? 0 : $sub_kc['sl2'];
            $usable_sl = $sub_kc['sl']-$t_sl2 + $sub_kc['lock_num'];
            $usable_sl = (int) $usable_sl > 0 ? $usable_sl : 0;
            //usable_sl 实际可用数 = 在库数 - 锁定数 + 当前订单的锁定数
            $kc_info[$k]['usable_sl'] = $usable_sl;
        }
        return $kc_info;
    }

    /**
     * 增加确认锁定库存
     */
    function add($kc_info, $log = NULL) {
        return $this->add_base($kc_info, null, $this->field_name, $log);
    }

    /**
     * 释放确认锁定库存
     */
    function reduce($kc_info, $log = NULL) {
        return $this->reduce_base($kc_info, null, $this->field_name, $log);
    }

}