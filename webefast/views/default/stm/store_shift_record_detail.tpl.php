<?php 
render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title' => '单据编号', 'type' => 'input', 'field' => 'record_code',),
            array('title' => '原单号', 'type' => 'input', 'field' => 'init_code'),
            array('title' => '移出日期', 'type' => 'date', 'field' => 'is_shift_out_time', 'value'=>date('Y-m-d')),
            array('title' => '移入日期 ', 'type' => 'date', 'field' => 'is_shift_in_time', 'value'=>date('Y-m-d')),
            array('title' => '移出仓库', 'type' => 'select', 'field' => 'shift_out_store_code', 'data' => load_model('base/StoreModel')->get_purview_store()),
            array('title' => '移入仓库', 'type' => 'select', 'field' => 'shift_in_store_code', 'data' => load_model('base/StoreModel')->get_purview_store()),
            array('title' => '折扣', 'type' => 'input', 'field' => 'rebate', 'value' =>'1.00'),
            array('title'=>'备注', 'type'=>'textarea', 'field'=>'remark'),
		), 
		'hidden_fields'=>array(array('field'=>'shift_record_id')), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'stm/store_shift_record/do_edit', //edit,add,view
	'act_add'=>'stm/store_shift_record/do_add',
	'data'=>$response['data'],
	'callback'=>'after_submit',
	'rules'=>array(
		array('is_shift_out_time', 'require'),
		array('is_shift_in_time', 'require'),
		array('shift_out_store_code', 'require'),
		array('shift_in_store_code', 'require'),
		array('rebate', 'require'),
		
	),
)); ?>

<script type="text/javascript">
    $("#record_code").attr("disabled", "disabled");

    form.on('beforesubmit', function () {
        var shift_out_store_code_val = $("#shift_out_store_code").val();
        var shift_in_store_code_val  = $("#shift_in_store_code").val();
        if(shift_out_store_code_val != '' && shift_in_store_code_val != '' && shift_out_store_code_val != shift_in_store_code_val){
			
        }else{
            BUI.Message.Alert("移出仓库与移入仓库不能相同", 'warning');
            return false;
        }
        $("#record_code").attr("disabled", false);
    });
</script>

<script type="text/javascript">
    function after_submit(result,ES_frmId){     
        var type = (result.status == 1) ? 'success' : 'error';
        if (type != 'success') {
                   BUI.Message.Alert(result.message, type);
               }else{
        var url = '?app_act=stm/store_shift_record/view&shift_record_id=' +result.data
        openPage(window.btoa(url),url,'商品移仓单');
            ui_closePopWindow(ES_frmId); 
          }
    }
</script>