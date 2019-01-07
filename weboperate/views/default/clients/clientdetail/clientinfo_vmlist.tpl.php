<?php
render_control('PageHead', 'head1', array('title' => '主机信息',
));
?>
<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'title' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => 'IP地址',
            'title' => '外网IP地址',
            'type' => 'input',
            'id' => 'ipaddr'
        ),
        array(
            'label' => '云服务商',
            'type' => 'select',
            'id' => 'ali_type',
            'data' => ds_get_select('host_cloud',2)
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
                'title' => '外网IP',
                'field' => 'ali_outip',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'input',
                'show' => 1,
                'title' => '云服务商',
                'field' => 'ali_type_name',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'input',
                'show' => 1,
                'title' => '到期时间',
                'field' => 'ali_endtime',
                'width' => '100',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'clients/ClientModel::get_vm_info',
    'queryBy' => 'searchForm',
    'params' => array('filter'=>array('page_size'=>5,'kh_id'=>$request['_id'])),
    'idField' => 'host_id',
    'CheckSelection' => false,
));
?>
