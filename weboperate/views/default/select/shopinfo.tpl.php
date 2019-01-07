<?php render_control('PageHead', 'head1', array('title'=>'店铺列表',));?>
<?php
render_control ( 'SearchForm', 'searchForm', array (
    'cmd' => array (
        'label' => '查询',
        'title' => '查询',
        'id' => 'btn-search' 
    ),
    
    'fields' => array (
        array (
            'label' => '店铺名称',
            'title' => '店铺',
            'type' => 'input',
            'id' => 'shopname' 
        ),
        array (
            'label' => '平台类型',
            'title' => '店铺',
            'type' => 'select',
            'id' => 'platform' ,
            'data'=>ds_get_select('shop_platform',2)
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
                'title' => '店铺名称',
                'field' => 'sd_name',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '平台类型',
                'field' => 'sd_pt_id_name',
                'width' => '100',
                'align' => '' 
            ),    
        ) 
    ),
    'dataset' => 'clients/ShopModel::get_shop_info',
    'params' => array('filter'=>array('sd_kh_id'=>$request['khid'],'sd_pt_id'=>$request['ptid'])),
    'queryBy' => 'searchForm',
    'idField' => 'sd_id',
    'CheckSelection'=>isset($request['multi']) && $request['multi']= 1 ? true : false,
) );
?>
<?php echo_selectwindow_js($request, 'table', array('id'=>'sd_id', 'code'=>'sd_id', 'name'=>'sd_name')) ?>
