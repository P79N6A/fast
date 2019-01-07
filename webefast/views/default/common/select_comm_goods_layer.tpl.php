<style type="text/css">
    .well .control-group {width: 45%;}
    /*.custom-dialog .bui-stdmod-footer{display: none;}*/
    .custom-dialog .bui-stdmod-body{margin-bottom: 5px;}
    .bui-stdmod-body{padding-top: 5px !important;}
    #table_pager{margin-top:5px;}
    .bui-grid,.bui-grid-body,.bui-grid-header{width: 718px !important;}
    /*    .bui-stdmod-body{
            overflow-x : hidden;
            overflow-y : auto;
        }*/
    #table_datatable{height:85%;overflow-y: scroll;}
    .bui-message{z-index: 9999 !important;}
</style>
<?php
$keyword_type = array();
$keyword_type['goods_code'] = '商品编码';
$keyword_type['goods_name'] = '商品名称';
$keyword_type['goods_short_name'] = '商品简称';
$keyword_type = array_from_dict($keyword_type);
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => '分类',
            'type' => 'select_multi',
            'id' => 'category_code',
            'data' => $response['category'],
        ),
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
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
                'title' => '商品编码',
                'field' => 'goods_code',
                'width' => '170',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品名称',
                'field' => 'goods_name',
                'width' => '240',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品分类',
                'field' => 'category_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '吊牌价',
                'field' => 'sell_price',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '70',
                'align' => 'center',
                'buttons' => array(
                    array(
                        'id' => 'select_spec',
                        'title' => '选择',
                        'callback' => 'open_spec_select',
                    ),
                ),
            ),
        )
    ),
    'dataset' => 'prm/GoodsSelectModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'goods_id',
    'params' => ['filter' => ['page_size' => 10]]
));
?>

<script type="text/javascript">
    var layer_line;
    function open_spec_select(state, res) {
        $.post('?app_act=prm/size_layer/check_goods_layer', {goods_code: res.goods_code}, function (ret) {
            if (ret.status == 1) {
                layer_line = ret.data;
                open_select(res);
            } else {
                BUI.Message.Alert(ret.message, 'warning');
                return false;
            }
        }, "json");
    }

    function open_select(res) {
        var store_code = "<?php echo $request['store_code'] ?>";
        var model = "<?php echo $request['model']; ?>";
        var record_id = "<?php echo $request['record_id']; ?>";
        var param = {store_code: store_code, goods_code: res.goods_code, goods_name: res.goods_name, sell_price: res.sell_price, layer_line: layer_line, model: model, record_id: record_id};
        var str = '';
        $.each(param, function (i, v) {
            str += '&' + i + '=' + v;
        });

        var url = '?app_act=prm/size_layer/select' + str;
        new ESUI.PopWindow(url, {
            title: '商品规格选择',
            width: '50%',
//            height: _opts.h,
            onBeforeClosed: function () {
//                tableStore.load();
//                if (typeof _opts.callback == 'function')
//                    _opts.callback();
            }
        }).show();

    }
</script>