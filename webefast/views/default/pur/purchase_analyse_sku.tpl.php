<?php
$list = array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '供应商',
                'field' => 'supplier_name',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '业务日期',
                'field' => 'record_time',
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
                'title' => '商品编码',
                'field' => 'goods_code',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '规格1',
                'field' => 'spec1_name',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '规格2',
                'field' => 'spec2_name',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品条形码',
                'field' => 'barcode',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '品牌',
                'field' => 'brand_name',
                'width' => '100',
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
                'title' => '采购数量',
                'field' => 'pur_num',
                'width' => '80',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '采购金额',
                'field' => 'pur_money',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退回数量',
                'field' => 'return_num',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退货金额',
                'field' => 'return_money',
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
        'list' => $list,
    ),
    'dataset' => 'pur/PurReportModel::get_report_detail_by_page',
    'params' => array('filter' => array('user_id' => $response['user_id'],'is_sort'=>$response['is_sort'])),
 //   'queryBy' => 'searchForm',
    'idField' => 'supplier_code_sku',
   // 'export' => array('id' => 'exprot_list', 'conf' => 'purchase_analyse_view_list', 'name' => '采购分析明细'),
    'init'=>'nodata',
));
?>
