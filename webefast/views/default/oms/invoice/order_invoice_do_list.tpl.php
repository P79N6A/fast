<style>
    .like_link{
        text-decoration:underline;
        color:#428bca; 
        cursor:pointer;
    }
</style>
<?php echo load_js("baison.js,record_table.js,pur.js",true);?>
<?php require_lib('util/oms_util', true);?>
<?php echo load_js('xlodop.js'); ?>
<?php echo load_js('lodop.js'); ?>

<?php echo load_js('jquery.cookie.js')?>

<?php render_control('PageHead', 'head1',
    array('title'=>'订单开票列表',
       
        'ref_table'=>'table'
    ));?>


<?php
$keyword_type = array();
//$keyword_type['deal_code'] = '交易号';
$keyword_type['sell_record_code'] = '订单号';
//$keyword_type['buyer_name'] = '买家昵称';
//$keyword_type['receiver_name'] = '收货人';
//$keyword_type['express_no'] = '快递单号';
//$keyword_type['goods_name'] = '商品名称';
//$keyword_type['goods_code'] = '商品编码';
//$keyword_type['barcode'] = '商品条形码';
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
     'show_row' => 3,
    'fields' => array (
	    array(
	    	'label' => array('id'=>'keyword_type','type'=>'select','data'=>$keyword_type),
	    	'type' => 'input',
	    	'title'=>'',
	    	'data'=>$keyword_type,
	    	'id' => 'keyword',
            'help' => '可以支持以中、英文逗号间隔的多条订单号查询',
	    ),
          array(
            'label' => '发货日期',
            'type' => 'group',
            'field' => 'daterange1',
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
            'label' => '企业名称',
            'type' => 'select_multi',
            'id' => 'nsrmc',
            'data' =>load_model('oms/invoice/JsFapiaoModel')->get_nsrmc(),
             
        ),
    )
) );
?>


<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '待开票', 'active' => true, 'id' => 'tabs_wait_invoice'),// 默认选中active=true的页签
	array('title' => '已开票', 'active' => false, 'id' => 'tabs_yi_invoice'),
        array('title' => '开票失败', 'active' => false, 'id' => 'tabs_error_invoice'),
        array('title' => '全部', 'active' => false, 'id' => 'tabs_all'),
        
    ),
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<div id="TabPage1Contents">
    <div>
        <ul  class="toolbar frontool"  id="ToolBar1">
            
            <li class="li_btns"><button class="button button-primary btn_opt_do_invoice"  onclick="opt_do_invoice()">批量开票</button></li>
            <li class="li_btns"><button class="button button-primary btn_opt_finish_invoice" onclick="opt_finish_invoice()">批量结案</button></li>
            
            <div class="front_close">&lt;</div>
        </ul>
        <script>
			$(function(){
				var custom_opts = $.parseJSON('[{"id":"opt_do_invoice","custom":"opt_do_invoice"}]','[{"id":"opt_finish_invoice","custom":"opt_finish_invoice"}]');
				for(var j in custom_opts){
				    var g = custom_opts[j];
				    $("#ToolBar2 .btn_"+g['id']).click(eval(g['custom']));
				}
			});
		</script>
    </div>
    	<div>
		<ul class="toolbar frontool" id="ToolBar2">
                     
		   <li class="li_btns"><button class="button button-primary btn_opt_do_invoice">批量开票</button></li>
                   <li class="li_btns"><button class="button button-primary btn_opt_finish_invoice" onclick="opt_finish_invoice()">批量结案</button></li>
                   
		    <div class="front_close">&lt;</div>
		</ul>
		<script>
			$(function(){
				var custom_opts = $.parseJSON('[{"id":"opt_invoice_do","custom":"opt_do_invoice"}]','[{"id":"opt_finish_invoice","custom":"opt_finish_invoice"}]');
				for(var j in custom_opts){
				    var g = custom_opts[j];
				    $("#ToolBar2 .btn_"+g['id']).click(eval(g['custom']));
				}
			});
		</script>
	</div>
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
</div>
<?php
//$expressList = oms_opts2_by_tb('base_express', 'express_code', 'express_name', array(), 2);
//$storeList = oms_opts2_by_tb('base_store', 'store_code', 'store_name', array('status'=>1), 2);
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
                    array('id'=>'invoice_zheng', 'title' => '开票', 'callback'=>'do_invoice','show_cond' => "obj.is_invoice==0 || obj.is_red==2"),
                    array('id'=>'invoice_hong', 'title' => '开红票', 'callback'=>'do_invoice','show_cond' => "obj.is_invoice==2 && obj.is_red==0",),
                    array('id'=>'finish_invoice', 'title' => '结案', 'callback'=>'finish_invoice','show_cond' => "obj.invoice_status==1",'confirm'=>'结案之后将无法对此订单再开票，是否确定要结案？'),
                ),
            ),
                    array (
                'type' => 'text',
                'show' => 1,
                'title' => '订单编号',
                'field' => 'sell_record_code',
                'width' => '150',
                'align' => '',
                //'format_js' => array(
	               // 'type' => 'html',
	                //'value'=>"<a param1=\"{sell_record_id}\" class=\"sell_record_view\" href=\"javascript:void(0)\">{record_code}</a>",
                //)
            ),
                  array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'deal_code_list',
                'width' => '150',
                'align' => '',
               // 'format_js' => array('type' => 'function','value'=>'get_is_print')
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

            array (
                'type' => 'text',
                'show' => 1,
                'title' => '发票类型',
                'field' => 'invoice_type',
                'width' => '80',
                'align' => '',
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '开票抬头',
                'field' => 'invoice_title',
                //'format_js'=> array('type'=>'map', 'value'=>$storeList),
                'width' => '100',
                'align' => ''
            ),
             array (
                'type' => 'text',
                'show' => 1,
                'title' => '开票性质',
                'field' => 'invoice_xz',
                //'format_js'=> array('type'=>'map', 'value'=>$storeList),
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '开票金额',
                'field' => 'invoice_amount',
                'format_js' => array(
                    'type' => 'function',
                    'value' => 'set_pay_amount'
                    ),
                
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '已开票金额',
                'field' => 'invoiced_money',
                'width' => '80',
                'align' => ''
            ),
         
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '应收金额',
                'field' => 'payable_money',
                'width' => '80',
                'align' => ''
            ),
      
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '平台优惠',
                'field' => 'discount_money',
                'width' => '80',
                'align' => ''
            ),
             array (
                'type' => 'text',
                'show' => 1,
                'title' => '发货日期',
                'field' => 'delivery_date',
                'width' => '80',
                'align' => '',
            ),
             array (
                'type' => 'text',
                'show' => 1,
                'title' => '开票日期',
                'field' => 'invoice_time',
                'width' => '80',
                'align' => '',
            ),
                array (
                'type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'status',
                'width' => '80',
                'align' => '',
            ),
                array (
                'type' => 'text',
                'show' => 1,
                'title' => '日志',
                'field' => 'invoice_log',
                'width' => '80',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'oms/invoice/OmsSellInvoiceModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'invoice_id',
    'export' => array('id' => 'exprot_list', 'conf' => 'invoice_list', 'name' => '开票列表','export_type' => 'file'),
    'params' => array('filter' => array('record_time_start' => date('Y-m-d', strtotime("-2 month")), 'record_time_end' => date('Y-m-d'),'do_list_tab' => $response['do_list_tab'])),
    'CheckSelection' => true,
//    'events' => array(
//        'rowdblclick' => 'billing',
//    ),
) );
?>

<script type="text/javascript">
    
$(document).ready(function() {
	$("#TabPage1 a").click(function() {
        tableStore.load();
    });
    tableStore.on('beforeload', function(e) {
    	e.params.do_list_tab = $("#TabPage1").find(".active").find("a").attr("id");
    	tableStore.set("params", e.params);
    });

})

    //读取已选中项
    function get_checked(f,isConfirm, obj, func){
        var ids = []
        var selecteds = tableGrid.getSelection();
        for(var i in selecteds){
            ids.push(selecteds[i].invoice_id)
        }

        if(ids.length == 0){
            BUI.Message.Alert("请选择订单", 'error');
            return
        }

        if(isConfirm) {
            BUI.Message.Show({
                title : '批量操作',
                msg : '是否执行列表'+obj.text()+'?',
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
        } else if (f=='finish_invoice') {
            BUI.Message.Show({
                title : '批量操作',
                msg : '结案之后将无法对此批订单再开票，是否确定要结案？',
                icon : 'question',
                buttons : [
                    {
                        text:'确认',
                        elCls : 'button button-primary',
                        handler : function(){
                            func.apply(null, [ids])
                        }
                    },
                    {
                        text:'取消',
                        elCls : 'button',
                        handler : function(){
                            this.close();
                        }
                    }
                ]
            });
        }else {
            func.apply(null, [ids])
        }
    }



    //开票
   function do_invoice(index,row) {
                //console.log(row.sell_record_code);
      	      new ESUI.PopWindow("?app_act=oms/invoice/order_invoice/zheng_invoice&sell_record_code="+row.sell_record_code, {
	            title: "开票",
	            width:800,
	            height:450,
	            onBeforeClosed: function() {
	            },
	            onClosed: function(){
	                //刷新数据
	                tableStore.load();
	            }
	        }).show()
        
    }
    
    //批量开票
    function opt_do_invoice(){
        get_checked('',false, $(this), function(ids){
            //console.log(ids);
             var params = {invoice_id: ids}
            $.post("?app_act=oms/invoice/order_invoice/get_sell_record_code",params, function(data) {
                if(data.status == 1){
                    
                    opt_invoice(data.data.toString());
                }else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json");
        })
    }
    
    //批量结案
    function opt_finish_invoice() {
        get_checked('finish_invoice',false, $(this), function(ids){
            $.post("?app_act=oms/invoice/order_invoice/update_finish_status",{invoice_id:ids}, function(data) {
                var type = data.status == 1 ? 'success' : 'error';
                BUI.Message.Alert(data.message, type);
                tableStore.load();
            }, "json");
        })
    }
    //结案
    function finish_invoice(_index,row){
        $.post("?app_act=oms/invoice/order_invoice/update_finish_status",{invoice_id:row.invoice_id}, function(data) {
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            tableStore.load();
        }, "json");
    }
    
    function opt_invoice(sell_record_code){
        
          new ESUI.PopWindow("?app_act=oms/invoice/order_invoice/zheng_invoice&sell_record_code="+sell_record_code, {
	            title: "开票",
	            width:800,
	            height:450,
	            onBeforeClosed: function() {
	            },
	            onClosed: function(){
	                //刷新数据
	                tableStore.load();
	            }
	        }).show()
    }
    
    //修改开票金额
    function set_pay_amount(value, row, index){
        if(row.is_invoice == 0 || row.is_red == 2) {
            return template('<span id="preant_'+row.sell_record_code+'"><span class="like_link" id="'+row.sell_record_code+'_i" onclick =\"get_pay_amount(this,\''+row.sell_record_code+'\',\''+value+'\');\"> {invoice_amount}</span></span>', row);
        } else {
           return template('<span>{invoice_amount}</span>', row);
        }
    }
    
    function get_pay_amount(obj,sell_record_code,value){
       $(obj).html('<input type="text" id="'+sell_record_code+'" value="'+value+'" onblur = \"edit_pay_amount(this,\''+sell_record_code+'\',\''+value+'\');\">');
       $("#"+sell_record_code+'_i').removeAttr('onclick');
       $("#"+sell_record_code).keyup(function (event){
            if(event.keyCode == 13){
                $("#"+sell_record_code).removeAttr('onblur');
                 edit_pay_amount(this,sell_record_code,value);
            }
        });
       $("#"+sell_record_code).focus();
    }
    
    function edit_pay_amount(elm,sell_record_code,value){
        var input_val = $(elm).val();//输入的金额
        if(input_val == ''){
             BUI.Message.Alert('开票金额不能为空', 'error');
             $("#preant_"+sell_record_code).html('<span class="like_link" id="'+sell_record_code+'_i" onclick =\"get_pay_amount(this,\''+sell_record_code+'\',\''+value+'\');\">'+value+'</span>');
             return;
         }
         if(input_val <= 0){
             BUI.Message.Alert('开票金额必须大于0', 'error');
              $("#preant_"+sell_record_code).html('<span class="like_link" id="'+sell_record_code+'_i" onclick =\"get_pay_amount(this,\''+sell_record_code+'\',\''+value+'\');\">'+value+'</span>');
             return;
         }
         var reg = /^[+]{0,1}(\d+)$|^[+]{0,1}(\d+\.\d+)$/;
         var preg = /(^[1-9]([0-9]+)?(\.[0-9]{1,2})?$)|(^(0){1}$)|(^[0-9]\.[0-9]([0-9])?$)/  //验证小数点两位的数字
         if(!reg.test(input_val)){
             BUI.Message.Alert('开票金额必须为数字', 'error');
              $("#preant_"+sell_record_code).html('<span class="like_link" id="'+sell_record_code+'_i" onclick =\"get_pay_amount(this,\''+sell_record_code+'\',\''+value+'\');\">'+value+'</span>');
             return;
         }
         if(!preg.test(input_val)){
             BUI.Message.Alert('正数不能以0开头,并且小数点后不能超过两位', 'error');
              $("#preant_"+sell_record_code).html('<span class="like_link" id="'+sell_record_code+'_i" onclick =\"get_pay_amount(this,\''+sell_record_code+'\',\''+value+'\');\">'+value+'</span>');
             return;
         }
        if(input_val != value){
            var params = {sell_record_code: sell_record_code,pay_money:input_val};
            var url = "?app_act=oms/invoice/order_invoice/edit_pay_money";
             $.post(url,params, function(ret) {
                      var type = ret.status == 1 ? 'success' : 'error';
                        if (type == 'success') {
                            BUI.Message.Alert('修改成功',type);
                             $("#preant_"+sell_record_code).html('<span class="like_link" id="'+sell_record_code+'_i" onclick =\"get_pay_amount(this,\''+sell_record_code+'\',\''+input_val+'\');\">'+input_val+'</span>');
                           
                        } else {
                            BUI.Message.Alert(ret.message, type);
                            $("#preant_"+sell_record_code).html('<span class="like_link" id="'+sell_record_code+'_i" onclick =\"get_pay_amount(this,\''+sell_record_code+'\',\''+value+'\');\">'+value+'</span>');
                        }
                        tableStore.load();
            }, "json");
        }
        $("#preant_"+sell_record_code).html('<span class="like_link" id="'+sell_record_code+'_i" onclick =\"get_pay_amount(this,\''+sell_record_code+'\',\''+value+'\');\">'+value+'</span>');
    }
</script>
