<style type="text/css">
.well .control-group {
    padding-left: 1%;
    width: 45%;
}
#searchForm .control-group .control-label {
    width: 30%;
}
</style>

<div class="panel">
    
</div>
<?php
$field = array(
    array(
        'label' => '供应商',
        'type' => 'input',
        'id' => 'supplier_name',
        'title' => '供应商名称/代码'
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
<div style="height:300px;overflow:scroll;overflow-x:hidden">
<?php

render_control('DataTable', 'tables', array('conf' => array('list' => array(
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
    'CheckSelection' => true, 
     'params' => array('filter' => array('page_size' => 5,'supplier_power'=>1)),
));
?>
</div>