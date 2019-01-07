<style>
    table .span4 { float: left; }
    .row { margin-left: 0px; }
</style>
<?php render_control('PageHead', 'head1',
    array('title'=>'新增模板',
        'links'=>array(
            array('url'=>'sys/print_templates/do_list', 'title'=>'模板列表', 'is_pop'=>false, 'pop_size'=>'800,600'),
        ),
        'ref_table'=>'table'
    ));?>

<table cellpadding="0" cellspacing="0" width="100%" style="table-layout:fixed;">
    <tr>
        <td style="width: 70%;">
            <OBJECT ID="LODOP" CLASSID="clsid:2105C259-1E0C-4534-8141-A753534CB4CA" width="100%" height="600">
                <param name="Caption" value="">
                <param name="Border" value="1">
                <param name="CompanyName" value="上海百胜软件">
                <param name="License" value="452547275711905623562384719084">
                <embed id="LODOP_EM" TYPE="application/x-print-lodop" width="100%" height="600" PLUGINSPAGE="">
            </OBJECT>
            <?php echo_print_plugin(0) ?>
        </td>
        <td style="width: 30%; overflow: hidden">
            <fieldset>
                <legend>纸张信息</legend>
                <div class="row">
                    向下偏: <input type="text" class="num" style="width: 60px"> 向右偏: <input type="text" class="num" style="width: 60px">
                    <br>
                    纸张宽: <input type="text" class="num" style="width: 60px"> 纸张高: <input type="text" class="num" style="width: 60px">
                </div>
                              <?php $company_data =  ds_get_select('express_company');?>
                  快递公司
                    <select id="company_code" name="company_code">
                        <?php foreach($company_data as $val):?>
                        <option value="<?php echo $val['company_code']; ?>"><?php echo $val['company_name']; ?></option>
                        <?php endforeach;?>
                    </select>
       

            <fieldset>
                <legend>收货人信息</legend>
                <div class="doc-content span16">
                    <div class="row show-grid">
                        <?php $i = 0; foreach ($response['variables']['receiving'] as $k => $v) { $i++ ?>
                            <div class="span4">
                                <label style="width: 100px">
                                    <input type="checkbox" value="<?php echo $k; ?>" class="print-item"/><?php echo $v; ?>
                                </label>
                            </div>
                            <?php if ($i % 3 == 0) echo '</div><div class="row">'; ?>
                        <?php } ?>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>发货信息</legend>
                <div class="doc-content span16">
                    <div class="row show-grid">
                        <?php $i = 0; foreach ($response['variables']['delivery'] as $k => $v) { $i++ ?>
                            <div class="span4">
                                <label style="width: 100px">
                                    <input type="checkbox" value="<?php echo $k; ?>" class="print-item"/><?php echo $v; ?>
                                </label>
                            </div>
                            <?php if ($i % 3 == 0) echo '</div><div class="row">'; ?>
                        <?php } ?>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>商品信息</legend>
                <div class="doc-content span16">
                    <div class="row show-grid">
                        <?php $i = 0; foreach ($response['variables']['detail'] as $k => $v) { $i++ ?>
                            <div class="span4">
                                <label style="width: 100px">
                                    <input type="checkbox" value="<?php echo $k; ?>" class="print-item"/><?php echo $v; ?>
                                </label>
                            </div>
                            <?php if ($i % 3 == 0) echo '</div><div class="row">'; ?>
                        <?php } ?>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>订单信息</legend>
                <div class="doc-content span16">
                    <div class="row show-grid">
                        <?php $i = 0; foreach ($response['variables']['record'] as $k => $v) { $i++ ?>
                            <div class="span4">
                                <label style="width: 100px">
                                    <input type="checkbox" value="<?php echo $k; ?>" class="print-item"/><?php echo $v; ?>
                                </label>
                            </div>
                            <?php if ($i % 3 == 0) echo '</div><div class="row">'; ?>
                        <?php } ?>
                    </div>
                </div>
            </fieldset>
        </td>
    </tr>
</table>

<?php echo load_js('xlodop.js'); ?>
<script>
    var g_print_vars = <?php echo json_encode($response['variables_all']);?>;
    $(function () {
        setTimeout(function () {
            DisplayDesign();
        }, 300);

        $('#submit').click(function () {
            var print_code = getProgram();
            var params = {
                express_id: g_express_id,
                express_company:$('#express_company').val(),
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
            xlodop.ADD_PRINT_TEXTA(itemName, 50, 50, 175, 25, text);
        });

        // 预览
        $('#preview').click(function () {
            $.post('<?php echo get_app_url('base/shipping/get_print_code')?>', {express_id: g_express_id}, function (data) {
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

    function DisplayDesign() {
        CreatePage();

        xlodop.setShowMode(ShowModeList.design);

        <?php if (isset($request['design']) && $request['design'] == 1):?>LODOP.PRINT_DESIGN();
        <?php else:?>
        LODOP.PRINT_SETUP();
        <?php endif;?>
    }

    function getProgram() {
        LODOP = getLodop();
        return LODOP.GET_VALUE("ProgramCodes", 0);
    }

    function prn_Preview() {
        LODOP = getLodop();
        eval(document.getElementById('form').value);
        LODOP.PREVIEW();
        LODOP = getLodop();
    }

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
