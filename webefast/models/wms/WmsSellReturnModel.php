<?php

require_model('wms/WmsRecordModel');

class WmsSellReturnModel extends WmsRecordModel {

    function __construct() {
        parent::__construct();
    }

    /**
     * 退单收货处理
     * @param string $sell_return_code 退单号
     * @param date $record_time 业务时间
     * @param array $return_mx 退单明细
     * @param string $express_code 快递代码
     * @param string $express_no 快递单号
     * @return array 处理结果
     */
    function order_shipping($sell_return_code, $record_time, $return_mx, $express_code = null, $express_no = null) {
        $record_type = 'sell_return';
        //不存在明细，不允许处理
        if (empty($return_mx)) {
            return $this->format_ret(-1, '', '未找到WMS发货明细,操作失败');
        }
        //已入库，不处理
        $sql = "SELECT return_shipping_status,deal_code,store_code FROM oms_sell_return WHERE sell_return_code = :sell_return_code";
        $ret_data = ctx()->db->get_row($sql, array(':sell_return_code' => $sell_return_code));
        if ($ret_data['return_shipping_status'] == 1) {
            return $this->format_ret(1);
        }
        $deal_code = $ret_data['deal_code'];
        //校验配送方式是否存在
        if (!empty($express_code)) {
            $ret_exists = $this->check_express_exists($express_code);
            if ($ret_exists['status'] != 1) {
                return $ret_exists;
            }
            $express_code = $ret_exists['data'];
        }
        $this->begin_trans();
        //次品自动切换仓库
        $ret = $this->check_is_more($sell_return_code, $deal_code);

        //更新退单发货信息
        $update_parmas = array();
        if (!empty($express_code)) {
            $update_parmas['return_express_code'] = $express_code;
        }
        if (!empty($express_no)) {
            $update_parmas['return_express_no'] = $express_no;
        }
        if (!empty($update_parmas)) {
            $this->update_exp('oms_sell_return', $update_parmas, array('sell_return_code' => $sell_return_code));
            $this->update_exp('oms_return_package', $update_parmas, array('sell_return_code' => $sell_return_code));
        }

        //退单收货
        $ret = load_model("oms/SellReturnOptModel")->opt_return_shipping($sell_return_code, array('receive_time' => $record_time), 1);
        if ($ret['status'] < 1) {
            $this->rollback();
            return $ret;
        }
        
        $this->commit();
        return $ret;
    }

    function check_is_more($record_code, $deal_code) {
        $sql = "SELECT api_product,efast_store_code FROM wms_oms_trade WHERE record_code=:record_code AND record_type=:record_type";
        $row = $this->db->get_row($sql, array(':record_code' => $record_code, ':record_type' => 'sell_return'));
        $api_product = $row['api_product'];
        $store_code = $row['efast_store_code'];
        $product_arr = array('ydwms', 'qimen');
        if (!in_array($api_product, $product_arr)) {
            return FALSE;
        }
        //删除系统退单原有批次数据
        $return_package_code = load_model('oms/ReturnPackageModel')->get_return_package_code($record_code);
        $sql_del = "DELETE FROM oms_sell_record_lof WHERE record_code='{$return_package_code}' AND record_type=2";
        $this->db->query($sql_del);

        //获取回传商品明细
        $this->get_wms_cfg($store_code, 'sell_return');
        $is_lof = isset($this->wms_cfg['is_lof']) ? $this->wms_cfg['is_lof'] : 0;
        if (in_array($this->api_product, array('qimen')) && $is_lof == 1) {
            $sql = "SELECT gs.goods_code,gs.spec1_code,gs.spec2_code,gs.sku,wl.lof_no,wl.production_date,wl.wms_sl 
                    FROM wms_oms_order_lof AS wl INNER JOIN goods_sku AS gs ON wl.barcode=gs.barcode
                    WHERE wl.record_code=:record_code AND wl.record_type='sell_return'";
        } else {
            $sql = "SELECT s.goods_code,s.spec1_code,s.spec2_code,s.sku,o.wms_sl FROM wms_oms_order o
                    INNER JOIN goods_sku s ON o.barcode=s.barcode
                    WHERE o.record_code=:record_code AND  o.record_type='sell_return'";
        }
        $data_detail = $this->db->get_all($sql, array(':record_code' => $record_code));

        //获取默认批次
        $moren = load_model('prm/GoodsLofModel')->get_sys_lof();
        $lof_no = isset($moren['data']['lof_no']) ? $moren['data']['lof_no'] : '';
        $production_date = isset($moren['data']['production_date']) ? $moren['data']['production_date'] : '';

        $pack_lof_mx = array();
        $date_time = time();
        $stock_date = date('Y-m-d');
        foreach ($data_detail as $val) {
            $pack_lof_mx[] = array(
                'record_type' => 2,
                'record_code' => $return_package_code,
                'deal_code' => $deal_code,
                'store_code' => $store_code,
                'goods_code' => $val['goods_code'],
                'spec1_code' => $val['spec1_code'],
                'spec2_code' => $val['spec2_code'],
                'sku' => $val['sku'],
                'lof_no' => isset($val['lof_no']) ? $val['lof_no'] : $lof_no,
                'production_date' => isset($val['production_date']) ? $val['production_date'] : $production_date,
                'num' => $val['wms_sl'],
                'stock_date' => $stock_date,
                'occupy_type' => 0,
                'create_time' => $date_time,
            );
        }
        if (!empty($pack_lof_mx)) {
            $this->insert_multi_exp("oms_sell_record_lof", $pack_lof_mx);
        }

        $sql = "SELECT * FROM wms_b2b_order_detail WHERE record_code=:record_code AND record_type=:record_type AND is_create=:is_create ";
        $sql_values = array(':record_code' => $record_code, ':record_type' => 'sell_return', ':is_create' => 0);
        $data = $this->db->get_all($sql, $sql_values);
        if (!empty($data)) {
            $item_type_arr = array();
            $cp_barcode_message = '';

            foreach ($data as $val) {
                $item_type_arr[$val['item_type']] = $val['item_type'];
                if ($val['item_type'] == 0) {
                    $cp_barcode_message .=$val['barcode'] . "次品,";
                }
            }

            if ($cp_barcode_message != '') {
                $sql = 'SELECT * FROM oms_sell_return WHERE sell_return_code=:sell_return_code';
                $record_data = $this->db->get_row($sql, array(':sell_return_code' => $record_code));
                load_model('oms/SellReturnModel')->add_action($record_data, '仓库收货', $cp_barcode_message);
                //打上包含次品标签
                $ret = load_model('oms/SellReturnTagModel')->add_return_tag($record_code, array('SYS001'));
                if ($ret['status'] == 1) {
                    //添加日志
                    load_model('oms/SellReturnActionModel')->add_action($record_data, '退单打标', $cp_barcode_message);
                } else {
                    return FALSE;
                }
            }

            if (count($item_type_arr) == 1) {
                $item_type_arr = array_values($item_type_arr);
                $item_type = $item_type_arr[0];
                if ($item_type == 0) { //次品，需要切换仓库
                    $cp_store_code = $this->get_cp_store_code($store_code);
                    //包订单
                    $sql = "select * from oms_return_package where sell_return_code=:sell_return_code AND return_order_status=0";
                    $package_record = $this->db->get_row($sql, array(':sell_return_code' => $record_code));
                    $return_package_code = $package_record['return_package_code'];
                    $this->db->update('oms_return_package', array('store_code' => $cp_store_code), array('return_package_code' => $return_package_code));
                    $where_arr = array('record_type' => 2, 'record_code' => $return_package_code);
                    $this->db->update('oms_sell_record_lof', array('store_code' => $cp_store_code), $where_arr);
                    $return_arr = array('sell_return_code' => $record_code);
                    $this->db->update('oms_sell_return', array('store_code' => $cp_store_code), $return_arr);
                    return true;
                }
            }
            return FALSE;
        } else {
            return FALSE;
        }
    }

    function order_cancel($msg = '') {
        $record = load_model("oms/SellReturnOptModel")->get_record_by_code($sell_return_code);
        $ret = $this->biz_unconfirm($record, $msg);
        return $ret;
    }

    /**
     * 获取上传中间表的单据数据
     * @param string $sell_return_code 退单号
     * @return array 数据集
     */
    function get_record_info($sell_return_code) {
        $sql = "select create_time,sell_return_code,sell_record_code,deal_code,store_code,shop_code,sale_channel_code,buyer_name,return_name,return_country,return_province,return_city,return_district,return_street,return_address,return_addr,return_zip_code,return_mobile,return_phone,return_email,return_express_code,return_express_no,return_reason_code,return_buyer_memo,return_remark from oms_sell_return where sell_return_code = :sell_return_code";
        $info = ctx()->db->get_row($sql, array(':sell_return_code' => $sell_return_code));
        if (empty($info)) {
            return $this->put_error(-1, '找不到退单');
        }
        $sql = "select deal_code,goods_code,spec1_code,spec2_code,sku,barcode,note_num as num,goods_price from oms_sell_return_detail where sell_return_code = :sell_return_code";
        $info['goods'] = ctx()->db->get_all($sql, array(':sell_return_code' => $sell_return_code));
        if (empty($info['goods'])) {
            return $this->put_error(-1, '找不到退单明细');
        }
        $info['goods'] = load_model('util/ViewUtilModel')->record_detail_append_goods_info($info['goods']);
        $ret = load_model('wms/WmsRecordModel')->check_mx($info['goods']);
        if ($ret['status'] < 0) {
            return $ret;
        }
        return $this->format_ret(1, $info);
    }
   function get_record_data(&$record_data) {

        $record_decrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_return_decrypt_info($record_data['sell_return_code']);
        if(empty($record_decrypt_info)){
            return false;
        }
        $record_data = array_merge($record_data, $record_decrypt_info);
        return true;
    }
}
