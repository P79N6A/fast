<style type="text/css">
    .well .control-group {
        padding-left: 1%;
        width: 49%;
    }
    .form-horizontal .control-label {
        width: 130px;
    }
</style>
<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'show_row' => 2,
    'fields' => array(
        array(
            'label' => '商品编码/商品条形码',
            'type' => 'input',
            'id' => 'goods_code_barcode',
            'width' => '300',
        ),
        array(
            'label' => '商品名称',
            'type' => 'input',
            'id' => 'goods_name',
            'width' => '300',
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
                'title' => '商品编码',
                'field' => 'goods_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品条形码',
                'field' => 'barcode',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '国标码',
                'field' => 'gb_code',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品名称',
                'field' => 'goods_name',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '规格1',
                'field' => 'spec1_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '规格2',
                'field' => 'spec2_name',
                'width' => '80',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'prm/GoodsBarcodeModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'goods_code_barcode',
    'CheckSelection' => $request['is_multi']==1?true:false,
    'params' => array('filter' => array('is_effective' => 1))
));
?>
<?php echo_selectwindow_js($request, 'table', array('goods_code' => 'goods_code', 'barcode' => 'barcode')) ?>
<script>
    $('#goods_name').parent().prev().width('83px');
</script>

