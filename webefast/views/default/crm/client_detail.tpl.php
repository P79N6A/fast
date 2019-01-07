<style>
    .bui-tab-item{
        position: relative;
    }
    .bui-tab-item .bui-tab-item-text{
        padding-right: 25px;
    }
    .addr_tbl{border-collapse:collapse;border:1px #ccc solid;}
    .addr_tbl th,.addr_tbl td{padding:6px;border-collapse:collapse;border:1px #ccc solid;}
    .addr_tbl th{background: #eee;}
    tr{height:35px; }
</style>
<div id="container">
    <div id="p1">
        <form  class="form-horizontal" id="form1" action="?app_act=crm/client/do_<?php echo $app['scene'] ?>" method="post">
            <div class="row">
                <div class="control-group span15">
                    <label class="control-label span3">会员代码：</label>
                    <div class="span10 controls" >
                        <input type="text" name="client_code" id="client_code" class="input-normal" value="" disabled="disabled"/>
                        <input type="hidden" name="client_code"  value=""/>
                        <b style="color:red"> *</b>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span15">
                    <label class="control-label span3">会员名称：</label>
                    <div class="span10 controls" >
                        <input type="text" name="client_name" id="client_name" class="input-normal" value=""  data-rules="{required: true}"/>
                        <b style="color:red"> *</b>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span15">
                    <label class="control-label span3">手机：</label>
                    <div class="span10 controls" >
                        <input type="text" name="client_tel" id="client_tel" class="input-normal" value=""/>
                        <b style="color:red"> *</b>
                        <div class="alertmsg" style="color:red; font-size: 13px;"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span15">
                    <label class="control-label span3">性别：</label>
                    <div class="span10 controls">
                        <select name="client_sex" style="width:146px;">
                            <option value="0">保密</option>
                            <option value="1">男</option>
                            <option value="2">女</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span15">
                    <label class="control-label span3">生日：</label>
                    <div class="span10 controls">
                        <input type="text" name="birthday" id="birthday" style="width:140px;" class="input-normal calendar" value=""  />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span15">
                    <label class="control-label span3">E-mail：</label>
                    <div class="span10 controls" >
                        <input type="text" name="email" id="email" class="input-normal" value=""  />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span15">
                    <label class="control-label span3">所在区域：</label>
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
                        <select name="street" id="street" style="width:100px;">
                            <option>街道</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span15">
                    <label class="control-label span3">详细地址：</label>
                    <div class="span10 controls" >
                        <input type="text" name="address" class="input-normal" value="" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span15">
                    <label class="control-label span3">备注：</label>
                    <div class="span10 controls" >
                        <textarea class="input-normal" name="remark"></textarea>
                    </div>
                </div>
            </div>
            <div class="row form-actions actions-bar">
                <div class="span13 offset3 ">
                    <button type="submit" class="button button-primary" id="submit">提交</button>
                    <button type="reset" class="button " id="reset">重置</button>
                </div>
            </div>
        </form>
    </div>
    <?php echo load_js('comm_util.js'); ?>
    <script type="text/javascript">
        var scene = '<?php echo $app['scene']; ?>';
        $(function () {
            if (scene == 'edit') {
                var addr_row = {
                    'province': "<?php echo!empty($response['data']['province']) ? $response['data']['province'] : ''; ?>",
                    'city': "<?php echo!empty($response['data']['city']) ? $response['data']['city'] : ''; ?>",
                    'district': "<?php echo!empty($response['data']['district']) ? $response['data']['district'] : ''; ?>",
                    'street': "<?php echo!empty($response['data']['street']) ? $response['data']['street'] : ''; ?>"
                };
                op_area(addr_row);

                var obj = <?php echo json_encode($response['data']); ?>;
                $.each(obj, function (key, val) {
                    if (key == 'client_sex') {
                        var obj = $("#form1 select[name='" + key + "']");
                        if (obj != 'undefined') {
                            obj.val(val);
                        }
                        return true;
                    }
                    if (key == 'remark') {
                        var obj = $("#form1 textarea[name='" + key + "']");
                        if (obj != 'undefined') {
                            obj.val(val);
                        }
                        return true;
                    }
                    var obj = $("#form1 input[name='" + key + "']");
                    if (obj != 'undefined') {
                        obj.val(val);
                    }
                });
            }
            if (scene == 'add') {
                $("#form1 input[name='client_code']").val("<?php echo $response['data']['shop_code']; ?>");
                $("#form1 input[name='client_tel']").val("<?php echo $response['data']['tel'] ; ?>");
            }

            $("#client_tel").blur(function () {
                var tel = ($("#client_tel").val() == null) ? '<?php echo $response['detail']['client_tel'] ?>' : $("#client_tel").val();
                var valid = RegExp(/^(0|86|17951)?(13[0-9]|15[012356789]|18[0-9]|17[0-9]|14[57])[0-9]{8}$/).test(tel);
                if (!valid) {
                    $("#client_tel").attr("style", "border: 1px dotted #F00;");
                    $(".alertmsg").show();
                    $(".alertmsg").html("<span class='x-icon x-icon-mini x-icon-error'>!</span>请输入正确的手机号");
                }
            });
            $("#client_tel").focus(function () {
                $("#client_tel").removeAttr('style');
                $(".alertmsg").hide();
            });
        });


        function op_area(info) {
            var url = '<?php echo get_app_url('base/shop_entity/get_area'); ?>';
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
        if (scene == 'add') {
            op_area('');
        }

        BUI.use('bui/calendar', function (Calendar) {
            var datepicker = new Calendar.DatePicker({
                trigger: '.calendar',
                autoRender: true
            });
        });
        BUI.use('bui/form', function (Form) {
            var form1 = new Form.HForm({
                srcNode: '#form1',
                submitType: 'ajax',
                callback: function (data) {
                    if (data.status == 1) {
                        BUI.Message.Alert('保存成功', 'success');
                        ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
                        tableStore.load();
                    } else {
                        BUI.Message.Alert(data.message, 'error');
                    }
                }
            }).render();
        });
    </script>
</div>
