<style>
    .panel-body {padding:0; border:1px solid #ddd; border-top:none;}
    .panel-body table {margin: 0; }

    #panel_baseinfo input{width: 50px; text-align:center; margin-left:7px;}
    #panel_baseinfo select{width: 110px;}

    input[type="text"]{padding: 0px;height: 20px;vertical-align: bottom;}
    .display_class{display: none;}
</style>
<form id="form1" method="post" action="?app_act=sys/params/do_save" tabindex="0" style="outline: none;">
    <?php foreach ($response['data'] as $k => $v) { ?>
        <div class="panel">
            <div class="panel-header clearfix">
                <h3 class="pull-left"><?php echo $response['moban'][$k]['param_name']; ?></h3>
                <div class="pull-right"></div>
            </div>
            <div class="panel-body" id="panel_baseinfo">
                <table cellspacing="0" class="table ">
                    <tbody>
                        <?php foreach ($v as $value) { ?>
                            <tr class="<?php echo $value['param_code'] . '_ye'; ?>">
                                <td style="width:120px; padding-left:20px"><?php echo $value['param_name']; ?>：</td>
                                <?php
                                switch ($value['type']) {
                                    case "text":
                                        ?>

                                        <td style="width:100px;"><input type="text" <?php if ($value['param_code'] == 'notice_email') { ?> style="width:350px;" <?php } ?> id = "code" data-rules="{required: true}" value="<?php echo $value['value']; ?>" name="<?php echo $value['param_code']; ?>">
                                        </td>
                                        <?php
                                        break;
                                    case "radio":
                                        ?>
                                        <td style=" width:400px">
                                            <?php foreach ($value['form_desc'] as $r_k => $r_v) { ?>
                                                <input type="radio" style="width:15px; margin:0 4px;" class="<?php echo $value['param_code']; ?>" name="<?php echo $value['param_code']; ?>"
                                                       value="<?php echo $r_k; ?>" <?php if ($value['value'] == $r_k) { ?> checked="checked"   <?php } ?> /><?php echo $r_v; ?>
                                                   <?php } ?>
                                        </td>
                                        <?php
                                        break;
                                    case "select":
                                        ?>
                                        <td style=" width:700px">
                                            <?php foreach ($response['shop'] as $key => $shop_row) { ?>
                                                <div class="controls span6" >
                                                    <input class="" name="jiazhuang_shop[]" id="<?php echo $shop_row['shop_code']; ?>" value="<?php echo $shop_row['shop_code']; ?>" <?php if (in_array($shop_row['shop_code'], $response['selected_shop'])) { ?> checked <?php } ?> type="checkbox" ><?php echo $shop_row['shop_name']; ?>
                                                </div>
                                            <?php } ?>
                                        </td>
                                        <?php break; ?>
                                <?php } ?>
                                <td></td>
                            </tr>
                            <?php if ($value['memo'] <> '') { ?>
                                <tr><td colspan="4" style="padding-left:153px; color:#999;"><font color='#edb03b '>说明:</font> <?php echo $value['memo']; ?></td></tr>
                            <?php } ?>
                        <?php } ?>
                        <?php if ($response['moban'][$k]['memo'] <> '') { ?>
                            <?php if (strlen($response['moban'][$k]['memo']) > 200) { ?>
                                <tr><td colspan="4" style="padding-left:153px; color:#999;"><font color='#edb03b '>说明:</font><br><?php echo $response['moban'][$k]['memo']; ?></td></tr>
                            <?php } else { ?>
                                <tr><td colspan="4" style="padding-left:153px; color:#999;"><font color='#edb03b '>说明:</font><?php echo html_entity_decode($response['moban'][$k]['memo']); ?></td></tr>
                            <?php } ?>


                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php } ?>
    <div style="text-align: center; padding-top:20px">
        <button class="button button-primary" type="submit">保存</button>
        <button id="reset" class="button button-primary" type="reset">取消</button>
    </div>
</form>
<?php echo load_js('comm_util.js') ?>
<script type="text/javascript">
    $(function () {
        if ($(".jiazhuang_trade_shipping:checked").val() == 0) {
            $('.jiazhuang_shop_ye').addClass('display_class');
            $('.jiazhuang_shop_ye').next().addClass('display_class');
        }

        $(".psw_strong[value='1']").attr("checked", true);
        $(".psw_strong").attr("disabled", "disabled");
    });

    $("#reset").click(function () {
        location.reload();
    });

    $(".jiazhuang_trade_shipping").click(function () {
        if ($(".jiazhuang_trade_shipping:checked").val() == 0) {
            $('.jiazhuang_shop_ye').addClass('display_class');
            $('.jiazhuang_shop_ye').next().addClass('display_class');
        } else {
            $('.jiazhuang_shop_ye').removeClass('display_class');
            $('.jiazhuang_shop_ye').next().removeClass('display_class');
        }
    });

    BUI.use('bui/form', function (Form) {
        var form1 = new BUI.Form.HForm({
            srcNode: '#form1',
            submitType: 'ajax',
            callback: function (data) {
                if (data.status != '1') {
                    BUI.Message.Alert(data.message, 'error');
                    return;
                } else {
                    BUI.Message.Alert(data.message, 'success');
                    if (data.data.length > 0) {
                        for (var i in data.data) {
                            top.updatemenu(data.data[i]);
                        }
                    }
                }

            }
        }).render();
    });
</script>
