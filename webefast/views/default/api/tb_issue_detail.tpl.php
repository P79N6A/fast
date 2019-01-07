<?php
render_control('PageHead', 'head1', array('title' => $response['title']));
?>
<?php
$tabs = array(
    array('title' => '基本信息', 'active' => true, 'id' => 'baseinfo'),
);
if (($app['scene'] == 'edit' || $app['scene'] == 'view') && $response['baseinfo_status'] != 0) {
    $tabs[] = array('title' => '宝贝描述', 'active' => false, 'id' => 'cowry_desc');
    $tabs[] = array('title' => '类目属性', 'active' => false, 'id' => 'item_prop');
    $tabs[] = array('title' => '销售属性', 'active' => false, 'id' => 'sell_prop');
}

render_control('TabPage', 'TabPage1', array(
    'tabs' => $tabs,
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>

<div id="TabPage1Contents">
    <div id="panel_baseinfo"></div>
    <div id="panel_cowry_desc"></div>
    <div id="panel_item_prop"></div>
    <div id="panel_sell_prop"></div>
</div>

<?php echo load_js('comm_util.js') ?>

<script type="text/javascript">
    var scene = "<?php echo $app['scene'] ?>";
    var shop_code = "<?php echo $request['shop_code'] ?>";
    var goods_code = "<?php echo $request['goods_code'] ?>";
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
        };
    });

    //加载选中页签内容
    function get_tab(_type, is_request) {
        var obj = $("#panel_" + _type);
        if (obj.html() != '' && is_request == 0) {
            return;
        }
        var params = {shop_code: shop_code, goods_code: goods_code, type: _type, app_scene: scene, ES_frmId: '<?php echo $request['ES_frmId']; ?>'};
        if (_type == 'item_prop' || _type == 'sell_prop') {
            params.category_id = $("input[name='category_id']").val();
        }

        $.post('?app_act=api/tb_issue/get_tab', params, function (data) {
            obj.html(data);
        });
    }

</script>