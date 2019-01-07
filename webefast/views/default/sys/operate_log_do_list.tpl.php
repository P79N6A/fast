
<?php render_control('PageHead', 'head1',
		array('title'=>'操作日志',
				
				'links'=>array(
						array('url'=>'sys/operate_log/delete&app_scene=add', 'title'=>'删除日志', 'is_pop'=>true, 'pop_size'=>'500,400'),
				),
				'ref_table'=>'table'
));?>

<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => '登录名',
            'type' => 'input',
            'id' => 'user_code'
        ),
        array(
            'label' => '真实姓名',
            'type' => 'input',
            'id' => 'user_name'
        ),
        array(
        		'label' => 'IP地址',
        		'type' => 'input',
        		'id' => 'ip'
        ),
        array(
        		'label' => '业务模块',
        		'type' => 'select',
        		'id' => 'module',
        		'data'=>$response['module'],
        ),
        array(
        		'label' => '操作类型',
        		'type' => 'select',
        		'id' => 'operate_type',
        		'data'=>$response['operate_type'],
        ),
        array(
        		'label' => '商品/单据编码',
        		'type' => 'input',
        		'id' => 'yw_code'
        ),
        array(
            'label' => '操作时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'add_time_start'),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'add_time_end'),
            )
        ),
        
        
    )
));
?>

<?php

render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '登录名',
                'field' => 'user_code',
                'width' => '120',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '真实姓名',
                'field' => 'user_name',
                'width' => '100',
                'align' => '' 
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '业务模块',
            		'field' => 'module',
            		'width' => '100',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '操作类型',
            		'field' => 'operate_type',
            		'width' => '100',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '商品/单据编码',
            		'field' => 'yw_code',
            		'width' => '150',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '操作时间',
            		'field' => 'add_time',
            		'width' => '200',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '登录IP',
            		'field' => 'ip',
            		'width' => '200',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '操作详情',
            		'field' => 'operate_xq',
            		'width' => '500',
            		'align' => ''
            ),
        ) 
    ),
    'dataset' => 'sys/OperateLogModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'operate_log_id',
   // 'params' => array('filter' => array('add_time_start' => '2014-11-01','add_time_end ' => '2014-11-11',)),
    //'RowNumber'=>true,
    //'CheckSelection'=>true,
) );
?>

<script type="text/javascript">

function do_delete() {
	_confirmMsg = template('确认要删除日志吗？');
	top.BUI.Message.Confirm(_confirmMsg, function(){
		$.ajax({ type: 'POST', dataType: 'json',  
		    url: '<?php echo get_app_url('sys/operate_log/do_delete');?>', 
		    success: function(ret) {
		    	var type = ret.status == 1 ? 'success' : 'error';
		    	if (type == 'success') {
		        BUI.Message.Alert('删除成功：', type);
		        tableStore.load();
		    	} else {
		        BUI.Message.Alert(ret.message, type);
		    	}
		    }
			});		
		},'question');
	return ;
}


</script>

