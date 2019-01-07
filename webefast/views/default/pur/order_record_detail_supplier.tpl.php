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
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '80',
                'align' => '',
                'buttons' => array(
                    array('id' => 'confirm', 'title' => '确定', 'callback' => 'confirm'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '供应商代码',
                'field' => 'supplier_code',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '供应商名称',
                'field' => 'supplier_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '折扣',
                'field' => 'rebate',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '联系人',
                'field' => 'contact_person',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '联系电话',
                'field' => 'tel',
                'width' => '100',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'base/SupplierModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'supplier_id',
    'params' => array('filter' => array('supplier_power' => 1)),
        //'RowNumber'=>true,
        //'CheckSelection'=>true,
));
?>
<script type="text/javascript">
    function confirm(_index, row) {
        parent.add_c(row.supplier_code);
        ui_closePopWindow("<?php echo $request['ES_frmId'] ?>")
    }

</script>