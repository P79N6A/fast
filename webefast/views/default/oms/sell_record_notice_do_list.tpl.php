<script type="text/javascript" src="../../webpub/js/jquery.cookie.js"></script>
<?php echo load_js('comm_util.js') ?>
<style>
    #save_conditions{
        background: #1695ca no-repeat scroll -75px 5px;
        border: medium none;
        color: #fff;
        font-size: 18px;
        height: 28px;
        width: 92px;
    }
    #de{
        color: red
    }
    .bui-dialog .bui-stdmod-footer {
        text-align:center;
    }

    #sku_set_all{ margin-top:8px; font-size:12px; border-collapse:inherit; color:#666;}
    #sku_set_all td.set_sku_btn{
        border:1px solid #d5d5d5;
        padding:0 15px;
        text-align:center;
        cursor:pointer;
        height:24px;
        border-radius:3px;
        position:relative;

    }


    #waves_name_select {
        margin-left:3px;
        width: 140px;
        height: 28px;
    }
    #pay_time_start{width:100px;}
    #pay_time_end{width:100px;}
    #record_time_start{width:100px;}
    #record_time_end{width:100px;}
    #is_notice_time_start{width:100px;}
    #is_notice_time_end{width:100px;}
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
</style>
<?php
render_control('PageHead', 'head1', array('title' => '波次策略',
    'links' => array(
        //array('url'=>'oms/sell_record/add', 'title'=>'新增订单', 'is_pop'=>false, 'pop_size'=>'500,400'),
    ),
    'ref_table' => 'table'
));
?>
<?php
$keyword_type = array();
$keyword_type['barcode'] = '商品条形码';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['goods_name'] = '商品名称';
$keyword_type = array_from_dict($keyword_type);
$time_type = array();
$time_type['record_time'] = '下单时间';
$time_type['pay_time'] = '付款时间';
$time_type['notice_time'] = '通知配货时间';
$time_type['plan_time'] = '计划发货时间';
$time_type = array_from_dict($time_type);
render_control('SearchForm', 'searchForm', array(
    'buttons' =>array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type'=>'submit'
        ),
        array(
            'label' => '保存条件',
            'id' => 'save_conditions',
        ),
    ) ,
    'show_row' => 3,
    'fields' => array(
        array(
            'label' => '仓库',
            'type' => 'select',
            'id' => 'store_code',
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
            'data' => ds_get_select('express'),
        ),
        array(
            'label' => '订单性质',
            'type' => 'select_multi',
            'id' => 'record_nature',
            'data' => ds_get_select_by_field('record_nature',0),
        ),
//        array(
//        	'label' => '加急订单',
//        	'type' => 'select',
//        	'id' => 'is_rush',
//        	'data' => ds_get_select_by_field('is_rush'),
//        ),
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '待拣货商品数',
            'type' => 'group',
            'field' => 'goods_num',
            'child' => array(
                array('title' => 'start','type' => 'input','field' => 'goods_num_start','class'=>'input-small'),
                array('pre_title' => '~','type' => 'input','field' => 'goods_num_end','class'=>'input-small'),
            )
        ),
        array(
            'label' => '商品数量',
            'type' => 'group',
            'field' => 'detail_goods_num',
            'child' => array(
                array('title' => 'start','type' => 'input','field' => 'detail_goods_num_start','class'=>'input-small'),
                array('pre_title' => '~','type' => 'input','field' => 'detail_goods_num_end','class'=>'input-small'),
            )
        ),
        array(
            'label' => '订单价格',
            'type' => 'group',
            'field' => 'money',
            'child' => array(
                array('title' => 'start','type' => 'input','field' => 'money_start','class'=>'input-small'),
                array('pre_title' => '~','type' => 'input','field' => 'money_end','class'=>'input-small','remark' => '<input type="checkbox" id="contain_express_money">含运费'),
            )
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
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'sale_channel_code',
            'data' => load_model('base/SaleChannelModel')->get_select()
        ),
        array(
            'label' => '货到付款订单',
            'type' => 'select',
            'id' => 'is_cod',
            'data' => ds_get_select_by_field('is_cod'),
        ),
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
            'label' => '开票订单',
            'type' => 'select',
            'id' => 'is_nvoice',
            'data' => ds_get_select_by_field('is_nvoice'),
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
            'label' => '省份（多选）',
            'type' => 'select_multi',
            'id' => 'province_multi',
            'data' => load_model('base/TaobaoAreaModel')->get_area_select(1),
        ),
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
            'label' => '订单标签',
            'type' => 'select_multi',
            'id' => 'order_tag',
            'data' => ds_get_select('order_label',4),
        ),
        array(
            'label' => '订单理论重量',
            'type' => 'group',
            'field' => 'order_weight',
            'child' => array(
                array('title' => 'start','type' => 'input','field' => 'weight_start','class'=>'input-small'),
                array('pre_title' => '~','type' => 'input','field' => 'weight_end','class'=>'input-small'),
            )
        ),
// 	    array(
//     		'label' => '分销订单',
//     		'type' => 'select',
//     		'id' => 'is_fenxiao',
//     		'data' => ds_get_select_by_field('is_fenxiao'),
// 	    ),


//         array(
//         	'label' => '聚划算订单',
//         	'type' => 'select',
//         	'id' => 'is_jhs',
//         	'data' => ds_get_select_by_field('is_jhs'),
//         ),

//        array(
//        	'label' => '通知配货时间',
//        	'type' => 'group',
//        	'field' => 'daterange1',
//        	'child' => array(
//        		array('title' => 'start', 'type' => 'time', 'field' => 'is_notice_time_start'),
//        		array('pre_title' => '~', 'type' => 'time', 'field' => 'is_notice_time_end', 'remark' => ''),
//        	)
//        ),
//        array(
//        	'label' => '计划发货时间',
//        	'type' => 'group',
//        	'field' => 'daterange2',
//        	'child' => array(
//        		array('title' => 'start', 'type' => 'date', 'field' => 'plan_time_start'),
//        		array('pre_title' => '~', 'type' => 'date', 'field' => 'plan_time_end', 'remark' => ''),
//        	)
//        ),
//        array(
//        	'label' => '下单时间',
//        	'type' => 'group',
//        	'field' => 'daterange3',
//        	'child' => array(
//        		array('title' => 'start', 'type' => 'time', 'field' => 'record_time_start'),
//        		array('pre_title' => '~', 'type' => 'time', 'field' => 'record_time_end', 'remark' => ''),
//        	)
//        ),
//        array(
//        	'label' => '付款时间',
//        	'type' => 'group',
//        	'field' => 'daterange4',
//        	'child' => array(
//        		array('title' => 'start', 'type' => 'time', 'field' => 'pay_time_start'),
//        		array('pre_title' => '~', 'type' => 'time', 'field' => 'pay_time_end', 'remark' => ''),
//        	)
//        ),

    )
));
?>

<div class="row" >
    <button type="button" class="button" onClick="do_page('multi');" >多SKU分析</button>
    <button type="button"  style="color:#1695ca"  class="button" >单SKU分析</button>
</div>
<div class="row" >
    &nbsp;
</div>
<!--
<table id="sku_set_all" style="margin-bottom: 10px;">
    <tr>
        <td onclick = "do_page('single')" style="color: #1695ca"  id = "sku_num1" class="set_sku_btn active">单SKU分析</td>
        <td onclick = "do_page('multi')" style="color: #1695ca"  id = "sku_num2" class="set_sku_btn ">多SKU分析</td>
        <td onclick = "set_sku_num(this, 1)" style="color: #1695ca"  id = "sku_num1" class="set_sku_btn active">单SKU分析</td>

        <td onclick = "set_sku_num(this,2)"  id = "sku_num2" class="set_sku_btn">双SKU分析</td>
    </tr>
</table>-->
<input id="sku_num" type="hidden" name="sku_num"  value="1"/>

<ul class="toolbar frontool">

    <li class="li_btns">
        <button class="button button-primary btn-opt-store_in single" onclick="create_wave(1)">单个生成波次</button>
        <button class="button button-primary btn-opt-store_in group" onclick="create_wave(2)">合并生成波次</button>
        每波次订单数<input type="text" id="order_num" name="order_num" value="" style="width:40px;" />
        <button class="button button-primary btn-opt-edit_express_code" >修改配送方式</button>
    </li>



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
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品',
                'field' => 'goods_info',
                'width' => '200',
                'format_js' => array('type' => 'html', 'value' => '<div>{goods_info}</div>'),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '库位',
                'field' => 'goods_shelf',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '待拣货商品数',
                'field' => 'goods_num',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单数',
                'field' => 'order_num',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '快递分配',
                'field' => 'express_info',
                'width' => '150',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'oms/SellRecordNoticeModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'sku',
    'CheckSelection' => true,
    'init'=>'nodata',
));
?>
<script>
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
            var title='请选择库位';
        }else if(_type=='spec1'){
            var url = '?app_act=oms/sell_record/select_spec1';
            var title='<?php echo '请选择'.$response['goods_spec1_rename']?>';
        }else if(_type=='spec2'){
            var url = '?app_act=oms/sell_record/select_spec2';
            var title='<?php echo '请选择'.$response['goods_spec2_rename']?>';
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

    var dialog = '';
    var create_wave_num = 0;
    var area_url = '<?php echo get_app_url('base/store/get_area'); ?>';
    $(function() {
        if($.cookie("combine_num") == null){
            $('#order_num').val(50);
        }else{
            $('#order_num').val($.cookie("combine_num"));
        }
        $('#searchForm').append($('#sku_num'));
        tableStore.on('beforeload', function(e) {

//            var sort_e = $("#sort").find(".active");
//            if (sort_e.length > 0) {
//                e.params.is_sort = $("#sort").find(".active").attr("id");
//            }
            e.params.shelf_code = $("#shelf_code").val();
            e.params.spec1= $("#spec1").val();
            e.params.spec2= $("#spec2").val();
            tableStore.set("params", e.params);
        });
        BUI.use('bui/tooltip',function (Tooltip) {
            var t = new Tooltip.Tip({
                trigger : '.single',
                alignType : 'top-left',
                offset : 10,
                title : '按照勾选的商品及订单数生成波次。当每个商品的订单数超过每波次订单数时，拆分生成波次单。例如：A有10笔订单，B商品有30笔订单，每波次订单数为11，A商品生成一个波次单，B商品生成三个波次单（11-11-8）。',
                elCls : 'tips tips-no-icon',
                titleTpl : '<p style="width:230px;height:150px;color:red;font:bold 15px 微软雅黑">{title}</p>'
            });
            t.render();

            var t = new Tooltip.Tip({
                trigger : '.group',
                alignType : 'top-left',
                offset : 10,
                title : '按照勾选的商品的订单数之和生成波次。当合并的订单数超过每波次订单数时，拆分生成波次单。例如：A有10笔订单，B商品有30笔订单，每波次订单数为11，将A、B商品订单数相加，合并生成四个波次单（11-11-11-7）。',
                elCls : 'tips tips-no-icon',
                titleTpl : '<p style="width:230px;height:150px;color:red;font:bold 15px 微软雅黑">{title}</p>'
            });
            t.render();
        });

        areaChange(1, 0, area_url);

        $('#province').change(function () {
            var parent_id = $(this).val();
            areaChange(parent_id, 1, area_url);
        });
        $('#city').change(function () {
            var parent_id = $(this).val();
            areaChange(parent_id, 2, area_url);
        });
        $('#district').change(function () {
            var parent_id = $(this).val();
            areaChange(parent_id, 3, area_url);
        });
    });
    $('#order_num').change(function(){
        var combine_num = $('#order_num').val();
        $.cookie('combine_num',combine_num,{expires: 30})
    })
    function create_wave(type) {
        create_wave_num = 0;
        var order_num = $('#order_num').val();
        var reg = new RegExp(/^\+?[1-9][0-9]*$/);
        if (!reg.test(order_num) || order_num > 500) {
            alert("请输入大于0正数小于500正数!");
            return;
        }
        if(type == 1){
            var url = '?app_act=oms/sell_record_notice/create_wave&app_fmt=json&order_num=' + order_num; //暂时不是框架级别
        }else{
            var url = '?app_act=oms/sell_record_notice/create_wave_combine&app_fmt=json&order_num=' + order_num;
        }
        params = tableStore.get('params');
        for (var key in params) {
            if (key != 'ctl_conf' && key != 'ctl_params' && key != 'ctl_dataset') {
                url += "&" + key + "=" + params[key];
            }
        }
        var select_data = tableGrid.getSelection();
        if (select_data.length < 1) {
            alert("请选择商品!");
            return;
        }
        var i = 0;
        var sku_data = {};
        for (var k in select_data) {
            sku_data[select_data[i]['sku']] = select_data[i]['express_code_all'];
            i++;
        }
        var params = {sku_data: sku_data,is_check: 0};
        create_msg();
        create_wave_post(url, params);

    }
    $(".btn-opt-edit_express_code").click(function () {
        get_checked($(this), function (ids) {
            var url = "?app_act=oms/sell_record_notice/edit_express_code&sku_list=" + ids + "&store_code=" + $("#store_code").val();
            params = tableStore.get('params');
            for (var key in params) {
                if (key != 'ctl_conf' && key != 'ctl_params' && key != 'ctl_dataset') {
                    url += "&" + key + "=" + params[key];
                }
            }
            new ESUI.PopWindow(url, {
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
    function get_checked(obj, func) {

        var ids = []
        var selecteds = tableGrid.getSelection();
        for (var i in selecteds) {
            ids.push(selecteds[i].sku)
        }

        if (ids.length == 0) {
            BUI.Message.Alert("请选择订单", 'error');
            return
        }
        func.apply(null, [ids])
    }
    function create_wave_post(url, params){
        $.post(url, params, function(ret) {
            if (ret['status'] == -1) {
                create_wave_num =create_wave_num + ret.data;
                // BUI.Message.Alert("已经生成波次单" + ret.data + "单，"+ret.message, 'error');
                over_msg("已经生成波次单" + ret.data + "单，异常终止："+ret.message,1);
            } else if(ret['status'] == -2){
                dialog.close();
                BUI.Message.Show({
                    title: '生成波次',
                    msg: ret.message,
                    icon: 'question',
                    buttons: [
                        {
                            text: '是',
                            elCls: 'button button-primary',
                            handler: function () {
//                                var params = {sku_data: sku_data,is_check: 0};
                                var _self = this;
                                params.is_check = 0;
                                create_msg();
                                create_wave_post(url, params);
                                _self.close();
//                                $.post(url, params, function (data) {
//                                    if (data.status == 1) {
//                                        BUI.Message.Alert(data.message, 'info');
//                                        //刷新
//                                        tableStore.load();
//                                        _self.close();
//                                    } else {
//                                        _self.close();
//                                        BUI.Message.Alert(data.message, 'error')
//                                    }
//                                }, "json");
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
            }else if(ret['status']==2){
                // BUI.Message.Alert("生成波次单" + ret.data + "单，", 'success');
                create_wave_num =create_wave_num + ret.data;
                $('.create_wave_num').text(create_wave_num);
                create_wave_post(url, params);

            }else if(ret['status']==1){
                create_wave_num =create_wave_num + ret.data;
                //BUI.Message.Alert("生成波次单" +create_wave_num+ "单，", 'success');
                over_msg("生成完成，生成波次单" +create_wave_num+ "单.",1);
                create_wave_num  = 0;
                $('#btn-search').click();
            }
        }, "json");

    }
    function close_dialog(){
        dialog.close();
    }
    function over_msg(msg,type){
        if(type===1){
            msg+='<p style="height: 80px; width: 160px; padding-left:110px;padding-top:35px;"><button  onclick="close_dialog()" class="button button-success" type="button">关闭</button></p>';
        }
        $('.create_msg').html(msg);
    }

    function create_msg(){
        var msg = '<p>正在生成波次单中...</p><p>已经生成  <span class="create_wave_num">0</span> 单 </p>';
        if(dialog===''){
            BUI.use('bui/overlay',function(Overlay){
                dialog = new Overlay.Dialog({
                    title:'波次单生成提示',
                    width:300,
                    height:200,
                    buttons:[],
                    bodyContent:'<div class="create_msg" style="color:red">'+msg+'</div>'
                });
                dialog.show();
            });
        }else{
            dialog.show();
            over_msg(msg,0);
        }

    }
    /*
        function set_sku_num(_this, num) {
            var s_num = $('#sku_num').val();
            if (s_num != num) {
                $(".set_sku_btn").css({"color": "#666"});
                $(".set_sku_btn").removeClass("active");
                $(_this).css({"color": "#1695ca"});
                $(_this).addClass("active");
                $('#sku_num').val(num);
                tableStore.load();
            }
        }*/
    function do_page(type) {
        if (type == 'single'){
            location.href = "?app_act=oms/sell_record_notice/do_list";
        } else {
            location.href = "?app_act=oms/sell_record_notice/multi_do_list";
        }

    }

</script>
<script type="text/javascript">
    $(document).ready(function(){
        get_waces_name();
    });
    function remove_one(name){
        BUI.Message.Confirm('确认删除?',function(){
            param="&wave_strategy_name="+name+"&type=0";
            $.post("?app_act=oms/sell_record_notice/delete_wave_strategy_name&app_fmt=json", param, function(data) {
                type = data.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    location.reload();
                }else{
                    BUI.Message.Alert(data.message, type);
                }
            }, "json");
            $("#waves_name_select").find("option:selected").remove();
        },'question');
    }
    function get_waces_name(){
        $("#waves_name_select").remove();
        $('<span id="waves_name_select" style="width:90px" ><input type="hidden" id="hide" name="hide"></span>').insertAfter("#save_conditions");
        $.post("?app_act=oms/sell_record_notice/get_waves_strategy&app_fmt=json&type=0", '', function(data) {
            if (data) {
                var jsonArr = [];
                for(var i =0 ;i < data.length;i++){
                    jsonArr[i] = data[i]+'&nbsp;&nbsp;&nbsp;<a id="de" onclick="remove_one(\''+data[i]+'\')">删除</a>';
                }
                BUI.use('bui/select',function(Select){
                    select = new Select.Combox({
                        render:'#waves_name_select',
                        items:jsonArr,
                    });
                    select.render();
                    $(".bui-combox-input").attr("placeholder","请选择策略");
                    select.on('change', function(ev){
                        var str = ev.item.text.split('&nbsp;');
                        $('.bui-combox-input').val(str[0]);
                        var strategy_name = str[0];
                        if(strategy_name){
                            $.post("?app_act=oms/sell_record_notice/get_waves_params_by_name&name="+strategy_name+"&app_fmt=json&type=0", '', function(data) {
                                if (data) {
                                    $.each(data,function(i,value){
                                        if(i == 'shop_code') {
                                            shop_code_select.setSelectedValue(value)
                                        } else if (i == 'sale_channel_code'){
                                            sale_channel_code_select.setSelectedValue(value)
                                        } else if (i == 'express_code'){
                                            express_code_select.setSelectedValue(value)
                                        } else {
                                            $("#"+i).val(value);
                                        }
                                    });
                                } else {
                                    BUI.Message.Alert(data.message, 'error')
                                }
                            }, "json");
                        }
                    });
                });
            } else {
                BUI.Message.Alert(data.message, 'error');
            }
        }, "json");
    }

    $("#save_conditions").click(function(){
        $('#wave_strategy_name').remove();
        BUI.use('bui/overlay',function(Overlay){
            var dialog = new Overlay.Dialog({
                title:'保存波次策略条件',
                width:300,
                height:160,
                bodyContent:"<div class='control-group wave_type_name'>"+
                "<label class='control-label'>波次策略名称：</label>"+
                "<input class='input-normal control-text' name='wave_strategy_name' id='wave_strategy_name' value='' type='text'>"+
                "</div>",
                mask:false,
                buttons:[
                    {
                        text:'确定',
                        elCls : 'button button-primary',
                        handler : function(){
                            var param = '';
                            var obj = searchFormForm.serializeToObject();
                            for(var key in obj){
                                param=param+"&"+key+"="+obj[key];
                            }
                            var wave_strategy_name = $("#wave_strategy_name").val();
                            param=param+"&wave_strategy_name="+wave_strategy_name+"&type=0";
                            $.post("?app_act=oms/sell_record_notice/save_wave_strategy_name&app_fmt=json", param, function(data) {
                                if (data.status == 1) {
                                    parent.BUI.Message.Alert(data.message, 'info')
                                    setTimeout(function(){
                                        $('.bui-dialog .bui-ext-close-x').trigger('click');
                                        get_waces_name();

                                    },1500);
                                    //刷新
//                            location.reload();
                                } else if(data.status == 2){
                                    parent.BUI.Message.Confirm(data.message,function(){
                                        $.post("?app_act=oms/sell_record_notice/replace_name&app_fmt=json", param, function(data) {
                                            if (data.status == 1) {
                                                parent.BUI.Message.Alert(data.message, 'info')
                                                setTimeout(function(){
                                                    $('.bui-dialog .bui-ext-close-x').trigger('click');
                                                    get_waces_name();
                                                },1500);
                                            } else {
                                                parent.BUI.Message.Alert(data.message, 'error')
                                            }
                                        }, "json");

                                    },'question');
                                } else {
                                    parent.BUI.Message.Alert(data.message, 'error')
                                }
                            }, "json");

                        }
                    },
                    {
                        text:'取消',
                        elCls : 'button',
                        handler : function(){
                            this.close();
                            this.remove();

                        }
                    }
                ]
            });
            dialog.show();

        });
    })

</script>
