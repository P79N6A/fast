<?php render_control('PageHead', 'head1', array('title'=>'客户列表',));
?>
<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '报价模板名称',
                'field' => 'price_name',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '产品',
                'field' => 'price_cpid_name',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '营销类型',
                'field' => 'price_stid_name',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '点数',
                'field' => 'price_dot',
                'width' => '100',
                'align' => '' ,
            ),
           array (
                'type' => 'text',
                'show' => 1,
                'title' => '基础报价',
                'field' => 'price_base',
                'width' => '150',
                'align' => '' ,
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '描述',
                'field' => 'price_note',
                'width' => '150',
                'align' => '' ,
            ),
//            array (
//                'type' => 'button',
//                'show' => 1,
//                'title' => '操作',
//                'field' => '_operate',
//                'width' => '150',
//                'align' => '',
//                'buttons' => array (
//                        array('id'=>'view', 'title' => '查看', 
//                		'act'=>'market/planprice/detail&app_scene=view', 'show_name'=>'查看报价方案'),
//                        array('id'=>'edit', 'title' => '编辑', 
//                		'act'=>'market/planprice/detail&app_scene=edit', 'show_name'=>'编辑报价方案'),   
//                        
//                ),
//            )
        ) 
    ),
    'dataset' => 'market/PlanpriceModel::get_plan_info',
    'params' => array('filter'=>array('cpid'=>$request['cpid'],'stid'=>$request['stid'])),
    'queryBy' => 'searchForm',
    'idField' => 'price_id',
    'CheckSelection'=>isset($request['multi']) && $request['multi']= 1 ? true : false,
    'CascadeTable'=>array(
    	'list'=>array(
    	       array('title'=>'平台', 'field'=>'pd_pt_id_name','width' => '200',),
    	       array('title'=>'店铺数', 'field'=>'pd_shop_amount','width' => '200',),
               array('title'=>'单价', 'field'=>'pd_shop_price','width' => '200',),	               
            ),
        'page_size'=>10,
    	'url'=>get_app_url('market/planprice/get_platshop_byid'),
    	'params'=>'price_id'
    )
) );


?>
<?php echo_selectwindow_js($request, 'table', array('id'=>'price_id', 'code'=>'price_id', 'name'=>'price_name')) ?>