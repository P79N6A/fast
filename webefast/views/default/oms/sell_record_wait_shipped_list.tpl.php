<style>
    #pay_time_start{width:100px;}
    #pay_time_end{width:100px;}
    #record_time_start{width:100px;}
    #record_time_end{width:100px;}
    #send_time_start{width: 100px;}
    #send_time_end{width: 100px;}
    #is_notice_time_start{width: 100px;}
    #is_notice_time_end{width: 100px;}
</style>
<?php echo load_js('comm_util.js') ?>
<?php
render_control('PageHead', 'head1', array('title' => '待验货订单列表',
    'links' => array(
       
    ),
    'ref_table' => 'table'
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
$keyword_type['barcode'] = '商品条形码';
$keyword_type['express_no'] = '物流单号';
$is_seller_remark = array();
$is_seller_remark['all'] = '商家留言';
$is_seller_remark[1] = '有商家留言';
$is_seller_remark[0] = '无商家留言';
$is_seller_remark = array_from_dict($is_seller_remark);
$keyword_type = array_from_dict($keyword_type);

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
            'help'=>'支持多订单号查询，用逗号隔开；
以下字段支持模糊查询：买家昵称、手机号码、商品条形码、商品编码、物流单号',
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
            'field' => 'daterange2',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'pay_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'pay_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '通知配货时间',
            'type' => 'group',
            'field' => 'daterange2',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'is_notice_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'is_notice_time_end', 'remark' => ''),
            )
        ),

        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
            'label' => '订单性质',
            'type'  => 'select_multi',
            'id'    => 'sell_record_attr',
            'data'  => load_model('util/FormSelectSourceModel')->sell_record_attr_new(),
        ),
        array(
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'sale_channel_code',
            //'data' => load_model('base/SaleChannelModel')->get_select()
            'data' => load_model('base/SaleChannelModel')->get_my_select()
        ),
        array(
            'label' => '支付方式',
            'type' => 'select_multi',
            'id' => 'pay_type',
            'data' => ds_get_select('pay_type'),
        ),
          array(
            'label' => array('id' => 'is_seller_remark', 'type' => 'select', 'data' => $is_seller_remark),
            'type' => 'input',
            'id' => 'seller_remark',
            'title' => '支持模糊查询'
        ),

//        array(
//            'label' => '国家',
//            'type' => 'select',
//            'id' => 'country',
//            'data' => ds_get_select('country',2)
//        ),
//        array(
//            'label' => '省份',
//            'type' => 'select',
//            'id' => 'province',
//            'data' => array(),
//        ),
//        array(
//            'label' => '城市',
//            'type' => 'select',
//            'id' => 'city',
//            'data' => array(),
//        ),
//        array(
//            'label' => '地区',
//            'type' => 'select',
//            'id' => 'district',
//            'data' => array(),
//        ),
//        array(
//            'label' => '是否签收',
//            'type' => 'select',
//            'id' => 'order_sign_status',
//            'data' => ds_get_select_by_field('boolstatus',2),
//        ),


    )
));
?>
<ul class="toolbar frontool" id="tool">
    <li class="li_btns"><button class="button button-primary" onclick="opt_print_sellrecord()">批量打印发货单</button></li>
    <li class="li_btns"><button class="button button-primary" onclick="opt_print_express()">批量打印快递单</button></li>
    <div class="front_close">&lt;</div>
</ul>
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

    <?php
    $expressList = oms_opts2_by_tb('base_express', 'express_code', 'express_name', array('status' => 1), 2);
    render_control('DataTable', 'table', array(
        'conf' => array(
            'list' => array(
//                    array (
//                    'type' => 'button',
//                    'show' => 1,
//                    'title' => '操作',
//                    'field' => '_operate',
//                    'width' => '80',
//                    'align' => '',
//                    'buttons' => array (
//                        array('id'=>'logistic_trace', 'title' => '查看物流', 'callback'=>'logistic_trace'),
//                    ),
//                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '订单号',
                    'field' => 'record_code',
                    'width' => '150',
                    'align' => '',
                    'format_js' => array(
                        'type' => 'html',
//                    'value' => '<a href="' . get_app_url('oms/sell_record/view') . '&sell_record_code={sell_record_code}">{sell_record_code}</a>',
                        'value' => '<a href="javascript:view(\\\'{sell_record_code}\\\')">{sell_record_code}</a>',
                    )
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '交易号',
                    'field' => 'deal_code_list',
                    'width' => '150',
                    'align' => ''
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
                    'title' => '店铺名称',
                    'field' => 'shop_name',
                    'width' => '100',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '仓库',
                    'field' => 'store_name',
                    'width' => '100',
                    'align' => ''
                ),
//                array(
//                    'type' => 'text',
//                    'show' => 1,
//                    'title' => '发货时间',
//                    'field' => 'delivery_time',
//                    'width' => '120',
//                    'align' => ''
//                ),




                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '配送方式',
                    'field' => 'express_code',
                    'format_js'=> array('type'=>'map', 'value'=>$expressList),
                    'width' => '80',
                    'align' => '',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '物流单号',
                    'field' => 'express_no',
                    'width' => '120',
                    'align' => '',
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
                    'title' => '手机（电话）',
                    'field' => 'receiver_mobile',
                    'width' => '120',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '收货地址',
                    'field' => 'receiver_address',
                    'width' => '200',
                    'align' => ''
                ),
//                array(
//                    'type' => 'text',
//                    'show' => 1,
//                    'title' => '快递交接',
//                    'field' => '',
//                    'width' => '100',
//                    'align' => '',
//                ),
//                array(
//                    'type' => 'text',
//                    'show' => 1,
//                    'title' => '网单回写',
//                    'field' => 'is_back_html',
//                    'width' => '70',
//                    'align' => '',
//                ),
//                 array(
//                     'type' => 'text',
//                     'show' => 1,
//                     'title' => '是否签收',
//                     'field' => 'order_sign_status',
//                     'width' => '70',
//                     'align' => '',
//                     'format_js' => array(
//                     'type' => 'map',
//                     'value' => array(
//                        '0' => '否',
//                        '1' => '是',
//                     ),
//                ),
//                 ),
//                 array(
//                     'type' => 'text',
//                     'show' => 1,
//                     'title' => '签收时间',
//                     'field' => 'sign_time',
//                     'width' => '150',
//                     'align' => ''
//                 ),
//                array(
//                    'type' => 'text',
//                    'show' => 1,
//                    'title' => '货到付款',
//                    'field' => 'pay_type_html',
//                    'width' => '70',
//                    'align' => '',
//                    //'format_js' => array('type'=>'map_checked')
//                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '应收款',
                    'field' => 'payable_money',
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
                    'title' => '通知配货时间',
                    'field' => 'is_notice_time',
                    'width' => '150',
                    'align' => ''
                ),
                  array(
                'type' => 'text',
                'show' => 1,
                'title' => '商家留言',
                'field' => 'seller_remark',
                'width' => '150',
                'align' => '',
                'sortable' => true
            ),

            )
        ),
        'dataset' => 'oms/SellRecordModel::get_by_eait_shipped_page',
        'queryBy' => 'searchForm',
        'idField' => 'sell_record_id',//send_time_end
        'export'=> array('id'=>'exprot_list','conf'=>'sell_record_wait_shipped_list','name'=>'待验货订单','export_type'=>'file'),
        'customFieldTable'=>'oms/sell_record_wait_shipped_list',
        'CheckSelection' => true,
        'params' => array(
            'filter' => array('shipping_status'=>'3','order_status'=>'1'),
        ),
        'CellEditing' => true,
        'init' => 'nodata'
    ));
    ?>
</div>
<script>
  var deliver_template_print = "<?php echo $response['print_delivery_record_template'];?>";
  var new_clodop_print = "<?php echo $response['new_clodop_print'];?>";
    function view(sell_record_code) {
	    var url = '?app_act=oms/sell_record/view&sell_record_code=' +sell_record_code
	    openPage(window.btoa(url),url,'待发货订单详情');
    }
    
    //读取已选中项
    function get_checked(isConfirm, obj, func){
        var ids = []
        var selecteds = tableGrid.getSelection();
        for(var i in selecteds){
            ids.push(selecteds[i].sell_record_id)
        }

        if(ids.length == 0){
            BUI.Message.Alert("请选择订单", 'error');
            return
        }

        if(isConfirm) {
            BUI.Message.Show({
                title : '自定义提示框',
                msg : '是否执行订单'+obj.text()+'?',
                icon : 'question',
                buttons : [
                    {
                        text:'是',
                        elCls : 'button button-primary',
                        handler : function(){
                            func.apply(null, [ids])
                        }
                    },
                    {
                        text:'否',
                        elCls : 'button',
                        handler : function(){
                            this.close();
                        }
                    }
                ]
            });
        } else {
            func.apply(null, [ids])
        }
    }
    
    //打印快递单
    function opt_print_express(){
        get_checked(false, $(this), function(ids){
            //TODO:打印
            var params = {record_ids: ids}
            $.post("?app_act=oms/sell_record/get_deliver_record_ids",params, function(data) {
                if(data.status == 1){
                    print_express(data.data.toString())
                }else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json");
        })
    }
   
   function print_express(deliver_record_ids){
        var param = '';
        var check_url = "?app_act=oms/deliver_record/check_is_print_express&app_fmt=json";
        $.post(check_url, {deliver_record_ids: deliver_record_ids}, function(ret){
            if(ret.status == -2){
                BUI.Message.Alert('单据异常，可打印单据为0', function () {
                    tableStore.load();
                },'error');
            } else if (ret.status == -1){
                BUI.Message.Confirm('存在重复打印快递单，订单号为：' + ret.data.print_data + "，是否继续打印？", function(){
                    param += "&deliver_record_ids=" + ret.data.deliver_record_ids;
                    check_action_print_express(param, ret.data.deliver_record_ids);
                },'question');
            }else{
                param += "&deliver_record_ids=" + ret.data.deliver_record_ids;
                check_action_print_express(param, ret.data.deliver_record_ids);
            }
        },'json');
    }
    
    function check_action_print_express(param, deliver_record_ids){
        var check_url = "?app_act=oms/deliver_record/check_express_type";
        $.post(check_url, {record_ids:deliver_record_ids, id_type: 0}, function(ret){
            var result = JSON.parse(ret);
            action_print_express(param, result.data, deliver_record_ids);
        })
    }
   
   var p_time = 0;
    function action_print_express(param, print_type, deliver_record_ids){
        if(print_type == 'cloud'){
            param = param + '&print_type=cainiao_print';
        }
        var id = "print_express" + p_time;
        if(new_clodop_print == 1 && print_type != 'cloud' && print_type != 'oldcloud'){
            new ESUI.PopWindow("?app_act=oms/deliver_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&record_ids=" + deliver_record_ids + "&is_print_express=1" + "&frame_id=" + id, {
                title: "快递单打印",
                width: 500,
                height: 220,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show()
        } else {
            var url = "?app_act=oms/deliver_record/print_express&iframe_id=" + id;
            if(deliver_record_ids != ""){
                url += "&deliver_record_ids=" + deliver_record_ids;
            }
            var iframe = $('<iframe id="'+id+' width="0" height="0"></iframe>').appendTo('body');
            iframe.attr('src',url);
        }
         p_time++;
    }
     
      //打印发货单
    function opt_print_sellrecord(){
        get_checked(false, $(this), function(ids){
            var params = {record_ids: ids};
            $.post("?app_act=oms/sell_record/get_deliver_record_ids",params, function(data) {
                if(data.status == 1){
                    if(new_clodop_print == 1){
                        new ESUI.PopWindow("?app_act=oms/deliver_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&print_templates_code=deliver_record&record_ids="+data.data.toString(), {
                            title: "发货单打印",
                            width: 500,
                            height: 220,
                            onBeforeClosed: function () {
                            },
                            onClosed: function () {
                            }
                        }).show()
                    } else {
                        if(deliver_template_print == 1){
                            var u = '?app_act=tprint/tprint/do_print&print_templates_code=deliver_record&record_ids='+data.data.toString();
//                            $("#print_iframe").attr('src',u);
                            var id = 'print_iframe';
                            var iframe = $('<iframe id="'+id+' width="0" height="0"></iframe>').appendTo('body');
                            iframe.attr('src',u);
                        } else {
                            var u = '?app_act=sys/flash_print/do_print'
                                u += '&template_id=5&model=oms/DeliverRecordModel&typ=default&record_ids='+data.data.toString();
                            var window_is_block = window.open(u);
                            if (null == window_is_block) {
                                alert("您的浏览器阻止了打印发货单的新窗口,请在浏览器的阻止提示处选择允许弹出新窗口");
                            }
                        }
                    } 
                }else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json");
        })
    }
    

</script>
<!--<iframe src="" id="print_iframe" style="width:0px;height:0px;" ></iframe>-->

