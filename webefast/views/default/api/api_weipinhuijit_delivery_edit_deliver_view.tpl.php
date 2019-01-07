<?php echo load_js("baison.js", true); ?>
<style>
    .td-left{width: 35%;text-align: center;}
    .td-right{width: 65%}
    body{overflow-y: hidden;}
</style>
<table cellspacing="0" class="table table-bordered">
    <tr>
        <td class="td-left">配送模式：</td>
        <td class="td-right">
            <select name="delivery_method" id="delivery_method">
                <option value="">请选择</option>
                <option value="1" <?php echo $response['data']['delivery_method'] == 1 ? 'selected' : '' ?>>汽运</option>
                <option value="2" <?php echo $response['data']['delivery_method'] == 2 ? 'selected' : '' ?>>空运</option>
            </select>
            <span style="color:red">*</span>
        </td>
    </tr>
    <tr>
        <td class="td-left">要求到货时间：</td>
        <td class="td-right">
            <input  id="arrival_date"  style="width:100px;" type="text" value="<?php echo isset($response['data']['arrival_date']) ? $response['data']['arrival_date'] : date('Y-m-d', strtotime('2day')); ?>" name="arrival_date" data-rules="{required : true}" class="calendar">
            <select name="time_slot" id="time_slot" style="width:80px;">
            </select>
            <span style="color:red">*</span>
            <label id="history_time"></label>
        </td>
    </tr>
    <tr>
        <td class="td-left">配送方式：</td>
        <td class="td-right">
            <div id="express_select" style="display: inline">
                <input type="hidden" id="express_code" value="<?php echo $response['data']['express_code'] ?>" name="express_code">
            </div>
            <span style="color:red">*</span>
        </td>
    </tr>
    <tr>
        <td class="td-left">快递单号：</td>
        <td class="td-right">
            <input  id="express_no"  style="width:185px;" type="text" value="<?php echo isset($response['data']['express']) ? $response['data']['express'] : ''; ?>" name="express_no" data-rules="{required : true}">
            <span style="color:red">*</span>
        </td>
    </tr>
    <tr>
        <td class="td-left">关联PO号：</td>
        <td class="td-right">
            <input id="po_no" style="width:185px;" type="text" value="<?php echo isset($response['data']['po_no']) ? $response['data']['po_no'] : ''; ?>" name="po_no" data-rules="{required : true}" readonly="true">
            <a id="add_po_no" style="text-decoration: none;cursor: pointer;">追加</a>
        </td>
    </tr>
</table>
<br />
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="ok">确定</button>
</div>
<script>
    var time_slot = "<?php echo $response['data']['time_slot']; ?>";
    var delivery_method = "<?php echo $response['data']['delivery_method']; ?>";
    var shop_code = "<?php echo $response['data']['shop_code']; ?>";
    time_slot_show();
    $("#time_slot").val(time_slot);
    //日历控件
    BUI.use('bui/calendar', function (Calendar) {
        var datepicker = new Calendar.DatePicker({
            trigger: '.calendar',
            showTime: false,
            autoRender: true
        });
    });

    $(document).ready(function () {
        $("#ok").click(function () {
            $("#ok").html("正在修改，请稍后。。。");
            $("#ok").attr("disabled", "disalbed");
            var delivery_method = $("#delivery_method").val();
            var arrival_date = $("#arrival_date").val();
            var time_slot = $('#time_slot').val();
            var express_code = $('#express_code').val();
            var express_no = $('#express_no').val();
            var po_no = $('#po_no').val();
            if (!delivery_method || !arrival_date || !time_slot || !express_code || !express_no) {
                BUI.Message.Alert('请将必填项填写完整', 'error');
                $("#ok").html("确定");
                $("#ok").removeAttr("disabled");
            } else {
                var params = {
                    "id": '<?php echo $request['id']; ?>',
                    "delivery_method": delivery_method,
                    "arrival_time": arrival_date + ' ' + time_slot,
                    "express_code": express_code,
                    "express": express_no,
                    "po_no": po_no,
                    "app_fmt": "json",
                };
                $.post("?app_act=api/api_weipinhuijit_delivery/edit_deliver_action", params, function (data) {
                    if (data.status == 1) {
                        BUI.Message.Alert('修改成功！', function () {
                            ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
                        }, 'success');
                    } else {
                        BUI.Message.Alert(data.message, 'error');
                        $("#ok").html("确定");
                        $("#ok").removeAttr("disabled");
                    }
                }, "json")
            }
        });
    });

    //更改配送模式加载其对应的到货时间
    $('#delivery_method').change(function () {
        if ($(this).val() == '') {
            $('#time_slot').css('display', 'none');
            return;
        }
        var obj = $('#time_slot');
        if ($(this).val() == 1) {
            obj.html('<option value="">请选择</option><option value="09:00:00">09:00:00</option><option value="10:00:00">10:00:00</option><option value="16:00:00">16:00:00</option><option value="20:00:00">20:00:00</option><option value="22:00:00">22:00:00</option><option value="23:59:00">23:59:00</option>');
        }
        if ($(this).val() == 2) {
            obj.html('<option value="">请选择</option><option value="09:00:00">09:00:00</option><option value="16:00:00">16:00:00</option><option value="18:00:00">18:00:00</option><option value="20:00:00">20:00:00</option><option value="23:59:00">23:59:00</option>');
        }
        $('#time_slot').css('display', '');
    });

    function time_slot_show() {
        var obj = $('#time_slot');
        if (delivery_method == 1) {
            obj.html('<option value="">请选择</option><option value="09:00:00">09:00:00</option><option value="10:00:00">10:00:00</option><option value="16:00:00">16:00:00</option><option value="20:00:00">20:00:00</option><option value="22:00:00">22:00:00</option><option value="23:59:00">23:59:00</option>');
        }
        if (delivery_method == 2) {
            obj.html('<option value="">请选择</option><option value="09:00:00">09:00:00</option><option value="16:00:00">16:00:00</option><option value="18:00:00">18:00:00</option><option value="20:00:00">20:00:00</option><option value="23:59:00">23:59:00</option>');
        }
    }

    BUI.use(['bui/select', 'bui/data'], function (Select, Data) {
        var store = new Data.Store({
            url: '?app_act=base/shipping/get_bui_select_shipping',
            autoLoad: true
        }),
                select = new Select.Select({
                    render: '#express_select',
                    valueField: '#express_code',
                    multipleSelect: false,
                    store: store
                });
        select.render();
    });

    $("#add_po_no").click(function () {
        show_select('po_no');
    });

    function show_select() {
        var param = {shop_code: shop_code, po_no_except: $("#po_no").val()};
        if (typeof (top.dialog) != 'undefined') {
            top.dialog.remove(true);
        }
        var url = '?app_act=api/api_weipinhuijit_po/select_po';
        var buttons = [
            {
                text: '保存继续',
                elCls: 'button button-primary',
                handler: function () {
                    var data = top.SelectoGrid.getSelection();
                    if (data.length > 0) {
                        deal_data(data);
                        top.skuSelectorStore.load({po_no_except: $("#po_no").val()});
                    }
                }
            },
            {
                text: '保存退出',
                elCls: 'button button-primary',
                handler: function () {
                    var data = top.SelectoGrid.getSelection();
                    if (data.length > 0) {
                        deal_data(data);
                    }
                    this.close();
                }
            },
            {
                text: '重置',
                elCls: 'button',
                handler: function () {
                    top.skuSelectorStore.load();
                }
            }
        ];
        top.BUI.use('bui/overlay', function (Overlay) {
            top.dialog = new Overlay.Dialog({
                title: '选择档期（建议只选取此出库单中拣货单对应的PO）',
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

    function deal_data(obj) {
        var chose_po_no = new Array();
        var chose_po_no_str = $("#po_no").val().trim();
        $.each(obj, function (i, val) {
            chose_po_no[i] = val['po_no'];
        });
        if (chose_po_no.length > 0) {
            po_str = chose_po_no.join(',');
            if (chose_po_no_str.length > 0) {
                chose_po_no_str = chose_po_no_str + ',' + po_str;
            } else {
                chose_po_no_str = po_str;
            }
            //去重
            chose_po_no_arr = chose_po_no_str.split(","); //字符分割 .
            chose_po_no_arr = unique(chose_po_no_arr);
            chose_po_no_str = chose_po_no_arr.join(',');
        }
        $("#po_no").val(chose_po_no_str);
    }

    //数组去重
    function unique(arr) {
        var result = [], hash = {};
        for (var i = 0, elem; (elem = arr[i]) != null; i++) {
            if (!hash[elem]) {
                result.push(elem);
                hash[elem] = true;
            }
        }
        return result;
    }
</script>