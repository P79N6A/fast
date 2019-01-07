<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '',
	'links'=>array(
		array('url'=>'market/valueservice/do_list','title'=>'增值服务列表')
	)
));?>
<form  class="form-horizontal" id="form1" action="<?php echo '?app_act=market/valueservice/valueserver_' . $app["scene"] ?>" method="post">
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left">增值服务</h3>
         <?php if ($app['scene'] == "add" || $app['scene'] == "edit") { ?>
                <div class="pull-right">
                    <button type="submit" class="button button-primary" id="submit">提交</button>
                    <button type="reset" class="button " id="reset">重置</button>
                </div>
        <?php } ?>
    </div>
<div class="panel-body">   
<?php render_control('Form', 'form1', array(
        'noform' => true,
	'conf'=>array(
		'fields'=>array(
                        array('title'=>'增值代码', 'type'=>'input', 'field'=>'value_code','edit_scene'=>'add','show_scene'=>'add,edit,view' ),
			array('title'=>'增值名称', 'type'=>'input', 'field'=>'value_name', ),
            array('title'=>'增值价格', 'type'=>'input', 'field'=>'value_price', ),
            array('title'=>'使用周期', 'type'=>'input', 'field'=>'value_cycle','remark'=>'月'),
          //  array('title' => '增值图片', 'type' => 'file', 'field' => 'pic_path','rules' => array('ext' => '.png,.jpg,.gif'),),
            array('title' => '增值入驻地址', 'type' => 'input', 'field' => 'source_path','reamrk'=>''),
            array('title'=>'增值产品', 'type'=>'select', 'field'=>'value_cp_id','data'=>ds_get_select('chanpin',2)),
            array('title'=>'增值类别', 'type'=>'select', 'field'=>'value_cat','show_scene' => 'add,edit',),
                      //  array('title'=>'产品版本', 'type'=>'select', 'field'=>'value_cp_version','data' =>ds_get_select_by_field('product_version', 2)),
            array('title' => '前台显示顺序', 'type' => 'input', 'field' => 'value_sort_order',),
            array('title' => '适用行业', 'type' => 'input', 'field' => 'value_appl_industry',),
            array('title' => '核心开发人员', 'type' => 'input', 'field' => 'development_member',),
            array('title' => '开发周期', 'type' => 'input', 'field' => 'develop_cycle',),
            array('title'=>'增值描述', 'type'=>'textarea', 'field'=>'value_desc', ),
            array('title' => '功能详细说明', 'type' => 'input', 'field' => 'function_application',),
            array('title' => '是否个性化', 'type' => 'checkbox', 'field' => 'is_personal',),
            array('title' => '是否发布', 'type' => 'checkbox', 'field' => 'value_publish_status',),
            array('title' => '备注', 'type' => 'textarea', 'field' => 'val_remark',),
                        array('title'=>'最低版本', 'type'=>'select_pop', 'field'=>'value_require_version','select'=>'products/edition','show_scene'=>'add,edit','eventtype'=>'custom'),
                        array('title'=>'最低版本', 'type'=>'input', 'field'=>'value_require_version_name','show_scene'=>'view'),
                        array('title'=>'状态', 'type'=>'checkbox', 'field'=>'value_enable','show_scene'=>'view'),
                        ),      
		'hidden_fields'=>array(array('field'=>'value_id'), array('field'=>'value_code'),), 
	), 
        'col'=>2,
	'act_edit'=>'market/valueservice/valueserver_edit', //edit,add,view
	'act_add'=>'market/valueservice/valueserver_add',
	'data'=>$response['data'],
        'rules'=>array(
                    array('value_code', 'require'), 
                    array('value_name', 'require'),
                    array('value_cat', 'require'),
                    array('value_cp_id','require'),
                    array('value_price','require'),
                    array('value_price','number'),
                    array('value_cycle','number'),
                    array('value_cp_version','require'))  
)); ?>
    </div>
</div>
</form>
<script type="text/javascript">
    new BUI.Form.HForm({
        srcNode: '#form1',
        submitType: 'ajax',
        callback: function(data) {
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
        }
    }).render();
</script>

<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '增值服务明细', 'active' => true), // 默认选中active=true的页签
    ),
    'for' => 'TabPageContents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<div id="TabPageContents">
    <div class="panel">
        <div class="panel-body">
            <button class="button button-small" onclick="PageHead_show_dialog_ref('?app_act=market/valueservice/value_func&app_scene=add&app_show_mode=pop&value_id=<?php echo $request['_id'] ?>', '添加增值明细', {w: 500, h: 400}, table1Store)"><i class="icon-plus"></i>添加增值明细</button>
            <?php
            render_control('DataTable', 'table1', array(
                'conf' => array(
                    'list' => array(
                        array(
                            'type' => 'select',
                            'show' => 1,
                            'title' => '业务ID',
                            'field' => 'vd_busine_id',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '业务代码',
                            'field' => 'vd_busine_code',
                            'width' => '150',
                            'align' => '',
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '功能类型',
                            'field' => 'vd_busine_type',
                            'width' => '150',
                            'align' => '',
                            'format'=>array('type'=>'map', 'value'=>ds_get_field('valueserver_type'))
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '备注',
                            'field' => 'remark',
                            'width' => '150',
                            'align' => '',
                        ),
                        array(
                            'type' => 'button',
                            'show' => 1,
                            'title' => '操作',
                            'field' => '_operate',
                            'width' => '120',
                            'align' => '',
                            'buttons' => array(
                                array('id' => 'edit', 'title' => '编辑',
                                    'act' => 'pop:market/valueservice/value_func&app_scene=edit', 'show_name' => '编辑增值明细',
                                ),
                                array('id' => 'del',
                                    'title' => '删除',
                                    'callback' => 'do_delete_vfunc',
                                    'confirm' => '确认要删除吗？'
                                ),
                            ),
                        )
                    )
                ),
                'dataset' => 'market/ValueModel::get_valueserver_detail',
                'params' => array('filter' => array('value_id' => $request['_id'])),
                'idField' => 'vd_id',
                'CheckSelection' => false,
            ));
            ?>
        </div>
    </div>
</div>


<script type="text/javascript">
    function pt_logoUploader_success(result) {
	var url = '<?php echo get_app_url('common/file/img')?>&f='+$.parseJSON(result.data.path)[0];
	$('#pt_logoUploader .bui-queue-item-success .success').html('<img src="'+url+'" style="width:100px; height:100px"/>')
}

    function PageHead_show_dialog_ref(_url, _title, _opts, refgrid) {
        new ESUI.PopWindow(_url, {
            title: _title,
            width: _opts.w,
            height: _opts.h,
            onBeforeClosed: function() {
                if (refgrid) {
                    refgrid.load();
                }
                if (typeof _opts.callback == 'function')
                    _opts.callback();
            }
        }).show();
    }
    //删除模块明细++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    function do_delete_vfunc(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: "<?php echo get_app_url('market/valueservice/do_vfunc_delete'); ?>",
            data: {vd_id: row.vd_id},
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    table1Store.load();
//                    tablemdStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
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

    var acttype = "<?php echo $app["scene"] ?>";
    init();
    function init() {
        if (acttype == "add") {
            $("#value_cat").append("<option value=''>请选择</option>");
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

     //绑定增值类别
    $("#value_cp_id").change(function() {
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
                } else {
                    $("#value_cat").empty();
                    $("#value_cat").append("<option value=''>请选择</option>");
                }
            }
        });
    });

</script>