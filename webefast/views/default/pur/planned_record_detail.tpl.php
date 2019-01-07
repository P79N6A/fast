<?php 
 
render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title' => '单据编号', 'type' => 'input', 'field' => 'record_code',),
                    array('title' => '下单时间', 'type' => 'time', 'field' => 'record_time', 'value' => date('Y-m-d H:i:s')),
            array('title' => '原单号', 'type' => 'input', 'field' => 'init_code'),
            array('title' => '计划日期', 'type' => 'date', 'field' => 'planned_time', 'value'=>date('Y-m-d')),
            array('title' => '入库期限', 'type' => 'date', 'field' => 'in_time', 'value'=>date('Y-m-d',strtotime("+1 month"))),
            array('title' => '采购类型', 'type' => 'select', 'field' => 'pur_type_code', 'data' => $response['pur_type']),
            array('title' => '仓库', 'type' => 'select', 'field' => 'store_code', 'data' => $response['store']),
            array('title' => '供应商', 'type' => 'select', 'field' => 'supplier_code', 'data' => $response['supplier'], 'remark' => "<a href='#' id = 'base_supplier'><img src='assets/img/search.png'></a>"),
            array('title' => '折扣', 'type' => 'input', 'field' => 'rebate','value'=>'1.0'),
            array('title' => '描述', 'type' => 'textarea', 'field' => 'remark'),
		), 
		'hidden_fields'=>array(array('field'=>'planned_record_id')), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'pur/planned_record/do_edit', //edit,add,view
	'act_add'=>'pur/planned_record/do_add',
	'data'=>$response['data'],
	'callback'=>'after_submit',
	'rules'=>array(
		array('planned_time', 'require'),
		array('in_time', 'require'),
		array('store_code', 'require'),
		array('supplier_code', 'require'),
                array('rebate', 'require'),
	),
)); ?>


<input id="detail_data" name="detail_data"  type="hidden" value="" />

<script type="text/javascript">
    $("#record_code").attr("disabled", "disabled");
    $(function(){
      var  detail_data='<?php if(isset($request['detail_data'])) { echo $request['detail_data'];}?>';
      $('#detail_data').val(detail_data);
      $('#form1').append($('#detail_data'));
    });


    form.on('beforesubmit', function () {
        $("#record_code").attr("disabled", false);
    });

    function after_submit(result,ES_frmId){ 
        var pfm = $('#detail_data').val();
        if(pfm==''){
         var url = '?app_act=pur/planned_record/view&planned_record_id=' +result.data
             openPage(window.btoa(url),url,'采购订单');
             ui_closePopWindow(ES_frmId);
        }else{   
            if(result.status<0){
                BUI.Message.Alert(result.message,'error');
            }else{
                 BUI.Message.Alert(result.message,function(){
                     ui_closePopWindow(ES_frmId);
                 },'info');
            }
        }
    
        
         
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
    parent.add_c = function(custom_code){
        $('#supplier_code').find('option[value="'+custom_code+'"]').attr('selected',true);
        $('#supplier_code').parent().find('.valid-text').html('');
        //parent.$('#distributor_code').click();        
    }
</script>


