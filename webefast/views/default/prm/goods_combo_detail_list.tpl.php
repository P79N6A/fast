<?php
render_control('PageHead', 'head1', array('title' => '商品子套餐查询',
    'links' => array(
    ),
    'ref_table' => 'table'
));
?>
<?php
$keyword_type = array();
$keyword_type['goods_code'] = '子商品编码';
$keyword_type['barcode'] = '子商品条形码';
$keyword_type['combo_barcode'] = '套餐条形码';
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
    'show_row' => 3,
    'fields' => array(
        array(
            'label' => array('id'=>'keyword_type','type'=>'select','data'=>$keyword_type),
            'type' => 'input',
            'title'=>'',
            'data'=>$keyword_type,
            'id' => 'keyword',
            ),
    )
));
?>

<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '子商品条形码',
            		'field' => 'barcode',
            		'width' => '180',
            		'align' => '',
            ), 
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '子商品编码',
            		'field' => 'goods_code',
            		'width' => '180',
            		'align' => '',
            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '套餐名称',
            		'field' => 'goods_name',
            		'width' => '200',
            		'align' => '',
            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '参与的套餐条形码',
            		'field' => 'combo_barcode',
            		'width' => '160',
            		'align' => ''
            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '参与的套餐数量',
            		'field' => 'num',
            		'width' => '100',
            		'align' => ''
            ),      
        )
    ),
    'dataset' => 'prm/GoodsComboModel::get_detail_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'goods_combo_id',
    'init' => 'nodata',
    'export'=> array('id'=>'exprot_list','conf'=>'prm_goods_combo_detail_list','name'=>'套餐子商品列表','export_type' => 'file'),//
));
?>
<?php echo load_js("pur.js",true);?>
<script type="text/javascript">

</script>


