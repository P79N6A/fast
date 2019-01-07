<?php

render_control('PageHead', 'head1', array('title' => '库存分配策略',
    'ref_table' => 'table'
));
?>
<?php

$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type = array_from_dict($keyword_type);

render_control('SearchForm', 'searchForm', array(
    'buttons' => array(array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
        array(
            'label' => '导出',
            'id' => 'exprot_list',
        ),
    ),
//    'show_row' => 2,
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '支持模糊查询',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
    )
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
                'field' => 'goods_name',
                'width' => '120',
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
                'title' => '条形码',
                'field' => 'barcode',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => $response['spec']['goods_spec1'],
                'field' => 'spec1_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => $response['spec']['goods_spec2'],
                'field' => 'spec2_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分类',
                'field' => 'category_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '品牌',
                'field' => 'brand_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '来源策略名称',
                'field' => 'sync_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '系统指定销售店铺',
                'field' => 'warn_goods_sell_shop',
                'width' => '120',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'op/InvSyncAntiOversoldModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'warn_goods_id',
    'export' => array('id' => 'exprot_list', 'conf' => 'warn_goods_list', 'name' => '预警商品'),
    'CascadeTable' => array(
        'list' => array(
            array('title' => '仓库代码', 'type' => 'text', 'width' => '180', 'field' => 'store_code'),
            array('title' => '仓库名称', 'type' => 'text', 'width' => '180', 'field' => 'store_name'),
            array('title' => '可用库存', 'type' => 'text', 'width' => '160', 'field' => 'available_inv'),
            array('title' => '锁定库存', 'type' => 'text', 'width' => '160', 'field' => 'spec1_name'),
            array('title' => '实物库存', 'type' => 'text', 'width' => '160', 'field' => 'spec2_code'),
            array('title' => '缺货库存', 'type' => 'text', 'width' => '160', 'field' => 'spec2_name'),
        ),
        'page_size' => 10,
        'url' => get_app_url('op/inv_sync/get_warn_goods&app_fmt=json'),
        'params' => 'warn_goods_id'
    ),
));
?>
