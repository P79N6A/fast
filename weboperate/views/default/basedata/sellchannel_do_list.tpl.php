<?php render_control('PageHead', 'head1',array('title'=>'产品销售渠道',
    	'links'=>array(
        array('url'=>'basedata/sellchannel/detail&app_scene=add', 'title'=>'新建销售渠道',  'pop_size'=>'500,400'),
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
            'label' => '关键字',
            'title' => '销售渠道名称',
            'type' => 'input',
            'id' => 'keyword' 
        ),
        array (
            'label' => '类型',
            'title' => '类型',
            'type' => 'select',
            'id' => 'channel_type',
            'data'=>ds_get_select_by_field('channel_type')
        ),
        array (
            'label' => '模式',
            'title' => '模式',
            'type' => 'select',
            'id' => 'channel_mode',
            'data'=>ds_get_select_by_field('channel_mode')
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
                'field' => 'channel_name',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '类型',
                'field' => 'channel_type',
                'width' => '150',
                'align' => '' ,
                'format'=>array('type'=>'map', 'value'=>ds_get_field('channel_type'))
                ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '模式',
                'field' => 'channel_mode',
                'width' => '150',
                'align' => '',
                'format'=>array('type'=>'map', 'value'=>ds_get_field('channel_mode'))
                
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '描述',
                'field' => 'channel_desc',
                'width' => '300',
                'align' => '' 
            ),
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '150',
                'align' => '',
                'buttons' => array (
                        array('id'=>'edit', 'title' => '编辑', 
                		'act'=>'basedata/sellchannel/detail&app_scene=edit', 'show_name'=>'编辑销售渠道'),
                ),
            )
        ) 
    ),
    'dataset' => 'basedata/SellchannelModel::get_sell_channel',
    'queryBy' => 'searchForm',
    'idField' => 'channel_id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
) );
?>
