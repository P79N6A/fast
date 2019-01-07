<?php

class ViewUtilModel extends TbModel {
    /*
      public function __construct($table = '', $db = '') {
      parent::__construct($table);
      } */

    function append_mx_info_by_barcode($mx_data, $check_no_match_sku = 1) {
        $mx_map = $this->get_map_arr($mx_data, 'barcode');
        $barcode_list = "'" . join("','", array_keys($mx_map)) . "'";
        $sql = "select goods_code,spec1_code,spec2_code,sku,barcode from goods_sku where barcode in({$barcode_list})";
        $db_barcode = ctx()->db->get_all($sql);
        $barcode_map = $this->get_map_arr($db_barcode, 'barcode');
        foreach ($mx_data as $k => $sub_map) {
            $_find_info = isset($barcode_map[$sub_map['barcode']]) ? $barcode_map[$sub_map['barcode']] : '';
            if (empty($_find_info) && $check_no_match_sku == 1) {
                return $this->format_ret(-1, '', $sub_map['barcode'] . '找不到对应的SKU码');
            }
            $_find_info = array_merge($_find_info, $sub_map);
            $mx_data[$k] = $_find_info;
        }
        return $this->format_ret(1, $mx_data);
    }

    //单据明细 附上 goods_name,spec1_name,spec2_name
    function record_detail_append_goods_info($record_detail, $is_barcode = 0, $is_mx_only_sku = 0) {
        if (empty($record_detail)) {
            return $record_detail;
        }

//		$flds = "s.sku,g.goods_name,s.spec1_name,s.spec2_name";
//		if ($is_mx_only_sku == 1){
//			$flds .= ",sku.goods_code,sku.spec1_code,sku.spec2_code";
//		}
//		$sku_list = "'".join("','",array_unique($sku_arr))."'";
//		$sql = "SELECT
//					{$flds}
//				FROM
//					goods_sku s,
//					base_goods g,
//
//				WHERE
//					s.goods_code = g.goods_code
//				AND s.sku IN ($sku_list)";
//		$db_skus = ctx()->db->get_all($sql);
//
//		$sku_arr = array();
//		foreach($db_skus as $sub_skus){
//			$sku_arr[$sub_skus['sku']] = $sub_skus;
//		}
//
//		if ($is_barcode == 1){
//			$sql = "select sku,barcode from goods_sku where sku in($sku_list)";
//			$db_barcode = ctx()->db->get_all($sql);
//			$barcode_arr = array();
//			foreach($db_barcode as $sub_barcode){
//				$barcode_arr[$sub_barcode['sku']] = $sub_barcode['barcode'];
//			}
//		}

        foreach ($record_detail as $k => $sub_detail) {
            if (!isset($sub_detail['sku'])) {
                continue;
            }
            $key_arr = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode', 'goods_code', 'goods_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($sub_detail['sku'], $key_arr);
            $record_detail[$k] = array_merge($sub_detail, $sku_info);
//			if ($is_barcode == 1){
//				$record_detail[$k]['barcode'] = isset($barcode_arr[$sub_detail['sku']]) ? $barcode_arr[$sub_detail['sku']] : '';
//			}
//
            //$sku_row = isset($sku_arr[$sub_detail['sku']]) ? $sku_arr[$sub_detail['sku']] : array();
//			$record_detail[$k]['goods_name'] = isset($sku_row['goods_name']) ? $sku_row['goods_name'] : '';
//			$record_detail[$k]['spec1_name'] = isset($sku_row['spec1_name']) ? $sku_row['spec1_name'] : '';
//			$record_detail[$k]['spec2_name'] = isset($sku_row['spec2_name']) ? $sku_row['spec2_name'] : '';
//			if ($is_mx_only_sku == 1){
//				$record_detail[$k]['goods_code'] = isset($sku_row['goods_code']) ? $sku_row['goods_code'] : '';
//				$record_detail[$k]['spec1_code'] = isset($sku_row['spec1_code']) ? $sku_row['spec1_code'] : '';
//				$record_detail[$k]['spec2_code'] = isset($sku_row['spec2_code']) ? $sku_row['spec2_code'] : '';
//			}
        }

        return $record_detail;
    }

    //从省市区的ID中拼装个一个完整的地址
    function get_address_for_each($arr, $fld_each_el, $fld_addr) {
        $fld_each_el_arr = explode(',', $fld_each_el);
        $id_arr = array();
        foreach ($fld_each_el_arr as $tfld) {
            $id_arr[] = $arr[$tfld];
        }
        if (empty($id_arr)) {
            return '';
        }
        $sql = "select id,name from base_area where id in('" . join("','", $id_arr) . "')";
        $db_area = ctx()->db->get_all($sql);

        $area_arr = array();
        foreach ($db_area as $sub_area) {
            $area_arr[$sub_area['id']] = $sub_area['name'];
        }
        $addr_arr = array();
        foreach ($id_arr as $t_id) {
            if (isset($area_arr[$t_id])) {
                $addr_arr[] = $area_arr[$t_id];
            }
        }
        $addr_arr[] = $arr[$fld_addr];
        $addr_str = join(' ', $addr_arr);
        return $addr_str;
    }

    //从一个数组COPY指定KEY的数据到新数组
    function copy_arr_by_fld($arr, $fld, $muil = 0, $set_null = 0) {
        $fld_arr = explode(',', $fld);
        $new_arr = array();
        if ($muil == 0) {
            foreach ($fld_arr as $s_fld) {
                if ($set_null == 0) {
                    $new_arr[$s_fld] = isset($arr[$s_fld]) ? $arr[$s_fld] : null;
                } else {
                    $new_arr[$s_fld] = isset($arr[$s_fld]) ? $arr[$s_fld] : '';
                }
            }
        } else {
            foreach ($arr as $k => $sub_arr) {
                foreach ($fld_arr as $s_fld) {
                    if ($set_null == 0) {
                        $new_arr[$k][$s_fld] = isset($sub_arr[$s_fld]) ? $sub_arr[$s_fld] : null;
                    } else {
                        $new_arr[$k][$s_fld] = isset($sub_arr[$s_fld]) ? $sub_arr[$s_fld] : '';
                    }
                }
            }
        }
        return $new_arr;
    }

    //设置2维数据中新的元素为新的值
    function set_arr_el_val($arr, $append_info) {
        foreach ($arr as $k => $sub_arr) {
            foreach ($append_info as $kk => $vv) {
                $arr[$k][$kk] = $vv;
            }
        }
        return $arr;
    }

    //设置2维数据中新的元素的值为另一个元素的值
    function set_arr_el_val_by_cfg($arr, $cfg) {
        foreach ($arr as $k => $sub_arr) {
            foreach ($cfg as $old_fld => $new_fld) {
                $arr[$k][$new_fld] = $arr[$k][$old_fld];
            }
        }
        return $arr;
    }

    //转变2维数据中以指定元素为KEY
    function get_map_arr($arr, $flds, $muil = 0, $v_fld = '', $is_lower = 0) {
        $fld_arr = explode(',', $flds);
        $result = array();
        foreach ($arr as $sub_arr) {
            $ks = array();
            foreach ($fld_arr as $_fld) {
                $ks[] = ($is_lower > 0) ? strtolower($sub_arr[$_fld]) : $sub_arr[$_fld];
            }
            if (empty($v_fld)) {
                $_v = $sub_arr;
            } else {
                $_v = $sub_arr[$v_fld];
            }
            if ($muil == 1) {
                $result[join(',', $ks)][] = $_v;
            } else {
                $result[join(',', $ks)] = $_v;
            }
        }
        return $result;
    }

    //从2维数组中抽出指定元素的值
    function get_arr_val_by_key($arr, $key_name, $v_type = 'string', $ret_type = 'array') {
        $result = array();
        foreach ($arr as $sub_arr) {
            $_t = isset($sub_arr[$key_name]) ? $sub_arr[$key_name] : null;
            if (empty($_t)) {
                continue;
            }
            $result[] = $_t;
        }
        if ($ret_type != 'array') {
            $result = array_unique($result);
            if ($v_type == 'string') {
                $ret = "'" . join("','", $result) . "'";
            } else {
                $ret = join(",", $result);
            }
        } else {
            $ret = $result;
        }
        return $ret;
    }

}
