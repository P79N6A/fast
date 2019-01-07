<?php
render_control('PageHead', 'head1', array('title' => isset($app['title']) ? $app['title'] : '添加配送方式',
    'ref_table' => 'table'
));
?>
<style>
    .form_tbl {width:100%;margin-top: 10px;}
    .form_tbl td{text-align:left;padding:4px;}
    .form_tbl .tdlabel{width:120px;text-align:right};
</style>
<script src="assets/js/jquery.formautofill2.min.js"></script>

<div id="form_data_source" style="display:none;"><?php
    if (isset($response['form_data_source'])) {
        echo $response['form_data_source'];
    }
    ?>
</div>

<?php
$tabs = array(
    array('title' => '基本信息', 'active' => true, 'id' => 'baseinfo'),
);
if ($app['scene'] == 'edit' || $app['scene'] == 'view') {
    $tabs[] = array('title' => '运费设置', 'active' => false, 'id' => 'yf_set');
    $tabs[] = array('title' => '热敏设置', 'active' => false, 'id' => 'rm_set');
}
render_control('TabPage', 'TabPage1', array(
    'tabs' => $tabs,
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>

<div id="TabPage1Contents">
    <div id="panel_baseinfo"></div>
    <div id="panel_yf_set"></div>
    <div id="panel_rm_set"></div>
</div>

<?php echo load_js('comm_util.js') ?>

<script type="text/javascript">
    var scene = "<?php echo $app['scene'] ?>"; //新增、编辑
    var action = '<?php echo $response['action']; ?>'; //具体操作

    var form_data_source_v = $("#form_data_source").html(); //填充数据
    var print_type = '<?php echo $response['data']['print_type']; ?>'; //打印类型
    var express_code = "<?php echo isset($response['data']['express_code']) ? $response['data']['express_code'] : ''; ?>";
    var company_code = '<?php echo $response['data']['company_code'] ?>';
    var operation_type = '<?php echo $response['data']['operation_type'] ?>'; //无界热敏,京东承运商经营类型
    var is_refresh = 0; //是否刷新页面
    $(function () {
        //TAB选项卡
        $("#TabPage1 a").click(function () {
            get_tab($(this).attr('id'), is_refresh);
        });

        $("#baseinfo").trigger('click');//打开页面默认加载基本信息页签
        if (action === 'do_edit_add') { //热敏设置页面
            $("#rm_set").trigger('click');
            TabPage1Tab.setSelected(TabPage1Tab.getItemAt(2));
        }
        document.onkeydown = function (e) {
            var ev = document.all ? window.event : e;
            if (ev.keyCode === 13) {
                e.preventDefault();
            }
        };

        $("#yf_set").hide();

        if (action == 'do_add') {
            $("#yf_set").hide();
            $("#rm_set").hide();
        }
        if (action == 'do_edit_add') {
            $("#rm_set").addClass("active");
            $("#baseinfo").removeClass("active");
        }
        if (action == 'do_edit') {
            if (print_type == 0) {
                $("#rm_set").hide();
            }
        }
    });

    //加载选中页签内容
    function get_tab(_type, is_refresh) {
        var obj = $("#panel_" + _type);
        if (obj.html() !== '' && is_refresh === 0) {
            return;
        }
        if (_type == 'rm_set') {
            if (print_type == 1) {
                if (company_code == 'JD') {
                    _type = 'yz_rm_set'; //京东直连热敏特殊,暂时使用云栈热敏页面
                } else {
                    _type = 'zl_rm_set'; //直连热敏
                }
            } else if (print_type == 2) {
                _type = 'yz_rm_set'; //云栈热敏
            } else if (print_type == 3) {
                _type = 'wj_rm_set'; //无界热敏
            }
        }
        $.post('?app_act=base/shipping/get_tab', {_id: '<?php echo $request['_id'] ?>', type: _type, express_code: express_code, app_scene: scene}, function (data) {
            obj.html(data);
        });
    }

    function getQueryString(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
        var r = window.location.search.substr(1).match(reg);
        if (r != null)
            return unescape(r[2]);
        return null;
    }
</script>