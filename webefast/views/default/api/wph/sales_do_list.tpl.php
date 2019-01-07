<style>
    #sale_st_start,#sale_st_end{width: 120px;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '专场列表',
    'links' => array(
        array('type' => 'js', 'js' => 'get_sales()', 'title' => '获取专场', 'priv' => 'api/wph/sales/get_sales_list'),
    ),
    'ref_table' => 'table'
));
?>

<?php
$keyword_goods['barcode'] = '平台商品条码';
$keyword_goods['product_name'] = '商品名称';
$keyword_goods['brand_name'] = '商品品牌';
$keyword_goods = array_from_dict($keyword_goods);
$keyword_sales['sales_no'] = '专场ID';
$keyword_sales['name'] = '专场名称';
$keyword_sales = array_from_dict($keyword_sales);
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
    ),
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_goods', 'type' => 'select', 'data' => $keyword_goods),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_goods,
            'id' => 'keyword_goods_value',
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => $response['shop'],
        ),
        array(
            'label' => '唯品会仓库',
            'type' => 'select_multi',
            'id' => 'warehouse',
            'data' => load_model('api/WeipinhuijitWarehouseModel')->get_warehouse_select(),
        ),
        array(
            'label' => array('id' => 'keyword_sales', 'type' => 'select', 'data' => $keyword_sales),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_sales,
            'id' => 'keyword_sales_value',
        ),
        array(
            'label' => '专场时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'sale_st_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'sale_st_end', 'remark' => ''),
            )
        ),
    )
));
?>
<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '全部', 'active' => true, 'id' => 'all'),
        array('title' => '未开始', 'active' => false, 'id' => 'no_start'),
        array('title' => '进行中', 'active' => false, 'id' => 'starting'),
        array('title' => '已结束', 'active' => false, 'id' => 'end'),
    ),
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
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
                'width' => '80',
                'align' => '',
                'buttons' => array(
                    array('id' => 'view_goods', 'title' => '查看商品', 'act' => 'api/wph/sales_sku/do_list&sales_no={sales_no}','show_name'=>'专场商品列表', 'show_cond' => 'obj.sales_status!=3'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '130',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '专场ID',
                'field' => 'sales_no',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '专场名称',
                'field' => 'name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '开售时间',
                'field' => 'sale_st',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '停售时间',
                'field' => 'sale_et',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '专场状态',
                'field' => 'status_txt',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '唯品会仓库',
                'field' => 'warehouse_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '下载时间',
                'field' => 'insert_time',
                'width' => '150',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'api/wph/WphSalesModel::get_by_page',
    'params' => array('filter' => array('sales_status' => 'all')),
    'queryBy' => 'searchForm',
    'idField' => 'id',
));
?>
<div class="tips tips-small tips-info" style="margin: 60px 0 0 10px;width: 67%;">
    <span class="x-icon x-icon-small x-icon-info"><i class="icon icon-white icon-info"></i></span>
    <div class="tips-content">友情提示：在专场开售时间之前，请对商品进行库存同步！专场开售之后，若对商品有调整库存，请手工调整库存并同步至唯品会。</div>
</div>
<script type="text/javascript">
    $(function () {
        //TAB选项卡
        $("#TabPage1 a").click(function () {
            tableStore.load();
        });
    });

    tableStore.on('beforeload', function (e) {
        e.params.sales_status = $("#TabPage1").find(".active").find("a").attr("id");
        tableStore.set("params", e.params);
    });

    //获取专场
    function get_sales() {
        var d = {'app_fmt': 'json'};
        $(".action-link a").attr('disabled', 'disabled');
        $.post('<?php echo get_app_url('api/wph/sales/get_sales_list'); ?>', d, function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            $(".action-link a").removeAttr('disabled');
            tableStore.load();
        }, "json");
    }
</script>