<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '查看产品',
	'links'=>array(
            array('url'=>'products/productinfo/do_list','title'=>'产品列表'),
            )
));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'产品代码', 'type'=>'input', 'field'=>'cp_code', 'edit_scene'=>'add'),
			array('title'=>'产品名称', 'type'=>'input', 'field'=>'cp_name', ),
                        array('title'=>'英文名称', 'type'=>'input', 'field'=>'cp_en_name', ),
                        array('title'=>'产品简称', 'type'=>'input', 'field'=>'cp_jc', ),
                        array('title'=>'在线订购', 'type'=>'checkbox', 'field'=>'cp_order', ),
                        array('title'=>'产品描述', 'type'=>'textarea', 'field'=>'cp_memo', ),
                        array('title'=>'创建人', 'type'=>'input', 'field'=>'cp_createuser_name','edit_scene'=>'','show_scene'=>'view,edit' ),
                        array('title'=>'创建时间', 'type'=>'input','field'=>'cp_createdate','edit_scene'=>'','show_scene'=>'view,edit'),
                        array('title'=>'修改人', 'type'=>'input', 'field'=>'cp_updateuser_name', 'edit_scene'=>'','show_scene'=>'view,edit'),
                        array('title'=>'修改时间', 'type'=>'input','field'=>'cp_updatedate','edit_scene'=>'','show_scene'=>'view,edit' ),
		),      
		'hidden_fields'=>array(array('field'=>'cp_id'), array('field'=>'cp_code'),), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
        'col'=>3,
	'act_edit'=>'products/productinfo/product_edit', //edit,add,view
	'act_add'=>'products/productinfo/product_add',
	'data'=>$response['data'],
        'rules'=>'products/products_edit',        //有效性验证
        'callback'=>'submitCall'
)); ?>
<script type="text/javascript">
    function submitCall(data,esfrmId){
        var scene="<?php echo $app['scene'] ?>";
        var type = data.status == 1 ? 'success' : 'error';
        BUI.Message.Alert(data.message, function() {
            if (data.status == 1) {
                var cpid="";
                if(scene=="add")
                    cpid=data.data;
                else
                    cpid=$("#cp_id").val();
                window.location='?app_act=products/productinfo/detail&app_scene=edit&_id='+cpid;
            }
        }, type);
    }
</script>