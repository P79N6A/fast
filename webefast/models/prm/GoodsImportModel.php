<?php

/**
 * 规格1相关业务
 *
 * @author dfr
 *
 */
require_lib('comm_util', true);
require_model('tb/TbModel');
require_lang('prm');
set_time_limit(0);
class GoodsImportModel extends TbModel {

    private $property_set_arr = array();

    //导入规格1
    function import_base_spec1($file) {
        //读文件***********************
        $start_line = 0;
        $file = fopen($file, "r");
        $i = 0;
        $header = array();
        $data = array();
        $key_idex = 0;
        $sql_mx = '';
        $s1l_rep = "";
        $file_str = '';
        $data_arr = array();
        while (!feof($file)) {
            if ($i >= $start_line) {
                $row = fgetcsv($file);


                if (!empty($row)) {
                    $data_arr[] = $row;
                    foreach ($row as $key => $value) {

                        $_new_row[$key] = iconv('GBK', 'UTF-8', strip_tags(addslashes($row[$key])));
                    }
                    $s1l_rep .= ",'" . $_new_row[0] . "'";
                    $i++;
                }
                $sql_mx .= ",('" . implode("','", $_new_row) . "')";
                $key_idex++;
                /*
                  if(!empty($key_arr)){
                  foreach($key_arr as $k=>$n_k){
                  $n_row[$n_k]=$row[$k];
                  }
                  }else{
                  $data[] = $row;
                  }
                 */
            } else {
                $header[] = fgetcsv($file);
            }

            $i++;
        }
        print_r($data_arr);
        fclose($file);
        //***********************
        $s1l_rep = substr($s1l_rep, 1);
        $s1l_rep1 = "(" . $s1l_rep . ")";
        print_r($row);
        print_r("sss");
        exit;
        //查询是否有重复
        $sql2 = "select spec1_code from base_spec1 where spec1_code in " . $s1l_rep1;
        $rs = $this->db->get_all($sql2);
        //print_r($rs);
        exit;
        foreach ($rs as $v) {
            foreach ($_new_row as $k1 => $v1) {
                // print_r($v);
                print_r($v1);
                if ($v['spec1_code'] == $v1[0]) {
                    $file_str.= "\t" . $v1[0] . ',' . "\t" . $v1[1] . ',' . "\t" . $v1[1] . ',' . "\t 已存在 \n";
                }
            }
        }
        echo $file_str;
        exit;
        if ($file_str <> '') {
            $header_str = iconv("utf-8", 'gbk', "代码,名称,说明,原因  \n");
            $this->save_csv($header_str, $file_str);
        }
        exit;

        //print_r($header);


        $sql_mx = substr($sql_mx, 1);

        //code相同忽略ignore
        $is_filter_repeat = true;
        $sql = 'INSERT ' . ($is_filter_repeat ? 'ignore' : '') . ' INTO  base_spec1 ' . '(spec1_code,spec1_name,remark) VALUES' . $sql_mx . ";";
        //echo $sql;
        $ret = $this->db->query($sql);
        if ($ret) {
            $id = $this->db->insert_id();
            $ret = array(
                'status' => '1',
                'data' => $id,
                'message' => "导入成功！"
            );
        } else {
            $ret = array(
                'status' => '-1',
                'data' => '',
                'message' => "导入失败！"
            );
        }
        return $ret;
    }

    //导入商品混合数据导入
    function import_base_goods_barcode_property($file, $type = 'name') {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);
        try {
            $this->begin_trans();
            $csv_data = $this->read_goods_barcode_property_csv($file, $type);
            $arr['goods_arr'] = array();
            $arr['barcode_arr'] = array();
            $arr['spce1_arr'] = array();
            $arr['spce2_arr'] = array();
            $arr['brand_arr'] = array();
            $arr['season_arr'] = array();
            $arr['year_arr'] = array();
            $arr['category_arr'] = array();
            $arr['property_arr'] = array();
            $arr['sku_arr'] = array();
            $msg_arr = array();
            foreach ($csv_data as $k => $row) {
                $csv_data[$k]['goods_code']=str_replace("\n","",$row['goods_code']);
                $csv_data[$k]['barcode']=str_replace("\n","",$row['barcode']);
                if ($type == 'name') {
                    $csv_data[$k]['spec1_name']=str_replace("\n","",$row['spec1_name']);
                    $csv_data[$k]['spec2_name']=str_replace("\n","",$row['spec2_name']);
                } else {
                    $csv_data[$k]['spec1_code']=str_replace("\n","",$row['spec1_code']);
                    $csv_data[$k]['spec2_code']=str_replace("\n","",$row['spec2_code']);
                }
            }
            foreach ($csv_data as $k => $row) {
                if (!empty($row['goods_code']) || !empty($row['goods_name']) || !empty($row['category_name']) || !empty($row['brand_name']) || !empty($row['barcode'])) {
                    $msg = $this->set_row_arr($arr, $row);
                    if (!empty($msg)) {
                        $line = $k + 3;
                        $msg_arr[] = '第' . $line . "行" . $msg;
                    }
                }
            }
            if (!empty($arr['barcode_arr'])) {
                $check_barcode = $this->check_is_barcode($arr['barcode_arr']);
                //导入的商品条码
                $barcode_arr = $arr['barcode_arr'];
                foreach ($check_barcode as $key => $value) {
                    unset($barcode_arr[$check_barcode[$key]]);
                }                
                if (!empty($check_barcode)) {
                    $msg_arr[] = "已经存在条码" . implode("，", $check_barcode);
                }
            }

            //设置基础档案
            $this->set_goods_other_base($arr, $type);
       
            //校验规格不存在的商品编码
            $fail_goods = array();
            $success_num = 0;
            foreach ($csv_data as $k => $row) {
                $this->set_goods_barcode_arr($arr, $row, $type);
                
                if (!empty($arr['barcode_arr'][$row['barcode']])) {
                    $barcode_row = $arr['barcode_arr'][$row['barcode']];
                        
                    if ((empty($barcode_row['spec1_code']) && $barcode_row['spec1_code'] !== '0') || empty($barcode_row['spec1_name']) || (empty($barcode_row['spec2_code']) && $barcode_row['spec2_code'] !== '0') || empty($barcode_row['spec2_name'])) {

                        unset($csv_data[$k]);
//                        unset($arr['goods_arr'][$row['goods_code']]);
                        unset($arr['barcode_arr'][$row['barcode']]);                       
                        unset($barcode_arr[$row['barcode']]);
                        $fail_goods[] = $row['goods_code'];
                    }                  
                }                
            }
  
            if (!empty($fail_goods)) {
                $fail_goods = array_unique($fail_goods);
                $msg_arr[] = "商品编码" . implode(',', $fail_goods) . "的规格信息在系统不存在";
            }

            $this->check_sku_barcode($arr);

            $goods_arr = array_values($arr['goods_arr']);
            if (!empty($goods_arr)) {
                $this->insert_multi_duplicate('base_goods', $goods_arr, $goods_arr);
            }

            if (!empty($arr['property_arr'])) {
                $this->save_property_val($arr['property_arr']);
            }

            if (!empty($arr['barcode_arr'])) {
                $result1 = array();
                $result2 = array();
                foreach ($arr['barcode_arr'] as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        if (isset($vv) && $vv != '') {
                            $result1[$kk] = $vv;
                            continue;
                        }
                    }
                    $result2[$k] = $result1;
                    unset($result1);
                }
                $update_barcode = " barcode=VALUES(barcode) ";
                $this->insert_multi_duplicate('goods_barcode', $result2, $update_barcode);
                $this->insert_multi_duplicate('goods_sku', $result2, $update_barcode);
                $this->update_lastchanged($result2);
            }
            $this->commit();
        } catch (Exception $ex) {
            $this->rollback();
            return $this->format_ret(-1, '', $ex->getMessage());
        }
        if (!empty($barcode_arr)) {
            //系统操作日志
            if ($type == 'name') {
                $operate_xq = '混合数据导入（按规格名称）'; //操作详情
            }else{
                $operate_xq = '混合数据导入（按规格代码）'; //操作详情
            }            
            $yw_code = ''; //业务编码          
            $module = '商品'; //模块名称
            $operate_type = '导入';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_xq' => $operate_xq, 'operate_type' => $operate_type);
            load_model('sys/OperateLogModel')->insert($log);
        } 
        if (empty($msg_arr)) {           
            return $this->format_ret(1);
        } else {
            if (!empty($msg_arr)) {
                $file_name = $this->create_import_fail_files($msg_arr, 'goods_mix_import_fail');
//                $msg .= "部分导入失败，失败信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" > 下载 </a>";
                $url = set_download_csv_url($file_name,array('export_name'=>'error'));
                $msg = "部分导入失败，失败信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";

                return array('status' => '-1',
                    'data' => '',
                    'message' => $msg
                );
            }
        }

//        print_r($data);
//        exit;
        $barcord_arr = array();
        $err_barcorde = '';
        $err_goods = '';
        $err_msg = '';
        if (!empty($data['err'])) {
            $err_goods = join(",", $data['err']);
        }
        $is_exist_goods_name = array();
        foreach ($data['chushi_data'] as $k1 => $v1) {
            if ($v1[17] <> '') {
                $i = 0;
                foreach ($data['chushi_data'] as $k2 => $v2) {
                    if ($v2[17] == $v1[17]) {
                        $i++;
                    }
                    if ($i >= 2) {

                        $repeat_arr[$v1[17]] = $v1[17];

                        break;
                    }
                }
            }
            //商品混合导入，如果商品名称一样，但是商品编码不一样，进行报错提醒
//          if (in_array($v1[1], $is_exist_goods_name)) {
//              if (array_search($v1[1], $is_exist_goods_name) != $v1[0]){
//                  $err_msg .= '<br/>商品名称为:'.$v1[1].'的商品编码不一致！';
//              }
//          } else {
//              $is_exist_goods_name[$v1[0]] = $v1[1];
//          }


            $barcord_arr[$k1] = array($v1[0], $v1[15], $v1[16], $v1[17], $v1[10], $v1[9], $v1[18]);
        }
        if ($err_msg) {
            return array('status' => '-1',
                'data' => '',
                'message' => $err_msg
            );
        }

        /*
          foreach($data['data_good_arr'] as $k1 => $v1){
          if($v1[17] <> '' ){
          foreach($repeat_arr as $k2 => $v2){
          if($v1[17] <>  $k2){
          unset($data['data_good_arr'][$k1]);
          }
          }
          }
          } */


        /*
          foreach($data['data_good_arr'] as $k1 => $v1){
          if($v1[17] <> '' ){
          $i = 0;
          foreach($data['data_good_arr'] as $k2 => $v2){
          if($v2[17] == $v1[17]){
          $i++;
          }
          if($i >= 2){
          $err_barcorde .= $v1[17].',';
          $repeat_arr[$v1[17]] = $v1[17];

          break;
          }
          }

          if($i <= 1){
          $barcord_arr[$k1] =  array($v1[0],$v1[15],$v1[16],$v1[17]) ;
          }
          if($i >= 2){
          unset($data['data_good_arr'][$k1]);
          }

          }else{
          $barcord_arr[$k1] = array($v1[0],$v1[15],$v1[16],$v1[17]);
          }

          } */

        $data['data_barcord_arr'] = $barcord_arr;

        $arr_property = array();
        $property_all = array();
        $data_property = array();

        //转换扩展属性格式
        $property_num = 27;
        $property_str = 'property_val';
        foreach ($data['data_good_arr'] as $k => $v) {
            $j = 1;
            $property['property_val_code'] = $v[0];
            $property['property_type'] = 'goods';
            for ($i = 18; $i <= $property_num; $i++) {
                $property[$property_str . $j] = $v[$i];

                $j++;
            }
            $property_all[$v[0]] = $property;
            $goods_arr[] = $v[0];
        }

        $data_property = array('property_all' => $property_all,
            'goods_arr' => $goods_arr
        );

        //转换格式
        foreach ($data['data_good_arr'] as $k => $v) {
            foreach ($v as $k1 => $v1) {
                if ($k1 > 14) {
                    unset($data['data_good_arr'][$k][$k1]);
                }
            }
        }
        /*
          foreach($data['data_barcord_arr'] as $k => $v){

          $data['data_barcord_arr'][$k] = array($v[0],$v[15],$v[16],$v[17]);

          } */
        //print_r($data);exit;
        //导入商品
        $ret_goods = $this->import_base_goods_c($data);
        //print_r($ret_goods);
        //导入规格
        $ret_spec = $this->good_spec_c_hunhe($data);
        //print_r($ret_spec);
        //print_r($ret_spec);
        $ret_property = $this->import_goods_property_c($data_property);
        //不判断商品编码重复
        if ($ret_goods['data'] <> '' || $err_goods <> '') {
            $err_msg .= '系统存在商品编码' . $ret_goods['data'] . 'excel表格重复商品编码:' . $err_goods;
        }

        if ($ret_spec['data'] <> '' || $err_barcorde <> '') {
            //$err_msg .= '系统存在商品条码'.$ret_spec['data'].'excel表格重复商品条码:'.$err_barcorde;
            $err_msg .= $ret_spec['data'];
        }

        if ($err_msg <> '') {

            $file_name = $this->create_import_fail_files($err_msg, 'goods_mix_import_fail');

            //        load_model("sys/ExportModel")->downlaod_csv($request['file_key'],$request['export_name']);
//            $msg .= "部分导入失败，失败信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" > 下载 </a>";
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $msg .= "部分导入失败，失败信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";

            return array('status' => '-1',
                'data' => '',
                'message' => $msg
            );
        } else {
            return $ret_property;
        }
    }

    private function save_property_val(&$property_arr) {
        $property_updata_arr = array();
        foreach ($this->property_set_arr as $property_key) {
            $property_updata_arr[] = " {$property_key} = VALUES({$property_key})";
        }
        $update_str = implode(",", $property_updata_arr);
        $this->insert_multi_duplicate('base_property', $property_arr, $update_str);
    }

    function create_import_fail_files($msg_arr, $name) {
        $fail_top = array('错误信息');
        $file_str = implode(",", $fail_top) . "\n";
        //    $fail_data = explode(",", $msg);
        foreach ($msg_arr as $key => $val) {
            $file_str .= $val . "\r\n";
        }
        $filename = md5($name . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        //var_dump($file_str);die;
        return $filename;
    }

    private function set_property_set_arr() {

        $sql = "select property_val from base_property_set where property_type='goods' AND property_val_title<>''";
        $data = $this->db->get_all($sql);

        foreach ($data as $val) {
            $this->property_set_arr[] = $val['property_val'];
        }
    }

    //读取混合数据
    function read_goods_barcode_property_csv($file, $type = 'name') {

        require_lib('csv_util');
        $exec = new execl_csv();
        $this->set_property_set_arr();
        $spec1 = 'spec1_name';
        $spec2 = 'spec2_name';
        //update 商品信息更新导入
        if ($type == 'code' || $type == 'update') {
            $spec1 = 'spec1_code';
            $spec2 = 'spec2_code';
        }
        $key_arr = array(
            'goods_code', 'goods_name', 'goods_short_name', 'category_name', 'brand_name', 'season_name', 'year_name', 'goods_prop', 'state',
            'weight', 'sell_price', 'cost_price', 'trade_price', 'purchase_price', 'period_validity', 'operating_cycles', 'goods_desc', $spec1, $spec2, 'barcode', 'barcode_weight', 'gb_code', 'goods_produce_name',
        );
        // 'property_val1', 'property_val2'

        $encode_key = array(
            'goods_name', 'goods_short_name', 'category_name', 'brand_name', 'season_name', 'year_name', 'goods_prop', 'state'
            , 'goods_desc', $spec1, $spec2, 'goods_produce_name'
        );
        //扩展属性
        if (!empty($this->property_set_arr)) {
            foreach ($this->property_set_arr as $property_key) {
                $key_arr[] = $property_key;
                $encode_key[] = $property_key;
            }
        }




        //'property_val1', 'property_val2'
        $csv_data = $exec->read_csv($file, 2, $key_arr, $encode_key);
        return $csv_data;




//           private function set_row_arr(&$arr ,$row){
//        $barcode_arr =  &$arr['barcode_arr'];
//        $spce1_arr = &$arr['spce1_arr'];
//        $spce2_arr = &$arr['spce2_arr'];
//        $brand_arr= &$arr['brand_arr'];
//        $year_arr = &$arr['year_arr'];
//        $category_arr = &$arr['category_arr'];
//        $property_arr = &$arr['property_arr'];
//        $season_arr = &$arr['season_arr'];
        //读文件***********************
        $start_line = 0;
        $is_utf8 = detect_encoding($file) == 'UTF-8' ? true : false;
        $file = fopen($file, "r");
        $i = 0;
        $header = array();
        $data = array();
        $key_idex = 0;
        $sql_mx = '';
        $s1l_rep = "";
        $file_str = '';
        $data_arr = array();
        $data_barcord_arr = array();
        $sql_mx_category = '';
        $property_num = 20;
        $err = array();
        //################规格
        //规格1
        $sql9 = "select spec1_id,spec1_code,spec1_name from base_spec1 order by spec1_id desc";
        $spec1 = $this->db->get_all($sql9);
        //print_r($category);exit;
        if (!empty($spec1)) {
            $spec1_def = intval($spec1[0]['spec1_id']);
        } else {
            $spec1_def = 0;
        }
        //规格2
        $sql9 = "select spec2_id,spec2_code,spec2_name from base_spec2 order by spec2_id desc";
        $spec2 = $this->db->get_all($sql9);
        if (!empty($spec2)) {
            $spec2_def = intval($spec2[0]['spec2_id']);
        } else {
            $spec2_def = 0;
        }

        $data_spec1_arr = array();
        $data_spec2_arr = array();
        $goods_code_arr = array();
        //################规格
        //################商品start
        //分类数据
        $sql9 = "select category_id,category_code,category_name from base_category order by category_id desc";
        $category = $this->db->get_all($sql9);
        //print_r($category);exit;
        if (!empty($category)) {
            $category_def = intval($category[0]['category_id']);
        } else {
            $category_def = 0;
        }
        //品牌
        $sql9 = "select brand_id,brand_code,brand_name from base_brand order by brand_id desc";
        $brand = $this->db->get_all($sql9);
        if (!empty($brand)) {
            $brand_def = intval($brand[0]['brand_id']);
        } else {
            $brand_def = 0;
        }
        //季节
        $sql9 = "select season_id,season_code,season_name from base_season order by season_id desc";
        $season = $this->db->get_all($sql9);
        if (!empty($season)) {
            $season_def = intval($season[0]['season_id']);
        } else {
            $season_def = 0;
        }
        //年份
        $sql9 = "select year_id,year_code,year_name from base_year order by year_id desc";
        $year = $this->db->get_all($sql9);
        if (!empty($year)) {
            $year_def = intval($year[0]['year_id']);
        } else {
            $year_def = 0;
        }
        $data_category_arr = array();
        $data_brand_arr = array();
        $data_season_arr = array();
        $data_year_arr = array();
        $chushi_data = array();
        //################商品end
        while (!feof($file)) {
            if ($i >= $start_line) {
                $row = fgetcsv($file);

                //print_r($row);
                if (!empty($row)) {
                    /*
                      for($i=0;$i<=$property_num;$i++){
                      if(isset($row[$i])&&!empty($row[$i])){
                      $row[$i] = trim(iconv('GBK', 'UTF-8', strip_tags(addslashes($row[$i]))));
                      }else{
                      $row[$i] = '';
                      }
                      } */
                    if (!$is_utf8) {
                        $goods_code = trim(iconv('GBK', 'UTF-8', $row[0]));
                    } else {
                        $goods_code = trim($row[0]);
                    }
                    if (!$is_utf8) {
                        $goods_barcord = trim(iconv('GBK', 'UTF-8', $row[17]));
                    } else {
                        $goods_barcord = trim($row[17]);
                    }
                    if (!$is_utf8) {
                        $goods_name = trim(iconv('GBK', 'UTF-8', $row[1]));
                    } else {
                        $goods_name = trim($row[1]);
                    }
                    if (!$is_utf8) {
                        $category_code = trim(iconv('GBK', 'UTF-8', $row[3]));
                    } else {
                        $category_code = trim($row[3]);
                    }
                    if (!$is_utf8) {
                        $brand_code = trim(iconv('GBK', 'UTF-8', $row[4]));
                    } else {
                        $brand_code = trim($row[4]);
                    }

                    //########一条记录
                    if($type != 'update'){
                        $condition = !empty($goods_code) && !empty($goods_name) && !empty($category_code) && !empty($brand_code);
                    }else{
                        $condition = !empty($goods_code);
                    }
                    if ($condition) {
                        //if (!empty($goods_code) && !empty($goods_barcord)) {
                        foreach ($row as $key => $value) {
                            if (!$is_utf8) {
                                $_new_row[$key] = trim(iconv('GBK', 'UTF-8', $row[$key]));
                            } else {
                                $_new_row[$key] = trim($row[$key]);
                            }

                            //规格1
                            if ($key == 15 && $_new_row[$key] <> '') {
                                $spec1_code = '';
                                foreach ($spec1 as $v_c) {
                                    if ($_new_row[$key] == $v_c['spec1_name']) {
                                        $spec1_code = $v_c['spec1_code'];
                                        //$_new_row[3] = $category_code;
                                        break;
                                    }
                                }
                                if ($spec1_code == '') {
                                    if ($i > 1) {
                                        $data_spec1_arr[$_new_row[$key]] = $_new_row[$key];
                                    }
                                }
                            }
                            //规格2
                            if ($key == 16 && $_new_row[$key] <> '') {
                                $spec2_code = '';
                                foreach ($spec2 as $v_c) {
                                    if ($_new_row[$key] == $v_c['spec2_name']) {
                                        $spec2_code = $v_c['spec2_code'];
                                        //$_new_row[3] = $category_code;
                                        break;
                                    }
                                }
                                if ($spec2_code == '') {
                                    if ($i > 1) {
                                        $data_spec2_arr[$_new_row[$key]] = $_new_row[$key];
                                    }
                                }
                            }

                            //分类
                            if ($key == 3 && $_new_row[$key] <> '') {
                                $category_code = '';
                                foreach ($category as $v_c) {
                                    if ($_new_row[$key] == $v_c['category_name']) {
                                        $category_code = $v_c['category_code'];
                                        //$_new_row[3] = $category_code;
                                        break;
                                    }
                                }
                                if ($category_code == '') {
                                    if ($i > 1) {
                                        $data_category_arr[$_new_row[$key]] = $_new_row[$key];
                                    }
                                }
                            }
                            //品牌
                            if ($key == 4 && $_new_row[$key] <> '') {
                                $brand_code = '';
                                foreach ($brand as $v_c) {
                                    if ($_new_row[$key] == $v_c['brand_name']) {
                                        $brand_code = $v_c['brand_code'];
                                        //$_new_row[3] = $category_code;
                                        break;
                                    }
                                }
                                if ($brand_code == '') {
                                    if ($i > 1) {
                                        $data_brand_arr[$_new_row[$key]] = $_new_row[$key];
                                    }
                                }
                            }
                            //季节
                            if ($key == 5 && $_new_row[$key] <> '') {
                                $season_code = '';
                                foreach ($season as $v_c) {
                                    if ($_new_row[$key] == $v_c['season_name']) {
                                        $season_code = $v_c['season_code'];
                                        //$_new_row[3] = $category_code;
                                        break;
                                    }
                                }
                                if ($season_code == '') {
                                    if ($i > 1) {
                                        $data_season_arr[$_new_row[$key]] = $_new_row[$key];
                                    }
                                }
                            }
                            //年份
                            if ($key == 6 && $_new_row[$key] <> '') {
                                $year_code = '';
                                foreach ($year as $v_c) {
                                    if ($_new_row[$key] == $v_c['year_code']) {
                                        $year_code = $v_c['year_name'];
                                        //$_new_row[3] = $category_code;
                                        break;
                                    }
                                }
                                if ($year_code == '') {
                                    if ($i > 1) {
                                        $data_year_arr[$_new_row[$key]] = $_new_row[$key];
                                    }
                                }
                            }//foreach
                        }
                        if ($i > 1) {
                            //取消掉商品编码重复判断
//                          if(isset($data_arr[$_new_row[0]])){
//                              $err[$_new_row[0]] = $_new_row[0];
//                          }

                            $data_arr[$_new_row[0]] = $_new_row;

                            // $goods_code_arr[] = $_new_row[0];
                            if ($_new_row[17] <> '') {
                                $data_barcord_arr[$goods_barcord] = $_new_row;
                            }
                            //$data_arr[$_new_row[0]] = $_new_row;
                            $chushi_data[] = $_new_row;
                        }
                        $s1l_rep .= ",'" . $_new_row[0] . "'";
                        //$i++;
                    }
                    //##########记录
                }
                if ($i > 1) {
                    $sql_mx .= ",('" . implode("','", $_new_row) . "')";
                }
                $key_idex++;
            } else {
                //$header[] = fgetcsv($file);
            }

            $i++;
            if ($i >= 5000) {
                //break;
            }
        }
        //print_r($data_arr);
        fclose($file);
        return array('data_good_arr' => $data_arr,
            //'data_goods_code_arr' => $goods_code_arr,
            'data_barcord_arr' => $data_barcord_arr,
            'chushi_data' => $chushi_data,
            'data_spec1_arr' => $data_spec1_arr,
            'data_spec2_arr' => $data_spec2_arr,
            'spec1_def' => $spec1_def,
            'spec2_def' => $spec2_def,
            'data_category_arr' => $data_category_arr,
            'data_brand_arr' => $data_brand_arr, 'data_season_arr' => $data_season_arr,
            'data_year_arr' => $data_year_arr, 'category_def' => $category_def,
            'brand_def' => $brand_def, 'season_def' => $season_def,
            'err' => $err,
            'year_def' => $year_def
        );
    }

    private function set_goods_other_base(&$arr, $type = 'name') {
        $spce1_arr = &$arr['spce1_arr'];
        $spce2_arr = &$arr['spce2_arr'];
        $brand_arr = &$arr['brand_arr'];
        $year_arr = &$arr['year_arr'];
        $category_arr = &$arr['category_arr'];

        $season_arr = &$arr['season_arr'];

        if (!empty($spce1_arr)) {
            $key_arr = array('tb' => 'base_spec1', 'code' => 'spec1_code', 'name' => 'spec1_name', 'id' => 'spec1_id');
            $spce1_arr = $this->set_base_info($spce1_arr, $key_arr, $type);
        
        }
        if (!empty($spce2_arr)) {
            $key_arr = array('tb' => 'base_spec2', 'code' => 'spec2_code', 'name' => 'spec2_name', 'id' => 'spec2_id');
            $spce2_arr = $this->set_base_info($spce2_arr, $key_arr, $type);
        }
        if (!empty($brand_arr)) {
            $key_arr = array('tb' => 'base_brand', 'code' => 'brand_code', 'name' => 'brand_name', 'id' => 'brand_id');
            $brand_arr = $this->set_base_info($brand_arr, $key_arr);
        }
        if (!empty($year_arr)) {
            $key_arr = array('tb' => 'base_year', 'code' => 'year_code', 'name' => 'year_name', 'id' => 'year_id');
            $year_arr = $this->set_base_info($year_arr, $key_arr);
        }
        if (!empty($category_arr)) {
            $key_arr = array('tb' => 'base_category', 'code' => 'category_code', 'name' => 'category_name', 'id' => 'category_id');
            $category_arr = $this->set_base_info($category_arr, $key_arr);
        }

        if (!empty($season_arr)) {
            $key_arr = array('tb' => 'base_season', 'code' => 'season_code', 'name' => 'season_name', 'id' => 'season_id');
            $season_arr = $this->set_base_info($season_arr, $key_arr);
        }
    }

    private function set_base_info(&$base_arr, $key_arr, $type = 'name') {
        $table = $key_arr['tb'];
        $code = $key_arr['code'];
        $name = $key_arr['name'];
        $id = $key_arr['id'];


        $base_str = "'" . implode("','", $base_arr) . "'";
        $sql_check = " select {$code},{$name} from {$table} where  {$name} in({$base_str})  ";
        if ($type == 'code') {
            $sql_check = " select {$code},{$name} from {$table} where  {$code} in({$base_str})  ";
        }
        $base_data = $this->db->get_all($sql_check);
        $base_new = array();
        //按照名称导入
        if ($type == 'name') {
            foreach ($base_data as $v) {
                $base_new[$v[$name]] = $v[$code];
                unset($base_arr[$v[$name]]);
            }
        } else {
            //按照代码导入
            foreach ($base_data as $v) {
                $base_new[$v[$code]] = $v[$name];
                unset($base_arr[$v[$code]]);
            }
        }
        $no_insert_tb = array('base_spec1', 'base_spec2');
        if (!empty($base_arr) && !in_array($table, $no_insert_tb)) {
            $code_i = $this->db->get_value("select {$id} from {$table} order by {$id} desc");
            $code_i = empty($code_i) ? 0 : $code_i;

            $insert_data = $this->create_base_code_data($base_arr, $code_i, $table, $code, $name);
            $this->insert_multi_exp($table, $insert_data);

            foreach ($insert_data as $val) {
                $base_new[$val[$name]] = $val[$code];
            }
        }
        return $base_new;
    }

    private function create_base_code_data(&$base_arr, $code_i, $table, $code, $name) {
        $insert_data = array();
        foreach ($base_arr as $val) {
            $code_i++;
            $n_code = add_zero($code_i, 3);
            $insert_data[$n_code] = array($code => $n_code, $name => $val);
            //  $base_new[$val] = $n_code;
        }
        $code_arr = array_keys($insert_data);
        $code_str = "'" . implode("','", $code_arr) . "'";
        $sql_check = " select count(1) from {$table}  where {$code} in({$code_str})  ";
        $check_num = $this->db->get_value($sql_check);
        if ($check_num > 0) {
            $code_i = $code_i * 1000;
            return $this->create_base_code_data($base_arr, $code_i, $table, $code, $name);
        }
        return $insert_data;
    }

    private function set_goods_barcode_arr(&$arr, $row, $type = "name") {
        $goods_arr = &$arr['goods_arr'];
        $barcode_arr = &$arr['barcode_arr'];
        $spce1_arr = &$arr['spce1_arr'];
        $spce2_arr = &$arr['spce2_arr'];
        $brand_arr = &$arr['brand_arr'];
        $year_arr = &$arr['year_arr'];
        $category_arr = &$arr['category_arr'];
        // $property_arr = &$arr['property_arr'];
        $season_arr = &$arr['season_arr'];
        $sku_arr = &$arr['sku_arr'];
        $goods_prop = array('普通商品' => 0, '补邮商品' => 1, '赠品' => 2);
        $state = array('在售' => 0, '在库' => 1);
        if (!empty($row['barcode'])) {
            if ($type == 'name') {
                $row['spec1_code'] = $spce1_arr[$row['spec1_name']];
                $row['spec2_code'] = $spce2_arr[$row['spec2_name']];
            } else {
                $row['spec1_name'] = $spce1_arr[$row['spec1_code']];
                $row['spec2_name'] = $spce2_arr[$row['spec2_code']];
            }
            $spec1_code = $row['spec1_code'];
            $spec2_code = $row['spec2_code'];
            $sku = $row['goods_code'] . $spec1_code . $spec2_code;
            $barcode_arr[$row['barcode']] = array(
                'goods_code' => $row['goods_code'],
                'spec1_name' => $row['spec1_name'],
                'spec1_code' => $row['spec1_code'],
                'spec2_code' => $row['spec2_code'],
                'spec2_name' => $row['spec2_name'],
                'barcode' => $row['barcode'],
                'sku' => $sku,
                'gb_code' => $row['gb_code'],
                'weight' => $row['barcode_weight'],
            );
            $sku_arr[$sku] = $row['barcode'];
        }

        $row['goods_prop'] = isset($goods_prop[$row['goods_prop']]) ? $goods_prop[$row['goods_prop']] : 0;
        $row['state'] = isset($state[$row['state']]) ? $state[$row['state']] : 0;

        $row['brand_code'] = !empty($row['brand_name']) ? $brand_arr[$row['brand_name']] : '';
        $row['year_code'] = !empty($row['year_name']) ? $year_arr[$row['year_name']] : '';
        $row['category_code'] = !empty($row['category_name']) ? $category_arr[$row['category_name']] : '';
        $row['season_code'] = !empty($row['season_name']) ? $season_arr[$row['season_name']] : '';

        unset($row['spec1_name'], $row['spec2_name'], $row['barcode'], $row['gb_code']);

        foreach ($this->property_set_arr as $property_key) {
            unset($row[$property_key]);
        }
        if (!empty($row['goods_code'])) {
            if(trim($row['goods_desc']) === '' || $row['goods_desc'] === null) unset($row['goods_desc']);
            $goods_arr[$row['goods_code']] = &$row;
        }
    }

    private function check_sku_barcode(&$arr, $loop_i = 100) {
        if (!empty($arr['sku_arr'])) {
            $sku_arr = &$arr['sku_arr'];
            $barcode_arr = &$arr['barcode_arr'];
            $sku_arr_keys = array_keys($sku_arr);
            $sku_str = "'" . implode("','", $sku_arr_keys) . "'";
            $sql = "select sku,barcode,goods_code,spec1_code,spec2_code from goods_sku where sku in({$sku_str})";
            $data = $this->db->get_all($sql);
            if (!empty($data)) {

                foreach ($data as $val) {
                    $old_sku = $val['sku'];
                    if (!isset($sku_arr[$old_sku])) {
                        continue;
                    }
                    $barcode = $sku_arr[$old_sku];
                    if (!isset($barcode_arr[$barcode])) {
                        continue;
                    }

                    $barcode_info = $barcode_arr[$barcode];
                    if ($val['goods_code'] != $barcode_info['goods_code'] || $val['spec1_code'] != $barcode_info['spec1_code'] || $val['spec2_code'] != $barcode_info['spec2_code']) {
                        $new_sku = $this->set_new_sku($old_sku, $loop_i);
                        $barcode_arr[$barcode]['sku'] = $new_sku;
                        $sku_arr[$new_sku] = $barcode;
                        unset($sku_arr[$old_sku]);
                        $loop_i++;
                    } else {
                        unset($sku_arr[$old_sku]);
                    }
                }


                // $this->check_sku_barcode($arr, $loop_i);
            }
        }
    }

    function set_new_sku($old_sku, $loop_i) {
        $new_sku = $old_sku . $loop_i;
        $check = $this->db->get_value("select count(1) from goods_sku where sku='{$new_sku}'");
        if ($check > 0) {
            $loop_i++;
            return $this->set_new_sku($old_sku, $loop_i);
        }
        return $new_sku;
    }

    private function set_row_arr(&$arr, &$row,$type='hunhe') {
        $barcode_arr = &$arr['barcode_arr'];
        $spce1_arr = &$arr['spce1_arr'];
        $spce2_arr = &$arr['spce2_arr'];
        $brand_arr = &$arr['brand_arr'];
        $year_arr = &$arr['year_arr'];
        $category_arr = &$arr['category_arr'];
        $property_arr = &$arr['property_arr'];
        $season_arr = &$arr['season_arr'];
        if($type == 'hunhe'){
            $check_arr = array('goods_code' => '商品编码', 'goods_name' => '商品名称', 'category_name' => '商品分类', 'brand_name' => '商品品牌', 'barcode' => '商品条形码');
        }elseif($type == 'update'){
            $check_arr = array('goods_code' => '商品编码','spec1_code'=>'规格1代码','spec1_code'=>'规格2代码', 'barcode' => '商品条形码');
        }
        $msg = '';
        foreach ($check_arr as $key => $val) {
            if (empty($row[$key])) {
                $msg .=$val . '不能为空,';
            }
        }

        if (!empty($row['barcode']) && isset($barcode_arr[$row['barcode']])) {
            $msg .= '导入存在相同的条码:' . $row['barcode'] . ',';
        }

        if (!empty($msg)) {
            return $msg;
        }

        if (!empty($row['barcode'])) {
            $barcode_arr[$row['barcode']] = $row['barcode'];
        }
        if (!empty($row['spec1_name'])) {
            $spce1_arr[$row['spec1_name']] = $row['spec1_name'];
        }

        if (!empty($row['spec2_name'])) {
            $spce2_arr[$row['spec2_name']] = $row['spec2_name'];
        }
        if (!empty($row['spec1_code']) || $row['spec1_code'] === '0') {
            $spce1_arr[$row['spec1_code']] = $row['spec1_code'];
        }

        if (!empty($row['spec2_code']) || $row['spec2_code'] === '0') {
            $spce2_arr[$row['spec2_code']] = $row['spec2_code'];
        }

        if (!empty($row['brand_name'])) {
            $brand_arr[$row['brand_name']] = $row['brand_name'];
        }
        if (!empty($row['year_name'])) {
            $year_arr[$row['year_name']] = $row['year_name'];
        }
        if (!empty($row['category_name'])) {
            $category_arr[$row['category_name']] = $row['category_name'];
        }
        if (!empty($row['season_name'])) {
            $season_arr[$row['season_name']] = $row['season_name'];
        }

        if (!empty($this->property_set_arr)) {
            $property_arr[$row['goods_code']] = array(
                'property_val_code' => $row['goods_code'],
                'property_type' => 'goods',
            );
            foreach ($this->property_set_arr as $property_key) {
                $property_arr[$row['goods_code']][$property_key] = $row[$property_key];
            }
        }
    }

    /**
     * 商品信息更新
     */
    private function set_row_arr_by_update(&$arr, &$row){
        $barcode_arr = &$arr['barcode_arr'];
        $spce1_arr = &$arr['spce1_arr'];
        $spce2_arr = &$arr['spce2_arr'];
        $brand_arr = &$arr['brand_arr'];
        $year_arr = &$arr['year_arr'];
        $category_arr = &$arr['category_arr'];
        $property_arr = &$arr['property_arr'];
        $season_arr = &$arr['season_arr'];
        $msg = '';
        if (!empty($row['barcode']) && isset($barcode_arr[$row['barcode']])) {
            $msg .= '导入存在相同的条码:' . $row['barcode'] . ',';
        }
        $check_arr = array('goods_code' => '商品编码','spec1_code'=>'规格1代码','spec1_code'=>'规格2代码', 'barcode' => '商品条形码');
        $msg = '';
        foreach ($check_arr as $key => $val) {
            if (empty($row[$key])) {
                $msg .=$val . '不能为空,';
            }
        }
        if (!empty($msg)) {
            return $msg;
        }

        if (!empty($row['barcode'])) {
            $barcode_arr[$row['barcode']] = $row['barcode'];
        }
        if (!empty($row['spec1_name'])) {
            $spce1_arr[$row['spec1_name']] = $row['spec1_name'];
        }

        if (!empty($row['spec2_name'])) {
            $spce2_arr[$row['spec2_name']] = $row['spec2_name'];
        }
        if (!empty($row['spec1_code']) || $row['spec1_code'] === '0') {
            $spce1_arr[$row['spec1_code']] = $row['spec1_code'];
        }

        if (!empty($row['spec2_code']) || $row['spec2_code'] === '0') {
            $spce2_arr[$row['spec2_code']] = $row['spec2_code'];
        }

        if (!empty($row['brand_name'])) {
            $brand_arr[$row['brand_name']] = $row['brand_name'];
        }
        if (!empty($row['year_name'])) {
            $year_arr[$row['year_name']] = $row['year_name'];
        }
        if (!empty($row['category_name'])) {
            $category_arr[$row['category_name']] = $row['category_name'];
        }
        if (!empty($row['season_name'])) {
            $season_arr[$row['season_name']] = $row['season_name'];
        }

        if (!empty($this->property_set_arr)) {
            $property_arr[$row['goods_code']] = array(
                'property_val_code' => $row['goods_code'],
                'property_type' => 'goods',
            );
            foreach ($this->property_set_arr as $property_key) {
                $property_arr[$row['goods_code']][$property_key] = $row[$property_key];
            }
        }
    }

    private function check_is_barcode($barcode_arr) {
        $barcode_str = "'" . implode("','", $barcode_arr) . "'";
        $sql = "select barcode from goods_barcode where barcode in({$barcode_str})";
        $data = $this->db->get_all($sql);
        $barcode_data = array();
        foreach ($data as $val) {
            $barcode_data[] = $val['barcode'];
        }
        return $barcode_data;
    }

    //导入商品
    function good_spec($file) {
        $data = $this->read_spec_csv($file);
        $ret = $this->good_spec_c($data);
        return $ret;
    }

    //导入规格商品
    function good_spec_c_hunhe($data) {
        // $data = $this->read_spec_csv($file);
        $err_msg = '';
        $data_good_arr = array();
        // $data_good_old_arr = $data['data_good_arr'];
        // $data_goods_code_arr = $data['data_goods_code_arr'];
        $data_barcord_arr = $data['data_barcord_arr'];
        $data_spec1_arr = $data['data_spec1_arr'];
        $data_spec2_arr = $data['data_spec2_arr'];
        $spec1_def = $data['spec1_def'];
        $spec2_def = $data['spec2_def'];

        $sql_mx_spec1 = '';
        $sql_mx_spec2 = '';

        $sql_mx_goods_barcode = '';
        $sql_mx_goods_spec1 = '';
        $sql_mx_goods_spec2 = '';
        $sql_mx_goods_sku = '';
        $is_filter_repeat = true;
        $goods_code_list = '';

        //##########商品是否存在
        //  $goods_code_list = "'" . join("','", $data_goods_code_arr) . "'";
        foreach ($data_barcord_arr as $k2 => $v2) {
            $goods_code_list .= $v2[0] . "','";
        }
        $goods_code_list = "'" . substr($goods_code_list, 0, strlen($goods_code_list) - 3) . "'";
        $sql = "select goods_code from base_goods where goods_code in({$goods_code_list})";
        //echo $sql;
        $exists_goods_code_arr = ctx()->db->get_all_col($sql);

        foreach ($data_barcord_arr as $k1 => $v1) {
            $v1[0] = (string)$v1[0];
            $v1[1] = (string)$v1[1];
            $v1[2] = (string)$v1[2];
            $v1[0] = (string)$v1[3];
            foreach ($exists_goods_code_arr as $k2 => $v2) {
                if ($v1[0] == $v2) {
                    $data_good_arr[$k1] = $v1;
                    if ($data_good_arr[$k1][1] == '') {
                        unset($data_good_arr[$k1]);
                        $error_msg .= '商品编码为:' . $v1[0] . '规格1不能为空,';
                    }
                    if ($data_good_arr[$k1][2] == '') {
                        unset($data_good_arr[$k1]);
                        $error_msg .= '商品编码为:' . $v1[0] . '规格2不能为空,';
                    }
                    //unset($data_barcord_arr[$k1]);
                    break;
                }
            }
        }
        $goods_barcord_list = '';
        //#############条形码重复判断
        foreach ($data_good_arr as $k2 => $v2) {
            $goods_barcord_list .= $v2[3] . "','";
        }


        $goods_barcord_list = "'" . substr($goods_barcord_list, 0, strlen($goods_barcord_list) - 3) . "'";
        $sql = "select b.barcode,b.goods_code,b.spec1_name,b.spec2_name from goods_sku b"
                . " where b.barcode in({$goods_barcord_list})";
        //echo $sql;
        $barcord_data = ctx()->db->get_all($sql);
        foreach ($barcord_data as $val) {
            $exists_goods_barcord_arr[$val['barcode']] = $val;
        }
        //$exists_goods_barcord_arr
        // print_r($exists_goods_barcord_arr);
        //print_r($exists_goods_barcord_arr);
        foreach ($data_good_arr as $k1 => $v1) {
            $barcord_info = isset($exists_goods_barcord_arr[$v1[3]]) ? $exists_goods_barcord_arr[$v1[3]] : array();
            if (!empty($barcord_info)) {
                if ($v1[0] != $barcord_info['goods_code'] || $v1[1] != $barcord_info['spec1_name'] || $v1[2] != $barcord_info['spec2_name']) {

                    unset($data_good_arr[$k1]);
                    $error_msg .= '系统存在barcord:' . $v1[3] . '，与导入商品信息不一致,';
                    break;
                }
            }
        }
        //校验excel中是否存在相同商品编码相同规格的导入信息
        $unset_data_key = array();
        $repeat_goods_gg = array();
        foreach ($data_good_arr as $k1 => $v1) {
            foreach ($data_good_arr as $k2 => $v2) {
                if ($v2[0] == $v1[0] && $v2[1] == $v1[1] && $v2[2] == $v1[2] && $k1 != $k2) {
                    //unset($data_good_arr[$k1]);
                    $unset_data_key[] = $k1;
                    $repeat_gg = $v1[0] . '_' . $v1[1] . '_' . $v1[2];
                    if (!in_array($repeat_gg, $repeat_goods_gg)) {
                        $repeat_goods_gg[] = $repeat_gg;
                        $error_msg .= "EXCEL的导入信息中同一商品有相同的规格信息，请检查excel的商品{$v1[0]}规格1：{$v1[1]}规格2：{$v1[2]},";
                    }

                    break;
                }
            }
        }
        if (!empty($unset_data_key)) {
            foreach ($unset_data_key as $un_key) {
                unset($data_good_arr[$un_key]);
            }
        }
        //商品相同 规格名称也相同的判断
        $goods_code_arr = array();
        foreach ($data_good_arr as $k2 => $v2) {
            $goods_code_arr[] = $v2[0];
        }
        if (!empty($goods_code_arr)) {
            $goods_code_arr = array_unique($goods_code_arr);
            $goods_code_str = "'" . join("','", $goods_code_arr) . "'";
            //商品已存在的规格信息
            $sql = "select goods_code,spec1_code,spec2_code,barcode,spec1_name,spec2_name from goods_sku  where goods_code in($goods_code_str) ";
            $gg_data = ctx()->db->get_all($sql);
            foreach ($gg_data as $val) {
                $exists_goods_gg_arr[$val['goods_code']][] = $val;
            }
            foreach ($data_good_arr as $k1 => $v1) {
                $goods_code = $v1[0];
                if (isset($exists_goods_gg_arr[$goods_code])) {
                    $goods_gg_exist = $exists_goods_gg_arr[$goods_code];
                    foreach ($goods_gg_exist as $gg_row) {
                        if ($v1[0] == $gg_row['goods_code'] && $v1[1] == $gg_row['spec1_name'] && $v1[2] == $gg_row['spec2_name'] && $v1[3] != $gg_row['barcode']) {

                            unset($data_good_arr[$k1]);
                            $error_msg .= "商品{$v1[0]}规格1{$v1[1]}规格2$v1[2]系统已存在,";
                            break;
                        }
                    }
                }
            }
        }
        //规格1
        $sql9 = "select spec1_id,spec1_code,spec1_name from base_spec1 order by spec1_id desc";
        $spec1 = $this->db->get_all($sql9);

        //规格2
        $sql9 = "select spec2_id,spec2_code,spec2_name from base_spec2 order by spec2_id desc";
        $spec2 = $this->db->get_all($sql9);
        //0=>goods_code,1=>spec1_name,2=>spec2_name,3=>barcode,4=>price,5=>weight,6=>remark
        if (!empty($data_good_arr)) {
            foreach ($data_good_arr as $key => $v) {
                $_new_row1 = array();
                $_new_row2 = array();

                $_new_sku = array();
                $_new_barcde = array();
       
                
                $_new_row1[0] = $v[0];
                $_new_row1[1] = '';
                $_new_row2[0] = $v[0];
                $_new_row2[1] = '';
                $_new_row3[0] = $v[0];
                $_new_row3[1] = '';
                $_new_row3[2] = '';

                //$v[7] = '0';
                //$v[8] = '0';
                if ($v[1] == '') {
                    $v[1] = '000';
                    $_new_row1[1] = '000';
                    $_new_row3[1] = '000';
                }
                if ($v[2] == '') {
                    $v[2] = '000';
                    $_new_row2[1] = '000';
                    $_new_row3[2] = '000';
                }
                $spec1_flag = 0;
                $spec2_flag = 0;
                //规格1
                if ($v[1] <> '') {
                    $spec1_code = '';
                    foreach ($spec1 as $v_c) {
                        if ($v[1] == $v_c['spec1_name']) {
                            $spec1_code = $v_c['spec1_code'];
                            $v[1] = $spec1_code;
                            $_new_row1[1] = $spec1_code;
                            $_new_row3[1] = $spec1_code;
                            $spec1_flag = 1;
                            break;
                        }
                    }
                }
                //导入时若系统中不存在规格1、规格2，导入失败，给予提示
                if ($spec1_flag == 0) {
                    $error_msg .= '商品编码为:' . $v[0] . ' 系统不存在规格1:' . $v[1] . ',';
                    unset($data_good_arr[$key]);
                    continue;
                }
                //规格2
                if ($v[2] <> '') {
                    $spec2_code = '';
                    foreach ($spec2 as $v_c) {
                        if ($v[2] == $v_c['spec2_name']) {
                            $spec2_code = $v_c['spec2_code'];
                            $v[2] = $spec2_code;
                            $_new_row2[1] = $spec2_code;
                            $_new_row3[2] = $spec2_code;
                            $spec2_flag = 1;
                            break;
                        }
                    }
                }
                if ($spec2_flag == 0) {
                    $error_msg .= '商品编码为:' . $v[0] . ' 系统不存在规格2:' . $v[2] . ',';
                    unset($data_good_arr[$key]);
                    continue;
                }
                //0=>goods_code,1=>spec1_code,2=>spec2_code,3=>barcode,4=>price,5=>weight,6=>remark
                $sku = "{$v[0]}{$v[1]}{$v[2]}";
                $_new_sku = array('goods_code' => $v[0], 'spec1_code' => $v[1], 'spec2_code' => $v[2], 'sku' => $sku, 'price' => $v[4], 'weight' => $v[5], 'gb_code' => $v[6]);

                //print_r($_new_row1);

                /*
                  echo '<hr/>$_new_row1<xmp>'.var_export($_new_row1,true).'</xmp>';
                  echo '<hr/>$_new_row2<xmp>'.var_export($_new_row2,true).'</xmp>';
                  echo '<hr/>$_new_sku<xmp>'.var_export($_new_sku,true).'</xmp>';
                  echo '<hr/>$_new_barcode<xmp>'.var_export($_new_barcode,true).'</xmp>';
                  die; */
                if ($v[3] <> '') {
                    $_new_barcode = array('goods_code' => $v[0], 'spec1_code' => $v[1], 'spec2_code' => $v[2], 'sku' => $sku, 'barcode' => $v[3], 'gb_code' => $v[6]);
                    $sql_mx_goods_barcode .= ",('" . implode("','", $_new_barcode) . "')";
                }
                $sql_mx_goods_spec1 .= ",('" . implode("','", $_new_row1) . "')";
                $sql_mx_goods_spec2 .= ",('" . implode("','", $_new_row2) . "')";
                $sql_mx_goods_sku .= ",('" . implode("','", $_new_sku) . "')";
            }
        }
        //echo '<hr/>$sql_mx<xmp>'.var_export($sql_mx,true).'</xmp>';die;
        //print_r($_new_row);
        //商品规格1
        if ($sql_mx_goods_spec1 <> '') {
            $sql_mx_goods_spec1 = substr($sql_mx_goods_spec1, 1);
            $sql = 'INSERT ' . ($is_filter_repeat ? 'ignore' : '') . ' INTO  goods_spec1 ' . '(goods_code,spec1_code) VALUES' . $sql_mx_goods_spec1 . ";";
            //echo $sql;
            $ret = $this->db->query($sql);
            if (!$ret) {
                return $this->format_ret("-1", '', 'insert_error');
            }
        }
        //商品规格2
        if ($sql_mx_goods_spec2 <> '') {
            $sql_mx_goods_spec2 = substr($sql_mx_goods_spec2, 1);
            $sql = 'INSERT ' . ($is_filter_repeat ? 'ignore' : '') . ' INTO  goods_spec2 ' . '(goods_code,spec2_code) VALUES' . $sql_mx_goods_spec2 . ";";
            //echo $sql;
            $ret = $this->db->query($sql);
            if (!$ret) {
                return $this->format_ret("-1", '', 'insert_error');
            }
        }
        //商品sku
        if ($sql_mx_goods_sku <> '') {
            $sql_mx_goods_sku = substr($sql_mx_goods_sku, 1);
            $sql = 'INSERT ' . ($is_filter_repeat ? 'ignore' : '') . ' INTO  goods_sku ' . '(goods_code,spec1_code,spec2_code,sku,price,weight,gb_code) VALUES' . $sql_mx_goods_sku . " ON DUPLICATE KEY UPDATE price=VALUES(price),weight=VALUES(weight),gb_code=VALUES(gb_code);";
            $ret = $this->db->query($sql);
            if (!$ret) {
                return $this->format_ret("-1", '', 'insert_error');
            }
        }
        //商品barcord
        if ($sql_mx_goods_barcode <> '') {
            $sql_mx_goods_barcode = substr($sql_mx_goods_barcode, 1);
            $sql = 'INSERT ' . ($is_filter_repeat ? 'ignore' : '') . ' INTO  goods_barcode ' . '(goods_code,spec1_code,spec2_code,sku,barcode,gb_code) VALUES' . $sql_mx_goods_barcode . ";";
            //echo $sql;
            $ret = $this->db->query($sql);
        }
        //exit;


        if ($ret) {
            $id = $this->db->insert_id();

            if ($error_msg <> '') {
                $ret = array(
                    'status' => '-1',
                    'data' => $error_msg,
                    'message' => "导入失败:" . $error_msg
                );
            } else {
                $ret = array(
                    'status' => '1',
                    'data' => $error_msg,
                    'message' => "导入成功！"
                );
            }
        } else {
            $ret = array(
                'status' => '-1',
                'data' => $error_msg,
                'message' => "导入失败！"
            );
        }
        return $ret;
    }

    //导入规格商品
    function good_spec_c($data) {
        // $data = $this->read_spec_csv($file);
        $err_msg = '';
        $data_good_arr = array();
        // $data_good_old_arr = $data['data_good_arr'];
        // $data_goods_code_arr = $data['data_goods_code_arr'];
        $data_barcord_arr = $data['data_barcord_arr'];
        $data_spec1_arr = $data['data_spec1_arr'];
        $data_spec2_arr = $data['data_spec2_arr'];
        $spec1_def = $data['spec1_def'];
        $spec2_def = $data['spec2_def'];

        $sql_mx_spec1 = '';
        $sql_mx_spec2 = '';

        $sql_mx_goods_barcode = '';
        $sql_mx_goods_spec1 = '';
        $sql_mx_goods_spec2 = '';
        $sql_mx_goods_sku = '';
        $is_filter_repeat = true;
        $goods_code_list = '';

        //##########商品是否存在
        //  $goods_code_list = "'" . join("','", $data_goods_code_arr) . "'";
        foreach ($data_barcord_arr as $k2 => $v2) {
            $goods_code_list .= $v2[0] . "','";
        }
        $goods_code_list = "'" . substr($goods_code_list, 0, strlen($goods_code_list) - 3) . "'";
        $sql = "select goods_code from base_goods where goods_code in({$goods_code_list})";
        //echo $sql;
        $exists_goods_code_arr = ctx()->db->get_all_col($sql);

        foreach ($data_barcord_arr as $k1 => $v1) {
            foreach ($exists_goods_code_arr as $k2 => $v2) {
                if ($v1[0] == $v2) {
                    $data_good_arr[$k1] = $v1;
                    unset($data_barcord_arr[$k1]);
                    break;
                }
            }
        }
        $goods_barcord_list = '';
        //#############条形码重复判断
        foreach ($data_barcord_arr as $k2 => $v2) {
            $goods_barcord_list .= $v2[3] . "','";
        }
        $goods_barcord_list = "'" . substr($goods_barcord_list, 0, strlen($goods_barcord_list) - 3) . "'";
        $sql = "select barcode from goods_barcode where barcode in({$goods_barcord_list})";
        //echo $sql;
        $exists_goods_barcord_arr = ctx()->db->get_all_col($sql);
        // print_r($data_good_arr);
        //print_r($exists_goods_barcord_arr);
        foreach ($data_good_arr as $k1 => $v1) {
            foreach ($exists_goods_barcord_arr as $k2 => $v2) {
                if ($v1[3] == $v2) {
                    unset($data_good_arr[$k1]);
                    unset($data_barcord_arr[$k1]);
                    break;
                }
            }
        }

        //#############
        //print_r($data_good_arr);
        //exit;
        //##########
        //规格1###########
        if (!empty($data_spec1_arr)) {
            foreach ($data_spec1_arr as $v_c) {
                $spec1_def++;

                $spec1_code = add_zero($spec1_def, 3);
                //$sql_mx_category .= ",('" . implode("','", $_new_row_category) . "')";

                $sql_mx_spec1 .= ",('" . $spec1_code . "','" . $v_c . "')";
            }
            $sql_mx_spec1 = substr($sql_mx_spec1, 1);

            $sql = 'INSERT ' . ($is_filter_repeat ? 'ignore' : '') . ' INTO  base_spec1 ' . '(spec1_code,spec1_name) VALUES' . $sql_mx_spec1 . ";";
            //echo $sql;
            $ret = $this->db->query($sql);
            if (!$ret) {
                return $this->format_ret("-1", '', 'insert_error');
            }
        }

        //规格2###########
        if (!empty($data_spec2_arr)) {
            foreach ($data_spec2_arr as $v_c) {
                $spec2_def++;

                $spec2_code = add_zero($spec2_def, 3);
                //$sql_mx_category .= ",('" . implode("','", $_new_row_category) . "')";

                $sql_mx_spec2 .= ",('" . $spec2_code . "','" . $v_c . "')";
            }
            $sql_mx_spec2 = substr($sql_mx_spec2, 1);

            $sql = 'INSERT ' . ($is_filter_repeat ? 'ignore' : '') . ' INTO  base_spec2 ' . '(spec2_code,spec2_name) VALUES' . $sql_mx_spec2 . ";";
            //echo $sql;
            $ret = $this->db->query($sql);
            if (!$ret) {
                return $this->format_ret("-1", '', 'insert_error');
            }
        }

        //规格1
        $sql9 = "select spec1_id,spec1_code,spec1_name from base_spec1 order by spec1_id desc";
        $spec1 = $this->db->get_all($sql9);

        //规格2
        $sql9 = "select spec2_id,spec2_code,spec2_name from base_spec2 order by spec2_id desc";
        $spec2 = $this->db->get_all($sql9);

        //0=>goods_code,1=>spec1_name,2=>spec2_name,3=>barcode,4=>price,5=>weight,6=>remark
        //print_r($data_good_arr);
        if (!empty($data_good_arr)) {
            foreach ($data_good_arr as $key => $v) {
                $_new_row1 = array();
                $_new_row2 = array();

                $_new_sku = array();
                $_new_barcde = array();

                $_new_row1[0] = $v[0];
                $_new_row1[1] = '';
                $_new_row2[0] = $v[0];
                $_new_row2[1] = '';
                $_new_row3[0] = $v[0];
                $_new_row3[1] = '';
                $_new_row3[2] = '';

                //$v[7] = '0';
                //$v[8] = '0';
                if ($v[1] == '') {
                    $v[1] = '000';
                    $_new_row1[1] = '000';
                    $_new_row3[1] = '000';
                }
                if ($v[2] == '') {
                    $v[2] = '000';
                    $_new_row2[1] = '000';
                    $_new_row3[2] = '000';
                }

                //规格1
                if ($v[1] <> '') {
                    $spec1_code = '';
                    foreach ($spec1 as $v_c) {
                        if ($v[1] == $v_c['spec1_name']) {
                            $spec1_code = $v_c['spec1_code'];
                            $v[1] = $spec1_code;
                            $_new_row1[1] = $spec1_code;
                            $_new_row3[1] = $spec1_code;
                            break;
                        }
                    }
                }
                //规格2
                if ($v[2] <> '') {
                    $spec2_code = '';
                    foreach ($spec2 as $v_c) {
                        if ($v[2] == $v_c['spec2_name']) {
                            $spec2_code = $v_c['spec2_code'];
                            $v[2] = $spec2_code;
                            $_new_row2[1] = $spec2_code;
                            $_new_row3[2] = $spec2_code;
                            break;
                        }
                    }
                }

                //0=>goods_code,1=>spec1_code,2=>spec2_code,3=>barcode,4=>price,5=>weight,6=>remark
                $sku = "{$v[0]}{$v[1]}{$v[2]}";
                $_new_sku = array('goods_code' => $v[0], 'spec1_code' => $v[1], 'spec2_code' => $v[2], 'sku' => $sku, 'price' => $v[4], 'weight' => $v[5], 'remark' => $v[6]);
                $_new_barcode = array('goods_code' => $v[0], 'spec1_code' => $v[1], 'spec2_code' => $v[2], 'sku' => $sku, 'barcode' => $v[3]);
                //print_r($_new_row1);

                /*
                  echo '<hr/>$_new_row1<xmp>'.var_export($_new_row1,true).'</xmp>';
                  echo '<hr/>$_new_row2<xmp>'.var_export($_new_row2,true).'</xmp>';
                  echo '<hr/>$_new_sku<xmp>'.var_export($_new_sku,true).'</xmp>';
                  echo '<hr/>$_new_barcode<xmp>'.var_export($_new_barcode,true).'</xmp>';
                  die; */

                $sql_mx_goods_barcode .= ",('" . implode("','", $_new_barcode) . "')";
                $sql_mx_goods_spec1 .= ",('" . implode("','", $_new_row1) . "')";
                $sql_mx_goods_spec2 .= ",('" . implode("','", $_new_row2) . "')";
                $sql_mx_goods_sku .= ",('" . implode("','", $_new_sku) . "')";
            }
        }
        //echo '<hr/>$sql_mx<xmp>'.var_export($sql_mx,true).'</xmp>';die;
        //print_r($_new_row);
        //商品规格1
        if ($sql_mx_goods_spec1 <> '') {
            $sql_mx_goods_spec1 = substr($sql_mx_goods_spec1, 1);
            $sql = 'INSERT ' . ($is_filter_repeat ? 'ignore' : '') . ' INTO  goods_spec1 ' . '(goods_code,spec1_code) VALUES' . $sql_mx_goods_spec1 . ";";
            //echo $sql;
            $ret = $this->db->query($sql);
            if (!$ret) {
                return $this->format_ret("-1", '', 'insert_error');
            }
        }
        //商品规格2
        if ($sql_mx_goods_spec2 <> '') {
            $sql_mx_goods_spec2 = substr($sql_mx_goods_spec2, 1);
            $sql = 'INSERT ' . ($is_filter_repeat ? 'ignore' : '') . ' INTO  goods_spec2 ' . '(goods_code,spec2_code) VALUES' . $sql_mx_goods_spec2 . ";";
            //echo $sql;
            $ret = $this->db->query($sql);
            if (!$ret) {
                return $this->format_ret("-1", '', 'insert_error');
            }
        }
        //商品sku
        if ($sql_mx_goods_sku <> '') {
            $sql_mx_goods_sku = substr($sql_mx_goods_sku, 1);
            $sql = 'INSERT ' . ($is_filter_repeat ? 'ignore' : '') . ' INTO  goods_sku ' . '(goods_code,spec1_code,spec2_code,sku,price,weight,remark) VALUES' . $sql_mx_goods_sku . " ON DUPLICATE KEY UPDATE price=VALUES(price),weight=VALUES(weight),remark=VALUES(remark);";
            //echo $sql;
            $ret = $this->db->query($sql);
            if (!$ret) {
                return $this->format_ret("-1", '', 'insert_error');
            }
        }
        //商品barcord
        if ($sql_mx_goods_barcode <> '') {
            $sql_mx_goods_barcode = substr($sql_mx_goods_barcode, 1);
            $sql = 'INSERT ' . ($is_filter_repeat ? 'ignore' : '') . ' INTO  goods_barcode ' . '(goods_code,spec1_code,spec2_code,sku,barcode) VALUES' . $sql_mx_goods_barcode . ";";
            //echo $sql;
            $ret = $this->db->query($sql);
        }
        //exit;
        //print_r($data);
        if (!empty($data_barcord_arr)) {
            foreach ($data_barcord_arr as $k => $v) {
                $error_msg .= '失败sku:' . $k . ',';
            }
            //$ret['data'] = $error_msg;
        }
        if ($ret) {
            $id = $this->db->insert_id();

            if ($error_msg <> '') {
                $ret = array(
                    'status' => '-1',
                    'data' => $error_msg,
                    'message' => "导入失败条码！" . $error_msg
                );
            } else {
                $ret = array(
                    'status' => '1',
                    'data' => $error_msg,
                    'message' => "导入成功！"
                );
            }
        } else {
            $ret = array(
                'status' => '-1',
                'data' => $error_msg,
                'message' => "导入失败！"
            );
        }
        return $ret;
    }

    function read_spec_csv($file) {
        //读文件***********************
        $start_line = 0;
        $is_utf8 = detect_encoding($file) == 'UTF-8' ? true : false;
        $file = fopen($file, "r");
        $i = 0;
        $header = array();
        $data = array();
        $key_idex = 0;
        $sql_mx = '';
        $s1l_rep = "";
        $file_str = '';
        $data_arr = array();
        $data_barcord_arr = array();
        $sql_mx_category = '';
        //规格1
        $sql9 = "select spec1_id,spec1_code,spec1_name from base_spec1 order by spec1_id desc";
        $spec1 = $this->db->get_all($sql9);
        //print_r($category);exit;
        if (!empty($spec1)) {
            $spec1_def = intval($spec1[0]['spec1_id']);
        } else {
            $spec1_def = 0;
        }
        //规格2
        $sql9 = "select spec2_id,spec2_code,spec2_name from base_spec2 order by spec2_id desc";
        $spec2 = $this->db->get_all($sql9);
        if (!empty($spec2)) {
            $spec2_def = intval($spec2[0]['spec2_id']);
        } else {
            $spec2_def = 0;
        }

        $data_spec1_arr = array();
        $data_spec2_arr = array();
        $goods_code_arr = array();
        while (!feof($file)) {
            if ($i >= $start_line) {
                $row = fgetcsv($file);


                if (!empty($row)) {
                    if ($is_utf8) {
                        $goods_code = trim(strip_tags(addslashes($row[0])));
                        $goods_barcord = trim(strip_tags(addslashes($row[3])));
                    } else {
                        $goods_code = trim(iconv('GBK', 'UTF-8', strip_tags(addslashes($row[0]))));
                        $goods_barcord = trim(iconv('GBK', 'UTF-8', strip_tags(addslashes($row[3]))));
                    }
                    //##########记录
                    if (!empty($goods_code) && !empty($goods_barcord)) {
                        foreach ($row as $key => $value) {
                            if ($is_utf8) {
                                $_new_row[$key] = trim(strip_tags(addslashes($row[$key])));
                            } else {
                                $_new_row[$key] = trim(iconv('GBK', 'UTF-8', strip_tags(addslashes($row[$key]))));
                            }
                            //规格1
                            if ($key == 1 && $_new_row[1] <> '') {
                                $spec1_code = '';
                                foreach ($spec1 as $v_c) {
                                    if ($_new_row[1] == $v_c['spec1_name']) {
                                        $spec1_code = $v_c['spec1_code'];
                                        //$_new_row[3] = $category_code;
                                        break;
                                    }
                                }
                                if ($spec1_code == '') {
                                    if ($i > 1) {
                                        $data_spec1_arr[$_new_row[1]] = $_new_row[1];
                                    }
                                }
                            }
                            //规格2
                            if ($key == 2 && $_new_row[2] <> '') {
                                $spec2_code = '';
                                foreach ($spec2 as $v_c) {
                                    if ($_new_row[2] == $v_c['spec2_name']) {
                                        $spec2_code = $v_c['spec2_code'];
                                        //$_new_row[3] = $category_code;
                                        break;
                                    }
                                }
                                if ($spec2_code == '') {
                                    if ($i > 1) {
                                        $data_spec2_arr[$_new_row[2]] = $_new_row[2];
                                    }
                                }
                            }
                        }
                        if ($i > 1) {
                            $data_arr[] = $_new_row;
                            // $goods_code_arr[] = $_new_row[0];
                            $data_barcord_arr[$goods_barcord] = $_new_row;
                        }
                        $s1l_rep .= ",'" . $_new_row[0] . "'";
                        //$i++;
                    }
                    //##########记录
                }
                if ($i > 1) {
                    $sql_mx .= ",('" . implode("','", $_new_row) . "')";
                }
                $key_idex++;
            } else {
                //$header[] = fgetcsv($file);
            }

            $i++;
            if ($i >= 1000) {
                break;
            }
        }
        //print_r($data_arr);
        fclose($file);

        return array('data_good_arr' => $data_arr,
            //'data_goods_code_arr' => $goods_code_arr,
            'data_barcord_arr' => $data_barcord_arr,
            'data_spec1_arr' => $data_spec1_arr,
            'data_spec2_arr' => $data_spec2_arr,
            'spec1_def' => $spec1_def,
            'spec2_def' => $spec2_def
        );
    }

    //导入商品
    function import_base_goods($file) {
        $data = $this->read_csv($file);

        $ret = $this->import_base_goods_c($data);
        return $ret;
    }

    //导入商品
    function import_base_goods_c($data) {
        //  $data = $this->read_csv($file);
        // print_r($data);
        //exit;
        $err_msg = '';
        $data_good_arr = $data['data_good_arr'];
        $data_category_arr = $data['data_category_arr'];
        $data_brand_arr = $data['data_brand_arr'];
        $data_season_arr = $data['data_season_arr'];
        $data_year_arr = $data['data_year_arr'];
        $category_def = $data['category_def'];
        $brand_def = $data['brand_def'];
        $season_def = $data['season_def'];
        $year_def = $data['year_def'];
        $sql_mx_category = '';
        $sql_mx_brand = '';
        $sql_mx_season = '';
        $sql_mx_year = '';
        $sql_mx = '';
        $sql_mx_price = '';
        $is_filter_repeat = true;

        /*

          $goods_code_list = '';
          //##########商品是否存在
          //  $goods_code_list = "'" . join("','", $data_goods_code_arr) . "'";
          foreach ($data_good_arr as $k2=>$v2){
          $goods_code_list .= $v2[0]."','" ;
          }
          $goods_code_list ="'" . substr($goods_code_list,0,strlen($goods_code_list)-3). "'";
          $sql = "select goods_code from base_goods where goods_code in({$goods_code_list})";
          //echo $sql;
          $exists_goods_code_arr = ctx()->db->get_all_col($sql);

          foreach ($data_good_arr as $k1 => $v1) {
          foreach ($exists_goods_code_arr as $k2 => $v2) {
          if ($v1[0] == $v2) {
          //$err_msg .= $v2.',';
          //取消掉重复
          unset($data_good_arr[$v2]);
          break;
          }
          }
          }
         */
        //分类###########
        if (!empty($data_category_arr)) {
            foreach ($data_category_arr as $v_c) {
                $category_def++;

                $cat_code = add_zero($category_def, 3);
                //$sql_mx_category .= ",('" . implode("','", $_new_row_category) . "')";

                $sql_mx_category .= ",('" . $cat_code . "','" . $v_c . "')";
            }
            $sql_mx_category = substr($sql_mx_category, 1);

            $sql = 'INSERT ignore  INTO  base_category ' . '(category_code,category_name) VALUES' . $sql_mx_category . ";";
            //echo $sql;

            $ret = $this->db->query($sql);
            if (!$ret) {
                return $this->format_ret("-1", '', 'insert_error');
            }
        }

        //品牌###########
        if (!empty($data_brand_arr)) {
            foreach ($data_brand_arr as $v_c) {
                $brand_def++;

                $brand_code = add_zero($brand_def, 3);
                //$sql_mx_category .= ",('" . implode("','", $_new_row_category) . "')";

                $sql_mx_brand .= ",('" . $brand_code . "','" . $v_c . "')";
            }
            $sql_mx_brand = substr($sql_mx_brand, 1);

            $sql = 'INSERT ' . ($is_filter_repeat ? 'ignore' : '') . ' INTO  base_brand ' . '(brand_code,brand_name) VALUES' . $sql_mx_brand . ";";
            //echo $sql;
            $ret = $this->db->query($sql);
            if (!$ret) {
                return $this->format_ret("-1", '', 'insert_error');
            }
        }

        //季节#####
        if (!empty($data_season_arr)) {
            foreach ($data_season_arr as $v_c) {
                $season_def++;

                $season_code = add_zero($season_def, 3);
                //$sql_mx_category .= ",('" . implode("','", $_new_row_category) . "')";

                $sql_mx_season .= ",('" . $season_code . "','" . $v_c . "')";
            }
            $sql_mx_season = substr($sql_mx_season, 1);

            $sql = 'INSERT ' . ($is_filter_repeat ? 'ignore' : '') . ' INTO  base_season ' . '(season_code,season_name) VALUES' . $sql_mx_season . ";";
            //echo $sql;
            $ret = $this->db->query($sql);
            if (!$ret) {
                return $this->format_ret("-1", '', 'insert_error');
            }
        }

        //年份#####
        if (!empty($data_year_arr)) {
            foreach ($data_year_arr as $v_c) {
                $year_def++;

                $year_code = add_zero($year_def, 3);
                //$sql_mx_category .= ",('" . implode("','", $_new_row_category) . "')";

                $sql_mx_year .= ",('" . $year_code . "','" . $v_c . "')";
            }
            $sql_mx_year = substr($sql_mx_year, 1);

            $sql = 'INSERT ' . ($is_filter_repeat ? 'ignore' : '') . ' INTO  base_year ' . '(year_code,year_name) VALUES' . $sql_mx_year . ";";
            //echo $sql;
            $ret = $this->db->query($sql);
            if (!$ret) {
                return $this->format_ret("-1", '', 'insert_error');
            }
        }

        //###
        //###
        //exit;
        //$ret = $this -> db -> query($sql);
        //print_r($sql_mx_category);
        //分类###########
        //print_r($sql_mx_category);
        //print_r($data_good_arr);
        //exit;
        //分类数据
        $sql9 = "select category_id,category_code,category_name from base_category order by category_id desc";
        $category = $this->db->get_all($sql9);
        //品牌
        $sql9 = "select brand_id,brand_code,brand_name from base_brand order by brand_id desc";
        $brand = $this->db->get_all($sql9);
        //季节
        $sql9 = "select season_id,season_code,season_name from base_season order by season_id desc";
        $season = $this->db->get_all($sql9);
        //年份
        $sql9 = "select year_id,year_code,year_name from base_year order by year_id desc";
        $year = $this->db->get_all($sql9);
        $_new_row = array();
        $goods_code_arr = array();
        if (!empty($data_good_arr)) {
            foreach ($data_good_arr as $key => $v) {
                $goods_code_arr[] = $v[0];
                $_new_row[0] = $v[0];
                $_new_row[1] = $v[10];
                $_new_row[2] = $v[11];
                $_new_row[3] = $v[12];
                $_new_row[4] = $v[13];
                //商品属性
                switch ($v[7]) {
                    case '普通商品':
                        $v[7] = '0';
                        break;
                    case '补邮商品':
                        $v[7] = '1';
                        break;
                    case '赠品':
                        $v[7] = '2';
                        break;
                    default:
                        $v[7] = '0';
                }
                //商品状态
                switch ($v[8]) {
                    case '在售':
                        $v[8] = '0';
                        break;
                    case '在库':
                        $v[8] = '1';
                        break;
                    default:
                        $v[8] = '0';
                }
                unset($v[10], $v[11], $v[12], $v[13]);
                //分类
                if ($v[3] <> '') {
                    $category_code = '';
                    foreach ($category as $v_c) {
                        if ($v[3] == $v_c['category_name']) {
                            $category_code = $v_c['category_code'];
                            $v[3] = $category_code;
                            break;
                        }
                    }
                }
                //品牌
                if ($v[4] <> '') {
                    $brand_code = '';
                    foreach ($brand as $v_c) {
                        if ($v[4] == $v_c['brand_name']) {
                            $brand_code = $v_c['brand_code'];
                            $v[4] = $brand_code;
                            break;
                        }
                    }
                }
                ///季节
                if ($v[5] <> '') {
                    $season_code = '';
                    foreach ($season as $v_c) {
                        if ($v[5] == $v_c['season_name']) {
                            $season_code = $v_c['season_code'];
                            $v[5] = $season_code;
                            break;
                        }
                    }
                }
                //年份
                if ($v[6] <> '') {
                    $year_code = '';
                    foreach ($year as $v_c) {
                        if ($v[6] == $v_c['year_name']) {
                            $year_code = $v_c['year_code'];
                            $v[6] = $year_code;
                            break;
                        }
                    }
                }
                $sql_mx .= ",('" . implode("','", $v) . "')";
                $sql_mx_price .= ",('" . implode("','", $_new_row) . "')";
            }
        }

        //print_r($_new_row);
        //商品数据
        if ($sql_mx <> '') {
            $sql_mx = substr($sql_mx, 1);
            $sql = 'INSERT  INTO  base_goods ' . '(goods_code,goods_name,goods_short_name,category_code,brand_code,season_code,year_code,goods_prop,state,weight,goods_desc) VALUES' . $sql_mx . " ";
            $sql .=" ON DUPLICATE KEY UPDATE ";
            //'goods_code','goods_name',
            $arr = array('goods_short_name', 'category_code', 'brand_code', 'season_code', 'year_code', 'goods_prop', 'state', 'weight', 'goods_desc');
            foreach ($arr as $v) {
                $sql .= '`' . $v . "` = VALUES(`" . $v . "`),";
            }
            $sql = substr($sql, 0, strlen($sql) - 1);

            //echo $sql;
            // exit;
            $ret = $this->db->query($sql);

            if (!$ret) {
                return $this->format_ret("-1", '', 'insert_error');
            }
        }
        //商品价格
        if ($sql_mx_price <> '') {
            $sql_mx_price = substr($sql_mx_price, 1);
            //print_r($sql_mx_price);

            $sql = 'INSERT  INTO  base_goods ' . '(goods_code,sell_price,cost_price,trade_price,purchase_price) VALUES' . $sql_mx_price;
            $sql .=" ON DUPLICATE KEY UPDATE ";
            $arr = array('sell_price', 'cost_price', 'trade_price', 'purchase_price');
            foreach ($arr as $_v) {
                $sql .= '`' . $_v . "` = VALUES(`" . $_v . "`),";
            }
            $sql = substr($sql, 0, strlen($sql) - 1);
            $ret = $this->db->query($sql);
        }
        if (!empty($goods_code_arr)) {
            $goods_code_str = "'" . implode("','", $goods_code_arr) . "'";
            $sql = " update base_goods ,base_brand  set base_goods.brand_name = base_brand.brand_name
        where base_goods.brand_code = base_brand.brand_code AND   base_goods.goods_code in({$goods_code_str})";
            $ret1 = $this->db->query($sql);
            $sql = " update base_goods ,base_season  set base_goods.season_name = base_season.season_name
        where base_goods.season_code = base_season.season_code AND    base_goods.goods_code in({$goods_code_str})";
            $ret1 = $this->db->query($sql);
            $sql = "update base_goods ,base_category  set base_goods.category_name = base_category.category_name
        where base_goods.category_code = base_category.category_code AND    base_goods.goods_code in({$goods_code_str})";
            $ret1 = $this->db->query($sql);
            $sql = "update base_goods ,base_year  set base_goods.year_name = base_year.year_name
        where base_goods.year_code = base_year.year_code AND    base_goods.goods_code in({$goods_code_str})";
            $ret1 = $this->db->query($sql);
        }



        //print_r($sql_mx);
        //exit;
        //***********************
        //$s1l_rep = substr($s1l_rep, 1);
        //$s1l_rep1 = "(".$s1l_rep.")";
        //code相同忽略ignore
        //$sql = 'INSERT ' . ($is_filter_repeat ? 'ignore' : '') . ' INTO  base_spec1 ' . '(spec1_code,spec1_name,remark) VALUES' . $sql_mx . ";";
        //echo $sql;
        //$ret = $this -> db -> query($sql);
        if ($ret) {
            $id = $this->db->insert_id();

            if ($err_msg <> '') {
                $ret = array(
                    'status' => '-1',
                    'data' => $err_msg,
                    'message' => "系统存在商品编码:" . $err_msg
                );
            } else {
                $ret = array(
                    'status' => '1',
                    'data' => $err_msg,
                    'message' => "导入成功"
                );
            }
        } else {
            $ret = array(
                'status' => '-1',
                'data' => $err_msg,
                'message' => "导入失败！"
            );
        }
        return $ret;
    }

    function read_property($file) {
        $start_line = 1;
        $i = 0;
        $is_utf8 = detect_encoding($file) == 'UTF-8' ? true : false;
        $file = fopen($file, "r");
        $property_all = array();
        $goods_arr = array();
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= $start_line) {
                if (!empty($row) && !empty($row[0])) {
                    $property = $this->get_goods_property($row, $is_utf8);
                    $property_all[$property['property_val_code']] = $property;
                    $goods_arr[] = $property['property_val_code'];
                }else if(!empty($row[1]) && empty($row[0])){
                    $e = $i + 1;
                    $empty_row[] = $e;
                }
            }
            $i ++;
        }
        //print_r($property_all);
        //print_r($goods_arr);
        //exit;
        return array(
            'property_all' => $property_all,
            'goods_arr' => $goods_arr,
            'empty_row' => $empty_row
        );
    }

    //导入扩展属性
    function import_goods_property($file, $is_cover = 1) {
        $data = $this->read_property($file);
        $ret = $this->import_goods_property_c($data);
        return $ret;
    }

    //导入扩展属性
    function import_goods_property_c($data, $is_cover = 1) {

        //$data = $this->read_property($file);
        $property_all = $data['property_all'];
        $goods_arr = $data['goods_arr'];
        $empty_row = $data['empty_row'];
        //print_r($property_all);
        //print_r($goods_arr);
        //exit;
        $goods_str = "'" . implode("','", $goods_arr) . "'";
        $sql = "select goods_code from base_goods where goods_code in({$goods_str})";

        $data = $this->db->get_all($sql);
        $udiff_arr = array();

        if (count($data) != count($goods_arr)) {//效验数据
            $new_goods_arr = array();
            foreach ($data as $val) {
                $new_goods_arr[] = $val['goods_code'];
            }
            $udiff_arr = array_diff($goods_arr, $new_goods_arr);           
            //var_dump($udiff_arr);die;
            foreach ($udiff_arr as $val) {
                unset($property_all[$val]);
            }
        }else{
            $new_goods_arr = array();
            foreach ($data as $val) {
                $new_goods_arr[] = $val['goods_code'];
            }           
        }
        $msg_arr = array();
        if (!empty($udiff_arr)) {
            $msg_arr[] = '未找到商品编码：' . implode("，", $udiff_arr);
        }
        if (!empty($empty_row)) {
            $msg_arr[] = '第'.implode("，", $empty_row).'行 商品编码为空 ';
        }
        //print_r($property_all);
        //exit;
        if (!empty($property_all)) {
            if ($is_cover == 1) {
                $property_all_arr[0] = current($property_all);
                $ret = $this->insert_multi_duplicate('base_property', $property_all, $property_all_arr);
            } else {
                $ret = $this->insert_multi_exp('base_property', $property_all, true);
            }
        }
        if (!empty($new_goods_arr)) {
            //系统操作日志            
            $operate_xq = '商品扩展属性导入'; //操作详情
            $yw_code = ''; //业务编码          
            $module = '商品'; //模块名称
            $operate_type = '导入';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_xq' => $operate_xq, 'operate_type' => $operate_type);
            load_model('sys/OperateLogModel')->insert($log);
        }
        if (!empty($msg_arr)) {
            $file_name = $this->create_import_fail_files($msg_arr, 'goods_mix_import_fail');
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $msg = "部分导入失败，失败信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
            $ret = array(
                'status' => '-1',
                'data' => '',
                'message' => $msg
            );
        }
        return $ret;
    }

    //导入商品
    function import_spec($file, $is_cover = 1, $type) {  //读文件***********************
        $start_line = 0;
        $is_utf8 = detect_encoding($file) == 'UTF-8' ? true : false;
        $file = fopen($file, "r");
        $i = 0;
        $header = array();
        $file_str = '';
        $data_arr = array();
        $result_data = array();
        while (!feof($file)) {
            if ($i >= $start_line) {
                $row = fgetcsv($file);
                if (!empty($row[0]) || !empty($row[1])) {
                    $data_arr[$i][0] = trim($row[0]);
                    $data_arr[$i][1] = trim($row[1]);
                    $data_arr[$i][2] = trim($row[2]);
                    $i++;
                }
            } else {
                $header[] = fgetcsv($file);
            }
            $i++;
        }
        array_shift($data_arr);
//        array_pop($data_arr);
        fclose($file);
        //记录导入的规格代码
        $code_arr = array();
        //记录不符合条件的规格代码及错误信息
        $faild = array();
        //记录失败条数
        $fail_num=0;
        //记录成功条数
        $success_num=0;
        foreach ($data_arr as $v) {
            $err_mess='';
            if(in_array($v[0], $code_arr)){
                $err_mess.='导入规格代码之间有重复,';
            }
            if (empty($v[0]) || empty($v[1])) {
                $err_mess.='代码 名称 不能为空';
            }
            if(!empty($err_mess)){
                $faild[$v[0]]=$err_mess;
                $fail_num++;
            } else {
                if ($is_utf8) {
                    $v[0] = trim(strip_tags(addslashes($v[0])));
                    $v[1] = trim(strip_tags(addslashes($v[1])));
                    $v[2] = trim(strip_tags(addslashes($v[2])));
                } else {
                    $v[1] = iconv('GBK', 'UTF-8', strip_tags(addslashes($v[1])));
                    $v[2] = iconv('GBK', 'UTF-8', strip_tags(addslashes($v[2])));
                }
                if ($type == 1) {
                    $sql = "select spec1_name from base_spec1 where spec1_code = :code";
                    $spec = $this->db->get_row($sql, array('code' => $v[0]));
                }

                if ($type == 2) {
                    $sql = "select spec2_name from base_spec2 where spec2_code = :code";
                    $spec = $this->db->get_row($sql, array('code' => $v[0]));
                }
                if (!empty($spec) && $type==1) {
                    $da[] = array(
                        'spec1_name' => $v[1],
                        'spec1_code' => $v[0],
                        'remark' => $v[2],
                        'lastchanged' => date("Y-m-d H:i:s", time()),
                    );
                }
                if (!empty($spec) && $type==2) {
                    $da[] = array(
                        'spec2_name' => $v[1],
                        'spec2_code' => $v[0],
                        'remark' => $v[2],
                        'lastchanged' => date("Y-m-d H:i:s", time()),
                    );
                }
                if (empty($spec) && $type == 1) {
                    $d[] = array(
                        'spec1_name' => $v[1],
                        'spec1_code' => $v[0],
                        'remark' => $v[2],
                        'lastchanged' => date("Y-m-d H:i:s", time()),
                    );
                }

                if (empty($spec) && $type == 2) {
                    $d[] = array(
                        'spec2_name' => $v[1],
                        'spec2_code' => $v[0],
                        'remark' => $v[2],
                        'lastchanged' => date("Y-m-d H:i:s", time()),
                    );
                }
                $code_arr[] = $v[0];
                $success_num++;
            }
        }
        if ($type == 1 && !empty($d)) {
            $this->insert_multi_exp('base_spec1', $d, true);
        }
        if ($type == 2 && !empty($d)){
            $this->insert_multi_exp('base_spec2', $d, true);
        }
        
        if ($type == 1 && !empty($da)) {
            foreach ($da as $value) {
                $this->update_exp('base_spec1', $value, array('spec1_code'=>$value['spec1_code']));
            }
        }
        if ($type == 2 && !empty($da)){
            foreach ($da as $value) {
                $this->update_exp('base_spec2', $value, array('spec2_code'=>$value['spec2_code']));
            }
        }

        $msg = '导入成功' . $success_num . '条';
        if ($type == 1 && $success_num>0 ) {
            //系统操作日志
            $operate_xq = '规格1(别名)导入'; //操作详情
            $yw_code = ''; //业务编码          
            $module = '商品'; //模块名称
            $operate_type = '导入';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_xq' => $operate_xq, 'operate_type' => $operate_type);
            load_model('sys/OperateLogModel')->insert($log);
        }else if ($type == 2 && $success_num>0 ) {
            $operate_xq = '规格2(别名)导入'; //操作详情
            $yw_code = ''; //业务编码          
            $module = '商品'; //模块名称
            $operate_type = '导入';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_xq' => $operate_xq, 'operate_type' => $operate_type);
            load_model('sys/OperateLogModel')->insert($log);                
        }
        if (!empty($faild)) {
            $msg .='，导入失败:' . $fail_num . '条';
            $fail_top = array('规格代码', '错误信息');
            $filename = 'spec_import';
            $file_name = $this->create_import_fail_files_new($faild, $fail_top, $filename);
//            $msg .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $msg .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
            return $this->format_ret(-1, '',$msg);
        } else {                        
            return $this->format_ret(1, '', '导入成功');
        }
    }

    function get_goods_property($row, $is_utf8 = false) {
        $property_num = 10;
        if ($is_utf8) {
            $goods_code = trim(strip_tags(addslashes($row[0])));
        } else {
            $goods_code = trim(iconv('GBK', 'UTF-8', strip_tags(addslashes($row[0]))));
        }

        $property_str = 'property_val';
        $property = array();
        $property['property_val_code'] = $goods_code;
        $property['property_type'] = 'goods';
        for ($i = 1; $i <= $property_num; $i++) {
            if (isset($row[$i]) && !empty($row[$i])) {
                if ($is_utf8) {
                    $property_val = trim(strip_tags(addslashes($row[$i])));
                } else {
                    $property_val = trim(iconv('GBK', 'UTF-8', strip_tags(addslashes($row[$i]))));
                }
            } else {
                $property_val = '';
            }
            $property[$property_str . $i] = $property_val;
        }
        return $property;
    }

    private function get_property_key($val) {
        $key = "property_val" . (int) $val;
        return $key;
    }

    // 商品导入读取csv文件
    function read_csv($file) {
        //读文件***********************
        $start_line = 0;
        $is_utf8 = detect_encoding($file) == 'UTF-8' ? true : false;
        $file = fopen($file, "r");
        $i = 0;
        $header = array();
        $data = array();
        $key_idex = 0;
        $sql_mx = '';
        $s1l_rep = "";
        $file_str = '';
        $data_arr = array();
        $sql_mx_category = '';
        //分类数据
        $sql9 = "select category_id,category_code,category_name from base_category order by category_id desc";
        $category = $this->db->get_all($sql9);
        //print_r($category);exit;
        if (!empty($category)) {
            $category_def = intval($category[0]['category_id']);
        } else {
            $category_def = 0;
        }
        //品牌
        $sql9 = "select brand_id,brand_code,brand_name from base_brand order by brand_id desc";
        $brand = $this->db->get_all($sql9);
        if (!empty($brand)) {
            $brand_def = intval($brand[0]['brand_id']);
        } else {
            $brand_def = 0;
        }
        //季节
        $sql9 = "select season_id,season_code,season_name from base_season order by season_id desc";
        $season = $this->db->get_all($sql9);
        if (!empty($season)) {
            $season_def = intval($season[0]['season_id']);
        } else {
            $season_def = 0;
        }
        //年份
        $sql9 = "select year_id,year_code,year_name from base_year order by year_id desc";
        $year = $this->db->get_all($sql9);
        if (!empty($year)) {
            $year_def = intval($year[0]['year_id']);
        } else {
            $year_def = 0;
        }
        $data_category_arr = array();
        $data_brand_arr = array();
        $data_season_arr = array();
        $data_year_arr = array();

        $_new_row = array();
        while (!feof($file)) {
            if ($i >= $start_line) {
                $row = fgetcsv($file);


                if (!empty($row)) {
                    //$data_arr[] = $row;
                    if ($is_utf8) {
                        $goods_code = trim(strip_tags(addslashes($row[0])));
                        $goods_name = trim(strip_tags(addslashes($row[1])));
                        $category_code = trim(strip_tags(addslashes($row[3])));
                        $brand_code = trim(strip_tags(addslashes($row[4])));
                    } else {
                        $goods_code = trim(iconv('GBK', 'UTF-8', strip_tags(addslashes($row[0]))));
                        $goods_name = trim(iconv('GBK', 'UTF-8', strip_tags(addslashes($row[1]))));
                        $category_code = trim(iconv('GBK', 'UTF-8', strip_tags(addslashes($row[3]))));
                        $brand_code = trim(iconv('GBK', 'UTF-8', strip_tags(addslashes($row[4]))));
                    }

                    //########一条记录
                    if (!empty($goods_code) && !empty($goods_name) && !empty($category_code) && !empty($brand_code)) {
                        foreach ($row as $key => $value) {
                            if ($is_utf8) {
                                $_new_row[$key] = trim(strip_tags(addslashes($row[$key])));
                            } else {
                                $_new_row[$key] = trim(iconv('GBK', 'UTF-8', strip_tags(addslashes($row[$key]))));
                            }

                            //分类
                            if ($key == 3 && $_new_row[3] <> '') {
                                $category_code = '';
                                foreach ($category as $v_c) {
                                    if ($_new_row[3] == $v_c['category_name']) {
                                        $category_code = $v_c['category_code'];
                                        //$_new_row[3] = $category_code;
                                        break;
                                    }
                                }
                                if ($category_code == '') {
                                    if ($i > 1) {
                                        $data_category_arr[$_new_row[3]] = $_new_row[3];
                                    }
                                }
                            }
                            //品牌
                            if ($key == 4 && $_new_row[4] <> '') {
                                $brand_code = '';
                                foreach ($brand as $v_c) {
                                    if ($_new_row[4] == $v_c['brand_name']) {
                                        $brand_code = $v_c['brand_code'];
                                        //$_new_row[3] = $category_code;
                                        break;
                                    }
                                }
                                if ($brand_code == '') {
                                    if ($i > 1) {
                                        $data_brand_arr[$_new_row[4]] = $_new_row[4];
                                    }
                                }
                            }
                            //季节
                            if ($key == 5 && $_new_row[5] <> '') {
                                $season_code = '';
                                foreach ($season as $v_c) {
                                    if ($_new_row[5] == $v_c['season_name']) {
                                        $season_code = $v_c['season_code'];
                                        //$_new_row[3] = $category_code;
                                        break;
                                    }
                                }
                                if ($season_code == '') {
                                    if ($i > 1) {
                                        $data_season_arr[$_new_row[5]] = $_new_row[5];
                                    }
                                }
                            }
                            //年份
                            if ($key == 6 && $_new_row[6] <> '') {
                                $year_code = '';
                                foreach ($year as $v_c) {
                                    if ($_new_row[6] == $v_c['year_code']) {
                                        $year_code = $v_c['year_name'];
                                        //$_new_row[3] = $category_code;
                                        break;
                                    }
                                }
                                if ($year_code == '') {
                                    if ($i > 1) {
                                        $data_year_arr[$_new_row[6]] = $_new_row[6];
                                    }
                                }
                            }//foreach
                        }

                        if ($i > 1) {
                            $data_arr[$_new_row[0]] = $_new_row;
                        }
                    }
                    //########一条记录
                    //$s1l_rep .= ",'".$_new_row[0]."'";
                    //$i++;
                }
                //if($i > 1 ){
                //$sql_mx .= ",('" . implode("','", $_new_row) . "')";
                //}
                $key_idex++;
            } else {
                //$header[] = fgetcsv($file);
            }

            $i++;
            if ($i >= 1000) {
                break;
            }
        }
        //print_r($data_arr);
        fclose($file);
        return array('data_good_arr' => $data_arr, 'data_category_arr' => $data_category_arr,
            'data_brand_arr' => $data_brand_arr, 'data_season_arr' => $data_season_arr,
            'data_year_arr' => $data_year_arr, 'category_def' => $category_def,
            'brand_def' => $brand_def, 'season_def' => $season_def,
            'year_def' => $year_def
        );
    }

    function save_csv($header_str, $file_str) {
        $filename = "hunhedaoru.csv";
        $filename = iconv('UTF-8', 'GB2312', $filename);
        //header( "Cache-Control: public" );
        header('Cache-Control:   must-revalidate,   post-check=0,   pre-check=0');
        header("Pragma: public");
        header("Content-type:application/vnd.ms-excel");
        //header("Content-type: text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Content-Type:APPLICATION/OCTET-STREAM');
        //$file_str=  iconv("utf-8",'gbk',$file_str);
        $file_str = iconv('UTF-8', 'GB2312', $file_str);
        ob_end_clean();

        echo $header_str;
        echo $file_str;
        die();
        exit;
    }

    function hunhe_bie() {
        $sql = "select property_val,property_val_title from base_property_set where property_type = 'goods'";
        $db_prop_map = ctx()->db->get_all($sql);
        $prop_map = array();
        foreach ($db_prop_map as $sub_map) {
            $prop_map[$sub_map['property_val']] = $sub_map['property_val_title'];
        }

        $html = join(",", $prop_map);
        return $html;
    }

    //导入规格 黄鸿宇
    function import_base_barcode($file) {
        ini_set('display_errors', 1);
        $line = 1;
        $file = fopen($file, "r");
        $data = array();
        $goods_code_arr = array();
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($line <= 2) {
                $line++;
                continue;
            }
            foreach ($row as $key => $value) {
                $_new_row[$key] = iconv('GBK', 'UTF-8', strip_tags(addslashes(trim($row[$key]))));
            }
            if (empty($_new_row[0]) && empty($_new_row[3])) {
                continue;
            }
            $data[] = $_new_row;
            $goods_code_arr[] = $_new_row[0];
            $line++;
        }
        $goods_code_list = "'" . join("','", $goods_code_arr) . "'";

        $sql = "select goods_code from base_goods where goods_code in({$goods_code_list})";
        $exists_goods_code_arr = ctx()->db->get_all_col($sql);

        $sql = "select spec1_code,spec1_name from base_spec1";
        $db_spec1 = ctx()->db->get_all($sql);
        $exists_spec1 = array();
        foreach ($db_spec1 as $sub_spec1) {
            $exists_spec1[trim($sub_spec1['spec1_name'])] = trim($sub_spec1['spec1_code']);
        }
        $sql = "select spec2_code,spec2_name from base_spec2";
        $db_spec2 = ctx()->db->get_all($sql);
        $exists_spec2 = array();
        foreach ($db_spec2 as $sub_spec2) {
            $exists_spec2[trim($sub_spec2['spec2_name'])] = trim($sub_spec2['spec2_code']);
        }

        $sql = "select count(*) from base_spec1";
        $base_spec1_count = ctx()->db->getOne($sql);
        $sql = "select count(*) from base_spec2";
        $base_spec2_count = ctx()->db->getOne($sql);

        $ins_sku = array();
        $ins_spec1 = array();
        $ins_spec2 = array();
        $ins_barcode = array();

        $ins_goods_spec1_arr = array();
        $ins_goods_spec2_arr = array();
        foreach ($data as $sub_data) {
            $_goods_code = $sub_data[0];
            $_spec1_name = $sub_data[1];
            $_spec2_name = $sub_data[2];
            $_goods_barcode = $sub_data[3];

            $_spec1_code = @$exists_spec1[$_spec1_name];
            $_spec2_code = @$exists_spec2[$_spec2_name];
            if (empty($_spec1_name)) {
                $_spec1_code = '000';
                $_spec1_name = '通用';
            }
            if (empty($_spec2_name)) {
                $_spec2_code = '000';
                $_spec2_name = '通用';
            }
            if (empty($_spec1_code)) {
                $base_spec1_count++;
                $_spec1_code = str_pad($base_spec1_count, 3, '0', STR_PAD_LEFT);
            }
            if (empty($_spec2_code)) {
                $base_spec2_count++;
                $_spec2_code = str_pad($base_spec2_count, 3, '0', STR_PAD_LEFT);
            }
            $exists_spec1[$_spec1_name] = $_spec1_code;
            $exists_spec2[$_spec2_name] = $_spec2_code;

            $ins_spec1[$_spec1_name] = array('spec1_code' => $_spec1_code, 'spec1_name' => $_spec1_name);
            $ins_spec2[$_spec2_name] = array('spec2_code' => $_spec2_code, 'spec2_name' => $_spec2_name);

            $_sku = "{$_goods_code}{$_spec1_code}{$_spec2_code}";
            $ins_sku[] = array('goods_code' => $_goods_code, 'spec1_code' => $_spec1_code, 'spec2_code' => $_spec2_code, 'sku' => $_sku);
            $ins_barcode[] = array('goods_code' => $_goods_code, 'spec1_code' => $_spec1_code, 'spec2_code' => $_spec2_code, 'sku' => $_sku, 'barcode' => $_goods_barcode);

            $ins_goods_spec1_arr[] = array('goods_code' => $_goods_code, 'spec1_code' => $_spec1_code);
            $ins_goods_spec2_arr[] = array('goods_code' => $_goods_code, 'spec2_code' => $_spec2_code);
        }
        /*
          echo '<hr/>$ins_spec1<xmp>'.var_export($ins_spec1,true).'</xmp>';
          echo '<hr/>$ins_spec2<xmp>'.var_export($ins_spec2,true).'</xmp>';
          echo '<hr/>$ins_sku<xmp>'.var_export($ins_sku,true).'</xmp>';
          echo '<hr/>$ins_barcode<xmp>'.var_export($ins_barcode,true).'</xmp>';
          die;

          echo '<hr/>$ins_goods_spec1_arr<xmp>'.var_export($ins_goods_spec1_arr,true).'</xmp>';
          echo '<hr/>$ins_goods_spec2_arr<xmp>'.var_export($ins_goods_spec2_arr,true).'</xmp>';
          die; */

        $ret = M('base_spec1')->insert_dup(array_values($ins_spec1));
        //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
        if ($ret['status'] < 0) {
            return $ret;
        }
        $ret = M('base_spec2')->insert_dup(array_values($ins_spec2));
        //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
        if ($ret['status'] < 0) {
            return $ret;
        }

        $ret = M('goods_spec1')->insert_dup($ins_goods_spec1_arr);
        //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
        if ($ret['status'] < 0) {
            return $ret;
        }

        $ret = M('goods_spec2')->insert_dup($ins_goods_spec2_arr);
        //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
        if ($ret['status'] < 0) {
            return $ret;
        }

        $ret = M('goods_sku')->insert_dup($ins_sku);
        //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
        if ($ret['status'] < 0) {
            return $ret;
        }
        $ret = M('goods_barcode')->insert_dup($ins_barcode);
        //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
        if ($ret['status'] < 0) {
            return $ret;
        }
        return $this->format_ret(1);
    }

    /**
     * @todo 导入商品条形码，同步更新base_goods表lastchanged字段值
     */
    function update_lastchanged($barcode_arr) {
        foreach ($barcode_arr as $value) {
            $goods_code[] = $value['goods_code'];
        }
        $goods_codes = implode("','", $goods_code);
        $sql = "UPDATE base_goods SET lastchanged = NOW() WHERE goods_code IN('{$goods_codes}')";
        return $this->db->query($sql);
    }

    /**
     * 通过存储过程导入商品
     * @param unknown_type $goods
     */
    function goods_import($goods = NULL) {
        $test = array(
            array(
                'p_code' => 'test_goods',
                'p_name' => 'test_goods',
                'p_cat_name' => '裤子',
                'p_brand_name' => '左西',
                'p_state' => 0,
                'p_spec1_name' => 'spec1_test',
                'p_spec2_name' => 'spec2_test',
                'p_barcode' => 'test_goods001',
                'p_goods_short_name' => 'test_goods',
                'p_season_name' => '夏季',
                'p_year_name' => '2016',
                'p_weight' => '100',
                'p_period_validity' => '5',
                'p_operating_cycles' => '5',
                'p_cost_price' => '130',
                'p_sell_price' => '300',
                'p_trade_price' => '200',
                'p_purchase_price' => '130'
            )
        );

        $goods = $test;

        foreach ($goods as $good) {
            $str = "'" . $good['p_code'] . "'," .
                    "'" . $good['p_name'] . "'," .
                    "'" . $good['p_cat_name'] . "'," .
                    "'" . $good['p_brand_name'] . "'," .
                    "" . $good['p_state'] . "," .
                    "'" . $good['p_spec1_name'] . "'," .
                    "'" . $good['p_spec2_name'] . "'," .
                    "'" . $good['p_barcode'] . "'," .
                    "'" . $good['p_goods_short_name'] . "'," .
                    "'" . $good['p_season_name'] . "'," .
                    "'" . $good['p_year_name'] . "'," .
                    "'" . $good['p_weight'] . "'," .
                    "'" . $good['p_period_validity'] . "'," .
                    "'" . $good['p_operating_cycles'] . "'," .
                    "" . $good['p_cost_price'] . "," .
                    "" . $good['p_sell_price'] . "," .
                    "" . $good['p_trade_price'] . "," .
                    "" . $good['p_purchase_price'] . "";

            print_r($str);

            $sqls = "SELECT f_goods_init({$str})";
            $res = $this->db->get_all($sqls);

            print_r($res);
            die;

            if (empty($res)) {
                return $this->format_ret(1, '', '商品初始化失败');
            }
        }
    }

    //导入商品信息价格
    function import_goods_barcode_price($file) {
        $start_line = 1;
        $is_utf8 = detect_encoding($file) == 'UTF-8' ? true : false;
        $file = fopen($file, "r");
        $i = 0;
        $header = array();
        $data_arr = array();
        $faild = array();
        $gb_code_all = array();
        $success_num = 0;
        $fail_num = 0;

        while (!feof($file)) {
            if ($i >= $start_line) {
                $row = fgetcsv($file);
                if (!empty($row)) {
                    if(!empty($row[5])){
                        $is_gb_code = in_array($row[5], $gb_code_all);
                    }
                    
                    if ($is_gb_code) {
                        $error_msg = '国标码'.$row[5].'已重复';
                        $faild[$row[0]] = $error_msg;
                        $fail_num++;
                        break;
                    } else {
                        $gb_code_all[] = $row[5];
                    }

                    $data_arr[$i]['barcode'] = $row[0];
                    $data_arr[$i]['price'] = $row[1];
                    $data_arr[$i]['cost_price'] = $row[2];
                    $data_arr[$i]['weight'] = $row[3];
                    $data_arr[$i]['remark'] = $row[4];
                    $data_arr[$i]['gb_code'] = $row[5];

                    $i++;
                }
            } else {
                $header[] = fgetcsv($file);
            }
            $i++;
        }
        fclose($file);

        $result_data = array();
        $sql = "select barcode from goods_sku ";
        $barcode = $this->db->get_all($sql);
        $barcode_all = array();
        foreach ($barcode as $va) {
            $barcode_all[] = $va['barcode'];
        }

        foreach ($data_arr as $v) {
            $exist = in_array($v['barcode'], $barcode_all);
            $error = 0;
            $error_msg = '';
            if (!$exist||empty($v['barcode'])) {
                $error_msg .= $v['barcode'] . "商品条形码不存在.";
                $error++;
            } else{
                $sql = "select goods_code, spec1_code, spec2_code,barcode from goods_barcode where barcode = :barcode ";
                $arr = array(':barcode' => $v['barcode']);                
                $spec_info = $this->db->get_row($sql,$arr);
                $spec = $spec_info['spec1_code'].'_'.$spec_info['spec2_code'];
                $ret = load_model('prm/GoodsModel')->gb_code_exist($v['gb_code'],$spec_info['goods_code'],$spec);
                if($ret['status'] == 1){
                    $error_msg .= $ret['message'];
                    $error++;
                }
            }

            if (empty($v['price']) && empty($v['cost_price']) && empty($v['weight']) && empty($v['remark']) && empty($v['gb_code'])) {
                $error_msg .="导入商品信息为空";
                $error++;
            }
            if ($error != 0) {
                $faild[$v['barcode']] = $error_msg;
                $fail_num++;
            } else {
                $d = array(
                    'barcode' => $v['barcode'],
                    'price' => $v['price'],
                    'cost_price' => $v['cost_price'],
                    'weight' => $v['weight'],
                    'remark' => $v['remark'],
                    'gb_code' => $v['gb_code'],
                );
                $result_data[] = $d;
                $success_num++;
            }
        }
        //批量更新
        $result1 = array();
        $result2 = array();
        foreach ($result_data as $k => $val) {
            foreach ($val as $key => $valu) {
                if (isset($valu) && $valu != '') {
                    $result1[$key] = $valu;
                    continue;
                }
            }
            $result2[$k] = $result1;
            unset($result1);
        }
        foreach ($result2 as $k1 => $v1) {
            $where = " barcode='{$v1['barcode']}'";
            $this->db->update('goods_sku', $v1, $where);
            $this->db->update('goods_barcode', $v1, $where);
        }

        $msg = '导入成功' . $success_num . '条';       
        if ($success_num > 0) {
            //系统操作日志
            $operate_xq = '商品条形码信息价格导入'; //操作详情
            $yw_code = ''; //业务编码          
            $module = '商品'; //模块名称
            $operate_type = '导入';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_xq' => $operate_xq, 'operate_type' => $operate_type);
            load_model('sys/OperateLogModel')->insert($log);
        }
        if (!empty($faild)) {
            $msg .='，导入失败:' . $fail_num . '条';
            $fail_top = array('条形码', '错误信息');
            $filename = 'goods_barcode_price_import';
            $file_name = $this->create_import_fail_files_new($faild, $fail_top, $filename);
//            $msg .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $msg .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
            return array('status' => '-1',
                'data' => '',
                'message' => $msg
            );
        } else {           
            return $this->format_ret(1, '', '导入成功');
        }
    }

    function create_import_fail_files_new($msg, $fail_top, $filename) {
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($msg as $key => $val) {
            $val_data = array($key, $val);
            $file_str .= "\t" . implode(",", $val_data) . "\r\n";
        }
        $filename = md5($filename . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

    /**
     * 套餐商品导入
     * @param string $file 文件地址
     */
    function import_goods_combo($file) {
        $power = load_model('sys/SysParamsModel')->get_val_by_code('spec_power');
        $data = array();
        //读取文件
        $file_num = $this->read_goods_file($file, $data, 'combo');
        foreach ($data as $key => $value) {
            if(empty($value['goods_code']) && empty($value['combo_barcode']) && empty($value['goods_name']) && empty($value['spec1_code']) && empty($value['spec2_code']) && empty($value['barcode'])){
                unset($data[$key]);
            } 
        }
        //print_r($data);
        //获取存在的barcode信息
        $sku_arr = $this->get_exists_barcode($data, 1);
        //获取商品子条码
        $barcode_child = $this->get_barcode_child();
        //获取已启用的套餐商品
        if($power['spec_power'] == 1){
            $combo_code_arr = deal_array_with_quote(array_column($data, 'goods_code'));
        }else{
            $combo_code_arr = deal_array_with_quote(array_column($data, 'combo_barcode'));
        }

        $combo_arr = array();
        $price_arr = array();
        $error_info = array();
        $error_combo = array();
        $right_combo = array();
        $exists_barcode = array();
        $sku_list = array();
        $bar = array();
        $s_list = array();
        $arr_list = array();
        //print_r($data);
        foreach ($data as $val) {
            $error_msg = array();
            $check_info = $this->check_required($val, 'combo');
            if ($check_info !== '') {
                $val['error_msg'] = $check_info;
                $error_info[] = $val;
                if (!empty($val['combo_barcode']) && isset($combo_arr[$val['combo_barcode']])) {
                    unset($combo_arr[$val['combo_barcode']]);
                }
                continue;
            }
            $combo_code = $val['combo_barcode'];
            $sql_check = "select goods_code,spec1_code,spec2_code from goods_combo_barcode where barcode='{$val['combo_barcode']}'";
            $check = $this->db->get_row($sql_check);
            if($power['spec_power'] == 1){
                $combo_code_1 = $val['goods_code'];
                if($val['goods_code'] != $check['goods_code'] && !empty($check['goods_code'])){
                    $error_msg[] = '套餐条形码已存在';
                }
                if($val['goods_code'] == $check['goods_code'] && ($val['spec1_code'] != $check['spec1_code'] || $val['spec2_code'] != $check['spec2_code'])){
                    $error_msg[] = '套餐规格与之前的套餐不一致';
                }
                if($arr_list[$val['combo_barcode']] != $val['goods_code'] && !empty($arr_list[$val['combo_barcode']])){
                    $error_msg[] = '导入的套餐商品信息不一致';
                }
            }else{
                if($val['combo_barcode'] != $check['goods_code'] && !empty($check['goods_code'])){
                    $error_msg[] = '套餐条形码已存在';
                }
            }
            $barcode = $val['barcode'];
            $spec1_code = $val['spec1_code'];
            $spec2_code = $val['spec2_code'];
            if($power['spec_power'] == 1){
                $spec1_exist = load_model('prm/Spec1Model')->get_by_code($spec1_code);
                if(empty($spec1_exist['data'])){
                  $error_msg[] = '套餐规格1不存在'; 
                }
                $spec2_exist = load_model('prm/Spec2Model')->get_by_code($spec2_code);
                if(empty($spec2_exist['data'])){
                  $error_msg[] = '套餐规格2不存在'; 
                }
                $sku_data = $val['combo_barcode'].$val['spec1_code'].$val['spec2_code'];
                $bar_data = $val['combo_barcode'];
                $s_data = $val['goods_code'].$val['spec1_code'].$val['spec2_code'];
                if(!in_array($sku_data, $sku_list) && in_array($bar_data,$bar)){
                $error_msg[] = '同条码的套餐重规格错误';
                }
                if(!in_array($bar_data,$bar) && in_array($s_data,$s_list)){
                $error_msg[] = '不同条码相同编码的套餐重规格重复';    
                }
            }

            if (!array_key_exists($barcode, $sku_arr)) {
                $error_msg[] = '套餐子商品条形码不存在';
            }
            if (array_key_exists($combo_code, $sku_arr)) {
                $error_msg[] = '套餐条形码与系统商品条形码不能相同';
            }
            if (in_array($combo_code, $barcode_child)) {
                $error_msg[] = '套餐条形码与系统商品子条码不能相同';
            }
            if (in_array($barcode, $exists_barcode[$val['combo_barcode']])) {
                $error_msg[] = '套餐存在相同的子商品条码';
            }
            if (!empty($error_msg)) {
                $error_msg = implode(' | ', $error_msg);
                $val['error_msg'] = $error_msg;
                $error_info[] = $val;
                if (isset($right_combo[$combo_code])) {
                    $error_info = array_merge($error_info, $right_combo[$combo_code]);
                }
                $error_combo[$combo_code] = $combo_code;
                $right_combo[$combo_code] = array();
            }

                if (in_array($combo_code, $error_combo)) {
                if (empty($error_msg)) {
                    $error_info[] = $val;
                }
                if (isset($combo_arr[$combo_code])) {
                    unset($combo_arr[$combo_code]);
                }
                continue;
                }

            $add_time = date('Y-m-d H:i:s', time());
            if($power['spec_power'] == 1){
                $combo_sku = $combo_code_1 .$spec1_code.$spec2_code.'_sku'; 
            }else{
                $combo_sku = $combo_code . '000000_sku';               
            }


            //套餐主信息
            $combo = array();
            if($power['spec_power'] == 1){
            $combo['goods_code'] = $combo_code_1;                 
            }else{
            $combo['goods_code'] = $combo_code;                
            }

            $combo['goods_name'] = $val['goods_name'];
            $combo['create_time'] = $add_time;
            $combo['status'] = 1;
            $combo_arr[$combo_code]['combo'] = $combo;

            //套餐子商品信息
            $combo_diy = $sku_arr[$barcode];
            if($power['spec_power'] == 1){
            $combo_diy['p_goods_code'] = $combo_code_1;                
            }else{
            $combo_diy['p_goods_code'] = $combo_code;               
            }
            $combo_diy['p_sku'] = $combo_sku;
            $combo_diy['add_time'] = $add_time;
            $combo_diy['num'] = $val['num'];
            $sku_price = empty($val['price']) ? ($combo_diy['price'] == '0.000' ? $combo_diy['sell_price'] : $combo_diy['price']) : $val['price'];
            $combo_diy['price'] = $sku_price;
            unset($combo_diy['sell_price']);
            $combo_arr[$combo_code]['combo_diy'][] = $combo_diy;

            //套餐条码主信息
            $combo_barcode = array();

            if($power['spec_power'] == 1){
                $combo_barcode['goods_code'] = $combo_code_1;
                $combo_barcode['spec1_code'] = $spec1_code;
                $combo_barcode['spec2_code'] = $spec2_code;                
            }else{
                $combo_barcode['goods_code'] = $combo_code;
                $combo_barcode['spec1_code'] = '000';
                $combo_barcode['spec2_code'] = '000';                
            }

            $combo_barcode['sku'] = $combo_sku;
            if($power['spec_power'] == 1){
                $combo_barcode['barcode'] = $val['combo_barcode'];
            }else{
                $combo_barcode['barcode'] = $combo_code;                
            }


            if (!in_array($barcode, $price_arr[$combo_code]['barcode'])) {
                $price_arr[$combo_code]['barcode'][] = $barcode;
                $price_arr[$combo_code]['price'] += $sku_price * $val['num'];
            } else {
                $price_arr[$combo_code]['price'] = $sku_price * $val['num'];
            }
            $combo_barcode['price'] = $price_arr[$combo_code]['price'];
            $combo_barcode['add_time'] = $add_time;
            $combo_arr[$combo_code]['combo_barcode'][] = $combo_barcode;

            //套餐规格1
            $combo_spec1 = array();
            $combo_spec1['goods_code'] = $combo_code;
            if($power['spec_power'] == 1){
                $combo_spec1['spec1_code'] = $val['spec1_code']; 
            }else{
                $combo_spec1['spec1_code'] = '000';                
            }            

            $combo_arr[$combo_code]['combo_spec1'][] = $combo_spec1;

            //套餐规格2
            $combo_spec2 = array();
            $combo_spec2['goods_code'] = $combo_code;
            if($power['spec_power'] == 1){
                $combo_spec2['spec2_code'] = $val['spec2_code']; 
            }else{
                $combo_spec2['spec2_code'] = '000';                
            } 
            $combo_arr[$combo_code]['combo_spec2'][] = $combo_spec2;

            $right_combo[$combo_code][] = $val;
            $exists_barcode[$combo_code][] = $barcode;
            $sku_list[] = $val['combo_barcode'].$val['spec1_code'].$val['spec2_code'];
            $bar[] = $val['combo_barcode'];
            $s_list[] = $val['goods_code'].$val['spec1_code'].$val['spec2_code'];
            $arr_list[$val['combo_barcode']] = $val['goods_code'];
        }
        unset($price_arr, $sku_arr, $barcode_child, $error_combo);
        if (!empty($combo_arr)) {
            $combo_main_arr = array();
            $combo_barcode_arr = array();
            $combo_diy_arr = array();
            $combo_spec1_arr = array();
            $combo_spec2_arr = array();
            foreach ($combo_arr as $val) {
                $combo_main_arr[] = $val['combo'];
                foreach($val['combo_barcode'] as $v){
                    $combo_barcode_arr[][] = $v;
                }

                $combo_diy_arr = array_merge($combo_diy_arr, $val['combo_diy']);
                $combo_spec1_arr = array_merge($combo_spec1_arr, $val['combo_spec1']);
                $combo_spec2_arr = array_merge($combo_spec2_arr, $val['combo_spec2']);
            }
            unset($combo_arr);

            $this->begin_trans();
            //如果套餐已存在，先清空其明细再导入新明细
            $combo_code_arr = deal_array_with_quote(array_column($combo_main_arr, 'goods_code'));
            $sql = "DELETE FROM goods_combo_diy WHERE p_goods_code IN(SELECT goods_code FROM goods_combo WHERE goods_code IN({$combo_code_arr}) AND status=0)";
            $ret = $this->query($sql);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '处理失败');
            }

            $update_str = 'goods_name=VALUES(goods_name)';
            $ret = $this->insert_multi_duplicate('goods_combo', $combo_main_arr, $update_str);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '导入套餐主信息失败');
            }
            
            $update_str = 'price=VALUES(price),barcode=VALUES(barcode)';
            foreach($combo_barcode_arr as $key=>$val){
                $ret = $this->insert_multi_duplicate('goods_combo_barcode', $val, $update_str);
                if ($ret['status'] != 1) {
                    $this->rollback();
                    return $this->format_ret(-1, '', '导入套餐条码主信息失败');
                }
            }


            $update_str = 'num=VALUES(num),price=VALUES(price)';
            $ret = $this->insert_multi_duplicate('goods_combo_diy', $combo_diy_arr, $update_str);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '导入套餐明细失败');
            }

            $ret = $this->insert_multi_exp('goods_combo_spec1', $combo_spec1_arr, true);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '处理失败');
            }

            $ret = $this->insert_multi_exp('goods_combo_spec2', $combo_spec2_arr, true);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '处理失败');
            }
            $this->commit();
        }

        $success_num = count($combo_diy_arr);
        $fail_num = count($error_info);
        $msg = "导入成功：{$success_num}条";
        $status = 1;
        if (!empty($error_info)) {
            $msg .= "，导入失败：{$fail_num}条";
            if($power['spec_power'] == 1){
               $fail_top = array('套餐编码', '套餐条形码', '套餐名称', '规格1代码', '规格2代码', '子商品条形码','子商品数量','子商品价格','错误信息'); 
            }else{
               $fail_top = array('套餐条形码', '套餐名称', '子商品条形码', '子商品数量', '子商品价格', '错误信息'); 
            }
            $msg .= $this->create_fail_file($error_info, $fail_top, '套餐商品导入错误信息');
            $status = '-1';
        }
        if ($success_num >0) {
            //系统操作日志
            $operate_xq = '套餐商品导入'; //操作详情
            $yw_code = ''; //业务编码          
            $module = '商品'; //模块名称
            $operate_type = '导入';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_xq' => $operate_xq, 'operate_type' => $operate_type);
            load_model('sys/OperateLogModel')->insert($log);
        }       
        return $this->format_ret($status, '', $msg);
    }

    /**
     * 获取存在的barcode信息
     * @param int $is_get_price 是否获取价格
     * @param int $is_deal 是否处理数据
     */
    private function get_exists_barcode(&$data, $is_get_price = 0, $is_deal = 1) {
        if ($is_deal == 1) {
            $barcode_arr = array_column($data, 'barcode');
        } else {
            $barcode_arr = $data;
        }
        $barcode_str = deal_array_with_quote($barcode_arr);
        $field = 'gs.barcode,gs.sku,gs.spec1_code,gs.spec2_code,bg.goods_code';
        $field .= $is_get_price === 1 ? ',gs.price,bg.sell_price' : '';
        $sql = "SELECT {$field} FROM goods_sku AS gs LEFT JOIN base_goods AS bg ON gs.goods_code=bg.goods_code WHERE gs.barcode IN({$barcode_str})";
        $sku_arr = $this->db->get_all($sql);

        $arr = array();
        foreach ($sku_arr as $val) {
            $barcode = $val['barcode'];
            unset($val['barcode']);
            $arr[$barcode] = $val;
        }

        return $arr;
    }

    /**
     * 获取商品子条码
     */
    private function get_barcode_child() {
        $sql = "SELECT barcode FROM goods_barcode_child";
        $barcode_child = $this->db->get_all($sql);
        $barcode_child = array_column($barcode_child, 'barcode');
        return $barcode_child;
    }

    /**
     * 检查必填项
     */
    private function check_required(&$data, $type) {
        $power = load_model('sys/SysParamsModel')->get_val_by_code('spec_power');
        $field = array();
        switch ($type) {
            case 'combo':
                if($power['spec_power'] == 1){
                $field = array('goods_code'=>'套餐编码','combo_barcode'=>'套餐条形码','goods_name'=> '套餐名称','spec1_code'=> '规格1代码', 'spec2_code'=>'规格2代码','barcode'=> '子商品条形码','num'=>'子商品数量');
                break;                    
                }else{
                $field = array('combo_barcode' => '套餐条形码', 'goods_name' => '套餐名称', 'barcode' => '子商品条形码', 'num' => '子商品数量');
                break;
                }
            case 'diy':
                if($power['spec_power'] == 1){
                $field = array('diy_code'=>'组装编码', 'goods_name'=>'组装名称','spec1_code'=> '规格1代码', 'spec2_code'=>'规格2代码','diy_barcode'=> '组装条形码','category_name'=> '分类名称','brand_name'=>'品牌名称','barcode'=>'子商品条码','num'=>'子商品数量');
                break;                    
                }else{
                $field = array('diy_barcode' => '组装条形码', 'goods_name' => '组装名称', 'category_name' => '分类', 'brand_name' => '品牌', 'barcode' => '子商品条形码', 'num' => '子商品数量');
                break;                    
                }

        }

        $fail_info = '';
        foreach ($field as $key => $val) {
            $d = $data[$key];
            if (empty($d)) {
                $fail_info = $val . '不能为空';
                break;
            }
            $d = (int) $d;
            if ($key == 'num' && (!is_int($d) || $d <= 0)) {
                $fail_info = $val . '必须为正整数';
                break;
            }
        }
        return $fail_info;
    }

    /**
     * 创建导出信息csv文件
     * @param array $msg 信息数组
     * @return string 文件地址
     */
    private function create_fail_file($msg, $fail_top, $name) {
        require_lib('csv_util');
        $csv_obj = new execl_csv();
        $file_name = $csv_obj->create_fail_csv_files($fail_top, $msg, $name);
//        $message = "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name={$name}\" >下载</a>";
        $url = set_download_csv_url($file_name,array('export_name'=>'error'));
        $message = "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        return $message;
    }

    /**
     * 读取套餐/组装excel商品信息
     * @param string $file 文件地址
     * @param array $data 数据集
     */
    private function read_goods_file($file, &$data, $type) {
        $field = array();
        $power = load_model('sys/SysParamsModel')->get_val_by_code('spec_power');
        if($power['spec_power'] == 1){
            switch ($type) {
            case 'combo':
                $field = array('goods_code','combo_barcode', 'goods_name','spec1_code','spec2_code', 'barcode', 'num', 'price');
                break;
            case 'diy':
                $field = array('diy_code', 'goods_name' , 'goods_short_name', 'spec1_code','spec2_code','diy_barcode', 'category_name', 'brand_name', 'barcode', 'num', 'price');
                break;
            }
        }else{
            switch ($type) {
            case 'combo':
                $field = array('combo_barcode', 'goods_name', 'barcode', 'num', 'price');
                break;
            case 'diy':
                $field = array('diy_barcode', 'goods_name', 'goods_short_name', 'category_name', 'brand_name', 'barcode', 'num', 'price');
                break;
            }
        }
        

        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i < 1 || empty($row)) {
                $i++;
                continue;
            }
            $arr = array();
            foreach ($field as $key => $val) {
                $arr[$val] = trim($row[$key]);
            }
            $data[] = $arr;

            $i++;
        }
        fclose($file);

        return $i;
    }

    /**
     * 组装商品导入
     */
    function import_goods_diy($file) {
        $power = load_model('sys/SysParamsModel')->get_val_by_code('spec_power');        
        $data = array();
        //读取文件
        $file_num = $this->read_goods_file($file, $data, 'diy');
        //获取存在的barcode信息
        $sku_arr = $this->get_exists_barcode($data, 1);
        //获取商品子条码
        $barcode_child = $this->get_barcode_child();
        //获取已存在的非组装商品/条形码
        $sql = 'SELECT gc.goods_code,gb.barcode FROM base_goods AS gc INNER JOIN goods_barcode AS gb ON gc.goods_code=gb.goods_code WHERE gc.diy=0 AND gb.barcode IS NOT NULL';
        $no_diy = $this->db->get_all($sql);
        $no_diy_barcode = array_column($no_diy, 'barcode');
        $no_diy_goods = array_column($no_diy, 'goods_code');
        $no_diy_goods = array_unique($no_diy_goods);
        //print_r($data);
        //获取已启用的组装商品
        if($power['spec_power'] == 1){
        $diy_code_arr = deal_array_with_quote(array_column($data, 'diy_code'));            
        }else{
        $diy_code_arr = deal_array_with_quote(array_column($data, 'diy_barcode'));            
        }
        $diy_active_arr = $this->db->get_all("SELECT goods_code FROM base_goods WHERE goods_code IN({$diy_code_arr}) AND status=0");
        $diy_active_arr = array_column($diy_active_arr, 'goods_code');

        //获取系统品牌信息
        $brand = $this->db->get_all('SELECT brand_code,brand_name FROM base_brand');
        $brand = array_column($brand, 'brand_code', 'brand_name');
        //获取系统分类信息
        $category = $this->db->get_all('SELECT category_code,category_name FROM base_category');
        $category = array_column($category, 'category_code', 'category_name');
        //获取通用规格名

        $diy_arr = array();
        $price_arr = array();
        $error_info = array();
        $error_diy = array();
        $right_diy = array();
        $exists_barcode = array();
        $exists_diy = array();
        $sku_list[] = array();
        $bar[] = array();
        $s_list[] = array();
        $arr_list[] = array();
        //print_r($data);
        foreach ($data as $val) {
            //print_r($exists_diy);
            $error_msg = array();
            $check_info = $this->check_required($val, 'diy');
            if ($check_info !== '') {
                $val['error_msg'] = $check_info;
                $error_info[] = $val;
                if (!empty($val['diy_barcode']) && isset($diy_arr[$val['diy_barcode']])) {
                    unset($diy_arr[$val['diy_barcode']]);
                }
                continue;
            }
            $diy_code = $val['diy_barcode'];
            $sql_check_1 = "select goods_code,spec1_code,spec2_code from goods_sku where barcode='{$val['diy_barcode']}'";
            $check = $this->db->get_row($sql_check_1);
            if($power['spec_power'] == 1){
                $diy_code_1 = $val['diy_code'];
                if($val['diy_code'] != $check['goods_code'] && !empty($check['goods_code'])){
                    $error_msg[] = '组装条形码已存在';
                }
                $spec1_exist = load_model('prm/Spec1Model')->get_by_code($val['spec1_code']);
                if(empty($spec1_exist['data'])){
                  $error_msg[] = '组装规格1不存在'; 
                }
                $spec2_exist = load_model('prm/Spec2Model')->get_by_code($val['spec2_code']);
                if(empty($spec2_exist['data'])){
                  $error_msg[] = '组装规格2不存在'; 
                }
                if($val['diy_code'] == $check['goods_code'] && !empty($check['goods_code']) && ($val['spec1_code'] != $check['spec1_code'] || $val['spec2_code'] != $check['spec2_code'])){
                    $error_msg[] = '组装规格与之前的组装不一致';
                }
                if($arr_list[$val['diy_barcode']] != $val['diy_code'] && !empty($arr_list[$val['diy_barcode']])){
                    $error_msg[] = '导入的组装商品信息不一致';
                }
            }else{
                if($val['diy_barcode'] != $check['goods_code'] && !empty($check['goods_code'])){
                    $error_msg[] = '组装条形码已存在';
                }                
            }
            $barcode = $val['barcode'];            
            if ($diy_code == $barcode) {
                $error_msg[] = '组装条形码和组装子商品条码不能相同';
            }
            if (in_array($diy_code, $no_diy_goods)) {
                $error_msg[] = '组装条形码不能与系统商品编码相同';
            }
            if (in_array($diy_code, $no_diy_barcode)) {
                $error_msg[] = '组装条形码不能与系统商品条形码相同';
            }
            if (in_array($diy_code, $barcode_child)) {
                $error_msg[] = '组装条形码不能与系统商品子条码相同';
            }
            if($power['spec_power'] == 1){
            if (in_array($diy_code_1, $diy_active_arr)) {
                $error_msg[] = $diy_code . '组装商品已启用，不能更新';
            }                
            }else{
             if (in_array($diy_code, $diy_active_arr)) {
                $error_msg[] = $diy_code . '组装商品已启用，不能更新';
            }               
            }

            if (in_array($barcode, $exists_barcode[$val['diy_barcode']])) {
                $error_msg[] = '组装商品存在相同的子商品条码';
            }
            if($power['spec_power'] == 1){
             $sku_data = $val['diy_barcode'].$val['spec1_code'].$val['spec2_code'];
            $bar_data = $val['diy_barcode'];
            $s_data = $val['diy_code'].$val['spec1_code'].$val['spec2_code'];
            if(!in_array($sku_data, $sku_list) && in_array($bar_data,$bar)){
                $error_msg[] = '同条码的组装商品规格错误';
                }
                if(!in_array($bar_data,$bar) && in_array($s_data,$s_list)){
                $error_msg[] = '不同条码相同编码的组装商品规格重复';    
                }   
            }
            
            if (!array_key_exists($val['category_name'], $category)) {
                $error_msg[] = $val['category_name'] . '分类不存在';
            }
            if (!array_key_exists($val['brand_name'], $brand)) {
                $error_msg[] = $val['brand_name'] . '品牌不存在';
            }
            if (!array_key_exists($val['barcode'], $sku_arr)) {
                $error_msg[] = $val['barcode'] . '组装子商品条形码不存在';
            }
            if (!empty($error_msg)) {
                $error_msg = implode(' | ', $error_msg);
                $val['error_msg'] = $error_msg;
                $error_info[] = $val;
                if (isset($right_diy[$diy_code])) {
                    $error_info = array_merge($error_info, $right_diy[$diy_code]);
                }
                $error_diy[$diy_code] = $diy_code;
                $right_diy[$diy_code] = array();
            }

            if (in_array($diy_code, $error_diy)) {
                if (empty($error_msg)) {
                    $val['error_msg'] = '输入的条形码在导入中存在错误';
                    $error_info[] = $val;
                }
                if (isset($diy_arr[$diy_code])) {
                    unset($diy_arr[$diy_code]);
                }
                continue;
            }

            $add_time = date('Y-m-d H:i:s', time());
            if($power['spec_power'] == 1){
                $sku = $diy_code_1.$val['spec1_code'].$val['spec2_code'];
            }else{
                $sku = $diy_code . '000000';
            }

            //组装商品主信息
            $goods = array();
            if($power['spec_power'] == 1){
            $goods['goods_code'] = $diy_code_1;                
            }else{
            $goods['goods_code'] = $diy_code;                
            }
            $goods['goods_name'] = $val['goods_name'];
            $goods['goods_short_name'] = $val['goods_short_name'];
            $goods['category_code'] = $category[$val['category_name']];
            $goods['category_name'] = $val['category_name'];
            $goods['brand_code'] = $brand[$val['brand_name']];
            $goods['brand_name'] = $val['brand_name'];
            $goods['diy'] = 1;
            $goods['status'] = 0;
            $diy_arr[$diy_code]['goods'] = $goods;

            //组装sku\barcode信息
            $goods_sku = array();
            if($power['spec_power'] == 1){
                $spec1_name = get_spec1_name_by_code($val['spec1_code']);
                $spec2_name = get_spec2_name_by_code($val['spec2_code']);
                $goods_sku['spec1_code'] = $val['spec1_code'];
                $goods_sku['spec1_name'] = $spec1_name;
                $goods_sku['spec2_code'] = $val['spec2_code'];
                $goods_sku['spec2_name'] = $spec2_name;                
            }else{
                $spec1_name = get_spec1_name_by_code('000');
                $spec2_name = get_spec2_name_by_code('000');
                $goods_sku['spec1_code'] = '000';
                $goods_sku['spec1_name'] = $spec1_name;
                $goods_sku['spec2_code'] = '000';
                $goods_sku['spec2_name'] = $spec2_name;
            }
            if($power['spec_power'] == 1){
            $goods_sku['goods_code'] = $diy_code_1;               
            }else{
            $goods_sku['goods_code'] = $diy_code;               
            }

            $goods_sku['sku'] = $sku;
            if($power['spec_power'] == 1){
                $goods_sku['barcode'] = $val['diy_barcode'];
            }else{
                $goods_sku['barcode'] = $diy_code;                
            }
            $sku_price = empty($val['price']) ? ($sku_arr[$barcode]['price'] == '0.000' ? $sku_arr[$barcode]['sell_price'] : $sku_arr[$barcode]['price'])  : $val['price'];
            if (!in_array($barcode, $price_arr[$diy_code]['barcode'])) {
                $price_arr[$diy_code]['barcode'][] = $barcode;
                $price_arr[$diy_code]['price'] += $sku_price * $val['num'];
            } else {
                $price_arr[$diy_code]['price'] = $sku_price * $val['num'];
            }
            $goods_sku['price'] = $price_arr[$diy_code]['price'];
            $goods_sku['add_time'] = $add_time;
            $diy_arr[$diy_code]['goods_sku'][] = $goods_sku;

            //组装子商品信息
            $goods_diy = $sku_arr[$val['barcode']];
            if($power['spec_power'] == 1){
            $goods_diy['p_goods_code'] = $diy_code_1;               
            }else{
            $goods_diy['p_goods_code'] = $diy_code;              
            }
            $goods_diy['p_sku'] = $sku;
            $goods_diy['add_time'] = $add_time;
            $goods_diy['num'] = $val['num'];
            $goods_diy['price'] = $sku_price;
            $diy_arr[$diy_code]['goods_diy'][] = $goods_diy;

            $right_diy[$diy_code][] = $val;
            $exists_barcode[$val['diy_barcode']][] = $barcode;
            $exists_diy[] = $val['diy_barcode'];
            $sku_list[] = $val['diy_barcode'].$val['spec1_code'].$val['spec2_code'];
            $bar[] = $val['diy_barcode'];
            $s_list[] = $val['diy_code'].$val['spec1_code'].$val['spec2_code'];
            $arr_list[$val['diy_barcode']] = $val['diy_code'];
        }
        unset($error_diy, $right_diy, $exists_barcode, $barcode_child, $sku_arr, $no_diy_goods, $diy_active_arr, $brand, $category);
        if (!empty($diy_arr)) {
            $goods_arr = array();
            $goods_sku_arr = array();
            $goods_diy_arr = array();
            foreach ($diy_arr as $val) {
                $goods_arr[] = $val['goods'];
                $goods_sku_arr = array_merge($goods_sku_arr, $val['goods_sku']);
                $goods_diy_arr = array_merge($goods_diy_arr, $val['goods_diy']);             
            }            
            unset($diy_arr);

            $this->begin_trans();
            //如果组装商品已存在，先清空其明细再导入新明细
            $diy_code_arr = deal_array_with_quote(array_column($goods_arr, 'goods_code'));
            $sql = "DELETE FROM goods_diy WHERE p_goods_code IN(SELECT goods_code FROM base_goods WHERE goods_code IN({$diy_code_arr}) AND status=1)";
            $ret = $this->query($sql);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '处理失败');
            }

            $update_str = 'goods_name=VALUES(goods_name),goods_short_name=VALUES(goods_short_name)';
            $ret = $this->insert_multi_duplicate('base_goods', $goods_arr, $update_str);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '导入组装商品主信息失败');
            }

            $update_str = 'price=VALUES(price),barcode=VALUES(barcode),sku=VALUES(sku)';
            $ret = $this->insert_multi_duplicate('goods_sku', $goods_sku_arr, $update_str);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '导入组装商品sku信息失败');
            }
            $update_str = 'barcode=VALUES(barcode),sku=VALUES(sku)';
            $ret = $this->insert_multi_duplicate('goods_barcode', $goods_sku_arr, $update_str);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '导入组装商品条码信息失败');
            }

            $update_str = 'num=VALUES(num)';
            $ret = $this->insert_multi_duplicate('goods_diy', $goods_diy_arr, $update_str);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '导入组装子商品信息失败');
            }

            $this->commit();
        }
        $success_num = count($goods_diy_arr);
        $fail_num = count($error_info);
        $msg = "导入成功：{$success_num}条";
        $status = 1;
        if (!empty($error_info)) {
            $msg .= "，导入失败：{$fail_num}条";
            if($power['spec_power'] == 1){
               $fail_top = array('组装编码', '组装名称', '组装简称', '规格1代码', '规格2代码', '组装条形码', '分类名称','品牌名称','子商品条码','子商品数量','子商品单价'); 
            }else{
               $fail_top = array('组装条形码', '组装名称', '组装简称', '分类名称', '品牌名称', '子商品条形码', '子商品数量','错误信息');
            }

            $msg .= $this->create_fail_file($error_info, $fail_top, '组装商品导入错误信息');
            $status = '-1';
        }
        if ($success_num>0) {
            //系统操作日志
            $operate_xq = '组装商品导入'; //操作详情
            $yw_code = ''; //业务编码          
            $module = '商品'; //模块名称
            $operate_type = '导入';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_xq' => $operate_xq, 'operate_type' => $operate_type);
            load_model('sys/OperateLogModel')->insert($log);
        }      
        return $this->format_ret($status, '', $msg);
    }

    /**
     * 商品价格导入
     * @param $file
     * @return array
     */
    function import_goods_price($file) {
        $this->read_csv_goods_price($file, $goods_code_arr, $goods_info);
        if (empty($goods_code_arr)) {
            return $this->format_ret('-1', '', '无商品编码！');
        }
        $all_num = count($goods_info);
        if ($all_num > 5000) {
            return $this->format_ret('-1', '', '单次导入仅支持5000条数据！');
        }
        $sql_value = array();
        $goods_code_str = $this->arr_to_in_sql_value($goods_code_arr, 'goods_code', $sql_value);
        $sql = "SELECT * FROM base_goods WHERE goods_code IN({$goods_code_str})";
        $goods = $this->db->get_all($sql, $sql_value);
        $goods_detail = array();
        foreach ($goods as $value) {
            $goods_detail[$value['goods_code']] = $value;           
        }
        $old_goods_code = array_column($goods, 'goods_code');
        $error_msg = array();
        $insert_params = array();
        $log_arr = array();
        foreach ($goods_info as $detail) {
            $goods_code = $detail['goods_code'];
            if (!in_array($goods_code, $old_goods_code)) {
                $error_msg[] = array($goods_code . "\t" => '商品编码在系统中不存在！');
                continue;
            }
            $goods_code = $detail['goods_code'];
            $log_msg = '';
            $old_goods_info = $goods_detail[$goods_code];
            $insert_params[$goods_code]['goods_code'] = $goods_code;
            if (!empty($detail['sell_price']) || $detail['sell_price'] == '0.000') {
                $insert_params[$goods_code]['sell_price'] = $detail['sell_price'];
                $log_msg .= ($old_goods_info['sell_price'] != $detail['sell_price']) ? "吊牌价由{$old_goods_info['sell_price']}改为{$detail['sell_price']}" : '';
            }
            if (!empty($detail['cost_price']) || $detail['cost_price'] == '0.000') {
                $insert_params[$goods_code]['cost_price'] = $detail['cost_price'];
                $log_msg .= ($old_goods_info['cost_price'] != $detail['cost_price']) ? "成本价由{$old_goods_info['cost_price']}改为{$detail['cost_price']}" : '';
            }
            if (!empty($detail['trade_price']) || $detail['trade_price'] == '0.000') {
                $insert_params[$goods_code]['trade_price'] = $detail['trade_price'];
                $log_msg .= ($old_goods_info['trade_price'] != $detail['trade_price']) ? "批发价由{$old_goods_info['trade_price']}改为{$detail['trade_price']}" : '';
            }
            if (!empty($detail['purchase_price']) || $detail['purchase_price'] == '0.000') {
                $insert_params[$goods_code]['purchase_price'] = $detail['purchase_price'];
                $log_msg .= ($old_goods_info['purchase_price'] != $detail['purchase_price']) ? "进货价由{$old_goods_info['purchase_price']}改为{$detail['purchase_price']}" : '';
            }
            if (!empty($detail['min_price']) || $detail['min_price'] == '0.000') {
                $insert_params[$goods_code]['min_price'] = $detail['min_price'];
                $log_msg .= ($old_goods_info['min_price'] != $detail['min_price']) ? "最低售价由{$old_goods_info['min_price']}改为{$detail['min_price']}" : '';
            }
            if (!empty($log_msg)) {
                $log_arr[$old_goods_info['goods_id']] = array(
                    'goods_id' => $old_goods_info['goods_id'],
                    'operation_note' => $log_msg,
                    'operation_name' => '导入商品价格',
                    'user_code' => CTX()->get_session('user_code'),
                    'user_id' => CTX()->get_session('user_id'),
                    'add_time' => time(),
                );
            }
        }

        if (!empty($insert_params)) {
            foreach ($insert_params as $import_goods_code => $import_info) {
                unset($import_info['goods_code']);
                $ret = $this->update_exp('base_goods', $import_info, array('goods_code' => $import_goods_code));
                if ($ret['status'] != 1) {
                    return $this->format_ret('-1', '', '更新失败！');
                }
            }
        }
        $ret = $this->format_ret(1, '', '导入成功！');
        //添加日志
        if (!empty($log_arr)) {
            $this->insert_multi_exp('base_goods_log', $log_arr);
        }
        $success_num = $all_num - $err_num;
        $message = '导入成功' . $success_num;
        if (!empty($error_msg)) {
            $err_num = count($error_msg);            
            $ret['status'] = '-1';
            $message .= ',' . '失败数量:' . $err_num;
            $fail_top = array('商品编码', '错误信息');
            $file_name = load_model('stm/StockLockRecordModel')->create_import_fail_files($fail_top, $error_msg);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
            $ret = $this->format_ret(-1, '', $message);
        }
        if ($success_num>0){
            //系统操作日志
            $operate_xq = '商品价格导入'; //操作详情
            $yw_code = ''; //业务编码          
            $module = '商品'; //模块名称
            $operate_type = '导入';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_xq' => $operate_xq, 'operate_type' => $operate_type);
            load_model('sys/OperateLogModel')->insert($log);
        }        
        return $ret;
    }


    function read_csv_goods_price($file, &$goods_code_arr, &$goods_info) {
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $goods_code_arr[] = trim($row[0]);
                    $goods_info[$i]['goods_code'] = trim($row[0]);
                    $goods_info[$i]['sell_price'] = trim($row[1]);
                    $goods_info[$i]['cost_price'] = trim($row[2]);
                    $goods_info[$i]['trade_price'] = trim($row[3]);
                    $goods_info[$i]['purchase_price'] = trim($row[4]);
                    $goods_info[$i]['min_price'] = trim($row[5]);
                }
            }
            $i++;
        }
        fclose($file);
    }
    //导入商品(信息导入）
    function goods_import_exec($file, $type = 'name') {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);
        try {
            $this->begin_trans();
            $csv_data = $this->read_goods_barcode_property_csv($file, $type);
            $arr['goods_arr'] = array();
            $arr['barcode_arr'] = array();
            $arr['spce1_arr'] = array();
            $arr['spce2_arr'] = array();
            $arr['brand_arr'] = array();
            $arr['season_arr'] = array();
            $arr['year_arr'] = array();
            $arr['category_arr'] = array();
            $arr['property_arr'] = array();
            $arr['sku_arr'] = array();
            $msg_arr = array();
            $nece_array = array(
                'goods_code'=>'商品编码',
                'barcode' => '商品条形码',
                'spec1_code'=>'规格1',
                'spec2_code'=>'规格2'
            );
            foreach ($csv_data as $k => &$row) {
                $row['goods_code']=str_replace("\n","",$row['goods_code']);
                $row['barcode']=str_replace("\n","",$row['barcode']);
                $row['spec1_code']=str_replace("\n","",$row['spec1_code']);
                $row['spec2_code']=str_replace("\n","",$row['spec2_code']);
                if (!empty($row['goods_code'])&& !empty($row['barcode']) && !empty($row['spec1_code']) && !empty($row['spec2_code'])) {
                    //商品信息更新中，条码与商品编码，规格对应的条码必须一致，如果都不存在则创建
                    $ret = $this->check_info($row);
                    if($ret['status'] < 0){
                        $msg = $ret['message'];
                    }else{
                        $msg = $this->set_row_arr($arr, $row);
                    }
                    if (!empty($msg)) {
                        unset($csv_data[$k]);
                        $line = $k + 3;
                        $msg_arr[] = '第' . $line . "行" . $msg;
                    }
                }else{
                    unset($csv_data);
                    $msg = '';
                    foreach ($nece_array as $k=>$value){
                        if(!isset($row[$k]) || $row[$k] == ''){
                            $msg .= $value.'为空;';
                        }
                    }
                    $line = $k + 3;
                    $msg_arr[] = '第' . $line . "行" . $msg;
                }
                foreach($row as $key=>$val){
                    if($val === ''){
                        unset($row[$key]);
                    }
                }
            }
            $barcode_arr = $arr['barcode_arr'];
            //设置基础档案
            $this->set_goods_other_base($arr, 'code');
            foreach ($csv_data as $k => $row) {
                $this->set_goods_barcode_arr($arr, $row, $type);
            }
            $this->check_sku_barcode($arr);
            $goods_arr = array_values($arr['goods_arr']);
            if (!empty($goods_arr)) {
                $this->insert_multi_duplicate('base_goods', $goods_arr, $goods_arr);
            }
            if (!empty($arr['property_arr'])) {
                $this->save_property_val($arr['property_arr']);
            }

            if (!empty($arr['barcode_arr'])) {
                $result1 = array();
                $result2 = array();
                foreach ($arr['barcode_arr'] as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        if (isset($vv) && $vv != '') {
                            $result1[$kk] = $vv;
                            continue;
                        }
                    }
                    $result2[$k] = $result1;
                    unset($result1);
                }
                $result_update = array_values($result2);
                $this->insert_multi_duplicate('goods_barcode', $result2, $result_update);
                $this->insert_multi_duplicate('goods_sku', $result2, $result_update);
                $this->update_lastchanged($result_update);
            }
            $this->commit();
        } catch (Exception $ex) {
            $this->rollback();
            return $this->format_ret(-1, '', $ex->getMessage());
        }
        if (!empty($barcode_arr)) {
            //系统操作日志
            $operate_xq = '商品信息更新导入';
            $yw_code = ''; //业务编码
            $module = '商品'; //模块名称
            $operate_type = '导入';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_xq' => $operate_xq, 'operate_type' => $operate_type);
            load_model('sys/OperateLogModel')->insert($log);
        }
        if (empty($msg_arr)) {
            return $this->format_ret(1);
        } else {
            if (!empty($msg_arr)) {
                $file_name = $this->create_import_fail_files($msg_arr, 'goods_mix_import_fail');
                $url = set_download_csv_url($file_name,array('export_name'=>'error'));
                $msg = "部分导入失败，失败信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";

                return array('status' => '-1',
                    'data' => '',
                    'message' => $msg
                );
            }
        }
        $barcord_arr = array();
        $err_barcorde = '';
        $err_goods = '';
        $err_msg = '';
        if (!empty($data['err'])) {
            $err_goods = join(",", $data['err']);
        }
        foreach ($data['chushi_data'] as $k1 => $v1) {
            if ($v1[17] <> '') {
                $i = 0;
                foreach ($data['chushi_data'] as $k2 => $v2) {
                    if ($v2[17] == $v1[17]) {
                        $i++;
                    }
                    if ($i >= 2) {

                        $repeat_arr[$v1[17]] = $v1[17];

                        break;
                    }
                }
            }
            $barcord_arr[$k1] = array($v1[0], $v1[15], $v1[16], $v1[17], $v1[10], $v1[9], $v1[18]);
        }
        if ($err_msg) {
            return array('status' => '-1',
                'data' => '',
                'message' => $err_msg
            );
        }
        $data['data_barcord_arr'] = $barcord_arr;
        $property_all = array();
        //转换扩展属性格式
        $property_num = 27;
        $property_str = 'property_val';
        foreach ($data['data_good_arr'] as $k => $v) {
            $j = 1;
            $property['property_val_code'] = $v[0];
            $property['property_type'] = 'goods';
            for ($i = 18; $i <= $property_num; $i++) {
                $property[$property_str . $j] = $v[$i];

                $j++;
            }
            $property_all[$v[0]] = $property;
            $goods_arr[] = $v[0];
        }

        $data_property = array('property_all' => $property_all,
            'goods_arr' => $goods_arr
        );

        //转换格式
        foreach ($data['data_good_arr'] as $k => $v) {
            foreach ($v as $k1 => $v1) {
                if ($k1 > 14) {
                    unset($data['data_good_arr'][$k][$k1]);
                }
            }
        }
        //导入商品
        $ret_goods = $this->import_base_goods_c($data);
        //导入规格
        $ret_spec = $this->good_spec_c_hunhe($data);
        $ret_property = $this->import_goods_property_c($data_property);
        //不判断商品编码重复
        if ($ret_goods['data'] <> '' || $err_goods <> '') {
            $err_msg .= '系统存在商品编码' . $ret_goods['data'] . 'excel表格重复商品编码:' . $err_goods;
        }

        if ($ret_spec['data'] <> '' || $err_barcorde <> '') {
            $err_msg .= $ret_spec['data'];
        }

        if ($err_msg <> '') {

            $file_name = $this->create_import_fail_files($err_msg, 'goods_mix_import_fail');
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $msg .= "部分导入失败，失败信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
            return array('status' => '-1',
                'data' => '',
                'message' => $msg
            );
        } else {
            return $ret_property;
        }
    }
    //校验记录唯一性
    public function check_info(&$row){
        $barcode = $row['barcode'];
        $condition = array(
            'goods_code'=>$row['goods_code'],
            'spec1_code'=>$row['spec1_code'],
            'spec2_code'=>$row['spec2_code']
        );
        $gb_code = $row['gb_code'];
        $barcode1_array = $this->get_goods_info($condition,'sku_id');
        $barcode2_array = $this->get_goods_info(array('barcode'=>$barcode),'sku_id');
        $barcode3_array = $this->get_goods_info(array('gb_code'=>$gb_code),'sku_id');
        $sku_id1 = empty($barcode1_array) ? '' : $barcode1_array['sku_id'];
        $sku_id2 = empty($barcode2_array) ? '' : $barcode2_array['sku_id'];
        $sku_id3 = empty($barcode3_array) ? '' : $barcode3_array['sku_id'];
        if($sku_id1 != $sku_id2 || ($sku_id2 != $sku_id3 && $sku_id3 != '')){
            return $this->format_ret(-1,'','已存在的条码商品编码,国标码和规格信息不符');
        }else{
            return $this->format_ret(1);
        }
    }
    //获取商品单条记录
    public function get_goods_info($condition,$select){
        if(empty($condition)) return array();
        $sql_values = array();
        $sql = 'select '.$select.' from goods_sku where 1=1 ';
        foreach ($condition as $key=>$value){
            $sql_values[':'.$key] = $value;
            $sql .= ' and '.$key.'= :'.$key;
        }
        return $this->db->get_row($sql,$sql_values);
    }




}
