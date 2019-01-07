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
                'field' => 'category_code',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '名称',
                'field' => 'category_name',
                'width' => '200',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'prm/CategoryModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'category_id',
    'HeaderFix'=>false,
    'CheckSelection' => $request['is_multi']==1?true:false,
    'params' => array('filter' => array('is_effective' => 1))
));
?>
<?php echo_selectwindow_js($request, 'table', array('category_code' => 'category_code', 'category_name' => 'category_name')) ?>