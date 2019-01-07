<?php
    return array(
        'upload_request_flag'=>array(
            array('status_code' => 'all', 'status_name' => '全部'),
            array('status_code' => 'wait_upload', 'status_name' => '未上传'),
            array('status_code' => 'uploading', 'status_name' => '上传中'),
            array('status_code' => 'upload_success', 'status_name' => '上传成功'),
            array('status_code' => 'upload_fail', 'status_name' => '上传失败'),
        ),
        'cancel_request_flag'=>array(
            array('status_code' => 'all', 'status_name' => '全部'),
           // array('status_code' => 'wait_cancel', 'status_name' => '未取消'),
            array('status_code' => 'canceling', 'status_name' => '取消中'),
            array('status_code' => 'cancel_success', 'status_name' => '取消成功'),
            array('status_code' => 'cancel_fail', 'status_name' => '取消失败'),
        ),
        'wms_order_flow_end_flag'=>array(
            array('status_code' => 'all', 'status_name' => '全部'),
            array('status_code' => '1', 'status_name' => '已收发货'),
            array('status_code' => '0', 'status_name' => '未收发货'),
        ),
        'process_flag'=>array(
            array('status_code' => 'all', 'status_name' => '全部'),
            array('status_code' => 'order_status_sync_success', 'status_name' => '处理成功'),
            array('status_code' => 'order_status_sync_fail', 'status_name' => '处理失败'),

        ),
    );
?>