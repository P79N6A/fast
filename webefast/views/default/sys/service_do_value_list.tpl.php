<?php echo load_js('comm_util.js') ?>

<?php render_control('PageHead', 'head1',
    array('title' => '增值服务订购',
        'links' => array(
            array('url' => '', 'title' => '在线咨询'),
        ),
        'ref_table' => 'table'
    ));

?>

<?php
render_control ( 'SearchForm', 'searchForm', array (
    'cmd' => array (
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array (          
        array (
            'label' => '分类',
            'type' => 'select_multi',
            'id' => 'vc_code',
            'data'=>$response['category'],
        ),
    		array (
    				'label' => '服务名称',
    				'type' => 'input',
    				'id' => 'value_name'
    		),  		    		    		    		   		
    )
) );
?>

<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '名称',
                'field' => 'value_name',
                'width' => '150',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '特点',
                'field' => 'value_desc',
                'width' => '150',
                'align' => ''
            ),  
                      array (
                'type' => 'text',
                'show' => 1,
                'title' => '价格',
                'field' => 'value_price',
                'width' => '150',
                'align' => ''
            ),   
                      array (
                'type' => 'text',
                'show' => 1,
                'title' => '周期',
                'field' => 'value_cycle',
                'width' => '150',
                'align' => ''
            ),   
     	array('type' => 'button',
        'show' => 1,
        'title' => '操作',
        'field' => '_operate',
        'width' => '150',
        'align' => '',
        'buttons' => array(
            array('id' => 'view', 'title' => '立即订购',
                'act' => '', 'show_name' => '立即订购'),
        ),
    ),
            
       
        )
    ),
    'dataset' => 'common/ServiceModel::get_by_value_page',
    'queryBy' => 'searchForm',
    'idField' => 'value_id',
) );
?>



