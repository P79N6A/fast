<?php 

render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title' => '单据编号', 'type' => 'input', 'field' => 'record_code',),
            array('title' => '原单号', 'type' => 'input', 'field' => 'init_code'),
            array('title' => '业务日期', 'type' => 'date', 'field' => 'record_time', 'value'=>date('Y-m-d')),
            array('title' => '批发类型', 'type' => 'select', 'field' => 'record_type_code', 'data' => ds_get_select('record_type',2,array('record_type_property'=>2))),
            array('title' => '分销商', 'type' => 'select', 'field' => 'distributor_code', 'data' => $response['fenxiao'],'remark' => "<a href='#' id = 'wbmselectcustom'><img src='assets/img/search.png'></a>"),
            array('title' => '仓库', 'type' => 'select', 'field' => 'store_code', 'data' => load_model('base/StoreModel')->get_select(2)),
            array('title' => '折扣', 'type' => 'input', 'field' => 'rebate', 'value' =>'1.00'),
			array('title'=>'备注', 'type'=>'textarea', 'field'=>'remark'),
		), 
		'hidden_fields'=>array(array('field'=>'notice_record_id')), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'wbm/notice_record/do_edit', //edit,add,view
	'act_add'=>'wbm/notice_record/do_add',
	'data'=>$response['data'],
	
	'rules'=>array(
		array('record_time', 'require'),
		array('distributor_code', 'require'),
		array('rebate', 'require'),
		array('store_code', 'require'),
	),
)); ?>

<script type="text/javascript">
    $("#record_code").attr("disabled", "disabled");

    form.on('beforesubmit', function () {
        $("#record_code").attr("disabled", false);
    });
    $("#rebate").keyup(function(){
    	this.value=this.value.replace(/[^0-9.]/g,'');
		if(this.value > 1 || this.value < 0){
			alert('折扣只能输入【0-1】之间的数值');this.value='1.0'
		}
	});
    parent.add_c = function(custom_code){
        $('#distributor_code').find('option[value="'+custom_code+'"]').attr('selected',true);
        $('#distributor_code').parent().find('.valid-text').html('');
        //parent.$('#distributor_code').click();        
    }

    jQuery(function(){
        $('#wbmselectcustom').click(function(){
            new ESUI.PopWindow("?app_act=wbm/notice_record/custom", {
                title: "选择分销商",
                width: 960,
                height: 500,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show();
        })
    })
    
</script>



