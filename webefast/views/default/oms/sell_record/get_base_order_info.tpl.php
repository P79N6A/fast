<style text="text/css">
	#wms_search {font-weight:bold;color:#000;cursor:pointer;}
</style>
<div class="row">
	<div>
		<div class="row detail-row">
			<div class="span9">
			<label class="detail-name">交易号：</label><span class="detail-text"><font color='red'><?php echo $response['record']['deal_code_list'];?></font></span>
                        <input type="hidden" class="good_oms_sell_code" value="<?php echo $response['record']['sell_record_code']; ?>">
                        <?php if($response['record']['receiver_addr']=='*****'):?>
                        <a  href="javascript:void(0)" id='reset_encrypt'>修复加密数据</a>
                        <?php endif;?>
                        
                        
                        <?php
                        $wms_arr = array('ydwms', 'iwms', 'qimen', 'jdwms');
                        $response['is_wms'] = isset($response['is_wms']) ? 1 : '';
                        if ($response['record']['order_status'] == 1 && $response['is_wms'] == 1 && $response['record']['shipping_status'] == 1) {
                            if (!in_array($response['wms_system_code'], $wms_arr) && $response['order_process'] != 1) {

                            } else {
                                ?>
                                <span id="wms_search" >WMS配发货查询</span>
                            <?php }
                        }
                        ?>
                        </div>
			<div class="span9">
			<label class="detail-name">订单应付款：</label><span class="detail-text"><?php echo $response['record']['payable_money'];?></span>
			</div>
			<div class="span9">
			<label class="detail-name">计划发货时间：</label><span class="detail-text"><?php echo $response['record']['plan_send_time'];?></span>
			</div>
		</div>
		<div class="row detail-row">
			<div class="span9">
			<label class="detail-name">买家昵称：</label><span class="detail-text"><?php echo $response['record']['buyer_name'];?></span>
			</div>
			<div class="span9">
			<label class="detail-name">订单已付款：</label><span class="detail-text"><?php echo $response['record']['paid_money'];?></span>
			</div>
			<div class="span9">
			<label class="detail-name">仓库：</label><span class="detail-text"><?php echo $response['record']['store_name'];?></span>
			</div>
		</div>
		<div class="row detail-row">
			<div class="span9">
			<label class="detail-name">商品总计：</label><span class="detail-text" style="display:inline-block; width:63%; overflow:hidden; text-overflow:ellipsis; vertical-align:bottom;"><?php echo $response['record']['sku_num'] ;?>类<?php echo $response['record']['goods_num'] ;?>件</span>
			</div>
			<div class="span9">
			<label class="detail-name">订单运费：</label><span class="detail-text"><?php echo $response['record']['express_money'];?></span>
			</div>
			<div class="span9">
			<label class="detail-name">配送方式：</label><span class="detail-text"><?php echo $response['record']['express_name'];?></span>
			</div>
		</div>
		<div class="row detail-row">
            <div class="span9">
                <label class="detail-name">买家留言：</label><span class="detail-text" style="display:inline-block; width:63%; overflow:hidden; text-overflow:ellipsis; vertical-align:bottom;"><?php echo $response['record']['buyer_remark'];?></span>
            </div>
			<div class="span9">
			<label class="detail-name">商家留言：</label><span class="detail-text"><?php echo $response['record']['seller_remark'];?>
                            <?php   if($response['record']['seller_flag']>0&&$response['record']['sale_channel_code']=='taobao'):?>
                              <img src="assets/img/taobao/op_memo_<?php echo $response['record']['seller_flag'];?>.png"/>
                            <?php endif; ?>
                        </span>
			</div>
            <div class="span9">
			<label class="detail-name">订单备注：</label><span class="detail-text"><?php echo $response['record']['order_remark'];?></span>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
var wms_system_code = "<?php echo $response['wms_system_code']; ?>";
var sell_record_code="<?php echo $response['record']['sell_record_code'] ?>";
var order_process = "<?php echo $response['order_process'] ?>";
$("#wms_search").click(function(){
    new ESUI.PopWindow("?app_act=oms/sell_record/get_wms_status&sell_record_code="+sell_record_code+"&wms_system_code="+wms_system_code+"&order_process="+order_process, {
        title: "WMS配发货查询",
        width:450,
        height:370,
        onBeforeClosed: function() {
        },
        onClosed: function(){
            component("all", "view");
            //刷新按钮权限
//            btn_check()
        }
    }).show()
});
   <?php if($response['record']['receiver_addr']=='*****'):?>
$('#reset_encrypt').click(function(){
    var url ="?app_act=oms/sell_record/reset_encrypt_sell_record&sell_record_code="+sell_record_code;
    $.post(url,{},function(ret){
        var type = ret.status<0?'error':'info';
        BUI.Message.Alert(ret.message,type);
    },'json');
    
    
});
<?php endif;?>
</script>