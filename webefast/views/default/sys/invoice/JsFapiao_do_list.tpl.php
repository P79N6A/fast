<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


render_control('PageHead', 'head1',
    array('title' => '开票主体信息列表',
          'links' => array(array('url'=>'sys/invoice/JsFapiao/detail&app_scene=add', 'title' => '新增开票主体', 'is_pop' => false, 'pop_size' => '900,600')),
          'ref_table' => 'table'
    ));

render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
    'id' => 'btn-search',
),
    'fields' => array(
        array(
            'label' => '主体挂靠店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
           'data' => load_model('base/ShopModel')->get_purview_shop(),
         ),
         array(
            'label' => '企业名称',
            'type' => 'select_multi',
            'id' => 'nsrmc',
            'data' =>load_model('oms/invoice/JsFapiaoModel')->get_nsrmc(),
         ),
    )
));
$button = array(
            array('id' => 'edit', 'title' => '编辑', 'act' => 'sys/invoice/JsFapiao/detail&app_scene=edit', 'show_name' => '编辑'),
            array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'confirm' => '确定要删除这条辛辛苦苦录入的数据吗？'),
        );

//$param = load_model('sys/SysParamsModel')->get_val_by_code('wms_split_goods_source');
//$arr = $param['wms_split_goods_source'] == 1 ? array_merge($button, $ex_button): $button;

render_control('DataTable', 'table', array('conf' => array('list' => array(
	
    array('type' => 'button',
        'show' => 1,
        'title' => '操作',
        'field' => '_operate',
        'width' => '200',
        'align' => '',
        'buttons' => $button
    ),
	array('type' => 'text',
        'show' => 1,
        'title' => '配置名称',
        'field' => 'invoice_config_name',
        'width' => '200',
        'align' => '',
    ),

)
),
    'dataset' => 'oms/invoice/JsFapiaoModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
));

?>

<script type="text/javascript">

function do_delete (_index, row) {
    $.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('sys/invoice/JsFapiao/do_delete');?>', data: {id: row.id},
    success: function(ret) {
    	var type = ret.status == 1 ? 'success' : 'error';
    	if (type == 'success') {
        BUI.Message.Alert('删除成功', type);
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