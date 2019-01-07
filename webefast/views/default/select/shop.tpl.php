<style type="text/css">
    .well .control-group {
        width: 100%;
    }
</style>
<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => '名称/编码',
            'type' => 'input',
            'id' => 'code_name'
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
                'title' => '店铺代码',
                'field' => 'shop_code',
                'width' => '45%',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺名称',
                'field' => 'shop_name',
                'width' => '55%',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'base/ShopModel::get_oms_store',
    'queryBy' => 'searchForm',
    'idField' => 'shop_id',
    'params' => array('filter' => array('shop_type' => $request['shop_type'], 'sale_channel_code' => isset($request['sale_channel_code']) ? $request['sale_channel_code'] : '')),
        //'RowNumber'=>true,
        //'CheckSelection'=>true,
));
?>

<?php echo_selectwindow_js($request, 'table', array('id' => 'shop_code', 'code' => 'shop_code', 'name' => 'shop_name')) ?>
