<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '查看增值服务',
	'links'=>array(
		array('url'=>'market/valueservice/do_list','title'=>'增值服务器列表')
	)
));?>
<?php render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => array(
            array('title' => '增值代码', 'type' => 'input', 'field' => 'value_code', 'edit_scene' => 'add', 'show_scene' => 'add,view'),
            array('title' => '增值名称', 'type' => 'input', 'field' => 'value_name',),
            array('title' => '增值价格', 'type' => 'input', 'field' => 'value_price',),
            array('title' => '销售周期', 'type' => 'input', 'field' => 'value_cycle', 'remark' => '月'),
            array('title' => '增值图片', 'type' => 'file', 'field' => 'pic_path','rules' => array('ext' => '.png,.jpg,.gif')),
            array('title' => '增值入驻地址', 'type' => 'input', 'field' => 'source_path','reamrk'=>''),
            array('title' => '增值产品', 'type' => 'select', 'field' => 'value_cp_id', 'data' => ds_get_select('chanpin', 2)),
            array('title' => '产品类别', 'type' => 'select', 'field' => 'value_cat', 'show_scene' => 'add,edit'),
            array('title' => '前台显示顺序', 'type' => 'input', 'field' => 'value_sort_order',),
            array('title' => '适用行业', 'type' => 'input', 'field' => 'value_appl_industry',),
            array('title' => '核心开发人员', 'type' => 'input', 'field' => 'development_member',),
            array('title' => '开发周期', 'type' => 'input', 'field' => 'develop_cycle',),
            array('title' => '功能描述', 'type' => 'textarea', 'field' => 'value_desc',),
            array('title' => '功能详细说明', 'type' => 'input', 'field' => 'function_application',),
            array('title' => '是否个性化', 'type' => 'checkbox', 'field' => 'is_personal',),
            array('title' => '是否发布', 'type' => 'checkbox', 'field' => 'value_publish_status',),//'remark'=>'发布后，前台服务市场即可查看，请谨慎操作！'
            array('title' => '备注', 'type' => 'textarea', 'field' => 'val_remark',),
            array('title' => '最低版本', 'type' => 'select_pop', 'field' => 'value_require_version', 'select' => 'products/edition', 'show_scene' => 'add,edit', 'eventtype' => 'custom'),
            array('title' => '最低版本', 'type' => 'input', 'field' => 'value_require_version_name', 'show_scene' => 'view'),
            array('title' => '状态', 'type' => 'checkbox', 'field' => 'value_enable', 'show_scene' => 'view'),

        ),
        'hidden_fields' => array(array('field' => 'value_id'), array('field' => 'value_code'),),
    ),
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
        'col'=>2,
	'act_edit'=>'market/valueservice/valueserver_edit', //edit,add,view
	'act_add'=>'market/valueservice/valueserver_add',
        'callback'=>'after_submit',
	'data'=>$response['data'],
        'rules'=>array(
                    array('value_code', 'require'), 
                    array('value_name', 'require'),
                    array('value_cat', 'require'),
                    array('value_cp_id','require'),
                    array('value_price','require'),
                    array('value_price','number'),
                    array('value_cycle','number'),
                    array('value_cycle','require'),
                    array('value_sort_order','number')
            )
)); ?>
<script type="text/javascript">

    var acttype = "<?php echo $app["scene"] ?>";
    var val_cp_id="<?php echo $response['val_cp_id']?>";
    init();
    function init() {
        if (acttype == "add") {
            $("#value_cat").append("<option value=''>请选择</option>");
            //增值产品默认为efast365
            $("#value_cp_id").val(val_cp_id);
            get_value_cat($("#value_cp_id").val());
        } else if (acttype == "edit") {
            if ($("#value_cp_id").val() == "") {
                $("#value_cat").empty();
                $("#value_cat").append("<option value=''>请选择</option>");
                return;
            }
            $.ajax({type: 'POST', dataType: 'json',
                url: "<?php echo get_app_url('market/valueservice/do_getvalue_type'); ?>",
                data: {cpid: $("#value_cp_id").val(), },
                success: function(ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        $("#value_cat").empty();
                        //重新绑定value_cat
                        $("#value_cat").append("<option value=''>请选择</option>");
                        $.each(ret.data, function(i, item) {
                            $("#value_cat").append("<option value='" + item.vc_id + "'>" + item.vc_name + "</option>");
                        });
                        $("#value_cat").val("<?php echo $response['data']["value_cat"] ?>");
                    } else {
                        //BUI.Message.Alert(ret.message, type);
                        $("#value_cat").empty();
                        $("#value_cat").append("<option value=''>请选择</option>");
                    }
                }
            });
        }
    }
    
     //绑定平台类型
    $("#value_cp_id").change(function() {
        if ($("#value_cp_id").val() == "") {
            $("#value_cat").empty();
            $("#value_cat").append("<option value=''>请选择</option>");
            return;
        }
        get_value_cat($("#value_cp_id").val());
    });
    


function get_value_cat(value_cp_id) {
    $.ajax({type: 'POST', dataType: 'json',
        url: "<?php echo get_app_url('market/valueservice/do_getvalue_type'); ?>",
        data: {cpid: value_cp_id, },
        success: function(ret) {
            var type = ret.status == 1 ? 'success' : 'error';
            if (type == 'success') {
                $("#value_cat").empty();
                //重新绑定value_cat
                $("#value_cat").append("<option value=''>请选择</option>");
                $.each(ret.data, function(i, item) {
                    $("#value_cat").append("<option value='" + item.vc_id + "'>" + item.vc_name + "</option>");
                });
            } else {
                $("#value_cat").empty();
                $("#value_cat").append("<option value=''>请选择</option>");
            }
        }
    });
}

    function pic_pathUploader_success(result) {
        var url = '<?php echo get_app_url('common/file/img') ?>&f=' + $.parseJSON(result.data.path)[0];
        $('#pic_pathUploader .bui-queue-item-success .success').html('<img src="' + url + '" style="width:100px; height:100px"/>')
    }


    //绑定增值产品选择事件
    $("#value_cp_id").change(function(){
       //清空关联版本
       $("#value_require_version_select_pop").val('');
       $("#value_require_version").val();
    });
    //alert($("#cp_id").val());
    var selectPopWindowvalue_require_version = {
    dialog: null,
    callback: function (value, id, code, name) {
        var nameArr = [], valueArr = [];
        for (var i = 0; i < value.length; i++) {
            nameArr.push('['+value[i][code]+']'+value[i][name]);
            valueArr.push(value[i][id]);
        }
        $('#value_require_version_select_pop').val(nameArr.join(','));
        $('#value_require_version').val(valueArr.join(','));
        if (selectPopWindowvalue_require_version.dialog != null) {
            selectPopWindowvalue_require_version.dialog.close();
        }
    }
};
$('#value_require_version_select_pop,#value_require_version_select_img').click(function() {
    if($("#value_cp_id").val()==""){
        BUI.Message.Alert("先选择产品信息", "error");
        return;
    }
    selectPopWindowvalue_require_version.dialog = new ESUI.PopSelectWindow('?app_act=common/select/edition&cpid='+$("#value_cp_id").val(), 'selectPopWindowvalue_require_version.callback', {title: '最低版本', width: 900, height:500 ,ES_pFrmId:"<?php echo $request['ES_frmId'] ?>" }).show();
});
    
     function after_submit(data, Esfrom_Id) {
         if (data.status == 1) {
             BUI.Message.Alert('添加成功！',function () {
                 ui_closeTabPage("<?php echo $request['ES_frmId'] ?>");
             } ,'success');
         } else {
             BUI.Message.Alert(data.message, 'error');
         }
     }
     
     
    $("input[name='value_code']").attr("placeholder","唯一校验，不允许编辑"); 
    $("input[name='source_path']").attr("placeholder","http://"); 
    $("input[name='value_sort_order']").attr("placeholder","默认为1，数字越大优先级越高"); 
    $("textarea[name='value_desc']").attr("placeholder","描述增值服务作用"); 
    $("input[name='function_application']").attr("placeholder","增值服务详细说明 http://"); 
    
    
</script>