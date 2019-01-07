<style>
    input,textarea{padding-left: 10px !important;}
</style>
<form id="form_baseinfo" action="?app_act=base/shipping/do_<?php echo $app['scene'] ?>&app_fmt=json" method="post">
    <table class="form_tbl">
        <tr>
            <td class="tdlabel">配送方式代码：</td>
            <td>
                <input type="hidden" id="express_id" name="express_id" value=""/>
                <input type="text" value="" class="control-text span5" id="express_code" name="express_code" data-rules="{required: true}"/>
                <label id="express_code_txt" style="margin-left: 10px;"></label>
                <b style="color:red"> * </b><span class="auxiliary-text">一旦保存不能修改!</span>
            </td>
        </tr>
        <tr>
            <td class="tdlabel">配送方式名称：</td>
            <td>
                <input type="text" value="" class="control-text span5" id="express_name" name="express_name" data-rules="{required: true}"/>
                <b style="color:red"> *</b>
            </td>
        </tr>
        <tr>
            <td class="tdlabel">所属快递公司：</td>
            <td>
                <div id="company_sel" style="display: inline-table;">
                    <input type="hidden" id="company_code" name="company_code" value="" data-rules="{required: true}">
                </div>
                <b style="color:red;"> *</b>
            </td>
        </tr>
        <tr>
            <td class="tdlabel">快递经营类型：</td>
            <td colspan="3">
                <label id="operation_type" style="margin-left: 10px;"></label>
            </td>
        </tr>
        <tr>
            <td class="tdlabel">打印类型：</td>
            <td>
                <div id="print_select">
                    <input type="hidden" id="print_type" name="print_type" value="" >
                </div>
            </td>
        </tr>
        <tr id="pt">
            <td class="tdlabel">普通模板：</td>
            <td>
                <div id="pt_id_sel">
                    <input type="hidden" id="pt_id" value="" name="pt_id">
                </div>
            </td>
        </tr>
        <tr id="df">
            <td class="tdlabel">到付模板：</td>
            <td>
                <div id="df_id_sel">
                    <input type="hidden" id="df_id" value="" name="df_id">
                </div>
            </td>
        </tr>
        <tr id="rm" style = "display:none;">
            <td class="tdlabel">热敏模板：</td>
            <td>
                <div id="rm_id_sel">
                    <input type="hidden" id="rm_id" value="" name="rm_id">
                </div>
            </td>
        </tr>
        <tr>
            <td class="tdlabel">启用：</td>
            <td>
                <input type="checkbox" value="1"  onclick = "changeStatus()" class="bui-form-field" id="status_type" name="status_type" />
                <input type="hidden" value="0" class="bui-form-field" id="status" name="status" />
            </td>
        </tr>
        <tr>
            <td class="tdlabel">备注：</td>
            <td>
                <textarea class="span5" id="remark" name="remark"></textarea>
            </td>
        </tr>
        <tr>
            <td class="tdlabel"></td>
            <td>
                <div style="margin-top:20px;">
                    <button class="button button-primary" type="submit">提交</button>
                    <button class="button " type="reset">重置</button>
                </div>
            </td>
        </tr>
    </table>
</form>

<script>
    if (form_data_source_v !== '') {
        $('form#form_baseinfo').autofill(eval("(" + form_data_source_v + ")"));
    }

    $(function () {
        if ($("#status").val() == 1) {
            $("#status_type").attr("checked", true);
        }

        showTemplate($("#print_type").val());
        if (scene === 'edit') {
            $("#express_code").hide();
            $("#express_code_txt").show();
            $("#express_code_txt").text(express_code);
            $("#express_code").siblings("b,.auxiliary-text").remove();
        }

        getDataSelect('company');//快递公司
        getDataSelect('template', 'pt');//普通模版
        getDataSelect('template', 'df');//到付模版
        getDataSelect('template', 'rm');//热敏模版
    });

    var form = new BUI.Form.HForm({
        srcNode: '#form_baseinfo',
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

    $("#print_type").on("change", function () {
        print_type = $(this).val();
        showTemplate();
    });

    /*
     * 根据打印类型加载对应的模版选择框
     */
    function showTemplate() {
        if (print_type == 0) {
            $('#rm').hide();
            $('#pt').show();
            $('#df').show();
            $("#rm_set").hide();
        } else {
            $('#pt').hide();
            $('#df').hide();
            $('#rm').show();
            $("#rm_set").show();
        }
    }

    BUI.use(['bui/select', 'bui/data'], function (Select, Data) {
        var print_select = new Select.Select({
            render: '#print_select',
            valueField: '#print_type',
            items: [
                {text: '普通打印', value: '0'},
                {text: '直连热敏', value: '1'},
                {text: '云栈热敏', value: '2'},
                {text: '无界热敏', value: '3'}
            ],
            width: 195
        });
        print_select.render();

        print_select.on('change', function (ev) {
            print_type = ev.item.value;
            //重新加载快递公司
            $("#company_sel .bui-select").remove();
            getDataSelect('company');
            //重新加载热敏模版
            if (print_type != 1) {
                $("#rm_id_sel .bui-select").remove();
                getDataSelect('template', 'rm');//热敏模版
            }

            loadOperationType();

            is_refresh = 1;
        });
    });

    /*
     * 获取选择数据
     */
    function getDataSelect(data_type, template_type) {
        var url = '?app_act=base/shipping/get_data_select&data_type=' + data_type + '&_type=' + print_type;
        var render, valueField;
        if (data_type === 'company') {
            render = '#company_sel';
            valueField = '#company_code';
        } else {
            render = '#' + template_type + '_id_sel';
            valueField = '#' + template_type + '_id';
        }

        BUI.use(['bui/select', 'bui/data'], function (Select, Data) {
            //快递公司
            var store = new Data.Store({
                url: url,
                autoLoad: true
            }), select = new Select.Select({
                render: render,
                valueField: valueField,
                multipleSelect: false,
                store: store,
                width: 195
            });
            select.render();

            if (data_type === 'company') {
                $("#company_code").val(company_code);
                
                select.on('change', function (ev) {
                    if (ev.item != null && company_code != ev.item.value) {
                        company_code = ev.item.value;
                    }
                    getJdProvider();
                    is_refresh = 1;
                });

                loadOperationType();
            }
        });
    }

    /*
     * 获取京东承运商信息
     */
    function getJdProvider() {
        if (print_type == 3) {
            $.post("?app_act=remin/wujie/get_jd_provider", {company_code: company_code}, function (ret) {
                if (ret.status == 1) {
                    operation_type = ret.data.operation_type;
                } else {
                    operation_type = '';
                }
                loadOperationType();
            }, "json");
        }
    }

    /*
     * 加载快递经营类型
     */
    function loadOperationType() {
        if (print_type == 3) {
            var operation_type_txt = '';
            if (operation_type == 1) {
                operation_type_txt = '直营型';
            } else if (operation_type == 2) {
                operation_type_txt = '加盟型';
            }
            $("#operation_type").text(operation_type_txt);
            $("#operation_type").parent().parent().show();
        } else {
            $("#operation_type").parent().parent().hide();
        }
    }

    function changeStatus() {
        if ($("#status_type").is(':checked') == true) {
            $("#status").val(1);
        } else {
            $("#status").val(0);
        }
    }
</script>