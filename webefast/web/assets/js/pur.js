/**
	**统一操作
	*/
	function _do_operate(url,data,ref){ 
		 $.ajax({
	            type: 'POST', dataType: 'json',
	            url: url,
	            data: data,
	            success: function (ret) {
	                var type = ret.status == 1 ? 'success' : 'error';
	                if (type == 'success') {
	                    BUI.Message.Alert(ret.message, type);
	                    if(ref == 'table'){
	                    	
	                    	tableStore.load();
	                    }else{
	                    	location.reload();
	                    }
	                } else {
	                    BUI.Message.Alert(ret.message, type);
	                }
	            }
	        });	
	}
    /**
     * 生成采购订单
     * @param _index
     * @param row
     * @param active
     * @private
     */
//    function _do_execute(url, ref,title='选择商品',width='880',height='400') {
function _do_execute(url, ref,title,width,height) {
        if(typeof(title) == undefined){
            title='选择商品';
        }
        if(typeof(width) == undefined){
            width='880';
        }
        if(typeof(height) == undefined){
            height='400';
        }
    	new ESUI.PopWindow(url, {
            title: title,
            width:width,
            height:height,
            onBeforeClosed: function() {
            },
            onClosed: function(){
                //刷新数据
            	if(ref == 'table'){
                	tableStore.load();
                }else{
                	location.reload();
                }
                
            }
        }).show()
        
    }
