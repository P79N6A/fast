<style>
    #form_plan{margin-top: 5px;}
    #form_plan .well{padding: 20px;}
    #form_plan .control-label {width:120px;}
    #form_plan .controls input[type="text"]{width: 200px;}
    #form_plan .well .row{margin-left: 20px;}
    #form_plan .well .row:not(:first-child){margin-top: 15px;}
    #form_plan .control-group{width:70%}
    #form_plan button{margin-left: 20px;}
    #form_plan .auxiliary-text{margin-left:5px;}
    #form_plan #plan_shop input{width:185px}
</style>
<form class="form-horizontal" id="form_plan" action="?app_act=op/presell/do_add" method="post">
    <div class="well">
        <div class="row">
            <div class="control-group">
                <label class="control-label">预售编码：<b style="color:red"> *</b></label>
                <div class="controls">
                    <input type="hidden" value="<?php echo $response['plan_code'] ?>" name="plan_code"/>
                    <input type="text" readonly="readonly" disabled="disabled" id="plan_code" class="input-normal control-text" value="<?php echo $response['plan_code'] ?>">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group">
                <label class="control-label">预售名称：<b style="color:red"> *</b></label>
                <div class="controls">
                    <input type="text" id="plan_name" name="plan_name" class=" control-text"  data-rules="{required:true}" data-messages="{required:'请输入预售名称'}" data-tip='{"text":"预售名称"}'>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group">
                <label class="control-label">预售开始时间：<b style="color:red"> *</b></label>
                <div class="controls">
                    <input id="start_time" type="text" name="start_time" class="input-normal calendar calendar-time bui-form-field-time bui-form-field" data-rules="{required:true}" data-messages="{required:'请选择开始时间'}" data-tip='{"text":"开始时间"}'>
                    <span class="auxiliary-text"><i>预售开始，商品由正常商品更新为预售商品，通过预售计划同步预售库存</i></span>
                    <!--<img height="23" width="23" src="assets/images/tip.png" class="tip" title="预售开始，商品由正常商品更新为预售商品，通过预售计划同步预售库存。" />-->
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group">
                <label class="control-label">预售结束时间：<b style="color:red"> *</b></label>
                <div class="controls">
                    <input id="end_time" type="text" name="end_time" class="input-normal calendar calendar-time bui-form-field-time bui-form-field calendar-end-time" data-rules="{required:true}" data-messages="{required:'请选择结束时间'}" data-tip='{"text":"结束时间"}'>
                    <span class="auxiliary-text"><i>预售结束，商品由预售商品更新为正常商品，通过系统自动同步库存</i></span>
                    <!--<img height="23" width="23" src="assets/images/tip.png" class="tip" title="预售结束，商品由预售商品更新为正常商品，通过系统自动同步库存。" />-->
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group">
                <label class="control-label">预售店铺：<b style="color:red"> *</b></label>
                <div class="controls" id="plan_shop">
    <!--                <input type="hidden" value="" name="shop_code"/>
                    <textarea id="shop_name" readonly="readonly" data-rules="{required:true}" data-messages="{required:'请选择店铺'}"></textarea>
                    <a href="#" id = 'select_shop'><img src='assets/img/search.png'></a>-->
                    <input type="hidden" id="shop_code" value="" name="shop_code" data-rules="{required:true}" data-messages="{required:'请选择店铺'}" >
                </div>
            </div>
        </div>
    </div>
    <div class="row form-actions actions-bar">
        <div class="span8 offset3" style="text-align: center;">
            <button type="submit" class="button button-primary">提交</button>
            <button type="reset" class="button">重置</button>
        </div>
    </div>
</form>
<script>
    BUI.use('bui/form', function (Form) {
        new Form.HForm({
            srcNode: '#form_plan',
            submitType: 'ajax',
            callback: function (data) {
                if (data.status == 1) {
                    BUI.Message.Alert(data.message, 'success');
                    view(data.data);
                    ui_closeTabPage('<?php echo $request['ES_frmId'] ?>');
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }
        }).render();
    });

    $('#start_time').change(function () {
        if (getTimestamp($(this).val()) <= getTimestamp(date('Y-m-d'))) {
            BUI.Message.Tip('开始时间不能早于当前时间', 'warning');
            $(this).val('');
            return;
        }
        var end_time = $('#end_time').val();
        if (end_time != '' && end_time <= $(this).val()) {
            BUI.Message.Tip('开始时间不能晚于结束时间', 'warning');
            $(this).val('');
        }
    });

    $('#end_time').change(function () {
        if (getTimestamp($(this).val()) <= getTimestamp(date('Y-m-d'))) {
            BUI.Message.Tip('结束时间不能早于当前时间', 'warning');
            $(this).val('');
            return;
        }
        var start_time = $('#start_time').val();
        if (start_time != '' && start_time >= $(this).val()) {
            BUI.Message.Tip('结束时间不能早于开始时间', 'warning');
            $(this).val('');
        }
    });

    //转换时间戳
    function getTimestamp(_date) {
        var timestamp = Date.parse(_date);
        return timestamp / 1000;
    }

    BUI.use(['bui/select', 'bui/data'], function (Select, Data) {
        var shop = new Data.Store({
            url: "?app_act=base/shop/get_purview_shop&first_type=0&first_val=''",
            autoLoad: true
        }), select = new Select.Select({
            render: '#plan_shop',
            valueField: '#shop_code',
            multipleSelect: false,
            store: shop
        });
        select.render();
    });

//    $("#select_shop,#shop_name").click(function () {
//        show_select();
//    });

    function show_select() {
        var param = {};
        if (typeof (top.dialog) != 'undefined') {
            top.dialog.remove(true);
        }
        var url = '?app_act=op/inv_sync/select_shop';
        var buttons = [
            {
                text: '确定',
                elCls: 'button button-primary',
                handler: function () {
                    var data = top.SelectoGrid.getSelection();
                    if (data.length > 0) {
                        var _name = new Array();
                        var _code = new Array();
                        $.each(data, function (i, val) {
                            _name[i] = val['shop_name'];
                            _code[i] = val['shop_code'];
                        });
                        _name = _name.join(',');
                        _code = _code.join(',');
                        $("#form_plan input[name='shop_code']").val(_code);
                        $("#form_plan #shop_name").val(_name);
                    }
                    auto_enter('#shop_name');
                    this.close();
                }
            }, {
                text: '取消',
                elCls: 'button',
                handler: function () {
                    this.close();
                }
            }
        ];
        top.BUI.use('bui/overlay', function (Overlay) {
            top.dialog = new Overlay.Dialog({
                title: '选择店铺',
                width: '700',
                height: '550',
                loader: {
                    url: url,
                    autoLoad: true, //不自动加载
                    params: param, //附加的参数
                    lazyLoad: false, //不延迟加载
                    dataType: 'text'   //加载的数据类型
                },
                align: {
                    //node : '#t1',//对齐的节点
                    points: ['tc', 'tc'], //对齐参考：http://dxq613.github.io/#positon
                    offset: [0, 20] //偏移
                },
                mask: true,
                buttons: buttons
            });
            top.dialog.on('closed', function (ev) {

            });
            top.dialog.show();
        });
    }

    //模拟回车事件，触发文本框规则检查
    function auto_enter(_id) {
        var e = jQuery.Event("keyup"); //模拟一个键盘事件
        e.keyCode = 13; //keyCode=13是回车
        $(_id).trigger(e);
    }

    function view(plan_code) {
        openPage('<?php echo base64_encode('?app_act=op/presell/plan_detail&app_scene=add&plan_code=') ?>' + plan_code, '?app_act=op/presell/plan_detail&plan_code=' + plan_code, '新增预售计划');
    }

</script>