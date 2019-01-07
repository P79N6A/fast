<style>
    .item_prop select{ width: 147px;}
    .item_prop .row{ margin-bottom:5px;}
    .item_prop{ padding:20px;}
</style>
<div class="item_prop">
    <form  class="form-horizontal" id="form_item_prop" action="?app_act=api/tb_issue/save_item_prop&tab_type=item_prop" method="post">
        <input type="hidden" name="shop_code" value="<?php echo $request['shop_code'] ?>">
        <input type="hidden" name="goods_code" value="<?php echo $request['goods_code'] ?>">
        <input type="hidden" name="prop_13021751" value="<?php echo $request['goods_code'] ?>">
        <?php if (!empty($response['item_element']['key_prop'])) { ?>
            <div class="row">
                <label class="control-label span6" style="width:100px;font-weight: bold;">关键属性：</label>
            </div>
            <?php foreach ($response['item_element']['key_prop'] as $key => $val) { ?>
                <div class="row">
                    <div class="control-group span13">
                        <label class="control-label span6" style="width:100px"><?php echo $val['title'] ?>:</label>
                        <div class="controls">
                            <?php if ($val['type'] == 'singleCheck') { ?>
                                <select name="<?php echo $key ?>" class="input-normal" data-rules="{required: true}">
                                    <option value="">请选择</option>
                                    <?php
                                    foreach ($val['option'] as $v) {
                                        $option = "<option value = '{$v['value']}' ";
                                        $option .=$val['value'] == $v['value'] ? 'selected="selected"' : '';
                                        $option.=" >{$v['displayName']}</option>";
                                        echo $option;
                                    }
                                    ?>
                                </select>
                                <b style="color:red"> *</b>
                                <?php
                            } else if ($val['type'] == 'input') {
                                echo "<input name='{$key}' type='text' {$rule} class='input-normal control-text' value='{$val['value']}'>";
                            } else if ($val['type'] == 'multiCheck') {
                                foreach ($val['option'] as $v) {
                                    $checked = in_array($v['value'], $val['value']) ? 'checked="checked"' : '';
                                    $controls .= "<label class='checkbox'><input name='{$key}[]' type='checkbox' value='{$v['value']}' {$checked} />{$v['displayName']}</label> &nbsp; &nbsp;";
                                }
                                echo $controls;
                                ?>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
        <?php if (!empty($response['item_element']['no_key_prop'])) { ?>
            <div class="row">
                <label class="control-label span6" style="width:100px;font-weight: bold;">非关键属性：</label>
            </div>
            <?php
            foreach ($response['item_element']['no_key_prop'] as $key => $val) {
                $rule = $val['rule'] == true ? 'data-rules="{required: true}"' : '';
                ?>
                <div class="row">
                    <div class="control-group span13">
                        <label class="control-label span6" style="width:100px"><?php echo $val['title'] ?>:</label>
                        <div class="controls">
                            <?php if ($val['type'] == 'singleCheck') { ?>
                                <select name="<?php echo $key ?>" class="input-normal" <?php echo $rule ?>>
                                    <option value="">请选择</option>
                                    <?php
                                    foreach ($val['option'] as $v) {
                                        $option = "<option value = '{$v['value']}' ";
                                        $option .=$val['value'] == $v['value'] ? 'selected="selected"' : '';
                                        $option.=" >{$v['displayName']}</option>";
                                        echo $option;
                                    }
                                    ?>
                                </select>
                                <?php
                                echo $val['rule'] == true ? '<b style="color:red"> *</b>' : '';
                            } else if ($val['type'] == 'input') {
                                echo "<input name='{$key}' type='text' {$rule} class='input-normal control-text' value='{$val['value']}'>";
                            } else if ($val['type'] == 'multiCheck') {
                                foreach ($val['option'] as $v) {
                                    $checked = in_array($v['value'], $val['value']) ? 'checked="checked"' : '';
                                    $controls .= "<label class='checkbox'><input name='{$key}[]' type='checkbox' value='{$v['value']}' {$checked} />{$v['displayName']}</label> &nbsp; &nbsp;";
                                }
                                echo $controls;
                                ?>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
        <div class="row form-actions actions-bar">
            <div class="span13 offset3 ">
                <button type="submit" class="button button-primary" id="submit">提交</button>
                <button type="reset" class="button " id="reset">重置</button>
            </div>
        </div>
    </form>
    <script type="text/javascript">
        $(function () {
            var form_item_prop = new BUI.Form.HForm({
                srcNode: '#form_item_prop',
                submitType: 'ajax',
                callback: function (data) {
                    if (data.status == 1) {
                        BUI.Message.Alert(data.message, 'success');
                    } else {
                        BUI.Message.Alert(data.message, 'error');
                    }

                }
            }).render();

            var item_prop = <?php echo $response['item_prop']['item_prop']; ?>;
            $.each(item_prop, function (key, val) {
                if ($.isArray(val)) {
                    $.each(val, function (k, v) {
                        var obj = $("#form_item_prop input[value='" + v + "']");
                        if (obj != 'undefined') {
                            obj.attr('checked', 'checked');
                        }
                    });
                }
                var obj = $("#form_item_prop input[name='" + key + "']");
                if (obj != 'undefined') {
                    obj.val(val);
                }
                var obj = $("#form_item_prop select[name='" + key + "']");
                if (obj != 'undefined') {
                    obj.val(val);
                }
            });
        })
    </script>
</div>