<?php render_control('PageHead', 'head1',
array('title'=>'公告列表',
	'links'=>array(
            array('url'=>'sys/notice/detail&app_scene=add', 'title'=>'新增公告', 'is_pop'=>false, 'pop_size'=>'500,400'),
	),
	'ref_table'=>'table'  //ref_table 表示是否刷新父页面
));?>
<?php
render_control ( 'SearchForm', 'searchForm', array (
    'cmd' => array (
        'label' => '查询',
        'label' => '查询',
        'id' => 'btn-search' 
    ),
    'fields' => array (
        array (
            'label' => '公告标题',
            'title' => '公告标题',
            'type' => 'input',
            'id' => 'notice_title' 
        ),
        array (
            'label' => '审核状态',
            'title' => '是否审核',
            'type' => 'select',
            'id' => 'not_sh',
            'data'=>ds_get_select_by_field('boolstatus')
        ),
    ) 
) );
?>
<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '公告标题',
                'field' => 'not_title',
                'width' => '200',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '公告类型',
                'field' => 'not_type',
                'width' => '100',
                'align' => '',
                'format_js'=>array('type'=>'map', 'value'=>ds_get_field('nottype'))
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '截至日期',
                'field' => 'not_enddate',
                'width' => '150',
                'align' => '',
                'format'=>array('type'=>'time'),
            ),
            array (
                'type' => 'checkbox',
                'show' => 1,
                'title' => '审核',
                'field' => 'not_sh',
                'width' => '100',
                'align' => '' 
            ),
            /*array (
                'type' => 'text',
                'show' => 1,
                'title' => '审核日期',
                'field' => 'not_shdate',
                'width' => '150',
                'align' => '' 
            ),*/
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '创建人',
                'field' => 'not_createuser_name',
                'width' => '200',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '创建日期',
                'field' => 'not_createdate',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '200',
                'align' => '',
                'buttons' => array (
                	array('id'=>'view', 'title' => '查看', 
                		'act'=>'sys/notice/detail&app_scene=view', 'show_name'=>'查看公告'),
                	array('id'=>'edit', 'title' => '编辑', 
                		'act'=>'sys/notice/detail&app_scene=edit', 'show_name'=>'编辑公告', 
                		'show_cond'=>'obj.not_sh != 1'),
                        array('id'=>'do_check', 'title' => '审核', 'callback'=>'do_check_notice', 
                        'show_cond'=>"obj.not_sh != '1'", 'confirm' => '确认审核'),
                ),
            )
        ) 
    ),
    'dataset' => 'sys/NoticeModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'not_id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
) );
?>
<script type="text/javascript">
function do_check_notice (_index, row) {
    $.ajax({ type: 'POST', dataType: 'json',  
        url: '<?php echo get_app_url('sys/notice/do_check');?>', 
        data: {not_id: row.not_id}, 
        success: function(ret) {
            var type = ret.status == 1 ? 'success' : 'error';
            if (type == 'success') {
                BUI.Message.Alert("审核成功", type);
                tableStore.load();
            } else {
                BUI.Message.Alert(ret.message, type);
            }
        }
    });
}
</script>