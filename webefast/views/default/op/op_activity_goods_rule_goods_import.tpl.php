<div class="upload1">
    <div class="row">
       <div class="control-group span11">
        <label class="control-label span3">文件上传：</label>
        <div class="span8 controls">
            <div id="J_Uploader">
            </div>
        </div>
       </div>  
        
    </div>
     <div><input type="hidden" id="url" name="url"></div>
    <div class="row form-actions actions-bar">
		<div class="span13 offset3 ">
		<button id="submit" class="button button-primary" type="submit">提交</button>
		<a class="button" target="_blank" href="<?php echo get_excel_url("op_api_activity_goods.xlsx",1) ?>">模版下载</a>
		</div>
   </div>
</div>

<script type="text/javascript">

	BUI.use('bui/uploader',function (Uploader) {

        /**
         * 返回数据的格式
         *
         *  默认是 {url : 'url'},否则认为上传失败
         *  可以通过isSuccess 更改判定成功失败的结构
         */
        var uploader = new Uploader.Uploader({
            'type':'iframe',
            render: '#J_Uploader',
            url: '?app_act=op/op_activity_goods/import_goods&app_fmt=json',
            //可以直接在这里直接设置成功的回调
            success: function(result){
            	$("#url").val(result.url);
            },
            //失败的回调
            error: function(result){
                BUI.Message.Alert("失败", "error")
            }
        }).render();
    });
	
$(document).ready(function (){
	$("#submit").click(function() {
        //  alert(r);
        var url = $("#url").val();
        if(url == ''){
  			BUI.Message.Alert('请先上传文件', 'error');
  			return false;
  		}

      	var r = '?app_act=op/op_activity_goods/rule_goods_import_action&app_fmt=json';
      	var params = {"url": url};
      	$.post(r, params, function(data){
	  var type = data.status == 1 ? 'success' : 'error';
          if(type=='success'){
              BUI.Message.Alert("导入成功", type);
          }else{
              BUI.Message.Alert(data.message, type);
          }

	    }, "json");
    });
});
          
</script>              
