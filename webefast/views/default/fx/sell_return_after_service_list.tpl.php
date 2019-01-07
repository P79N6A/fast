<style type="text/css">
   .frontool .li_btns {float: left;margin: 4px 3px;}
   #create_time_start, #create_time_end{width: 100px;}
   .like_link{
        text-decoration:underline;
        color:#428bca; 
        cursor:pointer;
    }
</style>

<?php
render_control('PageHead', 'head1', array('title' => '分销退单列表',
    'links' => array(
    //array('url'=>'oms/sell_record/add', 'title'=>'新增订单', 'is_pop'=>false, 'pop_size'=>'500,400'),
    ),
    'ref_table' => 'table'
));
?>

<?php
$keyword_type = array();
$keyword_type['deal_code'] = '交易号';
$keyword_type['sell_return_code'] = '退单号';
$keyword_type['return_name'] = '退货人';
$keyword_type['return_mobile'] = '退货人手机';
$keyword_type['buyer_name'] = '买家昵称';
$keyword_type['sell_return_package_code'] = '退货包裹单号';
$keyword_type['return_express_no'] = '退货运单号';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['sell_record_code'] = '原单号';
$keyword_type['confirm_person'] = '确认人';
if ($response['unique_status'] == 1){
	$keyword_type['unique_code'] = '唯一码';
}
$keyword_type = array_from_dict($keyword_type);

$is_remark = array();
$is_remark['is_return_buyer_memo'] = '买家退单说明';
$is_remark['is_return_remark'] = '卖家退单备注';
$is_remark = array_from_dict($is_remark);

$remark = array();
$remark['return_buyer_memo'] = '买家退单说明';
$remark['return_remark'] = '卖家退单备注';
$remark = array_from_dict($remark);

render_control('SearchForm', 'searchForm', array(
    'buttons' =>array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type'=>'submit'
        ),
        array(
            'label' => '导出',
            'id' => 'exprot_list',
        ),
    ) ,
    'show_row'=>3,
    'fields' => array(
        array(
            'label' => array('id'=>'keyword_type','type'=>'select','data'=>$keyword_type),
            'type' => 'input',
            'title'=>'',
            'data'=>$keyword_type,
            'id' => 'keyword',
        ),

        array(
            'label' => '退单类型',
            'type' => 'select_multi',
            'id' => 'return_type',
            'data' => ds_get_select_by_field('return_order_type',0),
        ),
        array(
            'label' => '退货原因',
            'type' => 'select_multi',
            'id' => 'return_reason_code',
            'data' => ds_get_select('return_reason'),
        ),
        array(
            'label' => '退货仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
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
            'id' => 'source',
            //'data' => load_model('base/SaleChannelModel')->get_select()
            'data' => load_model('base/SaleChannelModel')->get_my_select()
        ),
        array(
            'label' => '是否换货',
            'type' => 'select',
            'id' => 'is_change',
            'data' => ds_get_select_by_field('boolstatus'),
        ),
       array(
            'label' => array('id'=>'is_remark','type'=>'select','data'=>$is_remark),
            'type' => 'select',
            'title'=>'',
           'id' => 'is_remark_value',
           'data' => array(
                array('all','请选择'),array('0', '无'), array('1', '有')
            )
        ),

        array(
            'label' => array('id'=>'remark','type'=>'select','data'=>$remark),
            'type' => 'input',
            'title'=>'',
            'id' => 'remark_value',
        ),
        array(
            'label' => '退款方式',
            'type' => 'select_multi',
            'id' => 'return_pay_code',
            'data' => ds_get_select('refund_type'),
        ),
        array(
            'label' => '原单配送方式',
            'type' => 'select_multi',
            'id' => 'express_code',
            'data' => ds_get_select('express'),
        ),
        array(
            'label' => '原单物流单号',
            'type' => 'input',
            'id' => 'express_no',
            'title'=>'支持模糊查询'
        ),
        array(
            'label' => '退货确认客服',
            'type' => 'input',
            'id' => 'service_code'
        ),

        array(
            'label' => '退货物流单号',
            'type' => 'input',
            'id' => 'return_express_no',
        ),
        array(
            'label' => '退单创建时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'create_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'create_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '确认时间',
            'type' => 'group',
            'field' => 'daterange2',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'confirm_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'confirm_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '确认收货时间',
            'type' => 'group',
            'field' => 'daterange3',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'store_in_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'store_in_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '确认退款时间',
            'type' => 'group',
            'field' => 'daterange4',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'sure_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'sure_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '原单下单时间',
            'type' => 'group',
            'field' => 'daterange5',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'order_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'order_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '原单支付时间',
            'type' => 'group',
            'field' => 'daterange6',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'pay_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'pay_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '业务日期',
            'type' => 'group',
            'field' => 'daterange7',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'record_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'record_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '退单标签',
            'type' => 'select_multi',
            'id' => 'return_label_name',
            'data' =>load_model('base/ReturnLabelModel')->get_select(),
        ),
        array(
            'label' => '差异收货',
            'type' => 'select',
            'id' => 'is_differ',
           'data'=> ds_get_select_by_field('is_rush'),
        ),
    )
));
?>
<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '全部', 'active' => true, 'id' => 'tabs_all'),// 默认选中active=true的页签
	array('title' => '待确认', 'active' => false, 'id' => 'tabs_confirm'),
        array('title' => '待收货', 'active' => false, 'id' => 'tabs_receive_goods'),
        array('title' => '待退款', 'active' => false, 'id' => 'tabs_return_money'),
        array('title' => '待完成', 'active' => false, 'id' => 'tabs_wait_finish'),
        array('title' => '已完成', 'active' => false, 'id' => 'tabs_finish'),
        array('title' => '已作废', 'active' => false, 'id' => 'tabs_void'),
    ),
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<!--<div id="TabPage1Contents">
<div>
<ul class="clearfix frontool" id="ToolBar1">
    <li class="li_btns"><button class="button button-primary" id="btn_opt_label">批量打标</button></li>
    <li class="li_btns"><button class="button button-primary" id="btn_opt_print_return">批量打印退单</button></li>
    <div class="front_close">&lt;</div>
</ul>
</div>
<div>
<ul class="clearfix frontool" id="ToolBar2">

    <li class="li_btns"><button class="button button-primary" id="btn_opt_confirm" <?php if (!load_model('sys/PrivilegeModel')->check_priv('oms/return_opt/opt_confirm')) { ?> style="display:none;" <?php } ?> >批量确认</button></li>
    <li class="li_btns"><button class="button button-primary" id="btn_opt_edit_store_code">批量修改退货仓库</button></li>
    <li class="li_btns"><button class="button button-primary" id="btn_opt_cancel">批量作废</button></li>
    <li class="li_btns"><button class="button button-primary" id="btn_opt_print_return">批量打印退单</button></li>
    <li class="li_btns"><button class="button button-primary" id="btn_opt_return_money">批量转退款单</button></li>
    <?php // if($response['fast_return']==1) {?>
    <li class="li_btns"><button class="button button-primary" id="btn_opt_confirm_return_shipping">批量快速入库</button></li>
    <?php // }?>
    <div class="front_close">&lt;</div>
</ul>-->

		<script>
//		    $(function(){
//		        var default_opts = ['opt_confirm','opt_cancel','opt_return_money'];
//		                for(var i in default_opts){
//			    var f = default_opts[i];
//			    btn_init_opt("ToolBar2",f);
//			}
//		                var custom_opts = $.parseJSON('[{"id":"opt_confirm","custom":"btn_opt_confirm"},{"id":"opt_cancel","custom":"btn_opt_cancel"},{"id":"opt_return_money","custom":"btn_opt_return_money"}]');
//		        for(var j in custom_opts){
//		            var g = custom_opts[j];
//		            $("#ToolBar2 .btn_"+g['id']).click(eval(g['custom']));
//		        }
//		    });
		</script>
<!--</div>
<div>
<ul class="clearfix frontool" id="ToolBar3">
    <li class="li_btns"><button class="button button-primary" id="btn_opt_return_shipping">批量收货</button></li>
    <li class="li_btns"><button class="button button-primary" id="btn_opt_unconfirm">批量取消确认</button></li>
    <li class="li_btns"><button class="button button-primary" id="btn_opt_print_return">批量打印退单</button></li>
    <div class="front_close">&lt;</div>
</ul>-->
    <script>
//        $(function(){
//            var default_opts = ['opt_unconfirm','opt_return_shipping'];
//                    for(var i in default_opts){
//                var f = default_opts[i];
//                btn_init_opt("ToolBar3",f);
//            }
//            var custom_opts = $.parseJSON('[{"id":"opt_unconfirm","custom":"btn_opt_unconfirm"}]','[{"id":"opt_return_shipping","custom":"btn_opt_return_shipping"}]');
//            for(var j in custom_opts){
//                var g = custom_opts[j];
//                $("#ToolBar3 .btn_"+g['id']).click(eval(g['custom']));
//            }
//        });
    </script>
<!--</div>-->

<!--<div>
<ul class="clearfix frontool" id="ToolBar4">
    <li class="li_btns"><button class="button button-primary" id="btn_opt_finance_confirm">批量退款</button></li>
    <li class="li_btns"><button class="button button-primary" id="btn_opt_print_return">批量打印退单</button></li>
    <div class="front_close">&lt;</div>
</ul>-->
    <script>
//        $(function(){
//            var default_opts = ['opt_finance_confirm'];
//            for(var i in default_opts){
//                var f = default_opts[i];
//                btn_init_opt("ToolBar4",f);
//            }
//            var custom_opts = $.parseJSON('[{"id":"opt_finance_confirm","custom":"btn_opt_finance_confirm"}]');
//            for(var j in custom_opts){
//                var g = custom_opts[j];
//                $("#ToolBar4 .btn_"+g['id']).click(eval(g['custom']));
//            }
//        });
    </script>
<!--</div>-->
<!--<div>
<ul class="clearfix frontool" id="ToolBar5">
    <li class="li_btns"><button class="button button-primary" id="btn_opt_print_return">批量打印退单</button></li>
    <li class="li_btns"><button class="button button-primary" id="btn_opt_finish">批量完成</button></li>
    <div class="front_close">&lt;</div>
</ul>-->
   <script>
//        $(function(){
//            var default_opts = ['opt_finish'];
//                    for(var i in default_opts){
//                var f = default_opts[i];
//                btn_init_opt("ToolBar5",f);
//            }
//            var custom_opts = $.parseJSON('[{"id":"opt_finish","custom":"btn_opt_finish"}]');
//            for(var j in custom_opts){
//                var g = custom_opts[j];
//                $("#ToolBar5 .btn_"+g['id']).click(eval(g['custom']));
//            }
//        });
    </script>
<!--</div>-->
<!--<div>
<ul class="clearfix frontool" id="ToolBar6">
    <li class="li_btns"><button class="button button-primary" id="btn_opt_print_return">批量打印退单</button></li>
    <div class="front_close">&lt;</div>
</ul>
</div>-->
<?php
function format_select_by_field($select){
    $new = array();
    foreach($select as $data){
        $new[$data[0]] = $data[1];
    }
    return $new;
}
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退单号',
                'field' => 'sell_return_code',
                'width' => '120',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({sell_return_code})" title="{return_buyer_memo}">{sell_return_code}</a>',
                )
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退单类型',
                'field' => 'return_type',
                'width' => '80',
                'align' => '',
                'format_js'=>array(
                    'type' => 'map',
                    'value' => format_select_by_field(ds_get_select_by_field("return_order_type"))
                )
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退单状态',
                'field' => 'return_order_status_txt',
                'width' => '100',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '{return_order_status_txt}',
                )
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退货人',
                'field' => 'return_name',
                'width' => '80',
                'align' => '',
                'format_js' => array(
                    'type' => 'function',
                    'value' => 'check_return_name',
		),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分销商',
                'field' => 'fenxiao_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分销结算运费',
                'field' => 'fx_express_money',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退货人手机',
                'field' => 'return_mobile',
                'width' => '100',
                'align' => '',
                'format_js' => array(
                    'type' => 'function',
                    'value' => 'check_tel',
		),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退单创建时间',
                'field' => 'create_time',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '原单号',
                'field' => 'sell_record_code',
                'width' => '100',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view_new(\\\'{sell_record_code}\\\') "title={}">{sell_record_code}</a>',
                )
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'deal_code',
                'width' => '140',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '原单配送方式',
                'field' => 'express_name',
                'width' => '90',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家昵称',
                'field' => 'buyer_name',
                'width' => '100',
                'align' => '',
                'format_js' => array(
                    'type' => 'function',
                    'value' => 'set_wangwang_html')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '确认人',
                'field' => 'confirm_person',
                'width' => '80',
                'align' => ''
            ),

//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '退单原因',
//                'field' => 'return_reason_code',
//                'width' => '100',
//                'align' => ''
//            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '退货仓库',
//                'field' => 'store_code',
//                'width' => '100',
//                'align' => ''
//            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '信息审核时间',
//                'field' => 'check_time',
//                'width' => '100',
//                'align' => ''
//            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '验收入库时间',
//                'field' => 'receive_time',
//                'width' => '100',
//                'align' => ''
//            ),
            array(
               'type' => 'text',
               'show' => 1,
               'title' => '同意退款时间',
                'field' => 'agreed_refund_time',
                'width' => '100',
                'align' => ''
            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '订单下单时间',
//                'field' => '',
//                'width' => '100',
//                'align' => ''
//            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '订单付款时间',
//                'field' => '',
//                'width' => '100',
//                'align' => ''
//            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '入库状态',
//                'field' => 'return_order_status',
//                'width' => '100',
//                'align' => ''
//            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '财务退款状态',
//                'field' => 'finance_reject_reason',
//                'width' => '100',
//                'align' => ''
//            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '退货客服',
//                'field' => 'service_code',
//                'width' => '100',
//                'align' => ''
//            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '是否换货',
                'field' => 'change_record_img',
                'width' => '70',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家退单说明',
                'field' => 'return_buyer_memo',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '卖家退单备注',
                'field' => 'return_remark',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退货运单号',
                'field' => 'return_express_no',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收货时间',
                'field' => 'receive_time',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '验收入库人',
                'field' => 'receive_person',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '财务确认退款人',
                'field' => 'agree_refund_person',
                'width' => '100',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'oms/SellReturnModel::get_after_service_list',
    'queryBy' => 'searchForm',
    'idField' => 'sell_return_id',
    'export'=> array('id'=>'exprot_list','conf'=>'after_service_list','name'=>'售后服务单','export_type'=>'file'),
//    'CheckSelection' => true,
    'customFieldTable'=>'sell_return_after_service/table',
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
    'params' => array('filter' => array('list_type' => 'fx_return_list')),
));
?>
<script>
$(function(){
	function tools(){
        $(".frontool").animate({left:'0px'},1000);
        $(".front_close").click(function(){
            if($(this).html()=="&lt;"){
                $(".frontool").animate({left:'-100%'},1000);
                $(this).html(">");
				$(this).addClass("close_02").animate({right:'-10px'},1000);
            }else{
                $(".frontool").animate({left:'0px'},1000);
                $(this).html("<");
				$(this).removeClass("close_02").animate({right:'0'},1000);
            }
        });
    }

	tools();
})
</script>
<input type="hidden" id="tbl_muil_check_ids" value=""/>
<script>
    $(function(){
    	//TAB选项卡
        $("#TabPage1 a").click(function() {
            tableStore.load();
        });
        $("input[name='is_normal']").change(function(){
            tableStore.load();
        });
        tableStore.on('beforeload', function(e) {
            e.params.after_service_list_tab = $("#TabPage1").find(".active").find("a").attr("id");
            tableStore.set("params", e.params);
        });


        var default_opts = ['opt_confirm','opt_label'];
	    for(var i in default_opts){
		    var f = default_opts[i];
		    btn_init_opt("tools",f);
		}
        var custom_opts = $.parseJSON('');
        for(var j in custom_opts){
            var g = custom_opts[j];
            $("#tools .btn_"+g['id']).click(eval(g['custom']));
        }
    });

    //初始化批量操作按钮
    function btn_init_opt(tab_id, id) {
        $("#" + tab_id + " #btn_" + id).click(function() {
            get_checked($(this),id);
        });
    }

    //读取已选中项
    function get_checked(obj,act) {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择退单", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.sell_return_code);
        }
        $("#tbl_muil_check_ids").val(ids.join(','));
        var opt_info = '';
        opt_info = '是否执行退单' + obj.text() + '?';
        if(act == 'opt_return_money') {
            opt_info = '是否执行退单' + obj.text() + '?'+"<br>请确保选中退单需转换成【仅退款】的售后服务单！";
        }
        BUI.Message.Show({
            title: obj.text(),
            msg: opt_info,
            icon: 'question',
            buttons: [
                {
                    text: '是',
                    elCls: 'button button-primary',
                    handler: function() {
	               // show_process_batch_task_plan(obj.text(),'<div id="process_batch_task_tips"><div>处理中，请稍等......');
                        process_batch_task(act);
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
    var success = 0;
    var fail = 0;
    function process_batch_task(act){
	    var ids = $("#tbl_muil_check_ids").val();
	    if (ids == ''){
           //     $("#process_batch_task_tips div").append("<br/><span style='color:red'>批量任务执行完成。</span>");
               if(fail==0){
                      BUI.Message.Alert('操作成功', 'success')
                      success=0;
                      fail=0;
                      tableStore.load()
                      return;
               }else{
                      BUI.Message.Alert('操作成功'+success+'单，失败'+fail+'单！', 'info')
                      success=0;
                      fail=0;
                      tableStore.load()
                      return;
               }
	    }
	    var ids_arr = ids.split(',');
	    var cur_id = ids_arr.pop();
	    $("#tbl_muil_check_ids").val();
	    $("#tbl_muil_check_ids").val(ids_arr.join(','));
            ajax_url = "?app_fmt=json&app_act=oms/sell_return/opt&type="+act+"&sell_return_code="+cur_id;
	    $.get(ajax_url,function(result){
                var result_obj = eval('('+result+')');
                if(result_obj.status==1){
                    success++;
                }else{
                    fail++;
                }
                //$("#process_batch_task_tips div").append("<br/>"+cur_id+' '+result_obj.message);
                process_batch_task(act);
	    });
    }

	function show_process_batch_task_plan(title,content){
		BUI.use('bui/overlay',function(Overlay){
			var dialog = new Overlay.Dialog({
				title:title,
				width:500,
				height:400,
				mask:true,
				buttons:[],
				bodyContent:content
			});
			dialog.show();
		});
	}

    //数据行双击打开新页面显示详情
    function showDetail(index, row) {
        view(row.sell_return_code);
        view_new(row.sell_record_code);
    }

	function view(sell_return_code) {
	    var url = '?app_act=fx/sell_return/after_service_detail&sell_return_code=' +sell_return_code
	    openPage(window.btoa(url),url,'分销退货单详情');
	}
      function view_new(sell_record_code) {
	    var url = '?app_act=fx/sell_record/view&sell_record_code=' +sell_record_code
	    openPage(window.btoa(url),url,'分销订单详情');
	}
    //批量打标
    $("#btn_opt_label").click(function(){
	get_checked_label($(this), function(ids) {
        new ESUI.PopWindow("?app_act=oms/sell_return/label&batch=<?php echo urlencode("批量操作");?>&sell_return_code_list=" + ids.toString(), {
            title: "批量打标签",
            width: 500,
            height:300,
            onBeforeClosed: function() {},
            onClosed: function() {
                //刷新数据
                tableStore.load()
            }
        }).show()
    })
});

$("#btn_opt_print_return").click(function (){
    get_checked_label($(this), function(ids){
            //TODO:打印
        var u = '?app_act=tprint/tprint/do_print&print_templates_code=sell_return&record_ids='+ids;
        $("#print_iframe").attr('src',u);
    });
});

function get_checked_label(obj,func) {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择退单", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.sell_return_code);
        }
        $("#tbl_muil_check_ids").val(ids.join(','));

        BUI.Message.Show({
            title: '自定义提示框',
            msg: '是否执行退单' + obj.text() + '?',
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

    //批量修改退货仓库
    $("#btn_opt_edit_store_code").click(function () {
        get_checked_label($(this), function (ids) {
            new ESUI.PopWindow("?app_act=oms/sell_return/edit_store_code&sell_return_code_list=" + ids.toString(), {
                title: "批量修改退货仓库",
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
    }
    )


    //批量快速入库
    $("#btn_opt_confirm_return_shipping").click(function () {      
    get_checked_return($(this), function(ids){
       //批量快速
            var d = {"sell_return_code_list": ids.toString(), 'app_fmt': 'json'};
            $.post("?app_act=oms/sell_return/opt_confirm_return_shipping", d, function (data) {
                if(data.status != '1'){
                    BUI.Message.Alert(data.message, 'error')
                }else {
                    BUI.Message.Alert(data.message, 'info')
                    tableStore.load()
                }
            }, "json")
    });
    }
    )


    //读取已选中项
    function get_checked_return(obj, func) {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择退单", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.sell_return_code);
        }
        ids.join(',');
        BUI.Message.Show({
            title: '提示框',
            msg: '是否执行退单单' + obj.text() + '?',
            icon: 'question',
            buttons: [
                {
                    text: '是',
                    elCls: 'button button-primary',
                    handler: function () {
                        func.apply(null, [ids]);
                        this.close();
                    }
                },
                {
                    text: '否',
                    elCls: 'button',
                    handler: function () {
                        this.close();
                    }
                }
            ]
        });
    }
    function set_wangwang_html(value, row, index){
        if(row.sale_channel_code == 'taobao') {
            return template('<span class="like_link" onclick =\"show_info(this,\''+row.sell_return_code+'\',\'buyer_name\');\">{buyer_name}</span>', row);
        } else {
           return template('<span>{buyer_name}</span>', row);
       }
    }

    function launch_ww(record_code){
        var url = "?app_act=oms/sell_record/link_wangwang&type=1&record_code="+record_code;
        window.open(url);
    }
    //退货人解密
    function check_return_name(value, row, index){
        if(value.indexOf('***')>-1){
                return set_show_text(row,value,'return_name');
        }else{
            return value;
        }
    }
    //退货人手机
    function check_tel(value, row, index){
        if(value.indexOf('***')>-1){
                return set_show_text(row,value,'return_mobile');
        }else{
            return value;
        }
    }
    function set_show_text(row,value,type){
        return '<span class="like_link" onclick =\"show_info(this,\''+row.sell_return_code+'\',\''+type+'\');\">'+value+'</span>';
    }
    
           //解密
    function show_info(obj,sell_return_code,key){
        var url = "?app_act=oms/sell_return/get_record_key_data&app_fmt=json";
         $.post(url,{'sell_return_code':sell_return_code,key:key},function(ret){
             if(ret[key]==null){
                  BUI.Message.Tip('解密出现异常！', 'error');
                 return ;
             }
             $(obj).html(ret[key]);
             $(obj).attr('onclick','');
             $(obj).removeClass('like_link');
        },'json');
    }
</script>

<iframe src="" id="print_iframe" style="width:0px;height:0px;" ></iframe>