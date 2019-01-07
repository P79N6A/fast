
<style type="text/css">
.table_panel{width: 100%;border: 1px solid #ded6d9;margin-bottom: 5px;}
.table_pane2{width: 100%;border: solid 1px #ded6d9;margin-bottom: 5px;margin-top: 5px;}
 .table_panel td {
    border-top: 0px solid #dddddd;
    line-height: 15px;
	padding:5px 10px;
    text-align: left;
}
.table_panel1 td {
    border:1px solid #dddddd;
    line-height: 15px;
    padding: 5px;
    text-align: left;
}
.table_pane2 td {
    border:1px solid #dddddd;
    line-height: 15px;
    padding: 5px;
    text-align: left;
    width:14.2%;
}
.table_panel_tt td{ padding:10px 5px;}
.table_panel_tt2 td{ padding:10px 5px;}

.nav-tabs{ padding-top:10px; margin-bottom:10px;}
.panel-body { padding:5px; border: 1px solid #ded6d9;padding-bottom: 0;}
.panel > .panel-header{background-color: #ecebeb; border-color:#ded6d9; padding:5px 15px;}
.panel > .panel-header h3{ font-size:14px;}
input[type="checkbox"], input[type="radio"] { margin-right:2px; vertical-align: inherit;}
.panel_div { padding:5px; border: 1px solid #ded6d9;padding-bottom: 0;margin-bottom: 5px;}
</style>

<?php echo load_js("baison.js,record_table.js",true);?>
<ul class="nav-tabs oms_tabs">
    <li onClick="do_page('base');" ><a href="#"  >规则设置</a></li>
    <li class="active"><a href="#" >赠品商品</a></li>
    <li onClick="do_page('goods');"><a href="#" >活动商品</a></li>
    <li onClick="do_page('customer');"><a href="#" >定向会员</a></li>
   
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

<div class="panel_div_range">
<?php foreach ($response['range'] as $range_row) {?>
	<div >
	<!-- 金额/数量范围 -->
	<?php if ($response['is_range'] == 1) {?>
	
	<span class="range">当前选择的区间：<?php echo $range_row['range_start'];?>~<?php echo $range_row['range_end'];?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
	<?php }?>
	<input type="radio" <?php if($response['strategy']['is_check']==1){?>disabled <?php }?> <?php if($range_row['give_way'] == 0){ ?> checked <?php }?> onchange="change_way(<?php echo $range_row['id'];?>)" name="give_way_<?php echo $range_row['id'];?>" value="0"/>固定赠品<input type="radio" <?php if($response['strategy']['is_check']==1){?>disabled <?php }?> name="give_way_<?php echo $range_row['id'];?>" value="1" onchange="change_way(<?php echo $range_row['id'];?>)" <?php if($range_row['give_way'] == 1){ ?> checked <?php }?>  />随机赠品
	
	<input style="width:250px;margin-left:100px;<?php if ($range_row['give_way'] == 0){?> display:none;<?php }?>" type="text" class="random_panel" onchange="change_way(<?php echo $range_row['id'];?>)" id="gift_num_<?php echo $range_row['id'];?>" value="<?php echo $range_row['gift_num'];?>" placeholder="请输入随机赠送赠品数量，默认是1"/>
	<?php if ($range_row['give_way'] == 0) {?>
	<button style="float:right;" type="button" <?php if($response['strategy']['is_check']==1){?>disabled <?php }?> class="button button-primary btns"  onclick="show_select_goods(<?php echo $range_row['id'];?>,1,0);" >添加赠品</button>
	<?php } else {?>
	<button style="float:right;" type="button" <?php if($response['strategy']['is_check']==1){?>disabled <?php }?> class="button button-primary btns"  onclick="show_select_goods(<?php echo $range_row['id'];?>,1,1);" >添加赠品</button>
	<?php }?>
	</div>
	<table class='table_pane2'>
	<tr>
	<td width="10%">操作</td>
	<td width="35%">赠品名称</td>
	<td width="25%">赠品规格</td>
	<td width="20%">赠品条形码</td>
	<?php if ($range_row['give_way'] == 0){?>
	<td width="10%">赠品件数</td>
	<?php }?>
        <?php if ($response['strategy']['set_gifts_num'] == 1){?>
        <td width="30%">限量赠送数量</td>
        <td width="30%">已送数量</td>
	<?php }?>
	</tr>
	<?php        if(isset($response['gift_goods'][$range_row['id']] )):?>
        <?php 
        foreach ($response['gift_goods'][$range_row['id']] as $goods_row) {?>
	<tr>
	<td>
	<?php if($response['strategy']['is_check']==0){?>
	<a href="javascript:delGoods('<?php echo $goods_row['op_gift_strategy_goods_id'] ?>');" >删除 </a> 
	<?php }?>
	</td>
	<td><?php echo $goods_row['goods_name'];?></td>
	<td><?php echo $goods_row['spec'];?></td>
	<td><?php echo $goods_row['barcode'];?></td>
	<?php if ($range_row['give_way'] == 0){?>
	<td><?php echo $goods_row['num'];?></td>
	<?php }?>
        <?php if ($response['strategy']['set_gifts_num'] == 1){?>
        <td><div <?php if($response['strategy']['is_check']==0){?> class="edit-text" <?php } ?> name="<?php echo $goods_row['op_gift_strategy_goods_id'];?>"><?php echo $goods_row['gifts_num'];?></div></td>
        <td><?php echo $goods_row['send_gifts_num'];?></td>
	<?php }?>
	</tr>
	<?php }?>
            <?php endif;?>
	</table>
<?php }?>
    <div id='msg' style='color:red;display:none'>注：*赠品商品同时也在销售，请确保商品不会超卖！</div>
</div>
<script type="text/javascript">
var id = "<?php echo $request['_id']; ?>"; 
var barcode;
$(".edit-text").click(function(){
    goods_id = $(this).attr("name");
});
    BUI.use(['bui/editor'],function(Editor){
      //编辑普通文本
      var editor1 = new Editor.Editor({
        trigger : '.edit-text',
        width : 160,
        field : { //设置编辑的字段
          rules : {
            required : true
          }
        }
      });
      editor1.render();
      editor1.on('accept',function(record) {
        if (record.value < 0) {
                BUI.Message.Alert('不能为负数', 'error');
                window.location.reload();
                return;
            }
            var _record = record;
            $.post('?app_act=op/gift_strategy/edit_num',
                    {id: id, num: _record.value,goods_id:goods_id,shop:"<?php echo $response['strategy']['shop_code']; ?>"},
                    function (result) {
                        BUI.Message.Alert(result.message);
                        if(result.status == 2){
                            $("#msg").css('display','block');
                            $(".edit-text[name='"+ barcode +"']").css('color','red');
                        }else{
                            $(".edit-text[name='"+ barcode +"']").css('color','black');
                        }
                        //window.location.reload();
                    }, 'json');
      });
  }); 
//})
    
function change_way(range_id){
	var give_way = $("input:radio:checked[name='give_way_"+range_id+"']").val();
	var data = {'op_gift_strategy_detail_id':<?php echo $request['_id'];?>,'range_type':<?php echo $response['data']['range_type'];?>,'range_id':range_id,'give_way':give_way,'goods_condition':<?php echo $response['data']['goods_condition'];?>};
	if (give_way == 1){
		//随机
		var gift_num = $("#gift_num_"+range_id).val();
		data.gift_num = gift_num;
		
	} 
	
	$.post('<?php echo get_app_url('op/gift_strategy/upate_give_way');?>',data, function(data){
        var type = data.status == 1 ? 'success' : 'error';
		if (data.status == 1) {
			location.reload();
		} else {
			BUI.Message.Alert(data.message, function() {location.reload(); }, type);
		}
    }, "json");	
	
}

function do_page(type) {
	if (type == 'base'){
		location.href = "?app_act=op/gift_strategy/rule_view&app_scene=edit&_id="+id;
	} else if (type == 'gift'){
		location.href = "?app_act=op/gift_strategy/gift_goods&app_scene=edit&_id="+id;
	} else if (type == 'goods'){
		location.href = "?app_act=op/gift_strategy/rule_goods&app_scene=edit&_id="+id;
	} else if (type == 'customer'){
		location.href = "?app_act=op/gift_strategy/rule_customer&app_scene=edit&_id="+id;
	}
	
	
}
function save_range(){
	var range_start = $("#range_start").val();
	var range_end = $("#range_end").val();
	$.post('<?php echo get_app_url('op/gift_strategy/add_range');?>', {'op_gift_strategy_detail_id':id,'range_start':range_start,'range_end':range_end}, function(data){
        var type = data.status == 1 ? 'success' : 'error';
		if (data.status == 1) {
			$(".add_range").css('display','none');
			location.reload();
		} else {
			BUI.Message.Alert(data.message, function() { }, type);
		}
    }, "json");	
	
}

var select_is_gift = 0;
var select_is_select = 0;
var select_url = '';
function show_select_goods(range_id,is_gift,is_select){
	 select_is_gift = is_gift;
	 select_is_select = is_select;
	var param = {store_code:'',select_combo:0};
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
                   	addgoods(range_id,this,1);
                }
              },
              {
                text:'保存退出',
                elCls : 'button button-primary',
                handler : function(){
                    addgoods(range_id,this,0);
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
          location.reload();
    });    
        
	top.dialog.show();

    });


}
function addgoods(range_id,obj,type){
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
	      thisDialog.close();
	      return ;
	}
	var strategy_code = "<?php echo $response['strategy']['strategy_code'];?>";
	var detail_id = "<?php echo $response['data']['op_gift_strategy_detail_id'];?>";
  	var url ='?app_act=op/gift_strategy/do_add_goods&app_fmt=json&range_id='+range_id+'&strategy_code='+strategy_code+'&detail_id=' +detail_id+'&is_gift='+select_is_gift;

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
//删除赠品
function delGoods(op_gift_strategy_goods_id){
	BUI.Message.Confirm('是否确定要删除此赠品商品',function(){
		$.post('<?php echo get_app_url('op/gift_strategy/del_goods');?>', {'op_gift_strategy_goods_id':op_gift_strategy_goods_id}, function(data){
	        var type = data.status == 1 ? 'success' : 'error';
			if (data.status == 1) {
				location.reload();
			} else {
				BUI.Message.Alert(data.message, function() { }, type);
			}
	    }, "json");	
	},'warning');
}        
</script>
