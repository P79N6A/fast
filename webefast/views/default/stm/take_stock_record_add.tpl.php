<?php
render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title' => '单据编号', 'type' => 'input', 'field' => 'record_code',"readonly"=>true),
            array('title' => '原单号', 'type' => 'input', 'field' => 'init_code'),
            array('title' => '盘点日期', 'type' => 'date', 'field' => 'take_stock_time', 'value'=>date('Y-m-d')),
            array('title' => '盘点仓库', 'type' => 'select', 'field' => 'store_code', 'data' => load_model('base/StoreModel')->get_select()),
            //array('title' => '业务员', 'type' => 'select', 'field' => 'user_code','data'=>get_user(1)),
			array('title'=>'备注', 'type'=>'textarea', 'field'=>'remark'),
		),
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_add'=>'stm/take_stock_record/do_add',
	'data'=>$response['data'],
	'callback'=>'after_submit',
	'rules'=>array(
		array('record_code', 'require'),
		array('store_code', 'require'),
	),
)); ?>
<script type="text/javascript">
    $("#record_code").attr("disabled", "disabled");
    	form.on('beforesubmit', function () {
        $("#record_code").attr("disabled", false);
    });
    
    function after_submit(result,ES_frmId){         
        var url = '?app_act=stm/take_stock_record/view&_id=' +result.data
        openPage(window.btoa(url),url,'盘点单详情');
            ui_closePopWindow(ES_frmId); 
    }
</script>