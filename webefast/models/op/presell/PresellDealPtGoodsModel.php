<?php

require_model('tb/TbModel');

/**
 * 预售业务
 * @author WMH
 */
class PresellDealPtGoodsModel extends TbModel {

    protected $table = 'op_presell_plan_pt_goods';

    /**
     * 获取平台商品信息
     * @param array $params 参数
     * @return array 数据集
     */
    public function get_presell_pt_goods($params) {
        $plan_shop = load_model('op/presell/PresellModel')->get_presell_shop($params['plan_code']);
        $sql_values = array(':barcode' => $params['barcode'], ':plan_code' => $params['plan_code']);
        $shop_str = $this->arr_to_in_sql_value($plan_shop, 'shop_code', $sql_values);
        $sql = "SELECT gs.source,gs.shop_code,gs.goods_from_id,g.goods_name,g.status,gs.sku_id,gs.goods_barcode,gs.sku_properties_name,gs.sale_mode,gs.presell_end_time,pg.sku,pg.pid,pg.id AS pt_id FROM api_goods AS g 
                INNER JOIN api_goods_sku AS gs ON g.goods_from_id=gs.goods_from_id
                LEFT JOIN op_presell_plan_pt_goods AS pg ON pg.sku_id=gs.sku_id AND pg.plan_code=:plan_code
                WHERE gs.goods_barcode=:barcode AND gs.shop_code IN({$shop_str})";
        $data = $this->db->get_all($sql, $sql_values);
        foreach ($data as &$val) {
            $val['goods_status'] = $val['status'] == 1 ? '在售' : '在库';
            $val['is_edit_pt_goods'] = $val['sale_mode'] == 'presale' && !empty($val['presell_end_time']) ? 0 : 1;
            $val['is_presell'] = empty($val['sku']) ? 0 : 1;
        }
        return $data;
    }

    /**
     * 添加预售商品关联
     * @param string $plan_code
     * @param array $barcode_arr
     * @param string $sku_id
     * @return array
     */
    public function add_presell_pt_goods($plan_code, $barcode_arr, $sku_id = '') {
        if (empty($plan_code) || empty($barcode_arr)) {
            return $this->format_ret(-1, '', '数据有误');
        }
        $sql_values = array(':plan_code' => $plan_code);
        $wh = array();

        $plan_shop = load_model('op/presell/PresellModel')->get_presell_shop($plan_code);
        $shop_str = $this->arr_to_in_sql_value($plan_shop, 'shop_code', $sql_values);
        $wh[] = "shop_code IN({$shop_str})";

        if (!empty($barcode_arr)) {
            $barcode_str = $this->arr_to_in_sql_value($barcode_arr, 'goods_barcode', $sql_values);
            $wh[] = "goods_barcode IN({$barcode_str})";
        }

        $sql = "SELECT d.id AS pid,d.plan_code,s.sku,g.shop_code,g.sku_id FROM op_presell_plan_detail AS d,api_goods_sku AS g,goods_sku AS s
                WHERE d.sku=s.sku AND s.barcode=g.goods_barcode AND d.plan_code=:plan_code AND g.shop_code IN({$shop_str}) 
                AND g.goods_barcode IN({$barcode_str})";

        if ($sku_id !== '') {
            $sql_values[':sku_id'] = $sku_id;
            $sql .=' AND g.sku_id=:sku_id';
        }
        $pt_goods = $this->db->get_all($sql, $sql_values);
        if (empty($pt_goods)) {
            if ($sku_id !== '') {
                return $this->format_ret(-1, '', '未找到平台商品');
            }
            return $this->format_ret(1);
        }

        $ret = $this->insert_multi_exp($this->table, $pt_goods, TRUE);
        return $ret;
    }

    /**
     * 选择/反选详情的平台商品
     * @param array $params 参数
     * @return array 结果
     */
    public function up_goods_presell_status($params) {
        if (!in_array($params['sale_mode'], array('stock', 'presale'))) {
            return $this->format_ret(-1, '', '数据有误，请刷新页面重试');
        }
        $plan_info = load_model('op/presell/PresellModel')->exists_plan($params['plan_code']);
        if ($plan_info['exit_status'] != 0) {
            return $this->format_ret(-1, '', '预售计划已终止');
        }
        $msg = $params['sale_mode'] == 'presale' ? '添加预售' : '取消预售';
        if ($params['sale_mode'] == 'presale') {
            $ret = $this->add_presell_pt_goods($params['plan_code'], array($params['barcode']), $params['sku_id']);
        } else {
            $ret = $this->delete_presell_pt_goods($params);
        }

        if ($ret['status'] == 1) {
            load_model('op/presell/PresellLogModel')->insert_log($params['plan_code'], '更新明细', "平台SKUID：{$params['sku_id']} {$msg}");
            $ret['message'] = $msg . '成功';
        } else {
            $ret['message'] = $msg . '失败';
        }
        return $ret;
    }

    /**
     * 删除预售商品关联
     * @param array $params 参数
     * @return array
     */
    public function delete_presell_pt_goods($params) {
        if (!empty($params['id'])) {
            $ret = $this->delete(array('id' => $params['id']));
            if ($ret['status'] == 1 && $this->affected_rows() == 1) {
                return $ret;
            }
        }
        $where = get_array_vars($params, array('plan_code', 'sku', 'shop_code', 'sku_id'));
        $ret = $this->delete($where);
        if ($ret['status'] < 1 || $this->affected_rows() != 1) {
            $ret['status'] = -1;
        }
        return $ret;
    }

    /**
     * 设置平台商品预售数据
     * @param array $plan_info 预售计划信息
     * @param array $up_data 需要更新的数据
     * @return 更新结果
     */
    public function set_pt_goods_presell_status($plan_info, $up_data) {
        foreach ($up_data as $k => &$val) {
            if ($val['sale_mode'] == 'presale' && !empty($val['presell_end_time']) && $val['pre_sync_status_old'] != -1) {
                unset($up_data[$k]);
            }
            unset($val['pre_sync_status_old']);
            $val['sale_mode'] = 'presale';
            $val['is_allow_sync_inv'] = 0;
            $val['presell_end_time'] = $plan_info['end_time'];
        }
        if (empty($up_data)) {
            return $this->format_ret(1, '', '已更新，不能再次更新预售信息');
        }
        $update_str = 'is_allow_sync_inv=VALUES(is_allow_sync_inv),sale_mode=VALUES(sale_mode),presell_end_time=VALUES(presell_end_time),pre_sync_status=VALUES(pre_sync_status)';
        $ret = $this->insert_multi_duplicate('api_goods_sku', $up_data, $update_str);
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '更新平台商品预售信息失败');
        }

        //设置日志
        $barcode_arr = array_column($up_data, 'barcode');
        $this->set_pt_goods_opt_log($plan_info['plan_code'], $barcode_arr, 1);

        return $ret;
    }

    /**
     * 获取关联的预售商品
     * @param array $plan_code 预售编码
     * @return array
     */
    public function get_relate_pt_goods($plan_code) {
        $sql = 'SELECT pg.sku,gs.source,gs.goods_from_id,gs.sku_id,gs.shop_code,gs.goods_barcode AS barcode,gs.is_allow_sync_inv AS pre_sync_status,gs.sale_mode,gs.presell_end_time,gs.pre_sync_status AS pre_sync_status_old
                FROM op_presell_plan_pt_goods AS pg,api_goods_sku AS gs 
                WHERE pg.shop_code=gs.shop_code AND pg.sku_id=gs.sku_id AND pg.plan_code=:plan_code';
        return $this->db->get_all($sql, array(':plan_code' => $plan_code));
    }

    /**
     * 设置平台商品更新库存同步状态的日志
     * @param string $plan_code 预售计划
     * @param array $barcode_arr 条码
     * @param array $sync_type 同步状态
     */
    public function set_pt_goods_opt_log($plan_code, $barcode_arr, $sync_type) {
        $log_barcode = array_chunk($barcode_arr, 10, true);
        $txt = $sync_type == 1 ? '允许库存同步' : '禁止库存同步';
        foreach ($log_barcode as $val) {
            $v = implode(',', $val);
            $xq = "{$v}，预售计划开始，设置平台商品：{$plan_code}，{$txt}";
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => '网络订单', 'yw_code' => $barcode_arr[0], 'operate_type' => '编辑', 'operate_xq' => $xq);
            load_model('sys/OperateLogModel')->insert($log);
        }
    }

    /**
     * 预售结束，自动还原平台商品预售信息
     */
    public function auto_res_pt_presell_goods() {
        $product_version_no = load_model('sys/SysAuthModel')->product_version_no();
        if ($product_version_no == 0) {
            return $this->format_ret(1, '', '标准版不执行');
        }
        $presell_plan = load_model('sys/SysParamsModel')->get_val_by_code('presell_plan');
        if ($presell_plan['presell_plan'] != 1) {
            return $this->format_ret(-1, '', '预售计划未启用');
        }
        $sql = "UPDATE api_goods_sku SET sale_mode='stock',presell_end_time=0,is_allow_sync_inv=pre_sync_status,pre_sync_status='-1'
                WHERE presell_end_time<=:end_time AND sale_mode='presale' AND presell_end_time <> 0 AND pre_sync_status <> -1";
        return $this->query($sql, array(':end_time' => time()));
    }

}
