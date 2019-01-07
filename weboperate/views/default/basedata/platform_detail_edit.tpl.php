<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '编辑平台',
	'links'=>array(
		array('url'=>'basedata/platform/do_list',title=>'平台列表')
	)
));?>
<form  class="form-horizontal" id="form1" action="<?php echo '?app_act=basedata/platform/do_' . $app["scene"] ?>" method="post">
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left">平台详情</h3>
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
                        array('title'=>'平台代码', 'type'=>'input', 'field'=>'pt_code'),
                        array('title'=>'平台名称', 'type'=>'input', 'field'=>'pt_name',),
                        array('title'=>'平台官网URL', 'type'=>'input', 'field'=>'pt_offurl',),
                        array('title'=>'技术平台URL', 'type'=>'input', 'field'=>'pt_techurl',),
                        array('title'=>'服务市场URL', 'type'=>'input', 'field'=>'pt_serurl',),
                        array('title'=>'状态', 'type'=>'checkbox', 'field'=>'pt_state',),
                        array('title'=>'平台LOGO', 'type'=>'file',  'text'=>'选择','field'=>'pt_logo',
                            'rules'=>array('ext'=>'.png,.jpg,.gif')),
                        array('title'=>'付款类型', 'type'=>'select', 'field'=>'pt_pay_type','data'=>ds_get_select_by_field('pay_type',2)),
                        array('title'=>'描述', 'type'=>'textarea', 'field'=>'pt_bz', ),
                        ),      
		'hidden_fields'=>array(array('field'=>'pt_id')), 
	), 
        'col'=>2,
	'act_edit'=>'basedata/platform/do_edit', //edit,add,view
	'act_add'=>'basedata/platform/do_add',
	'data'=>$response['data'],
        'rules'=>array(
                    array('pt_code', 'require'), 
                    array('pt_name', 'require'),
                    array('pt_pay_type', 'require')),
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
        array('title' => '平台店铺类型', 'active' => true), // 默认选中active=true的页签
    ),
    'for' => 'TabPageContents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<div id="TabPageContents">
    <div class="panel">
        <div class="panel-body">
            <button class="button button-small" onclick="PageHead_show_dialog_ref('?app_act=basedata/platform/shop_type&app_scene=add&app_show_mode=pop&pd_pt_id=<?php echo $request['_id'] ?>', '添加店铺类型', {w: 500, h: 400}, table1Store)"><i class="icon-plus"></i>添加店铺类型</button>
            <?php
            render_control('DataTable', 'table1', array(
                'conf' => array(
                    'list' => array(
                        array(
                            'type' => 'select',
                            'show' => 1,
                            'title' => '平台名称',
                            'field' => 'pd_pt_id_name',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '店铺类型',
                            'field' => 'pd_shop_type',
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
                                    'act' => 'pop:basedata/platform/shop_type&app_scene=edit', 'show_name' => '编辑平台店铺类型',
                                ),
                                array('id' => 'del',
                                    'title' => '删除',
                                    'callback' => 'do_delete_shop',
                                    'confirm' => '确认要删除吗？'
                                ),
                            ),
                        )
                    )
                ),
                'dataset' => 'basedata/PlatformModel::get_platform_shop',
                'params' => array('filter' => array('pd_pt_id' => $request['_id'])),
                'idField' => 'pd_id',
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
    function do_delete_shop(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: "<?php echo get_app_url('basedata/platform/do_platshop_delete'); ?>",
            data: {pd_id: row.pd_id},
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

</script>