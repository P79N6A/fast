<style type="text/css">
    #record_time_start,#record_time_end,#pay_time_start,#pay_time_end{
        width:100px;
    }
    #expand_all_detail{
        border: none;
        width: 140px;
        height: 28px;
        font-size: 16px;
        color: #FFF;
        background-color: #1695ca;
    }
</style>
<?php

$links = array();
if ($response['order_down_priv'] == TRUE) {
    $links[] = array('url' => 'oms/api_order/down&app_show_mode', 'title' => '交易下载', 'is_pop' => true, 'pop_size' => '800,500');
}
render_control('PageHead', 'head1', array('title' => '平台交易列表',
    'links' => $links,
    'ref_table' => 'table'
));
?>

<?php
//库存同步
$status = array(
    '1' => '是',
    'all' => '全部',
    '0' => '否',
);
$record_time_start = date("Y-m-d 00:00:00", strtotime('-3 day'));
$record_time_end = date("Y-m-d 23:59:59");
$keyword_type = array();
$keyword_type['tid'] = '交易号';
$keyword_type['buyer_nick'] = '买家昵称';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['goods_barcode'] = '商品条形码';
$keyword_type['buyer_remark'] = '买家备注';
$keyword_type['seller_remark'] = '卖家备注';
$keyword_type = array_from_dict($keyword_type);
$status = array_from_dict($status);
//交易类型
$type = array_merge(array('' => '全部'), $response['type']);
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
    'show_row' => 3,
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
            'help' => '查询交易号为唯一查询，其他查询条件无效',
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
            'label' => '销售平台',
            'type' => 'select',
            'id' => 'source',
            //'data' => load_model('base/SaleChannelModel')->get_select(),
            'data' => load_model('base/SaleChannelModel')->get_my_select(),
        ),
        array(
            'label' => '转单状态',
            'type' => 'select',
            'id' => 'is_change',
            'data' => oms_opts_by_md('oms/SellRecordModel', 'tran_status', 1),
        ),
        array(
            'label' => '是否允许转单',
            'type' => 'select',
            'id' => 'status',
            'data' => $status,
        ),
        array(
            'label' => '交易类型',
            'type' => 'select',
            'id' => 'type',
            'data' => array_from_dict($type),
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
            'label' => '付款时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'pay_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'pay_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '包含代销商品',
            'type' => 'select',
            'id' => 'is_daixiao',
            'data' => ds_get_select_by_field('is_rush'),
        ),
        array(
            'label' => '卖家旗帜',
            'type' => 'select_multi',
            'id' => 'seller_flag',
            'data' => load_model('util/FormSelectSourceModel')->get_seller_flag(),
        ),
    )
));
?>
<?php if ($response['change_fail_num'] > 0) { ?>
    <span>
        <a name="order_change_fail" class="order_change_fail" style="cursor:pointer">
            <font color="red"> 转单失败订单(<span id="order_change_fail_num"><?php echo $response['change_fail_num']; ?></span>) 请及时修改交易并手工转单，系统不会自动处理失败的交易</font>
        </a>
    </span>
<?php } ?>

<ul class="toolbar frontool">
    <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/pl_td_tran')) { ?>
        <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="pl_td_tran()">批量转单</button></li>
    <?php } ?>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/pl_td_traned')) { ?>
        <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="set_traned()">批量置为已转单</button></li>
    <?php } ?>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/pl_td_untraned')) { ?>
        <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="set_no_traned()">批量置为未转单</button></li>
    <?php } ?>

    <!--li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="active('enable')">批量置为未转单</button></li-->
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

        $("#record_time_start").val("<?php echo $record_time_start ?>");
        $("#record_time_end").val("<?php echo $record_time_end ?>");
    })
</script>

<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '100',
                'align' => '',
                'buttons' => array(
                    array('id' => 'view', 'title' => '详情',
                        'act' => 'pop:oms/sell_record/td_view&id={id}', 'show_name' => '详情', 'show_cond' => '',
                        'priv' => 'oms/sell_record/td_view', 'pop_size' => '920,600'),
                    //array('id'=>'view', 'title' => '详情', 'act'=>'oms/sell_record/td_view&id={id}', 'show_name'=>'详情', 'show_cond'=>'', 'pop_size'=>'920,600'),
                    array('id' => 'tran', 'title' => '转单', 'callback' => 'td_tran', 'confirm' => '确认要转单吗？',
                        'priv' => 'oms/sell_record/td_tran', 'show_cond' => 'obj.status == 1  && obj.is_change <= 0'),
//                    array('id'=>'traned', 'title' => '置为已转单', 'callback'=>'td_traned','confirm'=>'确认要置为已转单吗？',
//                    'priv'=>'oms/sell_record/td_traned','show_cond' =>'obj.status == 1  && obj.is_change <= 0' ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '日志',
                'field' => 'change_remark',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '销售平台',
                'field' => 'source_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单下载时间',
                'field' => 'first_insert_time',
                'width' => '100',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单变更时间',
                'field' => 'last_update_time',
                'width' => '100',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '下单时间',
                'field' => 'order_first_insert_time',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '付款时间',
                'field' => 'pay_time',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'tid',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '允许转单',
                'field' => 'status',
                'width' => '80',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家备注',
                'field' => 'buyer_remark',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '卖家备注',
                'field' => 'seller_remark',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家昵称',
                'field' => 'buyer_nick',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '数量',
                'field' => 'num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '金额',
                'field' => 'order_money',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收货人',
                'field' => 'receiver_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '邮费',
                'field' => 'youfei',
                'width' => '100',
                'align' => ''
            ),            
            /*       array (
              'type' => 'text',
              'show' => 1,
              'title' => '平台标签',
              'field' => 'tag_name',
              'width' => '150',
              'align' => ''
              ), */
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '转单状态',
                'field' => 'is_change',
                'width' => '100',
                'align' => '',
                'format_js' => array(
                    'type' => 'map',
                    'value' => array(
                        '1' => '已转单',
                        '0' => '未转单',
                        '-1' => '未转单',
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '系统订单号',
                'field' => 'sell_record_code',
                'width' => '120',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({sell_record_code})">{sell_record_code}</a>',
                ),
            ),
        )
    ),
    'dataset' => 'oms/ApiOrderModel::get_by_page',
    'queryBy' => 'searchForm',
    'export' => array('id' => 'exprot_list', 'conf' => 'api_order_list', 'name' => '平台订单', 'export_type' => 'file'),
    'idField' => 'id',
    'CheckSelection' => true,
    'CascadeTable' => array(
        'list' => array(
            array('title' => '商品图片', 'type' => 'text', 'width' => '100', 'field' => 'pic_path', 'format_js' => array('type' => 'html', 'value' => '<img src="{pic_path}" style="width:48px; height:48px;">')),
            array('title' => '商品名称', 'type' => 'text', 'width' => '150', 'field' => 'title'),
            array('title' => '商品编码', 'type' => 'text', 'width' => '150', 'field' => 'goods_code'),
            array('title' => '商品条形码', 'type' => 'text', 'width' => '150', 'field' => 'goods_barcode'),
            array('title' => '商品属性', 'type' => 'text', 'width' => '160', 'field' => 'sku_properties'),
            array('title' => '数量', 'type' => 'text', 'width' => '100', 'field' => 'num'),
            array('title' => '金额', 'type' => 'text', 'width' => '100', 'field' => 'avg_money'),
            array('title' => '平台礼品', 'type' => 'text', 'width' => '100', 'field' => 'is_gift', 'format_js' => array('type' => 'map', 'value' => array('0' => '否', '1' => '是'))),
        ),
        'page_size' => 10,
        'url' => get_app_url('oms/sell_record/get_detail_list_by_tid&app_fmt=json'),
        'params' => 'tid',
        'ExpandCascadeDetail' => array(
            'detail_url' => get_app_url('oms/sell_record/get_td_list_cascade_data'),//查询展开详情的方法
            'detail_param' => 'tid',//查询展开详情的使用的参数
        ),
    ),
    'init' => 'nodata',
    'customFieldTable' => 'oms/sell_record_td_list',
        //'RowNumber'=>true,
        //'CheckSelection'=>true,
));
?>
<script type="text/javascript">
    var nodata = $(".nodata").text() === '' ?  0 : 1;
    $("#tid").css('border', '1px solid red');

    $(function () {
        $(".order_change_fail").click(function () {
            $("#is_change").val(-1);
            $("#record_time_start").val('');
            $("#record_time_end").val('');
            $("#btn-search").click();
        });
    });
    
    //转单
    function td_tran(index, row) {
        var d = {"api_order_id": row.id, 'app_fmt': 'json'};
        $.post('<?php echo get_app_url('oms/sell_record/td_tran'); ?>', d, function (data) {
            //alert(data);
            //console.log(data);
            //console.log('sss');
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            if (data.change_fail_num > 0) {
                $("#order_change_fail_num").html(data.change_fail_num);
            }
            tableStore.load();
        }, "json");
    }
//批量转单
    function pl_td_tran() {
        var ids = '';
        var check_id_arr = tableGrid.getSelection();
        for (var i = 0; i < check_id_arr.length; i++) {
            ids += check_id_arr[i].id + ",";
        }
        ids = ids.substring(0, ids.length - 1);
        if (ids.length == 0) {
            BUI.Message.Alert('请勾选需转订单', 'error');
            return;
        }

        var d = {"api_order_id": ids, 'app_fmt': 'json'};
        $.post('<?php echo get_app_url('oms/sell_record/td_tran'); ?>', d, function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            if (data.change_fail_num > 0) {
                $("#order_change_fail_num").html(data.change_fail_num);
            }
            tableStore.load();
        }, "json");
    }

    //标记为已转单
    function td_traned(index, row) {
        var d = {"id": row.id};
        $.post('<?php echo get_app_url('oms/sell_record/td_traned'); ?>', d, function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            tableStore.load();
        }, "json");
    }
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

    function set_traned() {
        var ids = '';
        var check_id_arr = tableGrid.getSelection();
        for (var i = 0; i < check_id_arr.length; i++) {
            ids += check_id_arr[i].id + ",";
        }
        ids = ids.substring(0, ids.length - 1);
        if (ids.length == 0) {
            BUI.Message.Alert('请勾选需转订单', 'error');
            return;
        }
        var d = {"id": ids};
        $.post('<?php echo get_app_url('oms/sell_record/td_traned'); ?>', d, function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            tableStore.load();
        }, "json");
    }

    function set_no_traned() {
        var ids = '';
        var check_id_arr = tableGrid.getSelection();
        for (var i = 0; i < check_id_arr.length; i++) {
            ids += check_id_arr[i].id + ",";
        }
        ids = ids.substring(0, ids.length - 1);
        if (ids.length == 0) {
            BUI.Message.Alert('请勾选需转订单', 'error');
            return;
        }
        var d = {"id": ids};
        $.post('<?php echo get_app_url('oms/sell_record/td_no_traned'); ?>', d, function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            tableStore.load();
        }, "json");
    }
    
    function view(sell_record_code) {
        var url = '?app_act=oms/sell_record/view&sell_record_code=' + sell_record_code;
        openPage(window.btoa(url), url, '订单详情');
    }
</script>



