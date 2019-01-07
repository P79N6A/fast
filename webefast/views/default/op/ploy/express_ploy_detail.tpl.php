<style>
    #form_ploy{margin-top: 5px;}
    #form_ploy .well{padding: 20px;}
    #form_ploy .control-label {width:120px;}
    #form_ploy .controls input[type="text"]{width: 200px;}
    #form_ploy .well .row{margin-left: 20px;}
    #form_ploy .well .row:not(:first-child){margin-top: 15px;}
    #form_ploy .control-group{width:85%}
    #form_ploy .form-actions{text-align: center;}
    #form_ploy .form-actions button{margin-left: 20px;}
    #form_ploy .auxiliary-text{margin-left:15px;margin-top: 3px;float: right}
    #form_ploy #express input{width:185px}
    [type="radio"],[type="checkbox"],.check_custom + label +label{cursor: pointer;}
    @-moz-document url-prefix() {
        #shop_name + img, #store_name + img{margin-left: -25px;margin-top: 3px;position: absolute;}
    }
    @media screen and (-webkit-min-device-pixel-ratio:0) {
        #shop_name + img, #store_name + img{margin-left: -29px;}
    }
    [type="radio"]{

    }

    /*---选择框-begin--*/
    .check_custom{visibility: hidden;}
    .check_custom + label{
        cursor: pointer;
        margin: 3px 8px 4px -12px;
        background-color: white;
        border-radius: 5px;
        border:1px solid #d3d3d3;
        width:20px;
        height:20px;
        display: inline-block;
        text-align: center;
        vertical-align: middle;
        line-height: 20px;
    }
    .check_custom:checked + label{
        background-color: #eee;
    }
    .check_custom:checked + label:after{
        content:"\2714";
    }
    /*---选择框-end--*/
    [type="radio"] + label{
        border-radius: 10px;
    }
    .send_adapt_style{line-height: 24px;margin-top: 10px !important;margin-left: 130px !important;}
    .send_adapt_style input,.send_adapt_style span,.send_adapt_style div{float: left}
    .adapt_input_style{width: 50px !important;margin-left: 5px;margin-right: 5px;}
    .send_adapt_style #adapt_order_status{margin-left: 5px;margin-right: 5px;}
    .send_adapt_style #adapt_order_status input{width: 80px !important;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '策略基本信息',
    'links' => array(),
    'ref_table' => 'table'
));
?>
<form class="form-horizontal" id="form_ploy" action="?app_act=op/ploy/express_ploy/do_<?php echo $app['scene'] ?>" method="post">
    <div class="well">
        <div class="row">
            <div class="control-group">
                <label class="control-label">策略名称：<b style="color:red"> *</b></label>
                <div class="controls">
                    <input type="hidden" name="ploy_code">
                    <input type="text" name="ploy_name" class="input-normal control-text" data-rules="{required:true}" data-messages="{required:'策略名称不能为空'}" data-tip="{text:'请输入策略名称'}">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group">
                <label class="control-label">适配店铺：<b style="color:red"> *</b></label>
                <div class="controls">
                    <input type="hidden" name="shop_code"/>
                    <input type="text" id="shop_name" class="input-normal control-text tip" readonly="readonly" data-rules="{required:true}" data-messages="{required:'店铺不能为空'}" data-tip="{text:'请选择店铺'}">
                    <img src='assets/img/search.png' id="select_shop">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group">
                <label class="control-label">发货仓库：<b style="color:red"> *</b></label>
                <div class="controls"  style="line-height: 20px">
                    <input type="hidden" name="store_code"/>
                    <input type="text" id="store_name" class="input-normal control-text tip" readonly="readonly" data-rules="{required:true}" data-messages="{required:'仓库不能为空'}"  data-tip="{text:'请选择仓库'}">
                    <img src='assets/img/search.png' id="select_store">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group">
                <label class="control-label">订单支付类型：<b style="color:red"> *</b></label>
                <div class="controls bui-form-group" data-rules="{checkRange:1}" data-messages="{checkRange:'至少选择一种订单支付类型！'}" >
                    <input type="checkbox" name="order_pay_type" id="order_pay_type1" class="check_custom" value="1"><label for="order_pay_type1"></label><label for="order_pay_type1">款到发货/担保交易</label>
                    <input type="checkbox" name="order_pay_type" id="order_pay_type2" class="check_custom" value="2" style="margin-left: 20px;"><label for="order_pay_type2"></label><label for="order_pay_type2">货到付款</label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group">
                <label class="control-label">默认配送快递：<b style="color:red"> *</b></label>
                <div class="controls" id="express">
                    <input type="hidden" name="default_express" id="default_express" data-rules="{required:true}" data-messages="{required:'请选择默认配送方式'}">
                    <span class="auxiliary-text"><i>优先级最低，当订单地址无法适配到策略定义的快递，则选择此快递</i></span>
                </div>

            </div>
        </div>
        <div class="row">
            <div class="control-group">
                <label class="control-label">最低运费判断：</label>
                <div class="controls">
                    <input type="radio" name="min_freight_judge" id="min_freight_judge1" class="check_custom" value="0"><label class="radio" for="min_freight_judge1"></label><label for="min_freight_judge1">停用</label>
                    <input type="radio" name="min_freight_judge" id="min_freight_judge2" class="check_custom" value="1" style="margin-left: 30px;"><label class="radio" for="min_freight_judge2"></label><label for="min_freight_judge2">启用</label>
                    <span class="auxiliary-text"><i>启用后 ，适配优先级为：配送区域 > 最低运费判断 > 配送方式优先级</i></span>
                </div>

            </div>
        </div>
        <div class="row">
            <div class="control-group">
                <label class="control-label">配送适配比例：</label>
                <div class="controls">
                    <input type="radio" name="send_adapt_ratio" id="send_adapt_ratio1" class="check_custom" value="0"><label class="radio" for="send_adapt_ratio1"></label><label for="send_adapt_ratio1">停用</label>
                    <input type="radio" name="send_adapt_ratio" id="send_adapt_ratio2" class="check_custom" value="1" style="margin-left: 30px;"><label class="radio" for="send_adapt_ratio2"></label><label for="send_adapt_ratio2">启用</label>
                    <span class="auxiliary-text"><i>启用后 ，适配优先级为：配送区域 > 配送适配比率 > 配送方式优先级</i></span>
                </div>
                <div class="controls send_adapt_style">
                    <span>当在</span>
                    <input type="text" name="adapt_days" id="adapt_days" class="adapt_input_style" data-rules="{regexp:/^[1-9]\d*$/}" data-messages="{regexp:'请填写有效天数'}">
                    <span>天内，</span>
                    <div id="adapt_order_status"><input type="hidden" name="order_status" id="order_status" value="1"></div>
                    <span>的订单数量大于</span>
                    <input type="text" name="order_num" id="order_num" class="adapt_input_style" data-rules="{regexp:/^[1-9]\d*$/}" data-messages="{regexp:'请填写有效订单数'}">
                    <span>单时，将自动根据配送适配比例匹配配送方式。</span>
                </div>
<!--<img height='25' width='25' title='启用后 ，适配优先级为：配送区域 > 配送适配比率 > 配送方式优先级' class="tip" alt='' src='assets/images/tip.png'>-->
<!--<span class="tip x-icon x-icon-small x-icon-info" title="启用后 ，适配优先级为：配送区域 > 配送适配比率 > 配送方式优先级">i</span>-->
            </div>
        </div>
        <div class="row">
            <div class="tips tips-small tips-warning" style="width: 70%">
                <span class="x-icon x-icon-small x-icon-error"><i class="icon icon-white icon-bell"></i></span>
                <div class="tips-content">同时启用 <最低运费判断> 和 <配送适配比例> ，适配优先级为：配送区域 > 配送适配比率 >  最低运费判断 >  配送方式优先级
                </div>
            </div>
        </div>
    </div>
    <div class="row form-actions actions-bar" style="margin-top: 10px;">
        <div class="span10 offset3 ">
            <button type="submit" class="button button-primary">保存</button>
            <button type="reset" class="button">重置</button>
        </div>
    </div>
</form>
<script>
    var scene = "<?php echo $app['scene'] ?>";
    var form;
    BUI.use('bui/form', function (Form) {
        form = new Form.HForm({
            srcNode: '#form_ploy',
            submitType: 'ajax',
            callback: function (data) {
                if (data.status == 1) {
                    BUI.Message.Tip(data.message, 'success');
                    if (scene == 'add') {
                        view(data.data);
                    }
                    ui_closeTabPage('<?php echo $request['ES_frmId'] ?>');
                } else if (data.status == 2) {
                    BUI.Message.Tip(data.message, 'warning');
                } else {
                    BUI.Message.Tip(data.message, 'error');
                }
            }
        }).render();
    });

    $(function () {
        //非新增时加载数据
        if ($.inArray(scene, ['edit', 'view']) != -1) {
            var ploy_info = <?php echo $response['ploy_info']; ?>;
            $.each(ploy_info, function (key, val) {
                var element = '';
                if ($.inArray(key, ['ploy_code', 'ploy_name', 'shop_code', 'store_code', 'default_express']) != -1) {
                    element = "#form_ploy input[name='" + key + "']";
                    $(element).val(val);
                } else if ($.inArray(key, ['shop_name', 'store_name']) != -1) {
                    element = "#" + key;
                    $(element).val(val);
                    $(element).attr('title', val);
                } else if (key == 'order_pay_type') {
                    var order_pay_type = val.split(',');
                    $.each(order_pay_type, function (k, v) {
                        $("#order_pay_type" + v).attr('checked', 'checked');
                    });
                } else if ($.inArray(key, ['min_freight_judge', 'send_adapt_ratio']) != -1) {
                    $("#" + key + (parseInt(val) + 1)).attr('checked', 'checked');
                    initElement();
                }
                if ($("input[name='send_adapt_ratio']:checked").val() == 1 && $.inArray(key, ['adapt_days', 'order_num', 'order_status']) != -1) {
                    element = "#form_ploy input[name='" + key + "']";
                    $(element).val(val);
                    if (key == 'order_status') {
                        val = val == 1 ? '已付款' : '已付款未发货';
                        $("#adapt_order_status input:eq(1)").val(val);
                    }
                }

                if (element != "") {
                    auto_enter(element);
                }
            });
        }
        if (scene == "add") {
            //新增时设置默认项
            $("#min_freight_judge1,#send_adapt_ratio1,input[name='order_pay_type']").attr('checked', 'checked');
            initElement();
        }

        if (scene == "view") {
            //查看时禁用页面编辑
            $("#select_store,#select_shop,.actions-bar").remove();
            $("#form_ploy").css('pointer-events', 'none');
        }

        $("#select_shop,#shop_name").click(function () {
            show_select('shop');
        });
        $("#select_store,#store_name").click(function () {
            show_select('store');
        });
        $("input[name='send_adapt_ratio']").change(function () {
            initElement();
        });
    });

    BUI.use(['bui/select', 'bui/data'], function (Select, Data) {
        //选择默认配送方式
        var store = new Data.Store({
            url: '?app_act=base/shipping/get_bui_select_shipping',
            autoLoad: true
        }), select = new Select.Select({
            render: '#express',
            valueField: '#default_express',
            multipleSelect: false,
            store: store
        });
        select.render();

        //配送适配比例启用选择订单状态
        var items = [
            {text: '已付款', value: '1'},
            {text: '已付款未发货', value: '2'}
        ], adapt_select = new Select.Select({
            render: '#adapt_order_status',
            valueField: '#order_status',
            items: items
        });
        adapt_select.render();
    });

    if (scene != 'add') {
        BUI.use('bui/tooltip', function (Tooltip) {
            var tips = new Tooltip.Tips({
                tip: {
                    trigger: '#form_ploy .tip', //出现此样式的元素显示tip
                    alignType: 'right', //默认方向
                    elCls: 'tips tips-info',
                    titleTpl: '<div class="tips-content" style="margin-left: 0px">{title}</div>',
                    offset: 10 //距离左边的距离
                }
            });
            tips.render();
        });
    }

    //不同操作的元素加载
    function initElement() {
        var field_adapt_days = form.getChild('adapt_days'),
                field_order_num = form.getChild('order_num');
        var adapt_ratio_status = $("input[name='send_adapt_ratio']:checked").val();
        if (adapt_ratio_status == 1) {
            field_adapt_days.enable();
            field_order_num.enable();
            $(".send_adapt_style").css('display', '');
        } else {
            field_adapt_days.disable();
            field_order_num.disable();
            $(".send_adapt_style").css('display', 'none');
        }
    }

    function show_select(_type) {
        var param = {};
        if (typeof (top.dialog) != 'undefined') {
            top.dialog.remove(true);
        }
        var url = '?app_act=op/inv_sync/select_' + _type;
        var buttons = [
            {
                text: '保存继续',
                elCls: 'button button-primary',
                handler: function () {
                    var data = top.SelectoGrid.getSelection();
                    if (data.length > 0) {
                        deal_data(data, _type);
                        var _id = _type == 'shop' ? 'shop_name' : 'store_name';
                    }
                    auto_enter('#' + _id);
                }
            },
            {
                text: '保存退出',
                elCls: 'button button-primary',
                handler: function () {
                    var data = top.SelectoGrid.getSelection();
                    if (data.length > 0) {
                        deal_data(data, _type);
                        var _id = _type == 'shop' ? 'shop_name' : 'store_name';
                    }
                    auto_enter('#' + _id);
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
        var shop_name = Array();
        var shop_code = Array();
        $.each(obj, function (i, val) {
            shop_name[i] = val[_type + '_name'];
            shop_code[i] = val[_type + '_code'];
        });
        shop_name = shop_name.join(',');
        shop_code = shop_code.join(',');
        $("#form_ploy input[name='" + _type + "_code']").val(shop_code);
        $("#form_ploy #" + _type + "_name").val(shop_name);
    }

    //模拟回车事件，触发文本框规则检查
    function auto_enter(_id) {
        var e = jQuery.Event("keyup");//模拟一个键盘事件
        e.keyCode = 13;//keyCode=13是回车
        $(_id).focus();
        $(_id).trigger(e);
    }


    function view(ploy_code) {
        openPage('<?php echo base64_encode('?app_act=op/ploy/express_ploy/exp_list&ploy_code=') ?>' + ploy_code, '?app_act=op/ploy/express_ploy/exp_list&ploy_code=' + ploy_code, '快递配置');
    }

</script>