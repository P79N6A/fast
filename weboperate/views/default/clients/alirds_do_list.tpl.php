 <?php render_control('PageHead', 'head1',
array('title'=>'客户云数据库列表',
	'links'=>array(
        array('url'=>'clients/alirds/detail&app_scene=add', 'title'=>'新建客户云数据库信息',  'pop_size'=>'500,400'),
	),
	'ref_table'=>'table'
));?>
<?php
render_control ( 'SearchForm', 'searchForm', array (
    'cmd' => array (
        'label' => '查询',
        'title' => '查询',
        'id' => 'btn-search' 
    ),
    'fields' => array (
        array (
            'label' => '客户名称',
            'type' => 'input',
            'id' => 'client_name',
            'title' => '客户',
        ),
        array (
            'label' => 'RDS实例名',
            'type' => 'input',
            'id' => 'dbname',
            'title' => '实例名',
        ),
        array (
            'label' => '云服务商',
            'type' => 'select',
            'id' => 'dbtype',
            'data'=>ds_get_select('host_cloud')
        ),
        /*array (
            'label' => '到期时间',
            'type' => 'date',
            'id' => 'rds_endtime' 
        ),*/
        array(
            'label' => '到期时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'rds_endtime_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'rds_endtime_end', 'remark' => ''),
            )
        ),
        array (
            'label' => '部署状态',
            'title' => '是否有效',
            'type' => 'select',
            'id' => 'rds_deployment',
            'data'=>ds_get_select_by_field('boolstatus')
        ),
        array (
            'label' => 'RDS连接地址',
            'type' => 'input',
            'id' => 'rds_link',
            'title' => 'RDS连接地址',
        ),
        array (
            'label' => 'RDS状态',
            'title' => '是否有效',
            'type' => 'select',
            'id' => 'rds_state',
            'data'=>ds_get_select_by_field('boolstate')
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
                'title' => '客户名称',
                'field' => 'kh_name',
                'width' => '150',
                'align' => '' 
            ),
//            array (
//                'type' => 'text',
//                'show' => 1,
//                'title' => '所属区域',
//                'field' => 'kh_name',
//                'width' => '150',
//                'align' => '' 
//            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => 'RDS用户',
                'field' => 'rds_user',
                'width' => '100',
                'align' => '' 
            ),
//            array (
//                'type' => 'text',
//                'show' => 1,
//                'title' => 'RDS密码',
//                'field' => 'rds_pass',
//                'width' => '200',
//                'align' => '' 
//            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => 'RDS连接',
                'field' => 'rds_link',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => 'RDS实例',
                'field' => 'rds_dbname',
                'width' => '120',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '云服务商',
                'field' => 'rds_dbtype_name',
                'width' => '80',
                'align' => '',
//                'format'=>array('type'=>'map', 'value'=>ds_get_field('servertype'))
                
            ),
            array (
                'type' => 'input',
                'show' => 1,
                'title' => '开始时间',
                'field' => 'rds_starttime',
                'width' => '90',
                'align' => '' 
            ),
            array (
                'type' => 'input',
                'show' => 1,
                'title' => '到期时间',
                'field' => 'rds_endtime',
                'width' => '90',
                'align' => '' 
            ),
            array(
                'type' => 'checkbox',
                'show' => 1,
                'title' => '状态',
                'field' => 'rds_state',
                'width' => '50',
                'align' => '',
            ),
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '300',
                'align' => '',
                'buttons' => array (
                         array('id'=>'view', 'title' => '详细', 
                		'act'=>'clients/alirds/detail&app_scene=view', 'show_name'=>'查看客户云数据库信息'), 
                        array('id'=>'edit', 'title' => '编辑', 
                		'act'=>'clients/alirds/detail&app_scene=edit', 'show_name'=>'编辑客户云数据库信息'), 
                        array('id'=>'viewpass', 'title' => '查看密码', 
                		'act'=>'pop:clients/alirds/viewpass', 'show_name'=>'查看密码'),
                        array('id'=>'change_pass', 'title' => '修改密码', 
                		'act'=>'pop:clients/alirds/change_pass&app_scene=edit', 'show_name'=>'修改密码',         
                		'show_cond'=>'obj.is_buildin != 1'),
                        array('id'=>'rds_test', 'title' => '连接测试', 
                		 'show_name'=>'连接测试','callback'=>'rds_test'),    
                        array('id' => 'forreset_pass', 'title' => '强制重置密码',
                            'act'=>'pop:clients/alirds/forreset_pass&app_scene=edit', 'show_name' => '强制重置密码',
                            'show_cond'=>'obj.is_buildin != 1'),
                        array('id'=>'enable', 'title' => '启用', 
                		'callback'=>'do_enable', 'show_cond'=>'obj.rds_state != 1'),
                        array('id'=>'disable', 'title' => '停用', 
                            'callback'=>'do_disable', 'show_cond'=>'obj.rds_state == 1', 
                            'confirm'=>'确认要停用吗？'),
                       
                ),
            )
        ) 
    ),
    'dataset' => 'clients/AlirdsModel::get_rds_info',
    'queryBy' => 'searchForm',
    'idField' => 'rds_id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
    'events'=>array(
            'rowdblclick'=>array('ref_button'=>'view')),
) );
?>
<script type="text/javascript">
function do_deployment(_index, row) {
    $.ajax({ type: 'POST', dataType: 'json',  
        url:'<?php echo get_app_url('clients/alirds/do_deployment');?>',
        data: {rds_id: row.rds_id}, 
        success: function(ret) {
            var type = ret.status == 1 ? 'success' : 'error';
            if (type == 'success') {
                BUI.Message.Alert(ret.message, type);
                tableStore.load();
            } else {
                BUI.Message.Alert(ret.message, type);
            }
        }
    });
}

function rds_test(_index, row) {
    $.ajax({ type: 'POST', dataType: 'json',  
        url:'<?php echo get_app_url('clients/alirds/do_rds_test');?>',
        data: {_id: row.rds_id}, 
        success: function(ret) {
            var type = ret.status == 1 ? 'success' : 'error';
            if (type == 'success') {
                BUI.Message.Alert(ret.message, type);
                
            } else {
                BUI.Message.Alert(ret.message, type);
            }
        }
    });
}

function do_enable(_index, row) {
	_do_set_active(_index, row, 'enable');
}
function do_disable(_index, row) {
	_do_set_active(_index, row, 'disable');
}
function _do_set_active(_index, row, active) {
        var url='<?php echo get_app_url('clients/alirds/set_active');?>';
	$.ajax({ type: 'POST', dataType: 'json',  
            url:url+"_"+active,
            data: {rds_id: row.rds_id, type: active}, 
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
	});
}

</script>
