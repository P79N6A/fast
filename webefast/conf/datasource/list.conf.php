<?php
return array(
			'kehu'	=> array('title'=>'客户',	'dataset'=>'kehu/Kehu_model::get_by_page'),
			'kehu_jxs'	=> array('title'=>'经销商',	'dataset'=>'kehu/Kehu_model::get_by_page', 'params'=>array('filter'=>array('khlb'=>2)),  
									'conf'=>'common/select_multi_kehu'),
			'kehu_dls'	=> array('title'=>'代理商',	'dataset'=>'kehu/Kehu_model::get_by_page', 'params'=>array('filter'=>array('khlb'=>1)),
									'conf'=>'common/select_multi_kehu'),
			'shop'	=> array('title'=>'店铺', 	'dataset'=>'shop/Shop_model::get_by_page'),
			'staff'	=> array('title'=>'员工', 	'dataset'=>'sys/yg_model::get_by_page'),
			'role'	=> array('title'=>'角色', 	'dataset'=>'sys/acl_role_model::get_by_page'),
			'department'	
					=> array('title'=>'部门', 	'dataset'=>'sys/department_model::get_by_page2'),
			'contacter'
					=> array('title'=>'联系人', 	'dataset'=>'base/Contacter_model::get_by_page'),
			'area'
					=> array('title'=>'大区', 	'dataset'=>'base/Base_dict_model::get_data_by_page', 'params'=>array('table'=>'area'),  'type'=>'base_dict'),
			'province'
					=> array('title'=>'大区', 	'dataset'=>'base/Base_dict_model::get_data_by_page', 'params'=>array('table'=>'province'),  'type'=>'base_dict'),
			'dpsx'
					=> array('title'=>'店铺属性', 'dataset'=>'base/Base_dict_model::get_data_by_page', 'params'=>array('table'=>'dpsx'),  'type'=>'base_dict'),
			'dpxz'
					=> array('title'=>'店铺性质', 'dataset'=>'base/Base_dict_model::get_data_by_page', 'params'=>array('table'=>'dpxz'),  'type'=>'base_dict'),
			'mdlx'
					=> array('title'=>'门店类型', 'dataset'=>'base/Base_dict_model::get_data_by_page', 'params'=>array('table'=>'mdlx'),  'type'=>'base_dict'),
			'brand'
				=> array('title'=>'品牌', 'dataset'=>'base/Base_dict_model::get_data_by_page', 'params'=>array('table'=>'brand'),  'type'=>'base_dict'),
			'pinbiao'
				=> array('title'=>'品标', 'dataset'=>'base/Base_dict_model::get_data_by_page', 'params'=>array('table'=>'pinbiao'),  'type'=>'base_dict'),
			'shop_form'
				=> array('title'=>'品标', 'dataset'=>'base/Base_dict_model::get_data_by_page', 'params'=>array('table'=>'shop_form'),  'type'=>'base_dict'),
		);