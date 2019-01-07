<?php echo load_js('comm_util.js')?>
<style>
    #record_time_start,
    #record_time_end,
    #pay_time_start,
    #pay_time_end {
        width: 100px;
    }
    #sort {
        width: 230px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '问题订单列表',
    'links' => array(
        //array('url'=>'oms/sell_record/add', 'title'=>'新增订单', 'is_pop'=>false, 'pop_size'=>'500,400'),
    ),
    'ref_table' => 'table'
));
?>
<?php
$keyword_type = array();
$keyword_type['deal_code_list'] = '交易号';
$keyword_type['sell_record_code'] = '订单号';
$keyword_type['buyer_name'] = '买家昵称';
$keyword_type['receiver_name'] = '收货人';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['receiver_mobile'] = '手机号码';
$keyword_type['is_lock_person'] = '锁定人';
$keyword_type['goods_name'] = '商品名称';
$keyword_type = array_from_dict($keyword_type);
$buttons = array(
    array(
        'label' => '查询',
        'id' => 'btn-search',
        'type'=>'submit'
    ),
) ;
if (load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/export_question_list')) {
    $buttons[] =  array(
        'label' => '导出',
        'id' => 'exprot_list',
    );
}

render_control('SearchForm', 'searchForm', array(
    'buttons' => $buttons,
    'show_row'=>3,
    'fields' => array(
	    array(
	    		'label' => array('id'=>'keyword_type','type'=>'select','data'=>$keyword_type),
	    		'type' => 'input',
	    		'title'=>'',
	    		'data'=>$keyword_type,
	    		'id' => 'keyword',
	    		'help'=>'以下字段支持模糊查询：买家昵称、手机号码、商品条形码、商品编码、收货人、商品名称',
	    ),
        array(
            'label' => '买家申请退款',
            'type' => 'select',
            'id' => 'apply_refund',
            'data' => ds_get_select_by_field('apply_refund',0),
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
            //'data' => load_model('base/SaleChannelModel')->get_select()
            'data' => load_model('base/SaleChannelModel')->get_my_select(),
        ),
        
        array(
            'label' => '问题类型',
            'type' => 'select_multi',
            'id' => 'is_problem_type',
            'data' => ds_get_select('problem_type'),
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
            'label' => '买家留言',
            'type' => 'input',
            'id' => 'buyer_remark',
            'title'=>'支持模糊查询'
        ),
        array(
            'label' => '商家留言',
            'type' => 'input',
            'id' => 'seller_remark',
            'title'=>'支持模糊查询'
        ),
        array(
            'label' => '旗帜',
            'type' => 'select_multi',
            'id' => 'seller_flag',
            'data' => load_model('util/FormSelectSourceModel')->get_seller_flag(),
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
        		'label' => '国家',
        		'type' => 'select',
        		'id' => 'country',
        		'data' => ds_get_select('country',2),
        ),
        array(
            'label' => '发票',
            'type' => 'select',
            'id' => 'invoice_status',
            'data' => ds_get_select_by_field('havestatus'),
        ),
        array(
        		'label' => '订单性质',
        		'type' => 'select_multi',
        		'id' => 'question_list',
        		'data' => load_model('FormSelectSourceModel')->question_list(),
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
            'label' => '支付时间',
            'type' => 'group',
            'field' => 'daterange2',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'pay_time_start',),
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
			'label' => '订单标签',
			'type' => 'select_multi',
			'id' => 'order_tag',
			'data' => load_model('base/OrderLabelModel')->get_select(),
	),
    )
));
?>
<?php


foreach($response['problem_type'] as $key => $value):?>
    <?php if ($value['num'] > 0) {?>
        <span>
    	<a id="<?php echo $value['question_label_code']; ?>" name="<?php echo $value['question_label_code']; ?>" class="question_label" style="cursor:pointer" title="<?php echo $value['remark']?>">
    		<?php echo $value['question_label_name']; ?>(<?php echo $value['num']; ?>)
    	</a><!--<img height="20" width="20" alt="" src="assets/images/tip.png">-->
    </span>&nbsp;&nbsp;
    <?php }?>
<?php endforeach; ?>
<!--<ul class="toolbar" style="margin-top: 10px;margin-left:20px">
    <li><button class="button button-primary btn-opt-return">批量返回正常单</button></li>
</ul>-->
<div>
    <span style="text-align:right; margin-right:20px;">
        <ul id="tool2" class="toolbar">        
            <li >
                <label>排序类型：</label>
                <select id="sort" name="sort" onchange="sort()" >
                    <option value="" >默认(计划发货时间+付款时间升序)</option>
                    <option value="pay_time_asc">付款时间升序</option>
                    <option value="pay_time_desc">付款时间降序</option>
                    <option value="record_time_asc">下单时间升序</option>
                    <option value="record_time_desc">下单时间降序</option>
                </select>
                <!--<button type="button" class="button button-small" id="sort_btn" onclick = "sort()">排序</button>-->
                <img src="assets/images/tip.png" alt="123" width="25" height="25" title ="排序所有页签"/>
            </li>
        </ul>
    </span>
</div>
<?php
/*
render_control("ToolBar", "tool",array(
    'button' => array(
        array('id' => 'opt_unproblem', 'value' => '批量返回正常单'),
    ),
    'custom_js' => 'btn_init_opt',
)); */

?>
    <ul id="tool" class="toolbar frontool">
        <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/opt_batch1')) { ?>
        <li class="li_btns"><button class="button button-primary btn_opt_unproblem ">批量返回正常单</button></li>
        <?php } ?>
        <li class="li_btns"><button class="button button-primary btn_edit_express_code">批量修改配送方式</button></li>
        <li class="li_btns"><button class="button button-primary btn_edit_store_code">批量修改仓库</button></li>   
        <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/opt_cancel')) { ?>
        <li class="li_btns"><button class="button button-primary btn_cancel">批量作废</button></li>
        <?php } ?>
         <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/btn_delete')) { ?>
        <li class="li_btns"><button class="button button-primary btn_delete">批量删除退款商品</button></li>
         <?php } ?>
        <li class="li_btns"><button class="button button-primary btn_opt_label">批量打标</button></li>
        <li class="li_btns"><button class="button button-primary btn_opt_edit_order_remark">批量备注</button></li>
        <li class="li_btns"><button class="button button-primary btn_opt_pending ">批量挂起</button></li>
        <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/question_split_order')) {?>
            <li class="li_btns"><button class="button button-primary btn_opt_split_order">批量拆单</button></li>
        <?php }?>
        <div class="front_close">&lt;</div>
    </ul>

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
})
</script>
<script>
    //排序
    function sort() {
        tableStore.load();
    }    
    $(function(){
        //排序
        tableStore.on('beforeload', function (e) {
            var sort_e = $("#sort  option:selected");
            if (sort_e.length > 0) {
                e.params.is_sort = $("#sort  option:selected").val();
            }
            tableStore.set("params", e.params);
        })
        var default_opts = ['opt_unproblem'];
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
                'align' => '',
                'format_js' => array(
                    'type' => 'html',           
                    'value' => '<a title="{desc}">{status_text}</a>',
                )
            ),
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '90',
                'align' => '',
                'buttons' => array (
                    array(
                        'id' => 'return',
                        'title' => '返回正常单',
                        'callback' => 'unproblem',
                        'confirm' => '确定要返回为正常单？',
                    ),
                    array(
                        'id' => 'edit', 
                        'title' => '修改',
                        'act' => 'pop:oms/sell_record/ex_update_detail&sell_record_code={sell_record_code}', 
                        'show_name' => '修改订单信息（{sell_record_code}）',
                        'pop_size' => '1200,403'
                        ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单号',
                'field' => 'sell_record_code',
                'width' => '120',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view(\\\'{sell_record_code}\\\')" title="{desc}">{sell_record_code}</a>',
                )
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '问题类型',
                'field' => 'problem_html',
                'width' => '150',
                'align' => '',
                'format_js' => array(
                    'type' => 'html', 
                    'value' => '<span title="{ddesc}">{problem_html}</span>',
                )
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
                'title' => '商家留言',
                'field' => 'seller_remark',
                'width' => '100',
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
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台',
                'field' => 'sale_channel_code',
                'width' => '70',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'deal_code_list',
                'width' => '135',
                'align' => ''
            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '买家昵称',
            		'field' => 'buyer_name',
            		'width' => '80',
            		'align' => '',
                         'format_js' => array(
                        'type' => 'function',
                        'value' => 'set_wangwang_html')
            
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收货人',
                'field' => 'receiver_name',
                'width' => '80',
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
                'title' => '仓库',
                'field' => 'store_name',
                'width' => '90',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '配送方式',
                'field' => 'express_code_name',
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
	            'title' => '付款时间',
	            'field' => 'pay_time',
	            'width' => '150',
	            'align' => ''
            ),
            array(
	            'type' => 'text',
	            'show' => 0,
	            'title' => '已付款',
	            'field' => 'paid_money',
	            'width' => '80',
	            'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '发票抬头',
                'field' => 'invoice_title',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '发票内容',
                'field' => 'invoice_content',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
				'show' => 1,
				'title' => '订单标签',
				'field' => 'order_tag',
				'width' => '100',
				'align' => '',
             ),
        )
    ),
    'dataset' => 'oms/SellRecordModel::get_list_by_page',
    'queryBy' => 'searchForm',

    'export'=> array('id'=>'exprot_list','conf'=>'question_record_list','name'=>'问题单','export_type'=>'file'),

    'idField' => 'sell_record_id',
    'customFieldTable'=>'oms/sell_record_question_list',
    'CheckSelection' => true,
    'ColumnResize' => true,
    'CascadeTable' => array(
        'list' => array(
            array('title' => '商品状态', 'type' => 'text', 'width' => '100', 'field' => 'goods_status', 'align' => 'center'),
            array('title' => '商品图片', 'type' => 'text', 'width' => '100', 'field' => 'pic_path'),
            array('title' => '商品名称', 'type' => 'text', 'width' => '100', 'field' => 'goods_name'),
            array('title' => '商品编码', 'type' => 'text', 'width' => '100', 'field' => 'goods_code'),
            array('title' => '商品条形码', 'type' => 'text', 'width' => '100', 'field' => 'barcode'),
            array('title' => $response['goods_spec1_rename'], 'type' => 'text', 'width' => '100', 'field' => 'spec1_name'),
            array('title' => $response['goods_spec2_rename'], 'type' => 'text', 'width' => '100', 'field' => 'spec2_name'),
            array('title' => '数量（实物锁定数）', 'type' => 'text', 'width' => '100', 'field' => 'num', 'format_js' => array('type' => 'html','value' => '{num}({lock_num})',)),
            array('title' => '标准价', 'type' => 'text', 'width' => '100', 'field' => 'goods_price'),
            array('title' => '单价', 'type' => 'text', 'width' => '100', 'field' => 'goods_price'),
            array('title' => '均摊金额', 'type' => 'text', 'width' => '100', 'field' => 'avg_money'),
            array('title' => '预售', 'type' => 'text', 'width' => '100', 'field' => 'sale_mode', 'format_js' => array('type' => 'map', 'value'=>array('stock'=>'现货','presale'=>'预售'))),
            array('title' => '赠品', 'type' => 'text', 'width' => '100', 'field' => 'is_gift', 'format_js' => array('type' => 'map', 'value'=>array('1'=>'是','0'=>'否'))),
            array('title' => '计划发货时间', 'type' => 'text', 'width' => '100', 'field' => 'plan_send_time'),
        ),
        'page_size' => 10,
        'url' => get_app_url('oms/sell_record/get_detail_list_by_sell_record_code&app_fmt=json'),
        'ExpandCascadeDetail' => array(
            'detail_url' => get_app_url('oms/sell_record/get_ex_list_cascade_data'),//查询展开详情的方法
            'detail_param' => 'sell_record_code',//查询展开详情的使用的参数
        ),
        'params' => 'sell_record_code'
    ),
    'params' => array(
        'filter' => array("search_mode"=>'problem_order','detail_list'=>'1'),
    ),

    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>
</div>
<script>
    function showDetail(index, row) {
        openPage('<?php echo base64_encode('?app_act=oms/sell_record/view&sell_record_code=') ?>'+row.sell_record_code,'?app_act=oms/sell_record/view&ref=do&sell_record_code='+row.sell_record_code,'问题订单详情');
    }
    function view(sell_record_code) {
        var url = '?app_act=oms/sell_record/view&sell_record_code=' +sell_record_code
        openPage(window.btoa(url),url,'问题订单详情');
    }
    var area_url = '<?php echo get_app_url('base/store/get_area');?>';
    $(document).ready(function(){
        $(".btn_edit_express_code").click(function(){
            get_checked($(this), function(ids){
                new ESUI.PopWindow("?app_act=oms/sell_record/edit_express_code&sell_record_code_list="+ids.toString(), {
                    title: "批量修改配送方式",
                    width:500,
                    height:250,
                    onBeforeClosed: function() {
                    },
                    onClosed: function(){
                        //刷新数据
                        tableStore.load()
                    }
                }).show()
            })
        })
        $(".btn_edit_store_code").click(function(){
            get_checked($(this), function(ids){
                new ESUI.PopWindow("?app_act=oms/sell_record/edit_store_code&sell_record_id_list="+ids.toString(), {
                    title: "批量修改仓库",
                    width:500,
                    height:250,
                    onBeforeClosed: function() {
                    },
                    onClosed: function(){
                        //刷新数据
                        tableStore.load()
                    }
                }).show()
            })
        })
        $(".btn_cancel").click(function(){
            get_checked($(this), function(ids){
                var params = {"sell_record_id_list": ids};
                $.post("?app_act=oms/sell_record/cancel_all", params, function(data){
                    if(data.status == '1'){
                        //刷新数据
                        tableStore.load()
                    } else {
                        BUI.Message.Alert(data.message, 'error');
                    }
                }, "json");

            })
        })
        $('#country').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,0,area_url);
        });
        $('#province').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,1,area_url);
        });
        $('#city').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,2,area_url);
        });
        $('#district').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,3,area_url);
        });
//        $('#country').val('1');
        $('#country').change();
    })
    $(function(){
        $("#record_code").css("border","red 1px solid");
        $("#deal_code_list").css("border","red 1px solid");
        $("#FULL_REFUND").css("color","red");
        $("#REFUND").css("color","red");
        $(".question_label").click(function(){
            var code = $(this).attr("name");
            $("#is_problem_type").val(code);
            $("#is_problem_type_select_multi").find(".bui-select-input").click();
            is_problem_type_select.get('picker').hide();
             $("#btn-search").click();
        });
    });


    function unproblem(index,row){
        var params = {"sell_record_code_list": [row.sell_record_code], "type": 'opt_unproblem'};
        do_unproblem(params);
    }
    //读取已选中项
    function get_checked(obj, func) {
        var ids = new Array();
        var idss = new Array();
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
        if (obj.text() == '批量修改仓库') {
            for (var j in rows) {
            var row = rows[j];
            idss.push(row.sell_record_id);
            }
            func.apply(null, [idss]);
        }else{
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
        
    }

    //初始化批量操作按钮
    function btn_init_opt(tab_id, id) {
        $("#" + tab_id + " .btn_" + id).click(function() {
            get_checked($(this), function(ids) {
                show_alert(ids,id);    
            })
        });
    }
    
    function show_alert(ids,id){
        var codes = {"sell_record_code_list": ids};
        $.post("?app_act=oms/sell_record/check_question", codes, function(data){
            if(data.status == 1){
               var params = {"sell_record_code_list": ids, "type": id, "batch":"批量操作"};
               do_unproblem(params);
            } else {
               show_question_code(ids,data.data,id);
            }
        }, "json");
    }
    
    function show_question_code(ids,codes,id){
        BUI.Message.Show({
            title: '批量返回正常单',
            msg: '下列订单包含多个问题原因：<br/>' + codes + '，<br/>确定返回正常单?',
            icon: 'question',
            buttons: [
                {
                    text: '是',
                    elCls: 'button button-primary',
                    handler: function() {
                        this.close();
                        var params = {"sell_record_code_list": ids, "type": id, "batch":"批量操作"};
                        do_unproblem(params);
                        
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
    
    function do_unproblem(params){
        $.post("?app_act=oms/sell_record/opt_batch", params, function(data){
            if(data.status == 1){
                BUI.Message.Alert(data.message, 'info');
                tableStore.load();
            } else {
                BUI.Message.Alert(data.message, 'error');
            }
        }, "json");
    }

    //function do_return(_index, row){
    //    return_action(row.sell_record_id);
    //}
    //
    //function return_action(sell_record_id){
    //    $.post('<?php // echo $response['operate']['return_normal']; ?>',{sell_record_id:sell_record_id},function(ret){
    //        if(ret.status){
    //            alert("操作成功");
    //            window.location.reload();
    //        }else{
    //            alert("操作失败");
    //        }
    //    },'json');
    //}
    //
    //$(function(){
    //    $(".toolbar .btn-opt-return").click(function(){
    //        var sell_record_id = new Array();
    //        $(".bui-grid-row-selected").each(function(){
    //            if($(this).attr("checked")){
    //                sell_record_id.push($(this).val());
    //            }
    //        });
    //        if(sell_record_id.length>0){
    //            return_action(sell_record_id);
    //        }else{
    //            alert("请先选择单据");
    //        }
    //    });
    //});
    
    
    
   //批量删除
        $(".btn_delete").click(function(){
            get_checked($(this), function(ids){
                var params = {"sell_record_code_list": ids};
                $.post("?app_act=oms/sell_record/do_delete", params, function(data){
                    if(data.status == '1'){
                        BUI.Message.Alert(data.message, 'success');
                        //刷新数据
                        tableStore.load()
                    } else {
                        BUI.Message.Alert(data.message, 'error');
                    }
                }, "json");

            })
        })
    
    
    //批量打标
    $(".btn_opt_label").click(function(){
	get_checked($(this), function(ids) {
        new ESUI.PopWindow("?app_act=oms/sell_record/label&batch=<?php echo urlencode("批量操作"); ?>&sell_record_code_list=" + ids.toString(), {
            title: "批量打标签",
            width: 500,
            height:300,
            onBeforeClosed: function() {
            },
            onClosed: function() {
                //刷新数据
                tableStore.load()
            }
        }).show()
    })
});
    
   
  //批量备注
    $(".btn_opt_edit_order_remark").click(function(){
        get_checked($(this), function (ids) {
            new ESUI.PopWindow("?app_act=oms/sell_record/edit_order_remark&sell_record_code_list=" + ids.toString(), {
                title: "批量备注",
                width: 500,
                height: 250,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    //刷新数据
                    tableStore.load();
                }
            }).show();
        });
    });
    
//批量挂起
$(".btn_opt_pending").click(function(){
	get_checked($(this), function(ids) {
        new ESUI.PopWindow("?app_act=oms/sell_record/pending&batch=<?php echo urlencode("批量操作"); ?>&sell_record_code_list=" + ids.toString(), {
            title: "批量挂起",
            width: 550,
            height:480,
            onBeforeClosed: function() {
            },
            onClosed: function() {
                //刷新数据
                tableStore.load()
            }
        }).show()
    })
});
    //旺旺
    function set_wangwang_html(value, row, index){
        if(row.sale_channel_code == '淘宝') {
            return template('<span> {buyer_name}</span><span class="wangwang"><span class="ww-light ww-small"><a href="javascript:launch_ww(\'{sell_record_code}\')" class="ww-inline ww-online" title="点此可以直接和买家交流。"><span>旺旺在线</span></a></span></span>', row);
        } else {
           return template('<span>{buyer_name}</span>', row);
        }
    }

  function launch_ww(record_code){
        var url = "?app_act=oms/sell_record/link_wangwang&record_code="+record_code;
        window.open(url);
    }


    //批量拆单
    $('.btn_opt_split_order').click(function(){
        get_checked($(this), function (ids) {
            new ESUI.PopWindow("?app_act=oms/sell_record/split_order_batch&sell_record_code_list=" + ids.toString(), {
                title: "批量拆单",
                width: 480,
                height: 350,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    tableStore.load();
                }
            }).show();
        });
    });

</script>


