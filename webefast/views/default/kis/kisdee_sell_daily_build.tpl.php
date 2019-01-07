<style>
    .bui-stdmod-footer{margin: 0!important}
    #record_date { width:120px;}
    #shop_name,#remark{width: 200px;}
    #form_daily button{margin-left: 3px;}
    #form_daily .control-group{margin-bottom: 5px}
    #form_daily .control-label{text-align: right;}
    .bui-stdmod-footer{margin-top: 0!important}
    body,form{overflow-y: hidden;}
</style>
<form id="form_daily" action="?app_act=kis/kisdee/create_sell_daily" method="post">
    <div class="row">
        <div class="control-group span9">
            <label class="control-label">业务日期：<b style="color:red"> *</b></label>
            <div class="controls">
                <input id="record_date" type="text" value="" name="record_date" class="input-normal calendar calendar-date bui-form-field-date bui-form-field" data-rules="{required:true}" data-messages="{required:'请选择业务日期'}">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="control-group span9">
            <label class="control-label">日报类型：<b style="color:red"> *</b></label>
            <div class="controls">
                <div id="record_type_select">
                    <input type="hidden" id="record_type" name="record_type" value="all" data-rules="{required:true}" data-messages="{required:'请选择仓库'}"/>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="control-group span9">
            <label class="control-label">店铺：<b style="color:red"> *</b></label>
            <div class="controls">
                <input type="hidden" value="" name="shop_code"/>
                <input type="text" readonly="readonly" id="shop_name" class="input-normal control-text" data-rules="{required:true}" data-messages="{required:'请选择店铺'}">
                <a href="#" id = 'select_shop'><img src='assets/img/search.png'></a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="control-group span8">
            <label class="control-label" style="margin-left: -10px;margin-right: 10px">摘要：</label>
            <div class="controls">
                <textarea id="remark" name="remark"></textarea>
            </div>
        </div>
    </div>
    <div class="row form-actions actions-bar">
        <div class="span9 offset3" style="text-align: center;">
            <button type="submit" class="button button-primary">生成</button>
            <button type="reset" class="button">重置</button>
            <button type="button" class="button" id="close_pop">关闭</button>
        </div>
    </div>
    <p style="color:red;position: fixed;bottom: 1px;">
        友情提示：按照店铺/仓库/日报类型每天生成一张日报，每次仅能生成一天的日报。
    </p>
</form>
<script>
    $(function () {
        setDate();
    });

    BUI.use('bui/form', function (Form) {
        new Form.HForm({
            srcNode: '#form_daily',
            submitType: 'ajax',
            callback: function (data) {
                if (data.status == 1) {
                    BUI.Message.Alert(data.message, 'success');
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }
        }).render();
    });

    BUI.use('bui/select', function (Select) {
        var items = [
            {text: '全部', value: 'all'},
            {text: '销售订单', value: 'sell_record'},
            {text: '销售退单', value: 'sell_return'}
        ],
                select = new Select.Select({
                    render: '#record_type_select',
                    valueField: '#record_type',
                    items: items,
                    width: 130
                });
        select.render();
//        select.on('change', function (ev) {
//            alert(ev.item);
//        });
    });
    $('#record_date').change(function () {
        if (getTimestamp($(this).val()) >= getTimestamp(date('Y-m-d'))) {
            BUI.Message.Show({
                msg: '业务日期不能大于当天日期',
                icon: 'warning',
                buttons: [],
                autoHide: true
            });
            setDate();
        }
    });
    //转换时间戳
    function getTimestamp(_date) {
        var timestamp = Date.parse(_date);
        return timestamp / 1000;
    }
    //设置业务日期
    function setDate() {
        var dd = new Date();
        dd.setDate(dd.getDate() - 1);
        $('#record_date').val(date('Y-m-d', dd));
    }

    $("#select_shop,#shop_name").click(function () {
        show_select('shop');
    });

    function show_select(_type) {
        var param = {};
        if (typeof (top.dialog) != 'undefined') {
            top.dialog.remove(true);
        }
        var url = _type == 'shop' ? '?app_act=op/inv_sync/select_' + _type : '?app_act=kis/kisdee/select_store';
        var buttons = [
            {
                text: '确定',
                elCls: 'button button-primary',
                handler: function () {
                    var data = top.SelectoGrid.getSelection();
                    if (data.length > 0) {
                        deal_data(data, _type);
                    }
                    var _id = _type == 'shop' ? 'shop_name' : 'store_name';
                    auto_enter('#' + _id);
                    $(".bui-stdmod-footer").css('margin', '0!important');
                    $(".bui-stdmod-footer").css('padding', '0!important');
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
                title: _type == 'shop' ? '选择店铺' : '选择仓库',
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

    function deal_data(obj, _type) {
        var _name = new Array();
        var _code = new Array();
        $.each(obj, function (i, val) {
            _name[i] = val[_type + '_name'];
            _code[i] = val[_type + '_code'];
        });
        _name = _name.join(',');
        _code = _code.join(',');
        $("#form_daily input[name='" + _type + "_code']").val(_code);
        $("#form_daily #" + _type + "_name").val(_name);
    }

    //模拟回车事件，触发文本框规则检查
    function auto_enter(_id) {
        var e = jQuery.Event("keyup"); //模拟一个键盘事件
        e.keyCode = 13; //keyCode=13是回车
        $(_id).trigger(e);
    }

    $('#close_pop').click(function () {
        ui_closeTopPopWindow();
    });

</script>