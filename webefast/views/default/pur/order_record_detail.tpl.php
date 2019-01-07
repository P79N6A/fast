<?php 

render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title' => '单据编号', 'type' => 'input', 'field' => 'record_code',),
            array('title' => '原单号', 'type' => 'input', 'field' => 'init_code'),
           // array('title' => '下单日期', 'type' => 'date', 'field' => 'order_time', 'value'=>date('Y-m-d')),
            array('title' => '入库期限', 'type' => 'date', 'field' => 'in_time', 'value'=>date('Y-m-d',strtotime("+1 month"))),
            array('title' => '采购类型', 'type' => 'select', 'field' => 'pur_type_code', 'data' => $response['pur_type']),
            array('title' => '供应商', 'type' => 'select', 'field' => 'supplier_code', 'data' => $response['supplier'], 'remark' => "<a href='#' id = 'base_supplier'><img src='assets/img/search.png'></a>"),
            array('title' => '仓库', 'type' => 'select', 'field' => 'store_code', 'data' => $response['store']),
            array('title' => '折扣', 'type' => 'input', 'field' => 'rebate','value'=>'1.0'),
          //  array('title' => '预售', 'type' => 'checkbox', 'field' => 'pre_sale'),
            array('title' => '备注', 'type' => 'textarea', 'field' => 'remark'),
		), 
		'hidden_fields'=>array(array('field'=>'planned_record_id')), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'pur/order_record/do_edit', //edit,add,view
	'act_add'=>'pur/order_record/do_add',
	'data'=>$response['data'],
	'callback'=>'after_submit',
	'rules'=>array(
		array('order_time', 'require'),
		array('in_time', 'require'),
		array('supplier_code', 'require'),
		array('store_code', 'require'),
		array('rebate', 'require'),
		array('rebate', 'regexp','value'=>'/^0\.[0-9]*[1-9]|1.0$/'),
		
	),
)); ?>

<script type="text/javascript">
    $("#record_code").attr("disabled", "disabled");

    form.on('beforesubmit', function () {
        //@"^0\.[0-9]*[1-9]$"
        $("#record_code").attr("disabled", false);
    });

    function after_submit(result,ES_frmId){ 
         var url = '?app_act=pur/order_record/view&order_record_id=' +result.data
         openPage(window.btoa(url),url,'通知单');
            ui_closePopWindow(ES_frmId);
         
    }
    parent.add_c = function(custom_code){
        $('#supplier_code').find('option[value="'+custom_code+'"]').attr('selected',true);
        $('#supplier_code').parent().find('.valid-text').html('');
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



