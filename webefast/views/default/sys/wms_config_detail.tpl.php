<style>
    td {
        line-height: 20px;
        padding: 4px;
        text-align: left;
        vertical-align: top;
    }

    .form_tbl,.form_tbl tr,.form_tbl td{border: 1px solid #dddddd;}

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

<form  id="form1" action="?app_act=sys/wms_config/do_<?php echo $response['app_scene'] ?>&app_fmt=json" method="post">
    <table class="form_tbl">
        <tr style="background-color:#f5f5f5;">
            <td class="tdlabel">&nbsp;&nbsp;基本信息</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td class="tdlabel" width="310px" style="text-align:right;">WMS配置名称&nbsp;&nbsp;</td>
            <td width="700px"><input type="hidden" id="wms_config_id" name="wms_config_id" value=""/>
                <input type="text" value="" class="input-normal bui-form-field" id="wms_config_name" name="wms_config_name" param="check" data-rules="{required: true}"/>
            </td>
        </tr>
    </table>
    <br/>

    <table class="form_tbl">
        <tr style="background-color:#f5f5f5;">
            <td class="tdlabel" width="100px" >&nbsp;&nbsp;参数配置</td>
            <td ></td>
            <td ></td>
        </tr>
        <tr>
            <td  width="180px" ></td>
            <td class="tdlabel" width="120px" style="text-align:right;">对接WMS系统&nbsp;&nbsp;</td>
            <td width="700px"><select id="wms_system_code" name="wms_system_code" style="width:150px;" <?php if ($response['app_scene'] == 'edit') echo 'disabled="disabled"' ?>>
                    <?php foreach ($response['system'] as $k => $v) { ?>
                        <option value="<?php echo $v[0] ?>" <?php if ($v[0] == $response['wms_system_code']) echo 'selected="selected"' ?>><?php echo $v[1] ?></option>
                    <?php } ?>
                </select>
                <?php if ($response['app_scene'] == 'edit') { ?>
                    <input type="hidden" name="wms_system_code" value="<?php echo $response['wms_system_code']; ?>">
                <?php } ?>
            </td>
        </tr>
        <tr id="wms_type">
            <td  width="180px" ></td>
            <td class="tdlabel" width="120px" style="text-align:right;">wms服务商&nbsp;&nbsp;</td>
            <td width="700px">
                <select style="width:150px;" id="wms_system_type" name="wms_system_type" data-rules="">
                    <option value="">--请选择--</option>
                    <?php foreach ($response['wms_sys_type'] as $k => $v) { ?>
                        <option value="<?php echo $v['wms_code'] ?>" <?php if ($v['wms_code'] == $response['wms_system_type']) echo 'selected="selected"' ?>><?php echo "{$v['wms_name']}【{$v['app_key']}】" ?></option>
                    <?php } ?>
                </select>
                <span style="color:red;">&nbsp;选填</span>
            </td>
        </tr>
        <tr>
            <td  width="180px" ></td>
            <td class="tdlabel" width="120px" style="text-align:right;">库存模式&nbsp;&nbsp;</td>
            <td width="700px">
                <input type="radio" id="inv" name="effect_inv_type" value="0" checked="checked"/>   库存覆盖（以WMS库存为准）
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="radio" id="prm" name="effect_inv_type" value="1"/>   进销存单据（双方各自管理）

            </td>
        </tr>
        <tr id="notice_iwms">
            <td  width="180px" ></td>
            <td class="tdlabel" width="120px" style="text-align:right;">多批入库&nbsp;&nbsp;</td>
            <td width="700px">
                <input type="radio" id="notice_iwms_enable" name="notice_iwms" value="1" />   开启
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="radio" id="notice_iwms_unable" name="notice_iwms" value="0" checked="checked"/>   关闭
                <span style="color:red;">（目前仅支持采购入库通知单）</span>
            </td>
        </tr>
        <tr id="goods_upload">
            <td  width="180px" ></td>
            <td class="tdlabel" width="120px" style="text-align:right;">商品档案上传模式&nbsp;&nbsp;</td>
            <td width="700px">
                <input type="radio" <?php if ($response['app_scene'] === 'edit') { ?> disabled="disabled" <?php } ?> id="barcode_uplode" name="goods_upload_type" value="0" checked="checked"/>   商品条形码
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="radio" <?php if ($response['app_scene'] === 'edit') { ?> disabled="disabled" <?php } ?> id="sku_uplode" name="goods_upload_type" value="1"/>  系统SKU码（商品编码+规格1编码+规格2编码）
            </td>
        </tr>
    </table>
    <table id='wms_params' class="form_tbl"></table>
    <table class="form_tbl">
        <tr id="prefix">
            <td></td>
            <td class="tdlabel" width="120px" style="text-align:right;">单号前缀&nbsp;&nbsp;</td>
            <td width="700px">
                <input type="text" value="" class="input-normal bui-form-field" id="wms_prefix" name="wms_prefix"/>
                <span style="color:red;">&nbsp;选填，该单号前缀仅能设置为4-6位英文字母</span>
            </td>
        </tr>
        <tr>
            <td width="180px" ></td>
            <td class="tdlabel" width="120px" style="text-align:right">截单时间&nbsp;&nbsp;</td>
            <td width="700px">
                <input type="text" value="" class="input-normal bui-form-field" id="wms_cut_time" name="wms_cut_time"/> 
                <span style="color:red;">&nbsp;格式：17:00；&nbsp;截单时间点之后将不再往WMS推送订单</span>
            </td>
        </tr>
        <tr>
            <td style="width: 1018px;text-align:center;" colspan="3">说明：参数值，请从WMS系统所属公司的服务人员或实施人员获取！</td>
        </tr>
    </table>
    <br/>
    <table id="shop" class="form_tbl" style="display: none;">
        <?php if ($response['app_scene'] == 'add') { ?>
            <tr style="background-color:#f5f5f5;">
                <td class="tdlabel" style="width:200px">&nbsp;&nbsp;eFAST店铺列表</td>
                <td colspan="3" style="width:700px">&nbsp;&nbsp;<span id="outshop_tip">外部WMS的店铺代码</span>
                    <p class="add_btn" onclick = "add('shop')"> <img src="assets/images/plus.png" />添加</p>
                </td>
            </tr>
        <?php } ?>
        <?php if ($response['app_scene'] == 'edit') { ?>
            <tr style="background-color:#f5f5f5;">
                <td class="tdlabel">&nbsp;&nbsp;eFAST店铺列表</td>
                <td colspan="3">&nbsp;&nbsp;<span id="outshop_tip">外部WMS的店铺代码</span>
                    <p class="add_btn" onclick = "add('shop')"> <img src="assets/images/plus.png" />添加</p>
                </td>
            </tr>
            <?php
            foreach ($response['wms_shop'] as $key => $value) {
                $outside_code = !empty($value['outside_code']) ? $value['outside_code'] : '';
                $fx_select = '<option value="">请选择</option>';
                foreach ($response['shop'] as $k => $v) {
                    if ($v['shop_code'] == $value['shop_store_code']) {
                        $shop_select .= '<option value="' . $v['shop_code'] . '" selected="selected" >' . $v['shop_name'] . '</option>';
                    } else {
                        $shop_select .= '<option value="' . $v['shop_code'] . '">' . $v['shop_name'] . '</option>';
                    }
                }
                echo '<tr>
            <td class="tdlabel" width="300px" ;">
                <select  name="shop[' . $key . '][shop_store_code]" style="width:200px;">' . $shop_select . '</select>
            </td>
            <td width="700px">&nbsp;
                <input type="text" value="' . $outside_code . '" class="input-normal bui-form-field"  name="shop[' . $key . '][outside_code]"  param="check" />
                <p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p>
            </td>
        </tr>';
            }
            ?>
        <?php } ?>
    </table>
    <br/>
    <table id="store" class="form_tbl">
        <tr style = "background-color:#f5f5f5;">
            <td class="tdlabel" style="width:200px" >&nbsp;&nbsp;eFAST仓库列表</td>
            <td style="width:700px">&nbsp;&nbsp;外部WMS的仓库代码
                <p class="add_btn" onclick = "add('store')"> <img src="assets/images/plus.png" />添加</p></td>
        </tr>
    </table>
    <table class="form_tbl" style="visibility: hidden;">
        <tr style="background-color:#f5f5f5;">
            <td class="tdlabel">&nbsp;&nbsp;库存同步</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td class="tdlabel" width="300px" style="text-align:right;">WMS商品库存同步至eFAST&nbsp;&nbsp;</td>
            <td width="700px"><input type="checkbox" value="" class="bui-form-field" id="item_sync" name="item_sync" checked /></td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="tdlabel"><button id="submit" class="button button-primary" type="submit">提交</button></td>
            <td colspan="3"><button id="reset" class="button"  type="reset">重置</button></td>
        </tr>
    </table>
</form>
<?php
$list = $response['shop'];
$shop_select = '<option value="">请选择</option>';
foreach ($list as $k => $v) {
    $shop_select .= '<option value="' . $v['shop_code'] . '">' . $v['shop_name'] . '</option>';
}
?>
<script type="text/javascript">
    var app_scene = "<?php echo $response['app_scene']; ?>";
    var wms_config_id = "<?php echo $request['_id']; ?>";
    var shop_select = '<?php echo $shop_select ?>';
    var wms_prefix = '<?php echo $response['wms_prefix']?>';
    var select_shop;
    $(function () {
        $('#wms_system_code').change(function () {
            var wms_system_code = $(this).val();
            get_tab_param(wms_system_code);
            var wms_set_type = ['iwms', 'iwmscloud', 'shunfeng', 'hwwms', 'ydwms', 'qimen'];
            if ($.inArray(wms_system_code, wms_set_type) != -1) {
                var html = '<tr id="extra_tr"><td class="tdlabel" style="width:200px" >&nbsp;&nbsp;eFAST仓库列表</td>';
                html += '<td style="width:350px">&nbsp;&nbsp;外部WMS的仓库代码</td>';
                html += '<td style="width:350px">仓库性质<p class="add_btn" onclick = "add(\'store\')"> <img src="assets/images/plus.png" />添加</p></td></tr>';
                $('#store tr').remove();
                $('#store').append(html);
                init_store();
            } else {
                var store_html = '<tr style = "background-color:#f5f5f5;">';
                store_html += '<td class="tdlabel" style="width:200px" >&nbsp;&nbsp;eFAST仓库列表</td>';
                store_html += '<td style="width:700px">&nbsp;&nbsp;外部WMS的仓库代码';
                store_html += '<p class="add_btn" onclick = "add(\'store\')"> <img src="assets/images/plus.png" />添加</p></td></tr>';
                if (typeof ($("#extra_tr").html()) != 'undefined') {
                    $('#store').empty();
                    $('#store').append(store_html);
                    init_store();
                }
            }
            //商品档案上传
            if (wms_system_code == 'iwms') {
                $("#goods_upload").show();
            } else {
                $("#goods_upload").hide();
            }
            //wms服务商
            if (wms_system_code == 'qimen') {
                $("#wms_type").show();
                $("#wms_system_type").removeAttr("disabled");
            } else {
                $("#wms_type").hide();
                $("#wms_system_type").attr("disabled", "disabled");
            }
            //单号前缀
            if ($.inArray(wms_system_code, ['iwms','iwmscloud', 'qimen']) != -1) {
                $("#prefix").show();
                $("#wms_prefix").removeAttr("disabled");
            } else {
                $("#prefix").hide();
                $("#wms_prefix").attr("disabled", "disabled");
            }

            var product_arr = ['jdwms', 'jdwmscloud', 'iwms', 'iwmscloud'];
            var is_show_shop = $.inArray(wms_system_code, product_arr);
            if (is_show_shop != -1) {
                if (wms_system_code == 'jdwms' || wms_system_code == 'jdwmscloud') {
                    $('#outshop_tip').text('外部WMS的店铺代码');
                } else {
                    $('#outshop_tip').text('外部WMS的店铺ID');
                }
                $("#shop").show();
            } else {
                $("#shop").hide();
            }
            if (app_scene == 'add') {
                $("#shop tr:eq(0)").siblings("tr").remove();
                if (wms_system_code == 'jdwms' || wms_system_code == 'jdwmscloud') {
                    add('shop');
                }
            }
            //设置iwms主动回传参数
            set_notice_iwms_show();
        });
        set_notice_iwms_show();
        setTimeout(function () {
            $('#wms_system_code').change();
        }, 10);
        init_store();


        set_change_store();
    });

    $("#wms_prefix").on("blur", function () {
        var prefix = $.trim($(this).val());
        if(wms_prefix.length != 0 && prefix.length == 0){
            $(this).val(wms_prefix);
            $(this).focus();
            BUI.Message.Tip('单号前缀不能修改为空!', 'warning');
            return false;
        }else if(wms_prefix.length == 0 && prefix.length == 0){
            return false;
        }

        var patrn = /^[a-zA-Z]{4,6}$/;
        if (!patrn.exec(prefix)) {
            BUI.Message.Tip('单号前缀格式错误!', 'warning');
            $(this).val(wms_prefix);
            $(this).focus();
        }
    });


    function set_notice_iwms_show() {
        var wms_system_code = $('#wms_system_code').val();
        var wms_set_type = ['iwms', 'iwmscloud'];
        if ($.inArray(wms_system_code, wms_set_type) != -1) {
            $("#notice_iwms").show();
        } else {
            $("#notice_iwms").hide();
        }
    }

    function get_tab_param(wms_system_code) {
        var url = '?app_act=sys/wms_config/get_wms_system&app_fmt=json';
        var data = {};
        data.wms_system_code = wms_system_code;
        data.wms_config_id = wms_config_id;
        $.post(url, data, function (result) {
            if (result.status == 1) {
                $("#wms_params").empty();
                var i = 1;
                content = '';
                select_shop = result.select_shop;
                for (var key in result.data) {
                    content += '<tr>';
                    content += '<td  width="180px" style="text-align:right;">';
                    content += '参数' + i + '：</td>';
                    content += '<td class="tdlabel" width="120px" >';
                    content += '  <input  class="input-small  control-text" name="param' + i + '" id="param' + i + '" value="' + key + '" type="text" readonly="readonly" ></td>';
                    content += get_type_html(result.data[key], i);
                    content += result.data[key].desc;
                    content += '  </tr>';
                    i++;
                }
                if ($.inArray(wms_system_code, ['iwms', 'iwmscloud']) != -1) {
                    content += '<tr><td></td><td></td><td><a class="button" onclick="api_test();"><i class="icon icon-random" style="margin:3px 5px 0 -5px;"></i>连通测试</a></td></tr>';
                }
                $("#wms_params").append(content);

                //给奇门app_secret设置半加密
                if (wms_system_code == 'qimen') {
                    if (app_scene == 'add') {
                        $('#param1_val').val('23300032');
                        $('#param5_val').val('http://qimen.api.taobao.com/router/qimen/service');
                        $('#param2_val').val('fc0c155345cf996ba9257bc7bd877770');
                        secret_val = $('#param2_val').val();
                        //console.log(old_val);
                        new_val = secret_val;
                        if (secret_val.length > 7) {
                            new_val = secret_val.substr(0, 6) + '**********';
                        }
                        new_html = '<input style="width:120px;"  id="new_param" value="' + new_val + '" type="text">';
                        $('#param2_val').hide();
                        $("#param2_val").after(new_html);
                        $('#new_param').focus(function () {
                            $('#new_param').hide();
                            $('#param2_val').show().focus();
                        });
                        $('#param2_val').blur(function () {
                            secret_val = $('#param2_val').val();
                            //console.log(va);
                            new_val = secret_val;
                            if (secret_val.length > 7) {
                                new_val = secret_val.substr(0, 6) + '**********';
                            }
                            new_html = '<input style="width:120px;"  id="new_param" value="' + new_val + '" type="text">';
                            $('#param2_val').hide();
                            $("#param2_val").after(new_html);
                            $('#new_param').focus(function () {
                                $('#new_param').hide();
                                $('#param2_val').show().focus();
                            });
                        });
                    } else {
                        secret_val = $('#param2_val').val();
                        //console.log(old_val);
                        new_val = secret_val;
                        if (secret_val.length > 7) {
                            new_val = secret_val.substr(0, 6) + '**********';
                        }
                        new_html = '<input style="width:120px;"  id="new_param" value="' + new_val + '" type="text">';
                        $('#param2_val').hide();
                        $("#param2_val").after(new_html);
                        $('#new_param').focus(function () {
                            $('#new_param').hide();
                            $('#param2_val').show().focus();
                        });
                        $('#param2_val').blur(function () {
                            secret_val_1 = $('#param2_val').val();
                            //console.log(secret_val_1);
                            new_val_1 = secret_val_1;
                            if (secret_val_1.length > 7) {
                                new_val_1 = secret_val_1.substr(0, 6) + '**********';
                            }
                            new_html_1 = '<input style="width:120px;"  id="new_param" value="' + new_val_1 + '" type="text">';
                            $('#param2_val').hide();
                            $("#param2_val").after(new_html_1);
                            $('#new_param').focus(function () {
                                $('#new_param').hide();
                                $('#param2_val').show().focus();
                            });
                        });
                    }

                }

                if (result.effect_inv_type == 0) {
                    $('#inv').attr('checked', true);
                } else {
                    $('#prm').attr('checked', true);
                }
                if (result.goods_upload_type == 1) {
                    $('#sku_upload').attr('checked', true);
                } else {
                    $('#barcode_upload').attr('checked', true);
                }
            } else {
                alert('error');
            }
        }, 'json');
    }

    function get_type_html(data, i) {
        var html = '';
        if (typeof (data.type) == 'undefined') {
            if (data.disabled == '1') {
                if (app_scene == 'add') {
                    html = '<td width="700px"><input style="width:120px;" class="input-small control-text" data-rules="{required: true}" readonly="readonly" name="param' + i + '_val" id="param' + i + '_val" value="" type="text"></td>';
                } else {
                    html = '<td width="700px"><input style="width:120px;" class="input-small control-text" data-rules="{required: true}" readonly="readonly" name="param' + i + '_val"  id="param' + i + '_val"  value="' + data.val + '"  type="text"></td>';
                }
            } else {
                if (app_scene == 'add') {
                    html = '<td width="700px"><input style="width:120px;" class="input-small control-text" data-rules="{required: true}" name="param' + i + '_val" id="param' + i + '_val" value="" type="text"></td>';
                } else {
                    html = '<td width="700px"><input style="width:120px;" class="input-small control-text" data-rules="{required: true}" name="param' + i + '_val" id="param' + i + '_val" value="' + data.val + '" type="text"></td>';
                }
            }
        } else if (data.type == 'radio') {
            var j = 1;
            for (var key in data.data) {

                var select = '';
                if (data.data[key] == data.val) {
                    select = 'checked="' + 'checked"';
                }
                html += '<td width="700px"><input ' + select + '  name="param' + i + '_val" value="' + data.data[key] + '" data-rules="{required: true}" type="radio">' + data.data[key] + '</td>';
                if (j % 2 == 1) {
                    html += '&nbsp;&nbsp;';
                }
            }
        } else if (data.type == 'select') {
            var form1_data_source_v = $("#form1_data_source").html();
            if (form1_data_source_v != '') {
                var wms_config_arr = eval("(" + form1_data_source_v + ")");
            }
            html += '<td width="700px"><select name="param' + i + '_val" id="select_shop" style="width:120px;"> <option value="">请选择</option>';
            for (var key in select_shop) {
                var selected_shop_arr = select_shop[key];
                if (app_scene == 'edit' && selected_shop_arr.shop_code == wms_config_arr.shop) {
                    html += '<option  value="' + selected_shop_arr.shop_code + '" selected>' + selected_shop_arr.shop_name + '</option>';
                } else {
                    html += '<option  value="' + selected_shop_arr.shop_code + '">' + selected_shop_arr.shop_name + '</option>';
                }
            }
            html += '</select><span style="color:red">请选择与京东签订合同的京东店铺</span></td>';
        } else if (data.type == 'pop_select') {
            var form1_data_source_v = $("#form1_data_source").html();
            if (form1_data_source_v != '') {
                var wms_config_arr = eval("(" + form1_data_source_v + ")");
            }
            html = '<td width="700px"><input type="text" style="width:120px;" class="input-small control-text" data-rules="{required: true}" readonly="true" id="select_code" name="param' + i + '_val" id="param' + i + '_val" value="';
            if (app_scene == 'edit') {
                html += typeof wms_config_arr.thirdCategoryNo == 'undefined' ? '' : wms_config_arr.thirdCategoryNo;
            }
            html += '" onclick="select_category()"><img id="select_img" onclick="select_category()" src="assets/img/search.png" style="margin-left:-20px"></td>';
            //若有其他wms使用弹出选择，可通过wms类型进行判断调用不同方法
        }
        return html;
    }

    var selectPopWindow_category = {
        dialog: null,
        callback: function (value) {
            $('#select_code').val(value[0]['category_code']);
            if (selectPopWindow_category.dialog != null) {
                selectPopWindow_category.dialog.close();
            }
        }
    };

    //选择
    function select_category() {
        selectPopWindow_category.dialog = new ESUI.PopSelectWindow('?app_act=common/select/jdwms_category', 'selectPopWindow_category.callback', {title: '选择分类', width: 700, height: 450, ES_pFrmId: '<?php echo $request['ES_frmId']; ?>'}).show();
    }

    var form1_data_source_v = $("#form1_data_source").html();
    if (form1_data_source_v != '') {
        $('form#form1').autofill(eval("(" + form1_data_source_v + ")"));
    }
    function getQueryString(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
        var r = window.location.search.substr(1).match(reg);
        if (r != null)
            return unescape(r[2]);
        return null;
    }


    var form = new BUI.Form.HForm({
        srcNode: '#form1',
        submitType: 'ajax',
        callback: function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, function () {
                if (data.status == 1) {
                    parent._reload_page();
                    ui_closeTabPage(getQueryString('ES_frmId'));
                }
            }, type);
        }
    }).render();

    //增加
    var store_select = '<?php echo $store_select ?>';

    function add(type) {
        if (type == 'shop') {
            var i = $("#shop").find("tr").length - 1;
            $("#shop").append('<tr><td class="tdlabel" width="300px" ;"><select  name="shop[' + i + '][shop_store_code]" style="width:200px;">' +
                    shop_select + '</select></td><td width="700px">&nbsp;&nbsp;' +
                    '<input type="text" value="" class="input-normal bui-form-field"  name="shop[' + i + '][outside_code]" data-rules="{required: true}"/><p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p></td></tr>');
            set_change_shop();
        }
        if (type == 'store') {
            var tr = add_store_select(store_i, '');
            store_i++;
            $('#store').append(tr);
            set_change_store();
        }
    }
    function set_change_store() {
        var select = $('#store').find('select');
        select.off("change");

        select.on("change", function () {
            var name = $(this).attr("name");
            var store_code = $(this).val();
            var check = get_other_store(name, store_code);
            if (check == 1) {
                BUI.Message.Alert("仓库已经被选择，请选择其他仓库", 'error');
                $(this).find("option[value='']").attr('selected', true);
            }
        });
    }

    function set_change_shop() {
        var select = $('#shop').find('select');
        select.off("change");

        select.on("change", function () {
            var name = $(this).attr("name");
            var shop_code = $(this).val();
            var check = get_other_shop(name, shop_code);
            if (check == 1) {
                BUI.Message.Alert("店铺已经被选择，请选择其他店铺", 'error');
                $(this).find("option[value='']").attr('selected', true);
            }
        });
    }

    function get_other_store(name, store_code) {
        var select = $('#store').find('select');
        var check = 0;
        $.each(select, function (i, item) {
            if (name != $(item).attr("name")) {
                if ($(item).val() == store_code) {
                    check = 1;
                }
            }
        });
        return check;
    }

    function get_other_shop(name, shop_code) {
        var select = $('#shop').find('select');
        var check = 0;
        $.each(select, function (i, item) {
            if (name != $(item).attr("name")) {
                if ($(item).val() == shop_code) {
                    check = 1;
                }
            }
        });
        return check;
    }

    function check() {
        var all = form.get('children');
        for (var f in all) {
            if (all[f]['__attrVals']['param'] == 'check') {
                var element = all[f]['__attrVals'];
                element['error'] = '不能为空';
                element['rules'] = {required: true};
            }
        }
    }

    function del(item) {
        $(item).parent("td").parent("tr").remove();
    }

    var store_list = <?php echo json_encode($response['store']); ?>;
    var select_store_data = <?php echo empty($response['wms_store']) ? "''" : json_encode($response['wms_store']); ?>;
    var store_i = 0;
    var store_list_str = '';
    function init_store() {
        if (store_list_str == '') {
            $.each(store_list, function (i, val) {
                store_list_str += '<option value="' + val.store_code + '" >' + val.store_name + '</option>';
            });
        }
        if (select_store_data != '') {
            $.each(select_store_data, function (index, obj) {
                var tr = add_store_select(store_i, obj);
                $('#store').append(tr);
                store_i++;
            });
        } else {
            var tr = add_store_select(store_i, '');
            $('#store').append(tr);
            store_i++;
        }
        set_change_store();
    }
    function add_store_select(i, obj) {
        var html = '';
        html += '<tr>';
        html += '<td class="tdlabel" style="width:300px;"><select  name="store[' + i + '][shop_store_code]" style="width:200px;"><option value="" >请选择</option>' + store_list_str + '</select></td>';
        html += '<td>&nbsp;&nbsp;';
        html += '<input type="text" value="" class="input-normal bui-form-field"  name="store[' + i + '][outside_code]"  param="check"  data-rules="{required: true}"/>';
        html += "</td>";
        html += "</tr>";
        var tr = $(html);
        var $store_type_html = $('<td><input type="radio" name="store[' + i + '][store_type]" value="1"  />正品库存&nbsp;&nbsp;<input type="radio" name="store[' + i + '][store_type]" value="0" >次品库存</td>');
        var wms_set_type = ['iwms', 'iwmscloud', 'shunfeng', 'hwwms', 'ydwms', 'qimen'];
        if ($.inArray($('#wms_system_code').val(), wms_set_type) != -1) {
            tr.append($store_type_html);
            tr.find('input[value="' + obj.store_type + '"]').attr("selected", true);
        } else {
            $store_type_html.remove();
        }
        tr.find('td').last().append('<p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p>');
        if (obj != '') {
            tr.find('select option[value="' + obj.shop_store_code + '"]').attr("selected", true);
            tr.find('input[param="check"]').val(obj.outside_code);

            if ($.inArray($('#wms_system_code').val(), wms_set_type) != -1) {
                tr.find('input[value="' + obj.store_type + '"]').attr("checked", true);
            }
        } else {
            tr.find('input[value="1"]').attr("checked", true);
        }
        return tr;
    }

    /*--- API连通测试 ---*/
    function api_test() {
        var api_url = $('#wms_params #param3_val').val();
        if (api_url == '') {
            BUI.Message.Tip('请设置URL', 'warning');
            return false;
        }
        $.ajax({
            url: '<?php echo get_app_url('sys/wms_config/iwms_api_test'); ?>',
            type: 'POST',
            dataType: 'json',
            data: {api_url: api_url},
            success: function (ret) {
                if (ret.status == 1) {
                    BUI.Message.Tip(ret.message, 'success');
                } else {
                    BUI.Message.Tip(ret.message, 'error');
                }
            }
        });
    }
//    //添加时间控件
//    BUI.use('bui/calendar',function(Calendar){
//      var datepicker = new Calendar.DatePicker({
//            trigger:'#wms_cut_time',
//            showTime : true,
//            dateMask : 'hh:mm',
//            autoRender : true
//          });
//        });
</script>
<?php echo load_js('comm_util.js') ?> 