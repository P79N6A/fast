
<?php echo load_js("baison.js,record_table.js,pur.js", true); ?>
<?php require_lib('util/oms_util', true); ?>
<?php echo load_js('xlodop.js'); ?>
<?php echo load_js('lodop.js'); ?>

<?php echo load_js('jquery.cookie.js') ?>

<?php
render_control('PageHead', 'head1', array('title' => '开票查询记录',
    'ref_table' => 'table'
));
?>


<?php
$keyword_type = array();
$keyword_type['invoice_no'] = '发票号';
$keyword_type['sell_record_code'] = '订单号';
//$keyword_type['buyer_name'] = '买家昵称';
//$keyword_type['receiver_name'] = '收货人';
//$keyword_type['express_no'] = '快递单号';
//$keyword_type['goods_name'] = '商品名称';
//$keyword_type['goods_code'] = '商品编码';
//$keyword_type['barcode'] = '商品条形码';
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
    'show_row' => 3,
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
            'help' => '可以支持以中、英文逗号间隔的多条订单号查询',
        ),
        array(
            'label' => '开票时间',
            'type' => 'group',
            'field' => 'invoice_time',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'record_time_start','value' => date('Y-m-d', strtotime("-2 months"))),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'record_time_end','value' => date('Y-m-d')),
            )
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
             'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
            'label' => '发票类型',
            'type' => 'select',
            'id' => 'invoice_type',
            'data' => ds_get_select_by_field('invoice_type', 2),
        ),
        array(
            'label' => '发票性质',
            'type' => 'select',
            'id' => 'is_red',
            'data' => ds_get_select_by_field('is_red', 2),
        ),
        array(
            'label' => '状态',
            'type' => 'select',
            'id' => 'status',
            'data' => ds_get_select_by_field('status', 1),
        ),
    )
));
?>
<ul class="toolbar frontool" id="tool">
     
         <li class="li_btns"><button class="button button-primary" onclick="opt_get_invoice_ret()">批量获取开票结果</button></li>
    
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
//$expressList = oms_opts2_by_tb('base_express', 'express_code', 'express_name', array(), 2);
//$storeList = oms_opts2_by_tb('base_store', 'store_code', 'store_name', array('status'=>1), 2);
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
                    array('id' => 'accept', 'title' => '获取开票结果', 'callback' => 'get_invoice_ret', 'show_cond' => "obj.status == '开票中'"),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '发票请求唯一流水号',
                'field' => 'fpqqlsh',
                'width' => '200',
                'align' => '',
//                'format_js' => array(
//	                'type' => 'html',
//	                'value'=>"<a param1=\"{sell_record_id}\" class=\"sell_record_view\" href=\"javascript:void(0)\">{record_code}</a>",
//                )
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '发票号码',
                'field' => 'invoice_no',
                'width' => '120',
                'align' => '',
            // 'format_js' => array('type' => 'function','value'=>'get_is_print')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '发票类型',
                'field' => 'invoice_type',
                'width' => '80',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '开票性质',
                'field' => 'is_red',
                'width' => '80',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '开票抬头',
                'field' => 'invoice_title',
                //'format_js'=> array('type'=>'map', 'value'=>$storeList),
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '实际开票金额',
                'field' => 'invoice_amount',
                // 'format_js'=> array('type'=>'map', 'value'=>$expressList),
                'width' => '80',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '开票日期',
                'field' => 'invoice_time',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '开票人',
                'field' => 'invoice_person',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单编号',
                'field' => 'sell_record_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'deal_code_list',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_name',
                'width' => '150',
                'align' => '',
            // 'format_js' => array('type' => 'function','value'=>'get_status')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '业务时间',
                'field' => 'invoice_time',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'status',
                'width' => '80',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '日志',
                'field' => '',
                'width' => '80',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'oms/invoice/OmSellInvoiceRecordModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'Invoice_id',
    'export' => array('id' => 'exprot_list', 'conf' => 'invoice_list_detail', 'name' => '开票查询结果列表','export_type' => 'file'),
    'params' => array('filter' => array('record_time_start' => date('Y-m-d', strtotime("-2 month")), 'record_time_end' => date('Y-m-d'))),
    'CheckSelection' => true,
    'events' => array(
        'rowdblclick' => 'billing',
    ),
));
?>
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
<script type="text/javascript">
        //读取已选中项
    function get_checked(isConfirm, obj, func){
        var ids = []
        var selecteds = tableGrid.getSelection();
        for(var i in selecteds){
            ids.push(selecteds[i].id);
        }

        if(ids.length == 0){
            BUI.Message.Alert("请选择开票记录", 'error');
            return
        }

        if(isConfirm) {
            BUI.Message.Show({
                title : '批量操作',
                msg : '是否执行开票记录'+obj.text()+'?',
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

    //获取开票结果
    function get_invoice_ret(_index, row) {

        var url = "?app_act=oms/invoice/order_invoice/get_invoice_result&app_fmt=json";
        var param = {id: row.id};

        $.post(url, param, function (ret) {
            if (ret.status < 0) {
                BUI.Message.Alert(ret.message, 'error');
            } else {
                BUI.Message.Alert('获取成功', 'info');
            }
            tableStore.load();
        }, 'json');
    }

    
    //批量获取开票结果记录
    function opt_get_invoice_ret(){
        get_checked(false, $(this), function(ids){
          
            for(var i=0;i<ids.length;i++){
                     var url = "?app_act=oms/invoice/order_invoice/get_invoice_result&app_fmt=json";
                    var param = {id: ids[i]};
                    $.post(url, param, function (ret) {
                        if (ret.status < 0) {
                            BUI.Message.Alert(ret.message, 'error');
                        } else {
                            BUI.Message.Alert('获取成功', 'info');
                        }
                        tableStore.load();
                    }, 'json');
            }
    
         })
    }
</script>
