


<div class="page-header1" style="margin-top: 10px;">
<span class="page-title">
<h2>导出文件下载</h2>
</span>
</div>
<div class="clear"></div>
<hr>



    <form class="form-horizontal well" id="show_select">
      <div class="row">
          <div class="span24" style="padding-left: 100px;">
      请选择导出文件格式:
        </div>
      
      </div>
          <div class="row">
        <div class="span24" style="padding-left: 300px;">
          <label class="control-label control-label-small"></label>
          <div class="controls">
              excel<input type="radio" id="execl" value="execl" name="file_type"  /> 格式(XLSX文件后缀，适用office 2007以后版本，<span style="color: red;">数据上限1万条</span>)<br/>
          </div>
        </div>
      
      </div>
          <div class="row">
        <div class="span24" style="padding-left: 300px;">
          <label class="control-label control-label-small"></label>
          <div class="controls"> csv<input type="radio" id="csv" value="csv" name="file_type"  /> (<span style="color: red;">如果数据超过1万条，建议使用csv格式</span>)</div>
        </div>
            
      </div>       
    </form>

<div id="show_download" style="display:none">
    <div class="row form-actions actions-bar">
   
		<div class="span24 offset3 " style="text-align:center">			
			 提示：文件正在生成中，请耐心等待，生成后，请点击下载
				   		
				</div>
														
                               </div>

<div id="loading" class="row span24" style="text-align:center;"  > 
    <img src="<?php echo get_theme_url('images/loading.gif');?>" />
</div>

<div class="row span24" style="text-align:center;display:none"  id="loading_ok" >
      <button id="download"  class="button button-success" name="download" type="buuton">下载</button>  
</div>
     </div>                    




<input id="task_id" type="hidden" name="task_id" value="<?php echo $response['data']['task_id']; ?>"  />
<input id="file_key" type="hidden"   name="file_key" value="<?php echo $response['data']['file_key']; ?>"   />
<input id="export_name" type="hidden"   name="export_name"  value="<?php echo $response['data']['export_name']; ?>"   />
<script type="text/javascript">
$(function(){
    
    $('input[name="file_type"]').click(function(){
        $('#show_select').hide();
        $('#show_download').show();
        
         var url ="?app_act=sys/export_csv/create_export_task&app_fmt=json&ctl_export_file_type="+$(this).val();
        var  param = <?php echo json_encode($request);?> ;
            $.post(url,param,function(ret){
                 $('#task_id').val(ret.data.task_id);
                  $('#file_key').val(ret.data.file_key);
                   $('#export_name').val(ret.data.export_name);
              get_status();
            
         },'json'); 
        
    });

    function get_status(){
         var url ="?app_act=sys/export_csv/get_export_status&app_fmt=json";
         var param = {};
         param.task_id = $('#task_id').val();
         $.post(url,param,function(ret){
             if(ret.data=='2'){
                 $('#loading').hide();
                 $('#loading_ok').show();
             }else{
                 setTimeout(function(){ get_status(); },3000);
             }
         },'json');
    }
    $('#download').click(function(){
         
        var url = '?app_act=sys/export_csv/download_execl&app_fmt=json&file_type='+$('input[name="file_type"]:checked').val();
         url+='&file_key='+$('#file_key').val();
         url+='&export_name='+$('#export_name').val();
         url+='&user_token='+'<?php echo create_user_token($request['ctl_export_name']);?>';

        window.location.href=url;
    });
  
})
</script>