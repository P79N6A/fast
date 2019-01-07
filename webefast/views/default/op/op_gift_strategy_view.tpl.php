
<style type="text/css">
.table_panel{width: 100%;border: 1px solid #ded6d9;margin-bottom: 5px;}
.table_panel1{
	width:100%;
	margin-bottom:5px;
 }
 .table_panel td {
    border-top: 0px solid #dddddd;
    line-height: 18px;
	padding:5px 10px;
    text-align: left;
}
.table_panel1 td {
    border:1px solid #dddddd;
    line-height: 20px;
    padding: 5px;
    text-align: left;
}
.table_panel_tt td{ padding:10px 25px;}
.nav-tabs{ padding-top:10px; margin-bottom:10px;}
.btns{ text-align:right; margin-bottom:5px;}
.panel-body { padding:5px; border: 1px solid #ded6d9;padding-bottom: 0;}
.panel > .panel-header{background-color: #ecebeb; border-color:#ded6d9; padding:5px 15px;}
.panel > .panel-header h3{ font-size:14px;}
input[type="checkbox"], input[type="radio"] { margin-right:2px; vertical-align: inherit;}

.bui-dialog .bui-stdmod-body {padding: 40px;}
.show_scan_mode{ text-align:center;}
.button-rule{ width:108px; height:108px; line-height: 104px;font-size: 22px;color: #666; background:url(assets/img/ui/add_rules.png) no-repeat; margin:0 8px; background-color:#f5f5f5; border-color:#dddddd; position:relative;}
.button-rule .icon{ display:block; width:37px; height:25px; background:url(assets/img/ui/add_rules.png) no-repeat center; position:absolute; top:-1px; right:-2px; display:none;}
.button-rule:active{ background-image:url(assets/img/ui/add_rules.png); box-shadow:none;}
.button-rule:active .icon{ display:block;}
.button-rule:hover{ background-color:#fff6f3; border-color:#ec6d3a; color:#ec6d3a;}
.button-manz{ background-position:41px 26px;}
.button-maiz{background-position:-208px 25px;}
.button-manz:hover{background-position:41px -214px;}
.button-maiz:hover{background-position:-208px -215px;}
</style>
<?php echo load_js("baison.js,record_table.js",true);?>
<ul class="nav-tabs oms_tabs">
    <li ><a href="#"  onClick="do_page('detail');">基本信息</a></li>
    <li class="active"  onClick="set_tab(this,'rule1');"><a href="#" >赠送规则</a></li>
     <li  onClick="set_tab(this,'customer');"  id="tab_customer"><a href="#" >会员定向</a></li>
   
</ul>
<table class='table_panel table_panel_tt' >
<tr>
  <td>策略名称：<?php echo $response['strategy']['strategy_name']; ?></td>
  <td >活动店铺：<?php echo $response['strategy']['shop_code_name']; ?></td>
</tr>
<tr>
  <td >活动开始时间：<?php echo date('Y-m-d H:i',$response['strategy']['start_time']); ?></td>
  <td >活动结束时间：<?php echo date('Y-m-d H:i',$response['strategy']['end_time']); ?></td>
</tr>
</table>

<div class="btns">
    <div id="rule1_btns">
<button type="button" class="button button-primary is_view" value="新增规则" id="btnSelectRule"  onClick="show_ovay_mode();"><i class="icon-plus-sign icon-white"></i> 新增规则</button>
<button type="button" class="button button-primary is_view"  id="btnSaveRule1"  ><i class="icon-plus-sign icon-white"></i> 保存</button>
<button type="button" class="button button-primary "  onclick="javascript:location.href = '?app_act=op/op_gift_strategy/do_list';"  ><i class="icon-plus-sign icon-white"></i> 返回</button>
</div>
    <div id="customer_btns" style="display:none;">     
        <button type="button" class="button button-primary is_view " onclick="import_customer()"   ><i class="icon-plus-sign icon-white"></i> 会员导入</button>
        <button type="button" class="button button-primary  is_view" onclick="clear_customer_data();"  ><i class="icon-plus-sign icon-white"></i> 一键清空</button>
    </div>
</div>
<div id='rule1'>
	
</div>  	
<div id='customer' style="display:none; ">
    <button id="searchForm" type="button" style="display:none" ></button>
    <div>
	<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (


            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '买家昵称',
                'field' => 'buyer_name',
                'width' => '150',
                'align' => ''
            ),

            array(
                'type' => 'text',
                'show' => 1,
                'title' => '手机号',
                'field' => 'tel',
                'width' => '150',
                'align' => ''
            ),
            
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '导入时间',
            		'field' => 'lastchanged',
            		'width' => '150',
            		'align' => ''
            ),
          )
    ),
    'dataset' => 'op/GiftStrategyCustomerModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'op_gift_strategy_customer_id',
	   'params' => array(
        'filter' => array('strategy_code'=>$response['strategy']['strategy_code']),
    ),

) );
?>
        </div>
    <br />    <br />    <br />   

    <div style="color:red;">说明：赠送规则勾选‘指定会员’，需要在此页面导入会员数据。即只有满足赠送规则且会员存在于此页面才送赠品，否则不送</div>
</div>  	

<script type="text/javascript">
   var strategy_code = "<?php echo $response['strategy']['strategy_code']; ?>";
   var id = "<?php echo $response['_id']; ?>";
   var show = '<?php echo $request['show'];?>';
   var type = 1;
   var  ES_pFrmId = '<?php echo $request['ES_frmId'];?>';
   var selectPopWindowshelf_code = {
		    //dialog: null,
		    callback: function (value, id, code, name) {
			         
			         $.ajax({
		                    type: "GET",
		                    url: "?app_act=op/op_gift_strategy/do_edit_detail_one",
		                    async: false,
		                    data: {sku:value[0]['sku'],op_gift_strategy_goods_id:value[0]['op_gift_strategy_goods_id'],op_gift_strategy_detail_id:value[0]['op_gift_strategy_detail_id'],app_fmt:'json'},
		                    dataType: "json",
		                    success: function(data){
		                        if(data.status==1){
		                        	 rule_init1(strategy_code);
		                        	 //window.location.reload();
		                        	// $.ajaxSetup({async:false}); 
		                        }
		                    }
		                });
	                //console.info(selectPopWindowshelf_code.dialog);
		        if (selectPopWindowshelf_code.dialog != null) {
		            selectPopWindowshelf_code.dialog.close();
		        }
		        
		        
	             
		    }
		};
	$(document).ready(function(){
	    //加载活动规则
	    rule_init1(strategy_code);
	    rule1_save(); 
	    condition();
	    give_way();
	    valifloat();
	    valinum();
	    //rule_del();
	});
	 
	
	function selectGoods(goods_code,op_gift_strategy_detail_id,op_gift_strategy_goods_id){
		selectPopWindowshelf_code.dialog = new ESUI.PopSelectWindow('?app_act=common/select/goods&goods_code='+goods_code+'&op_gift_strategy_detail_id='+op_gift_strategy_detail_id+'&op_gift_strategy_goods_id='+op_gift_strategy_goods_id, 'selectPopWindowshelf_code.callback', {title: '选择商品', width: 900, height:500 ,ES_pFrmId:ES_pFrmId}).show();
		
	}
	function delGoods(op_gift_strategy_goods_id){
		
		$.post('<?php echo get_app_url('op/op_gift_strategy/del_goods');?>', {'op_gift_strategy_goods_id':op_gift_strategy_goods_id}, function(data){
            var type = data.status == 1 ? 'success' : 'error';
    		if (data.status == 1) {
    			rule_init1(strategy_code);
    		} else {
    			BUI.Message.Alert(data.message, function() { }, type);
    		}
        }, "json");	
	}
    function rule_init1(strategy_code){
    	var data = {
   	         'strategy_code':strategy_code,
   	         'app_page':'NULL'
   	     };
   	     $.ajax({
   	         type : "get",  
   	         url : "?app_act=op/op_gift_strategy/show_rule1",  
   	         data : data,
   	         async : false,
   	         success : function(data){
   	             //ret = data; 
   	             $("#rule1").html(data);
      	          condition();
         		    give_way();
         		    valifloat();
         		    valinum();
   	         }
   	     });
   		
    }
	function condition(){
		$(".condition").click(function(){
            var xulie = $(this).attr("xulie");
            var value =  $(this).val();
            if(value == '1'){
                $("#buy_num_"+xulie).show();
            	$('#table_goods_'+xulie+' tr').find('td:eq(4)').hide();
            }else{
           	 $("#buy_num_"+xulie).hide();
            	$('#table_goods_'+xulie+' tr').find('td:eq(4)').show(); 
            }
          });
	}
	function give_way(){
		$(".give_way").click(function(){
            var xulie = $(this).attr("xulie");
            var value =  $(this).val();
            if(value == '1'){
                $("#gift_num_"+xulie).show();
            	$('#table_gift_'+xulie+' tr').find('td:eq(4)').hide();
            }else{
           	 $("#gift_num_"+xulie).hide();
            	$('#table_gift_'+xulie+' tr').find('td:eq(4)').show(); 
            }
          });
	}
	function valifloat(){
		$(".deci").blur(function(){
			var value = $(this).val();
			vfloat(value);
			
		 });	
	}
	
	function valinum(){
		$(".int_num").blur(function(){
			var value = $(this).val();
			vnum(value);
			
		 });	
	}
	function vfloat(value){
		var digit = /^\d+(\.\d+)?$/;
        if (!digit.test(value)) 
        {
           alert("只能输入小数或数字");
           this.focus();
           return false;
        }
        return true;
	}
	function vnum(value){
		var digit = /^[1-9]+$/;
        if (!digit.test(value)) 
        {
           alert("只能输入大于1数字");
           this.focus();
           return false;
        }
        return true;
	}
    function rule1_save(){
    	$("#btnSaveRule1").click(function(){
    		var float= $("[class='deci']");
    		var flag = true; 
    		var k = 0;
    		//小数判断
    		$(float).each(function() {
    		   
    		   flag =   vfloat(this.value);
    		   if(!flag){
              		k = 1;
              		return false;
              	}
    		});
    		if(k == 1){
        		return;
        	}
    		//整数判断
    		
    		var num = $("[class='int_num']");
    		$(num).each(function() {
      		   
      		   flag =   vnum(this.value);
      		   if(!flag){
                		k = 1;
                		return false;
                	}
      		});
    		if(k == 1){
        		return;
        	}
           	var data = $('#form2').serialize();
           
           	$.post('<?php echo get_app_url('op/op_gift_strategy/rule1_save');?>', data, function(data){
                var type = data.status == 1 ? 'success' : 'error';
        		if (data.status == 1) {
        			
       			   BUI.Message.Alert('修改成功：', type);
          			//
        		} else {
        			BUI.Message.Alert(data.message, function() { }, type);
        		}
        		setTimeout(function(){
					$(".bui-ext-close .bui-ext-close-x").trigger('click');
					window.location.reload();
       			}, 2000); 
            }, "json");
              
    			
           });
    }
   function rule_del(op_gift_strategy_detail_id){
	   
	  // $(".delDetail").click(function(){
		  // var data = {'op_gift_strategy_detail_id':$(this).attr("op_gift_strategy_detail_id")};
		   var data = {'op_gift_strategy_detail_id':op_gift_strategy_detail_id};  
	       	$.post('<?php echo get_app_url('op/op_gift_strategy/del_detail');?>', data, function(data){
	            var type = data.status == 1 ? 'success' : 'error';
	    		if (data.status == 1) {
	    			
	   			   BUI.Message.Alert('删除成功：', type);
	    			   rule_init1(strategy_code);
	      			 
	    		} else {
	    			BUI.Message.Alert(data.message, function() { }, type);
	    		}
	        }, "json");
        // });
   }
	function show_ovay_mode(){
	    BUI.use('bui/overlay',function(Overlay){
		 // var html_str = '<div class="show_scan_mode"><a class="button button-rule button-manz" href="javascript:fullGift()">满赠<i class="icon"></i></a>             <a class="button button-rule button-maiz" href="javascript:bb()">买赠<i class="icon"></i></a></div>';
		  var html_str = '<div class="show_scan_mode"><a class="button button-rule button-manz" href="javascript:fullGift(0)">满赠<i class="icon"></i></a>      <a class="button button-rule button-manz" href="javascript:fullGift(1)">买赠<i class="icon"></i></a>        </div>';
	      var dialog = new Overlay.Dialog({
	        title:'新增规则',
	        width:500,
	        height:220,
	        mask:false,
	        buttons:[],
	        bodyContent:html_str
	      });
	    dialog.show();
	  });
	
	  $(".show_scan_mode").click(function(){
		  $(".bui-ext-close").click();
	  });
	  
	}
	function fullGift(type){
		var data = {
	   	         'strategy_code':strategy_code,
		   	     'type':type
	   	     };
		$.post('<?php echo get_app_url('op/op_gift_strategy/do_add_detail');?>', data, function(data){
            var type = data.status == 1 ? 'success' : 'error';
    		if (data.status == 1) {
    			rule_init1(strategy_code);
   			   //BUI.Message.Alert('修改成功：', type);
    		} else {
    			//BUI.Message.Alert(data.message, function() { }, type);
    		}
        }, "json");
		 
	}
	function importGoods(strategy_code,op_gift_strategy_detail_id,sort){
		var param = {};
		var type = 2;
		var url= '?app_act=prm/goods/record_import&id='+op_gift_strategy_detail_id+"&type="+type+"&sort="+sort;
		 
		    new ESUI.PopWindow(url, {
		            title: '商品导入',
		            width:500,
		            height:380,
		            onBeforeClosed: function() { location.reload();
		            }
		        }).show();
		     
	}
	function importOtherRuleGoods(strategy_code,op_gift_strategy_detail_id,sort){
		var data = {
	   	         'op_gift_strategy_detail_id':op_gift_strategy_detail_id,
		   	     'strategy_code':strategy_code,  
		   	      'sort':sort 
	   	     };
		$.post('<?php echo get_app_url('op/op_gift_strategy/get_other_rule');?>', data, function(data){
            var type = data.status == 1 ? 'success' : 'error';
    		if (data.status == 1) {
        		var len = data.data.length;
        		var val = data.data;
        		var html_str1 = '';
   			 for (var i=0; i< len; i++){
      			 // html += "<tr><td >"+arr_spec1_name[i]+"</td><td >"+arr_spec2_name[j]+"</td><td >"+sku+"<input  id='"+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"_sku' name= '"+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"_sku' value='"+sku+"'  type='hidden' /></td><td ><span class='shuru' style='display:;'><input name='"+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"_barcode' id='"+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"_barcode' style='width:98%;' value='"+barcord1+"' onBlur= 'inputbarcord(this);' type='text' /></span></td><td >"+sell_price+"</td><td >"+weight+"</td><td><input name='"+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"_sku_remark' id='"+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"_sku_remark' style='width:98%;' value='"+sku_remark+"' type='text' /></td></tr>";
    				
    				op_gift_strategy_detail_id_new = val[i]['op_gift_strategy_detail_id'];
    				k = i+1;
    				if(op_gift_strategy_detail_id_new != op_gift_strategy_detail_id){
   				  		html_str1 += '<div class="show_scan_mode"><a class="button button-primary" href="javascript:fullOtherGift('+op_gift_strategy_detail_id_new+','+op_gift_strategy_detail_id+','+sort+')"> 活动规则'+k+'</a></div>';
    				}
			   }
    			BUI.use('bui/overlay',function(Overlay){
    	   			  var html_str = html_str1;
    	   		      var dialog = new Overlay.Dialog({
    	   		        title:'导入其他规则赠品',
    	   		        width:500,
    	   		        height:220,
    	   		        mask:false,
    	   		        buttons:[],
    	   		        bodyContent:html_str
    	   		      });
    	   		    dialog.show();
    	   		  });
    	   		
    	   		  $(".show_scan_mode").click(function(){
    	   			  $(".bui-ext-close").click();
    	   		  });
   			   //BUI.Message.Alert('修改成功：', type);
    		} else {
    			//BUI.Message.Alert(data.message, function() { }, type);
    		}
        }, "json");
		
		
	}
	function fullOtherGift(op_gift_strategy_detail_id_new,op_gift_strategy_detail_id,sort){
		var data = {
	   	         'op_gift_strategy_detail_id_new':op_gift_strategy_detail_id_new,
		   	     'op_gift_strategy_detail_id':op_gift_strategy_detail_id,  
		   	     'sort':sort 
	   	     };
		$.post('<?php echo get_app_url('op/op_gift_strategy/import_other_rule_goods');?>', data, function(data){
            var type = data.status == 1 ? 'success' : 'error';
    		if (data.status == 1) {
    			rule_init1(strategy_code);
    		} else {
    			BUI.Message.Alert(data.message, function() { }, type);
    		}
        }, "json");
	}
	function do_page(param) {
		
		if(strategy_code != ''){
			if(show == '1'){
				location.href = "?app_act=op/op_gift_strategy/detail&app_scene=edit&_id="+id+"&show=1&strategy_code=" + strategy_code+"&ES_frmId=<?php echo $request['ES_frmId'];?>";
				}else{
				location.href = "?app_act=op/op_gift_strategy/detail&app_scene=edit&_id="+id+"&strategy_code=" + strategy_code+"&ES_frmId=<?php echo $request['ES_frmId'];?>";
				}
			}
		
	}
        
        var select_detail_id=0;
        var select_is_gift = 0;
        var select_is_select = 0;
        var select_url = '';
        function show_select_goods(detail_id,is_gift,is_select){
         select_detail_id=detail_id;
         select_is_gift = is_gift;
         select_is_select = is_select;

        var param = {store_code:'',is_diy:0};
        var url = '?app_act=prm/goods/goods_select_tpl&is_select='+is_select;
                
	if(typeof(top.dialog)!='undefined'){
            if(url!=select_url){
		top.dialog.remove(true);
            }else{
                top.dialog.show(); 
                return ;
            }
	}
         var buttons = [
                       {
                        text:'保存继续',
                        elCls : 'button button-primary',
                        handler : function(){
                           	addgoods(this,1);
                        }
                      },
                      {
                        text:'保存退出',
                        elCls : 'button button-primary',
                        handler : function(){
                            addgoods(this,0);
                        }
                      },{
                        text:'取消',
                        elCls : 'button',
                        handler : function(){
                         this.close();
                        }
                      }
                    ];
        
	top.BUI.use('bui/overlay',function(Overlay){
		 top.dialog = new Overlay.Dialog({
		    title: '选择商品',
		    width: '80%',
		    height: 450,
		    loader: {
		        url:url,
		        autoLoad: true, //不自动加载
		        params: param, //附加的参数
		        lazyLoad: false, //不延迟加载
		        dataType: 'text'   //加载的数据类型
		    },
             			align: {
              //node : '#t1',//对齐的节点
              points: ['tc','tc'], //对齐参考：http://dxq613.github.io/#positon
              offset: [0,20] //偏移
            },
		    mask: true,
                    buttons:buttons
		});
                top.dialog.on('closed',function(){
                     // location.reload();
                      rule_init1(strategy_code);
                });
                
		top.dialog.show();
	
            });
        
        
        }
        function addgoods(obj,type){
             var select_data= {};
              if(select_is_select==1){
                    select_data = top.SelectoGrid.getSelection();
                }else{ 
                  var     data =top.skuSelectorStore.getResult();
                var di=0;
            BUI.each(data,function(value,key){
                var num_name = 'num_'+value.sku;
                if(top.$("input[name='"+num_name+"']").val()!=''&&top.$("input[name='"+num_name+"']").val()!=undefined){
                    value.num = top.$("input[name='"+num_name+"']").val();
                    select_data[di] = value; 
                    di++;
                    }
            });
            }
            var _thisDialog = obj;
          if(di==0){
              _thisDialog.close();
              return ;
          }
 
          var url ='?app_act=op/op_gift_strategy/do_add_goods&app_fmt=json&strategy_code='+strategy_code+'&detail_id=' +select_detail_id+'&is_gift='+select_is_gift;

        $.post(url, {data: select_data}, function (result) {
            if (result.status!=1) {
                //添加失败
                top.BUI.Message.Alert(result.message, function () {
               //       _thisDialog.close();
                }, 'error');
            } else {
                if(type==1){
                    top.skuSelectorStore.load();
                }else{
                     _thisDialog.close();
                }
            }

        }, 'json');

    }  
    
    
    function clear_customer_data(){
        var url = '?app_act=op/op_gift_strategy/clear_customer_data&app_fmt=json';
        var data = {};
        data.strategy_code = strategy_code;
        $.post(url,data,function(ret){
             tableStore.load();
        },'json');
    }       
    function set_tab(_this,name){
        if(!$(_this).hasClass('active')){
             $('.nav-tabs li').removeClass('active');
            $(_this).addClass('active');
            $('#'+name).show();
            if(name=='customer'){
                $('#rule1').hide();
                 $('#rule1_btns').hide();
                 $('#customer').show();
                 $('#customer_btns').show();
                 tableStore.load(); 
            }else{
                 $('#customer').hide();
                 $('#customer_btns').hide();
                 $('#rule1').show();
                 $('#rule1_btns').show();
            }
        }
        
    }
    	function import_customer(){
		var param = {};
		var type = 2;
		var url= '?app_act=op/op_gift_strategy/customer_import&strategy_code='+strategy_code;
		 
		    new ESUI.PopWindow(url, {
		            title: '导入会员',
		            width:500,
		            height:380,
		            onBeforeClosed: function() {  tableStore.load(); 
		            }
		        }).show();
		     
	}    
       
   $(function(){
       <?php if($request['type']==1):?>
             $('#tab_customer').click();
       <?php   endif;?>
   });    
        
</script>
