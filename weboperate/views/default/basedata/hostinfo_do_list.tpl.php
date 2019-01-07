<?php render_control('PageHead', 'head1',
array('title'=>'云主机(VM)列表',
	'links'=>array(
        array('url'=>'basedata/hostinfo/detail&app_scene=add', 'title'=>'新建云主机(VM)信息',  'pop_size'=>'500,400'),
	),
	'ref_table'=>'table'
));?>
<?php
$host_info = array();
$host_info['client_name'] = '客户名称';
$host_info['ipaddr'] = 'IP地址';
$host_info['ali_another_name'] = '别名';
$host_info['ali_notes'] = '备注';
$host_info = array_from_dict($host_info);
render_control ( 'SearchForm', 'searchForm', array (
    'cmd' => array (
        'label' => '查询',
        'title' => '查询',
        'id' => 'btn-search' 
    ),
    'fields' => array (
//        array (
//            'label' => '云服务供应商',
//            'type' => 'input',
//            'title' => '供应商',
//            'id' => 'supplier_name'
//        ),
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $host_info),
            'type' => 'input',
            'title' => '',
            'data' => $host_info,
            'id' => 'keyword',
        ),

//        array(
//            'label' => '客户名称',
//            'type' => 'input',
//            'title' => '客户',
//            'id' => 'client_name'
//        ),
        array (
            'label' => '云服务供应商',
            'type' => 'select',
            'id' => 'ali_type',
            'data'=>ds_get_select('host_cloud',2)
        ),
//        array (
//            'label' => 'IP地址',
//            'title' => '外网IP地址',
//            'type' => 'input',
//            'id' => 'ipaddr'
//        ),
        /*array (
            'label' => '到期时间',
            'title' => '服务器到期时间',
            'type' => 'date',
            'id' => 'ali_endtime' 
        ),*/
        array(
            'label' => '到期时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'ali_endtime_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'ali_endtime_end', 'remark' => ''),
            )
        ),
        array (
            'label' => '用途',
            'title' => '服务器用途',
            'type' => 'select',
            'id' => 'server_use',
             'data'=>ds_get_select_by_field('serveruse')
        ),
        array (
            'label' => '模式',
            'title' => '',
            'type' => 'select',
            'id' => 'ali_share_type',
            'data'=>ds_get_select_by_field('share_type')
        ),
        array (
            'label' => '操作系统',
            'type' => 'select',
            'id' => 'system_type',
             'data'=>ds_get_select_by_field('system_type')
        ),
        array (
            'label' => '状态',
            'type' => 'select',
            'id' => 'ali_state',
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
                'title' => '云服务商',
                'field' => 'ali_type_name',
                'width' => '70',
                'align' => '',
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '型号',
                'field' => 'ali_server_model_name',
                'width' => '100',
                'align' => '',
//                'format_js'=>array('type'=>'map', 'value'=>ds_get_field('servemodel'))
        ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '外网IP',
                'field' => 'ali_outip',
                'width' => '120',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '内网IP',
                'field' => 'ali_inip',
                'width' => '120',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '模式',
                'field' => 'ali_share_type_name',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '成本价',
                'field' => 'ali_cost_price',
                'width' => '100',
                'align' => '',
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '售价',
                'field' => 'ali_sales_price',
                'width' => '100',
                'align' => '',
            ),
            array (
                'type' => 'input',
                'show' => 1,
                'title' => '到期时间',
                'field' => 'ali_endtime',
                'width' => '120',
                'align' => '' 
            ),
            array (
                'type' => 'input',
                'show' => 1,
                'title' => '用途',
                'field' => 'ali_server_use',
                'width' => '100',
                'align' => '',
                'format_js'=>array('type'=>'map', 'value'=>ds_get_field('serveruse'))
            ),  
            array(
                'type' => 'checkbox',
                'show' => 1,
                'title' => '状态',
                'field' => 'ali_state',
                'width' => '50',
                'align' => '',
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '别名',
                'field' => 'ali_another_name',
                'width' => '120',
                'align' => ''
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
                		'act'=>'basedata/hostinfo/detail&app_scene=view', 'show_name'=>'查看云主机(VM)信息'),
                        array('id'=>'edit', 'title' => '编辑', 
                		'act'=>'basedata/hostinfo/detail&app_scene=edit', 'show_name'=>'编辑云主机(VM)信息'),
                        array('id'=>'net_test', 'title' => '连接测试', 
                		 'show_name'=>'连接测试','callback'=>'do_net_test'),            
                        array('id'=>'viewpass', 'title' => '查看密码', 
                		'act'=>'pop:basedata/hostinfo/viewpass', 'show_name'=>'查看密码'),
                        array('id'=>'change_pass', 'title' => '修改密码', 
                		'act'=>'pop:basedata/hostinfo/change_pass&app_scene=edit', 'show_name'=>'修改密码',  
                		'show_cond'=>'obj.is_buildin != 1'),
                        array('id' => 'forreset_pass', 'title' => '强制重置密码',
                                'act'=>'pop:basedata/hostinfo/forreset_pass&app_scene=edit', 'show_name' => '强制重置密码',
                                'show_cond'=>'obj.is_buildin != 1'),
                        array('id' => 'reset_pass', 'title' => '自动重置密码',
                                'show_name' => '自动重置密码','callback' => 'do_reset_pass'),
                        array('id'=>'enable', 'title' => '启用', 
                		'callback'=>'do_enable', 'show_cond'=>'obj.ali_state != 1'),
                        array('id'=>'disable', 'title' => '停用', 
                            'callback'=>'do_disable', 'show_cond'=>'obj.ali_state == 1', 
                            'confirm'=>'确认要停用吗？'),
                ),
            )
        ) 
    ),
    'dataset' => 'basedata/HostModel::get_host_info',
    'queryBy' => 'searchForm',
    'idField' => 'host_id',
    'CheckSelection'=>true,
    'events'=>array(
            // 双击事件，双机进入详细
            'rowdblclick'=>array('ref_button'=>'view'), 
        )
) );
?>
<script type="text/javascript">
function do_net_test(_index, row) {
    $.ajax({ type: 'POST', dataType: 'json',  
        url:'<?php echo get_app_url('basedata/hostinfo/net_test');?>',
        data: {_id: row.host_id}, 
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

//单条记录密码重置
    function do_reset_pass(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('basedata/hostinfo/do_reset_pass'); ?>',
            data: {host_id: row.host_id},
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    var strmsg="操作完成,ROOT密码重置"+ret.data.rootstate+",WEB密码重置:"+ret.data.webstate;
                    BUI.Message.Alert(strmsg, type);
                    tableStore.load();
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
        var url='<?php echo get_app_url('basedata/hostinfo/set_active');?>';
	$.ajax({ type: 'POST', dataType: 'json',  
            url:url+"_"+active,
            data: {host_id: row.host_id, type: active}, 
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
<style>
    #keyword_type{width:100px;}
</style>




