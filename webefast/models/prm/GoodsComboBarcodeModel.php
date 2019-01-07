<?php

/**
 * 套餐商品相关业务
 * @author dfr
 */
require_model('tb/TbModel');
require_lib('util/oms_util', true);
require_lang('prm');

class GoodsComboBarcodeModel extends TbModel {

    function get_table() {
        return 'goods_combo_barcode';
    }

    //保存barcode
    public function save($b) {
        //保存goods_combo_spec1，goods_combo_spec2表
        $spec1_code = explode(",", $b['spec1_code']);
        if(!empty($b['spec1'])){
            $ret = $this->save_spec1($b['goods_code'], $spec1_code);
        }
        $spec2_code = explode("'", $b['spec2_code']);
        if(!empty($b['spec2'])){
            $ret = $this->save_spec2($b['goods_code'], $spec1_code);
        }
        $ret = $this->save_borcode($b);
        $this->update_diy($b);
        return $ret;
    }

    function update_diy($b) {
        if ($b['goods_combo_id'] && $b['goods_code_old'] && $b['goods_code_old'] != $b['goods_code']) {
            $this->db->update('goods_combo_diy', array('p_goods_code' => $b['goods_code']), array('p_goods_code' => $b['goods_code_old']));
        }
        if(!empty($b['new_sku'])) {
            foreach($b['sku'] as $key => $val) {
                $ret = $this->db->update('goods_combo_diy', array('p_sku' => $b['new_sku'][$key]), array('p_sku' => $val));
            }
        }
        return $this->format_ret(1, '');
    }

    //保存条形码
    function save_borcode($b) {
        $edit_goods_code = $b['goods_code'];
        if (!empty($b['goods_combo_id'])) {
            $edit_goods_code = $b['goods_code_old'];
        }
        $sql = "select count(*) as cnt FROM goods_combo_barcode where goods_code = :goods_code ";
        $data = $this->db->get_row($sql, array('goods_code' => $edit_goods_code));
        

        $sql_mx = '';
        $spec1_arr = array();
        $spec2_arr = array();
        $barcode_arr = array();
        foreach ($b['barcode'] as $key => $row) {
         
            
            $arr = explode('_', $key);
            
            $spec1_arr[] = $arr[0];
            $spec2_arr[] = $arr[1];
            $barcode_arr[] = array(
                'price'=>$b['barcode_price'][$key],
                'sku'=> !empty($b['new_sku'][$key]) ? $b['new_sku'][$key] : $b['sku'][$key],
                'spec1_code'=>$arr[0],
                'spec2_code'=>$arr[1],
                'barcode'=>$row,
                'goods_code'=>$b['goods_code'],
            );
            
           // $sql_mx .= ",('" . $b['barcode_price'][$key] . "','" . $b['sku'][$key] . "','" . $arr[0] . "','" . $arr[1] . "','" . $row . "','" . $b['goods_code'] . "')";
        }
        if (!empty($sql_mx)) {
            $sql_mx = substr($sql_mx, 1);
        }
        if ($data[cnt] != 0) {
            $spec1_str = "'".implode("','", $spec1_arr)."'";
            $spec2_str = "'".implode("','", $spec2_arr)."'";
            $sql = "delete from goods_combo_barcode where goods_code ='{$edit_goods_code}' AND (spec1_code IN({$spec1_str}) AND spec2_code IN({$spec2_str})) ";
            $this->db->query($sql);
        }
     //   $sql = 'INSERT IGNORE INTO goods_combo_barcode (price,sku,spec1_code,spec2_code,barcode,goods_code) VALUES' . $sql_mx;
        $update_str = 'barcode = VALUES(barcode),goods_code = VALUES(goods_code),price = VALUES(price),sku = VALUES(sku)';
        $ret = $this->insert_multi_duplicate('goods_combo_barcode', $barcode_arr, $update_str);
       // $ret = $this->db->query($sql);
        if ($ret['status']<1) {
            return $this->format_ret("-1", '', 'insert_error');
        }

        
        
        return $ret;
    }

    function save_spec1($goods_code, $spec1_code_arr) {

        $sql = "select count(*) as cnt FROM goods_combo_spec1 where goods_code = :goods_code ";
        $data = $this->db->get_row($sql, array('goods_code' => $goods_code));
        if ($data[cnt] != 0) {
            $sql = "delete from goods_combo_spec1 where goods_code ='{$goods_code}'";
            $ret = $this->db->query($sql);
        }
        $sql_mx = '';
        foreach ($spec1_code_arr as $row) {
            $sql_mx .= ",('" . $row . "','" . $goods_code . "')";
        }
        if (!empty($sql_mx)) {
            $sql_mx = substr($sql_mx, 1);
        }
        $sql = 'INSERT ignore INTO goods_combo_spec1 (spec1_code,goods_code) VALUES' . $sql_mx;
        $ret = $this->db->query($sql);
        if ($ret) {
            $id = $this->db->insert_id();
            return $this->format_ret("1", $id, 'insert_success');
        } else {
            return $this->format_ret("-1", '', 'insert_error');
        }
        return $ret;
    }

    function get_barcode_info_by_sku($sku) {
        $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode, g.goods_name,if(b.price>0,b.price,g.price) as price  FROM goods_combo_barcode b
              INNER JOIN  goods_combo g ON b.goods_code=g.goods_code
                where b.sku=:sku ";
        $data = $this->db->get_row($sql, array(':sku' => $sku));
        filter_fk_name($data, array('spec1_code|spec1_code', 'spec2_code|spec2_code'));
        $data['spec2_name'] = $data['spec2_code_name'];
        $data['spec1_name'] = $data['spec1_code_name'];
        return $data;
    }

    function save_spec2($goods_code, $spec2_code_arr) {

        $sql = "select count(*) as cnt FROM goods_combo_spec2 where goods_code = :goods_code ";
        $data = $this->db->get_row($sql, array('goods_code' => $goods_code));
        if ($data[cnt] != 0) {
            $sql = "delete from goods_combo_spec2 where goods_code ='{$goods_code}'";
            $ret = $this->db->query($sql);
        }
        $sql_mx = '';
        foreach ($spec2_code_arr as $row) {
            $sql_mx .= ",('" . $row . "','" . $goods_code . "')";
        }
        if (!empty($sql_mx)) {
            $sql_mx = substr($sql_mx, 1);
        }
        $sql = 'INSERT ignore INTO goods_combo_spec2 (spec2_code,goods_code) VALUES' . $sql_mx;
        $ret = $this->db->query($sql);
        if ($ret) {
            $id = $this->db->insert_id();
            return $this->format_ret("1", $id, 'insert_success');
        } else {
            return $this->format_ret("-1", '', 'insert_error');
        }
        return $ret;
    }

    public function get_spec1($goods_code) {
        $sql = "select spec1_code FROM goods_combo_spec1 where goods_code = :goods_code ";
        $data = $this->db->get_all($sql, array('goods_code' => $goods_code));
        $arr = array();
        foreach ($data as $row) {
            $arr[] = $row['spec1_code'];
        }
        return $arr;
    }

    public function get_spec2($goods_code) {
        $sql = "select spec2_code FROM goods_combo_spec2 where goods_code = :goods_code ";
        $data = $this->db->get_all($sql, array('goods_code' => $goods_code));
        $arr = array();
        foreach ($data as $row) {
            $arr[] = $row['spec2_code'];
        }
        return $arr;
    }

    public function get_barcode($goods_code) {
        $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode, if(b.price>0,b.price,g.price) as price  FROM goods_combo_barcode b 
              INNER JOIN  goods_combo g ON b.goods_code=g.goods_code
                where b.goods_code=:goods_code ";
        $data = $this->db->get_all($sql, array(':goods_code' => $goods_code));
        foreach($data as &$value){
           $sql_goods = "select count(1) from goods_combo_diy where p_sku = '{$value['sku']}'";
           if($this->db->getOne($sql_goods) > 0){
                    $value['goods_exist'] = 1;
           }else{
                    $value['goods_exist'] = 0;
           }
        }
        filter_fk_name($data, array('spec1_code|spec1_code', 'spec2_code|spec2_code'));
        return $data;
    }
   
    function get_inv_by_sku($sku, $store_code) {
        $sql = "select d.num,i.stock_num,i.lock_num from  goods_combo_diy d "
                . "LEFT JOIN goods_inv i ON d.sku=i.sku "
                . "WHERE d.p_sku = :sku AND i.store_code=:store_code";
        $sql_values = array(':sku' => $sku, ':store_code' => $store_code);
        $data = $this->db->get_all($sql, $sql_values);
        $inv = array();
        //$inv =  array('available_mum'=>0,'stock_num'=>0);
        if (!empty($data)) {
            foreach ($data as $val) {
                if (empty($val['stock_num'])) {
                    $inv = array('available_mum' => 0, 'stock_num' => 0);
                    break;
                } else {
                    $available_mum = ceil(($val['stock_num'] - $val['lock_num']) / $val['num']);
                    $stock_num = ceil($val['stock_num'] / $val['num']);
                    if (((isset($inv['available_mum']) && $inv['available_mum'] > $available_mum) || !isset($inv['available_mum']))) {
                        $inv['available_mum'] = $available_mum;
                    }
                    if (((isset($inv['stock_num']) && $inv['stock_num'] > $available_mum) || !isset($inv['stock_num']))) {
                        $inv['stock_num'] = $stock_num;
                    }
                }
            }
        }
        if (!isset($inv['available_mum']) || $inv['available_mum'] < 0) {
            $inv['available_mum'] = 0;
        }
        if (!isset($inv['stock_num']) || $inv['stock_num'] < 0) {
            $inv['stock_num'] = 0;
        }
        return $inv;
    }

    //没停用barcord
    public function get_barcode_status($barcode) {
        $sql = "select b.* FROM goods_combo_barcode b 
				LEFT JOIN goods_combo c on c.goods_code = b.goods_code
				where  b.barcode = :barcode  and c.status = '1' ";
        $data = $this->db->get_row($sql, array('barcode' => $barcode));
        return $data;
    }

    //修改套餐价格
    function update_save($b) {
        $log_xq = '';
        $this->begin_trans();
        try {
            foreach ($b['barcord_price'] as $barcode => $price) {
                $sql = "select goods_combo_barcode_id,price,barcode from goods_combo_barcode where barcode= :barcode";
                $old_data = $this->db->get_row($sql, array('barcode' => $b['barcode_barcode'][$barcode]));
                $goods_combo_barcode_id = $old_data['goods_combo_barcode_id'];
                if ($old_data['price'] != $price) {
                    $log_xq.="套餐条形码为{$old_data['barcode']}的套餐价格由{$old_data['price']}改为{$price}，";
                }
                $r = $this->db->update('goods_combo_barcode', array('price' => $price), array('goods_combo_barcode_id' => $goods_combo_barcode_id));
                if ($r !== true) {
                    throw new Exception('保存失败');
                }
            }

            $this->commit();
            return array('status' => 1, 'message' => '更新成功', 'log_xq' => $log_xq);
        } catch (Exception $e) {
            $this->rollback();
            $log_xq = "套餐明细中套餐价格修改失败";
            return array('status' => -1, 'message' => $e->getMessage(), 'log_xq' => $log_xq);
        }
    }
    
    //套餐商品是否使用
    function check_combo_sku_use($combo_sku_arr) {
        if(empty($combo_sku_arr)) {
            return FALSE;
        }
        $sql_values = array();
        $sku_str = $this->arr_to_in_sql_value($combo_sku_arr, 'sombo_sku', $sql_values);
        $sql = "SELECT count(combo_sku) FROM oms_sell_record_detail WHERE combo_sku IN ({$sku_str}) ";
        $cun_num = $this->db->get_value($sql,$sql_values);
        if($cun_num > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

}
