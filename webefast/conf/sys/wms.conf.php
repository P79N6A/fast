<?php

return array(
    //api_sync 1 同步 0 异步
    //not_effect_inv 不影响哪种单据
    //intercept_is_cancel 订单拦截，是否作废订单
    'iwms' => array(
        'name' => '百胜iWMS', 
        'api_sync' => 1, 
        'not_effect_inv' => array('purchase', 'oms_return', 'wbm_return','shift_in'), 
        'intercept_is_cancel' => false
    ),
    'iwmscloud' => array(
        'name' => '胜境WMS365', 
        'api_sync' => 1, 
        'not_effect_inv' => array('purchase', 'oms_return', 'wbm_return','shift_in'), 
        'intercept_is_cancel' => false
    ),
    'ydwms' => array('name' => '韵达WMS',
        'api_sync' => 1,
        'not_effect_inv' => array('purchase', 'oms_return', 'wbm_return','shift_in'), 
        'intercept_is_cancel' => TRUE,
         'effect_inv_type'=> 1,
    ),
    
     'sfwms' => array('name' => '顺丰WMS',
        'api_sync' => 1,
        'not_effect_inv' =>array('purchase', 'oms_return', 'wbm_return','shift_in'), 
        'intercept_is_cancel' => false,
         //顺丰仓储模板
         'wms_storage_template'=>array(
             '0'=>'件',//默认
             '1'=>'',//客户ID 对应模板
         ),
         
      ),
    'bswms' => array(
            'name' => '百世WMS',
            'api_sync' => 1,
            'not_effect_inv' => array('purchase', 'oms_return', 'wbm_return','shift_in'),
            'intercept_is_cancel' => false
    ),
    'qimen' => array(
            'name' => '奇门',
            'api_sync' => 1,
            'not_effect_inv' => array('purchase', 'oms_return', 'wbm_return','shift_in'),
            'intercept_is_cancel' => false,
            'effect_inv_type'=> 1,
    ),
    'hwwms' => array(
            'name' => '汉维WMS',
            'api_sync' => 1,
            'not_effect_inv' => array('purchase', 'oms_return', 'wbm_return','shift_in'),
            'intercept_is_cancel' => false,
             'is_lof'=>TRUE,
             'effect_inv_type'=> 1,
    ),
    'apiwms' => array(
            'name' => '自主WMS',
            'api_sync' => 1,
            'not_effect_inv' => array(),
            'intercept_is_cancel' => false,
    ),
    'shunfeng' => array(
        'name' => '顺丰WMS(新)',
        'api_sync' => 1,
        'not_effect_inv' => array('purchase', 'oms_return', 'wbm_return','shift_in'),
        'intercept_is_cancel' => false,
         //顺丰仓储模板
        'wms_storage_template'=>array(
            '0'=>'件',//默认
            '1'=>'',//客户ID 对应模板
        ),
        'effect_inv_type'=> 1,
    ),
    'jdwms' => array(
        'name' => '京东沧海WMS',
        'api_sync' => 1,
        'not_effect_inv' => array(),
        'intercept_is_cancel' => false
    ),
    'jdwmscloud' => array(
        'name' => '京东物流云',
        'api_sync' => 1,
        'not_effect_inv' => array(),
        'intercept_is_cancel' => false
    ),
       'lifeng' => array(
        'name' => '利丰仓储',
        'api_sync' => 0,
        'not_effect_inv' => array(),
        'intercept_is_cancel' => false
    ),
    //去掉盛汇购个性化
//    'shwms' =>array(
//        'name' => '盛汇购',
//        'api_sync' => 1,
//        'not_effect_inv' => array(),
//        'intercept_is_cancel' => false
//    )
);
?>
