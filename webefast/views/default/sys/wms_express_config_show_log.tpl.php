
<?php
render_control('PageHead', 'head1',
    array('title' => '配送方式映射管理日志',
        'ref_table' => 'table'
    ));
render_control('DataTable', 'table', array(
        'conf' => array(
            'list' => array(
                array('type' => 'text',
                    'show' => 1,
                    'title' => '操作人',
                    'field' => 'user_name',
                    'width' => '200',
                    'align' => '',
                ),
                array('type' => 'text',
                    'show' => 1,
                    'title' => '操作名称',
                    'field' => 'action_name',
                    'width' => '200',
                    'align' => '',
                ),
                array('type' => 'text',
                    'show' => 1,
                    'title' => '操作时间',
                    'field' => 'lastchanged',
                    'width' => '200',
                    'align' => '',
                ),
                array('type' => 'text',
                    'show' => 1,
                    'title' => '操作描述',
                    'field' => 'action_note',
                    'width' => '200',
                    'align' => '',
                ),
            )
        ),
        'dataset' => 'sys/WmsExpressActionModel::get_by_page',
        'params'=>array('filter'=>array('wms_id'=>$request['_id']))
    )
);
?>

<script type="text/javascript">
    parent._reload_page = function(){
        tableStore.load();
    }

</script>