<?php render_control('PageHead', 'head1', array('title'=>'增值服务列表',));
?>
<?php
render_control ( 'SearchForm', 'searchForm', array (
    'cmd' => array (
        'label' => '查询',
        'title' => '查询',
        'id' => 'btn-search' 
    ),
    'fields' => array (
        array (
            'label' => '类别',
            'title' => '类别',
            'type' => 'select',
            'id' => 'value_cat',
            'data'=>ds_get_select('valueserver_cat',1)
        ),
        array (
            'label' => '状态',
            'title' => '状态',
            'type' => 'select',
            'id' => 'value_enable',
            'data'=>ds_get_select_by_field('valuetype')
        ),
    ) 
) );
?>
<br />
<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '代码',
                'field' => 'value_code',
                'width' => '80',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '名称',
                'field' => 'value_name',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '类别',
                'field' => 'value_cat_name',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '价格',
                'field' => 'value_price',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '产品',
                'field' => 'value_cp_id_name',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '最低版本要求',
                'field' => 'value_require_version_name',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'checkbox',
                'show' => 1,
                'title' => '状态',
                'field' => 'value_enable',
                'width' => '80',
                'align' => '',
            ),
        ) 
    ),
    'dataset' => 'market/ValueModel::get_valueserver',
    'params' => array('filter'=>array('value_cp_id'=>$request['cpid'])),
    'queryBy' => 'searchForm',
    'idField' => 'value_id',
    'CheckSelection'=>isset($request['multi']) && $request['multi']= 1 ? true : false,
) );


?>
<?php echo_selectwindow_js($request, 'table', array('id'=>'value_id', 'code'=>'value_code', 'name'=>'value_name')) ?>