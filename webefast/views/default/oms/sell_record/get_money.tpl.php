<!--<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="12%" align="right"><span title="商品均摊总金额+运费">订单应付款：</span></td>
        <td width="38%"><?php // echo sprintf("%.2f", $response['record']['payable_money']);?></td>
        <td width="12%" align="right">订单已付款：</td>
        <td width="38%">
        <?php
//         echo sprintf("%.2f", $response['record']['paid_money'])."（天猫积分：{$response['record']['point_fee']}，集分宝：{$response['record']['alipay_point_fee']}，抵用金额：{$response['record']['coupon_fee']}）";
        ?>
        </td>
    </tr>
    <tr>
        <td align="right"><span title="商品总额+运费">订单总额：</span></td>
        <td><?php // echo sprintf("%.2f", $response['record']['goods_money']+$response['record']['express_money']);?></td>
        <td align="right"><span title="订单已付款- 平台优惠 - 平台积分">买家实付金额：</span></td>
        <td><?php
//        $cur_fee = $response['record']['paid_money']-$response['record']['point_fee']-$response['record']['alipay_point_fee']-$response['record']['coupon_fee'];
//        echo sprintf("%.2f",$cur_fee);
        ?></td>
    </tr>
    <tr>
        <td align="right">运费：</td>
        <td>
            <?php // echo sprintf("%.2f", $response['record']['express_money']); ?>
        </td>
        <td align="right"><span title="订单总额-订单应付款">订单优惠：</span></td>
        <td>
        <?php
//         $cur_fee = $response['record']['goods_money']+$response['record']['express_money'] - $response['record']['payable_money'];
//         echo sprintf("%.2f", $cur_fee);
         ?>
         </td>
    </tr>
    <tr>
        <td align="right"><span title="吊牌价总和">商品总额：</span></td>
        <td><?php // echo sprintf("%.2f", $response['record']['goods_money']);?></td>
        <td align="right"><span title="不参与金额计算">订单运费险：</span></td>
        <td><?php // echo sprintf("%.2f", $response['record']['yfx_fee']);?></td>
    </tr>
    <tr>
        <td align="right"><span title="均摊金额总和">商品均摊总金额：</span></td>
        <td><?php // echo sprintf("%.2f", $response['record']['payable_money']-$response['record']['express_money']-$response['record']['delivery_money']);?></td>
        <td align="right">平台优惠信息：</td>
        <td><a href="###">查看</a></td>
    </tr>
</table>-->
        <style>
            .like_link{
                text-decoration:underline;
                color:#428bca; 
                cursor:pointer;
            }
        </style>
<?php 
$service_other_amount = load_model('common/ServiceModel')->check_is_auth_by_value('other_amount');
$fields = array();
if($service_other_amount == true){
    $fields = array(
            array('title' => '订单应付款', 'type' => 'label', 'field' => 'payable_money'),
            array('title' => '订单已付款', 'type' => 'html', 'field' => 'paid_money', 'html'=>sprintf("%.2f", $response['record']['paid_money'])."（天猫积分：{$response['record']['point_fee']}，集分宝：{$response['record']['alipay_point_fee']}，抵用金额：{$response['record']['coupon_fee']}）"),
            array('title' => '订单总额', 'type' => 'html', 'field' => 'order_total_money', 'html'=>sprintf("%.2f", $response['record']['goods_money']+$response['record']['express_money'])),
            array('title' => '买家实付金额', 'type' => 'html', 'field' => 'real_pay_money', 'html'=>sprintf("%.2f", $response['record']['paid_money']-$response['record']['point_fee']-$response['record']['alipay_point_fee']-$response['record']['coupon_fee'])),
            array('title' => '运费', 'type' => 'input', 'field' => 'express_money',),
            array('title' => '订单优惠', 'type' => 'html', 'field' => 'order_prefer', 'html'=>sprintf("%.2f", $response['record']['goods_money']+$response['record']['express_money'] - $response['record']['payable_money'])),
            array('title' => '商品总额', 'type' => 'label', 'field' => 'goods_money',),
            array('title' => '订单运费险', 'type' => 'label', 'field' => 'yfx_fee'),
            array('title' => '商品均摊总金额', 'type' => 'html', 'field' => 'invoice_title', 'html'=>sprintf("%.2f", $response['record']['payable_money']-$response['record']['express_money']-$response['record']['delivery_money'])),
            array('title' => '平台优惠信息', 'type' => 'html', 'field' => 'look_over','html'=>'<a href="###" onclick=event_details("'.$response['record']['deal_code_list'].'","'.$response['record']['sale_channel_code'].'") >查看</a>'),
             array('title' => '其他优惠金额','type' => 'html','field' => 'other_amount','html' => '<span id = "parent_span"><span class="like_link" id="'.$response['record']['sell_record_code'].'_i" onclick=edit_other_amount(this,"'.$response['record']['sell_record_code'].'","'.$response['record']['other_amount'].'")>'.$response['record']['other_amount'].'</span></span>'),       
        );
    if($response['record']['invoice_status'] == 1){
        $fields[] = ['title' => '开票金额', 'type' => 'label', 'field' => 'invoice_money'];
    }        
}else{
    $fields = array(
            array('title' => '订单应付款', 'type' => 'label', 'field' => 'payable_money'),
            array('title' => '订单已付款', 'type' => 'html', 'field' => 'paid_money', 'html'=>sprintf("%.2f", $response['record']['paid_money'])."（天猫积分：{$response['record']['point_fee']}，集分宝：{$response['record']['alipay_point_fee']}，抵用金额：{$response['record']['coupon_fee']}）"),
            array('title' => '订单总额', 'type' => 'html', 'field' => 'order_total_money', 'html'=>sprintf("%.2f", $response['record']['goods_money']+$response['record']['express_money'])),
            array('title' => '买家实付金额', 'type' => 'html', 'field' => 'real_pay_money', 'html'=>sprintf("%.2f", $response['record']['paid_money']-$response['record']['point_fee']-$response['record']['alipay_point_fee']-$response['record']['coupon_fee'])),
            array('title' => '运费', 'type' => 'input', 'field' => 'express_money',),
            array('title' => '订单优惠', 'type' => 'html', 'field' => 'order_prefer', 'html'=>sprintf("%.2f", $response['record']['goods_money']+$response['record']['express_money'] - $response['record']['payable_money'])),
            array('title' => '商品总额', 'type' => 'label', 'field' => 'goods_money',),
            array('title' => '订单运费险', 'type' => 'label', 'field' => 'yfx_fee'),
            array('title' => '商品均摊总金额', 'type' => 'html', 'field' => 'invoice_title', 'html'=>sprintf("%.2f", $response['record']['payable_money']-$response['record']['express_money']-$response['record']['delivery_money'])),
            array('title' => '平台优惠信息', 'type' => 'html', 'field' => 'look_over','html'=>'<a href="###" onclick=event_details("'.$response['record']['deal_code_list'].'","'.$response['record']['sale_channel_code'].'") >查看</a>'),      
        );
}


?>
        
<?php
render_control('FormTable', 'form2', array(
   
    'conf' => array(
        'fields' => $fields,
        'hidden_fields' => array(
            array('field' => 'sell_record_code','value'=>$response['record']['sell_record_code']),
        ),
    ),
    'act_edit'=>'',
    'col' => 2,
    'per' => '0.3',
    'buttons' => array(),
    'data' => $response['record'],
));
?>
<script>
    var record_code = "<?php echo $response['record']['sell_record_code'];?>";
    function event_details(deal_code_list,sale_channel_code) {
        var param = '&deal_code_list='+deal_code_list+'&sale_channel_code='+sale_channel_code+'';
        new ESUI.PopWindow("?app_act=oms/sell_record/event_details"+param, {
            title: "优惠信息",
            width: 800,
            height: 500,
            buttons:[
            {
                text:'关闭',
                elCls : 'button button-primary',
                handler : function(){
                  //do some thing
                    this.close();
                }
            }
            ],
            onBeforeClosed: function () {
            },
            onClosed: function () {
            }
        }).show();
    }
    //修改其他优惠金额
    function edit_other_amount(obj,sell_record_code,other_amount){
        $(obj).html('<input type="text" id="'+sell_record_code+'" value="'+other_amount+'" onblur = \"edit_amount(this,\''+sell_record_code+'\',\''+other_amount+'\');\">');
        $("#"+sell_record_code+'_i').removeAttr('onclick');
        $("#"+sell_record_code).keyup(function (event){
            if(event.keyCode == 13){
                $("#"+sell_record_code).removeAttr('onblur');
                edit_amount(this,sell_record_code,other_amount);
            }
        })
        $("#"+sell_record_code).focus();
    }
     //修改其他优惠金额
    function edit_amount(elm,sell_record_code,other_amount){
        var input_val = $(elm).val();//输入的金额
        if(input_val == ''){
            BUI.Message.Alert('其他优惠金额不能为空', 'error');
            $("#parent_span").html('<span class="like_link"  id="'+sell_record_code+'_i" onclick=edit_other_amount(this,"'+record_code+'","'+other_amount+'")>'+other_amount+'</span>');
            return;
        }
        if(input_val<0){
            BUI.Message.Alert('其他优惠金额必须大于0', 'error');
            $("#parent_span").html('<span class="like_link"  id="'+sell_record_code+'_i" onclick=edit_other_amount(this,"'+record_code+'","'+other_amount+'")>'+other_amount+'</span>');
             return;
        }
         var preg = /^[+]{0,1}(\d+)$|^[+]{0,1}(\d+\.\d+)$/;
         var reg = /(^[1-9]([0-9]+)?(\.[0-9]{1,2})?$)|(^(0){1}$)|(^[0-9]\.[0-9]([0-9])?$)/  //验证不为零开头的数字
        if(!preg.test(input_val)){
             BUI.Message.Alert('其他优惠金额必须为数字', 'error');
             $("#parent_span").html('<span class="like_link"  id="'+sell_record_code+'_i" onclick=edit_other_amount(this,"'+record_code+'","'+other_amount+'")>'+other_amount+'</span>');
             return;
         }
         if(!reg.test(input_val)){
             BUI.Message.Alert('正数不能以0开头,并且小数点后不能超过两位', 'error');
             $("#parent_span").html('<span class="like_link"  id="'+sell_record_code+'_i" onclick=edit_other_amount(this,"'+record_code+'","'+other_amount+'")>'+other_amount+'</span>');
             return;
         }
         if(input_val != other_amount){
            var params = {sell_record_code: sell_record_code,other_money:input_val};
            var url = "?app_act=oms/invoice/order_invoice/edit_other_amount";
             $.post(url,params, function(ret) {
                      var type = ret.status == 1 ? 'success' : 'error';
                        if (type == 'success') {
                            BUI.Message.Alert('修改成功',type);
                           $("#parent_span").html('<span class="like_link" id="'+sell_record_code+'_i" onclick=edit_other_amount(this,"'+record_code+'","'+input_val+'")>'+input_val+'</span>');
                        } else {
                            BUI.Message.Alert(ret.message, type);
                            $("#parent_span").html('<span class="like_link" id="'+sell_record_code+'_i" onclick=edit_other_amount(this,"'+record_code+'","'+other_amount+'")>'+other_amount+'</span>');
                        }
                        tableStore.load();
            }, "json");
        }
        $("#parent_span").html('<span class="like_link" id="'+sell_record_code+'_i" onclick=edit_other_amount(this,"'+record_code+'","'+other_amount+'")>'+other_amount+'</span>');
    }
</script>