<?php echo load_js("baison.js,record_table.js,pur.js",true);?>
<?php require_lib('util/oms_util', true);?>
<?php echo load_js('xlodop.js'); ?>
<?php echo load_js('lodop.js'); ?>

<?php echo load_js('jquery.cookie.js')?>

<?php render_control('PageHead', 'head1',
    array('title'=>'订单波次打印',
        'links'=>array(
            array('url'=>'oms/waves_record/two_order_picking ', 'title'=>'二次分拣', 'is_pop'=>false,/* 'pop_size'=>'500,400'*/),
        ),
        'ref_table'=>'table'
    ));?>


<?php
$keyword_type = array();
$keyword_type['deal_code'] = '交易号';
$keyword_type['sell_record_code'] = '订单号';
$keyword_type['buyer_name'] = '买家昵称';
$keyword_type['receiver_name'] = '收货人';
$keyword_type['express_no'] = '快递单号';
$keyword_type['record_code'] = '波次号';
$keyword_type['goods_name'] = '商品名称';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type = array_from_dict($keyword_type);

render_control ( 'SearchForm', 'searchForm', array (
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
    'show_row' => 4,
    'fields' => array (
	    array(
	    	'label' => array('id'=>'keyword_type','type'=>'select','data'=>$keyword_type),
	    	'type' => 'input',
	    	'title'=>'',
	    	'data'=>$keyword_type,
	    	'id' => 'keyword',
	    ),
        array(
            'label' => '拣货员',
            'type' => 'select_multi',
            'id' => 'staff_code',
            'data' =>load_model('base/StoreStaffModel')->get_select_store_staff(),
            'value' => $response['picker']['staff_code'],
        ),
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_store_no_contain_wms(),
        ),
        array(
        	'label' => '销售平台',
        	'type' => 'select_multi',
        	'id' => 'source',
        	//'data' => load_model('base/SaleChannelModel')->get_select()
            'data' => load_model('base/SaleChannelModel')->get_my_select()
        ),
        array(
        	'label' => '是否验收',
        	'type' => 'select',
        	'id' => 'check_accept',
        	'data' => ds_get_select_by_field('check_accept',2)
        ),
        array(
        	'label' => '是否发货',
        	'type' => 'select',
        	'id' => 'check_deliver',
        	'data' => ds_get_select_by_field('check_deliver',2)
        ),
        array(
            'label' => '订单性质',
            'type'  => 'select_multi',
            'id'    => 'sell_record_attr',
            'data'  => load_model('util/FormSelectSourceModel')->sell_record_attr_new(),
        ),
        array(
        	'label' => '店铺',
        	'type' => 'select_multi',
        	'id' => 'shop_code',
        	//          'data' => ds_get_select('shop'),
        	'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
            'label' => '业务日期',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'record_time_start', 'value' => date('Y-m-d', strtotime("-7 days"))),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'record_time_end', 'value' => date('Y-m-d'), 'remark' => ''),
            )
        ),
        array(
            'label' => '配送方式',
            'type' => 'select_multi',
            'id' => 'express_code',
            'data' => ds_get_select('express'),
        ),
        array(
            'label' => '波次备注',
            'type' => 'select',
            'id' => 'sell_num_type',
            'data' => array(
                array('','全部'),
                array('1','一单一品'),
                array('2','一单多品'),
            )
        ),
//         array(
//         	'label' => '打印快递单',
//         	'type' => 'select',
//         	'id' => 'is_print_express',
//         	'data' => ds_get_select_by_field('boolstatus'),
//         ),
//         array(
//         	'label' => '打印发货单',
//         	'type' => 'select',
//         	'id' => 'is_print_sellrecord',
//         	'data' => ds_get_select_by_field('boolstatus'),
//         ),
//         array(
//             'label' => '验收',
//             'type' => 'select',
//             'id' => 'is_accept',
//             'data' => ds_get_select_by_field('boolstatus'),
//         ),
//           array(
//             'label' => '发货',
//             'type' => 'select',
//             'id' => 'is_deliver',
//             'data' => ds_get_select_by_field('boolstatus'),
//         ),
//         array(
//             'label' => '打印波次单',
//             'type' => 'select',
//             'id' => 'is_print_goods',
//             'data' => ds_get_select_by_field('boolstatus'),
//         ),
    )
) );
?>

<!-- <ul class="toolbar frontool" id="tool"> -->
<!--     <li class="li_btns"><button class="button button-primary btn_edit_express_code">批量修改配送方式</button></li> -->
<!--     <li class="li_btns"><button class="button button-primary btn_edit_express_no">自动匹配物流</button></li> -->
<!--     <li class="li_btns"><button class="button button-primary btn_opt_print_goods">批量打印波次单</button></li> -->
<!--     <li class="li_btns"><button class="button button-primary btn_opt_print_express">批量打印快递单</button></li> -->
<!--     <li class="li_btns"><button class="button button-primary btn_opt_print_sellrecord">批量打印发货单</button></li> -->
<!--     <div class="front_close">&lt;</div> -->
<!-- </ul> -->
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
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '全部', 'active' => $response['tabs_all'], 'id' => 'tabs_all'),// 默认选中active=true的页签
		array('title' => '待打印快递单', 'active' => false, 'id' => 'tabs_print_express'),
        array('title' => '待打印发货单', 'active' => false, 'id' => 'tabs_print_sellrecord'),
        array('title' => '待验收', 'active' => false, 'id' => 'tabs_accept'),
        array('title' => '待发货', 'active' => false, 'id' => 'tabs_sending'),
        array('title' => '已发货', 'active' => $response['tabs_sended'], 'id' => 'tabs_sended'),
        array('title' => '已取消', 'active' => false, 'id' => 'tabs_cancel')
    ),
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<div id="TabPage1Contents">
        <!-- 全部 -->
	<div>
		<ul class="toolbar frontool" id="ToolBar1">
		    <!--<li class="li_btns"><button class="button button-primary btn_edit_express_code">批量修改配送方式</button></li>-->
		    <li class="li_btns"><button class="button button-primary btn_edit_express_no">自动匹配物流</button></li>
		    <li class="li_btns"><button class="button button-primary btn_opt_thermal_all">批量获取云栈热敏物流</button></li>
		    <li class="li_btns"><button class="button button-primary btn_opt_print_goods">批量打印波次单</button></li>
            <?php if (load_model('sys/SysParamsModel')->get_val_by_code(array('character_print'))['character_print']) { ?>
                <li class="li_btns"><button class="button button-primary btn_opt_print_goods_clothing">批量打印波次单（服装行业）</button></li>
            <?php }?>
		    <li class="li_btns"><button class="button button-primary btn_opt_print_express">批量打印快递单</button></li>
		    <li class="li_btns"><button class="button button-primary btn_opt_print_sellrecord">批量打印发货单</button></li>
                    <li class="li_btns"><button class="button button-primary btn_distribute_pick">批量分配拣货员</button></li>
		    <div class="front_close">&lt;</div>
		</ul>
		<script>
			$(function(){
// 			    var default_opts = ['edit_express_code','edit_express_no','opt_print_goods','opt_print_express','opt_print_sellrecord'];
// 			    for(var i in default_opts){
// 				    var f = default_opts[i];
// 				    btn_init("ToolBar1",f);
// 				}
				var custom_opts = $.parseJSON('[{"id":"edit_express_code","custom":"btn_init_edit_express_code"},{"id":"opt_print_goods_clothing","custom":"btn_opt_print_goods_clothing"},{"id":"edit_express_no","custom":"btn_init_edit_express_no"},{"id":"opt_thermal_all","custom":"btn_init_opt_thermal_all"},{"id":"opt_print_goods","custom":"btn_init_opt_print_goods"},{"id":"opt_print_express","custom":"btn_init_opt_print_express"},{"id":"opt_print_sellrecord","custom":"print_sellrecord"},{"id":"distribute_pick","custom":"distribute_pick_member"}]');
				for(var j in custom_opts){
				    var g = custom_opts[j];
				    $("#ToolBar1 .btn_"+g['id']).click(eval(g['custom']));
				}
			});
		</script>
	</div>
        <!-- 待打印快递单 -->
	<div>
		<ul class="toolbar frontool" id="ToolBar2">
		    <!--<li class="li_btns"><button class="button button-primary btn_edit_express_code">批量修改配送方式</button></li>-->
		    <li class="li_btns"><button class="button button-primary btn_edit_express_no">自动匹配物流</button></li>
		    <li class="li_btns"><button class="button button-primary btn_opt_print_goods">批量打印波次单</button></li>
            <?php if (load_model('sys/SysParamsModel')->get_val_by_code(array('character_print'))['character_print']) { ?>
                <li class="li_btns"><button class="button button-primary btn_opt_print_goods_clothing">批量打印波次单（服装行业）</button></li>
            <?php }?>
		    <li class="li_btns"><button class="button button-primary btn_opt_print_express">批量打印快递单</button></li>
		    <div class="front_close">&lt;</div>
		</ul>
		<script>
			$(function(){
// 			    var default_opts = ['edit_express_code','edit_express_no','opt_print_goods','opt_print_express'];
// 			    for(var i in default_opts){
// 				    var f = default_opts[i];
// 				    btn_init("ToolBar2",f);
// 				}
				var custom_opts = $.parseJSON('[{"id":"edit_express_code","custom":"btn_init_edit_express_code"},{"id":"opt_print_goods_clothing","custom":"btn_opt_print_goods_clothing"},{"id":"edit_express_no","custom":"btn_init_edit_express_no"},{"id":"opt_print_goods","custom":"btn_init_opt_print_goods"},{"id":"opt_print_express","custom":"btn_init_opt_print_express"}]');
				for(var j in custom_opts){
				    var g = custom_opts[j];
				    $("#ToolBar2 .btn_"+g['id']).click(eval(g['custom']));
				}
			});
		</script>
	</div>
        <!-- 待打印发货单 -->
	<div>
		<ul class="toolbar frontool" id="ToolBar3">
		    <li class="li_btns"><button class="button button-primary btn_opt_print_goods">批量打印波次单</button></li>
            <?php if (load_model('sys/SysParamsModel')->get_val_by_code(array('character_print'))['character_print']) { ?>
                <li class="li_btns"><button class="button button-primary btn_opt_print_goods_clothing">批量打印波次单（服装行业）</button></li>
            <?php }?>
		    <li class="li_btns"><button class="button button-primary btn_opt_print_sellrecord">批量打印发货单</button></li>
		    <div class="front_close">&lt;</div>
		</ul>
		<script>
			$(function(){
// 			    var default_opts = ['opt_print_express','opt_print_sellrecord'];
// 			    for(var i in default_opts){
// 				    var f = default_opts[i];
// 				    btn_init("ToolBar3",f);
// 				}
				var custom_opts = $.parseJSON('[{"id":"opt_print_goods","custom":"btn_init_opt_print_goods"},{"id":"opt_print_goods_clothing","custom":"btn_opt_print_goods_clothing"},{"id":"opt_print_sellrecord","custom":"print_sellrecord"}]');
				for(var j in custom_opts){
				    var g = custom_opts[j];
				    $("#ToolBar3 .btn_"+g['id']).click(eval(g['custom']));
				}
			});
		</script>
	</div>
        <!-- 待验收 -->
	<div>
		<ul class="toolbar frontool" id="ToolBar4">
		    <li class="li_btns"><button class="button button-primary btn_opt_print_goods">批量打印波次单</button></li>
            <?php if (load_model('sys/SysParamsModel')->get_val_by_code(array('character_print'))['character_print']) { ?>
                <li class="li_btns"><button class="button button-primary btn_opt_print_goods_clothing">批量打印波次单（服装行业）</button></li>
            <?php }?>
                    <li class="li_btns"><button class="button button-primary btn_opt_cancel_waves" <?php if (!load_model('sys/PrivilegeModel')->check_priv('oms/waves_record/do_cancel_waves')) { ?> style="display:none;" <?php } ?> >批量取消波次单</button></li>
		    <li class="li_btns"><button class="button button-primary btn_opt_print_express">批量打印快递单</button></li>
		    <li class="li_btns"><button class="button button-primary btn_opt_print_sellrecord">批量打印发货单</button></li>
		    <div class="front_close">&lt;</div>
		</ul>
		<script>
			$(function(){
// 			    var default_opts = ['edit_express_code','edit_express_no','opt_print_goods','opt_print_express','opt_print_sellrecord'];
// 			    for(var i in default_opts){
// 				    var f = default_opts[i];
// 				    btn_init("ToolBar4",f);
// 				}
				var custom_opts = $.parseJSON('[{"id":"opt_cancel_waves","custom":"btn_opt_cancel_waves"},{"id":"opt_print_goods_clothing","custom":"btn_opt_print_goods_clothing"},{"id":"opt_print_goods","custom":"btn_init_opt_print_goods"},{"id":"opt_print_express","custom":"btn_init_opt_print_express"},{"id":"opt_print_sellrecord","custom":"print_sellrecord"}]');
				for(var j in custom_opts){
				    var g = custom_opts[j];
				    $("#ToolBar4 .btn_"+g['id']).click(eval(g['custom']));
				}
			});
		</script>
	</div>
        <!-- 代发货 -->
	<div>
		<ul class="toolbar frontool" id="ToolBar5">
			<li class="li_btns"><button class="button button-primary btn_opt_print_goods">批量打印波次单</button></li>
            <?php if (load_model('sys/SysParamsModel')->get_val_by_code(array('character_print'))['character_print']) { ?>
                <li class="li_btns"><button class="button button-primary btn_opt_print_goods_clothing">批量打印波次单（服装行业）</button></li>
            <?php }?>
		    <li class="li_btns"><button class="button button-primary btn_opt_print_express">批量打印快递单</button></li>
		    <li class="li_btns"><button class="button button-primary btn_opt_print_sellrecord">批量打印发货单</button></li>
		    <div class="front_close">&lt;</div>
		</ul>
		<script>
			$(function(){
// 			    var default_opts = ['edit_express_code','edit_express_no','opt_print_goods','opt_print_express','opt_print_sellrecord'];
// 			    for(var i in default_opts){
// 				    var f = default_opts[i];
// 				    btn_init("ToolBar5",f);
// 				}
				var custom_opts = $.parseJSON('[{"id":"opt_print_goods","custom":"btn_init_opt_print_goods"},{"id":"opt_print_goods_clothing","custom":"btn_opt_print_goods_clothing"},{"id":"opt_print_express","custom":"btn_init_opt_print_express"},{"id":"opt_print_sellrecord","custom":"print_sellrecord"}]');
				for(var j in custom_opts){
				    var g = custom_opts[j];
				    $("#ToolBar5 .btn_"+g['id']).click(eval(g['custom']));
				}
			});
		</script>
	</div>
        <!-- 已发货 -->
	<div>
		<ul class="toolbar frontool" id="ToolBar6">
		    <li class="li_btns"><button class="button button-primary btn_opt_print_goods">批量打印波次单</button></li>
            <?php if (load_model('sys/SysParamsModel')->get_val_by_code(array('character_print'))['character_print']) { ?>
            <li class="li_btns"><button class="button button-primary btn_opt_print_goods_clothing">批量打印波次单（服装行业）</button></li>
		    <?php }?>
            <li class="li_btns"><button class="button button-primary btn_opt_print_express">批量打印快递单</button></li>
		    <li class="li_btns"><button class="button button-primary btn_opt_print_sellrecord">批量打印发货单</button></li>
		    <div class="front_close">&lt;</div>
		</ul>
		<script>
			$(function(){
// 			    var default_opts = ['edit_express_code','edit_express_no','opt_print_goods','opt_print_express','opt_print_sellrecord'];
// 			    for(var i in default_opts){
// 				    var f = default_opts[i];
// 				    btn_init("ToolBar6",f);
// 				}
				var custom_opts = $.parseJSON('[{"id":"opt_print_goods","custom":"btn_init_opt_print_goods"},{"id":"opt_print_goods_clothing","custom":"btn_opt_print_goods_clothing"},{"id":"opt_print_express","custom":"btn_init_opt_print_express"},{"id":"opt_print_sellrecord","custom":"print_sellrecord"}]');
				for(var j in custom_opts){
				    var g = custom_opts[j];
				    $("#ToolBar6 .btn_"+g['id']).click(eval(g['custom']));
				}
			});
		</script>
	</div>
	<div>
		<ul class="toolbar frontool" id="ToolBar7">

		</ul>
		<script>

		</script>
	</div>
</div>

<?php
$expressList = oms_opts2_by_tb('base_express', 'express_code', 'express_name', array(), 2);
$storeList = oms_opts2_by_tb('base_store', 'store_code', 'store_name', array('status'=>1), 2);
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '100',
                'align' => '',
                'buttons' => array (
                    array('id'=>'view', 'title' => '查看', 'callback' => 'showDetail'),
                    array('id'=>'accept', 'title' => '验收', 'callback'=>'do_accept','show_cond' => 'obj.is_cancel == 0  && obj.is_accept == 0'),
                    array('id'=>'cancel', 'title' => '取消发货', 'callback'=>'do_cancel','show_cond' => 'obj.is_cancel == 0  && obj.is_accept == 0 && obj.do_cancel_privilege == 1'),
                ),
            ),
                    array (
                'type' => 'text',
                'show' => 1,
                'title' => '波次号',
                'field' => 'record_code',
                'width' => '130',
                'align' => '',
                'format_js' => array(
	                'type' => 'html',
	                'value'=>"<a param1=\"{waves_record_id}\" class=\"waves_record_view\" href=\"javascript:void(0)\">{record_code}</a>",
                )
            ),
                  array(
                'type' => 'text',
                'show' => 1,
                'title' => '打印',
                'field' => 'is_print_goods',
                'width' => '120',
                'align' => '',
                'format_js' => array('type' => 'function','value'=>'get_is_print')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'is_accept',
                'width' => '80',
                'align' => '',
                'format_js' => array('type' => 'function','value'=>'get_status')
            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '波次单打印',
//                'field' => 'is_print_waves',
//                'width' => '80',
//                'align' => '',
//                'format_js' => array('type' => 'map_checked')
//            ),

//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '已打印快递单',
//                'field' => 'html_print_express',
//                'width' => '100',
//                'align' => '',
////              'format_js' => array('type' => 'map_checked'),
//            ),


            array (
                'type' => 'text',
                'show' => 1,
                'title' => '业务日期',
                'field' => 'record_time',
                'width' => '80',
                'align' => '',
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'store_code',
                'format_js'=> array('type'=>'map', 'value'=>$storeList),
                'width' => '100',
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
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '订单数量',
                'field' => 'total_sell_record',
                'width' => '80',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '订单有效数',
                'field' => 'sell_record_count',
                'width' => '80',
                'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '未发货订单数',
            		'field' => 'is_deliver_count',
            		'width' => '80',
            		'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '商品数量',
                'field' => 'goods_count',
                'width' => '80',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '商品取消数量',
                'field' => 'cancelled_goods_count',
                'width' => '80',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '商品有效数量',
                'field' => 'valide_goods_count',
                'width' => '80',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '操作人',
                'field' => 'user_name',
                'width' => '80',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'oms/WavesRecordModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'waves_record_id',
    'export' => array('id' => 'exprot_list', 'conf' => 'waves_record_list', 'name' => '波次单', 'export_type' => 'file'),
    'params' => array('filter' => array('record_time_start' => date('Y-m-d', strtotime("-7 days")), 'record_time_end' => date('Y-m-d'), 'do_list_tab' => $response['do_list_tab'],'staff_code'=>$response['picker']['staff_code'])),
    'CheckSelection' => true,
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
) );
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
<script type="text/javascript">
    
    var success_num = 0;
    var faild_num = 0;
    var counter = 0;
    
    var wave_check = <?php echo $response['wave_check']?>;
    var print_delivery_record_template = <?php echo $response['print_delivery_record_template']?>;
    var new_clodop_print = "<?php echo $response['new_clodop_print'];?>";
    $(function(){
    	//TAB选项卡
        $("#TabPage1 a").click(function() {
            tableStore.load();
        });
        $("input[name='is_normal']").change(function(){
            tableStore.load();
        });
        tableStore.on('beforeload', function(e) {
           e.params.do_list_tab = $("#TabPage1").find(".active").find("a").attr("id");
           tableStore.set("params", e.params);
        });

        set_waves_record_view();
    });
    var opts = ['edit_express_code', 'edit_express_no','opt_print_goods', 'opt_print_express', 'opt_print_sellrecord'];


    function set_waves_record_view(){
       tableGrid.on("aftershow",function(e){
           $('.waves_record_view').on('click',function(){
	           var url = '?app_act=oms/waves_record/view&waves_record_id=' +$(this).attr('param1');
	           openPage(window.btoa(url),url,'波次单详情');
           });
       });
    }

    //初始化批量操作按钮
    function btn_init(tab_id,id){
        
    }

    //读取已选中项
    function get_checked(isConfirm, obj, func){
        /*var ids = $("[name=ckb_record_id]:checkbox:checked").map(function(){
            return $(this).val()
        }).get()*/
        var ids = []
        var selecteds = tableGrid.getSelection();
        for(var i in selecteds){
            ids.push(selecteds[i].waves_record_id)
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

    //批量修改配送方式
    function btn_init_edit_express_code(){
            get_checked(false, $(this), function(ids){
                new ESUI.PopWindow("?app_act=oms/waves_record/edit_express_code&waves_record_id_list="+ids.toString(), {
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

    }

    //自动匹配物流单号
    function btn_init_edit_express_no(){
            get_checked(false, $(this), function(ids){
                new ESUI.PopWindow("?app_act=oms/waves_record/edit_express_no&waves_record_id_list="+ids.toString(), {
                    title: "自动匹配物流单号",
                    width:800,
                    height:600,
                    onBeforeClosed: function() {
                    },
                    onClosed: function(){
                        //刷新数据
                        tableStore.load()
                    }
                }).show()
            })
    }

    /********************************************/
    //批量获取获取云栈热敏物流 by:weichuan.hua
    function btn_init_opt_thermal_all(ids) {
        get_checked(false, $(this), function (ids) {
            parent.BUI.use('bui/overlay', function (Overlay) {
                var dialog = new Overlay.Dialog({
                    title: '批量获取云栈热敏物流',
                    width: 300,
                    height: 130,
                    mask: true,
                    buttons: [
                        {
                            text: '',
                            elCls: 'bui-grid-cascade-collapse',
                            handler: function () {
                                this.close();
                            }
                        }
                    ],
                    bodyContent: '获取热敏物流数据中，请稍后...'
                });
                dialog.show();
            });
            check_all_status_multi(ids);
        });
    }

    //批量验证是否以获取过云栈物流
    function check_all_status_multi(waves_record_ids) {
        $.post('?app_act=oms/deliver_record/check_all_status_multi', {'waves_record_ids': waves_record_ids}, function (data) {
            if (data.status == '1') {
                get_waybill_multi(waves_record_ids, 1, 2);
            } else {
                show_status_multi(data.data, waves_record_ids, 2);
            }
        }, 'json');
    }

    //批量调用云栈接口
    function get_waybill_multi(ids, type, print_type) {
        var params = {'waves_record_ids': ids, type: type, print_type: print_type};
        $.post('?app_act=oms/deliver_record/tb_wlb_waybill_get_multi', params, function (data) {
            parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
            parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
            var type = (data.status == 1) ? 'success' : 'error';
            BUI.Message.Alert(data.message, function () {
                tableStore.load();
            },type);
        }, "json");
    }

    //存在获取过的订单给出提示
    function show_status_multi(data,ids,type){
        parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
        parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
        BUI.Message.Show({
            title: '获取云栈热敏',
            msg: "订单号<br/>"+ data + "<br/>已获取云栈热敏，是否再次获取？",
            icon: 'question',
            buttons: [
                {
                    text: '是',
                    elCls: 'button button-primary',
                    handler: function () {
                        get_waybill_multi(ids, 1, type);
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

/*****************************************/
    
    function get_waybill(ids, type, print_type, wavesRecordId, id_array){
        $.post('?app_act=oms/deliver_record/tb_wlb_waybill_get', {waves_record_id: wavesRecordId,record_ids: ids, type: type, print_type: print_type}, function(data) {
            parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
            parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
            //console.log(data);
            if(data.status == 1) {
                tableStore.load();
            } else {
                var msg = data.message;
                $.each(data.data,function(i,k){
                    msg+=k;
                });
                
                if (msg.substring(msg.length - 1) == '!') {
//                    msg = '仅部分获取成功';
//                    alert(msg);
                    num_for_success = $.trim(msg.substring(7,11));
                    num_for_faild = $.trim(msg.substring(msg.length - 3,msg.length - 8));
                    success_num += parseInt(num_for_success);
                    faild_num += parseInt(num_for_faild);
                    counter ++;
                    if (counter == id_array.length) {
                        msg = '获取成功：'+ success_num +' 条,获取失败：'+ faild_num +' 条!';
                        BUI.Message.Alert(msg, 'error');
                        counter = 0 ;
                        success_num = 0;
                        faild_num = 0;
                    }
                    
                } else {
                    BUI.Message.Alert(msg, 'error');
                }
            }
       }, "json");
    }    
    
    
//一键获取云栈热敏物流
    function thermal_all (wavesRecordId,id_array) {
            parent.BUI.use('bui/overlay', function (Overlay) {
                var dialog = new Overlay.Dialog({
                    title: '批量获取云栈热敏物流',
                    width: 300,
                    height: 130,
                    mask: true,
                    buttons: [
                        {
                            text: '',
                            elCls: 'bui-grid-cascade-collapse',
                            handler: function () {
                                this.close();
                            }
                        }
                    ],
                    bodyContent: '获取热敏物流数据中，请稍后...'
                });
                dialog.show();
            });
            check_all_status(wavesRecordId,id_array);
        };
        
//状态判断
        function check_all_status(wavesRecordId,id_array){
        $.post('?app_act=oms/deliver_record/check_all_status', {waves_record_id: wavesRecordId}, function(data) {
            if(data.status == '1'){
               check_express_type(wavesRecordId, 1, wavesRecordId,id_array);
            }else{
                show_status(data.data,wavesRecordId,1, wavesRecordId,id_array);
            }
        },'json');
    }
        
    function show_status(data,ids,type, wavesRecordId,id_array){
        parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
        parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
        check_express_type(ids, type, wavesRecordId, id_array);
//        BUI.Message.Show({
//            title: '获取云栈热敏',
//            msg: "订单号<br/>"+ data + "<br/>已获取云栈热敏，是否再次获取？",
//            icon: 'question',
//            buttons: [
//                {
//                    text: '是',
//                    elCls: 'button button-primary',
//                    handler: function () {
//                        //get_waybill(ids,type)
//                        check_express_type(ids, type, wavesRecordId);
//                        this.close();
//                    }
//                },
//                {
//                    text: '否',
//                    elCls: 'button',
//                    handler: function () {
//                        this.close();
//                    }
//                }
//            ]
//        });
    }    
        
//检测快递类型
    function check_express_type(ids, id_type, wavesRecordId, id_array){
        var params = {record_ids : ids, id_type : id_type};
        $.post('?app_act=oms/deliver_record/check_express_type', params, function(data) {
//            if(data.status !=1){
//                //普通云栈获取提示更新信息            
//               show_change_msg(ids, id_type, 1, wavesRecordId, id_array);
//            }else{
                //云打印
//                get_waybill(ids, id_type, 2, wavesRecordId, id_array);
            //}
            get_waybill(ids, id_type, 1, wavesRecordId, id_array);
       }, "json");
    }
    

    
     function show_change_msg(ids, id_type, get_type, wavesRecordId, id_array){
        parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
        parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
        parent.BUI.use('bui/overlay', function (Overlay) {
           var dialog = new Overlay.Dialog({
                title: '温馨提示',
                width: 450,
                height: 150,
                mask: true,
                icon: 'warning',
                buttons: [
                {
                    text: '继续获取',
                    elCls: 'button button-primary ',
                    handler: function () {
                        get_waybill(ids, id_type, get_type, wavesRecordId, id_array);
                    }
                },
                {
                    text: '去了解菜鸟云打印',
                    elCls: 'button',
                    handler: function () {
                        window.open('http://operate.baotayun.com:8080/efast365-help/?p=3588');
                        this.close();
                    }
                }
                ],
                bodyContent: "<p style='font-size: 16px;text-align:center;color:red'>您现在使用的已下线的面单获取模式，请尽快切换到全新的菜鸟云打印！</p>"
            });
            dialog.show();
        });
    }
        
        
        
    function print_default(t, ids,print_type=1) {
        if(new_clodop_print == 1 || print_type == 2){
            var print_templates_code = 'oms_waves_record_new';
            if(print_type ==2){
                print_templates_code = 'oms_waves_record_clothing';
            }
            new ESUI.PopWindow("?app_act=oms/deliver_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&print_templates_code="+print_templates_code+"&record_ids="+ids, {
                title: "波次单打印",
                width: 500,
                height: 220,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show()
        }else{
        var url = '?app_act=sys/flash_print/do_print&template_id=27&model=oms/WavesRecordModel&typ=default&template_code=oms_waves_record&record_ids='+ids
        var window_is_block = window.open(url)
        if (null == window_is_block) {
            alert("您的浏览器阻止了打印发货单的新窗口,请在浏览器的阻止提示处选择允许弹出新窗口")
        }
    }
    }

    // 打印波次单(商品)
    function btn_init_opt_print_goods() {
         get_checked(false, $(this), function(ids){
             ids = ids.toString();
             $.post('?app_act=oms/waves_record/mark_print', {wave_record_ids: ids}, function(data) {
                 print_default("oms_waves_record", ids)
             })
        })
    }
    //打印波此单（服装特性）
    function btn_opt_print_goods_clothing(){
        get_checked(false, $(this), function(ids){
            ids = ids.toString();
            $.post('?app_act=oms/waves_record/check_is_print_record&app_fmt=json'+"&is_print_error=1",{wave_record_ids: ids},function(data){
                if(data.status < 1){
                    BUI.Message.Confirm(data.message,function(){
                        if(data.data.success.length <= 0) return false;
                        var suc_ids = data.data.success;
                        $.post('?app_act=oms/waves_record/mark_print', {wave_record_ids: suc_ids}, function(data) {
                            print_default("oms_waves_record", suc_ids,2)
                        })
                    })
                }else{
                    $.post('?app_act=oms/waves_record/mark_print', {wave_record_ids: ids}, function(data) {
                        print_default("oms_waves_record", ids, 2)
                    })
                }
            },'json')
        })
    }

    //打印快递单
    function btn_init_opt_print_express(){
        get_checked(false, $(this), function(ids){
                //TODO:打印
            var params = {record_ids: ids}
            $.post("?app_act=oms/waves_record/get_deliver_record_ids",params, function(data) {
                if(data.status == 1){
                    print_express(ids.toString(), data.data.toString())
                }else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json");
        })
    }
    
    //批量取消波次单
    function btn_opt_cancel_waves () {
        get_checked(false, $(this), function(ids){
            BUI.Message.Confirm('确认要取消波次单吗？',function(){
                var params = {waves_record_id:ids, do_type:'pl' };
                $.post("?app_act=oms/waves_record/opt_cancel_waves",params, function(data) {
                    if (data.status == 1) {                  
                        BUI.Message.Alert(data.message, 'success');
                        //刷新数据
                        tableStore.load();
                    }else{
                        BUI.Message.Alert(data.message, 'error');
                    }
                }, "json");
            },'question');           
        });
    }
    
    //验证是否重复打印发货单
      function print_sellrecord(){
        get_checked(false, $(this), function(ids){
            var params = {record_ids: ids};
            $.post("?app_act=oms/waves_record/get_deliver_record_ids",params, function(data) {
                if(data.status == 1){
                    var check_url = "?app_act=oms/deliver_record/check_is_print_sellrecord&app_fmt=json";
                    $.post(check_url, {deliver_record_ids: data.data.toString()}, function(ret){
                        if(ret.status == -2){
                            BUI.Message.Alert('单据异常，可打印单据为0', function () {
                                tableStore.load();
                            },'error');
                        } else if (ret.status == -1){
                            BUI.Message.Confirm('存在重复打印发货单，' + ret.data.print_data + "，是否继续打印？", function(){
                                btn_init_opt_print_sellrecord(ret.data.deliver_record_ids,ret.data.sell_record_code);
                            },'question');
                        }else{
                                btn_init_opt_print_sellrecord(ret.data.deliver_record_ids,ret.data.sell_record_code);
                        }
                    },'json');
                }
            }, "json");
        })
    }
  
 
    //打印发货单
    function btn_init_opt_print_sellrecord(deliver_record_ids,sell_record_code){
        //把选中订单标记为已打印
        $.post('?app_act=oms/sell_record/mark_sell_record_print',{record_ids:sell_record_code}, function(data) {

        }, "json");
        if(new_clodop_print == 1){
            new ESUI.PopWindow("?app_act=oms/deliver_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&print_templates_code=deliver_record&record_ids="+deliver_record_ids, {
                title: "发货单打印",
                width: 500,
                height: 220,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show()
        } else {
            if(print_delivery_record_template == 1){
                var u = '?app_act=tprint/tprint/do_print&print_templates_code=deliver_record&record_ids='+deliver_record_ids;
                $("#print_iframe").attr('src',u);
            } else {
                 var u = '?app_act=sys/flash_print/do_print'
                 u += '&template_id=5&model=oms/DeliverRecordModel&typ=default&record_ids='+deliver_record_ids;
                 var window_is_block = window.open(u);
                 if (null == window_is_block) {
                     alert("您的浏览器阻止了打印发货单的新窗口,请在浏览器的阻止提示处选择允许弹出新窗口");
                 }
            }
        }
    }

    function print_express(wave_record_ids, deliver_record_ids){
        var param = '';
        var check_url = "?app_act=oms/deliver_record/check_is_print_express&app_fmt=json";
        $.post(check_url, {deliver_record_ids: deliver_record_ids}, function(ret){
            if(ret.status == -2){
                BUI.Message.Alert('单据异常，可打印单据为0', function () {
                    tableStore.load();
                },'error');
            } else if (ret.status == -1){
                BUI.Message.Confirm('存在重复打印快递单，' + ret.data.print_data + "，是否继续打印？", function(){
                    param += "&deliver_record_ids=" + ret.data.deliver_record_ids;
                    check_action_print_express(param, ret.data.deliver_record_ids, wave_record_ids);
                },'question');
            }else{
                param += "&deliver_record_ids=" + ret.data.deliver_record_ids;
                check_action_print_express(param, ret.data.deliver_record_ids, wave_record_ids);
            }
        },'json');
    }
    
    function check_action_print_express(param, deliver_record_ids, wave_record_ids){
        var check_url = "?app_act=oms/deliver_record/check_express_type";
        $.post(check_url, {record_ids:deliver_record_ids, id_type: 0}, function(ret){
            var result = JSON.parse(ret);
            action_print_express(param, result.data, deliver_record_ids, wave_record_ids);
        })
    }
    
    var p_time = 0;
    function action_print_express(param, print_type, deliver_record_ids, wave_record_ids){
        if(print_type == 'cloud'){
            param = param + '&print_type=cainiao_print';
        }
        var id = "print_express" + p_time;
        if(new_clodop_print == 1 && print_type != 'cloud' && print_type != 'oldcloud'){
            new ESUI.PopWindow("?app_act=oms/deliver_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&record_ids=" + deliver_record_ids + "&waves_record_ids=" + wave_record_ids + "&is_print_express=1" + "&frame_id=" + id, {
                title: "快递单打印",
                width: 500,
                height: 220,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show()
        } else {
            var url = "?app_act=oms/deliver_record/print_express&iframe_id=" + id + "&waves_record_ids=" + wave_record_ids;
	        if(deliver_record_ids != ""){
	            url += "&deliver_record_ids=" + deliver_record_ids
	        }
	        var iframe = $('<iframe id="'+id+' width="0" height="0"></iframe>').appendTo('body');
	        iframe.attr('src',url);
        }
         p_time++;
    }

    //数据行双击打开新页面显示详情
    function showDetail(index, row) {
        var url = '?app_act=oms/waves_record/view&waves_record_id=' + row.waves_record_id;
        openPage(window.btoa(url),url,'波次拣货单');
    }

    function do_delete(_index, row) {
        $.ajax({ type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('oms/waves_record/do_delete');?>', data: {waves_record_id: row.waves_record_id},
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    function do_cancel(_index, row) {
        new ESUI.PopWindow("?app_act=oms/waves_record/edit_remark&waves_record_id=" +row.waves_record_id, {
                    title: "取消原因",
                    width: 500,
                    height: 225,
                    onBeforeClosed: function () {
                    },
                    onClosed: function () {
                        //刷新数据
                        tableStore.load()
                    }
                }).show()
    }

    function do_accept(_index, row) {
    	if(wave_check == '1')
        {
	        new ESUI.PopWindow("?app_act=oms/waves_record/accept&waves_record_id="+row.waves_record_id, {
	            title: "验收 - "+row.record_code,
	            width:800,
	            height:600,
	            onBeforeClosed: function() {
	            },
	            onClosed: function(){
	                //刷新数据
	                tableStore.load();
	            }
	        }).show()
        }else{
       	 //强制验收
      	   var params = {waves_record_id: row.waves_record_id,is_scan:0};
             $.post("?app_act=oms/waves_record/accept_action", params, function(data){
                 if(data.status != 1){
                     messageBox(data.message) // $("#msg").html(data.message)
                 } else {

                     BUI.Message.Alert('强制验收成功',function(){
                     	//刷新数据
      	                tableStore.load();
                     },'info');

                 }
             }, "json")
        }
    }

    function get_is_print(value, row, index) {
        var print_waves = (row.is_print_waves == 0) ? "未打印" : "已打印";
        var str = '';
        str += "快递单" + row.html_print_express + "<br />";
        str += "发货单" + row.html_print_sellrecord+"<br/>";
        str += "波次单" + print_waves;
        return str;
    }
        function get_status(value, row, index){
             var str = '';
             if(row.is_accept==1){
                str +="已验收"+"<br />";
             }else{
                  str +="未验收"+"<br />";
             }
          if(row.is_cancel==0){
              if(row.is_deliver==1){
                   str +="已发货";
             }else if (row.is_deliver==2){
                   str +="部分发货";
             } else {
            	   str +="未发货";
             }
          }else{
            	   str +="已取消";
             }

        return str;
    }


    /**
     *批量分配拣货员
     */
    function distribute_pick_member(){
        get_checked(false, $(this), function(ids){
            new ESUI.PopWindow("?app_act=oms/waves_record/distribute_pick_member&waves_record_id="+ids.toString(), {
                title: "批量分配拣货员",
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
    }
</script>
<iframe src="" id="print_iframe" style="width:0px;height:0px;" ></iframe>