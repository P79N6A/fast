
<?php
render_control('PageHead', 'head1', array('title' => '淘宝平台商品列表',
    'links' => array(
        array('url' => 'oms/api_goods/down&app_scene=add', 'title' => '一键下载', 'is_pop' => true, 'pop_size' => '500,400'),
    ),
    'ref_table' => 'table'
));
?>

<?php 
//库存同步
$is_synckc = array(
		'0' => '否',
		'1' => '是',
);
$is_synckc = array_from_dict($is_synckc);

render_control ( 'SearchForm', 'searchForm', array (
    'cmd' => array (
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array (
	   
	    array (
	    		'label' => '店铺',
	    		'type' => 'select_multi',
	    		'id' => 'shop_code',
	    		'data'=>oms_opts_by_tb('base_shop', 'shop_code', 'shop_name', array()),
	    ),
	    array (
	    		'label' => '商品状态',
	    		'type' => 'select_multi',
	    		'id' => 'status',
	    		'data'=>ds_get_select_by_field('approve_status',0),
	    ),
	    
        
        array (
        		'label' => '商品编码',
        		'type' => 'input',
        		'id' => 'goods_code'
        ),
        array (
        		'label' => '商品条形码',
        		'type' => 'input',
        		'id' => 'goods_barcode'
        ),
        array (
        		'label' => '库存同步',
        		'type' => 'select_multi',
        		'id' => 'is_synckc',
        		'data'=>$is_synckc,
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
	        		'width' => '100',
	        		'align' => '',
	        		'buttons' => array (
	        				array('id'=>'send', 'title' => '禁止库存同步', 'callback'=>'is_sync','show_cond'=>'obj.is_inv_sync == 0'),
	        				
	        				array('id'=>'send_again', 'title' => '再次回写', 'callback'=>'send','show_cond'=>'obj.status == -2'),
	        		),
	        ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '销售平台',
                'field' => 'source',
                'width' => '80',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '100',
                'align' => '' 
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '平台商品ID',
            		'field' => 'goods_from_id',
            		'width' => '100',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '平台商品编码',
            		'field' => 'goods_code',
            		'width' => '100',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '平台商品名称',
            		'field' => 'goods_name',
            		'width' => '100',
            		'align' => ''
            ),

            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '平台售价(元)',
            		'field' => 'price',
            		'width' => '100',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '库存更新时间',
            		'field' => '',
            		'width' => '100',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '是否有SKU',
            		'field' => '',
            		'width' => '100',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '库存扣减模式',
            		'field' => '',
            		'width' => '100',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '订单锁库存',
            		'field' => '',
            		'width' => '100',
            		'align' => '',
            		'format_js' => array('type' => 'map_checked')
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '商品状态',
            		'field' => '',
            		'width' => '100',
            		'align' => '',
            		'format'=>array('map', ds_get_field('item_sku_status')),
            ),


        ) 
    ),
    'dataset' => 'api/taobao/GoodsModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'api_goods_id',
    'CheckSelection'=>true,

	'CascadeTable'=>array(
		'list'=>array(
			array('title'=>'平台SKUID', 'field'=>'sku_id'),
			array('title'=>'平台商品条形码', 'field'=>'goods_barcode'),
			array('title'=>'平台售价(元)', 'field'=>'price'),
			array('title'=>'平台库存', 'field'=>'inv_num'),
			array('title'=>'最后同步库存数量', 'field'=>''),
			array('title'=>'最后同步库存时间', 'field'=>'price'),
			array('title'=>'是否同步库存', 'field'=>'item_sku_status', 'format_js' => array('type' => 'map_checked')),
			
		),
		'page_size'=>10,
		//'url'=>get_app_url('api/base_item/get_sku_list_by_item_id'),
		'url'=>get_app_url('oms/api_goods/get_sku_list_by_item_id'),
		'params'=>'goods_from_id'
	),
) );
?>

<script type="text/javascript">
 function is_sync(_index, row){
	 $.ajax({ type: 'POST', dataType: 'json',
		    url: '<?php echo get_app_url('oms/api_goods/update_active');?>',
		    data: {api_goods_id: row.api_goods_id, type: 'enable'},
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