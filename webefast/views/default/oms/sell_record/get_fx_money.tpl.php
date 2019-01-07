<style type="text/css">
    #pay_money_handle_detail td{text-align:center;}
    
</style>
<!--
<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="12%" align="right">订单结算总金额：</td>
        <td width="38%"><?php  #echo sprintf("%.2f", $response['record']['fx_payable_money']);?> （商品结算额+运费结算）</td>
        <td width="12%" align="right">商品结算金额：</td>
        <td width="38%"><?php #echo sprintf("%.2f", $response['record']['fx_payable_money']);?></td>
    </tr>
    <tr>
        <td align="right">运费结算：</td>
        <td><?php  #echo sprintf("%.2f", $response['record']['fx_express_money']);?></td>
        <td align="right"></td>
        <td></td>
    </tr>
</table>-->

<?php
render_control('FormTable', 'form3', array(
    'conf' => array(
        'fields' => array(
            array('title' => '订单结算总金额', 'type' => 'label', 'field' => 'fx_payable_money'),
            array('title' => '订单总额', 'type' => 'html', 'field' => 'order_total_money', 'html'=>sprintf("%.2f", $response['record']['fx_payable_money']+$response['record']['fx_express_money'])."（订单结算总金额+运费结算）"),
            array('title' => '分销结算运费', 'type' => 'input', 'field' => 'fx_express_money',),
            array('title' => '', 'type' => '', 'field' => '',),
        ),
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


<table cellspacing="0" class="table table-bordered" id="pay_money_handle_detail">
    <tr>
        <td width="10%" rowspan="<?php if(!empty($response['fx_payment_money_detail'])){echo (count($response['fx_payment_money_detail'])+1);} else {echo 2;}?>">支付详情</td>
        <td width="10%">流水号</td>
        <td width="8%">摘要</td>
        <td width="10%">创建时间</td>
        <td width="9%">金额</td>
        <td width="7%">状态</td>
        <td width="10%">创建人</td>
        <td width="15%">备注</td>
    </tr>
    <?php if(!empty($response['fx_payment_money_detail'])){?>
        <?php foreach ($response['fx_payment_money_detail'] as $key => $detail){?>
        <tr>
            <td><?php echo $detail['serial_number']?></td>
            <td><?php echo $detail['abstract']?></td>
            <td><?php echo date('Y-m-d H:i:s', $detail['create_time']); ?></td>
            <td><?php echo $detail['money']?></td>
            <td><?php echo $detail['status_str']?></td>
            <td><?php echo $detail['operator']?></td>
            <td><?php echo $detail['remark']?></td>
        </tr>
        <?php }?>
    <?php } else {?>
        <tr><td colspan="7">无结算记录......</td></tr>
    <?php }?>
</table>