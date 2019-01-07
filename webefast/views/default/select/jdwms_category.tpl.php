<style type="text/css">
    .well .control-group {
        width: 47%;
    }
</style>
<?php
require_lib('util/oms_util', true);
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => '三级分类代码',
            'type' => 'input',
            'id' => 'category_code'
        ),
        array(
            'label' => '三级分类名称',
            'type' => 'input',
            'id' => 'category_name'
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
                'title' => '三级分类代码',
                'field' => 'category_code',
                'width' => '300',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '三级分类名称',
                'field' => 'category_name',
                'width' => '300',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'wms/jdwms/JdwmsCategoryModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
));
?>
<?php echo_selectwindow_js($request, 'table', array('id' => 'category_code', 'code' => 'category_code', 'name' => 'category_name')) ?>
