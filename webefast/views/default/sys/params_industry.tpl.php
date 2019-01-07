<?php
render_control('PageHead', 'head1', ['title' => '行业特性设置']);
?>
<?php
$tabs = [
    ['title' => '通用', 'active' => true, 'id' => 'industry_currency'],
//    array('title' => '化妆品', 'active' => false, 'id' => 'cosmetic'),
//    array('title' => '医药', 'active' => false, 'id' => 'medicine')
];
foreach ($response['data'] as $key => $value) {
    $tabs[] = ['title' => $value['param_name'], 'active' => false, 'id' => $key];
}

render_control('TabPage', 'TabPage1', array(
    'tabs' => $tabs,
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>

<div id="TabPage1Contents">
    <div id="panel_industry_currency"></div>
    <?php
    foreach ($response['data'] as $key => $value) {
        echo "<div id=\"panel_{$key}\"></div>";
    }
    ?>
</div>

<?php echo load_js('comm_util.js') ?>

<script type="text/javascript">
    var activetab = '<?php echo $response['activetab'] ?>';
    $(function () {
        //TAB选项卡
        $("#TabPage1 a").click(function () {
            get_tab($(this).attr('id'), 0);
        });
        //打开页面默认加载基本信息页签
        $("#industry_" + activetab).trigger('click');
        var tabs = {industry_currency: 0, industry_clothing: 1};
        TabPage1Tab.setSelected(TabPage1Tab.getItemAt(tabs["industry_" + activetab]));

        document.onkeydown = function (e) {
            var ev = document.all ? window.event : e;
            if (ev.keyCode == 13) {
                e.preventDefault();
            }
        };
    });

    //加载点击页签内容
    function get_tab(_type, is_request) {
        var obj = $("#panel_" + _type);
        if (obj.html() != '' && is_request == 0) {
            return;
        }
        $.post('?app_act=sys/params/get_industry_tab', {type: _type}, function (data) {
            obj.html(data);
        });
    }

</script>