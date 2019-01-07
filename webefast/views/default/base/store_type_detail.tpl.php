<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '添加仓库模板',
));?>
<?php
$tabs = array(
    array('title' => '基本信息', 'active' => true, 'id' => 'tabs_base'),
);

$button = array(
    array('label' => '提交', 'type' => 'submit'),
    array('label' => '重置', 'type' => 'reset'),
);

render_control('TabPage', 'TabPage1', array(
    'tabs' => $tabs,
    'for' => 'TabPage1Contents'
));?>

<div id="TabPage1Contents">
    <div>
        <?php
        render_control('Form', 'form1', array(
            'conf' => array(
                'fields' => array(
                    array('title' => '类别代码', 'type' => 'input', 'field' => 'type_code', 'remark' => '一旦保存不能修改!', 'edit_scene' => 'add'),
                    array('title' => '类别名称', 'type' => 'input', 'field' => 'type_name'),
                    array('title'=>'备注', 'type'=>'textarea', 'field'=>'remark'),
                ),
                'hidden_fields' => array(array('field' => 'id')),
            ),
            'buttons' => $button,
            'act_edit' => 'base/store_type/do_edit&app_fmt=json', //edit,add,view
            'act_add' => 'base/store_type/do_add&app_fmt=json',
            'data' => $response['data'],
            'rules' => array(
                array('store_code', 'require'),
                array('store_name', 'require'),
            )
        ))
        ?>
    </div>
</div>


<?php echo load_js('comm_util.js')?>
<script type="text/javascript">
var handle_type = "<?php echo $app['scene'];?>";

$("#ship_area_code").attr("disabled", "disabled");

form.on('beforesubmit', function () {
    $("#ship_area_code").attr("disabled", false);
});

$(document).ready(function(){
   if(handle_type == 'edit'){
       $("label[for='type_code']").removeClass("control-label control-operate-label");
       $("label[for='type_code']").addClass('remark');
   }
   
   $("#TabPage1Submit").find("#submit").click(function(){
       var data = new Object();
       $("#form1").find("input").each(function(){
           data[$(this).attr("id")] = $(this).val();
       });
       $.post("?app_act=base/store_type/do_edit&app_fmt=json",data,function(ret){
            BUI.Message.Alert(ret.message);
       },'json');
   });
});

</script>

