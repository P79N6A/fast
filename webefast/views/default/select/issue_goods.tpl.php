<style type="text/css">
    .well .control-group {
        width: 48%;
    }
    .well .control-group:first-child {
        width: 32%;
    }
    #update_time_start,#update_time_end{
        width: 100px;
    }
</style>
<?php
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
    ),
    'fields' => array(
        array(
            'label' => '',
            'type' => 'input',
            'title' => '商品名称/编码',
            'id' => 'code_name',
        ),
        array(
            'label' => '更新时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'update_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'update_time_end', 'remark' => ''),
            )
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
                'width' => '200',
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
                'title' => '商品吊牌价',
                'field' => 'price',
                'width' => '200',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'prm/GoodsModel::get_issue_goods',
    'queryBy' => 'searchForm',
    'idField' => 'goods_id',
    'params' => array('filter' => array('shop_code' => $request['shop_code'])),
    //'RowNumber'=>true,
    'CheckSelection' => true,
));
?>

<?php echo_selectwindow_js($request, 'table', array('id' => 'goods_code', 'code' => 'goods_code', 'name' => 'goods_name')) ?>
