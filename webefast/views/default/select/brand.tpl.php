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
            'label' => '名称/代码',
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
                'title' => '代码',
                'field' => 'brand_code',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '名称',
                'field' => 'brand_name',
                'width' => '200',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'prm/BrandModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'brand_id',
    'HeaderFix'=>false,
//    'CheckSelection' => $request['is_multi']==1?true:false,
    'CheckSelection' => true,
    'params' => array('filter' => array('is_effective' => 1))
));
?>
<?php echo_selectwindow_js($request, 'table', array('brand_code' => 'brand_code', 'brand_name' => 'brand_name')) ?>