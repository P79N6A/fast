<?php

require_model('tb/TbModel');

class GoodsComboOpModel extends TbModel {

    function set_split_combo(&$api_mx, &$barcode_arr) {
        $combo_sku_arr = array();
        $no_find = array();
        foreach ($api_mx as $key1 => $sub_mx) {
            $find_barcode = isset($barcode_arr[$sub_mx['goods_barcode']]) ? $barcode_arr[$sub_mx['goods_barcode']] : null;
            if (empty($find_barcode)) {
                $no_find[$key1] = $sub_mx;
                //return $this->format_ret(-50,'',"商品条形码{$sub_mx['goods_barcode']}不存在");
            }
        }
        foreach ($no_find as $key => $val) {
            //$data_barcode = load_model('prm/GoodsComboBarcodeModel')->get_barcode_status($val['goods_barcode']);

            $combo_info = $this->get_combo_info_by_barcode($val['goods_barcode']);
            $combo_sku_arr[$val['goods_barcode']] = $combo_info;
            //print_r($data_barcode);exit;
            if (!empty($combo_info['sku'])) {
                $data_diy = $this->get_combo_data_by_p_sku($combo_info['sku']);
                //print_r($data_diy);
                if ($data_diy) {
                    $cnt = count($data_diy);
                    $dy_total_price = 0;
                    $jichu_money = $val['avg_money'];
                    $dy_tj = 0;
                    $num = $val['num'];
                    $fx_amount = isset($val['payment']) && !empty($val['payment']) ? $val['payment'] : 0;
                    $fx_tj = 0;
                    foreach ($data_diy as $dy_k1 => $dy_v1) {
                        $dy_total_price += $dy_v1['price'] * $dy_v1['num'];
                    }
                    foreach ($data_diy as $dy_k => $dy_v) {
                        $dy_v['barcode'] = strtolower($dy_v['barcode']);
                        $val['goods_barcode'] = $dy_v['barcode'];
                        if(empty($dy_v['barcode'])){
                            return $this->format_ret(-1,'','套餐'.$val['goods_barcode']."  条码不能为空");
                        }
                        
                        $val['price'] = $dy_v['price'];
                        $val['num'] = $dy_v['num'] * $num;
                        if ($cnt == 1) {
                            $val['avg_money'] = $jichu_money;
                            $val['payment'] = $fx_amount;
                        } else {
                            if ($dy_k == $cnt - 1) {
                                $val['avg_money'] = $jichu_money - $dy_tj;
                                $val['payment'] = $fx_amount - $fx_tj;
                            } else {
                                //$val['avg_money'] = $data_barcode['data']['price']*(($dy_v['sell_price']*$dy_v['num'])/$dy_total_price);
                                $val['avg_money'] = $dy_total_price>0?round($jichu_money * (($dy_v['price'] * $dy_v['num']) / $dy_total_price),2):0;
                                $val['payment'] = $dy_total_price>0?round($fx_amount * (($dy_v['price'] * $dy_v['num']) / $dy_total_price),2):0;
                                $fx_tj += $val['payment'];
                                $dy_tj += $val['avg_money'];
                            }
                        }
                        $val['sku_properties'] = '';
                        $val['pic_path'] = '';
                        $this->set_list($api_mx, $val);
                        //$api_mx[] = $val;
                        $barcode_arr[$dy_v['barcode']]['goods_code'] = $dy_v['goods_code'];
                        $barcode_arr[$dy_v['barcode']]['spec1_code'] = $dy_v['spec1_code'];
                        $barcode_arr[$dy_v['barcode']]['spec2_code'] = $dy_v['spec2_code'];
                        $barcode_arr[$dy_v['barcode']]['sku'] = $dy_v['sku'];
                        $barcode_arr[$dy_v['barcode']]['barcode'] = $dy_v['barcode'];
                        
                        if(isset( $barcode_arr[$dy_v['barcode']]['combo_sku'] )){
                            $barcode_arr[$dy_v['barcode']]['combo_sku'] .=",".$combo_info['sku'];
                        }else{
                             $barcode_arr[$dy_v['barcode']]['combo_sku'] = $combo_info['sku'];
                        }
                        
                        
                    }
                    unset($api_mx[$key]);
                }
            }
        }
        return $this->format_ret(1,$combo_sku_arr);
    }

    function set_list(&$api_mx, $val) {
        $k = 1;
        foreach ($api_mx as $key1 => $sub_mx) {
            if ($sub_mx['goods_barcode'] == $val['goods_barcode']) {
                $k++;
                $api_mx[$key1]['combo_num'] = isset($api_mx[$key1]['combo_num']) ? $api_mx[$key1]['combo_num'] + $val['num'] : $val['num'];
                $api_mx[$key1]['num'] = $val['num'] + $api_mx[$key1]['num'];
                $api_mx[$key1]['avg_money'] = $val['avg_money'] + $api_mx[$key1]['avg_money'];
                $api_mx[$key1]['payment'] = $val['payment'] + $api_mx[$key1]['payment'];
                break;
            }
        }
        if ($k == 1) {
            $val['combo_num'] = $val['num'];
            $api_mx[] = $val;
        }
    }

    function set_refund_mx(&$barcode_data, $combo_data) {
        foreach ($combo_data as $val) {
           // $combo_barcode = $this->get_combo_barcode($val['barcode']);
            $val['barcode'] = strtolower($val['barcode']);
            $combo_sku = $this->get_combo_sku_by_barcode($val['barcode']);
            
            if (!empty($combo_sku)) {
                $this->set_combo_barocde($barcode_data, $combo_sku, $barcode_data[$val['barcode']]);
            }
        }
    }

    function get_combo_barcode($barcode) {
        $sql = "select p.sell_price,gb.barcode,d.num from goods_combo_diy d "
                . " INNER JOIN goods_combo_barcode b ON b.sku= d.p_sku "
                . " INNER JOIN base_goods p ON p.goods_code=d.goods_code "
                . " INNER JOIN goods_sku gb ON gb.sku=d.sku "
                . " where b.barcode = :barcode";
        $data = $this->db->get_all($sql, array(':barcode' => $barcode));
        return $data;
    }

    function set_combo_barocde(&$barcode_data, $combo_sku, $detail) {
 
        
        $combo_barcode = $this->get_combo_data_by_p_sku($combo_sku);
        foreach ($combo_barcode as $val) {
            $new_detail = $detail;

            $new_detail['num'] = $new_detail['num'] * $val['num'];
            $new_detail['goods_barcode'] = $val['barcode'];

            if (isset($barcode_data[$val['barcode']])) {
                $barcode_data[$val['barcode']]['num'] +=$new_detail['num'];
                //  $barcode_data[$val['barcode']]['refund_price'] +=$new_detail['refund_price'];
            } else {
                $barcode_data[$val['barcode']] = $new_detail;
            }
        }

        unset($barcode_data[$detail['goods_barcode']]);
    }
    
    //根据套餐条码查询对应sku
    function get_sku_by_combo_barcode($barcode) {
        $sql_value = array();
        $barcode_like = $this->arr_to_like_sql_value(array($barcode),'barcode',$sql_value);
        $sql = "SELECT sku FROM goods_combo_barcode WHERE {$barcode_like}";
        $sku = $this->db->get_all_col($sql,$sql_value);
        return $sku;        
    }


    private function get_combo_sku_by_barcode($barcode) {
        $sql = "select b.sku from goods_combo_barcode b"
                  . " INNER JOIN goods_combo c ON b.goods_code=c.goods_code "
                . " where b.barcode=:barcode AND  c.status=:status";
        $row = $this->db->get_row($sql, array(':barcode' => $barcode,':status'=>1));
        if (empty($row)) {
            return '';
        } else {
            return $row['sku'];
        }
    }
    private function get_combo_info_by_barcode($barcode) {
        $sql = "select  b.goods_code,b.spec1_code,b.spec2_code,b.sku  from goods_combo_barcode b"
                . " INNER JOIN goods_combo c ON b.goods_code=c.goods_code "
                . "where b.barcode=:barcode  AND  c.status=:status";
        $row = $this->db->get_row($sql, array(':barcode' => $barcode,':status'=>1));
        if (empty($row)) {
            return array();
        } else {
            return $row;
        }
    }
    private function get_combo_data_by_p_sku($p_sku) {
        $sql = "SELECT s.sku,d.num,d.price,s.goods_code,s.spec1_code,s.spec2_code,s.barcode from goods_combo_diy d  "
                . " INNER JOIN goods_sku s ON d.sku=s.sku "
                . "where d.p_sku=:p_sku";
        $data = $this->db->get_all($sql, array(':p_sku' => $p_sku));
        foreach ($data as &$val) {
            if (is_null($val['price'])) {
               $key_arr = array('sell_price');
               $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], $key_arr);
               $val['price'] = $sku_info['sell_price'];
            }
        }
        return $data;
    }

}
