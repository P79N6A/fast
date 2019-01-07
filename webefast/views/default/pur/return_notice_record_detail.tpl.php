<?php 

render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title' => '单据编号', 'type' => 'input', 'field' => 'record_code',),
            array('title' => '原单号', 'type' => 'input', 'field' => 'init_code'),
            array('title' => '业务日期', 'type' => 'date', 'field' => 'record_time', 'value'=>date('Y-m-d')),
				array('title' => '退货类型', 'type' => 'select', 'field' => 'record_type_code', 'data' => ds_get_select('record_type', 2, array('record_type_property' => 1))),
            array('title' => '供应商', 'type' => 'select', 'field' => 'supplier_code', 'data' => $response['supplier'], 'remark' => "<a href='#' id = 'base_supplier'><img src='assets/img/search.png'></a>"),
            array('title' => '仓库', 'type' => 'select', 'field' => 'store_code', 'data' => $response['store']),
            array('title' => '折扣', 'type' => 'input', 'field' => 'rebate', 'value' =>'1.00'),
			array('title'=>'备注', 'type'=>'textarea', 'field'=>'remark'),
		), 
		'hidden_fields'=>array(array('field'=>'notice_record_id')), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'pur/return_notice_record/do_edit', //edit,add,view
	'act_add'=>'pur/return_notice_record/do_add',
	'data'=>$response['data'],
	'callback'=>'after_submit',
	'rules'=>array(
		array('record_time', 'require'),
		array('distributor_code', 'require'),
		array('rebate', 'require'),
		array('store_code', 'require'),
		array('supplier_code', 'require'),
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
    function after_submit(result,ES_frmId){ 
         var url = '?app_act=pur/return_notice_record/view&return_notice_record_id=' +result.data
         openPage(window.btoa(url),url,'采购退货通知单');
            ui_closePopWindow(ES_frmId);
         
    }
    parent.add_c = function(custom_code){
        $('#supplier_code').find('option[value="'+custom_code+'"]').attr('selected',true);
        //parent.$('#distributor_code').click();        
    }
    jQuery(function(){
        $('#base_supplier').click(function(){
            new ESUI.PopWindow("?app_act=pur/order_record/detail_supplier", {
                title: "选择供应商",
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




