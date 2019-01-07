<style type="text/css">
	.well .control-group {
	    width: 100%;
	}
	.sort_btn {
		border: 1px solid #d5d5d5;
		border-radius: 3px;
		cursor: pointer;
		height: 24px;
		padding: 0 15px;
		position: relative;
		text-align: center;
	}
	.control-group .controls .tip{ display:inline-block; width:20px; height:20px; position:absolute; top:3px; right:3px; background:url(assets/images/tip.png) no-repeat -2px -2px;}
</style>

<form class="form-horizontal well" style="padding: 5px 0 10px;" id="edit_inv_info_form">
    <div class="control-group">
        <label class="control-label">买家留言：</label>
        <div class="controls docs-input-sizes">
            <input class="span10 span-width control-text" type="text"   disabled="disabled" name="buyer_remark" value="<?php echo $response['record']['buyer_remark'];?>">
        </div>
    </div>
	<div class="control-group">
        <label class="control-label">商家留言：</label>
        <div class="controls docs-input-sizes">
            <input class="span10 span-width control-text" type="text" id="inv_seller_remark"  <?php if($response['record']['order_status'] != 0){?>  disabled="disabled"
                <?php }?> name="seller_remark" value="<?php echo $response['record']['seller_remark'];?>">
             <i class="tip" title="系统订单商家留言会同步平台商家留言，同步更新，如果人工在此编辑，可能会被平台商家留言覆盖"></i>
        </div>
    </div>
	<div style="clear:both;"></div>
	<div class="control-group">
        <label class="control-label">仓库留言：</label>
        <div class="controls docs-input-sizes">
            <input class="span10 span-width control-text" type="text" id="inv_store_remark"  <?php if($response['record']['order_status'] != 0){?>  disabled="disabled"
                <?php }?> name="store_remark" value="<?php echo $response['record']['store_remark'];?>">
            <i class="tip" title="用于提醒仓库拣货人员，此信息可以通过波次单打印显示"></i>
        </div>
    </div>
	<div style="clear:both;"></div>
	<div class="control-group">
        <label class="control-label">订单备注：</label>
        <div class="controls docs-input-sizes">
            <input class="span10 span-width control-text" type="text" id="inv_order_remark" name="order_remark" value="<?php echo $response['record']['order_remark'];?>">
        </div>
    </div>
    <div style="display: none">
    	<input type="hidden" name="seller_remark_old" id="seller_remark_old" value="<?php echo $response['record']['seller_remark'];?>">
    	<input type="hidden" name="store_remark_old" id="store_remark_old" value="<?php echo $response['record']['store_remark'];?>">
    	<input type="hidden" name="order_remark_old" id="order_remark_old" value="<?php echo $response['record']['order_remark'];?>">
        <input type="hidden" name="sell_record_code" id="sell_record_code" value="<?php echo $response['record']['sell_record_code'];?>">
    </div>
 	<div style="clear:both;"></div>
</form>
<div class="control-group">
	<button class="button button-small" id="btn_save_inv_info"><i class="icon-ok"></i>保存</button>
<!--	<button class="button button-small" <?php #if($response['record']['order_status'] != 0){?>
                disabled="disabled"
                <?php #}?> id="btn_cancel_inv_info"><i class="icon-ban-circle"></i>重置</button>-->
                <button class="button button-small" id="btn_cancel_inv_info"><i class="icon-ban-circle"></i>重置</button>
</div>
<script type="text/javascript">
$(document).ready(function(){
        var sell_record_code = $("#sell_record_code").val();
	var seller_remark = $("#seller_remark_old").val();
	var store_remark = $("#store_remark_old").val();
	var order_remark = $("#order_remark_old").val();
	$("#btn_save_inv_info").on("click", function() {
	    var params = {"data": {},"app_fmt":'json'};
		params.data["seller_remark"] = $("#edit_inv_info_form #inv_seller_remark").val();
		params.data["store_remark"] = $("#edit_inv_info_form #inv_store_remark").val();
		params.data["order_remark"] = $("#edit_inv_info_form #inv_order_remark").val();
                params.data["sell_record_code"] = $("#sell_record_code").val();
		//params.data["invoice_status"] = $("#sort").find(".active").attr("id");
		if (seller_remark != params.data["seller_remark"]) {
			params.data["seller_remark_diff"] = 1;
		}
		if (store_remark != params.data["store_remark"]) {
			params.data["store_remark_diff"] = 1;
		}
		if (order_remark != params.data["order_remark"]) {
			params.data["order_remark_diff"] = 1;
		}

	    var ajax_url = '?app_act=oms/sell_record/update_inv_info';
	    $.post(ajax_url,params,function(data){
	    	if(data.status == 1){
                    BUI.Message.Show({msg: '修改成功',icon: 'success',buttons: [],autoHide: true});
                    component("money,detail,action,base_order_info,goods_detail,shipping_info,inv_info", "view");
                    /*
	    		component("money", "view");
	            component("detail", "view");
	            component("action", "view");
	            component("base_order_info", "view");
	            component("goods_detail", "view");
	            component("shipping_info", "view");
	            component("inv_info", "view");*/
	        } else {
	            BUI.Message.Alert(data.message, 'error');
	        }
	    },'json');
	})
	$("#btn_cancel_inv_info").on('click', function (){
            component("money,detail,action,base_order_info,goods_detail,shipping_info,inv_info", "view");
            /*
		component("money", "view");
	    component("detail", "view");
	    component("action", "view");
	    component("base_order_info", "view");
	    component("goods_detail", "view");
	    component("shipping_info", "view");
	    component("inv_info", "view");*/
	})
});
function sort(_this) {
	$(".sort_btn").css({"color": "#666"});
	$(".sort_btn").removeClass("active");
	$(_this).css({"color": "#1695ca"});
	$(_this).addClass("active");
	tableStore.load();
}
</script>
