<?php render_control('PageHead', 'head1',
array('title'=>'监控列表',
	'links'=>array(
        array('url'=>'moniter/mo_shop/show_all', 'title'=>'API监控'),
        array('url'=>'moniter/mo_shop/do_order_list', 'title'=>'订单监控'),
	),
	'ref_table'=>'table'
));?>


<?php
$mo_type_arr = array('shop_fail','shop_expires');

?>
<?php if(in_array($request['mo_type'], $mo_type_arr)):?>
<ul class="nav-tabs oms_tabs">
    <li <?php if($request['mo_type']=='shop_fail') {echo ' class="active"';} ?> ><a href="javascript:link('shop_fail')">授权异常</a></li>
    <li <?php if($request['mo_type']=='shop_expires') {echo ' class="active"';} ?>><a href="javascript:link('shop_expires')" >近一个月即将过期</a></li>
   
</ul>

<?php endif;  ?>

<div style="display: none">

<?php

render_control('SearchForm', 'searchForm', array(
    'buttons' =>array(
        
   array(
        'label' => '查询',
        'id' => 'btn-search',
           'type'=>'submit'
    ),
          /* array(
        'label' => '导出',
        'id' => 'exprot_list',
    ),*/
         ) ,
    'fields' => array(
        array(
            'label' => '类型',
            'title' => '',
            'type' => 'input',
            'id' => 'mo_type',
            'value'=>$request['mo_type'],
        ),
    )
));
?>
</div>




 
  


 
  


 
  


<?php

$list =array(

            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '店铺ID',
            		'field' => 'shop_id',
            		'width' => '100',
            		'align' => '',
            ),
            
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '店铺名称',
            		'field' => 'shop_name',
            		'width' => '100',
            		'align' => '',
            		
            ),
                array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '店铺代码',
            		'field' => 'shop_name',
            		'width' => '100',
            		'align' => '',
            		
            ),
    array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '平台',
            		'field' => 'source',
            		'width' => '100',
            		'align' => '',
            		
            ),
        array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '客户ID',
            		'field' => 'kh_id',
            		'width' => '100',
            		'align' => '',
            		
            ),
        array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '客户名称',
            		'field' => 'kh_name',
            		'width' => '100',
            		'align' => '',
            		
            ),
    );


if($request['mo_type']=='shop_fail'||$request['mo_type']=='shop_expires'){
    $list[] =       array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '授权过期时间',
            		'field' => 'expires_in',
            		'width' => '100',
            		'align' => '',
            		
            );
    
}else if ($request['mo_type']=='order'){
    $list[] =       array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '监控漏单时间段',
            		'field' => 'moniter_time',
            		'width' => '200',
            		'align' => '',
            		
            );
       $list[] =       array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '平台订单笔数',
            		'field' => 'api_num',
            		'width' => '100',
            		'align' => '',
            		
            );
       
          $list[] =       array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '系统订单笔数',
            		'field' => 'sys_num',
            		'width' => '100',
            		'align' => '',
            		
            );
             $list[] =       array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '漏单率',
            		'field' => 'rate',
            		'width' => '100',
            		'align' => '',
            		
            );
    
}else if ($request['mo_type']=='trans_order'){
       $list[] =       array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '转单失败笔数',
            		'field' => 'fail_num',
            		'width' => '100',
            		'align' => '',
            		
            );
}else if ($request['mo_type']=='order_send'){
       $list[] =       array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '网单回写失败笔数',
            		'field' => 'fail_num',
            		'width' => '100',
            		'align' => '',
            		
            );
}

 














render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => $list,
    ),
    'dataset' => 'moniter/MoShopModel::get_info_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'init' => 'nodata',
   // 'export'=> array('id'=>'exprot_list','conf'=>'return_record_list','name'=>'批发销货'),
    /*
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
    */
));
?>




<script>
    $(function(){
        $('#btn-search').click();
    });
    
    function link(type){
  window.location.href = "?app_act=moniter/mo_shop/do_list&mo_type="+type;
    }

</script>