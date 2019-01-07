<?php
render_control('PageHead', 'head1', array('title' => isset($app['title']) ? $app['title'] : '编辑平台',
    'links' => array(
        array('url' => 'basedata/platform/do_list', title => '平台列表')
    )
));
?>
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left">平台详情</h3>
    </div>
    <div class="panel-body">   
        <?php render_control('Form', 'form1', array(
            'conf' => array(
                'fields' => array(
                    array('title' => '平台代码', 'type' => 'input', 'field' => 'pt_code', 'edit_scene' => 'add'),
                    array('title' => '平台名称', 'type' => 'input', 'field' => 'pt_name',),
                    array('title' => '平台官网URL', 'type' => 'input', 'field' => 'pt_offurl',),
                    array('title' => '技术平台URL', 'type' => 'input', 'field' => 'pt_techurl',),
                    array('title' => '服务市场URL', 'type' => 'input', 'field' => 'pt_serurl',),
                    array('title' => '状态', 'type' => 'checkbox', 'field' => 'pt_state',),
                    array('title' => '平台LOGO', 'type' => 'file', 'text' => '选择', 'field' => 'pt_logo',
                        'rules' => array('ext' => '.png,.jpg,.gif')),
                    array('title' => '付款类型', 'type' => 'select', 'field' => 'pt_pay_type', 'data' => ds_get_select_by_field('pay_type', 2)),
                    array('title' => '描述', 'type' => 'textarea', 'field' => 'pt_bz',),
                ),
                'hidden_fields' => array(array('field' => 'pt_id'), array('field' => 'pt_code'),),
            ),
            'buttons' => array(
                array('label' => '提交', 'type' => 'submit'),
                array('label' => '重置', 'type' => 'reset'),
            ),
            'col' => 2,
            'act_edit' => 'basedata/platform/do_edit', //edit,add,view
            'act_add' => 'basedata/platform/do_add',
            'data' => $response['data'],
            'callback'=>'submitCall',
            'rules' => array(
                array('pt_code', 'require'),
                array('pt_name', 'require'),
                array('pt_pay_type', 'require')),
        ));
        ?>
    </div>
</div>



<script type="text/javascript">
    function pt_logoUploader_success(result) {
        var url = '<?php echo get_app_url('common/file/img') ?>&f=' + $.parseJSON(result.data.path)[0];
        $('#pt_logoUploader .bui-queue-item-success .success').html('<img src="' + url + '" style="width:100px; height:100px"/>')
    }

    function submitCall(data, esfrmId) {
        var scene = "<?php echo $app['scene'] ?>";
        var type = data.status == 1 ? 'success' : 'error';
        BUI.Message.Alert(data.message, function() {
            if (data.status == 1) {
                var ptid = "";
                if (scene == "add")
                    ptid = data.data;
                else
                    ptid = $("#pt_id").val();
                window.location = '?app_act=basedata/platform/detail&app_scene=edit&_id=' + ptid;
            }
        }, type);
    }


</script>