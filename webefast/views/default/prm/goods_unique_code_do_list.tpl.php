<style type="text/css">
</style>
<?php render_control('PageHead', 'head1',
		array('title'=>'唯一码档案',
                        'links' => array(
 array('url' => 'prm/goods_unique_code/import', 'title' => '导入商品唯一码', 'is_pop' => true, 'pop_size' => '500,350'),
),
				'ref_table'=>'table'
));?>

<?php
$keyword_type = array();
$keyword_type['unique_code'] = '唯一码';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['goods_name'] = '商品名称';
$keyword_type = array_from_dict($keyword_type);
render_control ( 'SearchForm', 'searchForm', array (
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
    ),
    'fields' => array (
		     array(
	            'label' => array('id'=>'keyword_type','type'=>'select','data'=>$keyword_type),
	            'type' => 'input',
	            'title'=>'',
	            'data'=>$keyword_type,
	            'id' => 'keyword',
	        ),
    )
) );
?>

<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '全部', 'active' => true, 'id' => 'tabs_all'),
        array('title' => '可用', 'active' => false, 'id' => 'tabs_allow'),
        array('title' => '不可用', 'active' => false, 'id' => 'tabs_not_allow'), 
    ),
    // 'for' => 'TabPage1Contents'
));
?>



    <?php
    render_control ( 'DataTable', 'table', array (
        'conf' => array (
            'list' => array (

                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '唯一码',
                    'field' => 'unique_code',
                    'width' => '200',
                    'align' => ''
                ),
                array (
                	'type' => 'text',
                	'show' => 1,
                	'title' => '商品条形码',
                	'field' => 'barcode',
                	'width' => '150',
                	'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品名称',
                    'field' => 'goods_name',
                    'width' => '150',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品编码',
                    'field' => 'goods_code',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '规格1',
                    'field' => 'spec1_name',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '规格2',
                    'field' => 'spec2_name',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '系统SKU码',
                    'field' => 'sku',
                    'width' => '150',
                    'align' => ''
                ),
                
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '可用状态',
                    'field' => 'is_allow_name',
                    'width' => '100',
                    'align' => ''
                ),
            )
        ),
        'dataset' => 'prm/GoodsUniqueCodeModel::get_by_page',
        'queryBy' => 'searchForm',
        'idField' => 'unique_id',
        'export'=> array('id'=>'exprot_list','conf'=>'unique_code_list','name'=>'唯一码'),
        //'RowNumber'=>true,
		//'CheckSelection'=>true,
    ) );
    ?>


<script type="text/javascript">

$(document).ready(function() {
	$("#TabPage1 a").click(function() {
        tableStore.load();
    });
    
    tableStore.on('beforeload', function(e) {
    	e.params.do_list_tab = $("#TabPage1").find(".active").find("a").attr("id");
    	tableStore.set("params", e.params);
    });

})
</script>

