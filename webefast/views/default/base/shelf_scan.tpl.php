	<table style="margin: 50px auto 0px auto; width:700px;">

            <tr>
                <th style="width:80px; text-align: right;">仓库：</th>
                <td style="width:50px; ">
                <select name="store_code" id="store_code" style="width:150px;"  data-rules="{required : true}">
			    	 <option value ="">请选择仓库</option>
			       <?php foreach($response['store'] as $k=>$v){ ?>
			    	<option  value ="<?php echo $v['store_code']; ?>"  ><?php echo $v['store_name']; ?></option>
			       <?php } ?>
			    </select>
                </td>
                <th style="width:200px; text-align: right;">库位扫描插入：</th>
                <td style="width:50px; "><input id="shelf_code" class="input-normal bui-form-field" type="text" value="" name="shelf_code" ></td>
                <td ><th style="width:200px;color:#F00; text-align: right;" id="msg"></th></td>
            </tr>
            
        </table>
        
  <script type="text/javascript"> 
  $(document).ready(function(){ 
	  $("#shelf_code").keydown(function(e){ 
		  var curKey = e.which; 
		  if(curKey == 13){ 
			 var shelf_code = $(this).val();
			 var store_code = $("#store_code").val();
			 if(shelf_code != '' && store_code != ''){
				 $.ajax({ type: 'POST', dataType: 'json',
					    url: '<?php echo get_app_url('base/shelf/do_add_sm');?>',
					    data: {shelf_code: shelf_code,  store_code: store_code},
					    success: function(ret) {
					    	var type = ret.status == 1 ? 'success' : 'error';
					    	if (type == 'success') {
						        //BUI.Message.Alert(ret.message, type);
						        $("#msg").html("保存成功");
						        $("#shelf_code").val('');
					    	} else {
					    		BUI.Message.Alert(ret.message, type);
					    		$("#msg").html("");
					    		$("#shelf_code").val('');
					    		$("#shelf_code").focus();
					    	}
					    }
						});	 
			 }else{
				 BUI.Message.Alert("仓库或库位不能为空", "error"); 
			 }
		     
		  return false; 
		  } 
	  }); 
  }); 
	 	
  
  </script> 

