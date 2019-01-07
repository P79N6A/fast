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
    .page_container {
        padding: 0 1% 10px;
    }
</style>
<form  id="form2" class="form-horizontal" action="?app_act=base/custom/<?php echo 'do_' . $response['app_scene'] . '_addr'; ?>" method="post">
    <input type="hidden" id="custom_id" name="custom_id" value="<?php echo $request['custom_id']; ?>"/>
    <input type="hidden" name="custom_address_id" value="<?php echo $request['addr_id']; ?>"/>
    <table>
        <tr><td style="color:red;">新增地址&nbsp</td><td>&nbsp;&nbsp手机、固定电话选填一项</td></tr>
        <tr>
            <td>所在地区 &nbsp</td>
            <td >
                <select name="country" id="country" style="width:100px;" data-rules="{required: true}">
                    <option value ="">国家</option>
                    <?php
                    require_lib("util/oms_util");
                    $list = load_model('base/TaobaoAreaModel')->get_area('0');
                    foreach ($list as $k => $v) {
                        ?>

                        <option value="<?php echo $v['id'] ?>"><?php echo $v['name'] ?></option>
                    <?php } ?>
                </select>
                <select name="province" id="province" style="width:100px;" data-rules="{required: true}">
                    <option>省</option>
                </select>
                <select name="city" id="city" style="width:100px;">
                    <option>市</option>
                </select>
                <select name="district" id="district" style="width:100px;">
                    <option>区</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>详细地址&nbsp</td>
            <td>
                <input type="text" name="address" class="input-normal" value=""  data-rules="{required: true}"/><b style="color:red"> *</b>
            </td>
        </tr>
        <tr>
            <td>邮编</td>
            <td>
                <input type="text" name="zipcode" class="input-normal" value=""/>
            </td>
        </tr>

        <tr>
            <td>收货人</td>
            <td>
                <input type="text" name="name" class="input-normal" value=""  data-rules="{required: true}"/><b style="color:red"> *</b>
            </td>
        </tr>

        <tr>
            <td>手机号</td>
            <td>
                <input type="text" name="tel"  id="tel" class="input-normal"   value="" />
            </td>
        </tr>
        <tr>
            <td>联系电话</td>
            <td>
                <input type="text" name="home_tel"  id="home_tel" class="input-normal" value=""/>
            </td>
        </tr>

        <tr>
            <?php if ($response['app_scene'] == 'add') { ?>
                <td>默认收货地址</td>
                <td>
                    <input type="checkbox" name="is_default"  class="input-normal checkbox" value="1" />
                </td>
            <?php } ?>
        </tr>


    </table>
    <div class="row form-actions actions-bar">
        <div class="span13 offset3 ">
            <button type="submit" class="button button-primary" id="submit2">提交</button>
            <button type="reset" class="button " id="reset">重置</button>
        </div>
    </div>
</form>

<?php echo load_js('comm_util.js') ?>
<script>
    var scene = '<?php echo $response['app_scene']; ?>';
    var url = '<?php echo get_app_url('base/store/get_area'); ?>';
    BUI.use('bui/form', function (Form) {
        var form1 = new Form.HForm({
            srcNode: '#form2',
            submitType: 'ajax',
            callback: function (data) {
                if (data.status == 1) {
                    BUI.Message.Alert(data.message, 'success');
                    parent._action();
                    ui_closePopWindow('<?php echo $request['ES_frmId'] ?>');
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }
        }).render();

        form1.on('beforesubmit', function () {
            if ($('#home_tel').val() == "" && $('#tel').val() == "")
            {
                BUI.Message.Alert("手机、固定电话需选填一项");
                return false;
            }
        });
    });
    $(function () {
        $(".checkbox").css("width", "35px");
        if (scene == 'edit') {
            $("#country").val('<?php echo $response['data']['country']; ?>');
            areaChange($("#country").val(), 0, url, function () {
                $("#province").val('<?php echo $response['data']['province']; ?>');
                areaChange($("#province").val(), 1, url, function () {
                    $('#city').val('<?php echo $response['data']['city']; ?>');
                    areaChange($("#city").val(), 2, url, function () {
                        $('#district').val('<?php echo $response['data']['district']; ?>');
                        areaChange($("#district").val(), 3, url);
                    });
                });
            });
        }
        op_area();
        if(scene == 'edit') {
            $(':input[name="address"]').val('<?php echo $response['data']['address']; ?>');
            $(':input[name="zipcode"]').val('<?php echo $response['data']['zipcode']; ?>');
            $(':input[name="name"]').val('<?php echo $response['data']['name']; ?>');
            $(':input[name="home_tel"]').val('<?php echo $response['data']['home_tel']; ?>');
            $(':input[name="tel"]').val('<?php echo $response['data']['tel']; ?>');
        }
        
    })
    function op_area() {
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
    }
</script>