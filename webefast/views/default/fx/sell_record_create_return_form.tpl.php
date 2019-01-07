<style>
    .panel-body{
        padding:0px;
    }
    #panel_order form{
        border:solid #ddd 1px;
        border-right:0px;
        border-top:0px;
        padding:0px;
    }
    #panel_order .row{
        float:left;
        width:358px;
        padding:0px 0px 0px 5px;
        border-top:solid #ddd 1px;
        border-right:solid #ddd 1px;
    }
    #panel_order .span11{
        width:330px;
        margin:0px;
    }
    #panel_order .span8{
        width:180px;
        margin-left:5px;
    }
    #panel_order .form-horizontal .control-label{
        text-align: left;
        width:140px;
        border-right:solid #ddd 1px;
    }
    #panel_money form{
        border:solid #ddd 1px;
        border-right:0px;
        border-top:0px;
        padding:0px;
    }
    #panel_money .row{
        float:left;
        width:267px;
        padding:0px 0px 0px 5px;
        border-top:solid #ddd 1px;
        border-right:solid #ddd 1px;
    }
    #panel_money .span11{
        width:265px;
        margin:0px;
    }
    #panel_money .span8{
        width:150px;
        margin-left:5px;
    }
    #panel_money .form-horizontal .control-label{
        text-align: left;
        width:103px;
        border-right:solid #ddd 1px;
    }
    #table1 .bui-grid-row input.input-normal{
        width:70%;
    }
</style>
<?php render_control('PageHead', 'head1',
 array('title' => '售后服务单预览',
));
?>
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left"><img src="assets/img/sys/ddxq_icon.png"/>生成退单</h3>
        <div class="pull-right">
        </div>
    </div>
    <div class="panel-body" id="panel_order">
        <?php

        $shipping_status = $response['record']['shipping_status'];
        $pay_type = $response['record']['pay_type'];
        $return_type_arr = array();
        $active = '0';$selected='';
                
		/* COD的订单发货前 生成应退为0的退款单 
		*  COD的订单发货后 生成退款单(赔付) 或 退货单
		*  非COD发货前 生成应退为0的退款单
		*  非COD发货后 生成退款单(赔付) 或 退款退货单
		*/
		if ($response['record']['pay_type'] == 'cod'){
			if ($response['record']['shipping_status'] < 4){
				$return_type_arr[] = array(1,'退款单');
			}else{
				$return_type_arr[] = array(1,'退款单');			
				$return_type_arr[] = array(2,'退货单');	
                                $active = '2';
			}
		}else{
			if ($response['record']['shipping_status'] < 4){
				$return_type_arr[] = array(1,'退款单');
			}else{
				$return_type_arr[] = array(1,'退款单');			
				$return_type_arr[] = array(3,'退款退货单');
                                $active = '3';
                                $selected = '1';
			}			
		}		       

        render_control('FormTable', 'form1', array(
            'conf' => array(
                'fields' => array(
                    array('title' => '退单类型', 'type' => 'radio_group', 'field' => 'return_type', 'data' => $return_type_arr,'active'=>$active),
                    array('title' => '退款方式', 'type' => 'select', 'field' => 'return_pay_code', 'data' => ds_get_select('refund_type',2)),
                    array('title' => '额外赔付', 'type' => 'select', 'field' => 'is_compensate','data'=>ds_get_select_by_field('boolstatus', 0)),
                    array('title' => '退货仓库', 'type' => 'select', 'field' => 'return_store_code','data'=>load_model('base/StoreModel')->get_purview_store(),'value'=>$response['record']['return_store_code']),
                    array('title' => '买家退货配送方式', 'type' => 'select', 'field' => 'return_express_code', 'data' => ds_get_select('shipping', 2)),
                    array('title' => '买家退货快递单号', 'type' => 'input', 'field' => 'return_express_no'),
                    array('title' => '交易号', 'type' => 'label', 'field' => 'deal_code_list'),
                    array('title' => '平台退单号（平台退单状态）', 'type' => 'label', 'field' => ''),
                    array('title' => '买家确认支付状态', 'type' => 'select', 'field' => 'sell_record_checkpay_status', 'data' => array(array('0','买家未确认支付'),array('1','买家已确认支付'))),
                    array('title' => '买家退单说明', 'type' => 'input', 'field' => 'return_buyer_memo'),
                    array('title' => '卖家退单备注', 'type' => 'input', 'field' => 'return_remark'),
                    array('title' => '订单包裹是否已出库', 'type' => 'select', 'field' => 'is_package_out_stock','data'=>ds_get_select_by_field('boolstatus', 0),'active'=>$selected),
                    array('title' => '退单原因', 'type' => 'select', 'field' => 'return_reason_code', 'data' => ds_get_select('return_reason'),'active'=>$response['reason_code']),
                    array('title' => '','type'=>''),
                    array('title' => '','type'=>''),
                ),
            ),
            'col'=>'3',
            'act_edit' => "fx/sell_record/create_return",
            'buttons' => array(),
            'data' => $response['record'],
        ));
        ?>
    </div>
</div>
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left"><img src="assets/img/sys/ddje_icon.png"/>退款信息</h3>
        <div class="pull-right">
        </div>
    </div>
    <div class="panel-body" id="panel_money">
        <?php
        render_control('FormTable', 'form2', array(
            'conf' => array(
                'fields' => array(
                    array('title' => '实际总退款金额', 'type' => 'input', 'field' => 'refund_total_fee'),
                    array('title' => '卖家承担运费', 'type' => 'input', 'field' => 'seller_express_money'),
                    array('title' => '赔付金额', 'type' => 'input', 'field' => 'compensate_money'),
                    array('title' => '手工调整金额', 'type' => 'input', 'field' => 'adjust_money'),
                ),
            ),
            'col'=>'4',
            'act_edit' => "fx/sell_record/create_return",
            'buttons' => array(),
            'data' => $response['record'],
        ));
        ?>
    </div>
</div>

<table cellspacing="0" class="table table-bordered" id="tbl_mx">
    <tbody>
        <tr>
            <th>交易号</th>
            <th>商品名称</th>
            <th>商品编码</th>
            <th><?php echo $response['goods_spec1_rename'];?></th>
            <th><?php echo $response['goods_spec2_rename'];?></th>
            <th>商品条形码</th>
            <th>关联订单商品数量</th>
            <th>实际退货数量</th>
            <th>均摊金额</th>
            <th>实际退款金额</th>
            <th>已退货数量</th>
        </tr>
        <?php foreach($response['detail_list'] as $data): ?>
        <tr>
            <td><?php echo $data['deal_code'];?></td>
            <td><?php echo get_goods_name_by_code($data['goods_code']); ?></td>
            <td><?php echo $data['goods_code']; ?></td>
            <td><?php echo get_spec1_name_by_code($data['spec1_code']); ?></td>
            <td><?php echo get_spec2_name_by_code($data['spec2_code']); ?></td>
            <td><?php echo $data['barcode']; ?></td>
            <td><?php echo $data['num']; ?></td>
            <td>
            	<input type="text" 
            		   deal_code="<?php echo $data['deal_code'];?>" 
            		   sku="<?php echo $data['sku']; ?>" 
            		   mx_id="<?php echo $data['sell_record_detail_id']; ?>" 
            		   data_return_num="<?php echo $data['return_num'];?>" 
            		   max_num="<?php echo $data['num']; ?>"
            		   id="returnable_num_<?php echo $data['sell_record_detail_id']; ?>" 
            		   name="return_num" class="input-normal" 
            		   value="<?php echo $data['returnable_num']; ?>"/>
            </td>
            <td>
                        <?php if($response['record']['is_fenxiao'] == 1) {echo $data['fx_amount'];} else {echo $data['avg_money'];} ?>
	            <input type="hidden" mx_id="<?php echo $data['sell_record_detail_id']; ?>" id="_avg_money_<?php echo $data['sell_record_detail_id']; ?>" value="<?php echo $data['avg_money']; ?>"/>
            </td>
            <td><input type="text" mx_id="<?php echo $data['sell_record_detail_id']; ?>" id="avg_money_<?php echo $data['sell_record_detail_id']; ?>" name="avg_money" class="input-normal" value="<?php echo $data['avg_money']; ?>"/></td>
            <td><?php echo $data['return_num']; ?></td>
            
        </tr>
        <?php endforeach;?>
    </tbody>
</table>
<div style="height:100px;width:100%"></div>
<div class="clearfix" id="tools" style="text-align: center;position: fixed;bottom: 0px;left:-100%;width:100%;background-color:#fff">
    <div>
        <button class="button button-primary" id="btn_create_return">生成退单</button>
        <button class="button button-primary" id="btn_close">关闭</button>
    </div>
    <div style="width:5%;height:100%;background-color:#0066cc;float:right;cursor:pointer" id="close_tools"><</div>
</div>
<script>
 var ES_frmId  = '<?php echo $request['ES_frmId'];?>';
 var record_json = <?php echo json_encode($response['record']); ?>;
 //COD已发货的单子，要把运费带过来
 if (record_json['pay_type'] == 'cod' && record_json['shipping_status']>=4){
	$("#seller_express_money").val(<?php echo $response['record']['express_money']; ?>);
 }
 
 //如果已发货的订单，选退款类型时，那就是要赔付，这时不要选商品的明细
$("input[name='return_type']").click(function(){
	  if ($(this).val() == 1 && record_json['shipping_status']>=4){
	    $("#tbl_mx").css('display','none'); 
		$("input[name='return_num']").val(0);
		$("input[name='return_num']").change();	    
	  }else{
	    $("#tbl_mx").css('display','inline-table'); 	
	  }
           get_refund_total_fee();
});
//如果退单类型只有一个选项时，默认选中这个选项
if ($("input[name='return_type']").size() == 1){
  $("input[name='return_type'][value='1']").attr("checked",1);
}

 var mx_json = <?php
 $mx_arr = array();
 foreach($response['detail_list'] as $data){
	 $mx_arr['id_'.$data['sell_record_detail_id']] = array('real_num'=>$data['real_num'],'avg_money'=>$data['avg_money'],'fx_amount' => $data['fx_amount']);
 }
 echo json_encode($mx_arr);
 ?>;
var sell_record_code = '<?php echo $response['record']['sell_record_code']; ?>';
var input = new Array('return_type','return_pay_code','return_express_code','return_express_no','return_reason_code','return_buyer_memo','return_remark','is_compensate','sell_record_checkpay_status','is_package_out_stock',
            'refund_total_fee','seller_express_money','compensate_money','adjust_money','return_store_code');
var is_allowed_exceed = <?php echo $response['is_allowed_exceed'];?>;
$(function(){
    $(".row").attr("style",'');
    $("#btn_create_return").click(function(){
        var data = {};
        data['sell_record_code'] = sell_record_code;
        $.each(input,function(index,value){
            var d = document.getElementsByName(value);
            if (d.length == 0){
	            d = $("#"+value);
            }
            if(d.length==1){
                data[value] = d[0].value;
            }else if(d.length>1){
                data[value] = $('input[name='+value+"]:checked").val();
            }
        });
        var end_break = 0;
        if (is_allowed_exceed == 1 ) {
	        
	        $("input[name='return_num']").each(function(i,v){
	     		var data_return_num = $(this).attr("data_return_num");
	     		var max_num = $(this).attr("max_num");
	     		var returnable_num = $(this).val();
	     		if (returnable_num > max_num - data_return_num)
	     		{
	     			alert('退货商品数，不允许超过订单商品数');
	     			end_break = 1;
	     			return false;
	     		}
	     		
	        })
	    }
        if (end_break) {
            end_break = 0;
            return;
        }
        var mx_data = get_sel_mx();
        data['mx'] = mx_data['mx'];

		var must_select_mx = 1;
		if (record_json.shipping_status>=4 && $("input[name='return_type']").val() == 1){
			must_select_mx = 0;
		}
		//如果是退货单 或退款退货单，要选择商品
        if (data['mx'].length == 0 && must_select_mx == 1){
	        alert('请选择要退的商品');
	        return;
        }
        
		//console.log(data);
		//return;
        $.post("?app_act=fx/sell_record/create_return&app_fmt=json",data,function(ret){
            if(ret.status!=1){
                BUI.Message.Alert(ret.message,'error');
            }else{
                location.href= "?app_act=fx/sell_return/after_service_detail&sell_return_code="+ret.data+"&ES_frmId="+ES_frmId;
            }
        },'json');
    });
    //关闭按钮
    $("#btn_close").click(function(){
        ui_closeTabPage('<?php echo $request['ES_frmId']; ?>');
    });
    //初始化工具条
        tools();
});
function tools(){
        $("#tools").animate({left:'0px'},1000);
        $("#close_tools").click(function(){
            if($(this).html()=="&lt;"){
                $("#tools").animate({left:'-95%'},1000);
                $(this).html(">");
            }else{
                $("#tools").animate({left:'0px'},1000);
                $(this).html("<");
            }
        });
    }

 $("#seller_express_money").add("#compensate_money").add("#adjust_money").val(0);
 $("#refund_total_fee").attr('readonly','true');
 $("#refund_total_fee").css('background','#ccc');
 $("#seller_express_money").add("#compensate_money").add("#adjust_money").add("input[name='avg_money']").change(function(){
	get_refund_total_fee();
 });

 function get_refund_total_fee(){
	var total_fee = 0;
    if($("#seller_express_money").val() == ''){
        $("#seller_express_money").val(0);
    }else if($("#compensate_money").val() == ''){
        $("#compensate_money").val(0);
    }else if($("#adjust_money").val() == ''){
        $("#adjust_money").val(0);
    }
	total_fee += parseFloat($("#seller_express_money").val()) + parseFloat($("#compensate_money").val()) + parseFloat($("#adjust_money").val());
	if($("#tbl_mx").css('display')!='none'){
            var mx_data = get_sel_mx();
	    total_fee += mx_data['total_avg'];
        }
    total_fee  = Math.round(total_fee*100)/100;
    if(isNaN(total_fee)){
        total_fee = '';
    }
	$("#refund_total_fee").val(total_fee);
 }
 get_refund_total_fee();

 $("input[name='return_num']").change(function(){
	 //console.log($(this));
	 var sell_record_detail_id = $(this).attr("mx_id");
	 var return_num = mx_json['id_'+sell_record_detail_id]['real_num'];
	 var avg_money = mx_json['id_'+sell_record_detail_id]['avg_money'];
         if(record_json['is_fenxiao'] == 1) {
             avg_money = mx_json['id_'+sell_record_detail_id]['fx_amount'];
         }
	 var cur_num = $(this).val();
	 var cur_je = (avg_money/return_num) * cur_num;
	 //console.log(cur_je);
     cur_je = Math.round(cur_je*100)/100;
     if(isNaN(cur_je)){
         cur_je=0;
     }
	 $("#avg_money_"+sell_record_detail_id).val(cur_je);
	 get_refund_total_fee();
 });

  $("input[name='return_num']").change();

  $(".mx_sel").click(function(){
	  get_refund_total_fee();
  });

  function get_sel_mx(){
	var data = {};
	data['total_avg'] = 0;
	data['mx'] = new Array();
    $("input[name='return_num']").each(function(){
        var sell_record_detail_id = $(this).attr("mx_id");
        var return_num = $("#returnable_num_"+sell_record_detail_id).val();
        //console.log($("#returnable_num_"+sell_record_detail_id));
        //console.log(return_num);
        if (return_num>0){
            var t_row = {};
            t_row['deal_code'] = $(this).attr("deal_code");
            t_row['sku'] = $(this).attr("sku");
            t_row['return_num'] = return_num;
            t_row['avg_money'] = $("#avg_money_"+sell_record_detail_id).val();
            data['total_avg'] += parseFloat(t_row['avg_money']);
            data['mx'].push(t_row);
        }
    });
    return data;
  }
</script>

