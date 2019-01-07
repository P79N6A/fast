<?php echo load_js("baison.js", true); ?>
<style>
    #order_first_start, #order_first_end{width: 100px;}
</style>
<script>

</script>
<?php
$links = array();
if (load_model('sys/PrivilegeModel')->check_priv('api/sys/order_refund/down_priv')) {
    $links[] = array('url' => 'api/sys/order_refund/down', 'title' => '退单下载', 'is_pop' => true, 'pop_size' => '800,500');
}
render_control('PageHead', 'head1', array('title' => '平台退单列表',
    'links' => $links,
    'ref_table' => 'table'
));
?>

<?php
$keyword_type = array();
$keyword_type['refund_id'] = '退单编号';
$keyword_type['tid'] = '交易号';
$keyword_type['buyer_nick'] = '买家昵称';
$keyword_type = array_from_dict($keyword_type);
$is_buyer_remark = array();
//库存同步
$status = array(
    '1' => '是',
    'all' => '全部',
    '0' => '否',
);
$status = array_from_dict($status);
$order_first_start = date("Y-m-d", strtotime('-3 day')) . ' 00:00:00';
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
            'help' => '查询交易号为唯一查询，其他查询条件无效',
        ),
        array(
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'source',
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
            'label' => '处理状态',
            'type' => 'select',
            'id' => 'is_change',
            'data' => array_from_dict(array('0' => '未处理', '1' => '已处理', '-1' => '处理失败', 'all' => '全部')),
        ),
        array(
            'label' => '退货快递信息',
            'type' => 'select',
            'id' => 'refund_express_no',
            'data' => array_from_dict(array('all' => '全部', '1' => '有', '0' => '无')),
        ),
        array(
            'label' => '申请时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'order_first_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'order_first_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '是否允许转单',
            'type' => 'select',
            'id' => 'status',
            'data' => $status,
        ),
    )
));
?>
<script type="text/javascript">
    $(document).ready(function () {
        $("#order_first_start").val("<?php echo $order_first_start ?>");

        var do_search =<?php echo $response['do_search'] ?>;
        if (do_search == 1) {
            $("#keyword").val("<?php echo $response['refund_id'] ?>");
            $("#btn-search").click();
        }
        $(".order_change_fail").click(function () {
            $("#is_change").val(-1);
            $("#order_first_start").val('');
            $("#order_first_end").val('');
            $("#btn-search").click();
        });
   
    });

</script>
<?php if ($response['change_fail_num'] > 0) { ?>
    <span>
        <a name="order_change_fail" class="order_change_fail" style="cursor:pointer">
            <font color="red"> 转单失败订单(<span id="order_change_fail_num"><?php echo $response['change_fail_num']; ?></span>) 请及时修改交易并手工转单，系统不会自动处理失败的交易</font>
        </a>
    </span>
<?php } ?>
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
                'align' => 'center',
                'buttons' => array(
                    array('id' => 'view', 'title' => '详情',
                        'act' => 'pop:api/sys/order_refund/view&refund_id={refund_id}&from=pt_return', 'show_name' => '详情', 'pop_size' => '920,600'),
                    array('id' => 'tran', 'title' => '转退单', 'callback' => 'td_tran', 'confirm' => '确认要转退单吗？', 'show_cond' => 'obj.status == 1  && obj.is_change <= 0','priv'=>'oms/order_refund/set_refund'),
                    array('id' => 'no_allow', 'title' => '设为已处理', 'callback' => 'do_set_is_change', 'show_cond' => 'obj.is_change <= 0','priv'=>'oms/order_refund/set_is_change'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '销售平台',
                'field' => 'source',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '申请时间',
                'field' => 'order_first_insert_time',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '系统下载时间',
                'field' => 'first_insert_time',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退单编号',
                'field' => 'refund_id',
                'width' => '120',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'tid',
                'width' => '120',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '允许转单',
                'field' => 'status',
                'width' => '80',
                'align' => 'center',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家昵称',
                'field' => 'buyer_nick',
                'width' => '100',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退款金额',
                'field' => 'refund_fee',
                'width' => '100',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '转单状态',
                'field' => 'is_change',
                'width' => '100',
                'align' => 'center',
                'format_js' => array(
                    'type' => 'map',
                    'value' => array(
                        '0' => '未转单',
                        '-1' => '未转单',
                        '1' => '已转单',
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '日志',
                'field' => 'change_remark',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '系统退单号',
                'field' => 'refund_record_code',
                'width' => '120',
                'align' => 'center',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({refund_record_code})">{refund_record_code}</a>',
                ),
            ),
        )
    ),
    'dataset' => 'api/sys/OrderRefundModel::get_by_page',
    'export' => array('id' => 'exprot_list', 'conf' => 'order_refund_list', 'name' => '平台退单','export_type' => 'file'),
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'CascadeTable' => array(
        'list' => array(
            array('title' => '商品名称', 'type' => 'text', 'width' => '200', 'field' => 'title'),
            array('title' => '商品编码', 'type' => 'text', 'width' => '150', 'field' => 'goods_code'),
            array('title' => '商品条形码', 'type' => 'text', 'width' => '150', 'field' => 'goods_barcode'),
            array('title' => '商品属性', 'type' => 'text', 'width' => '160', 'field' => 'sku_properties'),
            array('title' => '数量', 'type' => 'text', 'width' => '60', 'field' => 'num'),
            array('title' => '金额', 'type' => 'text', 'width' => '60', 'field' => 'refund_price')
        ),
        'page_size' => 10,
        'url' => get_app_url('oms/sell_record/get_detail_list_by_tid_refund&app_fmt=json'),
        'params' => 'refund_id',
        'ExpandCascadeDetail' => array(
            'detail_url' => get_app_url('oms/sell_record/get_td_list_cascade_data_refund'),//查询展开详情的方法
            'detail_param' => 'refund_id',//查询展开详情的使用的参数
        ),
    ),
    'init' => 'nodata',
    'CheckSelection' => true,
));
?>
<div>
    <ul id="ToolBar1" class="toolbar frontool">
	<?php if ($response['power']['opt_set_refund']) {?>
        <li class="li_btns"><button class="button button-primary _sys_batch_task_force_btn" task_info="{act:'app_act=oms/sell_return/opt_td_tran',obj_name:'批量转退单',ids_params_name:'id'}">批量转退单</button></li>
	<?php }?>
	<?php if ($response['power']['opt_set_is_change']) {?>
        <li class="li_btns"><button class="button button-primary _sys_batch_task_force_btn" task_info="{act:'app_act=api/sys/order_refund/set_change_status&change=1',obj_name:'批量设为已处理',ids_params_name:'refund_id'}">批量设为已处理</button></li>
        <?php }?>
        <?php if ($response['power']['opt_set_no_change']) {?>
        <li class="li_btns"><button class="button button-primary _sys_batch_task_force_btn" task_info="{act:'app_act=api/sys/order_refund/set_change_status&change=0',obj_name:'批量设为未处理',ids_params_name:'refund_id'}">批量设为未处理</button></li>
        <?php }?>
        <div class="front_close">&lt;</div>
    </ul>
</div>
        
<script>
    
$(function(){
	function tools(){
            $(".frontool").css({left:'0px'});
            $(".front_close").click(function(){
                if($(this).html()=="&lt;"){
                    $(".frontool").animate({left:'-100%'},1000);
                    $(this).html(">");
	            $(this).addClass("close_02").animate({right:'-10px'},1000);
                }else{
                    $(".frontool").animate({left:'0px'},1);
                    $(this).html("<");
		    $(this).removeClass("close_02").animate({right:'0'},1000);
                }
            });
        }       
	tools();
});

</script>

<script type="text/javascript">
    $("#tid").css('border', '1px solid red');
</script>
<script type="text/javascript">
    function view(sell_return_code) {
        var url = '?app_act=oms/sell_return/after_service_detail&sell_return_code=' + sell_return_code
        openPage(window.btoa(url), url, '售后服务单详情');
    }
    //转单
    function td_tran(index, row) {
        var d = {"api_order_id": row.id, 'app_fmt': 'json'};
        $.post('<?php echo get_app_url('oms/sell_return/td_tran'); ?>', d, function (data) {
            //alert(data);
            //console.log(data);
            //console.log('sss');
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            tableStore.load();
        }, "json");
    }

    function do_set_is_change(_index, row) {
        var refund_id = row.refund_id;
        if (confirm("确认要把交易号" + row.tid + "对应的退单设为已处理吗？")) {
            var url = "?app_act=api/sys/order_refund/set_is_change&app_fmt=json&refund_id=" + refund_id;
            $.post(url, {}, function (json) {
                if (json.status < 0) {
                    BUI.Message.Alert('出错:' + json.message);
                } else {
                    tableStore.load();
                }
            }, 'json');
        }
    }
    $("._sys_batch_task_force_btn").click(function () {
        var task_info = eval('(' + $(this).attr('task_info') + ')');
        var task_name = $(this).text();
        process_batch_task(task_info['act'], task_name, task_info['obj_name'], task_info['ids_params_name'], task_info['submit_all_ids_flag'], undefined, undefined, undefined, 'api_refund');
    });
</script>
<?php include_once (get_tpl_path('process_batch_task'));