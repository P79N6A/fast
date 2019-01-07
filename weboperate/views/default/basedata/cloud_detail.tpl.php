<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '供应商详情',
	'links'=>array(
            array('url'=>'basedata/cloud/do_list','title'=>'供应商列表'),
            )
));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			    array('title'=>'服务商名称', 'type'=>'input', 'field'=>'cd_name', ),
                            array('title'=>'服务商官网', 'type'=>'input', 'field'=>'cd_official', ),
                            array('title'=>'服务商描述', 'type'=>'input', 'field'=>'cd_note', ),
		),      
		'hidden_fields'=>array(array('field'=>'cd_id')), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
        'col'=>3,
	'act_edit'=>'basedata/cloud/do_edit', //edit,add,view
	'act_add'=>'basedata/cloud/do_add',
	'data'=>$response['data'],
        'rules'=>'basedata/add_cloud',        //有效性验证
        'callback'=>'submitCall'
)); ?>
<script type="text/javascript">
    function submitCall(data,esfrmId){
        var scene="<?php echo $app['scene'] ?>";
        var type = data.status == 1 ? 'success' : 'error';
        BUI.Message.Alert(data.message, function() {
            if (data.status == 1) {
                var cdid="";
                if(scene=="add")
                    cdid=data.data;
                else
                    cdid=$("#cd_id").val();
                window.location='?app_act=basedata/cloud/detail&app_scene=edit&_id='+cdid;
            }
        }, type);
    }
</script>