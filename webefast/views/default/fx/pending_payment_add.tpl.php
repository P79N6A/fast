<?php require_lib('util/oms_util', true); ?>
<?php echo load_js("baison.js,record_table.js", true); ?>
<style>
    .panel-body{ padding:0;}
    .table{ margin-bottom:0;}
    .table tr{ padding:5px 0;}
    .table th, .table td{ border:1px solid #dddddd; padding:3px 0; vertical-align:middle;}
    .table th{ width:8.3%; text-align:center;}
    .table td{ width:23%; padding:0 1%;}

    .panel .payment_info{border:1px solid #dddddd; padding:10px;}
    .panel .icon-plus{margin-right: 5px;}

    .prettyprint {
        padding: 15px 50px;
        background-color: #f7f7f9;
        border: 1px solid #e1e1e8;
        margin-top: 8px;
    }
    .txt{margin-left: 10px;}
    .tag{cursor: pointer;}
    .panel .row{margin-bottom: 5px;}
    .panel .input-normal{width: 145px;}
</style>

<?php
render_control('PageHead', 'head1', array('title' => '添加收款记录',
    'links' => array(),
    'ref_table' => 'table'
));
?>

<div class="panel record_table" id="panel_html">
</div>
<div class="panel">
    <div class="panel-header" >
        <h3 class="">收款信息<i class="icon-folder-open toggle"></i></h3>
    </div>
    <div class="panel-body payment_info">
        <form id="J_Form" class="form-horizontal"  action="?app_act=fx/pending_payment/do_add" method="post">
            <input type="hidden" name="custom_code" id="custom_code" value="<?php echo $response['data']['custom_code'] ?>">
            <input type="hidden" name="record_code" id="record_code" value="<?php echo $response['data']['record_code'] ?>">
            <div>
                <div class="panel-header">
                    <span id="online" class="label label-info toggle tag" style="font-size: 1.05em"><i class="icon-plus"></i>使用资金账户支付</span>
                </div>
                <div class="panel-body prettyprint">
                    <div class="row">
                        <div class="control-group span8">
                            <label class="control-label">预存款账户：</label>
                            <div class="controls">
                                <input id="online_yck_money" name="online_yck_money" data-tip='{"text":"付款金额"}' type="text" data-rules="{required:true,regexp:/^([1-9]\d*|0)(\.\d*[1-9])?$/}" data-messages="{regexp:'请输入大于0的数字'}" class="input-normal control-text">
                            </div>
                        </div>
                        <div class="control-group span9" style="line-height: 26px;">
                            <label class="control-label">账户余额：</label>
                            <div class="controls">
                                <label id="yck_balance"><?php echo $response['custom']['yck_balance']; ?></label>
                                <span class="icon-refresh" style="margin-left: 10px;cursor: pointer;" id="refresh_balance"></span>
                                <span id="low_money" class="label label-important" style="margin-left: 20px;display:none;font-size: 0.85em;">余额不足</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="margin-top: 20px;">
                <div class="panel-header">
                    <span id="offline" class="label label-info toggle tag" style="font-size: 1.05em"><i class="icon-plus"></i>分销商已线下支付</span>
                </div>
                <div class="panel-body prettyprint">
                    <div style="width:50%;float: left">
                        <div class="row">
                            <div class="control-group span8">
                                <label class="control-label">收款日期：</label>
                                <div class="controls">
                                    <input type="text" name="offline_pay_time" id="offline_pay_time" class="calendar calendar-time" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="control-group span8">
                                <label class="control-label">收款金额：</label>
                                <div class="controls">
                                    <input name="offline_money" id="offline_money" type="text" class="input-normal control-text">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="control-group  span8">
                                <label class="control-label">&nbsp;</label>
                                <div class="controls bui-form-field-checklist" data-items="{'1':'记录收款账号'}" style="cursor: pointer;">
                                    <input id="is_record_bank" type="hidden" value="">
                                </div>
                            </div>
                        </div>
                        <div class="row account">
                            <div class="control-group span8">
                                <label class="control-label">账户名称：</label>
                                <div class="controls">
                                    <input id="account_name" type="text" class="input-normal control-text" disabled="disabled">
                                    <input type="hidden" name="offline_account_code" id="account_code">
                                </div>
                            </div>
                        </div>
                        <div class="row account">
                            <div class="control-group span8">
                                <label class="control-label">开户银行：</label>
                                <div class="controls">
                                    <input id="account_bank" type="text" class="input-normal control-text" disabled="disabled">
                                </div>
                            </div>
                        </div>
                        <div class="row account">
                            <div class="control-group span8">
                                <label class="control-label">银行账号：</label>
                                <div class="controls">
                                    <input id="bank_code" type="text" class="input-normal control-text" disabled="disabled">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="control-group span11">
                                <label class="control-label">备注：</label>
                                <div class="controls">
                                    <textarea name="offline_remark" data-tip='{"text":"请填写备注"}' class="input-large" type="text"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="float: right;margin-right: 50px;">
                        <div class="control-group span11">
                            <label class="control-label">附件：</label>
                            <div class="controls">
                                <div class="upload1">
                                    <div class="row form-actions actions-bar">
                                        <div style="float: left;"><span id="J_Uploader" style="display: inline-block;"></span></div>
                                        <input type="hidden" name="img_url" id="img_url" value="<?php echo $response['data']['goods_img']; ?>" >
                                        <input type="hidden" name="thumb_img_url" id="thumb_img_url" value="<?php echo $response['data']['goods_thumb_img']; ?>" >
                                    </div>
                                </div>
                                <div class="tips tips-small tips-info">
                                    <span class="x-icon x-icon-small x-icon-info"><i class="icon icon-white icon-info"></i></span>
                                    <div class="tips-content">建议上传订单付款凭证，如汇款单等等；附件支持jpg\png\gif格式，大小不超过2M</div>
                                </div>
                            </div>
                        </div>
                    </div>
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
<?php echo load_js('comm_util.js') ?>
<script type="text/javascript">
    var pending_money = <?php echo $response['data']['pending_money'] ?>;
    var yck_balance = <?php echo $response['custom']['yck_balance']; ?>;
    var custom_code = '<?php echo $response['data']['custom_code']; ?>';
    $(function () {
        //面板展开和隐藏
        $('.toggle').click(function () {
            $(this).parents('.panel-header').siblings('.panel-body').slideToggle('fast');
            return false;
        });
        $('.account').hide();
        setDate();
        setMoney();
        checkBalance();

        BUI.use('bui/form', function (Form) {
            new Form.HForm({
                srcNode: '#J_Form',
                submitType: 'ajax',
                defaultChildCfg: {
                    validEvent: 'blur' //移除时进行验证
                },
                callback: function (ret) {
                    var _type = ret.status == 1 ? 'success' : 'error';
                    BUI.Message.Show({
                        msg: ret.message,
                        icon: _type,
                        buttons: [],
                        autoHide: true
                    });
                    if (_type == 'success') {
                        ui_closeTabPage('<?php echo $request['ES_frmId'] ?>');
                    }
                }
            }).render();
        });

        var dataRecord = [
            {'title': '单据编号', 'type': 'input', 'name': 'record_code', 'value': '<?php echo $response['data']['record_code'] ?>'},
            {'title': '分销商名称', 'type': 'input', 'name': 'record_date', 'value': '<?php echo $response['data']['custom_name'] ?>'},
            {'title': '', 'type': '', 'name': '', 'value': ''},
            {'title': '待付金额', 'type': 'input', 'name': 'create_time', 'value': '<?php echo $response['data']['pending_money'] ?>'},
            {'title': '已付金额', 'type': 'input', 'name': 'record_type_name', 'value': '<?php echo $response['data']['pay_money'] ?>'},
            {'title': '单据金额', 'type': 'input', 'name': 'shop_code_name', 'value': '<?php echo $response['data']['money'] ?>'}
        ];
        var r = new record_table();
        r.init({
            "id": "panel_html",
            "data": dataRecord,
            "is_edit": false,
            "title": "单据信息",
            "edit_url": ""
        });

        $("#online_yck_money").keyup(function () {
            if (yck_balance <= 0) {
                $(this).val('0');
                return;
            }
            if ($(this).val() > yck_balance) {
                $(this).val(yck_balance);
            }
            if ($(this).val() > pending_money) {
                $(this).val(pending_money);
            }
            var offline_money = pending_money - $(this).val();
            $("#offline_money").val(offline_money);
        });

        $("#refresh_balance").click(function () {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<?php echo get_app_url('fx/account/get_balance'); ?>',
                data: {custom_code: custom_code},
                success: function (ret) {
                    yck_balance = ret.yck_balance;
                    $("#yck_balance").text(ret.yck_balance);
                }
            });
        });

        $(".bui-list-item").click(function () {
            var is_record = $("#is_record_bank").val();
            if (is_record == 1) {
                $(".account input").val('');
                $(".account").hide();
                return;
            }
            _select_account();
        });

        BUI.use('bui/uploader', function (Uploader) {
            var url = "<?php echo $response['upload_path']; ?>";
            var filetype = {
                ext: ['.jpg,.png,.gif', '文件类型只能为{0}'],
                maxSize: [2048, '文件大小不能大于2M'],
                //minSize: [1, '文件最小不能小于1k!'],
                max: [5, '文件最多不能超过{0}个！'],
                min: [1, '文件最少不能少于{0}个!'],
            };

            var uploader = new Uploader.Uploader({
                type: 'iframe',
                render: '#J_Uploader',
                url: url,
                rules: filetype,
                multiple: false,
                //可以直接在这里直接设置成功的回调
                success: function (result) {
                    $("#img_url").val(result.url);
                    $("#thumb_img_url").val(result.thumb_url);
                    BUI.Message.Alert("图片上传成功", "success");
                    $('#img_show').html('<img src="' + result.thumb_url + '"  />');
                },
                //失败的回调
                error: function (result) {
                    console.log("error" + result);
                    BUI.Message.Alert("上传失败", "error");
                }
            }).render();
        });
    });
    function setDate() {
        var dd = new Date();
        dd.setDate(dd.getDate() - 1);
        $('#offline_pay_time').val(date('Y-m-d H:i:s', dd));
    }

    function setMoney() {
        if (pending_money <= yck_balance) {
            $('#online_yck_money').val(pending_money);
            $('#offline_money').val('0');
//            $("#offline.toggle").click();
        } else {
            var off_money = pending_money - yck_balance;
            $('#online_yck_money').val(yck_balance);
            $("#offline_money").val(off_money);
        }
    }

    //检查余额是否充足
    function checkBalance() {
        if (yck_balance <= 0) {
            $("#low_money").show();
        } else {
            $("#low_money").hide();
        }
    }

    function afresh_set_money() {

    }

    function _select_account() {
        var param = {};
        if (typeof (top.dialog) != 'undefined') {
            top.dialog.remove(true);
        }
        var url = '?app_act=base/paymentaccount/select_account';
        var buttons = [
            {
                text: '确定',
                elCls: 'button button-primary',
                handler: function () {
                    var data = top.SelectoGrid.getSelection();
                    if (data.length > 0) {
                        $.each(data[0], function (key, val) {
                            var obj = $("#" + key);
                            if (obj != 'undefined') {
                                obj.val(val);
                            }
                        });
                        $('.account').show();
                    }
                    this.close();
                }
            }, {
                text: '取消',
                elCls: 'button',
                handler: function () {
                    this.close();
                }
            }
        ];
        top.BUI.use('bui/overlay', function (Overlay) {
            top.dialog = new Overlay.Dialog({
                title: '选择收款账户',
                width: '700',
                height: '550',
                loader: {
                    url: url,
                    autoLoad: true, //不自动加载
                    params: param, //附加的参数
                    lazyLoad: false, //不延迟加载
                    dataType: 'text'   //加载的数据类型
                },
                align: {
                    //node : '#t1',//对齐的节点
                    points: ['tc', 'tc'], //对齐参考：http://dxq613.github.io/#positon
                    offset: [0, 20] //偏移
                },
                mask: true,
                buttons: buttons
            });
            top.dialog.on('closed', function (ev) {

            });
            top.dialog.show();
        });
    }
</script>


