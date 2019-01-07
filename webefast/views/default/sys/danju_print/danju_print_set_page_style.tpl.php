<?php

render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'纸张类型', 'type'=>'select', 'field'=>'template_page_style','data' => $response['page_style']),
			array('title'=>'纸张宽度', 'type'=>'input', 'field'=>'template_page_width'),
			array('title'=>'纸张高度', 'type'=>'input', 'field'=>'template_page_height'),
		),
		'hidden_fields'=>array(array('field'=>'print_id')),
	),
	'buttons'=>array(
		array('label'=>'提交', 'type'=>'submit'),
		//array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'sys/danju_print/do_set_page_style', //edit,add,view

	'data'=>$response['data'],

	'rules'=>array(
//		array('order_time', 'require'),
//		array('reason','require'),
	),
)); ?>

<script type="text/javascript">
	$(function () {
		$("#template_page_style").bind('change',setDefaultStyle);
	});

    function setDefaultStyle() {

        if ($('#template_page_style').val() != 'custom_pager') {

	        var page_style = $('#template_page_style').val();

            $.post('?app_act=sys/danju_print/get_page_style&app_page=null&app_fmt=json&page_style=' + page_style, function (data) {
                var ret = $.parseJSON(data);
                $("#template_page_width").val(ret.width);
                $("#template_page_height").val(ret.height);
                $("#template_page_width").attr("disabled", true);
                $("#template_page_height").attr("disabled", true);
            });
        } else {
            $("#template_page_width").attr("disabled", false);
            $("#template_page_height").attr("disabled", false);
        }
    }
</script>