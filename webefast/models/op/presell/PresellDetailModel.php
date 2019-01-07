<?php

require_model('tb/TbModel');

/**
 * 预售业务
 * @author WMH
 */
class PresellDetailModel extends TbModel {

    function __construct() {
        parent::__construct('op_presell_plan_detail');
    }

    /**
     * 获取预售明细
     * @param array $filter 筛选条件
     * @return array 结果集
     */
    public function get_plan_detail_by_page($filter) {
        $sql_join = ' INNER JOIN `goods_sku` AS gs ON pd.`sku`=gs.`sku`
                    INNER JOIN `base_goods` AS bg ON gs.`goods_code`=bg.`goods_code`';
        $sql_main = "FROM `{$this->table}` AS pd {$sql_join} WHERE 1";
        $sql_values = array();
        $select = 'pd.`id`,pd.`sku`,pd.`presell_num`,pd.`sell_num`,pd.`plan_send_time`,bg.`goods_code`,bg.`goods_name`,gs.`barcode`,gs.`spec1_name`,gs.`spec2_name`';
        //计划编码
        if (isset($filter['plan_code']) && $filter['plan_code'] != '') {
            $sql_main .= ' AND pd.`plan_code`=:plan_code ';
            $sql_values[':plan_code'] = $filter['plan_code'];
        } else {
            return array();
        }

        //商品编码/条形码
        if (isset($filter['goods_search']) && $filter['goods_search'] != '') {
            $sql_main .= ' AND (gs.`barcode` LIKE :goods_search OR gs.`goods_code` LIKE :goods_search )';
            $sql_values[':goods_search'] = "%{$filter['goods_search']}%";
        }

        $sql_main .= ' ORDER BY pd.`plan_send_time` ASC';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        if (!empty($data['data'])) {
            $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('goods_spec1', 'goods_spec2'));
            $spec1_rename = isset($ret_arr['goods_spec1']) ? $ret_arr['goods_spec1'] : '';
            $spec2_rename = isset($ret_arr['goods_spec2']) ? $ret_arr['goods_spec2'] : '';

            $plan_info = load_model('op/presell/PresellModel')->exists_plan($filter['plan_code']);
        }

        $curr_time = time();
        foreach ($data['data'] as &$row) {
            $row['plan_code'] = $plan_info['plan_code'];
            $row['plan_send_time'] = empty($row['plan_send_time']) ? '请设置' : date('Y-m-d H:i:s', $row['plan_send_time']);
            $row['spec'] = "{$spec1_rename}：{$row['spec1_name']}；{$spec2_rename}：{$row['spec2_name']}";

            $row['is_allow_delete'] = $plan_info['start_time'] > $curr_time && $plan_info['sync_num'] < 1 ? 1 : 0;
        }

        $ret_data = $data;

        return $this->format_ret(1, $ret_data);
    }

    /**
     * 根据预售编码获取预售明细
     * @param string $plan_code 预售编码
     * @param string $fld 查询字段
     * @return array 数据集
     */
    public function get_detail_by_code($plan_code, $fld = '*') {
        $sql = "SELECT {$fld} FROM {$this->table} WHERE plan_code=:plan_code";
        return $this->db->get_all($sql, array(':plan_code' => $plan_code));
    }

    /**
     * 获取预售商品计划发货时间
     * @param string $shop_code 店铺代码
     * @param int $order_time 平台下单时间
     * @param array $skuid_arr skuid集合
     * @return array 数据集
     */
    public function get_presell_plan_send_time($shop_code, $record_time, $skuid_arr) {
        $plan_code_arr = load_model('op/presell/PresellModel')->get_plan_by_shop($shop_code);
        if (empty($plan_code_arr)) {
            return $this->format_ret(-1, array(), '店铺未关联预售计划或预售计划未开始');
        }
        $record_time = strtotime($record_time);
        foreach ($plan_code_arr as $k => $val) {
            if ($record_time < $val['start_time'] || $record_time > $val['end_time']) {
                unset($plan_code_arr[$k]);
            }
        }
        if (empty($plan_code_arr)) {
            return $this->format_ret(-1, array(), '订单不在预售计划时间内');
        }
        $plan_code_arr = array_unique(array_column($plan_code_arr, 'plan_code'));
        $sql_values = array(':shop_code' => $shop_code);
        $plan_code_str = $this->arr_to_in_sql_value($plan_code_arr, 'plan_code', $sql_values);
        $skuid_str = $this->arr_to_in_sql_value($skuid_arr, 'sku', $sql_values);
        $sql = "SELECT pd.plan_code,pd.sku,pd.plan_send_time,ag.sku_id FROM {$this->table} AS pd 
                INNER JOIN goods_sku AS gs ON pd.sku=gs.sku
                INNER JOIN api_goods_sku AS ag ON ag.goods_barcode=gs.barcode
                WHERE pd.plan_code IN({$plan_code_str}) AND ag.sku_id IN({$skuid_str}) AND ag.shop_code=:shop_code AND ag.sale_mode='presale' AND ag.pre_sync_status <> '-1'";
        $data = $this->db->get_all($sql, $sql_values);
        $data = load_model('util/ViewUtilModel')->get_map_arr($data, 'sku,sku_id');
        return $this->format_ret(1, $data);
    }

    /**
     * 添加预售计划明细
     * @param array $data 明细数据
     * @return type
     */
    public function add_presell_detail($plan_code, $data) {
        if (empty($plan_code) || empty($data)) {
            return $this->format_ret(-1, '', '数据错误，请刷新页面重试');
        }

        $plan_info = load_model('op/presell/PresellModel')->exists_plan($plan_code);
        if (empty($plan_info)) {
            return $this->format_ret(-1, '', '预售计划不存在或已删除');
        }
        if ($plan_info['exit_status'] != 0) {
            return $this->format_ret(-1, '', '预售计划已终止');
        }
        $sku_arr = array_column($data, 'sku');
        $sku_repeat = $this->check_detail_repeat($plan_info, $sku_arr);

        $this->begin_trans();
        //新增预售明细
        $log_data = array();
        $barcode_repeat = array();
        $num_error = array();
        $time_error1 = array();
        $time_error2 = array();
        foreach ($data as $key => &$value) {
            $barcode = $value['barcode'];
            if (in_array($value['sku'], $sku_repeat)) {
                $barcode_repeat[] = $barcode;
                unset($data[$key]);
                continue;
            }

            $presell_num = $value['presell_num'] + 0;
            if (!(is_int($presell_num) && $presell_num > 0)) {
                $num_error[] = $barcode;
                unset($data[$key]);
                continue;
            }

            if (!empty($value['plan_send_time'])) {
                $plan_send_time = strtotime($value['plan_send_time']);
                if ($plan_send_time === FALSE) {
                    $time_error1[] = $barcode;
                    unset($data[$key]);
                    continue;
                }
                if ($plan_send_time < $plan_info['end_time']) {
                    $time_error2[] = $barcode;
                    unset($data[$key]);
                    continue;
                }
                $value['plan_send_time'] = $plan_send_time;
            } else {
                unset($data[$key]['plan_send_time']);
            }

            $value['plan_code'] = $plan_code;
            $log_data[] = "{$barcode}数量{$presell_num}";
        }

        if (!empty($barcode_repeat)) {
            $barcode_repeat = implode('，', $barcode_repeat);
            $log_data[] = "{$barcode_repeat}在其他预售时间重叠的预售计划中已存在";
        }
        if (!empty($num_error)) {
            $num_error = implode('，', $num_error);
            $log_data[] = "{$num_error}预售数量必须为正整数";
        }
        if (!empty($time_error1)) {
            $time_error1 = implode('，', $time_error1);
            $log_data[] = "{$time_error1}计划发货时间格式错误";
        }
        if (!empty($time_error2)) {
            $time_error2 = implode('，', $time_error2);
            $log_data[] = "{$time_error2}计划发货时间不能小于预售结束时间";
        }

        $status = 2;
        if (!empty($data)) {
            $ret = $this->insert_multi_duplicate($this->table, $data, 'presell_num=VALUES(presell_num),plan_send_time=VALUES(plan_send_time)');
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '新增失败');
            }

            //更新关联的平台预售商品
            $barcode_arr = array_column($data, 'barcode');
            $ret = load_model('op/presell/PresellDealPtGoodsModel')->add_presell_pt_goods($plan_code, $barcode_arr);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $ret;
            }
            $status = 1;
        }

        //添加预售日志
        $log_data = implode('；', $log_data);
        load_model('op/presell/PresellLogModel')->insert_log($plan_code, '新增明细', '商品条码：' . $log_data);

        $this->commit();
        return $this->format_ret($status, '', $status == 1 ? '新增成功' : '新增失败');
    }

    /**
     * 编辑预售明细
     * @param array $data 数据
     * @return array 编辑结果
     */
    public function edit_presell_detail($data) {
        if (empty($data['id'])) {
            return $this->format_ret(-1, '', '数据有误，请刷新页面重试');
        }
        $id = $data['id'];
        unset($data['id']);
        $ret_exists = $this->exists_detail($id);
        if (empty($ret_exists)) {
            return $this->format_ret(-1, '', '明细不存在');
        }
        //查看主单
        $plan_info = load_model('op/presell/PresellLogModel')->exists_plan($ret_exists['plan_code']);
        if ($plan_info['exit_status'] != 0) {
            return $this->format_ret('-1', '', '该预售计划已终止！');
        }

        $log_msg = '';
        if ($data['presell_num'] != '') {
            if (!(is_int($data['presell_num'] / 1) && $data['presell_num'] > 0)) {
                return $this->format_ret(-1, '', '数量必须为正整数');
            }
            if ($ret_exists['presell_num'] == $data['presell_num']) {
                return $this->format_ret(2, '', '数据未变更');
            }
            if ($ret_exists['presell_num'] != $data['presell_num']) {
                $log_msg .= "预售数量由{$ret_exists['presell_num']}改为{$data['presell_num']}；";
            }
        } else {
            unset($data['presell_num']);
        }

        if ($data['plan_send_time'] != '') {
            $plan_send_time = strtotime($data['plan_send_time']);
            if ($plan_send_time === FALSE) {
                return $this->format_ret(-1, '', '时间格式不正确');
            }
            if ($ret_exists['plan_send_time'] == $plan_send_time) {
                return $this->format_ret(2, '', '数据未变更');
            }
            $data['plan_send_time'] = $plan_send_time;

            if ($ret_exists['plan_send_time'] != $data['plan_send_time']) {
                $old_time = empty($ret_exists['plan_send_time']) ? '未设置' : date('Y-m-d H:i:s', $ret_exists['plan_send_time']);
                $new_time = date('Y-m-d H:i:s', $data['plan_send_time']);
                $log_msg .= "计划发货时间由{$old_time}改为{$new_time}；";
            }
        } else {
            unset($data['plan_send_time']);
        }

        $ret = $this->update($data, array('id' => $id));
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '更新失败');
        }

        load_model('op/presell/PresellLogModel')->insert_log($ret_exists['plan_code'], '更新明细', '商品条码：' . $data['barcode'] . '。' . $log_msg);
        return $this->format_ret(1, '', '更新成功');
    }

    /**
     * 一键编辑
     * @param array $params 参数
     * @return array 结果
     */
    public function one_key_edit($params) {
        if (empty($params['plan_code'])) {
            return $this->format_ret(-1, '', '数据有误，请刷新页面重试');
        }
        $ret = load_model('op/presell/PresellModel')->exists_plan($params['plan_code']);
        if (empty($ret)) {
            return $this->format_ret(-1, '', '预售计划不存在或已删除');
        }
        if ($ret['exit_status'] != 0) {
            return $this->format_ret(-1, '', '该预售计划已终止！');
        }
        $detail_num = $this->exists_detail_by_code($params['plan_code']);
        if ($detail_num < 1) {
            return $this->format_ret(-1, '', '预售明细为空，不能操作');
        }
        $value = $params['value'];
        if ($params['field'] == 'presell_num') {
            if (!(is_int($value / 1) && $value > 0)) {
                return $this->format_ret(-1, '', '数量必须为正整数');
            }
        } else if ($params['field'] == 'plan_send_time') {
            $value = strtotime($value);
            if ($value === FALSE) {
                return $this->format_ret(-1, '', '时间格式不正确');
            }
        }
        $data = array($params['field'] => $value);
        $ret = $this->update($data, array('plan_code' => $params['plan_code']));
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '更新失败');
        }

        $fld_key = array('presell_num' => '预售数量', 'plan_send_time' => '计划发货时间');
        load_model('op/presell/PresellLogModel')->insert_log($params['plan_code'], '一键更新', "更新所有商品{$fld_key[$params['field']]}为{$params['value']}");
        return $this->format_ret(1, '', '更新成功');
    }

    /**
     * 判断预售计划是否存在明细
     */
    public function exists_detail_by_code($plan_code) {
        $sql = "SELECT COUNT(1) as detail_num FROM {$this->table} WHERE plan_code=:plan_code";
        return $this->db->get_value($sql, array(':plan_code' => $plan_code));
    }

    /**
     * 根据明细ID判断明细是否存在
     */
    public function exists_detail($id) {
        $sql = "SELECT plan_code,presell_num,plan_send_time FROM {$this->table} WHERE id=:id";
        return $this->db->get_row($sql, array(':id' => $id));
    }

    /**
     * 删除预售商品明细
     * @param array $params 参数
     * @return array 删除结果
     */
    public function delete_presell_detail($params) {
        if (empty($params['id'])) {
            return $this->format_ret(-1, '', '数据有误，请刷新页面重试');
        }
        $data = $this->exists_detail($params['id']);
        if (empty($data)) {
            return $this->format_ret(-1, '', '该商品明细不存在');
        }
        //判断主单
        $plan_info = load_model('op/presell/PresellLogModel')->exists_plan($data['plan_code']);
        if ($plan_info['exit_status'] != 0) {
            return $this->format_ret(-1, '', '该预售计划已终止！');
        }

        $this->begin_trans();
        //删除关联平台商品数据
        $ret = parent::delete_exp('op_presell_plan_pt_goods', array('pid' => $params['id']));
        if ($ret == FALSE) {
            $this->rollback();
            return $ret;
        }
        //删除明细数据
        $ret = parent::delete(array('id' => $params['id']));
        if ($ret['status'] < 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '删除失败');
        }

        load_model('op/presell/PresellLogModel')->insert_log($data['plan_code'], '删除明细', '商品条码：' . $params['barcode']);
        $this->commit();
        return $this->format_ret(1, '', '删除成功');
    }

    /**
     * 导入预售明细
     * @param string $plan_code 预售编码
     * @param string $file 导入文件地址
     * @return array 导入结果
     */
    public function imoprt_detail($plan_code, $file) {
        if (empty($plan_code)) {
            return $this->format_ret(-1, '', '主单数据有误，请刷新数据重试');
        }

        $plan_info = load_model('op/presell/PresellModel')->exists_plan($plan_code);
        if (empty($plan_info)) {
            return $this->format_ret(-1, '', '预售计划不存在或已删除');
        }
        if($plan_info['exit_status']!=0){
            return $this->format_ret(-1, '', '预售计划已终止');
        }
        $plan_end_time = $plan_info['end_time'];

        require_lib('csv_util');
        $exec = new execl_csv();
        $fld = array('barcode', 'presell_num', 'plan_send_time');
        $detail = $exec->read_csv($file, 1, $fld, $fld);

        $barcode_arr = array_unique(array_column($detail, 'barcode'));
        $sku_data = load_model('prm/SkuModel')->convert_barcode($barcode_arr);
        $sku_data = $sku_data['data'];

        $sku_arr = array_column($sku_data, 'sku');
        $sku_repeat = $this->check_detail_repeat($plan_info, $sku_arr);

        $err_msg = array();
        $ins_data = array();
        foreach ($detail as $val) {
            $barcode = strtolower($val['barcode']);
            $plan_send_time = strtotime($val['plan_send_time']);
            $presell_num = $val['presell_num'] / 1;
            $error = array();
            if (!isset($sku_data[$barcode])) {
                $error[] = '条码不存在';
            }
            if (in_array($sku_data[$barcode]['sku'], $sku_repeat)) {
                $error[] = '条码在其他预售时间重叠的预售计划中已存在';
            }
            if (!(is_int($presell_num) && $presell_num > 0)) {
                $error[] = '预售数量必须为正整数';
            }
            if ($plan_send_time === FALSE) {
                $error[] = '时间格式不正确';
            }
            if ($plan_send_time < $plan_end_time) {
                $error[] = '计划发货时间不能早于预售结束时间';
            }
            if (!empty($error)) {
                $val['error'] = implode('；', $error);
                $err_msg[] = $val;
                continue;
            }
            $ins_data[] = array(
                'plan_code' => $plan_code,
                'sku' => $sku_data[$barcode]['sku'],
                'presell_num' => $presell_num,
                'plan_send_time' => $plan_send_time,
            );
        }
        $status = 1;
        if (!empty($ins_data)) {
            $this->begin_trans();
            $ret = $this->insert_multi_duplicate($this->table, $ins_data, 'presell_num=VALUES(presell_num),plan_send_time=VALUES(plan_send_time)');
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '导入失败');
            }

            $barcode_arr = array_column($sku_data, 'barcode');
            $ret = load_model('op/presell/PresellDealPtGoodsModel')->add_presell_pt_goods($plan_code, $barcode_arr);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $ret;
            }

            load_model('op/presell/PresellLogModel')->insert_log($plan_code, '导入明细');

            $this->commit();
        } else {
            $status = -1;
        }

        $err_num = count($err_msg);
        $success_num = count($ins_data);

        $message = '导入成功：' . $success_num;
        if ($err_num > 0) {
            $message .= '；' . '失败数量：' . $err_num;
            $fail_top = array('商品条形码', '数量', '计划发货日期', '错误信息');
            $file_name = $exec->create_fail_csv_files($fail_top, $err_msg, 'presell_detail_error');
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }

        return $this->format_ret($status, '', $message);
    }

    /**
     * 获取计划重叠时间内已经存在的商品
     * @param array $plan_info 计划数据
     * @param array $sku_arr sku集合
     * @return array 数据集
     */
    function check_detail_repeat($plan_info, $sku_arr) {
        $plan_shop = load_model('op/presell/PresellModel')->get_presell_shop($plan_info['plan_code']);
        $sql_values = array(':start_time' => $plan_info['start_time'], ':end_time' => $plan_info['end_time'], ':plan_code' => $plan_info['plan_code']);
        $shop_str = $this->arr_to_in_sql_value($plan_shop, 'shop_code', $sql_values);
        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);

        $sql = "SELECT pd.sku FROM op_presell_plan AS pp 
               INNER JOIN op_presell_plan_shop AS ps ON pp.plan_code=ps.plan_code
               INNER JOIN {$this->table} AS pd ON pp.plan_code=pd.plan_code
               WHERE pp.plan_code<>:plan_code AND ps.shop_code IN($shop_str) AND pd.sku IN({$sku_str}) AND ((pp.start_time>=:start_time AND pp.start_time<=:end_time) OR (pp.end_time>=:start_time AND pp.end_time<=:end_time) OR (pp.start_time<=:start_time AND pp.end_time>=:end_time)) AND pp.exit_status=0 ";

        return $this->db->get_col($sql, $sql_values);
    }

}
