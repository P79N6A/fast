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
<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '添加仓库模板',
));?>
<?php
$tabs = array(
    array('title' => '基本信息', 'active' => true, 'id' => 'tabs_base'),
);
$button = array();
if($app['scene']!='add'){
    $tabs[]=array('title' => '快递单发货方信息', 'active' => false, 'id' => 'tabs_send');
    //$tabs[]=array('title' => '分销货权设置', 'active' => false, 'id' => 'tabs_fx_manager');
}else{
    $button = array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    );
}
render_control('TabPage', 'TabPage1', array(
    'tabs' => $tabs,
    'for' => 'TabPage1Contents'
));?>

<div id="TabPage1Contents">
    <div>
        <?php
        if($response['service_custom'] == TRUE) {
            $fields = array(
                array('title' => '仓库代码', 'type' => 'input', 'field' => 'store_code', 'remark' => '一旦保存不能修改!', 'edit_scene' => 'add'),
                array('title' => '仓库名称', 'type' => 'input', 'field' => 'store_name'),
                array('title' => '仓库类别', 'type' => 'select', 'field' => 'store_type_code', 'data' => $response['store_type']),
                array('title' => '缺货商品允许发货', 'type' => 'checkbox', 'field' => 'allow_negative_inv', 'remark' => '<span style="color:red;">（开启后可能会导致商品同步库存至销售平台时出现下架的情况，请谨慎开启！）</span>'),
                array('title' => '允许分销商查看库存', 'type' => 'checkbox', 'field' => 'is_enable_custom', 'remark' => '<span style="color:red;">（启用后，分销商即可查看该仓库所有商品库存。）</span>'),
            );
        } else {
            $fields = array(
                array('title' => '仓库代码', 'type' => 'input', 'field' => 'store_code', 'remark' => '一旦保存不能修改!', 'edit_scene' => 'add'),
                array('title' => '仓库名称', 'type' => 'input', 'field' => 'store_name'),
                array('title' => '仓库类别', 'type' => 'select', 'field' => 'store_type_code', 'data' => $response['store_type']),
                array('title' => '缺货商品允许发货', 'type' => 'checkbox', 'field' => 'allow_negative_inv', 'remark' => '<span style="color:red;">（开启后可能会导致商品同步库存至销售平台时出现下架的情况，请谨慎开启！）</span>'),
            );
        }
        render_control('Form', 'form1', array(
            'conf' => array(
                'fields' => $fields,
                'hidden_fields' => array(array('field' => 'store_id')),
            ),
            'buttons' => $button,
            'act_edit' => 'base/store/do_edit&app_fmt=json', //edit,add,view
            'act_add' => 'base/store/do_add&app_fmt=json',
            'data' => $response['data'],
            'callback' => 'goto_edit',
            'rules' => array(
                array('store_code', 'require'),
                array('store_name', 'require'),
            )
        ))
        ?>
    </div>
<?php if($app['scene']!='add'):?>
    <div>
        <?php
        render_control('Form', 'form2', array(
            'conf' => array(
                'fields' => array(
//                    array('title' => '店铺名称', 'type' => 'input', 'field' => 'shop_name',),
                    array('title' => '寄件人', 'type' => 'input', 'field' => 'shop_contact_person',),
                    array('title' => '联系人', 'type' => 'input', 'field' => 'contact_person',),
                    array('title' => '联系电话', 'type' => 'input', 'field' => 'contact_phone',),
                    array('title' => '所在地区 ', 'type' => 'select', 'field' => 'country', 'data' => $response['area']['country']),
                    array('title' => '', 'type' => 'select', 'field' => 'province', 'data' => $response['area']['province']),
                    array('title' => '', 'type' => 'select', 'field' => 'city', 'data' => $response['area']['city']),
                    array('title' => '', 'type' => 'select', 'field' => 'district', 'data' => $response['area']['district']),
                    array('title' => '', 'type' => 'select', 'field' => 'street', 'data' => $response['area']['street']),
                    array('title' => '街道地址', 'type' => 'input', 'field' => 'address',),
                    array('title' => '邮政编码', 'type' => 'input', 'field' => 'zipcode',),
                    array('title' => '店铺留言', 'type' => 'input', 'field' => 'message',),
                    array('title' => '店铺留言2', 'type' => 'input', 'field' => 'message2',),
                    array('title' => '发货区行政区划代码', 'type' => 'input', 'field' => 'ship_area_code', 'show_field'),
                //array('title'=>'文件', 'type'=>'file', 'field'=>'aa'),
                ),
                'hidden_fields' => array(array('field' => 'store_id')),
            ),
            'buttons' => array(),
            'act_edit' => 'base/store/do_edit&app_fmt=json', //edit,add,view
            'act_add' => 'base/store/do_add&app_fmt=json',
            'data' => $response['data'],
            'rules' => array()
        ));
        ?>
    </div>
    <div>
        <?php
//        render_control('Form', 'form3', array(
//            'conf' => array(
//                'fields' => array(
//                    array('title' => '启用分销', 'type' => 'checkbox', 'field' => 'is_enable_cusom'),
//                    array('title'=>'分销商', 'type'=>'select_pop', 'id'=>'p_code', 'select'=>'base/custom' ),
//                ),
//                'hidden_fields' => array(array('field' => 'store_id')),
//            ),
//            'buttons' => array(),
//            'act_edit' => 'base/store/do_edit&app_fmt=json', //edit,add,view
//            'act_add' => 'base/store/do_add&app_fmt=json',
//            'data' => $response['data'],
//            'rules' => array()
//        ));
        ?>
    </div>

    <div id="TabPage1Submit" class="row form-actions actions-bar">
            <div class="span13 offset3 ">
                <button type="submit" class="button button-primary" id="submit">提交</button>
                <button type="reset" class="button " id="reset">重置</button>
            </div>
        </div>
<?php endif;?>
</div>


<?php echo load_js('comm_util.js')?>
<script type="text/javascript">
var custom_name = "<?php echo $response['data']['custom_name']?>";
function goto_edit(data,ES_frmId){
    if(data.status == 1&&'<?php echo $app['scene'];?>'=='add'){
        window.location.href = "?app_act=base/store/detail&app_scene=edit&_id="+data.data+"&ES_frmId="+ES_frmId;
    }else{
    	BUI.Message.Alert(data.message);
    }
}
var url = '<?php echo get_app_url('base/store/get_area');?>';
$("#ship_area_code").attr("disabled", "disabled");

form.on('beforesubmit', function () {
    $("#ship_area_code").attr("disabled", false);
});

$(document).ready(function(){
   $("#TabPage1Submit").find("#submit").click(function(){
       var data = new Object();
       $("#form1").find("input").each(function(){
           data[$(this).attr("id")] = $(this).val();
       });
       $("#form1").find("select").each(function(){
           data[$(this).attr("id")] = $(this).val();
       });
       $("#form2").find("input").each(function(){
           data[$(this).attr("id")] = $(this).val();
       });
       $("#form2").find("select").each(function(){
           data[$(this).attr("id")] = $(this).val();
       });
       $("#form3").find("input").each(function(){
           data[$(this).attr("id")] = $(this).val();
       });
       $.post("?app_act=base/store/do_edit&app_fmt=json",data,function(ret){
            BUI.Message.Alert(ret.message);
       },'json');
   });
	$('#country').change(function(){
        var parent_id = $(this).val();
        areaChange(parent_id,0,url);
        $("#ship_area_code").val(parent_id);
    });
    $('#province').change(function(){
        var parent_id = $(this).val();
        areaChange(parent_id,1,url);
        $("#ship_area_code").val(parent_id);
    });
    $('#city').change(function(){
        var parent_id = $(this).val();
        areaChange(parent_id,2,url);
        $("#ship_area_code").val(parent_id);
    });
    $('#district').change(function(){
        var parent_id = $(this).val();
        areaChange(parent_id,3,url);
        $("#ship_area_code").val(parent_id);
    });

    $('#street').change(function(){
        var parent_id = $(this).val();
        $("#ship_area_code").val(parent_id);
    });
   
})
$(document).ready(function(){
        var html = '';
        html += '<input type="hidden" name="custom_code" value="" id="custom_code">';
        $("#_select_pop").parent().append(html);
        $("#_select_pop").val(custom_name);
    });
     var selectPopWindow = {
        dialog: null,
        callback: function(value) {
            var custom_code = value[0]['custom_code'];
            var custom_name = value[0]['custom_name'];
            $('#_select_pop').val(custom_name);
            $('#custom_code').val(custom_code);
            if (selectPopWindow.dialog != null) {
                selectPopWindow.dialog.close();
            }
        }
    };
    
</script>

