<?php
$list = array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分销商',
                'field' => 'custom_name',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品名称',
                'field' => 'goods_name',
                'width' => '160',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => $request['goods_spec']['goods_spec1'],
                'field' => 'spec1_name',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => $request['goods_spec']['goods_spec2'],
                'field' => 'spec2_name',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品条形码',
                'field' => 'barcode',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品编码',
                'field' => 'goods_code',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '品牌',
                'field' => 'brand_name',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分类',
                'field' => 'category_name',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '季节',
                'field' => 'season_name',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '年份',
                'field' => 'year_name',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '出库数量',
                'field' => 'out_num',
                'width' => '80',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '出库商品总金额',
                'field' => 'out_money',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退货数量',
                'field' => 'in_num',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退货商品总金额',
                'field' => 'in_money',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '吊牌价',
                'field' => 'sell_price',
                'width' => '100',
                'align' => ''
            ),
        );
if(!empty($response['proprety'])) {
    foreach($response['proprety'] as $val) {
        $list[] = array(
            'title' => $val['property_val_title'],
            'show' => 1,
            'type' => 'text',
            'width' => '80',
            'field' => $val['property_val']
        );
    }
}
render_control('DataTable', 'sku_table', array(
    'conf' => array(
        'list' => $list
    ),
    'dataset' => 'wbm/WbmReportModel::get_report_detail_by_page',
    //   'queryBy' => 'searchForm',
    'idField' => 'custom_code_sku',
    // 'export' => array('id' => 'exprot_list', 'conf' => 'purchase_analyse_view_list', 'name' => '采购分析明细'),
    'init'=>'nodata',
));
?>
