<?php render_control('PageHead', 'head1',
array('title'=>'客户信息',
	'links'=>array(
        array('url'=>'clients/clientinfo/detail&app_scene=add', 'title'=>'新建客户', 'is_pop'=>false, 'pop_size'=>'500,400'),
               array('type'=>'js', 'js'=>'update_kh_sys_auth()','title'=>'同步客户信息到业务系统', ),
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
            'title' => '名称',
            'type' => 'input',
            'id' => 'client_name' 
        ),
        array (
            'label' => '销售渠道',
            'title' => '销售渠道',
            'type' => 'select_pop',
            'id' => 'kh_place',
            'select'=>'basedata/sellchannel',
            'selecttype'=>'tree',
        ),
        array (
            'label' => '备注',
            'title' => '备注',
            'type' => 'text',
            'id' => 'kh_memo',
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
                'title' => '编号',
                'field' => 'kh_id',
                'width' => '50',
                'align' => '' 
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '销售渠道',
                'field' => 'kh_place_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '客户名称',
                'field' => 'kh_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'input',
                'show' => 1,
                'title' => '创建时间',
                'field' => 'kh_createdate',
                'width' => '150',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '客户IT姓名',
                'field' => 'kh_itname',
                'width' => '80',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '客户IT电话',
                'field' => 'kh_itphone',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'checkbox',
                'show' => 1,
                'title' => '审核状态',
                'field' => 'kh_verify_status',
                'width' => '70',
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
                		'act'=>'clients/clientinfo/detail&app_scene=view', 'show_name'=>'客户详细'),
                        array('id'=>'edit', 'title' => '编辑', 
                		'act'=>'clients/clientinfo/detail&app_scene=edit', 'show_name'=>'编辑客户', 
                		'show_cond'=>'obj.is_buildin != 1'),
                        array('id'=>'shopshow', 'title' => '店铺查询', 
                		'act'=>'pop:clients/clientinfo/shoplist','pop_size'=>'700,500','show_name'=>'<i>[{kh_name}]</i>店铺信息',),
                        array('id'=>'vmshow', 'title' => '云主机查询', 
                		'act'=>'pop:clients/clientinfo/vmlist','pop_size'=>'700,500','show_name'=>'<i>[{kh_name}]</i>云主机信息',),
                        array('id'=>'rdsshow', 'title' => 'RDS查询', 
                		'act'=>'pop:clients/clientinfo/rdslist','pop_size'=>'700,500','show_name'=>'<i>[{kh_name}]</i>RDS信息',),
                        array('id'=>'ordershow', 'title' => '订购查询', 
                		'act'=>'pop:clients/clientinfo/orderlist', 'show_name'=>'<i>[{kh_name}]</i>订购信息',),
                        array('id'=>'authshow', 'title' => '授权查询', 
                		'act'=>'pop:clients/clientinfo/authlist', 'show_name'=>'<i>[{kh_name}]</i>授权信息',),
                        array('id'=>'clientauth', 'title' => '审核客户', 
                		'show_cond'=>'obj.kh_verify_status != 1','show_name'=>'审核客户信息','callback'=>'check_clients','confirm' => '确认审核此客户'),        
                ),
            )
        ) 
    ),
    'dataset' => 'clients/ClientModel::get_clients_info',
    'queryBy' => 'searchForm',
    'idField' => 'kh_id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
) );
?>
<script>
        function check_clients(_index, row) {
        $.ajax({ type: 'POST', dataType: 'json',  
        url:'<?php echo get_app_url('clients/clientinfo/do_check_clients');?>',
        data: {_id: row.kh_id}, 
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
function update_kh_sys_auth(){

       var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择客户", 'error');
            return;
        }
        var ids ={};
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.kh_id);
        } 
        var ids_str = ids.join(',');
        var url = "?app_act=clients/clientinfo/update_kh_sys_auth";
        $.post(url,{kh_id:ids_str},function(ret){
            if(ret.status>1){
                 BUI.Message.Alert(ret.message, 'info');
            }else{
                 BUI.Message.Alert(ret.message, 'error');
            }
        },'json');
        
        
}


</script>

