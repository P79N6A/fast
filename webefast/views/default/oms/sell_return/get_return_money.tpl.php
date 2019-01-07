<style>
    .num_red{color:red;}
</style>
<?php
$baseinfo = $response['data']['baseinfo'];
$change_ysje = number_format(($baseinfo['change_express_money'] + $baseinfo['change_avg_money']), 3, '.', '');
$ytk = $baseinfo['should_refunds'];
$ytk_str = "实际退款总金额（<span class='num_red'>{$ytk}</span>）= 退单商品实际应退款（<span class='num_red'>{$baseinfo['return_avg_money']}</span>）+ 卖家承担运费（<span class='num_red'>{$baseinfo['seller_express_money']}</span>）+赔付金额（<span class='num_red'>{$baseinfo['compensate_money']}</span>）+手工调整金额（<span class='num_red'>{$baseinfo['adjust_money']}</span>）";
$total_return_money = bcadd($ytk, -$change_ysje, 3);
$return_refun_money_str = "退单应退款（<span class='num_red'>{$ytk}</span>）=
    退单商品实际应退款（<span class='num_red'>{$baseinfo['return_avg_money']}</span>）+ 
    卖家承担运费（<span class='num_red'>{$baseinfo['seller_express_money']}</span>）+ 
    赔付金额（<span class='num_red'>{$baseinfo['compensate_money']}</span>）+ 
    手工调整金额（<span class='num_red'>{$baseinfo['adjust_money']}</span>）";
$total_return_money_str = "
    实际退款总金额（<span class='num_red'>{$total_return_money}</span>）= 
    退单应退款（<span class='num_red'>{$ytk}</span>）- 
    换货单应收款（<span class='num_red'>{$change_ysje}</span>）";
if (load_model('sys/PrivilegeModel')->check_priv('oms/sell_return/update_abjust_money') && $baseinfo['return_order_status'] != 0) {
    $abjust_money_str = "<input type='text' id = 'abjust_money' value = '{$baseinfo['adjust_money']}'/>&nbsp;<input type = 'button' class='button' id = 'update_abjust_money' value = '保存'>";
    $d = array('title' => '手工调整金额', 'type' => 'html', 'html' => $abjust_money_str);
} else {
    $d = array('title' => '手工调整金额', 'type' => 'input', 'field' => 'adjust_money');
}
$fields = array(
    array('title' => '赔付金额', 'type' => 'input', 'field' => 'compensate_money'),
    array('title' => '换货单运费', 'type' => 'input', 'field' => 'change_express_money'),
    array('title' => '卖家承担运费', 'type' => 'input', 'field' => 'seller_express_money'),
    array('title' => '换货单商品实际应收款', 'type' => 'label', 'field' => 'change_avg_money'),
    $d,
    array('title' => '换货单应收款', 'type' => 'html', 'html' => $change_ysje),
    array('title' => '退单应退款', 'type' => 'html', 'html' => $return_refun_money_str),
    array('title' => '实际应退款总额', 'type' => 'html', 'html' => $total_return_money_str),
);
if ($response['data']['is_fenxiao'] == 2) {
    $fx_refun_money = number_format(($baseinfo['fx_payable_money'] + $baseinfo['fx_express_money']), 2, '.', '');
    $fx_payable_money_sum = number_format(($fx_refun_money - $baseinfo['change_fx_amount']), 2, '.', '');
    $fx_refun_money_htm = "分销商应退款（<span class='num_red'>{$fx_refun_money}</span>）= 商品结算金额（<span class='num_red'>{$baseinfo['fx_payable_money']}</span>）+ 分销结算运费（<span class='num_red'>{$baseinfo['fx_express_money']}</span>） ";
    $fx_payable_money_html = "分销商应退款总额（<span class='num_red'>{$fx_payable_money_sum}</span>）= 分销商应退款（<span class='num_red'>{$fx_refun_money}</span>）- 分销换货单应收款（<span class='num_red'>{$baseinfo['change_fx_amount']}</span>） ";

    $fields[] = array('title' => '分销结算运费', 'type' => 'input', 'field' => 'fx_express_money');
    $fields[] = array('title' => '商品结算金额', 'type' => 'label', 'field' => 'fx_payable_money');
    $fields[] = array('title' => '分销商应退款', 'type' => 'html', 'html' => $fx_refun_money_htm);
    $fields[] = array('title' => '分销商应退款总额', 'type' => 'html', 'html' => $fx_payable_money_html);
}
render_control('FormTable', 'return_money_form', array(
    'conf' => array(
        'fields' => $fields,
        'hidden_fields' => array(
            array('field' => 'sell_return_code', 'value' => $response['data']['sell_return_code']),
        ),
    ),
    'act_edit' => 'oms/sell_return/do_edit&app_fmt=json',
    'col' => 2,
    'per' => '0.3',
    'buttons' => array(),
    'data' => $response['data']['return_money'],
));
?>
<script>
    $(function () {
        $('#abjust_money').blur(function () {
            update_abjust_money();
        });
        $('#update_abjust_money').click(function () {
            update_abjust_money();
        });
    });

    function update_abjust_money() {
        var adjust_money = $("#abjust_money").val();
        if (isNaN(adjust_money)) {
            alert("必须是数字");
            return false;
        }
        if (adjust_money != <?php echo $baseinfo['adjust_money']; ?>) {
            BUI.Message.Show({
                title: '提示',
                msg: '收货后修改手工调整金额，仅影响售后服务单中金额，不影响网络应收明细金额。',
                icon: 'question',
                buttons: [
                    {
                        text: '确定',
                        elCls: 'button button-primary',
                        handler: function () {
                            $.post(
                                    "?app_act=oms/sell_return/update_abjust_money&app_fmt=json",
                                    {adjust_money: adjust_money, sell_return_code:<?php echo $baseinfo["sell_return_code"]; ?>},
                                    function (ret) {
                                        if (ret.status == 1) {
                                            BUI.Message.Alert('保存成功', 'success');
                                            location.reload();
                                        } else {
                                            BUI.Message.Alert('保存失败', 'error');
                                        }
                                    }, 'json');
                            this.close();
                        }
                    },
                    {
                        text: '取消',
                        elCls: 'button',
                        handler: function () {
                            this.close();
                        }
                    }
                ]
            });

        }
    }
</script>