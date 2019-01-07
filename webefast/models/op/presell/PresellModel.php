<?php

require_model('tb/TbModel');

/**
 * 预售业务
 * @author WMH
 */
class PresellModel extends TbModel {

    private $detail_table = 'op_presell_plan_detail';

    function __construct() {
        parent::__construct('op_presell_plan');
    }

    /**
     * 获取预售计划列表
     * @param array $filter 筛选条件
     * @return array 数据集
     */
    public function get_presell_plan_by_page($filter) {
        if (isset($filter['keyword_value']) && $filter['keyword_value'] != '') {
            $filter[$filter['keyword']] = trim($filter['keyword_value']);
        }
        $sql_join = '';
        if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
            $sql_join = ' LEFT JOIN `op_presell_plan_shop` AS ps ON pp.`plan_code`=ps.`plan_code` ';
        }

        if (isset($filter['keyword']) && in_array($filter['keyword'], array('barcode', 'goods_code')) && $filter[$filter['keyword']] != '') {
            $sql_join .= " LEFT JOIN `{$this->detail_table}` AS pd ON pp.`plan_code`=pd.`plan_code` 
                        LEFT JOIN goods_sku AS gs ON pd.`sku`=gs.`sku`";
        }
        $sql_main = "FROM `{$this->table}` AS pp {$sql_join} WHERE 1";
        $sql_values = array();
        $select = 'pp.`id`,pp.`plan_code`,pp.`plan_name`,pp.`start_time`,pp.`end_time`,pp.`sync_num`,pp.`last_sync_time`,pp.`create_person`,pp.`create_time`,pp.`exit_status`';
        //计划编码
        if (isset($filter['plan_code']) && $filter['plan_code'] != '') {
            $sql_main .= ' AND pp.`plan_code`=:plan_code ';
            $sql_values[':plan_code'] = $filter['plan_code'];
        }
        //计划名称
        if (isset($filter['plan_name']) && $filter['plan_name'] != '') {
            $sql_main .= ' AND pp.`plan_name` LIKE :plan_name ';
            $sql_values[':plan_name'] = "%{$filter['plan_name']}%";
        }
        if (isset($filter['keyword_date'])) {
            //开始时间-起始
            if (!empty($filter['time_start'])) {
                $sql_main .= " AND pp.`{$filter['keyword_date']}`>=:time_start ";
                $sql_values[':time_start'] = strtotime($filter['time_start'] . ' 00:00:00');
            }
            //开始时间-结束
            if (!empty($filter['time_end'])) {
                $sql_main .= " AND pp.`{$filter['keyword_date']}`<=:time_end ";
                $sql_values[':time_end'] = strtotime($filter['time_end'] . ' 23:59:59');
            }
        }
        //预售状态
        if (!empty($filter['presell_status'])) {
            $status = $filter['presell_status'];
            $curr_time = time();
            switch ($status) {
                case 'no_start':
                    $sql_main .= " AND pp.`start_time`>{$curr_time}";
                    break;
                case 'starting':
                    $sql_main .= " AND pp.`start_time`<={$curr_time} && pp.`end_time`>{$curr_time} AND exit_status=0 ";
                    break;
                default:
                    break;
            }
        }
        //预售店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
            $shop_arr = explode(',', $filter['shop_code']);
            $shop_str = $this->arr_to_in_sql_value($shop_arr, 'shop_code', $sql_values);
            $sql_main .= " AND ps.`shop_code` IN({$shop_str}) ";
        }
        //商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= ' AND gs.`barcode`=:barcode ';
            $sql_values[':barcode'] = $filter['barcode'];
        }
        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= ' AND gs.`goods_code`=:goods_code ';
            $sql_values[':goods_code'] = $filter['goods_code'];
        }

        $sql_main .= ' GROUP BY pp.`plan_code`';
        $sql_main .= ' ORDER BY pp.`create_time` DESC';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, TRUE);
        if (!empty($data['data'])) {
            $plan_code_arr = array_unique(array_column($data['data'], 'plan_code'));
            $shop_arr = $this->get_plan_shop_by_code($plan_code_arr);
        }

        $curr_time = time();
        foreach ($data['data'] as &$row) {
            $row['plan_start_time'] = empty($row['start_time']) ? '' : date('Y-m-d H:i:s', $row['start_time']);
            $row['plan_end_time'] = empty($row['end_time']) ? '' : date('Y-m-d H:i:s', $row['end_time']);
            $row['create_time'] = empty($row['create_time']) ? '' : date('Y-m-d H:i:s', $row['create_time']);
            $hour = floor(($row['start_time'] - $curr_time) % 86400 / 3600);
            $row['is_allow_sync'] = $hour < 24 && $row['end_time'] > $curr_time ? 1 : 0;
            $row['is_allow_delete'] = $row['start_time'] > $curr_time && $row['sync_num'] < 1 ? 1 : 0;
            $row['presell_status'] = $row['start_time'] > $curr_time ? 0 : 1;
            $row['sync_status'] = $row['sync_num'] > 0 ? '已同步' : '未同步';
            //是否显示立即终止
            $exit_show = 0;
            if ($row['start_time'] <= $curr_time && $row['end_time'] >= $curr_time && $row['exit_status'] != 1) {
                $exit_show = 1;
            }
            $row['exit_show'] = $exit_show;

            $row['plan_shop'] = $shop_arr[$row['plan_code']];
        }

        $ret_data = $data;

        return $this->format_ret(1, $ret_data);
    }

    /**
     * 获取预售店铺数据，列表查询展示
     * @param string $plan_code_arr 预售编码集合
     * @return array 数据集
     */
    public function get_plan_shop_by_code($plan_code_arr) {
        $sql_values = array();
        $plan_code_str = $this->arr_to_in_sql_value($plan_code_arr, 'plan_code', $sql_values);
        $sql = "SELECT GROUP_CONCAT(bs.`shop_name`) AS plan_shop,ps.`plan_code` 
                FROM `op_presell_plan_shop` AS ps INNER JOIN `base_shop` AS bs ON ps.`shop_code`=bs.`shop_code`
                WHERE ps.`plan_code` IN({$plan_code_str}) GROUP BY ps.`plan_code`";
        $shop_arr = $this->db->get_all($sql, $sql_values);
        $shop_arr = array_column($shop_arr, 'plan_shop', 'plan_code');
        return $shop_arr;
    }

    /**
     * 获取店铺对应的进行中的预售计划
     * 过滤已终止的计划 exit_status by:weichuan.hua
     * @param string $shop_code 店铺代码
     */
    public function get_plan_by_shop($shop_code) {
        $sql = "SELECT DISTINCT pp.plan_code,pp.start_time,pp.end_time FROM {$this->table} AS pp INNER JOIN op_presell_plan_shop AS ps  
                WHERE pp.start_time<=:curr_time AND pp.end_time>=:curr_time AND ps.shop_code=:shop_code AND pp.exit_status<>1 ";
        $sql_values = array(':curr_time' => time(), ':shop_code' => $shop_code);
        return $this->db->get_all($sql, $sql_values);
    }

    /**
     * 根据编码判断预售计划是否存在
     */
    public function exists_plan($plan_code) {
        $sql = "SELECT plan_code,start_time,end_time,sync_num,exit_status FROM {$this->table} WHERE plan_code=:plan_code";
        return $this->db->get_row($sql, array(':plan_code' => $plan_code));
    }

    /**
     * 更新预售计划主信息
     * @param array $params 参数集
     * @return array 更新结果
     */
    public function edit_presell_plan($params) {
        if (empty($params['plan_code'])) {
            return $this->format_ret(-1, '', '数据有误，请刷新页面重试');
        }
        $ret = $this->exists_plan($params['plan_code']);
        if (empty($ret)) {
            return $this->format_ret(-1, '', '预售计划不存在');
        }
        if ($ret['exit_status'] != 0) {
            return $this->format_ret(-1, '', '预售计划已终止');
        }

        $up_data = array();
        $log_msg = '';
        $start_time = strtotime($params['start_time']);
        $end_time = strtotime($params['end_time']);

        if ($start_time != $ret['start_time']) {
            if ($start_time === FALSE) {
                return $this->format_ret(-1, '', '时间格式不正确');
            }

            $up_data['start_time'] = $start_time;

            $old_time = date('Y-m-d H:i:s', $ret['start_time']);
            $log_msg .= "预售开始时间由{$old_time}改为{$params['start_time']}；";
        }
        if ($end_time != $ret['end_time']) {
            if ($end_time === FALSE) {
                return $this->format_ret(-1, '', '时间格式不正确');
            }
            $up_data['end_time'] = $end_time;

            $old_time = date('Y-m-d H:i:s', $ret['end_time']);
            $log_msg .= "预售结束时间由{$old_time}改为{$params['end_time']}；";
        }
        $curr_time = time();
        if ($start_time <= $curr_time) {
            return $this->format_ret(-1, '', '预售开始时间不能早于当前时间');
        }
        if ($end_time <= $curr_time) {
            return $this->format_ret(-1, '', '预售结束时间不能早于当前时间');
        }
        if ($start_time > $end_time) {
            return $this->format_ret(-1, '', '预售结束时间不能早于预售开始时间');
        }

        $ret = $this->update($up_data, array('plan_code' => $params['plan_code']));
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '更新失败');
        }

        load_model('op/presell/PresellLogModel')->insert_log($params['plan_code'], '更新基本信息', $log_msg);
        return $this->format_ret(1, '', '更新成功');
    }

    /**
     * 创建预售计划
     * @param array $data 数据
     * @return array 结果
     */
    public function create_presell_plan($data) {
        $ret = $this->check_params($data, array('plan_code' => '预售计划编码', 'plan_name' => '预售计划名称', 'start_time' => '预售开始时间', 'end_time' => '预售结束时间', 'shop_code' => '预售店铺'));
        if ($ret['status'] < 1) {
            return $ret;
        }
        $ret = $this->exists_plan($data['plan_code']);
        if (!empty($ret)) {
            return $this->format_ret(-1, '', '预售编码已存在，请刷新页面重试');
        }

        $plan_shop = explode(',', $data['shop_code']);
        unset($data['shop_code']);

        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time'] = strtotime($data['end_time']);
        if ($data['start_time'] > $data['end_time']) {
            return $this->format_ret(-1, '', '预售结束时间不能早于预售开始时间');
        }

        $data['create_time'] = time();
        $data['create_person'] = CTX()->get_session('user_name');

        $this->begin_trans();
        $ret = $this->insert($data);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '创建失败');
        }

        $shop_data = array();
        foreach ($plan_shop as $val) {
            $d = array();
            $d['plan_code'] = $data['plan_code'];
            $d['shop_code'] = $val;
            $shop_data[] = $d;
        }
        $ret = $this->insert_multi_exp('op_presell_plan_shop', $shop_data, TRUE);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '创建失败');
        }
        load_model('op/presell/PresellLogModel')->insert_log($data['plan_code'], '创建', '创建预售计划');

        $this->commit();
        return $this->format_ret(1, $data['plan_code'], '创建成功');
    }

    /**
     * 预售计划删除
     * @param string $plan_code 预售计划编码
     * @return array 结果
     */
    public function delete_presell_plan($plan_code) {
        $ret = $this->exists_plan($plan_code);
        if (empty($ret)) {
            return $this->format_ret(-1, '', '预售计划不存在');
        }
        if ($ret['exit_status'] != 0) {
            return $this->format_ret(-1, '', '该预售计划已终止！');
        }
        $this->begin_trans();
        //删除日志
        $ret = parent::delete_exp('op_presell_log', array('plan_code' => $plan_code));
        if ($ret != TRUE) {
            $this->rollback();
            return $this->format_ret(-1, '', '清除日志失败');
        }
        //清除预售商品绑定平台商品数据
        $ret = parent::delete_exp('op_presell_plan_pt_goods', array('plan_code' => $plan_code));
        if ($ret != TRUE) {
            $this->rollback();
            return $this->format_ret(-1, '', '清除预售商品绑定平台商品数据失败');
        }
        //删除明细数据
        $ret = parent::delete_exp($this->detail_table, array('plan_code' => $plan_code));
        if ($ret != TRUE) {
            $this->rollback();
            return $this->format_ret(-1, '', '清除明细失败');
        }
        //删除预售店铺
        $ret = parent::delete_exp('op_presell_plan_shop', array('plan_code' => $plan_code));
        if ($ret != TRUE) {
            $this->rollback();
            return $this->format_ret(-1, '', '清除预售店铺失败');
        }
        //删除主单数据
        $ret = parent::delete(array('plan_code' => $plan_code));
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '删除单据失败');
        }
        $this->commit();
        return $this->format_ret(1, '', '删除成功');
    }

    /**
     * 终止预售计划
     * @param $plan_code
     * @return array
     */
    public function exit_presell_plan($plan_code) {
        $ret = $this->exists_plan($plan_code);
        if (empty($ret)) {
            return $this->format_ret(-1, '', '预售计划不存在');
        }
        $curr_time = time();
        if ($curr_time < $ret['start_time'] || $curr_time > $ret['end_time']) {
            return $this->format_ret(-1, '', '当前时间不在预售计划时间内！');
        }
        if ($ret['exit_status'] != 0) {
            return $this->format_ret(-1, '', '预售计划已终止！');
        }

        $this->begin_trans();
        $sql = "SELECT gs.api_goods_sku_id FROM api_goods_sku AS gs INNER JOIN op_presell_plan_pt_goods AS ppg ON gs.sku_id=ppg.sku_id AND gs.shop_code=ppg.shop_code
                WHERE 1 AND gs.sale_mode='presale' AND gs.presell_end_time <> 0 AND gs.pre_sync_status <> -1 AND ppg.plan_code=:plan_code ";
        $sql_values = array();
        $sql_values[':plan_code'] = $plan_code;
        $id_arr = $this->db->get_all_col($sql, $sql_values);
        if (!empty($id_arr)) {
            $sql_values = array();
            $id_str = $this->arr_to_in_sql_value($id_arr, 'api_goods_sku_id', $sql_values);
            $sql = "UPDATE api_goods_sku SET sale_mode='stock',presell_end_time=0,is_allow_sync_inv=pre_sync_status,pre_sync_status='-1' WHERE api_goods_sku_id IN ({$id_str})";
            $ret = $this->query($sql, $sql_values);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '更新平台商品状态失败！');
            }
        }

        //更新状态为终止
        $ret = $this->update_exp('op_presell_plan', array('exit_status' => 1), array('plan_code' => $plan_code));
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '更新计划任务终止状态失败！');
        }

        $this->commit();
        //日志
        load_model('op/presell/PresellLogModel')->insert_log($plan_code, '终止预售计划', '手动终止预售计划');
        return $this->format_ret(1, '', '终止成功！');
    }

    /**
     * 同步预售库存检查
     * @param array $params 参数
     * @return array 结果
     */
    public function sync_presell_inv_check($params) {
        $plan_info = $this->exists_plan($params['plan_code']);
        if (empty($plan_info)) {
            return $this->format_ret(-1, '', '预售计划不存在，请检查单据');
        }
        if ($plan_info['exit_status'] != 0) {
            return $this->format_ret(-1, '', '该预售计划已手动终止！');
        }
        if ($plan_info['start_time'] > time() && $plan_info['sync_num'] < 1) {
            return $this->format_ret(2, date('Y-m-d H:i:s', $plan_info['start_time']));
        }
        return $this->format_ret(1);
    }

    /**
     * 同步预售库存
     * @param array $params 参数
     * @return array 结果
     */
    public function sync_presell_inv($params) {
        if (empty($params['plan_code'])) {
            return $this->format_ret(-1, '', '数据有误，请刷新页面重试');
        }
        $plan_code = $params['plan_code'];
        $plan_info = $this->exists_plan($plan_code);
        if (empty($plan_info)) {
            return $this->format_ret(-1, '', '预售计划不存在或已删除');
        }
        if ($plan_info['exit_status'] != 0) {
            return $this->format_ret(-1, '', '预售计划已终止');
        }
        $presell_goods = load_model('op/presell/PresellDetailModel')->get_detail_by_code($plan_code, 'sku,presell_num');
        if (empty($presell_goods)) {
            return $this->format_ret(-1, '', '预售计划明细为空，不能同步');
        }

        $ptGoodsObj = load_model('op/presell/PresellDealPtGoodsModel');
        //查询平台预售商品
        $pt_goods = $ptGoodsObj->get_relate_pt_goods($plan_code);
        if (empty($pt_goods)) {
            return $this->format_ret(-1, '', '预售计划明细未关联平台预售商品');
        }

        $this->begin_trans();
        //更新平台商品预售信息
        //记录之前商品同步状态，禁止平台商品库存同步
        $ret = $ptGoodsObj->set_pt_goods_presell_status($plan_info, $pt_goods);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        $this->commit();

        //组装预售库存同步数据，同步预售库存
        $presell_goods = array_column($presell_goods, 'presell_num', 'sku');
        $sync_data = array();
        foreach ($pt_goods as $val) {
            $val['plan_code'] = $plan_code;
            $val['num'] = $presell_goods[$val['sku']];
            if (isset($sync_data[$val['source']])) {
                $sync_data[$source]['goods'][] = $val;
                continue;
            }
            $sync_data[$source]['source'] = $val['source'];
            $sync_data[$source]['shop_code'] = $val['shop_code'];
            $sync_data[$source]['goods'][] = $val;
        }

        foreach ($sync_data as $val) {
            $ret = load_model("api/sys/ApiGoodsModel")->presell_goods_sync_inv_action($val);
        }

        //回写预售计划同步状态
        $this->update(array('sync_num' => $plan_info['sync_num'] + 1, 'last_sync_time' => time()), array('plan_code' => $plan_code));

        load_model('op/presell/PresellLogModel')->insert_log($plan_code, '库存同步');

        return $ret;
    }

    /**
     * 根据预售编码获取预售店铺
     * @param array $plan_code 预售编码
     * @return array 数据集
     */
    public function get_presell_shop($plan_code) {
        $sql = 'SELECT shop_code FROM op_presell_plan_shop WHERE plan_code=:plan_code';
        return $this->db->get_col($sql, array(':plan_code' => $plan_code));
    }

    /**
     * 检查参数是否存在
     * @param array $params 参数
     * @param array $field_arr 要检查的字段 array('字段'=>'字段名')
     * @return array 检查结果
     */
    public function check_params($params, $field_arr = array()) {
        if (empty($params) || empty($field_arr)) {
            return $this->format_ret(-1, '', '内部参数错误');
        }
        $status = '1';
        $msg = '';
        foreach ($field_arr as $k => $v) {
            if (!isset($params[$k]) || empty($params[$k])) {
                $status = '-1';
                $msg = $v . ' 不能为空';
                break;
            }
        }
        return $this->format_ret($status, array(), $msg);
    }

    /**
     * 生成单据号
     */
    public function create_fast_bill_sn($djh = 0) {
        if ($djh > 0) {
            $sql = "SELECT id FROM {$this->table} ORDER BY id DESC LIMIT 1 ";
            $data = $this->db->get_all($sql);
            if ($data) {
                $djh = intval($data[0]['id']) + 1;
            } else {
                $djh = 1;
            }
        } else {
            $djh = $djh + 1;
        }
        require_lib('comm_util', true);
        $new_jdh = "YS" . date("Ymd") . add_zero($djh);
        $sql = "SELECT id FROM {$this->table} WHERE plan_code=:plan_code ";
        $sql_value = array(':plan_code' => $new_jdh);
        $id = $this->db->get_value($sql, $sql_value);
        if (empty($id) || $id === false) {
            return $new_jdh;
        } else {
            return $this->create_fast_bill_sn($djh);
        }
    }

}
