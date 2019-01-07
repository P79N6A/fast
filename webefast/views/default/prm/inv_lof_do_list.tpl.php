<style type="text/css">
    #effec_num_start,#period_validity_start,#sellmonth_start {
        width:84px;
    }
    #effec_num_end,#period_validity_end,#sellmonth_end {
        width:84px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '商品批次库存查询',
    'links' => array(
        array('url' => 'prm/inv/safe_import', 'title' => '安全库存导入', 'is_pop' => true, 'pop_size' => '550,300'),
    ),
    'ref_table' => 'table'
));
?>

<?php
$keyword_type = array();
$keyword_type['goods_code'] = '商品编码';
$keyword_type['goods_name'] = '商品名称';
$keyword_type['barcode'] = '商品条形码';
$keyword_type = array_from_dict($keyword_type);
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
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_select(),
        ),
        array(
            'label' => '批次号',
            'type' => 'input',
            'id' => 'lof_no',
            'title' => '批次号',
        ),
        array(
            'label' => '保质期',
            'type' => 'group',
            'field' => 'period_validity',
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'period_validity_start'),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'period_validity_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '剩余可卖月',
            'type' => 'group',
            'field' => 'sellmonth',
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'sellmonth_start',),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'sellmonth_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '失效日期',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'lost_validity_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'lost_validity_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '可用库存',
            'type' => 'group',
            'field' => 'effec_num',
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'effec_num_start'),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'effec_num_end', 'remark' => ''),
            )
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
    )
));
?>
<div>
    <span id="summary"></span>
</div>
<?php
//$result = load_model('sys/GoodsRuleModel')->get_by_ids(array(1, 2));
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库名称',
                'field' => 'store_code_name',
                'width' => '100',
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
                'title' => '商品编码',
                'field' => 'goods_code',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => $response['goods_spec1_rename'],
                'field' => 'spec1_name',
                'width' => '80',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => $response['goods_spec2_rename'],
                'field' => 'spec2_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品条形码',
                'field' => 'barcode',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '批次号',
                'field' => 'lof_no',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '生产日期',
                'field' => 'production_date',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '保质期',
                'field' => 'period_validity',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '失效日期',
                'field' => 'lost_validity',
                'width' => '80',
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
                    'value' => '<a href=\\\'javascript:openPage("lock_detail","?app_act=prm/inv/lock_detail&store_code={store_code}&sku={sku}&goods_code={goods_code}&&first_mode=lof_mode","实物锁定明细")\\\'>{lock_num}</a>',
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
                'align' => ''
            ),
        )
    ),
    'dataset' => 'prm/InvLofModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'goods_inv_id',
    'export' => array('id' => 'exprot_list', 'conf' => 'inv_lof_list', 'name' => '商品库存', 'export_type' => 'file'),
//     'events' => array(
//         'rowdblclick' => 'showDetail',
//     ),
));
?>
<script type="text/javascript">
    if (typeof tableCellEditing != "undefined") {
        //列表区域,数量修改回调操作 +++++++++++++++++++++++++++++++++++++++++++
        tableCellEditing.on('accept', function (record, editor) {
            //console.log(record);
            //return;
            if (parseInt(record.record.safe_num) < 0) {
                BUI.Message.Alert('不能为负数', 'error');
                tableStore.load();
                return;
            }

            $.post('?app_act=prm/inv/edit_safe_num',
                    {goods_inv_id: record.record.goods_inv_id, safe_num: record.record.safe_num},
                    function (result) {
                        window.location.reload();
                    }, 'json');
        });
    }
    function do_delete(_index, row) {
        $.ajax({
            type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('prm/goods_barcode/do_delete'); ?>', data: {sku_id: row.sku_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功：', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    $(function () {
        searchFormFormListeners['beforesubmit'].push(function (ev) {
            if ($('#table_pager .bui-pb-page').val() == 1) {
                get_summary();
            }
        });
        get_summary();
        function get_summary() {
            var obj = searchFormForm.serializeToObject();
            var url = "?app_act=prm/inv/get_inv_summary&app_fmt=json";
            $.post(url, obj, function (result) {
                var str = "总在途库存：" + result.data.road_num + "&nbsp;&nbsp;|&nbsp;&nbsp;总缺货库存：" + result.data.out_num + "&nbsp;&nbsp;|&nbsp;&nbsp;总可用库存：" + result.data.available_mum + "&nbsp;&nbsp;|&nbsp;&nbsp;总锁定库存：" + result.data.lock_num + " &nbsp;&nbsp;|&nbsp;&nbsp;总实物库存：" + result.data.stock_num + " &nbsp;&nbsp;|&nbsp;&nbsp;总安全库存：" + result.data.safe_num + " ";
                $('#summary').html(str);
            }, 'json');

        }


    });
</script>




