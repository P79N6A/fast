<?php echo load_js('comm_util.js') ?>


<?php
$keyword_type = array();

$keyword_type['express_no'] = '快递单号';
$keyword_type['deal_code'] = '交易号';
$keyword_type['sell_record_code'] = '订单号';
$keyword_type['receiver_name'] = '收货人';
$keyword_type = array_from_dict($keyword_type);


render_control('SearchForm', 'searchForm', array(
    'buttons' =>array(

   array(
        'label' => '查询',
        'id' => 'btn-search',
           'type'=>'submit'
    ),
    array(
    		'label' => '导出',
    		'id' => 'exprot_list',
    ),
         ) ,
    'fields' => array(
         array(
            'label' => array('id'=>'keyword_type','type'=>'select','data'=>$keyword_type),
            'type' => 'input',
            'title'=>'',
            'data'=>$keyword_type,
            'id' => 'keyword',
           
        ),
        array(
        		'label' => '配送方式',
        		'type' => 'select_multi',
        		'id' => 'express_code',
        		'data' => ds_get_select('express'),
        ),
        array(
        		'label' => '店铺',
        		'type' => 'select_multi',
        		'id' => 'shop_code',
        		'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
        		'label' => '发货时间',
        		'type' => 'group',
        		'field' => 'daterange4',
        		'child' => array(
        				array('title' => 'start', 'type' => 'date', 'field' => 'delivery_time_start',),
        				array('pre_title' => '~', 'type' => 'date', 'field' => 'delivery_time_end', 'remark' => ''),
        		)
        ),
         array(
            'label' => '发货仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
        array(
            'label' => '包裹类别',
            'type' => 'select',
            'id' => 'package_num',
            'data' => array(array('' , '全部'), array('0' , '单包裹'), array('1' , '多包裹'))
        ),
    )
));
?>

<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '操作',
//                'field' => 'sell_record_code',
//                'width' => '200',
//                'align' => '',
//                'format_js' => array(
//                    'type' => 'html',
//                    'value'=>"<a class=\"sell_record_view\" href=\"javascript:void(0)\">跟踪</a>",
//                )
//            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单编号',
                'field' => 'sell_record_code',
                'width' => '150',
                'align' => '',
                     'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({sell_record_code})">{sell_record_code}</a>',
                ),
            ),
                       array(
                'type' => 'text',
                'show' => 1,
                'title' => '包裹',
                'field' => 'package_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '配送方式',
                'field' => 'express_code_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '快递单号',
                'field' => 'express_no',
                'width' => '120',
                'align' => ''
            ),

            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收货人',
                'field' => 'receiver_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收货地址',
                'field' => 'receiver_address',
                'width' => '250',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '发货时间',
                'field' => 'delivery_time',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺名称',
                'field' => 'shop_code_name',
                'width' => '120',
                'align' => '',
            ),
             array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '发货仓库',
                    'field' => 'store_name',
                    'width' => '100',
                    'align' => ''
                ),
        )
    ),
    'dataset' => 'oms/DeliverRecordModel::get_package_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'package_record_id',
     'init' => 'nodata',
     'export'=> array('id'=>'exprot_list','conf'=>'deliver_record_package','name'=>'订单包裹','export_type'=>'file'),
     //'export'=> array('id'=>'exprot_list','conf'=>'deliver_record_package','name'=>'订单包裹'),
));
?>

<script>
    
   $('#btn-search').click(function(){
       /*
      if($('#keyword').val() == ''){
            searchFormForm.set('disabled',true); 
         BUI.Message.Alert('请先设置查询条件',function(){
             searchFormForm.set('disabled',false);
         },'error');
        
  
          return  false;
      }*/
     searchFormForm.set('disabled',false); 
   });
   function view(sell_record_code) {
    var url = '?app_act=oms/sell_record/view&sell_record_code=' +sell_record_code
    openPage(window.btoa(url),url,'订单详情');
   } 
    
</script>