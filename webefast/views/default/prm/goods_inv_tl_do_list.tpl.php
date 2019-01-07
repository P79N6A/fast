<style>
    .form-horizontal .control-label{width: 110px;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '商品唯一码库存查询', 'ref_table' => 'table'));
?>


<?php
render_control('SearchForm', 'searchForm', array(
    'show_row' => 4,
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
        array(
            'label' => '导出',
            'id' => 'exprot_list',
        ),
    ),
    'fields' => array(
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
        array(
            'label' => '状态',
            'title' => '',
            'type' => 'select',
            'id' => 'status',
            'data' => array(
                array('', '全部'),
                array('0', '可用'),
                array('1', '不可用'),
            )
        ),
        array(
            'label' => '商品编码',
            'type' => 'input',
            'id' => 'goods_code',
        ),
        array(
            'label' => '商品条形码',
            'type' => 'input',
            'id' => 'barcode',
        ),
        array(
            'label' => '商品唯一码',
            'type' => 'input',
            'id' => 'unique_code',
        ),
        array(
            'label' => '商品名称',
            'type' => 'input',
            'id' => 'goods_code_name',
        ),
        array(
            'label' => '品牌',
            'type' => 'input',
            'id' => 'jewelry_brand',
        ),
        array(
            'label' => '子品牌',
            'type' => 'input',
            'id' => 'jewelry_brand_child',
        ),
        array(
            'label' => '检测站证书号',
            'type' => 'input',
            'id' => 'check_station_num',
        ),
        array(
            'label' => '金属颜色',
            'type' => 'input',
            'id' => 'metal_color',
        ),
        array(
            'label' => '手寸长度',
            'type' => 'input',
            'id' => 'ring_size',
        ),
        array(
            'label' => '系统sku码',
            'type' => 'input',
            'id' => 'sku',
        ),
        array(
            'label' => '通灵款',
            'type' => 'input',
            'id' => 'tongling_code',
        ),
        array(
            'label' => '饰品名称',
            'type' => 'input',
            'id' => 'goods_name',
        ),
        array(
            'label' => '成色',
            'type' => 'input',
            'id' => 'relative_purity',
        ),
        array(
            'label' => '金成色',
            'type' => 'input',
            'id' => 'relative_purity_of_gold',
        ),
        array(
            'label' => '国际证书号',
            'type' => 'input',
            'id' => 'international_num',
        ),
        array(
            'label' => '颜色',
            'type' => 'input',
            'id' => 'jewelry_color',
        ),
        array(
            'label' => '身份证',
            'type' => 'input',
            'id' => 'identity_num',
        ),
        array(
            'label' => '商品税收分类编码',
            'type' => 'input',
            'id' => 'good_revenue_code',
        ),
        array(
            'label' => '厂家款号',
            'type' => 'input',
            'id' => 'factory_code',
        ),
        array(
            'label' => '净度',
            'type' => 'input',
            'id' => 'jewelry_clarity',
        ),
        array(
            'label' => '切工',
            'type' => 'input',
            'id' => 'jewelry_cut',
        ),
        array(
            'label' => '主石重量',
            'type' => 'input',
            'id' => 'pri_diamond_weight',
        ),
        array(
            'label' => '主石数量',
            'type' => 'input',
            'id' => 'pri_diamond_count',
        ),
        array(
            'label' => '辅石重量',
            'type' => 'input',
            'id' => 'ass_diamond_weight',
        ),
        array(
            'label' => '辅石数量',
            'type' => 'input',
            'id' => 'ass_diamond_count',
        ),
        array(
            'label' => '珠宝总重量',
            'type' => 'input',
            'id' => 'total_weight',
        ),
        array(
            'label' => '类别',
            'type' => 'input',
            'id' => 'jewelry_type',
        ),
        array(
            'label' => '销售含税价',
            'type' => 'input',
            'id' => 'total_price',
        ),
        array(
            'label' => '证书类型',
            'type' => 'input',
            'id' => 'credential_type',
        ),
        array(
            'label' => '证书重量',
            'type' => 'input',
            'id' => 'credential_weight',
        ),
        array(
            'label' => '货单号',
            'type' => 'input',
            'id' => 'record_num',
        ),
        array(
            'label' => '饰品简称',
            'type' => 'input',
            'id' => 'short_name',
        ),
        array(
            'label' => '自定义属性1',
            'type' => 'input',
            'id' => 'user_defined_property_1',
        ),
        array(
            'label' => '自定义属性2',
            'type' => 'input',
            'id' => 'user_defined_property_2',
        ),
        array(
            'label' => '自定义属性3',
            'type' => 'input',
            'id' => 'user_defined_property_3',
        ),
        array(
            'label' => '自定义属性4',
            'type' => 'input',
            'id' => 'user_defined_property_4',
        ),
        array(
            'label' => '自定义属性5',
            'type' => 'input',
            'id' => 'user_defined_property_5',
        ),
        array(
            'label' => '自定义属性6',
            'type' => 'input',
            'id' => 'user_defined_property_6',
        ),
        array(
            'label' => '自定义属性7',
            'type' => 'input',
            'id' => 'user_defined_property_7',
        ),
        array(
            'label' => '自定义属性8',
            'type' => 'input',
            'id' => 'user_defined_property_8',
        ),
    )
));
?>
<div>

    <span id="summary"></span>
</div>
<?php
$list = array(
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '仓库',
        'field' => 'store_code_name',
        'width' => '90',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '商品唯一码',
        'field' => 'unique_code',
        'width' => '160',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '数量',
        'field' => 'goods_num',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '商品编码',
        'field' => 'goods_code',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '商品名称',
        'field' => 'goods_code_name',
        'width' => '80',
        'align' => '',
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '商品条形码',
        'field' => 'barcode',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '商品税收分类编码',
        'field' => 'good_revenue_code',
        'width' => '120',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '厂家款号',
        'field' => 'factory_code',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '通灵款',
        'field' => 'tongling_code',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '饰品名称',
        'field' => 'goods_name',
        'width' => '110',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '成色',
        'field' => 'relative_purity',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '金成色',
        'field' => 'relative_purity_of_gold',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '国际证书号',
        'field' => 'international_num',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '检测站证书号',
        'field' => 'check_station_num',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '身份证',
        'field' => 'identity_num',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '品牌',
        'field' => 'jewelry_brand',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '子品牌',
        'field' => 'jewelry_brand_child',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '金属颜色',
        'field' => 'metal_color',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '颜色',
        'field' => 'jewelry_color',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '净度',
        'field' => 'jewelry_clarity',
        'width' => '80',
        'align' => ''
    ),
     array(
        'type' => 'text',
        'show' => 1,
        'title' => '切工',
        'field' => 'jewelry_cut',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '主石重量',
        'field' => 'pri_diamond_weight',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '主石数量',
        'field' => 'pri_diamond_count',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '辅石重量',
        'field' => 'ass_diamond_weight',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '辅石数量',
        'field' => 'ass_diamond_count',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '珠宝总重量',
        'field' => 'total_weight',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '类别',
        'field' => 'jewelry_type',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '手寸长度',
        'field' => 'ring_size',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '销售含税价',
        'field' => 'total_price',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '证书类型',
        'field' => 'credential_type',
        'width' => '80',
        'align' => ''
    ),
     array(
        'type' => 'text',
        'show' => 1,
        'title' => '证书重量',
        'field' => 'credential_weight',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '货单号',
        'field' => 'record_num',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '饰品简称',
        'field' => 'short_name',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '自定义属性1',
        'field' => 'user_defined_property_1',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '自定义属性2',
        'field' => 'user_defined_property_2',
        'width' => '100',
        'align' => ''
    ),
     array(
        'type' => 'text',
        'show' => 1,
        'title' => '自定义属性3',
        'field' => 'user_defined_property_3',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '自定义属性4',
        'field' => 'user_defined_property_4',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '自定义属性5',
        'field' => 'user_defined_property_5',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '自定义属性6',
        'field' => 'user_defined_property_6',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '自定义属性7',
        'field' => 'user_defined_property_7',
        'width' => '100',
        'align' => ''
    ),
     array(
        'type' => 'text',
        'show' => 1,
        'title' => '自定义属性8',
        'field' => 'user_defined_property_8',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '系统sku码',
        'field' => 'sku',
        'width' => '100',
        'align' => ''
    ),
    
);

render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => $list
    ),
    'dataset' => 'prm/GoodsUniqueCodeTLModel::get_inv_by_page',
    'queryBy' => 'searchForm',
    'export' => array('id' => 'exprot_list', 'conf' => 'unique_inv_list', 'name' => '唯一码库存查询','export_type' => 'file'),
    'idField' => 'inv_id',
    'customFieldTable' => 'prm/goods_inv_tl',
    //'params' => array('filter' => array('user_id' => $response['user_id'])),
    'ColumnResize' => true,
    'CellEditing' => true,
));
?>
<script type="text/javascript">



    $(function () {


        searchFormFormListeners['beforesubmit'].push(function (ev) {
//            if ($('#table_pager .bui-pb-page').val() == 1) {
            get_summary();
//            }
        });
        get_summary();
        function get_summary() {
            var obj = searchFormForm.serializeToObject();
            var url = "?app_act=prm/goods_inv_tl/get_inv_summary&app_fmt=json";
            $.post(url, obj, function (result) {
                var str = "&nbsp;&nbsp;&nbsp;&nbsp;可用唯一码总数量：" + result.data.available_num + "&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;不可用唯一码总数量：" + result.data.un_available_num + "&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;唯一码总数量：" + result.data.all_num + " ";
                $('#summary').html(str);
            }, 'json');

        }
    });
</script>
<style>
    #group{
        font-weight:bold;  
    }
</style>

