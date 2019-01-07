var ESUI1 = {
	PopWindow : function(url, options) {
		var settings = {
			"width" : 600,
			"height" : 450,
			"buttons" : [],
			"autoRender" : true,
			closeAction : 'destroy' //每次关闭dialog释放
		};
		options = $.extend(settings, options);
		if (typeof options.id == 'undefined') {
			options.id = new Date().getTime();
		}
		
		url += '&ES_frmId='+options.id;
		top.window.ESUI_windows[options.id] = this; // 
		options.bodyContent = '<iframe src="' + url
				+ '" scrolling="auto" style="width:100%;height:100%" name="'+options.id+'" id="'+options.id+'"></iframe>';
		options.success = function() {
			alert('确认');
			this.close();
		}

		var dialog = new top.BUI.Overlay.Dialog(options);
		dialog.on('beforeclosed', function() {
			if (options.onBeforeClosed && typeof options.onBeforeClosed == 'function') {
				options.onBeforeClosed();
			}
		});

		/**
		 * 显示弹出框,用于form提交后弹出显示
		 */
		this.show = function() {
			dialog.show();
			return this;
		}

		/**
		 * 窗口隐藏
		 */
		this.hide = function() {
			dialog.hide();
			return this;
		}
		
		this.close = function() {
			dialog.close();

			if (options.onClosed && typeof options.onClosed == 'function') {
				options.onClosed();
			}

			return this;
		}

		return this;
	},
	PopSelectWindow : function(url, callbackName, options) {
		if (typeof options.id == 'undefined') {
			options.id = new Date().getTime();
		}
                if(options.selecttype=="tree"){
                    options.buttons = [ {
			text : '取消',
			elCls : 'button',
			handler : function() {
				this.close();
			}
                    }];
                }else{
		options.buttons = [ {
			text : '确认',
			elCls : 'button button-primary',
			handler : function() {
                                getTopFrameByName(options.id).window.ES_getSelection();
                                //this.close();
			}
		}, {
			text : '取消',
			elCls : 'button',
			handler : function() {
				this.close();
			}
		} ];}
		
		return new ESUI.PopWindow(url + '&app_show_mode=select&callback='
				+ callbackName+'&ES_pFrmId='+options.ES_pFrmId, options);
	}
}