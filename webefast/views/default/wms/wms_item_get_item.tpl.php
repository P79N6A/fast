<style>
    .form-horizontal{margin: 10px;}
    .row{margin-bottom: 10px;}
    .row .control-label{width: 145px;}
</style>
<div class="form-horizontal">
    <div class="row">
        <div class="control-group span12" style="float: left">
            <label class="control-label">WMS配置名称<img height="23" width="23" src="assets/images/tip.png" data-align="right" class="tip" title="仅支持京东沧海WMS" /><b style="color:red"> *</b>：</label>
            <div class="controls">
                <div id="wms_config">
                    <input type="hidden" id="wms_config_id" value="" name="wms_config_id">
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="control-group span20" style="float: left">
            <label class="control-label">商品条形码<img height="23" width="23" src="assets/images/tip.png" data-align="right" class="tip" title="支持多条码输入，输入多条码时请使用逗号隔开" /><b style="color:red"> *</b>：</label><br><br>
            <div class="controls" style="float:left">
                <textarea id="barcode" style="width:550px;height: 200px;"></textarea>
            </div>
        </div>
    </div>
    <div class="row form-actions actions-bar">
        <div class="span13 offset3" style="text-align:center;">
            <button type="submit" class="button button-primary" style="width: 80px;" onclick="get_item()">获取</button>
        </div>
    </div>
</div>

<script>
    //获取WMS商品
    function get_item() {
        var wms_config_id = $("#wms_config_id").val();
        var barcode = $("#barcode").val();
        if (wms_config_id == '') {
            BUI.Message.Tip('请选择WMS配置', 'warning');
            return false;
        }
        if (barcode == '') {
            BUI.Message.Tip('请输入要获取的商品条码', 'warning');
            return false;
        }

        var params = {wms_config_id: wms_config_id, barcode: barcode};
        $.post('?app_act=wms/wms_item/get_item_action', params, function (ret) {
            if (ret.status == 1) {
                BUI.Message.Tip(ret.message, 'success');
            } else {
                var errhtml = ret.message + '<br><textarea>' + ret.data.error_barcode + '</textarea>';
                BUI.Message.Alert(errhtml, 'error');
            }
        }, 'json');
    }

    //WMS配置选择
    BUI.use(['bui/select', 'bui/data'], function (Select, Data) {
        var store = new Data.Store({
            url: '?app_act=wms/wms_item/get_wms_config',
            autoLoad: true
        }),
                select = new Select.Select({
                    render: '#wms_config',
                    valueField: '#wms_config_id',
                    multipleSelect: false,
                    store: store
                });
        select.render();
        select.on('change', function (ev) {
//            alert(ev.item.text);
            $("#barcode").focus();
        });
    });

    //页面帮助
    BUI.use('bui/tooltip', function (Tooltip) {
        var tips = new Tooltip.Tips({
            tip: {
                trigger: '.tip', //出现此样式的元素显示tip
                alignType: 'left', //默认方向
                elCls: 'tips tips-info',
                titleTpl: '<div class="tips-content" style="margin-left: 0px;">{title}</div>',
                offset: 10 //距离左边的距离
            }
        });
        tips.render();
    });
</script>