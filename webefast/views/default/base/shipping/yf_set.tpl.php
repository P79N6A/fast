<!-- 运费设置 -->
<form action="?app_act=base/shipping/do_edit_freight&app_fmt=json" id="form_yf_set" method="post">
    <table class="form_tbl">
        <tr>
            <td class="tdlabel">首重：</td>
            <td >
                <input type="hidden" id="express_id" name="express_id" value=""/>
                <input type="text" value="" class="input-normal bui-form-field" id="base_weight" name="base_weight" data-rules="{required: true}"/>千克
                <b style="color:red"> *</b>
            </td>
        </tr>
        <tr>
            <td class="tdlabel">首重单价：</td>
            <td>
                <input type="text" value="" class="input-normal bui-form-field" id="base_fee" name="base_fee" data-rules="{required: true}"/>元 / 首重
                <b style="color:red"> *</b>
            </td>
        </tr>
        <tr>
            <td class="tdlabel">续重：</td>
            <td>
                <input type="text" value="" class="input-normal bui-form-field" id="per_weight" name="per_weight" data-rules="{required: true}"/>千克
                <b style="color:red"> *</b>
            </td>
        </tr>
        <tr>
            <td class="tdlabel">免费续重：</td>
            <td>
                <input type="text" value="" class="input-normal bui-form-field" id="free_per_weight" name="free_per_weight" data-rules="{required: true}"/>千克
                <b style="color:red"> *</b>
            </td>
        </tr>
        <tr>
            <td class="tdlabel">续重单价：</td>
            <td>
                <input type="text" value="" class="input-normal bui-form-field" id="per_fee" name="per_fee" data-rules="{required: true}"/>元 / 续重
                <b style="color:red"> *</b>
            </td>
        </tr>
        <tr>
            <td class="tdlabel">续重规则：</td>
            <td colspan="3" style="text-align: left;">
                <input type="radio" value="0.00" class="bui-form-field" name="per_rule"  id="per_rule"/>
                实重【超出首重的重量 * 续重单价】<br/>
                <input type="radio" value="1.00" class="bui-form-field"  name="per_rule" id="per_rule"/>
                半重【超出首重的重量不足0.5Kg时讲按照0.5Kg进行收费,超过则按照1Kg的进行收费】
                <br/>
                <input type="radio" value="2.00" class="bui-form-field" name="per_rule" id="per_rule"/>
                过重【无论超出首重多少都按照1Kg进行收费】
                <br/>
                <div style="color:green">
                    举例：某配送方式首重1KG，首重单价10元，续重单价10元/1KG，包裹实际重量为2.2KG； 若为实重，则运费为10+1.2*10=22元；若为半重，则运费为10+1.5*10=25元； 若为过重，则运费为10+2*10=30元。)
                </div>
            </td>
        </tr>
        <tr>
            <td class="tdlabel">免费额度：</td>
            <td colspan="3">
                <input type="text" value="" class="input-normal bui-form-field" id="free_fee" name="free_fee" data-rules="{required: true}"/> 元
                <b style="color:red"> *</b>
            </td>
        </tr>
        <tr>
            <td class="tdlabel">折扣：</td>
            <td colspan="3">
                <input type="text" value="" class="input-normal bui-form-field" id="zk" name="zk" data-rules="{required: true}"/>
                <b style="color:red"> *</b>
            </td>
        </tr>
        <tr>
            <td class="tdlabel">描述：</td>
            <td colspan="3">
                <textarea width="300" height="100" id="remark" name="remark"></textarea>
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
    var form_data_source_v = $("#form_data_source_v").html();
    if (form_data_source_v != '') {
        $('form#form_yf_set').autofill(eval("(" + form_data_source_v + ")"));
    }

    var form = new BUI.Form.HForm({
        srcNode: '#form_yf_set',
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
</script>