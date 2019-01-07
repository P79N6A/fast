<?php
//$links[] = array('url' => 'prm/inv/do_list', 'title' => '库存查询（条形码）', 'is_pop' => false, 'pop_size' => '500,400');
render_control('PageHead', 'head1', array('title' => '库存查询（商品）', 'links' => $links, 'ref_table' => 'table'));
?>
<?php
$keyword_type = array();
$keyword_type['goods_code'] = '商品编码';
$keyword_type['goods_name'] = '商品名称';
$keyword_type = array_from_dict($keyword_type);
$is_num = array();
$is_num['effec_num'] = '可用库存';
$is_num['road_num'] = '在途库存';
$is_num['safe_num'] = '安全库存';
$is_num['out_num'] = '缺货库存';
$is_num['stock_num'] = '实物库存';
$is_num = array_from_dict($is_num);
render_control('SearchForm', 'searchForm', array(
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
    'show_row'=>3,
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '支持模糊搜索',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '分类',
            'type' => 'select_multi',
            'id' => 'category_code',
            'data' => $response['category'],
        ),
        array(
            'label' => '品牌',
            'type' => 'select_multi',
            'id' => 'brand_code',
            'data' => $response['brand'],
        ),
        array(
            'label' => '年份',
            'type' => 'select_multi',
            'id' => 'year_code',
            'data' => ds_get_select('year'),
        ),
        array(
            'label' => '季节',
            'type' => 'select_multi',
            'id' => 'season_code',
            'data' => ds_get_select('season_code'),
        ),
        array(
            'label' => array('id' => 'is_num', 'type' => 'select', 'data' => $is_num),
            'type' => 'group',
            'field' => 'num',
            'data' => $is_num,
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'num_start', 'class' => 'input-small'),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'num_end', 'remark' => '', 'class' => 'input-small'),
            )
        ),
        array(
            'label' => '仓库类别',
            'type' => 'select_multi',
            'id' => 'store_type_code',
            'data' => load_model('base/StoreTypeModel')->get_select(),
        ),
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => $response['store'],
        ),
        array(
            'label' => '安全库存',
            'type' => 'select',
            'id' => 'less_than_safe_num',
            'data' => ds_get_select_by_field('less_than_safe_num'),
        ),
        array(
            'label' => '启用状态',
            'title' => '',
            'type' => 'select',
            'id' => 'status',
            'data' => array(
                array('', '全部'),
                array('0', '启用'),
                array('1', '停用'),
            )
        ),
//           array(
//            'label' => '按商品合并',
//            'type' => 'checkbox',
//            'id' => 'barcode_group',
//            'value'=>0,
//
//        ),
    )
));
?>
<div>
    <span  id="group" >按仓库合并&nbsp;<input id="goods_group" value="0" type="checkbox"></span>&nbsp;&nbsp;
    <span id="summary"></span>
</div>
<?php
$list = array(
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '仓库名称',
        'field' => 'store_code_name',
        'width' => '120',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '商品名称',
        'field' => 'goods_name',
        'width' => '160',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '商品图片',
        'field' => 'goods_pic',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '商品编码',
        'field' => 'goods_code',
        'width' => '160',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '分类',
        'field' => 'category_name',
        'width' => '150',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '在途库存',
        'field' => 'road_num',
        'width' => '65',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '缺货库存',
        'field' => 'out_num',
        'width' => '65',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '可用库存',
        'field' => 'effec_num',
        'width' => '65',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '实物锁定',
        'field' => 'lock_num',
        'width' => '65',
        'align' => '',
        'format_js' => array(
            'type' => 'html', //<a onclick=javascript:openPage('lock_detail'，'?app_act=prm/inv/lock_detail&store_code=001&sku=goodsrecord001000000&goods_code=goodsrecord001'，'实物锁定明细')>65</a>
            'value' => '<a href=\\\'javascript:openPage("lock_detail","?app_act=prm/inv/lock_detail&store_code={store_code}&goods_code={goods_code}","实物锁定明细")\\\'>{lock_num}</a>',
        ),
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '实物库存',
        'field' => 'stock_num',
        'width' => '65',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '安全库存',
        'field' => 'safe_num',
        'width' => '65',
        'align' => '',
    ),
);
if(!empty($response['proprety'])) {
    foreach($response['proprety'] as $val) {
        $list[] = array('title' => $val['property_val_title'],
            'show' => 1,
            'type' => 'text',
            'width' => '80',
            'field' => $val['property_val']);
    }
}
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => $list
    ),
    'dataset' => 'prm/InvModel::get_goods_list_by_page',
    'queryBy' => 'searchForm',
    'init' => 'nodata',
    'export' => array('id' => 'exprot_list', 'conf' => 'inv_list_goods', 'name' => '库存查询(商品)', 'export_type' => 'file'), //
    'params' => array('filter' => array('user_id' => $response['user_id'],'list_type' => $request['list_type'])),
    'idField' => 'goods_inv_id',
    'customFieldTable' => 'prm/goods_inv_do_list_goods/table',
//    'ColumnGroup' => "[{title : '库存', from : 9, to : 11 }]"
        //'RowNumber'=>true,
        //'CheckSelection'=>true,
//    'CellEditing' => true,
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
            obj.list_type = '<?php echo $request['list_type']; ?>';
            var url = "?app_act=prm/inv/get_goods_inv_summary&app_fmt=json";
            $.post(url, obj, function (result) {
                var str = "|&nbsp;&nbsp;总在途库存：" + result.road_num + "&nbsp;&nbsp;|&nbsp;&nbsp;总缺货库存：" + result.out_num + "&nbsp;&nbsp;|&nbsp;&nbsp;总可用库存：" + result.available_mum + "&nbsp;&nbsp;|&nbsp;&nbsp;总锁定库存：" + result.lock_num + " &nbsp;&nbsp;|&nbsp;&nbsp;总实物库存：" + result.stock_num + " &nbsp;&nbsp;|&nbsp;&nbsp;总安全库存：" + result.safe_num + " ";
                $('#summary').html(str);
            }, 'json');

        }
        $('#searchForm').append('<input id="goods_group_val"  name="goods_group_val" type="hidden" value=""  />');
        $('#goods_group').click(function () {
            var columns = tableGrid.get('columns');
            if ($(this).is(':checked')) {
                //   console.log(tableGrid.get('columns'))  ;
                columns[0].set('visible', false);
                $("#goods_group_val").val(1);
                $('#is_num').attr('disabled', true);
                $('#less_than_safe_num').attr('disabled', true);
                $('#num_start').attr('disabled', true);
                $('#num_end').attr('disabled', true);
            } else {
                columns[0].set('visible', true);
                $("#goods_group_val").val(0);
                $('#is_num').attr('disabled', false);
                $('#less_than_safe_num').attr('disabled', false);
                $('#num_start').attr('disabled', false);
                $('#num_end').attr('disabled', false);
            }
            $('#btn-search').click();
        });
        //图片放大
        $("body").on('mouseover', 'td>div>span>img', function (e) {
            var img_src = $(this).data('goods-img');
            var tooltip = "<div id='tooltipimg' style='position:fixed;top:25%;left:25%;'> <img  width='500px' height='auto' src='" + img_src + "' alt='原图'/> </div>";
            //创建 div 元素
            $('tbody').parent().parent().parent().parent().append(tooltip);
        }).mouseout(function () {
            $("#tooltipimg").remove(); //移除
        });

    });
</script>
<style>
    #group{
        font-weight:bold;  
    }
</style>

