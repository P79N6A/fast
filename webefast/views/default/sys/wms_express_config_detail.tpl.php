<style>
    .control-label{
        width: 120px!important;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' =>'新增配送方式映射'));
render_control('Form', 'payment_form', array(
    'conf' => array(
        'fields' => array(
            array('title' => 'wms配置名称', 'type' => 'select', 'field' => 'wms_config_id','data'=>$response['system_arr'],'value'=>$response['wms_config_id']),
            array('title' => 'wms配送方式代码', 'type' => 'input', 'field' => 'out_express_code','value'=>$response['out_express_code']),
            array('title' => '系统配送方式', 'type' => 'select', 'field' => 'express_code','data'=>$response['express_code_arr'],'value'=>$response['express_code']),
            array('title' => '描述', 'type' => 'textarea', 'field' => 'desc','value'=>$response['desc']),
        ),
        'hidden_fields' => array(array('field' => 'wms_id','value'=>$response['wms_id'])),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'sys/wms_express_config/do_edit', // edit,add,view
    'act_add' => 'sys/wms_express_config/do_add',
    'data' => $response['data'],
    'rules' => array(
        array('wms_config_id', 'require'),
        array('out_express_code', 'require'),
        array('express_code', 'require'),
    )
));
?>
<span style="color:red">配送方式映射仅用于WMS配送方式代码与系统配送方式代码不一致的情况。系统会先按获取到的WMS配送方式代码进行匹配，若无法匹配才会查询配送方式映射进行匹配</span>
