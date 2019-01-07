<style>
input[type="checkbox"], input[type="radio"] {margin-top: 6px;}
.form-horizontal .control-label { width:150px;}
.row{ margin-bottom:10px;}
.nav-tabs{ padding-top:10px; margin-bottom:10px;}
form.form-horizontal{ padding:20px; border:1px solid #ded6d9;}
input.input-normal{ width:auto;}
select.input-normal {width: 155px;}
</style>
<ul class="nav-tabs oms_tabs">
    <li class="active"><a href="#"  >基本信息</a></li>
    <?php if($response['app_scene'] == 'edit'){ ?>
    <li ><a href="#" onClick="do_page('view',0);" >赠送规则</a></li>
   <li ><a href="#" onClick="do_page('view','1');" >会员定向</a></li>
   <?php }?>
</ul>
<?php 
render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title' => '策略代码', 'type' => 'input', 'field' => 'strategy_code',),
            array('title' => '策略名称', 'type' => 'input', 'field' => 'strategy_name'),
            array('title' => '活动店铺', 'type' => 'select', 'field' => 'shop_code', 'data' => ds_get_select('shop',2)),
            array('title' => '活动开始时间', 'type' => 'time', 'field' => 'start_time', ),
            array('title' => '活动结束时间', 'type' => 'time', 'field' => 'end_time', ),
			array('title'=>'一个会员仅送一次', 'type'=>'checkbox', 'field'=>'is_once_only'),
			//array('title'=>'赠品库存不足自动停止', 'type'=>'checkbox', 'field'=>'is_stop_no_inv'),
		), 
		'hidden_fields'=>array(array('field'=>'op_gift_strategy_id')), 
	), 
	'buttons'=>array(
			//array('label'=>'提交', 'type'=>'submit'),
			//array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'pur/purchase_record/do_edit', //edit,add,view
	'act_add'=>'pur/purchase_record/do_add',
	'data'=>$response['data'],
	
	'rules'=>array(
	    array('shop_code', 'require'),
		array('strategy_name', 'require'),
		array('start_time', 'require'),
		array('end_time', 'require'),
	),
)); ?>
<div > <font color="red">说明：赠品策略，是以付款时间计算的，只有在活动期间内成功付款的订单(包含货到付款)，系统才会自动送赠品<br>
	若赠品库存为0，停止赠送赠品
</font></div>
<div>
    <div id="TabPage1Submit" class="row form-actions actions-bar">
            <div class="span13 offset3 ">
           
                <button type="submit" class="button button-primary" id="submit">下一步</button>
                <a class="button button-primary"  onclick="javascript:location.href = '?app_act=op/op_gift_strategy/do_list';"  >返回</a>
                
            </div>
        </div>
</div>
<script type="text/javascript">
    $("#strategy_code").attr("disabled", "disabled");
    if($("#app_scene").val() == 'edit'){
    	$("#shop_code").attr("disabled", "disabled");;
	}

    form.on('beforesubmit', function () {
        $("#strategy_code").attr("disabled", false);
        
    });
   
</script>
<script type="text/javascript">
var show = '<?php echo $request['show'];?>';
if(show == '1'){
	$("#submit").attr("disabled", true);
	$("#strategy_name").attr("disabled", true);
	$("#start_time").attr("disabled", true);
	$("#end_time").attr("disabled", true);
}
$(function(){
	
	$(".control-label").css("width","182px;");
	$("#TabPage1Submit").find("#submit").click(function(){
		
		var url = '';
		if($("#strategy_name").val() == '' || $("#shop_code").val() == '' ||  $("#start_time").val() == '' ||  $("#end_time").val() == ''  ){
			alert('策略名称 店铺 活动时间都不能为空');
			return false;
		}
		if($("#app_scene").val() == 'edit'){
			url	= '<?php echo get_app_url('op/op_gift_strategy/do_edit');?>';
		}else{
			url	= '<?php echo get_app_url('op/op_gift_strategy/do_add');?>';
		}
		//alert(url);
		 $("#strategy_code").attr("disabled", false);
		 $("#shop_code").attr("disabled", false);
		var data = $('#form1').serialize();
    	$.post(url, data, function(data){
            var type = data.status == 1 ? 'success' : 'error';
    		if (data.status == 1) {
    			
   			  // BUI.Message.Alert('修改成功：', type);
    			strategy_code = $("#strategy_code").val();
    			ids = data.data;
    			if(show == '1'){
    				url = "?app_act=op/op_gift_strategy/view&_id="+ids+"&show=1&strategy_code=" + strategy_code+"&ES_frmId=<?php echo $request['ES_frmId'];?>";
        		}else{
    				url = "?app_act=op/op_gift_strategy/view&_id="+ids+"&strategy_code=" + strategy_code+"&ES_frmId=<?php echo $request['ES_frmId'];?>";
        		}
    			//alert(url);
    			location.href  = url;
    		} else {
    			BUI.Message.Alert(data.message, function() { }, type);
    		}
        }, "json");
		
	});
		
});

function do_page(param,type) {
	
	var strategy_code = $("#strategy_code").val();
	var id = '<?php echo $response['_id'];?>';
	if(strategy_code != '' && id != ''){
		if(show == '1'){
			url = "?app_act=op/op_gift_strategy/"+param+"&_id="+id+"&show=1&strategy_code=" + strategy_code+"&type="+type+"&ES_frmId=<?php echo $request['ES_frmId'];?>";
		}else{
			url = "?app_act=op/op_gift_strategy/"+param+"&_id="+id+"&strategy_code=" + strategy_code+"&type="+type+"&ES_frmId=<?php echo $request['ES_frmId'];?>";
		}
		//alert(url);
		location.href  = url;
	}
	
}
</script>

<?php if($response['data']['is_check']==1):?>

<div class="panel">
    <div class="panel-header">
        <h3 class="">操作日志 <i class="icon-folder-open toggle"></i></h3>
    </div>
    <div class="panel-body">
    	<div class="row">
        
        <?php
        render_control('DataTable', 'log', array(
            'conf' => array(
                'list' => array(
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '操作者',
                        'field' => 'user_code',
                        'width' => '120',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '操作名称',
                        'field' => 'action_name',
                        'width' => '120',
                        'align' => ''
                    ),
                  
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '操作时间',
                        'field' => 'add_time',
                        'width' => '150',
                        'align' => ''
                    ),
 
                )
            ),
            'dataset' => 'op/GiftStrategyLogModel::get_by_page',
            //'queryBy' => 'searchForm',
            'idField' => 'log_id',
            'params' => array('filter' => array('strategy_code'=>$response['data']['strategy_code'])),
           
        ));
        ?>
    </div>
 </div>
 </div>
<?php endif;?>