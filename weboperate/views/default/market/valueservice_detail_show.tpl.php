<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '增值服务信息',
	'links'=>array(
		array('url'=>'market/valueservice/do_list',title=>'增值服务列表')
	)
));?>
<style>
    .panel-body {padding: 2px;}
    .panel-body table {margin: 0; }
</style>
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left">增值服务</h3>
    </div>
<div class="panel-body">   
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
                        array('title'=>'增值代码', 'type'=>'input', 'field'=>'value_code','edit_scene'=>'add','show_scene'=>'add,view' ),
			array('title'=>'增值名称', 'type'=>'input', 'field'=>'value_name', ),
            array('title'=>'增值价格', 'type'=>'input', 'field'=>'value_price', ),
            array('title'=>'使用周期', 'type'=>'input', 'field'=>'value_cycle','remark'=>'月'),
           // array('title' => '增值图片', 'type' => 'file', 'field' => 'pic_path','rules' => array('ext' => '.png,.jpg,.gif'),),
            array('title' => '增值入驻地址', 'type' => 'input', 'field' => 'source_path','reamrk'=>''),
            array('title'=>'增值产品', 'type'=>'select', 'field'=>'value_cp_id','data'=>ds_get_select('chanpin',2)),
            array('title'=>'增值类别', 'type'=>'select', 'field'=>'value_cat','data'=>ds_get_select('valueserver_cat',2)),
                     //   array('title'=>'产品版本', 'type'=>'select', 'field'=>'value_cp_version','data' =>ds_get_select_by_field('product_version', 2)),
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
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
        'col'=>2,
	'data'=>$response['data'],
)); ?>
    </div>
</div>
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
</script>