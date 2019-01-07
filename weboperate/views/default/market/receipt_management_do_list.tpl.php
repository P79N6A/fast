<?php
render_control('PageHead', 'head1', array('title' => '发票信息', 'ref_table' => 'table'
));
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'title' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => '申请时间',
            'type' => 'group',
            'field' => 'daterange2',
            'width' => '36.3%',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'applied_time_start'),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'applied_time_end', 'remark' => '')
            ),
        ),
    )
));
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '全部', 'active' => false, 'id' => 'tabs_all'),
        array('title' => '待审核', 'active' => true, 'id' => 'tabs_check'),
        array('title' => '待开票', 'active' => false, 'id' => 'tabs_confirm'),
    ),
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '150',
                'align' => 'center',
                'buttons' => array(
                    array('id' => 'view', 'title' => '查看', 'act' => 'market/receipt_management/detail','show_name'=>'发票详情'),
                    array('id' => 'check', 'title' => '审核', 'callback' => 'check_receipt', 'show_cond' => 'obj.is_check != 0', 'confirm' => '您确定审核过客户资料吗？'),
                    array('id' => 'draw', 'title' => '开票', 'callback' => 'draw_receipt', 'show_cond' => 'obj.is_draw != 0', 'confirm' => '您确定开票吗？')
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '发票抬头',
                'field' => 'kh_name',
                'width' => '300',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '发票金额',
                'field' => 'receipt_money',
                'width' => '150',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '申请时间',
                'field' => 'applied_time',
                'width' => '190',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'status',
                'width' => '150',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '开票时间',
                'field' => 'check_time',
                'width' => '190',
                'align' => 'center'
            ),
        )
    ),
    'dataset' => 'market/OspReceiptModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'receipt_id',
));
?>
<script  type="text/javascript">
    $(function () {
        //TAB选项卡
        $("#TabPage1 a").click(function () {
            tableStore.load();
        });
        $("input[name='is_normal']").change(function () {
            tableStore.load();
        });
        tableStore.on('beforeload', function (e) {
            e.params.receipt_list_tab = $("#TabPage1").find(".active").find("a").attr("id");
            tableStore.set("params", e.params);
        });
    });
    //审核操作
    function check_receipt(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: "?app_act=market/receipt_management/check_receipt",
            data: {receipt_id: row.receipt_id},
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
    //开票操作
    function draw_receipt(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: "?app_act=market/receipt_management/draw_receipt",
            data: {receipt_id: row.receipt_id},
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
</script>