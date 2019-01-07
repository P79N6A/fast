<?php

/**
 * 订单库存相关业务
 *
 * @author huanghy
 */
require_model('tb/TbModel');

abstract class InventoryBaseModel extends TbModel {
    /**
     * 实例化父类
     * @author huanghy
     */
    function __construct() {
        parent::__construct();
    }

    /*
     * 是否影响库存
     */
    public function is_change_stock($store_code) {
        return true;
    }

    /** 批量获取库存数
     * @param string $kc_info[] = array('store_code'=>xx,'goods_code'=>xx,'spec1_code'=>xx,'spec2_code'=>xx)
     */
    function get_kc_info($kc_info) {
        if (empty($kc_info)) {
            return array();
        }
        $arr_el = $this->get_unique_arrel($kc_info, 'store_code,goods_code,spec1_code,spec2_code');
        $sql = "SELECT
					store_code,
					goods_code,
					spec1_code,
					spec2_code,
					sl,
					sl2
				FROM
					oms_store_goods
				WHERE
					store_code IN ({ $arr_el [ 'store_code' ]})
				AND goods_code IN ({ $arr_el [ 'goods_code' ]})
				AND spec1_code IN ({ $arr_el [ 'spec1_code' ]})
				AND spec2_code IN ({ $arr_el [ 'spec2_code' ]})";

        $db_kc = $this->db->getAll($sql);
        $efast_kc_arr = array();
        foreach ($db_kc as $sub_kc) {
            $ks = "{$sub_kc['store_code']},{$sub_kc['goods_code']},{$sub_kc['spec1_code']},{$sub_kc['spec2_code']}";
            $sub_kc['sl'] = (int) $sub_kc['sl'] < 0 ? 0 : $sub_kc['sl'];
            $sub_kc['sl2'] = (int) $sub_kc['sl2'] < 0 ? 0 : $sub_kc['sl2'];
            $efast_kc_arr[$ks] = $sub_kc;
        }
        $ret = array();
        foreach ($kc_info as $k => $sub_info) {
            $ks = "{$sub_info['store_code']},{$sub_info['goods_code']},{$sub_info['spec1_code']},{$sub_info['spec2_code']}";
            $find_row = $efast_kc_arr[$ks];
            $ret[$ks] = array(
                'store_code' => $sub_info['store_code'],
                'goods_code' => $sub_info['goods_code'],
                'spec1_code' => $sub_info['spec1_code'],
                'spec2_code' => $sub_info['spec2_code'],
                'sl' => (int) $find_row['sl'],
                'sl2' => (int) $find_row['sl2'],
            );
        }
        return $ret;
    }

    /*
     * 在库存数组中添加 barcode,goods_sn,color_code,size_code 及处理变化后的数量
     */
    function kc_info_process($kc_info, $efast_kc_arr, $modi_field_name = 'sl', $mode = 'add') {
        if (empty($kc_info)) {
            return array();
        }
        $arr_el = $this->get_unique_arrel($kc_info, 'goods_code,spec1_code,spec2_code');
        $sql = "SELECT
					goods_code,
					spec1_code,
					spec2_code,
					barcode
				FROM
					goods_sku
				WHERE
					goods_code IN ({ $arr_el [ 'goods_code' ]})
				AND spec1_code IN ({ $arr_el [ 'spec1_code' ]})
				AND spec2_code IN ({ $arr_el [ 'spec2_code' ]})";
        $barcode_arr = $this->db_get_all_map($sql, array(), array('key' => 'goods_code,spec1_code,spec2_code'));

        foreach ($kc_info as $k => $sub_info) {
            $ks = "{$sub_info['goods_code']},{$sub_info['spec1_code']},{$sub_info['spec2_code']}";
            $ks2 = "{$sub_info['store_code']}," . $ks;
            $sub_info['barcode'] = @$barcode_arr[$ks];
            $find_efast_kc = $efast_kc_arr[$ks2];
            $find_efast_kc = isset($find_efast_kc) ? $find_efast_kc : array();
            $sub_info['modi_' . $modi_field_name] = $find_efast_kc[$modi_field_name]+$sub_info['num'];
            if ($modi_field_name == 'sl') {
                if ($mode == 'add') {
                    $sub_info['modi_sl'] = $find_efast_kc['sl']+$sub_info['num'];
                } else {
                    $sub_info['modi_sl'] = $find_efast_kc['sl']-$sub_info['num'];
                }
            } else {
                $sub_info['modi_sl'] = $find_efast_kc['sl'];
            }
            $kc_info[$k] = $sub_info;
        }
        return $kc_info;
    }

    public function add_base($kc_info, $efast_kc_arr = null, $field_name = 'sl', $log = NULL) {
        return $this->add_reduce_kc($kc_info, null, $field_name, 'add', $log);
    }

    public function reduce_base($kc_info, $efast_kc_arr = null, $field_name = 'sl', $log = NULL) {
        return $this->add_reduce_kc($kc_info, null, $field_name, 'reduce', $log);
    }

    /**
     * 增加 or 减少(通用方法)
     * @param string $info array('store_code'=>1,'goods_code'=>1,'spec1_code'=>1,'spec2_code'=>1,'num'=>1)
     * @param string $field_name 更新的字段，包含sl,sl1,sl2,sl4
     * @param array  $log 日志  [必要属性:order_type,order_sn,desc]
     */
    public function add_reduce_kc($kc_info, $efast_kc_arr = null, $field_name = 'sl', $mode = 'add', $log = NULL) {
        if (empty($efast_kc_arr)) {
            $efast_kc_arr = $this->get_kc_info($kc_info);
        }
        foreach ($kc_info as $sub_kc) {
            $t_num = (int) $sub_kc['num'];
            $t_store_code = (string) $sub_kc['store_code'];
            $t_goods_code = (string) $sub_kc['goods_code'];
            $t_spec1_code = (string) $sub_kc['spec1_code'];
            $t_spec2_code = (string) $sub_kc['spec2_code'];
            $ks = "{$sub_kc['store_code']},{$sub_kc['goods_code']},{$sub_kc['spec1_code']},{$sub_kc['spec2_code']}";
            $find_efast_kc_row = $efast_kc_arr[$ks];
            if (!isset($find_efast_kc_row) && $t_store_code <> '' && $t_goods_code <> '' && $t_spec1_code <> '' && $t_spec2_code <> '') {
                $sql = "INSERT IGNORE INTO oms_store_goods (
							store_code,
							goods_code,
							spec1_code,
							spec2_code ,{ $field_name }
						)
						VALUES
							(
								'{$t_store_code}',
								'{$t_goods_code}',
								'{$t_spec1_code}',
								'{$t_spec2_code}',
								'{$t_num}'
							)";
                $this->db->query($sql);
                continue;
            }
            if ($t_num != 0) {
                if ($field_name == 'sl2' && $t_num < 0) {
                    $t_num = $t_num * -1;
                    $sql = "UPDATE oms_store_goods
							SET sl2 =
							IF (
								sl2 -{ $t_num }< 0,
								0,
								sl2 -{ $t_num }
							)
							WHERE
								store_code = '{ $t_store_code }'
							AND goods_code = '{ $t_goods_code }'
							AND spec1_code = '{ $t_spec1_code }'
							AND spec2_code = '{ $t_spec2_code }'";

                } else {
                    $sql = "UPDATE oms_store_goods
								SET { $field_name } = { $field_name } + { $t_num }
								WHERE
									store_code = '{ $t_store_code }'
								AND goods_code = '{ $t_goods_code }'
								AND spec1_code = '{ $t_spec1_code }'
								AND spec2_code = '{ $t_spec2_code }'";
                }
                $this->db->query($sql);
            }
        }

        //生成库存流水账
        $this->write_inventory_log($log['order_type'], $log['order_sn'], $kc_info, $efast_kc_arr, $field_name, $log['desc']);
    }

    /**
     * 批量记录库存流水账
     * @param string       $order_type     订单类型（单据数据表明）
     * @param string       $order_sn       单据编号
     * @param int(11)      $modi_field_name        sl:实际库存 sl2确认锁定 sl1 配货锁
     * @param int(11)      $num            库存变化数
     * @param string       $desc           日志描述
     */
    function write_inventory_log($order_type, $order_sn, $kc_info, $efast_kc_arr, $modi_field_name, $desc) {
        $kc_info_goods = $this->kc_info_process($kc_info, $efast_kc_arr, $modi_field_name);
        $ins_arr = array();
        $add_time = date("Y-m-d H:i:s");
        foreach ($kc_info_goods as $sub_kc) {
            //没库存变化的，不要记录
            if ((int) $sub_kc['num'] == 0) {
                continue;
            }
            $ins_data = array();
            $ins_data['order_type'] = $order_type;
            $ins_data['order_sn'] = $order_sn;
            $ins_data['store_code'] = $sub_kc['store_code'];
            $ins_data['goods_code'] = $sub_kc['goods_code'];
            $ins_data['spec1_code'] = $sub_kc['spec1_code'];
            $ins_data['spec2_code'] = $sub_kc['spec2_code'];
            $ins_data['size_code'] = $sub_kc['size_code'];
            $ins_data['barcode'] = $sub_kc['barcode'];
            $ins_data['kc_type'] = $modi_field_name;
            $ins_data['change_num'] = abs($sub_kc['num']);
            $ins_data['kc_new_num'] = $sub_kc['modi_' . $modi_field_name];
            $ins_data['sl'] = $sub_kc['modi_sl'];
            $ins_data['add_time'] = $add_time;
            $ins_data['desc'] = addslashes($desc);
            $ins_arr[] = "('" . join("','", $ins_data) . "')";
        }
        if (empty($ins_arr)) {
            return;
        }
        $sql = "INSERT INTO oms_inventory_log(order_type,order_sn,barcode,goods_code,goods_sn,spec1_code,color_code,spec2_code,size_code,store_code,kc_type,change_num,kc_new_num,sl,add_time,`desc`) VALUES " . join(',', $ins_arr);
        $this->db->query($sql);
        return;
    }

}