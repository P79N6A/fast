<?php

require_model('tb/TbModel');

class ActivityGoodsModel extends TbModel {

    function get_table() {
        return 'crm_goods';
    }

    /* 获取列表数据 */

    function get_crm_goods_by_page($filter) {
        $type = $filter['type'];
        if ($type == 0) {//普通商品
            $select = 'rl.update_num,rl.sell_num,rl.lock_num,rl.inv_num,r3.goods_code,r3.goods_name,r2.spec1_name,r2.spec1_code,r2.spec2_code,r2.spec2_name,r2.barcode,rl.sync_type,rl.sku';
            $sql_main = "FROM {$this->table} AS rl LEFT JOIN goods_sku AS r2 ON rl.sku = r2.sku LEFT JOIN base_goods AS r3 ON r2.goods_code = r3.goods_code WHERE 1 = 1 and rl.sync_type = 0";
        } elseif ($type == 1) {//套餐商品
            $select = 'rl.update_num,rl.sell_num,rl.lock_num,rl.inv_num,r2.goods_code,r2.barcode,rr.goods_name,r2.spec1_code,r2.spec2_code,r2.barcode,rl.sync_type,rl.sku';
            $sql_main = "FROM {$this->table} AS rl LEFT JOIN goods_combo_barcode AS r2 ON rl.sku = r2.sku LEFT JOIN goods_combo AS rr ON r2.goods_code = rr.goods_code WHERE 1 = 1 and rl.sync_type = 1";
        }
        $sql_values = array();
        if ($filter['activity_code'] != '' && isset($filter['activity_code'])) {
            $sql_main .= " AND rl.activity_code = :activity_code";
            $sql_values[':activity_code'] = $filter['activity_code'];
        }

        if ($type == 0 && $filter['code_name'] != '' && isset($filter['code_name'])) {
            $sql_main .= " AND (r2.barcode = :code_name OR r3.goods_code = :code_name OR r3.goods_name like :goods_name)";
            $sql_values[':code_name'] = $filter['code_name'];
            $sql_values[':goods_name'] = "%" . $filter['code_name'] . "%";
        } elseif ($type == 1 && $filter['code_name'] != '' && isset($filter['code_name'])) {
            $sql_main .= " AND (r2.barcode = :code_name OR rr.goods_code = :code_name  OR rr.goods_name like :goods_name)";
            $sql_values[':code_name'] = $filter['code_name'];
            $sql_values[':goods_name'] = "%" . $filter['code_name'] . "%";
        }

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        if (empty($data['data'])) {
            return $this->format_ret(1, $data);
        }

        $send_store_code = $this->db->get_value('SELECT send_store_code FROM base_shop WHERE shop_code=:shop_code', [':shop_code' => $filter['shop_code']]);

        if ($type == 0) {
            $spec1_arr = load_model('base/ArchiveSearchModel')->get_single_data('spec1', $data['data'], 'spec1_code');
            $spec2_arr = load_model('base/ArchiveSearchModel')->get_single_data('spec2', $data['data'], 'spec2_code');
        }
        $goods_barcode_key = array();
        foreach ($data['data'] as $key => &$row) {
            if ($type == 1) {
                $goods_barcode_key[$row['sku']] = $key;
                $inv_num = $this->get_inv_combo($row['sku'], $send_store_code);
                $data['data'][$key]['result_inv'] = $inv_num - ($row['update_num'] - $row['sell_num']);
            } else {
                $row['spec1_name'] = $spec1_arr[$row['spec1_code']];
                $row['spec2_name'] = $spec2_arr[$row['spec2_code']];
                $row['spec'] = "商品名称：" . $row['goods_name'] . "<br/>" . "规格1：" . $row['spec1_name'] . ",<br>" . "规格2：" . $row['spec2_name'];

                $inv_num = $this->get_effec_num_common($send_store_code, $row['sku']);
                $data['data'][$key]['result_inv'] = $inv_num - ($row['update_num'] - $row['sell_num']);
            }
            if ($data['data'][$key]['result_inv'] < 0) {
                $data['data'][$key]['result_inv'] = 0;
            }
        }
        //如果是套餐,获取子商品信息
        if ($type == 1 && !empty($goods_barcode_key)) {
            $sku_arr = array_keys($goods_barcode_key);
            $sql_values = [];
            $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
            $sql = "SELECT s.spec1_name,s.spec2_name,s.barcode,s.goods_code,bg.goods_name,d.num,d.p_sku from goods_sku s INNER JOIN goods_combo_diy d ON s.sku=d.sku INNER JOIN base_goods bg ON s.goods_code=bg.goods_code WHERE p_sku IN ({$sku_str})";
            $sku_data = $this->db->get_all($sql, $sql_values);
            $chlid_spec_arr = [];
            foreach ($sku_data as $v) {
                $key = $goods_barcode_key[$v['p_sku']];
                $chlid_spec_arr[$key][] = "商品名称：" . $v['goods_name'] . "<br/>" . "商品{$v['barcode']}({$v['num']}件):{$v['spec1_name']},{$v['spec2_name']}";
                $data['data'][$key]['spec'] = implode("<br />", $chlid_spec_arr[$key]);
            }
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_inv_combo($sku, $send_store_code) {
        $sql = "select sku,num from goods_combo_diy where p_sku='{$sku}'";
        $sku_data = $this->db->get_all($sql);
        $sku_c_arr = array();
        foreach ($sku_data as $val) {
            $sku_c_arr[$val['sku']] = $val['num'];
        }
        $sku_arr = array_keys($sku_c_arr);

        $data_sum = array();
        if (!empty($sku_arr)) {
            $sql_values = [':store_code' => $send_store_code];
            $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
            $sql = "select stock_num,lock_num ,sku from goods_inv where sku in ({$sku_str}) and store_code=:store_code";
            $data_sum = $this->db->get_all($sql, $sql_values);
        }
        if (!empty($data_sum)) {
            $result = -1;
            foreach ($data_sum as $s_val) {
                $sku_num = $sku_c_arr[$s_val['sku']];
                $num = ceil(($s_val['stock_num'] - $s_val['lock_num']) / $sku_num);
                $num = $num < 0 ? 0 : $num;
                $result = $result == -1 ? $num : min($result, $num);
            }
        } else {
            $result = 0;
        }

        return $result;
    }

    /* 获取重叠时间段内商品改变数量 */

    function get_change_num($start_time, $end_time, $sku) {
        $inv_num_sum = 0;
        $data_inv = $this->get_data($start_time, $end_time, $sku);
        foreach ($data_inv as &$value) {
            $inv_num_sum += $value['update_num'];
        }
        return $inv_num_sum;
    }

    /* 获取子商品的库存数量 */

    function get_child_barcode_by_page($filter) {
        $sql_barcode = "select sku from {$this->table} where sync_type = 1 and activity_code = :activity_code ";
        $sql_b_values[':activity_code'] = $filter['activity_code'];
        $barcode = $this->db->get_all($sql_barcode, $sql_b_values);
        /* 获取套餐商品sku码 */
        foreach ($barcode as $key => &$val) {
            $list[] = "'" . $val['sku'] . "'";
        }
        /* 获取子商品条码 */
        $barcode_list = implode(',', $list);
        $sql = "SELECT r2.barcode,r2.sku,r2.goods_code,r2.spec1_code,r2.spec2_code,rl.p_sku,rl.num FROM goods_combo_diy AS rl LEFT JOIN goods_sku AS r2 ON rl.sku = r2.sku where 1 = 1 and rl.p_sku in ({$barcode_list}) ";
        $data = $this->db->get_all($sql);

        $send_store_code = $this->db->get_value('SELECT send_store_code FROM base_shop WHERE shop_code=:shop_code', [':shop_code' => $filter['shop_code']]);
        /* 子商品所包含的套餐名称 */
        foreach ($data as &$value) {
            $name_list = '';
            $sql_name = "select r3.barcode,rl.num from goods_combo_diy as rl LEFT JOIN goods_combo_barcode as r3 ON rl.p_sku=r3.sku where rl.sku = :sku and rl.p_sku in ({$barcode_list})";
            $name = $this->db->get_all($sql_name, array(':sku' => $value['sku']));
            foreach ($name as $val_name) {
                $name_list .= $val_name['barcode'] . ',';
            }
            $value['name_list'] = rtrim($name_list, ",");
            $value['inv_num'] = $this->get_effec_num_common($send_store_code, $value['sku'], $value['num']);
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        foreach ($data as &$value) {
            $n[$value['p_sku']] = $value['num'];
        }
        $ret_num = $n;
        return $this->format_ret($ret_status, $ret_data, $ret_num);
    }

    /* 获取子商品列表 */

    function get_child_barcode($filter) {
        $sql_main = "from crm_goods_children as rl inner join goods_sku r2 on rl.sku=r2.sku where activity_code = :activity_code group by r2.barcode";
        $sql_values[':activity_code'] = $filter['activity_code'];
        $select = "DISTINCT rl.sku,rl.lock_num,rl.inv_num,rl.p_sku,r2.barcode,r2.goods_code";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        foreach ($data['data'] as $key => &$val) {
            $val['inv_num'] = $val['inv_num'] - $val['lock_num'];
            $val['name_list'] = $this->get_name_list($val['sku'], $filter['activity_code']);
            $name = load_model("prm/GoodsModel")->get_by_goods_code($val['goods_code']);
            $val['goods_name'] = $name['data']['goods_name'];
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_name_list($sku, $activity) {
        $sql = "select p_sku from crm_goods_children where sku=:sku and activity_code=:activity_code";
        $data = $this->db->get_all($sql, array(':sku' => $sku, ':activity_code' => $activity));
        foreach ($data as $val) {
            $list[] = $this->get_barcode($val['p_sku']);
        }
        $list = array_unique($list);
        $name_list = implode(',', $list);
        return $name_list;
    }

    //添加库存同步设置
    function insert($filter) {
        $crm_goods_arr = array();
        $sku_arr = array();
        $combo_arr = array();
        //是否启用
        $activity_data = load_model('crm/ActivityModel')->is_exists($filter['activity_code']);
        if ($activity_data['data']['status'] == 1) {
            return $this->format_ret(-1, '', '活动已启用');
        }

        foreach ($filter['data'] as $key => $val) {

            $sql = "select barcode,sku from goods_sku where sku = :code";
            $sku_info = $this->db->get_row($sql, array('code' => $val['sku']));
            $td_ID = $this->get_td_ID($val['goods_code'], $filter['shop_code']);
            $crm_goods_val = array(
                'activity_code' => $filter['activity_code'],
                'shop_code' => $filter['shop_code'],
                'sku' => $val['sku'],
                'goods_code' => $val['goods_code'],
                'goods_from_id' => $td_ID
            );
            if (!empty($sku_info)) {
                $crm_goods_val['sync_type'] = 0;
                $sku_arr[] = $val['sku'];
            } else {
                $crm_goods_val['sync_type'] = 1;
                $sql = "select barcode,sku from goods_combo_barcode where sku = :code";
                $sku_info = $this->db->get_row($sql, array('code' => $val['sku']));
                $combo_arr[] = $val['sku'];
            }
            $crm_goods_arr[] = $crm_goods_val;
            $barcode = $sku_info['barcode'];
            $log[$val['sku']] = array('activity_code' => $filter['activity_code'], 'user_code' => CTX()->get_session('user_id'), 'user_name' => CTX()->get_session('user_code'), 'action_name' => '添加商品', 'action_time' => date('Y-m-d H:i:s', time()), 'action_desc' => "添加商品{$barcode}");
        }

        $this->insert_multi_exp('crm_goods', $crm_goods_arr, true);
        $this->refresh_goods_children_data($filter['activity_code'], $combo_arr);

        $this->inv_fresh_goods($filter['activity_code'], $filter['shop_code'], $sku_arr);
        $this->inv_fresh_combo($filter['activity_code'], $filter['shop_code'], $combo_arr);
        $ret = load_model('crm/ActivityLogModel')->insert_multi($log);

        return $this->format_ret(1);
    }

    function get_td_ID($goods_code, $shop_code) {
        $td_list = "select goods_from_id from api_goods where goods_code='{$goods_code}' and shop_code='{$shop_code}'";
        $td_ID = $this->db->get_row($td_list);
        return $td_ID['goods_from_id'];
    }

    //根据sku和活动编号判断商品是否存在
    function is_goods_sku($activity_code, $sku) {
        $ret = $this->get_row(array('activity_code' => $activity_code, 'sku' => $sku));
        return $ret;
    }

    //一键删除商品信息
    function delete($activity_code, $tab) {
        //是否启用
        $activity_data = load_model('crm/ActivityModel')->is_exists($activity_code);
        if ($activity_data['data']['status'] == 1) {
            return $this->format_ret(-1, '', '活动已启用');
        }
        if ($tab == 0) {
            $ret = parent::delete(array('activity_code' => $activity_code, 'sync_type' => 0));
            $log = array('activity_code' => $activity_code, 'user_code' => CTX()->get_session('user_id'), 'user_name' => CTX()->get_session('user_code'), 'action_name' => '删除商品', 'action_time' => date('Y-m-d H:i:s', time()), 'action_desc' => "一键删除普通商品");
            $ret = load_model('crm/ActivityLogModel')->insert($log);
        }
        if ($tab == 1) {
            $ret = parent::delete(array('activity_code' => $activity_code, 'sync_type' => 1));
            $ret = parent::delete_exp('crm_goods_children', array('activity_code' => $activity_code));
            $log = array('activity_code' => $activity_code, 'user_code' => CTX()->get_session('user_id'), 'user_name' => CTX()->get_session('user_code'), 'action_name' => '删除商品', 'action_time' => date('Y-m-d H:i:s', time()), 'action_desc' => "一键删除套餐商品");
            $ret = load_model('crm/ActivityLogModel')->insert($log);
        }
        return $ret;
    }

    //导入商品
    function imoprt_activity_goods($param, $file) {
        //是否启用
        $activity_data = load_model('crm/ActivityModel')->is_exists($param['activity_code']);
        if ($activity_data['data']['status'] == 1) {
            return $this->format_ret(-1, '', '错误：活动已启用');
        }
        $barcode_arr = $inv_num = array();
        $goods_data_combo = array();
        $goods_data_common = array();
        $this->read_csv_sku($file, $barcode_arr, $inv_num);
        $err_num = 0;
        if (count($barcode_arr) > 3000) {
            return $this->format_ret(-1, '', '导入商品最大支持3000条，请修改后重新导入');
        }

        $ret = load_model('crm/ActivityModel')->is_exists($param['activity_code']);

        $barcode_str = "'" . implode("','", $barcode_arr) . "'";
        $error_msg = '';
        if ($param['type'] == "combo") {
            $sql_combo = "select sku,barcode,goods_code from goods_combo_barcode where barcode in({$barcode_str}) group by barcode ";
            $goods_data_combo = $this->db->get_all($sql_combo);
            $message = "不是套餐商品";
        } else if ($param['type'] == "common") {
            $sql_common = "select sku,barcode,goods_code from goods_sku where barcode in({$barcode_str}) group by barcode ";
            $goods_data_common = $this->db->get_all($sql_common);
            $message = "不是普通商品";
        }
        $goods_data_log = array_merge($goods_data_combo, $goods_data_common);
        foreach ($goods_data_log as $val) {
            $goods_data[$val['barcode']] = $val;
        }
        //判断条码是否存在
        foreach ($barcode_arr as $val) {
            if (!isset($goods_data[$val])) {
                $error_msg[$val] = $message;
                $err_num++;
            }
        }

        $sucess_num = 0;
        $activity_goods = array();
        if ($param['type'] == "common") {
            foreach ($goods_data_common as $key => $val) {
                $activity_goods_common[$key]['sync_type'] = 0;
                $activity_goods_common[$key]['sku'] = $val['sku'];
                $activity_goods_common[$key]['goods_code'] = $val['goods_code'];
                $activity_goods_common[$key]['activity_code'] = $param['activity_code'];
                $activity_goods_common[$key]['shop_code'] = $ret['data']['shop_code'];
                $activity_goods_common[$key]['inv_num'] = 0;
                $activity_goods_common[$key]['update_num'] = $inv_num[$val['barcode']];
                $arr_common[] = $val['sku'];
                $sucess_num++;
            }
            $activity_goods = $activity_goods_common;
        } else if ($param['type'] == "combo") {
            foreach ($goods_data_combo as $key => $val) {
                $activity_goods_combo[$key]['sync_type'] = 1;
                $activity_goods_combo[$key]['sku'] = $val['sku'];
                $activity_goods_combo[$key]['goods_code'] = $val['goods_code'];
                $activity_goods_combo[$key]['activity_code'] = $param['activity_code'];
                $activity_goods_combo[$key]['shop_code'] = $ret['data']['shop_code'];
                $activity_goods_combo[$key]['inv_num'] = 0;
                $activity_goods_combo[$key]['update_num'] = $inv_num[$val['barcode']];
                $sucess_num++;
            }
            $activity_goods = $activity_goods_combo;
        }
//        $activity_goods = empty($activity_goods_common) ? array() : $activity_goods_common;
//
//        if (!empty($activity_goods_combo)) {
//            $activity_goods = !empty($activity_goods) ? array_merge($activity_goods, $activity_goods_combo) : $activity_goods_combo;
//        }

        $shop_code = $ret['data']['shop_code'];

        //添加活动商品表
        // $ret = $this->add_activity_goods($activity_goods, $arr_common, $ret['data']['shop_code'], $param['activity_code']);
        $update_str = " update_num = VALUES(update_num)";
        $ret = $this->insert_multi_duplicate('crm_goods', $activity_goods, $update_str);
        $ret = $this->db->query("update crm_goods r1 inner join api_goods r2 on r1.goods_code=r2.goods_code set r1.goods_from_id=r2.goods_from_id where r1.shop_code=r2.shop_code and r1.activity_code='{$param['activity_code']}';");
        $ret = $this->refresh_goods_children_data($param['activity_code']);

        $filter = array('activity_code' => $param['activity_code'], 'shop_code' => $shop_code);

        $ret = $this->inv_fresh($filter);

        $message = '导入成功' . $sucess_num;
        if ($err_num > 0 && !empty($err_num)) {
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

    function create_import_fail_files($fail_top, $msg) {
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($msg as $key => $val) {
            $val_data = array($key, $val);
            $file_str .= implode("\t,", $val_data) . "\r\n";
        }
        $filename = md5("activity_goods_fail" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

    function refresh_goods_children_data($activity_code, $combo_arr = array()) {

        $sql = "    insert IGNORE into crm_goods_children (shop_code ,activity_code,sku,p_sku )
            SELECT c.shop_code ,c.activity_code,d.sku,d.p_sku from crm_goods c
         INNER JOIN goods_combo_diy d ON c.sku=d.p_sku
         where c.sync_type=1 AND c.activity_code=:activity_code ";

        if (empty($combo_arr)) {
            $combo_str = "'" . implode("','", $combo_arr) . "'";
            $sql .= "and  d.p_sku in ({$combo_str}) ";
        }

        $this->db->query($sql, array(':activity_code' => $activity_code));

        ///少刷新activity_list 功能
    }

    //将exsl文件保存到数组中
    function read_csv_sku($file, &$barcode_arr, &$inv_num) {

        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $barcode_arr[] = preg_replace(' ## ', '', $row[0]);
                    $inv_num[$row[0]] = $row[1];
                }
            }
            $i++;
        }
        fclose($file);
        return $i;
    }

    /* 增加活动商品 */

    function add_activity_goods($activity_goods, $arr_common, $shop_code, $activity_code) {
        foreach ($activity_goods as $key => $val) {
            $val_new = array("sync_type" => $val['sync_type'], "sku" => $val['sku'], "activity_code" => $val['activity_code'], "shop_code" => $val['shop_code'], "inv_num" => $val['inv_num']);
            $ret = $this->get_row(array('activity_code' => $val['activity_code'], 'sku' => $val['sku']));
            if (!empty($ret['data'])) {
                $ret = $this->update($val_new, array('activity_code' => $val['activity_code'], 'sku' => $key));
            } else {
                $ret = $this->insert_dup($val_new);
            }
        }
        $filter = array("activity_code" => $activity_code, "shop_code" => $shop_code);
        $ret = $this->inv_fresh($filter);
        foreach ($activity_goods as &$val) {
            $sql = "select inv_num from crm_goods where activity_code= :activity_code and shop_code= :shop_code and sku= :sku";
            $val['inv_num'] = $this->db->get_value($sql, array(':activity_code' => $val['activity_code'], ':shop_code' => $val['shop_code'], ':sku' => $val['sku']));
        }
        $ret = $this->edit_num_action($activity_goods);
        return $ret;
    }

    /* 删除商品 */

    function delete_goods($activity_code, $sku) {
        $sql = "select * from crm_goods where activity_code= :activity_code and sku= :sku";
        $data = $this->db->get_row($sql, array(':activity_code' => $activity_code, ':sku' => $sku));
        $data['update_num'] = 0;
        $this->edit_num_action($data, 'del');

        $sql = "delete from crm_goods where activity_code= :activity_code and sku= :sku";
        $ret = $this->db->query($sql, array(':activity_code' => $activity_code, ':sku' => $sku));
        $sql = "delete from crm_goods_children where activity_code= :activity_code and p_sku= :p_sku";
        $ret = $this->db->query($sql, array(':activity_code' => $activity_code, ':p_sku' => $sku));
        $barcode = $this->get_barcode($sku);
        $log = array('activity_code' => $activity_code, 'user_code' => CTX()->get_session('user_id'), 'user_name' => CTX()->get_session('user_code'), 'action_name' => '删除商品', 'action_time' => date('Y-m-d H:i:s', time()), 'action_desc' => "删除商品{$barcode}");
        $ret = load_model('crm/ActivityLogModel')->insert($log);
        return $ret;
    }

    function check_inv($activity_code) {
        $sql = "select a.inv_num,b.barcode from crm_goods a
                INNER JOIN goods_combo_barcode b on a.sku=b.sku
                where activity_code=:activity_code AND sync_type=1 AND inv_num<0";
        $sql_values = array(':activity_code' => $activity_code);
        $data = $this->db->get_all($sql, $sql_values);
        $str = "";
        foreach ($data as $val) {
            $str .= "套餐商品条码{$val['barcode']}可用库存不足为：{$val['inv_num']}，";
        }
        $sql = "select a.inv_num,b.barcode from crm_goods a
                INNER JOIN goods_sku b on a.sku=b.sku
                where activity_code=:activity_code AND sync_type=0 AND inv_num<0";
        $data = $this->db->get_all($sql, $sql_values);
        foreach ($data as $val) {
            $str .= "商品条码{$val['barcode']}可用库存不足为：{$val['inv_num']}，";
        }
        if (!empty($str)) {
            return $this->format_ret(-5, '', $str);
        }
        return $this->format_ret(1);
    }

    function update_children_is_sync($activity_code) {
        $sql = " update crm_goods_children set is_sync=0 where  activity_code='{$activity_code}' AND 
            sku in(select sku from crm_goods where activity_code='$activity_code' AND sync_type=0)";
        $this->db->query($sql);
    }

    /* 获取普通商品库存 */

    function get_effec_num_common($send_store_code, $goods, $mul = 1) {
        $num_effec = 0;
        /* 通过获取的仓库名去计算库存 */
        $sql_num = "select stock_num,lock_num,out_num from goods_inv where store_code= :store_code and sku= :goods ";
        $num = $this->db->get_row($sql_num, array(':store_code' => $send_store_code, ':goods' => $goods));
        $num_new = $num['stock_num'] - $num['lock_num'] - $num['out_num'];
        //print_r($num_new);
        $num_effec += $num_new;
        $num_effec = intval($num_effec / $mul);
        return $num_effec;
    }

    /* 获取套餐库存 */

    function get_effec_num_combo($activity_code, $shop, $data) {
        /* 获取套餐的子商品 */
        $sql = "select r2.barcode,rl.sku,rl.update_num from crm_goods as rl LEFT JOIN goods_combo_barcode as r2 on rl.sku=r2.sku where rl.activity_code= :activity_code and rl.shop_code= :shop and sync_type=1";
        $combo = $this->db->get_all($sql, array(':activity_code' => $activity_code, ':shop' => $shop));
        $ret['combo'] = $combo;
        $i = 0;
        foreach ($combo as $com) {

            foreach ($data as $val) {
                $arr_name = explode(",", $val['name_list']);
                if (in_array($com['barcode'], $arr_name) == true) {
                    $mini_num = $val['inv_num'];
                    break 1;
                }
            }
            foreach ($data as $val) {
                $arr_name = explode(",", $val['name_list']);
//                   if(in_array($com['barcode'], $arr_name) == true && in_array($arr, $arr_name) == true){
//                       $val['inv_num'] = $val['inv_num'] - $ret['data']["$arr1"];
//                   }
                if (in_array($com['barcode'], $arr_name) == true) {
                    if ($mini_num > $val['inv_num']) {
                        $mini_num = $val['inv_num'];
                    }
                }
            }
            $arr = $com['barcode'];
            $arr1 = $com['sku'];
            $ret['data']["$arr1"] = $mini_num;
            $mini_num = 0;
            if (empty($ret['data']["$arr1"])) {
                $ret['data']["$arr1"] = 0;
            }
            $i++;
        }
        return $ret;
    }

    /* 获取商品条码 */

    function get_barcode($sku, $sync = 0) {
        //desc :活动商品同步比率 未使用
        $sync = $this->db->get_value("select sync_type from crm_goods where sku= :sku", array(':sku' => $sku));
        if ($sync == 0) {
            $ret = $this->db->get_value('select barcode from goods_sku where sku=:sku', array(':sku' => $sku));
        }
        if ($sync == 1) {
            $ret = $this->db->get_value('select barcode from goods_combo_barcode where sku=:sku', array(':sku' => $sku));
        }
        return $ret;
    }

    /* 页面修改，更新库存 */

    function edit_num_action($request, $action_type = 'edit') {
        $activity_code = $request['activity_code'];
        $this->begin_trans();
        $sql = "select * from crm_goods where activity_code=:activity_code AND sku=:sku ";
        $sql_values = array(':activity_code' => $request['activity_code'], ':sku' => $request['sku']);
        $activity_sku_data = $this->db->get_row($sql, $sql_values);
        if (empty($activity_sku_data)) {
            $activity_sku_data = $request;
        }

        $old_update_num = $activity_sku_data['update_num'];
        $combo_sku_arr = array();
        $sku_arr = array();
        if ($activity_sku_data['sync_type'] == 1) {//套餐
            $combo_sku_arr[] = $request['sku'];

            $sql = "update crm_goods set update_num={$request['update_num']}  where activity_code=:activity_code AND sku=:sku AND  sync_type=1 ";
            $sql_values = array(':activity_code' => $request['activity_code'], ':sku' => $request['sku']);
            $this->query($sql, $sql_values);

            $sql = "select sku from crm_goods_children where p_sku=:p_sku";
            $data = $this->db->get_all($sql, array(':p_sku' => $request['sku']));
            foreach ($data as $val) {
                $sku_arr[] = $val['sku'];
            }
            $combo_arr = $this->get_goodos_combo_sku($sku_arr);
            if (!empty($combo_arr)) {
                $combo_sku_arr = array_unique(array_merge($combo_arr, $combo_sku_arr));
            }
        } else {
            $sku_arr[] = $request['sku'];
            $combo_sku_arr = $this->get_goodos_combo_sku($sku_arr);

            $sql = "update crm_goods set update_num={$request['update_num']}  where activity_code=:activity_code AND sku=:sku AND  sync_type=0 ";
            $sql_values = array(':activity_code' => $request['activity_code'], ':sku' => $request['sku']);
            $this->query($sql, $sql_values);
        }

        if (!empty($combo_sku_arr)) {
            $this->inv_fresh_combo($activity_code, $request['shop_code'], $combo_sku_arr);
        }
        if (!empty($sku_arr)) {
            $this->inv_fresh_goods($activity_code, $request['shop_code'], $sku_arr);
        }


        $action_name = '修改上报库存';
        $action_desc = "商品{$request['barcode']}原上报库存为{$old_update_num}修改为{$request['update_num']}";


        if ($action_type <> 'del') {
            $log = array('activity_code' => $activity_code, 'user_code' => CTX()->get_session('user_id'), 'user_name' => CTX()->get_session('user_code'), 'action_name' => $action_name, 'action_time' => date('Y-m-d H:i:s', time()), 'action_desc' => $action_desc);
            $ret = load_model('crm/ActivityLogModel')->insert($log);
        }

        $this->commit();
        return $this->format_ret(1);
    }

    private function get_goodos_combo_sku($sku_arr) {
        $sql_values = [];
        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
        $sql = "SELECT p_sku FROM crm_goods_children WHERE sku IN({$sku_str})";
        $data = $this->db->get_all_col($sql, $sql_values);

        return $data;
    }

    function array_unique_fb($data) {
        foreach ($data as $v) {
            $v = join(',', $v); //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
            $temp[] = $v;
        }
        $temp = array_unique($temp); //去掉重复的字符串,也就是重复的一维数组
        foreach ($temp as $k => $v) {
            $temp[$k] = explode(',', $v); //再将拆开的数组重新组装
        }
        return $temp;
    }

    /* 通过子商品刷新套餐库存 */

    function update_num_refresh($ary_details) {
        $sql = "select r2.barcode,rl.sku,rl.inv_num,rl.lock_num,rl.update_num,rl.sync_type from crm_goods rl inner join goods_combo_barcode r2 on rl.sku=r2.sku where activity_code = :activity_code and shop_code = :shop_code";
        $combo = $this->db->get_all($sql, array(':activity_code' => $ary_details['activity_code'], ':shop_code' => $ary_details['shop_code']));
        $ret['combo'] = $combo;
        $i = 0;
        foreach ($combo as $com) {
//                $combo_list = explode(',', $com['activity_list']);
//                foreach($combo_list as $val){
            $sql = "SELECT inv_num FROM crm_goods_children WHERE find_in_set(:code, activity_list) and activity_code = :activity_code and shop_code = :shop_code";
            $arr = $this->db->get_all($sql, array(':code' => $com['barcode'], ':activity_code' => $ary_details['activity_code'], ':shop_code' => $ary_details['shop_code']));
            if (!empty($arr)) {
                $mini_num = $arr[0]['inv_num'];
            } else {
                $mini_num = $com['inv_num'];
            }
            foreach ($arr as $value) {
                if ($mini_num > $value['inv_num']) {
                    $mini_num = $value['inv_num'];
                }
            }
            if ($com['inv_num'] == $mini_num) {
                $ret = parent::update(array('inv_num' => $mini_num), array('activity_code' => $ary_details['activity_code'], 'sku' => $com['sku']));
            } else {
                $list = $this->get_mini_combo($ary_details['activity_code'], $ary_details['shop_code']);
                if (in_array($com['sku'], $list) || $com['sync_type'] == 1) {
                    $ret = parent::update(array('inv_num' => $mini_num, 'lock_num' => ($com['lock_num'] + $com['inv_num'] - $mini_num)), array('activity_code' => $ary_details['activity_code'], 'sku' => $com['sku']));
                } else {
                    $ret = parent::update(array('inv_num' => $mini_num, 'lock_num' => ($com['lock_num'] + $com['inv_num'] - $mini_num) + $com['update_num']), array('activity_code' => $ary_details['activity_code'], 'sku' => $com['sku']));
                }
            }
            //}
        }
        return $ret;
    }

    /* 获取基础活动信息 */

    function get_data($start_time, $end_time, $sku) {
        $list = $this->get_activity_list($start_time, $end_time, $sku);
        $sql = "select rl.sync_type,rl.lock_num,rl.activity_code,rl.shop_code,rl.sku,rl.inv_num,rl.update_num from crm_goods as rl inner join crm_activity as r2 on rl.activity_code=r2.activity_code where rl.activity_code in ({$list}) and sku= :sku";
        $data_final = $this->db->get_all($sql, array(':sku' => $sku));
        return $data_final;
    }

    /* 获取普通商品作为子商品在套餐中的锁定库存 */

    function get_common_children($start_time, $end_time, $sku) {
        $list = $this->get_activity_list($start_time, $end_time, $sku);
        $code = $this->get_barcode($sku);
        $sql_common = "select sync_type,lock_num,activity_code,shop_code,sku,inv_num,update_num from crm_goods where sku in (select sku from crm_goods_children where find_in_set(:code,activity_list)) and activity_code in ({$list})";
        $data_final_1 = $this->db->get_all($sql_common, array(':code' => $code));
        return $data_final_1;
    }

    /* 获取普通商品作为子商品在套餐中的变化数量 */

    function get_common_num($start_time, $end_time, $sku, $list) {
        $inv_num_sum = 0;
        $data_inv = $this->get_common_children($start_time, $end_time, $sku);
        foreach ($data_inv as &$value) {
            if (in_array($value['sku'], $list))
                $inv_num_sum += $value['update_num'];
        }
        return $inv_num_sum;
    }

    /* 获取相同时间段内套餐商品中的普通商品的锁定库存 */

    function get_children_common_list($start_time, $end_time, $sku) {
        $list = $this->get_activity_list($start_time, $end_time, $sku);
        $sql = "select activity_list from crm_goods_children where activity_code in ({$list}) and sku=:sku";
        $data = $this->db->get_all($sql, array(':sku' => $sku));
        $i = 0;
        foreach ($data as $val) {
            $ret = explode(',', $val['activity_list']);
            foreach ($ret as $value) {
                $bar = $this->db->get_value('select sku from goods_combo_barcode where barcode=:barcode', array(':barcode' => $value));
                $get[$i] = $bar;
                $i++;
            }
        }
        $get = array_unique($get);
        $get = deal_array_with_quote($get);
        if (!empty($get)) {
            $sql_1 = "select sync_type,lock_num,activity_code,shop_code,sku,inv_num,update_num from crm_goods where sku in ({$get}) and activity_code in ({$list})";
            $result = $this->db->get_all($sql_1);
        }
        return $result;
    }

    /* 相同公用最小锁定库存的套餐的锁定库存 */

    function get_repeat_combo($start_time, $end_time, $sku) {
        $result = array();
        $bar = $this->get_barcode($sku, 1);
        $list = $this->get_activity_list($start_time, $end_time, $sku);
        $sql = "select activity_list from crm_goods_children where activity_code in ({$list}) and find_in_set(:code, activity_list)";
        $data = $this->db->get_all($sql, array(':code' => $bar));
        $i = 0;
        foreach ($data as $val) {
            $ret = explode(',', $val['activity_list']);
            foreach ($ret as $value) {
                $bar = $this->db->get_value('select sku from goods_combo_barcode where barcode=:barcode', array(':barcode' => $value));
                $get[$i] = $bar;
                $i++;
            }
        }
        $get = array_unique($get);
        foreach ($get as $key => $value) {
            if ($value == $sku) {
                unset($get[$key]);
            }
        }
        $get = deal_array_with_quote($get);
        if (empty($get)) {
            return $result;
        }
        $sql_result = "select sync_type,lock_num,activity_code,shop_code,sku,inv_num,update_num from crm_goods where sku in ({$get}) and activity_code in ({$list})";
        $result = $this->db->get_all($sql_result);
        return $result;
    }

    function get_activity_list($start_time, $end_time, $sku) {
        $sql_no1 = "select activity_code from crm_activity where start_time<= :start_time and end_time>= :start_time";
        $data_1 = $this->db->get_all($sql_no1, array(':start_time' => $start_time, ':end_time' => $end_time));
        $sql_no2 = "select activity_code from crm_activity where start_time<= :end_time and end_time>= :end_time";
        $data_2 = $this->db->get_all($sql_no2, array(':start_time' => $start_time, ':end_time' => $end_time));
        foreach ($data_1 as &$val) {
            $data_1['data'][] = $val['activity_code'];
        }
        foreach ($data_2 as &$val) {
            $data_2['data'][] = $val['activity_code'];
        }
        $data = array_unique(array_filter(array_merge($data_1['data'], $data_2['data'])));
        $list = deal_array_with_quote($data);
        return $list;
    }

    /* 一键刷新 */

    function inv_fresh($filter) {


        $this->inv_fresh_combo($filter['activity_code'], $filter['shop_code']);
        $this->inv_fresh_goods($filter['activity_code'], $filter['shop_code']);

        return $this->format_ret(1);
    }

    /*
     * 刷新普通占用库存
     */

    function inv_fresh_goods($activity_code, $shop_code, $sku_arr = array()) {
        $where_str = "";
        $sku_str = '';
        if (!empty($sku_arr)) {
            $sku_str = "'" . implode("','", $sku_arr) . "'";
            $where_str = " AND sku IN ({$sku_str})";
        }


        $where = " activity_code = '{$activity_code}' AND sync_type=0  {$where_str} ";
        $this->db->update('crm_goods', array('inv_num' => '0', 'lock_num' => 0), $where);

        $sql = "select send_store_code from base_shop where shop_code=:shop_code";
        $sql_values = array(':shop_code' => $shop_code);
        $store_code = $this->db->get_value($sql, $sql_values);

        if (!empty($sku_arr)) {
            $where_str = " AND crm_goods.sku IN ({$sku_str})";
        }

        $sql = "update crm_goods,goods_inv
                    set crm_goods.inv_num = goods_inv.stock_num,crm_goods.lock_num=goods_inv.lock_num
                    where crm_goods.sku=goods_inv.sku AND goods_inv.store_code=:store_code
                            AND crm_goods.activity_code=:activity_code AND crm_goods.sync_type=0 
        {$where_str}";
        $this->db->query($sql, array(':store_code' => $store_code, ':activity_code' => $activity_code));




        //库存计算得来锁定
        $sql = "update crm_goods
                    set inv_num = inv_num-lock_num,lock_num=0
                    where activity_code=:activity_code AND sync_type=0 
        
        {$where_str}";

        $this->db->query($sql, array(':activity_code' => $activity_code));



        $sql = "update crm_goods_children,crm_goods
                set crm_goods.lock_num = crm_goods_children.lock_num+crm_goods.lock_num
                    where crm_goods_children.sku=crm_goods.sku AND crm_goods.sync_type=0
                    AND crm_goods_children.activity_code=crm_goods.activity_code  AND crm_goods.activity_code=:activity_code  {$where_str}";
        $this->db->query($sql, array(':activity_code' => $activity_code));



        //todo：同个仓库的活动计算 
        //本活动套餐锁定


        if (!empty($sku_arr)) {
            $where_str = " AND sku IN ({$sku_str})";
        }
        //计算可用
        $sql = "update crm_goods set  inv_num = inv_num-lock_num-update_num  where activity_code=:activity_code AND sync_type= 0 {$where_str} ";
        $this->db->query($sql, array(':activity_code' => $activity_code));
    }

    /*
     * 刷新本套餐预占用库存
     */

    function inv_fresh_combo($activity_code, $shop_code, $comm_arr = array()) {
        $where_str = "";
        $comm_str = '';
        if (!empty($comm_arr)) {
            $comm_str = "'" . implode("','", $comm_arr) . "'";
            $where_str = " AND c.p_sku IN ({$comm_str})";
        }

        $sql = "insert into crm_goods_children ( activity_code,shop_code, sku,p_sku,p_sync_num)
            select activity_code,shop_code, c.sku,c.p_sku,a.update_num*c.num as p_sync_num from crm_goods
            a INNER JOIN goods_combo_diy c ON  a.sku=c.p_sku 
            where a.sync_type=1 AND a.activity_code=:activity_code {$where_str}
            ON DUPLICATE KEY UPDATE p_sync_num=VALUES(p_sync_num);";
        $sql_values = array(':activity_code' => $activity_code);
        $this->db->query($sql, $sql_values);

        if (!empty($comm_arr)) {
            $comm_str = "'" . implode("','", $comm_arr) . "'";
            $where_str = " AND p_sku IN ({$comm_str})";
        }

        $where = " activity_code = '{$activity_code}' {$where_str} ";
        $this->db->update('crm_goods_children', array('inv_num' => '0', 'lock_num' => 0), $where);

        $sql = "select send_store_code from base_shop where shop_code=:shop_code";
        $sql_values = array(':shop_code' => $shop_code);
        $store_code = $this->db->get_value($sql, $sql_values);

        if (!empty($comm_arr)) {
            $where_str = " AND crm_goods_children.p_sku IN ({$comm_str})";
        }

        $sql = "update crm_goods_children,goods_inv
                set crm_goods_children.inv_num = goods_inv.stock_num,crm_goods_children.lock_num =goods_inv.lock_num
                where crm_goods_children.sku=goods_inv.sku AND goods_inv.store_code=:store_code 
                AND crm_goods_children.activity_code=:activity_code {$where_str}";
        $this->db->query($sql, array(':store_code' => $store_code, ':activity_code' => $activity_code));

        $sql = "update crm_goods_children set inv_num = inv_num-lock_num,lock_num=0
                where  activity_code=:activity_code {$where_str}";

        $this->db->query($sql, array(':activity_code' => $activity_code));

        $sql = "update crm_goods_children,crm_goods
                set crm_goods_children.lock_num = crm_goods_children.lock_num+crm_goods.lock_num
                where crm_goods_children.sku=crm_goods.sku AND crm_goods.sync_type=0
                AND crm_goods_children.activity_code=crm_goods.activity_code  AND crm_goods.activity_code=:activity_code {$where_str}";
        $this->db->query($sql, array(':activity_code' => $activity_code));

        //本活动套餐锁定
        if (!empty($comm_arr)) {
            $sku_sql = "select sku from crm_goods_children where p_sku IN ({$comm_str})";
            $sku_arr = $this->db->get_col($sku_sql);
            $sku_arr = array_unique($sku_arr);
            $sku_str = "'" . implode("','", $sku_arr) . "'";
            $where_str = " AND c.sku in ({$sku_str})";
        }

        //插入临时表数据，后续处理
        $sql_values = array(':activity_code' => $activity_code);
        $sql = "INSERT INTO crm_goods_children_temp (activity_code,sku,num)
                SELECT * FROM (
                    SELECT c.activity_code,c.sku,SUM(c.p_sync_num) AS num FROM crm_goods_children c 
                    WHERE c.activity_code=:activity_code {$where_str} GROUP BY c.sku
                    ) AS temp
                ON DUPLICATE KEY UPDATE num=VALUES(num)";
        $this->db->query($sql, $sql_values);

        $sql = "UPDATE crm_goods_children c INNER JOIN crm_goods_children_temp t ON c.sku=t.sku AND c.activity_code=t.activity_code
                SET c.lock_num=t.num WHERE c.activity_code=:activity_code AND t.num>0 {$where_str}";
        $this->db->query($sql, $sql_values);

        if (!empty($comm_arr)) {
            $where_str = " AND a.p_sku IN ({$comm_str})";
        }
        //刷新总可用
        $sql = "insert  into crm_goods(activity_code,sku,inv_num, lock_num)
            select a.activity_code,a.p_sku, min((a.inv_num-a.lock_num)/c.num) as inv_num, min(a.lock_num/c.num) as lock_num from   crm_goods_children a
            INNER JOIN goods_combo_diy c 
            ON   a.p_sku=c.p_sku AND  a.sku=c.sku
            where a.activity_code=:activity_code {$where_str}
            GROUP BY activity_code,p_sku 
            ON DUPLICATE KEY UPDATE inv_num=VALUES(inv_num),lock_num=VALUES(lock_num)";
        $this->db->query($sql, $sql_values);
    }

    function get_data_inv($data, $shop) {
        $ret = array();
        foreach ($data as $key => &$val) {
            $sql_inv = "select goods_from_id,sku_id from api_goods_sku where goods_barcode = :goods_barcode and shop_code= :shop_code";
            $data_inv = $this->db->get_all($sql_inv, array(':goods_barcode' => $val['barcode'], ':shop_code' => $shop));
            if (empty($data_inv)) {
                continue;
            } else {
                $ret[$key]['barcode'] = $val['barcode'];
                $ret[$key]['goods_from_id'] = $data_inv[0]['goods_from_id'];
                $ret[$key]['sku_id'] = $data_inv[0]['sku_id'];
                $ret[$key]['shop_code'] = $shop;
                $ret[$key]['num'] = $val['num'];
            }
        }
        return $ret;
    }

    /* 库存同步获取数据 */

    function create_sync_inv_task($code, $shop) {

        require_model('common/TaskModel');
        $task = new TaskModel();
        $task_data = array();

        $task_data['code'] = "activity_sync_inv_" . $code . "_" . $shop;
        $request['app_act'] = 'crm/activity/sync_inv';


        $request['code'] = $code;
        $request['shop'] = $shop;
        $request['app_fmt'] = 'json';
        $task_data['start_time'] = time();
        $task_data['request'] = $request;
        $ret = $task->save_task($task_data);
        if ($ret['status'] < 0) {
            if ($ret['status'] == -5) {
                $ret['message'] = "已经正在库存同步...";
            }
        }
        $ret['message'] = "库存同步已开始执行，请等待一会去查询平台库存是否同步成功！";
        return $ret;
    }

    function sync_inv($code, $shop, $sync_type = 1, $num = 0) {



        $sql = "select send_store_code,sale_channel_code from base_shop where shop_code=:shop_code";
        $sql_values = array(':shop_code' => $shop);
        $shop_info = $this->db->get_row($sql, $sql_values);
        $store_code = $shop_info['send_store_code'];

        if ($sync_type == 0) {
            $get_sql = "select s.barcode,a.update_num AS num,a.sku from crm_goods a
                INNER JOIN goods_sku s on a.sku=s.sku
             where activity_code= :activity_code and shop_code= :shop_code AND sync_type=0 ";
        } else if ($sync_type == 1) {
            $get_sql = "select s.barcode,a.update_num AS num,a.sku from crm_goods a
                INNER JOIN goods_combo_barcode s on a.sku=s.sku
             where activity_code= :activity_code and shop_code= :shop_code AND sync_type=1 ";
        } else if ($sync_type == 2) {


            $sql = "update crm_goods_children,goods_inv
                    set crm_goods_children.inv_num =  if( goods_inv.stock_num>goods_inv.lock_num,goods_inv.stock_num-goods_inv.lock_num,0)
                    where crm_goods_children.sku=goods_inv.sku AND goods_inv.store_code=:store_code ";

            $this->db->query($sql, array(':store_code' => $store_code));

            $get_sql = "select b.barcode,c.inv_num-c.lock_num as num,c.sku from crm_goods_children c
                    INNER JOIN goods_sku b on c.sku=b.sku
                     where  activity_code= :activity_code and shop_code= :shop_code AND  c.is_sync=1   ";
        }
        $start = $num * 1000;
        $get_sql .= " limit {$start},1000";
        $data = $this->db->get_all($get_sql, array(':activity_code' => $code, ':shop_code' => $shop));
        $barcode_num_arr = array();
        $barcode_arr = array();

        if (empty($data)) {
            return $this->format_ret(1, array());
        }

        $sku_num_arr = array();
        $sku_arr = array();
        foreach ($data as $key => &$val) {
            $barcode_num_arr[$val['barcode']]['num'] = $val['num'];
            $sku_num_arr[$val['sku']]['num'] = $val['num'];
            $barcode_arr[] = $val['barcode'];
            $sku_arr[] = $val['sku'];
        }

        if ($sync_type != 1 && !empty($sku_arr)) {
            $sku_str = "'" . implode("','", $sku_arr) . "'";
            // print_r($sku_arr);die;
            $sql = "select barcode,sku from goods_barcode_child where sku in ({$sku_str})";
            // echo $sql;die;
            $child_arr = $this->db->get_all($sql);
            foreach ($child_arr as $child_row) {
                $child_num = $sku_num_arr[$child_row['sku']]['num'];
                $barcode_num_arr[$child_row['barcode']]['num'] = $child_num;
                $barcode_arr[] = $child_row['barcode'];
            }
        }
        $barcoee_str = "'" . implode("','", $barcode_arr) . "'";

        $sql = "select shop_code,goods_from_id,source, sku_id,goods_barcode from api_goods_sku where goods_barcode in ({$barcoee_str}) AND shop_code=:shop_code";

        $data = $this->db->get_all($sql, array(':shop_code' => $shop));
        $new_barcode = array();
        foreach ($data as $v) {
            $barcode_v = $barcode_num_arr[$v['goods_barcode']];
            if (!empty($barcode_v)) {
                $new_barcode[] = array_merge($barcode_v, $v);
            }
        }


        if ($sync_type == 1 && $num == 0) {
            $ret = parent::update_exp('crm_activity', array('update_inv_time' => date('Y-m-d H:i:s', time())), array('activity_code' => $code));
        }
        return $this->format_ret(1, $new_barcode);
    }

    /* 获取锁定库存 */

    function inv_lock_detail($filter) {
        $sql = "select sync_type from crm_goods where sku=:sku";
        $sync = $this->db->get_value($sql, array(':sku' => $filter['sku']));
        /* 获取相同时间段内相同sku商品锁定库存 */
        $data = $this->get_data($filter['start_time'], $filter['end_time'], $filter['sku']);
        /* 获取相同时间段内普通商品包含在套餐下的锁定库存 */
        $data_1 = $this->get_common_children($filter['start_time'], $filter['end_time'], $filter['sku']);
        /* 获取相同时间段内套餐商品中的普通商品的锁定库存 */
        $data_2 = $this->get_children_common_list($filter['start_time'], $filter['end_time'], $filter['sku']);
        /* 相同公用最小锁定库存的套餐的锁定库存 */
        $data_3 = $this->get_repeat_combo($filter['start_time'], $filter['end_time'], $filter['sku']);
        if (empty($data)) {
            $data = array();
        }
        if (empty($data_1)) {
            $data_1 = array();
        }
        if (empty($data_2)) {
            $data_2 = array();
        }
        if (empty($data_3)) {
            $data_3 = array();
        }
        $result = $this->get_page_from_sql($filter, 'from crm_goods', $sql_values, '1');
        if ($sync == 1) {
            $flag = array_merge($data, $data_1, $data_3);
        } else {
            $flag = array_merge($data, $data_1, $data_2);
        }
        $list_mini = $this->get_mini_combo($filter['activity_code'], $filter['shop_code']);
        if (in_array($filter['sku'], $list_mini) || $sync == 1) {
            foreach ($flag as $key => $val) {
                if ($val['update_num'] == 0) {
                    unset($flag[$key]);
                }
                $list = $this->get_mini_combo($val['activity_code'], $val['shop_code']);
                if (in_array($val['sku'], $list) == false && $val['sync_type'] == 0) {
                    unset($flag[$key]);
                }
            }
        } else {
            foreach ($flag as $key => $val) {
                if ($val['update_num'] == 0) {
                    unset($flag[$key]);
                }
            }
        }

        foreach ($flag as &$value) {
            if ($value['sync_type'] == 1) {
                $value['barcode'] = $this->get_barcode($value['sku'], 1);
                $value['activity_name'] = $this->db->get_value("select activity_name from crm_activity where activity_code=:activity_code", array(':activity_code' => $value['activity_code']));
            } else {
                $value['barcode'] = $this->get_barcode($value['sku']);
                $value['activity_name'] = $this->db->get_value("select activity_name from crm_activity where activity_code=:activity_code", array(':activity_code' => $value['activity_code']));
            }
        }
        $result['data'] = $flag;

        $ret_status = OP_SUCCESS;
        $ret_data = $result;

        return $this->format_ret($ret_status, $ret_data);
    }

    /* 获取活动中套餐最小值的sku列表 */

    function get_mini_combo($activity_code, $shop_code) {
        $sql = "select inv_num from crm_goods where activity_code=:activity_code and shop_code=:shop_code and sync_type= 1";
        $data = $this->db->get_all($sql, array(':activity_code' => $activity_code, ':shop_code' => $shop_code));
        $i = 0;
        foreach ($data as $val) {
            $sql_sku = "select sku from crm_goods_children where inv_num=:inv_num and activity_code=:activity_code and shop_code=:shop_code";
            $data_sku = $this->db->get_all($sql_sku, array(':inv_num' => $val['inv_num'], ':activity_code' => $activity_code, ':shop_code' => $shop_code));
            foreach ($data_sku as $value) {
                $sku_list[$i] = $value['sku'];
                $i++;
            }
        }
        return $sku_list;
    }

    function get_activery_sell_data($activity_code, $type) {
        $sql = "select * from crm_activity where activity_code=:activity_code";
        $data = $this->db->get_row($sql, array(':activity_code' => $activity_code));


        $sql = "SELECT sum(d.num) as sell_num,g.activity_code,g.sku from api_order a
            INNER JOIN api_order_detail d ON a.tid=d.tid
            INNER JOIN {$type}  b ON b.barcode=d.goods_barcode
            INNER JOIN crm_goods g ON g.sku=b.sku
            where 
            a.`status`=1 AND  a.is_change=1 AND a.sell_record_code<>'' AND
            a.shop_code=:shop_code AND a.order_first_insert_time>=:start_time
            AND  g.activity_code=:activity_code
            AND   a.order_first_insert_time<=:end_time
            GROUP BY g.sku";
        $sql_values = array(':shop_code' => $data['shop_code'], ':activity_code' => $activity_code, ':start_time' => $data['start_time'], ':end_time' => $data['end_time']);
        $sell_data = $this->db->get_all($sql, $sql_values);
        if (!empty($sell_data)) {
            $update_str = " sell_num = VALUES(sell_num) ";
            $this->insert_multi_duplicate('crm_goods', $sell_data, $update_str);
        }
        return $this->format_ret(1);
    }

    function get_pt_goods($shop_code, $params) {
        $sql_values = array(':barcode' => $params['barcode'], ':shop_code' => $shop_code);
        $sql = "SELECT gs.source,gs.shop_code,gs.goods_from_id,g.goods_name,g.status,gs.sku_id,gs.goods_barcode,gs.sku_properties_name,gs.sale_mode FROM api_goods AS g 
                INNER JOIN api_goods_sku AS gs ON g.goods_from_id=gs.goods_from_id
                WHERE gs.goods_barcode=:barcode AND gs.shop_code=:shop_code";
        $data = $this->db->get_all($sql, $sql_values);
        foreach ($data as &$val) {
            $val['goods_status'] = $val['status'] == 1 ? '在售' : '在库';
        }
        return $data;
    }

    function check_inv_is_null($code) {
        $sql = "select sum(update_num) as update_total from crm_goods where activity_code=:code";
        $check = $this->db->get_row($sql, array(':code' => $code));
        if ($check['update_total'] == 0) {
            return $this->format_ret(-4, '', '活动上报库存为0，');
        }
    }

}
