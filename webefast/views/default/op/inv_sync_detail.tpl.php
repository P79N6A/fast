<?php
render_control('PageHead', 'head1', array('title' => $response['title']));
?>
<?php
$tabs = array(
    array('title' => '基本信息', 'active' => true, 'id' => 'baseinfo'),
);
if ($app['scene'] == 'edit' || $app['scene'] == 'view') {
    $tabs[] = array('title' => '店铺比例配置', 'active' => false, 'id' => 'shop_ratio');
    $tabs[] = array('title' => '商品比例配置', 'active' => false, 'id' => 'goods_ratio');
    $tabs[] = array('title' => '防超卖预警配置', 'active' => false, 'id' => 'anti_oversold');
    $tabs[] = array('title' => '操作日志', 'active' => false, 'id' => 'action_log');
}
render_control('TabPage', 'TabPage1', array(
    'tabs' => $tabs,
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>

<div id="TabPage1Contents">
    <div id="panel_baseinfo"></div>
    <div id="panel_shop_ratio"></div>
    <div id="panel_goods_ratio"></div>
    <div id="panel_anti_oversold"></div>
    <div id="panel_action_log"></div>
</div>

<?php echo load_js('comm_util.js') ?>

<script type="text/javascript">
    var scene = "<?php echo $app['scene'] ?>";
    var sync_code = '<?php echo isset($request['sync_code']) ? $request['sync_code'] : ''; ?>';
    $(document).ready(function () {
        //TAB选项卡
        $("#TabPage1 a").click(function () {
            get_tab($(this).attr('id'), 0);
        });
        $("#baseinfo").trigger('click');//打开页面默认加载基本信息页签
        document.onkeydown = function (e) {
            var ev = document.all ? window.event : e;
            if (ev.keyCode == 13) {
                e.preventDefault();
            }
        }

        if (scene == 'view') {
            $('input').attr('disabled', 'disabled');
        }
    });

    //加载选中页签内容
    function get_tab(_type, is_request) {
        var obj = $("#panel_" + _type);
        if (obj.html() != '' && is_request == 0) {
            return;
        }
        $.post('?app_act=op/inv_sync/get_tab', {type: _type, sync_code: sync_code, app_scene: scene}, function (data) {
            obj.html(data);
        });
    }

</script>