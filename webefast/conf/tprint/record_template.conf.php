<?php

return array(
    'data_source' => array(
        'guarantee_flash' => 'sys/flash_templates/edit&template_id=211&model=oms/DeliverRecordWtyModel&typ=default', //质保单
        'wbm_store_out_new' => 'tprint/tprint/do_edit&print_templates_code=wbm_store_out_new', //批发销货单模板
        'wbm_store_out' => 'sys/flash_templates/edit&template_code=wbm_store_out&model=wbm/StoreOutRecordModel&typ=default', //批发销货单模板
        'wbm_notice' => 'tprint/tprint/do_edit&print_templates_code=wbm_notice', //批发通知单模版
        'oms_waves_record' => 'sys/flash_templates/edit&template_code=oms_waves_record&model=oms/WavesRecordModel&typ=default', //波次单模板
        'oms_waves_record_new' => 'tprint/tprint/do_edit&print_templates_code=oms_waves_record_new', //波次单模板(新)
        'store_shift' => 'tprint/tprint/do_edit&print_templates_code=store_shift', //移仓单模版
        'pur_return_new' => 'tprint/tprint/do_edit&print_templates_code=pur_return_new', //采购退货单模板
        'pur_return' => 'sys/flash_templates/edit&template_code=pur_return&model=pur/ReturnRecordModel&typ=default&tabs=pur#', //采购退货单模板
        'barcode' => 'sys/flash_templates/edit_td&template_id=11&model=prm/GoodsBarcodeModel&typ=default', //条码模板
        'b2b_box' => 'tprint/tprint/do_edit&print_templates_code=b2b_box', //装箱单模板
        'aggr_box' => 'tprint/tprint/do_edit&print_templates_code=aggr_box', //装箱汇总单模板
        'send_record_flash' => 'sys/flash_templates/edit&template_id=5&model=oms/DeliverRecordModel&typ=default', //发货单模板
        'deliver_record' => 'tprint/tprint/do_edit&print_templates_code=deliver_record',//发货单模板(新)
        'pur_purchaser' => 'sys/flash_templates/edit&template_code=pur_purchaser&model=pur/PurchaseRecordModel&typ=default&tabs=pur', //采购入库单模板
        'pur_purchaser_new' => 'tprint/tprint/do_edit&print_templates_code=pur_purchaser_new',
        'weipinhuijit_box_print' => 'sys/weipinhuijit_box_print/do_list', //箱唛打印模板
        'invoice_record' => 'sys/flash_templates/edit_td&template_id=31&model=oms/InvoiceRecordModel&typ=default', //发票模板
        'sell_return' => 'tprint/tprint/do_edit&print_templates_code=sell_return', //售后服务单模版
        'cashier_ticket' => 'tprint/tprint/do_edit&print_templates_code=cashier_ticket', //售后服务单模版
        'wbm_return_new' => 'tprint/tprint/do_edit&print_templates_code=wbm_return_new', //批发退货单模板
        'wbm_return' => 'sys/flash_templates/edit&template_code=wbm_return&model=wbm/ReturnRecordModel&typ=default', //批发退货单模板
        'wbm_notice_store_out_new' => 'tprint/tprint/do_edit&print_templates_code=wbm_notice_store_out_new', //批发销货通知单模板
        'wbm_notice_store_out' => 'sys/flash_templates/edit&template_code=wbm_notice_store_out&model=wbm/NoticeRecordModel&typ=default', //批发销货通知单模板
        'pur_planned_record' => 'tprint/tprint/do_edit&print_templates_code=pur_planned_record',
        'wbm_store_out_record_goods' => 'tprint/tprint/do_edit&print_templates_code=wbm_store_out_record_goods',
        'oms_waves_record_clothing'=>'tprint/tprint/do_edit&print_templates_code=oms_waves_record_clothing',//波次单模板（服装行业）
        'wbm_store_out_clothing'=>'tprint/tprint/do_edit&print_templates_code=wbm_store_out_clothing',//批发销货单模板（服装行业）
    ),
    'template_code' => array(
        'guarantee_flash', 'wbm_store_out','wbm_store_out_record_goods','wbm_store_out_new', 'wbm_notice', 'oms_waves_record','oms_waves_record_new', 'store_shift', 'pur_purchaser','pur_purchaser_new', 'pur_return','pur_return_new', 'barcode', 'b2b_box', 'aggr_box', 'send_record_flash', 'deliver_record','weipinhuijit_box_print', 'invoice_record', 'sell_return', 'cashier_ticket', 'wbm_return','wbm_return_new', 'wbm_notice_store_out','wbm_notice_store_out_new','pur_planned_record','barcode_lodop','oms_waves_record_clothing','wbm_store_out_clothing'
    )
);

