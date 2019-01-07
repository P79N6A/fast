<style>
    .panel-body {padding: 0;}
    .panel-body table {margin: 0; }
    .barcode-update {color:#000;font-weight:bold}
</style>
<form id="recordForm" name="recordForm"  >
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left">订单信息</h3>
        <div class="pull-right">
        </div>
    </div>
    <div class="panel-body" id="panel_baseinfo">
        <table cellspacing="0" class="table table-bordered">
            <tr>
                <td width="15%" align="right">交易号：<input type="hidden" name="fenxiao_id" value="<?php echo isset($response['record']['fenxiao_id'])?$response['record']['fenxiao_id']:'';?>"></td>
                
                <td width="10%" id="tid"><?php echo $response['record']['fenxiao_id'];?></td>
                <td width="15%" align="right">下单时间：</td>
                <td width="10%"><?php echo $response['record']['created'];?></td>
                <td width="10%" align="right">付款时间：</td>
                <td width="20%"><?php echo $response['record']['pay_time'];?></td>
            </tr>
            <tr>
                <td  align="right">数量：</td>
                <td ><?php echo $response['record']['total_num'];?></td>
                <td align="right">金额：</td>
                <td ><?php echo round(($response['record']['total_fee']+$response['record']['post_fee']),2);?></td>
                <td  align="right">收货人：</td>
                <td ><span  id ='receiver_name'><?php echo $response['record']['receiver_name'];?></span></td>
            </tr>
            <tr>
                <td  align="right">收货人手机号：</td>
                <td ><span  id ='receiver_mobile_phone'><?php echo $response['record']['receiver_mobile_phone'];?></span></td>
                <td  align="right">固定电话：</td>
                <td ><span  id ='receiver_phone'><?php echo $response['record']['receiver_phone'];?></span></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td  align="right">买家留言：</td>
                <td  colspan="5"><?php echo $response['record']['memo'];?></td>
            </tr>
            <tr>
                <td  align="right">收货地址：</td>
                <td  colspan="5"><span class= 'sheng_city'>
                <?php echo $response['record']['receiver_state'].$response['record']['receiver_city'].$response['record']['receiver_district'].$response['record']['receiver_address'] ;?>
                <?php #echo $response['record']['receiver_address'];?>
                </span>
                <span class="quyu" style="display:none;">
                <?php echo $response['record']['receiver_country'].$response['record']['receiver_state'].$response['record']['receiver_city'].$response['record']['receiver_district'].$response['record']['receiver_street'].$response['record']['receiver_addr'] ;?><br>
                        <select id="province" name="province" data-rules="{required : true}">
                        <option value ="">请选择省</option>
                            <?php foreach($response['area']['province'] as $k=>$v){ ?>
                                <option  value ="<?php echo $v['id']; ?>" <?php if($v['name'] == $response['record']['receiver_state'] || $v['name'] == $response['record']['receiver_state']."省") { ?> selected="selected" <?php } ?>><?php echo $v['name']; ?></option>
                            <?php } ?>
                        </select>
                        <select id="city" name="city" data-rules="{required : true}">
                        	<option value ="">请选择市</option>
                            <?php foreach($response['area']['city'] as $k=>$v){ ?>
                                <option  value ="<?php echo $v['id']; ?>" <?php if($v['name'] == $response['record']['receiver_city']||$response['record']['ids']['city']==$v['id'] ) { ?> selected <?php } ?>><?php echo $v['name']; ?></option>
                            <?php } ?>
                        </select>
                        <select id="district" name="district" data-rules="{required : true}">
                        	<option value ="">请选择区县</option>
                            <?php foreach($response['area']['district'] as $k=>$v){ ?>
                                <option  value ="<?php echo $v['id']; ?>" <?php if($v['name'] == $response['record']['receiver_district']||$response['record']['ids']['district']==$v['id']  ) { ?> selected <?php } ?>><?php echo $v['name']; ?></option>
                            <?php } ?>
                        </select>
                        <span class = 'bianjirecord' id ='receiver_addr'><?php echo $response['record']['receiver_address'];?></span>
                </span>
                    </td>
            </tr>
        </table>
    </div>
</div>
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left">商品明细</h3>
        <div class="pull-right">
        </div>
    </div>
    <div class="panel-body" id="panel_baseinfo">
        <table cellspacing="0" class="table table-bordered">
            <tr>
                <th width="10%">图片</th>
                <th width="10%">商品编码</th>
                <th>商品名称</th>
                <th width="20%">商品属性</th>
                <th width="17%">SKU</th>
                <th width="6%">数量</th>
                <th width="9%">金额</th>
            </tr>
            <?php
            //echo '<hr/>$xx<xmp>'.var_export($response['record']['detail_list'],true).'</xmp>';
            foreach($response['record']['detail_list'] as $key=>$detail){?>
                <tr>
                    <td><?php if(isset($detail['snapshot_url']) && $detail['snapshot_url'] <> ''){ ?><img src="<?php echo $detail['snapshot_url'];?>" style="width:48px; height:48px;"><?php } ?></td>
                    <td><?php echo isset($detail['item_outer_id'])?$detail['item_outer_id']:'';?></td>
                    <td><?php echo isset($detail['title'])?$detail['title']:'';?></td>
                    <td><?php echo isset($detail['sku_properties'])?$detail['sku_properties']:'';?></td>
                    <td>
                        <span class = 'bianjigoods' id ="<?php echo 'barcode['.$detail['oid'].']'; ?>"><?php echo isset($detail['sku_outer_id'])?$detail['sku_outer_id']:'';?></span>
                    	<a href="javascript:void(0);" class="barcode-update">更新</a>
                    </td>
                    <td><?php echo isset($detail['num'])?$detail['num']:'';?></td>
                    <td><?php echo isset($detail['distributor_payment'])?$detail['distributor_payment']:'';?></td>
                </tr>
            <?php }?>
        </table>
    </div>
</div>
<div class="clearfix" style="padding: 0 0 15px 0; text-align: center;">
<?php if ($response['record']['is_change'] <> '1' && load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/td_save')){ ?>
	<input type="button" class="button button-primary" id="btn_edit" value="修改商品">
        <input type="button" class="button button-primary" id="btn_edit_record" value="修改订单">
<?php }?>
    <input type="button" class="button button-primary" id="btn_save" style="display:none;" value = "保存">
    <input type="button" class="button button-primary" id="btn_save_record" style="display:none;" value = "保存">
    <button class="button button-primary" id="btn_close">关闭</button>
</div>
</form>
<input id="shop_code" type="hidden" value="<?php echo $response['record']['shop_code'];?>" />
<?php echo load_js('comm_util.js')?>
<script>
    var url = '<?php echo get_app_url('base/store/get_area');?>';
//更新允许转单但未转单订单的商品条码操作
    $(".barcode-update").click(function(){
    	BUI.Message.Show({
            title: '自定义提示框',
            msg: '是否执行更新允许转单但未转单订单的商品条码操作？',
            icon: 'question',
            buttons: [
                {
                    text: '是',
                    elCls: 'button button-primary',
                    handler: function() {
                    	var params = {'app_fmt': 'json'};
                        params.fenxiao_id  = $('#tid').html();
                        params.shop_code  = $('#shop_code').val();
                    	$.post("?app_act=api/api_taobao_fx_order/barcode_update", params, function(data){
                            if(data.status == 1){
                                BUI.Message.Alert(data.message, 'info');
                                setTimeout("page_reload()",2000)
                            } else {
                                BUI.Message.Alert(data.message, 'error');
                            }
                        }, "json");
                        this.close();
                    }
                },
                {
                    text: '否',
                    elCls: 'button',
                    handler: function() {
                       
                        this.close();
                    }
                }
            ]
        });
	});

	function page_reload(){
		setTimeout("location.reload()",2000);
	}
    
$(document).ready(function(){
    	$('#country').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,0,url);
        });
        $('#province').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,1,url);
        });
        $('#city').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,2,url);
        });
        $('#district').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,3,url);
        });
        
        
   	$("#btn_edit").click(function(){
            $(".bianjigoods").each(function(index){
            	var value = $(this).html();
            	var name = $(this).attr("id");
            	$(this).html("<input type='text' name='"+name+"' class= '"+name+"' value='"+value+"'>");

            });
                $("#btn_edit").hide();
                $("#btn_edit_record").hide();
                $("#btn_save").show();
   		});
                
                
          $("#btn_edit_record").click(function(){
            $(".bianjirecord").each(function(index){
            	var value = $(this).html();
            	var name = $(this).attr("id");
            	$(this).html("<input type='text' name='"+name+"' class= '"+name+"' value='"+value+"'>");

            });
            $(".quyu").show();
            $(".sheng_city").hide();
            $("#btn_edit").hide();
            $("#btn_edit_record").hide();
            $("#btn_save_record").show();
   		});
      
      
       //修改商品
        $("#btn_save").click(function(){
           var data = $('#recordForm').serialize();
           data=data+'&shop_code='+$('#shop_code').val();
           $.post('<?php echo get_app_url('oms/sell_record/td_save_goods_check');?>', data, function(data){
        	if (data.status == 1) {
                          td_save_goods();
        		} else {
                          td_save_goods_no_update();                                       
        		}
            }, "json");
        });

//保存商品
function td_save_goods(){
         BUI.Message.Show({
            title: '提示',
            msg: '系统检测到存在其他交易订单，是否一并更新商品条码？',
            icon: 'question',
            buttons: [
                {
                    text: '是',
                    elCls: 'button button-primary',
                    handler: function() {
        	    var data = $('#recordForm').serialize();
                data=data+'&shop_code='+$('#shop_code').val()+'&update_status=1'+'&type_name="btn_save"';
        	$.post('<?php echo get_app_url('api/api_taobao_fx_order/td_save');?>', data, function(data){
                var type = data.status == 1 ? 'success' : 'error';
        		if (data.status == 1) {
       			   BUI.Message.Alert('修改成功', type);
          			window.location.reload();
        		} else {
        			BUI.Message.Alert(data.message, function() { }, type);
        		}
            }, "json");
                        this.close();
                    }
                },
                {
                    text: '否',
                    elCls: 'button',
                    handler: function() {
            	var data = $('#recordForm').serialize();
                data=data+'&shop_code='+$('#shop_code').val()+'&update_status=2'+'&type_name=btn_save';
        	$.post('<?php echo get_app_url('api/api_taobao_fx_order/td_save');?>', data, function(data){
                var type = data.status == 1 ? 'success' : 'error';
        		if (data.status == 1) {
       			   BUI.Message.Alert('修改成功', type);
          			window.location.reload();
        		} else {
        			BUI.Message.Alert(data.message, function() { }, type);
        		}
            }, "json");
                        this.close();
                    }
                }
            ]
        });  
}

//保存商品不更新
function td_save_goods_no_update(){
            	var data = $('#recordForm').serialize();
                data=data+'&shop_code='+$('#shop_code').val()+'&update_status=2'+'&type_name=btn_save';
        	$.post('<?php echo get_app_url('api/api_taobao_fx_order/td_save');?>', data, function(data){
                var type = data.status == 1 ? 'success' : 'error';
        		if (data.status == 1) {
       			   BUI.Message.Alert('修改成功', type);
          			window.location.reload();
        		} else {
        		   BUI.Message.Alert('修改失败', type);
        		}
            }, "json");
}

//修改订单
 $("#btn_save_record").click(function(){
//         var sMobile  = $(".receiver_mobile_phone").val();
//           if(sMobile!==''&&!(/^[\d]{7,11}$/.test(sMobile))&&typeof(sMobile) !== "undefined"){
//  	       BUI.Message.Alert('不是完整的11位手机号', 'error');
//    	     $(".receiver_mobile_phone").focus();
// 	        return false;
// 	     }      
        	var data = $('#recordForm').serialize();               
                data=data+'&update_status=2';
                console.log(data);
        	$.post('<?php echo get_app_url('api/api_taobao_fx_order/td_save');?>', data, function(data){
                var type = data.status == 1 ? 'success' : 'error';
        		if (data.status == 1) {
       			   BUI.Message.Alert('修改成功', type);
          			window.location.reload();
        		} else {
        			BUI.Message.Alert(data.message, function() { }, type);
        		}
            }, "json");            
        });

        $("#btn_close").click(function(){
        	ui_closePopWindow("<?php echo $request['ES_frmId']?>");
        });
    });
</script>