<?php  
$is_power = load_model('sys/PrivilegeModel')->check_priv('prm/inv/safe_import');
$links = array();
if($is_power == true){
    $links[] = array('url' => 'prm/inv/safe_import', 'title' => '安全库存导入', 'is_pop' => true, 'pop_size' => '550,300');
}
$links[] = array('url' => 'prm/inv/do_list_goods', 'title' => '库存查询（商品）', 'is_pop' => false, 'pop_size' => '500,400');
render_control('PageHead', 'head1', array('title' =>'商品库存实时查询','links' => $links,'ref_table' => 'table'));
?>

<?php
$keyword_type = array();
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
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
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
            'help' => '商品编码和商品条形码支持多个查询，用逗号分隔；支持商品编码、商品条形码的模糊查询',
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
//        array(
//            'label' => '可用库存',
//            'type' => 'group',
//            'field' => 'effec_num',
//            'child' => array(
//                array('title' => 'start', 'type' => 'input', 'field' => 'effec_num_start'),
//                array('pre_title' => '~', 'type' => 'input', 'field' => 'effec_num_end', 'remark' => ''),
//            )
//        ),
            array(
            'label' => array('id' => 'is_num', 'type' => 'select', 'data' => $is_num),
            'type' => 'group',
            'field' => 'num',
            'data' => $is_num,
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'num_start','class' => 'input-small'),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'num_end', 'remark' => '','class' => 'input-small'),
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
             array(
            'label' => '条码备注',
            'type' => 'input',
            'id' => 'barcode_remark',


        ),
    )
));
?>
<div>
    <span id="group">按仓库合并&nbsp;<input id="barcode_group" value="0" type="checkbox"></span>&nbsp;&nbsp;
    <span id="summary"></span>
    <span style="text-align:right; margin-right:20px;">
        <ul id="tool2" class="toolbar">        
            <li >
                <label>排序类型：</label>
                <select id="sort" name="sort">
                <!--<option value="" >请选择</option>-->
                    <option value="barcode_desc">默认（系统SKU码升序）</option>
                    <option value="road_num_desc">在途库存由高到低</option>
                    <option value="road_num_asc">在途库存由低到高</option>
                    <option value="stock_num_desc">实物库存由高到低</option>
                    <option value="stock_num_asc">实物库存由低到高</option>
                    <option value="out_num_desc">缺货库存由高到低</option>
                    <option value="out_num_asc">缺货库存由低到高</option>
                </select>
                <button type="button" class="button button-small" id="sort_btn" onclick = "sort()">排序</button>
                <img src="assets/images/tip.png" alt="123" width="25" height="25" title ="排序所有页签"/>
            </li>
        </ul>
    </span>
</div>
<?php
//$result = load_model('sys/GoodsRuleModel')->get_by_ids(array(1, 2));
$list = array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库名称',
                'field' => 'store_code_name',
                'width' => '90',
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
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => $response['goods_spec1_rename'],
                'field' => 'spec1_code_name',
                'width' => '98',
                'align' => '',
//                'format_js' => array('type' => 'html', 'value' => '[{spec1_code}]{spec1_code_name}')
            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => $result['data'][0]['name'] . '编码',
//                'field' => 'spec1_code',
//                'width' => '80',
//                'align' => ''
//            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => $response['goods_spec2_rename'],
                'field' => 'spec2_code_name',
                'width' => '120',
                'align' => ''
            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => $result['data'][1]['name'] . '编码',
//                'field' => 'spec2_code',
//                'width' => '80',
//                'align' => ''
//            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '系统SKU码',
//                'field' => 'sku',
//                'width' => '100',
//                'align' => ''
//            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品条形码',
                'field' => 'barcode',
                'width' => '110',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分类',
                'field' => 'category_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '库位',
                'field' => 'goods_self_name',
                'width' => '100',
                'align' => ''
            ),
            /*
              array(
              'type' => 'text',
              'show' => 1,
              'title' => '吊牌价',
              'field' => 'sell_price',
              'width' => '75',
              'align' => ''
              ),
              array(
              'type' => 'text',
              'show' => 1,
              'title' => '成本价',
              'field' => 'cost_price',
              'width' => '75',
              'align' => ''
              ),
             * /
             */
            /**
             * array(
             * 'type' => 'text',
             * 'show' => 1,
             * 'title' => '批次',
             * 'field' => 'batch',
             * 'width' => '100',
             * 'align' => ''
             * ),
             *
             */
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '在途库存',
                'field' => 'road_num',
                'width' => '65',
                'align' => '',
                'format_js' => array(
                    'type' => 'html', 
                    'value' => '<a href=\\\'javascript:openPage("road_detail","?app_act=prm/inv/road_detail&store_code={store_code}&sku={sku}&goods_code={goods_code}","在途库存明细")\\\'>{road_num}</a>',
                )
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
                'align' => '',
                'editor' => $response['safe_import'] == TRUE ? "{xtype:'number'}" : ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '条码备注',
                'field' => 'remark',
            ),
        /* array (
          'type' => 'button',
          'show' => 1,
          'title' => '操作',
          'field' => '_operate',
          'width' => '300',
          'align' => '',
          'buttons' => array (

          array('id'=>'delete', 'title' => '删除', 'callback'=>'do_delete','confirm'=>'确认要删除此信息吗？'),

          ),
          ) */
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
    'dataset' => 'prm/InvModel::get_by_page',
    'queryBy' => 'searchForm',
    'init' => 'nodata',
    'export' => array('id' => 'exprot_list', 'conf' => 'inv_list', 'name' => '商品库存', 'export_type' => 'file'),
    'params' => array('filter' => array('user_id' => $response['user_id'])),
    'idField' => 'goods_inv_id',
    'customFieldTable' => 'prm/goods_inv_do_list/table',
    'ColumnResize' => true,
//    'ColumnGroup' => "[{title : '库存', from : 9, to : 11 }]"
    //'RowNumber'=>true,
    //'CheckSelection'=>true,
    'CellEditing' => true,
));
?>
<script type="text/javascript">
    <?php if ($response['safe_import'] == TRUE): ?>
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
    <?php endif; ?>
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
    
    //排序
    function sort() {
        tableStore.load();
    }
    
    $(function () {
        //排序
        tableStore.on('beforeload', function (e) {
            var sort_e = $("#sort  option:selected");
            if (sort_e.length > 0) {
                e.params.is_sort = $("#sort  option:selected").val();
            }
            tableStore.set("params", e.params);
        })
        
        searchFormFormListeners['beforesubmit'].push(function (ev) {
//            if ($('#table_pager .bui-pb-page').val() == 1) {
                get_summary();
//            }
        });
        get_summary();
        function get_summary() {
            var obj = searchFormForm.serializeToObject();
            var url = "?app_act=prm/inv/get_inv_summary&app_fmt=json";
            $.post(url, obj, function (result) {
                var str = "|&nbsp;&nbsp;总在途库存：" + result.data.road_num + "&nbsp;&nbsp;|&nbsp;&nbsp;总缺货库存：" + result.data.out_num + "&nbsp;&nbsp;|&nbsp;&nbsp;总可用库存：" + result.data.available_mum + "&nbsp;&nbsp;|&nbsp;&nbsp;总锁定库存：" + result.data.lock_num + " &nbsp;&nbsp;|&nbsp;&nbsp;总实物库存：" + result.data.stock_num + " &nbsp;&nbsp;|&nbsp;&nbsp;总安全库存：" + result.data.safe_num + " ";
                $('#summary').html(str);
            }, 'json');

        }
        $('#searchForm').append('<input id="barcode_group_val"  name="barcode_group_val" type="hidden" value=""  />');
        $('#barcode_group').click(function(){
                 var columns = tableGrid.get('columns');
            if($(this).is(':checked')){
//               console.log(tableGrid.get('columns'))  ;

                 columns[0].set('visible',false);
                $("#barcode_group_val").val(1);

                $('#is_num').attr('disabled',true);
                $('#less_than_safe_num').attr('disabled',true);
                $('#num_start').attr('disabled',true);
                $('#num_end').attr('disabled',true);

            }else{
                columns[0].set('visible',true);
                $("#barcode_group_val").val(0);
                $('#is_num').attr('disabled',false);
                $('#less_than_safe_num').attr('disabled',false);
                $('#num_start').attr('disabled',false);
                $('#num_end').attr('disabled',false);
            }
            $.ajaxSetup({
                async: false
            });
            $('#btn-search').click();
            $('td.bui-grid-cell.bui-grid-cell-empty').css('width',0);
            $('.bui-grid-table').css('width','');
            $('th.bui-grid-hd-empty.bui-grid-hd').css('width',0);
            $.ajaxSetup({
                async: true
            });
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
<script type="text/javascript">
    //库存查询添加逗号分隔
    $('#keyword').attr('maxlength','10000');//输入框限制10000个字符长度
    $('#keyword').blur(function(){
        var str = $.trim($(this).val().replace((/[\r\n\"]/g),''));//去除换行双引号和前后空格
        new_str = str.replace((/\s+/g),',');
        //console.log(new_str);
        $(this).val(new_str);
    });  
</script>
<style>
#group{
    font-weight:bold;  
}
</style>

