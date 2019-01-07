<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '查看补丁',
	'links'=>array(
            array('url'=>'products/productpatch/do_list','title'=>'补丁列表'),
            )
));?>
<style>
    .panel-body {padding: 2px;}
    .panel-body table {margin: 0; }
</style>
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left">补丁信息</h3>
        <?php if($app['scene']=="add" || $app['scene']=="edit"){ ?>
            <div class="pull-right">
                <button type="submit" class="button button-primary" id="submit">提交</button>
                <button type="reset" class="button " id="reset">重置</button>
            </div>
        <?php }?>
    </div>
    <div class="panel-body">
    <?php render_control('Form', 'form1', array(
            'conf'=>array(
                    'fields'=>array(
                         array('title'=>'产品名称', 'type'=>'select', 'field'=>'cp_id', 'edit_scene'=>'add', 'data' => ds_get_select('chanpin', 2)),
			array('title'=>'版本编号', 'type'=>'input', 'field'=>'version_no',),
                        array('title'=>'补丁编号 ', 'type'=>'input', 'field'=>'version_patch', ),
                       // array('title'=>'包含SQL', 'type'=>'checkbox', 'field'=>'is_sql', ),
                        //array('title'=>'补丁包路径', 'type'=>'input', 'field'=>'version_file_path', ),
                        array('title'=>'补丁附件', 'type'=>'input', 'field'=>'version_file_name', ),
                        array('title'=>'创建时间', 'type'=>'input','field'=>'create_time','edit_scene'=>'','show_scene'=>'view,edit'),
                        array('title'=>'更新时间', 'type'=>'input','field'=>'update_time','edit_scene'=>'','show_scene'=>'view,edit'),
                        array('title'=>'状态 ', 'type'=>'select', 'field'=>'is_exec','edit_scene'=>'','show_scene'=>'view,edit','data' => ds_get_select_by_field('patch_status', 3)),
                    ),      
                    'hidden_fields'=>array(array('field'=>'id')), 
            ), 
            'col'=>2,
            'act_edit'=>'products/productinfo/product_edit', //edit,add,view
            'act_add'=>'products/productinfo/product_add',
            'data'=>$response['data'],
            'rules'=>'products/products_edit',        //有效性验证
    )); ?>
    </div>
</div>
<?php 
//render_control ( 'TabPage', 'TabPage1', array (
//		'tabs'=>array(
//		        array('title'=>'补丁SQL明细', 'active'=>true), // 默认选中active=true的页签
//		),
//		'for' => 'TabPageContents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
//) );
?>
<!--div id="TabPageContents">
    <div class="panel">
        <div class="panel-body">
            <?php
            
//                render_control ( 'DataTable', 'tablemd', array (
//                    'conf' => array (
//                        'list' => array (
//                            array (
//                                'type' => 'text',
//                                'show' => 1,
//                                'title' => '版本编号',
//                                'field' => 'version_no',
//                                'width' => '150',
//                                'align' => '' 
//                            ),
//                            array (
//                                'type' => 'text',
//                                'show' => 1,
//                                'title' => '版本补丁',
//                                'field' => 'version_patch',
//                                'width' => '150',
//                                'align' => '' 
//                            ),
//                            array (
//                                'type' => 'text',
//                                'show' => 1,
//                                'title' => 'SQL内容',
//                                'field' => 'content',
//                                'width' => '100',
//                                'align' => '' 
//                            ),
//                            array (
//                                'type' => 'text',
//                                'show' => 1,
//                                'title' => '执行',
//                                'field' => 'is_exec',
//                                'width' => '200',
//                                'align' => '' 
//                            ),
//                            array (
//                                'type' => 'text',
//                                'show' => 1,
//                                'title' => '关联任务',
//                                'field' => 'task_sn',
//                                'width' => '200',
//                                'align' => '' 
//                            ),
//                        ) 
//                    ),
//                    'dataset' => 'products/ProductpcdModel::get_by_page',
//                    'params' => array('filter'=>array('v_no'=>$response['data']['version_no'],'v_pt'=>$response['data']['version_patch'])),
//                    'idField' => 'id',
//                    'CheckSelection'=>false,
//                ) );
            ?>
        </div>
    </div>
</div>

