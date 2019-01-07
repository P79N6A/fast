<?php render_control('PageHead', 'head1',
    array('title' => '店铺短信设置列表',
        'links' => array(),
        'ref_table' => 'table'
    ));

?>
<?php
render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
    'id' => 'btn-search',
),
    'fields' => array(
       array('label' => '店铺名称',
            'title' => '',
            'type' => 'input',
            'id' => 'shop_name',
        ),
        array(
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'sale_channel_code',
            'data' => $response['sale_channel'],
        ),
        array('label' => '是否启用',
            'title' => '',
            'type' => 'select',
            'id' => 'is_active',
        	'data'=> ds_get_select_by_field('boolstatus'),
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
            array('id' => 'enable', 'title' => '启用', 'callback' => 'do_enable', 'show_cond' => 'obj.is_active != 1', 'priv' => 'op/sms_shop_config/opt_update_active'),
            array('id' => 'disable', 'title' => '停用', 'callback' => 'do_disable', 'show_cond' => 'obj.is_active == 1', 'priv' => 'op/sms_shop_config/opt_update_active'),
            array('id' => 'edit', 'title' => '编辑','priv' => 'op/sms_shop_config/detail#scene=edit','act' => 'op/sms_shop_config/detail&app_scene=edit', 'show_name' => '编辑'),
        ),
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '启用',
        'field' => 'is_active_text',
        'width' => '40',
        'align' => '',
        'format' => array('type' => 'map_checked'),
    ),
	array(
        'type' => 'text',
        'show' => 1,
        'title' => '所属平台',
        'field' => 'sale_channel_name',
        'width' => '200',
        'align' => '',
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '店铺名称',
        'field' => 'shop_name',
        'width' => '200',
        'align' => '',
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '启用发货通知',
        'field' => 'delivery_notice_status_text',
        'width' => '90',
        'align' => '',
        'format' => array('type' => 'map_checked'),
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '发货通知模板',
        'field' => 'tpl_name',
        'width' => '200',
        'align' => '',
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '短信发送时间',
        'field' => 'send_time',
        'width' => '200',
        'align' => '',
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '备注',
        'field' => 'remark',
        'width' => '200',
        'align' => '',
    ),
)
),
    'dataset' => 'op/SmsShopConfigModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'shop_code',
));

?>

<script type="text/javascript">
function do_enable(_index, row) {
	_do_set_active(_index, row, 'enable');
}
function do_disable(_index, row) {
	_do_set_active(_index, row, 'disable');
}
function _do_set_active(_index, row, active) {
	$.ajax({ type: 'POST', dataType: 'json',
        url: '<?php echo get_app_url('op/sms_shop_config/update_active');?>',
        data: {shop_code: row.shop_code, type: active},
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
});
</script>