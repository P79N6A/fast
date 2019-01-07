<?php render_control('PageHead', 'head1',array('title'=>'问题提单列表',
    	'links'=>array(
            array('url'=>'servicenter/productissue/detail&app_scene=add', 'title'=>'新建问题提单',  'pop_size'=>'500,400'),
            array('url'=>'assets/img/wttd.png','type'=>'url','title'=>'查看提单流程图'),
	),
	'ref_table'=>'table'    
    ));?>
<?php
render_control ( 'SearchForm', 'searchForm', array (
    'cmd' => array (
        'label' => '查询',
        'title' => '查询',
        'id' => 'btn-search' 
    ),
    'fields' => array (
        array (
            'label' => '提单编号',
            'title' => '编号',
            'type' => 'input',
            'id' => 'sue_number' 
        ),
        array (
            'label' => '提单标题',
            'title' => '标题模糊搜索',
            'type' => 'input',
            'id' => 'sue_title' 
        ),
        array (
            'label' => '产品',
            'type' => 'select',
            'id' => 'product',
            'class'=>'input-large',
            'data'=>ds_get_select('chanpin',2)
        ),
        array (
            'label' => '状态',
            'type' => 'select_multi',
//            'id' => 'issue_status',
            'field'=>'issue_status',
            'data'=>ds_get_select_by_field('issue_type',0)
        ),
        array (
            'label' => '提单人',
            'title' => '单据提交人',
            'type' => 'input',
            'id' => 'sue_user',
        ),
        array (
            'label' => '受理人',
            'title' => '受理人',
            'type' => 'input',
            'id' => 'sue_idea_user',
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
                'title' => '提单编号',
                'field' => 'sue_number',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '问题标题',
                'field' => 'sue_title',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '客户名称',
                'field' => 'sue_kh_id_name',
                'width' => '200',
                'align' => '' ,
//                'format'=>array('type'=>'map', 'value'=>ds_get_field('channel_type'))
                ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '产品名称',
                'field' => 'sue_cp_id_name',
                'width' => '120',
                'align' => '',
                
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '提单人',
                'field' => 'user_name',
                'width' => '120',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '提交时间',
                'field' => 'sue_submit_time',
                'width' => '150',
                'align' => '' 
            ), 
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '受理时间',
                'field' => 'sue_accept_time',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '解决时间',
                'field' => 'sue_solve_time',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'sue_status',
                'width' => '120',
                'align' => '',
               'format_js'=>array('type'=>'map', 'value'=>ds_get_field('issue_type'))
            ),
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '150',
                'align' => '',
                'buttons' => array (
                        array('id'=>'view', 'title' => '查看', 
                		'act'=>'servicenter/productissue/detail&app_scene=view', 'show_name'=>'查看问题提单'),
                        array('id'=>'edit', 'title' => '编辑', 
                		'act'=>'servicenter/productissue/detail&app_scene=edit', 'show_name'=>'编辑问题提单','show_cond'=>"obj.sue_status == 1 && obj.sue_user ==".CTX()->get_session("user_id")),   
                        array('id'=>'comedit', 'title' => '强制编辑', 
                		'act'=>'servicenter/productissue/do_comedit', 'show_name'=>'编辑问题提单','priv'=>'servicenter/productissue/do_comedit'),
                ),
            )
        ) 
    ),
    'dataset' => 'servicenter/ProductissueModel::get_product_issue',
    'queryBy' => 'searchForm',
    'idField' => 'sue_number',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
     'events'=>array(
            'rowdblclick'=>array('ref_button'=>'view')),
) );
?>
