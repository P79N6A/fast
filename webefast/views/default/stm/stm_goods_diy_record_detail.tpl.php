<?php 

render_control('Form', 'form1', array(
	'conf' => array(
        'fields' => array(
            array('title' => '单据编号', 'type' => 'input', 'field' => 'record_code',),
            array('title' => '业务日期', 'type' => 'date', 'field' => 'record_time', 'value' => date('Y-m-d')),
            array('title' => '仓库', 'type' => 'select', 'field' => 'store_code', 'data' => load_model('base/StoreModel')->get_select(2)),
            array('title' => '类型', 'type' => 'select', 'field' => 'record_type', 'data' => $response['record_type']),
            array('title' => '备注', 'type' => 'textarea', 'field' => 'remark'),
        ),
        'hidden_fields' => array(array('field' => 'notice_record_id')),
    ), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'stm/stm_goods_diy_record/do_edit', //edit,add,view
	'act_add'=>'stm/stm_goods_diy_record/do_add',
	'data'=>$response['data'],
	
	'rules'=>array(
		array('record_time', 'require'),
		array('store_code', 'require'),
        array('record_type', 'require'),
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
</script>





