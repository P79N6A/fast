<?php
render_control('PageHead', 'head1', array('title' => '快递单模板列表',
    'links' => array(
        array('type' => 'js', 'js' => 'set_cainiao_print_tpl()', 'title' => '初始化编辑云模板', 'color' => 'button-primary hint-msg'),
        array('type' => 'js', 'js' => 'get_cainiao_print_software()', 'title' => '下载菜鸟云打印组件'),
        array('url' => 'sys/express_tpl/shop_selector', 'title' => '下载&更新菜鸟云模板', 'is_pop' => true, 'pop_size' => '400,200'),
    ),
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
        'id' => 'btn-search',
    ),
    'fields' => array(
        array('label' => '模版名称',
            'title' => '',
            'type' => 'input',
            'id' => 'print_templates_name',
        ), array(
            'label' => '快递公司',
            'type' => 'select',
            'id' => 'company_code',
            'data' => ds_get_select('express_company', 1),
        ),
    )
));
?>
<?php
render_control('DataTable', 'table', array('conf' => array('list' => array(
            array('type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '300',
                'align' => '',
                'buttons' => array(
                    array('id' => 'edit_goods_info',
                        'title' => '商品信息配置',
                        'act' => 'pop:sys/express_tpl/edit_goods_info',
                        'pop_size' => '600,500',
                        'show_cond' => 'obj.is_buildin == 3',),
                    array('id' => 'delete', 'title' => '删除',
                        'callback' => 'do_delete',
                        'show_cond' => 'obj.is_buildin == 0 || obj.is_buildin == 3',
                        'confirm' => '确定要删除这条辛辛苦苦录入的数据吗？'),
                    array('id' => 'edit', 'title' => '编辑模版', 'callback' => 'do_edit', 'show_name' => '编辑')
                )
            ),
            array('type' => '   text',
                'show' => 1,
                'title' => '模版名称',
                'field' => 'print_templates_name',
                'width' => '200',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '快递公司',
                'field' => 'company_name',
                'width' => '200',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '打印机',
                'field' => 'printer',
                'width' => '200',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '类型',
                'field' => 'is_buildin_name',
                'width' => '200',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'sys/ExpressTplModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'print_templates_id',
));
?>
<?php
if ($response['new_clodop_print'] == 1) {
    echo "<script src='http://127.0.0.1:8000/CLodopfuncs.js'></script>";
}
?>
<script type="text/javascript">
    var new_clodop_print = '<?php echo $response['new_clodop_print'] ?>';
    $(function () {
        BUI.use('bui/tooltip', function (Tooltip) {
            var t = new Tooltip.Tip({
                trigger: '.hint-msg',
                alignType: 'bottom-left',
                offset: 10,
                title: '&nbsp;&nbsp;&nbsp;&nbsp;点击按钮后登陆淘宝账号，选择身份为"我是商家"，选择服务为"宝塔efast365电商ERP管理软件"，添加所需快递模板，保存并发布后，再点击"下载&更新菜鸟云模板"将编辑好的云模板下载到系统中。',
                elCls: 'tips tips-no-icon',
                titleTpl: '<p style="width:230px;height:150px;color:#1695CA;font:bold 15px 微软雅黑">{title}</p>'
            });
            t.render();
        });

        $(".control-label").css("width", "110px");
    });

    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('sys/express_tpl/do_delete');?>', 
            data: {id: row.print_templates_id, is_buildin: row.is_buildin},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功！', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    var LODOP;
    function DisplayDesign(template_body) {
        CreatePage(template_body);
        LODOP.SET_SHOW_MODE("DESIGN_IN_BROWSE", 1);
        LODOP.PRINT_DESIGN();
    }

    function CreatePage(template_body) {
        console.log(template_body);
        eval(template_body);
    }

    function do_edit(_index, row) {
        //云打印模板跳转编辑页面
        if (row.is_buildin == 3) {
            window.open('http://cloudprint.cainiao.com/cloudprint');
        } else if (new_clodop_print == 1) {
            var url = '?app_act=sys/express_tpl/edit_clodop&print_templates_id=' + row.print_templates_id;
            openPage(window.btoa(url), url, '编辑模板');
        } else {
            var _id = row.print_templates_id;
            var url = '?app_act=sys/print_templates/edit_express&_id=' + _id;
            openPage(window.btoa(url), url, '编辑模板');
        }
    }

    function do_enable(_index, row) {
        _do_set_active(_index, row, 'enable');
    }

    function do_disable(_index, row) {
        _do_set_active(_index, row, 'disable');
    }

    function _do_set_active(_index, row, active) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('sys/sms_tpl/update_active');
?>',
            data: {id: row.id, type: active},
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

    function get_cainiao_print_software() {
        window.open('https://www.cainiao.com/markets/cnwww/print');
    }

    function set_cainiao_print_tpl() {
        window.open('https://cloudprint.cainiao.com');
    }

    function do_edit_goods_info(_index, row) {
        $.ajax({type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('sys/express_tpl/do_edit_goods_info'); ?>',
            data: {id: row.print_templates_id, is_buildin: row.is_buildin},
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
</script>