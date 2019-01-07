<div class="layer_table_01">
    <form id="updatePaper">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <input type="hidden" id="type" name="type" value="1"/>
            <input type="hidden" id="shop_print_id" name="shop_print_id" value="<?php echo $response['danju_print_info']['data']['shop_print_id'];?>"/>
            <tr>
                <td width="100">纸张类型:</td>
                <td>
                    <select id="template_page_style" name="template_page_style"
                            onchange="setDefaultStyle();">
                        <option value="A4" <?php if ($response['danju_print_info']['data']['template_page_style'] == 'A4') echo 'selected="selected"';?>>
                            A4
                        </option>
                        <option value="A3" <?php if ($response['danju_print_info']['data']['template_page_style'] == 'A3') echo 'selected="selected"';?>>
                            A3
                        </option>
                        <option value="A5" <?php if ($response['danju_print_info']['data']['template_page_style'] == 'A5') echo 'selected="selected"';?>>
                            A5
                        </option>
                        <option value="B5" <?php if ($response['danju_print_info']['data']['template_page_style'] == 'B5') echo 'selected="selected"';?>>
                            B5
                        </option>
                        <option value="B6" <?php if ($response['danju_print_info']['data']['template_page_style'] == 'B6') echo 'selected="selected"';?>>
                            B6
                        </option>
                        <option value="custom_pager" <?php if ($response['danju_print_info']['data']['template_page_style'] == 'custom_pager') echo 'selected="selected"';?>>
                            自定义
                        </option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>纸张宽度:</td>
                <td><input type="text" id="template_page_width" name="template_page_width" onchange='if(!isNumeric(value)) value="0";'
                           value="<?php echo ($response['danju_print_info']['data']['template_page_width']);?>" size="3"
                           maxlength="100" <?php echo $response['danju_print_info']['data']['template_page_style'] != 'custom_pager' ? 'disabled="disabled"' : ''; ?>/>(mm)
                </td>
            </tr>
            <tr>
                <td>纸张高度:</td>
                <td><input type="text" id="template_page_height" name="template_page_height" onchange='if(!isNumeric(value)) value="0";'
                           value="<?php echo ($response['danju_print_info']['data']['template_page_height']); ?>"
                           size="3"
                           maxlength="100" <?php echo $response['danju_print_info']['data']['template_page_style'] != 'custom_pager' ? 'disabled="disabled"' : ''; ?>/>(mm)
                </td>
            </tr>
        </table>
    </form>
</div>

<script type="text/javascript">
    function setDefaultStyle() {
        if ($('#template_page_style').val() != 'custom_pager') {

	        var page_style = $('#template_page_style').val();

            $.post('?app_act=common/danju_print/get_page_style&app_page=null&app_fmt=json&page_style=' + page_style, function (data) {
                var ret = $.parseJSON(data);
                $("#template_page_width").val(ret.data.width);
                $("#template_page_height").val(ret.data.height);
                $("#template_page_width").attr("disabled", true);
                $("#template_page_height").attr("disabled", true);
            });
        } else {
            $("#template_page_width").attr("disabled", false);
            $("#template_page_height").attr("disabled", false);
        }
    }
</script>