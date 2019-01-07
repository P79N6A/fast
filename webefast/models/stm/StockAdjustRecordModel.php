<?php

header("Content-type: text/html; charset=utf-8");
/**
 * 库存调整管理相关业务
 * @author dfr
 */
require_model('tb/TbModel');
require_lang('stm');

class StockAdjustRecordModel extends TbModel {

    private $lof_no = '';
    private $production_date = '';

    function get_table() {
        return 'stm_stock_adjust_record';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        if (isset($filter['ctl_export_conf']) && $filter['ctl_export_conf'] == 'stock_adjust_record_list_detail') {
            $sql_join = " LEFT JOIN stm_stock_adjust_record_detail r2 on rl.record_code = r2
                          .record_code LEFT JOIN base_goods r3 on r3.goods_code = r2.goods_code LEFT JOIN goods_sku r4 on r2.sku
                          = r4.sku LEFT JOIN base_record_type r5 ON rl.adjust_type=r5.record_type_code ";
        } else {
            $sql_join = " LEFT JOIN stm_stock_adjust_record_detail r2 on rl.record_code = r2.record_code "
                    . "LEFT JOIN base_goods r3 on r3.goods_code = r2.goods_code "
                    . "LEFT JOIN goods_sku r4 on r2.sku = r4.sku";
        }
        $sql_main = "FROM {$this->table} rl {$sql_join}  WHERE 1";


        $sql_values = array();

        //门店还是网络店铺
        if (isset($filter['is_entity_shop']) && $filter['is_entity_shop'] == 'entity_shop') {
            $sql_main .= " AND rl.is_entity_shop = 1";
        } else {
            $sql_main .= " AND rl.is_entity_shop = 0";
            $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
            $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
        }
        $login_type = CTX()->get_session('login_type');
        if ($login_type > 0) {
            $filter['store_code'] = CTX()->get_session('oms_shop_code');
        }
        // 调整仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $store_code_arr = explode(',', $filter['store_code']);
            if (!empty($store_code_arr)) {
                $sql_main .= " AND (";
                foreach ($store_code_arr as $key => $value) {
                    $param_store = 'param_store' . $key;
                    if ($key == 0) {
                        $sql_main .= " rl.store_code = :{$param_store} ";
                    } else {
                        $sql_main .= " or rl.store_code = :{$param_store} ";
                    }

                    $sql_values[':' . $param_store] = $value;
                }
                $sql_main .= ")";
            }
        }

        // 调整类型
        if (isset($filter['adjust_type']) && $filter['adjust_type'] != '') {
            $adjust_type_arr = explode(',', $filter['adjust_type']);
            if (!empty($adjust_type_arr)) {
                $sql_main .= " AND (";
                foreach ($adjust_type_arr as $key => $value) {
                    $param_type = 'param_type' . $key;
                    if ($key == 0) {
                        $sql_main .= " rl.adjust_type = :{$param_type} ";
                    } else {
                        $sql_main .= " or rl.adjust_type = :{$param_type} ";
                    }

                    $sql_values[':' . $param_type] = $value;
                }
                $sql_main .= ")";
            }
        }
        //下单时间
        if (isset($filter['is_add_time_start']) && $filter['is_add_time_start'] != '') {
            $sql_main .= " AND (rl.is_add_time >= :is_add_time_start )";
            $sql_values[':is_add_time_start'] = $filter['is_add_time_start'] . ' 00:00:00';
        }
        if (isset($filter['is_add_time_end']) && $filter['is_add_time_end'] != '') {
            $sql_main .= " AND (rl.is_add_time <= :is_add_time_end )";
            $sql_values[':is_add_time_end'] = $filter['is_add_time_end'] . ' 23:59:59';
        }
        //yewu时间
        if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
            $sql_main .= " AND (rl.record_time >= :record_time_start )";
            $sql_values[':record_time_start'] = $filter['record_time_start'] . ' 00:00:00';
        }
        if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
            $sql_main .= " AND (rl.record_time <= :record_time_end )";
            $sql_values[':record_time_end'] = $filter['record_time_end'] . ' 23:59:59';
        }
        //商品编号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (rl.record_code LIKE :record_code or rl.init_code like :init_code )";
            $sql_values[':record_code'] = $filter['record_code'] . '%';
            $sql_values[':init_code'] = $filter['record_code'] . '%';
        }
        // 原单号
        if (isset($filter['init_code']) && $filter['init_code'] != '') {
            $sql_main .= " AND (rl.init_code LIKE :init_code )";
            $sql_values[':init_code'] = $filter['init_code'] . '%';
        }
        // 盘点单号
        if (isset($filter['relation_code']) && $filter['relation_code'] != '') {
            $sql_main .= " AND (rl.relation_code LIKE :relation_code )";
            $sql_values[':relation_code'] = $filter['relation_code'] . '%';
        }
        // 调整原因
        if (isset($filter['remark']) && $filter['remark'] != '') {
            $sql_main .= " AND (rl.remark LIKE :remark )";
            $sql_values[':remark'] = $filter['remark'] . '%';
        }
        //商品货号或名称
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :code_name or r3.goods_name LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }
        //商品条形码
        if (isset($filter['code_sku']) && $filter['code_sku'] != '') {
            $sql_main .= " AND r4.barcode LIKE :code_sku";
            $sql_values[':code_sku'] = $filter['code_sku'] . '%';
        }
        //验收
        if (isset($filter['is_check_and_accept']) && $filter['is_check_and_accept'] != '') {
            $sql_main .= " AND rl.is_check_and_accept = :is_check_and_accept";
            $sql_values[':is_check_and_accept'] = $filter['is_check_and_accept'];
        }
        if (isset($filter['add_person']) && $filter['add_person'] != '') {
            $sql_main .= " AND (rl.is_add_person = :add_person )";
            $sql_values[':add_person'] = trim($filter['add_person']);
        }
//        //明细
        if ($filter['ctl_type'] == 'export' && isset($filter['ctl_export_conf']) && $filter['ctl_export_conf'] != 'stock_adjust_record_list') {
            return $this->sell_record_search_csv($sql_main, $sql_values, $filter);
        }
        //$select = 'rl.*,r2.goods_name,r3.goods_name,r2.weight';
        $select = 'rl.*';
        $sql_main .= " group by rl.record_code order by record_time desc,rl.lastchanged desc";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['money'] = round($value['money'], 2);
            $arr = ds_get_table_row('base_store', 'store_code', $value['store_code'], 'store_name');
            $data['data'][$key]['store_name'] = isset($arr['store_name']) ? $arr['store_name'] : '';
        }
        filter_fk_name($data['data'], array('adjust_type|record_type'));

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $data = $this->get_row(array('stock_adjust_record_id' => $id));
        filter_fk_name($data['data'], array('adjust_type|record_type', 'store_code|store'));
        return $data;
    }

    function get_by_code($code) {
        $data = $this->get_row(array('record_code' => $code));
        filter_fk_name($data['data'], array('adjust_type|record_type', 'store_code|store'));
        return $data;
    }

    //导出明细
    private function sell_record_search_csv($sql_main, $sql_values, $filter) {
        $select = "r3.goods_name,r4.barcode,r2.goods_code,r2.spec1_code,r2.spec2_code,r2.sku,r2.num as num_detail,r2.money as moeny_detail,r5.record_type_name,rl.*";
        $ret_data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($ret_data['data'] as $key => $value) {
            //查询仓库名称
            $arr = load_model('base/StoreModel')->get_by_field('store_code', $value['store_code'], 'store_name');
            $ret_data['data'][$key]['store_name'] = isset($arr['data']['store_name']) ? $arr['data']['store_name'] : '';
            //查询规格1/规格2
            $key_arr = array('spec1_name', 'spec2_name', 'goods_name', 'barcode');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $ret_data['data'][$key]['spec1_name'] = $sku_info['spec1_name'];
            $ret_data['data'][$key]['spec2_name'] = $sku_info['spec2_name'];
            $ret_data['data'][$key]['goods_name'] = $sku_info['goods_name'];
            $ret_data['data'][$key]['barcode'] = $sku_info['barcode'];
        }
        return $this->format_ret(1, $ret_data);
    }

    /**
     * 通过field_name查询
     *
     * @param  $ :查询field_name
     * @param  $select ：查询返回字段
     * @return array (status, data, message)
     */
    public function get_by_field($field_name, $value, $select = "*") {

        $sql = "select {$select} from {$this->table} where {$field_name} = :{$field_name}";
        $data = $this->db->get_row($sql, array(":{$field_name}" => $value));

        if ($data) {
            return $this->format_ret('1', $data);
        } else {
            return $this->format_ret('-1', '', 'get_data_fail');
        }
    }

    function imoprt_detail($id, $file, $is_lof = 0) {
        $ret = $this->get_row(array('stock_adjust_record_id' => $id));
        $store_code = $ret['data']['store_code'];

        $lof_manage = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        $is_lof = $lof_manage['lof_status'];


        $barcode_arr = $barcode_num = array();
        if ($is_lof == 1) {
            $num = $this->read_csv_lof($file, $barcode_arr, $barcode_num);
        } else {
            //未开启批次导入库存方法
            $num = $this->read_csv_sku($file, $barcode_arr, $barcode_num);
            //没有开启批次的 查询默认批次 如果批次表里 没有，在批次表里添加goods_lof
//            $barcode_str = implode("','", $barcode_arr);
//            $sql = "select g.barcode,g.sku,l.lof_no,l.production_date,l.type from goods_lof l "
//                    . "INNER JOIN goods_sku g ON g.sku=l.sku  where"
//                    . " g.barcode in ('$barcode_str') group by g.barcode";
//            $sku_data = $this->db->get_all($sql);
            $sql_moren = "select lof_no,production_date from goods_lof  where type=1";
            $moren = $this->db->get_row($sql_moren);
            $lof_data_new = array();
//            foreach ($sku_data as $lof_data) {
//                $lof_data_new[$lof_data['barcode']]['production_date'] = $lof_data['production_date'];
//                $lof_data_new[$lof_data['barcode']]['lof_no'] = $lof_data['lof_no'];
//                $lof_data_new[$lof_data['barcode']]['sku'] = $lof_data['sku'];
//            }
            $new_barcode_num = $barcode_num;
            $barcode_num = array();
            foreach ($barcode_arr as $barcode) {
//                if (array_key_exists($barcode, $lof_data_new)) {
//                    $barcode_num[$barcode][$lof_data_new[$barcode]['lof_no']]['num'] = $new_barcode_num[$barcode];
//                    $barcode_num[$barcode][$lof_data_new[$barcode]['lof_no']]['lof_no'] = $lof_data_new[$barcode]['lof_no'];
//                    $barcode_num[$barcode][$lof_data_new[$barcode]['lof_no']]['production_date'] = $lof_data_new[$barcode]['production_date'];
//                } else {
                $barcode_num[$barcode][$moren['lof_no']]['num'] = $new_barcode_num[$barcode];
                $barcode_num[$barcode][$moren['lof_no']]['lof_no'] = $moren['lof_no'];
                $barcode_num[$barcode][$moren['lof_no']]['production_date'] = $moren['production_date'];
                //  }
            }
        }

        if (!empty($barcode_num) && !empty($barcode_arr)) {
            $all_num = count($barcode_arr);
            $sql_values = array();
            $barcode_str = $this->arr_to_in_sql_value($barcode_arr, 'barcode', $sql_values);
            $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,b.sku,g.price,g.purchase_price,g.sell_price  from
                    goods_sku b
                    inner join  base_goods g ON g.goods_code = b.goods_code
                    where b.barcode in({$barcode_str}) GROUP BY b.barcode";

            $detail_data = $this->db->get_all($sql, $sql_values); //sell_price
            $detail_data_lof = array();
            $temp = array();
            foreach ($detail_data as $key => $val) {
                foreach ($barcode_num[$val['barcode']] as $k1 => $v1) {
                    if (preg_match("/^[A-Za-z]/", $v1['num'])) {
                        $error_msg[] = array($val['barcode'] => '请输入数字');
                        $err_num ++;
                        unset($barcode_num[$val['barcode']]);
                    } else if ($v1['num'] == '' || is_float($v1['num'] + 0)) {
                        $error_msg[] = array($val['barcode'] => '商品数量不能为空或小数');
                        $err_num ++;
                        unset($barcode_num[$val['barcode']]);
                    } else {
                        $val['num'] = $v1['num'];
                        $val['lof_no'] = $v1['lof_no'];
                        $val['production_date'] = $v1['production_date'];
                        $detail_data_lof[] = $val;
                        unset($barcode_num[$val['barcode']]);
                    }
                }
            }
            //批次档案维护
            $ret = load_model('prm/GoodsLofModel')->add_detail_action($id, $detail_data_lof, 'adjust');
            //单据批次添加
            $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $store_code, 'adjust', $detail_data_lof);
            if ($ret['status'] < 1) {
                return $ret;
            }
            //调整单明细添加
            $ret = load_model('stm/StmStockAdjustRecordDetailModel')->add_detail_action($id, $detail_data_lof);
            if ($ret['status'] == '1') {
                //日志
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '增加明细', 'module' => "stock_adjust_record", 'pid' => $id);
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            }
        }
        $ret['data'] = '';
        if (!empty($barcode_num)) {
            $sku_error = array_keys($barcode_num);
            foreach ($sku_error as $err) {
                $error_msg[] = array($err => '系统不存在该条码信息');
                $err_num ++;
            }
        }

        $success_num = $all_num - $err_num;
        $message = '导入成功' . $success_num;
        if ($err_num > 0 || !empty($error_msg)) {
            $message .= ',' . '失败数量:' . $err_num;
            $fail_top = array('商品条码', '错误信息');
            $file_name = $this->create_import_fail_files($fail_top, $error_msg);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name, array('export_name' => 'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        $ret['message'] = $message;
        return $ret;
    }

    function create_import_fail_files($fail_top, $error_msg) {
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($error_msg as $key => $val) {
            $key = array_keys($val);
            $val_data = array($key[0], $val[$key[0]]);
            $file_str .= implode(",", $val_data) . "\r\n";
        }
        $filename = md5("stock_adjust_record" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

    function read_csv_sku($file, &$sku_arr, &$sku_num) {
        //    $key_arr = array('0'=>'sku','1'=>'num');
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $sku_arr[] = trim($row[0]);
                    $sku_num[trim($row[0])] = $row[1];
                }
            }
            $i++;
        }
        fclose($file);
        // var_dump($sku_arr,$sku_num);die;
    }

    private function tran_csv(&$row) {
        if (!empty($row)) {
            foreach ($row as &$val) {
                $val = iconv('gbk', 'utf-8', $val);
                $val = str_replace('"', '', $val);
                //   $row[$key] = $val;
            }
        }
    }

    function read_csv_lof($file, &$sku_arr, &$sku_num) {
        //    $key_arr = array('0'=>'sku','1'=>'num','lof_no'=>,);
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $row[0] = trim($row[0]);
                    $sku_arr[] = $row[0];
                    $sku_num[$row[0]][$row[3]]['lof_no'] = $row[1];
                    $production_date = load_model('prm/GoodsLofModel')->get_lof_production_date($row[1], $row[0]);
                    $sku_num[$row[0]][$row[3]]['production_date'] = !empty($production_date) ? $production_date : $row[2];
                    $sku_num[$row[0]][$row[3]]['num'] = $row[3];
                }
            }
            $i++;
        }
        fclose($file);
        return $i;
    }

    /*
     * 删除记录
     * */

    function delete($stock_adjust_record_id) {
        $ret = $this->get_by_id($stock_adjust_record_id);
        if ($ret['data']['is_check_and_accept'] == 1) {
            return $this->format_ret('-1', array(), '单据已经验收，不能删除！');
        }
        $ret = parent::delete(array('stock_adjust_record_id' => $stock_adjust_record_id));
        $this->db->create_mapper('stm_stock_adjust_record_detail')->delete(array('pid' => $stock_adjust_record_id));
        $this->db->create_mapper('b2b_lof_datail')->delete(array('pid' => $stock_adjust_record_id, 'order_type' => 'adjust'));
        //日志
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '删除', 'module' => "stock_adjust_record", 'pid' => $stock_adjust_record_id);
        $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        return $ret;
    }

    /*
     * 添加新纪录
     */

    function insert($stock_adjus) {
        $status = $this->valid($stock_adjus);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->is_exists($stock_adjus['record_code']);

        if (!empty($ret['data']))
            return $this->format_ret('-1', '', 'RECORD_ERROR_UNIQUE_CODE1');
        $stock_adjus['is_add_time'] = date('Y-m-d H:i:s');
        $stock_adjus['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $stock_adjus['remark']);
        return parent::insert($stock_adjus);
    }

    public function is_exists($value, $field_name = 'record_code') {
        $ret = parent::get_row(array($field_name => $value));

        return $ret;
    }

    /*
     * 服务器端验证
     */

    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['record_code']) || !valid_input($data['record_code'], 'required')))
            return RECORD_ERROR_CODE;
        return 1;
    }

    /**
     * 新增一条库存调整单记录
     *
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since  2014-10-23
     * @param array $ary_main 主单据数组
     * @return array 返回新增结果
     */
    public function add_action($ary_main) {
        //校验参数
        if (!isset($ary_main['store_code']) || !valid_input($ary_main['store_code'], 'required')) {
            return RECORD_ERROR_STORE_CODE;
        }
        //插入主单据
        //生成调整单号
        if (!isset($ary_main['record_code']) && empty($ary_main['record_code'])) {
            require_lib('comm_util', true);
            $ary_main['record_code'] = $this->create_fast_bill_sn();
        }
        $ary_main['is_add_time'] = date('Y-m-d H:i:s');
        $ret = $this->insert($ary_main);
        //返回结果
        return $ret;
    }

    /**
     * 生成单据号
     */
    function create_fast_bill_sn($num = 0) {

        $sql = "select stock_adjust_record_id  from {$this->table}   order by stock_adjust_record_id desc limit 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $djh = intval($data[0]['stock_adjust_record_id']) + 1;
        } else {
            $djh = 1;
        }
        $djh = $num + $djh;
        require_lib('comm_util', true);
        $jdh = "TZ" . date("Ymd") . add_zero($djh);
        return $jdh;
    }

    /**
     * 编辑一条库存调整记录
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since  2014-11-12
     * @param array $data
     * @param array $where
     * @return arrayinsert
     */
    public function edit_action($data, $where) {
        $data['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $data['remark']);
        if (!isset($where['stock_adjust_record_id']) && !isset($where['record_code'])) {
            return $this->format_ret(false, array(), '修改时缺少主单据ID或code!');
        }
        $result = $this->get_row($where);
        if (1 != $result['status']) {
            return $this->format_ret(false, array(), '没找到单据!');
        }
        if (1 == $result['data']['is_check_and_accept']) {
            return $this->format_ret(false, array(), '单据已经验收,不能修改!');
        }
        if (isset($data['store_code'])) {
            $ret = load_model('stm/GoodsInvLofRecordModel')->modify_store_code($data['store_code'], $result['data']['record_code']);
        }
        //更新主表数据
        return parent::update($data, $where);
    }

    function update_check($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'error_params');
        }
        $ret = parent:: update(array('is_check_and_accept' => $active), array('stock_adjust_record_id' => $id));
        return $ret;
    }

    /**
     * 根据调整单ID来验收调整单, 验收后生成库存流水, 并更改库存帐表
     *
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since  2014-11-11
     * @param int $id
     * @return array
     */
    function checkin($id) {
        //检查调整单状态是否为已验收
        $record = $this->get_row(array('stock_adjust_record_id' => $id));

        $details = load_model('stm/StmStockAdjustRecordDetailModel')->get_all(array('pid' => $id));
        if (isset($record['data']['is_check_and_accept']) && 1 == $record['data']['is_check_and_accept']) {
            return $this->format_ret(false, array(), '该单据已经验收!');
        }

        //检查明细是否为空
        if (false == $details['status'] || empty($details['data'])) {
            return $this->format_ret(false, array(), '单据明细不能为空!');
        }
        //更新库存帐表

        $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($id, 'adjust');

        if (empty($ret_lof_details['data'])) {
            return $this->format_ret(false, array(), '单据明细异常!');
        }

        require_model('prm/InvOpModel');
        $this->begin_trans();
        $invobj = new InvOpModel($record['data']['record_code'], 'adjust', $record['data']['store_code'], 3, $ret_lof_details['data']);

        $ret = $invobj->adjust();

        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            //验收失败,单独获取失败的商品sku
            $data = load_model('prm/InvModel')->get_fail_sku($ret_lof_details['data']);
            foreach ($data as $value) {
                //通过sku获取商品条形码
                $barcode = load_model('goods/SkuCModel')->get_barcode($value);
                $error_msg[] = array($barcode => '库存不足，不允许负库存');
            }
            $fail_top = array('商品条码', '错误信息');
            $file_name = $this->create_import_fail_files($fail_top, $error_msg);
//            $message .= "验收失败，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name, array('export_name' => 'error'));
            $message .= "验收失败，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
            $new_ret['status'] = $ret['status'];
            $new_ret['message'] = $message;
            return $new_ret;
        }
        $ret = parent:: update(array('is_check_and_accept' => 1,), array('stock_adjust_record_id' => $id, 'is_check_and_accept' => 0,));

        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }
        $this->commit(); //事务提交

        return $this->format_ret(1, array(), '验收成功!');
    }

    /**
     * @todo  API-参数处理
     * @param array $param 传入参数
     * @return array 符合条件参数数组
     */
    private function deal_api_data($param, $key_option) {
        $arr_option = array();
        //提取可选字段中已赋值数据
        $ret_option = valid_assign_array($param, $key_option, $arr_option);
        //合并数据
        $arr_deal = $arr_option;
        unset($arr_option);
        return $arr_deal;
    }

    /**
     * @todo        创建仓库调整单接口
     * @author      BaiSon PHP
     * @date        2016-03-17
     * @param       array $param
     *               array(
     *                  必选: 'store_code'
     *                  可选: 'init_code','adjust_type','record_time','remark'
     *               )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":""}
     */
    public function api_stock_adjust_create($param) {
        if (empty($param['store_code'])) {
            return $this->format_ret(-10001, '', 'RECORD_ERROR_STORE_CODE');
        }
        //可选字段
        $key_option = array(
            's' => array(
                'store_code', 'init_code', 'adjust_type', 'record_time', 'remark'
            )
        );
        $arr_deal = $this->deal_api_data($param, $key_option); //匹配可用参数值
        unset($param);

        //校验仓库
        $store = load_model('base/StoreModel')->get_by_code($arr_deal['store_code']);
        if (empty($store['data'])) {
            return $this->format_ret(-1, '', '盘点仓库不存在');
        }
        //校验调整类型
        if (!empty($arr_deal['adjust_type'])) {
            $adjust_type = load_model('base/StoreAdjustTypeModel')->get_by_page(array('record_type_code' => $arr_deal['adjust_type']));
            if (empty($adjust_type['data']['data'])) {
                return $this->format_ret(-1, '', '调整类型不存在');
            }
        } else {
            $arr_deal['adjust_type'] = '800'; //调整类型默认为 800=>期初调整
        }
        //判断日期
        if (empty($arr_deal['record_time'])) {
            $arr_deal['record_time'] = date('Y-m-d');
        } else if (strtotime($arr_deal['record_time']) == false) {
            return $this->format_ret(-1, '', '日期格式错误');
        }
        //生成单据号
        $arr_deal['record_code'] = $this->create_fast_bill_sn();
        $arr_deal['is_add_person'] = 'OPENAPI';
        $ret = $this->insert($arr_deal);
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '操作失败');
        }
        //日志
        $log = array('user_id' => '1', 'user_code' => 'OPENAPI', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => 'API-创建调整单', 'module' => "stock_adjust_record", 'action_note' => 'API调用生成', 'pid' => $ret['data']);
        $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        return $this->format_ret(1, array('record_code' => $arr_deal['record_code']), '保存成功');
    }

    /**
     * @todo        更新仓库调整单接口
     * @author      BaiSon PHP
     * @date        2016-03-17
     * @param       array $param
     *               array(
     *                  必选: 'record_code'
     *                  必选: 'barcode_list'=>array(
     *                        'barcode','num','lof_no','production_date'
     *                         )
     *               )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":""}
     */
    function api_stock_adjust_update($param) {
        if (!isset($param['record_code']) || empty($param['record_code'])) {
            return $this->format_ret(-10001, '', '调整单号为必填项');
        }

        $record = $this->get_by_field('record_code', $param['record_code'], 'stock_adjust_record_id id,store_code');
        if ($record['status'] != 1) {
            return $this->format_ret(-1, array('reocrd_code' => $param['record_code']), '调整单不存在');
        }
        $id = $record['data']['id'];
        $store_code = $record['data']['store_code'];

        $check_key = array('barcode' => '条码', 'num' => '调整数');
        $lof_status = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        if ($lof_status['lof_status'] == 1) {
            $check_key = array('barcode' => '条码', 'num' => '调整数', 'lof_no' => '批次', 'production_date' => '生产日期');
        }
        $adjust_detail = json_decode($param['barcode_list'], true);
        //检查明细是否为空
        $find_data = $this->api_check_detail($adjust_detail, $check_key);
        if ($find_data['status'] != 1) {
            return $find_data;
        }

        $data = $this->api_get_goods_detail($adjust_detail);
        //批次档案维护
        $ret = load_model('prm/GoodsLofModel')->add_detail_action($id, $data);
        //单据批次添加
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $store_code, 'adjust', $data);
        if ($ret['status'] < 1) {
            return $ret;
        }
        //调整单明细添加
        $ret = load_model('stm/StmStockAdjustRecordDetailModel')->add_detail_action($id, $data);
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', $ret['message']);
        }
        //日志
        $log = array('user_id' => '1', 'user_code' => '系统管理员', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => 'API-增加明细', 'module' => "stock_adjust_record", 'pid' => $id);
        $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        return $this->format_ret(1, '', '更新成功');
    }

    /**
     * 检查接口传入明细信息是否为空
     */
    private function api_check_detail($adjust_detail, $check_key) {
        $err_data = array();
        foreach ($adjust_detail as $key => $val) {
            foreach ($check_key as $k => $v) {
                if (empty($val[$k])) {
                    $err_data[$key][$k] = $v;
                }
            }
        }
        if (!empty($err_data)) {
            return $this->format_ret(-10001, $err_data, "明细数据不能为空");
        }
        return $this->format_ret(1);
    }

    /**
     * 获取商品明细信息
     */
    private function api_get_goods_detail($adjust_detail) {
        $data = array();
        foreach ($adjust_detail as $key => $val) {
            $sql = "SELECT b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,g.sell_price FROM goods_sku b
                    INNER JOIN  base_goods g ON g.goods_code = b.goods_code
                    WHERE b.barcode ='{$val['barcode']}' GROUP BY b.barcode ";
            $goods_data = $this->db->get_row($sql);
            $data[] = array_merge($goods_data, $adjust_detail[$key]);
        }
        return $data;
    }

    /**
     * @todo        验收仓库调整单接口
     * @author      BaiSon PHP
     * @date        2016-03-17
     * @param       array $param
     *              array(
     *                  必选: 'record_code'
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":""}
     */
    function api_stock_adjust_accept($param) {
        if (!isset($param['record_code']) || empty($param['record_code'])) {
            return $this->format_ret(-10001, '', 'API_RETURN_MESSAGE_10001');
        }
        $record_code_arr = json_decode($param['record_code'], true);
        if (!is_array($record_code_arr)) {
            $record_code_arr = array($param['record_code']);
        }
        $msg = array();
        $record = array();
        foreach ($record_code_arr as $val) {
            $ret = $this->get_by_field('record_code', $val, 'stock_adjust_record_id id,record_code');
            if ($ret['status'] == 1) {
                $record[] = $ret['data'];
            } else {
                $msg[] = array('status' => 1, 'data' => $val, 'message' => '调整单不存在');
            }
        }

        foreach ($record as $val) {
            $ret = $this->checkin($val['id']);
            if ($ret['status'] == '1') {
                $msg[] = array('status' => 1, 'data' => $val['record_code'], 'message' => $ret['message']);
                //日志
                $log = array('user_id' => '1', 'user_code' => '系统管理员', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '已验收', 'action_name' => 'API-验收单据', 'module' => "stock_adjust_record", 'pid' => $val['id']);
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            } else {
                if ($ret['status'] === FALSE) {
                    $ret['status'] = -1;
                    $msg[] = array('status' => $ret['status'], 'data' => $val['record_code'], 'message' => $ret['message']);
                } else if ($ret['status'] != 1) {
                    $ret['status'] = -1;
                    $msg[] = array('status' => $ret['status'], 'data' => $val['record_code'], 'message' => '操作失败');
                }
            }
        }
        return $this->format_ret(1, $msg);
    }

    function api_create_adjust_record($param) {

        if (!isset($param['store_code'])) {
            return $this->format_ret(-1, '', '请设置调整仓库');
        }

        $lof_manage = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        $is_lof = ($lof_manage['lof_status'] == 1) ? TRUE : FALSE;


        if (isset($param['wms_type'])) {
            $wms_conf = require_conf('sys/wms');
            $wms_type = $param['wms_type'];
            if (!isset($wms_conf[$wms_type])) {
                return $this->format_ret(-1, '', '暂不支持此外部仓储类型');
            }
            $is_lof = $wms_conf[$wms_type]['is_lof'];
            $store_code = load_model('sys/WmsConfigModel')->get_store_code_by_out_code($wms_type, $param['store_code']);
            if (empty($store_code)) {
                return $this->format_ret(-1, '', '为找到对应仓库');
            }
        } else {
            $store_code = $param['store_code'];
        }

        $record_data = array();
        $record_data['adjust_type'] = '802';
        $record_data['price_type'] = 'sell_price';
        $record_data['store_code'] = $store_code;
        $record_data['record_code'] = $this->create_fast_bill_sn();
        $record_data['init_code'] = isset($param['init_code']) ? $param['init_code'] : '';

        $detail = json_decode($param['detail'], TRUE);


        $check_key = array('barcode' => '条码', 'num' => '数量');
        if ($is_lof) {
            $check_key['lof_no'] = '批次';
        } else {
            $this->set_default_lof();
        }
        $detail_data = array();
        foreach ($detail as $val) {
            $find_data = $this->check_data($val, $check_key);

            if (!empty($find_data)) {
                return $this->format_ret(-1, "明细数据不能为空" . implode(",", $find_data));
            }

            $row = $this->set_detail($val, $is_lof);
            if (empty($row)) {
                $msg = ($is_lof === TRUE) ? "商品条码({$val['barcode']})或批次({$val['lof_no']})不存在" : '商品条码不存在:' . $val['barcode'];
                return $this->format_ret(-1, '', $msg);
            }
            $detail_data[] = $row;
        }
        $this->begin_trans();
        $ret_record = $this->insert($record_data);
        $id = $ret_record['data'];


        $ret2 = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $store_code, 'adjust', $detail_data);
        if ($ret2['status'] < 1) {
            $this->rollback();
            return $ret2;
        }
        //调整单明细添加
        $ret3 = load_model('stm/StmStockAdjustRecordDetailModel')->add_detail_action($id, $detail_data);

        $this->commit();

        $ret = $this->checkin($id);
        if ($ret['status'] < 1) {
            return $ret;
        }
        return $this->format_ret(1, '', '调整成功');
    }

    private function check_data($check_data, $key_arr) {
        $key_arr = array();

        $no_find = array();
        foreach ($key_arr as $key => $name) {
            if (!isset($check_data[$key])) {
                $no_find[] = $name . "({$key})";
            }
        }
        return $no_find;
    }

    private function set_detail($row, $is_lof) {
        $new_row = array();
        if ($is_lof) {
            $sql = "select s.sku,l.lof_no,l.production_date from goods_sku s "
                    . "INNER JOIN goods_lof l ON s.sku=l.sku"
                    . " where s.barcode=:barcode AND l.lof_no=:lof_no ";
            $data = $this->db->get_row($sql, array(':barcode' => $row['barcode'], ':lof_no' => $row['lof_no']));
            if (!empty($data)) {
                $new_row['sku'] = $data['sku'];
                $new_row['num'] = $row['num'];
                $new_row['lof_no'] = $data['lof_no'];
                $new_row['production_date'] = $data['production_date'];
            }
        } else {
            $sql = "select sku,lof_no,production_date from goods_sku s "
                    . " where s.barcode=:barcode  ";
            $data = $this->db->get_row($sql, array(':barcode' => $row['barcode']));
            if (!empty($data)) {
                $new_row['sku'] = $data['sku'];
                $new_row['num'] = $row['num'];
                $new_row['lof_no'] = $this->lof_no;
                $new_row['production_date'] = $this->production_date;
            }
        }
        return $new_row;
    }

    private function set_default_lof() {

        $ret_lof = load_model('prm/GoodsLofModel')->get_sys_lof();
        $this->lof_no = $ret_lof['data']['lof_no'];
        $this->production_date = $ret_lof['data']['production_date'];
    }

    function get_detail_by_record_code($record_code, $lof_status, $goods_code) {
        $sql = "SELECT
                    st.stock_adjust_record_id,
                    st.is_check_and_accept,
                    st.record_time,
                    st.init_code,
                    bt.record_type_name,
                    bs.store_name
                FROM
                    stm_stock_adjust_record st
                LEFT JOIN base_store bs on st.store_code=bs.store_code
                LEFT JOIN base_record_type bt ON st.adjust_type=bt.record_type_code
                WHERE record_code=:record_code";
        $sql_value = array(":record_code" => $record_code);
        $data = $this->db->get_row($sql, $sql_value);
        $data['is_check_and_accept'] = ($data['is_check_and_accept'] == 1) ? "已验收" : "未验收";
        if ($lof_status == 1) {
            $select = "SELECT sd.*,gs.spec1_name,gs.spec2_name,bg.goods_name,gs.barcode,bd.lof_no,bd.production_date,bd.num as d_num";
            $sql_join = "INNER JOIN base_goods bg ON bg.goods_code = sd.goods_code
                         INNER JOIN b2b_lof_datail bd ON sd.record_code = bd.order_code
                         INNER JOIN goods_sku gs ON bd.sku = gs.sku ";
            $sql_where = " AND order_type='adjust'";
        } else {
            $select = "SELECT sd.*,gs.spec1_name,gs.spec2_name,bg.goods_name,gs.barcode,sd.num as d_num";
            $sql_join = "INNER JOIN goods_sku gs ON sd.sku = gs.sku
                         INNER JOIN base_goods bg ON sd.goods_code = bg.goods_code";
        }
        $where = (!empty($goods_code)) ? " WHERE sd.record_code=:record_code AND sd.sku=gs.sku {$sql_where} AND (sd.goods_code LIKE :goods_code OR gs.barcode LIKE :goods_code) " : " WHERE sd.record_code=:record_code AND sd.sku=gs.sku {$sql_where} ";
        $detail_sql = "{$select} FROM stm_stock_adjust_record_detail sd {$sql_join} {$where} {$sql_group} ";
        $detail_value = (!empty($goods_code)) ? array(":record_code" => $record_code, ":goods_code" => "%" . trim($goods_code) . "%") : array(":record_code" => $record_code);
        $detail_data = $this->db->get_all($detail_sql, $detail_value);
        foreach ($detail_data as &$value) {
            $value['is_check_and_accept'] = $data['is_check_and_accept'];
            $value['record_code'] = $record_code;
            $value['record_time'] = $data['record_time'];
            $value['init_code'] = $data['init_code'];
            $value['adjust_type_name'] = $data['record_type_name'];
            $value['store_name'] = $data['store_name'];
        }
        return $detail_data;
    }

    //修改扫描数量
    function update_scan_num($record_code, $num, $id) {
        $ret = $this->get_row(array('record_code' => $record_code));
        $sku = substr($id, 8);
        //$sku = $this->db->get_value("select sku from goods_sku where barcode = :barcode", array(":barcode" => $barcode));

        $detail = $this->db->get_row("select * from stm_stock_adjust_record_detail where record_code = '{$record_code}' and sku = '{$sku}'");
        if (empty($detail)) {
            return $this->format_ret(-1, '', '单据明细信息不存在');
        }
        if ($detail['num'] != $num && $num < 0) {
            return $this->format_ret(-1, '', '修改扫描数量不能为负数');
        }
        $detail['num'] = $num;
        $ret = $this->edit_detail_action($ret['data']['stock_adjust_record_id'], $detail);
        if ($ret) {
            return $this->format_ret(1, '', '更细成功');
        } else {
            return $this->format_ret(-1, '', '扫描更新单据明细数量失败');
        }
    }

    public function edit_detail_action($pid, $data) {
        $ret = $this->db->update('stm_stock_adjust_record_detail', array('num' => $data['num'], 'money' => $data['num'] * $data['price'] * $data['rebate']), array('pid' => $pid, 'sku' => $data['sku']));
        //回写金额和数量
        $res = load_model('stm/StmStockAdjustRecordDetailModel')->mainWriteBack($pid);
        $this->update_lof_detail($data['record_code'], $data['sku'], $data['num']);
        return $ret;
    }

    function update_lof_detail($record_code, $sku, $num) {
        $sql = "select * from b2b_lof_datail where order_code=:record_code AND sku=:sku AND order_type='adjust' ";
        $data = $this->db->get_all($sql, array(':record_code' => $record_code, 'sku' => $sku));
        $is_only = 0;
        foreach ($data as $val) {
            if ($is_only == 0) {
                $sql = "update b2b_lof_datail set num='{$num}',init_num='{$num}' where id='{$val['id']}' ";
                $is_only = 1;
            } else {
                $sql = "delete from b2b_lof_datail where id='{$val['id']}' ";
            }
            $this->db->query($sql);
        }
    }

    public function add_detail($param) {
        $this->begin_trans();
        $ret = load_model('prm/GoodsLofModel')->add_detail_action($param['record_id'], $param['detail'], 'adjust');
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($param['record_id'], $param['store_code'], 'adjust', $param['detail']);
        if ($ret['status'] < 1) {
            $this->rollback();
            return $ret;
        }
        $ret = load_model('stm/StmStockAdjustRecordDetailModel')->add_detail_action($param['record_id'], $param['detail'], 'adjust');
        if ($ret['status'] == 1) {
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '增加明细', 'module' => "stock_adjust_record", 'pid' => $param['record_id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            $this->commit();
        } else {
            $this->rollback();
        }

        return $ret;
    }

}
