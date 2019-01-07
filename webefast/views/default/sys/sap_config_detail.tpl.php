<style>
    .add_btn,
    .minus_btn{ display:inline-block; float:right; cursor:pointer; margin:0;}
    .add_btn img,
    .minus_btn img{ vertical-align:text-bottom; margin-right:5px;}
</style>


<?php require_lib('util/oms_util', true); ?>
<script src="assets/js/jquery.formautofill2.min.js"></script>
<div id="form1_data_source" style="display:none;"><?php
    if (isset($response['form1_data_source'])) {
        echo $response['form1_data_source'];
    }
    ?></div> 
<form  id="form1" action="?app_act=sys/sap_config/do_<?php echo $response['app_scene'] ?>&app_fmt=json" method="post">

    <table id="form_tbl" border="1px" bordercolor="#dddddd">
        <tr style="background-color:#f5f5f5;">
            <td class="tdlabel">&nbsp;&nbsp;基本信息</td>
            <td colspan="3">
            </td>
        </tr>
        <tr>
            <td class="tdlabel" width="300px" style="text-align:right;">SAP配置名称&nbsp;&nbsp;</td>
            <td width="700px">
                <input type="hidden" value="" name="sap_config_id" />
                <input type="text" value="" name="sap_config_name" data-rules="{required: true}" class="input-normal bui-form-field" />
            </td>
        </tr>
        <tr>
            <td class="tdlabel" width="300px" style="text-align:right;">应用上线日期&nbsp;&nbsp;</td>
            <td width="700px">
                <?php if ($response['app_scene'] == 'add') { ?>
                    <input id="online_time"  type="text" value="<?php echo date('Y-m-d'); ?>" name="online_time" data-rules="{required : true}" class="calendar"/>
                <?php } else { ?>
                    <input id="online_time"  type="text"  name="online_time" data-rules="{required : true}" class="calendar"/>
                <?php } ?>
                <span style="color:red;">在上线日期之后发货或收货的单据，才会上传到SAP</span>
            </td>
        </tr>
    </table>
    <br/>
    <table id="form_tbl" border="1px"  bordercolor="#dddddd">
        <tr style="background-color:#f5f5f5;">
            <td class="tdlabel">&nbsp;&nbsp;参数配置</td>
            <td></td>
        </tr>
        <tr>
            <td class="tdlabel" width="300px" style="text-align:right;">IP&nbsp;&nbsp;</td>
            <td width="700px">
                <input type="text" value="" name="sap_address" >&nbsp;&nbsp;
                <!--<input class="button" type="button" value="测试" name="test"/>-->
            </td>
        </tr>
        <tr>
            <td class="tdlabel" width="300px" style="text-align:right;">实例编号&nbsp;&nbsp;</td>
            <td width="700px">
                <input type="text" value="" name="instance_number"/>
            </td>
        </tr>
        <tr>
            <td class="tdlabel" width="300px" style="text-align:right;">客户端&nbsp;&nbsp;</td>
            <td width="700px">
                <input type="text" value="" name="client"/>
            </td>
        </tr>
        <tr>
            <td class="tdlabel" width="300px" style="text-align:right;">账号&nbsp;&nbsp;</td>
            <td width="700px">
                <input type="text" value="" name="account"/>
            </td>
        </tr>
        <tr>
            <td class="tdlabel" width="300px" style="text-align:right;">密码&nbsp;&nbsp;</td>
            <td width="700px">
                <input type="password" value="" name="password"/>
            </td>
        </tr>
    </table>
    <br/>
    <?php if ($response['app_scene'] == 'add') { ?>
        <table id="store" border="1px" bordercolor="#dddddd">
            <tr style="background-color:#f5f5f5;">
                <td class="tdlabel">&nbsp;&nbsp;系统仓库列表</td>
                <td>&nbsp;&nbsp;外部SAP的仓库代码<p class="add_btn" onclick = "add('store')"> <img src="assets/images/plus.png" />添加</p></td>
            </tr>
            <tr>
                <td class="tdlabel" width="300px" >&nbsp;&nbsp;<select  name="store[0][efast_store_code]"  data-rules="{required: true}" style="width:200px;" >
                        <option value="">请选择</option>
                        <?php
                        $list = $response['store'];
                        foreach ($list as $k => $v) {
                            ?>
                            <option value="<?php echo $v['store_code'] ?>"><?php echo $v['store_name'] ?></option>
                        <?php } ?>
                    </select></td>
                <td width="700px" >
                    <input type="text" value="" name="store[0][sap_store_code]" data-rules="{required: true}" class="input-normal bui-form-field" />
                    &nbsp;&nbsp;
                </td>
            </tr>

        </table>
    <?php } ?>
    <?php if ($response['app_scene'] == 'edit') { ?>
        <table id="store" border="1px" bordercolor="#dddddd">
            <tr style="background-color:#f5f5f5;">
                <td class="tdlabel">&nbsp;&nbsp;系统仓库列表</td>
                <td colspan="3">&nbsp;&nbsp;外部SAP的仓库代码
                    <p class="add_btn" onclick = "add('store')"> <img src="assets/images/plus.png" />添加</p>
                </td>
            </tr>
            <?php 
            foreach ($response['sap_store'] as $key => $value) {
                $store_select = '<option value="">请选择</option>';
                foreach ($response['store'] as $k => $v) {
                    if ($v['store_code'] === $value['shop_store_code']) {
                        $store_select.='<option value="' . $v['store_code'] . '" selected="selected" >' . $v['store_name'] . '</option>';
                    } else {
                        $store_select.='<option value="' . $v['store_code'] . '">' . $v['store_name'] . '</option>';
                    }
                }

                echo '<tr>
<td class="tdlabel" width="300px" ;">&nbsp;&nbsp;<select  name="store[' . $key . '][efast_store_code]" style="width:200px;">
' . $store_select . '
</select></td>
<td width="700px">
&nbsp;&nbsp;
<input type="text" value=' . $value["outside_code"] . ' class="input-normal bui-form-field"  name="store[' . $key . '][sap_store_code]"  param="check"  data-rules="{required: true}"/>
<p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p>
</td>
</tr>';
            }
            ?>
        </table>
        <?php } ?>
    <br/>
        <?php if ($response['app_scene'] == 'add') { ?>
        <table id="shop" border="1px" bordercolor="#dddddd">
            <tr style="background-color:#f5f5f5;">
                <td class="tdlabel">&nbsp;&nbsp;系统店铺列表</td>
                <td>&nbsp;&nbsp;外部SAP的店铺代码<p class="add_btn" onclick = "add('shop')"> <img src="assets/images/plus.png" />添加</p></td>
            </tr>
            <tr>
                <td class="tdlabel" width="300px" >&nbsp;&nbsp;<select  name="shop[0][efast_shop_code]"  data-rules="{required: true}" style="width:200px;" >
                        <option value="">请选择</option>
    <?php
    $list = $response['shop'];
    foreach ($list as $k => $v) {
        ?>
                            <option value="<?php echo $v['shop_code'] ?>"><?php echo $v['shop_name'] ?></option>
                        <?php } ?>
                    </select></td>
                <td width="700px" >
                    <input type="text" value="" name="shop[0][sap_shop_code]" data-rules="{required: true}" class="input-normal bui-form-field" />
                    &nbsp;&nbsp;
                </td>
            </tr>

        </table>
<?php } ?>
<?php if ($response['app_scene'] == 'edit') { ?>
        <table id="shop" border="1px" bordercolor="#dddddd">
            <tr style="background-color:#f5f5f5;">
                <td class="tdlabel">&nbsp;&nbsp;系统店铺列表</td>
                <td colspan="3">&nbsp;&nbsp;外部SAP的店铺代码
                    <p class="add_btn" onclick = "add('shop')"> <img src="assets/images/plus.png" />添加</p>
                </td>
            </tr>
    <?php
    foreach ($response['sap_shop'] as $key => $value) {
        $shop_select = '<option value="">请选择</option>';
        foreach ($response['shop'] as $k => $v) {
            if ($v['shop_code'] === $value['shop_store_code']) {
                $shop_select.='<option value="' . $v['shop_code'] . '" selected="selected" >' . $v['shop_name'] . '</option>';
            } else {
                $shop_select.='<option value="' . $v['shop_code'] . '">' . $v['shop_name'] . '</option>';
            }
        }

        echo '<tr>
<td class="tdlabel" width="300px" ;">&nbsp;&nbsp;<select  name="shop[' . $key . '][efast_shop_code]" style="width:200px;">
' . $shop_select . '
</select></td>
<td width="700px">
<input type="text" value=' . $value["outside_code"] . ' class="input-normal bui-form-field"  name="shop[' . $key . '][sap_shop_code]"  param="check"  data-rules="{required: true}"/>
<p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p>
</td>
</tr>';
    }
    ?>
        </table>
        <?php } ?>
    <br/>
    <table id="form_tbl" width="1020px" border="1px" bordercolor="#dddddd">
        <tr style="background-color:#f5f5f5;">
            <td class="tdlabel" colspan="2" width="100%" style="text-align:left;">&nbsp;&nbsp;系统支持业务</td>
        </tr>
        <tr>
            <td class="tdlabel"  colspan="2" width="300px" style="text-align:left;">获取SAP调整单，生成系统调整单并影响库存</td>
        </tr>
        <tr>
            <td class="tdlabel" colspan="2" width="300px" style="text-align:left;">已发货销售订单上传 </td>
        </tr>
        <tr>
            <td class="tdlabel" colspan="2" width="300px" style="text-align:left;">已入库退货单上传</td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="tdlabel"><button id="submit" class="button button-primary" type="submit">提交</button></td>

            <td colspan="3"><button id="reset" class="button " type="reset">重置</button></td>
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
    var form1_data_source_v = $("#form1_data_source").html();
    if (form1_data_source_v != '') {
        $('form#form1').autofill(eval("(" + form1_data_source_v + ")"));
    }
    var form = new BUI.Form.HForm({
        srcNode: '#form1',
        submitType: 'ajax',
        callback: function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
        }
    }).render();
    //增加
<?php
$list = $response['store'];
$store_select = '<option value="">请选择</option>';
foreach ($list as $k => $v) {
    $store_select.='<option value="' . $v['store_code'] . '">' . $v['store_name'] . '</option>';
}
$list = $response['shop'];
$shop_select = '<option value="">请选择</option>';
foreach ($list as $k => $v) {
    $shop_select.='<option value="' . $v['shop_code'] . '">' . $v['shop_name'] . '</option>';
}
?>
    function add(type) {
//     var erp_system = $("input[name=erp_system]:checked").attr('value');


        var store_select = '<?php echo $store_select ?>';
        var shop_select = '<?php echo $shop_select ?>';
        if (type == 'shop') {
            var i = $("#shop").find("tr").length - 1;
            $("#shop").append('<tr><td class="tdlabel" width="300px" ;">&nbsp;&nbsp;<select  name="shop[' + i + '][efast_shop_code]" style="width:200px;">' +
                    shop_select + '</select></td><td width="700px">' +
                    '<input type="text" value="" class="input-normal bui-form-field"  name="shop[' + i + '][sap_shop_code]" data-rules="{required: true}"/><p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p></td></tr>');
        }

        if (type == 'store') {
            var i = $("#store").find("tr").length - 1;
            $("#store").append('<tr><td class="tdlabel" width="300px" ;">&nbsp;&nbsp;<select  name="store[' + i + '][efast_store_code]" style="width:200px;">' +
                    store_select + '</select></td><td width="700px">' +
                    '<input type="text" value="" class="input-normal bui-form-field" name="store[' + i + '][sap_store_code]" data-rules="{required: true}"/><p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p></td></tr>');
        }
    }
    function del(item) {
        $(item).parent("td").parent("tr").remove();
    }
</script>
<?php echo load_js('comm_util.js') ?>
