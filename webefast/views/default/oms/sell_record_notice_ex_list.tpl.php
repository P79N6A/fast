<style>
    #save_conditions{
        background: #1695ca no-repeat scroll -75px 5px;
        border: medium none;
        color: #fff;
        font-size: 18px;
        height: 28px;
        width: 92px;
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


    .waves-name-select {
        margin-left:5px;
        width: 140px;
        height: 28px;
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
$keyword_type['goods_short_name'] = '商品简称';

$keyword_type = array_from_dict($keyword_type);

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
    'fields' => array(
            array(
            'label' => array('id'=>'keyword_type','type'=>'select','data'=>$keyword_type),
            'type' => 'input',
            'title'=>'',
            'data'=>$keyword_type,
            'id' => 'keyword',
            'help'=>'支持多个，用逗号隔开：商品简称、商品条形码、商品编码',
        ),
                array(
            'label' => '商品数量',
            'type' => 'group',
            'field' => 'goods_num',
            'child' => array(
                array('title' => 'start','type' => 'input','field' => 'goods_num_start','class'=>'input-small'),
                array('pre_title' => '~','type' => 'input','field' => 'goods_num_end','class'=>'input-small'),
            )
        ),
	    array(
    		'label' => '仓库',
    		'type' => 'select',
    		'id' => 'store_code',
    		'data' => load_model('base/StoreModel')->get_store_no_contain_wms(),
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
     		'label' => '分销订单',
     		'type' => 'select',
     		'id' => 'is_fenxiao',
     		'data' => ds_get_select_by_field('is_fenxiao'),
 	    ),     
        array(
            'label' => '配送方式',
            'type' => 'select_multi',
            'id' => 'express_code',
            'data' => ds_get_select('express'),
        ),
	    array(
    		'label' => '货到付款订单',
    		'type' => 'select',
    		'id' => 'is_cod',
    		'data' => ds_get_select_by_field('is_cod'),
	    ),
	    array(
    		'label' => '开票订单',
    		'type' => 'select',
    		'id' => 'is_nvoice',
    		'data' => ds_get_select_by_field('is_nvoice'),
	    ),


//         array(
//         	'label' => '聚划算订单',
//         	'type' => 'select',
//         	'id' => 'is_jhs',
//         	'data' => ds_get_select_by_field('is_jhs'),
//         ),
        array(
        	'label' => '加急订单',
        	'type' => 'select',
        	'id' => 'is_rush',
        	'data' => ds_get_select_by_field('is_rush'),
        ),
        array(
        	'label' => '通知配货时间',
        	'type' => 'group',
        	'field' => 'daterange1',
        	'child' => array(
        		array('title' => 'start', 'type' => 'date', 'field' => 'is_notice_time_start',),
        		array('pre_title' => '~', 'type' => 'date', 'field' => 'is_notice_time_end', 'remark' => ''),
        	)
        ),
        array(
        	'label' => '计划发货时间',
        	'type' => 'group',
        	'field' => 'daterange2',
        	'child' => array(
        		array('title' => 'start', 'type' => 'date', 'field' => 'plan_time_start',),
        		array('pre_title' => '~', 'type' => 'date', 'field' => 'plan_time_end', 'remark' => ''),
        	)
        ),
        array(
        	'label' => '下单时间',
        	'type' => 'group',
        	'field' => 'daterange3',
        	'child' => array(
        		array('title' => 'start', 'type' => 'date', 'field' => 'record_time_start',),
        		array('pre_title' => '~', 'type' => 'date', 'field' => 'record_time_end', 'remark' => ''),
        	)
        ),
        array(
        	'label' => '付款时间',
        	'type' => 'group',
        	'field' => 'daterange4',
        	'child' => array(
        		array('title' => 'start', 'type' => 'date', 'field' => 'pay_time_start',),
        		array('pre_title' => '~', 'type' => 'date', 'field' => 'pay_time_end', 'remark' => ''),
        	)
        ),
    )
));
?>

<table id="sku_set_all" style="margin-bottom: 10px;">
    <tr>
        <td onclick = "link_create_wave()"   id = "sku_num1" class="set_sku_btn active">单SKU分析</td>
        
        <td  style="color: #1695ca"  id = "sku_num2" class="set_sku_btn active">自定义SKU分析</td>
          
        <!--td onclick = "set_sku_num(this,2)"  id = "sku_num2" class="set_sku_btn">双SKU分析</td-->
    </tr>
</table>
<input id="sku_num" type="hidden" name="sku_num"  value="1"/>

<ul class="toolbar frontool">

    <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="create_wave()">生成波次</button> 

        每波次订单数<input type="text" id="order_num" name="order_num" value="50" style="width:60px;" />
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
    });
     function link_create_wave(){
        window.location.href = '?app_act=oms/sell_record_notice/do_list&ES_frmId=oms/sell_record_notice/do_list';
    }
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
                'title' => '条码',
                'field' => 'barcode',
                'width' => '150',
                'align' => ''
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
    $(function() {
        $('#searchForm').append($('#sku_num'));
        tableStore.on('beforeload', function(e) {

            var sort_e = $("#sort").find(".active");
            if (sort_e.length > 0) {
                e.params.is_sort = $("#sort").find(".active").attr("id");
            }
            tableStore.set("params", e.params);
        });
    });
    function create_wave() {
        var order_num = $('#order_num').val();
        var reg = new RegExp(/^\+?[1-9][0-9]*$/);
        if (!reg.test(order_num) || order_num > 500) {
            alert("请输入大于0正数小于500正数!");
            return;
        }
        var url = '?app_act=oms/sell_record_notice/create_wave&app_fmt=json&order_num=' + order_num; //暂时不是框架级别
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
        $.post(url, {'sku_data': sku_data}, function(ret) {
            if (ret['status'] < 0) {
                BUI.Message.Alert("已经生成波次单" + ret.data + "单"+ret.message, 'error');
            } else {
                BUI.Message.Alert("生成波次单" + ret.data + "单", 'success');
            }
            if(ret.data>0){
                 $('#btn-search').click();
            }
        }, "json");
    }

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
    }

</script>
<script type="text/javascript">
$(document).ready(function(){
	get_waces_name();
	$("#waves_name_select").change(function(){
	 	var strategy_name = $(this).val();
	 	if(strategy_name){
	 		$.post("?app_act=oms/sell_record_notice/get_waves_params_by_name&name="+strategy_name+"&app_fmt=json&type=1", '', function(data) {
 				if (data) {
 	 				$.each(data,function(i,value){
 	 					if(i == 'shop_code') {
 	 	 					shop_code_select.setSelectedValue(value)
 	 					} else if (i == 'sale_channel_code'){
 	 						sale_channel_code_select.setSelectedValue(value)
 	 	 				} else if (i == 'express_code'){
 	 	 					express_code_select.setSelectedValue(value)
 	 	 				} else if (i == 'goods_short_name'||i == 'barcode' || i == 'goods_code'){
 	 	 					keyword_type_select.setSelectedValue(i);
                                                         $("#keyword_type").val(value);
                                                        $("#"+i).val(value);
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
function get_waces_name(){
	$("#waves_name_select").remove();
	$("<select name='strategy_name' class='waves-name-select' id='waves_name_select'></select>").insertAfter("#save_conditions");
	$("<option value=''>请选择</option>").appendTo("#waves_name_select");
	$.post("?app_act=oms/sell_record_notice/get_waves_strategy&app_fmt=json&type=1", '', function(data) {
        if (data) {
            $.each(data,function(i,v){
            	$("<option value='"+v+"'>"+v+"</option>").appendTo("#waves_name_select");
            });
        } else {
        	BUI.Message.Alert(data.message, 'error')
        }
    }, "json");
}

$("#save_conditions").click(function(){
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
	                param=param+"&wave_strategy_name="+wave_strategy_name+"&type=1";
                	$.post("?app_act=oms/sell_record_notice/save_wave_strategy_name&app_fmt=json", param, function(data) {
                        if (data.status == 1) {
                            BUI.Message.Alert(data.message,function(){
                                 window.location.reload();
                            },'info');
//                            setTimeout(function(){
//                                $('.bui-dialog .bui-ext-close-x').trigger('click');
//                                get_waces_name();
//                            	
//                            },1500);
                            //刷新
                        } else if(data.status == 2){
                            BUI.Message.Confirm(data.message,function(){
                            	$.post("?app_act=oms/sell_record_notice/replace_name&app_fmt=json", param, function(data) {
                                    if (data.status == 1) {
                                    BUI.Message.Alert(data.message,function(){
                                                window.location.reload();
                                           },'info');
//                                        setTimeout(function(){
//                                        	$('.bui-dialog .bui-ext-close-x').trigger('click');
//                                            get_waces_name();
//                                        },1500);
                                    } else {
                                    	BUI.Message.Alert(data.message, 'error')
                                    }
                                }, "json");
                                
                            },'question');
                        } else {
                        	BUI.Message.Alert(data.message, 'error')
                        }
                    }, "json");
                	
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
      dialog.show();
      dialog.on('closed',function(e){
          dialog.remove();
      });

  });
})

</script>