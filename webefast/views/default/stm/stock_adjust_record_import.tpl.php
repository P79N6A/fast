<?php

render_control('Form', 'table', array(
    'conf' => array(
        'fields' => array(
            array(
                'title' => '单据编号',
                'type' => 'input',
                'text' => '请选择',
                'field' => '',
            ),
            array(
                'title' => '原单号',
                'type' => 'input',
                'text' => '请选择',
                'field' => '',
            ),
            array(
                'title' => '下单日期',
                'type' => 'date',
                'text' => '请选择',
                'field' => '',
            ),
            array(
                'title' => '仓库',
                'type' => 'select',
                'text' => '请选择',
                'field' => '',
            	'data' => $response['store']
            ),
            array(
                'title' => '调整类型',
                'type' => 'select',
                'text' => '请选择',
                'field' => '',
            	'data' => $response['adjust_type']
            ),
            array(
                'title' => '调整原因',
                'type' => 'input',
                'text' => '请选择',
                'field' => '',
            ),
            array(
                'title' => '备注',
                'type' => 'textarea',
                'text' => '请选择',
                'field' => '',
            ),
            array(
                'title' => '选择文件',
                'type' => 'file',
                'text' => '请选择',
                'field' => 'excel_src',
                'rules' => array('ext' => '.xlsx', 'max' => 5, 'minSize' => 1, 'maxSize' => 10240),
            ),
        ),
        'hidden_fields' => array(
            array('field' => 'code'),
            array('field' => 'import_type'),
        ),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '模版下载', 'type' => 'url', 'act' => '?app_act=sys/excel_import/tplDownload&code=oms_stm_stock_adjust_record', 'target'=>'_blank'),
    ),
    'data' => array(
        'code' => 'oms_stm_stock_adjust_record',
        'import_type' => 'import_single',
    ),
    'act_add' => 'sys/excel_import/import',
));
