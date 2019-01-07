<?php echo load_js('comm_util.js') ?>
<style>
    #money_start{width:50px;}
    #money_end{width:50px;}
    #pay_time_start{width:100px;}
    #pay_time_end{width:100px;}
    #record_time_start{width:100px;}
    #record_time_end{width:100px;}
</style>
<div class="page-header1" style="width: 98%; display: block; clear: both; position: fixed; top:0px; left:0px; background-color: #FFF; padding: 4px 1%; z-index: 9999; box-shadow:0px 0px 5px #ccc;">
    <span class="page-title"><h2>赠品工具</h2></span>
    <span class="page-link">
<!-- <span class="action-link">
        <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/order_gift/add')) { ?> 
        <a href="javascript:PageHead_show_dialog('?app_act=oms/order_gift/add&app_show_mode=pop', '一键添加赠品', {w:500,h:300})" class="button button-primary">一键添加赠品</a>
        </span>
        <?php } ?>
        <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/order_gift/delete')) { ?>
        <span class="action-link">
        <a href="javascript:PageHead_show_dialog('?app_act=oms/order_gift/delete&app_show_mode=pop', '一键删除赠品', {w:500,h:300})" class="button button-primary">
         一键删除赠品</a>
        </span>

        <?php } ?>   -->     
        <button class="button button-primary" onclick="javascript:location.reload();"><i class="icon-refresh icon-white"></i> 刷新</button>
    </span>
</div>
<div class="clear" style="margin-top: 40px; "></div>
<?php
render_control('SearchSelectButton', 'select_button', array(
    'fields' => array(
        array('id' => 'is_gift', 'title' => '有无赠品', 'children' => array(
                array('content' => '全部', 'id' => 'all', 'selected' => true,),
                array('content' => '有', 'id' => '1',),
                array('content' => '无', 'id' => '0',),
            )),
        array('id' => 'pay_type', 'title' => '货到付款', 'children' => array(
                array('content' => '全部', 'id' => 'all', 'selected' => true,),
                array('content' => '是', 'id' => 'cod',),
                array('content' => '否', 'id' => '0',),
            )),
        array('id' =>'is_fenxiao','title'=>'是否分销单','children'=>array(
                array('content'=>'全部','id'=>'all','selected'=>true,),
                array('content'=>'是','id'=>'1',),
                array('content'=>'否','id'=>'0',),
        )),
        
    ),
    'for' => 'searchForm',
    'style' => 'width:192px;'
));
?>
<?php
//订单状态
// $order_status = array(
//         'arr' => '请选择',
//         '0' => '待确认',
//         '1' => '待通知配货',
//         '2' => '待生成波次单',
// );
$keyword_type = array();
$keyword_type['deal_code_list'] = '交易号';
$keyword_type['sell_record_code'] = '订单号';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['goods_name'] = '商品名称';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['combo_barcode'] = '套餐条形码';
$keyword_type = array_from_dict($keyword_type);
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
    ),
    'show_row' => 2,
    'fields' => array(
        array(
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'sale_channel_code',
            //'data' => load_model('base/SaleChannelModel')->get_select()
            'data' => load_model('base/SaleChannelModel')->get_my_select(),
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
            'label' => '仓库',
            'type' => 'select',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_select_purview_store(),
        ),
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
            'help' => '支持多交易号查询，用逗号隔开；
以下字段支持模糊查询：交易号、商品条形码、商品编码、商品名称;',
        ),
        // array(
        //     'label' => '订单状态',
        //     'type' => 'select',
        //     'id' => 'order_status',
        //     'data' => $order_status,
        //     ), 	
        array(
            'label' => '订单价格',
            'type' => 'group',
            'field' => 'money',
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'money_start', 'class' => 'input-small'),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'money_end', 'class' => 'input-small', 'remark' => '<input type="checkbox" id="contain_express_money">含运费'),
            )
        ),
        array(
            'label' => '挂起单',
            'type' => 'select',
            'id' => 'is_pending',
            'data' => ds_get_select_by_field('boolstatus', 1),
        ),
        array(
            'label' => '付款时间',
            'type' => 'group',
            'field' => 'daterange2',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'pay_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'pay_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '下单时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'record_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'record_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '订单标签',
            'type' => 'select_multi',
            'id' => 'order_tag',
            'data' => ds_get_select('order_label'),
        ),
        array(
            'label' => '商品数量',
            'type' => 'group',
            'field' => 'num',
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'num_start', 'class' => 'input-small'),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'num_end', 'class' => 'input-small', 'remark' => ''),
            ),
        ),
    )
));
?>

<ul id="ToolBar1" class="toolbar frontool">
    <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/order_gift/add')) { ?> 
        <li class="li_btns"><button class="button button-primary add_gift_btn">批量添加赠品</button></li>
    <?php } ?>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/order_gift/delete')) { ?> 
        <li class="li_btns"><button class="button button-primary delete_gift_btn" >批量删除赠品</button></li>
    <?php } ?>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_edit_express_code')) { ?> 
        <li class="li_btns"><button class="button button-primary btn_opt_edit_express_code">批量修改配送方式</button></li>
    <?php } ?>
    <div class="front_close">&lt;</div>
</ul>

<script>
    $(function () {
        function tools() {
            $(".frontool").animate({left: '0px'}, 1000);
            $(".front_close").click(function () {
                if ($(this).html() == "&lt;") {
                    $(".frontool").animate({left: '-100%'}, 1000);
                    $(this).html(">");
                    $(this).addClass("close_02").animate({right: '-10px'}, 1000);
                } else {
                    $(".frontool").animate({left: '0px'}, 1000);
                    $(this).html("<");
                    $(this).removeClass("close_02").animate({right: '0'}, 1000);
                }
            });
        }

        tools();
    })
    function PageHead_show_dialog(_url, _title, _opts) {

        new ESUI.PopWindow(_url, {
            title: _title,
            width: _opts.w,
            height: _opts.h,
            onBeforeClosed: function () {
                if (typeof _opts.callback == 'function')
                    _opts.callback();
            }
        }).show();
    }
</script>
<script>
    $(function () {
        var default_opts = ['opt_lock', 'opt_unlock', 'opt_intercept'];
        for (var i in default_opts) {
            var f = default_opts[i];
            btn_init_opt("ToolBar1", f);
        }
        var custom_opts = $.parseJSON('');
        for (var j in custom_opts) {
            var g = custom_opts[j];
            $("#ToolBar1 .btn_" + g['id']).click(eval(g['custom']));
        }
    });
</script>

<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单号',
                'field' => 'sell_record_code',
                'width' => '120',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({sell_record_code})">{sell_record_code}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'deal_code_list',
                'width' => '150',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({sell_record_code})">{deal_code_list}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '销售平台',
                'field' => 'sale_channel_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_name',
                'width' => '140',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'store_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '下单时间',
                'field' => 'record_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '付款时间',
                'field' => 'pay_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单应付款',
                'field' => 'payable_money',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '有无赠品',
                'field' => 'is_gift_status',
                'width' => '80',
                'align' => ''
            ),
        // array(
        //     'type' => 'text',
        //     'show' => 1,
        //     'title' => '订单状态',
        //     'field' => 'status',
        //     'width' => '120',
        //     'align' => ''
        // ),
        // array(
        //     'type' => 'text',
        //     'show' => 1,
        //     'title' => '波次状态',
        //     'field' => 'waves_record_status',
        //     'width' => '120',
        //     'align' => ''
        // ),
        )
    ),
    'dataset' => 'oms/OrderGiftModel::do_list_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'sell_record_code',
    'CheckSelection' => true,
    //'customFieldTable'=>'order_gift_do_list/table',
    //'init' => 'nodata',
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
    'params' => array('filter' => array('list_type' => 'order_gift_do_list')),
    'CascadeTable' => array(
        'list' => array(
            array('title' => '商品图片', 'type' => 'text', 'width' => '100', 'field' => 'pic_path'),
            array('title' => '商品名称', 'type' => 'text', 'width' => '100', 'field' => 'goods_name'),
            array('title' => '商品编码', 'type' => 'text', 'width' => '100', 'field' => 'goods_code'),
            array('title' => '商品条形码', 'type' => 'text', 'width' => '100', 'field' => 'barcode'),
            array('title' => $response['goods_spec1_rename'], 'type' => 'text', 'width' => '100', 'field' => 'spec1_name'),
            array('title' => $response['goods_spec2_rename'], 'type' => 'text', 'width' => '100', 'field' => 'spec2_name'),
            array('title' => '数量（实物锁定数）', 'type' => 'text', 'width' => '100', 'field' => 'num', 'format_js' => array('type' => 'html', 'value' => '{num}(<span style="color:red">{lock_num}</span>)',)),
            array('title' => '标准价', 'type' => 'text', 'width' => '100', 'field' => 'goods_price'),
            array('title' => '单价', 'type' => 'text', 'width' => '100', 'field' => 'goods_price'),
            array('title' => '均摊金额', 'type' => 'text', 'width' => '100', 'field' => 'avg_money'),
            array('title' => '预售', 'type' => 'text', 'width' => '100', 'field' => 'sale_mode', 'format_js' => array('type' => 'map', 'value' => array('stock' => '否', 'presale' => '是'))),
            array('title' => '赠品', 'type' => 'text', 'width' => '100', 'field' => 'is_gift', 'format_js' => array('type' => 'map', 'value' => array('0' => '否', '1' => '是'))),
            array('title' => '计划发货时间', 'type' => 'text', 'width' => '100', 'field' => 'plan_send_time'),
        ),
        'page_size' => 10,
        'url' => get_app_url('oms/sell_record/get_detail_list_by_sell_record_code&app_fmt=json'),
        'params' => 'sell_record_code',
        'ExpandCascadeDetail' => array(
            'detail_url' => get_app_url('oms/sell_record/get_ex_list_cascade_data'), //查询展开详情的方法
            'detail_param' => 'sell_record_code', //查询展开详情的使用的参数
        ),
    ),
    
));
?>
<div id="u15" class="text" style="color:red;">
    <p></p>
    <p></p>
    <p></p>
    <p><span style="font-family:'Applied Font Regular', 'Applied Font';">说明：只针对已付款、未确认的销售订单（包括货到付款订单）添加赠品</span></p>
</div>

<script type="text/javascript">

    function view(sell_record_code) {
        var url = '?app_act=oms/sell_record/view&sell_record_code=' + sell_record_code
        openPage(window.btoa(url), url, '订单详情');
    }

    $(".add_gift_btn").click(function () {
        get_checked($(this), function (ids) {
            var url = '?app_act=oms/order_gift/add&app_show_mode=pop&id=' + ids;
            PageHead_show_dialog(url, '批量添加赠品', {w: 500, h: 400});
        })
    });

    $(".delete_gift_btn").click(function () {
        get_checked($(this), function (ids) {
            var url = '?app_act=oms/order_gift/delete&app_show_mode=pop&id=' + ids;
            PageHead_show_dialog(url, '批量删除赠品', {w: 500, h: 400});
        })
    });

//批量修改配送方式
    $(".btn_opt_edit_express_code").click(function () {
        get_checked($(this), function (ids) {
            new ESUI.PopWindow("?app_act=oms/sell_record/edit_express_code&sell_record_code_list=" + ids.toString(), {
                title: "批量修改配送方式",
                width: 500,
                height: 250,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    //刷新数据
                    tableStore.load()
                }
            }).show()
        })
    });

    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('prm/brand/do_delete'); ?>', data: {brand_id: row.brand_id},
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
    var url = '<?php echo get_app_url('base/store/get_area'); ?>';
    $(document).ready(function () {
        tableStore.on('beforeload', function (e) {
            e.params.contain_express_money = $("#contain_express_money").attr('checked') == 'checked' ? '1' : '0';
            e.params.is_gift = $("#is_gift").find(".active").attr("id");
            e.params.pay_type = $("#pay_type").find(".active").attr("id");
            e.params.notice_flag = $("#notice_flag").find(".active").attr("id");
            e.params.shipping_flag = $("#shipping_flag").find(".active").attr("id");
            e.params.cancel_flag = $("#cancel_flag").find(".active").attr("id");
            tableStore.set("params", e.params);
        });
        $('#country').change(function () {
            var parent_id = $(this).val();
            areaChange(parent_id, 0, url);
        });
        $('#province').change(function () {
            var parent_id = $(this).val();
            areaChange(parent_id, 1, url);
        });
        $('#city').change(function () {
            var parent_id = $(this).val();
            areaChange(parent_id, 2, url);
        });
        $('#district').change(function () {
            var parent_id = $(this).val();
            areaChange(parent_id, 3, url);
        });
        $('#country').val('1');
        $('#country').change();

//        $('#btn-csv').click(function(){
//        	report_excel();
//        });
    })
//读取已选中项
    function get_checked(obj, func) {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择订单", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.sell_record_code);
        }
        ids.join(',');
        func.apply(null, [ids]);
        // BUI.Message.Show({
        //     title: '自定义提示框',
        //     msg: '是否执行订单' + obj.text() + '?',
        //     icon: 'question',
        //     buttons: [
        //         {
        //             text: '是',
        //             elCls: 'button button-primary',
        //             handler: function() {
        //                 func.apply(null, [ids]);
        //                 this.close();
        //             }
        //         },
        //         {
        //             text: '否',
        //             elCls: 'button',
        //             handler: function() {
        //                 this.close();
        //             }
        //         }
        //     ]
        // });
    }

    //初始化批量操作按钮
    function btn_init_opt(tab_id, id) {
        $("#" + tab_id + " .btn_" + id).click(function () {
            get_checked($(this), function (ids) {
                var params = {"sell_record_code_list": ids, "type": id};
                $.post("?app_act=oms/sell_record/opt_batch", params, function (data) {
                    if (data.status == 1) {
                        BUI.Message.Alert(data.message, 'info')
                        //刷新
                        tableStore.load()
                    } else {
                        BUI.Message.Alert(data.message, 'error')
                    }
                }, "json");
            })
        });
    }

    function showDetail(index, row) {
        openPage('<?php echo base64_encode('?app_act=oms/sell_record/view&sell_record_code=') ?>' + row.sell_record_code, '?app_act=oms/sell_record/view&ref=do&sell_record_code=' + row.sell_record_code, '订单详情');
    }
</script>

<?php include_once (get_tpl_path('process_batch_task')); ?>