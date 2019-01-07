<?php echo load_js('comm_util.js') ?>
<style type="text/css">
    #is_add_time_start,#is_add_time_end{
        width: 100px;
    }
    #consume_money_start,#consume_money_end,#consume_num_start,#consume_num_end{
        width:85px;
    }
</style>
<?php
render_control('PageHead', 'head1',
		array('title'=>'会员列表',
				'links'=>array(
						array('url'=>'crm/customer/detail&app_scene=add', 'title'=>'添加'),
                                                array('url' => 'crm/customer/import', 'title' => '导入会员', 'is_pop' => true, 'pop_size' => '500,350'),
				),
				'ref_table'=>'table'
));?>


<?php
$buttons = array(
    array(
        'label' => '查询',
        'id' => 'btn-search',
        'type' => 'submit'
    ),
);
if (load_model('sys/PrivilegeModel')->check_priv('crm/customer/export_list')) {
    $buttons[] = array(
        'label' => '导出',
        'id' => 'exprot_list',
    );
}
$keyword_type = array();
$keyword_type['customer_name'] = "会员名称";
$keyword_type['name'] = '收货人';
$keyword_type['tel'] = '手机号';
$keyword_type = array_from_dict($keyword_type);
render_control ( 'SearchForm', 'searchForm', array (
    'buttons' => $buttons,
    'show_row'=>3,
    'fields' => array (
    		array (
    				'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
    				'type' => 'input',
    				'id' => 'keyword',
                                'data' =>$keyword_type
    		),
            array (
                    'label' => '销售平台',
                    'type' => 'select_multi',
                    'id' => 'sale_channel_code',
                    'data'=>load_model('base/SaleChannelModel')->get_select()
            ),            
            array (
                    'label' => '店铺',
                    'type' => 'select_multi',
                    'id' => 'shop_code',
                    'data'=>load_model('base/ShopModel')->get_purview_shop(),
            ),
            array (
                    'label' => '黑名单',
                    'type' => 'select',
                    'id' => 'type',
                    'data'=>array(
	    array('2','是'),array('1','否'),array('','全部')
	    ),
            ),   		
    		array (
    				'label' => '国家',
    				'type' => 'select',
    				'id' => 'country',
    				'data'=> ds_get_select("country",2),
    		), 
    		array (
    				'label' => '省份',
    				'type' => 'select',
    				'id' => 'province',
    				'data'=> array(),
    		),
    		array (
    				'label' => '城市',
    				'type' => 'select',
    				'id' => 'city',
    				'data'=> array(),
    		), 
    		array (
    				'label' => '区/县',
    				'type' => 'select',
    				'id' => 'district',
    				'data'=> array(),
    		),
    		array (
    				'label' => '详细地址',
    				'type' => 'input',
    				'id' => 'address'
    		),
    		array(
            'label' => '消费数',
            'type' => 'group',
            'field' => 'consume_num',
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'consume_num_start',),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'consume_num_end', 'remark' => ''),
            	)
       		 ),
            array(
            'label' => '消费额',
            'type' => 'group',
            'field' => 'consume_money',
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'consume_money_start',),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'consume_money_end', 'remark' => ''),
            	)
        	),
            array(
            'label' => '添加时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'is_add_time_start'),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'is_add_time_end', 'remark' => ''),
            )
        ),
    )
) );
?>

<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
			array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '150',
                'align' => '',
                'buttons' => array (
                	array('id'=>'edit', 'title' => '编辑',
                		'act'=>'crm/customer/detail&app_scene=edit', 'show_name'=>'编辑',
                		'show_cond'=>'obj.is_buildin != 1'),
                	array(
	                  'id' => 'delete',
	                  'title' => '删除',
	                  'callback' => 'do_delete',
	                  'confirm' => '确定要删除这条辛辛苦苦录入的数据吗？',
	                  ), 
	                array('id' => 'enable', 'title' => '设为白名单',
                		'callback' => 'do_enable', 'show_cond' => 'obj.type == 2'),
            		array('id' => 'disable', 'title' => '设为黑名单',
                		'callback' => 'do_disable', 'show_cond' => 'obj.type == 1'), 
                ),
            ),
//            array (
//                'type' => 'text',
//                'show' => 1,
//                'title' => '销售平台',
//                'field' => 'sale_channel_name',
//                'width' => '100',
//                'align' => ''
//            ),
//            array (
//                'type' => 'text',
//                'show' => 1,
//                'title' => '店铺',
//                'field' => 'shop_name',
//                'width' => '150',
//                'align' => ''
//            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '会员名称',
                'field' => 'customer_name',
                'width' => '150',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '消费数',
                'field' => 'consume_num',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '消费额',
                'field' => 'consume_money',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '黑名单',
                'field' => 'type_html',
                'width' => '100',
                'align' => '',
            ),
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '收货人',
                'field' => 'name',
                'width' => '150',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_name',
                'width' => '150',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '手机',
                'field' => 'tel',
                'width' => '150',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '地址',
                'field' => 'address',
                'width' => '300',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '添加时间',
                'field' => 'is_add_time',
                'width' => '150',
                'align' => ''
            ),  
            
        )
    ),
    'dataset' => 'crm/CustomerModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'customer_id',
    'export' => array('id' => 'exprot_list', 'conf' => 'customer_do_list', 'name' => '会员列表','export_type'=>'file'),
    //'RowNumber'=>true,
    //'CheckSelection'=>true,
) );
?>



<script type="text/javascript">
function do_delete (_index, row) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('crm/customer/do_delete');?>', data: {customer_id: row.customer_id},
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
var url = '<?php echo get_app_url('base/store/get_area'); ?>';
$(function(){
//	$(".input-normal").css("width","85px");
    $('#country').change(function(){
        var parent_id = $(this).val();
        areaChange(parent_id,0,url);
    });
    $('#province').change(function(){
        var parent_id = $(this).val();
        areaChange(parent_id,1,url);
    });
    $('#city').change(function(){
        var parent_id = $(this).val();
        areaChange(parent_id,2,url);
    });
    $('#district').change(function(){
        var parent_id = $(this).val();
        areaChange(parent_id,3,url);
    });
    $('#country').val('1');
    $('#country').change();
})


function do_enable(_index, row) {
	_do_set_active(_index, row, 'enable');
}
function do_disable(_index, row) {
	_do_set_active(_index, row, 'disable');
}
function _do_set_active(_index, row, active) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('crm/customer/update_active');

?>',
    data: {customer_id: row.customer_id, type: active},
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