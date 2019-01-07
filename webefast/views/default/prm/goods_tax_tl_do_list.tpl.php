<?php echo load_js("baison.js", true); ?>
<style>
     .like_link{
        color:#428bca; 
        cursor:pointer;
    }
</style>
<?php

    $link[] = array('url' => 'prm/goods_tax_tl/import&action=do_barcode', 'title' => '按商品条形码导入', 'is_pop' => true, 'pop_size' => '500,400');


    $link[] = array('url' => 'prm/goods_tax_tl/import&action=do_goods_code', 'title' => '按商品编码导入', 'is_pop' => true, 'pop_size' => '500,400');

render_control('PageHead', 'head1', array('title' => '商品税务编码维护',
    'links' =>$link,
    'ref_table' => 'table',
));
?>

<?php
$keyword_type = array();
$keyword_type['barcode'] = '商品条形码';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['tax_code'] = '税务编码';
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
    render_control('DataTable', 'table', array(
        'conf' => array(
            'list' => array(
                array(
                    'type' => 'button',
                    'show' => 1,
                    'title' => '操作',
                    'field' => '_operate',
                    'width' => '150',
                    'align' => 'center',
                    'buttons' => array (
                        array('id'=>'edit', 'title' => '编辑', 
                		'act'=>'pop:prm/goods_tax_tl/detail&app_scene=edit', 'show_name'=>'编辑',
                		'show_cond'=>'obj.use_num == 0'),
                	array('id' => 'del', 'title' => '删除', 'callback'=>'do_delete','confirm'=>'确认要删除此信息吗？','show_cond' => 'obj.use_num == 0'),
                ),
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品条形码',
                    'field' => 'barcode',
                    'width' => '200',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品编码',
                    'field' => 'goods_code',
                    'width' => '200',
                    'align' => ''
                ),
               
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '税收编码',
                    'field' => 'tax_code',
                    'width' => '200',
                    'align' => '',
                    //'editor' => "{xtype : 'text'}",
//                     'format_js' => array(
//                        'type' => 'function',
//                        'value' => 'show_tax_code',
//                    )
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '单位',
                    'field' => 'unit',
                    'width' => '100',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品编码简称',
                    'field' => 'goods_code_short',
                    'width' => '100',
                    'align' => ''
                ),
            )
        ),
        'dataset' => 'prm/GoodsTaxModel::get_by_page',
        'queryBy' => 'searchForm',
        'idField' => 'tax_id',
        'export' => array('id' => 'exprot_list', 'conf' => 'goods_tax_tl_list', 'name' => '商品税务编码','export_type' => 'file'),
            //'RowNumber'=>true,
       // 'CheckSelection'=>true,
        'customFieldTable' => 'prm/goods_tax_tl',
        'ColumnResize' => true,
        'CellEditing' => true,
    ));
?>
<script type="text/javascript">
function do_delete (_index, row) {
	$.ajax({ 
        type: 'POST', 
        dataType: 'json',
        url: '<?php echo get_app_url('prm/goods_tax_tl/do_delete');?>', 
        data: {tax_id: row.tax_id},
        success: function(ret) {
            var type = ret.status == 1 ? 'success' : 'error';
            if (type == 'success') {
            BUI.Message.Alert('删除成功', type);
            tableStore.load();
            } else {
            BUI.Message.Alert(ret.message, type);
            }
        }
    });
}

   
</script>
