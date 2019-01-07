<style>
    .control-label{
        width: 110px!important;
    }
</style>
<?php
render_control('PageHead', 'head1',
    array('title' => '配送方式映射管理',
        'links' => array(array('url'=>'sys/wms_express_config/detail&app_scene=add', 'title' => '新增配送方式映射', 'is_pop' => false, 'pop_size' => '900,600')),
        'ref_table' => 'table'
    ));

render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
    'id' => 'btn-search',
),
    'fields' => array(
        array(
            'label' => 'wms配置名称',
            'type' => 'select_multi',
            'id' => 'wms_system_code',
            'data'=>$response['system']
        ),
        array(
            'label' => '系统配送方式',
            'type' => 'select_multi',
            'id' => 'express_code',
            'data' => $response['express'],
        ),
        array(
            'label' => 'WMS配送方式代码',
            'type' => 'input',
            'id' => 'out_express_code',
        ),
    )
));
$button = array(
    array('id' => 'edit', 'title' => '编辑', 'act' => 'sys/wms_express_config/detail&app_scene=edit', 'show_name' => '编辑'),
);

render_control('DataTable', 'table', array(
        'conf' => array(
                'list' => array(
                                array('type' => 'button',
                                    'show' => 1,
                                    'title' => '操作',
                                    'field' => '_operate',
                                    'width' => '200',
                                    'align' => '',
                                    'buttons' => $button
                                ),
                                array('type' => 'text',
                                    'show' => 1,
                                    'title' => 'wms配置名称',
                                    'field' => 'wms_config_name',
                                    'width' => '200',
                                    'align' => '',
                                ),
                                array('type' => 'text',
                                    'show' => 1,
                                    'title' => '系统配送方式',
                                    'field' => 'express_name',
                                    'width' => '200',
                                    'align' => '',
                                ),
                                array('type' => 'text',
                                    'show' => 1,
                                    'title' => 'wms配送方式代码',
                                    'field' => 'out_express_code',
                                    'width' => '200',
                                    'align' => '',
                                ),
                                array('type' => 'text',
                                    'show' => 1,
                                    'title' => '最后一次更新时间',
                                    'field' => 'lastchanged',
                                    'width' => '200',
                                    'align' => '',
                                ),
                                array('type' => 'text',
                                    'show' => 1,
                                    'title' => '日志',
                                    'field' => 'log',
                                    'width' => '200',
                                    'align' => '',
                                ),
                            )
                ),
                'dataset' => 'sys/WmsExpressConfigModel::get_by_page',
                'queryBy' => 'searchForm',
                'idField' => 'wms_id',
            )
        );
?>

<script type="text/javascript">
    parent._reload_page = function(){
        tableStore.load();
    }

</script>