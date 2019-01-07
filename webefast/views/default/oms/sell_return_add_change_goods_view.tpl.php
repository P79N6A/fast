<style>
    .panel-body{
        padding:0px;
    }
	.panel{
        width:680px;
    }
   
#panel_order .span11{
        width:400px;
        margin:0px;
    }
  
.bui-grid-header{ border-bottom:1px solid #dddddd;}    
.bui-grid-body{ border-bottom:1px solid #dddddd;}
.bui-grid-table .bui-grid-cell{ border-top:none; border-bottom:1px solid #dddddd;}
.bui-grid-bbar{ border:none;}
  .bui-select-list{
    overflow: auto;
    overflow-x: auto;
    max-height: 150px;
    _height : 300px;
  }
</style>


<div class="panel">
    
        <form>
        <div class="panel-body" id="panel_order">
		    <table cellspacing="0" class="table table-bordered" id="table1">
		        <tbody>
		            <tr>
			            <td >选择退货商品</td>
			            <td id="select_return" >
			            <input type="hidden" id="return_goods" value="" name="return_goods">
			            </td>
		            </tr>
		            <tr>
			            <td >输入补差金额</td>
			            <td ><input type='text' value='0'  class="span11" placeholder="换货商品金额与退货商品金额不相等，可录入差价" name='bc_je' id='bc_je' ></td>
		            </tr> 
		            <tr>
			            <td >选择换货商品</td>
			            <td >
			            <input type='text' value=''  class="span11" name='select_goods' id='select_goods' placeholder="支持商品名称，商品编码，商品条形码查询">
			            <input type="button" class="button" id="btn-search" onclick="select_change_goods()" value="查询" />
			            </td>
		            </tr>
		        </tbody>
		    </table>
		    </div>
		    <div  id="result_grid" class="panel-body">
		  
		  	</div>
		  	<div class="clearfix" style="text-align:right;margin-top:50px;display:none;" id="save_change">
                                <input class="button button-primary" onclick="save_continue_change_goods()" value="保存并继续" />
		  		<input class="button button-primary" onclick="save_change_goods()" value="保存并退出" />
		  	</div>
		  	 <div class="panel-body" style="color:red;">
		  	<span>温馨提示：<br /></span>
		  	<span>1.关于补差金额，：<br /></span>
		  	<span>如果退货商品价格为100，换货商品价格为120，则录入补差价为20；<br /></span>
		  	<span>如果退货商品价格为100，换货商品价格为80，则录入的补差价为-20；<br /></span>
		  	<span>2.如果补差金额大于0，换货单生成时，系统将自动设问</span>
		  	</div>
		</form>
    
</div>

<script>

BUI.use('bui/select',function(Select){
	  
    var items = <?php echo json_encode($response['return_goods']) ?>,
    select = new Select.Select({
        render:'#select_return',
        valueField:'#return_goods',
        width : 400,
        items:items
    });
    select.render();
});
var skuSelectorStore;
$(function () {
    //右下方结果表格++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    BUI.use(['bui/grid', 'bui/data', 'bui/form','bui/tooltip'], function (Grid, Data, Form,Tooltip) {
        //数据变量---------------------------------------------------------------
        var grid = new Grid.Grid();
        var select_goods = $("#select_goods").val();
        skuSelectorStore = new Data.Store({
        url : '?app_act=oms/sell_return/select_change_goods',
        autoLoad:false, //自动加载数据
        autoSync: true,
        pageSize:10	// 配置分页数目
      });
        var columns = [
						{title: '', dataIndex: '',width: 60,'sortable':false, renderer : function(value,obj){
			
                                                     return '<input type="radio" class="input-small" name="change_sku"  value="'+obj.sku+'"/>';
		    
						    
						}},
						{title: '数量', dataIndex: 'num',width: 100,'sortable':false, renderer : function(value,obj){
						    return '<input type="text" class="input-small" id="'+obj.sku+'"   data-rules="{number:true,min:1}"  value="1"/>';
						}},
            			{title: '商品编码', dataIndex: 'goods_code', width: 100,'sortable':false},
						{title: '商品名称', dataIndex: 'goods_name', width: 100,'sortable':false},
            			{title: '<?php echo $response['goods_spec1_rename'];?>', dataIndex: 'spec1_name',width: 60,'sortable':false},
            			{title: '<?php echo $response['goods_spec2_rename'];?>', dataIndex: 'spec2_name', width: 60,'sortable':false},
            			{title: '商品条形码', dataIndex: 'barcode', width: 100,'sortable':false},
            			{title: '可用库存', dataIndex: 'available_mum', width: 80,'sortable':false},
                		]; 
      grid = new Grid.Grid({
          render: '#result_grid',
          columns: columns,
          idField: 'goods_code',
          store: skuSelectorStore
      });
      grid.render(); 
    });
    
    	        
     
});

function select_change_goods(){
	var select_goods = $("#select_goods").val();
	//var store_code = '<?php echo $request['store_code'];?>';
        var sell_return_code = '<?php echo $request['sell_return_code'];?>';
	if (select_goods == ""){
		BUI.Message.Alert("请输入换货商品名称、编码、条码后点击查询", 'error');
		return false;
	} 
	var obj = {goods_multi:select_goods,sell_return_code:sell_return_code};
	$("#save_change").css('display','');
	skuSelectorStore.load(obj);
	
}

$(document).ready(function (){
	select();	
})

function select(){
	$("#select_goods").keydown(function(e) {
                 if (e.keyCode == "13") {//keyCode=13是回车键
            	 select_change_goods();
            }
        });
}


//保存换货商品
function save_change_goods(){
	var return_goods = $("#return_goods").val();
	var bc_je = $("#bc_je").val();
	if(return_goods == ""){
		BUI.Message.Alert("请选择退货商品", 'error');
		return false;
	}
	if(bc_je == ""){
		BUI.Message.Alert("请输入补差金额", 'error');
		return false;
	}
	var boolCheck=$('input:radio[name="change_sku"]').is(":checked");
	if (!boolCheck){
		BUI.Message.Alert("请勾选一个换货商品", 'error');
		return false;
	} 
	var change_sku = $("input:radio:checked[name='change_sku']").val();
	//var num = $("#"+change_sku).val();
	var num = $(document.getElementById(change_sku)).val();
	var deal_code = '<?php echo $request['deal_code'];?>';
	var url = "?app_fmt=json&app_act=oms/sell_return/do_add_change_goods&sell_return_code=<?php echo $request['sell_return_code'];?>&store_code=<?php echo $request['store_code'];?>";
	var data = {sku:change_sku,num:num,bc_je:bc_je,return_goods:return_goods,deal_code:deal_code}
	$.post(url,data,function(data){
		var type = data.status == 1 ? 'success' : 'error';
        if(type == 'error'){
           BUI.Message.Alert(data.message, 'error')
       }else {
              parent._action("change_goods", "view");
             parent._action("return_money", "view");
           BUI.Message.Alert(data.message, function(){
           	ui_closePopWindow("<?php echo $request['ES_frmId']?>")
           },type)
           
       }
	},'json');
}

//保存并且继续换货商品
function save_continue_change_goods(){
	var return_goods = $("#return_goods").val();
	var bc_je = $("#bc_je").val();
	if(return_goods == ""){
		BUI.Message.Alert("请选择退货商品", 'error');
		return false;
	}
	if(bc_je == ""){
		BUI.Message.Alert("请输入补差金额", 'error');
		return false;
	}
	var boolCheck=$('input:radio[name="change_sku"]').is(":checked");
	if (!boolCheck){
		BUI.Message.Alert("请勾选一个换货商品", 'error');
		return false;
	} 
	var change_sku = $("input:radio:checked[name='change_sku']").val();
	var num = $(document.getElementById(change_sku)).val();
	var deal_code = '<?php echo $request['deal_code'];?>';
	var url = "?app_fmt=json&app_act=oms/sell_return/do_add_change_goods&sell_return_code=<?php echo $request['sell_return_code'];?>&store_code=<?php echo $request['store_code'];?>";
	var data = {sku:change_sku,num:num,bc_je:bc_je,return_goods:return_goods,deal_code:deal_code}
	$.post(url,data,function(data){
		var type = data.status == 1 ? 'success' : 'error';
        if(type == 'error'){
           BUI.Message.Alert(data.message, 'error')
       }else {
//           skuSelectorStore.load(); 
           
             parent._action("change_goods", "view");
             parent._action("return_money", "view");
             window.location.reload();
             
       }
	},'json');
}
</script>
