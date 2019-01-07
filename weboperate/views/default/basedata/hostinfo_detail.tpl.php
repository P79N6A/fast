<?php
render_control('PageHead', 'head1', array('title' => isset($app['title']) ? $app['title'] : '查看云主机(VM)信息',
    'links' => array(
        array('url' => 'basedata/hostinfo/do_list', title => '云主机(VM)列表')
    )
));
?>
<?php
render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => array(
            array('title'=>'客户名称', 'type'=>'select_pop', 'field'=>'kh_id', 'select'=>'clients/clientinfo','show_scene'=>'add,edit'),
            array('title'=>'客户名称', 'type'=>'input', 'field'=>'kh_id_name','show_scene'=>'view','edit_scene'=>''),
            array('title' => '用途', 'type' => 'select', 'field' => 'ali_server_use', 'data' => ds_get_select_by_field('serveruse', 2)),
            array('title' => '云服务商', 'type' => 'select', 'field' => 'ali_type', 'data' => ds_get_select('host_cloud', 2)),
            array('title' => '型号', 'type' => 'select', 'field' => 'ali_server_model', 'show_scene' => 'add,edit'),
            array('title' => '型号', 'type' => 'select', 'field' => 'ali_server_model', 'show_scene' => 'view', 'data' => ds_get_select('host_model', 2)),
            array('title' => '操作系统', 'type' => 'select', 'field' => 'ali_operate_system', 'data' => ds_get_select_by_field('system_type', 3)),
            array('title' => '外网IP', 'type' => 'input', 'field' => 'ali_outip',),
            array('title' => '内网IP', 'type' => 'input', 'field' => 'ali_inip',),
            array('title' => '别名', 'type' => 'input', 'field' => 'ali_another_name',),
            array('title' => '模式', 'type' => 'select', 'field' => 'ali_share_type','data' =>ds_get_select_by_field('share_type', 2)),
            /*array('title' => '标准价', 'type' => 'input', 'field' => 'ali_stan_price',),
            array('title' => '结算价', 'type' => 'input', 'field' => 'ali_settle_price',),*/
            array('title' => '成本价', 'type' => 'input', 'field' => 'ali_cost_price',),
            array('title' => '售价', 'type' => 'input', 'field' => 'ali_sales_price',),
            array('title' => '开始时间', 'type' => 'date', 'field' => 'ali_starttime',),
            array('title' => '到期时间', 'type' => 'date', 'field' => 'ali_endtime',),
            array('title' => 'ROOT密码', 'type' => 'input', 'field' => 'ali_root', 'show_scene' => 'add',),
            array('title' => 'WEB用户', 'type' => 'input', 'field' => 'ali_user', 'show_scene' => 'add',),
            array('title' => 'WEB密码', 'type' => 'input', 'field' => 'ali_pass', 'show_scene' => 'add',),
            array('title' => 'Apache版本', 'type' => 'input', 'field' => 'ali_apache',),
            array('title' => 'PHP版本号', 'type' => 'input', 'field' => 'ali_php',),
            array('title' => 'Mysql版本', 'type' => 'input', 'field' => 'ali_mysql',),
            array('title' => 'WEB内存', 'type' => 'input', 'field' => 'ali_mem',),
            array('title' => 'CPU核心', 'type' => 'input', 'field' => 'ali_cpu',),
            array('title' => '带宽', 'type' => 'input', 'field' => 'ali_net',),
            array('title' => '硬盘', 'type' => 'input', 'field' => 'ali_disk',),
            array('title' => '创建时间', 'type' => 'input', 'field' => 'ali_createdate','show_scene' => 'view'),
            array('title' => '创建人', 'type' => 'input', 'field' => 'ali_createuser_name','show_scene' => 'view'),
            array('title' => '更新时间', 'type' => 'input', 'field' => 'ali_updatedate','show_scene' => 'view'),
            array('title' => '更新人', 'type' => 'input', 'field' => 'ali_updateuser_name','show_scene' => 'view'),
            array('title' => '服务器备注', 'type' => 'textarea', 'field' => 'ali_notes',),
        ),
        'hidden_fields' => array(array('field' => 'host_id')),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'col' => 2,
    'act_edit' => 'basedata/hostinfo/ali_edit', //edit,add,view
    'act_add' => 'basedata/hostinfo/ali_add',
    'data' => $response['data'],
    'rules' => 'basedata/add_hostinfo', //对应方法在conf/validator/basedata_conf.php
    'event'=>array('beforesubmit'=>'formBeforesubmit'),
));
?>

<script type="text/javascript">
    
    function formBeforesubmit() {
        if($("#ali_server_use").val()=='1'){  //表示客户正式，客户字段必填
            if($("#kh_id").val()==''){
                BUI.Message.Alert("客户正式用途，关联客户不能为空","error");
                return false;
            }
        }
	return true; // 如果不想让表单继续提交，则return false
    }
    

    var acttype = "<?php echo $app["scene"] ?>";
    init();
    function init() {
        if (acttype == "add") {
            $("#ali_server_model").append("<option value=''>请选择</option>");
        } else if (acttype == "edit") {
            if ($("#ali_type").val() == "") {
                $("#ali_server_model").empty();
                $("#ali_server_model").append("<option value=''>请选择</option>");
                return;
            }
            $.ajax({type: 'POST', dataType: 'json',
                url: "<?php echo get_app_url('basedata/cloud/do_getcloud_server'); ?>",
                data: {cdid: $("#ali_type").val(), },
                success: function(ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        $("#ali_server_model").empty();
                        //重新绑定ali_server_model
                        $("#ali_server_model").append("<option value=''>请选择</option>");
                        $.each(ret.data, function(i, item) {
                            $("#ali_server_model").append("<option value='" + item.cm_id + "'>" + item.cm_host_type + "</option>");
                        });
                        $("#ali_server_model").change(function() {
                            bindservermod();
                        });
                        $("#ali_server_model").val("<?php echo $response['data']["ali_server_model"] ?>");
                    } else {
                        //BUI.Message.Alert(ret.message, type);
                        $("#ali_server_model").empty();
                        $("#ali_server_model").append("<option value=''>请选择</option>");
                    }
                }
            });
        }
    }

    //绑定云服务商事件
    $("#ali_type").change(function() {
        if ($("#ali_type").val() == "") {
            $("#ali_server_model").empty();
            $("#ali_server_model").append("<option value=''>请选择</option>");
            return;
        }
        $.ajax({type: 'POST', dataType: 'json',
            url: "<?php echo get_app_url('basedata/cloud/do_getcloud_server'); ?>",
            data: {cdid: $("#ali_type").val(), },
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    $("#ali_server_model").empty();
                    //重新绑定ali_server_model
                    $("#ali_server_model").append("<option value=''>请选择</option>");
                    $.each(ret.data, function(i, item) {
                        $("#ali_server_model").append("<option value='" + item.cm_id + "'>" + item.cm_host_type + "</option>");
                    });
                    $("#ali_server_model").change(function() {
                        bindservermod();
                    });
                    $("#ali_cpu").val("");
                    $("#ali_mem").val("");
                    $("#ali_net").val("");
                    $("#ali_disk").val("");
                } else {
                    //BUI.Message.Alert(ret.message, type);
                    $("#ali_server_model").empty();
                    $("#ali_server_model").append("<option value=''>请选择</option>");
                }
            }
        });
    });


    //绑定云型号事件
    function bindservermod() {
        if ($("#ali_server_model").val() == "") {
            return;
        }
        $.ajax({type: 'POST', dataType: 'json',
            url: "<?php echo get_app_url('basedata/cloud/get_host_info'); ?>",
            data: {cdmdid: $("#ali_server_model").val(), },
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    /*if ($("#ali_cpu").val() == "")
                        $("#ali_cpu").val(ret.data.cm_host_cpu);
                    if ($("#ali_mem").val() == "")
                        $("#ali_mem").val(ret.data.cm_host_mem);
                    if ($("#ali_net").val() == "")
                        $("#ali_net").val(ret.data.cm_host_net);
                    if ($("#ali_disk").val() == "")
                        $("#ali_disk").val(ret.data.cm_host_disk);
                    */
                    $("#ali_cpu").val(ret.data.cm_host_cpu);
                    $("#ali_mem").val(ret.data.cm_host_mem);
                    $("#ali_net").val(ret.data.cm_host_net);
                    $("#ali_disk").val(ret.data.cm_host_disk);
                } else {

                }
            }
        });
    }
    
    
</script>