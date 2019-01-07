<style>
    table .span4 {
        float: left;
    }

    .row {
        margin-left: 0px;
    }

</style>
<?php $print = $response['data']['print']; ?>
<div class="search_table" style="margin-top: 5px">
    <form>
        <input type="hidden" id="express_id" value="<?php echo $response['data']['print']['express_id']; ?>"/>
        <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td style="width: 60%">
                    <!-- 工具栏 -->
                    <div style="width: 100%; background: #EFEFEF">
                        <div style="text-align: right; width: 100%; ">
                            <b><?php echo $print['express_name']; ?></b>——打印模板设计
                            <input type="button" id="preview" value="预览" class="button button-primary"/>
                            <input type="button" id="delete" onclick="removePrintVar();" value="移除选中" class="button button-primary"/>
                            <input type="button" value="保 存" id="submit" class="button button-primary"/>
                        </div>
                    </div>
                    <!-- LODOP -->
                    <div>
                        <OBJECT ID="LODOP" CLASSID="clsid:2105C259-1E0C-4534-8141-A753534CB4CA" width="100%" height="97%">
                            <param name="Caption" value="">
                            <param name="Border" value="1">
                            <param name="CompanyName" value="上海百胜软件">
                            <param name="License" value="452547275711905623562384719084">
                            <embed id="LODOP_EM" TYPE="application/x-print-lodop" width="800" height="600" PLUGINSPAGE="">
                        </OBJECT>
                        <?php echo_print_plugin(0) ?>
                    </div>
                </td>
                <td valign="top" align="left" style="text-align:left; padding-left: 15px">
                    <fieldset>
                        <legend>基本信息</legend>
                        模板名称：<input type="text" value="<?php echo $print['express_name']; ?>" id="tplName"/>
                    </fieldset>
                    <fieldset>
                        <legend>纸张信息</legend>
                        <div class="row">
                            向下偏: <input type="text" class="num" style="width: 60px">向右偏: <input type="text" class="num" style="width: 60px">
                            <br>
                            纸张宽: <input type="text" class="num" style="width: 60px">纸张高: <input type="text" class="num" style="width: 60px">
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>打印项</legend>
                        <div class="doc-content span16">
                            <div class="row show-grid">
                                <?php
                                $i = 0;
                                foreach ($response['data']['print_vars'] as $_k => $_v): $i++
                                    ?>
                                    <div class="span4">
                                        <label style="width: 100px">
                                            <input type="checkbox" value="<?php echo $_k; ?>" class="print-item"/><?php echo $_v; ?>
                                        </label>
                                    </div>
                                    <?php if ($i % 3 == 0) echo '</div><div class="row">'; ?>
                                <?php endforeach; ?>
                            </div>
                            <hr/>
                            商品明细<br/>
                            <select>
                                <option>商品代码</option>
                                <option>规格1代码</option>
                                <option>规格2代码</option>
                                <option>规格代码</option>
                                <option>规格名称</option>
                                <option>数量</option>
                            </select>
                            <input type="button" class="button" value="添加" id="addDetail"/>
                            <hr/>
                            <input type="button" class="button" value="自定义打印项" id="addText"/>
                        </div>
                    </fieldset>
                </td>
            </tr>
            <tr style="display:none;">
                <td>模板代码:
                    <textarea name="form" id="form" style="width: 100%; height:30px;" readonly="readonly"><?php echo $response['data']['print']['print']; ?></textarea>
                </td>
            </tr>
        </table>
    </form>
</div>
<?php echo load_js('xlodop.js'); ?>
<script type="text/javascript">
    var g_express_id = <?php echo $response['data']['print']['express_id']; ?>;
    var g_print_vars = <?php echo json_encode($response['data']['print_vars']); ?>;

    $(function () {
        setTimeout(function () {
            DisplayDesign();
        }, 300);

        $('#submit').click(function () {
            var print_code = getProgram();
            var params = {
                express_id: g_express_id,
                print: print_code
            };

            $.post('?app_act=base/shipping/do_edit_print&app_fmt=json', params, function (data) {
                var ret = $.parseJSON(data);
                alert(ret.message);
            });
        });

        $('.print-item').change(function () {
            var itemName = $(this).val();
            var text = g_print_vars[itemName];
            if ($(this).attr('checked')) {
                xlodop.ADD_PRINT_TEXTA(itemName, 50, 50, 175, 25, text);
            } else {
                xlodop.SET_PRINT_STYLEA(itemName, 'Deleted', true);
            }
        });

        $('.print-d-item').change(function () {
            var itemName = 'print-d-item';
            var text = $(this).attr('_text');
            var checked = $(this).attr('checked');
            if (!xlodop.itemExist(itemName)) { // 不存在
                if (checked) {
                    xlodop.ADD_PRINT_TEXTA(itemName, 50, 50, 175, 50, text);
                    xlodop.dataDetailItemAttr('print-d-item', '');
                }
            } else {
                if (checked) {
                    text = xlodop.itemAttr('ItemContent', itemName) + ' ' + text;
                    xlodop.SET_PRINT_STYLEA(itemName, 'ItemContent', text);
                } else {

                }
            }
        });
        // 预览
        $('#preview').click(function () {
            $.post('<?php echo get_app_url('base/shipping/get_print_code') ?>', {express_id: g_express_id}, function (data) {
                var ret = eval('(' + data + ')');
                console.info(ret.data);
                eval(ret.data);
                LODOP.PREVIEW();
                DisplayDesign();
            });
        });

        $('#addText').click(function () {
            var itemName = '自定义文本';
            xlodop.ADD_PRINT_TEXTA('_txt:' + Date.parse(new Date()), 50, 50, 150, 25, itemName);
        });


    });

    //标识是否修改了信息(即是否需要离开页面时的提示)
    var g_isModified = false;
    function cb_isModified() {
        return !($('#form').text() == getProgram());
    }
</script>

<script type="text/javascript">

    var LODOP;

    function CreatePage() {
        LODOP = getLodop();
        eval($("#form").val());   //初始化数据
    }
    ;
    function DisplayDesign() {
        CreatePage();

        xlodop.setShowMode(ShowModeList.design);

<?php if (isset($request['design']) && $request['design'] == 1): ?>LODOP.PRINT_DESIGN();
<?php else: ?>
            LODOP.PRINT_SETUP();
<?php endif; ?>
    }
    ;
    function getProgram() {
        LODOP = getLodop();
        return LODOP.GET_VALUE("ProgramCodes", 0);
    }
    ;
    function prn_Preview() {
        LODOP = getLodop();
        eval(document.getElementById('form').value);
        LODOP.PREVIEW();
        LODOP = getLodop();
    }
    ;

    //修正toFixed的bug
    Number.prototype.toFixed = function (exponent) {
        return parseInt(this * Math.pow(10, exponent) + 0.5) / Math.pow(10, exponent);
    }

    function addPrintVar() {
        LODOP = getLodop();
        var title = $('#vars').val();
        var varName = title;
        window._u_hint = false;
        LODOP.ADD_PRINT_TEXTA(title, 10, 250, 175, 30, varName);
        setTimeout(function () {
            window._u_hint = true;
        }, 200);
    }

    function removePrintVar() {
        LODOP.SET_PRINT_STYLEA('Selected', 'Deleted', true);
    }
</script>