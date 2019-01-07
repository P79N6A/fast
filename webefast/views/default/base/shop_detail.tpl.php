<style>
    .table_panel td {
    border:1px solid #dddddd;
    line-height: 20px;
    padding: 6px;
    text-align: left;
    width:200px;
    vertical-align: top;
}
#form1 .span11{
    width:500px;
}
    .add_btn,
    .minus_btn { display: inline-block; float: right; cursor: pointer; margin: 0; }
    .add_btn img,
    .minus_btn img { vertical-align: text-bottom; margin-right: 5px; }
    td {
        border-top: 1px solid #dddddd;
        line-height: 0px;
        padding: 4px;
        text-align: left;
        vertical-align: center;
    }
</style>
<?php
$tabs = array(
    array('title' => '基本信息', 'active' => $response['active'] == 2 ? false : true, 'id' => 'tabs_base'),
);
$button = array();

$abc = render_control('TabPage', 'TabPage1', array(
    'tabs' => $tabs,
    'for' => 'TabPage1Contents'
        ));
?>
<div id="TabPage1Contents">
    <div>
        <?php
        if($response['login_type'] != 2) {
            $data = array(array('0','直营'));
            if($response['service_custom'] == true) {
                $data[] = array('2','分销');
            }
            $html = array('title'=>'分销商','type'=>'select_pop', 'id'=>'_select_pop', 'select'=>'base/shop_custom');
        } else {
            $data = array(array('2','分销'));
            $html = array('title'=>'分销商','type' => 'input', 'field' => 'custom_name');
        }
        $fields = array(
            array('title' => '店铺代码', 'type' => 'input', 'field' => 'shop_code'),
            array('title' => '店铺名称', 'type' => 'input', 'field' => 'shop_name'),
            array('title' => '销售平台', 'type' => 'select', 'field' => 'sale_channel_code', 'data' => $response['sale_channel']),
            array('title' => '店铺性质', 'type' => 'select', 'field' => 'entity_type', 'data' => $data),
//            array('title' => '选择分销商', 'type' => 'select', 'field' => '_select_pop', 'data' => $response['custom'],'remark' => "<a href='#' id = 'wbmselectcustom'><img src='assets/img/search.png'></a>"),
            $html,
            array('title' => '启用淘分销', 'type' => 'radio_group', 'field' => 'fenxiao_status', 'data' => array(array('1', '启用'), array('0', '关闭'))),
            array('title' => '发货仓库', 'type' => 'select', 'field' => 'send_store_code', 'data' => $app['scene'] == 'edit' && $response['login_type'] != 2 && $response['data']['entity_type'] == 2 ? $response['fx_store'] : $response['store']),
            //array('title' => '库存来源仓库', 'type' => 'select_multi', 'field' => 'stock_source_store_code', 'data' => $response['store']),
            array('title' => '退货仓库', 'type' => 'select', 'field' => 'refund_store_code', 'data' => $response['store']),
            array('title' => '默认配送方式', 'type' => 'select', 'field' => 'express_code', 'data' => $response['express']),
            array('title' => '承诺发货天数', 'type' => 'input', 'field' => 'days', 'value' => '3'),
            array('title' => '绑定旗舰店', 'type' => 'select', 'field' => 'taobao_shop_code', 'data' =>$response['shop_data'],'remark'=>"<span style='color:red;'>旗舰店参数会覆盖本店铺参数</span>"),
        );
        $authorize_type = array('taobao', 'jingdong','yihaodian','suning');
        if ($_GET['app_scene'] != 'add') {
            if(in_array($response['data']['sale_channel_code'],$authorize_type)){
                $fields[] = array('title' => '授权状态', 'type' => 'html', 'field' => 'authorize_state', 'html' => $response['authorize_state'] == 1 ? '已授权' . '&nbsp;<input type="button" class="button button-primary" onClick="pre_authorize();" value="重新授权">' : '未授权' . '&nbsp;<input type="button" class="button button-primary" onClick="pre_authorize();" value="授权">');
            }else{
                $fields[] = array('title' => '授权状态', 'type' => 'html', 'field' => 'authorize_state', 'html' => $response['authorize_state'] == 1 ? '已授权' : '未授权');
            }
            $fields[] = array('title' => '店铺昵称', 'type' => 'html', 'html' => $response['shop_user_nick']);
        }

        render_control('Form', 'form1', array(
            'conf' => array(
                'fields' => $fields,
                'hidden_fields' => array(array('field' => 'shop_id')),
            ),
            'buttons' => array(
            //array('label' => '提交', 'type' => 'submit'),
            //array('label' => '重置', 'type' => 'reset'),
            ),
            'act_edit' => 'base/shop/do_edit', //edit,add,view
            'act_add' => 'base/shop/do_add',
            'data' => $response['data'],
            'rules' => array(
                array('shop_code', 'require'),
                array('shop_name','require'),
                array('sale_channel_code', 'require'),
                array('send_store_code', 'require'),
                array('express_code', 'require'),
                array('days', 'require'),
                array('_select_pop', 'require'),
            ),
        ));
        ?>
    </div>
</div>
<div>
    <div id="api_weipinhui" style = "display:none;">
        <div id="weipinhui_sync_type_txt" style = "display:none;" >
            <table id="weipinhui_type" border="1px" bordercolor="#dddddd" width="300px">
                <tr style='background-color:#f5f5f5;'><td width='100px' ><span>唯品会仓库</span></td><td  colspan='1' width='200px'><span>&nbsp;&nbsp;同步比例%</span></td><td >&nbsp;&nbsp;<p class='add_btn' style="width: 60px" onclick="add('weipinhui')"><img src='assets/images/plus.png'/>添加</p></td></tr>
                <?php if (!empty($response['shop_warehouse']) && $app['scene'] == 'edit') {
                    foreach ($response['shop_warehouse'] as $value) {
                        $select = '<option value="">请选择</option>';
                        $sync_val=$value['sync_val'];
                        foreach($response['warehouse'] as  $k => $v){
                            if($v['warehouse_code']==$value['warehouse_code']){
                                $select.='<option value="'.$v['warehouse_code'].'" selected="selected" >'.$v['warehouse_name'].'</option>';
                            }else{
                                $select.='<option value="'.$v['warehouse_code'].'">'.$v['warehouse_name'].'</option>';
                            }
                        }
                        echo '<tr><td width="100px"">&nbsp;&nbsp;<select style="width:100px;">'.$select.'</select></td><td width="150px">&nbsp;&nbsp;<input type="text" style="width:150px" value="' . $sync_val . '" /></td><td width="60px"><p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p></td></tr>';
                    }}?>
            </table>
        </div>
    </div>
    <div id="dajiashequ_txt" style = "display:none;" >
        <span style="color:red;">友情提示：请先写大家社区帐号和密码获取接口参数，再填写相应参数提交！</span>
    </div>
    <div id="TabPage1Submit" class="row form-actions actions-bar">
        <div class="span13 offset3 ">
            <button id="shop_auth" style = "display:none;" class="button" onclick="auth_shop();">获取授权</button>
            <button type="submit" class="button button-primary" id="submit" onclick="do_submit(1);">提交</button>
            <button type="reset" class="button " id="reset">重置</button>
        </div>
    </div>
    <div id="meilishuo_txt" style = "display:none;" >
        <span style="color:red;">美丽说接口授权步骤:</span></br>
        <span>1、点击“获取授权”，调转到授权地址，点击‘获取access_token’，使用美丽说主账号登陆，成功登陆了，即可查看到授权信息</span></br>
        <span>2、分别将美丽说授权信息<font color="red">access_token,refresh_token</font>填入参数信息中</span></br>
        <span>3、点击“授权激活并保存”，如果提示成功即完成授权</span>
    </div>
    <div id="mogujie_txt" style = "display:none;" >
        <span style="color:red;">蘑菇街接口授权步骤:</span></br>
        <span>1、点击“获取授权”，调转到授权地址，点击‘获取access_token’，使用蘑菇街主账号登陆，成功登陆了，即可查看到授权信息</span></br>
        <span>2、分别将蘑菇街授权信息<font color="red">access_token,refresh_token</font>填入参数信息中</span></br>
        <span>3、点击“授权激活并保存”，如果提示成功即完成授权</span>
    </div>
    <div id="beibei_txt" style = "display:none;" >
        <span style="color:red;">贝贝接口授权步骤:</span></br>
        <span>1、点击“获取授权”，使用贝贝主账号登陆，成功登陆了，即可查看到授权信息</span></br>
        <span>2、将贝贝授权信息<font color="red">session</font>填入<font color="red">app_session</font>中</span></br>
        <span>3、点击“授权激活并保存”，如果提示成功即完成授权</span>
    </div>
    <div id="weipinhui_txt" style = "display:none;" >
        <span style="color:red;">唯品会接口授权步骤:</span></br>
        <span>1、点击“获取授权”，使用唯品会主账号登陆，成功登陆了，即可查看到授权信息</span></br>
        <span>2、将唯品会授权信息<font color="red">access_token</font>填入<font color="red">access_token</font>中</span></br>
        <span>3、点击“授权激活并保存”，如果提示成功即完成授权</span>
    </div>
    <div id="jumei_txt" style = "display:none;" >
        <span style="color:red;">聚美优品接口授权步骤:</span></br>
        <span>1、点击“获取授权”，使用聚美主账号登陆，成功登陆，点击基础管理->ERP对接管理</span></br>
        <span>2、将商家<font color="red">发货系统ID，商家键值key以及接口签名sign</font>填入<font color="red">系统</font>中</span></br>
        <span>3、点击“授权激活并保存”，如果提示成功即完成授权</span>
    </div>
    <div id="huayang_txt" style = "display:none;" >
        <span style="color:red;">友情提示：华阳接口，仅获取快递配送的订单数据，门店自提和商家配送订单数据不获取，请知悉！</span>
    </div>
    <div id="youzan_txt" style = "display:none;" >
        <span style="color:red;">友情提示：有赞订单，收货人将做为会员昵称处理，请知悉！</span>
    </div>
    <div id="chuizhicai_txt" style = "display:none;" >
        <span style="color:red;">友情提示：请先填写AppKey和AppSecret获取授权，将获取的access_token和refresh_token填写至相应处，最后提交！</span>
    </div>
</div>
<div id="form3_express" style="display:none;">
    <form  class="form-horizontal" id="form3" action="" method="post">
        <div class="row">
            <div class="control-group span16">
                <?php foreach ($response['express_data'] as $key => $express) { ?>
                    <div class="controls span4" style="margin-left: 0px;">
                        <input class="" name="express_data[]" id="<?php echo $express[0]; ?>" value="<?php echo $express[0]; ?>" type="checkbox" <?php
                        if (in_array($express[0], $response['data']['express_data'])) {
                            echo 'checked="checked"';
                        }
                        ?>><?php echo $express[1]; ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </form>
</div>
<div id="form4_express" style="display:none;">
    <form  class="form-horizontal" id="form4" action="" method="post">
        <div class="row show-grid">
            <div class="span8">
                <label class="control-label">联系人：</label>
                <div class="controls control-row1"><input class="input-normal control-text" type="text" name="contact_person" value="<?php echo isset($response['data']['contact_person'])? $response['data']['contact_person']:''?>"></div>
            </div>
        </div>
        <div class="row show-grid">
            <div class="span8">
                <label class="control-label">联系电话：</label>
                <div class="controls control-row1"><input class="input-normal control-text" type="text" name="tel" value="<?php echo isset($response['data']['tel'])? $response['data']['tel']:''?>"></div>
            </div>
        </div>
        <div class="row show-grid">
            <div class="span16">
                <label class="control-label">发货地址：</label>
                <div class="controls control-row1">
                    <select name="province" id="province" style="width:100px;">
                        <option>省</option>
                    </select>
                    <select name="city" id="city" style="width:100px;">
                        <option>市</option>
                    </select>
                    <select name="district" id="district" style="width:100px;">
                        <option>区</option>
                    </select>
                    <select name="street" id="street" style="width:100px;" data-rules="{required: true}">
                        <option value="">街道</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row show-grid">
             <div class="span16">
                 <label class="control-label">详细地址：</label>
                <div class="controls control-row1"><input class="input-normal control-text" type="text" name="address" style="width:250px;" value="<?php echo isset($response['data']['address'])? $response['data']['address']:''?>"></div>
            </div>
        </div>
    </form>
</div>
<div id="form5_info" style="display:none;">
    <form  class="form-horizontal" id="form5" action="" method="post">
        <div class="row">
            <div class="control-group span16">
                <table cellspacing="0" class="table_panel">
                <tr>
                    <td style="width:5%"><input type="checkbox" value="<?php echo isset($response['data']['inv_syn'])?$response['data']['inv_syn'] : 0; ?>" id="inv_syn" name="inv_syn" <?php if($response['data']['inv_syn']==1){ echo 'checked="checked"';}?> >商品无库存记录以0库存同步</td>
                    <td style="width:10%"> 参数默认关闭，请谨慎开启，开启后店铺商品库存来源仓无库存记录以0库存同步平台店铺中，会导致商品下架。</td>
                </tr>
                </table>
            </div>
        </div>
    </form>
</div>
<span id="kehu_code" style="display: none;">oms_test</span>
<?php echo load_js('comm_util.js')?>
<?php echo load_js('base64.js', true)?>
<input type="hidden" id="auth_url" value="<?php echo $response['auth_url']; ?>"/>

<script type="text/javascript">
    var scene = "<?php echo $app['scene']; ?>";
    var sale_channel_code = "<?php echo $response['data']['sale_channel_code']; ?>";
    var product_version_no = "<?php echo $response['product_version_no']; ?>";
    var login_type = '<?php echo $response['login_type']; ?>';
    var active = '<?php echo $response['active']?>';
    var auth_sale_channel = ['jingdong','chuanyi','chuchujie','meilishuo','yamaxun','beibei','baidumall','mogujie','zhe800','youzan','aliexpress','suning','miya','weimob','vdian','yintai','gonghang','kaola','huayang','shangpin','weipinhui','juanpi','okbuy','renrendian','mxyc','fenxiao','alibaba','feiniu','xiaohongshu','pinduoduo','yihaodian','biyao','jumei','xiaomizhijia','ofashion','siku','yoho','yougou','dangdang','chuizhicai', 'ebay','pinduoduo','zouxiu'];
    $("#shop_code").attr("disabled", "disabled");
    var api_weipinhui = $("#api_weipinhui").html();
    $("#api_weipinhui").remove();
    form.on('beforesubmit', function () {
        $("#shop_code").attr("disabled", false);
    });
    var selectPopWindow = {
        dialog: null,
        callback: function(value) {
            if(value[0] != undefined) {
                var custom_code = value[0]['custom_code'];
                var custom_name = value[0]['custom_name'];
                $('#_select_pop').val(custom_name);
                $('#custom_code').val(custom_code);
            }
            if (selectPopWindow.dialog != null) {
                selectPopWindow.dialog.close();
            }
        }
    };
    //店铺代码生成
    if (scene == 'add') {
        $("#sale_channel_code").change(function () {
            sale_channel_code = $(this).val();
            var url = '?app_act=base/shop/serial_num';
            $.ajax({
                type: "POST",
                url: url,
                data: {'sale_channel_code': sale_channel_code},
                dataType: "json",
                async: true,
                success: function (data) {
                    if (data.status == 'success') {
                        $("#shop_code").val(data.data);
                        //BUI.Message.Alert(data.message);
                    }
                }
            });
        });
        $("#sale_channel_code").change();
        $("input[name='fenxiao_status']").eq(1).attr("checked", "checked");
    }

    function pre_authorize() {
        var url = $("#auth_url").val();
        window.open(url);

        //弹出页面
        BUI.use(['bui/overlay', 'bui/mask'], function (Overlay) {
            var dialog = new Overlay.Dialog({
                title: '店铺授权',
                width: '98%',
                height: 200,
                buttons: [
                    {text: '授权成功', elCls: 'button button-primary', handler: function () {
                            authorize_succ();
                            this.hide();
                        }}
                    ,
                    {text: '我已订购，重试一次', elCls: 'button button-danger', handler: function () {
                            this.hide();
                            pre_authorize();
                        }}
                ],
                bodyContent: '请选择授权结果？（注：未订购应用请先完成订购，之后授权）',
                mask: true
            });
            dialog.show();
        });
    }

    //授权成功
    function authorize_succ() {
        var shop_code = $('#shop_code').val();
        var url = '?app_act=base/shop/shop_authorize_success';

        $.ajax({
            type: "POST",
            url: url,
            data: {'shop_code': shop_code},
            dataType: "json",
            async: true,
            success: function (data) {
                if (data.status == 1) {
                    //BUI.Message.Alert(data.message);
                    location.reload();
                } else {
                    BUI.Message.Alert(data.message);
                }
            }
        });
    }

//    $("#days").keyup(function () {
//        if (this.value.length == 1) {
//            this.value = this.value.replace(/[^1-9]/g, '')
//        } else {
//            this.value = this.value.replace(/\D/g, '')
//        }
//
//    });
    
    
    $(".control-label").css("width", "100px");
    function get_tab_param(sale_channel_code) {
        var url = '?app_act=base/shop/get_sale_channel_params&app_fmt=json';
        var data = {};
        data.sale_channel_code = sale_channel_code;
        data.shop_code = $('#shop_code').val();
        $.post(url, data, function (result) {
            if (result.status == 1) {
                //先删除
                var count = TabPage1Tab.getItemCount();
                if (count > 1) {
                    $("#TabPage1 ul").find("li").not("li:first").remove();
//                    var item = TabPage1Tab.getLastItem();
//                    item.remove(true);
                }

                var content = '    <form  class="form-horizontal" id="form2" action="" method="post">';
                var i = 1;
                for (var key in result.data) {
                    content += '<div class="row" style="height:30px;line-height:30px;">';
                    content += '<div class="control-group span16">'
                    content += '<label class="control-label span3" style="width: 60px;">参数' + i + '：</label>';
                    content += '<div class="controls">';
                    if (sale_channel_code == 'chuanyi' || sale_channel_code == 'chuchujie' || sale_channel_code == 'yamaxun') {
                        content += '  <input  type="hidden"  name="param' + i + '" id="param' + i + '" value="' + key + '" />';
                        content += '  <input  class="input-small  control-text" name="param_name' + i + '" id="param_name' + i + '" value="' + result.data[key].name + '" type="text" disabled="disabled" style="width:100px;"><label></label>';
                    } else {
                        content += '  <input  class="input-small  control-text" name="param' + i + '" id="param' + i + '" value="' + key + '" type="text" disabled="disabled" ><label> </label>';
                    }
                    content += get_type_html(result.data[key], i);
                    content += '<span style="color:red;">' + result.data[key].desc + '</span>';
                    content += '  </div>';
                    content += '  </div>';
                    content += '  </div>';
                    i++;
                }
                content += '</form>';
                if (sale_channel_code == 'weipinhui') {
                    content += api_weipinhui;
                }
                <?php if ($response['active'] == 2): ?>
                    TabPage1Tab.addChild({title: '参数信息', panelContent: content, 'selected': 'active', 'tpl': '<a href="#" onClick="sub_show(2);" id="tabs_param">{text}{title}</a>'});
                    sub_show(2);
                <?php else: ?>
                    TabPage1Tab.addChild({title: '参数信息', panelContent: content, 'tpl': '<a href="#" onClick="sub_show(2);" id="tabs_param">{text}{title}</a>'});
                <?php endif; ?>
                if (scene == 'edit' && product_version_no > 0) {
                    var express_content = $("#form3_express").html();
                    TabPage1Tab.addChild({title: '配送方式', panelContent: express_content, 'tpl': '<a href="#" onClick="sub_show(3);" id="tabs_express">{text}{title}</a>'});
                }
                var contact_content = $("#form4_express").html();
                TabPage1Tab.addChild({title: '联系信息', panelContent: contact_content, 'tpl': '<a href="#" onClick="sub_show(4);" id="tabs_contact">{text}{title}</a>'});
                var addr_row = {
                    'province':"<?php echo !empty($response['data']['province'])?$response['data']['province']:'';?>",
                    'city':"<?php echo !empty($response['data']['city'])?$response['data']['city']:'';?>",
                    'district':"<?php echo !empty($response['data']['district'])?$response['data']['district']:'';?>",
                    'street':"<?php echo !empty($response['data']['street'])?$response['data']['street']:'';?>"
                };
                op_area(addr_row);
                if(scene == 'edit'||scene=='add'){
                var senior_content = $("#form5_info").html();
                TabPage1Tab.addChild({title: '高级信息', panelContent: senior_content, 'tpl': '<a href="#" onClick="sub_show(5);" id="tabs_senior">{text}{title}</a>'});
                }
            } else {
//                var sale_channel_code =  "<?php // echo $response['data']['sale_channel_code']?>";

                if (sale_channel_code == 'houtai') {

                    var contact_content = $("#form4_express").html();
                    TabPage1Tab.addChild({title: '联系信息', panelContent: contact_content, 'tpl': '<a href="#" onClick="sub_show(4);" id="tabs_contact">{text}{title}</a>'});
                    var addr_row = {
                        'province':"<?php echo !empty($response['data']['province'])?$response['data']['province']:'';?>",
                        'city':"<?php echo !empty($response['data']['city'])?$response['data']['city']:'';?>",
                        'district':"<?php echo !empty($response['data']['district'])?$response['data']['district']:'';?>",
                        'street':"<?php echo !empty($response['data']['street'])?$response['data']['street']:'';?>"
                    };
                    op_area(addr_row);


                } else {

                    var count = TabPage1Tab.getItemCount();
                    if (count > 1) {
                        $("#TabPage1 ul").find("li").not("li:first").remove();
    //                    var item = TabPage1Tab.getLastItem();
    //                    item.remove(true);
                    }

                }
            }

        }, 'json');

    }
    $("#sale_channel_code").change(function(){
        $("#tabs_param").parent().remove();
     });
    function get_type_html(data, i) {
        var html = '';
        if (typeof (data.type) == 'undefined') {
            if (data.disabled == '1') {
                html = '<input style="width:120px;" class="input-small control-text" disabled="disabled" name="param' + i + '_va"  id="param' + i + '_val" value="' + data.val + '"  type="text">';
            } else {
                html = '<input style="width:120px;" class="input-small control-text"  name="param' + i + '_va"  id="param' + i + '_val" value="' + data.val + '"  type="text">';
            }
        } else if (data.type == 'radio') {
            var j = 1;
            for (var key in data.data) {
                var select = '';
                if (data.data[key] == data.val) {
                    select = 'checked="' + 'checked"';
                }
                var get_shop_warehouse = '';
                if (data.data[key] == '普通JIT' || data.data[key] == '分销JIT') {
                    get_shop_warehouse = "onclick=get_shop_warehouse('" + key + "')";
                }
                html += '<input ' + select + get_shop_warehouse+' name="param' + i + '_val" value="' + data.data[key] + '"  type="radio">' + data.data[key];
                if (j % 2 == 1) {
                    html += '&nbsp;&nbsp;';
                }
            }
        } else if(data.type == 'password'){
            html = '<input style="width:120px;" class="input-small control-text"  name="param' + i + '_va"  id="param' + i + '_val" value="' + data.val + '"  type="password">';
        }
        return html;
    }

    //设置页签提交按钮信息
    //参数(页签代码)：1-基本信息，2-参数信息，3-配送方式
    function  sub_show(tab_id) {
        disa_val = false;
        disp_val = 'none';
        sub_text = '提交';
        if (tab_id == 2) {
            disa_val = 'disabled';
            disp_val = '';
            sub_text = '授权激活并保存';
        }
//        if (sale_channel_code == 'taobao' ) {
//            $("#TabPage1Submit").find("#submit").attr('disabled', disa_val);
//            $("#TabPage1Submit").find("#reset").attr('disabled', disa_val);
//        }
        
        var auth_shop_arr = ['beibei', 'meilishuo','mogujie', 'weipinhui', 'feiniu', 'jumei', 'huayang', 'youzan', 'aliexpress', 'vdian', 'kaola', 'renrendian', 'weimob', 'zhe800','yihaodian','zouxiu', 'dajiashequ', 'chuizhicai', 'alibaba','ebay','pinduoduo'];
        if($.inArray(sale_channel_code, auth_shop_arr) != -1){
            $("#shop_auth").css('display', disp_val);
            $("#" + sale_channel_code + "_txt").css('display', disp_val);
        }

        if (scene == 'edit' && sale_channel_code == 'weipinhui') {
            var code = $('input:radio[name="param6_val"]:checked').val();
            if (code == '普通JIT') {
                $("#weipinhui_sync_type_txt").css('display', '');
            }else{
                $("#weipinhui_sync_type_txt").css('display', 'none');
            }
        }
//      if (sale_channel_code == 'jingdong') {
//                      $("#submit").html('授权激活并保存');
//         }
        //|| sale_channel_code == 'jingdong'


        /*
        if (sale_channel_code == 'chuanyi' || sale_channel_code == 'chuchujie' || sale_channel_code == 'meilishuo' || sale_channel_code == 'yamaxun' || sale_channel_code == 'beibei' || sale_channel_code == 'baidumall' || sale_channel_code == 'mogujie' || sale_channel_code == 'zhe800' || sale_channel_code == 'youzan' || sale_channel_code == 'aliexpress' || sale_channel_code == 'suning' || sale_channel_code == 'miya' || sale_channel_code == 'weimob' || sale_channel_code == 'vdian' || sale_channel_code == 'yintai' || sale_channel_code == 'gonghang') {
            $("#submit").html(sub_text);
        }*/

        if($.inArray(sale_channel_code, auth_sale_channel) != -1){
            $("#submit").html(sub_text);
        }
    }
    function get_shop_message(sale_channel_code){
         var url = '?app_act=base/shop/get_sale_channel_params&app_fmt=json';
            var data = {};
            data.sale_channel_code = sale_channel_code;
            data.shop_code = $('#taobao_shop_code').val();
            $.post(url, data, function (result) {
                var content = '';
                var i = 1;
                for (var key in result.data) {
                    content += '<div class="row" style="height:30px;line-height:30px;">';
                    content += '<div class="control-group span16">'
                    content += '<label class="control-label span3" style="width: 60px;">参数' + i + '：</label>';
                    content += '<div class="controls">';
                    content += '  <input  class="input-small  control-text" name="param' + i + '" id="param' + i + '" value="' + key + '" type="text" disabled="disabled" ><label> </label>';
                    content += get_type_html(result.data[key], i);
                    content += '<span style="color:red;">' + result.data[key].desc + '</span>';
                    content += '  </div>';
                    content += '  </div>';
                    content += '  </div>';
                    i++;
                }
                $("#form2").html();
                $("#form2").html(content);
            },'json');
    }
    $(function () {
        var html = '';
        html += '<input type="hidden" name="custom_code" value="" id="custom_code">';
        $("#_select_pop").parent().append(html);
        if (scene == 'add') {
            $("#_select_pop").hide();
            $("#_select_pop").parent().prev().hide();
            $("#_select_pop").next().hide();
        } else if (scene == 'edit') {
            if($('#entity_type :selected').val() == 2){
                var custom_name = '<?php echo $response['custom_name']; ?>';
                var custom_code = '<?php echo $response['data']['custom_code']; ?>';
                $("#_select_pop").show();
                $("#_select_pop").parent().prev().show();
                $("#_select_pop").next().show();
                $("#_select_pop").val(custom_name);
                $('#custom_code').val(custom_code);
            } else {
                $("#_select_pop").hide();
                $("#_select_pop").parent().prev().hide();
                $("#_select_pop").next().hide();
            }
        }
        $("#entity_type").change(function(){
            if($('#entity_type :selected').val() == 2){
                $("#_select_pop").show();
                $("#_select_pop").parent().prev().show();
                $("#_select_pop").next().show();
                if(login_type != 2) {
                    var html = '';
                    <?php foreach ($response['fx_store'] as $val) { ?>
                        html += "<option value='<?php echo $val['store_code']?>'><?php echo $val['store_name']?></option>";
                    <?php } ?>
                    $('#send_store_code').html(html);
                }
            } else {
                $("#_select_pop").hide();
                $("#_select_pop").parent().prev().hide();
                $("#_select_pop").next().hide();
                if(login_type != 2) {
                    var html = '';
                    <?php foreach ($response['store'] as $val) { ?>
                        html += "<option value='<?php echo $val[0]?>'><?php echo $val[1]?></option>";
                    <?php } ?>
                    $('#send_store_code').html(html);
                }
            }
        });
        $("#sale_channel_code").change(function(){
            if($('#sale_channel_code :selected').val() == 'taobao') {
                $(':input[name = "fenxiao_status"]').show();
                $(':input[name = "fenxiao_status"]').next().show();
                $('#rd_fenxiao_status_0').parent().parent().prev().show();
//                $("#entity_type").hide();
//                $("#entity_type").parent().prev().hide();
//                $('#_select_pop').hide();
//                $("#_select_pop").parent().prev().hide();
//                $('#_select_pop').next().hide();
            } else {
                $(':input[name = "fenxiao_status"]').hide();
                $(':input[name = "fenxiao_status"]').next().hide();
                $('#rd_fenxiao_status_0').parent().parent().prev().hide();
                $("#entity_type").show();
                $("#entity_type").parent().prev().show();
                if($("#entity_type :selected").val() == 2) {
                    $('#_select_pop').show();
                    $("#_select_pop").parent().prev().show();
                    $('#_select_pop').next().show();
                }
            }
            if($('#sale_channel_code :selected').val() == 'fenxiao'){
                $('#taobao_shop_code').show();
                $('#taobao_shop_code').next().show();
                $('#taobao_shop_code').parent().prev().show();
            }else{
                $('#taobao_shop_code').hide();
                $('#taobao_shop_code').next().hide();
                $('#taobao_shop_code').parent().prev().hide();
            }
        });

        if (scene == 'edit') {
            if(active == '1'){
            $('#taobao_shop_code').attr("disabled", "disabled");                
            }
            $("#sale_channel_code").attr("disabled", "disabled");
            var is_update_entity = '<?php echo $response['is_update_entity']; ?>';
            if(is_update_entity > 0) {
                $("#entity_type").attr("disabled", "disabled");
            }
        }
        $('#sale_channel_code').change(function () {
            var sale_channel_code = $(this).val();
            get_tab_param(sale_channel_code);
        });
        $('#taobao_shop_code').change(function () {
           var sale_channel_code =  $('#sale_channel_code').val()
           get_shop_message(sale_channel_code);
        });
        
        setTimeout(function () {
            $('#sale_channel_code').change();
        }, 10);
        $("#tabs_base").click(function () {
            sub_show(1);
        });
        
        if(login_type == 2) { //分销商登录
            $('#custom_name').attr('disabled',true);
            $('#custom_name').val('<?php echo $response['custom']['custom_name']; ?>');
            if (scene == 'edit') {
                $('#send_store_code').attr('disabled',true);
                $('#refund_store_code').attr('disabled',true);
            }
        }
        
    });
    
    
        function do_submit(type){
            //小数只支持一位
            var days = $('#days').val();       
            var pattern1 = /^\d+(\.\d+)$/g;  //正则 先判断是不是小数
            var pattern2 = /^\d+(\.\d{1})$/g; //再判断是不是一位小数
            var result1 = days.match(pattern1);
            if (result1 != null) {
                var result2 = days.match(pattern2);
                if (result2 == null) {
                    BUI.Message.Alert('承诺发货天数最多只能是一位小数', 'error');
                    return;
                }
            }
            var sale_channel_code = $("#sale_channel_code").val();
            if (sale_channel_code == '') {
                BUI.Message.Alert('销售平台不能为空', 'error');
                return;
            }
            var send_store_code = $("#send_store_code").val();
            if (send_store_code == '') {
                BUI.Message.Alert('发货仓库不能为空', 'error');
                return;
            }
            var express_code = $("#express_code").val();
            if (express_code == '') {
                BUI.Message.Alert('默认配送方式不能为空', 'error');
                return;
            }

            if (sale_channel_code == 'taobao') {
                var nick = $("#param5_val").val();
                if (nick == '') {
                    BUI.Message.Alert('参数信息页签下的nick不能为空', 'error');
                    return;
                }
            }
            if (sale_channel_code == 'jingdong') {
                var nick = $("#param7_val").val();
                if (nick == '') {
                    BUI.Message.Alert('参数信息页签下的nick不能为空', 'error');
                    return;
                }
            }
            if($('#entity_type :selected').val() == 2 && $("#_select_pop").val() == '') {
                BUI.Message.Alert('请选择分销商', 'error');
                return;
            }
            var data = new Object();

            $("#form1").find("input").each(function () {
                data[$(this).attr("id")] = $(this).val();
            });
            $("#form1").find("select").each(function () {
                data[$(this).attr("id")] = $(this).val();
            });
            $("#form1").find("input:radio:checked").each(function () {
                data[$(this).attr("name")] = $(this).val();
            });
            if (TabPage1Tab.getItemCount() > 1) {
                $("#form2").find("input[type='text']").each(function () {
                    data[$(this).attr("id")] = $(this).val();
                });
                $("#form2").find("input[type='hidden']").each(function () {
                    data[$(this).attr("id")] = $(this).val();
                });
                $("#form2").find("input[type='radio']:checked").each(function () {
                    data[$(this).attr("name")] = $(this).val();
                });
                if (scene == 'edit' && product_version_no > 0) {
                    var express_data = [];
                    $("#form3").find("input[name='express_data[]']:checked").each(function () {
                        express_data.push($(this).val());
                    });
                    data['express_data'] = express_data;
                }
                $("#form4").find("input[type='text']").each(function () {
                    data[$(this).attr("name")] = $(this).val();
                });
                $("#form4").find("select").each(function () {
                    data[$(this).attr("id")] = $(this).val();
                });
                var  inv_syn='';
                  $("#form5").find("input[name='inv_syn']:checked").each(function () {
                        inv_syn=($(this).val());
                    });
                 data['inv_syn'] = inv_syn;
            }
            //店铺，唯品会仓库绑定
            var warehouse_info={}
            $("#weipinhui_type :input[type='text']").each(function (i) {
                var sync_val = this.value;
                warehouse_info['sync_val_' + i] = sync_val;
            });
            $("#weipinhui_type").find('select').each(function (i) {
                var warehouse_code = this.value;
                warehouse_info['warehouse_code_' + i] = warehouse_code;
            });
            data['shop_warehouse_params'] = warehouse_info;
            var url = '?app_act=base/shop/do_edit&app_fmt=json';
            if (scene == 'add') {
                url = '?app_act=base/shop/do_add&app_fmt=json';
            }
            $.post(url, data, function (ret) {
                if (ret.status == 1) {
                    if(type == 1) {
                        ui_closePopWindow('<?php echo $request['ES_frmId'] ?>');
                        window.location.reload();
                    }
                } else {
                    BUI.Message.Alert(ret.message, 'error');
                }

            }, 'json');
     }

    function auth_shop() {
        switch(sale_channel_code){
            case 'meilishuo' :
                window.open("http://login.baotayun.com/session/meilishuo");
                break;
            case 'beibei' :
                window.open("http://api.open.beibei.com/outer/oauth/app.html?app_id=ejjx");
                break;
            case 'mogujie' :
                window.open("http://login.baotayun.com/session/mogujie");
                break;
            case 'weipinhui' :
                var val=$('input:radio[name="param1_val"]:checked').val();
                if(val=='直发'){
                    window.open("http://login.baotayun.com/session/weipinhui");
                }else{
                    window.open("http://login.baotayun.com/session/wphjit");
                }
                break;
            case 'feiniu' :
                window.open("http://login.baotayun.com/session/fniu");
                break;
            case 'jumei' :
                window.open("http://partner.ext.jumei.com/Index/Login");
                break;
            case 'aliexpress' :
                set_api_params(sale_channel_code);
                break;
            case 'weimob' :
                set_api_params(sale_channel_code);
                break;
            case 'zouxiu' :
                set_api_params(sale_channel_code);
                break;
            case 'dajiashequ' :
                set_api_params(sale_channel_code);
                break;
            case 'chuizhicai' :
                set_api_params(sale_channel_code);
                break;
            case 'alibaba' :
                set_api_params(sale_channel_code);
                break;
            case 'ebay' :
                set_api_params(sale_channel_code);
                break;
            case 'pinduoduo' :
                set_api_params(sale_channel_code);
                break;
            default :
                auth_platform(sale_channel_code);
                break;
        }
    }
    
    function auth_platform(sale_channel_code, api_params = ''){
        var url = '?app_act=base/shop/auth_platform&app_fmt=json';
        var param = {sale_channel_code: sale_channel_code, api_params: api_params};
        $.post(url, param, function (result) {
            if(sale_channel_code == 'zouxiu'){
                if(result.errorCode == 0){
                    var token = result.data;
                    $("#param5_val").val(token);
                }
            }
//            if (sale_channel_code == 'aliexpress' && api_params.app_key == '24683750') {
//                result.data = 'http://login.baotayun.com/session_app/aliexpress_app/';
//            }
            window.open(result.data);
            if(sale_channel_code == 'ebay') {
                var type = result.status == 1 ? 'success' : 'error';
                BUI.Message.Alert(result.message, type);
            }
        }, 'json')
    }
    
    function set_api_params(sale_channel_code){
        var shop_code = $("#shop_code").val();
        switch(sale_channel_code){
            case 'weimob' :
//               var AppId = $("#param2_val").val();
//               var AppSecret  = $("#param3_val").val();
//               if(AppId == '' || AppSecret == ''){
//                   BUI.Message.Alert('AppId和AppSecret不能为空，请先填写AppId和AppSecret', 'error');
//                   return;
//               }
                var api_params = {'shop_code': shop_code};
                break;
            case 'aliexpress' :
               var app_key = $("#param2_val").val();
               var app_secret  = $("#param3_val").val();
               if(app_key == '' || app_secret == ''){
                   BUI.Message.Alert('app_key和app_secret不能为空， 请先填写app_key和app_secret', 'error');
                   return;
               }
               //var shop_code = $("#shop_code").val();
               var api_params = {'app_key': app_key, 'app_secret': app_secret, 'shop_code': shop_code};
               break;
            case 'zouxiu' :
                var uid = $("#param2_val").val();
                var username = $("#param3_val").val();
                var password = $("#param4_val").val();
                //var shop_code = $("#shop_code").val();
                if(uid == '' || username == '' || password == ''){
                    BUI.Message.Alert('uid,username,password不能为空， 请先填写uid,username,password', 'error');
                    return;
                }
                var api_params = {'uid': uid, 'username': username,'password':password,'shop_code': shop_code};
                break;
            case 'dajiashequ' :
                var username = $("#param2_val").val();
                var password = $("#param3_val").val();
                var companyID = $("#param4_val").val();
                if(username == '' || password == '' || companyID == ''){
                    BUI.Message.Alert('username, password, companyID不能为空， 请先填写username, password, companyID', 'error');
                    return;
                }
                var b = new Base64();
                var api_params = {'username': username,'password':b.encode(password), 'companyID': companyID};
                break;
            case 'chuizhicai' :
                var AppKey = $("#param2_val").val();
                var AppSecret = $("#param3_val").val();
                //var shop_code = $("#shop_code").val();
                if(AppSecret == '' || AppKey == ''){
                    BUI.Message.Alert('AppKey, AppSecret不能为空， 请先填写AppKey, AppSecret', 'error');
                    return;
                }
                var api_params = {'shop_code': shop_code, 'AppKey': AppKey,'AppSecret':AppSecret};
                break;
            case 'alibaba' :
                var app_key = $("#param1_val").val();
                var app_secret = $("#param2_val").val();
                if(app_key == '' || app_secret == ''){
                    BUI.Message.Alert('app_key, app_secret不能为空， 请先填写app_key, app_secret', 'error');
                    return;
                }
                var api_params = {'app_key': app_key,'app_secret':app_secret, 'shop_code': shop_code};
                break;
            case 'ebay' :
                var AppID = $("#param2_val").val();
                var DevID = $("#param3_val").val();
                var CertID = $("#param4_val").val();
                var RuName = $("#param5_val").val();
                do_submit();
                var api_params = {'AppID': AppID,'DevID': DevID,'CertID': CertID,'RuName': RuName, 'shop_code': $('#shop_code').val()};
                break;
        }
        auth_platform(sale_channel_code, api_params);
    }
    
    function op_area(info){
        var url = "<?php echo get_app_url('base/store/get_area'); ?>";
        $('#province').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,1,url);
        });

        $('#city').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id, 2, url);
        });
        $('#district').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id, 3, url);
        });
        areaChange(1,0,url,function(){
            $("#province").val(info.province);
            areaChange($("#province").val(),1,url,function(){
                $('#city').val(info.city);
                areaChange($("#city").val(),2,url,function(){
                    $('#district').val(info.district);
                    areaChange($("#district").val(),3,url,function(){
                        $('#street').val(info.street);
                    });
                });
            });
        });
    }


    function add(type){
        if (type == 'weipinhui') {
            var num = $("#weipinhui_type").find("tr").length - 1;
            var warehouse =<?php echo json_encode($response['warehouse']);?>;
            var html = '';
            html += "<tr class='sync_type' ><td  width='100px' class='tdlabel'><select style='width:100px;' class='type_" + num + "' id='warehouse_"+num+"'><option value=''>请选择</option>";
            $.each(warehouse, function (i, val) {
                html += "<option value='" + val.warehouse_code + "'>" + val.warehouse_name + "</option>";
            });
            html += "</select></td>";
            html +="<td class='tdlabel' width='100px'><input type='text' style='width: 150px' value='100' class='type_" + num + "' id='sync_"+num+"'/></td><td><p class='minus_btn' onclick=del(this)  style='width: 50px'><img src='assets/images/minus.png'>删除</p></td></tr>";
           console.log(html);
            $("#weipinhui_type").append(html);
        }
    }

    function del(item){
        $(item).parent("td").parent("tr").remove();
    }


    //唯品会平台，普通JIT和分销jit切换
    function get_shop_warehouse(i) {
        var sale_channel_code = $("#sale_channel_code").val();
        if (sale_channel_code == 'weipinhui') {
            if (i == 0) {
                $("#weipinhui_sync_type_txt").css('display', '');
            } else {
              //  $("#api_weipinhui").css('display', 'none');
                $("#weipinhui_sync_type_txt").css('display', 'none');

            }
        }
    }


</script>


