<?php
render_control('PageHead', 'head1', array('title' => '计划任务',
    'ref_table' => 'table'
));
?>



<?php 
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'type' => 'button',
        'id' => 'btn-search',
    ),
    'fields' => array(
        array(
            'label' => '销售平台',
            'title' => '销售平台',
            'type' => 'select',
            'id' => 'sale_channel_id',
            'data' => $response['sale_channel'],
        ),
        array(
            'label' => '任务类型',
            'title' => '任务类型',
            'type' => 'select',
            'id' => 'class_code',
            'data' => $response['sys_schedule']['class']
        ),
        array(
            'label' => '状态',
            'title' => '状态',
            'type' => 'select',
            'id' => 'status',
            'data' => $response['sys_schedule']['status']
        ),
    )
));
?>


<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => '销售平台',
	        		'field' => 'sale_channel_name',
	        		'width' => '200',
	        		'align' => '',
	        ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '任务类型',
                'field' => 'class_name',
                'width' => '200',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '描述',
                'field' => 'desc',
                'width' => '250',
                'align' => '',
            ),

            array('type' => 'button',
            		'show' => 1,
            		'title' => '开启服务',
            		'field' => 'status',
            		'width' => '200',
            		'align' => '',
            		'buttons' => array(
            				array('id' => 'enable', 'title' => '启用',
            						'callback' => 'do_enable', 'show_cond' => 'obj.status != 1'),
            				array('id' => 'disable', 'title' => '停用',
            						'callback' => 'do_disable', 'show_cond' => 'obj.status == 1',
            						'confirm' => '确认要停用吗？'),
            		),
                ),
            array('type' => 'button',
            		'show' => 1,
            		'title' => '高级设置',
            		'field' => '_operate',
            		'width' => '200',
            		'align' => '',
            		'buttons' => array(
            				array('id' => 'edit', 'title' => '店铺设置',
                                                        'act' => 'pop:sys/sys_schedule/get_schedule_shop', 'show_name' => '店铺设置',
            						),

            		),
            )
           /*
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '300',
                'align' => '',
                'buttons' => array(
                    array('id' => 'view', 'title' => '延长SESSION',
                        'act' => 'pop:base/shop/detail&app_scene=view', 'show_name' => '延长SESSION'),
                    array('id' => 'reset_password', 'title' => '接口测试', 'callback' => 'do_test_api'),
                    array('id' => 'edit', 'title' => '编辑',
                        'act' => 'pop:base/shop/detail&app_scene=edit', 'show_name' => '编辑'),
                    array('id' => 'delete', 'title' => '删除',
                        'callback' => 'do_delete',
                        'confirm' => '确认要删除吗？'),
                ),
            )*/
        )
    ),
    'dataset' => 'sys/SysScheduleModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    //'RowNumber'=>true,
    //'CheckSelection' => true,
));
?>


<script type="text/javascript">

function do_enable(_index, row) {
	set_status(row.id, 1);
}
function do_disable(_index, row) {
	set_status(row.id, 0);
}
 function set_status(id, status) {
        $.ajax({ type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('sys/sys_schedule/set_schedule_status'); ?>',
            data: {id: id, status: status},
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                     tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }


</script>