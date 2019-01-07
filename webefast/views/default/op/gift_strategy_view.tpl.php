<style>
input[type="checkbox"], input[type="radio"] {margin-top: 6px;}
.form-horizontal .control-label { width:150px;}
.row{ margin-bottom:10px;}
.nav-tabs{ padding-top:10px; margin-bottom:10px;}
form.form-horizontal{ padding:20px; border:1px solid #ded6d9;}
input.input-normal{ width:auto;}
select.input-normal {width: 155px;}
#info {
    position:absolute;
    top:90px;
    margin-left:750px;  
}
</style>
<ul class="nav-tabs oms_tabs">
    <li class="active"><a href="#"  >基本信息</a></li>
    <li ><a href="#" onClick="do_page();" >赠送规则</a></li>
</ul>

<?php 
/*
render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title' => '策略代码', 'type' => 'input', 'field' => 'strategy_code',),
            array('title' => '策略名称', 'type' => 'input', 'field' => 'strategy_name'),
          //  array('title' => '活动店铺', 'type' => 'select', 'field' => 'shop_code', 'data' => ds_get_select('shop',2)),
            array('title' => '活动开始时间', 'type' => 'time', 'field' => 'start_time', ),
            array('title' => '活动结束时间', 'type' => 'time', 'field' => 'end_time', ),
			array('title'=>'一个会员仅送一次', 'type'=>'checkbox', 'field'=>'is_once_only'),
			//array('title' => '活动店铺', 'type' => 'txt', 'field' => 'shop'),
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
)); */?>

 <form  class="form-horizontal" id="form1" action="" method="post">
 <input type="hidden" id="app_scene" name="app_scene" value="<?php echo $response['app_scene'];?>" />
	<input type="hidden" id="op_gift_strategy_id" name="op_gift_strategy_id" value="<?php echo $response['data']['op_gift_strategy_id'];?>"/>
		<div class="row">				
			<div class="control-group span11">
			<label class="control-label span3">策略代码：                </label>
	                   
			<div class="controls " >
			  <input type="text" name="strategy_code" id="strategy_code" class="input-normal" value="<?php echo $response['data']['strategy_code'];?>"   />	    </div>
			</div>
		</div>				
		<div class="row">				
			<div class="control-group span11">
				<label class="control-label span3">策略名称：                </label>
		                   
				<div class="controls " >
				  <input type="text" name="strategy_name" id="strategy_name" class="input-normal" value="<?php echo $response['data']['strategy_name'];?>" data-rules="{required: true}"  /><b style="color:red"> *</b>	    
				</div>
			</div>
		</div>				
		<div class="row">				
			<div class="control-group span11">
				<label class="control-label span3">活动开始时间：                </label>
		                   
				<div class="controls " >
				  <input type="text" name="start_time" id="start_time" class="input-normal calendar calendar-time"  value="<?php echo $response['data']['start_time'];?>" data-rules="{required: true}" />	    
				</div>
			</div>
		</div>				
		<div class="row">				
			<div class="control-group span11">
				<label class="control-label span3">活动结束时间：                </label>
		                   
				<div class="controls " >
				  <input type="text" name="end_time" id="end_time" class="input-normal calendar calendar-time calendar-end-time"  value="<?php echo $response['data']['end_time'];?>" data-rules="{required: true}" />	    
				</div>
			</div>
		</div>		
		<div class="row">				
			<div class="control-group span11">
				<label class="control-label span3">时间维度：                </label>
		                   
				<div class="controls bui-form-group " >
				  <input type="radio" name="time_type" class="field" value="0" <?php if($response['data']['time_type'] == 0){?>checked<?php }?> />付款时间
				  <input type="radio" name="time_type" class="field" value="1" <?php if($response['data']['time_type'] == 1){?>checked<?php }?> />下单时间 
			    </div>	
			</div>
		</div>		
		<div class="row">				
			<div class="control-group span11" style="width:1000px;">
				<label class="control-label span3">一个会员仅送一次：                </label>
		                   
				<div class="controls bui-form-group " >
				  <input type="checkbox" name="is_once_only" id="is_once_only" class="field" value="1"     <?php if($response['data']['is_once_only'] == 1){?>checked<?php }?> />	    
                                  <font style="color:red;margin-left:50px" >（说明：开启后，同一会员购买多单，仅赠送一次！）</font>
                                </div>	
			</div>
		</div>
                <div class="row" >				
			<div class="control-group span11" style="width:1000px;">
				<label class="control-label span3">  赠品库存不足继续赠送：                </label>
		                   
				<div class="controls bui-form-group " >
				  <input type="checkbox" name="is_continue_no_inv" id="is_continue_no_inv" class="field" value="1"     <?php if(isset($response['data']['is_continue_no_inv']) && $response['data']['is_continue_no_inv'] == 1){?>checked<?php }?> />
                                  <font style="color:red;margin-left:50px" >（说明：开启后，系统赠品库存不足，店铺交易订单会继续增加赠品，有风险请谨慎开启！）</font>
			    </div>	
			</div>
		</div>
        
		<div class="row">				
			<div class="control-group span11" style="width:1000px;">
				<label class="control-label span3">合并订单赠品升档：                </label>
		                   
				<div class="controls bui-form-group " >
				  <input type="checkbox" name="combine_upshift" id="combine_upshift" class="field" value="1"     <?php if($response['data']['combine_upshift'] == 1){?>checked<?php }?> />	    
                                  <font style="color:red;margin-left:50px" >（说明：开启后，系统合并订单，会先删除已有的赠品，重新匹配赠品策略，增加新的赠品！）</font>
                                </div>	
			</div>
		</div>
        
                <div class="row">				
			<div class="control-group span11" style="width:1000px;">
				<label class="control-label span3">赠品指定数量赠送：                </label>
		                   
				<div class="controls bui-form-group " >
				  <input type="checkbox" name="set_gifts_num" id="set_gifts_num" class="field" value="1"     <?php if($response['data']['set_gifts_num'] == 1){?>checked<?php }?> />	    
                                  <font style="color:red;margin-left:50px" >（说明：开启后，赠品可以手工设置最大赠送数量，即店铺交易订单匹配超过最大赠品数量即不赠送赠品！）</font>
                                </div>	
			</div>
		</div>
                
		<div class="row" >
				<label class="control-label span3">活动店铺：                </label>
				<div class="controls bui-form-group " style="margin-left: 150px;"> 
			<?php foreach ($response['shop'] as $key => $shop_row){?>
			
				<div class="controls span6" > 
				
                                    <input class="" name="shop_code[]" id="<?php echo $shop_row['shop_code'];?>" value="<?php echo $shop_row['shop_code'];?>" <?php if(isset($response['gift_shop']) && is_array($response['gift_shop']) && in_array($shop_row['shop_code'], $response['gift_shop'])) {?> checked <?php }?> type="checkbox" ><?php echo $shop_row['shop_name'];?>
				</div>
			<?php }?>
			</div>
			</div>
			
			
     <a class="info" id="info" target="_blank" href="http://operate.baotayun.com:8080/efast365-help/?p=2502">您还不清楚怎么建立策略吗，点我知道更多</a>
    </form>
	
<div > <font color="red">说明：赠品策略，若以付款时间计算，只有在活动期间内成功付款的订单(包含货到付款)，系统才会自动送赠品；<br>
	
</font></div>
<div>
    <div id="TabPage1Submit" class="row form-actions actions-bar">
            <div class="span13 offset3 ">
           
                <button type="submit" class="button button-primary" <?php if($response['data']['is_check']==1){?>disabled <?php }?> id="submit">保存</button>
                
            </div>
        </div>
</div>
<script type="text/javascript">
     var form;
//$(function() {       
     form =  new BUI.Form.HForm({
                srcNode : '#form1',
                submitType : 'ajax',
                callback : function(data){
						                        var type = data.status == 1 ? 'success' : 'error';
                        if (data.status == 1) {
                                                        //window.location.reload();
                        } else {
                            BUI.Message.Alert(data.message, function() { }, type);
                        }
                       
						                }
        }).render();
     //});
    </script>
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
$(function(){
	
	$(".control-label").css("width","182px;");
	$("#TabPage1Submit").find("#submit").click(function(){
		
		var url = '';
		if($("#strategy_name").val() == '' ||  $("#start_time").val() == '' ||  $("#end_time").val() == ''  ){
			alert('策略名称  活动时间都不能为空');
			return false;
		}
		if($("#app_scene").val() == 'edit'){
			url	= '<?php echo get_app_url('op/gift_strategy/do_edit');?>';
		}else{
			url	= '<?php echo get_app_url('op/gift_strategy/do_add');?>';
		}
		//alert(url);
		 $("#strategy_code").attr("disabled", false);
		var data = $('#form1').serialize();
    	$.post(url, data, function(data){
            var type = data.status == 1 ? 'success' : 'error';
    		if (data.status == 1) {
    			
   			  // BUI.Message.Alert('修改成功：', type);
    			strategy_code = $("#strategy_code").val();
    			ids = data.data;
    			url = "?app_act=op/gift_strategy/view&_id="+ids+"&app_scene=edit&strategy_code=" + strategy_code+"&ES_frmId=<?php echo $request['ES_frmId'];?>";
    			//alert(url);
    			location.href  = url;
    		} else {
    			BUI.Message.Alert(data.message, function() { }, type);
    		}
        }, "json");
		
	});
		
});

function do_page() {
	var strategy_code = $("#strategy_code").val();
	var id = '<?php echo $response['_id'];?>';
	
	if(strategy_code != '' && id != ''){
		var url = "?app_act=op/gift_strategy/rule_do_list&_id="+id+"&show=1&strategy_code=" + strategy_code;
		location.href  = url;
	}
	
}
</script>


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
                        'title' => '操作描述',
                        'field' => 'action_desc',
                        'width' => '400',
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
    <?php if($response['can_test']){ ?>
    <div class="clearfix" id="tools" style="text-align: center;bottom: 0px;left:-100%;width:100%;">
        <p class="p_btns">
            <button class="button button-primary" id="btn_opt_lock" onclick="test_info()">测试</button>
        </p>
        <div id="close_tools">&lt;</div>
    </div>
    <?php }?>
 </div>
<script>
    function test_info(){
        openPage("<?php echo $response['_url'];?>","<?php echo $response['url'];?>",'测试');
    }
</script>
