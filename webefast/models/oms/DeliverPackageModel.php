<?php

require_model('tb/TbModel');

/**
 * 多包裹验货（后置打单）
 *
 * @author WMH
 */
class DeliverPackageModel extends TbModel {

    protected $table = 'oms_deliver_record_package';
    protected $detail_table = 'oms_deliver_package_detail';

    /**
     * 获取包裹单
     * @param array $filter 过滤条件
     * @return array 结果集
     */
    public function get_package_by_page($filter) {
        $select = 'rp.package_record_id,rp.waves_record_id,rp.sell_record_code,rp.package_no,rp.express_code,rp.express_no,rp.goods_num,rp.scan_num,rp.packet_status,rp.print_status,rp.print_time,COUNT(pd.id) AS detail_count';
        $sql_join = "LEFT JOIN {$this->detail_table} AS pd ON rp.package_record_id=pd.package_record_id AND rp.sell_record_code=pd.sell_record_code AND rp.package_no=pd.package_no";
        $sql_main = "FROM {$this->table} AS rp {$sql_join} WHERE 1=1";
        $sql_values = array();

        if (empty($filter['sell_record_code']) || empty($filter['waves_record_id'])) {
            return array();
        } else {
            $sql_main .= ' AND rp.sell_record_code=:sell_record_code';
            $sql_values[':sell_record_code'] = $filter['sell_record_code'];

            $sql_main .= ' AND rp.waves_record_id=:waves_record_id';
            $sql_values[':waves_record_id'] = $filter['waves_record_id'];
        }

        //包裹序号
        if (isset($filter['package_no']) && $filter['package_no'] != '') {
            $sql_main .= ' AND rp.package_no=:package_no';
            $sql_values[':package_no'] = $filter['package_no'];
        }
        $sql_main .= ' GROUP BY rp.sell_record_code,rp.package_no,rp.waves_record_id';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, TRUE);
        if (!empty($data['data'])) {
            $sql = 'SELECT express_name FROM base_express WHERE express_code=:express_code';
            $express_name = $this->db->get_value($sql, array(':express_code' => $data['data'][0]['express_code']));

            foreach ($data['data'] as &$row) {
                $row['express_name'] = "{$express_name}[{$row['express_code']}]";
                $row['print_time'] = empty($row['print_time']) ? '' : date('Y-m-d H:i:s', $row['print_time']);
                $row['print_status_txt'] = $row['print_status'] == 1 ? '已打印' : '未打印';

                if ($row['detail_count'] < 1) {
                    $row['is_allow_delete'] = 1;
                } else {
                    $row['is_allow_delete'] = 0;
                }
                unset($row['detail_count']);
            }
        }

        return $this->format_ret(1, $data);
    }

    /**
     * 获取包裹单明细
     * @param array $filter 过滤条件
     * @return array 结果集
     */
    public function get_package_detail_by_page($filter) {
        $select = "pd.package_record_id,pd.sell_record_code,pd.package_no,pd.sku,pd.goods_num,pd.scan_num,gs.barcode,CONCAT_WS('；',spec1_name,spec2_name) AS spec,bg.goods_name,bg.goods_code";
        $sql = "SELECT {$select} FROM {$this->detail_table} AS pd
                INNER JOIN goods_sku AS gs ON pd.sku=gs.sku INNER JOIN base_goods AS bg ON gs.goods_code=bg.goods_code WHERE 1=1";
        $sql_values = array();
        if (empty($filter['sell_record_code'])) {
            return array();
        } else {
            $sql .= ' AND pd.sell_record_code=:sell_record_code';
            $sql_values[':sell_record_code'] = $filter['sell_record_code'];
        }
        if (!empty($filter['package_no'])) {
            $sql .= ' AND pd.package_no=:package_no';
            $sql_values[':package_no'] = $filter['package_no'];
        }

        $detail = $this->db->get_all($sql, $sql_values);
        return $detail;
    }

    /**
     * 检查扫描订单
     * @param string $sell_code 订单号
     * @return array
     */
    public function check_scan_record($sell_code) {
        $sql = 'SELECT deliver_record_id,sell_record_code,store_code,shop_code,goods_num,express_code,express_no,express_data,waves_record_id,is_deliver 
                FROM oms_deliver_record WHERE is_cancel=0 AND sell_record_code=:code';
        $record = $this->db->get_row($sql, array(':code' => $sell_code));
        if (empty($record)) {
            return $this->format_ret(-1, '', '波次订单不存在');
        }

        $status = load_model('mid/MidBaseModel')->check_is_mid('scan', 'sell_record', $record['store_code']);
        if ($status !== false) {
            return $this->format_ret(-1, '', '仓库对接' . $status . '，不允许手工发货');
        }
        if ($record['is_deliver'] == 1) {
            return $this->format_ret(-1, '', '订单已发货');
        }
        if (empty($record['express_code'])) {
            return $this->format_ret(-1, '', '订单未设置配送方式');
        }

        $sql = "SELECT count(1) FROM oms_sell_return WHERE sell_record_code=:code AND return_order_status<>3 ";
        $return_check = $this->db->get_value($sql, array(':code' => $sell_code));
        if ($return_check > 0) {
            return $this->format_ret(-1, '', '订单已发生退货');
        }
        $sql = "SELECT shipping_status FROM oms_sell_record WHERE sell_record_code=:code AND order_status <> 3 ";
        $shipping_status = $this->db->get_value($sql, array(':code' => $sell_code));
        if (empty($shipping_status)) {
            return $this->format_ret(-1, '', '订单已作废');
        }
        if ($shipping_status != 3) {
            return $this->format_ret(-1, '', '订单未完成拣货');
        }

        // 对应波次单是否已验收
        $sql = "SELECT * FROM oms_waves_record WHERE waves_record_id=:id";
        $waves_data = $this->db->get_row($sql, array(':id' => $record['waves_record_id']));
        if (empty($waves_data)) {
            return $this->format_ret(-1, '', '对应波次单不存在');
        }
        if ($waves_data['is_accept'] != 1) {
            return $this->format_ret(-1, '', '波次单未验收');
        }

        $sql = "SELECT COUNT(1) FROM oms_deliver_record_detail WHERE deliver_record_id=:id";
        $detail_count = $this->db->get_value($sql, array(':id' => $record['deliver_record_id']));
        if ($detail_count < 1) {
            return $this->format_ret(-1, '', '订单明细为空');
        }

        $sql = "SELECT COUNT(1) FROM {$this->table} WHERE is_multi_examine=0 AND sell_record_code=:code";
        $empty_package = $this->db->get_value($sql, array(':code' => $sell_code));
        if ($empty_package > 0) {
            return $this->format_ret(-1, '', '订单存在空包裹');
        }

        $check_cloud_print = load_model('oms/DeliverRecordModel')->check_express_type($record['deliver_record_id'], 0);
        if ($check_cloud_print['status'] < 1) {
            $ret_express = load_model('base/ShippingModel')->table_get_by_field('base_express', 'express_code', $record['express_code'], 'express_name');
            $check_cloud_print['message'] = "订单绑定的快递[ {$ret_express['data']['express_name']} ]未使用云打印";
            return $check_cloud_print;
        }

        $package_data = $this->get_package_data($sell_code);

        $return_data = array(
            'deliver_record_id' => $record['deliver_record_id'],
            'sell_record_code' => $record['sell_record_code'],
            'waves_record_id' => $record['waves_record_id'],
            'express_code' => $record['express_code'],
            'goods_num' => (int)$record['goods_num'],
        );
        $return_data = array_merge($return_data, $package_data);

        load_model('oms/SellRecordActionModel')->add_action($sell_code, '扫描出库', '多包裹验货扫描订单号');

        return $this->format_ret(1, $return_data);
    }

    /**
     * 获取订单包裹数据
     * @param string $sell_code 订单号
     * @return array
     */
    public function get_package_data($sell_code) {
        $package_num = 0;
        $scan_num = 0;
        $next_scan_package_no = 0;
        $sql = "SELECT package_no,scan_num,packet_status FROM {$this->table} WHERE sell_record_code=:code ORDER BY package_no ASC";
        $package_data = $this->db->get_all($sql, array(':code' => $sell_code));
        if (!empty($package_data)) {
            foreach ($package_data as $row) {
                $scan_num += $row['scan_num'];
                $package_num++;

                //获取下一个需要扫描的包裹号
                if ($row['packet_status'] == 0 && $next_scan_package_no == 0) {
                    $next_scan_package_no = $row['package_no'];
                }
            }
        }

        return array('package_num' => $package_num, 'scan_num' => $scan_num, 'package_no' => $next_scan_package_no);
    }

    /**
     * 获取未封包的第一个包裹
     * @param array $params
     * @return array
     */
    public function get_no_packet_first_package($params) {
        $sql = "SELECT package_no FROM {$this->table} WHERE packet_status=0 AND sell_record_code=:code AND waves_record_id=:id ORDER BY package_no";
        return $this->db->get_value($sql, array(':id' => $params['waves_record_id'], ':code' => $params['sell_record_code']));
    }

    /**
     * 检查订单状态，用于扫描过程中的操作校验
     * @param string $sell_code 订单号
     * @return array
     */
    public function check_sell_record($sell_code) {
        $sql = 'SELECT dr.deliver_record_id,dr.sell_record_code,dr.express_code,dr.waves_record_id,dr.is_deliver,sr.order_status 
                FROM oms_deliver_record AS dr,oms_sell_record AS sr WHERE dr.sell_record_code=sr.sell_record_code 
                AND dr.is_cancel=0 AND dr.sell_record_code=:code';
        $record = $this->db->get_row($sql, array(':code' => $sell_code));
        if (empty($record)) {
            return $this->format_ret(-1, '', '订单不存在');
        }
        if (empty($record['order_status']) == 3) {
            return $this->format_ret(-1, '', '订单已作废');
        }
        if ($record['is_deliver'] == 1) {
            return $this->format_ret(-1, '', '订单已发货');
        }
        return $this->format_ret(1, $record);
    }

    /**
     * 检查包裹是否存在
     * @param string $sell_code 订单号
     * @param int $package_no 包裹号
     * @return array
     */
    public function check_package_exists($sell_code, $package_no) {
        $sql = "SELECT COUNT(1) FROM {$this->table} WHERE sell_record_code=:code AND package_no=:package_no";
        return $this->db->get_value($sql, array(':code' => $sell_code, ':package_no' => $package_no));
    }

    /**
     * 检查空包裹
     * @param string $sell_code 订单号
     * @param int $package_no 包裹号
     * @return array
     */
    public function check_empty_package($sell_code, $package_no) {
        $sql = "SELECT COUNT(1) FROM {$this->detail_table} WHERE sell_record_code=:code AND package_no=:package_no";
        return $this->db->get_value($sql, array(':code' => $sell_code, ':package_no' => $package_no));
    }

    /**
     * 获取包裹数据
     * @param string $sell_code 订单号
     * @param int $package_no 包裹号
     * @return array
     */
    public function get_package_record($sell_code, $package_no) {
        $sql = "SELECT sell_record_code,express_code,express_no,express_data,package_no,waves_record_id,goods_num,scan_num,packet_status,print_status
                FROM {$this->table} WHERE sell_record_code=:code AND package_no=:package_no";
        return $this->db->get_row($sql, array(':code' => $sell_code, ':package_no' => $package_no));
    }

    /**
     * 获取包裹明细数据
     * @param string $sell_code 订单号
     * @param int $package_no 包裹号
     * @return array
     */
    public function get_package_detail($sell_code, $package_no) {
        $sql = "SELECT sell_record_code,package_no,sku,goods_num,scan_num FROM {$this->detail_table} 
                WHERE sell_record_code=:code AND package_no=:package_no";
        return $this->db->get_all($sql, array(':code' => $sell_code, ':package_no' => $package_no));
    }

}
