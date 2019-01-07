<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '查看产品',
	'links'=>array(
            array('url'=>'products/productinfo/do_list','title'=>'产品列表'),
            )
));?>
<style>
    .panel-body {padding: 2px;}
    .panel-body table {margin: 0; }
</style>
<form  class="form-horizontal" id="form1" action="<?php echo '?app_act=products/productinfo/product_'.$app["scene"] ?>" method="post">
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left">产品信息</h3>
        <?php if($app['scene']=="add" || $app['scene']=="edit"){ ?>
            <div class="pull-right">
                <button type="submit" class="button button-primary" id="submit">提交</button>
                <button type="reset" class="button " id="reset">重置</button>
            </div>
        <?php }?>
    </div>
    <div class="panel-body">
    <?php render_control('Form', 'form1', array(
            'noform'=>true,
            'conf'=>array(
                    'fields'=>array(
                            array('title'=>'产品代码', 'type'=>'input', 'field'=>'cp_code', 'edit_scene'=>'add'),
                            array('title'=>'产品名称', 'type'=>'input', 'field'=>'cp_name', ),
                            array('title'=>'英文名称', 'type'=>'input', 'field'=>'cp_en_name', ),
                            array('title'=>'产品简称', 'type'=>'input', 'field'=>'cp_jc', ),
                            array('title'=>'在线订购', 'type'=>'checkbox', 'field'=>'cp_order', ),
                            array('title'=>'系统维护', 'type'=>'checkbox', 'field'=>'cp_maintain', ),
                            array('title'=>'自动受理问题', 'type'=>'checkbox', 'field'=>'cp_autoacc', ),
                            array('title'=>'产品描述', 'type'=>'textarea', 'field'=>'cp_memo', ),
                            array('title'=>'创建人', 'type'=>'input', 'field'=>'cp_createuser_name','edit_scene'=>'','show_scene'=>'view,edit' ),
                            array('title'=>'创建时间', 'type'=>'input','field'=>'cp_createdate','edit_scene'=>'','show_scene'=>'view,edit'),
                            array('title'=>'修改人', 'type'=>'input', 'field'=>'cp_updateuser_name', 'edit_scene'=>'','show_scene'=>'view,edit'),
                            array('title'=>'修改时间', 'type'=>'input','field'=>'cp_updatedate','edit_scene'=>'','show_scene'=>'view,edit' ),
                    ),      
                    'hidden_fields'=>array(array('field'=>'cp_id'), array('field'=>'cp_code'),), 
            ), 
            'col'=>3,
            'act_edit'=>'products/productinfo/product_edit', //edit,add,view
            'act_add'=>'products/productinfo/product_add',
            'data'=>$response['data'],
            'rules'=>'products/products_edit',        //有效性验证
    )); ?>
    </div>
</div>
</form>
<script type="text/javascript">
    new BUI.Form.HForm({
            srcNode : '#form1',
            submitType : 'ajax',
            callback : function(data){
                    var type = data.status == 1 ? 'success' : 'error';
                    BUI.Message.Alert(data.message, type);
            }
    }).render();
</script>
<?php render_control ( 'TabPage', 'TabPage1', array (
		'tabs'=>array(
		        array('title'=>'产品模块', 'active'=>true), // 默认选中active=true的页签
                        array('title'=>'产品成员',),
		),
		'for' => 'TabPageContents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
) );
?>
<div id="TabPageContents">
    <div class="panel">
        <div class="panel-body">
            <button class="button button-small" onclick="PageHead_show_dialog_ref('?app_act=products/productmk/detail&app_scene=add&app_show_mode=pop&cpid=<?php echo $request['_id'] ?>', '添加产品模块', {w:500,h:400},tablemdStore)"><i class="icon-plus"></i>添加模块</button>
            <?php
                render_control ( 'DataTable', 'tablemd', array (
                    'conf' => array (
                        'list' => array (
                            array (
                                'type' => 'text',
                                'show' => 1,
                                'title' => '模块名称',
                                'field' => 'pm_name',
                                'width' => '150',
                                'align' => '' 
                            ),
                            array (
                                'type' => 'text',
                                'show' => 1,
                                'title' => '英文名称',
                                'field' => 'pm_en_name',
                                'width' => '150',
                                'align' => '' 
                            ),
                            array (
                                'type' => 'text',
                                'show' => 1,
                                'title' => '简称',
                                'field' => 'pm_jc',
                                'width' => '100',
                                'align' => '' 
                            ),
                            array (
                                'type' => 'text',
                                'show' => 1,
                                'title' => '描述',
                                'field' => 'pm_memo',
                                'width' => '200',
                                'align' => '' 
                            ),
                            array (
                                'type' => 'button',
                                'show' => 1,
                                'title' => '操作',
                                'field' => '_operate',
                                'width' => '150',
                                'align' => '',
                                'buttons' => array (
                                        array('id'=>'edit', 'title' => '编辑', 
                                                'act'=>'pop:products/productmk/detail&app_scene=edit', 'show_name'=>'编辑产品模块', 
                                             ),
                                        array('id'=>'del', 
                                            'title' => '删除', 
                                            'callback' => 'do_delete_detail', 
                                            'confirm'=>'确认要删除吗？'
                                            ),
                                ),
                            )
                        ) 
                    ),
                    'dataset' => 'products/ProductmkModel::get_by_page',
                    'params' => array('filter'=>array('cpid'=>$request['_id'])),
                    'idField' => 'pm_id',
                    'CheckSelection'=>false,
                ) );
            ?>
        </div>
    </div>
    <div class="panel">
        <div class="panel-body">
            <button class="button button-small" onclick="PageHead_show_dialog_ref('?app_act=products/productmd/detail&app_scene=add&app_show_mode=pop&cpid=<?php echo $request['_id'] ?>', '添加产品成员', {w:500,h:400},tablembStore)"><i class="icon-plus"></i>添加成员</button>
            <?php
                render_control ( 'DataTable', 'tablemb', array (
                    'conf' => array (
                        'list' => array (
                            array (
                                'type' => 'text',
                                'show' => 1,
                                'title' => '成员',
                                'field' => 'pcm_user_name',
                                'width' => '150',
                                'align' => '' 
                            ),
                            array (
                                'type' => 'text',
                                'show' => 1,
                                'title' => '岗位',
                                'field' => 'pcm_user_post_name',
                                'width' => '150',
                                'align' => '' 
                            ),
                            array (
                                'type' => 'button',
                                'show' => 1,
                                'title' => '操作',
                                'field' => '_operate',
                                'width' => '150',
                                'align' => '',
                                'buttons' => array (
                                        array('id'=>'edit', 'title' => '编辑', 
                                                'act'=>'pop:products/productmd/detail&app_scene=edit', 'show_name'=>'编辑产品成员', 
                                             ),
                                        array('id'=>'del', 
                                            'title' => '删除', 
                                            'callback' => 'do_delete_detail_mer', 
                                            'confirm'=>'确认要删除吗？'
                                            ),
                                ),
                            )
                        ) 
                    ),
                    'dataset' => 'products/ProductmdModel::get_by_page',
                    'params' => array('filter'=>array('cpid'=>$request['_id'])),
                    'idField' => 'pcm_id',
                    'CheckSelection'=>false,
                ) );
            ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    
    function PageHead_show_dialog_ref(_url, _title, _opts,refgrid) {

        new ESUI.PopWindow(_url, {
                title: _title,
                width:_opts.w,
                height:_opts.h,
                onBeforeClosed: function() { 
                    if(refgrid){
                        refgrid.load();
                    }
                    if (typeof _opts.callback == 'function') _opts.callback();
                }
            }).show();
    }
    
    //删除模块明细++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    function do_delete_detail(_index, row) {
	$.ajax({ type: 'POST', dataType: 'json',  
            url:"<?php echo get_app_url('products/productmk/do_delete');?>",
            data: {pm_id: row.pm_id}, 
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    tablemdStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
	});
    }
    
     function do_delete_detail_mer(_index, row) {
	$.ajax({ type: 'POST', dataType: 'json',  
            url:"<?php echo get_app_url('products/productmd/do_delete');?>",
            data: {pcm_id: row.pcm_id}, 
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    tablembStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
	});
    }
    
    
</script>

