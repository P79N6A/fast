<?php echo load_js('ueditor1_4_3/ueditor.config.js,ueditor1_4_3/ueditor.all.js')?>
<?php
$fields = array(
    array('title' => '提单编号', 'type' => 'input', 'field' => 'xqsue_number', 'edit_scene' => 'add'),
    array('title'=>'紧急程度', 'type'=>'select', 'field'=>'xqsue_urgency','data' => ds_get_select_by_field('xqsue_urgency', 2), 'edit_scene' => 'edit'),
    array('title'=>'难易度', 'type'=>'select', 'field'=>'xqsue_difficulty','data' => ds_get_select_by_field('xqsue_difficulty', 2), 'edit_scene' => 'edit'),
    array('title'=>'备注', 'type'=>'richinput', 'field'=>'xqsue_remark','span'=>15,),
);
$hidden_fields = array(array('field' => 'xqsue_number'));

render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => $fields,
        'hidden_fields' => $hidden_fields,
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'servicenter/productxqissue/do_xqissue_edit_reark', //edit,add,view
    //'act_add' => 'servicenter/productxqissue/do_xqissue_add',
    'callback' => 'after_submit',
    'data' => $response['data'],
    'rules' => array(
        array('xqsue_urgency', 'require'),
        array('xqsue_difficulty', 'require'),
    ),
));
?>

<script type="text/javascript">
    //表单提交前操作
    form.on('beforesubmit', function () {
    });

    //回调函数
    function after_submit(result, ES_frmId) {
        //关闭弹窗
        ui_closePopWindow(ES_frmId);
    }


</script>

