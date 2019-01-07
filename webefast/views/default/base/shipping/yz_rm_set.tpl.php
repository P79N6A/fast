<!-- 账号设置 -->
<form  id="form_yz_rm_set" action="?app_act=base/shipping/do_edit_shop&app_fmt=json" method="post">
    <input type="hidden" id="express_id" name="express_id" value=""/>
    <table class="form_tbl">
        <tr>
            <td class="tdlabel">挂靠店铺：</td>
            <td colspan="3">
                <div id="shop_sel">
                    <input type="hidden" id="rm_shop_code" value="" name="rm_shop_code">
                </div>
            </td>
        </tr>
        <tr>
            <td colspan = '2' style="color:red;">
                <p style="margin-left: 50px;margin-top: 10px;" id="tip_txt"></p>
            </td>
        </tr>
        <tr>
            <td class="tdlabel"></td>
            <td colspan="3" class="btn-opt">
                <div style="margin-top:20px;">
                    <button class="button button-primary" type="submit">提交</button>
                    <button class="button " type="reset">重置</button>
                </div>
            </td>
        </tr>
    </table>
</form>

<script>
    is_refresh = 0;
    if (form_data_source_v != '') {
        $('form#form_yz_rm_set').autofill(eval("(" + form_data_source_v + ")"));
    }

    $(function () {
        if (print_type == 1 && company_code === 'JD') {
            $("#tip_txt").text('温馨提示：若希望所有店铺订单均使用一个京东店铺来获取快递单号，则设置挂靠店铺。否则无需设置');
        } else {
            $("#tip_txt").text('提示：选择已开通电子面单的店铺，所有店铺均采用所选店铺获取电子面单号');
        }
    });

    var form = new BUI.Form.HForm({
        srcNode: '#form_yz_rm_set',
        submitType: 'ajax',
        callback: function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, function () {
                if (data.status == 1) {
                    ui_closePopWindow(getQueryString('ES_frmId'));
                }
            }, type);
        }
    }).render();

    BUI.use(['bui/select', 'bui/data'], function (Select, Data) {
        //店铺选择
        var shop_data = new Data.Store({
            url: '?app_act=base/shipping/get_data_select&data_type=shop',
            autoLoad: true
        });
        var shop_select = new Select.Select({
            render: '#shop_sel',
            valueField: '#rm_shop_code',
            multipleSelect: false,
            store: shop_data
        });
        shop_select.render();
    });
</script>