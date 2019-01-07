<style type="text/css">
    .well {
        min-height: 0px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '支付宝流水核销查询',
    'links' => array(
    ),
    'ref_table' => 'table'
));
?>
<?php 
$ym = array();
$firstday = date("Y-m-01");
for($i=0;$i<=6;$i++){
	if ($i == 0){
		$v = date('Y-m');
		$ym[$v] = date('Y年-m月');
	}else{
		//$v = date('Y-m',strtotime("-{$i} month"));
                $v = date("Y-m",strtotime("$firstday -{$i} month"));
		//$ym[$v] = date('Y年-m月',strtotime("-{$i} month"));
                $ym[$v] = date('Y年-m月',strtotime("$firstday -{$i} month"));
	}
}
?>
<?php

render_control('SearchForm', 'searchForm', array(
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
    'fields' => array(
		    array(
	    		'label' => '账期',
	    		'type' => 'select',
	    		'id' => 'account_month_ym',
	    		'data' => array_from_dict($ym),
		    ),
		    array(
		    		'label' => '交易号',
		    		'title' => '',
		    		'type' => 'input',
		    		'id' => 'deal_code'
		    ),
		    array(
		    		'label' => '店铺',
		    		'title' => '',
		    		'type' => 'select_multi',
		    		'id' => 'shop_code',
		    		'data' => load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code('taobao')
		    ),
        array(
            'label' => '科目',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'account_item',
            'data' => load_model('acc/ApiTaobaoAlipayModel')->get_all_account_item('temp')
        )
	        
    )
));
?>


<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
    	array('title' => '全部', 'active' => false, 'id' => 'all'),
		array('title' => '未核销', 'active' => true, 'id' => 'no_check'),
        array('title' => '部分核销', 'active' => false, 'id' => 'part_check'),
        array('title' => '已核销', 'active' => false, 'id' => 'have_check'),
       
        
       
    ),
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<span id="total_in" style="color:red;">收入合计:<?php echo $response['total']['in_je']?></span>
<span id="total_out" style="color:red;">支出合计:<?php echo $response['total']['out_je']?></span>
<div id="TabPage1Contents">
    <div>
    </div>
    <div>
        <ul  class="toolbar frontool"  id="ToolBar2">
            <?php if (load_model('sys/PrivilegeModel')->check_priv('acc/api_taobao_alipay/do_check_account_muilt')) {?>
                <li class="li_btns"><button class="button button-primary btn_opt_check" >批量人工核销</button></li>
            <?php }?>
        </ul>
        <script>
            $(function () {
                var custom_opts = $.parseJSON('[{"id":"opt_check","custom":"btn_init_opt_check"}]');
                for (var j in custom_opts) {
                    var g = custom_opts[j];
                    $("#ToolBar2 .btn_" + g['id']).click(eval(g['custom']));
                }
                tools();
            });
        </script>
    </div>
</div>
<?php

render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
	        array (
	        		'type' => 'button',
	        		'show' => 1,
	        		'title' => '操作',
	        		'field' => '_operate',
	        		'width' => '100',
	        		'align' => '',
	        		'buttons' => array (
	        			array('id'=>'create_out_record', 'title' => '人工核销', 'callback'=>'check_account','show_cond'=>'obj.check_accounts_status == 0 || obj.check_accounts_status == 50'),
	        		),
	        ),
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => '交易号',
	        		'field' => 'deal_code',
	        		'width' => '140',
	        		'align' => ''
	        ),
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => '收入',
	        		'field' => 'in_amount',
	        		'width' => '100',
	        		'align' => ''
	        ),
	        
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => '支出',
	        		'field' => 'out_amount',
	        		'width' => '100',
	        		'align' => ''
	        ),
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => '科目',
	        		'field' => 'account_item_txt',
	        		'width' => '120',
	        		'align' => ''
	        ),
            array(
            		'type' => 'text',
            		'show' => '1',
            		'title' => '店铺',
            		'field' => 'shop_code_name',
            		'width' => '160',
            		'align' => ''
            ),
            
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '支付宝订单号',
            		'field' => 'alipay_order_no',
            		'width' => '150',
            		'align' => ''
            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '支付宝流水创建时间',
            		'field' => 'create_time',
            		'width' => '150',
            		'align' => ''
            ),
            
            array(
	            'type' => 'text',
	            'show' => 1,
	            'title' => '核销状态',
	            'field' => 'check_accounts_status_txt',
	            'width' => '80',
	            'align' => '',
            
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '核销时间',
                'field' => 'check_accounts_time',
                'width' => '100',
                'align' => '',
                'editor' => "{xtype : 'date',datePicker : {showTime : true},editableFn : function(value,record){
              if(record.check_accounts_status==40){
                return true;
              }
              return false;
            }}",
                // 'format_js' => array('type' => 'function', 'value' => "edit_accounts_time",)
//                'format_js' => array(
//                    'type' => 'html',
//                    'value' => '<a href=javascript:view({sell_return_id})>{sell_return_code}</a>',
//                ),
            ),
            array(
	            'type' => 'text',
	            'show' => 1,
	            'title' => '核销备注',
	            'field' => '',
	            'width' => '120',
	            'align' => '',
            ),
            array(
	            'type' => 'text',
	            'show' => 1,
	            'title' => '核销操作人',
	            'field' => 'check_accounts_user_code',
	            'width' => '100',
	            'align' => ''
            ),
            
        )
    ),
    'dataset' => 'acc/ApiTaobaoAlipayModel::get_search_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'aid',
    'export' => array('id' => 'exprot_list', 'conf' => 'alipay_search_list', 'name' => '支付宝流水核销查询','export_type' => 'file'),
    'init' => 'nodata',
    'CheckSelection' => true,
    'CellEditing' => true,
//    'events' => array(
//        'rowdblclick' => 'showDetail',
//    ),
));
?>

<?php echo load_js("pur.js",true);?>
<script type="text/javascript">
$(function(){
	//TAB选项卡
    $("#TabPage1 a").click(function() {
        if($('.nodata').length>0){
            $('.nodata').remove();
        }
        tableStore.load();
        total_amount_search();
    });
    tableStore.on('beforeload', function(e) {
        e.params.check_tab = $("#TabPage1").find(".active").find("a").attr("id");
        tableStore.set("params", e.params);
    });

    //编辑列表
    if (typeof tableCellEditing != "undefined") {
        tableCellEditing.on('accept', function (record, editor) {
            var editValue = record.editor.__attrVals.editValue,
                editId = record.editor.__attrVals.id,
                _record = record.record;
            var params = {};
//            if (editId == 'editor1') {
//                if (!isPositiveNum(_record.presell_num)) {
//                    BUI.Message.Tip('数量必须为正整数', 'error');
//                    tableStore.load();
//                    return;
//                }
//                if (_record.presell_num == editValue) {
//                    BUI.Message.Tip('数据未变更', 'warning');
//                    tableStore.load();
//                    return;
//                }
//                params.presell_num = _record.presell_num;
//            }

            if (editId == 'editor1') {
                if (_record.check_accounts_time == null) {
                    BUI.Message.Tip('核销时间未设置', 'warning');
                    tableStore.load();
                    return;
                }
                if (_record.check_accounts_status != '40') {
                    BUI.Message.Tip('单据非人工核销状态', 'warning');
                    tableStore.load();
                    return;
                }
                var check_accounts_time = getFormatTime(_record.check_accounts_time);

                params.check_accounts_time = check_accounts_time;
            }
            params.aid = _record.aid;

            $.post('?app_act=acc/api_taobao_alipay/update_alipay_info', params, function (ret) {
                if (ret.status == 1) {
                    BUI.Message.Tip(ret.message, 'success');
                    tableStore.load();
                    table_logStore.load();
                } else if (ret.status == 2) {
                    BUI.Message.Tip(ret.message, 'warning');
                    tableStore.load();
                } else {
                    BUI.Message.Tip(ret.message, 'error');
                }
            }, 'json');

        });
    }

});

//将时间对象格式化为Y-m-d H:i:s
function getFormatTime(timestamp) {
    var d = new Date(timestamp);
    var date = (d.getFullYear()) + "-" +
        (d.getMonth() + 1) + "-" +
        (d.getDate()) + " " +
        (d.getHours()) + ":" +
        (d.getMinutes()) + ":" +
        (d.getSeconds());
    return date;
}

$("#btn-search").click(function(){
	total_amount_search();
});
function total_amount_search(){
	var url = '?app_act=acc/api_taobao_alipay/total_amount_search',
	params = tableStore.get('params');
	var obj = searchFormForm.serializeToObject();
    for(var key in obj){
      params[key] =  obj[key];
	}    
  
    for(var key in params){
        url +="&"+key+"="+params[key];
	}
    var d = {'app_fmt': 'json'};
    $.post(url, d, function(data){
	    $("#total_in").html('收入合计:'+data['in_je']);
	    $("#total_out").html('支出合计:'+data['out_je']);
	 }, "json");
}
function check_account(index, row){
	var d = {"id": row.aid,'app_fmt': 'json'};
	 $.post('<?php echo get_app_url('acc/api_taobao_alipay/do_check_account');?>', d, function(data){
		 var type = data.status == 1 ? 'success' : 'error';
         BUI.Message.Alert(data.message,function(){
             tableStore.load();
         },type);
	 }, "json");
}
function tools(){
    $(".frontool").css({left:'0px'});
    /*$(".front_close").click(function(){
        if($(this).html()=="&lt;"){
            $(".frontool").animate({left:'-100%'},1000);
            $(this).html(">");
            $(this).addClass("close_02").animate({right:'-10px'},1000);
        }else{
            $(".frontool").animate({left:'0px'},1);
            $(this).html("<");
            $(this).removeClass("close_02").animate({right:'0'},1000);
        }
    });*/
}
function btn_init_opt_check() {
    get_checked($(this), function (ids) {
        $.post('<?php echo get_app_url('acc/api_taobao_alipay/do_check_account');?>', {id:ids}, function (data) {
            if (data.status == 1) {
                BUI.Message.Alert(data.message,function(){
                    tableStore.load();
                }, 'info');

            } else {
                BUI.Message.Alert(data.message, 'error');
            }
        }, "json");
    });
}
function get_checked(obj, func, type) {
    var ids = new Array();
    var rows = tableGrid.getSelection();
    if (rows.length == 0) {
        BUI.Message.Alert("请选择未核销的支付宝订单号", 'error');
        return;
    }
    for (var i in rows) {
        var row = rows[i];
        ids.push(row.aid);
    }
    BUI.Message.Show({
        title: '批量操作',
        msg: '是否执行批量人工核销?',
        icon: 'question',
        buttons: [
            {
                text: '是',
                elCls: 'button button-primary',
                handler: function () {
                    func.apply(null, [ids]);
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




</script>

