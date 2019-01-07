<style>
    #start_time, #end_time { width: 100px; }
</style>
<?php echo load_js('comm_util.js') ?>
<?php echo load_js("pur.js", true); ?>
<?php
render_control('PageHead', 'head1', array('title' => '有货采购单回写',
    'links' => array(),
    'ref_table' => 'table'
));
?>
<?php
$keyword_type['purchase_no'] = '采购单号';
$keyword_type['delivery_no'] = '出库单号';
$keyword_type = array_from_dict($keyword_type);
$time_type = array();
$time_type['insert_time'] = '发货时间';
$time_type['delivery_time'] = '回写时间';
$time_type = array_from_dict($time_type);
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
        array(
            'label' => '导出明细',
            'id' => 'exprot_detail',
        ),
    ),
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => $response['shop'],
        ),
        array(
            'label' => '回写状态',
            'type' => 'select',
            'id' => 'is_delivery',
            'data' => array(
                array('content' => '', 'id' => '全部'),
                array('content' => '0', 'id' => '未回写'),
                array('content' => '1', 'id' => '已回写'),
            )
        ),
        array(
            'label' => array('id' => 'time_type', 'type' => 'select', 'data' => $time_type),
            'type' => 'group',
            'field' => 'time_type',
            'data' => $time_type,
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'start_time', 'class' => 'input-small'),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'end_time', 'class' => 'input-small'),
            )
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
                'width' => '100',
                'align' => '',
                'buttons' => array(
                    array('id' => 'confirm', 'title' => '回写', 'confirm'=>'确定回写？','callback' => 'confirm_delivery', 'show_cond' => "obj.is_delivery==0"),
                    array('id' => 'view', 'title' => '查询', 'callback' => 'showDetail'),
                    //  array('id' => 'edit_info', 'title' => '修改', 'callback' => 'edit_info', 'show_cond' => "obj.is_delivery==0", 'priv' => 'api/api_weipinhuijit_delivery/edit_info'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '采购单号',
                'field' => 'purchase_no',
                'width' => '150',
                'align' => '',
                //'format_js' => array(
                //    'type' => 'html',
                //    'value' => '<a href="javascript:view({id})">{delivery_no}</a>',
                //),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '出库单号',
                'field' => 'delivery_no',
                'width' => '150',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({id})">{delivery_no}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '发货时间',
                'field' => 'insert_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '配送方式',
                'field' => 'express_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '快递单号',
                'field' => 'express_no',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品总数量',
                'field' => 'numbers',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '回写状态',
                'field' => 'is_delivery',
                'width' => '90',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '回写时间',
                'field' => 'delivery_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '回写日志',
                'field' => 'delivery_log',
                'width' => '150',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'api/YohoDeliveryModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'delivery_no',
    'export' => array('id' => 'exprot_detail', 'conf' => 'api_yohojit_delivery', 'name' => '有货采购单回写', 'export_type' => 'file'),//
    'CheckSelection' => true,
    // 'customFieldTable' => 'api_weipinhuijit_delivery/table',
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>
<br /><br />
<div>
    <span style="color:red;">温馨提示：JIT出库单由有货采购单在操作生成批发销货时，自动生成。</span>
</div>
<ul id="ToolBar1" class="toolbar frontool">
    <li class="li_btns">
        <button class="button button-primary multi_confirm_delivery ">批量回写</button>
    </li>
    <div class="front_close">&lt;</div>
</ul>
<script>
    $(function () {
        function tools() {
            $(".frontool").animate({left: '0px'}, 1000);
            $(".front_close").click(function () {
                if ($(this).html() == "&lt;") {
                    $(".frontool").animate({left: '-100%'}, 1000);
                    $(this).html(">");
                    $(this).addClass("close_02").animate({right: '-10px'}, 1000);
                } else {
                    $(".frontool").animate({left: '0px'}, 1000);
                    $(this).html("<");
                    $(this).removeClass("close_02").animate({right: '0'}, 1000);
                }
            });
        }

        tools();
    })
</script>
<script type="text/javascript">
    //数据行双击打开新页面显示详情
    function showDetail(index, row) {
        var url = '?app_act=api/api_yoho_delivery/view&id=' + row.id;
        openPage(window.btoa(url), url, '有货JIT出库单详情');
    }
    function view(id) {
        var url = '?app_act=api/api_yoho_delivery/view&id=' + id
        openPage(window.btoa(url), url, '有货JIT出库单详情');
    }

//回写
    function confirm_delivery(index, row) {
        var delivery_no = row.delivery_no;
        confirm_delivery_action(delivery_no);
    }


    //读取已选中项
    function get_checked(obj, func) {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择出库单", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.delivery_no);
        }
        ids.join(',');
        func.apply(null, [ids]);
    }

//批量回写
    $(".multi_confirm_delivery").click(function () {
        get_checked($(this), function (ids) {
            BUI.Message.Confirm('确定要批量回写吗？', function () {
                var delivery_no = ids.toString();
                confirm_delivery_action(delivery_no);
            });
        });
    });


    function confirm_delivery_action(delivery_no) {
        BUI.use('bui/overlay', function (Overlay) {
            var dialog = new Overlay.Dialog({
                width: 450,
                height: 120,
                elCls: 'custom-dialog',
                bodyContent: '<p style="font-size:15px">正在回写，请稍后...</p>',
                buttons: []
            });
            dialog.show();
        });
        var data = {delivery_no: delivery_no, 'app_fmt': 'json'};
        var url = '?app_act=api/api_yoho_delivery/confirm_delivery';
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: url,
            data: data,
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    $(".bui-ext-close .bui-ext-close-x").click();
                    BUI.Message.Alert(ret.message, type);
                    location.reload();
                } else {
                    $(".bui-ext-close .bui-ext-close-x").click();
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
</script>



