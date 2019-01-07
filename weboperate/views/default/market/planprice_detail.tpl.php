<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '报价模板详情',
	'links'=>array(
            array('url'=>'market/planprice/do_list','title'=>'报价模板列表'),
            )
));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
                                array('title'=>'模板名称', 'type'=>'input', 'field'=>'price_name'),
                                array('title'=>'产品', 'type'=>'select', 'field'=>'price_cpid','data'=>ds_get_select('chanpin',2)),
                                array('title'=>'产品版本', 'type'=>'select', 'field'=>'price_pversion','data' => ds_get_select_by_field('product_version', 2)),  
                                array('title'=>'基础报价', 'type'=>'input', 'field'=>'price_base'),
                                array('title'=>'默认点数', 'type'=>'input', 'field'=>'price_dot'),
                                array('title'=>'营销类型', 'type'=>'select', 'field'=>'price_stid','data'=>ds_get_select('market',2),'value'=>'2'),
                                /*array('title'=>'满', 'type'=>'input', 'field'=>'price_fulldate','remark'=>'月'),
                                array('title'=>'优惠', 'type'=>'input', 'field'=>'price_disdate','remark'=>'月'),*/
                                array('title'=>'默认期限', 'type'=>'input', 'field'=>'price_default_limit','remark'=>'月'),
                                array('title'=>'描述','type'=>'textarea', 'field'=>'price_note'),
		),      
		'hidden_fields'=>array(array('field'=>'price_id')), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
        'col'=>2,
	'act_edit'=>'market/planprice/do_edit', //edit,add,view
	'act_add'=>'market/planprice/do_add',
	'data'=>$response['data'],
        'rules'=>'market/planprice_add' ,        //有效性验证
        'callback'=>'submitCall',
        'event'=>array('beforesubmit'=>'formBeforesubmit'),
)); ?>
<script type="text/javascript">
    
    function formBeforesubmit() {
        if($("#price_stid").val()=='2'){  //表示租用型，必须设置默认期限
            if($("#price_default_limit").val()==''){
                BUI.Message.Alert("租用型，默认期限不能为空","error");
                return false;
            }
        }
	return true; // 如果不想让表单继续提交，则return false
    }
    
    function submitCall(data,esfrmId){
        var scene="<?php echo $app['scene'] ?>";
        var type = data.status == 1 ? 'success' : 'error';
        BUI.Message.Alert(data.message, function() {
            if (data.status == 1) {
                var priceid="";
                if(scene=="add")
                    priceid=data.data;
                else
                    priceid=$("#price_id").val();
                window.location='?app_act=market/planprice/detail&app_scene=edit&_id='+priceid;
            }
        }, type);
    }
</script>