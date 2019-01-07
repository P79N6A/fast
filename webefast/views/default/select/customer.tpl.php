
<?php
$keyword_type = array();
$keyword_type['code_name'] = '昵称';
$keyword_type['name_list'] = '收货人';
$keyword_type['customer_tel'] = '手机号';
$keyword_type = array_from_dict($keyword_type);
render_control ('SearchForm', 'searchForm', array ('cmd' => array ('label' => '查询',
            'id' => 'btn-search'
            ),
          'fields' => array (
		        array (
		        	'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
                                'type' => 'input',
                                'title' => '',
                                'data' => $keyword_type,
                                'id' => 'keyword',
		        ),
            
            )
        ));
        
?>
<?php

render_control ('DataTable', 'table', array ('conf' => array ('list' => array (
				
				array(
						'type' => 'text',
						'show' => 1,
						'title' => '会员名称',
						'field' => 'customer_name',
						'width' => '130',
						'align' => '',
				),
				array(
						'type' => 'text',
						'show' => 1,
						'title' => '收货人',
						'field' => 'name',
						'width' => '150',
						'align' => '',
				),
				array(
						'type' => 'text',
						'show' => 1,
						'title' => '手机',
						'field' => 'tel',
						'width' => '110',
						'align' => '',
				),
				array(
						'type' => 'text',
						'show' => 1,
						'title' => '地址',
						'field' => 'address',
						'width' => '300',
						'align' => '',
				),
                                array(
						'type' => 'text',
                                                'show' => 1,
                                                'title' => '黑名单',
                                                'field' => 'black_name',
                                                'width' => '100',
                                                'align' => '',
                                                'format_js' => array('type' => 'map_checked')
				),
                )
            ),
        'dataset' => 'crm/CustomerModel::get_by_page_list',
        'queryBy' => 'searchForm',
        'idField' => 'customer_id',
        'init' => 'nodata',
        'params' => array('filter' => array('shop_code' => $request['shop_code']))
        //'CheckSelection'=>true, // 显示复选框
        
        ));

?>

<?php echo_selectwindow_js($request, 'table', array('id'=>'customer_code', 'code'=>'customer_code', 'name'=>'customer_name')) ?>

<script type="text/javascript">
$(document).ready(function(){
	var shop_code = '<?php echo $request['shop_code']?>';
	$('#keyword').parent().addClass("customer_name");
    $('#keyword').parent(".customer_name").css({"margin-left":"90px","margin-top":"-25px"});
    $('#searchForm .customer_name').append('<input type="hidden" name="shop_code" value="'+shop_code+'" >');
});

</script>
