var goods_imgUploader;
BUI.use('bui/uploader', function(Uploader){
	var _filepath = eval('('+$("#goods_img").val()+')');
	goods_imgUploader = new Uploader.Uploader({
		render: '#goods_imgUploader',
		url: '?app_act=common/file/upload',rules: {
		ext: ['.jpg,.png,.gif', '文件类型只能为{0}'],
		max: ['2', '文件的最大个数不能超过{0}个']
		},
		success: function(result){
              
			var _path = $.parseJSON(result.data.path);
			_filepath.push(_path);
			$('#goods_img').val(JSON.stringify(_filepath));
			if (typeof goods_imgUploader_success == 'function') {
				goods_imgUploader_success(result);
			}
		},
		//失败的回调
		error: function(result){
                    
			if (typeof goods_imgUploader_error == 'function') {
				goods_imgUploader_error(result);
			}
                $('#goods_imgUploader .uploader-error').eq(0).html(result.message);
		},
		//根据业务需求来判断上传是否成功，这里返回一个boolean
		isSuccess: function(result){ 
		if (result.status == 1) {
                    return true;
		}
               
		return false;
		}
	}).render();
	var queue = goods_imgUploader.get('queue');
	queue.on('itemremoved',function(ev){
		var arr = $.parseJSON($('#goods_img').val());
		var realPath = $.parseJSON(ev.item.data.path)[0];
		for (var i = 0 ; i < arr.length; i++) {
			if (arr[i][0] == realPath) {
				arr.splice(i, 1);
				break;
			}
		}
		$('#goods_img').val(JSON.stringify(arr));
	});
}); 