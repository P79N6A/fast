<style type="text/css">
    .well {
        min-height: 100px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '商品进销存报表',
    'ref_table' => 'table'
));
?>

<?php
$price_type_arr = array();
$price_type_arr[] = array('sell_price','吊牌价');
$price_type_arr[] = array('cost_price','成本价');
$price_type_arr[] = array('purchase_price','进货价');

$order_date_start = load_model('prm/JxcReportModel')->get_cur_month_first_day(date('Y-m-d'));
$order_date_end = date('Y-m-d');

render_control('SearchForm', 'searchForm', array (
  'buttons' => 
  array (
    0 => 
    array (
      'label' => '查询',
      'id' => 'btn-search',
      'type'=>'submit'
    ),
    /*
    1 => 
    array (
      'label' => '导出',
      'id' => 'exprot_list',
    ),*/
  ),
  'show_row' => 4,
  'fields' => 
  array (
    1 => 
    array (
      'label' => '仓库',
      'type' => 'select_multi',
      'id' => 'store_code',
      'data' => load_model('util/FormSelectSourceModel')->get_store(),
    ),
    2 => 
    array (
      'label' => '商品编码',
      'type' => 'text',
      'id' => 'goods_code',
      'title' => '支持模糊查询',
    ),
    3 => 
    array (
      'label' => '商品条形码',
      'type' => 'text',
      'id' => 'goods_barcode',
      'title' => '支持模糊查询',
    ),
    4 => 
    array (
      'label' => '分类',
      'type' => 'select_multi',
      'id' => 'category_code',
      'data' => load_model('util/FormSelectSourceModel')->get_catagory(),
    ),
    5 => 
    array (
      'label' => '品牌',
      'type' => 'select_multi',
      'id' => 'brand_code',
      'data' => load_model('util/FormSelectSourceModel')->get_brand(),
    ),
    6 => 
    array (
      'label' => '季节',
      'type' => 'select_multi',
      'id' => 'season_code',
      'data' => load_model('util/FormSelectSourceModel')->get_season(),
    ),
    7 => 
    array (
      'label' => '业务日期',
      'type' => 'group',
      'field' => 'daterange1',
      'child' => 
      array (
        0 => 
        array (
          'title' => 'start',
          'type' => 'date',
          'field' => 'order_date_start',
        ),
        1 => 
        array (
          'pre_title' => '~',
          'type' => 'date',
          'field' => 'order_date_end',
          'remark' => '',
        ),
      ),
    ),
    8 => 
    array (
      'label' => '价格',
      'type' => 'select',
      'id' => 'price_type',
      'data' => $price_type_arr,
    ),
  ),
));
?>

<?php
render_control('DataTable', 'table', array (
  'conf' => 
  array (
    'list' => 
    array (
      0 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '仓库',
        'field' => 'store_name',
        'width' => '100',
        'align' => '',
      ),
      1 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '商品编码',
        'field' => 'goods_code',
        'width' => '100',
        'align' => '',
      ),
      2 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '规格1',
        'field' => 'spec1_name',
        'width' => '100',
        'align' => '',
      ),
      3 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '规格2',
        'field' => 'spec2_name',
        'width' => '100',
        'align' => '',
      ),
      4 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '商品条形码',
        'field' => 'barcode',
        'width' => '100',
        'align' => '',
      ),
      5 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '期初库存',
        'field' => 'qc',
        'width' => '100',
        'align' => '',
      ),
      6 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '期初金额',
        'field' => 'qc_je',
        'width' => '100',
        'align' => '',
      ),
      7 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '采购入库数',
        'field' => 'purchase',
        'width' => '100',
        'align' => '',
      ),
      8 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '采购退货数',
        'field' => 'pur_return',
        'width' => '100',
        'align' => '',
      ),
      9 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '调整数',
        'field' => 'adjust',
        'width' => '100',
        'align' => '',
      ),
      10 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '移入数',
        'field' => 'shift_in',
        'width' => '100',
        'align' => '',
      ),
      11 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '移出数',
        'field' => 'shift',
        'width' => '100',
        'align' => '',
      ),
      12 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '零售发货数',
        'field' => 'sell_record',
        'width' => '100',
        'align' => '',
      ),
      13 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '零售退货数',
        'field' => 'sell_return',
        'width' => '100',
        'align' => '',
      ),
      14 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '期末库存',
        'field' => 'qm',
        'width' => '100',
        'align' => '',
      ),
      15 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '期末金额',
        'field' => 'qm_je',
        'width' => '100',
        'align' => '',
      ),
    ),
  ),
  'dataset' => 'prm/JxcReportModel::get_page_data',
  'queryBy' => 'searchForm',
  'export'=> array('id'=>'exprot_list','conf'=>'inv_list','name'=>'商品进销存'),
  'idField' => 'id',
  'CheckSelection' => false,
));
?>
<script type="text/javascript">
	$(function(){
		$("#order_date_start").val('<?php echo $order_date_start;?>');
		$("#order_date_end").val('<?php echo $order_date_end;?>');
    });
/*
$(function(){
     $('#exprot_list').click(function(){
        var params;
        var url = tableStore.get('url'); 
        params = tableStore.get('params');
        params.ctl_type = 'export';
        if( show_mode == 'normal_mode'){
             params.ctl_export_conf = 'inv_list';
         }else{
           params.ctl_export_conf = 'inv_lof_list';
        }
        params.ctl_export_name =  '商品库存';
        var obj = searchFormForm.serializeToObject();
          for(var key in obj){
                 params[key] =  obj[key];
	  } 
          for(var key in params){
                url +="&"+key+"="+params[key];
	  }
        window.location.href = url;
    });*/
</script>




