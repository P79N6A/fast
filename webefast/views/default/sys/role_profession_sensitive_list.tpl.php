<style>
.bui-pagingbar{float:left;}
.div_title{padding:6px;font-weight:bold;}
.table_panel1{
	width:80%;
	margin-bottom:5px;
 }
.table_panel1 td {
    border:1px solid #dddddd;
    line-height: 20px;
    padding: 5px;
    text-align: left;
}
</style>
<input type="hidden" id="role_code" value="<?php echo $response['role_code'];?>" />
<input type="hidden" id="role_id" value="<?php echo $request['role_id'];?>" />
<input type="hidden" id="profession_type" value="1" />
<?php render_control('PageHead', 'head1',
    array('title'=>'业务/数据权限['.$response['role']['role_code'].'-'.$response['role']['role_name'].']',
        'links'=>array(
            // array('url'=>'sys/role/do_list', 'title'=>'角色列表'),
        ),
    ));?>
<div>
    <form id="searchForm" class="form-horizontal well" tabindex="0" style="outline: none;">
    <table width="100%"><tr><td width="100%">
                								<div class="row">				<div class="control-group">
					<label class="control-label">角色列表</label>
				    <div class="controls">
				   <select name="select_role_code" id="select_role_code" data-rules="{required : true}">
			       <?php foreach($response['role_list'] as $k=>$v){ 
			       	     
			       	?>
			    	<option  value ="<?php echo $v['role_code']; ?>" <?php if($response['role_code'] == $v['role_code']){ ?> selected <?php } ?> ><?php echo $v['role_name']; ?></option>
			       <?php } ?>
			        </select><b style="color:red"> *</b>
				    </div>
				</div>
																</td>
               
                </tr>
                </table>
</form>
</div>

<ul class="nav-tabs oms_tabs">
    <li ><a href="#" onClick="do_page('do_list');">店铺</a></li>
    <li><a href="#" onClick="do_page('store_list');" >仓库</a></li>
    <li><a href="#" onClick="do_page('brand_list');" >品牌</a></li>
    <?php if ($response['version_no'] > 0): ?>
    <li><a href="#" onClick="do_page('supplier_list');" >供应商</a></li>
    <?php endif; ?>
   <li class="active"><a href="#" onClick="do_page('sensitive_list');" >敏感数据</a></li>
   <li><a href="#" onClick="do_page('manage_price');" >价格管控</a></li>
    <li><a href="#" onClick="do_page('custom_list');" >分销商</a></li>
</ul>

<div class="row-fluid msg" > <?php if($response['power'] == '1'){ ?> 已启用敏感数据 权限，停用请点击这里<a href="#" onClick="do_set_active_shenhe('sensitive_power','disable');"> <font color="#0000FF ">停用</font></a> <?php }else{ ?>  未启用 敏感数据 权限，只有启用后才允许配置，启用请猛击这里<a href="#" onClick="do_set_active_shenhe('sensitive_power','enable');"> <font color="#0000FF ">启用</font></a>
 <br> <font color="red">说明：敏感数据权限启用后，敏感数据的导出，将会模糊显示；主要在订单类、会员类数据的导出功能中生效；</font>
<?php } ?></div>
<div class="row-fluid" <?php if($response['power'] == '1'){ ?> style="display:block;" <?php }else{ ?>style="display:none;" <?php } ?>>
 
  	<div >敏感数据导出，模糊化配置列表：</div>
  	<div class="panel">
	    
	    <div class="panel-body">
	    	<div class="row" >
	    	<div class= 'detail'>
	    	<table class='table_panel1' >
	    	<tr><td> 模糊化配置</td> <td> 示例值</td><td> 说明</td>
	    	</tr>
	    	
	    	 <?php foreach($response['sensitive_list'] as $k2=>$v2){ ?>
	    	<tr>
	    	<td> 	    	<input name="<?php echo 'sensi['.$v2['sensitive_code'].']'; ?>" type="checkbox"   <?php if(isset($v2['role_code']) && $v2['role_code'] <> ''){ ?> checked    <?php } ?>  value="<?php echo $v2['sensitive_code'] ?>" />   <?php echo $v2['sensitive_name'] ?></td><td><?php echo $v2['example'] ?></td><td> <?php echo $v2['desc'] ?></td>
	    	 
	    	</tr>
	    	<?php } ?>
	    	
	    	
	    	</table>
	    	</div>
	    	<div >
	    	  <div><font color="red">说明：敏感数据权限启用后，敏感数据的导出，将会模糊显示；主要在订单类、会员类数据的导出功能中生效；</font></div>
	    	  <div><font color="red">目前已生效的列表：订单查询、订单列表、问题订单列表、缺货订单列表、合并订单列表、挂起订单列表、已发货订单列表</font></div>
	    	  <div>&nbsp;</div>
	    	</div>
	    	<div class="btns">
			<button type="button" class="button button-primary"  id="btnSave"  ><i class="icon-plus-sign icon-white"></i> 保存</button>
			<!--  <button class="button button-primary" type="reset">重置</button>-->
	    	</div>
	  
	 </div>
 </div> 
	
	
	
   <div class="row-fluid msg">
  	
   	
  </div>
</div>

<script type="text/javascript">
	var role_code = $("#select_role_code").val();		       	
$(document).ready(function(){
	$("#select_role_code").change(function(){
		role_code = $(this).val();
		det_init(role_code);	
       });
	det_init(role_code);
    btn_save();  
});
function det_init(role_code){
	var data = {
	         'role_code':role_code,
	         'app_page':'NULL'
	     };
	     $.ajax({
	         type : "get",  
	         url : "?app_act=sys/role_profession/sensitive_do_list",  
	         data : data,
	         async : false,
	         success : function(data){
	             //ret = data;
	              
	             $(".detail").html(data);
	         }
	     });
		
}
function btn_save(){
	$("#btnSave").click(function(){
		var ids = new Array();
		$("input[name^='sensi']:checkbox:checked").each(function(){
			ids.push($(this).val());
	        
	    });
	   
	    
	    if(ids.length == 0)
	    {
	    	// BUI.Message.Alert("请选择敏感数据", 'error');
	        //    return;
	    }
			        
        ids.join(',');
        var params = {"ids": ids, "select_role_code": $("#select_role_code").val()};
       	$.post('<?php echo get_app_url('sys/role_profession/sensitive_save');?>', params, function(data){
            var type = data.status == 1 ? 'success' : 'error';
    		if (data.status == 1) {
    			
   			   BUI.Message.Alert('设置成功：', type);
      			det_init(role_code);
    		} else {
    			BUI.Message.Alert(data.message, function() { }, type);
    		}
        }, "json");
          
			
       });
} 	
function do_set_active_shenhe(param_code,active) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '?app_act=sys/params/update_active',
    data: {param_code: param_code, type: active},
    success: function(ret) {
    	var type = ret.status == 1 ? 'success' : 'error';
    	if (type == 'success') {
        window.location.reload();
        //tableStore.load();
    	} else {
        BUI.Message.Alert(ret.message, type);
    	}
    }
	});
}
function do_page(param) {	
    location.href = "?app_act=sys/role_profession/"+param+"&role_code=" + $("#role_code").val()+"&role_id=" + $("#role_id").val()+"&keyword=";
}
</script>


