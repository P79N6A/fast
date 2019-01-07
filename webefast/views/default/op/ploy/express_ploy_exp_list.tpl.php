<?php
$check = $response['ploy_status'] != 1;
$links = array();
if ($check) {
    $links[] = array('type' => 'js', 'js' => 'selectExpress()', 'title' => '新增快递');
}
render_control('PageHead', 'head1', array('title' => "策略：{$response['ploy_name']}[{$response['ploy_code']}]",
    'links' => $links,
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
                    array('id' => 'express_deploy', 'title' => '区域/运费配置', 'act' => 'op/ploy/express_ploy/area_fare_set&ploy_code={ploy_code}&express_code={express_code}&express_name={express_name}&ploy_express_id={ploy_express_id}', 'show_name' => '可达区域及运费配置'),
                    array('id' => 'express_delete', 'title' => '删除', 'callback' => 'ploy_exp_delete', 'show_cond' => 'obj.express_status != 1 && obj.ploy_status!=1'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => $check ? '启用状态<img height="23" width="23" src="assets/images/tip.png" class="tip" data-align="bottom-left" title="点击图标操作启用\停用" />' : '启用状态',
                'field' => 'express_status',
                'width' => '110',
                'align' => 'center',
                'format_js' => array('type' => 'function', 'value' => 'changeExpressStatus')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '快递代码',
                'field' => 'express_code',
                'width' => '180',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '快递名称',
                'field' => 'express_name',
                'width' => '180',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '优先级<img height="23" width="23" src="assets/images/tip.png" class="tip" data-align="bottom-left" title="数字越大，优先级越高" />',
                'field' => 'express_level',
                'width' => '150',
                'align' => 'center',
                'editor' => $check ? "{xtype:'number'}" : ""
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '快递占比（%）<img height="23" width="23" src="assets/images/tip.png" class="tip" data-align="bottom-left" title="启用策略配送适配比例后可进行配置并生效" />',
                'field' => 'express_ratio',
                'width' => '150',
                'align' => 'center',
                'editor' => $check && $response['send_adapt_ratio'] == 1 ? "{xtype:'number'}" : ""
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '添加时间',
                'field' => 'insert_time',
                'width' => '200',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'op/ploy/ExpressPloyExpModel::get_express_by_page',
    'params' => array('filter' => array('ploy_code' => $request['ploy_code'])),
    'idField' => 'ploy_express_id',
    'CellEditing' => $check ? TRUE : FALSE,
));
?>

<script type="text/javascript">
<?php if ($response['ploy_status'] != 1): ?>
        var ploy_code = "<?php echo $response['ploy_code']; ?>";
        if (typeof tableCellEditing != "undefined") {
            tableCellEditing.on('accept', function (record, editor) {
                var editValue = record.editor.__attrVals.editValue,
                        editId = record.editor.__attrVals.id,
                        _record = record.record;
                var params = {};
                if (editId == 'editor1') {
                    if (!isPositiveNum(_record.express_level)) {
                        BUI.Message.Tip('优先级必须为正整数', 'warning');
                        tableStore.load();
                        return;
                    }
                    if (_record.express_level == editValue) {
                        return;
                    }
                    params.express_level = _record.express_level;
                }
                if (editId == 'editor2') {
                    if (Number(_record.express_ratio) < 0) {
                        BUI.Message.Tip('快递占比必须大于0', 'warning');
                        tableStore.load();
                        return;
                    }
                    if (_record.express_ratio == editValue) {
                        return;
                    }

                    params.express_ratio = _record.express_ratio;
                }
                params.ploy_express_id = _record.ploy_express_id;
                params.ploy_code = _record.ploy_code;
                params.express_code = _record.express_code;
                params.express_name = _record.express_name;

                $.post('?app_act=op/ploy/express_ploy/ploy_exp_edit', params, function (ret) {
                    if (ret.status == 1) {
                        BUI.Message.Tip(ret.message, 'success');
                        tableStore.load();
                    } else if (ret.status == 2) {
                        BUI.Message.Tip(ret.message, 'warning');
                        tableStore.load();
                    } else {
                        BUI.Message.Tip(ret.message, 'error');
                    }
                }, 'json');
            });
        }

        //选择快递
        function selectExpress() {
            var param = {ploy_code: ploy_code, data_type: 'ploy_express'};
            if (typeof (top.dialog) != 'undefined') {
                top.dialog.remove(true);
            }
            var url = '?app_act=common/select_comm/express';
            var buttons = [
                {
                    text: '保存继续',
                    elCls: 'button button-primary',
                    handler: function () {
                        var data = top.SelectoGrid.getSelection();
                        if (data.length < 1) {
                            BUI.Message.Tip('未选择快递', 'warning');
                            return;
                        }
                        addExpress(data);
                    }
                },
                {
                    text: '保存退出',
                    elCls: 'button button-primary',
                    handler: function () {
                        var data = top.SelectoGrid.getSelection();
                        if (data.length < 1) {
                            BUI.Message.Tip('未选择快递', 'warning');
                        }
                        addExpress(data);
                        this.close();
                    }
                }
            ];
            top.BUI.use('bui/overlay', function (Overlay) {
                top.dialog = new Overlay.Dialog({
                    title: '选择快递',
                    width: '700',
                    height: '550',
                    loader: {
                        url: url,
                        autoLoad: true, //不自动加载
                        params: param, //附加的参数
                        lazyLoad: false, //不延迟加载
                        dataType: 'text'   //加载的数据类型
                    },
                    align: {
                        //node : '#t1',//对齐的节点
                        points: ['tc', 'tc'], //对齐参考：http://dxq613.github.io/#positon
                        offset: [0, 20] //偏移
                    },
                    mask: true,
                    buttons: buttons
                });
                top.dialog.on('closed', function (ev) {

                });
                top.dialog.show();
            });
        }

        //新增快递
        function addExpress(data) {
            $.post('?app_act=op/ploy/express_ploy/ploy_exp_add', {ploy_code: ploy_code, express: data}, function (ret) {
                var tag;
                if (ret.status == 1) {
                    tableStore.load();
                    tag = 'success';
                } else if (ret.status == 2) {
                    tag = 'warning';
                } else {
                    tag = 'error';
                }
                BUI.Message.Tip(ret.message, tag);
            }, 'json');
        }

        //策略删除
        function ploy_exp_delete(index, row) {
            BUI.Message.Confirm('确认要删除快递吗？<br><span style="color:red">注意：删除快递会同时清空快递配置，请谨慎操作！</span>', function () {
                $.post('?app_act=op/ploy/express_ploy/ploy_exp_delete', {ploy_express_id: row.ploy_express_id, ploy_code: row.ploy_code, express_code: row.express_code, express_name: row.express_name}, function (ret) {
                    var tag;
                    if (ret.status == 1) {
                        tableStore.load();
                        tag = 'success';
                    } else if (ret.status == 2) {
                        tag = 'warning';
                    } else {
                        tag = 'error';
                    }
                    BUI.Message.Tip(ret.message, tag);
                }, 'json');
            }, 'question');
        }
<?php endif; ?>

    var change_status_priv = "<?php echo $response['change_status_priv']; ?>";
    function changeExpressStatus(value, row, index) {
        var params = JSON.stringify(row).toString();
        //有启用\停用权限，并且策略未启用，才能操作快递
        if (value == 1) {
            if (change_status_priv == 1 && row.ploy_status != 1) {
                return '<a href=\'javascript:setExpressActive(' + params + ',' + value + ')\'><img  src="' + ES.Util.getThemeUrl('images/ok.png') + '" class="tip" data-align="right" title="点击停用" /></a>';
            } else {
                return '<img  src="' + ES.Util.getThemeUrl('images/ok.png') + '" />';
            }
        } else {
            if (change_status_priv == 1 && row.ploy_status != 1) {
                return '<a href=\'javascript:setExpressActive(' + params + ',' + value + ')\'><img  src="' + ES.Util.getThemeUrl('images/no.gif') + '" class="tip" data-align="right" title="点击启用" /></a>';
            } else {
                return '<img  src="' + ES.Util.getThemeUrl('images/no.gif') + '" />';
            }
        }
    }

    function setExpressActive(params, value) {
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
                        tableStore.load();
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

    //页面帮助提示
    BUI.use('bui/tooltip', function (Tooltip) {
        var tips = new Tooltip.Tips({
            tip: {
                trigger: '.tip', //出现此样式的元素显示tip
                alignType: 'right', //默认方向
                elCls: 'tips tips-info',
                titleTpl: '<div class="tips-content" style="margin-left: 0px">{title}</div>',
                offset: 10 //距离左边的距离
            }
        });
        tips.render();
    });

    function expressDeploy() {
        BUI.Message.Tip('功能暂未开放，敬请关注', 'warning');
    }

    //判断是否为正整数  
    function isPositiveNum(s) {
        var re = /^[0-9]*[1-9][0-9]*$/;
        return re.test(s);
    }

</script>