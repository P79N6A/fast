
<?php
//print_r($request);
render_control ('DataTable', 'table', array ('conf' => array ('list' => array (
				array(
						'type' => 'text',
						'show' => 1,
						'title' => '收货人',
						'field' => 'name',
						'width' => '200',
						'align' => '',
				),
				array(
						'type' => 'text',
						'show' => 1,
						'title' => '手机',
						'field' => 'tel',
						'width' => '200',
						'align' => '',
				),
				array(
						'type' => 'text',
						'show' => 1,
						'title' => '固定电话',
						'field' => 'home_tel',
						'width' => '200',
						'align' => '',
				),
				
				array(
						'type' => 'text',
						'show' => 1,
						'title' => '收货地址',
						'field' => 'address1',
						'width' => '200',
						'align' => '',
				),
                )
            ),
        'dataset' => 'crm/CustomerModel::get_by_page_address',
        //'queryBy' => 'searchForm',
        'idField' => 'customer_address_id',
        'params' => array('filter' => array('customer_code' => $request['customer_code'])),
        //'CheckSelection'=>true, // 显示复选框
        
        ));

?>

<?php echo_selectwindow_js($request, 'table', array('customer_code'=>'customer_code','customer_address_id'=>'customer_address_id', 'name'=>'name','tel'=>'tel','home_tel'=>'home_tel','address'=>'address','country'=>'country','province'=>'province','city'=>'city','district'=>'district','street'=>'street' )) ?>


