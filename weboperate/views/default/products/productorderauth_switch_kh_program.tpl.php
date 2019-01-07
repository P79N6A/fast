<form  class="form-horizontal" id="form1" action="?app_act=products/productorderauth/do_switch_kh_program" method="post">
<input type="hidden" id="pra_id" name="pra_id" value="<?php echo $response['data']['pra_id'];?>" />
		<div class="row">				<div class="control-group span11">
		<label class="control-label span3">客户名称：                </label>
                   
		<div class="controls " >
		  <label class="control-label control-operate-label" style="text-align:left" ><?php echo $response['data']['pra_kh_id_name']; ?></label>	    </div>
		</div>
	</div>				<div class="row">				<div class="control-group span11">
		<label class="control-label span3">当前程序版本：       
            
                
                </label>
                   
		<div class="controls " >
		  <label class="control-label control-operate-label" style="text-align:left" >    <?php $arr = ds_get_field('pra_program_version'); echo $arr[$response['data']['pra_program_version']];
                ?></label>	    </div>
		</div>
	</div>				<div class="row">				<div class="control-group span11">
		<label class="control-label span3">选择新的程序版本：           </label>
                   
		<div class="controls " >
		  <label  class="control-label control-operate-label" style="text-align:left" ></label>	    </div>
		</div>
	</div>				<div class="row">				<div class="control-group span11">
                <label class="control-label span3">   &nbsp; &nbsp; &nbsp; &nbsp;           </label>
                   
		<div class="controls " >
		  <label  class="control-label control-operate-label" style="text-align:left" >
                      正式版本  <input type="radio" name="pra_program_version" value="standard"  />
                  </label>	    </div>
		</div>
	</div>	
        
        		<div class="row">				<div class="control-group span11">
		        <label class="control-label span3">   &nbsp; &nbsp; &nbsp; &nbsp;           </label>
                   
		<div class="controls " >
		  <label class="control-label control-operate-label" style="text-align:left" >
                      BETA版本 <input type="radio" name="pra_program_version" value="beta"  />
                  </label>	    </div>
		</div>
	</div>	
        		<div class="row">				<div class="control-group span11">
		        <label class="control-label span3">   &nbsp; &nbsp; &nbsp; &nbsp;           </label>
                   
		<div class="controls " >
		  <label  class="control-label control-operate-label" style="text-align:left" >
                      客制化版本  <input type="radio" name="pra_program_version" value="customer"  />
                  </label>	    </div>
		</div>
	</div>	
        	<div class="row" id="vm_ip_div" >				<div class="control-group span11">
	        <label class="control-label span3">   &nbsp; &nbsp; &nbsp; &nbsp;           </label>
                   
		<div class="controls " >
		  <label  class="control-label control-operate-label" style="text-align:left" >
                      自动服务IP  <input  type="text" name="vm_ip" id="vm_ip" value=""  />
                      <span style="color:red"  > 客制化也为程序IP</span>
                  </label>	    </div>
		</div>
	</div>	
        
<div class="row form-actions actions-bar">
<div class="span13 offset3 ">
<button type="submit" class="button button-primary" id="submit">确认</button>
<button type="reset" class="button " id="reset">重置</button>
</div>
</div>
    

</form>
<script type="text/javascript">
        
    var form =  new BUI.Form.HForm({
       srcNode : '#form1',
       submitType : 'ajax',
       callback : function(data){
            var type = data.status == 1 ? 'success' : 'error';
            if (data.status == 1) {
                ui_closePopWindow('<?php echo $request['ES_frmId'] ?>'); 	

                window.location.reload();
            } else {
                BUI.Message.Alert(data.message, function() { }, type);
            }
        }
   }).render();
   
    function formBeforesubmit() {
	return true; // 如果不想让表单继续提交，则return false
    }
//    $(function(){
//        $('input[name="pra_program_version"]').click(function(){
//            if($(this).val()=='customer'){
//                $('#vm_ip_div').show();
//            }else{
//                   $('#vm_ip_div').hide();
//            }
//        });
//    });
</script>