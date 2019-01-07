<style>
    #keyword_type {
        width: 100px;
    }
</style>
<?php render_control('PageHead', 'head1', array('title' => 'RDS列表',)); ?>
<?php
$host_info = array();
$host_info['client_name'] = '云服务商';
$host_info['dbname'] = 'RDS实例名';
$host_info['rds_link'] = 'RDS连接地址';
$host_info['rds_notes'] = '备注';
$host_info = array_from_dict($host_info);
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $host_info),
            'type' => 'input',
            'title' => '',
            'data' => $host_info,
            'id' => 'keyword',
        ), array(
            'label' => '模式',
            'title' => '',
            'type' => 'select',
            'id' => 'ali_share_type',
            'data' => ds_get_select_by_field('share_type')
        ),
        array(
            'label' => '用途',
            'title' => '服务器用途',
            'type' => 'select',
            'id' => 'server_use',
            'data' => ds_get_select_by_field('serveruse')
        ),
        array(
            'label' => '型号',
            'title' => '型号',
            'type' => 'select',
            'id' => 'rds_server_model',
            'data' => ds_get_select('db_model', 2)
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
                'title' => '云服务商',
                'field' => 'rds_dbtype_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '型号',
                'field' => 'rds_server_model_name',
                'width' => '60',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => 'RDS实例',
                'field' => 'rds_dbname',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => 'RDS连接地址',
                'field' => 'rds_link',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '模式',
                'field' => 'ali_share_type_name',
                'width' => '60',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '用途',
                'field' => 'rds_server_use',
                'width' => '100',
                'align' => '',
                'format_js'=>array('type'=>'map', 'value'=>ds_get_field('serveruse'))
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '到期时间',
                'field' => 'rds_endtime',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'checkbox',
                'show' => 1,
                'title' => '状态',
                'field' => 'rds_state',
                'width' => '50',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'rds_notes',
                'width' => '100',
                'align' => ''
            ),

        )
    ),
    'dataset' => 'basedata/RdsModel::get_rds_info',
    //'params' => array('filter' => array('kh_id' => 0)),
    'queryBy' => 'searchForm',
    'idField' => 'rds_id',
    'init' => 'nodata',
//    'CheckSelection' => isset($request['multi']) && $request['multi'] = 1 ? true : false,
));
?>
<?php echo_selectwindow_js($request, 'table', array('id' => 'rds_id', 'code' => 'rds_id', 'name' => 'rds_user')) ?>
<script>
    $(function () {
        var type =<?php echo $request['type']; ?>;
        if (type == '1') {
            $("#ali_share_type").val(type);
        } else if (type == '2') {
            $("#ali_share_type").val(type);
        }
        $('#btn-search').click();
    });
</script>