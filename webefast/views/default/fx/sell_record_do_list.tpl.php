<?php echo load_js('comm_util.js') ?>
<style>
    #money_start{width:50px;}
    #money_end{width:50px;}
    #pay_time_start{width:100px;}
    #pay_time_end{width:100px;}
    #record_time_start{width:100px;}
    #record_time_end{width:100px;}
    #delivery_time_start{width:100px;}
    #delivery_time_end{width:100px;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '分销订单查询',
    'links' => array(
    //array('url'=>'oms/sell_record/add', 'title'=>'新增订单', 'is_pop'=>false, 'pop_size'=>'500,400'),
    ),
    'ref_table' => 'table',
));
?>
<?php
render_control('SearchSelectButton', 'select_button', array(
    'fields' => array(
        array('id' => 'custom_type', 'title' => '分销类型', 'children' => array(
                array('content' => '全部', 'id' => 'all', 'selected' => true),
                array('content' => '淘分销', 'id' => 'tb_fx'),
                array('content' => '普通分销', 'id' => 'pt_fx'),
            )),
        array('id' => 'is_fx_settlement', 'title' => '结算状态', 'children' => array(
                array('content' => '全部', 'id' => 'all', 'selected' => true),
                array('content' => '未结算', 'id' => '0'),
                array('content' => '已结算', 'id' => '1'),
            )),
        array('id' => 'order_status', 'title' => '确认状态', 'children' => array(
                array('content' => '全部', 'id' => 'all', 'selected' => true),
                array('content' => '未确认', 'id' => '0'),
                array('content' => '已确认', 'id' => '1'),
            )),
        array('id' => 'notice_flag', 'title' => '通知配货状态', 'children' => array(
                array('content' => '全部', 'id' => 'all', 'selected' => true),
                array('content' => '未通知', 'id' => '0'),
                array('content' => '已通知', 'id' => '1'),
            )),
        array('id' => 'shipping_flag', 'title' => '发货状态', 'children' => array(
                array('content' => '全部', 'id' => 'all', 'selected' => true),
                array('content' => '未发货', 'id' => '0'),
                array('content' => '已发货', 'id' => '1'),
            )),
        array('id' => 'cancel_flag', 'title' => '作废状态', 'children' => array(
                array('content' => '全部', 'id' => 'all', 'selected' => true),
                array('content' => '未作废', 'id' => '0'),
                array('content' => '已作废', 'id' => '1'),
            )),
    ),
    'for' => 'searchForm',
    'style' => 'width:192px;',
));
?>
<?php
$keyword_type = array();
$keyword_type['deal_code_list'] = '交易号';
$keyword_type['sell_record_code'] = '订单号';
$keyword_type['buyer_name'] = '买家昵称';
$keyword_type['receiver_mobile'] = '手机号码';
$keyword_type['receiver_name'] = '收货人';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['goods_name'] = '商品名称';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['express_no'] = '快递单号';
$keyword_type['fenxiao_name'] = '分销商';
if ($response['unique_status'] == 1) {
    $keyword_type['unique_code'] = '唯一码';
}
$keyword_type = array_from_dict($keyword_type);
$is_buyer_remark = array();
$is_buyer_remark['all'] = '买家留言';
$is_buyer_remark[1] = '有买家留言';
$is_buyer_remark[0] = '无买家留言';
$is_buyer_remark = array_from_dict($is_buyer_remark);
$is_seller_remark = array();
$is_seller_remark['all'] = '商家留言';
$is_seller_remark[1] = '有商家留言';
$is_seller_remark[0] = '无商家留言';
$is_seller_remark = array_from_dict($is_seller_remark);
$buttons = array(
    array(
        'label' => '查询',
        'id' => 'btn-search',
        'type' => 'submit',
    ),
    array(
        'label' => '导出',
        'id' => 'exprot_list',
    ),
);
$buttons[] = array(
    'label' => '导出明细',
    'id' => 'exprot_detail',
);
$fields = array(
    array(
        'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
        'type' => 'input',
        'title' => '',
        'data' => $keyword_type,
        'id' => 'keyword',
        'help' => '支持多交易号、多订单号查询，用逗号隔开；
以下字段支持模糊查询：交易号、订单号、买家昵称、手机号码、商品条形码、商品编码',
    ),
    array(
        'label' => '店铺',
        'type' => 'select_multi',
        'id' => 'shop_code',
        'data' =>  load_model('base/ShopModel')->get_purview_ptfx_shop('all_fx'),
    ),
    array(
        'label' => '销售平台',
        'type' => 'select_multi',
        'id' => 'sale_channel_code',
        //'data' => load_model('base/SaleChannelModel')->get_select(),
        'data' => load_model('base/SaleChannelModel')->get_my_select()
    ),
    array(
        'label' => '发货时间',
        'type' => 'group',
        'field' => 'daterange',
        'child' => array(
            array('title' => 'start', 'type' => 'time', 'field' => 'delivery_time_start'),
            array('pre_title' => '~', 'type' => 'time', 'field' => 'delivery_time_end', 'remark' => ''),
        )
    ),
    array(
        'label' => '配送方式',
        'type' => 'select_multi',
        'id' => 'express_code',
        'data' => ds_get_select('express'),
    ),
    array(
        'label' => '仓库',
        'type' => 'select_multi',
        'id' => 'store_code',
        'data' => load_model('base/StoreModel')->get_purview_store(),
    ),
    array(
        'label' => '旗帜',
        'type' => 'select_multi',
        'id' => 'seller_flag',
        'data' => load_model('util/FormSelectSourceModel')->get_seller_flag(),
    ),
    array(
        'label' => '订单性质',
        'type' => 'select_multi',
        'id' => 'sell_record_attr',
        'data' => load_model('FormSelectSourceModel')->sell_record_attr(),
    ),
    array(
        'label' => '支付方式',
        'type' => 'select_multi',
        'id' => 'pay_type',
        'data' => ds_get_select('pay_type'),
    ),
    array(
        'label' => '国家',
        'type' => 'select',
        'id' => 'country',
        'data' => ds_get_select('country', 2),
    ),
    array(
        'label' => '省份',
        'type' => 'select',
        'id' => 'province',
        'data' => array(),
    ),
    array(
        'label' => '城市',
        'type' => 'select',
        'id' => 'city',
        'data' => array(),
    ),
    array(
        'label' => '地区',
        'type' => 'select',
        'id' => 'district',
        'data' => array(),
    ),
    array(
        'label' => '详细地址',
        'type' => 'input',
        'id' => 'receiver_addr',
        'title' => '支持以逗号分隔的模糊查询',
    ),
    array(
        'label' => array('id' => 'is_buyer_remark', 'type' => 'select', 'data' => $is_buyer_remark),
        'type' => 'input',
        'id' => 'buyer_remark',
        'title' => '支持模糊查询',
    ),
    array(
        'label' => array('id' => 'is_seller_remark', 'type' => 'select', 'data' => $is_seller_remark),
        'type' => 'input',
        'id' => 'seller_remark',
        'title' => '支持模糊查询',
    ),
    array(
        'label' => '下单时间',
        'type' => 'group',
        'field' => 'daterange1',
        'child' => array(
            array('title' => 'start', 'type' => 'time', 'field' => 'record_time_start'),
            array('pre_title' => '~', 'type' => 'time', 'field' => 'record_time_end', 'remark' => ''),
        ),
    ),
    array(
        'label' => '付款时间',
        'type' => 'group',
        'field' => 'daterange2',
        'child' => array(
            array('title' => 'start', 'type' => 'time', 'field' => 'pay_time_start'),
            array('pre_title' => '~', 'type' => 'time', 'field' => 'pay_time_end', 'remark' => ''),
        ),
    ),
    array(
        'label' => '计划发货时间',
        'type' => 'group',
        'field' => 'daterange4',
        'child' => array(
            array('title' => 'start', 'type' => 'date', 'field' => 'plan_send_time_start'),
            array('pre_title' => '~', 'type' => 'date', 'field' => 'plan_send_time_end', 'remark' => ''),
        ),
    ),
    array(
        'label' => '有无发票',
        'type' => 'select',
        'id' => 'is_invoice',
        'data' => ds_get_select_by_field('havestatus', 2),
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
    array(
        'label' => '订单价格',
        'type' => 'group',
        'field' => 'money',
        'child' => array(
            array('title' => 'start', 'type' => 'input', 'field' => 'money_start', 'class' => 'input-small'),
            array('pre_title' => '~', 'type' => 'input', 'field' => 'money_end', 'class' => 'input-small', 'remark' => '<input type="checkbox" id="contain_express_money">含运费'),
        ),
    ),
);

render_control('SearchForm', 'searchForm', array(
    'buttons' => $buttons,
    'show_row' => 1,
    'fields' => $fields,
));
?>

<ul id="ToolBar1" class="toolbar frontool">
    <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_intercept_do_list')) { ?>
        <li class="li_btns"><button class="button button-primary _sys_batch_task_btn" task_info="{act:'app_act=oms/sell_record/opt&type=opt_intercept',obj_name:'订单',ids_params_name:'sell_record_code'}">批量订单拦截</button></li>
    <?php }
    ?>
    <?php #if (load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_lock')) { ?>
    <!-- <li class="li_btns"><button class="button button-primary _sys_batch_task_btn" task_info="{act:'app_act=oms/sell_record/opt&type=opt_lock',obj_name:'订单',ids_params_name:'sell_record_code'}">批量锁定</button></li> -->
    <?php #}  ?>
    <?php #if (load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_unlock')) { ?>
    <!-- <li class="li_btns"><button class="button button-primary _sys_batch_task_btn" task_info="{act:'app_act=oms/sell_record/opt&type=opt_unlock',obj_name:'订单',ids_params_name:'sell_record_code'}">批量解锁</button></li> -->
    <?php #}  ?>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_pending')) { ?>
        <li class="li_btns"><button class="button button-primary btn_opt_pending ">批量挂起</button></li>
    <?php }
    ?>
    <li class="li_btns"><button class="button button-primary btn_cancel">批量作废</button></li>
    <li class="li_btns"><button class="button button-primary btn_opt_label ">批量打标</button></li>
    <div class="front_close">&lt;</div>
</ul>
<script>
    $(function() {
        function tools() {
            $(".frontool").animate({left: '0px'}, 1000);
            $(".front_close").click(function() {
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

    });
</script>
<script>
    $(function() {
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
                'title' => '订单图标',
                'field' => 'status_text',
                'width' => '80',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单号',
                'field' => 'sell_record_code',
                'width' => '130',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({sell_record_code})">{sell_record_code}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_name',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台',
                'field' => 'sale_channel_name',
                'width' => '60',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分销商',
                'field' => 'fenxiao_name',
                'width' => '100',
                'align' => '',
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
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '仓库货权',
//                'field' => 'fenxiao_power_name',
//                'width' => '70',
//                'align' => '',
//            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '结算金额',
                'field' => 'fx_payable_money',
                'width' => '70',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分销结算运费',
                'field' => 'fx_express_money',
                'width' => '100',
                'align' => '',
            ),
            
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'status',
                'width' => '155',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收货人',
                'field' => 'receiver_name',
                'width' => '70',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '手机',
                'field' => 'receiver_mobile',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收货地址',
                'field' => 'receiver_address',
                'width' => '250',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '配送方式',
                'field' => 'express_name',
                'width' => '70',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '下单时间',
                'field' => 'record_time',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '发货时间',
                'field' => 'delivery_time',
                'width' => '120',
                'align' => '',
                'sortable'=>true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '快递单号',
                'field' => 'express_no',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商家留言',
                'field' => 'seller_remark',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家留言',
                'field' => 'buyer_remark',
                'width' => '100',
                'align' => '',
            ),
            
        ),
    ),
    'dataset' => 'oms/SellRecordModel::do_list_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'sell_record_code',
    'export' => array('id' => 'exprot_detail', 'conf' => 'fx_search_record_detail', 'name' => '订单查询明细', 'export_type' => 'file'),
    'CascadeTable' => array(
        'list' => array(
            array('title' => '商品名称', 'type' => 'text', 'width' => '100', 'field' => 'goods_name'),
            array('title' => '商品编码', 'type' => 'text', 'width' => '100', 'field' => 'goods_code'),
            array('title' => '商品条形码', 'type' => 'text', 'width' => '100', 'field' => 'barcode'),
            array('title' => $response['goods_spec1_rename'], 'type' => 'text', 'width' => '100', 'field' => 'spec1_name'),
            array('title' => $response['goods_spec2_rename'], 'type' => 'text', 'width' => '100', 'field' => 'spec2_name'),
            array('title' => '数量（实物锁定数）', 'type' => 'text', 'width' => '100', 'field' => 'num', 'format_js' => array('type' => 'html', 'value' => '{num}(<span style="color:red">{lock_num}</span>)')),
            array('title' => '单价', 'type' => 'text', 'width' => '100', 'field' => 'goods_price'),
            array('title' => '结算价', 'type' => 'text', 'width' => '100', 'field' => 'trade_price'),
            array('title' => '结算金额', 'type' => 'text', 'width' => '100', 'field' => 'fx_amount'),
            array('title' => '赠品', 'type' => 'text', 'width' => '100', 'field' => 'is_gift', 'format_js' => array('type' => 'map', 'value' => array('0' => '否', '1' => '是'))),
            array('title' => '计划发货时间', 'type' => 'text', 'width' => '100', 'field' => 'shipping_time'),
        ),
        'page_size' => 10,
        'url' => get_app_url('fx/sell_record/get_detail_list_by_sell_record_code&app_fmt=json'),
        'params' => 'sell_record_code',
    ),
    'CheckSelection' => true,
    'customFieldTable' => 'fx/sell_record_do_list',
    'init' => 'nodata',
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>


<script type="text/javascript">
    $(function() {
        $('#exprot_list').click(function() {
//        var url = '?app_act=ctl/index/do_index&app_ctl=DataTable/do_get_data';
            var url = '?app_act=sys/export_csv/export_show', //暂时不是框架级别
                    params = tableStore.get('params');

            params.ctl_type = 'export';
            params.ctl_export_conf = 'fx_search_record_list';
            params.ctl_export_name = '订单查询';
           <?php echo   create_export_token_js('oms/SellRecordModel::do_list_by_page');?>
            var obj = searchFormForm.serializeToObject();
            for (var key in obj) {
                params[key] = obj[key];
            }

            for (var key in params) {
                url += "&" + key + "=" + params[key];
            }
            params.ctl_type = 'view';
            //window.location.href = url;
            window.open(url);

        });
    });

    function view(sell_record_code) {
        var url = '?app_act=fx/sell_record/view&sell_record_code=' + sell_record_code + '&record_type=fx';
        openPage(window.btoa(url), url, '订单详情');
    }

    $(".btn_cancel").click(function() {
        get_checked($(this), function(ids) {
            var params = {"sell_record_id_list": ids};
            $.post("?app_act=oms/sell_record/cancel_all", params, function(data) {
                if (data.status == '1') {
                    //刷新数据
                    BUI.Message.Alert(data.message, 'success');
                    tableStore.load()
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json");

        })
    })

    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('prm/brand/do_delete'); ?>', data: {brand_id: row.brand_id},
            success: function(ret) {
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
    $(document).ready(function() {
        tableStore.on('beforeload', function(e) {
            e.params.contain_express_money = $("#contain_express_money").attr('checked') == 'checked' ? '1' : '0';
            e.params.is_fx_settlement = $("#is_fx_settlement").find(".active").attr("id");
            e.params.order_status = $("#order_status").find(".active").attr("id");
            e.params.notice_flag = $("#notice_flag").find(".active").attr("id");
            e.params.shipping_flag = $("#shipping_flag").find(".active").attr("id");
            e.params.cancel_flag = $("#cancel_flag").find(".active").attr("id");
            e.params.exist_fenxiao = 1;
            tableStore.set("params", e.params);
        });

        $('#country').change(function() {
            var parent_id = $(this).val();
            if(parent_id===''){
                parent_id=1;
            }
            areaChange(parent_id, 0, url);
        });
        $('#province').change(function() {
            var parent_id = $(this).val();
            areaChange(parent_id, 1, url);
        });
        $('#city').change(function() {
            var parent_id = $(this).val();
            areaChange(parent_id, 2, url);
        });
        $('#district').change(function() {
            var parent_id = $(this).val();
            areaChange(parent_id, 3, url);
        });
//        $('#country').val('1');
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
        if (obj.text() == '批量修改发货仓库' || obj.text() == '批量修改仓库留言' || obj.text() == '批量修改配送方式' || obj.text() == '批量挂起') {
            func.apply(null, [ids]);
        } else {
            BUI.Message.Show({
                title: '自定义提示框',
                msg: '是否执行订单' + obj.text() + '?',
                icon: 'question',
                buttons: [
                    {
                        text: '是',
                        elCls: 'button button-primary',
                        handler: function() {
                            func.apply(null, [ids]);
                            this.close();
                        }
                    },
                    {
                        text: '否',
                        elCls: 'button',
                        handler: function() {
                            this.close();
                        }
                    }
                ]
            });
        }
    }



    //初始化批量操作按钮
    function btn_init_opt(tab_id, id) {
        $("#" + tab_id + " .btn_" + id).click(function() {
            get_checked($(this), function(ids) {
                var params = {"sell_record_code_list": ids, "type": id};
                $.post("?app_act=oms/sell_record/opt_batch", params, function(data) {
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
        openPage('<?php echo base64_encode('?app_act=fx/sell_record/view&sell_record_code=') ?>' + row.sell_record_code, '?app_act=fx/sell_record/view&ref=do&sell_record_code=' + row.sell_record_code, '分销订单详情');
    }

//批量挂起

    $(".btn_opt_pending").click(function() {
        get_checked($(this), function(ids) {
            new ESUI.PopWindow("?app_act=oms/sell_record/pending&batch=<?php echo urlencode("批量操作"); ?>&sell_record_code_list=" + ids.toString(), {
                title: "批量挂起",
                width: 550,
                height: 480,
                onBeforeClosed: function() {
                },
                onClosed: function() {
                    //刷新数据
                    tableStore.load()
                }
            }).show()
        })
    });

//批量打标

    $(".btn_opt_label").click(function() {
        get_checked($(this), function(ids) {
            new ESUI.PopWindow("?app_act=oms/sell_record/label&batch=<?php echo urlencode("批量操作"); ?>&sell_record_code_list=" + ids.toString(), {
                title: "批量打标签",
                width: 500,
                height: 300,
                onBeforeClosed: function() {
                },
                onClosed: function() {
                    //刷新数据
                    tableStore.load()
                }
            }).show()
        })
    });


    function report_excel()
    {
        var searchForm = document.forms['searchForm'].elements;
        var param = "";

//    param=param+"&contain_express_money="+$("#contain_express_money").attr('checked')=='checked'?'1':'0';
        param = param + "&pay_status=" + $("#pay_status").find(".active").attr("id");
        param = param + "&order_status=" + $("#order_status").find(".active").attr("id");
        param = param + "&notice_flag=" + $("#notice_flag").find(".active").attr("id");
        param = param + "&shipping_flag=" + $("#shipping_flag").find(".active").attr("id");
        param = param + "&cancel_flag=" + $("#cancel_flag").find(".active").attr("id");

        param = param + "&deal_code_list=" + searchForm['deal_code_list'].value;
        param = param + "&pay_type=" + searchForm['pay_type'].value;
        param = param + "&sale_channel_code=" + searchForm['sale_channel_code'].value;
        param = param + "&shop_code=" + searchForm['shop_code'].value;
        param = param + "&store_code=" + searchForm['store_code'].value;
        param = param + "&alipay_no=" + searchForm['alipay_no'].value;
        param = param + "&buyer_name=" + searchForm['buyer_name'].value;
        param = param + "&goods_code=" + searchForm['goods_code'].value;
        param = param + "&barcode=" + searchForm['barcode'].value;

        param = param + "&is_change_record=" + searchForm['is_change_record'].value;
        param = param + "&seller_remark=" + searchForm['seller_remark'].value;
        param = param + "&buyer_remark=" + searchForm['buyer_remark'].value;

        param = param + "&order_remark=" + searchForm['order_remark'].value;
        param = param + "&receiver_name=" + searchForm['receiver_name'].value;
        param = param + "&receiver_mobile=" + searchForm['receiver_mobile'].value;

        param = param + "&express_code=" + searchForm['express_code'].value;
        param = param + "&express_no=" + searchForm['express_no'].value;
        param = param + "&country=" + searchForm['country'].value;

        param = param + "&province=" + searchForm['province'].value;
        param = param + "&city=" + searchForm['city'].value;
        param = param + "&district=" + searchForm['district'].value;

        param = param + "&receiver_addr=" + searchForm['receiver_addr'].value;
        param = param + "&is_stock_out=" + searchForm['is_stock_out'].value;
        param = param + "&sale_mode=" + searchForm['sale_mode'].value;

        param = param + "&is_lock_person=" + searchForm['is_lock_person'].value;
        param = param + "&is_lock=" + searchForm['is_lock'].value;
        param = param + "&is_pending=" + searchForm['is_pending'].value;

        param = param + "&is_problem=" + searchForm['is_problem'].value;
        param = param + "&is_handwork=" + searchForm['is_handwork'].value;
        param = param + "&is_combine=" + searchForm['is_combine'].value;

        param = param + "&is_split=" + searchForm['is_split'].value;
        param = param + "&is_copy=" + searchForm['is_copy'].value;
        param = param + "&num_start=" + searchForm['num_start'].value;

        param = param + "&num_end=" + searchForm['num_end'].value;
//	param=param+"&is_invoice="+searchForm['is_invoice'].value;
        param = param + "&invoice_title=" + searchForm['invoice_title'].value;

        param = param + "&contain_express_money=" + searchForm['contain_express_money'].value;
        param = param + "&money_start=" + searchForm['money_start'].value;
        param = param + "&money_end=" + searchForm['money_end'].value;

        param = param + "&record_time_start=" + searchForm['record_time_start'].value;
        param = param + "&record_time_end=" + searchForm['record_time_end'].value;
        param = param + "&pay_time_start=" + searchForm['pay_time_start'].value;

        param = param + "&pay_time_end=" + searchForm['pay_time_end'].value;
        param = param + "&send_time_start=" + searchForm['send_time_start'].value;
        param = param + "&send_time_end=" + searchForm['send_time_end'].value;

        param = param + "&plan_send_time_start=" + searchForm['plan_send_time_start'].value;
        param = param + "&plan_send_time_end=" + searchForm['plan_send_time_end'].value;
        param = param + "&weight_start=" + searchForm['weight_start'].value;
        param = param + "&weight_end=" + searchForm['weight_end'].value;
        param = param + "&action_type=export_csv&app_fmt=json";
        url = "?app_act=oms/sell_record/export_csv_list" + param;
        window.location.href = url;
    }


</script>

<?php include_once get_tpl_path('process_batch_task'); ?>
