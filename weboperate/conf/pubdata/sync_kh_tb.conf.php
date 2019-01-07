<?php

return array(
    'base_express_company' => array(
        'column' => array('company_code', 'company_name', 'rule'),
        'update' => array('company_name', 'rule'),
    ),

    'base_area_new' => array(
        'kh_tb' => 'base_area',
        'column' => array('id', 'type', 'name', 'parent_id', 'zip', 'url', 'catch'),
        'update' => array('type', 'zip','url','catch'),
    ),

    'api_weipinhuijit_warehouse' => array(
        'kh_tb' => 'api_weipinhuijit_warehouse',
        'column' => array('warehouse_no', 'warehouse_code', 'warehouse_name', 'create_time','status'),
        'update' => array('warehouse_name','warehouse_no'),
    ),

);
