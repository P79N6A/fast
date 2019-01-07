<style type="text/css">
    body{overflow-y: hidden;}
    .main{margin: 10px 0px 0px 30px;}
    /*---选择框-begin--*/
    .check_custom{visibility: hidden;}
    .check_custom + label{
        cursor: pointer;
        margin: 3px 8px 4px -12px;
        background-color: white;
        border-radius: 5px;
        border:1px solid #d3d3d3;
        width:18px;
        height:18px;
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

    [type="radio"] + label{
        border-radius: 10px;
    }
    /*---选择框-end--*/

    .mode-title{font-size: 1.1em;}
    .mode-title #split_mode_weight{margin-left: 15px;}
    .mode-title #split_mode_num{margin-left: 20px;}

    .mode-val{margin-top: 15px;}
    .mode-val .control-group div{width: 87%;float: left;}
    .mode-val input{width:75%;height: 30px;border-radius: 0;padding: 5px 50px 5px 20px;font-size: 1.3em}
    .mode-val .unit-style{margin-left: -40px;position: absolute;margin-top: 10px;font-size: 1.3em;}
    .mode-val .require-flag{color: red;font-size: 1.5em;line-height: 45px;vertical-align: middle;}

    .mode-explain{margin-top: 10px;color: red;}
    .mode-explain p{margin-left: 20px;}

    .opt-style{margin-left: 18%;margin-top: 10px;}
    .opt-style button{height: 30px;width: 100px;}
</style>
<div class="main">
    <div class="row mode-title">
        <div class="control-group" >
            <label>拆单模式
                <img src="assets/images/tip.png" class="tip" style="height:23px;width:23px;cursor: pointer;" title="举例，订单重量为5kg，包含了3个商品（A，1kg；B，2kg；C，2kg），如果希望单笔订单最大支持3kg，则该笔订单拆分会生成2笔订单，一笔订单包含2个商品（A，1kg；B，2kg），另一笔订单包含1个商品（C，2kg）" />：
            </label>
            <input type="radio" name="split_mode" id="split_mode_weight" class="check_custom" value="weight" checked="checked">
            <label class="radio" for="split_mode_weight"></label><label for="split_mode_weight" style="cursor: pointer;">按重量拆单</label>
<!--            <input type="radio" name="split_mode" id="split_mode_num" class="check_custom" value="num">
            <label class="radio" for="split_mode_num"></label><label for="split_mode_num"  style="cursor: pointer;">按数量拆单</label>-->
        </div>
    </div>
    <div class="row mode-val">
        <div class="control-group">
            <div>
                <input type="text" id="weight" placeholder="请输入单笔订单最大重量">
                <label class="unit-style">Kg</label>
            </div>
            <div>
                <input type="text" id="num" placeholder="请输入单笔商品件数">
            </div>
            <label class="require-flag">*</label>
        </div>
    </div>
    <div class="row mode-explain">
        <span>说明：</span>
        <p><em>1、</em>单次操作最大订单数为100。</p>
        <p><em>2、</em>若订单包含商品未维护重量或重量是0，则不参与拆分。</p>
        <p><em>2、</em>赠品将视为普通商品参与拆分。</p>
    </div>
    <div class="row opt-style">
        <button class="button button-primary" id="opt_split_order">确认拆单</button>
        <button class="button button-primary" onclick="closePop()" style="margin-left: 20px;">取消拆单</button>
    </div>
</div>
<?php echo load_js('jquery.cookie.js') ?>
<script>
    $(function () {
        changeModeLoadElement();
    });

    $("input:radio[name='split_mode']").on('change', function () {
        setConfigCookie('split_mode', $(this).val());
        changeModeLoadElement();
    });

    //拆单模式改变加载元素
    function changeModeLoadElement() {
        var split_mode = getConfigCookie('split_mode');
        if (split_mode == 'weight') {
            $("#num").parent().hide();
            $(".mode-explain p:eq(1)").show();
            $(".mode-explain p:eq(2)").hide();
        } else if (split_mode == 'num') {
            $("#weight").parent().hide();
            $(".mode-explain p:eq(1)").hide();
            $(".mode-explain p:eq(2)").show();
        }
        $("input:radio[name='split_mode'][value='" + split_mode + "']").prop("checked", "checked");
        $("#" + split_mode).val('');
        $("#" + split_mode).focus();
        $("#" + split_mode).parent().show();
    }

    $("#opt_split_order").on('click', function () {
        split_order();
    });

    $("#weight,#num").keyup(function (event) {
        if (event.keyCode == 13) {
            split_order();
        }
    });

    function split_order() {
        var split_mode = $("input:radio[name='split_mode']:checked").val();
        var split_value = $("#" + split_mode).val();
        if (split_mode == 'num') {
            BUI.Message.Tip('功能暂未开放', 'warning');
            return false;
        }
        if (split_mode == 'weight') {
            if (isNaN(split_value) || split_value <= 0) {
                BUI.Message.Tip('订单重量必须大于0Kg', 'warning');
                return false;
            }
        } else if (split_mode == 'num') {
            if (!(/^(\+|-)?\d+$/.test(split_value)) || split_value <= 0) {
                BUI.Message.Tip('商品数量必须为正整数', 'warning');
                return false;
            }
        }
        var sell_record_code_list = <?php echo json_encode(explode(',', $request['sell_record_code_list'])); ?>;
        var params = [];
        $.each(sell_record_code_list, function (i, code) {
            var p = {};
            p.split_mode = split_mode;
            p.split_value = split_value;
            p.sell_record_code = code;
            params.push(p);
        });
        var act = 'oms/sell_record/split_order_batch_act';
        process_batch_task(act, '批量拆单', params, 'sell_record_code', 0, 'opt_split_order');
    }

    BUI.use('bui/tooltip', function (Tooltip) {
        var tips = new Tooltip.Tips({
            tip: {
                trigger: '.tip', //出现此样式的元素显示tip
                alignType: 'bottom-left', //默认方向
                elCls: 'tips tips-info',
                titleTpl: '<div class="tips-content" style="margin-left: 0px;">{title}</div>',
                offset: 10 //距离左边的距离
            }
        });
        tips.render();
    });

    function closePop() {
        ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
    }

    /*--------页面缓存设置----BEGIN----*/
    //页面加载时，读取cookie,设置配置项状态
    function getConfigCookie(_name) {
        var cookie_val = $.cookie(_name);
        if (cookie_val == undefined) {
            cookie_val = 'weight';
        }
        return cookie_val;
    }

    //配置项状态状态更改时，设置cookie
    function setConfigCookie(_name, _value) {
        $.cookie(_name, _value, {expires: 30});
    }
    /*--------页面缓存设置----END----*/
</script>
<?php include_once (get_tpl_path('common/process_batch_task')); ?>