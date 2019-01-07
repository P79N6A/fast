<style>
    #TabPage1Contents{padding-top: 5px;margin-bottom: -20px;}
    #TabPage1Contents .row{margin-bottom: 5px;}
    .form-horizontal .control-label {
        text-align: right;
        line-height: 30px;
        width: 90px;
    }
    .form-horizontal .well{
        padding: 10px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => $response['title'],
    'links' => array(),
    'ref_table' => 'table'
));
?>
<?php
$tabs = array(
    array('title' => '通用信息', 'active' => true, 'id' => 'tabs_base'),
);
$button = array();
if ($app['scene'] != 'add') {
    $tabs[] = array('title' => '详细信息', 'active' => false, 'id' => 'tabs_send');
}
render_control('TabPage', 'TabPage1', array(
    'tabs' => $tabs,
    'for' => 'TabPage1Contents'
));
?>
<div id="TabPage1Contents">
<form id="form1" class="form-horizontal"  action="?app_act=base/supplier/do_<?php echo $app['scene'] ?>&app_fmt=json&type=base" method="post">
    <input name="supplier_id" type="hidden" >
        <div>
            <div class="well">
                <div class="row">
                    <div class="control-group"  style="width:60%">
                        <label class="control-label"><s>*</s>供应商代码：</label>
                        <div class="controls">
                            <?php if ($app['scene'] == 'add'): ?>
                            <input name="supplier_code" data-tip='{"text":"供应商代码"}' type="text" data-rules="{required:true}" class="input-normal control-text">
                                <span class="auxiliary-text">一旦保存不能修改</span>
                            <?php endif; ?>
                            <?php if ($app['scene'] == 'edit'): ?>
                                <?php echo $response['supplier_code']?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="control-group span8">
                        <label class="control-label"><s>*</s>供应商名称：</label>
                        <div class="controls">
                            <input type="text" name="supplier_name" class="input-normal control-text" value="" data-rules="{required:true}" data-tip='{"text":"供应商名称"}' />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="control-group span8">
                        <label class="control-label">折扣：</label>
                        <div class="controls">
                            <input type="text" name="rebate" class="input-normal control-text" value="1.00" data-tip='{"text":"折扣"}' />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row form-actions actions-bar">
            <div class="span13 offset3 ">
                <button type="submit" class="button button-primary">提交</button>
                <button type="reset" class="button ">重置</button>
            </div>
        </div>
</form>
<?php if ($app['scene'] != 'add'): ?>
<form id="form2" class="form-horizontal"  action="?app_act=base/supplier/do_<?php echo $app['scene'] ?>&app_fmt=json&type=send" method="post">
    <input name="supplier_id" type="hidden" >
    <div id="TabPage1Contents">
            <div>
                <div class="well">
                    <div class="row">
                        <div class="control-group">
                            <label class="control-label"><s>*</s>联系人：</label>
                            <div class="controls">
                                <input id="online_yck_money" name="contact_person" data-tip='{"text":"联系人"}' type="text" data-rules="{required:true}" class="input-normal control-text">
                            </div>
                        </div>
                        <div class="control-group span8">
                            <label class="control-label"><s>*</s>手机号：</label>
                            <div class="controls">
                                <input type="text" name="mobile" class="input-normal control-text" value="" data-rules="{required:true}" data-tip='{"text":"手机号码"}' />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="control-group span8">
                            <label class="control-label">联系电话：</label>
                            <div class="controls">
                                <input type="text" name="tel" class="input-normal" value="" data-tip='{"text":"电话号码"}' />
                            </div>
                        </div>
                        <div class="control-group span8">
                            <label class="control-label">传真：</label>
                            <div class="controls">
                                <input type="text" name="fax" class="input-normal control-text" value="" data-tip='{"text":"传真号码"}' />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="control-group span12" style="width:70%">
                            <label class="control-label"><s>*</s>联系地址：</label>
                            <div class="controls">
                                <select name="country" id="country" style="width:100px;" data-rules="{required: true}" data-messages="{required:'请选择国家'}"></select>
                                <select name="province" id="province" style="width:100px;" data-rules="{required: true}" data-messages="{required:'请选择省份'}"></select>
                                <select name="city" id="city" style="width:100px;" data-rules="{required: true}" data-messages="{required:'请选择城市'}"></select>
                                <select name="district" id="district" style="width:100px;"></select>
                                <select name="street" id="street" style="width:100px;" ></select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="control-group span8" style="width:50%">
                            <label class="control-label"><s>*</s>详细地址：</label>
                            <div class="controls">
                                <input type="text" name="address" class="input-large control-text" value=""  data-rules="{required: true}" data-tip='{"text":"不含省市区"}' />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="control-group span8" style="width:50%">
                            <label class="control-label">邮编：</label>
                            <div class="controls">
                                <input type="text" name="zipcode" class="input-large control-text" value="" data-tip='{"text":"邮政编码"}' />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="control-group span8" style="width:50%">
                            <label class="control-label">电子邮件：</label>
                            <div class="controls">
                                <input type="text" name="email" class="input-large control-text" value="" data-tip='{"text":"电子邮件"}' />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="control-group span8" style="width:50%">
                            <label class="control-label">公司网址：</label>
                            <div class="controls">
                                <input type="text" name="website" class="input-large control-text" value="" data-tip='{"text":"公司网址"}' />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="control-group span8" style="width:50%">
                            <label class="control-label">备注：</label>
                            <div class="controls">
                                <textarea name="remark" class="input-large" data-tip='{"text":"备注"}' ></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <div class="row form-actions actions-bar">
            <div class="span13 offset3 ">
                <button type="submit" class="button button-primary">提交</button>
                <button type="reset" class="button ">重置</button>
            </div>
        </div>
    </div>
</form>
</div>
<?php endif; ?>
<div class="tips tips-small tips-warning span10">
    <span class="x-icon x-icon-small x-icon-error"><i class="icon icon-white icon-bell"></i></span>
    <div class="tips-content">温馨提示：若对接<b>WMS</b>系统，请完善详细信息页签数据。</div>
</div>
<?php echo load_js('comm_util.js') ?>
<script type="text/javascript">
    var scene = '<?php echo $app['scene']; ?>';
    //通用信息
    BUI.use('bui/form', function (Form) {
        new Form.Form({
            srcNode: '#form1',
            submitType: 'ajax',
            callback: function (data) {
                if (data.status == 1) {
                    BUI.Message.Alert(data.message, 'success');
                    if (scene == 'add') {
                        window.location.href = "?app_act=base/supplier/detail&app_scene=edit&_id=" + data.data + "&ES_frmId=" + '<?php echo $request['ES_frmId'] ?>';
                    }
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }
        }).render();
    });

    //详情信息
    BUI.use('bui/form', function (Form) {
        new Form.Form({
            srcNode: '#form2',
            submitType: 'ajax',
            callback: function (data) {
                if (data.status == 1) {
                    BUI.Message.Alert(data.message, 'success');
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }
        }).render();
    });


    $(function () {
        if (scene == 'edit') {
            var data = <?php echo $response['data']; ?>;
            $.each(data, function (key, val) {
                var obj = $("#form1 input[name='" + key + "']");
                if (obj != 'undefined' && val != '') {
                    obj.val(val);
                    obj.click();
                }
                //详细
                var obj_detail = $("#form2 input[name='" + key + "']");
                if (key == 'remark') {
                    obj_detail = $("#form2 textarea[name='" + key + "']");
                    if (obj_detail != 'undefined' && val != '') {
                        obj_detail.val(val);
                        obj_detail.click();
                    }
                    return true;
                }

                if (obj_detail != 'undefined' && val != '') {
                    obj_detail.val(val);
                    obj_detail.click();
                }
            });
            op_area(<?php echo $response['area_info'] ?>);
        } else {
            op_area();
        }
    });

    function op_area(info) {
        var url = '<?php echo get_app_url('base/shop_entity/get_area'); ?>';
        $('#country').change(function () {
            var parent_id = $(this).val();
            if(parent_id==250){
                var obj=document.getElementById('province');
                obj.options.length=0;
                document.getElementById("province").options.add(new Option('请选择省',''));
                document.getElementById("province").options.add(new Option('海外',250000));
                $("#province").find("option[value=250000]").attr("selected", "selected");
                document.getElementById("city").options.add(new Option('海外',25000000));
                $("#city").find("option[value=25000000]").attr("selected", "selected");
                document.getElementById("district").options.add(new Option('请选择区/县',''));
                $("#district").find("option[value='']").attr("selected", "selected");
                document.getElementById("street").options.add(new Option('请选择街道',''));
                $("#street").find("option[value='']").attr("selected", "selected");
                return;
            }
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
        areaChange(0, -1, url, function () {
            $("#country").val(info.country);
            areaChange($("#country").val(), 0, url, function () {
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
        });
    }
</script>




