
<div class="page-header1" style="width: 98%; display: block; clear: both; position: fixed; top:0px; left:0px; background-color: #FFF; padding: 4px 1%; z-index: 9999; box-shadow:0px 0px 5px #ccc;">
	<span class="page-title"><h2>淘宝分销订单列表</h2></span>
	<span class="page-link">
	<span class="action-link">
    <a href="javascript:PageHead_show_dialog('?app_act=oms/api_order/down&app_show_mode=pop', '一键下载', {w:800,h:600})" class="button button-primary">

            一键下载</a>
        </span>
       <span class="action-link">

       <a href="javascript:PageHead_show_dialog('?app_act=oms/api_order/change&app_show_mode=pop', '一键转单', {w:800,h:600})" class="button button-primary">

            一键转单</a>
        </span>
      <a href="javascript:PageHead_show_dialog('?app_act=oms/api_order/change&app_show_mode=pop', '一键转单', {w:800,h:600})" class="button button-primary">

            下载并转单</a>
        </span>
    </span>
</div>
<div class="clear" style="margin-top: 40px; "></div>
<?php
//库存同步
$status = array(
		'' => '全部',
		'0' => '否',
		'1' => '是',
);
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
         
       array (
            'label' => '交易号',
            'type' => 'input',
            'id' => 'tid'
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
	    		'data'=>oms_opts_by_tb('base_shop', 'shop_code', 'shop_name', array()),
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
            'label' => '付款时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'pay_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'pay_time_end', 'remark' => ''),
            )
        ),
	    
	    

		array (
	    		'label' => '销售平台',
	    		'type' => 'select',
	    		'id' => 'source',
	    		'data'=>load_model('base/SaleChannelModel')->get_select()
	    ),
	    
        array (
            'label' => '转单状态',
            'type' => 'select',
            'id' => 'is_change',
            'data'=>oms_opts_by_md('oms/SellRecordModel', 'tran_status',1),
        ),

        array (
        		'label' => '是否允许转单',
        		'type' => 'select',
        		'id' => 'status',
        		'data'=>$status,
        ),

    )
) );
?>
<ul class="toolbar frontool">
    <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="set_traned()">批量置为已转单</button></li>
    <!--li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="active('enable')">批量置为未转单</button></li-->
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
                   array('id'=>'view', 'title' => '详情', 'act'=>'pop:oms/sell_record/td_view&id={id}', 'show_name'=>'详情', 'show_cond'=>'','priv'=>'sys/user/enable', 'pop_size'=>'920,600'),
                  //array('id'=>'view', 'title' => '详情', 'act'=>'oms/sell_record/td_view&id={id}', 'show_name'=>'详情', 'show_cond'=>'', 'pop_size'=>'920,600'),
                   array('id'=>'tran', 'title' => '转单', 'callback'=>'td_tran','confirm'=>'确认要转单吗？','priv'=>'sys/user/enable','show_cond' => 'obj.status == 1  && obj.is_change <= 0'),
                   array('id'=>'traned', 'title' => '置为已转单', 'callback'=>'td_traned','confirm'=>'确认要置为已转单吗？','priv'=>'sys/user/enable','show_cond' =>'obj.status == 1  && obj.is_change <= 0' ),
                ),
            ),
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '100',
                'align' => ''
            ),
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '分销商',
                'field' => 'distributor_username',
                'width' => '100',
                'align' => ''
            ),
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'tid',
                'width' => '100',
                'align' => ''
            ),
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '数量',
                'field' => '',
                'width' => '100',
                'align' => ''
            ),
            
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '买家实付金额',
                'field' => 'buyer_payment',
                'width' => '100',
                'align' => ''
            ),
            
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '分销商实付金额',
                'field' => 'distributor_payment',
                'width' => '100',
                'align' => ''
            ),

            array (
                'type' => 'text',
                'show' => 1,
                'title' => '下单时间',
                'field' => 'order_first_insert_time',
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
                'title' => '允许转单',
                'field' => 'status',
                'width' => '80',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
 
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '转单状态',
                'field' => 'is_change',
                'width' => '100',
                'align' => '',
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
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '系统分销订单号',
                'field' => 'sell_record_code',
                'width' => '100',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'oms/ApiOrderModel::get_by_page',
    'queryBy' => 'searchForm',
     'export'=> array('id'=>'exprot_list','conf'=>'api_order_list','name'=>'平台订单'),
    'idField' => 'id',
	    'CheckSelection'=>true,
    //'RowNumber'=>true,
    //'CheckSelection'=>true,
) );
?>
<script type="text/javascript">
    $("#tid").css('border','1px solid red');
    //转单
    function td_tran(index, row){
        var d = {"api_order_id": row.id,'app_fmt': 'json'};
        $.post('<?php echo get_app_url('oms/sell_record/td_tran');?>', d, function(data){

            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            tableStore.load();
        }, "json");
    }

    //标记为已转单
    function td_traned(index, row){
        var d = {"id": row.id};
        $.post('<?php echo get_app_url('oms/sell_record/td_traned');?>', d, function(data){
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
				ids += check_id_arr[i].id+",";
			}
			ids = ids.substring(0,ids.length-1);	
		    var d = {"id": ids};
			$.post('<?php echo get_app_url('oms/sell_record/td_traned');?>', d, function(data){
				var type = data.status == 1 ? 'success' : 'error';
				BUI.Message.Alert(data.message, type);
				 tableStore.load();
			}, "json");
		}
		
		function set_no_traned(){
		
		}
</script>



