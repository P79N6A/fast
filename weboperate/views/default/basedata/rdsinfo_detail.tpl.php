<?php
render_control('PageHead', 'head1', array('title' => isset($app['title']) ? $app['title'] : '查看云数据库(RDS)信息',
    'links' => array(
        array('url' => 'basedata/rdsinfo/do_list', title => '云数据库(RDS)列表')
    )
));
?>
<?php
render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => array(
            array('title'=>'客户名称', 'type'=>'select_pop', 'field'=>'kh_id', 'select'=>'clients/clientinfo','show_scene'=>'add,edit'),
            array('title'=>'客户名称', 'type'=>'input', 'field'=>'kh_id_name','show_scene'=>'view','edit_scene'=>''),
            array('title' => '用途', 'type' => 'select', 'field' => 'rds_server_use', 'data' => ds_get_select_by_field('serveruse', 2)),
            array('title' => '云服务商', 'type' => 'select', 'field' => 'rds_dbtype', 'data' => ds_get_select('host_cloud', 2)),
            array('title' => '型号', 'type' => 'select', 'field' => 'rds_server_model', 'show_scene' => 'add,edit'),
            array('title' => '型号', 'type' => 'select', 'field' => 'rds_server_model', 'show_scene' => 'view', 'data' => ds_get_select('db_model', 2)),
            array('title' => 'RDS用户名', 'type' => 'input', 'field' => 'rds_user',),
            array('title' => 'RDS密码', 'type' => 'input', 'field' => 'rds_pass', 'show_scene' => 'add'),
            array('title' => 'RDS连接', 'type' => 'input', 'field' => 'rds_link',),
            array('title' => 'RDS实例', 'type' => 'input', 'field' => 'rds_dbname',),
            array('title' => '别名', 'type' => 'input', 'field' => 'ali_another_name',),
            array('title' => '模式', 'type' => 'select', 'field' => 'ali_share_type','data' =>ds_get_select_by_field('share_type', 2)),
            /*array('title' => '标准价', 'type' => 'input', 'field' => 'rds_stan_price',),
            array('title' => '结算价', 'type' => 'input', 'field' => 'rds_settle_price',),*/
            array('title' => '成本价', 'type' => 'input', 'field' => 'rds_cost_price',),
            array('title' => '售价', 'type' => 'input', 'field' => 'rds_sales_price',),
            array('title' => '开始时间', 'type' => 'date', 'field' => 'rds_starttime',),
            array('title' => '到期时间', 'type' => 'date', 'field' => 'rds_endtime',),
            array('title' => '内存', 'type' => 'input', 'field' => 'rds_mem',),
            array('title' => '容量', 'type' => 'input', 'field' => 'rds_disk',),
            array('title' => '最大连接数', 'type' => 'input', 'field' => 'rds_con',),
            array('title' => 'QPS最大执行次数', 'type' => 'input', 'field' => 'rds_qps',),
            array('title' => 'IOPS每秒最大读写', 'type' => 'input', 'field' => 'rds_iops',),
            array('title' => '创建时间', 'type' => 'input', 'field' => 'rds_createdate', 'show_scene' => 'view'),
            array('title' => '创建人', 'type' => 'input', 'field' => 'rds_createuser_name', 'show_scene' => 'view'),
            array('title' => '更新时间', 'type' => 'input', 'field' => 'rds_updatedate', 'show_scene' => 'view'),
            array('title' => '更新人', 'type' => 'input', 'field' => 'rds_updateuser_name', 'show_scene' => 'view'),
            array('title'=>'备注', 'type'=>'input', 'field'=>'rds_notes', ),
        ),
        'hidden_fields' => array(array('field' => 'rds_id')),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'col' => 2,
    'act_edit' => 'basedata/rdsinfo/rds_edit', //edit,add,view
    'act_add' => 'basedata/rdsinfo/rds_add',
    'data' => $response['data'],
    'rules' => 'basedata/add_rdsinfo', //对应方法在conf/validator/basedata_conf.php
    'event'=>array('beforesubmit'=>'formBeforesubmit'),
));
?>



<script type="text/javascript">
    function formBeforesubmit() {
        if($("#rds_server_use").val()=='1'){  //表示客户正式，客户字段必填
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
            $("#rds_server_model").append("<option value=''>请选择</option>");
        } else if (acttype == "edit") {
            if ($("#rds_dbtype").val() == "") {
                $("#rds_server_model").empty();
                $("#rds_server_model").append("<option value=''>请选择</option>");
                return;
            }
            $.ajax({type: 'POST', dataType: 'json',
                url: "<?php echo get_app_url('basedata/cloud/do_getcloud_db'); ?>",
                data: {cdid: $("#rds_dbtype").val(), },
                success: function(ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        $("#rds_server_model").empty();
                        //重新绑定rds_server_model
                        $("#rds_server_model").append("<option value=''>请选择</option>");
                        $.each(ret.data, function(i, item) {
                            $("#rds_server_model").append("<option value='" + item.cm_id + "'>" + item.cm_db_type + "</option>");
                        });
                        $("#rds_server_model").change(function() {
                            bindservermod();
                        });
                        $("#rds_server_model").val("<?php echo $response['data']["rds_server_model"] ?>");
                    } else {
                        //BUI.Message.Alert(ret.message, type);
                        $("#rds_server_model").empty();
                        $("#rds_server_model").append("<option value=''>请选择</option>");
                    }
                }
            });
        }
    }

    //绑定云服务商事件
    $("#rds_dbtype").change(function() {
        if ($("#rds_dbtype").val() == "") {
            $("#rds_server_model").empty();
            $("#rds_server_model").append("<option value=''>请选择</option>");
            return;
        }
        $.ajax({type: 'POST', dataType: 'json',
            url: "<?php echo get_app_url('basedata/cloud/do_getcloud_db'); ?>",
            data: {cdid: $("#rds_dbtype").val(), },
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    $("#rds_server_model").empty();
                    //重新绑定ali_server_model
                    $("#rds_server_model").append("<option value=''>请选择</option>");
                    $.each(ret.data, function(i, item) {
                        $("#rds_server_model").append("<option value='" + item.cm_id + "'>" + item.cm_db_type + "</option>");
                    });
                    $("#rds_server_model").change(function() {
                        bindservermod();
                    });
                    $("#rds_disk").val("");
                    $("#rds_mem").val("");
                    $("#rds_con").val("");
                    $("#rds_qps").val("");
                    $("#rds_iops").val("");
                } else {
                    //BUI.Message.Alert(ret.message, type);
                    $("#rds_server_model").empty();
                    $("#rds_server_model").append("<option value=''>请选择</option>");
                }
            }
        });
    });



    //绑定云型号事件
    function bindservermod() {
        if ($("#rds_server_model").val() == "") {
            return;
        }
        $.ajax({type: 'POST', dataType: 'json',
            url: "<?php echo get_app_url('basedata/cloud/get_db_info'); ?>",
            data: {cdmdid: $("#rds_server_model").val(), },
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    /*if ($("#rds_disk").val() == "")
                        $("#rds_disk").val(ret.data.cm_db_disk);
                    if ($("#rds_mem").val() == "")
                        $("#rds_mem").val(ret.data.cm_db_mem);
                    if ($("#rds_con").val() == "")
                        $("#rds_con").val(ret.data.cm_max_con);
                    if ($("#rds_qps").val() == "")
                        $("#rds_qps").val(ret.data.cm_max_qps);
                    if ($("#rds_iops").val() == "")
                        $("#rds_iops").val(ret.data.cm_max_iops);
                    */
                   $("#rds_disk").val(ret.data.cm_db_disk);
                   $("#rds_mem").val(ret.data.cm_db_mem);
                   $("#rds_con").val(ret.data.cm_max_con);
                   $("#rds_qps").val(ret.data.cm_max_qps);
                   $("#rds_iops").val(ret.data.cm_max_iops);
                } else {

                }
            }
        });
    }
</script>