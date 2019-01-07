<ul class="nav-tabs oms_tabs">
    <li class="bui-tab-panel-item11  active"><a href="#"  id="cashier" >门店收银</a></li>  
</ul>
<!-- 门店收银 -->
<form id="form1" method="post" action="?app_act=sys/params/do_save" tabindex="0" style="outline: none;" >
    <table cellspacing="0" style="width:90%" class="table table-bordered" id="form_tbl">
        <?php foreach ($response['data'] as $k => $v) { ?>
            <?php foreach ($v as $value) { ?>
                <tr>
                    <td width="30%" ><?php echo $value['param_name']; ?></td>
                    <?php
                    switch ($value['type']) {
                        case "text":
                            ?>
                            <td style="width:100px;"><input type="text"  data-rules="{required: true}" value="<?php echo $value['value']; ?>" name="<?php echo $value['param_code']; ?>"> 月</td>
                            <?php
                            break;
                        case "time":
                            ?>
                            <td style="width:100px;"><input type="text" style="width:150px;" class= ' calendar ' id = "code"  data-rules="{required: true}" value="<?php echo $value['value']; ?>" name="<?php echo $value['param_code']; ?>"></td>
                            <?php break; ?> 

                        <?php case "select": ?>
                            <td style="width:100px;">
                                <select class="<?php echo $value['param_code']; ?>" name="<?php echo $value['param_code']; ?>" >
                                    <?php foreach ($value['form_desc'] as $r_k => $r_v) { ?>
                                        <option  value ="<?php echo $r_k; ?>" <?php if ($value['value'] == $r_k) { ?> selected <?php } ?> ><?php echo $r_v; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <?php break; ?> 
                        <?php case "radio": ?>
                            <td style="width:400px;">
                                <?php foreach ($value['form_desc'] as $r_k => $r_v) { ?>
                                    <input type="radio" style="width:40px;" class="<?php echo $value['param_code']; ?>" name="<?php echo $value['param_code']; ?>" 
                                           value="<?php echo $r_k; ?>" <?php if ($value['value'] == $r_k) { ?> checked="checked"   <?php } ?> /><?php echo $r_v; ?>
                                       <?php } ?>
                            </td>
                            <?php break; ?>
                    <?php }//switch  ?>
                    <td width="45%" ><?php echo isset($value['memo']) ? $value['memo'] : ''; ?></td>
                </tr>
            <?php } ?>
        <?php } ?>
    </table>
    <?php ?>
    <div style="text-align: center;">
        <button class="button button-primary" type="submit">保存</button>
        <button id="reset" class="button button-primary" type="reset">重置</button>
    </div>
</form>
<script type="text/javascript">
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
                }

            }
        }).render();
    });
    $(document).ready(function () {
        //TAB选项卡
        $(".oms_tabs a").click(function () {
            $(".oms_tabs").find(".active").removeClass("active");
            $(this).parent("li").addClass("active");
            if ($(this).attr('id') == "cashier") {
                $("#form1").show();
            }
        });
    });
</script>
