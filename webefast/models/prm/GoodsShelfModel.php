<?php

require_model('tb/TbModel');
require_lib('util/oms_util', true);

//require_lang('prm');

class GoodsShelfModel extends TbModel {

    /**
     * @var string 表名
     */
    protected $table = 'goods_shelf';
    private $default_lof = '';
    private $barcode = null;
    private $store = null;
    private $shelf = null;
    private $lof = null;

    private function str_chunk($s) {
        $arr = explode(',', $s);
        foreach ($arr as &$code) {
            $code = "'" . $this->db->escape($code) . "'";
        }
        return implode(",", $arr);
    }

    function get_goods_shelf($store_code, $sku, $lof_no = '', $production_date = '') {
        $sql = " select b.shelf_name from base_shelf b
                INNER JOIN goods_shelf g ON b.shelf_code = g.shelf_code AND b.store_code = g.store_code
                where g.store_code=:store_code  and g.sku = :sku   ";
        $sql_values = array(':store_code' => $store_code, ':sku' => $sku);
        if ($lof_no != '') {
            $sql .=" AND batch_number=:lof_no ";
            $sql_values[':lof_no'] = $lof_no;
        }
        $data = $this->db->get_all($sql, $sql_values);
        $arr = array();
        foreach ($data as $val) {
            $arr[] = $val['shelf_name'];
        }
        return $arr;
    }

    function get_shelf_by_page($invId, $filter) {

        $sql_values = array();
        $sql_main = " FROM base_shelf rl LEFT JOIN base_store r2 on r2.store_code = rl.store_code WHERE  rl.status = 1  ";

        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $str = $this->str_chunk($filter['store_code']);
            $sql_main .= " AND rl.store_code IN ($str) ";
        }
        //编码
        if (isset($filter['shelf_code']) && $filter['shelf_code'] != '') {
            $sql_main .= " AND (rl.shelf_code LIKE :shelf_code OR rl.shelf_name LIKE :shelf_code )";
            $sql_values[':shelf_code'] = $filter['shelf_code'] . '%';
        }
        //ex_list 排除列表
//        if (!empty($filter['ex_list'])) {
//            $keys = implode(',', array_keys($filter['ex_list']));
//            $str = $this->str_chunk($keys);
//            $sql_main .= " AND rl.shelf_code NOT IN ($str) ";
//        }
        //sku
        if (isset($filter['sku']) && $filter['sku'] != '') {
            $sql = "select shelf_code,store_code from goods_shelf where sku = :sku  ";
            $shelf_info = $this->db->get_all($sql, array('sku' => $filter['sku']));
            $shelf_id = array();
            foreach ($shelf_info as $code) {
                $sql_shelf = "SELECT shelf_id FROM base_shelf WHERE shelf_code='{$code['shelf_code']}' AND store_code='{$code['store_code']}'";
                $shelf_id[] = $this->db->get_value($sql_shelf);
            }
            $str = deal_array_with_quote($shelf_id);
            $sql_main .= " AND rl.shelf_id NOT IN ($str) ";
        }

        $select = 'rl.*,r2.store_name';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as $k => $sub_data) {
            $data['data'][$k]['shelf_code_txt'] = $sub_data['shelf_code'] . '<input type="hidden" value="' . $sub_data['shelf_code'] . '"/>';
        }

        return $this->format_ret(1, $data);
    }

    function get_shelf_by_page2($invId, $filter, $request) {
        $sql_values = array();
        $sql_main = " FROM goods_shelf rl LEFT JOIN base_shelf r2 on r2.shelf_code = rl.shelf_code and r2.store_code = rl.store_code  WHERE 1 ";

        //in_list 列表
//        if (!empty($filter['in_list'])) {
//            $keys = implode(',', array_keys($filter['in_list']));
//            $str = $this->str_chunk($keys);
//            $sql_main .= " AND shelf_code IN ($str) ";
//        }

        if (isset($filter['sku']) && $filter['sku'] != '') {
            $sql_main .= " AND rl.sku = :sku";
            $sql_values[':sku'] = $filter['sku'];
        } else {

            $sql_main .= " AND 1 = 2 ";
        }

        $select = 'r2.*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as $k => $sub_data) {
            $data['data'][$k]['shelf_code_txt'] = $sub_data['shelf_code'] . '<input type="hidden" value="' . $sub_data['shelf_code'] . '"/>';
            $data['data'][$k]['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $sub_data['store_code']));
        }
        return $this->format_ret(1, $data);
    }

    function get_by_page($filter) {
        $sql_values = array();
        //INNER JOIN goods_inv_lof r4 on rl.goods_code = r4.goods_code
        $sql_main = "FROM goods_shelf rl
        LEFT JOIN base_goods r2 on rl.goods_code = r2.goods_code
        LEFT JOIN goods_lof r4 on r4.sku = rl.sku AND r4.lof_no=rl.batch_number
        WHERE  1=1  ";

        //分类
        if (isset($filter['category_code']) && $filter['category_code'] != '') {
             $arr = explode(',', $filter['category_code']);
        $str = $this->arr_to_in_sql_value($arr, 'category_code', $sql_values);
            $sql_main .= " AND r2.category_code IN ($str)";
        }
        //品牌
        if (isset($filter['brand_code']) && $filter['brand_code'] != '') {
              $arr = explode(',', $filter['brand_code']);
        $str = $this->arr_to_in_sql_value($arr, 'brand_code', $sql_values);
            $sql_main .= " AND r2.brand_code IN ($str)";
        }
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
               $arr = explode(',', $filter['store_code']);
        $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
            $sql_main .= " AND rl.store_code IN ($str)";
        }
        //库位
        if (isset($filter['shelf_code']) && $filter['shelf_code'] != '') {
        $arr = explode(',', $filter['shelf_code']);
        $str = $this->arr_to_in_sql_value($arr, 'shelf_code', $sql_values);
            $sql_main .= " AND rl.shelf_code IN ($str)";
        }
        //商品编号
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (rl.goods_code LIKE :goods_code )";
            $sql_values[':goods_code'] = $filter['goods_code'] . '%';
        }
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] != '') {
            $sql_main .= " AND (r2.goods_name LIKE :goods_name )";
            $sql_values[':goods_name'] = $filter['goods_name'] . '%';
        }
        //SKU
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $sql_main .= " AND 1=2 ";
            } else {
                $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sql_main .= " AND rl.sku in({$sku_str}) ";
            }
        }

        $select = 'rl.*,r2.goods_name,r4.production_date,r2.goods_name,r2.weight'; //r4.production_date


        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        foreach ($data['data'] as $key => &$value) {
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
            $key_arr = array('spec1_name', 'spec2_name', 'goods_name', 'barcode');

            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $value = array_merge($value, $sku_info);
        }
        return $this->format_ret(1, $data);
    }

    function ex_by_page($filter) {
        $sql_values = array();
        $arr = array('lof_status');
        $arr_lof = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $lof_status = $arr_lof['lof_status'];
        if ($lof_status == 1) {
            $sql_main = "FROM goods_sku gs
        INNER JOIN base_goods on gs.goods_code = base_goods.goods_code
        LEFT JOIN goods_lof on gs.sku = goods_lof.sku
        WHERE not exists (
            select * from goods_shelf gf
            where gf.goods_code = gs.goods_code
            and gf.sku = gs.sku
            and gf.batch_number = goods_lof.lof_no)  ";
        } else {
            $sql_main = "FROM goods_sku  gs
        INNER JOIN base_goods on gs.goods_code = base_goods.goods_code
        WHERE not exists (
            select * from goods_shelf gf
            where gf.goods_code = gs.goods_code
            and gf.sku = gs.sku
           )  ";
        }
        //分类
        if (isset($filter['category_code']) && $filter['category_code'] != '') {
        $arr = explode(',', $filter['category_code']);
        $str = $this->arr_to_in_sql_value($arr, 'category_code', $sql_values);
            $sql_main .= " AND base_goods.category_code IN ($str)";
        }
        //品牌
        if (isset($filter['brand_code']) && $filter['brand_code'] != '') {
             $arr = explode(',', $filter['brand_code']);
        $str = $this->arr_to_in_sql_value($arr, 'brand_code', $sql_values);
            $sql_main .= " AND base_goods.brand_code IN ($str)";
        }
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
               $arr = explode(',', $filter['store_code']);
        $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
            $sql_main .= " AND gf.store_code IN ($str)";
        }
        //库位
        if (isset($filter['shelf_code']) && $filter['shelf_code'] != '') {
            $str = $this->str_chunk($filter['shelf_code']);
            $sql_main .= " AND goods_inv_lof.shelf_code IN ($str)";
        }
        //商品编号
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (base_goods.goods_code LIKE :goods_code )";
            $sql_values[':goods_code'] = $filter['goods_code'] . '%';
        }
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] != '') {
            $sql_main .= " AND (base_goods.goods_name LIKE :goods_name )";
            $sql_values[':goods_name'] = $filter['goods_name'] . '%';
        }
        //SKU
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $sql_main .= " AND 1=2 ";
            } else {
                $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sql_main .= " AND gs.sku in({$sku_str}) ";
            }
        }



        $group = false;
        if ($lof_status == 1) {
            $select = 'gs.sku_id  as goods_inv_id ,gs.*,base_goods.goods_name';
            $select .= ',goods_lof.lof_no,goods_lof.production_date';
        } else {
            $select = 'gs.sku_id  as goods_inv_id ,gs.*,base_goods.goods_name';
            $sql_main .= " group by  gs.sku";
            $group = true;
        }

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, $group);
//        foreach($data['data'] as $key => &$value) {
//           // $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code'=>$value['store_code']));
//            $value['spec1_name'] = oms_tb_val('base_spec1', 'spec1_name', array('spec1_code'=>$value['spec1_code']));
//            $value['spec2_name'] = oms_tb_val('base_spec2', 'spec2_name', array('spec2_code'=>$value['spec2_code']));
//
//        }

        return $this->format_ret(1, $data);
    }

    /**
     * 新增
     * @param $skuCode
     * @param $storeCode
     * @param $shelfCode
     * @param string $batchNumber
     * @return array
     */
    function import_goods_shelf($file, $bischecked,$type='')
    {
        set_time_limit(0);
        require_lib('csv_util');
        $ret_param = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        $lof_status = $ret_param['lof_status'];
        if($type=="goods_code"){
            $key_arr = array('0' => 'goods_code', '1' => 'store_code', '2' => 'shelf_code');
            if ($lof_status == 1) {
                $key_arr = array('0' => 'goods_code', '1' => 'batch_number', '2' => 'store_code', '3' => 'shelf_code');
            }
            $csv_cls = new execl_csv();
            $data = $csv_cls->read_csv($file, 2, $key_arr);
            $success_num = 0;
            $fail_num = 0;
            $faild = array();
            $shelf_data = array();
            foreach ($data as $key => &$v) {
                if (empty($v['goods_code']) && empty($v['store_code']) && empty($v['shelf_code'])) {
                    continue;
                }
                $barcode=$this->db->get_all("select sku,barcode from goods_sku where goods_code=:goods_code",array(':goods_code'=>$v['goods_code']));
                $v['sku']=$barcode;
                $r = $this->set_shelf_data_goods_code($v);
                if ($r['status'] == '1') {
                    $success_num++;
                    $shelf_data[] = $r['data'];
                    //增加系统操作日志
                    $operate_xq = '';
                    $yw_code = '';
                    $store = $this->db->get_row("select store_name from base_store where store_code = :store_code", array(':store_code' => $v['store_code']));
                    $shelf = $this->db->get_row("select shelf_name from base_shelf where shelf_code = :shelf_code and store_code = :store_code", array(':store_code' => $v['store_code'], ':shelf_code' => $v['shelf_code']));
                    $operate_xq = '商品编码:' . $v['goods_code'] . '绑定' . $store['store_name'] . '的' . $shelf['shelf_name'] . '库位；'; //操作详情
                    $yw_code = $r['data']['goods_code']; //业务编码
                    //系统操作日志
                    $module = '基础数据'; //模块名称
                    $operate_type = '绑定';
                    $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_xq' => $operate_xq, 'operate_type' => $operate_type);
                    load_model('sys/OperateLogModel')->insert($log);
                } else {
                    $linenum = $key + 3;
                    $line['line'] = "第" . $linenum . "行";
                    $faild[$line['line']] = $r['message'];
                    $fail_num++;

                }
            }
            if ($lof_status == 1) {
                foreach($shelf_data as $k=>$val){
                    foreach($val["sku"] as $num=>$item){
                        $item['goods_code']=$val['goods_code'];
                        $item['store_code']=$val['store_code'];
                        $item['shelf_code']=$val['shelf_code'];
                        $item['batch_number']=$val['batch_number'];
                        $new_data[]=$item;
                        if($bischecked==1){
                            $bis_data[$val['goods_code']][]=$val['store_code'] . '_' . $item['sku'];
                        }
                    }

                }
            }else{
                foreach($shelf_data as $k=>$val){
                    foreach($val["sku"] as $num=>$item){
                        $item['goods_code']=$val['goods_code'];
                        $item['store_code']=$val['store_code'];
                        $item['shelf_code']=$val['shelf_code'];
                        $new_data[]=$item;
                        if($bischecked==1){
                            $bis_data[$val['goods_code']][]=$val['store_code'] . '_' . $item['sku'];
                        }
                    }

                }
            }

            $this->begin_trans();
            if (!empty($bis_data)) {
                foreach ($bis_data as $val) {
                    $bis_str = "'" . implode("','", $val) . "'";
                    $sql2 = "SELECT goods_code,sku,store_code,shelf_code FROM goods_shelf WHERE CONCAT(store_code,'_',sku) in({$bis_str}) ";
                    $del_ret = $this->db->get_all($sql2);
                    foreach ($del_ret as $v) {
                        //增加系统操作日志
                        $operate_xq = '';
                        $yw_code = '';
                        $store = $this->db->get_row("select store_name from base_store where store_code = :store_code", array(':store_code' => $v['store_code']));
                        $shelf = $this->db->get_row("select shelf_name from base_shelf where shelf_code = :shelf_code and store_code = :store_code", array(':store_code' => $v['store_code'], ':shelf_code' => $v['shelf_code']));
                        $barcode = $this->db->get_row("select barcode from goods_sku where sku = :sku", array(':sku' => $v['sku']));
                        $operate_xq = '商品条形码:' . $barcode['barcode'] . '从' . $store['store_name'] . '的' . $shelf['shelf_name'] . '库位解除绑定；'; //操作详情
                        $yw_code = $v['goods_code']; //业务编码
                        //系统操作日志
                        $module = '基础数据'; //模块名称
                        $operate_type = '解除绑定';
                        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_xq' => $operate_xq, 'operate_type' => $operate_type);
                        load_model('sys/OperateLogModel')->insert($log);
                    }
                    $sql = "DELETE FROM goods_shelf WHERE CONCAT(store_code,'_',sku) in({$bis_str})";
                    $ret = $this->query($sql);
                    if ($ret['status'] != 1) {
                        $this->rollback();
                        return $this->format_ret(-1, '', '覆盖原有库位时失败');
                    }
                }
            }


            $message = '导入成功：' . $success_num . '条';
            $status = 1;
            if (!empty($faild)) {
                $status = -1;
                $message .= ',' . '编码导入失败:' . $fail_num . '条';
                $fail_top = array('编码行数', '错误信息');
                $filename = 'goods_shelf_import';
                $file_name = $this->create_import_fail_files($faild, $fail_top, $filename);
        //            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
                $url = set_download_csv_url($file_name, array('export_name' => 'error'));
                $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
            }

            if (!empty($new_data)) {
                $shelf_arr = array_chunk($new_data, 2000, TRUE);
                foreach ($shelf_arr as $shelf) {
                    $ret = $this->insert_multi($shelf, true);
                    if ($ret['status'] != 1) {
                        $this->rollback();
                        return $this->format_ret(-1, '', '导入过程中出现错误，导入失败');
                    }
                }
            }
            $this->commit();
            $ret['status'] = $status;
            $ret['message'] = $message;
            return $ret;
        }
            $key_arr = array('0' => 'barcode', '1' => 'store_code', '2' => 'shelf_code');
            if ($lof_status == 1) {
                $key_arr = array('0' => 'barcode', '1' => 'batch_number', '2' => 'store_code', '3' => 'shelf_code');
            }
            $csv_cls = new execl_csv();
            $data = $csv_cls->read_csv($file, 2, $key_arr);
            $this->barcode = array_unique(array_column($data, 'barcode'));
            $this->store = array_unique(array_column($data, 'store_code'));
            $this->shelf = array_unique(array_column($data, 'shelf_code'));
            if ($lof_status == 1) {
                $this->lof = array_unique(array_column($data, 'batch_number'));
            }
            $this->set_default_lof();
            $success_num = 0;
            $fail_num = 0;
            $faild = array();
            $shelf_data = array();
            $bis_data = array();
            foreach ($data as $key => $v) {
                if (empty($v['barcode']) && empty($v['store_code']) && empty($v['shelf_code'])) {
                    continue;
                }
                $r = $this->set_shelf_data($v, $bischecked, $lof_status);
                if ($r['status'] == '1') {
                    $success_num++;
                    $shelf_data[] = $r['data'];
                    if ($bischecked == 1) {
                        $bis_data[] = $r['data']['store_code'] . '_' . $r['data']['sku'];
                    }
                    //增加系统操作日志
                    $operate_xq = '';
                    $yw_code = '';
                    $store = $this->db->get_row("select store_name from base_store where store_code = :store_code", array(':store_code' => $v['store_code']));
                    $shelf = $this->db->get_row("select shelf_name from base_shelf where shelf_code = :shelf_code and store_code = :store_code", array(':store_code' => $v['store_code'], ':shelf_code' => $v['shelf_code']));
                    $operate_xq = '商品条形码:' . $v['barcode'] . '绑定' . $store['store_name'] . '的' . $shelf['shelf_name'] . '库位；'; //操作详情
                    $yw_code = $r['data']['goods_code']; //业务编码
                    //系统操作日志
                    $module = '基础数据'; //模块名称
                    $operate_type = '绑定';
                    $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_xq' => $operate_xq, 'operate_type' => $operate_type);
                    load_model('sys/OperateLogModel')->insert($log);
                } else {
                    $linenum = $key + 3;
                    $line['line'] = "第" . $linenum . "行";
                    $faild[$line['line']] = $r['message'];
                    $fail_num++;

                }
            }

            $this->begin_trans();
            if (!empty($bis_data)) {
                $bis_arr = array_chunk($bis_data, 2000, TRUE);
                foreach ($bis_arr as $val) {
                    $bis_str = "'" . implode("','", $val) . "'";
                    $sql2 = "SELECT goods_code,sku,store_code,shelf_code FROM goods_shelf WHERE CONCAT(store_code,'_',sku) in({$bis_str}) ";
                    $del_ret = $this->db->get_all($sql2);
                    foreach ($del_ret as $v) {
                        //增加系统操作日志
                        $operate_xq = '';
                        $yw_code = '';
                        $store = $this->db->get_row("select store_name from base_store where store_code = :store_code", array(':store_code' => $v['store_code']));
                        $shelf = $this->db->get_row("select shelf_name from base_shelf where shelf_code = :shelf_code and store_code = :store_code", array(':store_code' => $v['store_code'], ':shelf_code' => $v['shelf_code']));
                        $barcode = $this->db->get_row("select barcode from goods_sku where sku = :sku", array(':sku' => $v['sku']));
                        $operate_xq = '商品条形码:' . $barcode['barcode'] . '从' . $store['store_name'] . '的' . $shelf['shelf_name'] . '库位解除绑定；'; //操作详情
                        $yw_code = $v['goods_code']; //业务编码
                        //系统操作日志
                        $module = '基础数据'; //模块名称
                        $operate_type = '解除绑定';
                        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_xq' => $operate_xq, 'operate_type' => $operate_type);
                        load_model('sys/OperateLogModel')->insert($log);
                    }
                    $sql = "DELETE FROM goods_shelf WHERE CONCAT(store_code,'_',sku) in({$bis_str})";
                    $ret = $this->query($sql);
                    if ($ret['status'] != 1) {
                        $this->rollback();
                        return $this->format_ret(-1, '', '覆盖原有库位时失败');
                    }
                }
            }

            $message = '导入成功：' . $success_num . '条';
            $status = 1;
            if (!empty($faild)) {
                $status = -1;
                $message .= ',' . '导入失败:' . $fail_num . '条';
                    $fail_top = array('条形码行数', '错误信息');
                    $filename = 'goods_shelf_import';
                    $file_name = $this->create_import_fail_files($faild, $fail_top, $filename);
           // $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
                    $url = set_download_csv_url($file_name, array('export_name' => 'error'));
                    $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
            }
            if (!empty($shelf_data)) {
                $shelf_arr = array_chunk($shelf_data, 2000, TRUE);
                foreach ($shelf_arr as $shelf) {
                    $ret = $this->insert_multi($shelf, true);
                    if ($ret['status'] != 1) {
                        $this->rollback();
                        return $this->format_ret(-1, '', '导入过程中出现错误，导入失败');
                    }
                }
            }
            $this->commit();
            $ret['status'] = $status;
            $ret['message'] = $message;
            return $ret;
    }

    function set_default_lof() {
        $data = load_model("prm/GoodsLofModel")->get_sys_lof();
        $this->default_lof = $data['lof_no'];
    }
    function set_shelf_data_goods_code($shelf_data){
        if(empty($shelf_data['goods_code'])){
            return array('status' => -1, 'message' => '商品编码不能为空');
        }

        if(empty($shelf_data['store_code'])){
            return array('status' => -1, 'message' => '仓库代码不能为空');
        }

        if(empty($shelf_data['shelf_code'])){
            return array('status' => -1, 'message' => '库位代码不能为空');
        }


        $goods_code = $this->get_check_data_goods_code('goods_code',$shelf_data['goods_code']);
        if(!$goods_code){
            return array('status' => -1, 'message' => '系统中不存在此编码');
        }
        foreach($shelf_data['sku'] as $val){
            if(empty($val['barcode'])){
                return array('status' => -1, 'message' => '此编码下部分条码为空');
            }
        }

        $store = $this->get_check_data_goods_code('store',$shelf_data['store_code']);
        if (!$store) {
            return array('status' => -1, 'message' => '仓库代码系统中不存在');
        }

        $shelf = $this->get_check_data_goods_code('shelf',$shelf_data['shelf_code']);
        if (!$shelf) {
            return array('status' => -1, 'message' => '库位代码系统中不存在' );
        }
        return $this->format_ret(1,$shelf_data);

    }

    function set_shelf_data($shelf_data, $bischecked, $lof_status) {
        if(empty($shelf_data['barcode'])){
            return array('status' => -1, 'message' => '商品条码不能为空');
        }
        $sku = $this->get_check_data('sku');
        if (!array_key_exists($shelf_data['barcode'], $sku)) {
            return array('status' => -1, 'message' => '商品条码系统中不存在');
        }
        $sku = explode('_line_', $sku[$shelf_data['barcode']]);

        if(empty($shelf_data['store_code'])){
            return array('status' => -1, 'message' => '仓库代码不能为空');
        }
        $store = $this->get_check_data('store');
        if (!in_array($shelf_data['store_code'], $store)) {
            return array('status' => -1, 'message' => '仓库代码系统中不存在');
        }

        if(empty($shelf_data['shelf_code'])){
            return array('status' => -1, 'message' => '库位代码不能为空');
        }
        $shelf = $this->get_check_data('shelf');
        $shelf_str = $shelf_data['store_code'] . '_' . $shelf_data['shelf_code'];
        if (!in_array($shelf_str, $shelf)) {
            return array('status' => -1, 'message' => '库位代码系统中不存在' );
        }

        if ($lof_status == 1) {
            $lof_info = $this->get_check_data('lof_info');
            $lof_str = $sku[1] . '_' . $shelf_data['batch_number'];
            if (!in_array($lof_str, $lof_info)) {
                return array('status' => -1, 'message' => '商品条形码和批次信息不存在');
            }
        } else {
            $shelf_data['batch_number'] = $this->default_lof;
        }

        $d = array(
            'sku' => $sku[1],
            'batch_number' => $shelf_data['batch_number'],
            'store_code' => $shelf_data['store_code'],
            'shelf_code' => $shelf_data['shelf_code'],
            'goods_code' => $sku[0],
        );
        return $this->format_ret(1, $d);
    }

    function get_check_data_goods_code($_type,$data){
        switch ($_type) {
            case 'goods_code':
                $sql = "SELECT goods_id FROM base_goods WHERE goods_code =:goods_code";
                $ret= $this->db->get_all($sql,array(':goods_code'=>$data));
                if(empty($ret)){
                    $goods_code=false;
                }else{
                    $goods_code=true;
                }
                break;
            case 'store':
                $sql = "SELECT store_id FROM base_store WHERE store_code  =:store_code";
                $ret= $this->db->get_all($sql,array(':store_code'=>$data));
                if(empty($ret)){
                    $store=false;
                }else{
                    $store=true;
                }
                break;
            case 'shelf':
                $sql = "SELECT shelf_id  FROM base_shelf WHERE shelf_code =:shelf_code";
                $ret = $this->db->get_all($sql,array(':shelf_code'=>$data));
                if(empty($ret)){
                    $shelf=false;
                }else{
                    $shelf=true;
                }
                break;
            default:
                break;
        }
        return ${$_type};
    }

    function get_check_data($_type) {
        static $sku = NULL;
        static $store = NULL;
        static $shelf = NULL;
        static $lof_info = NULL;

        if (!is_null(${$_type})) {
            return ${$_type};
        }
        switch ($_type) {
            case 'sku':
                $barcode = "'" . implode("','", $this->barcode) . "'";
                $sql = "SELECT CONCAT(goods_code,'_line_',sku) AS goods_sku,barcode FROM goods_barcode WHERE barcode IN($barcode)";
                $ret_sku = $this->db->get_all($sql);
                $sku = array_column($ret_sku, 'goods_sku', 'barcode');
                break;
            case 'store':
                $store = "'" . implode("','", $this->store) . "'";
                $sql = "SELECT store_code FROM base_store WHERE store_code IN({$store})";
                $ret_store = $this->db->get_all($sql);
                $store = array_column($ret_store, 'store_code');
                break;
            case 'shelf':
                $shelf = "'" . implode("','", $this->shelf) . "'";
                $sql = "SELECT CONCAT(store_code,'_',shelf_code) AS shelf FROM base_shelf WHERE shelf_code IN({$shelf})";
                $ret_shelf = $this->db->get_all($sql);
                $shelf = array_column($ret_shelf, 'shelf');
                break;
            case 'lof_info':
                $lof = "'" . implode("','", $this->lof) . "'";
                $sql = "SELECT CONCAT(sku,'_',lof_no) AS lof_info FROM goods_lof WHERE lof_no IN({$lof})";
                $ret_lof = $this->db->get_all($sql);
                $lof_info = array_column($ret_lof, 'lof_info');
                break;

            default:
                break;
        }

        return ${$_type};
    }

    function a_key_unbind($filter) {
        $inner = "";
        if($filter['type'] == "true"){
            $inner .= "LEFT JOIN goods_inv b ON b.store_code = a.store_code AND b.sku = a.sku ";
        }
        $inner .= "LEFT JOIN goods_sku r2 ON a.sku = r2.sku";
        $sql = "select a.*,r2.barcode from goods_shelf a ".$inner." where 1";
        if (isset($filter['store']) && $filter['store'] != '') {
            $sql .= " AND a.store_code=:store_code";
        }
        if($filter['type'] == "true"){
            $sql .= " AND (b.sku is null or b.stock_num <= b.lock_num) ";
        }
        $data = $this->db->get_all($sql,array(':store_code'=>$filter['store']));
        if (empty($data)) {
            return array('status' => -1, 'message' => '没有商品库位关联需要解除');
        }
    
        //以goods_code为下标重新排列数组
        $data_new = array();
        foreach($data as $v){
            $goods_code=$v['goods_code'];
            $data_new[$goods_code][] = $v;
        }
        $yw_code = '';
        $ok = 0;
        $fa = 0;
        foreach ($data_new as $k => $v1) {
            
            $operate_xq = '';
            foreach ($v1 as $key => $v) {
                $r = $this->delete(array('goods_shelf_id' => $v['goods_shelf_id']));
                $store = $this->db->get_row("select store_name from base_store where store_code = :store_code" ,array(':store_code'=>$v['store_code']));
                $shelf = $this->db->get_row("select shelf_name from base_shelf where shelf_code = :shelf_code and store_code = :store_code",array(':store_code'=>$v['store_code'],':shelf_code'=>$v['shelf_code']));
                $operate_xq .= '商品条形码:'.$v['barcode'].'从'.$store['store_name'].'的'.$shelf['shelf_name'].'库位解除绑定；'; //操作详情
            }
            /* $d = array(
              'batch_number' => '',
              'store_code' => '',
              'shelf_code' => '',
              );
              $r = $this->update($d, array('goods_shelf_id'=>$v['goods_shelf_id'])); */                       
            $yw_code = $k; //业务编码
            if ($r['status'] == '1') {
                $ok++;
                //增加系统操作日志
                $module = '基础数据'; //模块名称
                $operate_type = '解除绑定';
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_xq' => $operate_xq, 'operate_type' => $operate_type);
                load_model('sys/OperateLogModel')->insert($log);
            } else {
                $fa++;
            }
        }

        return array('status' => '1', 'ok' => $ok, 'faild' => $fa);
    }

    function scanning_unbind($storeCode, $shelfCode) {
        $sql = "select r1.*,r2.barcode from goods_shelf r1 
        LEFT JOIN goods_sku r2 ON r1.sku = r2.sku
        where store_code = :store_code AND shelf_code = :shelf_code ";
        $data = $this->db->get_all($sql, array('store_code' => $storeCode, 'shelf_code' => $shelfCode));
        if (empty($data)) {
            return array('status' => -1, 'message' => '商品库位关联不存在');
        }

        $sql = "DELETE FROM goods_shelf
        where store_code = :store_code AND shelf_code = :shelf_code ";
        $ret =  $this->query($sql, array('store_code' => $storeCode, 'shelf_code' => $shelfCode));
        //增加系统操作日志
        if ($ret) {
            $operate_xq = '';
            $yw_code = '';
            foreach ($data as $k => $v) {
                $store = $this->db->get_row("select store_name from base_store where store_code = :store_code" ,array(':store_code'=>$v['store_code']));
                $shelf = $this->db->get_row("select shelf_name from base_shelf where shelf_code = :shelf_code and store_code = :store_code",array(':store_code'=>$v['store_code'],':shelf_code'=>$v['shelf_code']));
                $operate_xq = '商品条形码:'.$v['barcode'].'从'.$store['store_name'].'的'.$shelf['shelf_name'].'库位解除绑定；'; //操作详情
                $yw_code = $v['goods_code']; //业务编码
                //系统操作日志
                $module = '基础数据'; //模块名称
                $operate_type = '解除绑定';
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_xq' => $operate_xq, 'operate_type' => $operate_type);
                load_model('sys/OperateLogModel')->insert($log);
            }                  
        }       
        return $ret;
    }

    function unbind($goods_shelf_id) {
        $sql = "select r1.*,r2.barcode from goods_shelf r1
        LEFT JOIN goods_sku r2 ON r1.sku = r2.sku
        where goods_shelf_id = :goods_shelf_id ";
        $data = $this->db->get_all($sql, array('goods_shelf_id' => $goods_shelf_id));
        if (empty($data)) {
            return array('status' => -1, 'message' => '商品库位关联不存在');
        }

        $sql = "DELETE FROM goods_shelf
        where goods_shelf_id = :goods_shelf_id ";        
        $ret = $this->query($sql, array('goods_shelf_id' => $goods_shelf_id));        
        //增加系统操作日志
        if ($ret) {
            $operate_xq = '';
            $yw_code = '';
            foreach ($data as $k => $v) {                 
                $store = $this->db->get_row("select store_name from base_store where store_code = :store_code" ,array(':store_code'=>$v['store_code']));
                $shelf = $this->db->get_row("select shelf_name from base_shelf where shelf_code = :shelf_code and store_code = :store_code",array(':store_code'=>$v['store_code'],':shelf_code'=>$v['shelf_code']));
                $operate_xq = '商品条形码:'.$v['barcode'].'从'.$store['store_name'].'的'.$shelf['shelf_name'].'库位解除绑定；'; //操作详情
                $yw_code = $v['goods_code']; //业务编码
            }        
            //系统操作日志
            $module = '基础数据'; //模块名称
            $operate_type = '解除绑定';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_xq' => $operate_xq, 'operate_type' => $operate_type);
            load_model('sys/OperateLogModel')->insert($log);
        }       
        return $ret;
    }

    function new_unbind($sku, $store_code, $shelf_code) {
        $sql = "select r1.*,r2.barcode from goods_shelf r1
        LEFT JOIN goods_sku r2 ON r1.sku = r2.sku
        where r1.sku = :sku and  r1.store_code = :store_code  and  r1.shelf_code = :shelf_code  ";
        $data = $this->db->get_all($sql, array('sku' => $sku, 'store_code' => $store_code, 'shelf_code' => $shelf_code));
        if (empty($data)) {
            return array('status' => -1, 'message' => '商品库位关联不存在');
        }

        $sql = "DELETE FROM goods_shelf
        where sku = :sku and  store_code = :store_code  and  shelf_code = :shelf_code ";
        $ret = $this->query($sql, array('sku' => $sku, 'store_code' => $store_code, 'shelf_code' => $shelf_code));
        //增加系统操作日志
        if ($ret) {
            $operate_xq = '';
            $yw_code = '';
            foreach ($data as $k => $v) {                 
                $store = $this->db->get_row("select store_name from base_store where store_code = :store_code" ,array(':store_code'=>$v['store_code']));
                $shelf = $this->db->get_row("select shelf_name from base_shelf where shelf_code = :shelf_code and store_code = :store_code",array(':store_code'=>$v['store_code'],':shelf_code'=>$v['shelf_code']));
                $operate_xq = '商品条形码:'.$v['barcode'].'从'.$store['store_name'].'的'.$shelf['shelf_name'].'库位解除绑定；'; //操作详情
                $yw_code = $v['goods_code']; //业务编码
            }        
            //系统操作日志
            $module = '基础数据'; //模块名称
            $operate_type = '解除绑定';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_xq' => $operate_xq, 'operate_type' => $operate_type);
            load_model('sys/OperateLogModel')->insert($log);
        }       
        return $ret;
    }

    function bind($goods_inv_id, $shelf_code_list) {


        $sql = "select s.goods_code,s.spec1_code,s.spec2_code,s.sku,l.lof_no from goods_sku s
		left join goods_lof l ON  s.sku=l.sku
        where s.sku_id = :goods_inv_id ";


        $inv = $this->db->get_row($sql, array('goods_inv_id' => $goods_inv_id));
        if (empty($inv)) {
            return array('status' => -1, 'message' => '商品库存记录不存在');
        }
        $ret_lof = load_model('prm/GoodsLofModel')->get_sys_lof();


        $keys = implode(',', $shelf_code_list);
        $str = $this->str_chunk($keys);
        $sql = "select * from base_shelf where shelf_code IN ($str) ";
        $data = $this->db->get_all($sql);
        if (empty($data)) {
            return array('status' => -1, 'message' => '库位不存在');
        }
        if (empty($inv['lof_no'])) {
            $inv['lof_no'] = $ret_lof['data']['lof_no'];
        }
        foreach ($data as $k => $v) {
            $d = array(
                'goods_code' => $inv['goods_code'],
//                'spec1_code' => $inv['spec1_code'],
//                'spec2_code' => $inv['spec2_code'],
                'sku' => $inv['sku'],
                'batch_number' => $inv['lof_no'],
                'store_code' => $v['store_code'],
                'shelf_code' => $v['shelf_code'],
            );
            $this->insert($d);
        }

        return array('status' => 1, 'message' => '关联成功');
    }

    function multi_bind($goods_inv_id, $shelf_code_list, $skuCode, $batch_number,$shelf_info) {

        $sql = "select s.* from goods_sku s left join goods_barcode b ON s.sku=b.sku
		where s.sku = :code  or  b.barcode=:code ";

        $sku = $this->db->get_row($sql, array(':code' => $skuCode));

        if (empty($sku)) {
            return array('status' => -1, 'message' => 'SKU不存在:' . $skuCode);
        }
        $ret_lof = load_model('prm/GoodsLofModel')->get_sys_lof();

        //验证库位信息是否存在
        foreach ($shelf_info as $num => $value) {
            $sql = "select 1 from base_shelf where shelf_code='{$value['shelf_code']}' AND store_code='{$value['store_code']}'";
            $result = $this->db->get_row($sql);
            if (empty($result)) {
                unset($shelf_info[$num]);
            }
        }
        if (empty($shelf_info)) {
            return array('status' => -1, 'message' => '库位不存在');
        }

//
//        $keys = implode(',', $shelf_code_list);
//        $str = $this->str_chunk($keys);
//        $sql = "select * from base_shelf where shelf_code IN ($str) ";
//        $data = $this->db->get_all($sql);
//        if (empty($data)) {
//            return array('status' => -1, 'message' => '库位不存在');
//        }
        if (empty($batch_number)) {
            $sku['lof_no'] = $ret_lof['data']['lof_no'];
        } else {
            $sku['lof_no'] = $batch_number;
        }
        $d = array();
        //增加系统操作日志
        $operate_xq = '';
        foreach ($shelf_info as $k => $v) {
            $d[] = array(
                'goods_code' => $sku['goods_code'],
//                'spec1_code' => $sku['spec1_code'],
//                'spec2_code' => $sku['spec2_code'],
                'sku' => $skuCode,
                'batch_number' => $sku['lof_no'],
                'store_code' => $v['store_code'],
                'shelf_code' => $v['shelf_code'],
            );
            $operate_xq .= '商品条形码:'.$sku['barcode'].'绑定'.$v['store_name'].'的'.$v['shelf_name'].'库位；'; //操作详情
            //  $this->insert($d);
        }
        $this->insert_multi_exp('goods_shelf', $d, true);
        //系统操作日志
        $module = '基础数据'; //模块名称
        $yw_code = $sku['goods_code']; //业务编码
        $operate_type = '绑定';
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_xq' => $operate_xq, 'operate_type' => $operate_type);
        load_model('sys/OperateLogModel')->insert($log);
        return array('status' => 1, 'message' => '关联成功');
    }

    function new_bind($sku, $store_code, $shelf_code) {

        $sql = "select s.goods_code,s.spec1_code,s.spec2_code,s.sku,l.lof_no from goods_sku s
		left join goods_lof l ON  s.sku=l.sku
        where s.sku = :sku ";

        $inv = $this->db->get_row($sql, array('sku' => $sku));
        if (empty($inv)) {
            return array('status' => -1, 'message' => '商品库存记录不存在');
        }
        $ret_lof = load_model('prm/GoodsLofModel')->get_sys_lof();

        $sql = "select * from base_shelf where shelf_code = '" . $shelf_code . "' and  store_code = '" . $store_code . "' ";
        $data = $this->db->get_all($sql);
        if (empty($data)) {
            return array('status' => -1, 'message' => '库位不存在');
        }
        if (empty($inv['lof_no'])) {
            $inv['lof_no'] = $ret_lof['data']['lof_no'];
        }

        $d = array(
            'goods_code' => $inv['goods_code'],
//                'spec1_code' => $inv['spec1_code'],
//                'spec2_code' => $inv['spec2_code'],
            'sku' => $sku,
            'batch_number' => $inv['lof_no'],
            'store_code' => $store_code,
            'shelf_code' => $shelf_code,
        );
        return $this->insert($d);

//        return array('status'=>1, 'message'=>'关联成功');
    }

    /**
     *
     * 方法名                               api_goods_shelf_update
     *
     * 功能描述                           添加库位对产品关系数据
     *
     * @author      BaiSon PHP R&D
     * @date        2015-06-19
     * @param       array $param
     *              array(
     *                  必选: 'barcode', 'store_code', 'shelf_code',
     *                  可选: 'batch_number'
     *                 )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    public function api_goods_shelf_update($param) {
        //必选字段【说明：i=>代码数据检测类型为数字型  s=>代表数据检测类弄为字符串型】
        $key_required = array(
            's' => array('barcode', 'store_code', 'shelf_code'),
        );
        $arr_required = array();
        //验证必选字段是否为空并提取必选字段数据
        $ret_required = valid_assign_array($param, $key_required, $arr_required, TRUE);
        //必填项检测通过
        if (TRUE == $ret_required['status']) {
            //可选字段
            $key_option = array(
                's' => array('batch_number')
            );
            $arr_option = array();
            //提取可选字段中已赋值数据
            $ret_option = valid_assign_array($param, $key_option, $arr_option);

            //合并数据
            $arr_deal = array_merge($arr_required, $arr_option);

            //清空无用数据
            unset($arr_required);
            unset($arr_option);
            unset($param);

            //检查商品条码库中是否存在客户上传的barcode
            $filter = array('barcode' => $arr_deal['barcode']);
            $ret = load_model('prm/GoodsBarcodeModel')->check_exists_by_condition($filter, 'goods_barcode');
            if (1 != (int) $ret['status']) {
                return $this->format_ret("-10002", $filter, "API_RETURN_MESSAGE_10002");
            }
            //产品信息
            $goods = $ret['data'];

            //清空无用数据
            unset($filter);
            unset($ret);

            //检测仓库是否存在并获取仓库数据
            $filter = array('store_code' => $arr_deal['store_code']);
            $ret = load_model('base/StoreModel')->check_exists_by_condition($filter);

            if (1 != (int) $ret['status']) {
                return $this->format_ret("-10002", $filter, "API_RETURN_MESSAGE_10002");
            }
            $store = $ret['data'];

            //检测库位是否存在并获取库位数据
            $filter = array('store_code' => $arr_deal['store_code'], 'shelf_code' => $arr_deal['shelf_code']);
            $ret = load_model('base/ShelfModel')->check_exists_by_condition($filter);
            if (1 != (int) $ret['status']) {
                return $this->format_ret("-10002", $filter, "API_RETURN_MESSAGE_10002");
            }
            //$shelf = $ret['data'];
            //清空无用数据
            unset($filter);
            unset($ret);

            //检查库位对产品关系是否存在
            $filter = array('store_code' => $arr_deal['store_code'], 'shelf_code' => $arr_deal['shelf_code'], 'sku' => $goods['sku']);
            $ret = $this->check_exists_by_condition($filter);
            if (1 == (int) $ret['status']) {
                return $this->format_ret("-10003", $filter, "API_RETURN_MESSAGE_10003");
            }
            unset($ret);
            unset($filter);

            //插入新的产品对库位关系数据
            $data = array(
                'goods_code' => $goods['goods_code'], 'spec1_code' => $goods['spec1_code'], 'spec2_code' => $goods['spec2_code'],
                'sku' => $goods['sku'], 'batch_number' => $arr_deal['batch_number'], 'store_code' => $arr_deal['store_code'],
                'shelf_code' => $arr_deal['shelf_code']
            );
            unset($arr_deal);
            //插入数据
            $ret = $this->insert($data);
            return $ret;

            //构造产品对库存表数据是否存在查询数据
            $filter = array('barcode' => $arr_deal['barcode']);
        } else {
            return $this->format_ret("-10001", $param, "API_RETURN_MESSAGE_10001");
        }
    }

    /**
     * @todo API-商品和库位解绑
     * @date 2016-06-06
     * @param array $param
     *         array(
     *            必选: 'barcode', 'store_code', 'shelf_code',
     *            可选: 'batch_number' //开启批次则为必填,不开启则不需要
     *         )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":""}
     */
    function api_goods_shelf_unbind($param) {
        $key_required = array(
            's' => array('barcode', 'store_code', 'shelf_code'),
        );
        //判断是否开启批次，开启则增加批次号为必填项
        $ret_lof = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        if ($ret_lof['lof_status'] == 1) {
            $key_required['s'][] = 'batch_number';
        }

        $arr_deal = array();
        //验证必选字段是否为空并提取必选字段数据
        $ret_required = valid_assign_array($param, $key_required, $arr_deal, TRUE);
        if ($ret_required['status'] == FALSE) {
            return $this->format_ret("-10001", $ret_required['req_empty'], "API_RETURN_MESSAGE_10001");
        }

        //清空无用数据
        unset($param);

        //判断是否存在绑定关系
        $arr_deal['sku'] = oms_tb_val('goods_sku', 'sku', array('barcode' => $arr_deal['barcode']));
        unset($arr_deal['barcode']);
        $ret_shelf = $this->get_row($arr_deal);
        if ($ret_shelf['status'] != 1) {
            return $this->format_ret(-10002, '', '商品库位未绑定，不需要解绑');
        }

        //解绑
        $ret = $this->delete($arr_deal);
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '解绑失败');
        }

        return $this->format_ret(1, '', '解绑成功');
    }

    /**
     * @todo API-商品的库位查询
     * @date 2016-06-06
     * @param array $param
     *         array(
     *            必选: 'store_code',
     *            可选: 'barcode','goods_code','batch_number' //批次号,开启批次时有效
     *                  //barcode、goods_code 二选一传入，两者都有则取barcode查询
     *         )
     * @return json [string status, obj data, string message]
     *          {"status":"1","message":"保存成功"," data":""}
     */
    function api_goods_shelf_get($param) {
        $key_required = array(
            's' => array('store_code'),
        );

        $arr_required = array();
        //验证必选字段是否为空并提取必选字段数据
        $ret_required = valid_assign_array($param, $key_required, $arr_required, TRUE);
        if ($ret_required['status'] == FALSE) {
            return $this->format_ret("-10001", $ret_required['req_empty'], "API_RETURN_MESSAGE_10001");
        }

        //可选字段
        $key_option = array(
            's' => array('goods_code', 'barcode')
        );
        //判断是否开启批次，开启则增加批次号为必填项
        $ret_lof = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        if ($ret_lof['lof_status'] == 1) {
            $key_option['s'][] = 'batch_number';
        }
        $arr_option = array();
        //提取可选字段中已赋值数据
        $ret_option = valid_assign_array($param, $key_option, $arr_option);

        //商品条码、商品编码必须传入其中一个
        if ((!isset($arr_option['barcode']) || empty($arr_option['barcode'])) && (!isset($arr_option['goods_code']) || empty($arr_option['goods_code']))) {
            return $this->format_ret('-10001', array('goods_code', 'barcode'), '商品编码和条码必填其一');
        }
        //如果商品条码和编码都传入，只取条码查询
        if (isset($arr_option['barcode']) && isset($arr_option['goods_code'])) {
            unset($arr_option['goods_code']);
        }
        //合并数据
        $arr_deal = array_merge($arr_required, $arr_option);
        //清空无用数据
        unset($param, $arr_required, $arr_option);

        //判断商品编码或商品条码是否存在
        if (isset($arr_deal['goods_code'])) {
            $sql = 'SELECT goods_code FROM base_goods WHERE goods_code=:goods_code';
            $ret_goods = $this->db->get_row($sql, array(':goods_code' => $arr_deal['goods_code']));
            if (empty($ret_goods)) {
                return $this->format_ret('-10002', array('goods_code' => $arr_deal['goods_code']), '商品编码不存在');
            }
        }
        if (isset($arr_deal['barcode'])) {
            $sql = 'SELECT sku FROM goods_sku WHERE barcode=:barcode';
            $ret_sku = $this->db->get_row($sql, array(':barcode' => $arr_deal['barcode']));
            if (empty($ret_sku)) {
                return $this->format_ret('-10002', array('barcode' => $arr_deal['barcode']), '商品条码不存在');
            }
            $arr_deal['sku'] = $ret_sku['sku'];
            unset($arr_deal['barcode']);
        }

        //执行查询
        $ret = $this->get_shelf_by_where($arr_deal, 'goods');
        return $ret;
    }

    /**
     * @todo API-库位的商品查询
     * @date 2016-06-06
     * @param array $param
     *         array(
     *            必选: 'store_code','shelf_code'
     *            可选: 'batch_number' //批次号,开启批次时有效并
     *         )
     * @return json [string status, obj data, string message]
     *          {"status":"1","message":"保存成功"," data":""}
     */
    function api_shelf_goods_get($param) {
        $key_required = array(
            's' => array('store_code', 'shelf_code'),
        );

        $arr_required = array();
        //验证必选字段是否为空并提取必选字段数据
        $ret_required = valid_assign_array($param, $key_required, $arr_required, TRUE);
        if ($ret_required['status'] == FALSE) {
            return $this->format_ret("-10001", $ret_required['req_empty'], "API_RETURN_MESSAGE_10001");
        }

        //可选字段
        $arr_option = array();
        //判断是否开启批次，开启则增加批次号为必填项
        $ret_lof = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        if ($ret_lof['lof_status'] == 1) {
            $key_option['s'][] = 'batch_number';

            //提取可选字段中已赋值数据
            $ret_option = valid_assign_array($param, $key_option, $arr_option);
        }

        //合并数据
        $arr_deal = array_merge($arr_required, $arr_option);
        //清空无用数据
        unset($param, $arr_required, $arr_option);

        //判断库位是否存在
        $sql = 'SELECT shelf_code FROM base_shelf WHERE shelf_code=:shelf_code';
        $ret_shelf = $this->db->get_row($sql, array(':shelf_code' => $arr_deal['shelf_code']));
        if (empty($ret_shelf)) {
            return $this->format_ret('-10002', array('shelf_code' => $arr_deal['shelf_code']), '库位不存在');
        }

        //执行查询
        $ret = $this->get_shelf_by_where($arr_deal, 'shelf');
        return $ret;
    }

    /**
     * @todo API-商品/库位查询
     * @param array $wh_arr 查询条件数组
     * @param string $type 查询类型，goods=>商品的库位查询,shelf=>库位的商品查询
     */
    function get_shelf_by_where($wh_arr, $type) {
        //检查仓库是否存在
        $sql = 'SELECT store_code FROM base_store WHERE store_code=:store_code';
        $ret_store = $this->db->get_row($sql, array(':store_code' => $wh_arr['store_code']));
        if (empty($ret_store)) {
            return $this->format_ret('-10002', array('store_code' => $wh_arr['store_code']), '仓库不存在');
        }

        $select = '';
        $join = '';
        $wh = ' AND gf.store_code=:store_code';
        $filter = array(':store_code' => $wh_arr['store_code']);

        if (isset($wh_arr['batch_number'])) {
            $wh .=' AND gf.batch_number=:batch_number';
            $filter[':batch_number'] = $wh_arr['batch_number'];
        }
        if ($type == 'goods') {
            $select .= ' gf.shelf_code,bs.shelf_name';
            $join .=' LEFT JOIN base_shelf bs ON gf.shelf_code=bs.shelf_code';
            $fld = isset($wh_arr['sku']) ? 'sku' : 'goods_code';
            $wh .= " AND gf.{$fld}=:{$fld}";
            $filter[':' . $fld] = $wh_arr[$fld];
        } else if ($type == 'shelf') {
            $select .='gf.goods_code,bg.goods_name,sku.barcode';
            $join .=' LEFT JOIN goods_sku sku ON gf.sku=sku.sku
                      LEFT JOIN base_goods bg ON gf.goods_code=bg.goods_code ';
            $wh .= " AND gf.shelf_code=:shelf_code";
            $filter[':shelf_code'] = $wh_arr['shelf_code'];
        } else {
            return $this->format_ret(-1, '', '异常操作');
        }
        $sql = "SELECT {$select} FROM goods_shelf gf {$join} WHERE 1 {$wh}";
        $data = $this->db->get_all($sql, $filter);
        if (empty($data)) {
            return $this->format_ret('-10002', '', '未查询到相关数据');
        }
        return $this->format_ret(1, $data, '操作成功');
    }

    function create_import_fail_files($msg, $fail_top, $filename) {
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($msg as $key => $val) {
            $val_data = array($key, $val);
            $file_str .= implode(",", $val_data) . "\r\n";
        }
        $filename = md5($filename . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

    /**
     * 根据商品的sku和仓库获取库位
     * @param $store_code_arr 仓库码
     * @param $sku_arr sku
     * @param $key 数组键
     * @return array
     */

    public function get_shelf_name($store_code_arr,$sku_arr,$key='store_code,sku'){
        if(!is_array($store_code_arr)){
            $store_code_arr[] = $store_code_arr;
        }
        if(!is_array($sku_arr)){
            $sku_arr[] = $sku_arr;
        }
        if(empty($store_code_arr) || empty($sku_arr)){
            return $this->format_ret(-1,'','仓库或者商品sku不能为空');
        }
        $store_code_str = $this->arr_to_in_sql_value($store_code_arr,'store_code',$shelf_sql_values);
        $sku_str = $this->arr_to_in_sql_value($sku_arr,'sku_code',$shelf_sql_values);
        $shelf_sql = "select 
                          DISTINCT b.shelf_name,b.store_code,s.sku
                    from base_shelf b 
                    INNER JOIN goods_shelf s ON b.shelf_code=s.shelf_code and b.store_code = s.store_code 
                    where  b.store_code in ({$store_code_str}) AND s.sku in ({$sku_str}) ";
        $shelf_arr = $this->db->get_all($shelf_sql,$shelf_sql_values);
        $shelf_name_arr = array();
        if(!empty($shelf_arr)){
            $shelf_arr = load_model('util/ViewUtilModel')->get_map_arr($shelf_arr, $key,1);
            foreach($shelf_arr as $key=>$value){
                $shelf_name_arr[$key]['shelf_name'] = implode(',',array_column($value,'shelf_name'));
            }
        }
        return $this->format_ret(1,$shelf_name_arr);
    }

}
