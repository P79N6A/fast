<?php

return array(
    //服务中心-产品问他提单-新建检测是否有效
    'issue_add' => array(
        array('sue_cp_id', 'require'),
        array('sue_pv_id', 'require'),
        array('sue_kh_id', 'require'),
        array('sue_title', 'require'),
        array('sue_detail', 'require'),
        array('sue_product_fun', 'require'),
    ),
    //服务中心-产品需求提单-新建检测是否有效
    'xqissue_add' => array(
        array('xqsue_cp_id', 'require'),
        array('xqsue_pv_id', 'require'),
        array('xqsue_kh_id', 'require'),
        array('xqsue_title', 'require'),
        array('xqsue_detail', 'require'),
    ),
);
