<?php

/**
 * 商品SKU相关业务
 */
require_model('tb/TbModel');
require_model('api/BaseSkuModel');

class SkuModel extends TbModel {

    function get_table() {
        return 'goods_sku';
    }

    //根据SKU信息获取到商品的规格信息个规格ID
    function get_spec_by_sku($sku) {
        $ret = $this->get_row(array('sku' => $sku));
        if (true == $ret['status']) {
            $return = array(
                'spec1_id' => isset($ret['data']['spec1_id']) ? $ret['data']['spec1_id'] : '',
                'spec2_id' => isset($ret['data']['spec2_id']) ? $ret['data']['spec2_id'] : '',
                'spec1_code' => isset($ret['data']['spec1_code']) ? $ret['data']['spec1_code'] : '',
                'spec2_code' => isset($ret['data']['spec2_code']) ? $ret['data']['spec2_code'] : '',
                'goods_id' => isset($ret['data']['goods_id']) ? $ret['data']['goods_id'] : '',
                'goods_code' => isset($ret['data']['goods_code']) ? $ret['data']['goods_code'] : '',
            );
        } else {
            //SKU没有查到相应的数据
            $return = array();
        }
        return $return;
    }

    /**
     * 判断是否存在
     * @param $value
     * @param string $field_name
     * @return array
     */
    function is_exists($value, $field_name = 'sku') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    /**
     * 添加新纪录
     */
    function insert($data) {
        $ret = parent::insert($data);
        return $ret;
    }

    /**
     * 生成sku 目前自动建档使用
     * @param $data array(0=>'goods_code', 'spec1_code', 'spec2_code','sku')
     */
    function _save_sku($data) {
        if (!$data) {
            return false;
        }
        foreach ($data as $_val) {
            if (!isset($_val['sku']) || '' == $_val['sku']) {
                $_val['sku'] = $_val['goods_code'] . $_val['spec1_code'] . $_val['spec2_code'];
            }

            $check_exist = $this->is_exists($_val['sku'], 'sku');
            if ($check_exist['data']) {
                //回写api_base_sku表
                if (isset($_val['base_sku_id']) && '' != $_val['base_sku_id']) {
                    $this->writebarck_api_base_sku($check_exist['data']['sku_id'], $_val['base_sku_id']);
                }
                continue;
            }

            $insert_data = array(
                'goods_code' => $_val['goods_code'],
                'sku' => $_val['sku'],
                'spec1_code' => $_val['spec1_code'],
                'spec2_code' => $_val['spec2_code'],
                'price' => $_val['price']
            );

            $ret = $this->insert($insert_data);
            //回写api_base_sku表
            if (isset($_val['base_sku_id']) && '' != $_val['base_sku_id']) {
                $this->writebarck_api_base_sku($ret['data'], $_val['base_sku_id']);
            }
        }
    }

    /**
     * 回写api_base_sku表
     * @param $goods_sku_id
     * @param $api_sku_id
     */
    function writebarck_api_base_sku($goods_sku_id, $api_sku_id) {
        $mdl_base_sku = new BaseSkuModel();
        $mdl_base_sku->update(array('goods_sku_id' => $goods_sku_id), array('sku_id' => $api_sku_id));
    }

    /**
     *
     * 方法名                               api_goods_sku_update
     *
     * 功能描述                           更新商品明细信息
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-13
     * @param       array $param
     *              array(
     *                  必填: 'goods_code', 'spec1_code', 'spec2_code', 'barcode'
     *                  可选: 'weight', 'price', 'gb_code'
     *                 )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    public function api_goods_sku_update($param) {
        //必选字段【说明：i=>代码数据检测类型为数字型  s=>代表数据检测类弄为字符串型】
        $key_required = array(
            's' => array('goods_code', 'spec1_code', 'spec2_code')
        );
        $sku_array = array();
        //验证必选字段是否为空并提取必选字段数据
        $ret_required = valid_assign_array($param, $key_required, $sku_array, TRUE);
        if (TRUE == $ret_required['status']) {
            //可选字段
            $key_option = array(
                's' => array('gb_code'),
                'i' => array('weight', 'price')
            );
            $arr_option = array();
            //提取可选字段中已赋值数据
            $ret_option = valid_assign_array($param, $key_option, $arr_option);

            //合并数据
            $sku_array = array_merge($sku_array, $arr_option);

            unset($param);

            $goods_code = $sku_array['goods_code'];
            $spec1_code = $sku_array['spec1_code'];
            $spec2_code = $sku_array['spec2_code'];

            //检查客户传入的产品是否存在
            $goods = load_model('prm/GoodsModel')->get_by_goods_code($goods_code);
            if (1 != $goods['status']) {
                return $this->format_ret("-10002", array('goods_code' => $goods_code), "API_RETURN_MESSAGE_10002");
            }

            //检查客户传入的规格1是否存在
            $spec1 = load_model('prm/Spec1Model')->get_by_code($spec1_code);
            if (1 != $spec1['status']) {
                return $this->format_ret("-10002", array('spec1_code' => $spec1_code), "API_RETURN_MESSAGE_10002");
            }

            //检查客户传入的规格2是否存在
            $spec2 = load_model('prm/Spec2Model')->get_by_code($spec2_code);
            if (1 != $spec2['status']) {
                return $this->format_ret("-10002", array('spec2_code' => $spec2_code), "API_RETURN_MESSAGE_10002");
            }

            //因为有三表操作作故开启事务
            $this->begin_trans();
            try {
                //检测规格1，不存在则添加
//	            $good_spec1_mod = load_model('prm/GoodsSpec1Model');
//	            $ret = $good_spec1_mod->is_exists(array('goods_code' => $goods_code, 'spec1_code' => $spec1_code));
//	            if (1 != $ret['status']) {
//	                $ret = $good_spec1_mod->save($goods_code, $spec1_code);
//	                if (1 != $ret['status']) {
//	                    throw new Exception(lang('API_SPEC1_CODE_UPDATE_FAILD'), -1);
//	                }
//	            }
//	            //检测规格2，不存在则添加
//	            $good_spec2_mod = load_model('prm/GoodsSpec2Model');
//	            $ret = $good_spec2_mod->is_exists(array('goods_code' => $goods_code, 'spec2_code' => $spec2_code));
//	            if (1 != $ret['status']) {
//	                $ret = $good_spec2_mod->save($goods_code, $spec2_code);
//	                if (1 != $ret['status']) {
//	                    throw new Exception(lang('API_SPEC2_CODE_UPDATE_FAILD'), -1);
//	                }
//	            }
                //检查商品sku是否存在
                $sku_code = $goods_code . $spec1_code . $spec2_code;
                $sku = load_model('prm/SkuModel')->is_exists($sku_code);
                $r = true;
                if (1 == $sku['status']) {
                    if (!empty($arr_option)) {
                        $r = $this->db->update('goods_sku', $arr_option, array('sku_id' => $sku['data']['sku_id']));
                    }
                } else {
                    //检查“商品sku”表中goods_code|spec1_code|spec2_code组合唯一键是否存在
                    $filter = array('goods_code' => $goods_code, 'spec1_code' => $spec1_code, 'spec2_code' => $spec2_code);
                    $ret = load_model('prm/SkuModel')->check_exists_by_condition($filter, 'goods_sku');
                    if (1 == $ret['status']) {
                        throw new Exception(lang('API_CODE_SPEC1_SPEC2_UNIQUE'), -10004);
                    } else {
                        $sku = array(
                            'goods_code' => $sku_array['goods_code'], 'spec1_code' => $sku_array['spec1_code'],
                            'spec2_code' => $sku_array['spec2_code'], 'sku' => $sku_code,
                            'gb_code' => $sku_array['gb_code'], 'weight' => $sku_array['weight'],
                            'price' => $sku_array['price']
                        );
                        $r = $this->db->insert('goods_sku', $sku);
                    }
                }
                if (true !== $r) {
                    $ret = $this->format_ret("-1", '', "update_error");
                    throw new Exception(lang('update_error'), -1);
                }
                //以前操作均成功，提交事务
                $this->commit();
                return $this->format_ret("1", '', "update_success");
            } catch (Exception $e) {
                //异常处理，回滚事务
                $this->rollback();
                return array('status' => $e->getCode(), 'message' => $e->getMessage());
            }
        } else {
            return $this->format_ret("-10001", $param, "API_RETURN_MESSAGE_10001");
        }
    }

    function update($data, $where) {
        return parent::update($data, $where);
    }

    function get_spec_by_goods_code($code, $spec_type = 1) {
        $spec_arr = array(
            '1' => 'spec1_code,spec1_name',
            '2' => 'spec2_code,spec2_name',
        );
        $sql = "select distinct {$spec_arr[$spec_type]} from  goods_sku where goods_code=:goods_code";
        $data = $this->db->get_all($sql, array(':goods_code' => $code));

        return $this->format_ret(1, $data);
    }

    /**
     * 获取商品重量
     * @param array $sku_arr sku集合
     * @return array 数据集
     */
    public function get_goods_weight($sku_arr) {
        $sql_values = array();
        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
        $sql = "SELECT gs.sku,gs.weight AS sku_weight,bg.weight AS goods_weight 
                FROM {$this->table} AS gs INNER JOIN base_goods AS bg ON gs.goods_code=bg.goods_code
                WHERE gs.sku IN({$sku_str})";
        $weight_data = $this->db->get_all($sql, $sql_values);

        $weight_arr = array();
        foreach ($weight_data as $row) {
            $sku_weight = (float) $row['sku_weight'];
            $goods_weight = (float) $row['goods_weight'];
            $weight = 0;
            if (!empty($sku_weight)) {
                $weight = $sku_weight;
            } else if (!empty($goods_weight)) {
                $weight = $goods_weight;
            }
            $weight_arr[$row['sku']] = $weight / 1000;
        }
        return $weight_arr;
    }

    /**
     * 转单\库存同步-条码识别（系统条形码->国标码->子条码）
     * @param array/string $barcode 条码集合
     * @return array 返回合并后的条码数据和各类条码数据
     */
    function convert_barcode($barcode) {
        $barcode = is_array($barcode) ? $barcode : array($barcode);
        $obj_util = load_model('util/ViewUtilModel');

        //系统条码、国标码、子条码对应的查询sql，按识别顺序
        $barcode_type = array(
            'barcode' => "SELECT goods_code,sku,barcode,spec1_code,spec2_code FROM goods_sku WHERE barcode IS NOT NULL AND barcode<>'' AND barcode IN",
            'gb_code' => "SELECT goods_code,sku,barcode,spec1_code,spec2_code,gb_code FROM goods_sku WHERE gb_code IS NOT NULL AND gb_code<>'' AND gb_code IN",
            'barcode_child' => "SELECT bc.goods_code,bc.sku,gs.barcode,bc.spec1_code,bc.spec2_code,bc.barcode AS barcode_child FROM goods_barcode_child bc
                INNER JOIN goods_sku gs ON bc.sku=gs.sku WHERE bc.barcode IS NOT NULL AND bc.barcode<>'' AND bc.barcode IN"
        );
        $data = array(); //全部数据
        $barcode_data = array(); //系统条形码数据
        $gb_code_data = array(); //国标码数据
        $barcode_child_data = array(); //子条码数据
        foreach ($barcode_type as $key => $val) {
            if (empty($barcode)) {
                continue;
            }
            $temp = $key . '_data';
            //查询barcode对应的sku数据
            $sql_values = array();
            $bar_str = $this->arr_to_in_sql_value($barcode, 'barcode', $sql_values);
            $sql = $val . "({$bar_str})";
            $sku_arr = $this->db->get_all($sql, $sql_values);
            if (empty($sku_arr)) {
                continue;
            }

            //转换barcode为小写
            array_walk($sku_arr, function(&$val) use($key, &$data) {
                $val['barcode'] = strtolower($val['barcode']);
                $val[$key] = strtolower($val[$key]);
                $data[$val[$key]] = $val; //所有识别出的条码数据
            });

            //条码数据，以$key(平台条码)作为二维数组$sku_arr的键
            $$temp = $obj_util->get_map_arr($sku_arr, $key);

            //非最后转换的条码，需要获取未转换数据
            if ($key != 'barcode_child') {
                $barcode_exists = array_column($sku_arr, $key);
                $barcode = array_diff($barcode, $barcode_exists);
            }
        }

        //返回合并后的条码数据和各类条码数据
        return array('data' => $data, 'barcode_data' => $barcode_data, 'gb_data' => $gb_code_data, 'child_data' => $barcode_child_data);
    }

    /**
     * 条码扫描-条码识别（系统条形码->国标码->子条码->条码识别方案）
     * @param string $barcode 扫描条码
     * @return array 返回扫描条码数据
     */
    public function convert_scan_barcode($barcode, $is_convert_rule = 1, $rule_priority = 1) {
        if (empty($barcode)) {
            return array();
        }
        $barcode_type = array('barcode', 'gb_code', 'barcode_child'); //按识别顺序
        if ($is_convert_rule == 1) {
            $barcode_type[] = 'barcode_rule';
        }
        $data = array();
        foreach ($barcode_type as $type) {
            if ($type == 'barcode_rule') {
                $ret = load_model('prm/GoodsBarcodeIdentifyRuleModel')->yanzheng($barcode, $rule_priority);
                if ($ret['status'] === TRUE) {
                    $data = get_array_vars($ret['data'], array('goods_code', 'sku', 'barcode', 'spec1_code', 'spec2_code'));
                }
                break;
            }
            $sql = 'SELECT gs.goods_code,gs.sku,gs.barcode,gs.spec1_code,gs.spec2_code FROM goods_sku AS gs';
            if ($type == 'barcode_child') {
                $sql .= " INNER JOIN goods_barcode_child AS bc ON bc.sku=gs.sku WHERE bc.barcode=:{$type}";
            } else {
                $sql .= " WHERE gs.{$type}=:{$type}";
            }
            $ret = $this->db->get_row($sql, array(":{$type}" => $barcode));
            if (!empty($ret)) {
                $data = $ret;
                break;
            }
        }
        if (empty($data['sku'])) {
            $data = array();
        }
        return $data;
    }

}
