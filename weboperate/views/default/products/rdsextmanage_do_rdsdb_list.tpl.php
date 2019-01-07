 <?php render_control('PageHead', 'head1',
array('title'=>'当前RDS信息',
	'links'=>array(
        
	),
	'ref_table'=>'table'
));?>
<!--?php
render_control ( 'SearchForm', 'searchForm', array (
    'cmd' => array (
        'label' => '查询',
        'title' => '查询',
        'id' => 'btn-search' 
    ),
    'fields' => array (
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
    ) 
) );
?-->
<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '数据库名称',
                'field' => 'rem_db_name',
                'width' => '180',
                'align' => '' 
            ),
            array (
                'type' => 'checkbox',
                'show' => 1,
                'title' => '绑定客户',
                'field' => 'rem_db_is_bindkh',
                'width' => '80',
                'align' => '',
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '客户名称',
                'field' => 'rem_db_khid_name',
                'width' => '180',
                'align' => '' 
            ),
            array (
                'type' => 'checkbox',
                'show' => 1,
                'title' => '试用客户',
                'field' => 'rem_try_kh',
                'width' => '80',
                'align' => '',
            ),
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '数据库操作',
                'field' => '_operate',
                'width' => '120',
                'align' => '',
                'buttons' => array (
                        array('id'=>'del', 
                                'title' => '删除', 
                                'show_cond'=>"obj.rem_db_is_bindkh != '1' || obj.rem_try_kh == '1'",
                                'callback' => 'do_delete_detail', 
                                //'confirm'=>'确认要删除吗？'
                        ),
                ),
            )
        ) 
    ),
    'dataset' => 'products/RdsextmanageModel::get_rdsdb_info',
    //'queryBy' => 'searchForm',
    'params' => array('filter'=>array('page_size'=>10,'rem_db_pid'=>$request['_id'])),
    'idField' => 'rem_db_id',
    'CheckSelection'=>false,
) );
?>
<script type="text/javascript">
    
    //删除数据库明细++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    function do_delete_detail(_index, row) {
        BUI.Message.Confirm("确认删除", function(){
            $.ajax({ type: 'POST', dataType: 'json',  
                url:"<?php echo get_app_url('products/rdsextmanage/do_delete');?>",
                data: {rem_db_id: row.rem_db_id,rem_db_is_bindkh:row.rem_db_is_bindkh,rem_db_pid: row.rem_db_pid}, 
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
        },'question');
        
    }
    
</script>
