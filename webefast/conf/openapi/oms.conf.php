<?php

return array(
// 对外开放的接口，只有配置在这里方法才会对外开放调用。
    'api' => array(
        'oms/SellRecordModel' => array(
            'api_order_list_get' => '',
            'api_order_detail_get' => '',
            'api_order_intercep' => '',
            'api_order_search_get' => '',
        ),
        'oms/SellRecordOptModel' => array(
            'api_order_send' => '',
            'api_order_cancel' => ''
        ),
        'oms/SellReturnModel' => array(
            'api_order_return_list_get' => '',
            'api_order_return_detail_get' => '',
            'api_order_return_list' => '',
        ),
        'oms/SellReturnOptModel' => array(
            'api_opt_return_shipping' => '',
        ),
        'oms/SellRecordActionModel' => array(
            'api_order_log_get' => '',
        ),
        'oms/ApiOrderModel' => array(
            'api_add_order' => '',
            'get_order_send_status' => '',
        ),
        'api/sys/OrderRefundModel' => array(
            'api_add_refund_order' => '',
        ),
        'oms/WavesRecordModel' => array(
            'api_wave_order_get' => '',
        ),
        'oms/ReturnPackageModel' => array(
            'api_return_package_create' => '',
        ),
        'oms/SellRecordProcessModel' => array(
            'api_order_process_report' => '',
        ),
        'mid/mes/MidMesPubModel' => array(
            'mes_shipping' => '',
        ),
        'oms/SellRecordHistoryImport' => array(
            'api_shipped_order_add' => '',
            'api_shipped_fxorder_add' => '',
        ),
        'oms/WavesRecordApiModel' => array(
            'api_wave_census_get' => '',
            'api_wave_info_get' => '',
            'api_wave_goods_get' => '',
            'api_wave_accept' => '',
            'api_cainiao_waybill_get' => '',
            'api_wave_shipping_get' => '',
            'api_wave_send' => '',
            'api_wave_record_scan' => '',
            'api_wave_barcode_scan' => '',
        ),
        'oms/SellRecordApiModel' => array(
            'api_express_return' => '',
            'api_package_receive_scan' => '',
            'api_package_census_get' => '',
            'api_deliver_print_struct_get' => '',
        ),
        'oms/DeliverRecordApiModel' => array(
            'api_record_express_scan' => '',
            'api_record_barcode_scan' => '',
        ),
        'erp/ErpApiModel' => array(
            'api_record_marking' => '',
        ),
        'oms/invoice/OmsSellInvoiceModel' => array(
            'api_sell_invoice_get' => '',
        ),
    ),
    // 设置别名，可以根据接口别名路由到对应的model方法。
    'alias' => array(
        'oms.order.list.get' => 'oms/SellRecordModel::api_order_list_get', //已发货订单列表（包含订单详细信息）
        'oms.order.detail.get' => 'oms/SellRecordModel::api_order_detail_get',
        'oms.order.search.get' => 'oms/SellRecordModel::api_order_search_get', //订单查询
        'oms.order.return.list.get' => 'oms/SellReturnModel::api_order_return_list_get', //收发货
        'oms.order.return.list' => 'oms/SellReturnModel::api_order_return_list', //退单查询
        'oms.order.return.detail.get' => 'oms/SellReturnModel::api_order_return_detail_get',
        'oms.order.send' => 'oms/SellRecordOptModel::api_order_send', //订单发货
        'oms.sell.settlement.list.get' => 'oms/SellSettlementModel::api_sell_settlement_list_get', //零售结算明细
        'oms.api.order.add' => 'oms/ApiOrderModel::api_add_order', //添加订单
        'oms.order.return.add' => 'api/sys/OrderRefundModel::api_add_refund_order', //创建退单
        'oms.order.send.get' => 'oms/OrderRefundModel::get_order_send_status',
        'oms.order.intercep' => 'oms/SellRecordModel::api_order_intercep', //订单拦截
        'oms.wave.order.get' => 'oms/WavesRecordModel::api_wave_order_get', //获取波次订单数据
        'oms.order.cancel' => 'oms/SellRecordOptModel::api_order_cancel', //订单作废
        'oms.return.shipping' => 'oms/SellReturnOptModel::api_opt_return_shipping', //退单收货
        'oms.package.create' => 'oms/ReturnPackageModel::api_return_package_create', //退单收货
        'oms.orderprocess.report' => 'oms/SellRecordProcessModel::api_order_process_report', //订单流水通知
        'oms.mid.shipping' => 'mid/mes/MidMesPubModel::mes_shipping', //mes收发货
        'oms.order.log.get' => 'oms/SellRecordActionModel::api_order_log_get', //订单日志查询
        'oms.shipped.order.add' => 'oms/SellRecordHistoryImport::api_shipped_order_add', //已发货普通订单新增
        'oms.shipped.fxorder.add' => 'oms/SellRecordHistoryImport::api_shipped_fxorder_add', //已发货分销订单新增
        'oms.wave.census.get' => 'oms/WavesRecordApiModel::api_wave_census_get', //获取波次单汇总
        'oms.wave.info.get' => 'oms/WavesRecordApiModel::api_wave_info_get', //获取波次单信息
        'oms.wave.goods.get' => 'oms/WavesRecordApiModel::api_wave_goods_get', //获取单品单件波次单商品
        'oms.wave.accept' => 'oms/WavesRecordApiModel::api_wave_accept', //波次订单验收
        'oms.cainiao.waybill.get' => 'oms/WavesRecordApiModel::api_cainiao_waybill_get', //获取菜鸟电子面单号
        'oms.wave.shipping.get' => 'oms/WavesRecordApiModel::api_wave_shipping_get', //获取待发货订单
        'oms.wave.send' => 'oms/WavesRecordApiModel::api_wave_send', //波次订单发货
        'oms.wave.record.scan' => 'oms/WavesRecordApiModel::api_wave_record_scan', //二次分拣-波次扫描
        'oms.wave.barcode.scan' => 'oms/WavesRecordApiModel::api_wave_barcode_scan', //二次分拣-波次条码扫描
        'oms.express.return' => 'oms/SellRecordApiModel::api_express_return', //回传快递单号（发货前）
        'oms.record.express.scan' => 'oms/DeliverRecordApiModel::api_record_express_scan', //快递单号扫描发货
        'oms.record.barcode.scan' => 'oms/DeliverRecordApiModel::api_record_barcode_scan', //扫描条码记录
        'oms.package.receive.scan' => 'oms/SellRecordApiModel::api_package_receive_scan', //快递交接扫描
        'oms.package.cencus.get' => 'oms/SellRecordApiModel::api_package_census_get', //快递交接统计
        'oms.record.marking' => 'erp/ErpApiModel::api_record_marking', //快递交接统计
        'oms.invoice.info.get' => 'oms/invoice/OmsSellInvoiceModel::api_sell_invoice_get', //订单开票信息查询
        'print.struct.get' => 'oms/SellRecordApiModel::api_deliver_print_struct_get', //打印数据结构获取
    )
);
