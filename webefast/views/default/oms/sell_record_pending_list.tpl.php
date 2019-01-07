<?php echo load_js('comm_util.js') ?>
<?php
render_control('PageHead', 'head1', array('title' => '挂起订单列表',
    
    'ref_table' => 'table'
));
?>

<?php
$keyword_type = array();
$keyword_type['sell_record_code'] = '订单号';
$keyword_type['deal_code_list'] = '交易号';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['buyer_remark'] = '买家留言';
$keyword_type['seller_remark'] = '商家留言';
$keyword_type = array_from_dict($keyword_type);
$buttons = array(
    array(
        'label' => '查询',
        'id' => 'btn-search',
        'type'=>'submit'
    ),
) ;
if (load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/export_pending_list')) {
    $buttons[] =  array(
        'label' => '导出',
        'id' => 'exprot_list',
    );
}
render_control('SearchForm', 'searchForm', array(
    'buttons' =>$buttons,
    'show_row'=>3,
    'fields' => array(
                array(
                    'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' =>$keyword_type),
                    'type' => 'input',
                    'title' => '',
                    'data' => $keyword_type,
                    'id' => 'keyword',	
        ),
         array(
            'label' => '挂起原因',
            'type' => 'select_multi',
            'id' => 'is_pending_code',
            'data' => ds_get_select('pending_label'),
        ),
        array(
            'label' => '挂起备注',
            'type' => 'input',
            'id' => 'is_pending_memo',
            'title'=>'支持模糊查询'
        ),
        
//        array(
//            'label' => '订单号',
//            'type' => 'input',
//            'id' => 'sell_record_code'
//        ),
//        array(
//            'label' => '交易号',
//            'type' => 'input',
//            'id' => 'deal_code_list'
//        ),
      array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
      array(
            'label' => '挂起时间',
            'type' => 'group',
            'field' => 'is_pending_time',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'is_pending_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'is_pending_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '解挂时间',
            'type' => 'group',
            'field' => 'daterange3',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'unpsending_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'unpsending_time_end', 'remark' => ''),
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
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),

        array(
       'label' => '订单标签',
            'type' => 'select_multi',
            'id' => 'order_tag',
            'data' => ds_get_select('order_label',4),
        ), 

//        array(
//            'label' => '商品编码',
//            'type' => 'input',
//            'id' => 'goods_code'
//        ),
//        array(
//            'label' => '商品条形码',
//            'type' => 'input',
//            'id' => 'barcode',
//            'title'=>'支持模糊查询'
//        ),
        array(
            'label' => '配送方式',
            'type' => 'select_multi',
            'id' => 'express_code',
            'data' => ds_get_select('express'),
        ),
//        array(
//            'label' => '买家留言',
//            'type' => 'input',
//            'id' => 'buyer_remark',
//            'title'=>'支持模糊查询'
//        ),
//        array(
//            'label' => '商家留言',
//            'type' => 'input',
//            'id' => 'seller_remark',
//            'title'=>'支持模糊查询'
//        ),
        array(
            'label' => '换货单',
            'type' => 'select',
            'id' => 'is_change_record',
            'data' => ds_get_select_by_field('boolstatus',2),
        ),
        array(
            'label' => '收货人',
            'type' => 'input',
            'id' => 'receiver_name',
            'title'=>'支持模糊查询'
        ),
        array(
            'label' => '手机号码',
            'type' => 'input',
            'id' => 'receiver_mobile',
            'title'=>'支持模糊查询'
        ),
        array(
            'label' => '国家',
            'type' => 'select',
            'id' => 'country',
            'data' => ds_get_select('country',2),
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
            'title'=>'支持模糊查询'
        ),
        array(
            'label' => '发票',
            'type' => 'select',
            'id' => 'is_invoice',
            'data' => ds_get_select_by_field('havestatus',2),
        ),
        array(
            'label' => '下单时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'record_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'record_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '支付时间',
            'type' => 'group',
            'field' => 'daterange2',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'pay_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'pay_time_end', 'remark' => ''),
            )
        ),
    )
));
?>
<?php 
/*
render_control("ToolBar", "tool",array(
    'button' => array(
        array('id' => 'opt_unpending', 'value' => '批量解挂'),
    ),
    'check_box'=>array(
        array('id'=> 'is_my_lock', 'value'=>'只显示我锁定的订单'),
    ),
    'custom_js' => 'btn_init_opt',
)); 
*/
?>
<ul id="tool2" class="toolbar" style="margin-top: 10px;">
	<li style="float:right"><input id="is_my_lock" type="checkbox">只显示我锁定的订单</li>
</ul>
  
<ul class="toolbar frontool" id="tool">
        <li class="li_btns"><button class="button button-primary btn_opt_unpending ">批量解挂</button></li>
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
<script>
    $(function(){
        var default_opts = ['opt_unpending'];
                for(var i in default_opts){
	    var f = default_opts[i];
	    btn_init_opt("tool",f);
	}
                var custom_opts = $.parseJSON('');
        for(var j in custom_opts){
            var g = custom_opts[j];
            $("#tool .btn_"+g['id']).click(eval(g['custom']));
        }
    });
</script>

<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单图标',
                'field' => 'status_text',
                'width' => '70',
                'align' => ''
            ),
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '70',
                'align' => '',
                'buttons' => array (
                    array('id'=>'view', 'title' => '解挂', 'callback' => 'unpending'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单编号',
                'field' => 'sell_record_code',
                'width' => '120',
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
                'title' => '挂起时间',
                'field' => 'is_pending_time',
                'width' => '180',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '自动解挂时间',
                'field' => 'is_unpending_time',
                'width' => '180',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '挂起原因',
                'field' => 'is_pending_name',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台',
                'field' => 'sale_channel_name',
                'width' => '70',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_name',
                'width' => '90',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'deal_code_list',
                'width' => '120',
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
                'title' => '收货地址',
                'field' => 'receiver_address',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'store_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '配送方式',
                'field' => 'express_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '下单时间',
                'field' => 'record_time',
                'width' => '100',
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
                'title' => '买家留言',
                'field' => 'buyer_remark',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单标签',
                'field' => 'tag_desc',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '挂起备注',
                'field' => 'is_pending_memo',
                'width' => '150',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'oms/SellRecordModel::get_pending_list',
    'queryBy' => 'searchForm',
    'fields' => $fields,
    'idField' => 'sell_record_id',
    'customFieldTable'=>'oms/sell_record_pending_list',
    'export'=> array('id'=>'exprot_list','conf'=>'hang_record_list','name'=>'挂起订单','export_type'=>'file'),
    'CheckSelection' => true,
    'CascadeTable' => array(
        'list' => array(
            array('title' => '商品名称', 'type' => 'text', 'width' => '100', 'field' => 'goods_name'),
            array('title' => '商品编码', 'type' => 'text', 'width' => '100', 'field' => 'goods_code'),
            array('title' => '商品条形码', 'type' => 'text', 'width' => '100', 'field' => 'barcode'),
            array('title' => $response['goods_spec1_rename'], 'type' => 'text', 'width' => '100', 'field' => 'spec1_name'),
            array('title' => $response['goods_spec2_rename'], 'type' => 'text', 'width' => '100', 'field' => 'spec2_name'),
            array('title' => '数量（实物锁定数）', 'type' => 'text', 'width' => '100', 'field' => 'num', 'format_js' => array('type' => 'html','value' => '{num}({lock_num})',)),
            array('title' => '标准价', 'type' => 'text', 'width' => '100', 'field' => 'goods_price'),
            array('title' => '单价', 'type' => 'text', 'width' => '100', 'field' => 'goods_price'),
            array('title' => '均摊金额', 'type' => 'text', 'width' => '100', 'field' => 'avg_money'),
            array('title' => '预售', 'type' => 'text', 'width' => '100', 'field' => 'sale_mode', 'format_js' => array('type' => 'map','value'=>array('stock'=>'现货','presale'=>'预售'))),
            array('title' => '赠品', 'type' => 'text', 'width' => '100', 'field' => 'is_gift', 'format_js' => array('type' => 'map','value' => array('0' => '否', '1' => '是'))),
            array('title' => '计划发货时间', 'type' => 'text', 'width' => '100', 'field' => 'plan_send_time'),
        ),
        'page_size' => 10,
        'url' => get_app_url('oms/sell_record/get_detail_list_by_sell_record_code&app_fmt=json'),
        'params' => 'sell_record_code'
    ),
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>


<script type="text/javascript">
    var url = '<?php echo get_app_url('base/store/get_area');?>';
    $(document).ready(function() {
        $("#sell_record_code").css("border","red 1px solid");
        $("#deal_code_list").css("border","red 1px solid");
        tableStore.on('beforeload', function(e) {
            e.params.is_my_lock = $("#is_my_lock").attr('checked')=='checked'?'1':'0';
        });
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
        tableStore.load();
    });

    function showDetail(index, row) {
        openPage('<?php echo base64_encode('?app_act=oms/sell_record/view&sell_record_code=') ?>'+row.sell_record_code,'?app_act=oms/sell_record/view&ref=do&sell_record_code='+row.sell_record_code,'挂起订单详情');
    }
    
    function unpending(index,row){
        var params = {"sell_record_code_list": [row.sell_record_code], "type": 'opt_unpending'};
            do_unpending(params);
    }
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
        BUI.Message.Show({
            title: '自定义提示框',
            msg: '是否执行订单' + obj.text() + '?',
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

    //初始化批量操作按钮
    function btn_init_opt(tab_id, id) {
        $("#" + tab_id + " .btn_" + id).click(function() {
            get_checked($(this), function(ids) {
                var params = {"sell_record_code_list": ids, "type": id, "batch":"批量操作"};
                do_unpending(params);
            })
        });
    }
    
    function do_unpending(params){
        $.post("?app_act=oms/sell_record/opt_batch", params, function(data){
                if(data.status == 1){
                    BUI.Message.Alert(data.message, 'info'); 
                    tableStore.load();
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json");
    }
    function view(sell_record_code) {
	    var url = '?app_act=oms/sell_record/view&sell_record_code=' +sell_record_code
	    openPage(window.btoa(url),url,'挂起订单详情');
       }
</script>
