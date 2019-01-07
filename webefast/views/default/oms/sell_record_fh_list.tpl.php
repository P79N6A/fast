<?php echo load_js('jquery.cookie.js') ?>
<?php echo load_js('comm_util.js') ?>
<?php
render_control('PageHead', 'head1', array('title' => '订单波次生成',
    'links' => array(
        //array('url'=>'oms/sell_record/add', 'title'=>'新增订单', 'is_pop'=>false, 'pop_size'=>'500,400'),
    ),
    'ref_table' => 'table'
));
?>
    <style>
        #num_start,#num_end{
            width:60px;
        }
        .detail_main td{
            width:200px;
            text-align: right;
            padding:10px;
        }
        #sort{ margin-top:8px; font-size:12px; border-collapse:inherit; color:#666;}
        #sort td.sort_btn{
            border:1px solid #d5d5d5;
            padding:0 15px;
            text-align:center;
            cursor:pointer;
            height:24px;
            border-radius:3px;
            position:relative;

        }
        td#header{
            padding:0 15px;
            text-align:center;
            height:24px;
            color:#1695ca;
            font-size:14px;
        }
        #start_time{width:100px;}
        #end_time{width:100px;}
        #shelf_name{width:150px}
        #spec1_name{width:150px}
        #spec2_name{width:150px}
        #clear_shelf{
            position: absolute;
            right: 31px;
            top: 1px;
            border:none;
            border-left:1px solid rgba(128, 128, 128, 0.64);
            height: 24px;
        }
        #clear_shelf:hover
        {
            background-color:#80808038;
        }
        #clear_spec1{
            position: absolute;
            right: 31px;
            top: 1px;
            border:none;
            border-left:1px solid rgba(128, 128, 128, 0.64);
            height: 24px;
        }
        #clear_spec1:hover
        {
            background-color:#80808038;
        }
        #clear_spec2{
            position: absolute;
            right: 31px;
            top: 1px;
            border:none;
            border-left:1px solid rgba(128, 128, 128, 0.64);
            height: 24px;
        }
        #clear_spec2:hover
        {
            background-color:#80808038;
        }
        .icon-remove{
            position: absolute;
            right: 4px;
            top: 4px;
        }
        #pay_time_start,#pay_time_end,#record_time_start,#record_time_end,#is_notice_time_start,#is_notice_time_end,#plan_time_start,#plan_time_end,#plan_send_time_start,#plan_send_time_end,#shelf_code{width:100px;}
    </style>

<?php
$keyword_type = array();
$keyword_type['sell_record_code'] = '订单号';
$keyword_type['deal_code'] = '交易号';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['goods_name'] = '商品名称';
$keyword_type['combo_barcode'] = '套餐条形码';
$keyword_type['buyer_name'] = '买家昵称';
$keyword_type['receiver_name'] = '收货人';
$keyword_type = array_from_dict($keyword_type);
$time_type = array();
$time_type['record_time'] = '下单时间';
$time_type['pay_time'] = '付款时间';
$time_type['notice_time'] = '通知配货时间';
$time_type['plan_time'] = '计划发货时间';
$time_type = array_from_dict($time_type);

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
        ),
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
//          'data' => ds_get_select('store'),
            'data' => load_model('base/StoreModel')->get_store_no_contain_wms(),
        ),
        array(
            'label' => '库位',
            'type' => 'group',
            'field' => 'shelf',
            'child' => array(
                array('type' => 'input','field'=>'shelf_name','readonly'=>1,'remark' => "<span class='x-icon x-icon-normal' id = 'clear_shelf' title='清除选中库位' ><i class='icon-remove'></i></span><a href='#' id = 'base_shelf'><img src='assets/img/search.png'></a><input type='hidden' id='shelf_code'>"),
            ),
        ),
        array(
            'label' => '配送方式',
            'type' => 'select_multi',
            'id' => 'express_code',
            'data' => ds_get_select('shipping', 0, array('status' => 1)),
        ),
        array(
            'label' => array('id' => 'time_type', 'type' => 'select', 'data' => $time_type),
            'type' => 'group',
            'field' => 'time_type',
            'data' => $time_type,
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'start_time','class'=>'input-small'),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'end_time','class'=>'input-small'),
            )
        ),
        array(
            'label' => '订单性质',
            'type' => 'select_multi',
            'id' => 'record_nature',
            'data' => ds_get_select_by_field('record_nature',0),
        ),
        array(
            'label' => '支付类型',
            'type' => 'select',
            'id' => 'pay_type',
            'data' => ds_get_select_by_field('pay_type'),
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
//        array(
//            'label' => '下单时间',
//            'type' => 'group',
//            'field' => 'daterange3',
//            'child' => array(
//                array('title' => 'start', 'type' => 'time', 'field' => 'record_time_start'),
//                array('pre_title' => '~', 'type' => 'time', 'field' => 'record_time_end', 'remark' => ''),
//            )
//        ),
//        array(
//            'label' => '通知配货时间',
//            'type' => 'group',
//            'field' => 'daterange4',
//            'child' => array(
//                array('title' => 'start', 'type' => 'time', 'field' => 'is_notice_time_start',),
//                array('pre_title' => '~', 'type' => 'time', 'field' => 'is_notice_time_end', 'remark' => ''),
//            )
//        ),
//        array(
//            'label' => '计划发货时间',
//            'type' => 'group',
//            'field' => 'daterange2',
//            'child' => array(
//                array('title' => 'start', 'type' => 'time', 'field' => 'plan_send_time_start'),
//                array('pre_title' => '~', 'type' => 'time', 'field' => 'plan_send_time_end', 'remark' => ''),
//            )
//        ),
        array(
            'label' => '买家留言',
            'type' => 'select',
            'id' => 'buyer_remark',
            'data' => ds_get_select_by_field('havestatus'),
        ),
        array(
            'label' => '商家留言',
            'type' => 'select',
            'id' => 'seller_remark',
            'data' => ds_get_select_by_field('havestatus'),
        ),
        array(
            'label' => '仓库留言',
            'type' => 'select',
            'id' => 'store_remark',
            'data' => ds_get_select_by_field('havestatus'),
        ),
        array(
            'label' => 'sku种类数',
            'type' => 'input',
            'id' => 'sku_num'
        ),
        array(
            'label' => '商品数量',
            'type' => 'group',
            'field' => 'num',
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'num_start'),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'num_end', 'remark' => ''),
            )
        ),
        /* array(
          'label' => '包含SKU',
          'type' => 'input',
          'id' => 'sku'
          ), */
        array(
            'label' => '排除商品编码',
            'type' => 'input',
            'id' => 'goods_code_exp'
        ),
        array(
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'source',
            //'data' => load_model('base/SaleChannelModel')->get_select()
            'data' => load_model('base/SaleChannelModel')->get_my_select()
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
//          'data' => ds_get_select('shop'),
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
            'label' => '订单标签',
            'type' => 'select_multi',
            'id' => 'order_tag',
            'data' => ds_get_select('order_label',4),
        ),
        array(
            'label' => '发票',
            'type' => 'select',
            'id' => 'invoice_status',
            'data' => ds_get_select_by_field('havestatus'),
        ),
//        array(
//            'label' => '付款时间',
//            'type' => 'group',
//            'field' => 'daterange1',
//            'child' => array(
//                array('title' => 'start', 'type' => 'time', 'field' => 'pay_time_start'),
//                array('pre_title' => '~', 'type' => 'time', 'field' => 'pay_time_end', 'remark' => ''),
//            )
//        ),
        array(
            'label' => '季节',
            'type' => 'select_multi',
            'id' => 'season_code',
            'data' => ds_get_select('season_code'),
        ),
//        array(
//            'label' => '品牌',
//            'type' => 'select_multi',
//            'id' => 'brand_code',
//            'data' => ds_get_select('brand_code'),
//        ),
        array(
            'label' => $response['goods_spec1_rename'],
            'type' => 'group',
            'field' => 'spec1',
            'child' => array(
                array('type' => 'input','field'=>'spec1_name','readonly'=>1,'remark' => "<span class='x-icon x-icon-normal' id = 'clear_spec1' title='清除选中规格' ><i class='icon-remove'></i></span><a href='#' id = 'base_spec1'><img src='assets/img/search.png' ></a><input type='hidden' id='spec1'>"),
            ),
        ),
        array(
            'label' => $response['goods_spec2_rename'],
            'type' => 'group',
            'field' => 'spec2',
            'child' => array(
                array('type' => 'input','field'=>'spec2_name','readonly'=>1,'remark' => "<span class='x-icon x-icon-normal' id = 'clear_spec2' title='清除选中规格' ><i class='icon-remove'></i></span><a href='#' id = 'base_spec2'><img src='assets/img/search.png' ></a><input type='hidden' id='spec2'>"),
            ),
        ),
        array(
            'label' => '品牌',
            'type' => 'select_multi',
            'id' => 'brand_code',
            'data' => load_model('prm/BrandModel')->get_brand_name()
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
//        array(
//            'label' => '是否加急单',
//            'type' => 'select',
//            'id' => 'is_rush',
//            'data' => ds_get_select_by_field('is_rush'),
//        ),
        array(
            'label' => '订单理论重量',
            'type' => 'group',
            'field' => 'weight',
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'weight_start', 'class' => 'input-small'),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'weight_end', 'class' => 'input-small', 'remark' => ''),
            ),
        ),
        array(
            'label' => '省份（多选）',
            'type' => 'select_multi',
            'id' => 'province_multi',
            'data' => array_map(function ($item) {
                $return[] = $item['id'];
                $return[] = $item['name'];
                return $return;
            }, load_model('base/TaobaoAreaModel')->get_area('1')),
        ),
    )
));
?>

    <table id="sort" style="margin-bottom: 10px;">
        <tr>
            <td id = "header">排序类型：</td>
            <td onclick = "sort(this)" id = "record_time" class="sort_btn">下单时间</td>
            <td onclick = "sort(this)" id = "pay_time" class="sort_btn">付款时间</td>
            <td onclick = "sort(this)" id = "plan_send_time" class="sort_btn">计划发货时间</td>
            <td onclick = "sort(this)" id = "is_notice_time" class="sort_btn">通知配货时间</td>
        </tr>
    </table>

    <ul class="toolbar frontool" id="tool">
        <!--<li><label class="checkbox" for="checkall"><input type="checkbox" class="checkall" name="checkall" id="checkall">全选</label></li>-->
        <li class="li_btns"><button class="button button-primary btn_opt_waves">生成波次</button></li>
        <li class="li_btns"><button class="button button-primary btn_edit_express_code">批量修改配送方式</button></li>
        <?php if ($response['sys_params']['cainiao_intelligent_delivery'] == 1) { ?>
            <li class="li_btns"><button class="button button-primary btn_cainiao_intelligent_delivery">批量菜鸟智能匹配</button></li>
        <?php }?>
        <!--<li><button class="button button-primary btn_edit_express_no">自动匹配物流</button></li>
        <li><button class="button button-primary btn_opt_print_express">批量打印快递单</button></li>
        <li><button class="button button-primary btn_opt_print_order">批量打印发货单</button></li>-->
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
    </script>
<?php
$expressList = oms_opts2_by_tb('base_express', 'express_code', 'express_name', array('status' => 1), 2);
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单号',
                'field' => 'sell_record_code',
                'width' => '200',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => "<a class=\"sell_record_view\" href=\"javascript:void(0)\">{sell_record_code}</a>",
                )
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'deal_code_list',
                'width' => '200',
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
                'title' => '计划发货时间',
                'field' => 'plan_send_time',
                'width' => '150',
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
                'title' => '店铺名称',
                'field' => 'shop_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '配送方式',
                'field' => 'express_code',
                'format_js' => array('type' => 'map', 'value' => $expressList),
                'width' => '80',
                'align' => '',
                //'editor'=>"{xtype : 'select', items: ".json_encode($expressList)."}"
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '物流单号',
                'field' => 'express_no',
                'width' => '100',
                'align' => '',
                //'editor' => "{xtype : 'text'}",
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
                'title' => '买家昵称',
                'field' => 'buyer_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收货地址',
                'field' => 'receiver_address',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '已付金额',
                'field' => 'paid_money',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库留言',
                'field' => 'store_remark',
                'width' => '90',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '客户留言',
                'field' => 'buyer_remark',
                'width' => '90',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商家留言',
                'field' => 'seller_remark',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单备注',
                'field' => 'order_remark',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单理论重量(kg)',
                'field' => 'goods_weigh',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品数量',
                'field' => 'goods_num',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单标签',
                'field' => 'sell_record_tag',
                'width' => '100',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'oms/SellRecordModel::get_wave_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'sell_record_id',
    'export' => array('id' => 'exprot_list', 'conf' => 'deliver_record_list', 'name' => '发货订单','export_type'=>'file'),
    'CheckSelection' => true,
    'customFieldTable' => 'sell_record_fh_list/table',
    'CascadeDetail' => 'show_detail',
//    'events' => array(
//        'rowdblclick' => 'showDetail',
//    ),
    //'CellEditing' => true,
));
?>


    <!--<div id="searchAdv" style="display: none">
        <div class="row">
            <div class="control-group span8">
                <label class="control-label" >生成波次</label>
                <div class="controls">
                    <div class="button-group" id="b1">
                    </div>
                </div>
            </div>
            <div class="control-group span8">
                <label class="control-label" >扫描验货</label>
                <div class="controls">
                    <div class="button-group" id="b2">
                    </div>
                </div>
            </div>
            <div class="control-group span8">
                <label class="control-label" >打印快递单</label>
                <div class="controls">
                    <div class="button-group" id="b3">
                    </div>
                </div>
            </div>
            <div class="control-group span8">
                <label class="control-label" >打印发货单</label>
                <div class="controls">
                    <div class="button-group" id="b4">
                    </div>
                </div>
            </div>
            <div class="control-group span8">
                <label class="control-label" >订单称重</label>
                <div class="controls">
                    <div class="button-group" id="b5">
                    </div>
                </div>
            </div>
                    <div class="control-group span8">
                        <label class="control-label" >临近发货</label>
                        <div class="controls">
                            <div class="button-group" id="b5">
                            </div>
                        </div>
                    </div>
        </div>
    </div>
    -->
    <div  id="result_grid" class="panel-body"></div>
    <div id="result_grid_pager"></div>
    <script type="text/javascript">
        $(function () {
            var expand_detail_node = $(".bui-grid-header > table:nth-child(1) > thead:nth-child(1) > tr:nth-child(1) > th:nth-child(2) > div:nth-child(1) > span:nth-child(1)");
            tableStore.on('load',function(){
                if(expand_detail_node.hasClass('bui-grid-cascade-expand')){
                    expand_detail_node.removeClass('bui-grid-cascade-expand');
                    tableCascadeDetail.collapseAll();
                }
            });
            expand_detail_node.html('<i class="bui-grid-cascade-icon" id="expand_detail"></i>');
            expand_detail_node.click(function() {
                tableStore_data = tableStore.getResult();
                if(tableStore_data.length == 0){
                    BUI.Message.Alert("未查询或查询数据不存在，无法展开！","error");
                    return;
                }
                var all_plus = $(".bui-grid-cascade");
                all_plus.each(function(){
                    if($(this).attr('class') == "bui-grid-cascade"){
                        $(this).attr('class',"bui-grid-cascade bui-grid-cascade-expand");
                    } else{
                        $(this).attr('class',"bui-grid-cascade");
                    }
                });
                if($(this).hasClass('bui-grid-cascade-expand')){
                    $(this).removeClass('bui-grid-cascade-expand');
                    tableCascadeDetail.collapseAll();
                }else  {
                    tableCascadeDetail.expandAll();
                    $(this).addClass('bui-grid-cascade-expand');
                }
            })
        });
        $(function () {
            $("#shelf_code").attr("value","");
            $("#shelf_name").attr("value","");
            $("#spec1").attr("value","");
            $("#spec1_name").attr("value","");
            $("#spec2").attr("value","");
            $("#spec2_name").attr("value","");
        });
        $("#base_shelf").click(function () {
            show_select('shelf');
        });
        $("#base_spec1").click(function(){
            show_select('spec1');
        });
        $("#base_spec2").click(function(){
            show_select('spec2');
        });
        $("#clear_shelf").click(function () {
            $("#shelf_code").attr("value","");
            $("#shelf_name").attr("value","");
        });
        $("#clear_spec1").click(function () {
            $("#spec1").attr("value","");
            $("#spec1_name").attr("value","");
        });
        $("#clear_spec2").click(function () {
            $("#spec2").attr("value","");
            $("#spec2_name").attr("value","");
        });
        function show_select(_type) {
            if(_type=='shelf'){
                var store_code = $("#store_code").val();
                var param = {'store_code':store_code};
                var url = '?app_act=oms/sell_record/select_shelf';
                var title='请选择库位'
            }else if(_type=='spec1'){
                var url = '?app_act=oms/sell_record/select_spec1';
                var title='<?php echo '请选择'. $response['goods_spec1_rename']?>';
            }else if(_type=='spec2'){
                var url = '?app_act=oms/sell_record/select_spec2';
                var title='<?php echo '请选择'. $response['goods_spec2_rename']?>';
            }
            if (typeof (top.dialog) !== 'undefined') {
                top.dialog.remove(true);
            }
            var buttons = [
                {
                    text:'保存继续',
                    elCls : 'button button-primary',
                    handler: function () {
                        var data = top.tablesGrid.getSelection();
                        if (data.length > 0) {
                            deal_data_1(data, _type);
                        }
                        auto_enter('#shelf_code');
                        top.tablesStore.load();
                        if(_type=='shelf'){
                            var string_name = $("#shelf_name").val();
                            var string_code = $("#shelf_code").val();
                        }else if(_type=='spec1'){
                            var string_name = $("#spec1_name").val();
                            var string_code = $("#spec1").val();
                        }else if(_type=='spec2'){
                            var string_name = $("#spec2_name").val();
                            var string_code = $("#spec2").val();
                        }
                        if (string_name !== '') {
                            store_shelf_name(string_name,'name',_type);
                            store_shelf_name(string_code,'code',_type);
                        }
                    }
                },
                {
                    text:'保存退出',
                    elCls : 'button button-primary',
                    handler: function () {
                        var data = top.tablesGrid.getSelection();
                        if (data.length > 0) {
                            deal_data_1(data, _type);
                        }
                        auto_enter('#shelf_code');
                        if(_type=='shelf'){
                            var string_name = $("#shelf_name").val();
                            var string_code = $("#shelf_code").val();
                        }else if(_type=='spec1'){
                            var string_name = $("#spec1_name").val();
                            var string_code = $("#spec1").val();
                        }else if(_type=='spec2'){
                            var string_name = $("#spec2_name").val();
                            var string_code = $("#spec2").val();
                        }
                        if (string_name !== '') {
                            store_shelf_name(string_name,'name',_type);
                            store_shelf_name(string_code,'code',_type);
                        }
                        this.close();
                    }
                },
                {
                    text:'取消',
                    elCls : 'button',
                    handler: function () {
                        this.close();
                    }
                }
            ];
            top.BUI.use('bui/overlay', function (Overlay) {
                top.dialog = new Overlay.Dialog({
                    title: title,
                    width: '800',
                    height: '500',
                    loader: {
                        url: url,
                        autoLoad: true, //不自动加载
                        params: param, //附加的参数
                        lazyLoad: false, //不延迟加载
                        dataType: 'text'   //加载的数据类型
                    },
                    align: {
                        //node : '#t1',//对齐的节点
                        points: ['tc', 'tc'], //对齐参考：http://dxq613.github.io/#positon
                        offset: [0, 20] //偏移
                    },
                    mask: true,
                    buttons: buttons
                });
                top.dialog.on('closed', function (ev) {

                });
                top.dialog.show();
            });
        }
        //去重
        function store_shelf_name(string_name,type,_type) {
            if(_type=='shelf'){
                string = string_name.split(',');
                var hash=[],arr=[];
                for (var i = 0,elem;(elem=string[i])!=null; i++) {
                    if(!hash[elem]){
                        arr.push(elem);
                        hash[elem]=true;
                    }
                }
                if (type === 'code') {
                    $("#shelf_code").val(arr.join(','));
                }else{
                    $("#shelf_name").val(arr.join(','));
                }
            }else if(_type=='spec1'){
                string = string_name.split(',');
                var hash=[],arr=[];
                for (var i = 0,elem;(elem=string[i])!=null; i++) {
                    if(!hash[elem]){
                        arr.push(elem);
                        hash[elem]=true;
                    }
                }
                if (type === 'code') {
                    $("#spec1").val(arr.join(','));
                }else{
                    $("#spec1_name").val(arr.join(','));
                }
            }else if(_type=='spec2'){
                string = string_name.split(',');
                var hash=[],arr=[];
                for (var i = 0,elem;(elem=string[i])!=null; i++) {
                    if(!hash[elem]){
                        arr.push(elem);
                        hash[elem]=true;
                    }
                }
                if (type === 'code') {
                    $("#spec2").val(arr.join(','));
                }else{
                    $("#spec2_name").val(arr.join(','));
                }
            }

        }
        function deal_data_1(obj, _type) {
            var shelf_code = new Array();
            var shelf_name = new Array();
            var string_code = "";
            var string_name = "";
            if (_type == 'shelf') {

                string_code = $("#shelf_code").val();
                string_name = $("#shelf_name").val();
                $.each(obj, function (i, val) {
                    shelf_code[i] = val[_type + '_code'];
                    shelf_name[i] = val[_type + '_name']+"["+val[_type + '_code']+"]";
                });
                shelf_code = shelf_code.join(',');
                shelf_name = shelf_name.join(',');
                if (string_code == "") {
                    string_code = shelf_code;
                    string_name = shelf_name;
                    $("#shelf_code").val(string_code);
                    $("#shelf_name").val(string_name);
                } else {
                    string_code = string_code + ',' + shelf_code;
                    string_name = string_name + ',' + shelf_name;
                    $("#shelf_code").val(string_code);
                    $("#shelf_name").val(string_name);
                }
            } else if (_type == 'spec1') {

                string_code = $("#spec1").val();
                string_name = $("#spec1_name").val();
                $.each(obj, function (i, val) {
                    shelf_code[i] = val[_type + '_code'];
                    shelf_name[i] = val[_type + '_name'];
                });
                shelf_code = shelf_code.join(',');
                shelf_name = shelf_name.join(',');
                if (string_code == "") {
                    string_code = shelf_code;
                    string_name = shelf_name;
                    $("#spec1").val(string_code);
                    $("#spec1_name").val(string_name);
                } else {
                    string_code = string_code + ',' + shelf_code;
                    string_name = string_name + ',' + shelf_name;
                    $("#spec1").val(string_code);
                    $("#spec1_name").val(string_name);
                }
            } else if (_type == 'spec2') {
                string_code = $("#spec2").val();
                string_name = $("#spec2_name").val();
                $.each(obj, function (i, val) {
                    shelf_code[i] = val[_type + '_code'];
                    shelf_name[i] = val[_type + '_name'];
                });
                shelf_code = shelf_code.join(',');
                shelf_name = shelf_name.join(',');
                if (string_code == "") {
                    string_code = shelf_code;
                    string_name = shelf_name;
                    $("#spec2").val(string_code);
                    $("#spec2_name").val(string_name);
                } else {
                    string_code = string_code + ',' + shelf_code;
                    string_name = string_name + ',' + shelf_name;
                    $("#spec2").val(string_code);
                    $("#spec2_name").val(string_name);
                }

            }
        }
        function auto_enter(_id) {
            var e = jQuery.Event("keyup");//模拟一个键盘事件
            e.keyCode = 13;//keyCode=13是回车
            $(_id).trigger(e);
        }
        function toolbarmaker(Toolbar, children, id) {
            var g = new Toolbar.Bar({
                elCls: 'button-group',
                itemStatusCls: {
                    selected: 'active' //选中时应用的样式
                },
                defaultChildCfg: {
                    elCls: 'button button-small',
                    selectable: true //允许选中
                },
                children: children,
                render: '#' + id
            });
            g.render();
            g.on('itemclick', function (ev) {
                //$('#l1').text(ev.item.get('id') + ':' + ev.item.get('content'));
            });
        }

        //    $(document).ready(function () {
        //        $("#searchForm").find(".row").eq(0).before($("#searchAdv").html())
        //        $("#searchAdv").remove()
        //
        //        BUI.use('bui/toolbar', function (Toolbar) {
        //            //可勾选
        //            var b1 = [
        //                {content: '全部', id: 'all'},
        //                {content: '已生成', id: '1'},
        //                {content: '未生成', id: '0', selected: true},
        //            ];
        //            var b2 = [
        //                {content: '全部', id: 'all'},
        //                {content: '已发货', id: '4'},
        //                {content: '未发货', id: '1,2,3', selected: true}
        //            ];
        //            var b3 = [
        //                {content: '全部', id: 'all', selected: true},
        //                {content: '已打印', id: '1'},
        //                {content: '未打印', id: '0'}
        //            ];
        //            var b4 = [
        //                {content: '全部', id: 'all', selected: true},
        //                {content: '已打印', id: '1'},
        //                {content: '未打印', id: '0'}
        //            ];
        //            var b5 = [
        //                {content: '全部', id: 'all', selected: true},
        //                {content: '已称重', id: '1'},
        //                {content: '未称重', id: '0'}
        //            ];
        //            toolbarmaker(Toolbar, b1, 'b1');
        //            toolbarmaker(Toolbar, b2, 'b2');
        //            toolbarmaker(Toolbar, b3, 'b3');
        //            toolbarmaker(Toolbar, b4, 'b4');
        //            toolbarmaker(Toolbar, b5, 'b5');
        ////            toolbarmaker(Toolbar, b5, 'b5');
        //        });
        //
        tableStore.on('beforeload', function (e) {
//            e.params.waves_record_id = $("#b1").find(".active").attr("id");
//            e.params.shipping_status = $("#b2").find(".active").attr("id");
//            e.params.is_weigh = $("#b5").find(".active").attr("id");
            e.params.contain_express_money = $("#contain_express_money").attr('checked') == 'checked' ? '1' : '0';
            var sort_e = $("#sort").find(".active");
            if (sort_e.length > 0) {
                e.params.is_sort = $("#sort").find(".active").attr("id");
            }
            e.params.shelf_code = $("#shelf_code").val();
            e.params.spec1= $("#spec1").val();
            e.params.spec2= $("#spec2").val();
            tableStore.set("params", e.params);
//            e.params.close_send_out = $("#b5").find(".active").attr("id");
        });
        //
        //    });

        function show_detail(row) {
            var ret;
            var data = {
                'sell_record_code': row.sell_record_code,
                'app_tpl': 'oms/deliver_record_detail',
                'app_page': 'NULL'
            };
            $.ajax({
                type: "post",
                url: "?app_act=oms/sell_record/get_detail_by_sell_record_code",
                data: data,
                async: false,
                success: function (data) {
                    ret = data;
                }
            });
            return ret;
        }
    </script>




    <script type="text/javascript">
        var opts = [
            'opt_waves',
            'edit_express_code', 'edit_express_no',
            'opt_print_goods', 'opt_print_express', 'opt_print_sellrecord','cainiao_intelligent_delivery'
        ];
        var url = '<?php echo get_app_url('base/store/get_area'); ?>';
        $(document).ready(function () {
            $("#kw_end").css("width", "85px");
            $("#kw_start").css("width", "85px");
            $("#num_start").css("width", "85px");
            $("#num_end").css("width", "85px");
            tableStore.on('beforeload', function (e) {
                //e.params.ex_list_tab = $(".oms_tabs").find(".active").find("a").attr("id");
            })



            //全选
            $('.checkall:checkbox').click(function () {
                var c = $('[name=ckb_record_id]')
                c.prop("checked", !c.prop("checked"))
            })
            $('#country').change(function () {
                var parent_id = $(this).val();
                if(parent_id===''){
                    parent_id=1;
                }
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

            //初始化按钮
            btn_init();
            set_sell_record_view();
            $('#country').change();
            tableStore.load();


            BUI.use('bui/tooltip',function (Tooltip) {
                var t = new Tooltip.Tip({
                    trigger : '.btn_edit_express_code',
                    alignType : 'top-left',
                    offset : 10,
                    title : '若需要修改配送方式，需要订单拦截!',
                    elCls : 'tips tips-no-icon',
                    titleTpl : '<p style="width:230px;height:50px;color:red;font:bold 15px 微软雅黑">{title}</p>'
                });
                t.render();
            });


        })
        function set_sell_record_view() {
            tableGrid.on("aftershow", function (e) {
                $('.sell_record_view').on('click', function () {
                    var url = '?app_act=oms/sell_record/view&sell_record_code=' + $(this).text()
                    openPage(window.btoa(url), url, '订单详情');
                });
            });
        }

        //初始化批量操作按钮
        function btn_init() {
            for (var i in opts) {
                var f = opts[i]
                switch (f) {
                    case "opt_waves":
                        btn_init_opt_waves();
                        break
                    case "edit_express_code":
                        btn_init_edit_express_code();
                        break
                    case "edit_express_no":
                        btn_init_edit_express_no();
                        break
                    case "opt_print_goods":
                        btn_init_opt_print_goods();
                        break
                    case "opt_print_express":
                        btn_init_opt_print_express();
                        break
                    case "opt_print_sellrecord":
                        btn_init_opt_print_sellrecord();
                        break
                    case "cainiao_intelligent_delivery":
                        btn_init_cainiao_intelligent_delivery();
                        break
                }
            }
        }

        //读取已选中项
        function get_checked(isConfirm, obj, func) {
            /*var ids = $("[name=ckb_record_id]:checkbox:checked").map(function(){
             return $(this).val()
             }).get()*/

            var ids = []
            var selecteds = tableGrid.getSelection();
            for (var i in selecteds) {
                ids.push(selecteds[i].sell_record_code)
            }

            if (ids.length == 0) {
                BUI.Message.Alert("请选择订单", 'error');
                return
            }

            if (isConfirm) {
                BUI.Message.Show({
                    title: '自定义提示框',
                    msg: '是否执行订单' + obj.text() + '?',
                    icon: 'question',
                    buttons: [
                        {
                            text: '是',
                            elCls: 'button button-primary',
                            handler: function () {
                                func.apply(null, [ids])
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
            } else {
                func.apply(null, [ids])
            }
        }

        //初始化生成波次
        function btn_init_opt_waves() {
            $(".btn_opt_waves").click(function () {
                //get_checked(true, $(this), function(ids){
                get_checked(false, $(this), function (ids) {
                    var params = {"sell_record_id_list": ids, is_check: 1};
                    $.post("?app_act=oms/waves_record/create_waves", params, function (data) {
                        if (data.status == 1) {
                            //  BUI.Message.Alert(data.message, 'info')
                            waves_record_view(data.message, data.data);
                            //刷新
                            tableStore.load()
                        } else if (data.status == -2) {
                            BUI.Message.Show({
                                title: '自定义提示框',
                                msg: data.message,
                                icon: 'question',
                                buttons: [
                                    {
                                        text: '是',
                                        elCls: 'button button-primary',
                                        handler: function () {
                                            var params = {"sell_record_id_list": ids, is_check: 0};
                                            var _self = this;
                                            $.post("?app_act=oms/waves_record/create_waves", params, function (data) {
                                                if (data.status == 1) {
                                                    //BUI.Message.Alert(data.message, 'info');
                                                    ;
                                                    waves_record_view(data.message, data.data);
                                                    //刷新
                                                    tableStore.load();
                                                    _self.close();
                                                } else {
                                                    _self.close();
                                                    BUI.Message.Alert(data.message, 'error')
                                                }
                                            }, "json");
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
                        } else {
                            BUI.Message.Alert(data.message, 'error')
                        }
                    }, "json");
                })
            });
        }

        function waves_record_view(message, id) {
            // BUI.Message.Confirm(message,function(){
            var url = '?app_act=oms/waves_record/view&waves_record_id=' + id;
            openPage(window.btoa(url), url, '波次拣货单');
            // });
        }

        //批量修改配送方式
        function btn_init_edit_express_code() {
            $(".btn_edit_express_code").click(function () {
                get_checked(false, $(this), function (ids) {
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
            })
        }

        //自动匹配物流单号
        function btn_init_edit_express_no() {
            $(".btn_edit_express_no").click(function () {
                get_checked(false, $(this), function (ids) {
                    new ESUI.PopWindow("?app_act=oms/sell_record/edit_express_no&sell_record_id_list=" + ids.toString(), {
                        title: "自动匹配物流单号",
                        width: 800,
                        height: 600,
                        onBeforeClosed: function () {
                        },
                        onClosed: function () {
                            //刷新数据
                            tableStore.load()
                        }
                    }).show()
                })
            })
        }

        //打印商品明细
        function btn_init_opt_print_goods() {
            $(".btn_opt_print_goods").click(function () {
                get_checked(false, $(this), function (ids) {
                    //TODO:打印
                    ids = ids.toString();

                    var url = '?app_act=oms/sell_record/mark_sell_record_print';
                    var params = {};
                    params.record_ids = ids;
                    $.post(url, params, function (data) {

                    });


                    var window_is_block = window.open('?app_act=sys/danju_print/do_print_record&app_page=null&print_data_type=order_sell_record&record_ids=' + ids);
                    if (null == window_is_block) {
                        alert("您的浏览器阻止了打印发货单的新窗口,请在浏览器的阻止提示处选择允许弹出新窗口");
                    }
                })
            })
        }

        //打印快递单
        function btn_init_opt_print_express() {
            $(".btn_opt_print_express").click(function () {
                get_checked(false, $(this), function (ids) {
                    //TODO:打印
                    ids = ids.toString();
                    print_express.print_express(ids);
                })
            })
        }

        //打印发货单
        function btn_init_opt_print_sellrecord() {

        }

        function sort(_this) {
            $("#sort .sort_btn").css({"color": "#666"});
            $("#sort .sort_btn").removeClass("active");
            $(_this).css({"color": "#1695ca"});
            $(_this).addClass("active");
            tableStore.load();
        }

        //菜鸟智能
        function btn_init_cainiao_intelligent_delivery() {
            $(".btn_cainiao_intelligent_delivery").click(function () {
                //   $(".btn_cainiao_intelligent_delivery").attr("disabled", "disalbed");
                get_checked(false, $(this), function (ids) {
                    BUI.use('bui/overlay', function (Overlay) {
                        var dialog = new Overlay.Dialog({
                            width: 450,
                            height: 120,
                            elCls: 'custom-dialog',
                            bodyContent: '<p style="font-size:15px">正在获取快递信息，请稍后...</p>',
                            buttons: []
                        });
                        dialog.show();
                    });
                    var params = {"sell_record_code_list": ids.toString()};
                    $.post("?app_act=oms/sell_record_notice/cainiao_intelligent_delivery", params, function (data) {
                        if (data.status == 1) {
                            //    $(".btn_cainiao_intelligent_delivery").removeAttr("disabled");
                            //刷新
                            $(".bui-ext-close .bui-ext-close-x").click();
                            BUI.Message.Alert(data.message, function () {
                                tableStore.load();
                            },'success')
                        } else {
                            //  $(".btn_cainiao_intelligent_delivery").removeAttr("disabled");
                            $(".bui-ext-close .bui-ext-close-x").click();
                            BUI.Message.Alert(data.message, 'error')
                        }
                    }, "json");
                })
            })
        }

        var selectPopWindowpbrand_code = {
            dialog: null,
            callback: function(value) {
                var brand_name = value[0]['brand_name'];
                var brand_code = value[0]['brand_code'];
                $('#brand_code').val(brand_code);
                $('#brand_name').val(brand_name);
                if (selectPopWindowpbrand_code.dialog != null) {
                    selectPopWindowpbrand_code.dialog.close();
                }
            }
        };

        $('#brand_code_select_pop').click(function() {
            selectPopWindowp_code.dialog = new ESUI.PopSelectWindow('?app_act=prm/goods/brand', 'selectPopWindowpbrand_code.callback', {title: '选择商品品牌', width: 900, height:500 ,ES_pFrmId:'<?php echo $request['ES_frmId'];?>' }).show();
        });
    </script>

    <!-- 打印快递单公共文件 -->
<?php
//include_once (get_tpl_path('oms/print_express'));?>