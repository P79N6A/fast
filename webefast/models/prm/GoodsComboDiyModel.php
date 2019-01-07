<?php

/**
 * 套餐商品明细相关业务
 * @author dfr
 */
require_model('tb/TbModel');
require_lib('util/oms_util', true);
require_lang('prm');

class GoodsComboDiyModel extends TbModel {

    function get_table() {
        return 'goods_combo_diy';
    }

    function get_by_id($id) {
        $data = $this->get_row(array('goods_combo_diy_id' => $id));
        return $data;
    }

    function get_by_all($p_sku) {
        $sql = "select r1.*,r2.barcode,r3.sell_price FROM goods_combo_diy r1
				INNER JOIN goods_sku r2 on r1.sku = r2.sku
				INNER JOIN base_goods r3 on r1.goods_code = r3.goods_code
		        where r1.p_sku = :p_sku  ";
        $arr = array(':p_sku' => $p_sku);
        $data = $this->db->get_all($sql, $arr);
        return $data;
    }

    public function is_exists($p_sku, $p_goods_code) {
        $sql = "select * FROM goods_combo_barcode  where goods_code = :goods_code and sku = :sku ";
        $arr = array(':sku' => $p_sku, ':goods_code' => $p_goods_code);
        $rs = $this->db->get_row($sql, $arr);
        return $rs;
    }

    //组合商品
    function get_diy_list($arr) {
        $sql = "select * FROM goods_combo_diy where 1 ";
        foreach ($arr as $key1 => $value1) {
            $key = substr($key1, 1);
            $sql .= " and {$key} = {$key1} ";
        }

        $rs = $this->db->get_all($sql, $arr);
        foreach ($rs as &$val) {
            $key_arr = array('barcode', 'spec1_code', 'spec2_code', 'spec1_name', 'spec2_name', 'goods_name', 'sell_price');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], $key_arr);
            $val['price'] = empty($val['price']) ? $sku_info['sell_price'] : $val['price'];
            $val = array_merge($val, $sku_info);
        }
        return $rs;
    }

    /**
     * 根据明细的sku和主单据id,判断明细是否已经存在
     * @param   int     $pid    主单据ID
     * @param   string  $sku    SKU编号
     * @return  boolean 存在返回true
     */
    private function is_detail_exists($p_sku, $p_goods_code, $sku) {
        $ret = $this->get_row(array(
            'p_sku' => $p_sku,
            'p_goods_code' => $p_goods_code,
            'sku' => $sku
        ));
        if ($ret['status'] == 1 && !empty($ret['data'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 新增套餐明细
     */
    public function add_detail_action($p_sku, $p_goods_code, $ary_details) {
        //判断主单据的pid是否存在
        $record = $this->is_exists($p_sku, $p_goods_code);
        $data = $this->db->get_row("select barcode from goods_combo_barcode where sku = :sku", array(':sku'=>$p_sku));
        $p_barcode = $data['barcode'];
        if (empty($record)) {
            return $this->format_ret(false, array(), '商品条码不存在!');
        }

        $this->begin_trans();
        try {
            foreach ($ary_details as $ary_detail) {
                if (!isset($ary_detail['num']) || $ary_detail['num'] == 0) {
                    continue;
                }
                $ary_detail['p_sku'] = $p_sku;
                $ary_detail['p_goods_code'] = $p_goods_code;
                //todo 此处参考价格取goods_price表中的sell_price字段, 如需开启到sku级价格需要进行判断
                $sku_info =load_model('goods/SkuCModel')->get_sku_info( $ary_detail['sku'],array('sell_price'));
                 $ary_detail['price'] = $sku_info['sell_price']; 
         
                //判断SKU是否已经存在
                $check = $this->is_detail_exists($p_sku, $p_goods_code, $ary_detail['sku']);
                if ($check) {
                    //更新明细数据
                    $ret = $this->update($ary_detail, array(
                        'p_sku' => $p_sku, 'p_goods_code' => $p_goods_code, 'sku' => $ary_detail['sku']
                    ));
                } else {
                    //插入明细数据
                    $ret = $this->insert($ary_detail);
                }
                if (1 != $ret['status']) {
                    return $ret;
                }
                //操作详情
                $operate_xq .= $ary_detail['barcode'].',';               
            }
            $this->commit();
            //增加操作日志
            $module = '商品'; //模块名称
            $yw_code = ''; //业务编码            
            $operate_type = '新增';
            $dr_goods = rtrim($operate_xq,',');
            $log_xq = '套餐条码:'.$p_barcode.'的商品套餐增加商品条形码为:'.$dr_goods.'的商品';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_type' => $operate_type, 'operate_xq' => $log_xq);
            $ret1 = load_model('sys/OperateLogModel')->insert($log);
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, array(), '数据库错误:' . $e->getMessage());
        }
    }

    //修改
    function update_save($diy_price, $spec1_code, $spec2_code,$diy_combo_diy_price) {
        $log_xq = '';
        $barcode = '';
        $this->begin_trans();
        try {
            foreach ($diy_price as $id => $num) {
                $data = $this->get_by_id($id);
                $p_sku = $data['data']['p_sku'];
                $barcode_arr = load_model('prm/GoodsComboBarcodeModel')->get_row(array("sku" => $p_sku));
                $barcode = isset($barcode_arr['data']['barcode']) ? $barcode_arr['data']['barcode'] : '';

                //添加系统日志
                $old_data = $this->db->get_row("select r1.goods_code,r3.spec1_code,r4.spec2_code,r3.spec1_name,r4.spec2_name,num,r1.price,r2.barcode as goods_barcode from goods_combo_diy r1 left join goods_barcode r2 on r1.sku = r2.sku left join base_spec1 r3 on r1.spec1_code = r3.spec1_code left join base_spec2 r4 on r1.spec2_code = r4.spec2_code  where r1.goods_combo_diy_id = :goods_combo_diy_id", array(':goods_combo_diy_id' => $id));                
                if ($old_data['spec1_code'] != $spec1_code[$id]) {
                    $spec1_data = $this->db->get_row("select spec1_name from base_spec1 where spec1_code = '{$spec1_code[$id]}'");
                    $log_xq.="套餐条码{$barcode}中商品条码为{$old_data['goods_barcode']}的商品颜色由{$old_data['spec1_name']}修改为{$spec1_data['spec1_name']}，";
                }
                if ($old_data['spec2_code'] != $spec2_code[$id]) {
                    $spec2_data = $this->db->get_row("select spec2_name from base_spec2 where spec2_code = '{$spec2_code[$id]}'");
                    $log_xq.="套餐条码{$barcode}中商品条码为{$old_data['goods_barcode']}的商品尺寸由{$old_data['spec2_name']}修改为{$spec2_data['spec2_name']}，";
                }
                if ($old_data['num'] != $num) {
                    $log_xq.="套餐条码{$barcode}中商品条码为{$old_data['goods_barcode']}的商品数量由{$old_data['num']}修改为{$num}，";
                }
                if ($old_data['price'] != $diy_combo_diy_price[$id]) {
                    $log_xq.="套餐条码{$barcode}中商品条码为{$old_data['goods_barcode']}的商品吊牌价由{$old_data['price']}修改为{$diy_combo_diy_price[$id]}，";
                }
                //$sku = $data['data']['goods_code'].$spec1_code[$id].$spec2_code[$id];	
                $r = $this->db->update('goods_combo_diy', array('num' => $num, 'spec1_code' => $spec1_code[$id],'price' => $diy_combo_diy_price[$id], 'spec2_code' => $spec2_code[$id], 'sku' => $data['data']['goods_code'] . $spec1_code[$id] . $spec2_code[$id]), array('goods_combo_diy_id' => $id));
                if ($r !== true) {
                    throw new Exception('保存失败');
                }
            }
            $this->commit();
            return array('status' => 1, 'message' => '更新成功', 'log_xq' => $log_xq);
        } catch (Exception $e) {
            $this->rollback();
            //return array('status'=>-1, 'message'=>$e->getMessage());
            $log_xq = "套餐明细中商品信息修改失败，";
            if ($barcode <> '') {
                $err = '可能是套餐条形码' . $barcode . '明细下，有重复的条码';
                $log_xq.=$err;
            }
            return array('status' => -1, 'message' => '更新失败,' . $err, 'log_xq' => $log_xq);
        }
    }

    //删除组合商品
    function del_diy($goods_combo_diy_id, $p_goods_code, $p_sku) {        
        /*
          $used = $this->is_used_by_id($p_goods_code,$p_sku);
          if($used){
          return $this->format_ret(-1,array(),'已经在业务系统中使用，不能删除！');
          } */
        //删除组合表  获取条码
        $data = $this->db->get_row("select barcode from goods_combo_barcode where sku = '{$p_sku}'");
        $p_barcode = $data['barcode'];
        $barcode = $this->db->get_row("select r2.barcode from goods_combo_diy r1 left join goods_sku r2 on r1.sku = r2.sku where goods_combo_diy_id=:goods_combo_diy_id ",array(':goods_combo_diy_id'=>$goods_combo_diy_id));
        $data = $this->db->create_mapper("goods_combo_diy")->delete(array('goods_combo_diy_id' => $goods_combo_diy_id));
        if ($data) {
            //增加操作日志
            $module = '商品'; //模块名称
            $yw_code = ''; //业务编码            
            $operate_type = '删除';           
            $log_xq = '套餐条码:'.$p_barcode.'的商品套餐中删除商品条形码为:'.$barcode['barcode'].'的商品';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_type' => $operate_type, 'operate_xq' => $log_xq);
            if ($barcode['barcode'] != '') {                
                $ret1 = load_model('sys/OperateLogModel')->insert($log);
            }
            return $this->format_ret("1", $data, 'delete_success');
        } else {
            return $this->format_ret("-1", '', 'delete_error');
        }
    }

    function import($goods_code, $file) {
        set_time_limit(0);
        $data = $this->read_diy_import($file);
        $no_combo = array();
        $no_barcode = array();
        $exist_barcode = array();
        //套餐下的所有规格
        $combo_barcode_ret = load_model('prm/GoodsComboBarcodeModel')->get_barcode($goods_code);
        $combo_barcode = array();
        foreach ($combo_barcode_ret as $ret_row) {
            $combo_barcode[$ret_row['barcode']] = $ret_row;
        }
        $error_msg = "";
        foreach ($data as $row) {
            if (empty($row['combo_barcode'])) {
                $error_msg .= "套餐条形码不能为空,";
                continue;
            }
            if (empty($row['barcode'])) {
                $error_msg .= "商品条形码不能为空,";
                continue;
            }
            if (empty($row['num']) || $row['num'] <= 0) {
                $error_msg .= "套餐条形码{$row['combo_barcode']}中的{$row['barcode']}商品数量必须大于0,";
                continue;
            }
            if (in_array($row['combo_barcode'], $no_combo)) {
                continue;
            }
            if (empty($combo_barcode[$row['combo_barcode']])) {
                $error_msg .= "套餐条形码{$row['combo_barcode']}套餐{$goods_code}中不存在,";
                $no_combo[] = $row['combo_barcode'];
                continue;
            }
            //校验商品条码是否存在
            if (in_array($row['barcode'], $no_barcode)) {
                continue;
            }
            if (!isset($exist_barcode[$row['barcode']])) {
                $goods_sku = load_model('prm/SkuModel')->get_row(array("barcode" => $row['barcode']));
                $exist_barcode[$row['barcode']] = $goods_sku['data'];
            }

            if (empty($exist_barcode[$row['barcode']])) {
                $error_msg .= "商品条形码{$row['barcode']}系统不存在,";
                $no_barcode[] = $row['barcode'];
                continue;
            }
            if (!isset($row['price']) || $row['price'] == 0) {
                $goods_sku = load_model('prm/SkuModel')->get_row(array("barcode" => $row['barcode']));
                $row['price'] = $goods_sku['data']['price'];
                if ($row['price'] == 0) {
                    $goods = load_model('prm/GoodsModel')->get_row(array("goods_code" => $goods_sku['goods_code']));
                    $row['price'] = $goods['data']['sell_price'];
                }
            }
            $combo_row = $combo_barcode[$row['combo_barcode']];
            $sku_row = $exist_barcode[$row['barcode']];
            //插入套餐明细表
            $diy_row = array(
                'goods_code' => $sku_row['goods_code'],
                'spec1_code' => $sku_row['spec1_code'],
                'spec2_code' => $sku_row['spec2_code'],
                'sku' => $sku_row['sku'],
                'num' => $row['num'],
                'price' => $row['price'],
                'p_goods_code' => $combo_row['goods_code'],
                'p_sku' => $combo_row['sku'],
                'add_time' => date('Y-m-d H:i:s'),
            );
            $this->insert_dup($diy_row, 'UPDATE', 'num');
        }
        if (!empty($error_msg)) {
            $file_name = create_import_fail_files($error_msg, 'goods_combo_diy_import_fail');
//            $msg .= "部分导入失败，失败信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" > 下载 </a>";
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $msg .= "部分导入失败，失败信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";

            return $this->format_ret(-1, '', $msg);
        } else {
            return $this->format_ret(1, '');
        }
    }

    function read_diy_import($file) {
        //读文件***********************
        $start_line = 1;
        $file = fopen($file, "r");
        $i = 0;
        $header = array();
        $file_str = '';
        $data_arr = array();
        $trans = array('combo_barcode' => 0, 'barcode' => 1, 'num' => 2, 'price' => 3);
        while (!feof($file)) {

            $row = fgetcsv($file);
            if (!empty($row)) {
                if ($i >= $start_line) {
                    foreach ($trans as $k => $v) {
                        $trans_row[$k] = trim($row[$v]);
                    }
                    $data_arr[] = $trans_row;
                }
            }
            $i++;
        }
        fclose($file);
        return $data_arr;
    }

    function import_upload() {
        set_time_limit(0);
        $files = array();
        $url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/";

        $fileInput = 'fileData';
        $dir = ROOT_PATH . 'webefast/uploads/';
        $type = $_POST['type'];

        $isExceedSize = false;
        $files_name_arr = array($fileInput);
        $is_max = 0;
        $is_file_type = 0;
        $file_type = array('csv', 'xlsx', 'xls');
        $upload_max_filesize = 2097152;
        foreach ($files_name_arr as $k => $v) {
            $pic = $_FILES[$v];
            if (!isset($pic['tmp_name']) || empty($pic['tmp_name'])) {
                $is_max = 1;
                continue;
            }
            $file_ext = get_file_extension($pic['name']);
            if (!in_array($file_ext, $file_type)) {
                $is_file_type = 1;
                continue;
            }
            $isExceedSize = $pic['size'] > $upload_max_filesize;
            if (!$isExceedSize) {
                if (file_exists($dir . $pic['name'])) {
                    @unlink($dir . $pic['name']);
                }
                // 解决中文文件名乱码问题
                $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
                $result = move_uploaded_file($pic['tmp_name'], $dir . $new_file_name);
                if (true == $result && ('xlsx' == $file_ext || 'xls' == $file_ext)) {
                    $result = excel2csv($dir . $new_file_name, $file_ext);
                    $new_file_name = str_replace('.' . $file_ext, '.csv', $new_file_name);
                }
            }
        }

        if ($is_max) {
            $ret = array(
                'status' => 0,
                'type' => $type,
                'name' => $_FILES[$fileInput]['name'],
                'msg' => str_replace('{0}', substr(ini_get('upload_max_filesize'), 0, -1) * 1024, lang('upload_msg_maxSize'))
            );
        } else if ($is_file_type) {
            $ret = array(
                'status' => 0,
                'type' => $type,
                'name' => $_FILES[$fileInput]['name'],
                'msg' => str_replace('{0}', implode(',', $file_type), lang('upload_msg_ext'))
            );
        } else if (!$isExceedSize && $result) {
            $ret = array(
                'status' => 1,
                'type' => $type,
                'name' => $_FILES[$fileInput]['name'],
                'url' => $dir . $new_file_name
            );
        } else if ($isExceedSize) {
            $ret = array(
                'status' => 0,
                'type' => $type,
                'msg' => str_replace('{0}', $upload_max_filesize / 1024, lang('upload_msg_maxSize'))
            );
        } else {
            $ret = array(
                'status' => 0,
                'type' => $type,
                'msg' => "未知错误！" . $result
            );
        }
        return $ret;
    }

}
