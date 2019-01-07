<?php render_control('PageHead', 'head1', array('title'=>'产品版本列表',));?>
<?php
render_control ( 'SearchForm', 'searchForm', array (
        'cmd' => array (
            'label' => '查询',
            'label' => '查询',
            'id' => 'btn-search' 
        ),
        'fields' => array (
            array (
                'label' => '关键字',
                'title' => '版本代码/版本名称',
                'type' => 'input',
                'id' => 'keyword' 
            ),
	) 
) );
?>
<br /><br />
<?php
render_control ( 'DataTable', 'table', array (
		'conf' => array (
                    'list' => array (
                        array (
                            'type' => 'text',
                            'show' => 1,
                            'title' => 'RDS连接地址',
                            'field' => 'rem_rds_id_name',
                            'width' => '150',
                            'align' => '' 
                        ),
                        array (
                            'type' => 'text',
                            'show' => 1,
                            'title' => '绑定产品',
                            'field' => 'rem_cp_id_name',
                            'width' => '150',
                            'align' => '' 
                        ),
                    ) 
		),
                'dataset' => 'products/RdsextmanageModel::get_by_page',
                'params' => array('filter'=>array('pv_cp_id'=>$request['cpid'])),
                'queryBy' => 'searchForm',
                'idField' => 'pv_id',
		'CheckSelection'=>isset($request['multi']) && $request['multi']= 1 ? true : false,
) );
?>
<?php echo_selectwindow_js($request, 'table', array('id'=>'rem_rds_id', 'code'=>'rem_rds_id', 'name'=>'rem_rds_id_name')) ?>
