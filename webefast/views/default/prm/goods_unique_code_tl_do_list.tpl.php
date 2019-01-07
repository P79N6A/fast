<style type="text/css">
</style>
<?php 
if(load_model('sys/PrivilegeModel')->check_priv('prm/goods_unique_code_tl/export_list')){
    $link[] = array('url' => 'prm/goods_unique_code_tl/import', 'title' => '导入珠宝唯一码', 'is_pop' => true, 'pop_size' => '500,350');
}
render_control('PageHead', 'head1',
		array('title'=>'唯一码档案',
                        'links' => $link,
				'ref_table'=>'table'
));?>

<?php
$keyword_type = array();
$keyword_type['unique_code'] = '唯一码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['goods_name'] = '饰品名称';
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
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '100',
                'align' => '',
                'buttons' => array (

                	array('id' => 'edit', 'title' => '编辑', 'priv' => 'prm/goods_unique_code_tl/detail&action=do_edit', 'callback' => 'do_edit', 'show_cond' => 'obj.status != 1'),
                    //array('id'=>'delete', 'title' => '删除','priv' => 'prm/goods_unique_code_tl/do_delete', 'show_cond'=>'obj.status != 1', 'callback'=>'do_delete','confirm'=>'确认要删除此信息吗？'),

                ),
            ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品唯一码',
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
                	'title' => '仓库',
                	'field' => 'store_name',
                	'width' => '150',
                	'align' => ''
                ),
                array (
                	'type' => 'text',
                	'show' => 1,
                	'title' => '商品税收分类编码',
                	'field' => 'good_revenue_code',
                	'width' => '150',
                	'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '厂家款号',
                    'field' => 'factory_code',
                    'width' => '150',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '通灵款',
                    'field' => 'tongling_code',
                    'width' => '150',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '饰品名称',
                    'field' => 'goods_name',
                    'width' => '150',
                    'align' => ''
                ),
//                array (
//                    'type' => 'text',
//                    'show' => 1,
//                    'title' => '商品编码',
//                    'field' => 'goods_code',
//                    'width' => '100',
//                    'align' => ''
//                ),
//                array (
//                    'type' => 'text',
//                    'show' => 1,
//                    'title' => '规格1',
//                    'field' => 'spec1_name',
//                    'width' => '100',
//                    'align' => ''
//                ),
//                array (
//                    'type' => 'text',
//                    'show' => 1,
//                    'title' => '规格2',
//                    'field' => 'spec2_name',
//                    'width' => '100',
//                    'align' => ''
//                ),
                
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '成色',
                    'field' => 'relative_purity',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '金成色',
                    'field' => 'relative_purity_of_gold',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '国际证书号',
                    'field' => 'international_num',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '检测站证书号',
                    'field' => 'check_station_num',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '身份证',
                    'field' => 'identity_num',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '品牌',
                    'field' => 'jewelry_brand',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '子品牌',
                    'field' => 'jewelry_brand_child',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '金属颜色',
                    'field' => 'metal_color',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '颜色',
                    'field' => 'jewelry_color',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '净度',
                    'field' => 'jewelry_clarity',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '切工',
                    'field' => 'jewelry_cut',
                    'width' => '100',
                    'align' => ''
                ),
                               
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '主石重量',
                    'field' => 'pri_diamond_weight',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '主石数量',
                    'field' => 'pri_diamond_count',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '辅石重量',
                    'field' => 'ass_diamond_weight',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '辅石数量',
                    'field' => 'ass_diamond_count',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '珠宝总重量',
                    'field' => 'total_weight',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '类别',
                    'field' => 'jewelry_type',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '手寸长度',
                    'field' => 'ring_size',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '销售含税价',
                    'field' => 'total_price',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '证书类型',
                    'field' => 'credential_type',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '证书总重',
                    'field' => 'credential_weight',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '货单号',
                    'field' => 'record_num',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '饰品简称',
                    'field' => 'short_name',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '自定义属性1',
                    'field' => 'user_defined_property_1',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '自定义属性2',
                    'field' => 'user_defined_property_2',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '自定义属性3',
                    'field' => 'user_defined_property_3',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '自定义属性4',
                    'field' => 'user_defined_property_4',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '自定义属性5',
                    'field' => 'user_defined_property_5',
                    'width' => '100',
                    'align' => ''
                ),
                
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '自定义属性6',
                    'field' => 'user_defined_property_6',
                    'width' => '100',
                    'align' => ''
                ),
                
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '自定义属性7',
                    'field' => 'user_defined_property_7',
                    'width' => '100',
                    'align' => ''
                ),
                
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '自定义属性8',
                    'field' => 'user_defined_property_8',
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
        'dataset' => 'prm/GoodsUniqueCodeTLModel::get_by_page',
        'queryBy' => 'searchForm',
        'idField' => 'unique_id',
        'export'=> array('id'=>'exprot_list','conf'=>'unique_code_list_tl','name'=>'珠宝唯一码','export_type' => 'file'),
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
//修改唯一码商品信息
 function do_edit(_index, row) {
        openPage('<?php echo base64_encode('?app_act=prm/goods_unique_code_tl/detail&action=do_edit&unique_code=') ?>' + row.unique_code, '?app_act=prm/goods_unique_code_tl/detail&action=do_edit&unique_code=' + row.unique_code, '编辑');
        return;
    }
    /*
    * 唯一吗删除
    * 仅针对未启用的且未产生销售记录和库存记录的商品进行删除操作
    */
    function do_delete(_index,row){
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('prm/goods_unique_code_tl/do_delete'); ?>',
            data: {unique_code: row.unique_code},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message,type);
                }
            }
        });
    }


</script>

