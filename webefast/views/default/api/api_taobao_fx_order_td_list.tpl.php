
<!--<div class="page-header1" style="width: 98%; display: block; clear: both; position: fixed; top:0px; left:0px; background-color: #FFF; padding: 4px 1%; z-index: 9999; box-shadow:0px 0px 5px #ccc;">
	<span class="page-title"><h2>淘宝分销订单列表</h2></span>
         <button class="button button-primary" onclick="javascript:location.reload();"><i class="icon-refresh icon-white"></i> 刷新</button>
	<span class="page-link">
            <span class="page-link">
        <a href="javascript:PageHead_show_dialog('?app_act=oms/sell_record/import_fenxiao_trade', '淘宝分销订单导入', {w:800,h:600})" class="button button-primary">淘宝分销订单导入</a>
    </span>-->
<style>
    #record_time_start, #record_time_end, #pay_time_start, #pay_time_end{width: 100px;}
</style>>    
 <?php
    //array('url' => 'oms/sell_record/import_fenxiao_trade', 'is_pop' => true, 'title' => '淘宝分销订单导入'),
    $links = array(array('url' => 'api/api_taobao_fx_order/down&app_show_mode=pop', 'title' => '订单下载', 'is_pop' => true, 'pop_size' => '800,500'),);
    render_control('PageHead', 'head1', array('title' => '淘宝分销订单列表',
        'links' => $links,
        'ref_table' => 'table'
    ));
  ?>
	<!-- <span class="action-link">
    <a href="javascript:PageHead_show_dialog('?app_act=oms/api_order/down&app_show_mode=pop', '一键下载', {w:800,h:600})" class="button button-primary">

            一键下载</a>
        </span>
       <span class="action-link">

       <a href="javascript:PageHead_show_dialog('?app_act=oms/api_order/change&app_show_mode=pop', '一键转单', {w:800,h:600})" class="button button-primary">

            一键转单</a>
        </span>
      <a href="javascript:PageHead_show_dialog('?app_act=oms/api_order/change&app_show_mode=pop', '一键转单', {w:800,h:600})" class="button button-primary">

            下载并转单</a>
        </span> -->
<!--</div>-->
<!--<div class="clear" style="margin-top: 40px; "></div>-->
<?php
//库存同步
$status = array(
		'1' => '是',
		'all' => '全部',
		'0' => '否',
		
);
$record_time_start = date("Y-m-d", strtotime('-3 day')) . ' 00:00:00';
$keyword_type = array();
$keyword_type['fenxiao_id'] = '交易号';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['goods_barcode'] = '商品条形码';
$keyword_type = array_from_dict($keyword_type);
$status = array_from_dict($status);
 render_control ( 'SearchForm', 'searchForm', array (
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

    'fields' => array (
         
       array(
            'label' => array('id'=>'keyword_type','type'=>'select','data'=>$keyword_type),
            'type' => 'input',
            'title'=>'',
            'data'=>$keyword_type,
            'id' => 'keyword',
            'help' => '查询交易号为唯一查询，其他查询条件无效',
        ),
        	
       array (
            'label' => '分销商',
            'type' => 'input',
            'id' => 'distributor_username'
        	),
        	
	    array (
	    		'label' => '店铺',
	    		'type' => 'select_multi',
	    		'id' => 'shop_code',
//	    		'data'=>oms_opts_by_tb('base_shop', 'shop_code', 'shop_name', array()),
                        'data'=>load_model('base/ShopModel')->get_purview_tbfx_shop(),
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
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'pay_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'pay_time_end', 'remark' => ''),
            )
        ),
	    
	    

// 		array (
// 	    		'label' => '销售平台',
// 	    		'type' => 'select',
// 	    		'id' => 'source',
// 	    		'data'=>load_model('base/SaleChannelModel')->get_select()
// 	    ),
	    
        array (
            'label' => '转单状态',
            'type' => 'select',
            'id' => 'is_change',
            'data'=>oms_opts_by_md('oms/SellRecordModel', 'tran_status',1),
        ),

        array (
        		'label' => '是否允许转单',
        		'type' => 'select',
        		'id' => 'is_invo',
        		'data'=>$status,
        ),

    )
) );
?>

<?php if ($response['change_fail_num'] > 0){?>
<span>
	<a name="order_change_fail" class="order_change_fail" style="cursor:pointer">
    	<font color="red"> 转单失败订单(<span id="order_change_fail_num"><?php echo $response['change_fail_num']; ?></span>)</font>
    </a>
</span>
<?php }?>


<ul class="toolbar frontool">
    <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="pl_td_tran()">批量转单</button></li>
    <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="set_traned()">批量置为已转单</button></li>
    <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="set_no_traned()">批量置为未转单</button></li>
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


<?php render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '100',
                'align' => 'center',
                'buttons' => array (
                   array('id'=>'view', 'title' => '详情', 'act'=>'pop:api/api_taobao_fx_order/td_view&ttid={ttid}', 'show_name'=>'详情', 'show_cond'=>'','priv'=>'sys/user/enable', 'pop_size'=>'920,600'),
                   array('id'=>'tran', 'title' => '转单', 'callback'=>'td_tran','confirm'=>'确认要转单吗？','priv'=>'sys/user/enable','show_cond' => 'obj.is_invo == 1  && obj.is_change <= 0'),
                   array('id'=>'traned', 'title' => '置为已转单', 'callback'=>'td_traned','confirm'=>'确认要置为已转单吗？','priv'=>'sys/user/enable','show_cond' =>'obj.is_invo == 1  && obj.is_change <= 0' ),
                ),
            ),
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '120',
                'align' => ''
            ),
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '分销商',
                'field' => 'distributor_username',
                'width' => '120',
                'align' => ''
            ),
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'fenxiao_id',
                'width' => '130',
                'align' => 'center'
            ),
            /*
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '数量',
                'field' => '',
                'width' => '100',
                'align' => ''
            ),
            */
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '买家实付金额',
                'field' => 'buyer_payment',
                'width' => '100',
                'align' => 'center'
            ),
            
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '分销商实付金额',
                'field' => 'distributor_payment',
                'width' => '100',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单下载时间',
                'field' => 'first_insert_time',
                'width' => '100',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单变更时间',
                'field' => 'last_update_time',
                'width' => '100',
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '下单时间',
                'field' => 'created',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '付款时间',
                'field' => 'pay_time',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '邮费',
                'field' => 'post_fee',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '允许转单',
                'field' => 'is_invo',
                'width' => '80',
                'align' => 'center',
                'format_js' => array('type' => 'map_checked')
            ),
 
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '转单状态',
                'field' => 'is_change',
                'width' => '100',
                'align' => 'center',
                'format_js' => array(
                		'type' => 'map',
                		'value' => array(
                			'1'=>'已转单',
                			'0'=>'未转单',
                			'-1'=>'未转单',
                		),
                ),
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '日志',
                'field' => 'change_remark',
                'width' => '130',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '系统分销订单号',
                'field' => 'sell_record_code',
                'width' => '120',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({sell_record_code})">{sell_record_code}</a>',
                ),
            ),
        )
    ),
    'dataset' => 'api/FxTaobaoTradeModel::get_by_page',
    'queryBy' => 'searchForm',
    'export'=> array('id'=>'exprot_list','conf'=>'api_taobao_fx_order_td_list','name'=>'淘宝分销订单','export_type' => 'file'),//
    'idField' => 'id',
    'init' => 'nodata',
	    'CheckSelection'=>true,
    //'RowNumber'=>true,
    //'CheckSelection'=>true,
) );
?>
<script type="text/javascript">
    $("#tid").css('border','1px solid red');
    
    
        $(function(){
            $("#record_time_start").val("<?php echo $record_time_start ?>");
        $(".order_change_fail").click(function(){
            $("#is_change").val(-1);
            $("#pay_time_start").val('');
            $("#pay_time_end").val('');
            $("#record_time_start").val('');
            $("#record_time_end").val('');
            $("#btn-search").click();
                       
        });
    });
    
    
    //转单
    function td_tran(index, row){
        var d = {"ttid": row.ttid,'app_fmt': 'json'};
        $.post('<?php echo get_app_url('api/api_taobao_fx_order/td_tran');?>', d, function(data){

            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
             if(data.change_fail_num > 0){
                $("#order_change_fail_num").html(data.change_fail_num);
            }
            tableStore.load();
        }, "json");
    }

    //标记为已转单
    function td_traned(index, row){
        var d = {"ttid": row.ttid};
        $.post('<?php echo get_app_url('api/api_taobao_fx_order/td_traned');?>', d, function(data){
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
             tableStore.load();
        }, "json");
    }
            function PageHead_show_dialog(_url, _title, _opts) {

            new ESUI.PopWindow(_url, {
                title: _title,
                width:_opts.w,
                height:_opts.h,
                onBeforeClosed: function() {
                    if (typeof _opts.callback == 'function') _opts.callback();
                }
            }).show();
        }

		function set_traned(){
			var  ids = '';
			var check_id_arr = tableGrid.getSelection();
			for(var i=0;i < check_id_arr.length;i++){
				ids += check_id_arr[i].ttid+",";
			}
			ids = ids.substring(0,ids.length-1);	
		    var d = {"ttid": ids};
			$.post('<?php echo get_app_url('api/api_taobao_fx_order/td_traned');?>', d, function(data){
				var type = data.status == 1 ? 'success' : 'error';
				BUI.Message.Alert(data.message, type);
				 tableStore.load();
			}, "json");
		}
		function view(sell_record_code) {
		    var url = '?app_act=oms/sell_record/view&sell_record_code=' +sell_record_code
		    openPage(window.btoa(url),url,'订单详情');
		   }
		
		function set_no_traned(){
                        var  ids = '';
			var check_id_arr = tableGrid.getSelection();
			for(var i=0;i < check_id_arr.length;i++){
				ids += check_id_arr[i].fenxiao_id+",";
			}
			ids = ids.substring(0,ids.length-1);	
		    var d = {"id": ids};
			$.post('<?php echo get_app_url('api/api_taobao_fx_order/td_no_traned');?>', d, function(data){
				var type = data.status == 1 ? 'success' : 'error';
				BUI.Message.Alert(data.message, type);
				 tableStore.load();
			}, "json");
		}
        function pl_td_tran() {
            var ids = '';
            var check_id_arr = tableGrid.getSelection();
            for (var i = 0; i < check_id_arr.length; i++) {
                ids += check_id_arr[i].ttid + ",";
            }
            ids = ids.substring(0, ids.length - 1);
            if (ids.length == 0) {
                BUI.Message.Alert('请勾选需转订单', 'error');
                return;
            }
            console.log(ids);
            var d = {"ttid": ids,'app_fmt': 'json'};
            $.post('<?php echo get_app_url('api/api_taobao_fx_order/td_tran'); ?>', d, function (data) {
                var type = data.status == 1 ? 'success' : 'error';
                BUI.Message.Alert(data.message, type);
                if (data.change_fail_num > 0) {
                    $("#order_change_fail_num").html(data.change_fail_num);
                }
                tableStore.load();
            }, "json");
    }
</script>



