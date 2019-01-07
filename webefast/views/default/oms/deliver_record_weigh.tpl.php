<style>
    .panel-body table {margin: 0; }
    .form-horizontal .panel1 .control-label {width: auto;}
    .form-horizontal .panel2 .control-text {width: 240px;}
    .span8 { width: auto; }
</style>

<?php render_control('PageHead', 'head1',
    array('title'=>'扫描验货',
        'links'=>array(
        ),
        'ref_table'=>'table'
    ));?>

<form class="form-horizontal">
    <div class="panel panel1">
        <div class="panel-body">
            <div id="searchAdv">
                <div class="row">
                    <div class="control-group span8">
                        <label class="control-label" style="padding-top:7px">连接称重器</label>
                        <div class="controls">
                            <div class="button-group" id="b1" style="margin: 9px 0;">
                            </div>
                        </div>
                    </div>
                    <div class="control-group span8">
                        <label class="control-label" style="padding-top:7px">称重后计算运费</label>
                        <div class="controls">
                            <div class="button-group" id="b2" style="margin: 9px 0;">
                            </div>
                            <div class="button-group" id="delay_seconds_panel">
                                <input type="text" id="delay_seconds" value="2" style="width: 20px; text-align: center">
                                秒后自动计算运费
                            </div>
                        </div>
                    </div>
                    <div class="control-group span8">
                        <label class="control-label" style="padding-top:7px">称重完成后</label>
                        <div class="controls">
                            <div class="button-group" id="b3" style="margin: 9px 0;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel2">
        <div class="panel-body">
            <div class="row">
                <div class="control-group">
                    <label class="control-label">物流单号:</label>
                    <div class="controls">
                        <input type="text" class="control-text" id="express_no">
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">订单号:</label>
                    <div class="controls">
                        <input type="text" class="control-text" id="sell_record_code" disabled>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">实际重量:</label>
                    <div class="controls">
                        <input type="text" class="control-text" id="real_weight"> 千克
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">运费:</label>
                    <div class="controls">
                        <input type="text" class="control-text" id="express_money"> 元
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">理论重量:</label>
                    <div class="controls">
                        <input type="text" class="control-text" id="theoretical_weight" disabled> 千克
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">重量差异:</label>
                    <div class="controls">
                        <input type="text" class="control-text" id="weight_difference" disabled> 千克
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">　</label>
                    <div class="controls">
                        <button type="button" class="button" id="btn-count">计算运费</button>
                        <button type="button" class="button" id="btn-submit">确认称重</button>
                        <button type="button" class="button" id="btn-clear">清除扫描记录</button>
                        <span id="msg" style="color: #ff0000; font-weight: bold;"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    var deliver_record_id = 0
    var connectionWeighingScales = 0
    var autoCountShippingFee = 1
    var autoConfirm = 1

    $(document).ready(function() {
        BUI.use('bui/toolbar',function(Toolbar){
            //连接称重器
            var g1 = new Toolbar.Bar({
                elCls : 'button-group',
                itemStatusCls  : {
                    selected : 'active' //选中时应用的样式
                },
                defaultChildCfg : {
                    elCls : 'button button-small',
                    selectable : true //允许选中
                },
                children : [
                    {content : '连接',id:'1'},
                    {content : '不连接',id:'0',selected : true}
                ],
                render : '#b1'
            })
            g1.render()
            g1.on('itemclick',function(ev){
                connectionWeighingScales = ev.item.get('id')
            })

            //称重后计算运费
            var g2 = new Toolbar.Bar({
                elCls : 'button-group',
                itemStatusCls  : {
                    selected : 'active' //选中时应用的样式
                },
                defaultChildCfg : {
                    elCls : 'button button-small',
                    selectable : true //允许选中
                },
                children : [
                    {content : '手工计算', id:'0'},
                    {content : '自动计算', id:'1', selected : true}
                ],
                render : '#b2'
            })
            g2.render();
            g2.on('itemclick',function(ev){
                autoCountShippingFee = ev.item.get('id')
                if(autoCountShippingFee == 0) {
                    $("#delay_seconds_panel").hide()
                } else {
                    $("#delay_seconds_panel").show()
                }
            })

            //称重完成后是否自动确认
            var g3 = new Toolbar.Bar({
                elCls : 'button-group',
                itemStatusCls  : {
                    selected : 'active' //选中时应用的样式
                },
                defaultChildCfg : {
                    elCls : 'button button-small',
                    selectable : true //允许选中
                },
                children : [
                    {content : '手工确认', id:'0'},
                    {content : '自动确认', id:'1', selected : true}
                ],
                render : '#b3'
            })
            g3.render();
            g3.on('itemclick',function(ev){
                autoConfirm = ev.item.get('id')
            })
        })

        //
        $("#express_no").keyup(function(event){
            if(event.keyCode == 13) {
                var p = {express_no: $(this).val()}
                $.post("?app_act=oms/deliver_record/weigh_detail", p, function(data){
                    if(data.status == 1) {
                        deliver_record_id = data.record.deliver_record_id
                        $("#express_no").attr("disabled", true)
                        $("#sell_record_code").val(data.record.record_code)
                        $("#theoretical_weight").val(data.record.goods_weigh)
                        $("#btn-count").removeAttr("disabled")
                        $("#btn-submit").removeAttr("disabled")
                        $("#btn-clear").removeAttr("disabled")
                        $("#msg").html("")
                        $("#real_weight").focus()
                        if(connectionWeighingScales == 1){
                            //FIXME: 连接稳重器
                        }

                        //返回订单明细

                    } else {
                        $("#msg").html(data.message)
                        $("#express_no").val("")
                    }
                }, "json")
            }
        })

        $("#real_weight").keyup(function(event){
            if(event.keyCode == 13) {
                var a = parseFloat($(this).val())
                var b = parseFloat($("#theoretical_weight").val())
                $("#weight_difference").val(a-b)

                if(autoCountShippingFee == 1) {
                    //FIXME: 自动计算运费
                    count_fee()
                } else {
                    $("#express_money").focus()
                }
            }
        })

        $("#express_money").keyup(function(event) {
            if (event.keyCode == 13) {
                //是否自动确认
                if(autoConfirm == 1){
                    submit_it()
                }
            }
        })

        $("#btn-count").click(function() {
            count_fee()
        })

        $("#btn-submit").click(function() {
            submit_it()
        })

        //
        $("#btn-clear").click(function(){
            deliver_record_id = 0
            $("#express_no").val("")
            $("#express_no").removeAttr("disabled")
            $("#sell_record_code").val("")
            $("#real_weight").val("")
            $("#express_money").val("")
            $("#theoretical_weight").val("")
            $("#weight_difference").val("")

            $("#btn-count").attr("disabled", true)
            $("#btn-submit").attr("disabled", true)
            $("#btn-clear").attr("disabled", true)
            $("#express_no").focus()
        })


        $("#btn-clear").click()
    })

    function count_fee() {
        var t = parseInt($("#delay_seconds").val())*1000;
        setTimeout(function(){
            var params = {}
            $.post("?app_act=oms/deliver_record/auto_shipping_fee", params, function(data){
                if(data.status != 1){
                    $("#msg").html(data.message)
                } else {
                    //是否自动确认
                    if(autoConfirm == 1){
                        submit_it()
                    }
                }
            }, "json");
        }, t)
    }

    function submit_it() {
        var params = {
            deliver_record_id: deliver_record_id,
            real_weight: $("#real_weight").val(),
            express_money: $("#express_money").val()
        }
        $.post("?app_act=oms/deliver_record/weigh_action", params, function(data){
            if(data.status != 1){
                $("#msg").html(data.message)
            } else {
                $("#btn-clear").click()
                $("#msg").html("称重成功, 请继续")
            }
        }, "json");
    }
</script>