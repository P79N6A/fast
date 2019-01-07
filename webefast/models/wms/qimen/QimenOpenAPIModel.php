<?php

require_model('wms/qimen/QimenAPIModel');

class QimenOpenAPIModel extends QimenAPIModel {

    protected $db;
    private $log_id = 0;
    private $req_data_xml;
    private $log_unid = '';
    private $is_lof = 0;
    //wms类型
    private $wms_sys_type = NULL;

    function __construct($token = array()) {
        parent::__construct($token);
        $this->db = CTX()->db;
        $lof_status = load_model('sys/SysParamsModel')->get_val_by_code('lof_status');
        $this->is_lof = $lof_status['lof_status'];
    }

    function get_wms_params_by_out_store($out_store) {
        $sql = " select  c.wms_params  from wms_config c
    INNER JOIN sys_api_shop_store s ON c.wms_config_id=s.p_id
    where s.outside_code='{$out_store}' AND s.p_type=1 AND s.outside_type=1  ";

        $row = $this->db->get_row($sql);
        $data = array();
        if (!empty($row)) {
            $data = json_decode($row['wms_params'], true);
            $data ['outside_code'] = $out_store;
        }
        return $data;
    }

    function get_efast_store_code($wms_store_code) {
        $sql = " select  s.shop_store_code  from wms_config c
    INNER JOIN sys_api_shop_store s ON c.wms_config_id=s.p_id
    where s.outside_code='{$wms_store_code}' AND s.p_type=1 AND s.outside_type=1  AND c.wms_system_code='qimen'   ";

        $row = $this->db->get_row($sql);
        return isset($row['shop_store_code']) ? $row['shop_store_code'] : '';
    }

    function exec_api($request) {
        $this->req_data_xml = $GLOBALS['HTTP_RAW_POST_DATA'];
//        if(!empty($xml)){
//            $this->req_data_xml = $xml;
//        }

        $ret = $this->check_sign($request);
        if ($ret !== true) {
            return $ret;
        }
        //     $_REQUEST['customerId']
        //    $this->store_code
        //entryorder.confirm

        $method = $request['method'];
        $action_method = str_replace('.', '_', $method);

        if (method_exists($this, $action_method)) {
            $data = $this->xml2array($this->req_data_xml);
            return $this->$action_method($data);
        } else {
            return $this->return_info(-1, '找不到指定方法');
        }
    }

    /**
     * 处理接口数据
     * @param array $data 接口数据
     * @param string $efast_store_code 系统仓库
     * @param array $goods_info_zp 正品数据
     * @param array $goods_info_cp 次品数据
     */
    private function deal_api_data($data, $efast_store_code, &$goods_info_zp, &$goods_info_cp) {
        if (!empty($data['itemId'])) {
            $barcode = load_model('wms/WmsRecordModel')->get_sys_code($efast_store_code, $data['itemId']);
        } else {
            $barcode = $data['itemCode'];
        }
        if (empty($barcode)) {
            return $this->format_ret(-10, $barcode, '回传商品在WMS商品档案中未找到');
        }
        if ($this->is_lof == 0) {
            //非批次
            $batchs_arr = isset($data['batchs']['batch']) ? $data['batchs']['batch'] : array();
            if (empty($batchs_arr)) {
                $data['inventoryType'] = isset($data['inventoryType']) ? $data['inventoryType'] : 'ZP';
                if ($data['inventoryType'] == 'ZP') {
                    $goods_info_zp[] = array('sl' => $data['actualQty'], 'barcode' => $barcode);
                } else {
                    $goods_info_cp[] = array('sl' => $data['actualQty'], 'barcode' => $barcode);
                }
            } else {
                if (isset($batchs_arr['actualQty'])) {
                    $batchs_arr = array($batchs_arr);
                }
                foreach ($batchs_arr as $item) {
                    $item['inventoryType'] = isset($item['inventoryType']) ? $item['inventoryType'] : 'ZP';
                    if ($item['inventoryType'] == 'ZP') {
                        $goods_info_zp[] = array('sl' => $item['actualQty'], 'barcode' => $barcode);
                    } else {
                        $goods_info_cp[] = array('sl' => $item['actualQty'], 'barcode' => $barcode);
                    }
                }
            }
        } else {
            //批次获取
            $batchs_arr = isset($data['batchs']['batch']) ? $data['batchs']['batch'] : array();
            //对于菜鸟wms并且单批次
            if(empty($batchs_arr) && $this->wms_sys_type === 'cainiao'){
                $batchs_arr = $data;
                $batchs_arr['batchCode'] = $batchs_arr['produceCode'];
            }
            if (empty($batchs_arr)) {
                return $this->format_ret(-1, '', '无批次数据！');
            }
            if (isset($batchs_arr['actualQty'])) {
                $batchs_arr = array($batchs_arr);
            }
            foreach ($batchs_arr as $item) {
                $item['inventoryType'] = isset($item['inventoryType']) ? $item['inventoryType'] : 'ZP';
                if ($item['inventoryType'] == 'ZP') {
                    $goods_info_zp[] = array('sl' => $item['actualQty'], 'barcode' => $barcode, 'lof_no' => $item['batchCode'], 'production_date' => $item['productDate']);
                } else {
                    $goods_info_cp[] = array('sl' => $item['actualQty'], 'barcode' => $barcode, 'lof_no' => $item['batchCode'], 'production_date' => $item['productDate']);
                }
            }
        }
        return $this->format_ret(1);
    }

    /**
     * 入库单确认接口
     * @param array $data 回传数据
     * @return array 处理结果
     */
    function entryorder_confirm(&$data) {
        $data = &$data['request'];
        $check_un = $this->check_log_unid($data['entryOrder']['outBizCode']);
        if ($check_un) {
            return $this->return_info(-1, '重复请求！');
        }

        $ret_data = array();
             //获取wms类型
        $ret_data['data']['wms_store_code'] = $data['entryOrder']['warehouseCode'];
        $this->get_wms_by_out_store($ret_data['data']['wms_store_code']);
        if ($this->wms_sys_type == 'cainiao') {
            if ($data['entryOrder']['confirmType'] == '0') {
                $data['entryOrder']['status'] = !isset($data['entryOrder']['status']) || empty($data['entryOrder']['status']) ? 'FULFILLED' : $data['entryOrder']['status'];
            }
        }



        //入库单状态 (NEW-未开始处理, ACCEPT-仓库接单 , PARTFULFILLED-部分收货完成, FULFILLED-收货完成, EXCEPTION-异常, CANCELED-取消, CLOSED-关闭, REJECT-拒单, CANCELEDFAIL-取消失败)
        $status = isset($data['entryOrder']['status']) ? strtoupper($data['entryOrder']['status']) : '';
        //0 表示入库单最终状态确认； 1 表示入库单中间状态确认
        $data['entryOrder']['confirmType'] = isset($data['entryOrder']['confirmType']) ? $data['entryOrder']['confirmType'] : '0'; 

        if (!in_array($status, ['PARTFULFILLED', 'FULFILLED', 'CANCELED', 'REJECT'])) {
            return $this->return_info(-1, '入库单回传状态不正确');
        }
        if ($data['entryOrder']['confirmType'] == '0' && $status == 'FULFILLED') {
            $ret_data['data']['order_status'] = 'flow_end';
            $ret_data['data']['order_status_txt'] = '已收发货';
        } else {
            //不准确，属于部分发货
            $ret_data['data']['order_status'] = 'upload';
            $ret_data['data']['order_status_txt'] = '已上传';
        }

        $record_code = $data['entryOrder']['entryOrderCode'];

        if ($data['entryOrder']['entryOrderType'] == 'CGRK') {
            $record_type = 'pur_notice';
        } else if ($data['entryOrder']['entryOrderType'] == 'B2BRK'||$data['entryOrder']['entryOrderType'] == 'XTRK') {
            $record_type = 'wbm_return_notice';
        } else if ($data['entryOrder']['entryOrderType'] == 'DBRK') {
            $record_type = 'shift_in';
        }
        if (!isset($record_type)) {
            return $this->return_info(-1, '缺少入库单类型！');
        }

        $ret_data['data']['efast_record_code'] = $data['entryOrder']['entryOrderCode'];
        $ret_data['data']['wms_record_code'] = $data['entryOrder']['entryOrderId'];
        $ret_data['data']['wms_store_code'] = $data['entryOrder']['warehouseCode'];
        //获取wms类型
        $this->get_wms_by_out_store($ret_data['data']['wms_store_code']);
        $ret_data['data']['flow_end_time'] = $data['entryOrder']['operateTime'];
        $efast_store_code = $this->get_efast_store_code($ret_data['data']['wms_store_code']);
        if (empty($efast_store_code)) {
            return $this->return_info(-1, '未找到仓储仓库对应的系统仓库');
        }
        if (isset($data['orderLines']['orderLine']['actualQty'])) {
            $data['orderLines']['orderLine'] = array($data['orderLines']['orderLine']);
        }
        $goods_info_zp = array();
        $goods_info_cp = array();
        $error_goods = [];
        foreach ($data['orderLines']['orderLine'] as $val) {
            $ret = $this->deal_api_data($val, $efast_store_code, $goods_info_zp, $goods_info_cp);
            //处理数据失败
            if ($ret['status'] == -1) {
                return $this->return_info(-1, $ret['message']);
            }
            if ($ret['status'] == -10) {
                $error_goods[] = $ret['data'];
            }
        }
        if (!empty($error_goods)) {
            $err_goods_str = implode(',', $error_goods);
            $message = "单据{$record_code}回传商品在系统WMS商品档案中未找到:{$err_goods_str}";
            return $this->return_info(-1, $message);
        }

        CTX()->db->begin_trans();

        if ($status == 'PARTFULFILLED' || $status == 'FULFILLED') {
            if (!empty($goods_info_zp)) {
                //收货商品同步
                $ret = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($record_code, $record_type, $goods_info_zp);
                if ($ret['status'] < 0) {
                    CTX()->db->rollback();
                    return $this->return_info($ret['status'], $ret['message']);
                }
            }
            if (!empty($goods_info_cp)) {
                //收货商品同步
                $ret = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($record_code, $record_type, $goods_info_cp, 0);
                if ($ret['status'] < 0) {
                    CTX()->db->rollback();
                    return $this->return_info($ret['status'], $ret['message']);
                }
            }
        }

        //取消, 关闭,  REJECT-拒单,  CANCELEDFAIL-取消失败
        //对方取消
        if ($status == 'CANCELED' || $status == 'REJECT') {
            $obj_wms_record = load_model('wms/WmsRecordModel');
            $obj_wms_record->get_wms_cfg($efast_store_code);
            $ret = array('status' => 1, 'data' => '', 'message' => '');
            $obj_wms_record->process_cancel_after($record_code, $record_type, $ret);
            $this->update_log_key_id();
            CTX()->db->commit();
            return $this->return_info($ret['status'], $ret['message']);
        }

        //完成入库回传
        $total_num = array_sum(array_column(array_merge($goods_info_cp, $goods_info_zp), 'sl'));
        if ($ret_data['data']['order_status'] == 'flow_end' && !empty($total_num)) {
            $ret_data['status'] = 1;
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_end($record_code, $record_type, $ret_data, 1);
        }
        $this->update_log_key_id();
        CTX()->db->commit();
        return $this->return_info($ret['status'], $ret['message']);
    }

    /**
     * 出库单确认接口
     * @param array $data 回传数据
     * @return array 处理结果
     */
    function stockout_confirm(&$data) {
        $data = &$data['request'];
        $check_un = $this->check_log_unid($data['deliveryOrder']['outBizCode']);
        if ($check_un) {
            return $this->return_info(-1, '重复请求！');
        }

        $ret_data = array();
        
      //获取wms类型
        $ret_data['data']['wms_store_code'] = $data['deliveryOrder']['warehouseCode'];
        $this->get_wms_by_out_store($ret_data['data']['wms_store_code']);
        if ($this->wms_sys_type == 'cainiao') {
            if ($data['deliveryOrder']['confirmType'] == '0') {
                $data['deliveryOrder']['status'] = !isset($data['deliveryOrder']['status']) || empty($data['deliveryOrder']['status']) ? 'DELIVERED' : $data['deliveryOrder']['status'];
            }
        }
        //出库单状态(NEW-未开始处理,  ACCEPT-仓库接单 , PARTDELIVERED-部分发货完成,  DELIVERED-发货完成,  EXCEPTION-异常,  CANCELED-取消,  CLOSED-关闭,  REJECT-拒单,  CANCELEDFAIL-取消失败)
        $status = isset($data['deliveryOrder']['status']) ? strtoupper($data['deliveryOrder']['status']) : '';
        //支持出库单多次发货:0 表示发货单最终状态确认； 1 表示发货单中间状态确认；
        $data['deliveryOrder']['confirmType'] = isset($data['deliveryOrder']['confirmType']) ? $data['deliveryOrder']['confirmType'] : '0'; 

        if (!in_array($status, ['PARTDELIVERED', 'DELIVERED'])) {
            return $this->return_info(-1, '出库单回传状态不正确');
        }

        if ($data['deliveryOrder']['confirmType'] == '0' && $status == 'DELIVERED') {
            $ret_data['data']['order_status'] = 'flow_end';
            $ret_data['data']['order_status_txt'] = '已收发货';
        } else {
            //不准确，属于部分发货
            $ret_data['data']['order_status'] = 'upload';
            $ret_data['data']['order_status_txt'] = '已上传';
        }

        $record_code = $data['deliveryOrder']['deliveryOrderCode'];

        $data['deliveryOrder']['orderType'] = strtoupper($data['deliveryOrder']['orderType']);
        if ($data['deliveryOrder']['orderType'] == 'PTCK' || $data['deliveryOrder']['orderType'] == 'CGTH') {
            $record_type = 'pur_return_notice';
            //orderType  PTCK=普通出库单（退仓），DBCK=调拨出库 ，B2BCK=B2B出库，QTCK=其他出库
        } else if ($data['deliveryOrder']['orderType'] == 'B2BCK') {
            $record_type = 'wbm_notice';
        } else if ($data['deliveryOrder']['orderType'] == 'DBCK') {//
            $record_type = 'shift_out';
        }

        $ret_data['data']['efast_record_code'] = $data['deliveryOrder']['deliveryOrderCode'];
        $ret_data['data']['wms_record_code'] = $data['deliveryOrder']['deliveryOrderId'];
        $ret_data['data']['wms_store_code'] = $data['deliveryOrder']['warehouseCode'];
   
 
        $ret_data['data']['flow_end_time'] = $data['deliveryOrder']['orderConfirmTime'];
        $ret_data['data']['express_code'] = $data['deliveryOrder']['logisticsCode'];
        $ret_data['data']['express_no'] = $data['deliveryOrder']['expressCode'];

        $efast_store_code = $this->get_efast_store_code($ret_data['data']['wms_store_code']);
        if (empty($efast_store_code)) {
            return $this->return_info(-1, '未找到仓储仓库对应的系统仓库');
        }

        if (isset($data['orderLines']['orderLine']['actualQty'])) {
            $data['orderLines']['orderLine'] = array($data['orderLines']['orderLine']);
        }

        $goods_info_zp = array();
        $goods_info_cp = array();
        foreach ($data['orderLines']['orderLine'] as $val) {
            $ret = $this->deal_api_data($val, $efast_store_code, $goods_info_zp, $goods_info_cp);
            if ($ret['status'] == -1) {
                $this->return_info(-1, $ret['message']);
            }
            if ($ret['status'] == -10) {
                $error_goods[] = $ret['data'];
            }
        }
        if (!empty($error_goods)) {
            $err_goods_str = implode(',', $error_goods);
            $message = "单据{$record_code}回传商品在系统WMS商品档案中未找到:{$err_goods_str}";
            return $this->return_info(-1, $message);
        }

        CTX()->db->begin_trans();

        if (!empty($goods_info_zp)) {
            //收货商品同步
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($record_code, $record_type, $goods_info_zp);
            if ($ret['status'] < 0) {
                CTX()->db->rollback();
                return $this->return_info($ret['status'], $ret['message']);
            }
        }
        if (!empty($goods_info_cp)) {
            //收货商品同步
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($record_code, $record_type, $goods_info_cp, 0);
            if ($ret['status'] < 0) {
                CTX()->db->rollback();
                return $this->return_info($ret['status'], $ret['message']);
            }
        }

        //完成入库回传
        $total_num = array_sum(array_column(array_merge($goods_info_cp, $goods_info_zp), 'sl'));
        if ($ret_data['data']['order_status'] == 'flow_end' && !empty($total_num)) {
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_end($record_code, $record_type, $ret_data, 1);
        }
        if ($ret['status'] > 0) {
            $this->update_log_key_id();
            CTX()->db->commit();
        }
        return $this->return_info($ret['status'], $ret['message']);
    }

    /**
     * 退货入库单确认接口
     * @param array $data 回传数据
     * @return array 处理结果
     */
    function returnorder_confirm(&$data) {
        $data = &$data['request'];

        $check_un = $this->check_log_unid($data['returnOrder']['outBizCode']);
        if ($check_un) {
            return $this->return_info(-1, '重复请求！');
        }

        $ret_data = array();

        $ret_data['data']['order_status'] = 'flow_end';
        $ret_data['data']['order_status_txt'] = '已收发货';

        $record_code = $data['returnOrder']['returnOrderCode'];
        $record_type = 'sell_return';

        $ret_data['data']['efast_record_code'] = $data['returnOrder']['returnOrderCode'];
        $ret_data['data']['wms_record_code'] = $data['returnOrder']['returnOrderId'];
        $ret_data['data']['wms_store_code'] = $data['returnOrder']['warehouseCode'];
        //获取wms类型
        $this->get_wms_by_out_store($ret_data['data']['wms_store_code']);
        $ret_data['data']['flow_end_time'] = $data['returnOrder']['orderConfirmTime'];
        $ret_data['data']['express_code'] = $data['returnOrder']['logisticsCode'];
        $ret_data['data']['express_no'] = $data['returnOrder']['expressCode'];

        $efast_store_code = $this->get_efast_store_code($ret_data['data']['wms_store_code']);
        if (empty($efast_store_code)) {
            return $this->return_info(-1, '未找到仓储仓库对应的系统仓库');
        }

        if (isset($data['orderLines']['orderLine']['actualQty'])) {
            $data['orderLines']['orderLine'] = array($data['orderLines']['orderLine']);
        }

        $goods_info_zp = array();
        $goods_info_cp = array();
        foreach ($data['orderLines']['orderLine'] as $val) {
            $ret = $this->deal_api_data($val, $efast_store_code, $goods_info_zp, $goods_info_cp);
            if ($ret['status'] == -1) {
                return $this->return_info(-1, $ret['message']);
            }
            if ($ret['status'] == -10) {
                $error_goods[] = $ret['data'];
            }
        }
        if (!empty($error_goods)) {
            $err_goods_str = implode(',', $error_goods);
            $message = "单据{$record_code}回传商品在系统WMS商品档案中未找到:{$err_goods_str}";
            return $this->return_info(-1, $message);
        }

        CTX()->db->begin_trans();

        if (!empty($goods_info_zp)) {
            //收货商品同步
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($record_code, $record_type, $goods_info_zp);
            if ($ret['status'] < 0) {
                CTX()->db->rollback();
                return $this->return_info($ret['status'], $ret['message']);
            }
        }
        if (!empty($goods_info_cp)) {
            //收货商品同步
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($record_code, $record_type, $goods_info_cp, 0);
            if ($ret['status'] < 0) {
                CTX()->db->rollback();
                return $this->return_info($ret['status'], $ret['message']);
            }
        }
        $total_num = array_sum(array_column(array_merge($goods_info_cp, $goods_info_zp), 'sl'));
        if ($ret_data['data']['order_status'] == 'flow_end' && !empty($total_num)) {
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_end($record_code, $record_type, $ret_data, 1);
        }
        if ($ret['status'] > 0) {
            $this->update_log_key_id();
            CTX()->db->commit();
        }
        return $this->return_info($ret['status'], $ret['message']);
    }

    /**
     * 发货单确认接口
     * @param array $data 回传数据
     * @return array 处理结果
     */
    function deliveryorder_confirm(&$data) {
        $data = &$data['request'];

        $check_un = $this->check_log_unid($data['deliveryOrder']['outBizCode']);
        if ($check_un) {
            return $this->return_info(-1, '重复请求！');
        }
        $ret_data = array();
        //获取wms类型
        $ret_data['data']['wms_store_code'] = $data['deliveryOrder']['warehouseCode'];
        $this->get_wms_by_out_store($ret_data['data']['wms_store_code']);
        if($this->wms_sys_type=='cainiao'){
            if( $data['deliveryOrder']['confirmType']==0){
                      $data['deliveryOrder']['status'] =!isset($data['deliveryOrder']['status'])||empty($data['deliveryOrder']['status'])
                    ? 'DELIVERED': $data['deliveryOrder']['status']; 
            }
        }
        
        //出库单状态,(NEW-未开始处理,  ACCEPT-仓库接单 , PARTDELIVERED-部分发货完成,  DELIVERED-发货完成,  EXCEPTION-异常,  CANCELED-取消,  CLOSED-关闭,  REJECT-拒单,  CANCELEDFAIL-取消失败)
        $status = isset($data['deliveryOrder']['status']) ? strtoupper($data['deliveryOrder']['status']) : '';
        //支持出库单多次发货:0 表示发货单最终状态确认； 1 表示发货单中间状态确认；
        $data['deliveryOrder']['confirmType'] = isset($data['deliveryOrder']['confirmType']) ? $data['deliveryOrder']['confirmType'] : '0';

        if (!in_array($status, ['PARTDELIVERED', 'DELIVERED'])) {
            return $this->return_info(-1, '发货单回传状态不正确');
        }

        if ($status == 'DELIVERED') {
            $ret_data['data']['order_status'] = 'flow_end';
            $ret_data['data']['order_status_txt'] = '已收发货';
        } else {
            //不准确，属于部分发货
            $ret_data['data']['order_status'] = 'upload';
            $ret_data['data']['order_status_txt'] = '已上传';
        }

        if ($ret_data['data']['order_status'] == 'upload') {
            return $this->return_info(1, '接口到请求，非收发货状态！');
        }
        $record_code = $data['deliveryOrder']['deliveryOrderCode'];
        $record_type = 'sell_record';

        $ret_data['data']['efast_record_code'] = $data['deliveryOrder']['deliveryOrderCode'];
        $ret_data['data']['wms_record_code'] = $data['deliveryOrder']['deliveryOrderId'];
        $ret_data['data']['wms_store_code'] = $data['deliveryOrder']['warehouseCode'];
        //获取wms类型
        $this->get_wms_by_out_store($ret_data['data']['wms_store_code']);
        $ret_data['data']['flow_end_time'] = $data['deliveryOrder']['orderConfirmTime'];

        $efast_store_code = $this->get_efast_store_code($ret_data['data']['wms_store_code']);
        if (empty($efast_store_code)) {
            return $this->return_info(-1, '未找到仓储仓库对应的系统仓库');
        }

        if (isset($data['packages']['package']['logisticsCode'])) {
            $data['packages']['package'] = array($data['packages']['package']);
        }
        $ret_data['data']['order_weight'] = 0;
        $first_packages = current($data['packages']['package']);
        $ret_data['data']['express_code'] = $first_packages['logisticsCode'];
        $ret_data['data']['express_no'] = $first_packages['expressCode'];
        foreach ($data['packages']['package'] as $p_val) {
            $ret_data['data']['order_weight'] += $p_val['weight'];
        }

        if (isset($data['orderLines']['orderLine']['actualQty'])) {
            $data['orderLines']['orderLine'] = array($data['orderLines']['orderLine']);
        }

        $goods_info_zp = array();
        $goods_info_cp = array();
        foreach ($data['orderLines']['orderLine'] as $val) {
            if ($val['actualQty'] == 0) {
                continue;
            }
            $ret = $this->deal_api_data($val, $efast_store_code, $goods_info_zp, $goods_info_cp);
            if($ret['status']!=1){
                return $this->return_info(-1, $ret['message']);
            }
            if ($ret['status'] == -10) {
                $error_goods[] = $ret['data'];
            }
        }
        if (!empty($error_goods)) {
            $err_goods_str = implode(',', $error_goods);
            $message = "单据{$record_code}回传商品在系统WMS商品档案中未找到:{$err_goods_str}";
            return $this->return_info(-1, $message);
        }

        CTX()->db->begin_trans();
        //多包裹数据处理
        $ret_pararm = load_model('sys/SysParamsModel')->get_val_by_code(array('is_more_deliver_package', 'safety_control'));
        $is_more_deliver_package = isset($ret_pararm['is_more_deliver_package']) ? $ret_pararm['is_more_deliver_package'] : '0';
        if ($is_more_deliver_package == 1) {
            $ret = load_model('wms/WmsRecordModel')->more_package_add($record_code, $data['packages']['package']);
            if ($ret['status'] < 0) {
                CTX()->db->rollback();
                return $this->return_info($ret['status'], $ret['message']);
            }
        }

        if (!empty($goods_info_zp)) {
            //收货商品同步
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($record_code, $record_type, $goods_info_zp);
            if ($ret['status'] < 0) {
                CTX()->db->rollback();
                return $this->return_info($ret['status'], $ret['message']);
            }
        }
        if (!empty($goods_info_cp)) {
            //收货商品同步
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($record_code, $record_type, $goods_info_cp, 0);
            if ($ret['status'] < 0) {
                CTX()->db->rollback();
                return $this->return_info($ret['status'], $ret['message']);
            }
        }

        if ($ret_data['data']['order_status'] == 'flow_end') {
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_end($record_code, $record_type, $ret_data, 1);
        }
        if ($ret['status'] > 0) {
            $this->update_log_key_id();
            CTX()->db->commit();
        }

        return $this->return_info($ret['status'], $ret['message']);
    }

    //发货单SN通知接口
    function sn_report(&$data) {
        return $this->return_info(1, '');
    }

    function inventory_report(&$data) {
        $data = &$data['request'];
        $check_un = $this->check_log_unid($data['request']['outBizCode']);
        if ($check_un) {
            return $this->return_info(-1, '重复请求！');
        }
        if (isset($data['checkOrderCode'])) {
            $record_data['wms_record_code'] = $data['checkOrderCode']; //checkOrderId
        } else if (isset($data['checkOrderId'])) {
            $record_data['wms_record_code'] = $data['checkOrderId']; //checkOrderId
        }
        $record_data['process_time'] = $data['checkTime'];
        $record_data['wms_store_code'] = $data['warehouseCode'];
        $order_zp = array();
        $order_cp = array();
        $item = $data['items']['item'];
        if (isset($item['itemCode'])) {
            $item = array($item);
        }

        foreach ($item as $val) {
            if (strtoupper($val['inventoryType']) == 'ZP') {
                $order_zp[] = array('barcode' => $val['itemCode'], 'sl' => $val['quantity']);
            } else {
                $order_cp[] = array('barcode' => $val['itemCode'], 'sl' => $val['quantity']);
            }
        }
        if (!empty($order_zp)) {
            load_model('wms/WmsInvModel')->create_inv_order($record_data, $order_zp, 'adjust');
        }

        if (!empty($order_cp)) {
            load_model('wms/WmsInvModel')->create_inv_order($record_data, $order_cp, 'adjust', 0);
        }

        return $this->return_info(1);
    }

    function save_order_in_info(&$order_info) {
        $type_arr = array('PO' => 'pur_notice', 'RS' => 'sell_return');
        if (isset($type_arr[$order_info['OrderType']])) {
            $record_type = $type_arr[$order_info['OrderType']];
        } else {
            return array('status' => -1, 'message' => '暂时不支持此类型回传');
        }


        $record_code = $order_info['OMSOrderNo'];
        $ret_data = array();

        $ret_data['data']['efast_record_code'] = $order_info['OMSOrderNo'];
        $ret_data['data']['wms_record_code'] = $order_info['WMSOrderno'];
        $ret_data['data']['wms_store_code'] = $order_info['WarehouseID'];

        //99 强制完成  40完全收货
        if ($order_info['Status'] == '40' || $order_info['Status'] == '99') {
            $ret_data['data']['order_status'] = 'flow_end';
            $ret_data['data']['order_status_txt'] = '已收发货';
        } else {
            //不准确，属于部分发货
            $ret_data['data']['order_status'] = 'upload';
            $ret_data['data']['order_status_txt'] = '已上传';
        }

        $ret_data['data']['flow_end_time'] = date('Y-m-d H:i:s');
        //  $ret_data['data']['goods'] = array();
        $item = $order_info['item'];
        if (isset($item['SKU'])) {
            $order_info['item'] = array($item);
        }
        $goods_info = array();
        foreach ($order_info['item'] as $val) {
            $goods_info[] = array('sl' => $val['ReceivedQty'], 'barcode' => $val['SKU']);
        }
        CTX()->db->begin_trans();
        if (!empty($goods_info)) {
            //收货商品同步
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($record_code, $record_type, $goods_info);
            if ($ret['status'] < 0) {
                CTX()->db->rollback();
                return $ret;
            }
        }

        //完成入库回传
        if ($ret_data['data']['order_status'] == 'flow_end') {
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_end($record_code, $record_type, $ret_data, 1);
        }
        if ($ret['status'] > 0) {
            $this->update_log_key_id();
            CTX()->db->commit();
        }
        return $ret;
    }

    function save_send_info(&$order_info) {
        $type_arr = array('SO' => 'sell_record', 'RP' => 'pur_return_notice', 'TO' => 'wbm_notice');
        if (isset($type_arr[$order_info['OrderType']])) {
            $record_type = $type_arr[$order_info['OrderType']];
        } else {
            return array('status' => -1, 'message' => '暂时不支持此类型回传');
        }
        $record_code = $order_info['OMSOrderNo'];
        $ret_data = array();
        $ret_data['data']['express_code'] = $order_info['CarrierId'];
        $ret_data['data']['express_no'] = $order_info['DeliveryNo'];
        $ret_data['data']['flow_end_time'] = date('Y-m-d H:i:s');
        $ret_data['data']['goods'] = array();
        $item = $order_info['item'];
        if (isset($item['OrderNo'])) {
            $order_info['item'] = array($item);
        }
        foreach ($order_info['item'] as $val) {
            $ret_data['data']['goods'][] = array('sl' => $val['QtyShipped'], 'barcode' => $val['SKU']);
        }
        return load_model('wms/WmsRecordModel')->uploadtask_order_end($record_code, $record_type, $ret_data);
    }

    function check_sign($request) {
        $this->save_log($request);
        $status = $this->set_wms_params($request['customerId']);
        if ($status === FALSE) {
            return $this->return_info(-1, '找不到对应仓库');
        }
        $this->url_data = array(
            'app_key' => $request['app_key'],
            // 'session' => $request['session'],
            'format' => $request['format'],
            'v' => $request['v'],
            'sign_method' => $request['sign_method'],
            'timestamp' => $request['timestamp'],
            'method' => $request['method'],
        );

        $sign = $this->sign($this->req_data_xml);

        if ($sign != $request['sign']) {
            //return $this->return_info(-1, '加密效验失败');
        }

        return true;
    }

    function set_wms_params($customerid) {
        $sql = "select wms_params from wms_config w
            INNER JOIN sys_api_shop_store a ON w.wms_config_id=a.p_id
            where a.p_type=1 AND w.wms_system_code=:wms_system_code ";

        $data = $this->db->get_all($sql, array(':wms_system_code' => 'qimen'));

        if (!empty($data)) {
            foreach ($data as $val) {
                $api_data = $val['wms_params'];
                $token = json_decode($api_data, true);
                if ($token['customerid'] == $customerid) {
                    $this->set_token($token);
                    return true;
                }
            }
        }
        return FALSE;
    }

    function save_log($request) {
        $request['method'] = empty($request['method']) ? '' : $request['method'];
        $data = array('type' => 'qmwms', 'method' => $request['method'], 'add_time' => date('Y-m-d H:i:s'));
        $data['post_data'] = empty($this->req_data_xml) ? '' : $this->req_data_xml;
        $data['url'] = json_encode($request);
        $data['url'] = empty($data['url']) ? '' : $data['url'];

        $this->db->insert('api_open_logs', $data);
        $this->log_id = $this->db->insert_id();
    }

    function update_log($return, $status) {
        if ($this->log_id != 0) {
            $up_data = array('return_data' => $return);
            if ($status > 0) {
                $up_data['key_id'] = $this->log_unid;
            } else {
                $up_data['key_id'] = NULL;
            }
            $this->db->update('api_open_logs', $up_data, "id = '{$this->log_id }'");
        }
    }

    function update_log_key_id() {
        $up_data['key_id'] = $this->log_unid;
        $this->db->update('api_open_logs', $up_data, "id = '{$this->log_id }'");
    }

    function check_log_unid($log_unid) {
        $this->log_unid = $log_unid;
        $sql = "select 1 from api_open_logs where key_id=:key_id order by id desc ";
        $check = $this->db->get_value($sql, array(':key_id' => $log_unid));
        $is_get = false;
        if ($check > 0) {
            $is_get = true;
        }

        return $is_get;
    }

    function return_info($status, $message = '', $ret_data = array()) {
        $return = array(
            '0' => array('flag' => 'failure', 'code' => -1000, 'message' => 'ok', 'returnFlag' => '1'),
            '1' => array('flag' => 'success', 'code' => 1000, 'message' => '', 'returnFlag' => '0'),
        );
        if ($status < 0) {
            $status = 0;
        }
        $ret = &$return[$status];
        if (!empty($message)) {
            $ret['message'] = $message;
        }
        if (!empty($ret_data)) {
            $ret = array_merge($ret, $ret_data);
        }
        $ret_xml = $this->array2xml($ret, 'response');
        $this->update_log($ret_xml, $status);
        return $ret_xml;
    }

    /**
     * 获取wms类型
     * @param $out_store
     */
    private function get_wms_by_out_store($out_store){
        $sql = "select  c.wms_system_type  from wms_config c
                INNER JOIN sys_api_shop_store s ON c.wms_config_id=s.p_id
                where s.outside_code='{$out_store}' AND s.p_type=1 AND s.outside_type=1  ";

        $wms_sys_type = $this->db->get_value($sql);
        $this->wms_sys_type = $wms_sys_type;
    }

}
