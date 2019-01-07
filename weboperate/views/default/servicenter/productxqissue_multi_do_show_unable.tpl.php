<?php render_control('PageHead', 'head1'); ?>
<?php echo load_js('ueditor1_4_3/ueditor.config.js,ueditor1_4_3/ueditor.all.js') ?>
<form class="form-horizontal" id="form1" action="?app_act=servicenter/productxqissue/multi_do_xqissueunable" method="post">
    <?php render_control('Form', 'form1', array(
        'noform' => true,
        'conf' => array(
            'fields' => array(
                array('title' => '需求审批意见', 'type' => 'richinput', 'field' => 'xqsue_idea', 'span' => 15,),
            ),
            'hidden_fields' => array(array('field' => 'xqsue_number'), array('field' => 'type')),
        ),
        'buttons' => array(
            array('label' => '确认', 'type' => 'submit'),
            array('label' => '重置', 'type' => 'reset'),
        ),
        'act_add' => 'servicenter/productxqissue/multi_do_xqissueunable',
        'data' => $response['data'],
        'rules' => array(
            array('xqsue_idea', 'require')),
        'event' => array('beforesubmit' => 'formBeforesubmit'),
    ));
    ?>
</form>
<script type="text/javascript">
    var form = new BUI.Form.HForm({
        srcNode: '#form1',
        submitType: 'ajax',
        callback: function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            if (data.status == 1) {
                ui_closePopWindow('<?php echo CTX()->request['ES_frmId']?>');
                window.location.reload();
            } else {
                BUI.Message.Alert(data.message, function () {
                }, type);
            }
        }
    }).render();

    function formBeforesubmit() {
        return true; // 如果不想让表单继续提交，则return false
    }
</script>
