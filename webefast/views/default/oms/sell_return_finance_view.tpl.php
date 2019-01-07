<style>
    .panel-body {padding: 0;}
    .panel-body table {margin: 0; }
    .num_red{color:red;}
</style>
<?php
//$result = load_model('sys/GoodsRuleModel')->get_by_ids(array(1, 2));
render_control('PageHead', 'head1', array('title' => '待退款订单',
    'links' => array(
        array('url' => 'oms/sell_return_finance/do_list','is_pop' => false,'target' => '_self', 'title' => '待退款售后服务单')
    ),
    'ref_table' => 'table'
));
$baseinfo = $response['record'];
$ytk = $baseinfo['return_avg_money']+$baseinfo['seller_express_money']+$baseinfo['compensate_money']+
    $baseinfo['adjust_money']-$baseinfo['change_express_money']-$baseinfo['change_avg_money']
;
$ytk_str = "
    实际退款总金额（<span class='num_red'>{$ytk}</span>）= 
    退单商品实际应退款（<span class='num_red'>{$baseinfo['return_avg_money']}</span>）+ 
    卖家承担运费（<span class='num_red'>{$baseinfo['seller_express_money']}</span>）+ 
    赔付金额（<span class='num_red'>{$baseinfo['compensate_money']}</span>）+ 
    手工调整金额（<span class='num_red'>{$baseinfo['adjust_money']}</span>）- 
    换货单商品实际应收款（<span class='num_red'>{$baseinfo['change_avg_money']}</span>）- 
    换货单运费（<span class='num_red'>{$baseinfo['change_express_money']}</span>）
";
unset($baseinfo);
?>
<form id="recordForm" name="recordForm"  >
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left">订单信息</h3>
        <div class="pull-right">
         <?php if($response['record']['finance_check_status'] == '0'){ ?>
			<button id="btnFormEdit"  class="button button-small" type="button">
			<i class="icon-edit"></i>
			编辑
			</button>
			<button id="btnFormSave" class="button button-small" style="display: none;" type="button">
			<i class="icon-ok"></i>
			保存
			</button>
			<button id="btnFormCancel" class="button button-small" style="display: none;" type="button">
			<i class="icon-remove"></i>
			取消
			</button>
		 <?php }?>
      </div>
    </div>
    <div class="panel-body" id="panel_baseinfo">
        <table cellspacing="0" class="table table-bordered">
            <tr>
                <td width="10%" align="right">退单号（类型）<input type="hidden" name="sell_return_id" value="<?php echo $response['record']['sell_return_id']; ?>"></td>
                <td width="15%"><a href="javascript:openPage('售后服务单详情','?app_act=oms/sell_return/after_service_detail&sell_return_code=<?php echo $response['record']['sell_return_code'];?>&amp;ref=do','售后服务单详情')"><?php echo $response['record']['sell_return_code'];?></a>(<?php echo $response['record']['return_type_txt'];?>)</td>
                <td width="10%" align="right">原单号（交易号）</td>
                <td width="15%"><a href="javascript:openPage('订单详情','?app_act=oms/sell_record/view&amp;sell_record_code=<?php echo $response['record']['sell_record_code'];?>&amp;ref=do','订单详情')"><?php echo $response['record']['sell_record_code'];?></a>(<?php echo $response['record']['deal_code'];?>)</td>
                
            </tr>
            <tr>
                <td  align="right">店铺</td>
                <td ><?php echo $response['record']['shop_code_name'];?></td>
                <td align="right">平台退单号（平台退单状态）</td>
                <td ><?php //echo round($response['record']['order_money'],2);?></td>
            </tr>
            <tr>
                <td  align="right">买家昵称</td>
                <td ><?php echo $response['record']['buyer_name'];?></td>
                <td  align="right">销售平台</td>
                <td ><?php echo $response['record']['sale_channel_name'];?></td>
               
            </tr>
            <tr>
                <td  align="right">实际应退款总额</td>
                <td  ><span  id ='refund_total_fee'><?php echo number_format($ytk, 3, '.', '');?></span></td>
                <td>原单收货人</td>
                <td><?php echo $response['relation_record']['receiver_name'];?> </td>
            </tr>
            <tr>
                <td>退款方式</td>
                <td>
                <span class= 'pay_type'>
                <?php echo $response['record']['return_pay_code_name'];?> 
                </span>
                 <span class="quyu" style="display:none;">
                 <select id="return_pay_code" name="return_pay_code" data-rules="{required : true}">
                            <option value ="">请选择</option>
                            <?php foreach($response['refund_type'] as $k=>$v){ ?>
                                <option  value ="<?php echo $v['refund_type_code']; ?>" <?php if($v['refund_type_code'] == $response['record']['return_pay_code'] ) { ?> selected <?php } ?> ><?php echo $v['refund_type_name']; ?></option>
                            <?php } ?>
                </select>
                 </span>
                </td>
                <td>原单发货状态</td>
                <td><?php echo $response['relation_record']['sell_record_shipping_status_txt'];?></td>
            </tr>
            <tr>
                <td>退单原因</td>
                <td>
                  <span class='return_reason'>
                <?php echo $response['record']['return_reason_name'];?>
                </span>
                 <span class="quyu" style="display:none;">
                 <select id="return_reason_code" name="return_reason_code" data-rules="{required : true}">
                     <option value ="">请选择</option>
                     <?php foreach($response['record']['return_reason'] as $k=>$v){ ?>
                         <option  value ="<?php echo $v['return_reason_code']; ?>" <?php if($v['return_reason_code'] == $response['record']['return_reason_code'] ) { ?> selected <?php } ?> ><?php echo $v['return_reason_name']; ?></option>
                     <?php } ?>
                 </select>
                 </span>
                </td>
                <td>退单状态</td>
                <td><?php echo $response['record']['return_order_status'];?></td>
            </tr>
            
            <tr>
                <td>买家退单说明</td>
                <td><?php echo $response['record']['return_buyer_memo'];?></td>
                <td>卖家退单备注</td>
                <td><?php echo $response['record']['return_remark'];?></td>
            </tr>      
        </table>
    </div>
</div><br>
<span class='num_red'>计算公式：</span>
<?php echo $ytk_str;?>
<br><br>
<?php
if (isset($response['detail_list'])) {
?>
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left">退货商品信息</h3>
    </div>
    <div class="panel-body" id="panel_baseinfo">
        <table cellspacing="0" class="table table-bordered">
            <tr>
                <th width="10%" align="right">交易号</th>
                <th width="20%">商品名称</th>
                <th width="10%">商品编码</th>
                <th width="10%">系统规格</th>
                <th width="15%">商品条形码</th>
                <th width="10%">申请退货数量</th>
                <th width="10%">吊牌价</th>
                <th width="10%">实际应退款</th>
                <th width="5%">赠品</th>
            </tr>
            <?php
            foreach ($response['detail_list'] as $val) {
            ?>
            <tr>
                <td><?php echo $val['deal_code'];?></td>
                <td><?php echo $val['goods_name'];?></td>
                <td><?php echo $val['goods_code'];?></td>
                <td><?php echo $val['spec1_name'] . ' ' . $val['spec2_name'];?></td>
                <td><?php echo $val['barcode'];?></td>
                <td><?php echo $val['note_num'];?></td>
                <td><?php echo $val['goods_price'];?></td>
                <td><?php echo $val['avg_money'];?></td>
                <td><?php echo ((isset($val['is_gift']) && $val['is_gift'] == 1) ? '是' : '否');?></td>
            </tr>
            <?php
            }
            ?>
        </table>
    </div>
</div>
<br>
<?php
}
?>
<?php
if (isset($response['change_detail_list'])) {
?>
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left">换货商品信息</h3>
    </div>
    <div class="panel-body" id="panel_baseinfo">
        <table cellspacing="0" class="table table-bordered">
            <tr>
                <th width="10%" align="right">交易号</th>
                <th width="20%">商品名称</th>
                <th width="10%">商品编码</th>
                <th width="10%">系统规格</th>
                <th width="15%">商品条形码</th>
                <th width="10%">数量</th>
                <th width="10%">吊牌价</th>
                <th width="10%">实际应退款</th>
                <th width="5%">赠品</th>
            </tr>
            <?php
            foreach ($response['change_detail_list'] as $val) {
            ?>
            <tr>
                <td><?php echo $val['deal_code'];?></td>
                <td><?php echo $val['goods_name'];?></td>
                <td><?php echo $val['goods_code'];?></td>
                <td><?php echo $val['spec1_name'] . ' ' . $val['spec2_name'];?></td>
                <td><?php echo $val['barcode'];?></td>
                <td><?php echo $val['num'];?></td>
                <td><?php echo $val['goods_price'];?></td>
                <td><?php echo $val['avg_money'];?></td>
                <td><?php echo ((isset($val['is_gift']) && $val['is_gift'] == 1) ? '是' : '否');?></td>
            </tr>
            <?php
            }
            ?>
        </table>
    </div>
</div>
<br>
<?php
}
?>
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left">其它信息</h3>
    </div>
    <div class="panel-body" id="panel_baseinfo">
        <table cellspacing="0" class="table table-bordered">
            <tr>
                <td width="10%" align="right">创建人</td>
                <td width="15%"><?php echo $response['record']['create_person'];?></td>
                <td width="10%">创建时间</td>
                <td width="15%"><?php if($response['record']['create_time'] <> '0000-00-00 00:00:00'){ ?><?php echo $response['record']['create_time'];?><?php } ?></td>
            </tr>
            <tr>
                <td>确认人</td>
                <td><?php echo $response['record']['confirm_person'];?></td>
                <td>确认时间</td>
                <td><?php if($response['record']['confirm_time'] <> '0000-00-00 00:00:00'){ ?><?php echo $response['record']['confirm_time'];?> <?php } ?></td>
            </tr>
            <tr>
                <td>确认收货人</td>
                <td><?php echo $response['record']['receive_person'];?></td>
                <td>确认收货时间</td>
                <td><?php if($response['record']['receive_time'] <> '0000-00-00 00:00:00'){ ?><?php echo $response['record']['receive_time'];?><?php }?></td>
            </tr>
            <tr>
                <td>确认退款人</td>
                <td><?php echo $response['record']['agree_refund_person'];?></td>
                <td>确认退款时间</td>
                <td><?php if($response['record']['agreed_refund_time'] <> '0000-00-00 00:00:00'){ ?><?php echo $response['record']['agreed_refund_time'];?><?php }?></td>
            </tr>
             <tr>
                <td>退回人</td>
                <td><?php echo $response['record']['finance_reject_person'];?></td>
                <td>退回时间</td>
                <td><?php if($response['record']['finance_reject_time'] <> '0000-00-00 00:00:00'){ ?><?php echo $response['record']['finance_reject_time'];?><?php }?></td>
            </tr>
            <tr>
                <td>操作人</td>
                <td><?php echo $response['user_name'];?></td>
                <td>沟通日志</td>
                <td><?php echo $response['communication_log'];?></td>
            </tr>
        </table>
    </div>
</div>
<div class="clearfix" style="padding: 0 0 15px 0; text-align: center;">
<?php if($response['record']['finance_check_status'] == '2'){ ?>
	<?php if( $response['order_return_huo'] == '1' && $response['record']['return_type'] == '3' && $response['record']['return_shipping_status'] <> '1'){ ?>
	<input type="button" class="button button-primary"  disabled="disabled" id="btn_opt_finance_confirm" value="确认退款">
    <?php }else{?>
    <input type="button" class="button button-primary"   id="btn_opt_finance_confirm" value="确认退款">
    <?php }?>
    <input type="button" class="button button-primary" id="btn_opt_finance_reject"  value = "财务退回">
    <?php }?>
</div>
</form>

<input id="change_record" type="hidden"  value="<?php echo $response['record']['change_record'];?>" />

   <?php echo load_js('comm_util.js')?>
<script>
    var sell_return_code = <?php echo $response['record']['sell_return_code']?>;
    var url = '<?php echo get_app_url('base/store/get_area');?>';
    var opts = ['opt_finance_confirm','opt_finance_reject'];
    $(document).ready(function(){
        
   	    //初始化按钮
        btn_init();
   		$("#btnFormEdit").click(function(){//编辑
   			var str_edit = {};
            $(".bianji").each(function(index){
            	var value = $(this).html();
            	var name = $(this).attr("id");
            	$(this).html("<input type='text' name='"+name+"' class= '"+name+"' value='"+value+"'>");
                	
            });
            $(".quyu").show();
            $(".pay_type").hide();
            $(".return_reason").hide();
   	   		$("#btnFormEdit").hide();
   	   	   	$("#btnFormSave").show();
   	   	   	$("#btnFormCancel").show();
   		});	
        $("#btnFormSave").click(function(){   
        	var data = $('#recordForm').serialize();
        	$.post('<?php echo get_app_url('oms/sell_return_finance/save');?>', data, function(data){
                var type = data.status == 1 ? 'success' : 'error';
        		if (data.status == 1) {
        			//ui_closePopWindow("<?php echo $request['ES_frmId']?>")
       			   BUI.Message.Alert('修改成功：', type);
          			window.location.reload();
          			
        		} else {
        			BUI.Message.Alert(data.message, function() { }, type);
        		}
            }, "json");
            /*
            var d = {};
            $(".barcode_list").each(function(index){
                d[$(this).attr("name")] = $(this).val();
            });
			*/
			
        });

        $("#btnFormCancel").click(function(){
        	window.location.reload();
        });	
    });

    //初始化按钮
    function btn_init(){
        
        //操作按钮
        for(var i in opts){
            var f = opts[i]
            btn_init_opt(f);
        }
    }
	
    function btn_init_opt(id){
        $("#btn_"+id).click(function(){
           
            var params = {"sell_return_code": sell_return_code, "type": id};
            if(id=='opt_finance_confirm'){
                var change_record = $('#change_record').val();
                var msg = ''
                if(change_record.length > 0){
                	msg += '此退单为换货单，换货单号：'+change_record+'，'
                }
                BUI.Message.Confirm(msg+'请确认退款金额，避免多退',function(){
                     action_opt(params);
                },'question');  
            }else{
                action_opt(params);
            }
        
           
        });
    }
    function action_opt(params){
        $.post("?app_act=oms/sell_return/opt", params, function(data){
                if(data.status == 1){
                    //刷新按钮权限
                    $("#btn_opt_finance_confirm").hide();
                    $("#btn_opt_finance_reject").hide();
                    $("#btnFormEdit").hide();
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json");    
    }
    

    
</script>