<?php echo load_js('comm_util.js') ?>

<?php render_control('PageHead', 'head1',
    array('title' => '已经订购增值服务',
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
                'title' => '订购流水号',
                'field' => 'val_num',
                'width' => '120',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '服务名称',
                'field' => 'value_name',
                'width' => '150',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '服务特点',
                'field' => 'value_desc',
                'width' => '180',
                'align' => ''
            ),      
          array (
                'type' => 'text',
                'show' => 1,
                'title' => '订购时间',
                'field' => 'val_orderdate',
                'width' => '150',
                'align' => ''
            ),         
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '服务到期时间',
                'field' => 'vra_enddate',
                'width' => '150',
                'align' => ''
            ),
      
            
       
        )
    ),
    'dataset' => 'common/ServiceModel::get_by_order_page',
    'queryBy' => 'searchForm',
    'idField' => 'val_num',
) );
?>



