<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '查看店铺',
	'links'=>array(
		array('url'=>'clients/shopinfo/do_list','title'=>'店铺信息')
	)
));?>

<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
//			array('title'=>'店铺代码', 'type'=>'input', 'field'=>'sd_code',  'edit_scene'=>'add'),
			array('title'=>'店铺名称', 'type'=>'input', 'field'=>'sd_name', ),
                        array('title'=>'平台类型',  'type'=>'select', 'field'=>'sd_pt_id','data'=>ds_get_select('shop_platform',2)),
                        array('title' => '店铺类型', 'type' => 'select', 'field' => 'sd_pt_shoptype', 'show_scene' => 'add,edit'),
                        array('title' => '店铺类型', 'type' => 'select', 'field' => 'sd_pt_shoptype', 'show_scene' => 'view', 'data' => ds_get_select('platformshop_type', 2)),
                        array('title'=>'所属客户', 'type'=>'select_pop', 'field'=>'sd_kh_id', 'select'=>'clients/clientinfo','show_scene'=>'add,edit'),
                        array('title'=>'所属客户', 'type'=>'input', 'field'=>'sd_kh_id_name','show_scene'=>'view','edit_scene'=>''),
                        array('title'=>'代理名称', 'type'=>'input', 'field'=>'sd_agent', ),
                        array('title'=>'店铺登录名称', 'type'=>'input', 'field'=>'sd_login_name', ),
                        array('title'=>'昵称', 'type'=>'input', 'field'=>'sd_nick', 'edit_scene'=>'add'),
                        array('title'=>'服务负责人', 'type'=>'select_pop', 'field'=>'sd_servicer', 'select'=>'sys/orguser','show_scene'=>'add,edit'),
                        array('title'=>'服务负责人', 'type'=>'input', 'field'=>'sd_servicer_name','show_scene'=>'view'),
                        array('title'=>'商店负责人', 'type'=>'input', 'field'=>'sd_fzr', ),
                        array('title'=>'店铺联系方式', 'type'=>'input', 'field'=>'sd_lxfs', ),
                        array('title'=>'店铺邮箱','type'=>'input','field'=>'sd_email'),
//                        array('title'=>'是否按单收费', 'type'=>'input', 'field'=>'sd_is_adsf', ),
                        array('title'=>'创建人', 'type'=>'input', 'field'=>'sd_createuser_name','edit_scene'=>'','show_scene'=>'view,edit' ),
                        array('title'=>'创建日期','type'=>'input', 'field'=>'sd_createdate','edit_scene'=>'','show_scene'=>'view,edit' ),
                        array('title'=>'修改人', 'type'=>'input', 'field'=>'sd_updateuser_name', 'edit_scene'=>'','show_scene'=>'view,edit'),
                        array('title'=>'修改日期', 'type'=>'input', 'field'=>'sd_updatedate','edit_scene'=>'','show_scene'=>'view,edit' ),
                        array('title'=>'备注', 'type'=>'textarea', 'field'=>'sd_bz', ),
                    ),      
		'hidden_fields'=>array(array('field'=>'sd_id')), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
        'col'=>2,
	'act_edit'=>'clients/shopinfo/shop_edit', //edit,add,view
	'act_add'=>'clients/shopinfo/shop_add',
	'data'=>$response['data'],
        'rules'=>'clients/add_shops',        //对应方法在conf/validator/clients_conf.php,新建店铺必填字段验证。
)); ?>

<script type="text/javascript">
      var acttype = "<?php echo $app["scene"] ?>";
    init();
    function init() {
        if (acttype == "add") {
            $("#sd_pt_shoptype").append("<option value=''>请选择</option>");
        } else if (acttype == "edit") {
            if ($("#sd_pt_id").val() == "") {
                $("#sd_pt_shoptype").empty();
                $("#sd_pt_shoptype").append("<option value=''>请选择</option>");
                return;
            }
            $.ajax({type: 'POST', dataType: 'json',
                url: "<?php echo get_app_url('clients/shopinfo/do_getshop_type'); ?>",
                data: {ptid: $("#sd_pt_id").val(), },
                success: function(ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        $("#sd_pt_shoptype").empty();
                        //重新绑定sd_pt_shoptype
                        $("#sd_pt_shoptype").append("<option value=''>请选择</option>");
                        $.each(ret.data, function(i, item) {
                            $("#sd_pt_shoptype").append("<option value='" + item.pd_id + "'>" + item.pd_shop_type + "</option>");
                        });
                        $("#sd_pt_shoptype").val("<?php echo $response['data']["sd_pt_shoptype"] ?>");
                    } else {
                        //BUI.Message.Alert(ret.message, type);
                        $("#sd_pt_shoptype").empty();
                        $("#sd_pt_shoptype").append("<option value=''>请选择</option>");
                    }
                }
            });
        }
    }
    
     //绑定平台类型
    $("#sd_pt_id").change(function() {
        if ($("#sd_pt_id").val() == "") {
            $("#sd_pt_shoptype").empty();
            $("#sd_pt_shoptype").append("<option value=''>请选择</option>");
            return;
        }
        $.ajax({type: 'POST', dataType: 'json',
            url: "<?php echo get_app_url('clients/shopinfo/do_getshop_type'); ?>",
            data: {ptid: $("#sd_pt_id").val(), },
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    $("#sd_pt_shoptype").empty();
                    //重新绑定sd_pt_shoptype
                    $("#sd_pt_shoptype").append("<option value=''>请选择</option>");
                    $.each(ret.data, function(i, item) {
                        $("#sd_pt_shoptype").append("<option value='" + item.pd_id + "'>" + item.pd_shop_type + "</option>");
                    });
//                    $("#sd_pt_shoptype").change(function() {
//                        bindservermod();
//                    });
                } else {
                    //BUI.Message.Alert(ret.message, type);
                    $("#sd_pt_shoptype").empty();
                    $("#sd_pt_shoptype").append("<option value=''>请选择</option>");
                }
            }
        });
    });
    
</script>


