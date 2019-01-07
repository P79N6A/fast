<?php echo load_js('jquery.min.js'); ?>
<style>
    body{ font-family:'微软雅黑'; font-size:12px;}
    ol li,ul li{ list-style:none;}
    #purview_menu ul{display:block;}
    #purview_menu ul li{ line-height:30px; font-size:18px; font-weight:bold; color:#000;}
    #purview_menu ul ul li{ font-size:16px; font-weight:normal; color:#333;}
    #purview_menu ul ul ul li{ font-size:14px; font-weight:normal; color:#666;}
    #purview_menu ul ul ul ul li{ font-size:12px; font-weight:normal; color:#999;}
    #purview_menu li input{ margin:0 5px 0 5px;}
    #purview_menu ul ul{ margin:0 0 0 20px;}
    .role_operate{float:left;color:#565656;}
    .addListSubmit a{cursor:pointer;}
    .baocun-box{ margin:5px 10px 20px 10px;}

    #tbl_1{border-collapse:collapse;border:0;}
    #tbl_th th{width:140px;padding:4px;border:1px #ccc solid;border-bottom:0px;}

    #tbl_1{border-collapse:collapse;border:1px #ccc solid;}
    #tbl_1 label{display:block;width:140px;}
    #tbl_1 td{padding:4px;border:1px #ccc solid;border-collapse:collapse;}
    #tbl_1 .intd{padding:0;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '分配权限[' . $response['role']['role_code'] . '-' . $response['role']['role_name'] . ']',
    'links' => array(
        array('url' => 'sys/role/do_list', 'title' => '角色列表'),
    ),
));
?>
<!-- 分派角色权限 -->
<div id="purview_menu" style="margin-left: 8px">
    <table id="tbl_th">
        <tr><th>一级菜单</th><th>二级菜单</th><th>三级菜单</th><th>功能按钮</th></tr>
    </table>
    <table id="tbl_1">
        <?php
        foreach ($response['menu_tree'] as $m1) {
            if (in_array($m1['action_id'], $response['privilege_arr'])) {
                $chk_tag_1 = "checked";
            } else {
                $chk_tag_1 = "";
            }
            echo "<tr><td><label><input type='checkbox' level='1' v='{$m1['action_id']}' value='{$m1['action_id']}' {$chk_tag_1}/>{$m1['action_name']}</label></td><td class='intd'><table>";
            foreach ($m1['_child'] as $m2) {
                if (in_array($m2['action_id'], $response['privilege_arr'])) {
                    $chk_tag_2 = "checked";
                } else {
                    $chk_tag_2 = "";
                }
                echo "<tr><td><label><input type='checkbox' level='2' m1v='{$m1['action_id']}' v='{$m2['action_id']}' value='{$m2['action_id']}' {$chk_tag_2}/>{$m2['action_name']}</label></td><td class='intd'><table>";
                foreach ($m2['_child'] as $m3) {
                    if (in_array($m3['action_id'], $response['privilege_arr'])) {
                        $chk_tag_3 = "checked";
                    } else {
                        $chk_tag_3 = "";
                    }
                    echo "<tr><td><label><input type='checkbox' level='3' m1v='{$m1['action_id']}' m2v='{$m2['action_id']}' value='{$m3['action_id']}' {$chk_tag_3}/>{$m3['action_name']}</label></td><td class='intd'><table>";
                    foreach ($m3['_child'] as $m4) {
                        if (in_array($m4['action_id'], $response['privilege_arr'])) {
                            $chk_tag_4 = "checked";
                        } else {
                            $chk_tag_4 = "";
                        }
                        echo "<tr><td><label><input type='checkbox' level='4' m1v='{$m1['action_id']}' m2v='{$m2['action_id']}' m3v='{$m3['action_id']}' value='{$m4['action_id']}' {$chk_tag_4}/>{$m4['action_name']}</label></td></tr>";
                    }
                    echo "</table></td></tr>";
                }
                echo "</table></td></tr>";
            }
            echo "</table></td></tr>";
        }
        ?>
    </table>
    <div>
<?php //echo '<hr/>$order_info<xmp>'.var_export($response['menu_tree'],true).'</xmp>'; ?>
    </div>
</div>
<div class="addListSubmit frontool">
    <div class="baocun-box">
        <input type="button" value="全选" class="button button-primary p_btns" onclick="checkBoxAll(jQuery('#purview_menu'))"/>
        <input type="button" value="反选" class="button button-primary p_btns" onclick="checkBoxNoAll(jQuery('#purview_menu'))"/>
        <input type="button" id="submit" name="submit"  value=" 保存" class="button button-primary p_btns" />
        <input type="hidden" id="role_id" name="role_id" value="<?php echo $response['allot']['role_id'] ?>" class="p_btns" />
        <?php
        require_lib('security/CSRFHandler', true);
        echo '<input type="hidden" id="__es_csrf_t__" name="__es_csrf_t__" value="' . CsrfHandler::get_token() . '"/>';
        ?>
    </div>
    <div class="front_close">&lt;</div>
</div>

<script>
$(function(){
	function tools(){
        $(".frontool").animate({left:'0px'},1000);
        $(".front_close").click(function(){
            if($(this).html()=="&lt;"){
                $(".frontool").animate({left:'-100%'},1000);
                $(this).html(">");
				$(this).addClass("close_02").animate({right:'-10px'},1000);
            }else{
                $(".frontool").animate({left:'0px'},1000);
                $(this).html("<");
				$(this).removeClass("close_02").animate({right:'0'},1000);
            }
        });
    }
	
	tools();
})
</script>
<script type="text/javascript">
    $("#tbl_1 input").click(function () {
        var level = $(this).attr('level');
        var cur_v = $(this).attr('v');
        var chk_flag = $(this).attr("checked");
        if (level == 1) {
            if (chk_flag) {
                $(":checkbox[m1v=" + cur_v + "]").attr("checked", true);
            } else {
                $(":checkbox[m1v=" + cur_v + "]").removeAttr("checked");
            }
        }
        if (level == 2) {
            if (chk_flag) {
                $(":checkbox[m2v=" + cur_v + "]").attr("checked", true);
            } else {
                $(":checkbox[m2v=" + cur_v + "]").removeAttr("checked");
            }
        }
        if (level == 3) {
            if (chk_flag) {
                var m1v = $(this).attr('m1v');
                var m2v = $(this).attr('m2v');
                $(":checkbox[value=" + m1v + "]").attr("checked", true);
                $(":checkbox[value=" + m2v + "]").attr("checked", true);
            }
        }
        if (level == 4) {
            if (chk_flag) {
                var m1v = $(this).attr('m1v');
                var m2v = $(this).attr('m2v');
                var m3v = $(this).attr('m3v');
                $(":checkbox[value=" + m1v + "]").attr("checked", true);
                $(":checkbox[value=" + m2v + "]").attr("checked", true);
                $(":checkbox[value=" + m3v + "]").attr("checked", true);
            }
        }
    });
    /**
     * 全选操作
     * @param div jquery对象
     */
    function checkBoxAll(div) {
        div.find("input[type='checkbox']").attr("checked", true);
    }
    /**
     * 反选操作
     * @param div jquery对象
     */
    function checkBoxNoAll(div) {
        div.find("input[type='checkbox']").attr("checked", false);
    }

    /**
     * 绑定checkbox选择事件
     * 绑定保存提交事件
     */
    jQuery(function () {
        //jQuery("input[type='checkbox']").bind("click",role_click);
        $('#submit').click(function () {
            var menu = "";
            jQuery("input[type='checkbox']").each(function () {
                if (jQuery(this).attr("checked") && jQuery(this).val() != '') {
                    menu += jQuery(this).val() + ",";
                }
            })
            //alert(menu);return;
            //menu = menu.slice(0,-1);
            if (menu == '') {
                alert('请选择一项权限');
                return false;
            }
            var params = {'do': 1};
            params.role_id = $('#role_id').val();
            params.__es_csrf_t__ = $('#__es_csrf_t__').val();
            params.action_id = menu;
            $('#submit').attr('disabled', true);
            var url = '<?php echo get_app_url('sys/role/update_allot') ?>';
            $.post(url, params, function (data) {
                try {
                    var ret = eval('(' + data + ')');
                    if (ret.status == 1) {
                        BUI.Message.Alert('保存成功', 'success');
                    } else {
                        BUI.Message.Alert(ret.message, 'error');
                    }
                } catch (e) {
                    BUI.Message.Alert(e, 'error');
                }
                $('#submit').attr('disabled', false);
            });
        });
    })
    function role_click() {
        var id = jQuery(this).attr("id");
        if (jQuery(this).is(':checked') == true) {
            jQuery("." + id).find("input[type='checkbox']").attr("checked", true);
            //勾选父级
            if (typeof jQuery(this).parent().parent().attr("class") != "0") {
                var pid = jQuery(this).parent().parent().attr("class");
                jQuery("#" + pid).find("input[type='checkbox']").attr("checked", true);
            }
            if (typeof jQuery(this).parent().parent().parent().attr("class") != "0") {
                var pid = jQuery(this).parent().parent().parent().attr("class");
                jQuery("#" + pid).find("input[type='checkbox']").attr("checked", true);
            }
            if (typeof jQuery(this).parent().parent().parent().parent().attr("class") != "0") {
                var pid = jQuery(this).parent().parent().parent().parent().attr("class");
                jQuery("#" + pid).find("input[type='checkbox']").attr("checked", true);
            }
        } else {
            jQuery("." + id).find("input[type='checkbox']").attr("checked", false);
        }
    }
</script>