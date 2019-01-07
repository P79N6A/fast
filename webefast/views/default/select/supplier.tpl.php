<style type="text/css">
    .well .control-group {
        padding-left: 1%;
        width: 45%;
    }
</style>
<?php
$field = array(
    array(
        'label' => '名称/代码',
        'type' => 'input',
        'id' => 'supplier_name',
        'title' => '名称/代码'
    ),
);
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => $field
));
?>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '供应商代码',
                'field' => 'supplier_code',
                'width' => '200',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '供应商名称',
                'field' => 'supplier_name',
                'width' => '200',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'base/SupplierModel::get_supplier_select',
    'queryBy' => 'searchForm',
    'idField' => 'supplier_id',
    'CheckSelection' => $request['is_multi'] == 1 ? true : false,
    'HeaderFix' => false,
    'params' => array('filter' => array('page_size' => 5, 'supplier_power' => $request['supplier_power'])),
));
?>
<?php echo_selectwindow_js($request, 'table', array('supplier_code' => 'supplier_code', 'supplier_name' => 'supplier_name')) ?>