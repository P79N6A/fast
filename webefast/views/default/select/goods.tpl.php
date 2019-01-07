
<?php
//print_r($request);
render_control ('DataTable', 'table', array ('conf' => array ('list' => array (
				array(
						'type' => 'text',
						'show' => 1,
						'title' => '商品条形码',
						'field' => 'goods_code',
						'width' => '200',
						'align' => '',
				),
				array(
						'type' => 'text',
						'show' => 1,
						'title' => '规格1',
						'field' => 'spec1_name',
						'width' => '200',
						'align' => '',
				),
				array(
						'type' => 'text',
						'show' => 1,
						'title' => '规格2',
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
				
                )
            ),
        'dataset' => 'prm/GoodsModel::get_by_page_select',
        //'queryBy' => 'searchForm',
        'idField' => 'goods_id',
        'params' => array('filter' => array('goods_code' => $request['goods_code'],'op_gift_strategy_goods_id' => $request['op_gift_strategy_goods_id'],'op_gift_strategy_detail_id' => $request['op_gift_strategy_detail_id'])),
        //'CheckSelection'=>true, // 显示复选框
        
        ));

?>

<?php echo_selectwindow_js($request, 'table', array('sku'=>'sku','op_gift_strategy_detail_id'=>'4', 'goods_code'=>'goods_code' ),'sss') ?>



