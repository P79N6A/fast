<?php


$u['bug_2291']=array(
    "UPDATE sys_params SET param_name='WMS按指定商品下发' WHERE param_code='wms_split_goods_source';",
    "ALTER TABLE wms_archive ADD INDEX ind_wms_config_id (`wms_config_id`) USING BTREE;",
    "ALTER TABLE wms_archive ADD INDEX ind_api_code (`api_code`) USING BTREE;",
    "ALTER TABLE wms_custom_goods_sku ADD INDEX ind_sku (`sku`) USING BTREE;",
    "ALTER TABLE wms_custom_goods_sku ADD INDEX ind_wms_config_id (`wms_config_id`) USING BTREE;",
    "ALTER TABLE goods_sku ADD INDEX ind_goods_code (`goods_code`) USING BTREE;"
);