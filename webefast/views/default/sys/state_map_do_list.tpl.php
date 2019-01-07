<?php render_control('PageHead', 'head1',
    array('title' => '淘宝订单全链路状态映射',
        'links' => array(
            array('url' => 'sys/order_link/do_list', 'title' => '淘宝订单全链路列表'),
        ),
        'ref_table' => 'table'
    ));

?>

<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (

			array('type' => 'button',
		        'show' => 1,
		        'title' => '操作',
		        'field' => '_operate',
		        'width' => '150',
		        'align' => '',
		        'buttons' => array(
		            array('id' => 'enable', 'title' => '设置',
		                'callback' => 'do_config'),
		        ),
		    ),

			array('type' => 'text',
		        'show' => 1,
		        'title' => '全链路状态',
		        'field' => 'status_text',
		        'width' => '200',
		        'align' => '',
		    ),
			array('type' => 'text',
		        'show' => 1,
		        'title' => '系统状态',
		        'field' => 'sys_text',
		        'width' => '200',
		        'align' => '',
		    ),      
        )
    ),
    'dataset' => 'sys/StateMapModel::get_by_page',
//    'queryBy' => 'searchForm',
    'idField' => 'express_id',
    //'RowNumber'=>true,
//    'CheckSelection'=>true,
) );
?>


<script type="text/javascript">

function PageHead_show_dialog(_url, _title, _opts) {

    new ESUI.PopWindow(_url, {
        title: _title,
        width:_opts.w,
        height:_opts.h,
        onBeforeClosed: function() {
            if (typeof _opts.callback == 'function') _opts.callback();
        }
    }).show();
}

function do_config(_index, row) {
	PageHead_show_dialog("?app_act=sys/state_map/sys_state_list&app_scene=add&app_show_mode=pop&id="+row.id+"&link_name="+row.status_text, '设置', {w:500,h:400});
}

$(function(){
    top.n_tableStore = tableStore;
});

</script>