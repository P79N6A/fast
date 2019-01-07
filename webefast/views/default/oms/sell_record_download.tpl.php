<form id="searchForm" class="form-horizontal well " tabindex="0" style="outline: none;">
    <div class="row">
        <div class="control-group span8">
            <label class="control-label">销售平台</label>
            <div class="controls">
                <select id="source" name="source" class="field">
                    <option value="9">淘宝</option>
                </select></div>
        </div>
        <div class="control-group span8">
            <label class="control-label">店铺</label>
            <div class="controls">
                <select id="shop_code" name="shop_code" class="field">
                    <?php $list = $response['arr_shop']; foreach($list as $k=>$v){ ?>
                        <option value="<?php echo $v['shop_code']?>"><?php echo $v['shop_name']?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="control-group span8">
            <label class="control-label">交易状态</label>
            <div class="controls">
                <!--<select id="status" name="status" class="field">
                    <option value="">全部</option>
                    <option value="TRADE_NO_CREATE_PAY">没有创建支付宝交易</option>
                    <option value="WAIT_BUYER_PAY">等待买家付款</option>
                    <option value="SELLER_CONSIGNED_PART">卖家部分发货</option>
                    <option value="WAIT_SELLER_SEND_GOODS">等待卖家发货</option>
                    <option value="WAIT_BUYER_CONFIRM_GOODS">等待买家确认收货</option>
                    <option value="TRADE_BUYER_SIGNED">买家已签收</option>
                    <option value="TRADE_FINISHED">交易成功</option>
                    <option value="TRADE_CLOSED">付款后交易关闭</option>
                    <option value="TRADE_CLOSED_BY_TAOBAO">付款前交易关闭</option>
                    <option value="WAIT_SELLER_AGREE">等待卖家同意</option>
                </select>-->
                <span id="status_multi"><input type="hidden" id="status" value="" name="status"></span>
            </div>
        </div>
        <div class="control-group span8">
            <label class="control-label">下单时间</label>
            <div class="controls">
                <input type="text" name="created_min" id="created_min" class="input-normal calendar" value="<?php echo date('Y-m-d', strtotime(date('Y-m-d'))- (24*60*60*7))?>"/>
                ~
                <input type="text" name="created_max" id="created_max" class="input-normal calendar" value="<?php echo date('Y-m-d')?>"/>
                <label class="remark" for="created_max"></label>
            </div>
        </div>
    </div>
    <div class="row text-center">
        <button type="button" class="button button-primary" id="btn_download">下载并转单</button>
    </div>
</form>

<div id="loading" style="text-align: center; display: none;">
    <div>
        <img src="assets/images/loadingGray.gif">
    </div>

    <div>
        订单下载中...
    </div>
</div>
<div id="result" style="display: none;">

</div>
<script>
    //$.post("?app_act=oms/sell_record/download_action", {}, function(data){
    //BUI.Message.Alert(data.message, 'success');
    //ui_closePopWindow("<?php echo $request['ES_frmId']?>");
    //$("#loading").html("下载完成")
    //}, "json")

    <?php
        $items = array(array('text'=>'全部', 'value'=>''));
        $statuses = load_model('oms/TaobaoRecordModel')->status;
        foreach($statuses as $k=>$v){
            $items[] = array('value'=>$k, 'text'=>$v);
        }
    ?>
    var status_select = new BUI.Select.Select({
        render:'#status_multi',
        valueField:'#status',
        multipleSelect:true,
        items:<?php echo json_encode($items)?>
    });
    status_select.render()
    status_select.setSelectedValue('');

    $("#btn_download").click(function(){
        $("#loading").show()
        $("#result").hide()

        var params = {
            "source": $("#source").val(),
            "shop_code": $("#shop_code").val(),
            "status": $("#status").val(),
            "created_min": $("#created_min").val(),
            "created_max": $("#created_max").val()
        }

        $.post("?app_act=oms/sell_record/download_action", params, function(data){
            $("#loading").hide()
            $("#result").show()
            $("#result").html("下载:<br>"+data.down.message+"<br>转入:<br>"+data.tran.message)
        }, "json")
    })

    BUI.use('bui/calendar',function(Calendar){
        new Calendar.DatePicker({
            trigger:'#created_min',
            autoRender : true
        });
        new Calendar.DatePicker({
            trigger:'#created_max',
            autoRender : true
        });
    });
</script>