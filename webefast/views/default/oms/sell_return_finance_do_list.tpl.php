<?php
render_control('PageHead', 'head1', array('title' => '待退款售后服务单',
    'links' => array(
    //array('url'=>'oms/sell_record/add', 'title'=>'新增订单', 'is_pop'=>false, 'pop_size'=>'500,400'),
    ),
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchSelectButton', 'select_button', array(
    'fields' => array(
        array('id' => 'return_shipping_status', 'title' => '收货状态', 'children' => array(
                array('content' => '全部', 'id' => 'all',),
                array('content' => '未确认收货', 'id' => '0' ),
                array('content' => '已确认收货', 'id' => '1','selected' => true,),
            )),
        array('id' => 'sub_return_status', 'title' => '退款状态', 'children' => array(
                array('content' => '全部', 'id' => 'all'),
                array('content' => '未退款', 'id' => '2', 'selected' => true,),
                array('content' => '已退款', 'id' => '1',),
            )),
    /*
      array('id'=>'return_status','title'=>'退回状态','children'=>array(
      array('content'=>'全部','id'=>'all'),
      array('content'=>'未退回','id'=>'1','selected'=>true,),
      array('content'=>'已退回','id'=>'2',),
      )),
     */
    ),
    'for' => 'searchForm',
    'style' => 'width:192px;'
));
?>

<?php
$keyword_type = array();
$keyword_type['deal_code'] = '交易号';
$keyword_type['sell_return_code'] = '退单号';
$keyword_type['refund_id'] = '平台退单号';
$keyword_type['sell_record_code'] = '订单号';
$keyword_type['return_mobile'] = '手机号码';
$keyword_type['buyer_name'] = '买家昵称';
$keyword_type = array_from_dict($keyword_type);


$time_type = array();
$time_type['create_time']='退单创建时间';
$time_type['receive_time']='确认收货时间';
$time_type['agree_refund_time']='财务退款时间';
$time_type = array_from_dict($time_type);

    $buttons = array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
        array(
            'label' => '导出',
            'id' => 'exprot_list',
        ),
    );

    
    $fields = array(
                array(
			'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
			'type' => 'input',
			'title' => '',
			'data' => $keyword_type,
			'id' => 'keyword',
			'help' => '支持多交易号、多订单号查询，用逗号隔开；
                            以下字段支持模糊查询：交易号、退单号、平台退单号、订单号、手机号码、买家昵称',
		),
        array(
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'sale_channel_code',
            'data' => load_model('base/SaleChannelModel')->get_select()
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
	        'label' => '买家支付宝',
	        'type' => 'input',
	        'id' => 'buyer_alipay_no',
        ),
        array(
            'label' => array('id' => 'time_type','type' => 'select','data' => $time_type),
            'type' => 'group',
            'field' => 'daterange4',
            'data' => $time_type,
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '退货原因',
            'type' => 'select_multi',
            'id' => 'return_reason_code',
            'data' => ds_get_select('return_reason'),
        ),
        array(
            'label' => '退单标签',
            'type' => 'select_multi',
            'id' => 'return_label_name',
            'data' =>load_model('base/ReturnLabelModel')->get_select(),
        ),
        array(
            'label' => '是否换货',
            'type' => 'select',
            'id' => 'is_change',
            'data' => ds_get_select_by_field('boolstatus'),
        ),
    );

    render_control('SearchForm', 'searchForm', array(
	'buttons' => $buttons,
	'show_row' => 2,
	'fields' =>$fields,
));
?>
<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '全部', 'active' => true, 'id' => 'tabs_all'),// 默认选中active=true的页签
        array('title' => '线下退款', 'active' => false, 'id' => 'tabs_offline'), 
        array('title' => '线上退款', 'active' => false, 'id' => 'tabs_online')
    ),
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<ul class="toolbar frontool" id="tool">
    <li class="li_btns"><button class="button button-primary" onclick="opt_confirm()">批量确认退款</button></li>
    <li class="li_btns"><button class="button button-primary" id="btn_opt_label2">批量打标</button></li>
    <div class="front_close">&gt;</div>
</ul>
<script>
$(function(){
	function tools(){
        $(".frontool").animate({left:'-99.1%'},1000);
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
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '170',
                'align' => '',
                'buttons' => array(
                    array(
                        'id' => 'view',
                        'title' => '查看',
                        'callback' => 'do_view'
                    ),
                    array(
                        'id' => 'check1',
                        'title' => '确认退款',
                        'callback' => 'do_confirm',
                        'show_cond' => 'obj.finance_check_status == 0 && obj.return_show != 2 && obj.return_status == 1'
                    ),
//                    array(
//                        'id' => 'finish',
//                        'title' => '财务退回',
//                        'callback' => 'do_reject',
//                        'show_cond' => 'obj.finance_check_status == 2'
//                    ),
                     array('id' => 'communicate_log', 'title' => '沟通日志', 'callback' => 'communicate_log'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺名称',
                'field' => 'shop_code_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '销售平台',
                'field' => 'sale_channel_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退款金额',
                'field' => 'refund_total_fee',
                'width' => '80',
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
	            'title' => '买家支付宝账号',
	            'field' => 'buyer_alipay_no',
	            'width' => '120',
	            'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退单号',
                'field' => 'sell_return_code',
                'width' => '120',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href=javascript:view({sell_return_id})>{sell_return_code}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '标签',
                'field' => 'sell_return_tag',
                'width' => '130',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退单状态',
                'field' => 'return_order_status',
                'width' => '130',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '确认收货时间',
                'field' => 'receive_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '是否换货',
                'field' => 'change_record_txt',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'deal_code',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台退单号',
                'field' => '',
                'width' => '100',
                'align' => ''
            ),
            /*
              array(
              'type' => 'text',
              'show' => 1,
              'title' => '审核人',
              'field' => 'finance_confirm_person',
              'width' => '100',
              'align' => ''
              ), */
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退款人',
                'field' => 'agree_refund_person',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退款时间',
                'field' => 'agreed_refund_time',
                'width' => '100',
                'align' => ''
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
                'title' => '手机号码',
                'field' => 'return_mobile',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退货原因',
                'field' => 'return_reason',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '卖家退单备注',
                'field' => 'return_remark',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 0,
                'title' => '申请退货数',
                'field' => 'note_num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 0,
                'title' => '实际退货数',
                'field' => 'recv_num',
                'width' => '100',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'oms/SellReturnFinanceModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'sell_return_id',
    'CheckSelection' => true,
    'customFieldTable' => 'sell_return_finance/do_list',
    'export' => array('id' => 'exprot_list', 'conf' => 'return_finance_list', 'name' => '待退款'),
    'params' => array('filter' => array('sub_return_status' => '2','return_shipping_status' => '1')),
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
    'CascadeTable' => array(
    'list' => array(
            array('type' => 'text', 'title' => '商品名称', 'field' => 'goods_name', 'width' => '180',),
            array('type' => 'text', 'title' => '商品编码', 'field' => 'goods_code', 'width' => '120',),
            array('type' => 'text', 'title' => '规格1', 'field' => 'spec1_name', 'width' => '80',),
            array('type' => 'text', 'title' => '规格2', 'field' => 'spec2_name', 'width' => '80',),
            array('type' => 'text', 'title' => '商品条形码', 'field' => 'barcode', 'width' => '130'),
            array('title' => '申请退货数量', 'field' => 'note_num', 'width' => '130'),
            array('title' => '实际退货数量', 'width' => '130', 'field' => 'recv_num'),
            array('title' => '吊牌价', 'width' => '100', 'field' => 'goods_price'),
            array('title' => '实际应退款', 'width' => '100', 'field' => 'avg_money'),
        ),
    'page_size' => 50,
    'url' => get_app_url('oms/sell_return_finance/get_detail_list_by_sell_return_code&app_fmt=json'),
    'params' => 'sell_return_code',
    ),
));
?>
<input type="hidden" id="tbl_muil_check_ids" value=""/>
<script type="text/javascript">
    $(function () {
        $("#deal_code").css("border", "red 1px solid");

        //TAB选项卡
        $("#TabPage1 a").click(function () {
            tableStore.load();
        });
        $("input[name='is_normal']").change(function () {
            tableStore.load();
        });
        tableStore.on('beforeload', function (e) {
            e.params.do_list_tab = $("#TabPage1").find(".active").find("a").attr("id");
            tableStore.set("params", e.params);

        });
    });

//数据行双击打开新页面显示详情
    function showDetail(_index, row) {
        view(row.sell_return_id);
    }
    function do_view(_index, row) {
        view(row.sell_return_id);
    }
    function view(sell_return_id) {
        openPage('<?php echo base64_encode('?app_act=oms/sell_return_finance/view&sell_return_id') ?>' + sell_return_id, '?app_act=oms/sell_return_finance/view&sell_return_id=' + sell_return_id, '财务退款单');
    }
    function  do_confirm(_index, row) {
        var url = '?app_act=oms/sell_return/opt';
        var data = {"sell_return_code": row.sell_return_code, "type": 'opt_finance_confirm'};
        if (row.change_record !== '') {
            BUI.Message.Confirm('此退单为换货单，换货单号：' + row.change_record + '，请确认退款金额，避免多退', function () {
                _do_operate(url, data);
            }, 'question');
        } else {
            _do_operate(url, data);
        }
    }
    function do_reject(_index, row) {
        url = '?app_act=oms/sell_return/opt';
        data = {"sell_return_code": row.sell_return_code, "type": 'opt_finance_reject'};
        _do_operate(url, data);
    }
    function _do_operate(url, data) {
        $.ajax({
            type: 'POST', dataType: 'json',
            url: url,
            data: data,
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    //读取已选中项
    function get_checked(isConfirm, obj, func){
        var ids = []
        var selecteds = tableGrid.getSelection();
        for(var i in selecteds){
            ids.push(selecteds[i].sell_return_code)
        }

        if(ids.length == 0){
            BUI.Message.Alert("请选择退单", 'error');
            return
        }

        if(isConfirm) {
            BUI.Message.Show({
                title : '',
                msg : '是否批量确认退款',
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
    
    function opt_confirm(){
        get_checked(true, $(this), function(ids){
            var params = {"record_ids": ids, "type": 'opt_finance_confirm'}
            $.post("?app_act=oms/sell_return/batch_confirm ",params, function(data) {
                if(data.status == 1){
                    BUI.Message.Alert(data.message, 'success');
                    tableStore.load();
                }else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json");
        })
    }
    $("#btn_opt_label2").click(function(){
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
    //沟通日志
    function communicate_log(index, row) {
        new ESUI.PopWindow("?app_act=oms/sell_return/communicate_log&sell_return_code="+row.sell_return_code,{
            title: "沟通日志",
            width: 450,
            height: 350,
            onBeforeClosed: function () {
            },
            onClosed: function () {
                component("all", "view");
                //刷新按钮权限
                //                    btn_check()
            }
        }).show();
    }
    
</script>
