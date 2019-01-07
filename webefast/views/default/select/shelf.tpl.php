<style type="text/css">
.well .control-group {
    padding-left: 1%;
    width: 45%;
}
</style>
<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_select(),
        ),
        array(
            'label' => '名称/代码',
            'type' => 'input',
            'id' => 'code_name'
        ),
    )
));
?>
<?php

render_control('DataTable', 'table', array('conf' => array('list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '库位代码',
                'field' => 'shelf_code',
                'width' => '200',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '库位名称',
                'field' => 'shelf_name',
                'width' => '200',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '所属仓库',
                'field' => 'store_name',
                'width' => '200',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'base/ShelfModel::get_by_page_detail',
    'queryBy' => 'searchForm',
    'idField' => 'shelf_id',
    'CheckSelection' => true, // 显示复选框
));
?>

<?php echo_selectwindow_js($request, 'table', array('id' => 'shelf_code', 'code' => 'shelf_code', 'name' => 'shelf_name')) ?>
