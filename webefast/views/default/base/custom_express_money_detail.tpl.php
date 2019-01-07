<style>
    .add_btn,
    .minus_btn{ display:inline-block; float:right; cursor:pointer; margin:0;}
    .add_btn img,
    .minus_btn img{ vertical-align:text-bottom; margin-right:5px;}
    .panel-body{ padding:0;}
    .table{ margin-bottom:1;}
    .table tr{ padding:5px 0;}
    .table th, .table td{ border:1px solid #dddddd; vertical-align:middle;}
    .table th{ width:8.3%; text-align:center;}
    .table td{ width:23%; padding:0 1%;}
    .row{ margin-left:0; padding: 2px 8px; border: 1px solid #ddd;}
    .bui-grid-header{ border-top:none;}
    p{ margin:0;}
    b{ vertical-align:middle;}
</style>

<?php
require_lib('util/oms_util', true);
render_control('PageHead', 'head1', array('title' => '分销商固定运费维护',
    'links' => array(
        // array('type' => 'js', 'js' => 'report_excel()', 'title' => '导出'), 
        array('url' => 'base/custom/do_list', 'target' => '_self', 'title' => '返回分销商列表'),
    ),
    'ref_table' => 'table'
));
?>
<?php echo load_js("baison.js,record_table.js", true); ?>

<form  id="form1" action="?app_act=base/custom/save_express_money&app_fmt=json" method="post">
    <script>
        var data = [
            {
                "name": "custom_name",
                "title": "名称",
                "value": "<?php echo $response['data']['custom_name'] ?>",
                "type": "input",
            },
            {
                "name": "contact_person",
                "title": "联系人",
                "value": "<?php echo $response['data']['contact_person']; ?>",
                "type": "input",
            },
            {
                "name": "mobile",
                "title": "手机号",
                "value": "<?php echo $response['data']['mobile']; ?>",
                "type": "input",
            },
            {
                "name": "tel",
                "title": "联系电话",
                "value": "<?php echo $response['data']['tel']; ?>",
                "type": "input",
            },
            {
                "name": "settlement_method_name",
                "title": "运费结算方式",
                "value": "<?php echo $response['data']['settlement_method_name']; ?>",
                "type": "input",
            },
            {
                "name": "fixed_money",
                "title": "结算运费",
                "value": "<?php echo $response['data']['fixed_money']; ?>",
                "type": "input",
            },
        ];

        jQuery(function () {
            var r = new record_table();
            r.init({
                "id": "panel_html",
                "data": data,
            });
            $('[name = panel_html]').hide();
        })
    </script>   

    <div class="panel record_table" id="panel_html">

    </div>

    <script src="assets/js/jquery.formautofill2.min.js"></script>
    <input type="hidden" name="custom_code" value="<?php echo $response['data']['custom_code']; ?>" />

    <table id="express" border="1px" bordercolor="#dddddd" style=" width: 600px;">
        <tr style="background-color:#f5f5f5;">
            <td class="tdlabel">&nbsp;&nbsp;快递方式</td>
            <td colspan="3">&nbsp;&nbsp;固定运费
                <p class="add_btn" onclick = "add()"> <img src="assets/images/plus.png" />添加</p>
            </td>
        </tr>
        <?php
        foreach ($response['custom_express'] as $key => $value) {
//            $express_select = '<option value="">请选择</option>';
            foreach ($response['express'] as $k => $v) {
                if ($v['express_code'] === $value['express_code']) {
                    $express_select.='<option value="' . $v['express_code'] . '" selected="selected" >' . $v['express_name'] . '</option>';
                } else {
                    $express_select.='<option value="' . $v['express_code'] . '">' . $v['express_name'] . '</option>';
                }
            }
            echo '<tr>
<td class="tdlabel" width="300px" ;">&nbsp;&nbsp;<select  name="express[' . $key . '][express_code]" style="width:200px;">
' . $express_select . '
</select></td>
<td width="700px">
&nbsp;&nbsp;
<input type="text" value="' . $value["express_money"] . '" class="input-normal bui-form-field"  name="express[' . $key . '][express_money]"  param="check"  data-rules="{required: true}"/>
<p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p>
</td>
</tr>';
        }
        ?>
    </table>
    <table>
        <tr>
            <td class="tdlabel"><button id="submit" class="button button-primary" type="submit">提交</button></td>
        </tr>
    </table>
</form>
<style>

    td {
        border-top: 1px solid #dddddd;
        line-height: 20px;
        padding: 4px;
        text-align: left;
        vertical-align: top;
    }
</style>
<script>
    BUI.use('bui/calendar', function (Calendar) {
        var datepicker = new Calendar.DatePicker({
            trigger: '.calendar',
            showTime: true,
            autoRender: true
        });
    });
    var form = new BUI.Form.HForm({
        srcNode: '#form1',
        submitType: 'ajax',
        callback: function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            location.reload();
        }
    }).render();
    //增加
<?php
$list = $response['express'];
$express_select = '<option value="">请选择</option>';
foreach ($list as $k => $v) {
    $express_select.='<option value="' . $v['express_code'] . '">' . $v['express_name'] . '</option>';
}
//$list = $response['shop'];
//$shop_select = '<option value="">请选择</option>';
//foreach ($list as $k => $v) {
//    $shop_select.='<option value="' . $v['shop_code'] . '">' . $v['shop_name'] . '</option>';
//}
?>
    function add() {
        var express_select = '<?php echo $express_select ?>';
        var i = $("#express").find("tr").length - 1;
        $("#express").append('<tr><td class="tdlabel" width="300px" ;">&nbsp;&nbsp;<select  name="express[' + i + '][express_code]" style="width:200px;">' +
                express_select + '</select></td><td width="700px">' +
                '&nbsp;&nbsp;&nbsp;<input type="text" value="" class="input-normal bui-form-field" name="express[' + i + '][express_money]" data-rules="{required: true}"/><p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p></td></tr>');

    }
    function del(item) {
        $(item).parent("td").parent("tr").remove();
    }
</script>
<?php echo load_js('comm_util.js') ?>
