<?php

require_model('tb/TbModel');

/**
 * 成本月结单详情业务
 */
class CostMonthDetailModel extends TbModel {

    private $cost_yj_mx_tbls; //月结单明细表

    function __construct() {
        parent::__construct();
        //获取所有库存月结单明细表
        $this->cost_yj_mx_tbls = $this->db->get_all_col("show TABLES like 'cost_yj_mx_%'");
        rsort($this->cost_yj_mx_tbls); //倒序排列
    }

    /**
     * @todo 根据条件查询数据
     */
    function get_by_page($filter) {
        if (isset($filter['ymonth']) && !empty($filter['ymonth'])) {
            $detail_tbl = $this->get_cost_detail_tbl($filter['ymonth']);
            if ($detail_tbl['status'] != 1) {
                return $this->format_ret(OP_SUCCESS, array());
            }
        }

        $sql_main = "FROM {$detail_tbl['data']} r1 WHERE  1=1 ";
        $sql_values = array();
        //月结单号
        if (isset($filter['record_code']) && !empty($filter['record_code'])) {
            $sql_main .= " AND r1.record_code = :record_code";
            $sql_values[':record_code'] = $filter['record_code'];
        } else {
            return;
        }
        //商品编码
        if (isset($filter['goods_code']) && !empty($filter['goods_code'])) {
            $sql_main .= " AND r1.goods_code = :goods_code";
            $sql_values[':goods_code'] = $filter['goods_code'];
        }
        //是否调价
        if (isset($filter['adjust_status']) && $filter['adjust_status'] != '') {
            if ($filter['adjust_status'] == 1) {
                $sql_main .= " AND r1.adjust_cost>0";
            } else if ($filter['adjust_status'] == 0) {
                $sql_main .= " AND r1.adjust_cost<=0";
            }
        }

        //$select = 'r1.goods_code,r1.begin_num,r1.begin_cost,r1.purchase,r1.pur_money,r1.end_num,r1.end_cost,r1.end_adjust_cost';
        $select = 'r1.*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as $key => &$value) {
            $value['goods_name'] = get_goods_name_by_code($value['goods_code']);
            $value['begin_amount'] = $value['begin_cost'] * $value['begin_num'];
            $value['end_amount'] = $value['end_adjust_cost'] * $value['end_num'];
            if (isset($filter['ctl_type']) && $filter['ctl_type'] == 'export') {
                $store_name = load_model('base/StoreModel')->get_store_by_code_arr($value['store_code']);
                $value['store_name'] = implode('，', $store_name);
            }
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * @todo 根据月结单号查询
     */
    function get_by_record_code($record_code, $ymonth) {
        $detail_tbl = $this->get_cost_detail_tbl($ymonth);
        $sql = "select * from {$detail_tbl['data']} where record_code='{$record_code}'";
        $data = $this->db->get_all($sql);
        return $data;
    }

    /**
     * @todo 根据月结月份和仓库查询
     */
    function get_by_store_code($store_code, $ymonth) {
        $detail_tbl = $this->get_cost_detail_tbl($ymonth);
        $sql = "SELECT * FROM {$detail_tbl['data']} WHERE store_code='{$store_code}'";
        $data = $this->db->get_row($sql);
        return $data;
    }

    /**
     * @todo 根据明细id获取子明细
     */
    function get_by_mx_id($id, $ymonth) {
        $detail_tbl = $this->get_cost_detail_tbl($ymonth);
        $sql = "select begin_num,purchase,pur_return,oms_sell_record,oms_sell_return,wbm_store_out,wbm_return,adjust,shift_in,shift_out,end_num from {$detail_tbl['data']} where cost_yj_mx_id='{$id}'";
        $data = $this->db->get_all($sql);
        return $data;
    }

    /**
     * @todo 根据明细的record_code判断主表单据是否存在
     * @param string $value 值
     * @param string $field_name 字段名
     */
    private function is_exists($value, $field_name = 'record_code') {
        $m = load_model('acc/CostMonthModel');
        $ret = $m->get_row(array($field_name => $value));
        return $ret;
    }

    /**
     * 验证明细参数是否正确
     * @param array $data 明细单据
     * @param boolean $is_edit 是否为编辑
     * @return int
     */
    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['pid']) || !valid_input($data['pid'], 'required')))
            return RECORD_ERROR_CODE;
        return 1;
    }

    /**
     * @todo 创建成本月结单明细单据
     * @param array $param
     */
    public function create_cost_month_detail($param) {
        $ymonth = $param['ymonth'];
        $prev_month = load_model('acc/InvMonthModel')->get_prev_month($ymonth);
        //生成库存月结单明细
        $ret_inv = load_model('acc/InvMonthModel')->first_create_inv_month($ymonth, $param['store_code']);
        if ($ret_inv['status'] != 1) {
            return $this->format_ret(-1, '', "生成 {$ymonth} 库存月结明细失败");
        }
        $inv_month_detail_tbl = $ret_inv['data']; //库存月结单明细表名
        $store_code = deal_strs_with_quote($param['store_code']);

        $select = 'inv.goods_code,sum(inv.begin_num) begin_num,sum(inv.purchase) purchase,sum(inv.pur_return) pur_return,sum(inv.oms_sell_record) oms_sell_record,sum(inv.oms_sell_return) oms_sell_return,sum(inv.wbm_store_out) wbm_store_out,sum(inv.wbm_return) wbm_return,sum(inv.adjust) adjust,sum(inv.shift_in) shift_in,sum(inv.shift_out) shift_out,sum(inv.pos_sell) pos_sell,sum(inv.end_num) end_num,sum(inv.pur_money) pur_money';
        $sql_wh = " WHERE inv.store_code IN({$store_code})";
        $sql_join = '';
        $prev_detail_tbl = $this->get_cost_detail_tbl($prev_month);
        //如果存在上月月结单 ，期初成本取上月期末成本
        if ($prev_detail_tbl['status'] == 1) {
            $prev_detail = $this->get_by_store_code($param['store_code'], $prev_month);
            if (!empty($prev_detail)) {
                $select .=',cost.end_adjust_cost begin_cost';
                $sql_join = " LEFT JOIN {$prev_detail_tbl['data']} cost ON (inv.goods_code=cost.goods_code AND cost.store_code='{$param['store_code']}')";
            }
        }
        if (empty($sql_join)) {
            $select .=',AVG(inv.begin_cost) begin_cost';
        }
        $sql = "SELECT {$select} FROM {$inv_month_detail_tbl} inv {$sql_join} {$sql_wh}";
        $sql .= " GROUP BY inv.goods_code";
        $inv_detail = $this->db->get_all($sql);

        foreach ($inv_detail as &$val) {
            $val['store_code'] = $param['store_code'];
            $inv_num = $val['begin_num'] + $val['purchase'];
            $inv_num = $inv_num == 0 ? 1 : $inv_num;
            $val['end_cost'] = ($val['begin_num'] * $val['begin_cost'] + $val['pur_money']) / $inv_num;
            $val['end_adjust_cost'] = $val['end_cost'];
            $val['record_time'] = date('Y-m-d h:i:s');
            $val['record_code'] = $param['record_code'];
        }
        $new_tbl = $this->get_cost_detail_tbl($ymonth); //新表名
        if ($new_tbl['status'] != 1) {
            $ret_cost = $this->init_cost_month_mx_tbl($new_tbl['data']);
            if ($ret_cost['status'] != 1) {
                return $this->format_ret(-1, '', "生成 {$ymonth} 成本月结单明细失败");
            }
        }

        $update_str = "begin_num = VALUES(begin_num),purchase = VALUES(purchase),pur_return = VALUES(pur_return),oms_sell_record = VALUES(oms_sell_record),oms_sell_return = VALUES(oms_sell_return),wbm_store_out = VALUES(wbm_store_out),wbm_return = VALUES(wbm_return),adjust = VALUES(adjust),shift_in = VALUES(shift_in),shift_out = VALUES(shift_out),end_num = VALUES(end_num),pur_money = VALUES(pur_money),begin_cost = VALUES(begin_cost),end_cost = VALUES(end_cost),end_adjust_cost = VALUES(end_adjust_cost)";
        $ret = $this->insert_multi_duplicate($new_tbl['data'], $inv_detail, $update_str); //批量插入数据，存在则更新
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', "生成 {$ymonth} 成本月结单明细失败");
        }
        //回写主单据
        $ret = $this->main_write_back($param['record_code'], $new_tbl['data']);
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', "创建成本月结单失败");
        }
        return $this->format_ret(1, $new_tbl['data'], '创建月结单成功');
    }

    /**
     * @todo 创建成本月结单明细表，格式示例：cost_yj_mx_2016_04
     * @param string $tbl_name 年月
     * @return string 返回表名
     */
    function init_cost_month_mx_tbl($tbl_name) {
        $sql = "CREATE TABLE `{$tbl_name}` (
                `cost_yj_mx_id` int(11) NOT NULL AUTO_INCREMENT,
                `record_code` varchar(64) DEFAULT '' COMMENT '单据编号',
                `store_code` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '仓库代码，多个仓库以逗号隔开',
                `goods_code` varchar(30) DEFAULT '' COMMENT '商品编码',
                `begin_num` int(11) DEFAULT '0' COMMENT '期初数',
                `purchase` int(11) DEFAULT '0' COMMENT '采购进货数',
                `pur_return` int(11) DEFAULT '0' COMMENT '采购退货数',
                `oms_sell_record` int(11) DEFAULT '0' COMMENT '网络销售数',
                `oms_sell_return` int(11) DEFAULT '0' COMMENT '网络退货数',
                `wbm_store_out` int(11) DEFAULT '0' COMMENT '批发销货单数',
                `wbm_return` int(11) DEFAULT '0' COMMENT '批发退货数',
                `adjust` int(11) DEFAULT '0' COMMENT '库存调整数',
                `shift_in` int(11) DEFAULT '0' COMMENT '移仓出库数',
                `shift_out` int(11) DEFAULT '0' COMMENT '移仓入库数',
                `pos_sell` int(11) DEFAULT '0' COMMENT '门店销售数',
                `pos_sell_return` int(11) DEFAULT '0' COMMENT '门店退货数',
                `end_num` int(11) DEFAULT '0' COMMENT '期末数',
                `pur_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '采购金额',
                `begin_cost` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '期初成本单价(上月期末调整后成本单价)',
                `end_cost` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '期末成本单价',
                `adjust_cost` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '期末调整成本',
                `end_adjust_cost` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '期末调整后成本单价',
                `record_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '单据创建时间',
                `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
                `remark` varchar(255) DEFAULT '' COMMENT '备注',
                PRIMARY KEY (`cost_yj_mx_id`),
                UNIQUE KEY `idxu_ss` (`record_code`,`store_code`,`goods_code`),
                KEY `ix_record_code` (`record_code`) USING BTREE,
                KEY `ix_store_code` (`store_code`) USING BTREE,
                KEY `ix_goods_code` (`goods_code`) USING BTREE,
                KEY `ix_begin_num` (`begin_num`),
                KEY `ix_purchase` (`purchase`),
                KEY `ix_pur_return` (`pur_return`),
                KEY `ix_oms_sell_record` (`oms_sell_record`),
                KEY `ix_oms_sell_return` (`oms_sell_return`),
                KEY `ix_wbm_store_out` (`wbm_store_out`),
                KEY `ix_wbm_return` (`wbm_return`),
                KEY `ix_adjust` (`adjust`),
                KEY `ix_shift_in` (`shift_in`),
                KEY `ix_shift_out` (`shift_out`),
                KEY `ix_pos_sell` (`pos_sell`),
                KEY `ix_pos_sell_return` (`pos_sell_return`),
                KEY `ix_end_num` (`end_num`),
                KEY `ix_pur_money` (`pur_money`),
                KEY `ix_begin_cost` (`begin_cost`),
                KEY `ix_end_cost` (`end_cost`),
                KEY `ix_adjust_cost` (`adjust_cost`),
                KEY `ix_end_adjust_cost` (`end_adjust_cost`),
                KEY `ix_record_time` (`record_time`) USING BTREE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='成本月结单明细单据';";
        $ret = $this->db->query($sql);
        if ($ret != true) {
            return $this->format_ret(-1, '', '新建表' . $tbl_name . '失败');
        }
        $this->cost_yj_mx_tbls[] = $tbl_name;
        rsort($this->cost_yj_mx_tbls);
        return $this->format_ret(1, $tbl_name, '新建表' . $tbl_name . '成功');
    }

    /**
     * @todo 回写主单据
     */
    function main_write_back($record_code, $detail_tbl) {
        //汇总详情表
        $sql_detail = "select sum(begin_num*begin_cost) begin_amount,sum(begin_num) begin_total,sum(end_num*end_adjust_cost) end_amount,sum(end_num) end_total,sum(pur_money) purchase_amount,sum(purchase) purchase_total from {$detail_tbl} where record_code='{$record_code}'";
        $data_total = $this->db->get_row($sql_detail);

        $ret = $this->update_exp('cost_month', $data_total, array('record_code' => $record_code));
        return $ret;
    }

    /**
     * 获取指定成本月结单详情表名
     */
    public function get_cost_detail_tbl($ymonth) {
        $ymonth_arr = explode('-', $ymonth);
        $detail_tbl = 'cost_yj_mx_' . $ymonth_arr[0] . '_' . $ymonth_arr[1]; //新表名
        //判断表是否已存在
        if (in_array($detail_tbl, $this->cost_yj_mx_tbls)) {
            return $this->format_ret(1, $detail_tbl, '明细表已存在');
        }
        return $this->format_ret(-1, $detail_tbl, '明细表不存在');
    }

    /**
     * @todo 修改明细信息-调整成本
     */
    public function edit_detail_action($detail) {
        if (!isset($detail['record_code']) || !isset($detail['cost_yj_mx_id'])) {
            return $this->format_ret(false, array(), '修改时缺少单据ID或code!');
        }
        $detail_tbl = $this->get_cost_detail_tbl($detail['ymonth']);

        $sql = "SELECT end_cost,adjust_cost,end_adjust_cost FROM {$detail_tbl['data']} WHERE cost_yj_mx_id=:cost_yj_mx_id";
        $sql_value = array(':cost_yj_mx_id' => $detail['cost_yj_mx_id']);
        $result = $this->db->get_row($sql, $sql_value);
        if (empty($result)) {
            return $this->format_ret(-1, array(), '未找到单据!');
        }

        $data['end_adjust_cost'] = $result['end_cost'] + $detail['adjust_cost'];
        $data['adjust_cost'] = $detail['adjust_cost'];
        $ret = $this->update_exp($detail_tbl['data'], $data, array('cost_yj_mx_id' => $detail['cost_yj_mx_id']));

        if ($ret['status'] == 1 && $result['end_adjust_cost'] != $data['end_adjust_cost']) {
            load_model('acc/CostMonthLogModel')->add_log($detail['record_code'], '修改明细', "商品{$detail['goods_code']}调整成本由{$result['adjust_cost']}修改为{$detail['adjust_cost']}");
        }

        $this->main_write_back($detail['record_code'], $detail_tbl['data']); //回写主单据
        return $ret;
    }

    /**
     * @todo 判断值在多维数组中是否存在
     */
    function deep_in_array($value, $array) {
        foreach ($array as $item) {
            if (!is_array($item)) {
                if ($item == $value) {
                    return true;
                } else {
                    continue;
                }
            }
            if (in_array($value, $item)) {
                return $item;
            } else if ($this->deep_in_array($value, $item)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 销售数据刷新
     */
    function cost_oam($param) {
        $detail_tbl = $this->get_cost_detail_tbl($param['ymonth']);
        if ($detail_tbl['status'] != 1) {
            return $this->format_ret(-1, '', '月结单明细不存在,不能进行数据维护');
        }
        $date_start = load_model('acc/InvMonthModel')->get_cur_month_first_day($param['ymonth']);
        $date_end = load_model('acc/InvMonthModel')->get_cur_month_last_day($param['ymonth']);
        $store_code = deal_strs_with_quote($param['store_code']);
        //获取符合条件的销售订单
        $sql_oms = "SELECT sell_record_code FROM `oms_sell_record` WHERE delivery_date>=:date_start AND delivery_date<=:date_end AND store_code in({$store_code})";
        $sql_values = array(':date_start' => $date_start, ':date_end' => $date_end);
        $oms_record = $this->db->get_all($sql_oms, $sql_values);
        if (empty($oms_record)) {
            return $this->format_ret(-1, '', '不存在销售数据，无需维护');
        }

        $record_code_arr = array();
        foreach ($oms_record as $val) {
            $record_code_arr[] = $val['sell_record_code'];
        }
        $record_code_str = deal_array_with_quote($record_code_arr);

        $sql = "UPDATE `oms_sell_record_detail` oms INNER JOIN `{$detail_tbl['data']}` yj ON oms.goods_code = yj.goods_code AND yj.record_code=:record_code SET oms.cost_price = yj.end_adjust_cost WHERE oms.sell_record_code in({$record_code_str})";
        $sql_values = array(':record_code' => $param['record_code']);
        $ret = $this->query($sql, $sql_values);
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '销售单成本维护失败');
        }
        return $this->format_ret(1);
    }

    /**
     * @todo 月结单明细数据刷新
     */
    function data_refresh($param) {
        $inv_detail_tbl = load_model('acc/InvMonthModel')->get_inv_detail_tbl($param['ymonth'], TRUE);
        $this->begin_trans();
        if ($inv_detail_tbl['status'] == 1) {
            $sql = "DROP TABLE {$inv_detail_tbl['data']}";
            $ret = $this->query($sql);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '刷新数据失败');
            }
        }
        $ret = $this->create_cost_month_detail($param);
        if ($ret['status'] == 1) {
            $sql_detail = "UPDATE {$ret['data']} SET end_adjust_cost = end_cost + adjust_cost";
            $ret = $this->query($sql_detail);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '刷新数据失败');
            }
        } else {
            $this->rollback();
            return $this->format_ret(-1, '', '刷新数据失败');
        }
        $this->commit();
        return $ret;
    }

    /**
     * @todo 导入商品成本
     * @describe type-adjust_cost:以调整成本导入
     * @describe type-end_adjust_cost:以调整后成本单价导入
     */
    function import_detail($param, $file) {
        $detail_tbl = $this->get_cost_detail_tbl($param['ymonth']);
        $sql_detail = "SELECT goods_code,end_cost,adjust_cost,end_adjust_cost FROM {$detail_tbl['data']} WHERE record_code=:record_code";
        $sql_value = array(':record_code' => $param['record_code']);
        $detail = $this->db->get_all($sql_detail, $sql_value);

        $goods = array();
        $num = $this->read_csv($file, $goods);

        $import_count = count($goods);
        $error_msg = array();
        $err_num = 0;

        //处理导入信息，正确和错误信息分离
        $end_adjust_cost = array();
        $adjust_cost = array();
        if ($param['type'] == 'adjust_cost') {
            foreach ($goods as $key1 => $val1) {
                $res = $this->deep_in_array($key1, $detail);
                if (is_array($res)) {
                    $adjust_cost[$key1] = $val1;
                    $end_adjust_cost[$key1] = $res['end_cost'] + $val1;
                    continue;
                }
                $error_msg[] = array($key1 => '明细中不存在此商品');
                $err_num++;
            }
        } else {
            foreach ($goods as $key2 => $val2) {
                $res = $this->deep_in_array($key2, $detail);
                if (is_array($res)) {
                    $adjust_cost[$key2] = $val2 - $res['end_cost'];
                    $end_adjust_cost[$key2] = $val2;
                    continue;
                }
                $error_msg[] = array($key2 => '明细中不存在此商品');
                $err_num++;
            }
        }

        //拼接批量更新sql语句
        $goods_code = implode(',', array_keys($adjust_cost));
        $goods_code_str = deal_strs_with_quote($goods_code);
        $sql = "UPDATE {$detail_tbl['data']} SET end_adjust_cost = CASE goods_code ";
        foreach ($end_adjust_cost as $k1 => $v1) {
            $sql .= sprintf("WHEN '%s' THEN '%s' ", $k1, $v1);
        }
        $sql .= 'END,adjust_cost = CASE goods_code ';
        foreach ($adjust_cost as $k2 => $v2) {
            $sql .= sprintf("WHEN '%s' THEN '%s' ", $k2, $v2);
        }
        $sql .= "END WHERE goods_code IN ({$goods_code_str}) AND record_code='{$param['record_code']}'";
        $ret = $this->query($sql);

        $this->main_write_back($param['record_code'], $detail_tbl['data']); //回写主单据

        $action_name = $param['type'] == 'adjust_cost' ? '以调整成本导入' : '以调整后期末成本导入';
        if ($ret['status'] == 1) {
            load_model('acc/CostMonthLogModel')->add_log($param['record_code'], $action_name);
        }

        $success_num = $import_count - $err_num;
        $message = '导入成功' . $success_num;
        if ($err_num > 0 || !empty($error_msg)) {
            $message .=',' . '失败数量:' . $err_num;
            $fail_top = array('商品编码', '错误信息');
            $file_name = load_model('wbm/StoreOutRecordModel')->create_import_fail_files($fail_top, $error_msg);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        $ret['message'] = $message;
        return $ret;
    }

    /**
     * @todo 读取excel文件，以数组形式保存
     */
    function read_csv($file, &$goods) {
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $goods[$row[0]] = $row[1];
                }
            }
            $i++;
        }
        fclose($file);
        return $i;
    }

    /**
     * @todo 编码转换
     */
    private function tran_csv(&$row) {
        if (!empty($row)) {
            foreach ($row as &$val) {
                $val = iconv('gbk', 'utf-8', $val);
                $val = trim(str_replace('"', '', $val));
            }
        }
    }

}
