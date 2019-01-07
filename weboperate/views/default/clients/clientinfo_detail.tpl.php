<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '查看客户',
	'links'=>array(
		array('url'=>'clients/clientinfo/do_list','title'=>'客户列表')
	)
));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			/*array('title'=>'编号', 'type'=>'input', 'field'=>'kh_id', 'show_scene'=>'view'),*/
			array('title'=>'客户名称', 'type'=>'input', 'field'=>'kh_name', ),
                        array('title'=>'登录用户名', 'type'=>'input', 'field'=>'kh_code', ),
                        array('title'=>'销售渠道', 'type'=>'select_pop', 'field'=>'kh_place','select'=>'basedata/sellchannel','selecttype'=>'tree','show_scene'=>'add,edit'),
                        array('title'=>'销售渠道', 'type'=>'input', 'field'=>'kh_place_name','show_scene'=>'view'),
                        array('title'=>'星联卡号', 'type'=>'input', 'field'=>'star_code', ),
                       array('title'=>'星联密码', 'type'=>'input', 'field'=>'star_password', ),
                        array('title'=>'客户地址', 'type'=>'input', 'field'=>'kh_address', ),
                        array('title'=>'客户电话', 'type'=>'input', 'field'=>'kh_tel', ),
                        array('title'=>'客户邮箱', 'type'=>'input', 'field'=>'kh_email', ),
                        array('title'=>'客户网站', 'type'=>'input', 'field'=>'kh_web', ),
                        array('title'=>'客户IT姓名', 'type'=>'input', 'field'=>'kh_itname', ),
                        array('title'=>'客户IT电话', 'type'=>'input', 'field'=>'kh_itphone', ),
                        array('title'=>'服务工程师', 'type'=>'select_pop', 'field'=>'kh_fwuser', 'select'=>'sys/orguser','show_scene'=>'add,edit'),
                        array('title'=>'服务工程师', 'type'=>'input', 'field'=>'kh_fwuser_name','show_scene'=>'view'),
                        array('title'=>'客户经理', 'type'=>'select_pop', 'field'=>'kh_xsuser', 'select'=>'sys/orguser','show_scene'=>'add,edit'),
                        array('title'=>'服务工程师邮箱', 'type'=>'input', 'field'=>'kh_fwuser_email', ),
                        array('title'=>'客户经理', 'type'=>'input', 'field'=>'kh_xsuser_name','show_scene'=>'view'),
                        array('title'=>'账号类型', 'type' => 'select', 'field' => 'kh_account_type','show_scene'=>'view','data' => ds_get_select_by_field('kh_account_type', 3)),
                        array('title'=>'证件号', 'type' => 'input', 'field' => 'kh_licence_num','show_scene'=>'view',),
                        array('title'=>'扫描件', 'type' => 'html', 'field' => 'kh_licence_img','show_scene'=>'view','html'=>'<img src="'.$response['data']['kh_licence_img'].'" width="100px" height="100px" />'),
//			array('title'=>'是否云客户', 'type'=>'input', 'field'=>'kh_is_ykh', ),
                        array('title'=>'创建人', 'type'=>'input', 'field'=>'kh_createuser_name','show_scene'=>'view'),
                        array('title'=>'创建时间', 'type'=>'input', 'field'=>'kh_createdate','show_scene'=>'view' ),
                        array('title'=>'修改人', 'type'=>'input', 'field'=>'kh_updateuser_name','show_scene'=>'view'),
                        array('title'=>'修改时间', 'type'=>'input', 'field'=>'kh_updatedate','show_scene'=>'view'),
                        array('title'=>'备注', 'type'=>'textarea', 'field'=>'kh_memo', ),
//                          array('title'=>'所属产品', 'type'=>'select', 'field'=>'cp_id','data'=>ds_get_select('chanpin',2)),
		),      
		'hidden_fields'=>array(array('field'=>'kh_id')), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
        'col'=>2,
	'act_edit'=>'clients/clientinfo/client_edit', //edit,add,view
	'act_add'=>'clients/clientinfo/client_add',
	'data'=>$response['data'],
        'rules'=>'clients/add_clients',        //对应方法在conf/validator/clients_conf.php,新建客户必填字段验证。
)); ?>