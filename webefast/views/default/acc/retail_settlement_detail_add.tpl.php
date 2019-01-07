<style>
    .form-horizontal .control-label{
        width:200px;
    }    
    .form-horizontal .row .control-group{
        width:100%;
    }    
</style>
<?php
render_control('PageHead', 'head1', array('title' => '网络订单应收明细',
    'links' => array(
    ),
    'ref_table' => 'table'
));
render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => array(
            array('title' => '销售平台', 'type' => 'select', 'field' => 'sale_channel_code','data'=>  load_model('base/SaleChannelModel')->get_select()),
            array('title' => '所属店铺', 'type' => 'select', 'field' => 'shop_code','data'=>ds_get_select("shop")),
            //array('title' => '交易号', 'type' => 'input', 'field' => 'deal_code','remark' => '交易号必须在系统中存在，否则不能添加'),
            array('title' => '交易号', 'type' => 'input', 'field' => 'deal_code','remark' => '交易号必须在系统中存在，否则不能添加'),
            array('title' => '支付宝交易号', 'type' => 'input', 'field' => 'alipay_no'),
            array('title' => '单据性质', 'type' => 'html', 'field' => 'order_attr','html'=>'调整'),
            array('title' => '订(退)单号', 'type' => 'input', 'field' => 'sell_record_code'),
            array('title' => '结算类型', 'type' => 'html', 'field' => 'settle_type','html'=>'调整'),
            array('title' => '调整金额', 'type' => 'input', 'field' => 'adjust_money'),
            array('title' => '调整备注', 'type' => 'textarea', 'field' => 'adjust_remark'),
        ),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'acc/retail_settlement_detail/do_edit', //edit,add,view
    'act_add' => 'acc/retail_settlement_detail/do_add',
    'rules' => array(
        array('sale_channel_code', 'require'),
        array('shop_code', 'require'),
        array('deal_code', 'require'),
        array('order_attr', 'require'),
        array('adjust_money', 'require'),
    ),
));
?>


