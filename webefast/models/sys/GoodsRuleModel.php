<?php

/**
 * 商品规约相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('sys');

class GoodsRuleModel extends TbModel {

    function get_table() {
        return 'sys_goods_rule';
    }

    /** 根据id返回sys_goods_rule表的记录
     * @param  array|string $ids
     * @return array
     */
    function get_by_ids($ids) {
        if(is_array($ids)) {
            $idstr = implode(",", $ids);
            $where = " goods_rule_id in($idstr)";
            return $this->get_all($where);
        } else {
            return $this->get_row(array('goods_rule_id' => $ids));
        }
        
    }

    /*
     * 修改纪录
     */

    function update($goods_rule, $goods_rule_id) {
        $status = $this->valid($goods_rule, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret = $this->get_row(array('goods_rule_id' => $goods_rule_id));
        if (isset($goods_rule['name']) && $goods_rule['name'] != $ret['data']['name']) {
            $ret1 = $this->is_exists($goods_rule['name'], 'name');
            if (!empty($ret1['data'])) {
                return $this->format_ret(SPEC_ALIAS_ERROR_UNIQUE_NAME);
            }
        }
        $ret = parent::update($goods_rule, array('goods_rule_id' => $goods_rule_id));
        return $ret;
    }

    /*
     * 服务器端验证
     */

    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['name']) || !valid_input($data['name'], 'required'))) {
            return "规格别名不允许为空";
        }
        
        return 1;
    }

}
