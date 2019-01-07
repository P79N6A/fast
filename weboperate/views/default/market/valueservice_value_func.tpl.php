<?php render_control('PageHead', 'head1',
    array('title' => isset($app['title']) ? $app['title'] : '',
    )); ?>
<?php render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => array(
            array('title' => '业务ID', 'type' => 'input', 'field' => 'vd_busine_id',),
            array('title' => '业务代码', 'type' => 'input', 'field' => 'vd_busine_code',),
            array('title' => '功能类型', 'type' => 'select', 'field' => 'vd_busine_type', 'data' => ds_get_select_by_field('valueserver_type', 3)),
            array('title' => '备注', 'type' => 'textarea', 'field' => 'remark'),
        ),
        'hidden_fields' => array(array('field' => 'vd_id'), array('field' => 'value_id', 'value' => $request['value_id']),),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'market/valueservice/do_vfunc_edit', //edit,add,view
    'act_add' => 'market/valueservice/do_vfunc_add',
    'data' => $response['data'],
    'rules' => array(
        array('vd_busine_id', 'require'),
        array('vd_busine_code', 'require'),
    ),        //有效性验证
    'callback'=>'after_submit',
)); ?>
<script type="text/javascript">

    function after_submit(data, Esfrom_Id) {
        if (data.status == 1) {
            BUI.Message.Alert(data.message, function () {
                ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
            }, 'success');
        } else {
            BUI.Message.Alert(data.message, 'error');
        }
    }

</script>