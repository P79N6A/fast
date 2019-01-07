<table cellspacing="0" class="table table-bordered" id="goods_info_detail">
    <thead>
    <tr>
        <th style="width:120px;">交易号</th>
        <th>商品名称</th>
        <th>商品编码</th>
        <th style="width:195px;">系统规格</th>
        <th>商品条形码</th>
        <th>数量(实物锁定)</th>
        <th>吊牌价</th>
        <th>数量</th>
        <th>应收金额</th>
        <?php if($response['record']['is_fenxiao'] == 1 || $response['record']['is_fenxiao'] == 2){?>
            <th>结算单价</th>
            <th>结算金额</th>
        <?php }?>
        <th style="width: 50px;">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $record_type = $response['record_type'] == 'fx'? 'fx':'oms';
    foreach($response['detail_list'] as $key=>$detail){?>
        <tr class="detail_<?php
        echo $detail['sell_record_detail_id'];
        if($response['record']['order_status']!=3 && $response['record']['must_occupy_inv'] == 1 && $detail['num'] > $detail['lock_num']){
            echo " is_stock_out_row";
        }
           if ($detail['api_refund_num']>0){
			echo " api_refund_desc";
		}
        ?>"
        <?php
        if ($detail['api_refund_num']>0){
			echo " style='background-color:#666666'   title='{$detail['api_refund_desc']}'";
		}
        ?>
            param1="<?php echo $detail['sell_record_detail_id']; ?>" param2="<?php echo $detail['sku']; ?>"
        >
            <td>
                <input class="deal_code" style="width: 120px;" name="deal_code"
                <?php if($response['record']['order_status'] != 0 || ($response['record']['is_fenxiao'] == 2 && $response['record']['is_fx_settlement'] == 1)){?>
                disabled="disabled"
                <?php }?>
                type="text" value="<?php echo $detail['deal_code'];?>">
                <input type="hidden" class="good_oms_sell_code" value="<?php echo $response['record']['sell_record_code'];?>">
                <input type="hidden" class="sell_record_detail_id" value="<?php echo $detail['sell_record_detail_id'];?>">
            </td>
            <td><?php echo $detail['goods_name'];?></td>
            <td class='goods_code'><?php echo $detail['goods_code'];?></td>
            <td>
	            <select class="good_spec1_code" name='good_spec1_code' style="width:90px;" <?php if($response['record']['order_status'] != 0 || ($response['record']['is_fenxiao'] == 2 && $response['record']['is_fx_settlement'] == 1)){?>
                disabled="disabled"
                <?php }?>>
	            	<?php foreach($detail['spec1_arr'] as $key=>$spec1){?>
	            		<option value="<?php echo $spec1['spec1_code'];?>" <?php if ($detail['spec1_code'] === $spec1['spec1_code']){ echo "selected='selected'";}?>>
	            			<?php echo $spec1['spec1_name'];?>
	            		</option>
	            	<?php }?>
	            </select>
	            <select name='good_spec2_code' style="width:90px;margin-left:10px;" <?php if($response['record']['order_status'] != 0 || ($response['record']['is_fenxiao'] == 2 && $response['record']['is_fx_settlement'] == 1)){?>
                disabled="disabled"
                <?php }?>>
	            	<?php foreach($detail['spec2_arr'] as $key=>$spec2){?>
	            		<option value="<?php echo $spec2['spec2_code'];?>" <?php if ($detail['spec2_code'] === $spec2['spec2_code']){ echo "selected='selected'";}?>>
	            			<?php echo $spec2['spec2_name'];?>
	            		</option>
	            	<?php }?>
	            </select>
            </td>

            <td><?php echo $detail['barcode'];?></td>
            <td name="num" style="width: 100px">
                <div><?php echo $detail['num'];?>(<span class="num" style="color:red;" onclick="show_batch_detail(this)"><?php echo $detail['lock_num'];?></span>)</div>
            </td>

            <td><?php echo sprintf("%.2f", $detail['goods_price']);?></td>
            <td>
            	<input name="goods_num" type="text" onblur="change_avg_money('<?php echo $detail['num']; ?>','<?php echo sprintf("%.2f", $detail['avg_money']); ?>','<?php echo sprintf("%.2f", $detail['fx_amount']);?>',this)" value="<?php echo $detail['num'];?>" <?php if($response['record']['order_status'] != 0 || ($response['record']['is_fenxiao'] == 2 && $response['record']['is_fx_settlement'] == 1)){?>
                disabled="disabled"
                <?php }?> style="width: 50px">
            </td>
            <td name="avg_money">
                <input class="avg_money" name="good_avg_money" style="width: 50px;" type="text" value="<?php echo sprintf("%.2f", $detail['avg_money']);?>" <?php if($response['record']['order_status'] != 0 || ($response['record']['is_fenxiao'] == 2 && $response['record']['is_fx_settlement'] == 1)){?>
                disabled="disabled"
                <?php }?>>
            </td>
            <?php if($response['record']['is_fenxiao'] == 1 || $response['record']['is_fenxiao'] == 2){?>
            <td class="trade_price"><?php echo sprintf("%.2f", $detail['trade_price']);?></td>
            <td name="fx_amount">
                <input class="fx_amount" name="goods_fx_amount" style="width: 50px;" type="text" onchange="change_trade_price('<?php echo $detail['num']; ?>',this)" value="<?php echo sprintf("%.2f", $detail['fx_amount']);?>" <?php if($response['record']['order_status'] != 0 || $response['login_type']==2||($response['record']['is_fenxiao'] == 2 && $response['record']['is_fx_settlement'] == 1)){?>
                disabled="disabled"
                <?php }?>>
            </td>
            <?php }?>
            <td style="width: 100px;">
                <?php if($response['login_type'] == 2) { ?>                   
                    <?php if(load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/fx_change_goods') ){  ?>
                        <button class="button button-small change" title="等价换货" <?php if($response['record']['order_status'] != 0 || ($response['record']['is_fenxiao'] == 2 && $response['record']['is_fx_settlement'] == 1)){?> disabled="disabled" <?php }?>  onclick="change_goods_add('<?php echo $detail['sell_record_code']; ?>','<?php echo $detail['deal_code'];?>','<?php echo $detail['goods_name'];?>','<?php echo $detail['goods_code'];?>','<?php echo $detail['sku'];?>','<?php echo $detail['barcode'];?>','<?php echo $response['record']['store_code'];?>','<?php echo $detail['spec1_name'];?>','<?php echo $detail['spec2_name'];?>','<?php echo $detail['avg_money'];?>','<?php echo $detail['sell_record_detail_id']?>','<?php echo $detail['num'];?>')"><i class="icon-pencil"></i></button>
                    <?php } ?>
                <?php }else{ ?>                    
                    <?php if($record_type == 'oms' && load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/oms_change_goods') ){  ?>
                        <button class="button button-small change" title="等价换货" <?php if($response['record']['order_status'] != 0 || ($response['record']['is_fenxiao'] == 2 && $response['record']['is_fx_settlement'] == 1)){?> disabled="disabled" <?php }?>  onclick="change_goods_add('<?php echo $detail['sell_record_code']; ?>','<?php echo $detail['deal_code'];?>','<?php echo $detail['goods_name'];?>','<?php echo $detail['goods_code'];?>','<?php echo $detail['sku'];?>','<?php echo $detail['barcode'];?>','<?php echo $response['record']['store_code'];?>','<?php echo $detail['spec1_name'];?>','<?php echo $detail['spec2_name'];?>','<?php echo $detail['avg_money'];?>','<?php echo $detail['sell_record_detail_id']?>','<?php echo $detail['num'];?>')"><i class="icon-pencil"></i></button>
                    <?php }elseif ($record_type == 'fx' && load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/fx_change_goods')) {?>                                         
                        <button class="button button-small change" title="等价换货" <?php if($response['record']['order_status'] != 0 || ($response['record']['is_fenxiao'] == 2 && $response['record']['is_fx_settlement'] == 1)){?> disabled="disabled" <?php }?>  onclick="change_goods_add('<?php echo $detail['sell_record_code']; ?>','<?php echo $detail['deal_code'];?>','<?php echo $detail['goods_name'];?>','<?php echo $detail['goods_code'];?>','<?php echo $detail['sku'];?>','<?php echo $detail['barcode'];?>','<?php echo $response['record']['store_code'];?>','<?php echo $detail['spec1_name'];?>','<?php echo $detail['spec2_name'];?>','<?php echo $detail['avg_money'];?>','<?php echo $detail['sell_record_detail_id']?>','<?php echo $detail['num'];?>')"><i class="icon-pencil"></i></button>
                    <?php } ?>                   
                <?php } ?>                                                     
               <button class="button button-small delete" <?php if($response['record']['order_status'] != 0 || ($response['record']['is_fenxiao'] == 2 && $response['record']['is_fx_settlement'] == 1)){?>
                disabled="disabled"
                <?php }?> title="删除" onclick="goods_detail_delete(<?php echo $detail['sell_record_detail_id'];?>)"><i class="icon-trash"></i></button>
            </td>
        </tr>
    <?php }?>
    </tbody>
</table>
<button class="button button-small" <?php if($response['record']['order_status'] != 0 || ($response['record']['is_fenxiao'] == 2 && $response['record']['is_fx_settlement'] == 1)){?>
                disabled="disabled"
                <?php }?> id="btn_save_goods_info"><i class="icon-ok"></i>保存</button>
<button class="button button-small" <?php if($response['record']['order_status'] != 0 || ($response['record']['is_fenxiao'] == 2 && $response['record']['is_fx_settlement'] == 1)){?>
                disabled="disabled"
                <?php }?> id="btn_cancel_goods_info"><i class="icon-ban-circle"></i>重置</button>
<script>
   var is_fenxiao = "<?php echo $response['record']['is_fenxiao'];?>";
//明细删除
function goods_detail_delete(id){
    var params = {"sell_record_code": sell_record_code, "sell_record_detail_id": id};
    $.post("?app_act=oms/sell_record/opt_delete_detail", params, function(data){
        if(data.status == 1){
            component("money,detail,action,goods_detail,base_order_info,shipping_info,inv_info", "view");
            $("div .panel").hide();
			$("#edit_goods_info").show();
        } else {
            BUI.Message.Alert(data.message, 'error');
        }
    }, "json");
}

$("#btn_save_goods_info").on("click", function() {

    var params = {"data": {},"app_fmt":'json'};
	$("#goods_info_detail tr").each( function (index,element){
		if(index >0) {
			var sell_record_code = $(element).find(".good_oms_sell_code").val();
			var deal_code = $(element).find(".deal_code").val();
			var spec1_code = $(element).find('.good_spec1_code').val();
			var spec2_code = $(element).find("[name='good_spec2_code']").val();
			var goods_num = $(element).find("[name='goods_num']").val();
			var avg_money = $(element).find("[name='good_avg_money']").val();
			var goods_code = $(this).find(".goods_code").html();
			var trade_price = $(element).find(".trade_price").html();
			var fx_amount = $(element).find("[name='goods_fx_amount']").val();
			var sell_record_detail_id = $(element).find(".sell_record_detail_id").val();
			params.data[index] = "deal_code="+deal_code+";"+"spec1_code="+spec1_code+";"+"spec2_code="+spec2_code+";"+"goods_num="+goods_num+";"+"avg_money="+avg_money+";goods_code="+goods_code+";sell_record_code="+sell_record_code+";sell_record_detail_id="+sell_record_detail_id+";fx_amount="+fx_amount+";trade_price="+trade_price;
		}
    });
    var ajax_url = '?app_act=oms/sell_record/update_goods_info';
    $.post(ajax_url,params,function(data){
    	if(data.status == 1){
            BUI.Message.Show({msg: '修改成功',icon: 'success',buttons: [],autoHide: true});
            component("money,detail,action,base_order_info,goods_detail,shipping_info,inv_info", "view");
        } else {
            BUI.Message.Alert(data.message, 'error');
            component("goods_detail", "view");
        }
    },'json');
});
$("#btn_cancel_goods_info").on('click', function (){
        component("money,detail,action,base_order_info,goods_detail,shipping_info,inv_info", "view");
});
var selectPopWindowshelf_code = {
	    callback: function (value, id, code, name) {
	    	if(typeof value.sku == "undefined"){
		    	alert("请选择商品");
		    	return;
		    }
		    var num = value.num;
		    var   type="^[0-9]*[1-9][0-9]*$";
	        var   re   =   new   RegExp(type);
	       if(num.match(re)==null)
	        {
	         alert( "请输入大于零的整数!");
	        return;
	        }


		         $.ajax({
	                    type: "GET",
	                    url: "?app_act=oms/sell_record/opt_change_detail",
	                    //async: false,
	                    data: {sku:value.sku,
		                    num:value.num,
		                    sell_record_detail_id:value.sell_record_detail_id,
		                    sell_record_code:sell_record_code,
		                    deal_code:value.deal_code,
		                    avg_money:value.avg_money,
		                    is_gift:value.is_gift,
		                    app_fmt:'json'},
	                    dataType: "json",
	                    success: function(data){
	                        if(data.status==1){
                                    component("money,detail,action,base_order_info,goods_detail,shipping_info,inv_info", "view");
	                        }
	                    }
	                });
	        if (selectPopWindowshelf_code.dialog != null) {
	            selectPopWindowshelf_code.dialog.close();
	        }
	    }
	};
	function detail_change(goods_code,sell_record_detail_id,sku,deal_code,avg_money,is_gift,num){
            selectPopWindowshelf_code.dialog = new ESUI1.PopSelectWindow('?app_act=common/select1/order_goods&goods_code='+goods_code+'&sell_record_detail_id='+sell_record_detail_id+'&sku='+sku+'&deal_code='+deal_code+'&avg_money='+avg_money+'&is_gift='+is_gift+'&num='+num, 'selectPopWindowshelf_code.callback', {title: '等价换货', width: 900, height:500 ,ES_pFrmId:'<?php echo $request['ES_frmId'];?>'}).show();
	}
    function change_avg_money(num,avg_money,fx_amount,_this){
        var new_num = $(_this).val();
        //$(_this).parents("tr").find(".avg_money").val(Number((avg_money/num)*new_num).toFixed(2));
        if(is_fenxiao == 2){
            $(_this).parents("tr").find(".fx_amount").val(Number((fx_amount/num)*new_num).toFixed(2));
        }
    }
    function change_trade_price(num,_this) {
        if(is_fenxiao == 2){
            var fx_amount = $(_this).val();
            $(_this).parents("tr").find(".trade_price").html(Number((fx_amount/num)).toFixed(2));
        }
    }
    
      
    BUI.use('bui/tooltip', function (Tooltip) {
        var tips = new Tooltip.Tips({
            tip: {
                trigger: '.api_refund_desc', //出现此样式的元素显示tip
                alignType: 'top', //默认方向
                elCls: 'panel',
                width: 200,
                zIndex: '1000000',
                titleTpl: ' <div class="panel-body" style="background-color:#FFFF99">{title}</div>',
                offset: 5
            }
        });
        tips.render();
    });
    
<?php if ($response['lof_status'] == 1): ?>
    //供detail和goods_detail两部分使
    $('.num').css('cursor', 'pointer');
    $('.num').css('text-decoration', 'underline');

    function show_dialog(url, title, opt) {
        new ESUI.PopWindow(url, {
            title: title,
            width: opt.w,
            height: opt.h,
            onBeforeClosed: function () {
                if (typeof opt.callback == 'function')
                    opt.callback();
            }
        }).show();
    }

    function show_batch_detail(_this) {
        var num_v = $(_this).html();
        if (num_v == 0) {
            return;
        }
        var id = $(_this).parents('tr').attr('param1');
        var sku = $(_this).parents('tr').attr('param2');
        var title = '';
        var url = "?app_act=oms/sell_record/lock_detail&sell_record_detail_id=" + id + "&sku=" + sku;
        show_dialog(url, title, {w: 900, h: 400});
    }
<?php endif; ?>
</script>