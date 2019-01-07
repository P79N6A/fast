<style>
    .print_type_btn{ border:1px solid #efefef; background:#FFF; color:#666; margin-right:2px; border-radius:3px;}
    .print_type_select{background:#1695ca; color:#FFF; border-color:#1695ca;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '配送方式',
    'links' => array(
        array('url' => 'base/shipping/detail&app_scene=add&action=do_add', 'title' => '添加配送方式', 'is_pop' => true, 'pop_size' => '800,500'),
    ),
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => '代码',
            'title' => '配送方式代码',
            'type' => 'input',
            'id' => 'express_code'
        ),
        array(
            'label' => '名称',
            'title' => '配送方式名称',
            'type' => 'input',
            'id' => 'express_name'
        ),
        array(
            'label' => '是否启用',
            'title' => '',
            'type' => 'select',
            'data' => ds_get_select_by_field('clerkstatus'),
            'id' => 'status'
        ),
    )
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
                'width' => '80',
                'align' => 'center',
                'buttons' => array(
                    array('id' => 'enable', 'title' => '启用',
                        'callback' => 'do_enable', 'show_cond' => 'obj.status != 1'),
                    array('id' => 'disable', 'title' => '停用',
                        'callback' => 'do_disable', 'show_cond' => 'obj.status == 1'),
                    array('id' => 'edit_ship', 'title' => '编辑', 'act' => '/base/shipping/detail&app_scene=edit&action=do_edit', 'show_name' => '编辑配送方式'),
                    array('id' => 'edit', 'title' => '热敏设置', 'act' => '/base/shipping/detail&app_scene=edit&action=do_edit_add', 'show_name' => '热敏设置', 'show_cond' => '(obj.print_type != 0 && obj.company_code!="SFC") || (obj.company_code =="SFC" && obj.print_type == 1)'
                    )
                )
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'status',
                'width' => '50',
                'align' => 'center',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '代码',
                'field' => 'express_code',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '名称',
                'field' => 'express_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '所属快递公司',
                'field' => 'company_html',
                'width' => '130',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '打印类型',
                'field' => 'print_type_html',
                'width' => '320',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '普通模板',
                'field' => 'pt_id_html',
                'width' => '180',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '到付模板',
                'field' => 'df_id_html',
                'width' => '180',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '热敏模板',
                'field' => 'rm_id_html',
                'width' => '180',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'remark',
                'width' => '100',
                'align' => '',
                'format' => array(
                    'type' => 'truncate',
                    'value' => 20
                )
            )
        )
    ),
    'dataset' => 'base/ShippingModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'express_id',
));
?>

<script type="text/javascript">
    function do_enable(_index, row) {
        _do_set_active(_index, row, 'enable');
    }
    function do_disable(_index, row) {
        _do_set_active(_index, row, 'disable');
    }
    function _do_set_active(_index, row, active) {
        var param = {express_id: row.express_id, type: active};
        $.post('<?php echo get_app_url('base/shipping/update_active'); ?>', param, function (ret) {
            if (ret.status === '1') {
                BUI.Message.Tip(ret.message, 'success');
                tableStore.load();
            } else {
                BUI.Message.Alert(ret.message, 'error');
            }
        }, 'json');
    }

    function changeType(express_id, _type) {
        var param = {express_id: express_id, type: _type};
        $.post('<?php echo get_app_url('base/shipping/update_type'); ?>', param, function (ret) {
            if (ret.status === '1') {
                if (_type !== 0) {
                    BUI.Message.Alert("已设置为热敏打印，请设置热敏模板及热敏账号!");
                } else {
                    BUI.Message.Tip(ret.message, 'success');
                }
                tableStore.load();
            } else {
                BUI.Message.Alert(ret.message, 'error');
            }
        }, 'json');
    }

    function changeTpl(express_id, value, type) {
        var param = {express_id: express_id, value: value, type: type};
        $.post('<?php echo get_app_url('base/shipping/update_tpl'); ?>', param, function (ret) {
            if (ret.status === '1') {
                BUI.Message.Tip(ret.message, 'success');
                tableStore.load();
            } else {
                BUI.Message.Alert(ret.message, 'error');
            }
        }, 'json');
    }

    /**
     * 修改打印机
     * @param _index
     * @param row
     */
    function modify_printer(_index, row) {
        var express_id = row.express_id;
        var LODOP = getLodop();
        var printer_count = LODOP.GET_PRINTER_COUNT();
        if (printer_count < 1) {
            BUI.Message.Alert('该系统未安装打印设备,请添加相应的打印设备');
            return;
        }
        //选择打印机
        var p = LODOP.SELECT_PRINTER();
        if (p < 0) {
            return;
        }
        //获取打印机名称
        var printer_name = LODOP.GET_PRINTER_NAME(p);
        var params = {express_id: express_id, printer_name: printer_name};

        $.post('?app_act=sys/shipping/modify_printer&app_fmt=json', params, function (data) {
            var ret = $.parseJSON(data);
            BUI.Message.Alert(ret.message);
            window.location.reload();
        });
    }
</script>