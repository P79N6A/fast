<?php
render_control('PageHead', 'head1', array('title' => '预售跟踪列表',
    'links' => array(),
    'ref_table' => 'table'
));
?>

<?php
$keyword['barcode'] = '商品条形码';
$keyword['goods_code'] = '商品编码';
$keyword['plan_code'] = '预售编码';
$keyword['plan_name'] = '预售名称';
$keyword = array_from_dict($keyword);
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
//        array(
//            'label' => '导出',
//            'id' => 'exprot_list',
//        )
    ),
    'fields' => array(
        array(
            'label' => array('id' => 'keyword', 'type' => 'select', 'data' => $keyword),
            'type' => 'input',
            'title' => '',
            'data' => $keyword,
            'id' => 'keyword_value',
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => $response['shop'],
        ),
    )
));
?>
<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '预售中', 'active' => true, 'id' => 'starting'),
        array('title' => '即将预售', 'active' => false, 'id' => 'no_start'),
    ),
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品名称',
                'field' => 'goods_code_name',
                'width' => '140',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品编码',
                'field' => 'goods_code',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品规格',
                'field' => 'spec',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品条形码',
                'field' => 'barcode',
                'width' => '130',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '预售店铺',
                'field' => 'plan_shop',
                'width' => '160',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '预售名称',
                'field' => 'plan_name',
                'width' => '140',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:open_presell_list(\\\'{plan_code}\\\')">{plan_name}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '预售开始时间',
                'field' => 'plan_start_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '预售数量',
                'field' => 'presell_num',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '销售数量',
                'field' => 'sell_num',
                'width' => '80',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'op/presell/PresellTrackModel::get_presell_track_by_page',
    'idField' => 'id',
    'queryBy' => 'searchForm',
    'init' => 'nodata',
//    'export' => array('id' => 'exprot_list', 'conf' => 'presell_plan_track_list', 'name' => '预售跟踪列表',), // 'export_type' => 'file'
    'CascadeTable' => array(
        'list' => array(
            array('title' => '平台SKUID', 'type' => 'text', 'width' => '150', 'field' => 'sku_id', 'format_js' => array('type' => 'function', 'value' => 'open_pt_goods')),
            array('title' => '平台规格编码', 'type' => 'text', 'width' => '150', 'field' => 'goods_barcode'),
            array('title' => '平台商品名称', 'type' => 'text', 'width' => '500', 'field' => 'goods_name', 'format_js' => array('type' => 'function', 'value' => 'get_goods_link')),
            array('title' => '平台商品属性', 'type' => 'text', 'width' => '230', 'field' => 'sku_properties_name'),
        ),
        'page_size' => 50,
        'url' => get_app_url("op/presell/get_pt_goods"),
        'params' => 'plan_code,barcode',
//                'ExpandCascadeDetail' => array(
//                    'detail_url' => get_app_url('op/presell/get_api_goods'), //查询展开详情的方法
//                    'detail_param' => 'barcode', //查询展开详情的使用的参数
//                ),
    ),
));
?>

<script type="text/javascript">
    $(function () {
        //Tab页签数据加载
        $("#TabPage1 a").click(function () {
            $('.nodata').hide();
            tableStore.load();
        });
    });

    tableStore.on('beforeload', function (e) {
        e.params.presell_status = $("#TabPage1").find(".active").find("a").attr("id");
        tableStore.set("params", e.params);
    });

    function open_presell_list(plan_code) {
        openPage('<?php echo base64_encode('?app_act=op/presell/plan_detail&plan_code') ?>' + plan_code, '?app_act=op/presell/plan_detail&plan_code=' + plan_code, '预售计划详情');
    }

    function open_pt_view(sku_id) {
        openPage('<?php echo base64_encode('?app_act=api/sys/goods/index&sku_id') ?>' + sku_id, '?app_act=api/sys/goods/index&sku_id=' + sku_id, '平台商品列表');
    }

    //获取平台商品链接
    function open_pt_goods(index, row) {
        return '<a href="javascript:open_pt_view(\'' + row.sku_id + '\')">' + row.sku_id + '</a>';
    }

    //获取平台商品链接
    function get_goods_link(index, row) {
        if (row.source == 'taobao') {
            return '<a href="http://item.taobao.com/item.htm?id=' + row.goods_from_id + '" target="_blank">' + row.goods_name + '</a>';
        } else {
            return row.goods_name;
        }
    }
</script>