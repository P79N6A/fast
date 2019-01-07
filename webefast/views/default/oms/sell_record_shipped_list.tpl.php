<style>
    #send_time_start,#send_time_end,
    #is_notice_time_start,#is_notice_time_end,
    #embrace_time_start,#embrace_time_end,
    #sign_time_start,#sign_time_end,
    #record_time_start,#record_time_end,
    #pay_time_start,#pay_time_end
    {width: 100px;}
    #exprot_list {width:120px;}
</style>
<?php echo load_js('comm_util.js') ?>
<?php $service_is_print_warranty = load_model('common/ServiceModel')->check_is_auth_by_value('print_warranty');//质保书打印列增值服务?>
<?php
render_control('PageHead', 'head1', array('title' => '已发货订单列表',
    'links' => array(
        //array('url'=>'oms/sell_record/rewrite', 'title'=>'一键网单回写', 'is_pop'=>false, 'pop_size'=>'500,400'),
        array('url'=>'oms/sell_record/import', 'title'=>'导入变更快递', 'is_pop'=>true, 'pop_size'=>'600,250'),
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
$keyword_type['delivery_person'] = '验货员';

$keyword_type = array_from_dict($keyword_type);

$send_time_start = date("Y-m").'-01 00:00:00';
$buttons = array(
    array(
        'label' => '查询',
        'id' => 'btn-search',
        'type'=>'submit'
    ),
) ;
if (load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/export_shipped_list')) {
    $buttons[] =  array(
        'label' => '导出明细',
        'id' => 'exprot_list',
    );
}
render_control('SearchForm', 'searchForm', array(
    'buttons' =>$buttons,
    'show_row'=>3,
    'fields' => array(
        array(
            'label' => array('id'=>'keyword_type','type'=>'select','data'=>$keyword_type),
            'type' => 'input',
            'title'=>'',
            'data'=>$keyword_type,
            'id' => 'keyword',
            'help'=>'以下字段支持模糊查询：买家昵称、手机号码、商品条形码、商品编码、物流单号',
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
            'label' => '发货时间',
            'type' => 'group',
            'field' => 'daterange3',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'send_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'send_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '揽件时间',
            'type' => 'group',
            'field' => 'embrace_time',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'embrace_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'embrace_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '签收时间',
            'type' => 'group',
            'field' => 'sign_time',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'sign_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'sign_time_end', 'remark' => ''),
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
            'label' => '是否揽件',
            'type' => 'select',
            'id' => 'embrace_status',
            'data' => ds_get_select_by_field('boolstatus',2),
        ),
        array(
            'label' => '是否快递交接',
            'type' => 'select',
            'id' => 'receive_status',
            'data' => ds_get_select_by_field('receive_status',2),
        ),
        array(
            'label' => '是否签收',
            'type' => 'select',
            'id' => 'order_sign_status',
            'data' => ds_get_select_by_field('boolstatus',2),
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
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'sale_channel_code',
            //'data' => load_model('base/SaleChannelModel')->get_select()
            'data' => load_model('base/SaleChannelModel')->get_my_select(),
        ),
        array(
            'label' => '支付方式',
            'type' => 'select_multi',
            'id' => 'pay_code',
            'data' => ds_get_select('pay_type'),
        ),

        array(
            'label' => '国家',
            'type' => 'select',
            'id' => 'country',
            'data' => ds_get_select('country',2)
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


    )
));
?>
<ul class="toolbar frontool" id="tool">
    <!--<li><input type="checkbox">全选</li>
    <li><button class="button button-primary">批量回写</button></li>
    <li><button class="button button-primary">批量强制回写</button></li>
    <li><button class="button button-primary">批量回写本地</button></li-->
    <li class="li_btns"><button class="button button-primary" onclick="opt_print_sellrecord()">批量打印发货单</button></li>
    <li class="li_btns"><button class="button button-primary" onclick="opt_print_express()">批量打印快递单</button></li>
    <?php if($service_is_print_warranty == true){ //开启增值服务
        if (load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/opt_print_warranty')){ ?> 
            <li class="li_btns"><button class="button button-primary" onclick="opt_print_warranty()">批量打印质保书</button></li>
     <?php }?>       
   <?php  } ?>
    
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
    $option_conf_list = array(
                    array (
                    'type' => 'button',
                    'show' => 1,
                    'title' => '操作',
                    'field' => '_operate',
                    'width' => '120',
                    'align' => '',
                    'buttons' => array (
                        array('id'=>'logistic_trace', 'title' => '查看物流', 'callback'=>'logistic_trace'),
                        array('id'=>'opt_replenish', 'title' => '补单', 'callback'=>'opt_replenish',
                            'priv' => 'oms/order_opt/opt_replenish'
                        ),
//                        array('id'=>'print_zbs', 'title' => '打印质保书', 'callback'=>'do_print',
//                               'priv' => 'oms/sell_record/do_print'
//                            ),

                    ),
                ),
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
                    'field' => 'sale_channel_code',
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
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '发货时间',
                    'field' => 'delivery_time',
                    'width' => '120',
                    'align' => ''
                ),

                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '数量',
                    'field' => 'goods_num',
                    'width' => '80',
                    'align' => ''
                ),


                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '配送方式',
                    'field' => 'express_code',
                    'format_js'=> array('type'=>'map', 'value'=>$expressList),
                    'width' => '80',
                    'align' => '',
                    'editor'=>(1==$response['edit_express_status'] or 1==$response['edit_express_status_new'])?"{xtype : 'select', items: ".json_encode($expressList)."}":""
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '物流单号',
                    'field' => 'express_no',
                    'width' => '120',
                    'align' => '',
                    'editor' =>(1==$response['edit_express_status'] or 1==$response['edit_express_status_new'])?"{xtype : 'text'}":"",
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
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '网单回写',
                    'field' => 'is_back_html',
                    'width' => '70',
                    'align' => '',
                ),
                 array(
                     'type' => 'text',
                     'show' => 1,
                     'title' => '是否签收',
                     'field' => 'order_sign_status',
                     'width' => '70',
                     'align' => '',
                     'format_js' => array(
                     'type' => 'map',
                     'value' => array(
                        '0' => '否',
                        '1' => '是',
                     ),
                ),
                 ),
                 array(
                     'type' => 'text',
                     'show' => 1,
                     'title' => '签收时间',
                     'field' => 'sign_time',
                     'width' => '150',
                     'align' => ''
                 ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '货到付款',
                    'field' => 'pay_type_html',
                    'width' => '70',
                    'align' => '',
                    //'format_js' => array('type'=>'map_checked')
                ),
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
                    'show' => 0,
                    'title' => '通知配货时间',
                    'field' => 'is_notice_time',
                    'width' => '150',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 0,
                    'title' => '揽件时间',
                    'field' => 'embrace_time',
                    'width' => '150',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 0,
                    'title' => '订单理论重量(Kg)',
                    'field' => 'goods_weigh',
                    'width' => '120',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 0,
                    'title' => '称重重量(Kg)',
                    'field' => 'real_weigh',
                    'width' => '100',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 0,
                    'title' => '称重运费',
                    'field' => 'weigh_express_money',
                    'width' => '80',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 0,
                    'title' => '是否快递交接',
                    'field' => 'receive_status',
                    'width' => '90',
                    'align' => ''
                )
            );
    if($service_is_print_warranty == true){
        $option_conf_list[0] = array (
                    'type' => 'button',
                    'show' => 1,
                    'title' => '操作',
                    'field' => '_operate',
                    'width' => '120',
                    'align' => '',
                    'buttons' => array (
                        array('id'=>'logistic_trace', 'title' => '查看物流', 'callback'=>'logistic_trace'),
                        array('id'=>'opt_replenish', 'title' => '补单', 'callback'=>'opt_replenish',
                            'priv' => 'oms/order_opt/opt_replenish'
                        ),
                        array('id'=>'print_zbs', 'title' => '打印质保书', 'callback'=>'do_print',
                               'priv' => 'oms/sell_record/do_print'
                            ),

                    ),
                );
        $option_conf_list[] =  array(
                'type' => 'text',
                'show' => 0,
                'title' => '打印',
                'field' => 'is_print_warranty',
                'width' => '120',
                'align' => '',
                'format_js' => array('type' => 'function','value'=>'get_is_print')
            );
    }

    render_control('DataTable', 'table', array(
        'conf' => array(
            'list' => $option_conf_list,
        ),
        'dataset' => 'oms/SellRecordModel::get_list_by_page',
        'queryBy' => 'searchForm',
        'idField' => 'sell_record_id',//send_time_end
        'export'=> array('id'=>'exprot_list','conf'=>'send_record_list','name'=>'已发货订单','export_type'=>'file'),
        'customFieldTable'=>'oms/sell_record_shipped_list',
        'CheckSelection' => true,
        'params' => array(
            'filter' => array('shipping_status'=>'4'),
        ),
        'CellEditing' => true,
        'ColumnResize' => true,
        'init' => 'nodata'
    ));
    ?>
</div>
<script>
    $("#send_time_start").val("<?php echo $send_time_start?>");
    var kdniao_enable = <?php echo $response['kdniao_enable'];?>;
    var url = '<?php echo get_app_url('base/store/get_area');?>';
    var deliver_template_print = "<?php echo $response['print_delivery_record_template'];?>";
    var new_clodop_print = "<?php echo $response['new_clodop_print'];?>";
    $(document).ready(function() {
        $("#sell_record_code").css("border","red 1px solid");
        $("#deal_code_list").css("border","red 1px solid");
        $('#country').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,0,url);
        });
        $('#province').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,1,url);
        });
        $('#city').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,2,url);
        });
        $('#district').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,3,url);
        });
//        $('#country').val('1');
        $('#country').change();
        //tableStore.load();

        tableCellEditing.on('accept', function(record) {
            var params = {
                "sell_record_code": record.record.sell_record_code,
                "express_code": record.record.express_code,
                "express_no": record.record.express_no.trim(),
                "is_force": 1
            }
            var str = params.express_no;
            if (str != '') {
                var reg = new RegExp(/^[0-9A-Za-z]+$/);
                if (!reg.test(str)) {
                    BUI.Message.Alert("快递单号必须为数字或者字母", 'error');
                    return false;
                }
            }
            $.post("?app_act=oms/deliver_record/edit_express", params, function(data) {
                if (data.status < 0) {
                    BUI.Message.Alert(data.message, 'error');
                    tableStore.load();
                } else if(data.status == 1){
                    BUI.Message.Tip(data.message, 'success');
                    tableStore.load();
                }
            }, "json")
        });
    })
    function show_detail(url,_this) {
        var param1 = $(_this).attr('param1');
        url += "&app_tpl=oms/sell_record_question_detail&app_page=NULL";
        if($("#tr"+param1).length == 0){
            $.get(url,function(ret){
                $(_this).find("span").attr('class','bar-btn-close');
                $(_this).parents("tr").after(ret);
            });
        }else{
            $(_this).find("span").attr('class','bar-btn-add');
            $("#tr"+param1).remove();
        }
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
                title : '批量操作',
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
    
        //批量打印质保书
    function opt_print_warranty(){
        get_checked(false, $(this), function(ids){
            //TODO:打印
            var params = {record_ids: ids}
            $.post("?app_act=oms/sell_record/get_deliver_record_ids",params, function(data) {
                if(data.status == 1){
                    print_warranty(data.data.toString())
                }else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json");
        })
    }
//批量打印质保书
     function print_warranty(deliver_record_ids){
         var params = {record_ids: deliver_record_ids};
         $.post("?app_act=oms/sell_record/getRecord",params,function(data){
               var u = '?app_act=sys/flash_print/do_print'
                u += '&template_id=211&model=oms/DeliverRecordWtyModel&typ=default&record_ids='+data.data.toString();
                window.open(u);
         }, "json")

     }
      function  do_print(index,row) {
            var params = {sell_record: row.sell_record_code};
                    //检验发货单是否存在
              $.post("?app_act=oms/sell_record/check_deliver",params,function(data){
                  if(data.status == 1){
                    var u = '?app_act=sys/flash_print/do_print'
                        u += '&template_id=211&model=oms/DeliverRecordWtyModel&typ=default&record_ids='+row.sell_record_code;
                        window.open(u);
                    }else {
                        BUI.Message.Alert(data.message, 'error');
                    }
                }, "json");

        }
        
    function opt_replenish(index,row){
        var u = '?app_act=oms/sell_record/opt_replenish'
        BUI.Message.Confirm('确认要给'+row.sell_record_code+'订单补单吗?',function(){
            $.post(u,{sell_record_code:row.sell_record_code},function(data){
                if(data.status == 1){
                    BUI.Message.Alert('补单成功','success')
                }else{
                    BUI.Message.Alert(data.message,'error')
                }
            },'json')
        })
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
                    }else{
                        if(deliver_template_print == 1){
                            //新发货单模板
                            var u = '?app_act=tprint/tprint/do_print&print_templates_code=deliver_record&record_ids='+data.data.toString();
//                            $("#print_iframe").attr('src',u);
                            var id = 'print_iframe';
                            var iframe = $('<iframe id="'+id+' width="0" height="0"></iframe>').appendTo('body');
                            iframe.attr('src',u);
                        } else { 
                            //旧发货单模板
                            var u = '?app_act=sys/flash_print/do_print'
                                u += '&template_id=5&model=oms/DeliverRecordModel&typ=default&record_ids='+data.data.toString()
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

    function view(sell_record_code) {
	    var url = '?app_act=oms/sell_record/view&sell_record_code=' +sell_record_code
	    openPage(window.btoa(url),url,'已发货订单详情');
    }

    //查看物流信息
    function logistic_trace(index, row){
        var title = "物流轨迹跟踪记录(订单:" + row.sell_record_code + ")";
        if(kdniao_enable == 1){
            title += "，数据来源于快递鸟";
        }
        new ESUI.PopWindow("?app_act=oms/sell_record/logistic_trace&order_code=" +  row.sell_record_code, {
                title: title,
                width: 650,
                height: 550,
                onBeforeClosed: function () {
                },
                onClosed: function () {

                }
            }).show();
    }
      //质保书是否打印
     function get_is_print(value, row, index) {
        var print_warranty = (row.is_print_warranty == 0) ? "未打印" : "已打印";
        var str = '';
        str += "质保书" + print_warranty;
        return str;
     }
</script>
<!--<iframe src="" id="print_iframe" style="width:0px;height:0px;" ></iframe>-->

