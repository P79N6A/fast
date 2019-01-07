<style>
    #pay_time_start{width:100px;}
    #pay_time_end{width:100px;}
    #record_time_start{width:100px;}
    #record_time_end{width:100px;}
    #money_start{width: 65px;}
    #money_end{width: 65px;}
</style>
<?php echo load_js('jquery.cookie.js') ?>
<?php echo load_js('comm_util.js') ?>
<?php
render_control('PageHead', 'head1', array('title' => '分销订单列表',
    'links' => array(
        array('url' => 'fx/sell_record/add', 'title' => '新增订单', 'is_pop' => false, 'pop_size' => '500,400'),
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
$keyword_type['goods_name'] = '商品名称';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['fenxiao_name'] = '分销商';

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
        'type' => 'submit'
    ),
);
if (load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/export_ext_list')) {
    $buttons[] = array(
        'label' => '导出',
        'id' => 'exprot_list',
    );
}
if($response['login_type'] != 2) {
    $buttons[] = array(
        'label' => '差异数据',
        'id' => 'exprot_detail',
    );
}
render_control('SearchForm', 'searchForm', array(
    'buttons' => $buttons,
    'show_row' => 2,
    'fields' => array(
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
            //'data' => load_model('base/SaleChannelModel')->get_select()
            'data' => load_model('base/SaleChannelModel')->get_my_select()
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
            'label' => '卖家旗帜',
            'type' => 'select_multi',
            'id' => 'seller_flag',
            'data' => load_model('util/FormSelectSourceModel')->get_seller_flag(),
        ),
        array(
            'label' => '订单性质',
            'type' => 'select_multi',
            'id' => 'sell_record_attr',
            'data' => load_model('FormSelectSourceModel')->order_nature(),
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
            'title' => '支持以逗号分隔的模糊查询'
        ),
        array(
            'label' => array('id' => 'is_buyer_remark', 'type' => 'select', 'data' => $is_buyer_remark),
            'type' => 'input',
            'id' => 'buyer_remark',
            'title' => '支持模糊查询'
        ),
        array(
            'label' => array('id' => 'is_seller_remark', 'type' => 'select', 'data' => $is_seller_remark),
            'type' => 'input',
            'id' => 'seller_remark',
            'title' => '支持模糊查询'
        ),
        array(
            'label' => '下单时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'record_time_start'),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'record_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '支付时间',
            'type' => 'group',
            'field' => 'daterange2',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'pay_time_start'),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'pay_time_end', 'remark' => ''),
            )
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
    )
));
?>

<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '全部', 'active' => false, 'id' => 'tabs_all'),
        array('title' => '待付款', 'active' => false, 'id' => 'tabs_pay'),
        array('title' => '待结算', 'active' => true, 'id' => 'tabs_settlement'),
        array('title' => '待确认', 'active' => false, 'id' => 'tabs_confirm_fx'),
        array('title' => '待供应商发货', 'active' => false, 'id' => 'tabs_send_fx'),
    ),
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<div id="TabPage1Contents">
    <div>
        <ul  class="toolbar frontool"  id="ToolBar1">
            <li class="li_btns"><button class="button button-primary btn_opt_intercept hide">批量订单拦截</button></li>
            <div class="front_close">&lt;</div>
        </ul>
        <script>
            $(function() {
                var default_opts = ['opt_intercept'];
                for (var i in default_opts) {
                    var f = default_opts[i];
                    btn_init_opt("ToolBar1", f);
                }
//                var custom_opts = $.parseJSON('');
//                for (var j in custom_opts) {
//                    var g = custom_opts[j];
//                    $("#ToolBar1 .btn_" + g['id']).click(eval(g['custom']));
//                }
            });
        </script>

    </div>
    <div>
        <ul  class="toolbar frontool"  id="ToolBar2">
            <li class="li_btns"><button class="button button-primary btn_opt_pay ">批量付款</button></li>
            <div class="front_close">&lt;</div>
        </ul>
        <script>
            $(function() {
                var default_opts = ['opt_pay'];
                for (var i in default_opts) {
                    var f = default_opts[i];
                    btn_init_opt("ToolBar2", f);
                }
//                var custom_opts = $.parseJSON('');
//                for (var j in custom_opts) {
//                    var g = custom_opts[j];
//                    $("#ToolBar2 .btn_" + g['id']).click(eval(g['custom']));
//                }
            });
        </script>

    </div>
    <div>
        <ul  class="toolbar frontool"  id="ToolBar3">
            <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/opt_fx_settlement')) { ?>
                <li class="li_btns"><button class="button button-primary btn_opt_settlement">批量结算</button></li>
            <?php } ?>
                <li class="li_btns"><button class="button button-primary btn_opt_edit_order_remark">批量备注</button></li>
            <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_edit_store_code')) { ?>
                <li class="li_btns"><button class="button button-primary btn_opt_edit_store_code ">批量修改发货仓库</button></li>
            <?php } ?>
          
            <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_edit_express_code')) { ?>
                <li class="li_btns"><button class="button button-primary btn_opt_edit_express_code ">批量修改配送方式</button></li>
            <?php } ?>
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
            })
        </script>
        <script>
            $(function() {
                var default_opts = ['opt_settlement'];
                for (var i in default_opts) {
                    var f = default_opts[i];
                    btn_init_opt("ToolBar3", f);
                }
                var custom_opts = $.parseJSON('[{"id":"opt_edit_store_code","custom":"btn_init_edit_store_code"},{"id":"opt_edit_express_code","custom":"btn_init_edit_express_code"},{"id":"opt_edit_order_remark","custom":"btn_init_edit_order_remark"}]');
                for (var j in custom_opts) {
                    var g = custom_opts[j];
                    $("#ToolBar3 .btn_" + g['id']).click(eval(g['custom']));
                }
            });
        </script>
    </div>
    <div>
        <ul  class="toolbar frontool"  id="ToolBar4">
            <?php /*if (load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_intercept_list')) { */?><!--
            <li class="li_btns"><button class="button button-primary _sys_batch_task_btn" task_info="{act:'app_act=oms/sell_record/opt&type=opt_intercept',obj_name:'订单',ids_params_name:'sell_record_code'}">批量订单拦截</button></li>
            <div class="front_close">&lt;</div>
            --><?php /*}
            */?>
            <li class="li_btns"><button class="button button-primary btn_opt_unsettlement ">批量取消结算</button></li>
            <li class="li_btns"><button class="button button-primary btn_opt_edit_express_code ">批量修改配送方式</button></li>
        </ul>
        <script>
            $(function() {
                var default_opts = ['opt_unsettlement'];
                for (var i in default_opts) {
                    var f = default_opts[i];
                    btn_init_opt("ToolBar4", f);
                }
                var custom_opts = $.parseJSON('[{"id":"opt_edit_express_code","custom":"btn_init_edit_express_code"}]');
                for (var j in custom_opts) {
                    var g = custom_opts[j];
                    $("#ToolBar4 .btn_" + g['id']).click(eval(g['custom']));
                }
            });
        </script>
    </div>
</div>
<style>
    #tool2{ height:30px;}
    #tool2 input{ vertical-align:middle;}
    #tool2 label{ vertical-align:middle; margin-right:5px;}
</style>
<ul id="tool2" class="toolbar" style="margin-top: 10px;">

    <li style="float:right;margin-right: 30px;">	
        <label>排序类型：</label>
        <select id="sort" name="sort" onchange="sort()">
            <option value="" >请选择</option>
            <option value="paid_money_desc">已付款金额降序</option>
        </select>
        <!--<button type="button" class="button button-small" id="sort_btn" onclick = "sort()">排序</button>-->
        <img src="assets/images/tip.png" alt="123" width="25" height="25" title ="排序所有页签"/>

    </li>
</ul>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单图标',
                'field' => 'status_text',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单号',
                'field' => 'sell_record_code',
                'width' => '100',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({sell_record_code})">{sell_record_code}</a>',
                ),
                'sortable' => true
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
                'title' => '店铺',
                'field' => 'shop_name',
                'width' => '100',
                'align' => '',
                'sortable' => true
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
                'sortable' => true
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
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分销结算运费',
                'field' => 'fx_express_money',
                'width' => '100',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单状态',
                'field' => 'status',
                'width' => '155',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家昵称',
                'field' => 'buyer_name',
                'width' => '100',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收货人',
                'field' => 'receiver_name',
                'width' => '100',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '手机',
                'field' => 'receiver_mobile',
                'width' => '120',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收货地址',
                'field' => 'receiver_address',
                'width' => '200',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '配送方式',
                'field' => 'express_name',
                'width' => '80',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '快递单号',
                'field' => 'express_no',
                'width' => '100',
                'align' => '',
                'editor' => "{xtype : 'text'}",
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商家留言',
                'field' => 'seller_remark',
                'width' => '100',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家留言',
                'field' => 'buyer_remark',
                'width' => '100',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '下单时间',
                'field' => 'record_time',
                'width' => '100',
                'align' => '',
                'sortable' => true
            ),
               array(
                'type' => 'text',
                'show' => 1,
                'title' => '应付款',
                'field' => 'payable_money',
                'width' => '100',
                'align' => '',
                'sortable' => true
            ),
             array(
                'type' => 'text',
                'show' => 1,
                'title' => '已付款',
                'field' => 'paid_money',
                'width' => '100',
                'align' => '',
                'sortable' => true
            ),
        )
    ),
    'dataset' => 'oms/SellRecordModel::ex_list_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'sell_record_id',
    'customFieldTable' => 'fx/sell_record_ex_list',
    'export' => array('id' => 'exprot_list', 'conf' => 'ex_record_list', 'name' => '订单列表', 'export_type' => 'file'),
    'CellEditing' => true,
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
            array('title' => '赠品', 'type' => 'text', 'width' => '100', 'field' => 'is_gift', 'format_js' => array('type' => 'map', 'value' => array('0' => '否', '1' => '是'))),
            array('title' => '计划发货时间', 'type' => 'text', 'width' => '100', 'field' => 'plan_send_time'),
        ),
        'page_size' => 10,
        'url' => get_app_url('fx/sell_record/get_detail_list_by_sell_record_code&app_fmt=json'),
        'params' => 'sell_record_code',
        'ExpandCascadeDetail' => array(
            'detail_url' => get_app_url('oms/sell_record/get_ex_list_cascade_data'), //查询展开详情的方法
            'detail_param' => 'sell_record_code', //查询展开详情的使用的参数
        ),
    ),
    'CheckSelection' => true,
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
    //'init' => 'nodata',
));
?>
<div id="msg_id"></div>
<script type="text/javascript">
    function sort() {
        tableStore.load();
    }
    function view(sell_record_code) {
        var url = '?app_act=fx/sell_record/view&sell_record_code=' + sell_record_code + '&record_type=fx';
        openPage(window.btoa(url), url, '订单详情');
    }
    var url = '<?php echo get_app_url('base/store/get_area'); ?>';
    $(function() {
        
        $('#exprot_detail').click(function() {
//        var url = '?app_act=ctl/index/do_index&app_ctl=DataTable/do_get_data';
        var url = '?app_act=sys/export_csv/export_show'; //暂时不是框架级别
        var  params = tableStore.get('params');
        var ctl_dataset =  params.ctl_dataset;
        params.ctl_dataset = "oms/SellRecordModel::fx_diff_record_data";
        params.ctl_type = 'export';
        params.ctl_export_conf = 'fx_diff_record_data';
        params.ctl_export_name =  '分销订单';
        <?php echo   create_export_token_js('oms/SellRecordModel::fx_diff_record_data');?>
//        params.ctl_export_token =  'c9453c5e7e15b9d966e5b8febb858faf';
//        params.ctl_export_time =  '1495417944';
        
        var obj = searchFormForm.serializeToObject();
          for(var key in obj){
                 params[key] =  obj[key];
	  } 
          for(var key in params){
                url +="&"+key+"="+params[key];
	  }
        params.ctl_type = 'view';
        params.ctl_dataset = ctl_dataset;
          window.open(url); 
       // window.location.href = url;

        });
        
        //TAB选项卡
        $("#TabPage1 a").click(function() {
            tableStore.load();
        });

        tableStore.on('beforeload', function(e) {
            e.params.ex_list_tab = $("#TabPage1").find(".active").find("a").attr("id");
            e.params.contain_express_money = $("#contain_express_money").attr('checked') == 'checked' ? '1' : '0';
//            e.params.is_normal = $("input[name='is_normal']:checked").val();
            var sort_e = $("#sort  option:selected");
            //前端排序
            var sort_params={};
            if (sort_e.length > 0) {
                e.params.is_sort = $("#sort  option:selected").val();
                switch (e.params.is_sort) {
                    case 'paid_money_desc':
                        sort_params.field = 'paid_money';
                        sort_params.direction = 'DESC';
                        break;
                }
                tableStore.set('sortInfo',sort_params);
            }
            e.params.is_sort = $("#sort  option:selected").val();
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
        //tableStore.load();
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
        if (obj.text() == '批量修改发货仓库' || obj.text() == '批量修改仓库留言' || obj.text() == '批量修改配送方式' || obj.text() == '批量挂起' || obj.text() == '批量备注') {
            func.apply(null, [ids]);
        } else {
            BUI.Message.Show({
                title: '批量操作',
                msg: '是否执行订单' + obj.text() + '?',
                icon: 'question',
                buttons: [
                    {
                        text: '是',
                        elCls: 'button button-primary',
                        handler: function() {
                            this.close();
                            func.apply(null, [ids]);
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
                if (id == 'opt_confirm') {
                    var sell_record_code_list = get_sell_record_code_list(ids);
                    var params = {};
                    params.sell_record_code_list = sell_record_code_list;
                    $.post("?app_act=oms/sell_record/opt_confirm", params, function(data) {
                        if (data.status == 1) {
                            BUI.Message.Alert(data.message, 'info')
                            //刷新
                            tableStore.load()
                        } else {
                            BUI.Message.Alert(data.message, 'error')
                        }
                    }, "json");
                } else if(id == 'opt_settlement'){
                    $.post('?app_act=fx/sell_record/have_out_goods',{ids:ids},function(data){
                        if(data.status == 2){
                            BUI.Message.Confirm(data.message,function(){
                                do_settlement(ids,id,1)
                            });
                        }else{
                            do_settlement(ids,id,0);
                        }
                    },'json')
                } else if(id=='opt_unsettlement'){
                    do_unsettlement(ids,id,0);
                }else {
                    var params = {"sell_record_code_list": ids, "type": id, "batch": "批量操作"};
                    $.post("?app_act=oms/sell_record/opt_batch", params, function(data) {
                        if (data.status == 1) {
                            BUI.Message.Alert(data.message, 'info')
                            //刷新
                            tableStore.load()
                        } else {
                            BUI.Message.Alert(data.message, 'error')
                        }
                    }, "json");
                }
            })


        });
    }
    function do_settlement(ids,id,allow_out=0){
        task_do_settlement('app_act=oms/sell_record/opt&type=opt_settlement&batch=自动确认&action_name=批量结算&allow_out='+allow_out,'批量结算','订单', 'sell_record_code');
    }
    function do_unsettlement(ids,id,allow_out=0){
        task_do_settlement('app_act=oms/sell_record/opt&type=opt_unsettlement&batch=自动确认&action_name=批量取消结算&allow_out='+allow_out,'批量取消结算','订单', 'sell_record_code');
    }
    function task_do_settlement(act, task_name, obj_name, ids_params_name, submit_all_ids_flag, process_batch_task_ids, task_name_tips, btn_id, task){
        var ids = new Array();
        var sell_record_codes = new Array();//订单号
        var rows = tableGrid.getSelection();//读取选中列表
        if (rows.length == 0) {
            BUI.Message.Alert("请选择" + obj_name, 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.sell_record_code);
        }
        $("body").data("process_batch_task_ids", ids.join(','));
        $("#process_batch_task_tips").remove();
        show_process_batch_task_plan(task_name, '<div id="process_batch_task_tips" style="height:300px;overflow-y:scroll;"><div>处理中，请稍等......</div></div>');
        //console.log('==11');
        process_batch_task_act(act, ids_params_name, submit_all_ids_flag, btn_id, task, sell_record_codes);
    }
    function get_sell_record_code_list(ids) {
        var sell_record_code_list = '';
        for (var key in ids) {
            sell_record_code_list += ids[key] + ",";
        }
        return  sell_record_code_list.substring(0, sell_record_code_list.length - 1);
    }
    //批量修改配送方式
    function btn_init_edit_express_code() {
        get_checked($(this), function (ids) {
            $.ajax({type: 'POST',
                dataType: 'json',
                url: '<?php echo get_app_url('oms/sell_record/fx_account'); ?>',
                data: {'sell_record_code': ids},
                success: function(data) {
                    if (data.status == -1) {
                        BUI.Message.Confirm(data.message,function(){
                            new ESUI.PopWindow("?app_act=oms/sell_record/edit_express_code&sell_record_code_list=" + ids.toString(), {
                                title: "批量修改配送方式",
                                width: 500,
                                height: 250,
                                onBeforeClosed: function () {
                                },
                                onClosed: function () {
                                    tableStore.load();
                                }
                            }).show();
                        },'question');
                    }else{
                        new ESUI.PopWindow("?app_act=oms/sell_record/edit_express_code&sell_record_code_list=" + ids.toString(), {
                            title: "批量修改配送方式",
                            width: 500,
                            height: 250,
                            onBeforeClosed: function () {
                            },
                            onClosed: function () {
                                tableStore.load();
                            }
                        }).show();
                    }
                }
            });
        });
    }

    //批量修改发货仓库
    function btn_init_edit_store_code() {
        get_checked($(this), function(ids) {
            new ESUI.PopWindow("?app_act=oms/sell_record/edit_store_code&sell_record_code_list=" + ids.toString(), {
                title: "批量修改发货仓库",
                width: 500,
                height: 250,
                onBeforeClosed: function() {
                },
                onClosed: function() {
                    //刷新数据
                    tableStore.load()
                }
            }).show()
        })
    }

    //批量修改仓库留言
    function btn_init_edit_store_remark() {
        get_checked($(this), function(ids) {
            new ESUI.PopWindow("?app_act=oms/sell_record/edit_store_remark&sell_record_code_list=" + ids.toString(), {
                title: "批量修改仓库留言",
                width: 500,
                height: 250,
                onBeforeClosed: function() {
                },
                onClosed: function() {
                    //刷新数据
                    tableStore.load()
                }
            }).show()
        })
    }
    //批量挂起
    function btn_init_pending() {
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
    }

    //批量备注
    function btn_init_edit_order_remark() {
        get_checked($(this), function(ids) {
            new ESUI.PopWindow("?app_act=oms/sell_record/edit_order_remark&sell_record_code_list=" + ids.toString(), {
                title: "批量备注",
                width: 500,
                height: 250,
                onBeforeClosed: function() {
                },
                onClosed: function() {
                    //刷新数据
                    tableStore.load();
                }
            }).show();
        });
    }

    //自动匹配物流单号
    function btn_init_edit_express_no() {
        $(".btn_edit_express_no").click(function() {
            get_checked($(this), function(ids) {
                new ESUI.PopWindow("?app_act=oms/sell_record/edit_express_no&sell_record_id_list=" + ids.toString(), {
                    title: "自动匹配物流单号",
                    width: 800,
                    height: 600,
                    onBeforeClosed: function() {
                    },
                    onClosed: function() {
                        //刷新数据
                        tableStore.load()
                    }
                }).show()
            })
        })
    }

    //打印发货单
    function btn_init_opt_print_send() {
        $(".btn_opt_print_send").click(function() {
            get_checked($(this), function(ids) {
                //TODO:打印
                var ids = ids.toString();
                var url = '?app_act=oms/sell_record/mark_sell_record_print';
                var params = {};
                params.record_ids = ids;
                $.post(url, params, function(data) {
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
        $(".btn_opt_print_express").click(function() {
            get_checked($(this), function(ids) {
                //TODO:打印
                var ids = ids.toString();
                print_express.print_express(ids);
            })
        })
    }

    function showDetail(index, row) {
        openPage('<?php echo base64_encode('?app_act=fx/sell_record/view&sell_record_code') ?>' + row.sell_record_id, '?app_act=fx/sell_record/view&ref=ex&sell_record_code=' + row.sell_record_code, '分销订单详情');
    }

    tableCellEditing.on('accept', function(record) {
        var params = {
            "sell_record_code": record.record.sell_record_code,
            "express_code": record.record.express_code,
            "express_no": record.record.express_no.trim(),
        }
        var str = params.express_no;
        if (str != '') {
            var reg = new RegExp(/^[0-9A-Za-z]+$/);
            if (!reg.test(str)) {
                BUI.Message.Alert("快递单号必须为数字或者字母", 'error');
                return false;
            }
        }
        $.post("?app_act=oms/sell_record/edit_express", params, function(data) {
            if (data.status != 1) {
                BUI.Message.Alert(data.message, 'error');
            }
        }, "json")
    });

    function a_key_confirm() {
        $.get("?app_act=oms/sell_record/a_key_confirm&app_fmt=json", function(data) {
            if (data.status < 1) {
                BUI.Message.Alert(data.message, 'error');
            } else {
                get_log(data.data, 0);
            }
            tableStore.load();
        }, "json")
    }

    function get_log(task_id, log_file_offset) {
        var request_data = {
            'task_id': task_id,
            'log_file_offset': log_file_offset,
            'timestamp': new Date().getTime()
        }
        //才页面功能已经实现，也可以跟进自己页面进行自行增加
        var ajax_url = '?app_act=sys/sys_schedule/get_task_log&app_fmt=json';
        $.post(ajax_url, request_data, function(data) {
            var result = eval('(' + data + ')')
            if (result == '') {
                return;
            }
            // msg_id 为存储信息的页面DOM ID
            $('#msg_id').prepend(result.msg);
            if (result.code == 0) {
                //2秒获取1次信息
                setTimeout(function() {
                    get_log(result.task_id, result.log_file_offset);
                }, 2000);
            }
        });
    }
</script>
<?php echo load_js('task.js', true); ?>
<!-- 打印快递单公共文件 -->

<?php include_once (get_tpl_path('process_batch_task')); ?>


