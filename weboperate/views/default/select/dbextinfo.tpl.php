<?php render_control('PageHead', 'head1', array('title' => '绑定数据库',)); ?>
<?php 
render_control('SearchForm', 'searchForm1', array(
    'cmd' => array(
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
//        array (
//            'label' => 'RDS信息',
//            'type' => 'input',
//            'id' => 'rem_db_pid_name',
//            'data' => array()
//        ),
        array(
            'label' => '数据库名称',
            'type' => 'input',
            'id' => 'rem_db_name',
            'data' => array()
        ),
    )
));
?>
<br/><br/>
<?php
render_control('DataTable', 'tb_table1', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => 'RDS信息',
                'field' => 'rem_db_pid_name',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '数据库名称',
                'field' => 'rem_db_name',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '版本号',
                'field' => 'rem_db_version_name',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '创建时间',
                'field' => 'rem_db_createdate',
                'width' => '200',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'products/dbextmanageModel::get_by_page',
    'queryBy' => 'searchForm1',
    'params' => array('filter' => array('bind_kh' => '0','rds_id'=>$request['rds_id'],'dbext'=>1)),
    'idField' => 'rem_db_id',
    'CheckSelection' => false,
));
?>
<?php echo_selectwindow_js($request, 'tb_table1', array('id' => 'rem_db_name', 'code' => 'rem_db_name', 'name' => 'rem_db_version_name')) ?>
<script>
    $(function () {

    });
</script>
