<div class="page-header1" style="margin-top: 10px;">
<span class="page-title">
<h2>库存维护</h2>
</span>
</div>
<div class="clear"></div>
<hr>
    <div class="row form-actions actions-bar">
		<div class="span24 offset3 " style="text-align:center">			
				仓库			   
                                        <select id="store_code" name="store_code">
                                            <option value="" >请选择</option>
                                        <?php foreach($response['store']  as $val): ?>
                                            <option value="<?php echo $val['store_code'];?>"><?php echo $val['store_name'];?></option>
                                        <?php endforeach;?>
                                          </select>
				   		
				</div>
														
                               </div>
<div class="row span24" style="text-align:center">
            <button type="button" class="button button-success" value="库存维护" id="inv_maintain_0"><i class="icon-plus-sign icon-white"></i> 库存维护</button>
                    <button type="button" class="button button-success" value="锁定库存维护" id="inv_maintain_1"><i class="icon-plus-sign icon-white"></i> 锁定库存维护</button>
                            <button type="button" class="button button-success" value="缺货库存维护" id="inv_maintain_2"><i class="icon-plus-sign icon-white"></i> 缺货库存维护</button>
     <button type="button" class="button button-success" value="在途库存维护" id="inv_maintain_3"><i class="icon-plus-sign icon-white"></i> 在途库存维护</button>
<?php if($response['close_lof']==1):?>
     <button type="button" class="button button-success" value="关闭批次后批次数据维护" id="inv_maintain_4"><i class="icon-plus-sign icon-white"></i> 关闭批次后批次数据维护</button>
<?php endif?>

</div>


                         
<div class="row" style="text-align:center">
    <div class="span18" id="message"></div>
</div>
                            

<script>
    var run_type = -1;
    var inv_i = <?php echo ($response['close_lof']==1)?5:4 ; ?>;
$(function(){
    
    for(var i=0;i<inv_i;i++){
      set_task(i);
    }
    $('#store_code').change(function(){
        $('#message').html('');
    });
    function set_disabled_but(status){
         for(var i=0;i<inv_i;i++){
           var id= 'inv_maintain_'+i;
          $('#'+id).attr('disabled', status);
        } 
         $('#store_code').attr('disabled', status);
    }
    
    
    function set_task(i){
        var url = '?app_act=prm/inv/set_maintain_task&app_fmt=json';
         var id= 'inv_maintain_'+i;
       $('#'+id).click(function(){
         var data = {};
         var store_code = $('#store_code').val();
         if(store_code==''){
                BUI.Message.Alert('请选择仓库', 'error');
                return ;
         }
         data.store_code=store_code;
         data.type=i;
         run_type = i;
         set_disabled_but(true);
         $.post(url, data, function(result){
        
             if(result.status=='1'){
                   $('#message').html('正在进行'+$('#'+id).val()+'...');
                    get_status();

             }else if(result.status=='-5'){
                  $('#message').html(result.message);    
             }else{
                  BUI.Message.Alert(result.message, 'error');
                   set_disabled_but(false);
             }
         }, 'json');
     });
    }
    var run_i = 0;

    function get_status(){
        var timestamp = new Date().getTime();  
        var url = '?app_act=prm/inv/get_maintain_task&app_fmt=json&timestamp='+timestamp;
        var data = {};
        data.type = run_type;
        data.store_code = $('#store_code').val();

        if(run_type<0){
            return ;
        }
        
        $.post(url, data, function(result){
            var check = 0;
             if(result.status>0){
  
                         if(typeof( result.data.is_over)!= "undefined"){
                            if(result.data.is_over==2){
                                $('#message').html($('#inv_maintain_'+run_type).val()+'完成');
                            }else if(result.data.is_over==3){
                                 $('#message').html($('#inv_maintain_'+run_type).val()+'异常');
                            }else{
                                check++;
                            }
                         }
      
                 if(check>0){
                        setTimeout(function(){get_status();},2000);
                  }else{
                          run_type = -1;
                          set_disabled_but(false);  
                  }
             }else{
                  run_type = -1;
                  set_disabled_but(false);  
                  BUI.Message.Alert(result.message, 'error');
             }
         }, 'json');
    }
    
});

</script>