<?php

 render_control('PageHead', 'head1',
    array('title' => '库位商品列表[' . $response['store_name']. '-' . $response['shelf_code'] . ']',
	        'links' => array(
	            array('url' => "base/shelf/view&store_code=".$response['store_code'],'target' => '_self','title' => '返回仓库库位列表' ),
	        ),
        	'ref_table' => 'table'
    ));
   
?>


<?php

render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'label' => '查询',
        'id' => 'btn-search',
    ),
    'fields' => array(
        array('label'=>'库位', 'type'=>'select_pop', 'id'=>'shelf_code', 'select'=>'base/shelf' ),
    ),
    //'hidden_fields'=>array(array('field'=>'shelf_code', 'value'=>$response['shelf_code'])) // 配置隐藏字段
    
));

?>

<?php

render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => '商品编码',
	        		'field' => 'goods_code',
	        		'width' => '200',
	        		'align' => '',
	        ),
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => '商品名称',
	        		'field' => 'goods_name',
	        		'width' => '200',
	        		'align' => '',
	        ),
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => $response['goods_spec1_rename'],
	        		'field' => 'spec1_name',
	        		'width' => '200',
	        		'align' => '',
	        ),
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => $response['goods_spec2_rename'],
	        		'field' => 'spec2_name',
	        		'width' => '200',
	        		'align' => '',
	        ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品条形码',
                'field' => 'barcode',
                'width' => '200',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '库位名称',
                'field' => 'shelf_name',
                'width' => '200',
                'align' => '',
            ),
           array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '所属仓库',
            		'field' => 'store_name',
            		'width' => '200',
            		'align' => '',
            ),
             
        )
    ),
    'dataset' => 'base/ShelfModel::get_by_page_goods',
    'queryBy' => 'searchForm',
    'idField' => 'goods_shelf_id',
        'params' => array('filter' => array('shelf_code' => $response['shelf_code'],'store_code'=>$response['store_code'])),
    //'RowNumber'=>true,
    // 'CheckSelection'=>true,
));

?>
<script type="text/javascript">

function do_view(_index, row) {
    location.href = "?app_act=base/shelf/goods_list&shelf_code=" + row.shelf_code;
}


</script>

