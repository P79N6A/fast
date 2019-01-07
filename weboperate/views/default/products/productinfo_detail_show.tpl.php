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

