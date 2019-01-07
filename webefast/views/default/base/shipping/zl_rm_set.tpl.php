<!-- 热敏账号设置 -->
<form  id="form_rm_set" action="" method="post"  style = "display:none;">
    <table class="form_tbl">
        <tr>
            <td class="tdlabel">账号：</td>
            <td colspan="3">
                <input type="hidden" id="express_id" name="express_id" value=""/>
                <input type="text" value="" class="control-text span5"  data-rules="{required: true}"/>
            </td>
        </tr>
        <tr>
            <td class="tdlabel">密码：</td>
            <td colspan="3">
                <input type="text" value="" class="control-text span5" data-rules="{required: true}"/>
            </td>
        </tr>
        <tr>
            <td class="tdlabel"></td>
            <td colspan="3" class="btn-opt">
                <div style="margin-top:20px;">
                    <button class="button button-primary" type="submit">提交</button>
                    <button class="button " type="reset">重置</button>
                </div>
            </td>
        </tr>
    </table>
</form>

<form action="" id="form_rmsf_set" style = "display:none;">
    <span class="button " style="margin-top:10px;" onclick="add_j_custid()"><i class="icon-plus"></i>新增账号</span>
    <?php
    render_control('DataTable', 'sf_table', array(
        'conf' => array(
            'list' => array(
                array(
                    'type' => 'button',
                    'show' => 1,
                    'title' => '操作',
                    'field' => '_operate',
                    'width' => '80',
                    'align' => '',
                    'buttons' => array(
                        array('id' => 'edit', 'title' => '编辑',
                            'act' => 'pop:remin/shunfeng/config_add', 'show_name' => '编辑',
                            'show_cond' => ''),
                        array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'confirm' => '确认要删除此信息吗？'),
                    ),
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '接口URL',
                    'field' => 'api_url',
                    'width' => '300',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '月结卡账号',
                    'field' => 'j_custid',
                    'width' => '100',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '校验码',
                    'field' => 'checkword',
                    'width' => '150',
                    'align' => ''
                ),
            )
        ),
        'dataset' => 'remin/ShunfengModel::get_j_custid_list',
        'idField' => 'id',
        'params' => array('filter' => array('express_id' => $request['_id'])),
        'init' => 'nodata'
    ));
    ?>
</form>

<form action="" id="form_rmsfc_set" style = "display:none;">
    <span class="button " style="margin-top:10px;" onclick="add_api()"><i class="icon-plus"></i>新增账号</span>
    <?php
    render_control('DataTable', 'sfc_table', array(
        'conf' => array(
            'list' => array(
                array(
                    'type' => 'button',
                    'show' => 1,
                    'title' => '操作',
                    'field' => '_operate',
                    'width' => '80',
                    'align' => '',
                    'buttons' => array(
                        array('id' => 'edit', 'title' => '编辑', 'act' => 'pop:remin/sfc/config_add&app_scene=edit', 'show_name' => '编辑', 'show_cond' => '', 'pop_size' => '500,320'),
                        array('id' => 'delete', 'title' => '删除', 'callback' => 'do_api_delete', 'confirm' => '确认要删除此信息吗？'),
                    ),
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => 'appKey',
                    'field' => 'sfckey',
                    'width' => '250',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => 'token',
                    'field' => 'token',
                    'width' => '250',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => 'userId',
                    'field' => 'sfcid',
                    'width' => '150',
                    'align' => ''
                ),
            )
        ),
        'dataset' => 'remin/SfcModel::get_api_list',
        'idField' => 'pid',
        'params' => array('filter' => array('express_id' => $request['_id'])),
        'init' => 'nodata'
    ));
    ?>
</form>

<?php echo load_js("pur.js", true); ?>
<script>
    is_refresh = 0;
    if (print_type == 1) {
        //直连热敏,顺丰特殊
        if (company_code == 'SF') {
            $("#form_rmsf_set").show();
            sf_tableStore.load();
            $(".nodata").hide();
        } else if (company_code == 'SFC') {
            $("#form_rmsfc_set").show();
            sfc_tableStore.load();
            $(".nodata").hide();
        } else {
            $("#form_rm_set").show();
        }
    }

    function add_j_custid() {
        var express_id = '<?php echo $response['data']['express_id']; ?>';
        var url = "?app_act=remin/shunfeng/config_add&express_id=" + express_id;
        _do_execute(url, 'table', '新增账号', 500, 400);

    }

    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('remin/shunfeng/do_delete'); ?>', data: {id: row.id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功：', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    function do_api_delete(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('remin/sfc/do_delete'); ?>',
            data: {id: row.pid},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功', type);
                    sfctableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    function add_api() {
        var express_id = '<?php echo $response['data']['express_id']; ?>';
        var express_code = '<?php echo $response['data']['express_code']; ?>';
        var url = "?app_act=remin/sfc/config_add&express_id=" + express_id + "&express_code=" + express_code + "&app_scene=add&app_show_mode=pop";
        _do_execute(url, 'table', '新增账号', 500, 350);
    }

    parent._sfcreload_page = function () {
        sfc_tableStore.load();
    };
</script>