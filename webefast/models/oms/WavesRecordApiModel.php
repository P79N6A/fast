<?php

require_model('tb/TbModel');
require_lang('oms');

/**
 * 波次单接口
 * @author WMH
 */
class WavesRecordApiModel extends TbModel {

    protected $table = 'oms_waves_record';

    /**
     * 获取波次单汇总
     * @author wmh
     * @date 2017-05-03
     * @param array $param 接口参数
     * <pre> 可选 'start_time',end_time',is_accept',store_code'
     * @return array 操作结果
     */
    public function api_wave_census_get($param) {
        $key_option = array(
            's' => array('start_time', 'end_time', 'store_code'),
            'i' => array('is_accept')
        );
        $r_option = array();
        $ret_option = valid_assign_array($param, $key_option, $r_option);

        $sql = "SELECT dr.is_deliver,wr.is_accept FROM oms_deliver_record AS dr 
                INNER JOIN {$this->table} AS wr ON dr.waves_record_id=wr.waves_record_id
                WHERE wr.is_cancel=0 AND dr.is_cancel=0";
        $sql_values = array();
        //仓库代码
        if (isset($r_option['store_code']) && $r_option['store_code'] != '') {
            $sql .= ' AND dr.store_code=:store_code';
            $sql_values[':store_code'] = $r_option['store_code'];
        }
        //验收状态
        if (isset($r_option['is_accept']) && $r_option['is_accept'] != '') {
            if (!in_array($r_option['is_accept'], array(0, 1))) {
                return $this->format_ret(-10002, array('is_accept' => $r_option['is_accept']), '参数值错误');
            }
            $sql .= ' AND wr.is_accept=:is_accept';
            $sql_values[':is_accept'] = $r_option['is_accept'];
        }
        //波次单时间
        if (!empty($r_option['start_time'])) {
            $start_time = strtotime($r_option['start_time']);
            if ($start_time === FALSE) {
                return $this->format_ret(-10005, array('start_time' => $r_option['start_time']), '时间格式错误');
            }
        } else {
            $r_option['start_time'] = date("Y-m-d H:i:s", strtotime("today"));
        }
        if (!empty($r_option['end_time'])) {
            $start_time = strtotime($r_option['end_time']);
            if ($start_time === FALSE) {
                return $this->format_ret(-10005, array('end_time' => $r_option['end_time']), '时间格式错误');
            }
        } else {
            $r_option['end_time'] = date("Y-m-d H:i:s", strtotime("today +1 days"));
        }

        $sql.=" AND wr.record_time >= :start_time";
        $sql_values[':start_time'] = $r_option['start_time'];

        $sql.=" AND wr.record_time < :end_time";
        $sql_values[':end_time'] = $r_option['end_time'];

        $data = $this->db->get_all($sql, $sql_values);

        $picking_num = 0; //待拣货订单数
        $picked_shipping_num = 0; //已拣货未发货订单数
        $shipping_num = 0; //未发货订单数
        $shipped_num = 0; //已发货订单数
        foreach ($data as $val) {
            if ($val['is_deliver'] == 0) {
                $shipping_num++;
            }
            if ($val['is_accept'] == 0 && $val['is_deliver'] == 0) {
                $picking_num++;
            }
            if ($val['is_accept'] == 1 && $val['is_deliver'] == 0) {
                $picked_shipping_num++;
            }
            if ($val['is_deliver'] == 1) {
                $shipped_num++;
            }
        }
        $return_data = array(
            'picking_num' => $picking_num,
            'picked_shipping_num' => $picked_shipping_num,
            'shipping_num' => $shipping_num,
            'shipped_num' => $shipped_num,
        );
        return $this->format_ret(1, $return_data);
    }

    /**
     * 获取波次单数据
     * @author wmh
     * @date 2017-05-03
     * @param array $param 接口参数
     * <pre> 必选 'record_type','picker_code'
     * <pre> 可选 'record_code', 'start_time', 'end_time', 'store_code'
     * @return array 操作结果
     */
    public function api_wave_info_get($param) {
        $key_required = array(
            's' => array('picker_code'),
            'i' => array('record_type')
        );
        $key_option = array(
            's' => array('record_code', 'start_time', 'end_time', 'store_code'),
        );

        $r_required = array();
        $ret_required = valid_assign_array($param, $key_required, $r_required, TRUE);
        if ($ret_required['status'] !== TRUE) {
            return $this->format_ret(-10001, $ret_required['req_empty'], 'API_RETURN_MESSAGE_10001');
        }

        $r_option = array();
        $ret_option = valid_assign_array($param, $key_option, $r_option);
        $arr_deal = array_merge($r_required, $r_option);

        $picker_name = $this->get_user_name_by_code($arr_deal['picker_code']);
        if (empty($picker_name)) {
            return $this->format_ret(-10002, array('picker_code' => $arr_deal['picker_code']), '拣货员不存在');
        }

        $sql_values = array();
        //波次单类型
        $record_type = explode(',', $arr_deal['record_type']);
        $sell_num_type = $this->arr_to_in_sql_value($record_type, 'sell_num_type', $sql_values);

        $sql = "SELECT wr.record_code,wr.record_time,wr.sell_record_count,wr.goods_count,wr.picker AS picker_code,wr.express_code,wr.sell_num_type AS record_type FROM {$this->table} wr WHERE wr.is_accept=0 AND is_cancel=0 AND sell_num_type IN({$sell_num_type}) AND (wr.picker=:picker OR wr.picker='')";
        $sql_values[':picker'] = $arr_deal['picker_code'];
        //单据编码
        if (isset($arr_deal['record_code']) && $arr_deal['record_code'] != '') {
            $sql .= ' AND wr.record_code=:record_code';
            $sql_values[':record_code'] = $arr_deal['record_code'];
        }
        //仓库代码
        if (isset($arr_deal['store_code']) && $arr_deal['store_code'] != '') {
            $sql .= ' AND wr.store_code=:store_code';
            $sql_values[':store_code'] = $arr_deal['store_code'];
        }

        //波次单时间
        if (!empty($arr_deal['start_time'])) {
            $start_time = strtotime($arr_deal['start_time']);
            if ($start_time === FALSE) {
                return $this->format_ret(-10005, array('start_time' => $arr_deal['start_time']), '时间格式错误');
            }
        } else {
            $arr_deal['start_time'] = date("Y-m-d H:i:s", strtotime("today"));
        }
        if (!empty($arr_deal['end_time'])) {
            $start_time = strtotime($arr_deal['end_time']);
            if ($start_time === FALSE) {
                return $this->format_ret(-10005, array('end_time' => $arr_deal['end_time']), '时间格式错误');
            }
        } else {
            $arr_deal['end_time'] = date("Y-m-d H:i:s", strtotime("today +1 days"));
        }

        $sql.=" AND wr.record_time >= :start_time";
        $sql_values[':start_time'] = $arr_deal['start_time'];

        $sql.=" AND wr.record_time < :end_time";
        $sql_values[':end_time'] = $arr_deal['end_time'];

        $data = $this->db->get_all($sql, $sql_values);
        filter_fk_name($data, array('express_code|express'));
        foreach ($data as &$val) {
            $val['picker_name'] = ($val['picker_code'] == $arr_deal['picker_code']) ? $picker_name : '';
            $val['express_name'] = $val['express_code_name'];
            unset($val['express_code_code'], $val['express_code_name']);
        }

        return $this->format_ret(1, $data);
    }

    /**
     * 获取波次单商品
     * @author wmh
     * @date 2017-05-04
     * @param array $param 接口参数
     * <pre> 必选 'record_code','picker_code
     * @return array 操作结果
     */
    public function api_wave_goods_get($param) {
        $key_required = array(
            's' => array('record_code', 'picker_code')
        );
        $r_required = array();
        $ret_required = valid_assign_array($param, $key_required, $r_required, TRUE);
        if ($ret_required['status'] !== TRUE) {
            return $this->format_ret(-10001, $ret_required['req_empty'], 'API_RETURN_MESSAGE_10001');
        }
        $msg = array('record_code' => $r_required['record_code']);

        $wave_data = $this->get_row($msg);
        $wave_data = $wave_data['data'];
        if (empty($wave_data)) {
            return $this->format_ret(-10002, $msg, '波次单不存在');
        }
        if ($wave_data['is_accept'] == 1) {
            return $this->format_ret(-1, $msg, '波次单已验收');
        }
        if ($wave_data['is_cancel'] == 1) {
            return $this->format_ret(-10006, $msg, '波次单已取消');
        }
        //更新拣货员
        $picker_name = $this->get_user_name_by_code($r_required['picker_code']);
        if (empty($picker_name)) {
            return $this->format_ret(-10002, array('picker_code' => $r_required['picker_code']), '拣货员不存在');
        }
        $this->update(array('picker' => $r_required['picker_code']), $msg);

        //获取波次下订单
        $sql = "SELECT dr.sell_record_code FROM {$this->table} AS wr INNER JOIN oms_deliver_record AS dr ON wr.waves_record_id=dr.waves_record_id WHERE wr.record_code=:record_code AND dr.is_cancel=0 AND dr.is_deliver=0";
        $sell_record_arr = $this->db->get_all_col($sql, [':record_code'=>$r_required['record_code']]);
        if (empty($sell_record_arr)) {
            return $this->format_ret(-10002, $msg, '波次下不存在需要拣货的订单');
        }
        $sql_values = array(':waves_record_id' => $wave_data['waves_record_id']);
        $sell_record_str = $this->arr_to_in_sql_value($sell_record_arr, 'sell_record_code', $sql_values);

        $sql = "SELECT bg.goods_code,bg.goods_name,gs.spec1_name,gs.spec2_name,gs.barcode,gs.sku,SUM(rd.num) AS num FROM oms_deliver_record_detail AS rd INNER JOIN goods_sku AS gs ON rd.sku=gs.sku INNER JOIN base_goods AS bg ON rd.goods_code=bg.goods_code WHERE rd.sell_record_code IN({$sell_record_str}) AND rd.waves_record_id=:waves_record_id GROUP BY rd.sku";
        $goods = $this->db->get_all($sql, $sql_values);
        if (empty($goods)) {
            return $this->format_ret(-10002, $msg, '波次单不存在需拣货的商品');
        }

        $sql_values = array(':store_code' => $wave_data['store_code']);
        $sku_arr = array_column($goods, 'sku');
        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
        $sql = "SELECT gs.sku,gs.shelf_code,bs.shelf_name FROM goods_shelf AS gs,base_shelf AS bs WHERE gs.shelf_code=bs.shelf_code AND  gs.store_code=bs.store_code AND gs.store_code=:store_code AND gs.sku IN({$sku_str}) ORDER BY gs.shelf_code";
        $shelf_data = $this->db->get_all($sql, $sql_values);
        $shelf = array();

        foreach ($shelf_data as $val) {
            $sku = $val['sku'];
            unset($val['sku']);
            $shelf[$sku][] = $val;
        }

        $return_goods = array();

        //临时修改，组织新数组按库存顺序组织商品
        foreach ($shelf as $key => $val) {
            $check = false;

            foreach ($goods as $row) {
                if ($row['sku'] == $key) {
                    $row['goods_spec'] = $row['spec1_name'] . '，' . $row['spec2_name'];
                    $row['shelf'] = $val;
                    $return_goods[] = $row;
                }
            }
        }

        //临时修改，处理无库位商品
        foreach ($goods as $row) {

            $check = false;

            foreach ($return_goods as $val) {
                if ($val['sku'] == $row['sku']) {
                    $check = true;
                }
            }

            if (!$check) {
                $row['goods_spec'] = $row['spec1_name'] . '，' . $row['spec2_name'];
                $row['shelf'] = array();
                $return_goods[] = $row;
            }
        }

        /*
          foreach ($shelf_data as $val) {
          $sku = $val['sku'];
          unset($val['sku']);
          $shelf[$sku][] = $val;
          }
          foreach ($goods as &$row) {
          $sku = $row['sku'];
          $row['goods_spec'] = $row['spec1_name'] . '，' . $row['spec2_name'];
          $row['shelf'] = isset($shelf[$sku]) ? $shelf[$sku] : array();
          unset($row['spec1_name'], $row['spec2_name'], $row['sku']);
          } */

        $return_data = array(
            'record_code' => $wave_data['record_code'],
            'record_type' => $wave_data['sell_num_type'],
            'detail' => $return_goods,
        );
        return $this->format_ret(1, $return_data);
    }

    /**
     * 波次单验收
     * @author wmh
     * @date 2017-05-03
     * @param array $param 接口参数
     * <pre> 必选 'record_code'
     * @return array 操作结果
     */
    public function api_wave_accept($param) {
        $key_required = array(
            's' => array('record_code', 'picker_code')
        );
        $r_required = array();
        $ret_required = valid_assign_array($param, $key_required, $r_required, TRUE);
        if ($ret_required['status'] !== TRUE) {
            return $this->format_ret(-10001, $ret_required['req_empty'], 'API_RETURN_MESSAGE_10001');
        }

        $sql = "SELECT waves_record_id FROM {$this->table} WHERE record_code=:record_code";
        $waves_record_id = $this->db->get_value($sql, array(':record_code' => $r_required['record_code']));
        if (empty($waves_record_id)) {
            return $this->format_ret(-10002, array('record_code' => $ret_required['record_code']), '波次单不存在');
        }
        $picker_name = $this->get_user_name_by_code($r_required['picker_code']);
        if (empty($picker_name)) {
            return $this->format_ret(-10002, array('picker_code' => $r_required['picker_code']), '拣货员不存在');
        }

        load_model('oms/SellRecordActionModel')->record_log_check = 0;
        $ret = load_model('oms/WavesRecordModel')->accept($waves_record_id, $picker_name);
        load_model('oms/SellRecordActionModel')->record_log_check = 1;
        return $ret;
    }

    /**
     * 获取待发货订单
     * @author wmh
     * @date 2017-05-11
     * @param array $param 接口参数
     * <pre> 必选 'record_code','picker_code
     * @return array 操作结果
     */
    public function api_wave_shipping_get($param) {
        $key_required = array(
            's' => array('record_code', 'picker_code')
        );
        $r_required = array();
        $ret_required = valid_assign_array($param, $key_required, $r_required, TRUE);
        if ($ret_required['status'] !== TRUE) {
            return $this->format_ret(-10001, $ret_required['req_empty'], 'API_RETURN_MESSAGE_10001');
        }

        $picker_name = $this->get_user_name_by_code($r_required['picker_code']);
        if (empty($picker_name)) {
            return $this->format_ret(-10002, array('picker_code' => $r_required['picker_code']), '拣货员不存在');
        }

        $filter = array('record_code' => $r_required['record_code']);
        $wave_data = $this->get_row($filter);
        $wave_data = $wave_data['data'];
        if (empty($wave_data)) {
            return $this->format_ret(-10002, $filter, '波次单不存在');
        }
        if ($wave_data['is_accept'] == 0) {
            return $this->format_ret(-1, $filter, '波次单未验收');
        }
        if ($wave_data['is_deliver'] == 1) {
            return $this->format_ret(-1, $filter, '波次单已发货');
        }
        if ($wave_data['is_cancel'] == 1) {
            return $this->format_ret(-10006, $filter, '波次单已取消');
        }

        //获取波次下订单
        $sql = "SELECT dr.sell_record_code,dr.express_code,be.express_name,dr.express_no,dr.goods_num,dr.sort_no FROM oms_deliver_record AS dr LEFT JOIN base_express AS be ON dr.express_code=be.express_code WHERE dr.is_cancel=0 AND dr.is_deliver=0 AND dr.waves_record_id=:waves_record_id";
        $sell_record_data = $this->db->get_all($sql, array('waves_record_id' => $wave_data['waves_record_id']));
        if (empty($sell_record_data)) {
            return $this->format_ret(-10002, $filter, '波次下不存在待发货订单');
        }
        $sell_record_data = load_model('util/ViewUtilModel')->get_map_arr($sell_record_data, 'sell_record_code');

        //获取波次订单商品
        $sql_values = array(':waves_record_id' => $wave_data['waves_record_id']);
        $sell_record_arr = array_column($sell_record_data, 'sell_record_code');
        $sell_record_str = $this->arr_to_in_sql_value($sell_record_arr, 'sell_record_code', $sql_values);
        $sql = "SELECT rd.sell_record_code,rd.goods_code,bg.goods_name,gs.spec1_name,gs.spec2_name,gs.barcode,rd.sku,rd.num FROM oms_deliver_record_detail AS rd INNER JOIN goods_sku AS gs ON rd.sku=gs.sku INNER JOIN base_goods AS bg ON rd.goods_code=bg.goods_code WHERE rd.sell_record_code IN({$sell_record_str}) AND rd.waves_record_id=:waves_record_id";
        $goods = $this->db->get_all($sql, $sql_values);
        if (empty($goods)) {
            return $this->format_ret(-10002, $filter, '波次单不存在待发货订单商品');
        }

        $record_data = array();
        foreach ($goods as $row) {
            $sell_code = $row['sell_record_code'];
            $row['goods_spec'] = $row['spec1_name'] . '，' . $row['spec2_name'];
            unset($row['sell_record_code'], $row['spec1_name'], $row['spec2_name'], $row['sku']);

            if (!isset($sell_record_data[$sell_code])) {
                continue;
            }

            if (isset($record_data[$sell_code])) {
                $record_data[$sell_code]['detail'][] = $row;
            } else {
                $record_data[$sell_code] = $sell_record_data[$sell_code];
                $record_data[$sell_code]['detail'][] = $row;
            }
        }

        $sell_record_count = count($sell_record_data);
        $goods_count = array_sum(array_column($sell_record_data, 'goods_num'));

        $record_data = array_values($record_data);
        $return_data = array(
            'record_code' => $wave_data['record_code'],
            'picker_code' => $r_required['picker_code'],
            'picker_name' => $picker_name,
            'record_type' => $wave_data['sell_num_type'],
            'sell_record_count' => $sell_record_count,
            'goods_count' => $goods_count,
            'record' => $record_data,
        );
        return $this->format_ret(1, $return_data);
    }

    /**
     * 波次单发货
     * @author wmh
     * @date 2017-05-11
     * @param array $param 接口参数
     * <pre> 必选 'record_code'
     * @return array 操作结果
     */
    public function api_wave_send($param) {
        $key_required = array(
            's' => array('record_code', 'picker_code')
        );
        $r_required = array();
        $ret_required = valid_assign_array($param, $key_required, $r_required, TRUE);
        if ($ret_required['status'] !== TRUE) {
            return $this->format_ret(-10001, $ret_required['req_empty'], 'API_RETURN_MESSAGE_10001');
        }

        $picker_name = $this->get_user_name_by_code($r_required['picker_code']);
        if (empty($picker_name)) {
            return $this->format_ret(-10002, array('picker_code' => $r_required['picker_code']), '拣货员不存在');
        }

        $obj_wave = load_model('oms/WavesRecordModel');
        $ret = $obj_wave->get_waves_send_sell_record($r_required['record_code'], '', FALSE);
        if ($ret['status'] < 1) {
            return $ret;
        }

        $return_data = array();
        $opt_user = array(
            'user_code' => $r_required['picker_code'],
            'user_name' => $picker_name,
            'user_source' => 'PDA'
        );
        $delivery_record = explode(',', $ret['data']);

        $obj_action = load_model('oms/SellRecordActionModel');
        $obj_action->record_log_check = 0;
        foreach ($delivery_record as $code) {
            $ret_send = $obj_wave->waves_send_sell_record($code, $opt_user);
            if ($ret_send < 1) {
                $ret_send['message'] = $ret_send['data'];
            }
            $ret_send['data'] = array('sell_record_code' => $code);
            $return_data[] = $ret_send;
        }
        $obj_action->record_log_check = 1;
        return $this->format_ret(1, $return_data);
    }

    private function get_user_name_by_code($user_code) {
        $sql = 'SELECT user_name FROM sys_user WHERE user_code=:user_code';
        return $this->db->get_value($sql, array(':user_code' => $user_code));
    }

    /**
     * 获取菜鸟电子面单号
     * @author wmh
     * @date 2017-05-04
     * @param array $param 接口参数
     * <pre> 必选 'sell_record_code'
     * @return array 操作结果
     */
    public function api_cainiao_waybill_get($param) {
        if (empty($param['sell_record_code'])) {
            return $this->format_ret(-10001, array('sell_record_code'), 'API_RETURN_MESSAGE_10001');
        }
        $sell_record_code = $param['sell_record_code'];
        $sql = 'SELECT sr.order_status,sr.shipping_status,sr.is_problem,SUM(rd.api_refund_num) AS api_refund_num FROM oms_sell_record sr,oms_sell_record_detail rd WHERE sr.sell_record_code=rd.sell_record_code AND sr.sell_record_code=:sell_record_code GROUP BY sr.sell_record_code';
        $record_info = $this->db->get_row($sql, array('sell_record_code' => $sell_record_code));
        if (empty($record_info)) {
            return $this->format_ret(-10002, array('sell_record_code' => $sell_record_code), '订单不存在');
        }
        if ($record_info['is_problem'] == 1 && $record_info['api_refund_num'] > 0) {
            return $this->format_ret(-2, ['sell_record_code' => $sell_record_code], '该订单存在退单');
        }
        $return_num = $this->db->get_value('SELECT COUNT(1) FROM oms_sell_return WHERE sell_record_code=:sell_record_code', ['sell_record_code' => $sell_record_code]);
        if ($return_num > 0) {
            return $this->format_ret(-2, ['sell_record_code' => $sell_record_code], '该订单存在退单');
        }

        if ($record_info['order_status'] == 3) {
            return $this->format_ret(-2, ['sell_record_code' => $sell_record_code], '该订单已作废');
        }
        if ($record_info['order_status'] < 1) {
            return $this->format_ret(-2, array('sell_record_code' => $sell_record_code), '订单未确认');
        }
        $sql = "SELECT deliver_record_id,waves_record_id,sell_record_code,express_code,express_no,express_data FROM oms_deliver_record WHERE is_cancel=0 AND sell_record_code=:sell_record_code";
        $deliver_info = $this->db->get_row($sql, array('sell_record_code' => $sell_record_code));
        if (empty($deliver_info)) {
            return $this->format_ret(-10002, array('sell_record_code' => $sell_record_code), '发货单不存在');
        }
        $result = array();
        if (!empty($deliver_info['express_no']) && !empty($deliver_info['express_data'])) {
            $result = $this->format_ret(1, array('express_code' => $deliver_info['express_code'], 'express_no' => $deliver_info['express_no'], 'print_data' => $deliver_info['express_data']), '已获取菜鸟物流单号');
        } else {
            $result = load_model('oms/DeliverRecordModel')->cn_wlb_waybill_get($deliver_info['waves_record_id'], $deliver_info['deliver_record_id'], 'api');
            if ($result['status'] < 1) {
                return $result;
            }
        }
        $ret = load_model('oms/DeliverRecordModel')->get_combine_print_data(array($deliver_info['deliver_record_id']));
        $result['data']['print_info'] = $ret['print_data_json'];

        return $result;
    }

    /**
     * 二次分拣-波次单商品分拣-波次扫描
     * @author wmh
     * @date 2017-06-14
     * @param array $param 接口参数
     * @return array 扫描波次数据
     */
    public function api_wave_record_scan($param) {
        if (empty($param['record_code'])) {
            return $this->format_ret(-10001, array('record_code'), 'API_RETURN_MESSAGE_10001');
        }
        $record_code = $param['record_code'];
        $ret = load_model('oms/DeliverRecordModel')->get_record_by_record_waves($param['record_code'], 0);
        if ($ret['status'] < 1) {
            if (strpos($ret['message'], '波次单已取消') !== FALSE) {
                $ret['status'] = -10006;
            }
            return $ret;
        }
        $wave_data = $ret['data'];

        $revert_data = array(
            'record_code' => $record_code,
            'goods_num' => $wave_data['valide_goods_count'],
            'pick_num' => $wave_data['sum_picking_num'],
        );
        return $this->format_ret(1, $revert_data, '扫描成功');
    }

    /**
     * 二次分拣-波次单商品分拣-波次条码扫描
     * @author wmh
     * @date 2017-06-14
     * @param array $param 接口参数
     * @return array 扫描波次商品数据
     */
    public function api_wave_barcode_scan($param) {
        $key_required = array(
            's' => array('record_code', 'barcode'),
        );

        $r_required = array();
        $ret_required = valid_assign_array($param, $key_required, $r_required, TRUE);
        if ($ret_required['status'] !== TRUE) {
            return $this->format_ret(-10001, $ret_required['req_empty'], 'API_RETURN_MESSAGE_10001');
        }

        $ret = load_model('oms/DeliverRecordModel')->get_check_barcode_data($r_required);
        if ($ret['status'] < 1) {
            if (strpos($ret['message'], '波次单已取消') !== FALSE) {
                $ret['status'] = -10006;
            }
            return $ret;
        }
        $wave_data = $ret['data'];

        $sql = "SELECT bg.goods_code,bg.goods_name,CONCAT_WS('；',gs.spec1_name,gs.spec2_name) AS goods_spec,gs.barcode 
                FROM goods_sku AS gs INNER JOIN base_goods AS bg ON gs.goods_code = bg.goods_code
                WHERE gs.barcode = :barcode";
        $goods_data = $this->db->get_row($sql, array(':barcode' => $wave_data['barcode']));
        $revert_data = array(
            'record_code' => $wave_data['record_code'],
            'record_no' => $wave_data['sort_no'],
            'goods_num' => $wave_data['valide_goods_count'],
            'pick_num' => $wave_data['sum_picking_num'],
            'goods_code' => $goods_data['goods_code'],
            'goods_name' => $goods_data['goods_name'],
            'goods_spec' => $goods_data['goods_spec'],
            'barcode' => $goods_data['barcode'],
        );

        return $this->format_ret(1, $revert_data, '扫描成功');
    }

}
