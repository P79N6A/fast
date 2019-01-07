<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '查看主机明细',
	'links'=>array(
		array('url'=>'products/vhost_manage/do_list','title'=>'主机信息')
	)
));?>

<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'所属主机IP', 'type'=>'select_pop', 'field'=>'vem_vm_id', 'select'=>'products/vhostinfo','show_scene'=>'add,edit'),
                        array('title'=>'所属主机IP', 'type'=>'input', 'field'=>'vem_vm_id_name','show_scene'=>'view','edit_scene'=>''),
//                        array('title'=>'平台类型',  'type'=>'select', 'field'=>'sd_pt_id','data'=>ds_get_select('shop_platform',2)),
                        array('title'=>'关联产品',  'type'=>'select', 'field'=>'vem_cp_id','data'=>ds_get_select('chanpin',2)),
//                        array('title' => '店铺类型', 'type' => 'select', 'field' => 'sd_pt_shoptype', 'show_scene' => 'view', 'data' => ds_get_select('platformshop_type', 2)),
                        array('title'=>'产品版本',  'type'=>'select', 'field'=>'vem_product_version','data' => ds_get_select_by_field('product_version', 2)),
                        array('title'=>'系统版本', 'type'=>'select', 'field'=>'vem_cp_version', 'data' => ds_get_select('issue_chanpin_version', 2)),
                        array('title'=>'服务IP', 'type'=>'input', 'field'=>'vem_cp_version_ip', ),
                        array('title'=>'服务目录', 'type'=>'input', 'field'=>'vem_cp_path', ),
                        array('title'=>'网站访问路径','type'=>'input','field'=>'vem_cp_web_path'),
                        array('title'=>'主机状态', 'type'=>'checkbox', 'field'=>'vem_status', 'edit_scene'=>'add',),
                        array('title'=>'创建日期','type'=>'input', 'field'=>'vem_createdate','edit_scene'=>'','show_scene'=>'view' ),
                        array('title'=>'修改日期', 'type'=>'input', 'field'=>'lastchanged','edit_scene'=>'','show_scene'=>'view' ),
                    ),      
		'hidden_fields'=>array(array('field'=>'vem_id')), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
        'col'=>2,
	'act_edit'=>'products/vhost_manage/do_vhost_edit', //edit,add,view
	'act_add'=>'products/vhost_manage/do_vhost_add',
	'data'=>$response['data'],
        'rules'=>'products/add_vhost',        //对应方法在conf/validator/clients_conf.php,新建店铺必填字段验证。
)); ?>


