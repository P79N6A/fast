<style type="text/css">
</style>
<?php
$button = array(
    array('label' => '提交', 'type' => 'submit'),
    array('label' => '重置', 'type' => 'reset'),
);
?>
<div id="TabPage1Contents">
    <div>
        <?php
        $fields = array(
            array('title' => '快递编码', 'type' => 'input', 'field' => 'company_code', ),//'remark' => '请保持和平台一致'
            array('title' => '快递公司', 'type' => 'input', 'field' => 'company_name',),
            array('title' => '快递规则', 'type' => 'textarea', 'field' => 'rule', ),//'remark' => '请保持和平台一致'
        );
        render_control('Form', 'form1', array(
            'conf' => array(
                'fields' => $fields,
                // 'hidden_fields' => array(array('field' => 'custom_id')),
            ),
            'buttons' => $button,
            //'act_edit' => 'base/custom/do_edit', //edit,add,view
            'act_add' => 'pubdata/pubdata/do_add',
            'data' => $response['data'],
            'callback' => 'after_submit',
            'rules' => array(
                array('company_code', 'require'),
                array('company_name', 'require'),
            ),
        ));
        ?>
    </div>
</div>
<?php echo load_js('comm_util.js') ?>
<script type="text/javascript">
    $("#country").attr('disabled', true);
    form.on('beforesubmit', function () {

    });

    function after_submit(data, ES_frmId) {
        if (data.status == 1) {
            //ui_closeTabPage('<?php echo $request['ES_frmId'] ?>');//页面
            ui_closePopWindow(ES_frmId);//弹窗
        } else {
            BUI.Message.Alert(data.message, 'error');
        }
    }
</script>

