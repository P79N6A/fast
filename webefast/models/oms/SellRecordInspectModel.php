<?php

require_model('tb/TbModel');

/**
 * 快速审单功能
 */
class SellRecordInspectModel extends TbModel {

    function __construct() {
        parent::__construct('oms_sell_record_inspect');
    }

    /**
     * 获取筛选数据集
     * @param array $filter 筛选条件
     */
    function get_filter_data($filter) {
        $fld = array('shop_code', 'filter_type', 'is_refresh');
        foreach ($fld as $val) {
            if (!array_key_exists($val, $filter) || $filter[$val] == '') {
                return $this->format_ret(-1, '', '请刷新页面重试');
            }
        }

        $filter_type = $filter['filter_type'] == 'filter_addr' ? 0 : 1;
        if ($filter['shop_code'] == 'all') {
            $shop_code = load_model('base/ShopModel')->get_purview_shop('shop_code');
            $shop_code = implode(',', array_column($shop_code, 'shop_code'));
        } else {
            $shop_code = $filter['shop_code'];
        }
        $shop_code = $shop_code == '' ? '' : deal_strs_with_quote($shop_code);
        if ($filter['is_refresh'] == 1) {
            $ret = $this->insert_filter_data($shop_code, $filter_type);
            if ($ret['status'] != 1) {
                return $ret;
            }
        }

        if ($filter_type == 1) {
            $sql = "SELECT si.type_val AS value,sum(si.num) AS num,gs.barcode AS text FROM {$this->table} AS si LEFT JOIN goods_sku AS gs ON si.type_val=gs.sku
                    WHERE si.type=1 ";
        } else {
            $sql = "SELECT si.type_val AS value,sum(si.num) AS num,ba.name AS text FROM {$this->table} AS si LEFT JOIN base_area AS ba ON si.type_val=ba.id AND ba.type=2 WHERE si.type=0 ";
        }

        if (!empty($shop_code)) {
            $sql .=" AND shop_code in({$shop_code}) ";
        }
        $sql .= ' GROUP BY type_val ORDER BY num DESC LIMIT 10';

        $data = $this->db->get_all($sql);
        return $this->format_ret(1, $data);
    }

    /**
     * 获取快速审单筛选数据，页面刷新
     */
    function insert_filter_data($shop_code, $filter_type) {
        $this->begin_trans();
        try {
            $del_sql = "DELETE FROM {$this->table} WHERE type='{$filter_type}'";
            if (!empty($shop_code)) {
                $del_sql .= " AND shop_code in({$shop_code})";
            }
            $ret = $this->db->query($del_sql);
            if ($ret == FALSE) {
                throw new Exception('筛选出错');
            }

            $area_data = $this->single_filter_inspect_data($filter_type, 0, $shop_code);
            if (!empty($area_data)) {
                $ret = $this->insert_multi($area_data, true);
                if ($ret['status'] != 1) {
                    throw new Exception('筛选失败');
                }
            }
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    /**
     * 获取快速审单筛选数据-定时器方法
     */
    function insert_filter_data_timer() {
        $this->begin_trans();
        try {
            $del_sql = "TRUNCATE {$this->table}";
            $ret = $this->db->query($del_sql);
            if ($ret == FALSE) {
                throw new Exception('清空临时表失败');
            }
            //区域筛选
            $area_data = $this->single_filter_inspect_data(0, 1);
            if (!empty($area_data)) {
                $ret = $this->insert_multi($area_data, true);
                if ($ret['status'] != 1) {
                    throw new Exception('按区域筛选失败');
                }
            }
            //条码筛选
            $sku_data = $this->single_filter_inspect_data(1, 1);
            if (!empty($sku_data)) {
                $ret = $this->insert_multi($sku_data, true);
                if ($ret['status'] != 1) {
                    throw new Exception('按商品SKU筛选失败');
                }
            }
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    /**
     * 获取筛选数据
     * @param string $type 筛选方式 0-区域;1-条码
     * @param int $is_timer 是否定时器调用
     * @param array $shop_str 店铺代码
     */
    private function single_filter_inspect_data($type, $is_timer, $shop_str = '') {
        $sql = '';
        $wh = " (sr.must_occupy_inv = '1' AND sr.lock_inv_status = '1') AND sr.is_pending = '0' AND sr.is_problem = '0' AND (sr.pay_status=2 or sr.pay_type='cod') AND sr.order_status = 0 ";
        if ($type == 0) {
            $sql = "SELECT shop_code,receiver_province AS type_val,COUNT(1) AS num FROM oms_sell_record AS sr WHERE {$wh} ";
            if ($is_timer == 0 && !empty($shop_str)) {
                $sql .=" AND shop_code in({$shop_str}) ";
            }
            $sql .= " GROUP BY shop_code,receiver_province ORDER BY shop_code,num DESC";
        } else if ($type == 1) {
            $sql = "SELECT sr.shop_code,rd.sku AS type_val,COUNT(DISTINCT sr.sell_record_code) AS num FROM oms_sell_record AS sr
                    INNER JOIN oms_sell_record_detail rd ON sr.sell_record_code = rd.sell_record_code
                    WHERE {$wh}";
            if ($is_timer == 0 && !empty($shop_str)) {
                $sql .=" AND sr.shop_code in({$shop_str}) ";
            }
            $sql .= " GROUP BY sr.shop_code,rd.sku ORDER BY shop_code,num DESC";
        } else {
            return $this->format_ret(-1, '', '筛选方式不存在');
        }
        $data = $this->filter_inspect_data_select($sql, 'single');
        $this->deal_insert_data($data, $type);
        return $data;
    }

    /**
     * 获取筛选数据-多店铺汇总-精确
     * @param string $type 筛选方式 0-区域;1-条码
     * @param string $shop_str 店铺代码
     */
    private function mutil_filter_inspect_data($type, $shop_str = '') {
        $sql = '';
        $wh = " (sr.must_occupy_inv = '1' AND sr.lock_inv_status = '1') AND sr.is_pending = '0' AND sr.is_problem = '0' AND (sr.pay_status=2 or sr.pay_type='cod') AND sr.order_status = 0 ";
        if ($type == 0) {
            $sql = "SELECT receiver_province AS type_val,COUNT(1) AS num FROM oms_sell_record AS sr WHERE {$wh} ";
            if (!empty($shop_str)) {
                $sql .=" AND shop_code in({$shop_str}) ";
            }
            $sql .= " GROUP BY receiver_province ORDER BY num DESC";
        } else if ($type == 1) {
            $sql = "SELECT rd.sku AS type_val,COUNT(DISTINCT sr.sell_record_code) AS num FROM oms_sell_record AS sr
                    INNER JOIN oms_sell_record_detail rd ON sr.sell_record_code = rd.sell_record_code
                    WHERE {$wh}";
            if (!empty($shop_str)) {
                $sql .=" AND sr.shop_code in({$shop_str}) ";
            }
            $sql .= " GROUP BY rd.sku ORDER BY num DESC";
        } else {
            return $this->format_ret(-1, '', '筛选方式不存在');
        }
        $data = $this->filter_inspect_data_select($sql, 'mutil');
        $this->deal_insert_data($data, $type);
        return $data;
    }

    /**
     * 处理筛选结果数据
     * @param array $data 筛选数据集
     * @param int $type 筛选方式 0-区域;1-条码
     */
    function deal_insert_data(&$data, $type) {
        foreach ($data as $key => &$val) {
            unset($data[$key]['rownum']);
            $val['type'] = $type;
        }
    }

    /**
     * 店铺筛选汇总-核心筛选方法
     * @param string $select 查询数据sql
     * @param string $type single-单店铺汇总；mutil-多店铺汇总（精确）
     */
    private function filter_inspect_data_select($select, $type) {
        if ($type == 'single') {
            $sql = "SELECT @group_row:=CASE WHEN @shop_code=a.shop_code THEN  @group_row+1 ELSE 1 END AS rownum,
                    @shop_code:=a.shop_code AS shop_code,a.type_val,a.num
                    FROM ({$select}) a, (SELECT @group_row:=1,@shop_code:='') AS b HAVING rownum<=10 ORDER BY shop_code,rownum";
        } else {
            $sql = "SELECT @group_row:=@group_row+1 AS rownum,sr.type_val,sr.num
                    FROM ({$select}) a, (SELECT @group_row:=0) AS b HAVING rownum<=10 ORDER BY rownum";
        }
        $data = $this->db->get_all($sql);
        return $data;
    }

    /**
     * 一键确认(审单)
     */
    function mutil_inspect_record($param) {
        $fld = array('shop_code', 'filter_type', 'filter_data');
        foreach ($fld as $val) {
            if (!array_key_exists($val, $param) || empty($param[$val])) {
                return $this->format_ret(-1, '', '请刷新页面重试');
            }
        }

        $filter_data = deal_array_with_quote($param['filter_data']);
        $sql = 'SELECT DISTINCT sr.sell_record_code FROM oms_sell_record AS sr';
        $wh = " (sr.must_occupy_inv = '1' AND sr.lock_inv_status = '1') AND sr.is_pending = '0' AND sr.is_problem = '0' AND (sr.pay_status=2 or sr.pay_type='cod') AND sr.order_status = 0 ";
        if ($param['filter_type'] == 'filter_addr') {
            $sql .= " WHERE {$wh} AND sr.receiver_province IN({$filter_data})";
        } else if ($param['filter_type'] == 'filter_barcode') {
            $sql .= " INNER JOIN oms_sell_record_detail AS rd ON sr.sell_record_code=rd.sell_record_code WHERE {$wh} AND rd.sku IN({$filter_data})";
        } else {
            return $this->format_ret(-1, '', '一键确认出错,请刷新页面重试');
        }

        $filter_shop_code = $param['shop_code'] == 'all' ? null : $param['shop_code'];
        $sql .= load_model('base/ShopModel')->get_sql_purview_shop('sr.shop_code', $filter_shop_code);

        $record_code_arr = $this->db->get_all($sql);
        $record_code_arr = array_column($record_code_arr, 'sell_record_code');
        $record_code_str = implode(',', $record_code_arr);
        $ret = load_model('oms/SellRecordOptModel')->new_opt_confirm($record_code_str);
        return $ret;
    }

}
