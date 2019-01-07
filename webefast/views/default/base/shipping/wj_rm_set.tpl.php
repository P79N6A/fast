<!-- 账号设置 -->
<form  id="form_wj_rm_set" action="?app_act=base/shipping/do_edit_shop&app_fmt=json" method="post">
    <input type="hidden" id="express_id" name="express_id" value=""/>
    <table class="form_tbl">
        <tr>
            <td class="tdlabel">挂靠店铺：</td>
            <td>
                <div id="shop_sel" style="display: inline-table;">
                    <input type="hidden" id="rm_shop_code" class="control-text span5" value="" name="rm_shop_code" data-rules="{required: true}">
                </div>
                <b style="color:red;"> *</b>
            </td>
        </tr>
        <tr>
            <td colspan = '2' style="color:red;">
                <p style="margin-left: 50px;margin-top: 10px;">
                    提示：选择已开通签约京东无界电子面单的店铺，所有店铺均采用所选店铺获取京东无界电子面单号
                </p>
            </td>
        </tr>
        <tr>
            <td class="tdlabel"></td>
            <td colspan="3">
                <div style="margin-top:20px;">
                    <button class="button button-primary" type="submit">提交</button>
                    <button class="button " type="reset">重置</button>
                </div>
            </td>
        </tr>
    </table>
</form>

<form action="#" id="form_provider_set" style="padding: 30px;">
    <span class="button " style="margin:10px;" onclick="getJdProviderSign(this)">获取京东承运商签约信息</span>
    <!--<i class="icon-plus"></i>-->
    <?php
    render_control('DataTable', 'provider_table', array(
        'conf' => array(
            'list' => array(
                array(
                    'type' => 'button',
                    'show' => 1,
                    'title' => '操作',
                    'field' => '_operate',
                    'width' => '70',
                    'align' => 'center',
                    'buttons' => array(
                        array(
                            'id' => 'binding',
                            'title' => '绑定',
                            'callback' => 'bindProvider',
                            'confirm' => '确定要绑定此签约承运商吗',
                            'show_cond' => "obj.bind != 1"
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '绑定状态',
                    'field' => 'bind_txt',
                    'width' => '70',
                    'align' => 'center'
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '承运商名称',
                    'field' => 'provider_name',
                    'width' => '100',
                    'align' => 'center'
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '网点编码',
                    'field' => 'branch_code',
                    'width' => '70',
                    'align' => 'center'
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '网点名称',
                    'field' => 'branch_name',
                    'width' => '200',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '财务结算编码',
                    'field' => 'settlement_code',
                    'width' => '100',
                    'align' => 'center'
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '剩余单号量',
                    'field' => 'amount',
                    'width' => '80',
                    'align' => 'center'
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '签约发货地址',
                    'field' => 'address',
                    'width' => '500',
                    'align' => ''
                ),
            )
        ),
        'dataset' => 'remin/WujieModel::get_provider_sign_list',
        'idField' => 'sign_id',
        'queryBy' => '',
        'init' => 'nodata'
    ));
    ?>
</form>

<script>
    is_refresh = 0;
    if (form_data_source_v != '') {
        $('form#form_wj_rm_set').autofill(eval("(" + form_data_source_v + ")"));
    }

    $(function () {
        $("#operation_type").val(operation_type);
        if ($("#rm_shop_code").val() == '') {
            $("#form_provider_set").hide();
        }
    });

    var form = new BUI.Form.HForm({
        srcNode: '#form_wj_rm_set',
        submitType: 'ajax',
        callback: function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            if (type === 'success') {
                BUI.Message.Tip('更新成功', type);
                $("#form_provider_set").show();
            } else {
                BUI.Message.Alert(data.message, type);
            }
        }
    }).render();

    BUI.use(['bui/select', 'bui/data'], function (Select, Data) {
        //店铺选择
        var shop_data = new Data.Store({
            url: '?app_act=base/shipping/get_data_select&data_type=shop&_type=jd',
            autoLoad: true
        });
        var shop_select = new Select.Select({
            render: '#shop_sel',
            valueField: '#rm_shop_code',
            multipleSelect: false,
            store: shop_data
        });
        shop_select.render();

        shop_select.on('change', function (ev) {
            refresh_provider();
        });
    });

    function getJdProviderSign(_this) {
        $(_this).attr('disabled','disabled');
        $.post("?app_act=remin/wujie/get_jd_provider_sign_api", {shop_code: $("#rm_shop_code").val()}, function (ret) {
            if (ret.status == 1) {
                BUI.Message.Tip('获取成功', 'success');
                provider_tableStore.load();
            } else {
                BUI.Message.Alert(ret.message, 'error');
            }
            $(_this).removeAttr('disabled');
        }, "json");
    }

    function refresh_provider() {
        var params = {};
        params.shop_code = $('#rm_shop_code').val();
        params.company_code = company_code;
        params.express_code = $("#express_code").val();
        provider_tableStore.load(params, function () {
            $(".nodata").text('');//清除加载提示
        });
    }

    function bindProvider(state, res) {
        $.post('?app_act=base/shipping/do_update_sign', {sign_id: res.sign_id, express_id: $("#express_id").val()}, function (ret) {
            if (ret.status == 1) {
                BUI.Message.Tip('绑定成功', 'success');
                refresh_provider();
            } else {
                BUI.Message.Alert(ret.message, 'error');
            }
        }, "json");
    }
</script>