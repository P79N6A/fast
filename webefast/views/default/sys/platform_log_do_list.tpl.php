<?php render_control('PageHead', 'head1',
    array('title' => '平台日志列表',

        'ref_table' => 'table'
    ));

?>
<?php
render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
    'id' => 'btn-search',
),
    'fields' => array(
        array('label' => '平台',
            'title' => '',
            'type' => 'select',
            'id' => 'platform_code',
        	'data' => array(),
        	),
       array('label' => '店铺',
            'title' => '',
            'type' => 'input',
            'id' => 'shop_code',
        ),
    )
));

?>
<?php
render_control('DataTable', 'table', array('conf' => array('list' => array(
    
	array('type' => 'text',
        'show' => 1,
        'title' => '平台',
        'field' => 'platform_name',
        'width' => '200',
        'align' => '',
    ),
	array('type' => 'text',
        'show' => 1,
        'title' => '店铺',
        'field' => 'shop_name',
        'width' => '200',
        'align' => '',
    ),
    array('type' => 'text',
        'show' => 1,
        'title' => '业务类型',
        'field' => 'business_type',
        'width' => '200',
        'align' => '',
    ),
    array('type' => 'text',
        'show' => 1,
        'title' => '执行时间',
        'field' => 'do_time',
        'width' => '200',
        'align' => '',
    ),
    
   array('type' => 'text',
        'show' => 1,
        'title' => '执行人',
        'field' => 'do_person',
        'width' => '200',
        'align' => '',
    ),
    
    array('type' => 'text',
        'show' => 1,
        'title' => '业务操作描述',
        'field' => 'action_desc',
        'width' => '200',
        'align' => '',
    ),
    
)
),
    'dataset' => 'sys/PlatformLogModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
));

?>

<script type="text/javascript">

$(function(){
	$(".control-label").css("width","110px");
})

</script>