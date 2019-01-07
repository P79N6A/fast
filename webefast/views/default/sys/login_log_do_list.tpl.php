
<?php render_control('PageHead', 'head1',
		array('title'=>'登录日志',
				
				'links'=>array(
						array('url'=>'sys/login_log/delete&app_scene=add', 'title'=>'删除日志', 'is_pop'=>true, 'pop_size'=>'500,400'),
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
        		'label' => '类型',
        		'type' => 'select',
        		'id' => 'type',
        		'data'=>$response['type'],
        ),
        array(
            'label' => '时间',
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
                'width' => '100',
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
            		'title' => '类型',
            		'field' => 'type',
            		'width' => '100',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '时间',
            		'field' => 'add_time',
            		'width' => '150',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => 'IP地址',
            		'field' => 'ip',
            		'width' => '300',
            		'align' => ''
            ),
            
        ) 
    ),
    'dataset' => 'sys/LoginLogModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'login_log_id',
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
		    url: '<?php echo get_app_url('sys/login_log/do_delete');?>', 
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


