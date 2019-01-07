<?php
render_control('PageHead', 'head1', array('title' => '快递适配策略（新）',
    'links' => array(
        array('url' => 'op/ploy/express_ploy/exp_census', 'title' => '快递分布', 'is_pop' => FALSE),
        array('url' => 'op/ploy/express_ploy/ploy_detail&app_scene=add', 'title' => '新增策略', 'is_pop' => FALSE),
    ),
    'ref_table' => 'table'
));
?>

<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '150',
                'align' => '',
                'buttons' => array(
                    array('id' => 'view', 'title' => '查看', 'act' => 'op/ploy/express_ploy/ploy_view&app_scene=view&ploy_code={ploy_code}', 'show_name' => '查看策略', 'show_cond' => 'obj.ploy_status !=0'),
                    array('id' => 'edit', 'title' => '编辑', 'act' => 'op/ploy/express_ploy/ploy_detail&app_scene=edit&ploy_code={ploy_code}', 'show_name' => '编辑策略', 'show_cond' => 'obj.ploy_status != 1', 'priv' => 'op/ploy/express_ploy/ploy_detail'),
                    array('id' => 'deploy', 'title' => '快递配置', 'act' => 'op/ploy/express_ploy/exp_list&ploy_code={ploy_code}', 'show_name' => '快递配置'),
                    array('id' => 'delete', 'title' => '删除', 'callback' => 'ploy_delete', 'show_cond' => 'obj.ploy_status != 1', 'priv' => 'op/ploy/express_ploy/ploy_delete')
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => $response['change_status_priv'] == 1 ? '启用状态<img height="23" width="23" src="assets/images/tip.png" class="tip" data-align="bottom-left" title="点击图标操作启用\停用" />' : '启用状态',
                'field' => 'ploy_status',
                'width' => '100',
                'align' => 'center',
                'format_js' => array('type' => 'function', 'value' => 'changeStatus')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '策略代码',
                'field' => 'ploy_code',
                'width' => '110',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '策略名称',
                'field' => 'ploy_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '适配店铺',
                'field' => 'ploy_shop',
                'width' => '170',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '发货仓库',
                'field' => 'send_store',
                'width' => '170',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '创建时间',
                'field' => 'create_time',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作日志',
                'field' => '_operate_log',
                'width' => '80',
                'align' => 'center',
                'buttons' => array(
                    array('id' => 'view_log', 'title' => '查看', 'callback' => 'show_log')
                ),
            ),
        )
    ),
    'dataset' => 'op/ploy/ExpressPloyModel::get_ploy_by_page',
    'idField' => 'ploy_id',
    'CascadeTable' => array(
        'list' => array(
            array('title' => '配置', 'field' => 'express_set', 'width' => '100', 'format_js' => array('type' => 'function', 'value' => 'expressSet')),
            array('title' => '快递启用状态', 'field' => 'express_status', 'width' => '100', 'format_js' => array('type' => 'function', 'value' => 'changeExpressStatus'), 'align' => 'center'),
            array('title' => '快递代码', 'type' => 'text', 'width' => '180', 'field' => 'express_code'),
            array('title' => '快递名称', 'type' => 'text', 'width' => '180', 'field' => 'express_name'),
            array('title' => '优先级<img height="23" width="23" src="assets/images/tip.png" class="tip" data-align="top-left" title="数字越大，优先级越高" />', 'type' => 'text', 'width' => '150', 'align' => 'center', 'field' => 'express_level'),
            array('title' => '快递占比（%）', 'type' => 'text', 'width' => '150', 'align' => 'center', 'field' => 'express_ratio'),
            array('title' => '添加时间', 'type' => 'text', 'width' => '200', 'field' => 'insert_time'),
        ),
        'page_size' => 50,
        'url' => get_app_url("op/ploy/express_ploy/get_ploy_express"),
        'params' => 'ploy_code',
    ),
));
?>

<script type="text/javascript">
    var change_status_priv = <?php echo $response['change_status_priv']; ?>;
    function changeStatus(value, row, index) {
        if (value == 1) {
            if (change_status_priv == 1) {
                return '<a href="javascript:setActive(this,\'' + row.ploy_code + '\',' + value + ')"><img  src="' + ES.Util.getThemeUrl('images/ok.png') + '" class="tip" data-align="right" title="点击停用" /></a>';
            } else {
                return '<img  src="' + ES.Util.getThemeUrl('images/ok.png') + '" />';
            }
        } else {
            if (change_status_priv == 1) {
                return '<a href="javascript:setActive(this,\'' + row.ploy_code + '\',' + value + ')"><img  src="' + ES.Util.getThemeUrl('images/no.gif') + '" class="tip" data-align="right" title="点击启用" /></a>';
            } else {
                return '<img  src="' + ES.Util.getThemeUrl('images/no.gif') + '" />';
            }
        }
    }

    function setActive(_this, ploy_code, value) {
        value = (value == 0) ? 1 : 0;
        var msg = value == 1 ? '启用' : '停用';
        BUI.Message.Confirm('确定要' + msg + '策略吗？', function () {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<?php echo get_app_url('op/ploy/express_ploy/update_ploy_active'); ?>',
                data: {ploy_code: ploy_code, active: value},
                success: function (ret) {
                    if (ret.status == 1) {
                        tableStore.load();
                        tag = 'success';
                    } else if (ret.status == 2) {
                        tableStore.load();
                        tag = 'warning';
                    } else {
                        tag = 'error';
                    }
//                        var row = {ploy_code: ploy_code};
//                        var html = optChangeStatus(value, row, 1);
//                        $(_this).parent().html(html);
                }
            });
        });
    }

    function changeExpressStatus(value, row, index) {
        var params = JSON.stringify(row).toString();
        //有启用\停用权限，并且策略未启用，才能操作快递
        if (value == 1) {
            if (change_status_priv == 1 && row.ploy_status != 1) {
                return '<a href=\'javascript:void(0)\' onclick=\'setExpressActive(this,' + params + ',' + value + ')\'><img  src="' + ES.Util.getThemeUrl('images/ok.png') + '" class="tip" data-align="right" title="点击停用" /></a>';
            } else {
                return '<img  src="' + ES.Util.getThemeUrl('images/ok.png') + '" />';
            }
        } else {
            if (change_status_priv == 1 && row.ploy_status != 1) {
                return '<a href=\'javascript:void(0)\' onclick=\'setExpressActive(this,' + params + ',' + value + ')\'><img  src="' + ES.Util.getThemeUrl('images/no.gif') + '" class="tip" data-align="right" title="点击启用" /></a>';
            } else {
                return '<img  src="' + ES.Util.getThemeUrl('images/no.gif') + '" />';
            }
        }
    }

    function expressSet(index, row) {
        var url = "?app_act=op/ploy/express_ploy/area_fare_set&ploy_code=" + row.ploy_code + "&ploy_express_id=" + row.ploy_express_id + "&express_code=" + row.express_code + "&express_name=" + window.encodeURIComponent(row.express_name);
        return "<a href=\"#\" onclick=\"openPage(window.btoa('" + url + "'), '" + url + "', '可达区域及运费配置');return false;\">区域/运费配置</a>";
    }

    function setExpressActive(_this, params, value) {
        value = (value == 0) ? 1 : 0;
        var msg = value == 1 ? '启用' : '停用';
        var data = {ploy_express_id: params.ploy_express_id, ploy_code: params.ploy_code, express_code: params.express_code, express_name: params.express_name, active: value};
        BUI.Message.Confirm('确定要' + msg + '快递配置吗？', function () {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<?php echo get_app_url('op/ploy/express_ploy/update_express_active'); ?>',
                data: {params: data},
                success: function (ret) {
                    if (ret.status == 1) {
                        var html = changeExpressStatus(value, params, 1);
                        $(_this).parent().html(html);
                        tag = 'success';
                    } else if (ret.status == 2) {
                        tableStore.load();
                        tag = 'warning';
                    } else {
                        tag = 'error';
                    }
                    BUI.Message.Tip(ret.message, tag);
                }
            });
        });
    }

    //策略删除
    function ploy_delete(index, row) {
        BUI.Message.Confirm('确认要删除策略吗？<br><span style="color:red">注意：删除策略会同时清空策略下的所有配置，请谨慎操作！</span>', function () {
            $.post('?app_act=op/ploy/express_ploy/ploy_delete', {ploy_code: row.ploy_code}, function (data) {
                if (data.status == 1) {
                    tableStore.load();
                    BUI.Message.Tip(data.message, 'success');
                } else {
                    BUI.Message.Tip(data.message, 'error');
                }
            }, 'json');
        }, 'question');
    }

    //页面帮助提示
    BUI.use('bui/tooltip', function (Tooltip) {
        var tips = new Tooltip.Tips({
            tip: {
                trigger: '.tip', //出现此样式的元素显示tip
                alignType: 'right', //默认方向
                elCls: 'tips tips-info',
                titleTpl: '<div class="tips-content" style="margin-left: 0px;">{title}</div>',
                offset: 10 //距离左边的距离
            }
        });
        tips.render();
    });

    //查看操作日志
    function show_log(_index, row) {
        new ESUI.PopWindow("?app_act=op/ploy/express_ploy/log&ploy_code=" + row.ploy_code, {
            title: '操作日志 ，策略编码：' + row.ploy_code,
            width: 800,
            height: 550,
            onBeforeClosed: function () {}
        }).show();
    }

</script>