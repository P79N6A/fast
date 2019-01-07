<?php render_control('PageHead', 'head1',
    array('title' => '商品库位管理',

        'links' => array(
            array('url' => 'prm/goods_shelf/scanning_unbind', 'title' => '扫描解除绑定', 'is_pop' => true, 'pop_size' => '600,300'),
            array('url' => 'prm/goods_shelf/a_key_unbind', 'title' => '一键解除绑定', 'is_pop' => true, 'pop_size' => '300,300'),
            array('url' => 'prm/goods_shelf/import', 'title' => '库位商品导入(按条码)', 'is_pop' => true, 'pop_size' => '500,400'),
            array('url' => 'prm/goods_shelf/import_bygoods_code', 'title' => '库位商品导入(按商品编码)', 'is_pop' => true, 'pop_size' => '500,400')
        ),
        'ref_table' => 'table'
    ));?>

<?php

	render_control ( 'SearchForm', 'searchForm', array (
	    'buttons' =>array(
	    array(
	        'label' => '查询',
	        'id' => 'btn-search',
			  'type'=>'submit'
	    ),
	           array(
	        'label' => '导出',
	        'id' => 'exprot_list',
	    ),
	         ) ,
	    'show_row'=>4,
	    'fields' => array (
	        array (
	            'label' => '分类',
	            'type' => 'select_multi',
	            'id' => 'category_code',
	            'data'=>$response['category'],
	            'value' =>$response['category_code_val'],
	        ),
	        array (
	            'label' => '品牌',
	            'type' => 'select_multi',
	            'id' => 'brand_code',
	            'data'=>ds_get_select('brand_code'),
	            'value' => $response['brand_code_val'],
	        ),
	        array(
	            'label' => '仓库',
	            'type' => 'select_multi',
	            'id' => 'store_code',
	            'data' => load_model('base/StoreModel')->get_select(),
	        ),
	        array (
	            'label' => '商品编码',
	            'type' => 'input',
	            'id' => 'goods_code',
	            'value' => $response['goods_code_val'],
	        ),
	        array (
	            'label' => '商品名称',
	            'type' => 'input',
	            'id' => 'goods_name',
	            'value' => $response['goods_name_val'],
	        ),
	        array (
	            'label' => '商品条形码',
	            'type' => 'input',
	            'id' => 'barcode',
	            'value' => $response['barcode_val'],
	        ),
	    )
	) );

?>

<ul class="nav-tabs oms_tabs" style="margin-bottom:6px;">
    <li><a href="#" onClick="do_page('do_list');">已绑定</a></li>
    <li class="active"><a href="#" onClick="do_page('ex_list');">未绑定</a></li>
</ul>

<?php
if(isset($response['lof_status']) && $response['lof_status'] == '1'){
	render_control('DataTable', 'table', array(
	    'conf' => array(
	        'list' => array(
                    array(
	                'type' => 'button',
	                'show' => 1,
	                'title' => '操作',
	                'field' => '_operate',
	                'width' => '150',
	                'align' => '',
	                'buttons' => array(
	                    array('id' => 'bind', 'title' => '绑定库位', 'callback'=>'do_bind'),
	                ),
	            ),
	            array (
	                'type' => 'text',
	                'show' => 1,
	                'title' => '商品编码',
	                'field' => 'goods_code',
	                'width' => '100',
	                'align' => ''
	            ),
	            array (
	                'type' => 'text',
	                'show' => 1,
	                'title' => '商品名称',
	                'field' => 'goods_name',
	                'width' => '200',
	                'align' => ''
	            ),
	            /*array (
	                'type' => 'text',
	                'show' => 1,
	                'title' => $result['data'][0]['name'].'编码',
	                'field' => 'spec1_code',
	                'width' => '100',
	                'align' => ''
	            ),*/
	            array (
	                'type' => 'text',
	                'show' => 1,
	                'title' => $response['goods_spec1_rename'],
	                'field' => 'spec1_name',
	                'width' => '100',
	                'align' => ''
	            ),
	            /*array (
	                'type' => 'text',
	                'show' => 1,
	                'title' => $result['data'][1]['name'].'编码',
	                'field' => 'spec2_code',
	                'width' => '100',
	                'align' => ''
	            ),*/
	            array (
	                'type' => 'text',
	                'show' => 1,
	                'title' => $response['goods_spec2_rename'],
	                'field' => 'spec2_name',
	                'width' => '100',
	                'align' => ''
	            ),
	            array (
	                'type' => 'text',
	                'show' => 1,
	                'title' => '商品条形码',
	                'field' => 'barcode',
	                'width' => '150',
	                'align' => ''
	            ),
	            array (
	                'type' => 'text',
	                'show' => 1,
	                'title' => '批次',
	                'field' => 'lof_no',
	                'width' => '150',
	                'align' => ''
	            ),
	            array (
	                'type' => 'text',
	                'show' => 1,
	                'title' => '生产日期',
	                'field' => 'production_date',
	                'width' => '150',
	                'align' => ''
	            ),
	            /*array(
	                'type' => 'text',
	                'show' => 1,
	                'title' => '仓库',
	                'field' => 'store_name',
	                'width' => '100',
	                'align' => '',
	            ),
	            array(
	                'type' => 'text',
	                'show' => 1,
	                'title' => '库位',
	                'field' => 'shelf_code',
	                'width' => '100',
	                'align' => '',
	            ),*/
	
	            
	        )
	    ),
	    'dataset' => 'prm/GoodsShelfModel::ex_by_page',
	    'queryBy' => 'searchForm',
	    'idField' => 'goods_inv_id',
	    'params' => array('filter' => array('category_code' => $response['category_code_val'],'brand_code'=>$response['brand_code_val'],'goods_code'=>$response['goods_code_val'],'goods_name'=>$response['goods_name_val'],'barcode'=>$response['barcode_val'])),
	    'export'=> array('id'=>'exprot_list','conf'=>'goods_shelf_list','name'=>'商品未绑定库位列表'),
	    //'RowNumber'=>true,
	    // 'CheckSelection'=>true,
	));
}else{
	render_control('DataTable', 'table', array(
	'conf' => array(
	'list' => array(
	array (
	'type' => 'text',
	'show' => 1,
	'title' => '商品编码',
	'field' => 'goods_code',
	'width' => '100',
	'align' => ''
	),
	array (
	'type' => 'text',
	'show' => 1,
	'title' => '商品名称',
	'field' => 'goods_name',
	'width' => '200',
	'align' => ''
	),
	/*array (
	 'type' => 'text',
	 'show' => 1,
	 'title' => $result['data'][0]['name'].'编码',
	 'field' => 'spec1_code',
	 'width' => '100',
	 'align' => ''
	 ),*/
	array (
	'type' => 'text',
	'show' => 1,
	'title' => $response['goods_spec1_rename'],
	'field' => 'spec1_name',
	'width' => '100',
	'align' => ''
	),
	/*array (
	 'type' => 'text',
	 'show' => 1,
	 'title' => $result['data'][1]['name'].'编码',
	 'field' => 'spec2_code',
	 'width' => '100',
	 'align' => ''
	 ),*/
	array (
	'type' => 'text',
	'show' => 1,
	'title' => $response['goods_spec2_rename'],
	'field' => 'spec2_name',
	'width' => '100',
	'align' => ''
	),
	array (
	'type' => 'text',
	'show' => 1,
	'title' => '商品条形码',
	'field' => 'barcode',
	'width' => '120',
	'align' => ''
	),
	
	array(
	'type' => 'button',
	'show' => 1,
	'title' => '操作',
	'field' => '_operate',
	'width' => '200',
	'align' => '',
	'buttons' => array(
	array('id' => 'bind', 'title' => '绑定库位', 'callback'=>'do_bind'),
	),
	)
	)
	),
	'dataset' => 'prm/GoodsShelfModel::ex_by_page',
	'queryBy' => 'searchForm',
	'idField' => 'goods_inv_id',
	'params' => array('filter' => array('category_code' => $response['category_code_val'],'brand_code'=>$response['brand_code_val'],'goods_code'=>$response['goods_code_val'],'goods_name'=>$response['goods_name_val'],'barcode'=>$response['barcode_val'])),
	'export'=> array('id'=>'exprot_list','conf'=>'goods_unbind_shelf_list','name'=>'商品未绑定库位列表'),
	//'RowNumber'=>true,
	// 'CheckSelection'=>true,
	));
}
?>
<script>
    function do_bind(index, row){
        var url = "?app_act=prm/goods_shelf/bind&sku="+row.sku.toString()+"&_id="+row.goods_inv_id.toString();
        <?php    if($response['lof_status']==1):?>
                //+"&lof_no"+row.lof_no.toString()
          url +="&lof_no="+row.lof_no.toString();   
        <?php    endif;?>

        new ESUI.PopWindow(url, {
            title: "绑定库位",
            width:1000,
            height:600,
            onBeforeClosed: function() {
            },
            onClosed: function(){
                //刷新数据
                tableStore.load()
            }
        }).show()
    }
    function do_page(param) {	
        location.href = "?app_act=prm/goods_shelf/"+param+"&category_code=" + $("#category_code").val()+"&brand_code="+$("#brand_code").val()+"&goods_code="+$("#goods_code").val()+"&goods_name="+$("#goods_name").val()+"&barcode="+$("#barcode").val()+"&ES_frmId=prm/goods_shelf/do_list";
    }
</script>