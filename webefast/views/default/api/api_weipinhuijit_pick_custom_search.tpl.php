<style type="text/css">
    .well .control-group {
        padding-left: 1%;
        width: 49%;
    }
    .form-horizontal .control-label {
        width: 100px;
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
            'label' => '分销商编码',
            'type' => 'input',
            'id' => 'custom_code',
            'width' => '340',
        ),
        array(
            'label' => '分销商名称',
            'type' => 'input',
            'id' => 'custom_name',
            'width' => '340',
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
                'title' => '分销商编码',
                'field' => 'custom_code',
                'width' => '300',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分销商名称',
                'field' => 'custom_name',
                'width' => '300',
                'align' => ''
            )
        )
    ),
    'dataset' => 'api/WeipinhuijitPickModel::get_by_custom',
    'queryBy' => 'searchForm',
    'idField' => 'custom_code',
    'CheckSelection' => $request['is_multi']==1?true:false,
    'params' => array('filter' => array('is_effective' => 1))
));
?>
<?php echo_selectwindow_js($request, 'table', array('custom_code' => 'custom_code', 'custom_name' => 'custom_name')) ?>


