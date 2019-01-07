<?php echo load_css('bui.css', true) ?>
<?php echo load_css('dpl.css', true) ?>
<?php echo load_js('comm_util.js', true); ?>
<?php echo load_js('bui.js', true); ?>
<style>
    .clearfloat{
        clear: both;
        margin-top: 15px;        
    }
    .span10{
        width: 500px;
    }
</style>
<form id="J_Form" action="" method="post" class="form-horizontal">
    <div class="control-group clearfloat">
        <label class="control-label"><s>*</s>发票抬头：</label>
        <div class="controls">
            <input name="kh_name" type="text" value="" class="input-large" data-rules="{required : true}">
        </div>
    </div>
    <div class="control-group clearfloat">
        <label class="control-label"><s>*</s>发票类型：</label>
        <div class="controls">
            <label class="radio" for=""><input type="radio" name="receipt_type" value="2" checked="checked" id="special">增值税专用发票<img src="assets/img/tip.png"  id="specialimg"></label>&nbsp;&nbsp;&nbsp;
            <label class="radio" for=""><input type="radio" name="receipt_type" value="1" id="normal">增值税普通发票<img src="assets/img/tip.png" id="normalimg"></label>
        </div>
    </div>
    <div class="control-group clearfloat">
        <label class="control-label">关联订单：</label>
        <div class="controls">
            <input name="pro_num" type="text" id="pro_num" class="input-large" value="">
            <strong>若关联了订单，则发票金额等于订单金额</strong>
        </div>
    </div>
    <div class="control-group clearfloat">
        <label class="control-label"><s>*</s>发票金额：</label>
        <div class="controls">
            <input name="receipt_money" type="text" id="receipt_money" class="input-large">
        </div>
    </div>
    <div class="control-group clearfloat">
        <label class="control-label"><s>*</s>税务登记证号：</label>
        <div class="controls">
            <input name="tax_code" type="text" class="input-large" data-rules="{required : true}">
        </div>
    </div>
    <div class="control-group clearfloat">
        <label class="control-label"><s>*</s>开户行：</label>
        <div class="controls">
            <input name="bank_name" type="text" class="input-large" data-rules="{required : true}">
        </div>
    </div>
    <div class="control-group clearfloat">
        <label class="control-label"><s>*</s>开户账号：</label>
        <div class="controls">
            <input name="bank_account" type="text" class="input-large" data-rules="{required : true}">
        </div>
    </div>
    <div class="control-group clearfloat">
        <label class="control-label"><s>*</s>注册地址：</label>
        <div class="controls">
            <input name="register_addr" type="text" class="input-large" data-rules="{required : true}">
        </div>
    </div>
    <div class="control-group clearfloat">
        <label class="control-label"><s>*</s>注册固定电话：</label>
        <div class="controls">
            <input name="register_tel" type="text" class="input-large" data-rules="{required : true}">
        </div>
    </div>
    <div class="control-group clearfloat imgcopy">
        <label class="control-label"><s>*</s>营业执照复印件：</label>
        <a class="btn1 button-pill button-small1 button-primary1" id="showlicenceimg">查看图片</a>
    </div>
    <div class="control-group clearfloat imgcopy">
        <label class="control-label"><s>*</s>税务登记复印件：</label>
        <a class="btn1 button-pill button-small1 button-primary1" id="showtaximg">查看图片</a>
    </div>
    <div class="control-group clearfloat imgcopy">
        <div class="control-label"><s>*</s><span>资格认证复印件：</span></div>
        <a class="btn1 button-pill button-small1 button-primary1" id="showqulificationimg">查看图片</a>
    </div>
    <div class="control-group clearfloat">
        <label class="control-label"><s>*</s>收票人姓名：</label>
        <div class="controls">
            <input name="taker_name" type="text" class="input-large" data-rules="{required : true}">
        </div>
    </div>
    <div class="control-group clearfloat">
        <label class="control-label"><s>*</s>收票人手机：</label>
        <div class="controls">
            <input name="taker_phone" type="text" class="input-large" data-rules="{required : true}">
        </div>
    </div>
    <div class="control-group clearfloat">
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
    <div class="control-group clearfloat">
        <label class="control-label"><s>*</s>收票人地址：</label>
        <div class="controls">
            <input type="text" name="taker_addr" class="input-large" data-rules="{required : true}">
        </div>
    </div>
    <div class="control-group clearfloat">
        <label class="control-label"><s>*</s>收票人邮编：</label>
        <div class="controls">
            <input type="text" name="taker_zipcode" class="input-large" data-rules="{required : true}">
        </div>
    </div>
    <div class="control-group clearfloat" style="margin-bottom: 30px; "></div>
</form>

<script type="text/javascript">
    var scene = '<?php echo $response['app_scene']; ?>';
    var receipt_type = '<?php echo $response['receipt_type'] ?>';
    $(function () {
        $("input,select").attr("disabled", true);
        if (receipt_type == 1) {
            $('#normal').attr("checked", "checked");
            $('#special').removeAttr("checked");
        } else {
            $('#normal').removeAttr("checked");
            $('#special').attr("checked", "checked");
        }
        var addr_row = {
            'province': "<?php echo!empty($response['province']) ? $response['province'] : ''; ?>",
            'city': "<?php echo!empty($response['city']) ? $response['city'] : ''; ?>",
            'district': "<?php echo!empty($response['district']) ? $response['district'] : ''; ?>",
            'street': "<?php echo!empty($response['street']) ? $response['street'] : ''; ?>"
        };
        op_area(addr_row);
        $("#J_Form input[name='kh_name']").attr('value', "<?php echo!empty($response['kh_name']) ? $response['kh_name'] : ''; ?>");
        $("#J_Form input[name='pro_num']").attr('value', "<?php echo!empty($response['pro_num']) ? $response['pro_num'] : ''; ?>");
        $("#J_Form input[name='receipt_money']").attr('value', "<?php echo!empty($response['receipt_money']) ? $response['receipt_money'] : ''; ?>");
        $("#J_Form input[name='tax_code']").attr('value', "<?php echo!empty($response['tax_code']) ? $response['tax_code'] : ''; ?>");
        $("#J_Form input[name='bank_name']").attr('value', "<?php echo!empty($response['bank_name']) ? $response['bank_name'] : ''; ?>");
        $("#J_Form input[name='bank_account']").attr('value', "<?php echo!empty($response['bank_account']) ? $response['bank_account'] : ''; ?>");
        $("#J_Form input[name='register_addr']").attr('value', "<?php echo!empty($response['register_addr']) ? $response['register_addr'] : ''; ?>");
        $("#J_Form input[name='register_tel']").attr('value', "<?php echo!empty($response['register_tel']) ? $response['register_tel'] : ''; ?>");
        $("#J_Form input[name='taker_name']").attr('value', "<?php echo!empty($response['taker_name']) ? $response['taker_name'] : ''; ?>");
        $("#J_Form input[name='taker_phone']").attr('value', "<?php echo!empty($response['taker_phone']) ? $response['taker_phone'] : ''; ?>");
        $("#J_Form input[name='taker_addr']").attr('value', "<?php echo!empty($response['taker_addr']) ? $response['taker_addr'] : ''; ?>");
        $("#J_Form input[name='taker_zipcode']").attr('value', "<?php echo!empty($response['taker_zipcode']) ? $response['taker_zipcode'] : ''; ?>");
    });
    function op_area(info) {
        var url = '<?php echo get_app_url('market/receipt_management/get_area'); ?>';
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

    //发票小提示
    BUI.use('bui/tooltip', function (Tooltip) {
        var t1 = new Tooltip.Tip({
            trigger: '#normalimg',
            alignType: 'right',
            offset: 10,
            title: '增值税普通发票开给小规模纳税人或者开票资料不齐全的购买人，购买人取得后不可以进行进项税额抵扣。<br>若您还有疑问，建议联系贵司财务确认后再提交开票需求。',
            elCls: 'tips tips-no-icon',
            titleTpl: '<div class="tips-content">{title}</div>'
        });
        t1.render();
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
            width: 800,
            height: 520,
            mask: true,
            buttons: [],
            bodyContent: "<img src='http://<?php echo $_SERVER['SERVER_NAME']?>/eFast/weborder/web/licenceimg/<?php echo $response['kh_licence_img']; ?>' width=770 height=425></img>"
        });
        $('#showlicenceimg').click(function () {
            dialog.show();
        });

        var dialog1 = new Overlay.Dialog({
            title: '查看图片',
            width: 800,
            height: 520,
            mask: true,
            buttons: [],
            bodyContent: "<img src='http://<?php echo $_SERVER['SERVER_NAME']?>/eFast/weborder/web/taximg/<?php echo $response['kh_tax_img']; ?>' width=770 height=425></img>"
        });
        $('#showtaximg').click(function () {
            dialog1.show();
        });

        var dialog2 = new Overlay.Dialog({
            title: '查看图片',
            width: 800,
            height: 520,
            mask: true,
            buttons: [],
            bodyContent: "<img src='http://<?php echo $_SERVER['SERVER_NAME']?>/eFast/weborder/web/qualificationimg/<?php echo $response['kh_qualification_img']; ?>' width=770 height=425></img>"
        });
        $('#showqulificationimg').click(function () {
            dialog2.show();
        });
    });
</script>

