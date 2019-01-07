<?php
return array(
    'erp_item_download_cmd' =>array(
				'bs3000j' => 'bs3000j_api/item_base_data_sync',
				'bserp' => 'bserp_api/item_base_data_sync',
		),//3000j档案同步
    'erp_barcode_download_cmd' =>array(
                    'bs3000j' => 'bs3000j_api/barcode_sync',
                    'bserp' => 'bserp_api/barcode_sync',
    ),//3000j条码档案同步
    'erp_item_inv_update_cmd' =>array(
                    'bs3000j' => 'bs3000j_api/item_quantity_sync',
                    'bserp' => 'bserp_api/item_quantity_sync',
    ),//3000j库存获取及更新
    'erp_trade_upload_cmd' =>array(
                    'bs3000j' => 'bs3000j_api/trade_upload',
                    'bserp' => 'bserp_api/trade_upload',
    ),//3000j单据上传
);
