<style>
    .print_type_btn{ border:1px solid #1695ca; background:#FFF; color:#1695ca; margin-right:2px; border-radius:3px;}
    .tips .tips-info .tips-content{width: 20%;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '订单审核规则',
    'links' => array(
    ),
    'ref_table' => 'table',
));
?>

<?php
render_control('DataTable', 'table', array('conf' => array('list' => array(
            array('type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '50',
                'align' => 'center',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:edit_strategy(\\\'{check_strategy_code}\\\')">配置</a>',
                ),
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'is_active',
                'width' => '110',
                'align' => 'center',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '规则说明',
                'field' => 'instructions',
                'width' => '330',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '规则内容',
                'field' => 'content',
                'width' => '100%',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'oms/OrderCheckStrategyModel::get_by_page',
    'idField' => 'strategy_id',
));
?>
<script type="text/javascript">
    $(function () {
        parent.refreshlist = tableStore;
    });

    function edit_strategy(check_strategy_code) {
        if (check_strategy_code === 'not_auto_confirm_with_goods') {
            var url = '?app_act=oms/order_check_strategy/detail&app_scene=edit&check_strategy_code=' + check_strategy_code;
            openPage(window.btoa(url), url, '订单包含指定商品');
        } else if (check_strategy_code === 'not_auto_confirm_with_shop') {
            new ESUI.PopWindow("?app_act=oms/order_check_strategy/detail_shop&check_strategy_code=" + check_strategy_code, {
                title: "订单对应平台/店铺 ",
                width: 800,
                height: 600,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    location.reload();
                }
            }).show();
        } else if (check_strategy_code === 'auto_confirm_time') {
            new ESUI.PopWindow("?app_act=oms/order_check_strategy/detail_time&check_strategy_code=" + check_strategy_code, {
                title: "执行时间点设置",
                width: 600,
                height: 600,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    location.reload();
                }
            }).show();
        } else if (check_strategy_code === 'protect_time') {
            new ESUI.PopWindow("?app_act=oms/order_check_strategy/protect_time&check_strategy_code=" + check_strategy_code, {
                title: "订单保护期设置",
                width: 600,
                height: 400,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    location.reload();
                }
            }).show();
        } else if (check_strategy_code === 'not_auto_confirm_with_store') {
            new ESUI.PopWindow("?app_act=oms/order_check_strategy/detail_store&check_strategy_code=" + check_strategy_code, {
                title: "订单对应平台/店铺 ",
                width: 800,
                height: 600,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    location.reload();
                }
            }).show();
        } else if (check_strategy_code === 'not_auto_confirm_with_money') {
            new ESUI.PopWindow("?app_act=oms/order_check_strategy/detail_money&check_strategy_code=" + check_strategy_code, {
                title: "订单金额范围设置",
                width: 390,
                height: 300,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    location.reload();
                }
            }).show();
        }

    }

    function change_status(_this, status, id) {

        _do_set_active(_this, status, id);
    }

    function _do_set_active(_this, status, id) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('oms/order_check_strategy/update_active'); ?>',
            data: {id: id, type: status},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    //页面帮助提示
    BUI.use('bui/tooltip', function (Tooltip) {
        var tips = new Tooltip.Tips({
            tip: {
                trigger: '.tip', //出现此样式的元素显示tip
                alignType: 'bottom-left', //默认方向
                elCls: 'tips tips-info',
                titleTpl: '<div class="tips-content" style="margin-left: 0px;">{title}</div>',
                offset: 10 //距离左边的距离
            }
        });
        tips.render();
    });
</script>