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
            'label' => '名称/编号',
            'type' => 'input',
            'id' => 'code_name'
        ),
        array(
            'label' => '分销商分类',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'custom_grade',
            'data' => load_model('base/CustomGradesModel')->get_all_grades(2),
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
                'title' => '分销商编号',
                'field' => 'custom_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分销商名称',
                'field' => 'custom_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '注册时间',
                'field' => 'create_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分销商分类',
                'field' => 'grade_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分销商类型',
                'field' => 'custom_type_name',
                'width' => '120',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'base/CustomModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'custom_id',
    'CheckSelection' => $request['is_multi']==1?true:false,
    'params' => array('filter' => array('is_effective' => 1,'custom_type' => 'pt_fx'))
));
?>

<?php echo_selectwindow_js($request, 'table', array('id' => 'custom_code', 'code' => 'custom_code', 'name' => 'custom_name')) ?>