<?php render_control('PageHead', 'head1',
    array('title' => '短信发送列表',
        'links' => array(
            array('url' => 'sys/sms_queue/batch_send', 'title' => '批量发送短信', 'is_pop' => true, 'pop_size' => '800,500'),
        ),
        'ref_table' => 'table'
    ));
?>

<?php
render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
    'label' => '查询',
    'id' => 'btn-search',
),
    'fields' => array(
        array('label' => '会员',
            'title' => '会员',
            'type' => 'input',
            'id' => 'user_nick',
        ),
        array('label' => '手机号码',
            'title' => '手机号码',
            'type' => 'input',
            'id' => 'tel',
        ),
        array('label' => '发送内容',
            'title' => '发送内容',
            'type' => 'input',
            'id' => 'msg_content',
        ),
        array(
            'label' => '发送时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'send_time_start'),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'send_time_end'),
            )
        ),
        array('label' => '发送状态',
            'title' => '发送状态',
            'type' => 'select',
            'id' => 'status',
        	'data'=>array(
	    array(2,'失败'),array(1,'成功'),array('','全部'),
	    )
        ),
    )
));

?>

<?php
render_control('DataTable', 'table', array('conf' => array('list' => array(
    
	array('type' => 'button',
        'show' => 1,
        'title' => '操作',
        'field' => '_operate',
        'width' => '150',
        'align' => '',
        'buttons' => array(
            array('id' => 'view', 'title' => '查看',
                'act' => 'pop:sys/sms_queue/detail', 'show_name' => '查看'),
            array('id' => 're_send', 'title' => '重试发送',
                'callback' => 'do_re_send', 'show_cond' => 'obj.status == 2'),
            array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'confirm' => '确认要删除吗？', 'show_cond' => 'obj.status == 0||2'),
        ),
    ),
	array('type' => 'text',
        'show' => 1,
        'title' => '会员',
        'field' => 'user_nick',
        'width' => '100',
        'align' => '',
    ),
    array('type' => 'text',
        'show' => 1,
        'title' => '手机号码',
        'field' => 'tel',
        'width' => '100',
        'align' => '',
    ),
    array('type' => 'text',
        'show' => 1,
        'title' => '短信内容',
        'field' => 'msg_content',
        'width' => '200',
        'align' => '',
        'format' => array('type' => 'truncate',
            'value' => 20,
        )
    ),
    array('type' => 'text',
        'show' => 1,
        'title' => '发送时间',
        'field' => 'send_time',
        'width' => '200',
        'align' => '',
    ),
    array('type' => 'text',
        'show' => 1,
        'title' => '发送状态',
        'field' => 'status_exp',
        'width' => '100',
        'align' => '',
    ),
    
)
),
    'dataset' => 'sys/SmsQueueModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    // 'RowNumber'=>true,
    'CheckSelection' => true,
));

?>
<script type="text/javascript">
function do_delete (_index, row) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('sys/sms_queue/do_delete');
?>', data: {id: row.id},
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
}

function do_re_send(_index, row) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('sys/sms_queue/do_re_send');
?>',
    data: {id: row.id},
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