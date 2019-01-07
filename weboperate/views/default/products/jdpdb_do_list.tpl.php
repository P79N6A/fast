<?php render_control('PageHead', 'head1',
array('title'=>'淘宝数据推送',
	'links'=>array(
        array('url'=>'products/jdpdb/detail&app_scene=add', 'title'=>'添加推送', 'is_pop'=>false, 'pop_size'=>'500,400'),
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
            'label' => '客户名称',
            'title' => '客户名称',
            'type' => 'input',
            'id' => 'kh_name',

        ),
                array (
      'label' => '客户ID',
            'title' => '客户ID',
            'type' => 'input',
            'id' => 'kh_id',

        ),
            array (
          'label' => 'rds名称',
            'title' => 'rds名称',
            'type' => 'input',
            'id' => 'rds_name',

        ),
        array (
             'label' => 'rdsid',
            'title' => 'rdsid',
            'type' => 'input',
            'id' => 'rds_id',

        ),
              array (
             'label' => '店铺昵称',
            'title' => '店铺昵称',
            'type' => 'input',
            'id' => 'nick',

        ),

)) );
?>
<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => 'ID',
                'field' => 'id',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '客户id',
                'field' => 'kh_id',
                'width' => '120',
                'align' => '' 
            ),
                      array (
                'type' => 'text',
                'show' => 1,
                'title' => '客户名称',
                'field' => 'kh_name',
                'width' => '200',
                'align' => '' 
            ),
              array (
                'type' => 'text',
                'show' => 1,
                'title' => 'rds_id',
                'field' => 'rds_id',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => 'rds名称',
                'field' => 'rds_dbname',
                'width' => '200',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '店铺昵称',
                'field' => 'nick',
                'width' => '200',
                'align' => '' 
            ),
           
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '100',
                'align' => '',
                'buttons' => array (

                   array('id'=>'rds_test', 'title' => '删除', 
                		 'show_name'=>'删除','callback'=>'del_data'), 
                ),
            )
        ) 
    ),
    'dataset' => 'products/ShopTbjdpDbModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',

) );
?>
<script>
 function del_data(_index, row) {

        var url = "?app_act=products/jdpdb/do_del&app_fmt=json";
        var param = {};
        param.id=row.id;
        $.post(url, param, function(ret) {
            if(ret.status>0){
                $('#btn-search').click();
            }else{
                BUI.Message.Alert(ret.message);
            }
        }, 'json');

    }
</script>
   