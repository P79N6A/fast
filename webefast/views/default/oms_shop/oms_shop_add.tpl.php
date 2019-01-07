<style>
    .panel-body {padding: 0;}
    .panel-body table {margin: 0; }
    #panel_baseinfo input{width: 140px;}
    #panel_baseinfo select{width: 145px;}
    .sear_ico{ top:3px;}
    .form-horizontal .controls {margin-left: 0px;float:none;}
    .shdz .valid-text{ display:inline-block; width:194px;}
</style>

<?php
render_control('PageHead', 'head1', array('title' => '新增订单',
    'links' => array(),
    'ref_table' => 'table'
));
?>
<form id="form1" method="post" action="?app_act=oms_shop/oms_shop/add_action" tabindex="0" style="outline: none;">
    <div class="panel">
        <div class="panel-header clearfix">
            <h3 class="pull-left">订单信息</h3>
            <div class="pull-right"></div>
        </div>
        <div class="panel-body" id="panel_baseinfo">
            <table cellspacing="0" class="table table-bordered">
                <tbody>
                    <tr >
                        <td >销售平台：</td>
                        <td>
                            <select id="sale_channel_code" name="sale_channel_code" data-rules="{required : true}">
                                <?php foreach ($response['arr_source'] as $k => $v) { ?>
                                    <option  value ="<?php echo $k ?>"><?php echo $v ?></option>
                                <?php } ?>
                            </select>
                            <script>$("#sale_channel_code").val("taobao");</script>
                        </td>
                        <td>所属店铺：</td>
                        <td>
                            <select id="shop_code" name="shop_code" data-rules="{required : true}">
                                <option value ="">请选择</option>
                                <?php $list = $response['arr_shop'];
                                foreach ($list as $k => $v) { ?>
                                    <option value="<?php echo $v['shop_code'] ?>"><?php echo $v['shop_name'] ?></option>
<?php } ?>
                                <script>$("#shop_code").val("<?php echo $response['data']['shop_code']; ?>");</script>
                            </select>
                        </td>
                        <td >交易号：</td>
                        <td><input id="deal_code" type="text" value="" style="border-color:red" name="deal_code" data-rules="{required : true}"></td>

                        <td>支付方式：</td>
                        <td>
                            <select name="pay_code" onchange = "changeType()" id="pay_code" data-rules="{required : true}">
                                <?php $list = oms_tb_all('base_pay_type', array('status' => 1));
                                foreach ($list as $k => $v) { ?>
                                    <option   value="<?php echo $v['pay_type_code'] ?>"><?php echo $v['pay_type_name'] ?></option>
<?php } ?>
                            </select>
                            <script>$("#pay_code").val("alipay");</script>
                        </td>
                    </tr>
                    <tr>
                        <td >下单时间：</td>
                        <td><input id="record_time" type="text" value="<?php echo date('Y-m-d H:i:s'); ?>" name="record_time" data-rules="{required : true}" class="calendar"></td>

                        <td>支付类型：</td>
                        <td>
                            <select name="pay_type" id="pay_type" data-rules="{required : true}">
                                <?php
                                require_model('oms/SellRecordModel');
                                $sell_obj = new sellRecordModel();
                                $list = $sell_obj->pay_type;
                                foreach ($list as $k => $v) {
                                    ?>
                                    <option id = "<?php echo $k ?>_type"  value="<?php echo $k ?>"><?php echo $v ?></option>
<?php } ?>
                            </select>
                        </td> 
                        <td>配送方式：</td>
                        <td>
                            <select name="express_code" id="express_code" data-rules="{required : true}">
                                <option value ="">请选择</option>
                                <?php $list = oms_tb_all('base_express', array('status' => 1));
                                foreach ($list as $k => $v) {
                                    ?>
                                    <option value="<?php echo $v['express_code'] ?>"><?php echo $v['express_name'] ?></option>
<?php } ?>
                            </select>
                        </td>            
                        <td>会员昵称：</td>
                        <td>
                            <div class="span4 controls">
                                <input id="buyer_name" type="text" value="" name="buyer_name" data-rules="{required : true}">
                                <img id="buyer_code_select_img" class="sear_ico" src="assets/img/search.png">
                                <input id="buyer_code" class="bui-form-field" type="hidden" name="buyer_code" value="" style="display: none;" aria-disabled="false">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <!--<td><input id="buyer_name" type="text" value="" name="buyer_name" data-rules="{required : true}"></td>-->                
                        <td>发货仓库：</td>
                        <td>
                            <select name="store_code" id="store_code" data-rules="{required : true}">
                                <option value ="">请选择</option>
<?php $list = oms_tb_all('base_store', array('status' => 1));
foreach ($list as $k => $v) { ?>
                                    <option value="<?php echo $v['store_code'] ?>"><?php echo $v['store_name'] ?></option>
<?php } ?>
                            </select>
                        </td>
                        <td>运费：</td>
                        <td><input id="express_money" type="text" value="" name="express_money" data-rules="{number : true}"></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="panel">
        <div class="panel-header clearfix">
            <h3 class="pull-left">收货信息</h3>
            <div class="pull-right"></div>
        </div>
        <div class="panel-body">
            <table cellspacing="0" class="table table-bordered">
                <tbody>
                    <tr>
                        <td>收货人：</td>
                        <td><input id="receiver_name" type="text" value="" name="receiver_name" data-rules="{required : true}"></td>
                        <td>手机：</td>
                        <td><input id="receiver_mobile" type="text" value="" name="receiver_mobile" data-rules="{mobile : true}"></td>
                        <td>固定电话：</td>
                        <td><input id="receiver_phone" type="text" value="" name="receiver_phone" data-rules="{regexp : [/((^\s*$)|^((\d{7,8})|(\d{4}|\d{3})-(\d{7,8})|(\d{4}|\d{3})-(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1})|(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1}))$)/,'不是有效的固定电话号码']}"></td>
                    </tr>
                    <tr>
                        <td>收货地址：</td>
                        <td colspan="5" class="shdz">
                            <select id="country" name="country" data-rules="{required : true}">
                                <option value ="">请选择国家</option>
<?php foreach ($response['area']['country'] as $k => $v) { ?>
                                    <option  value ="<?php echo $v['id']; ?>"  ><?php echo $v['name']; ?></option>
<?php } ?>
                            </select>
                            <select id="province" name="province" data-rules="{required : true}"></select>
                            <select id="city" name="city" data-rules="{required : true}"></select>
                            <select id="district" name="district"></select>
                            <select id="street" name="street"></select><br>
                            <span class="valid-text">&nbsp; </span>
                        </td>
                    </tr>
                    <tr>
                        <td>详细地址：</td>
                        <td colspan="3"><input id="receiver_addr" type="text" name="receiver_addr" data-rules="{required : true}" style="width: 99%;"></td>
                        <td>邮编：</td>
                        <td><input id="receiver_zip_code" type="text" value="" name="receiver_zip_code" data-rules=""/></td>
                    </tr>
                    <tr>
                        <td>订单备注：</td>
                        <td colspan="5"><textarea id="order_remark" style="width: 99%; height: 39px;" name="order_remark"></textarea></td>
                    </tr>
                    <tr>
                        <td>仓库留言：</td>
                        <td colspan="5"><textarea id="store_remark" style="width: 99%; height: 39px;" name="store_remark"></textarea></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div style="text-align: center;">
        <button class="button button-primary" type="submit">下一步</button>
    </div>
</form>
<?php echo load_js('comm_util.js') ?>

<script>
    var selectPopWindowshelf_code = {
        dialog: null,
        callback: function (value, id, code, name) {
            $.ajax({
                type: "GET",
                url: "?app_act=crm/customer/get_default_addr",
                async: false,
                data: {customer_code: value[0]['customer_code'], app_fmt: 'json'},
                dataType: "json",
                success: function (data) {
                    if (data.status == 1) {
                        $("#receiver_name").val(data.data['name']);
                        $("#receiver_mobile").val(data.data['tel']);
                        $("#receiver_phone").val(data.data['home_tel']);
                        $("#receiver_addr").val(data.data['address']);
                        $("#receiver_zip_code").val(data.data['zipcode']);
                        $.ajaxSetup({async: false});
                        fill_select(change_after, 'country', data.data['country']);
                        fill_select(change_after, 'province', data.data['province']);
                        fill_select(change_after, 'city', data.data['city']);
                        fill_select(change_after, 'district', data.data['district']);
                        fill_select(change_after, 'street', data.data['street']);
                        $.ajaxSetup({async: true});
                    }
                }
            });
            $("#buyer_code").val(value[0]['customer_code']);
            $("#buyer_name").val(value[0]['customer_name']);
            if (selectPopWindowshelf_code.dialog != null) {
                selectPopWindowshelf_code.dialog.close();
            }
        }
    };
    function change_after(param) {
        $("#" + param).change();
    }
    function fill_select(callback, param, value) {
        $("#" + param).val(value);
        callback(param);
    }
    $('#buyer_code_select_pop,#buyer_code_select_img').click(function () {
        var shop_code = $("#shop_code").val();
        selectPopWindowshelf_code.dialog = new ESUI.PopSelectWindow('?app_act=common/select/customer&shop_code=' + shop_code, 'selectPopWindowshelf_code.callback', {title: '选择会员', width: 900, height: 500, ES_pFrmId: '<?php echo $request['ES_frmId']; ?>'}).show();
    });


    function changeType() {
        if ($('#pay_code option:selected').val() == "cod") {
            $("#nosecured_type").hide();
            $("#secured_type").hide();
            $("#pay_type").val("cod");
        } else {
            $("#nosecured_type").show();
            $("#secured_type").show();
            $("#cod_type").hide();
            $("#pay_type").val("secured");
        }
    }
</script>


<script type="text/javascript">
    var ES_frmId = '<?php echo $request['ES_frmId']; ?>';
    var shopExpressList = <?php echo json_encode($response['shop_express_list']) ?>;
    var shopStoreList = <?php echo json_encode($response['shop_store_list']) ?>;
    var channelShopList = <?php echo json_encode($response['channel_shop_list']) ?>;
    var searchFormForm;
    var flag = true;
    BUI.use('bui/form', function (Form) {
        var form1 = new BUI.Form.HForm({
            srcNode: '#form1',
            submitType: 'ajax',
            callback: function (data) {
                if (data.status != '1') {
                    BUI.Message.Alert(data.message, 'error')
                    return
                }
                window.location = "?app_act=oms/sell_record/view&sell_record_code=" + data.data + "&ES_frmId=" + ES_frmId
            }
        }).render();
        form1.on('beforesubmit', function () {
            if ($("#receiver_mobile").val() == '' && $("#receiver_phone").val() == '') {
                BUI.Message.Tip('手机号和电话号码不能同时为空', 'error');
                return false;
            }
            $.ajax({
                type: "POST",
                url: "?app_act=api/order/check_deal_code",
                async: false,
                data: {deal_code: $("#deal_code").val(), app_fmt: 'json'},
                dataType: "json",
                success: function (ret) {
                    if (ret.status != '1') {
                        if (!confirm("平台交易号不存在，是否继续？")) {
                            flag = false;
                        } else {
                            flag = true;
                        }
                    }
                }
            });
            return flag;
        });
    });

    BUI.use('bui/calendar', function (Calendar) {
        var datepicker = new Calendar.DatePicker({
            trigger: '.calendar',
            showTime: true,
            autoRender: true
        });
    });

    var url = '<?php echo get_app_url('base/store/get_area'); ?>';
    $(document).ready(function () {
        $('#country').change(function () {
            var parent_id = $(this).val();
            areaChange(parent_id, 0, url);
        });
        $('#province').change(function () {
            var parent_id = $(this).val();
            areaChange(parent_id, 1, url);
        });
        $('#city').change(function () {
            var parent_id = $(this).val();
            areaChange(parent_id, 2, url);
        });
        $('#district').change(function () {
            var parent_id = $(this).val();
            areaChange(parent_id, 3, url);
        });
        $("#shop_code").change(function () {
            $("#express_code").val(shopExpressList[$(this).val()]);
            $("#store_code").val(shopStoreList[$(this).val()]);
        });
        $("#sale_channel_code").change(function () {
            var html = '<option value ="">请选择</option>';
            if (typeof channelShopList[$(this).val()] !== 'undefined') {
                $.each(channelShopList[$(this).val()], function (n, v) {
                    html += "<option value='" + n + "'>" + v + "</option>";
                });
            }
            $("#shop_code").html(html);
        });

        $("#country").find("option[value=1]").attr("selected", "selected");
        $("#country").change();
        $("#sale_channel_code").change();
        $("#cod_type").hide();
    })
</script>