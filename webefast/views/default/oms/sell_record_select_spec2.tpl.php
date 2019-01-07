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
        'label' => $response['goods_spec2_rename'].'名称/代码',
        'type' => 'input',
        'id' => 'spec2_name'
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
            'title' => $response['goods_spec2_rename']."代码",
            'field' => 'spec2_code',
            'width' => '350',
            'align' => '',
        ),
        array(
            'type' => 'text',
            'show' => 1,
            'title' => $response['goods_spec2_rename']."名称",
            'field' => 'spec2_name',
            'width' => '200',
            'align' => '',
        ),
    )
    ),
        'dataset' => 'base/ShelfModel::get_goods_spec2',
        'queryBy' => 'searchForm',
        'idField' => 'spec1_id',
        'CheckSelection' => true,

    ));
    ?>
</div>