 <?php           
    
    render_control('PageHead', 'head1', array('title' => '淘宝分销退单',
        'links' => '',
        'ref_table' => 'table'
    ));
  ?>
<?php
$status = array(
		'all' => '全部',
		'0' => '否',
		'1' => '是',
);
$status = array_from_dict($status);
$keyword_type = array();
$keyword_type['purchase_order_id'] = '交易号';
$keyword_type['sub_order_id'] = '退单编号';
$keyword_type['refund_record_code'] = '系统退单号';
$keyword_type = array_from_dict($keyword_type);
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
        ),
       array (
            'label' => '分销商',
            'type' => 'input',
            'id' => 'distributor_nick'
        	),
        	
	    array (
	    		'label' => '店铺',
	    		'type' => 'select_multi',
	    		'id' => 'shop_code',
	    		'data'=>load_model('base/ShopModel')->get_purview_tbfx_shop(),
	    ),
        array (
            'label' => '转单状态',
            'type' => 'select',
            'id' => 'is_change',
            'data'=>oms_opts_by_md('oms/SellRecordModel', 'tran_status',1),
        ),
        
	    array(
            'label' => '申请时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'refund_create_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'refund_create_time_end', 'remark' => ''),
            )
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
       <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="set_tran()">批量转单</button></li>
    <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="set_traned()">批量置为已转单</button></li>

    <div class="front_close">&lt;</div>
</ul>


<?php render_control ( 'DataTable', 'table', array (
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
                    array('id'=>'tran', 'title' => '转退单', 'callback'=>'td_tran','confirm'=>'确认要转退单吗？','show_cond' => 'obj.is_change <= 0'),
                    array('id'=>'traned', 'title' => '设为已处理', 'callback'=>'td_traned','confirm'=>'确认要设为已处理吗？','show_cond'=>'obj.is_change <= 0' ),
            ),
            ),
                array (
                'type' => 'text',
                'show' => 1,
                'title' => '退单编号',
                'field' => 'sub_order_id',
                'width' => '120',
                'align' => 'center'
            ),
           array (
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'purchase_order_id',
                'width' => '120',
                'align' => 'center'
            ),
           array (
                'type' => 'text',
                'show' => 1,
                'title' => '申请时间',
                'field' => 'refund_create_time',
                'width' => '100',
                'align' => ''
            ),
             array (
                'type' => 'text',
                'show' => 1,
                'title' => '分销商',
                'field' => 'distributor_nick',
                'width' => '100',
                'align' => ''
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
                'title' => '退款金额',
                'field' => 'refund_fee',
                'width' => '80',
                'align' => 'center'
            ),
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '退款说明',
                'field' => 'refund_desc',
                'width' => '150',
                'align' => ''
            ),

 
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '转单状态',
                'field' => 'is_change',
                'width' => '80',
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
                'width' => '150',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '系统退单号',
                'field' => 'refund_record_code',
                'width' => '120',
                'align' => 'center',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({refund_record_code})">{refund_record_code}</a>',
                ),
            ),
        )
    ),
    'dataset' => 'api/FxTaobaoRefundModel::get_by_page',
    'queryBy' => 'searchForm',
    'export'=> array('id'=>'exprot_list','conf'=>'api_taobao_fx_refund_do_list','name'=>'淘宝分销退单','export_type' => 'file'),//
    'idField' => 'id',
	    'CheckSelection'=>true,
    //'RowNumber'=>true,
    //'CheckSelection'=>true,
) );
?>
<script type="text/javascript">


    $(function(){
    $(".order_change_fail").click(function(){
        $("#is_change").val(-1);
        $("#refund_create_time_start").val('');
        $("#refund_create_time_end").val('');
        $("#btn-search").click();

    });
});





    //转单
    function td_tran(index, row){
        var d = {"sub_order_id": row.sub_order_id,'app_fmt': 'json'};
        $.post('<?php echo get_app_url('api/api_taobao_fx_refund/td_tran');?>', d, function(data){

            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            if(data.change_fail_num > 0){
                $("#order_change_fail_num").html(data.change_fail_num);
            }
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
				ids += check_id_arr[i].id+",";
			}
			ids = ids.substring(0,ids.length-1);	
		    var d = {"id": ids};
			$.post('<?php echo get_app_url('api/api_taobao_fx_refund/td_traned');?>', d, function(data){
				var type = data.status == 1 ? 'success' : 'error';
				BUI.Message.Alert(data.message, type);
				 tableStore.load();
			}, "json");
		}
	
        function do_tran(){
        		var  ids = '';
			var check_id_arr = tableGrid.getSelection();
			for(var i=0;i < check_id_arr.length;i++){
				ids += check_id_arr[i].sub_order_id+",";
			}
			ids = ids.substring(0,ids.length-1);	
		    var d = {"id": ids};
			$.post('<?php echo get_app_url('api/api_taobao_fx_refund/do_tran');?>', d, function(data){
				var type = data.status == 1 ? 'success' : 'error';
				BUI.Message.Alert(data.message, type);
				 tableStore.load();
			}, "json");    
        }
        
        
    function view(sell_return_code) {
        var url = '?app_act=oms/sell_return/after_service_detail&sell_return_code=' +sell_return_code
        openPage(window.btoa(url),url,'订单详情');
       }
    //单个标记为已转单
    function td_traned(index, row){
        var d = {"sub_order_id": row.sub_order_id};
        $.post('<?php echo get_app_url('api/api_taobao_fx_refund/td_traned_one');?>', d, function(data){
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            tableStore.load();
        }, "json");
    }
	

</script>



