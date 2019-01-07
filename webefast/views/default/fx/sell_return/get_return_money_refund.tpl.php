<style>
.num_red{color:red;}
</style>
<?php
$baseinfo = $response['data']['baseinfo'];
$ytk = $baseinfo['seller_express_money']+$baseinfo['compensate_money']+$baseinfo['adjust_money'];
$total_return_money_str = "实际退款总金额（<span class='num_red'>{$ytk}</span>）= 卖家承担运费（<span class='num_red'>{$baseinfo['seller_express_money']}</span>）+赔付金额（<span class='num_red'>{$baseinfo['compensate_money']}</span>）+手工调整金额（<span class='num_red'>{$baseinfo['adjust_money']}</span>）";

render_control('FormTable', 'return_money_form', array(
    'conf' => array(
        'fields' => array(
            array('title' => '赔付金额', 'type' => 'input', 'field' => 'compensate_money'),
            array('title' => '手工调整金额', 'type' => 'input', 'field' => 'adjust_money'),
            array('title' => '卖家承担运费', 'type' => 'input', 'field' => 'seller_express_money'),
            array('title' => '实际退款总金额（退单）', 'type' => 'html', 'html' => $total_return_money_str),
        ),
        'hidden_fields' => array(
            array('field' => 'sell_return_code','value'=>$response['data']['sell_return_code']),
        ),
    ),
    'act_edit'=>'fx/sell_return/do_edit&app_fmt=json',
    'col' => 2,
    'per' => '0.3',
    'buttons' => array(),
    'data' => $response['data']['return_money'],
));
?>

