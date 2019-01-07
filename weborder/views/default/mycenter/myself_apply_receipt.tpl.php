<?php echo load_css('bui.css', true) ?>
<?php echo load_css('dpl.css', true) ?>
<?php echo load_js('comm_util.js', TRUE); ?>
<?php echo load_js('bui.js', true); ?>

<style type="text/css">
    .bui-uploader-button-text {
        color: #33333;
        font-size: 14px;
        line-height: 20px;
        text-align: center;
    }

    .bui-uploader .bui-uploader-button-wrap .file-input-wrapper {
        display: block;
        height: 20px;
        left: 0;
        overflow: hidden;
        position: absolute;
        top: 0;
        width: 60px;
        z-index: 300;
    }
    .defaultTheme .bui-uploader-button-wrap {
        /* 	background:none; */
        background: rgba(0, 0, 0, 0) -moz-linear-gradient(center top , #fdfefe, ) repeat scroll 0 0;
        border-radius: 4px;
        color: #333;
        display: inline-block;
        font-size: 14px;
        height: 20px;
        line-height: 20px;
        margin-right: 10px;
        overflow: hidden;
        padding: 0;
        position: relative;
        text-align: center;
        text-decoration: none;
        z-index: 500;
        padding: 2px 12px;
    }
    .bui-uploader-htmlButton {
        float:left;
    }
    .bui-simple-list {
        float:left;
    }
    .imgcopy{margin: 8px 0 8px 0;}
    .button{
        width: 79px;
        height: 33px;
        border: 3px solid #e95513;
        text-align: center;
        font-size: 15px;
        margin-right: 2px;
        cursor: pointer;
        background: #e95513;
        color: #FFF;
    }
    .button:hover{
        background:#f25c1e;
        border-color:#ee571b;
        color:#eee;
    }
    .pos{
        position: absolute;
        right: 5%;
        top: 16%;
    }
</style>
<script type="text/javascript" src="../../webpub/js/upload_img.js"></script>
<script type="text/javascript" src="../../webpub/js/uploader-min.js"></script>
<?php echo load_css('common.css', true); ?>
<?php echo load_css('order.css',true);?>
<div class="order_wrap">
<?php include get_tpl_path('top') ?>
<div class="receipt-content" style="margin: 110px 0 0 100px;font-size: 12px;">
    <form id="J_Form" action="?app_act=mycenter/myself/do_apply_receipt&app_fmt=json" method="post" class="form-horizontal">
        <input type="hidden" name="receipt_id" value="<?php echo!empty($response['receipt_id']) ? $response['receipt_id'] : ''; ?>"/>
        <input type="hidden" name="app_scene" value="<?php echo!empty($response['app_scene']) ? $response['app_scene'] : ''; ?>"/>
        <div class="control-group">
            <label class="control-label"><s>*</s>发票抬头：</label>
            <div class="controls">
                <input name="kh_name" type="text" value="<?php echo (empty($response['app_scene'])) ? $response['data']['kh_name'] : $response['kh_name']; ?>"class="input-large" data-rules="{required : true}">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><s>*</s>发票类型：</label>
            <div class="controls" data-rules="{checkRange:1}" data-messages="{checkRange:'至少选择一项！'}">
                <label class="radio" for=""><input type="radio" name="receipt_type" value="2" checked="checked" id="special">增值税专用发票<img src="assets/img/tip.png" id="specialimg"></label>&nbsp;&nbsp;&nbsp;
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">关联订单：</label>
            <div class="controls">
                <input name="pro_num" type="text" id="pro_num" class="input-large" value="">
                <strong>若关联了订单，则发票金额等于订单金额</strong>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><s>*</s>发票金额：</label>
            <div class="controls">
                <input name="receipt_money" type="text" id="receipt_money" class="input-large" data-rules="{required : true}">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><s>*</s>税务登记证号：</label>
            <div class="controls">
                <input name="tax_code" type="text" class="input-large" data-rules="{required : true}">
                <strong>请与贵公司财务人员核实并填写准确的税务登记证号，以免影响您发票后续的使用</strong>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><s>*</s>开户行：</label>
            <div class="controls">
                <input name="bank_name" type="text" class="input-large" data-rules="{required : true}">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><s>*</s>开户账号：</label>
            <div class="controls">
                <input name="bank_account" type="text" class="input-large" data-rules="{required : true}">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">注册地址：</label>
            <div class="controls">
                <input name="register_addr" type="text" class="input-large">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">注册固定电话：</label>
            <div class="controls">
                <input name="register_tel" type="text" class="input-large">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><s>*</s>收票人姓名：</label>
            <div class="controls">
                <input name="taker_name" type="text" class="input-large" data-rules="{required : true}">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><s>*</s>收票人手机：</label>
            <div class="controls">
                <input name="taker_phone" type="text" class="input-large" data-rules="{required : true}">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><s>*</s>收票人省市：</label>
            <div class="span10 controls" >
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
        <div class="control-group">
            <label class="control-label"><s>*</s>收票人地址：</label>
            <div class="controls">
                <input type="text" name="taker_addr" class="input-large" data-rules="{required : true}">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">收票人邮编：</label>
            <div class="controls">
                <input type="text" name="taker_zipcode" class="input-large">
            </div>
        </div>
        <div class="row actions-bar">
            <div class="form-actions span13 offset3">
                <button type="submit" class="button button-primary">保存</button>
                <button type="reset" class="button">重置</button>
            </div>
        </div>
    </form>
</div>
</div>
<button class="button pos">返回</button>
<br><br>
<div class="order_wrap">
    <div class="order_bottom">
        <p><span>百胜官网：www.baison.com.cn</span><span>400-680-9510</span><span>地址：上海市浦东新区峨山路91弄100号陆家嘴软件园2号楼5楼（200127）</span></p>
    </div>
</div>
<script type="text/javascript">
    $(".pos").click(function(){
       location.href="?app_act=mycenter/myself/order_info";
    });
    var scene = '<?php echo $response['app_scene']; ?>';
    var receipt_type = '<?php echo $response['receipt_type'] ?>';
    $(function () {
        if (scene == 'view') {
            //$(".upload1,.imgalert,.actions-bar").css("display", "none");
            $("input,select").attr("disabled", true);
        }
//        if (scene == 'view' || scene == 'edit') {
//            $(".btn1").css("display", "inline-block");
//            if (receipt_type == 1) {
//                $('#normal').attr("checked", "checked");
//                $('#special').removeAttr("checked");
//            } else {
//                $('#normal').removeAttr("checked");
//                $('#special').attr("checked","checked");
//            }
//          }

        var addr_row = {
            'province': "<?php echo!empty($response['province']) ? $response['province'] : ''; ?>",
            'city': "<?php echo!empty($response['city']) ? $response['city'] : ''; ?>",
            'district': "<?php echo!empty($response['district']) ? $response['district'] : ''; ?>",
            'street': "<?php echo!empty($response['street']) ? $response['street'] : ''; ?>"
        };
        op_area(addr_row);
        $("#J_Form input[name='pro_num']").attr('value', "<?php echo !empty($response['pro_num']) ? $response['pro_num'] : ''; ?>");
        $("#J_Form input[name='receipt_money']").attr('value', "<?php echo !empty($response['receipt_money']) ? $response['receipt_money'] : ''; ?>");
        $("#J_Form input[name='tax_code']").attr('value', "<?php echo !empty($response['tax_code']) ? $response['tax_code'] : ''; ?>");
        $("#J_Form input[name='bank_name']").attr('value', "<?php echo !empty($response['bank_name']) ? $response['bank_name'] : ''; ?>");
        $("#J_Form input[name='bank_account']").attr('value', "<?php echo !empty($response['bank_account']) ? $response['bank_account'] : ''; ?>");
        $("#J_Form input[name='register_addr']").attr('value', "<?php echo !empty($response['register_addr']) ? $response['register_addr'] : ''; ?>");
        $("#J_Form input[name='register_tel']").attr('value', "<?php echo !empty($response['register_tel']) ? $response['register_tel'] : ''; ?>");
        $("#J_Form input[name='taker_name']").attr('value', "<?php echo !empty($response['taker_name']) ? $response['taker_name'] : ''; ?>");
        $("#J_Form input[name='taker_phone']").attr('value', "<?php echo !empty($response['taker_phone']) ? $response['taker_phone'] : ''; ?>");
        $("#J_Form input[name='taker_addr']").attr('value', "<?php echo !empty($response['taker_addr']) ? $response['taker_addr'] : ''; ?>");
        $("#J_Form input[name='taker_zipcode']").attr('value', "<?php echo !empty($response['taker_zipcode']) ? $response['taker_zipcode'] : ''; ?>");
    });
    function op_area(info) {
        var url = '<?php echo get_app_url('mycenter/myself/get_area'); ?>';
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
        areaChange(1, 0, url, function () {
            $("#province").val(info.province);
            areaChange($("#province").val(), 1, url, function () {
                $('#city').val(info.city);
                areaChange($("#city").val(), 2, url, function () {
                    $('#district').val(info.district);
                    areaChange($("#district").val(), 3, url, function () {
                        $('#street').val(info.street);
                    });
                });
            });
        });
    }
    op_area();

    //根据订单编号获取金额
    $("#pro_num").blur(function () {
        var pro_num = $("#pro_num").val();
        if (pro_num != '') {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<?php echo get_app_url('mycenter/myself/get_order_info'); ?>',
                data: {pro_num: pro_num},
                success: function (ret) {
                    var type = ret.status === 1 ? 'success' : 'error';
                    if (type === 'success') {
                        $('#receipt_money').attr('value', ret.data.pro_sell_price);
                    }
                }
            });
        }
    });

    //图片上传
    BUI.use('bui/uploader', function (Uploader) {
        var filetype = {
            ext: ['.jpg,.png,.gif', '文件类型只能为{0}'],
            maxSize: [2048, '文件大小不能大于2M'],
            minSize: [1, '文件最小不能小于1k!'],
            max: [5, '文件最多不能超过{0}个！'],
            min: [1, '文件最少不能少于{0}个!'],
        };

        var uploader = new Uploader.Uploader({
            type: 'iframe',
            render: '#J_Uploader',
            url: '?app_act=mycenter/myself/upload_images&path=licenceimg&app_fmt=json',
            rules: filetype,
            multiple: false,
            //可以直接在这里直接设置成功的回调
            success: function (result) {
                $("#upload_url").val(result.url);
                BUI.Message.Alert("图片上传成功", "success");
                var value = $("#J_Uploader .success").attr("data-url");
                $("#licenceimg").attr('value', value);
            },
            //失败的回调
            error: function (result) {
                console.log("error" + result);
                BUI.Message.Alert("上传失败", "error");
            }
        }).render();

        var uploader = new Uploader.Uploader({
            type: 'iframe',
            render: '#J_Uploader2',
            url: '?app_act=mycenter/myself/upload_images&path=taximg&app_fmt=json',
            rules: filetype,
            multiple: false,
            //可以直接在这里直接设置成功的回调
            success: function (result) {
                $("#upload_url").val(result.url);
                BUI.Message.Alert("图片上传成功", "success");
                var value = $("#J_Uploader2 .success").attr("data-url");
                $("#taximg").attr('value', value);
            },
            //失败的回调
            error: function (result) {
                console.log("error" + result);
                BUI.Message.Alert("上传失败", "error");
            }
        }).render();

        var uploader = new Uploader.Uploader({
            type: 'iframe',
            render: '#J_Uploader3',
            url: '?app_act=mycenter/myself/upload_images&path=qualificationimg&app_fmt=json',
            rules: filetype,
            multiple: false,
            //可以直接在这里直接设置成功的回调
            success: function (result) {
                $("#upload_url").val(result.url);
                BUI.Message.Alert("图片上传成功", "success");
                var value = $("#J_Uploader3 .success").attr("data-url");
                $("#qualificationimg").attr('value', value);
            },
            //失败的回调
            error: function (result) {
                console.log("error" + result);
                BUI.Message.Alert("上传失败", "error");
            }
        }).render();
    });

    //发票小提示
    BUI.use('bui/tooltip', function (Tooltip) {
//        var t1 = new Tooltip.Tip({
//            trigger: '#normalimg',
//            alignType: 'right',
//            offset: 10,
//            title: '增值税普通发票开给小规模纳税人或者开票资料不齐全的购买人，购买人取得后不可以进行进项税额抵扣。<br>若您还有疑问，建议联系贵司财务确认后再提交开票需求。',
//            elCls: 'tips tips-no-icon',
//            titleTpl: '<div class="tips-content">{title}</div>'
//        });
//        t1.render();
        var t2 = new Tooltip.Tip({
            trigger: '#specialimg',
            alignType: 'right',
            offset: 10,
            title: '增值税专用发票开给一般纳税人，申请时需要提供公司名称、税号、地址电话、开户行名称及账号，一般纳税人批复，购买人取得后可以进行进项税额抵扣。<br>若您还有疑问，建议联系贵司财务确认后再提交开票需求。',
            elCls: 'tips tips-no-icon',
            titleTpl: '<div class="tips-content">{title}</div>'
        });
        t2.render();
    });

    //复印件图片展示
    BUI.use('bui/overlay', function (Overlay) {
        var dialog = new Overlay.Dialog({
            title: '查看图片',
            width: 900,
            height: 600,
            mask: true,
            buttons: [],
            bodyContent: "<img src='licenceimg/<?php echo $response['kh_licence_img']; ?>' width=870 height=500></img>"
        });
        $('#showlicenceimg').click(function () {
            dialog.show();
        });

        var dialog1 = new Overlay.Dialog({
            title: '查看图片',
            width: 900,
            height: 600,
            mask: true,
            buttons: [],
            bodyContent: "<img src='taximg/<?php echo $response['kh_tax_img']; ?>' width=870 height=500></img>"
        });
        $('#showtaximg').click(function () {
            dialog1.show();
        });

        var dialog2 = new Overlay.Dialog({
            title: '查看图片',
            width: 900,
            height: 600,
            mask: true,
            buttons: [],
            bodyContent: "<img src='qualificationimg/<?php echo $response['kh_qualification_img']; ?>' width=870 height=500></img>"
        });
        $('#showqulificationimg').click(function () {
            dialog2.show();
        });
    });


    BUI.use('bui/form', function (Form) {
        new Form.HForm({
            srcNode: '#J_Form',
            submitType: 'ajax',
            callback: function (data) {
                if (data.status == 1) {
                    BUI.Message.Alert(data.message, 'success');
                    location.href = '?app_act=mycenter/myself/receipt_info';
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }
        }).render();
    });
</script>

