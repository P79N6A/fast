<?php echo load_js('clipboard.min.js');?>
<?php
foreach ($response['record']['sell_return_codes'] as $sell_return_code) {
    $sell_return_codes_html .= "<a href='?app_act=oms/sell_return/after_service_detail&sell_return_code={$sell_return_code}'>{$sell_return_code}</a>, ";
}
$sell_return_codes_html = substr($sell_return_codes_html, 0, -2);
$sell_record_html = '';
if(!empty($response['record']['problem_desc'])){
    $sell_record_html .= '<span id="show_problem" style="color:red">'.$response['record']['sell_record_code'].'&nbsp;(问题单:'.$response['record']['problem_desc'].')</span>';
}elseif($response['record']['is_pending']>0){
    $sell_record_html .= '<span style="color:red">'.$response['record']['sell_record_code'].'&nbsp;(挂起单:'.$response['record']['is_pending_desc'].')</span>';
}else{
    $sell_record_html .= $response['record']['sell_record_code'];
}

$name_arr = $response['record'];
safe_data($name_arr);
if($response['record']['is_fenxiao'] == 1 || $response['record']['is_fenxiao'] == 2) {
    $buyer_name = "<span id = 'buyer_name'>".$name_arr['buyer_name'] ."</span>&nbsp;&nbsp;分销商：".$response['record']['fenxiao_name'];
} else {
    $buyer_name = "<span>".$name_arr['buyer_name'] ."</span>";
}
if($response['record']['sale_channel_code'] == 'taobao') {
    $wangwang_html = '<span class="wangwang"><span class="ww-light ww-small"><a href="javascript:launch_ww(' . "'{$response['record']['sell_record_code']}'" . ')" class="ww-inline ww-online" title="点此可以直接和买家交流。"><span>旺旺在线</span></a></span></span>';
} else {
    $wangwang_html = '';
}
$sell_record_html .= ($response['record']['is_handwork']>0?'<span style="color:red">&nbsp;&nbsp;【手工单】</span>':'');
//if($response['record']['seller_flag']>0 && $response['record']['sale_channel_code']=='taobao'){
//    $response['record']['seller_remark'] .= "&nbsp;<img src='assets/img/taobao/op_memo_".$response['record']['seller_flag'].".png'/>";
//}
render_control('FormTable', 'form1', array(
    'conf' => array(
        'fields' => array(
            array('title' => '订单编号', 'type' => 'html', 'field' => 'sell_record_code', 'html'=>$sell_record_html."&nbsp;&nbsp;&nbsp;<a id='copy_record' data-clipboard-text= '".$response['record']['sell_record_code']."' style='color: #428bca; font-size: 11px;'>复制</a>"),
            array('title' => '订单状态', 'type' => 'html', 'field' => 'status', 'html'=>$response['record']['status'].(($response['record']['is_lock']>0)?'<img src="assets/img/sys/sell_record_lock.png"/> 锁定人:'.$response['record']['is_lock_person_name']:'')),
            array('title' => '订单来源', 'type' => 'html', 'field' => 'sale_channel_name', 'html'=>"<div style='word-break:break-all;'>".$response['record']['sale_channel_name'].($response['record']['is_jhs']>0?"<img src='assets/img/sys/order_jhs.png'/>":'').($response['record']['is_wap']>0?"<img src='assets/img/sys/order_wap.png'/>":'')."&nbsp;&nbsp;交易号: ".$response['record']['deal_code_list']."&nbsp;&nbsp;&nbsp;<a data-clipboard-text= '".$response['record']['deal_code_list']."' style='color: #428bca; font-size: 11px;' id='copy_deal'>复制</a></div>"),
            array('title' => '下单时间', 'type' => 'label', 'field' => 'record_time'),
            array('title' => '店铺', 'type' => 'label', 'field' => 'shop_name'),
            array('title' => '支付方式', 'type' => 'label', 'field' => 'pay_name', 'data'=>  ds_get_select("pay_type")),
            array('title' => '会员昵称', 'type' => 'html', 'field' => 'buyer_name_custom','html'=>$buyer_name .  $wangwang_html),
            array('title' => '支付类型', 'type' => 'label', 'field' => 'pay_type_name', 'data'=> ds_get_select_by_field("pay_type")),
            array('title' => '买家留言', 'type' => 'label', 'field' => 'buyer_remark'),
            array('title' => '支付宝交易号', 'type' => 'label', 'field' => 'alipay_no'),
            array('title' => '商家留言', 'type' => 'textarea', 'field' => 'seller_remark'),
            array('title' => '支付时间', 'type' => 'html', 'field' => 'pay_time','html'=>$response['record']['pay_time']==0?'':$response['record']['pay_time']),
            array('title' => '订单回写', 'type' => 'html', 'field' => 'is_back','html'=>$response['record']['is_back_txt'].(($response['record']['is_back'] == -1)?'<span style="color:red">('.$response['record']['is_back_reason'].')</span>':'')),
            array('title' => '计划发货时间', 'type' => 'html', 'field' => 'plan_send_time','html'=>$response['record']['plan_send_time']==0?'':$response['record']['plan_send_time']),
            array('title' => '退单编号', 'type' => 'html', 'field' => 'sell_return_codes','html'=>$sell_return_codes_html),
            array('title' => '转单时间', 'type' => 'html', 'field' => 'create_time','html'=>$response['record']['create_time']==0?'':$response['record']['create_time']),
        ),
        'hidden_fields' => array(
            array('field' => 'sell_record_code','value'=>$response['record']['sell_record_code']),
        ),
    ),
    'act_edit' => '',
    'col' => 2,
    'per' => '0.3',
    'buttons' => array(),
    'data' => $response['record'],
));
?>
<?php if(!empty($response['record']['problem_desc'])): ?>
<script>
    $("#show_problem").click(function(){
        new ESUI.PopWindow("?app_act=oms/sell_record/problem_list&sell_record_code=<?php echo $response['record']['sell_record_code']; ?>", {
            title: '订单问题详情',
            width:900,
            height:400,
            onBeforeClosed: function() {
                 
            }
        }).show();
    });
</script>
<?php endif; ?>

<script>
$(document).ready(function(){
    var clipboard1 = new ClipboardJS('#copy_record');
    clipboard1.on('success', function(e) {
        BUI.Message.Tip("复制成功",'success');
    });
    clipboard1.on('error', function(e) {
        BUI.Message.Tip("复制失败",'success');
    });
    
    var clipboard2 = new ClipboardJS('#copy_deal');
    clipboard2.on('success', function(e) {
        BUI.Message.Tip("复制成功",'success');
    });
    clipboard1.on('error', function(e) {
        BUI.Message.Tip("复制失败",'success');
    });
})
function launch_ww(record_code){
    var url = "?app_act=oms/sell_record/link_wangwang&record_code="+record_code;
    window.open(url);
}
</script>