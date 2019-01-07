<?php render_control('PageHead', 'head1',
    array('title' => '淘宝订单全链路',
        'links' => array(
            array('url' => '', 'title' => '一键上传', 'is_pop' => true, 'pop_size' => '600,450'),
            array('url' => 'sys/state_map/do_list', 'title' => '状态映射'),
        ),
        'ref_table' => 'table'
    ));

?>
<?php
render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
    'id' => 'btn-search',
),
    'fields' => array(
        array('label' => '店铺',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'shop_code',
        	'data' => load_model('base/ShopModel')->get_purview_shop(),
	),
       array('label' => '全链路状态',
            'title' => '',
            'type' => 'select',
            'id' => 'status',
       		'data' => $response['link_state'],
        ),
       array('label' => '上传状态',
            'title' => '',
            'type' => 'select',
            'id' => 'efast_process_flag',
       		'data'=>array(
		    array('0','未上传'),array('1','上传成功'),array('2','上传失败'),
			array('','请选择')
	    )),
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
            array('id' => 'enable', 'title' => '上传',
                'callback' => 'do_enable', 'show_cond' => 'obj.is_active != 1'),
            array('id' => 'disable', 'title' => '已上传',
                 'show_cond' => 'obj.is_active == 1'),
        ),
    ),
	array('type' => 'text',
        'show' => 1,
        'title' => '店铺',
        'field' => 'shop_name',
        'width' => '200',
        'align' => '',
    ),
	array('type' => 'text',
        'show' => 1,
        'title' => '交易号',
        'field' => 'tid',
        'width' => '200',
        'align' => '',
    ),
    array('type' => 'text',
        'show' => 1,
        'title' => '全链路状态',
        'field' => 'link_status',
        'width' => '200',
        'align' => '',
    ),
        array('type' => 'text',
        'show' => 1,
        'title' => '创建时间',
        'field' => 'action_time',
        'width' => '200',
        'align' => '',
    ),
        array('type' => 'text',
        'show' => 1,
        'title' => '消息上传状态',
        'field' => 'upload_status',
        'width' => '200',
        'align' => '',
    ),
        array('type' => 'text',
        'show' => 1,
        'title' => '上传时间',
        'field' => 'efast_process_time',
        'width' => '200',
        'align' => '',
    ),
    
)
),
    'dataset' => 'sys/OrderLinkModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
));

?>

<script type="text/javascript">


function _do_set_active(_index, row, active) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('sys/sms_tpl/update_active');

?>',
    data: {id: row.id, type: active},
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


$(function(){
	$(".control-label").css("width","110px");
})

</script>