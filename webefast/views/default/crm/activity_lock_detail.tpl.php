<style type="text/css">
    .well {
        min-height: 50px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '实物锁定详情查询',
    'ref_table' => 'table'
));
?>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品条形码',
                'field' => 'barcode',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '活动名称',
                'field' => 'activity_name',
                'width' => '140',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '实物锁定（占用数）',
                'field' => 'update_num',
                'width' => '65',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'crm/ActivityGoodsModel::inv_lock_detail',
    'queryBy' => 'searchForm',
    'idField' => 'goods_inv_id',
    'params' => array(
        'filter' => array('sku' => $response['sku'], 'start_time' => $response['start_time'], 'end_time' => $response['end_time'], 'activity_code' => $response['activity_code'], 'shop_code' => $response['shop_code']),
    ),
));
?>
<div>
    <div id='table'></div>
</div>
<script type="text/javascript">

</script>

