<style type="text/css">
.bui-uploader-button-text {
  	color: #33333;
    font-size: 14px;
    line-height: 20px;
    text-align: center;
}

.bui-uploader .bui-uploader-button-wrap .file-input-wrapper {
    display: block;
    height: 20px;
    left: 0;
    overflow: hidden;
    position: absolute;
    top: 0;
    width: 60px;
    z-index: 300;
}
.defaultTheme .bui-uploader-button-wrap {
/* 	background:none; */
    background: rgba(0, 0, 0, 0) -moz-linear-gradient(center top , #fdfefe, ) repeat scroll 0 0;
    border-radius: 4px;
    color: #333;
    display: inline-block;
    font-size: 14px;
    height: 20px;
    line-height: 20px;
    margin-right: 10px;
    overflow: hidden;
    padding: 0;
    position: relative;
    text-align: center;
    text-decoration: none;
    z-index: 500;
    padding: 2px 12px;
}
.bui-uploader-htmlButton {
float:left;
}
.bui-simple-list {
float:left;
}
.check-express-no{
	margin-bottom: 10px;
    margin-top: -15px;
}
</style>


<div class="upload1">
    <div class="row form-actions actions-bar">
        <div class="span13 offset3 ">
            <a class="button" style="float: left;" target="_blank" href="<?php echo get_excel_url("import_export_no.xlsx",1) ?>">模版下载</a>
            <div style="float: left;"><span id="J_Uploader" style="display: inline-block;"></span></div>
            <input type="hidden" name="upload_url" id="upload_url" value="" ><br><br>
            <button style="float:left;" type="button" class="button button-success" value="导入快递单号" id="submit_import_goods" ><i class="icon-plus-sign icon-white"></i> 导入快递单号</button>&nbsp;&nbsp;<input type="checkbox" value="1" name="check_express_no" id="check_express_no">效验物流单号
        </div>
        <div style="clear: both"></div>
    </div>
</div>
<div class="result1" style="display: block">
    <div style="color:red;margin-left: 10px;">
        提示：<br>1、若订单已有快递单号，则覆盖数据<br>2、导入快递单号，默认为包裹1，不支持多包裹。
    </div>
</div>

<script type="text/javascript">
	var id = "<?php echo $request['waves_record_id']; ?>";
	BUI.use('bui/uploader',function (Uploader) {
                             /**
                             * 返回数据的格式
                             *
                             *  默认是 {url : 'url'},否则认为上传失败
                              *  可以通过isSuccess 更改判定成功失败的结构
                              */
	   var url = '?app_act=oms/deliver_record/import_express&app_fmt=json';
                        //     ?app_act=prm/goods/do_record_import
	   var filetype = {
	          	ext: ['.csv,.xlsx,.xls','文件类型只能为{0}'],
	           	maxSize: [2048, '文件大小不能大于2M'],
	            minSize: [1, '文件最小不能小于1k!'],
	            max: [5, '文件最多不能超过{0}个！'],
	            min: [1, '文件最少不能少于{0}个!'],
	    };

	    var uploader = new Uploader.Uploader({
	          'type':'iframe',
	          render: '#J_Uploader',
	          url: url,
	          rules:filetype,
	          multiple:false,
	          //可以直接在这里直接设置成功的回调
	          success: function(result){
	              $("#upload_url").val(result.url);
	          },
	          //失败的回调
	          error: function(result){
	        	  console.log("error"+result);
	              BUI.Message.Alert("失败", "error")
	          }
	     }).render();
	});

$(document).ready(function (){
	$("#submit_import_goods").click(function() {
        //  alert(r);
        var check_express_no = $("#check_express_no").is(':checked');
        var url = $("#upload_url").val();
        if(url == ''){
  			BUI.Message.Alert('请先上传文件', 'error');
  			return false;
  		}
      	var r = '?app_act=oms/deliver_record/do_record_import&id=<?php echo $request['waves_record_id']?>&check_express_no='+check_express_no+'&app_fmt=json';
      	var params = {"url": url};
      	var msg = '';
      	$.post(r, params, function(data){
			var type = data.status == 1 ? 'success' : 'error';
            if (type == 'success') {

                BUI.Message.Alert('导入成功'+data.message);

            } else {
                BUI.Message.Alert(data.message);
            }

	    }, "json");
    });
});

</script>
