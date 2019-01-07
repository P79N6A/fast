<style type="text/css">
 .table_panel{
	width:800px;
 }
 .table_panel td {
    border-top: 0px solid #dddddd;
    line-height: 20px;
    padding: 10px;
    text-align: left;
    vertical-align: top;
}
.table_panel1 td {
    border:1px solid #dddddd;
    line-height: 20px;
    padding: 10px;
    text-align: left;
    vertical-align: top;
}

</style>
<?php render_control('PageHead', 'head1',
array('title'=>'商品编辑'));
//$spec1_realname = load_model('prm/GoodsSpec1Model')->get_spec1_realname();
//$spec2_realname = load_model('prm/GoodsSpec2Model')->get_spec2_realname();
//$result = load_model('sys/GoodsRuleModel')->get_by_ids(array(1, 2));
?>

<div id="tab">
<ul>
    <li class='bui-tab-panel-item11 '><a href='?app_act=prm/goods/diy_goods_detail&action=do_edit&type=1&goods_id=<?php echo $response['goods_id']; ?>'>通用信息</a></li>
    <li class='bui-tab-panel-item11 '><a href='?app_act=prm/goods/diy_goods_detail&action=do_edit&type=2&goods_id=<?php echo $response['goods_id']; ?>'>规格信息</a></li>
    <li class='bui-tab-panel-item11 active'><a href='?app_act=prm/goods_diy/detail&goods_code=<?php echo $response['goods_code']; ?>&goods_id=<?php echo $response['goods_id']; ?>'>组装明细</a></li>
    <li class='bui-tab-panel-item11 '><a href='?app_act=prm/goods/diy_goods_detail&action=do_edit&type=3&goods_id=<?php echo $response['goods_id']; ?>'>扩展属性</a></li>
    <li class='bui-tab-panel-item11 '><a href='?app_act=prm/goods/diy_goods_detail&action=do_edit&type=4&goods_id=<?php echo $response['goods_id']; ?>'>日志</a></li>
</ul>
</div>
<script>
var p_sku = "";
var p_goods_code = "<?php echo $response['goods_code']; ?>";
var p_goods_code_en = "<?php echo urlencode($response['goods_code']); ?>";
jQuery(function(){

	get_goods_panel({
        "class":"btnSelectGoods",
        'param':{'store_code':'','diy':'0'},
        "callback":addgoods
    });
})
function get_goods_panel(obj){
	
	var param = new Object();
     
	if(typeof obj.param != "undefined"){
		param = obj.param;
	}
	if(typeof(top.dialog)!='undefined'){
		top.dialog.remove(true);
	}
	top.BUI.use('bui/overlay',function(Overlay){
		 top.dialog = new Overlay.Dialog({
		    title: '选择商品',
		    width: '80%',
		    height: 400,
		    loader: {
		        url: '?app_act=prm/goods/goods_select_tpl',
		        autoLoad: true, //不自动加载
		        params: param, //附加的参数
		        lazyLoad: false, //不延迟加载
		        dataType: 'text'   //加载的数据类型
		    },
		    mask: true,
		    success: function () {
		    	if(typeof obj.callback == "function"){
		    		obj.callback(this,obj.class);
		    	}
		    }
		});
		$("."+obj.class).click(function(event) {
			p_sku = $(this).attr("p_sku");
			//p_goods_code = $(this).attr("p_goods_code");
			top.dialog.show();
		});
    });
}
function addgoods(obj){
	  var data =top.skuSelectorStore.getResult();
       var select_data= {}
            var di=0;
    BUI.each(data,function(value,key){
        var num_name = 'num_'+value.sku;
        if(top.$("input[name='"+num_name+"']").val()!=''&&top.$("input[name='"+num_name+"']").val()!=undefined){
            value.num = top.$("input[name='"+num_name+"']").val();
            select_data[di] = value; 
            di++;
            }
    });
        var _thisDialog = obj;
      if(di==0){
          _thisDialog.close();
          return ;
      }
    $.post('?app_act=prm/goods_diy/do_add_detail&p_sku=' + encodeURIComponent(p_sku)+"&p_goods_code="+p_goods_code_en , {data: select_data}, function (result) {
        if (true != result.status) {
            //添加失败
            top.BUI.Message.Alert(result.message, function () {
                //_thisDialog.close();
                _thisDialog.remove(true);
            }, 'error');
        } else {
            //_thisDialog.close();
        	_thisDialog.remove(true);
            //tableStore.load();
            //form.submit();
        }
        location.reload();
        //show_detail_ajax(p_sku);
    }, 'json');
	
}  
</script>
<div>&nbsp;&nbsp;&nbsp;</div>
<form action="?app_act=prm/goods_diy/save" id="form2" method="post">
<div id='p2'>
<table class='table_panel1' ><input type="hidden" id="goods_code" name="goods_code" value="<?php echo $response['goods_code'];?>">
  <input type="hidden" id="spec1_code"  value="" name="spec1_code" />
  <input type="hidden" id="spec1_name"  value="" name="spec1_name" />
  <tr>
  <td style="width:80px;">商品名称</td>
  <td ><?php echo $response['goods_spec1_rename'];?></td>
  <td ><?php echo $response['goods_spec2_rename'];?></td>
  <td >系统SKU码</td>
  <td > 商品条形码 </td>
  <td> 吊牌价(元) </td>
  <td> 操作 </td>
  </tr>
  <?php foreach($response['barcord'] as $k=>$v){ ?>
  <tr>
 <td style="width:20px;" ><span onClick="show_detail('<?php echo $v['sku'] ?>');"><i class="bui-grid-cascade-icon"> </i>&nbsp;&nbsp;</span> <?php echo $v['goods_code_name'] ?></td>
  <td><?php echo $v['spec1_code_name'] ?> </td>
  <td ><?php echo $v['spec2_code_name'] ?></td>
  <td ><?php echo $v['sku'] ?></td>
  <td ><?php echo $v['barcode'] ?><!-- <input  name="<?php //echo 'barcode_barcode['.$v['barcode_id'].']'; ?>" type="text" style="width:100px;"  value=""> --></td>
  <td ><?php echo $v['sell_price'] ?> </td>
  <td > <button type="button" class="button button-success btnSelectGoods" value="新增商品"  p_sku="<?php echo $v['sku'] ?>"  p_goods_code = "<?php echo $v['goods_code'] ?>" ><i class="icon-plus-sign icon-white"></i> 新增组装商品</button>&nbsp;&nbsp;&nbsp; <!--  <button type="button" p_sku="<?php // echo $v['sku'] ?>" class="button del_barcord">删除</button>--></td>
  </tr>
   <tr class="<?php echo "show_tr_".$v['sku'] ?>" <?php //if($response['sku'] == $v['sku']){ ?>  <?php //}else{ ?>  <?php //} ?>>
   <td></td>
   <td colspan=7 class="<?php echo "show_".$v['sku'] ?>" >
    <?php //if($response['sku'] == $v['sku']){ ?>
          <table class='table_panel1' style="background-color: #f1f1f1;" >
           <tr><td style="width:15%;">商品名称</td><td style="width:15%;">商品编码</td><td style="width:10%;"><?php echo $response['goods_spec1_rename'];?></td><td style="width:10%;"><?php echo $response['goods_spec2_rename'];?></td><td style="width:100px;">商品条形码</td><td style="width:80px;;">吊牌价</td><td style="width:80px;">数量</td><td style="width:100px;">操作</td></tr>
           <?php if(isset($v['diy'])){ ?>
            <?php foreach($v['diy'] as $k1=>$v1){ ?>
		  
		  <tbody id="tiaoma">
		  
		     <tr><td ><?php echo $v1['goods_code_name']; ?></td><td ><?php echo isset($v1['goods_code'])?$v1['goods_code']:'' ?></td><td ><?php echo $v1['spec1_code_name'] ?> </td>
		     <td ><?php echo $v1['spec2_code_name'] ?>	</td><td ><?php echo isset($v1['barcode'])?$v1['barcode']:'' ?></td><td ><?php echo isset($v1['sell_price'])?$v1['sell_price']:'' ?></td><td><input  name="<?php echo 'diy['.$v1['goods_diy_id'].']'; ?>" type="text" style="width:40px;"  value="<?php echo $v1['num']; ?>"></td>
		<td> <span onclick="del_diy('<?php echo $v1['goods_diy_id'] ?>','<?php echo $v1['p_goods_code'] ?>','<?php echo $v1['p_sku'] ?>')">删除</span></td>
		</tr>
		   
		    <?php } ?>
		    
		    <?php } ?>
		  </tbody >
		  </table>
     <?php //} ?>
  </td></tr>
 <?php } ?>
  
  <tr><td colspan=7>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" class="button button-primary" id="btn_save"  value = "保存"><input type="hidden" name="msg" id="msg"></td></tr>
</table>

	<div><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<font color="red">提示：只能对已保存的商品规格设置组装明细，所以请设置商品规格先保存，然后在设置组装明细</font>
   </div>
</div>

</form>

<script type="text/javascript">
BUI.use(['bui/tab','bui/mask'],function(Tab){
	var tab = new Tab.TabPanel({
	srcNode : '#tab',
	elCls : 'nav-tabs',
	itemStatusCls : {
	'selected' : 'active'
	},
	panelContainer : '#panel'//如果不指定容器的父元素，会自动生成
	//selectedEvent : 'mouseenter',//默认为click,可以更改事件
	});
	tab.render();
	});
$(document).ready(function(){
	$("#btn_save").click(function(){
        
       	var data = $('#form2').serialize();
       
       	$.post('<?php echo get_app_url('prm/goods_diy/save');?>', data, function(data){
            var type = data.status == 1 ? 'success' : 'error';
    		if (data.status == 1) {
    			
   			   BUI.Message.Alert('修改成功：', type);
      			window.location.reload();
    		} else {
    			BUI.Message.Alert(data.message, function() { }, type);
    		}
        }, "json");
          
			
       });

	
    $(".del_barcord").click(function(){
    	p_sku = $(this).attr("p_sku");
    	var data = {
   	         'sku':p_sku,
   	         'goods_code':"<?php echo $response['goods_code'];?>"
   	            
   	     };
       	$.post('<?php echo get_app_url('prm/goods_diy/del_barcord');?>', data, function(data){
            var type = data.status == 1 ? 'success' : 'error';
    		if (data.status == 1) {
    			
   			   BUI.Message.Alert('删除成功：', type);
      			window.location.reload();
    		} else {
    			BUI.Message.Alert(data.message, function() { }, type);
    		}
        }, "json");
          
			
       });
});
function show_detail(sku) {
	if($(".show_tr_"+sku).is(":hidden")) 
	{  
		show_detail_ajax(sku);
	}else{
		$(".show_tr_"+sku).hide();
	}
	 
 }
 //删除组合商品
 function del_diy(goods_diy_id,p_goods_code,p_sku){
	 var data = {
   	         'goods_diy_id':goods_diy_id,
      	     'p_goods_code':p_goods_code,
        	 'p_sku':p_sku,      
   	     };
	 $.post('<?php echo get_app_url('prm/goods_diy/del_diy');?>', data, function(data){
         var type = data.status == 1 ? 'success' : 'error';
 		if (data.status == 1) {
 			
			   BUI.Message.Alert('删除成功：', type);
   			window.location.reload();
 		} else {
 			BUI.Message.Alert(data.message, function() { }, type);
 		}
     }, "json");
 }
 function show_detail_ajax(sku){
	
     var data = {
         'p_sku':sku,
         'p_goods_code':"<?php echo $response['goods_code'];?>",
        // 'app_tpl':'oms/deliver_record_detail',
         'app_page':'NULL'
     };
     $.ajax({
         type : "get",  
         url : "?app_act=prm/goods_diy/show_detail",  
         data : data,
         async : false,
         success : function(data){
             //ret = data; 
             $(".show_"+sku).html(data);
         }
     });
	$(".show_tr_"+sku).show();
 }
</script>
