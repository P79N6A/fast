<?php

require_model('erp/bserp/BserpQimenBaseModel');

class BserpTradeQimenModel extends BserpQimenBaseModel {

//$tb_key
    function __construct($erp_config_id) {
        parent::__construct();
        $this->get_erp_config($erp_config_id);
    }

    function erp_upload_trade() {
        $this->create_client();
        //erp上线日期
        //该配置仓库及店铺
        $efast_cks = array_keys($this->config_store);
        $efast_sds = array_keys($this->config_shop);
        $efast_ck_str = "'" . join("','", $efast_cks) . "'";
        $efast_sd_str = "'" . join("','", $efast_sds) . "'";
        //获取订单未上传的单据
        $upload_time = date('Y-m-d H:i:s', strtotime('-30days'));
        $bserp_trade_tb = "api_{$this->tb_key}_trade";
        $record_sql = "select om.sell_record_code as orderId, 'LSCK' as orderType,om.payable_money as amount,om.store_code,om.delivery_time,om.shop_code,om.sale_channel_code as channelCode,om.goods_num as actualQty,om.deal_code,om.deal_code_list,IFNULL(bs.upload_status,0) as upload_status from oms_sell_record om left join {$bserp_trade_tb} bs on om.sell_record_code=bs.sell_record_code and bs.order_type=1 where om.shipping_status=4 ";
        $sql = "select * FROM ($record_sql) as tmp WHERE delivery_time>= '{$this->config['online_time']}' and delivery_time>='{$upload_time}' and upload_status != 1 and store_code in($efast_ck_str) and shop_code in ($efast_sd_str) limit 1000";
        $record_arr = $this->db->getAll($sql);

        //获取退单未上传的单据
        $return_sql = "select om.sell_return_code as orderId,'LSTH' as orderType,om.refund_total_fee as amount,om.store_code,om.agreed_refund_time,om.shop_code,om.deal_code,om.deal_code_list,om.sale_channel_code as channelCode,IFNULL(bs.upload_status,0) as upload_status from oms_sell_return om left join {$bserp_trade_tb} bs on om.sell_return_code=bs.sell_record_code and bs.order_type=2 where om.return_shipping_status=1 and om.return_type!=1 ";
        $sql = "select * FROM ($return_sql) as tmp WHERE agreed_refund_time>= '{$this->config['online_time']}' and agreed_refund_time>='{$upload_time}' and upload_status != 1 and store_code in($efast_ck_str) and shop_code in ($efast_sd_str) limit 1000";
        $return_arr = $this->db->getAll($sql);
        $recode_code_arr = array();
        $return_code_arr = array();
        $oms_sell_arr = array();
        $oms_sell_arr_log = array();
        $oms_return_arr = array();
        $oms_return_arr_log = array();
        foreach ($record_arr as $val) {
            //销售订单
            $recode_code_arr[] = $val['orderId'];
            $val['warehouseCode'] = $this->config_store[$val['store_code']];
            $val['customerCode'] = $this->config_shop[$val['shop_code']];
            //上传数据拼装
            $oms_sell_arr[$val['orderId']] = $val;
            unset($oms_sell_arr[$val['orderId']]['store_code']);
            unset($oms_sell_arr[$val['orderId']]['delivery_time']);
            unset($oms_sell_arr[$val['orderId']]['shop_code']);
            unset($oms_sell_arr[$val['orderId']]['upload_status']);
            unset($oms_sell_arr[$val['orderId']]['deal_code']);
            unset($oms_sell_arr[$val['orderId']]['deal_code_list']);
            //记录日志数据
            $oms_sell_arr_log[$val['orderId']]['order_type'] = 1;
            $oms_sell_arr_log[$val['orderId']]['sell_record_code'] = $val['orderId'];
            $oms_sell_arr_log[$val['orderId']]['deal_code'] = $val['deal_code'];
            $oms_sell_arr_log[$val['orderId']]['deal_code_list'] = $val['deal_code_list'];
            $oms_sell_arr_log[$val['orderId']]['store_code'] = $val['store_code'];
            $oms_sell_arr_log[$val['orderId']]['shop_code'] = $val['shop_code'];
            $oms_sell_arr_log[$val['orderId']]['upload_time'] = date("Y-m-d H:i:s");
        }
        foreach ($return_arr as $val) {
            //销售退单
            $return_code_arr[] = $val['orderId'];
            $val['warehouseCode'] = $this->config_store[$val['store_code']];
            $val['customerCode'] = $this->config_shop[$val['shop_code']];
            //数据拼装
            $oms_return_arr[$val['orderId']] = $val;
            unset($oms_return_arr[$val['orderId']]['store_code']);
            unset($oms_return_arr[$val['orderId']]['agreed_refund_time']);
            unset($oms_return_arr[$val['orderId']]['shop_code']);
            unset($oms_return_arr[$val['orderId']]['upload_status']);
            unset($oms_return_arr[$val['orderId']]['deal_code']);
            unset($oms_return_arr[$val['orderId']]['deal_code_list']);
            //记录日志数据
            $oms_return_arr_log[$val['orderId']]['order_type'] = 2;
            $oms_return_arr_log[$val['orderId']]['sell_record_code'] = $val['orderId'];
            $oms_return_arr_log[$val['orderId']]['deal_code'] = $val['deal_code'];
            $oms_return_arr_log[$val['orderId']]['deal_code_list'] = $val['deal_code_list'];
            $oms_return_arr_log[$val['orderId']]['store_code'] = $val['store_code'];
            $oms_return_arr_log[$val['orderId']]['shop_code'] = $val['shop_code'];
            $oms_return_arr_log[$val['orderId']]['upload_time'] = date("Y-m-d H:i:s");
        }

        //获取销售订单明细
        if (!empty($recode_code_arr)) {
            $recode_code_str = "'" . join("','", $recode_code_arr) . "'";
            $sql = "select num as actualQty,goods_price as amount,barcode as styleCode,spec1_code as colorCode,spec2_code as sizeCode,1 as discount,avg_money as stdprice,sell_record_code as orderId from oms_sell_record_detail where sell_record_code in($recode_code_str)";
            $oms_detail_ret = $this->db->getAll($sql);
            $oms_detail = array();
            foreach ($oms_detail_ret as $detail_row) {
                $oms_detail[$detail_row['orderId']][] = $detail_row;
            }
        }
        //获取销售退单明细
        if (!empty($return_code_arr)) {
            $return_code_str = "'" . join("','", $return_code_arr) . "'";
            $sql = "select recv_num as actualQty,goods_price as amount,barcode as styleCode,spec1_code as colorCode,spec2_code as sizeCode,1 as discount,avg_money as stdprice,sell_return_code as orderId from oms_sell_return_detail where sell_return_code in($return_code_str)";
            $return_detail_ret = $this->db->getAll($sql);
            $return_detail = array();
            foreach ($return_detail_ret as $detail_row) {
                $detail_row['actualQty'] = (int)$detail_row['actualQty'];
                $return_detail[$detail_row['orderId']][] = $detail_row;
            }
        }
        //上传销售订单
        foreach ($oms_sell_arr as $val) {
            //todo 数据格式转换
            $row['orderLine'] = json_encode($oms_detail[$val['orderId']]);
            $row['Order'] = json_encode([$val]);
            //单据上传
            $ret = $this->upload_trade($row);
            if ($ret['status'] == -1) {
                $oms_sell_arr_log[$val['orderId']]['upload_status'] = 2;
                $oms_sell_arr_log[$val['orderId']]['upload_msg'] = $ret['message'];
            } else {
                $oms_sell_arr_log[$val['orderId']]['upload_status'] = 1;
                $oms_sell_arr_log[$val['orderId']]['upload_msg'] = $ret['message'];
            }
            //更新上传表状态
            $update_str = "upload_status = VALUES(upload_status),upload_time = VALUES(upload_time),upload_msg = VALUES(upload_msg)";
            $this->insert_multi_duplicate("api_{$this->tb_key}_trade", $oms_sell_arr_log, $update_str);
        }
        //上传销售退单
        foreach ($oms_return_arr as $val) {
            //todo 数据格式转换
            $row['orderLine'] = json_encode($return_detail[$val['orderId']]);
            $row['Order'] = json_encode([$val]);
            //单据上传
            $ret = $this->upload_trade($row);
            if ($ret['status'] == -1) {
                $oms_return_arr_log[$val['orderId']]['upload_status'] = 2;
                $oms_return_arr_log[$val['orderId']]['upload_msg'] = $ret['message'];
            } else {
                $oms_return_arr_log[$val['orderId']]['upload_status'] = 1;
                $oms_return_arr_log[$val['orderId']]['upload_msg'] = $ret['message'];
            }
            //更新上传表状态
            $update_str = "upload_status = VALUES(upload_status),upload_time = VALUES(upload_time),upload_msg = VALUES(upload_msg)";
            $this->insert_multi_duplicate("api_{$this->tb_key}_trade", $oms_return_arr_log, $update_str);
        }

        return $this->format_ret(1);
    }

    /**
     * 保存商品用新表 api_erp_item
     * @param type $api_data
     */
    function upload_trade($api_data) {
        $ret = $this->erp_client->get_retail_order($api_data);
        return $ret;
    }
}
