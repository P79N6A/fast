<?php
$lof_status = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
$arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code('goods_spec1');
$arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code('goods_spec2');
$arr_3 = array (
  'is_store_out' => 
  array (
    'title' => '验收',
    'type' => 1
  ),
  'record_code' => 
  array (
    'title' => '单据编号','type'=>1
  ),
  'relation_code' => 
  array (
    'title' => '退货通知单号','type'=>1
  ),
  'order_time' => 
  array (
    'title' => '下单时间','type'=>1
  ),
  'supplier_name' => 
  array (
    'title' => '供应商',
  ),
  'store_name' => 
  array (
    'title' => '仓库',
  ),  
  'rebate' => 
  array (
    'title' => '折扣',
  ),
  'record_type_name' => 
  array (
    'title' => '退货类型',
  ),
  'goods_name' => 
  array (
    'title' => '商品名称','type'=>1
  ),
  'goods_code' => 
  array (
    'title' => '商品编码','type'=>1
  ),
  'spec1_code' => 
  array (
    'title' => $arr_spec1['goods_spec1'].'代码','type'=>1
  ),
    'spec1_name' => 
  array (
    'title' => $arr_spec1['goods_spec1'].'名称','type'=>1
  ),
    'spec2_code' => 
  array (
    'title' => $arr_spec2['goods_spec2'].'代码','type'=>1
  ),
    'spec2_name' => 
  array (
    'title' => $arr_spec2['goods_spec2'].'名称','type'=>1
  ),
    'barcode' => 
  array (
    'title' => '商品条形码','type'=>1
  ),
  'per_price' => 
  array (
    'title' => '进货单价','type'=>1
  ),  
  'num' => 
  array (
    'title' => '实际退货数','type'=>1
  ),
    'money' => 
  array (
    'title' => '金额','type'=>1
  ),
    'enotice_num' => 
  array (
    'title' => '通知退货数','type'=>1
  ),
    'num_differ' => 
  array (
    'title' => '差异数','type'=>1
  )
);
if ($lof_status['lof_status'] == 1) {
    $arr_1 = array(
        'lof_no' => 
            array (
               'title' => '批次号','type'=>1
            ),
        'production_date' => 
            array (
              'title' => '生产日期','type'=>1
            ),
        'price' => 
            array (
              'title' => '进货价','type'=>1
            )
    );
    $arr = array_merge_recursive($arr_3,$arr_1);
} else {
    $arr_2 = array(
        'price' => 
            array (
              'title' => '进货价','type'=>1
            )
    );
    $arr = array_merge_recursive($arr_3,$arr_2);
}


return $arr;