<style>
    .form-horizontal .control-label{
        width:110px;
    }
</style>
<?php
$op = array(
    'conf' => array(
        'fields' => array(
            array('title' => '单据编号', 'type' => 'input', 'field' => 'return_package_code'),
            array('title' => '业务日期', 'type' => 'date', 'field' => 'stock_date'),
            array('title' => '退货仓库', 'type' => 'select', 'field' => 'store_code', 'data' =>  ds_get_select("store")),
            array('title' => '退货库位', 'type' => 'select', 'field' => '', 'data' => array()),
            array('title' => '退货人姓名', 'type' => 'input', 'field' => 'return_name'),
            array('title' => '退货人手机', 'type' => 'input', 'field' => 'return_telephone'),
            array('title' => '退货地址', 'type' => 'html', 'field' => 'return_address', 'html'=> $response['address_html']),
            array('title' => '快递公司', 'type' => 'select', 'field' => 'return_express_code', 'data' => ds_get_select("express_company")),
            array('title' => '快递单号', 'type' => 'input', 'field' => 'return_express_no'),
            array('title' => '关联退单号', 'type' => 'select_pop', 'field' => 'sell_return_code', 'select'=>'oms/sell_return', 'span'=>'4', 'eventtype'=>'custom'),
            array('title' => '交易号', 'type' => 'input', 'field' => 'deal_code', 'readonly'=>1),
            array('title' => '关联订单号', 'type' => 'select_pop', 'field' => 'sell_record_code', 'select'=>'oms/sell_record', 'span'=>'4', 'eventtype'=>'custom'),
            
            array('title' => '备注', 'type' => 'input', 'field' => 'buyer_remark'),
        ),
    ),
    'act_add' => '',
    'act_edit' => '',
    'col' => 2,
    'buttons' => array(),
    'rules' => array(
        array('stock_date', 'require'),
	array('store_code','require'),
        array('return_express_code', 'require'),
	array('return_express_no','require'),
    ),
);
if($app['scene']=='add'){
    $op['buttons'] = array(
        array('label'=>'提交', 'type'=>'submit'),
	array('label'=>'返回', 'type'=>'button'),
    );
}else{
    $op['data'] = $response['record'];
}
render_control('Form', 'form1', $op);
?>
<script>
    var selectPopWindow = {
    dialog: null,
    callback: function (value, id, code, name) {
        var returnArr = [], recordArr = [], dealArr=[];
        for (var i = 0; i < value.length; i++) {
            returnArr.push(value[i][id]);
            recordArr.push(value[i][code]);
            dealArr.push(value[i][name]);
        }
        $('#sell_return_code_select_pop').val(returnArr.join(','));
        $('#sell_return_code').val(returnArr.join(','));
        $('#sell_record_code_select_pop').val(recordArr.join(','));
        $('#sell_record_code').val(recordArr.join(','));
        $('#deal_code').val(dealArr.join(','));
        if (selectPopWindow.dialog != null) {
            selectPopWindow.dialog.close();
        }
    }
};
$('#sell_return_code_select_pop,#sell_return_code_select_img').click(function() {
    selectPopWindow.dialog = new ESUI.PopSelectWindow('?app_act=common/select/sell_return', 'selectPopWindow.callback', {title: '关联退单号', width: 900, height:500, ES_pFrmId:'<?php echo $request['ES_frmId'];?>'}).show();
});
$('#sell_record_code_select_pop,#sell_record_code_select_img').click(function() {
    selectPopWindow.dialog = new ESUI.PopSelectWindow('?app_act=common/select/sell_record', 'selectPopWindow.callback', {title: '关联订单号', width: 900, height:500, ES_pFrmId:'<?php echo $request['ES_frmId'];?>'}).show();
});
</script>



