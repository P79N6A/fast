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
if (isset($response['store_code']) && $response['store_code'] != '') {
    $field = array(
        array(
            'label' => '库位名称/代码',
            'type' => 'input',
            'id' => 'code_name',
        ),
        array(
            'type' => 'hidden',
            'id' => 'store_code',
            'value'=>$response['store_code']
        ),
    );
}else{
    $field = array(
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_select(),
        ),
        array(
            'label' => '名称/代码',
            'type' => 'input',
            'id' => 'code_name'
        ),
    );
}
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
                'title' => '库位代码',
                'field' => 'shelf_code',
                'width' => '200',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '库位名称',
                'field' => 'shelf_name',
                'width' => '200',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '所属仓库',
                'field' => 'store_name',
                'width' => '200',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'base/ShelfModel::get_by_page_detail',
    'queryBy' => 'searchForm',
    'idField' => 'shelf_id',
    'CheckSelection' => true, 
     'params' => array('filter' => array('page_size' => 5,'store_code'=>$response['store_code'])),
));
?>
</div>