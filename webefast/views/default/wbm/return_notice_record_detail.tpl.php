<?php 

render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title' => '单据编号', 'type' => 'input', 'field' => 'return_notice_code'),
            array('title' => '原单号', 'type' => 'input', 'field' => 'init_code'),
            array('title' => '分销商', 'type' => 'select', 'field' => 'custom_code', 'data' => $response['custom'],'remark' => "<a href='#' id = 'wbm_return_custom'><img src=assets/img/search.png></a>"),
            array('title' => '仓库', 'type' => 'select', 'field' => 'store_code', 'data' => $response['store']),
            array('title' => '退货类型', 'type' => 'select', 'field' => 'return_type_code', 'data' => ds_get_select('record_type', 2, array('record_type_property' => 3))),
            array('title' => '折扣', 'type' => 'input', 'field' => 'rebate','value'=>'1.0'),
            array('title' => '备注', 'type' => 'textarea', 'field' => 'remark'),
		), 
		//'hidden_fields'=>array(array('field'=>'planned_record_id')), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'wbm/return_notice_record/do_edit', //edit,add,view
	'act_add'=>'wbm/return_notice_record/do_add',
	'data'=>$response['data'],
	'callback'=>'after_submit',
	'rules'=>array(
		array('return_notice_code', 'require'),
		//array('init_code', 'require'),
		array('fenxiao_code', 'require'),
        array('custom_code', 'require'),
		array('store_code', 'require'),
		array('rebate', 'require'),
		array('rebate', 'regexp','value'=>'/^0\.[0-9]*[1-9]|1.0$/'),
		
	),
)); ?>

<script type="text/javascript">
    $("#return_notice_code").attr("disabled", "disabled");

    form.on('beforesubmit', function () {
        //@"^0\.[0-9]*[1-9]$"
        $("#return_notice_code").attr("disabled", false);
    });

    function after_submit(result,ES_frmId){ 
         var url = '?app_act=wbm/return_notice_record/view&return_notice_record_id=' +result.data
         openPage(window.btoa(url),url,'批发退货通知单');
            ui_closePopWindow(ES_frmId);
         
    }
    parent.add_c = function(custom_code){
        $('#custom_code').find('option[value="'+custom_code+'"]').attr('selected',true);
        //parent.$('#distributor_code').click();     
    }
    $(function(){
        $('#wbm_return_custom').click(function(){
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



