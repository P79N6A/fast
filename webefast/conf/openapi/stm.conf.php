<?php

return array(
// 对外开放的接口，只有配置在这里方法才会对外开放调用。
    'api' => array(
        'stm/StmGoodsDiyRecordModel' => array(
            'api_wms_diy_accept' => '',
        ),
        'stm/StockAdjustRecordModel' => array(
            'api_create_adjust_record' => '',
            'api_stock_adjust_create' => '',
            'api_stock_adjust_update' => '',
            'api_stock_adjust_accept' => '',
        ),
        'stm/TakeStockRecordModel' => array(
            'api_stock_create' => '',
            'api_stock_update' => '',
        ),
        'stm/StoreShiftRecordModel' => array(
            'api_shift_record_list_get' => '',
            'api_shift_record_detail_get' => '',
            'api_shift_record_out_accept' => '',
            'api_shift_record_detail_update' => '',
            'api_shift_record_in_accept' => '',
        ),
        'pur/OrderRecordModel' => array(
            'api_pur_notice_create' => '',
            'api_pur_notice_update' => '',
            'api_pur_notice_confirm' => '',
            'api_pur_notice_finish' => '',
            'api_order_notice_list_get' => '',
            'api_order_notice_detail_get' => '',
        ),
        'pur/PurchaseRecordModel' => array(
            'api_pur_record_create' => '',
            'api_pur_record_update' => '',
            'api_pur_record_accept' => '',
        ),
        'pur/ReturnNoticeRecordModel' => array(
            'api_return_notice_create' => '',
            'api_return_notice_update' => '',
            'api_return_notice_confirm' => '',
        ),
        'wbm/StoreOutRecordModel' => array(
            'api_store_out_record_create' => '',
            'api_store_out_detail_update' => '',
            'api_store_out_record_accept' => '',
        ),
        'wbm/ReturnRecordModel' => array(
            'api_return_record_create' => '',
            'api_return_detail_update' => '',
            'api_return_record_accept' => '',
        ),
        'wms/iwms/IwmsBillApiModel' => array(
            'api_shift_record_process' => '',
        ),
        'wbm/NoticeRecordModel' => array(
            'api_notice_record_get' => '',
        ),
        'wbm/NoticeRecordDetailModel' => array(
            'api_notice_detail_get' => '',
        ),
        'wbm/ReturnNoticeRecordModel' => array(
            'api_return_notice_get' => '',
        ),
        'wbm/ReturnNoticeDetailRecordModel' => array(
            'api_return_notice_detail_get' => '',
        ),
        'pur/OrderRecordApiModel' => array(
            'api_detail_update' => '',
        ),
        'pur/PurchaseRecordApiModel' => array(
            'api_record_create' => '',
            'api_record_get' => '',
            'api_detail_get' => '',
            'api_detail_update' => '',
        ),
        'pur/ReturnNoticeApiModel' => array(
            'api_record_get' => '',
            'api_detail_get' => '',
        ),
        'pur/PurchaseReturnApiModel' => array(
            'api_record_create' => '',
            'api_record_get' => '',
            'api_detail_get' => '',
            'api_detail_update' => '',
            'api_record_accept' => '',
        ),
        'stm/TakeStockRecordApiModel' => array(
            'api_record_get' => '',
            'api_detail_update' => '',
        ),
        'wbm/StoreOutRecordApiModel' => array(
            'api_record_create' => '',
            'api_record_get' => '',
            'api_detail_get' => '',
            'api_detail_update' => '',
        ),
        'wbm/ReturnNoticeApiModel' => array(
            'api_record_get' => '',
        ),
        'wbm/ReturnRecordApiModel' => array(
            'api_record_get' => '',
            'api_detail_get' => '',
            'api_detail_update' => '',
        ),
        'b2b/BoxRecordApiModel' => array(
            'api_record_create' => '',
            'api_detail_update' => '',
            'api_record_accept' => '',
            'api_box_record_print' => '',
            'api_box_record_mark_print' => '',
        ),
    ),
    // 设置别名，可以根据接口别名路由到对应的model方法。
    'alias' => array(
        'stm.adjust.create' => 'stm/StockAdjustRecordModel::api_create_adjust_record',
        'stm.stock.create' => 'stm/TakeStockRecordModel::api_stock_create',
        'stm.stock.update' => 'stm/TakeStockRecordModel::api_stock_update',
        'pur.notice.create' => 'pur/OrderRecordModel::api_pur_notice_create', //创建采购通知单
        'pur.notice.update' => 'pur/OrderRecordModel::api_pur_notice_update', //更新采购通知单（明细提交）
        'pur.notice.confirm' => 'pur/OrderRecordModel::api_pur_notice_confirm', //确认采购通知单
        'pur.notice.finish' => 'pur/OrderRecordModel::api_pur_notice_finish', //完成采购通知单
        'pur.notice.list.get' => 'pur/OrderRecordModel::api_order_notice_list_get', //采购通知单列表查询
        'pur.notice.detail.get' => 'pur/OrderRecordModel::api_order_notice_detail_get', //采购通知单明细查询
        'pur.record.create' => 'pur/PurchaseRecordModel::api_pur_record_create', //根据通知单创建采购入库单
        'pur.record.update' => 'pur/PurchaseRecordModel::api_pur_record_update', //更新采购入库单
        'pur.record.accept' => 'pur/PurchaseRecordModel::api_pur_record_accept', //验收采购入库单
        'pur.return.notice.create' => 'pur/ReturnNoticeRecordModel::api_return_notice_create', //创建采购退货通知单
        'pur.return.notice.update' => 'pur/ReturnNoticeRecordModel::api_return_notice_update', //更新采购退货通知单（明细提交）
        'pur.return.notice.confirm' => 'pur/ReturnNoticeRecordModel::api_return_notice_confirm', //确认采购退货通知单
        'stm.diy.accept' => 'stm/StmGoodsDiyRecordModel::api_wms_diy_accept', //组装单确认调整
        'stm.stock.adjust.create' => 'stm/StockAdjustRecordModel::api_stock_adjust_create', //创建仓库调整单
        'stm.stock.adjust.update' => 'stm/StockAdjustRecordModel::api_stock_adjust_update', //更新仓库调整单
        'stm.stock.adjust.accept' => 'stm/StockAdjustRecordModel::api_stock_adjust_accept', //验收仓库调整单
        'stm.shift.record.list.get' => 'stm/StoreShiftRecordModel::api_shift_record_list_get', //移仓单列表查询
        'stm.shift.record.detail.get' => 'stm/StoreShiftRecordModel::api_shift_record_detail_get', //移仓单明细查询
        'stm.shift.record.out.accept' => 'stm/StoreShiftRecordModel::api_shift_record_out_accept', //移仓单移出验收
        'stm.shift.record.detail.update' => 'stm/StoreShiftRecordModel::api_shift_record_detail_update', //移仓单明细移入数量更新
        'stm.shift.record.in.accept' => 'stm/StoreShiftRecordModel::api_shift_record_in_accept', //移仓单移入验收
        'stm.shift.record.process' => 'wms/iwms/IwmsBillApiModel::api_shift_record_process', //移仓单移入验收
        'wbm.order.create' => 'wbm/StoreOutRecordModel::api_store_out_record_create', //创建批发销货单
        'wbm.order.detail.update' => 'wbm/StoreOutRecordModel::api_store_out_detail_update', //更新批发销货单（明细提交）
        'wbm.order.accept' => 'wbm/StoreOutRecordModel::api_store_out_record_accept', //验收批发销货单（影响库存）
        'wbm.return.create' => 'wbm/ReturnRecordModel::api_return_record_create', //创建批发退货单
        'wbm.return.detail.update' => 'wbm/ReturnRecordModel::api_return_detail_update', //更新批发退货单（明细提交）
        'wbm.return.accept' => 'wbm/ReturnRecordModel::api_return_record_accept', //验收批发退货单（影响库存）
        'wbm.notice.list.get' => 'wbm/NoticeRecordModel::api_notice_record_get', //批发销货通知单查询
        'wbm.notice.detail.get' => 'wbm/NoticeRecordDetailModel::api_notice_detail_get', //批发销货通知单明细查询
        'wbm.return.notice.list.get' => 'wbm/ReturnNoticeRecordModel::api_return_notice_get', //批发退货通知单查询
        'wbm.return.notice.detail.get' => 'wbm/ReturnNoticeDetailRecordModel::api_return_notice_detail_get', //批发退货通知单明细查询

        /* ---进销存接口 2.0--- */
        'stm.stock.record.get' => 'stm/TakeStockRecordApiModel::api_record_get', //查询盘点单 2.0
        'stm.stock.detail.update' => 'stm/TakeStockRecordApiModel::api_detail_update', //更新盘点单明细 2.0
        'pur.notice.detail.update' => 'pur/OrderRecordApiModel::api_detail_update', //采购通知单明细更新 2.0
        'pur.record.produce' => 'pur/PurchaseRecordApiModel::api_record_create', //创建采购入库单 2.0
        'pur.record.get' => 'pur/PurchaseRecordApiModel::api_record_get', //查询采购入库单 2.0
        'pur.record.detail.get' => 'pur/PurchaseRecordApiModel::api_detail_get', //查询采购入库单明细 2.0
        'pur.record.detail.update' => 'pur/PurchaseRecordApiModel::api_detail_update', //更新采购入库单明细 2.0
        'pur.return.notice.get' => 'pur/ReturnNoticeApiModel::api_record_get', //查询采购退货通知单 2.0
        'pur.return.notice.detail.get' => 'pur/ReturnNoticeApiModel::api_detail_get', //查询采购退货通知单明细 2.0
        'pur.return.produce' => 'pur/PurchaseReturnApiModel::api_record_create', //创建采购退货单 2.0
        'pur.return.get' => 'pur/PurchaseReturnApiModel::api_record_get', //查询采购退货单 2.0
        'pur.return.detail.get' => 'pur/PurchaseReturnApiModel::api_detail_get', //查询采购退货单明细 2.0
        'pur.return.detail.update' => 'pur/PurchaseReturnApiModel::api_detail_update', //更新采购退货单明细 2.0
        'pur.return.accept' => 'pur/PurchaseReturnApiModel::api_record_accept', //验收采购退货单 2.0
        'wbm.record.produce' => 'wbm/StoreOutRecordApiModel::api_record_create', //创建批发销货单 2.0
        'wbm.record.get' => 'wbm/StoreOutRecordApiModel::api_record_get', //查询批发销货单 2.0
        'wbm.record.detail.get' => 'wbm/StoreOutRecordApiModel::api_detail_get', //查询批发销货单明细 2.0
        'wbm.record.detail.update' => 'wbm/StoreOutRecordApiModel::api_detail_update', //更新批发销货单明细 2.0
        'wbm.return.notice.get' => 'wbm/ReturnNoticeApiModel::api_record_get', //查询批发退货通知单 2.0
        'wbm.return.record.get' => 'wbm/ReturnRecordApiModel::api_record_get', //查询批发退货单 2.0
        'wbm.return.record.detail.get' => 'wbm/ReturnRecordApiModel::api_detail_get', //查询批发退货单明细 2.0
        'wbm.return.record.detail.update' => 'wbm/ReturnRecordApiModel::api_detail_update', //更新批发退货单明细 2.0
        'b2b.box.record.produce' => 'b2b/BoxRecordApiModel::api_record_create', //创建装箱单
        'b2b.box.record.accept' => 'b2b/BoxRecordApiModel::api_record_accept', //装箱单验收
        'b2b.box.record.detail.update' => 'b2b/BoxRecordApiModel::api_detail_update', //装箱单明细更新
        'b2b.box.record.print' => 'b2b/BoxRecordApiModel::api_box_record_print', //装箱单打印数据获取
        'b2b.box.record.mark.print' => 'b2b/BoxRecordApiModel::api_box_record_mark_print', //装箱单箱唛打印数据获取
    )
);
