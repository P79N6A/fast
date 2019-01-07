<div class="upload1">
    <div class="row form-actions actions-bar">
        <div class="span13 offset3 ">

        <a class="button" style="float: left;" target="_blank" href="<?php echo get_excel_url("wbm_return_notice_record.xlsx",1) ?>">模版下载</a>
        <div style="float: left;margin-left: 10px;"><span id="J_Uploader" style="display: inline-block;"></span></div>
    <!--     <button type="button" class="button button-success" value="新增商品导入" id="btnimport" ><i class="icon-plus-sign icon-white"></i> 商品导入</button>-->
        </div>
   </div>
</div>
<div class="result1" style="display: block">
            
<font color="red"> 提示：若商品已存在，则覆盖数量</font>
<br />
<font color="red" id="result2"> </font>
</div>

<script type="text/javascript">

	var id = "<?php echo $response['id']; ?>";
	BUI.use('bui/uploader',function (Uploader) {
	    /**
	    * 返回数据的格式
	    *
	    *  默认是 {url : 'url'},否则认为上传失败
	    *  可以通过isSuccess 更改判定成功失败的结构
	    */
		var url = '?app_act=pur/order_record/import_goods&app_fmt=json';
		//     ?app_act=prm/goods/do_record_import
		var filetype = {
			ext: ['.csv,.xlsx,.xls','文件类型只能为{0}'],
			maxSize: [2048, '文件大小不能大于2M'],
			//minSize: [1, '文件最小不能小于1k!'],
			max: [5, '文件最多不能超过{0}个！'],
			//min: [1, '文件最少不能少于{0}个!'],
		};
		
		var uploader = new Uploader.Uploader({
			'type':'iframe',
			render: '#J_Uploader',
			url: url,
			rules:filetype,
			multiple:false,
			//可以直接在这里直接设置成功的回调
			success: function(result){
                         BUI.Message.Confirm('确定要导入数据吗？', function () {
                            var url = result.url;
                            var r = '?app_act=wbm/return_notice_record/import_goods&app_fmt=json&id='+id;
                            var params = {"url": url};
                            var msg = '';
                            $.post(r, params, function(data){
                                var type = data.status == 1 ? 'success' : 'error';
                                $('#result2').html(data.message);
                                }, "json");
                           }, 'question');
			},
		//失败的回调
			error: function(result){
				BUI.Message.Alert("失败", "error")
			}
		}).render();
	});
        
</script>              
