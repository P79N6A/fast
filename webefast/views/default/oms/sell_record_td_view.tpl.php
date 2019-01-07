<style>
    .panel-body {padding: 0;}
    .panel-body table {margin: 0; }
    .barcode-update {color:#000;font-weight:bold}
</style>
<form id="recordForm" name="recordForm"  >
    <div class="panel">
        <div class="panel-header clearfix">
            <h3 class="pull-left">订单信息</h3>
            <div class="pull-right">
            </div>
        </div>
        <div class="panel-body" id="panel_baseinfo">
            <table cellspacing="0" class="table table-bordered">
                <tr>
                    <td width="15%" align="right">交易号：
                        <input type="hidden" name="mingxi" value="<?php echo isset($response['record']['mingxi']) ? $response['record']['mingxi'] : ''; ?>">
                        <input type="hidden" name="api_order_id" value="<?php echo isset($response['record']['id']) ? $response['record']['id'] : ''; ?>">
                        <input type="hidden" name="tid" value="<?php echo isset($response['record']['tid']) ? $response['record']['tid'] : ''; ?>">
                    </td>
                    <td width="10%" id="tid"><?php echo $response['record']['tid']; ?></td>
                    <td width="15%" align="right">下单时间：</td>
                    <td width="10%"><?php echo $response['record']['order_first_insert_time']; ?></td>
                    <td width="10%" align="right">付款时间：</td>
                    <td width="20%"><?php echo $response['record']['pay_time']; ?></td>
                </tr>
                <tr>
                    <td  align="right">数量：</td>
                    <td ><?php
                        if ($response['record']['num'] > 0) {
                            echo $response['record']['num'];
                        } else {
                            echo '0';
                        }
                        ?></td>
                    <td align="right">金额：</td>
                    <td ><?php echo round($response['record']['order_money'], 2); ?></td>
                    <td  align="right">收货人：</td>
                    <td ><span class = 'bianjirecord' id ='receiver_name'><?php echo $response['record']['receiver_name']; ?></span></td>
                </tr>
                <tr>
                    <td  align="right">手机号码：</td>
                    <td ><span class = 'bianjirecord' id ='receiver_mobile'><?php echo $response['record']['receiver_mobile']; ?></span></td>
                    <td  align="right">固定电话：</td>
                    <td ><span class = 'bianjirecord' id ='receiver_phone'><?php echo $response['record']['receiver_phone']; ?></span></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td  align="right">买家留言：</td>
                    <td  colspan="5"><?php echo $response['record']['buyer_remark']; ?></td>
                </tr>
                <tr>
                    <td  align="right">商家留言：</td>
                    <td  colspan="5"><?php echo $response['record']['seller_remark']; ?></td>
                </tr>
                <tr>
                    <td  align="right">收货地址：</td>
                    <td  colspan="5"><span class= 'sheng_city'>
                            <?php echo $response['record']['receiver_country'] . $response['record']['receiver_province'] . $response['record']['receiver_city'] . $response['record']['receiver_district'] . $response['record']['receiver_street']; ?>
                        </span>
                        <span class="quyu" style="display:none;">
                                <?php echo $response['record']['receiver_country'] . $response['record']['receiver_province'] . $response['record']['receiver_city'] . $response['record']['receiver_district'] . $response['record']['receiver_street'] . $response['record']['receiver_addr']; ?><br>
                            <select id="country" name="country" data-rules="{required : true}">
                                <option value ="">请选择国家</option>
                                <?php foreach ($response['area']['country'] as $k => $v) { ?>
                                    <option  value ="<?php echo $v['id']; ?>" <?php if ($v['name'] == $response['record']['receiver_country']) { ?> selected="selected" <?php } ?> ><?php echo $v['name']; ?></option>
<?php } ?>
                            </select>
                            <select id="province" name="province" data-rules="{required : true}">
                                <option value ="">请选择省</option>
                                <?php foreach ($response['area']['province'] as $k => $v) { ?>
                                    <option  value ="<?php echo $v['id']; ?>" <?php if ($v['name'] == $response['record']['receiver_province'] || $v['name'] == $response['record']['receiver_province'] . "省") { ?> selected="selected" <?php } ?>><?php echo $v['name']; ?></option>
<?php } ?>
                            </select>
                            <select id="city" name="city" data-rules="{required : true}">
                                <option value ="">请选择市</option>
                                <?php foreach ($response['area']['city'] as $k => $v) { ?>
                                    <option  value ="<?php echo $v['id']; ?>" <?php if ($v['name'] == $response['record']['receiver_city'] || $response['record']['ids']['city'] == $v['id']) { ?> selected <?php } ?>><?php echo $v['name']; ?></option>
<?php } ?>
                            </select>
                            <select id="district" name="district" data-rules="{required : true}">
                                <option value ="">请选择区县</option>
                                <?php foreach ($response['area']['district'] as $k => $v) { ?>
                                    <option  value ="<?php echo $v['id']; ?>" <?php if ($v['name'] == $response['record']['receiver_district'] || $response['record']['ids']['district'] == $v['id']) { ?> selected <?php } ?>><?php echo $v['name']; ?></option>
<?php } ?>
                            </select>
                            <select id="street" name="street">
                                <option value ="">请选择街道</option>
                                <?php foreach ($response['area']['street'] as $k => $v) { ?>
                                    <option  value ="<?php echo $v['id']; ?>" <?php if ($v['name'] == $response['record']['receiver_street']) { ?> selected <?php } ?>><?php echo $v['name']; ?></option>
<?php } ?>
                            </select>
                        </span>
                        <span class = 'bianjirecord' id ='receiver_addr'><?php echo $response['record']['receiver_addr']; ?></span></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="panel">
        <div class="panel-header clearfix">
            <h3 class="pull-left">商品明细</h3>
            <div class="pull-right">
            </div>
        </div>
        <div class="panel-body" id="panel_baseinfo">
            <table cellspacing="0" class="table table-bordered">
                <tr>
                    <th width="10%">操作</th>
                    <th width="10%">图片</th>
                    <th width="10%">商品编码</th>
                    <th>商品名称</th>
                    <th width="16%">商品属性</th>
                    <th width="13%">商品条形码</th>
                    <th width="6%">数量</th>
                    <th width="9%">金额</th>
                    <th width="8%">平台礼品</th>
                </tr>
                <?php
                //echo '<hr/>$xx<xmp>'.var_export($response['record']['detail_list'],true).'</xmp>';
                foreach ($response['record']['detail_list'] as $key => $detail) {
                    ?>
                    <tr>
                        <td>
                            <?php
                            if (load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/td_view/td_delete')) {
                                if ($response['record']['is_change'] != 1) {
                                    ?>
                                    <a href="#" onclick="td_delete(<?php echo $detail['detail_id'] ?>,'<?php echo $detail['tid'] ?>')">删除</a>
                                <?php
                                }
                            }
                            ?>
                        </td>
                        <td><?php if (isset($detail['pic_path']) && $detail['pic_path'] <> '') { ?><img src="<?php echo $detail['pic_path']; ?>" style="width:48px; height:48px;"><?php } ?></td>
                        <td><?php echo isset($detail['goods_code']) ? $detail['goods_code'] : ''; ?></td>
                        <td><?php echo isset($detail['title']) ? $detail['title'] : ''; ?></td>
                        <td><?php echo isset($detail['sku_properties']) ? $detail['sku_properties'] : ''; ?></td>
                        <td>
                            <span class = 'bianjigoods' id ="<?php echo 'barcode[' . $detail['detail_id'] . ']'; ?>"><?php echo isset($detail['goods_barcode']) ? $detail['goods_barcode'] : ''; ?></span>
                            <!--	<a href="javascript:void(0);" class="barcode-update">更新</a>-->
                        </td>
                        <td><?php echo isset($detail['num']) ? $detail['num'] : ''; ?></td>
                        <td><?php echo isset($detail['avg_money']) ? $detail['avg_money'] : ''; ?></td>
                        <td><?php echo isset($detail['is_gift']) && $detail['is_gift'] ==1 ? '是':'否'; ?></td>
                    </tr>
<?php } ?>
            </table>
        </div>
    </div>
    <div class="clearfix" style="padding: 0 0 15px 0; text-align: center;">
        <?php if ($response['record']['is_change'] <> '1' && load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/td_save')) { ?>
            <input type="button" class="button button-primary" id="btn_edit" value="修改商品">
            <input type="button" class="button button-primary" id="btn_edit_record" value="修改订单">
<?php } ?>
        <input type="button" class="button button-primary" id="btn_save" style="display:none;" value = "保存">
        <input type="button" class="button button-primary" id="btn_save_record" style="display:none;" value = "保存">
        <button class="button button-primary" id="btn_close">关闭</button>
    </div>
</form>
<input id="shop_code" type="hidden" value="<?php echo $response['record']['shop_code']; ?>" />
<?php echo load_js('comm_util.js') ?>
<script>
    var url = '<?php echo get_app_url('base/store/get_area'); ?>';
    var tid ='<?php echo $response['record']['tid'];?>';
//更新允许转单但未转单订单的商品条码操作
    $(".barcode-update").click(function () {
        BUI.Message.Show({
            title: '自定义提示框',
            msg: '是否执行更新允许转单但未转单订单的商品条码操作？',
            icon: 'question',
            buttons: [
                {
                    text: '是',
                    elCls: 'button button-primary',
                    handler: function () {
                        var params = {'app_fmt': 'json'};
                        params.tid = $('#tid').html();
                        params.shop_code = $('#shop_code').val();
                        $.post("?app_act=oms/sell_record/barcode_update", params, function (data) {
                            if (data.status == 1) {
                                BUI.Message.Alert(data.message, 'info');
                                location.reload();
                            } else {
                                BUI.Message.Alert(data.message, 'error');
                            }
                        }, "json");
                        this.close();
                    }
                },
                {
                    text: '否',
                    elCls: 'button',
                    handler: function () {

                        this.close();
                    }
                }
            ]
        });
    });



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




        $("#btn_edit").click(function () {
            $(".bianjigoods").each(function (index) {
                var value = $(this).html();
                var name = $(this).attr("id");
                $(this).html("<input type='text' name='" + name + "' class= '" + name + "' value='" + value + "'>");
              <?php  $response['record']['receiver_addr'] ;?>

            });
            $("#btn_edit").hide();
            $("#btn_edit_record").hide();
            $("#btn_save").show();
        });
         var source = '<?php echo $response['record']['source'];?>';
           if(source=='taobao'){
                    $('#receiver_name').removeClass('bianjirecord');
                       $('#receiver_mobile').removeClass('bianjirecord');
                          $('#receiver_phone').removeClass('bianjirecord');
            }

        $("#btn_edit_record").click(function () {
            $(".bianjirecord").each(function (index) {
                var value = $(this).html();
                var name = $(this).attr("id");
                //receiver_name receiver_mobile receiver_phone

                $(this).html("<input type='text' name='" + name + "' class= '" + name  + "'>");
                $("input[name='"+name+"']").val(value);

            });
            $(".quyu").show();
            $(".sheng_city").hide();
            $("#btn_edit").hide();
            $("#btn_edit_record").hide();
            $("#btn_save_record").show();
        });


        //修改商品
        $("#btn_save").click(function () {
            var data = $('#recordForm').serialize();
            data = data + '&shop_code=' + $('#shop_code').val();
            $.post('<?php echo get_app_url('oms/sell_record/td_save_goods_check'); ?>', data, function (data) {
                if (data.status == 1) {
                    td_save_goods();
                } else {
                    td_save_goods_no_update();
                }
            }, "json");
        });

//保存商品
        function td_save_goods() {
            BUI.Message.Show({
                title: '修改商品',
                msg: "您正在修改交易号："+tid+"的商品条形码，请确认操作！<br /> <input type='checkbox' id='update_status'/>&nbsp;<span style='color:red'>同步修改同店铺其他转单失败交易的商品条形码</span>",
                icon: 'question',
                buttons: [
                    {
                        text: '确认',
                        elCls: 'button button-primary',
                        handler: function () {
                            var data = $('#recordForm').serialize();
                            var update_status=$("#update_status").attr('checked') == 'checked' ? '1' : '0';
                            data = data + '&shop_code=' + $('#shop_code').val() + '&update_status='+update_status;
                            $.post('<?php echo get_app_url('oms/sell_record/td_save'); ?>', data, function (data) {
                                var type = data.status == 1 ? 'success' : 'error';
                                if (data.status == 1) {
                                    BUI.Message.Alert('修改成功', type);
                                    window.location.reload();
                                } else {
                                    BUI.Message.Alert(data.message, function () { }, type);
                                }
                            }, "json");
                            this.close();
                        }
                    },
                    {
                        text: '取消',
                        elCls: 'button',
                        handler: function () {
                            this.close();
                        }
                    }
                ]
            });
        }

//保存商品不更新
        function td_save_goods_no_update() {
            var data = $('#recordForm').serialize();
            data = data + '&shop_code=' + $('#shop_code').val() + '&update_status=2';
            $.post('<?php echo get_app_url('oms/sell_record/td_save'); ?>', data, function (data) {
                var type = data.status == 1 ? 'success' : 'error';
                if (data.status == 1) {
                    BUI.Message.Alert('修改成功', type);
                    window.location.reload();
                } else {
                    BUI.Message.Alert('修改失败', type);
                }
            }, "json");
        }

        function judgeIsNum (srt){
            var pattern=/^\d+$/g;  //正则表达式 ^ 代表从开始位置起   $ 末尾   + 是连续多个  \d 是数字的意思   不懂的话可以去百度正则表达式表
            var result= srt.match(pattern);//match 是匹配的意思   用正则表达式来匹配
            if (result==null){
                return false;
            }else{
                return true;
            }
        }
//修改订单
        $("#btn_save_record").click(function () {
            var source = '<?php echo $response['record']['source'];?>';
            if(source!='taobao'){
                var sMobile = $(".receiver_mobile").val();
                if (sMobile !== '' && !judgeIsNum(sMobile) && typeof (sMobile) !== "undefined") {
                    BUI.Message.Alert('输入的号码不是纯数字', 'error');
                    $(".receiver_mobile").focus();
                    return false;
                }
            }
            
            var data = $('#recordForm').serialize();
            data = data + '&update_status=2';
            $.post('<?php echo get_app_url('oms/sell_record/td_save'); ?>', data, function (data) {
                var type = data.status == 1 ? 'success' : 'error';
                if (data.status == 1) {
                    BUI.Message.Alert('修改成功', type);
                    window.location.reload();
                } else {
                    BUI.Message.Alert(data.message, function () { }, type);
                }
            }, "json");
        });

        $("#btn_close").click(function () {
            ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
        });
    });
    function td_delete(detail_id, tid) {
        if (confirm("确定删除吗")) {
            $.ajax({type: 'POST', dataType: 'json',
                url: '<?php echo get_app_url('oms/sell_record/td_delete'); ?>', data: {detail_id: detail_id, tid: tid},
                success: function (ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        BUI.Message.Alert('删除成功', type);
                        window.location.reload();
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                }
            });
        }
    }
</script>