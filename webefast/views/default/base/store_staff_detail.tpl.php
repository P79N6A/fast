<style type="text/css">
    .form-horizontal .control-label {
        display: inline-block;
        float: left;
        line-height: 30px;
        text-align: left;
        width: 130px;
    }
    .span11 {
        width: 3000px;
    }
</style>
<?php
render_control('PageHead', 'head1',
    array('title' => $app['title'],
    ));
?>
<?php
$tabs = array(
    array('title' => '基本信息', 'active' => true, 'id' => 'tabs_base'),
);
render_control('TabPage', 'TabPage1', array(
    'tabs' => $tabs,
    'for' => 'TabPage1Contents'
)); ?>

<div id="TabPage1Contents">
    <div>
        <?php
        $fields = array(
            array('title' => '员工代码', 'type' => 'input', 'field' => 'staff_code', 'remark' => '一旦保存不能修改!', 'edit_scene' => 'add'),
            array('title' => '员工名称', 'type' => 'input', 'field' => 'staff_name'),
            array('title' => '员工类别', 'type' => 'select', 'field' => 'staff_type', 'data' => $response['staff_type']),
            array('title' => '启用', 'type' => 'checkbox', 'field' => 'status', 'remark' => ''),
        );
        render_control('Form', 'form1', array(
            'conf' => array(
                'fields' => $fields,
                'hidden_fields' => array(array('field' => 'staff_id')),
            ),
            'buttons' => array(
                array('label' => '提交', 'type' => 'submit'),
                array('label' => '重置', 'type' => 'reset'),
            ),
            'act_edit' => 'base/store_staff/do_edit&app_fmt=json', //edit,add,view
            'act_add' => 'base/store_staff/do_add&app_fmt=json',
            'data' => $response['data'],
            'callback' => 'after_submit',
            'rules' => array(
                array('staff_code', 'require'),
                array('staff_name', 'require'),
            )
        ))
        ?>
    </div>
    <?php echo load_js('comm_util.js') ?>
    <script type="text/javascript">
        var type = '<?php echo $response['scene']; ?>';
        //提交成功后回调函数
        function after_submit(data, ES_frmId) {
            if (data.status == 1) {
                BUI.Message.Alert(data.message, 'success');
                ui_closePopWindow('<?php echo $request['ES_frmId'] ?>');//关闭页面
                window.location.reload();//刷新
            } else {
                BUI.Message.Alert(data.message, 'error');
            }
        }


        $(document).ready(function () {
            //新增时默认选中
            if (type == 'add') {
                $('#status').attr('checked', true);
                $('#status').val(1);
                $('#status').click(function () {
                    var update_status = $("#status").attr('checked') == 'checked' ? '1' : '0';
                    $('#status').val(update_status);
                });
            }
        });
    </script>

