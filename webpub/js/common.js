var DataTable = {
	getButtons : function(btnVars, field, obj, idFieldName) {
		var btns = btnVars[field];
		var str = '', isShow, i = 0;
		for (i in btns) {
			btn = btns[i];
			if (typeof btn.show_cond !== 'undefined' && btn.show_cond != '') {
				isShow = (new Function('obj', 'return ' + btn.show_cond + ';'))
						(obj)
			} else {
				isShow = true;
			}
			if (isShow) {
				click = '';
				str += '<span class="grid-command ' + btn.id + '" ' + click
						+ ' es_btn_id="'+btn.id+'">' + btn.title + '</span>';
			}
		}

		return str;
	}
}
var LANG = { // 后面移到专门的语言文件中
	'ok' : '确认',
	'cancel' : '取消',
	'confirm_close_pop_window': '确认要关闭当前弹出窗体吗？'
}
function lang(key) {
	return typeof LANG[key] != 'undefined' ? LANG[key] : key;
}

function openPage(_id, _url, _title) {
        _url += '&ES_frmId='+_id;
	if(typeof top.topManager == "undefined"){
		window.open(_url, "_blank");
	}else{
		top.topManager.openPage({
			id : _id,
			href : _url,
			title : _title
		});
	}
}

/**
 * 新打开一个页签
 * @param _id
 * @param _url
 * @param _title
 */
function ui_openTabPage(_id, _url, _title) {
	openPage(_id, _url, _title);
}

/**
 * 关闭页签
 * @param _id 页签id
 */
function ui_closeTabPage(_id) {
	top.topManager.closePage(_id);
}

/**
 * 获取grid的某个单元格的DOM（jquery对象）
 * @param grid BUI.grid.grid BUI grid对象
 * @param idFieldValue string 
 * @param colName string 
 * @returns
 */
function ui_getGridCell(grid, idFieldValue, colName) {
	return $(grid.findElement(grid.getItem(idFieldValue))).find('td[data-column-field="'+colName+'"]');
}

function openUrlPage(_url) {
	window.open(_url, "_blank");
}

function template(template, data) {
	var outPrint = "";
	var matchs = template.match(/\{[a-zA-Z_]+\}/gi);
	if (!matchs) {
		return template;
	}
	var temp = "";
	for ( var j = 0; j < matchs.length; j++) {
		if (temp == "")
			temp = template;
		var re_match = matchs[j].replace(/[\{\}]/gi, "");
		temp = temp.replace(matchs[j], data[re_match]);
	}
	outPrint += temp;

	return outPrint;
}

function ui_getDefaultSize() {
	return {
		w : 400,
		h : 300
	}
}
function ui_showPopupForm(title, opts, callback) {
	top.__ui_showPopupForm(title, opts, callback);
}
function __ui_showPopupForm(title, opts, callback) {
	var _url = opts.url
	var w = opts.w, h = opts.h;
	var size = ui_getDefaultSize();
	if (typeof w == 'undefined')
		w = size.w;
	if (typeof h == 'undefined')
		h = size.h;

	var form;
	var dialog = new BUI.Overlay.Dialog({
		title : title,
		width : w,
		height : h,
		closeAction : 'destroy', // 每次关闭dialog释放
		loader : {
			url : _url,
			autoLoad : true, // 不自动加载
			lazyLoad : false,
			callback : function() {
				var node = dialog.get('el').find('form');// 查找内部的表单元素

				form = new BUI.Form.HForm({
					srcNode : node,
					autoRender : true,
					callback : function(data) {
						callback(data);
						form && form.destroy();
						dialog.close();
					}
				});
			}
		},
		buttons : [ {
			text : lang('ok'),
			elCls : 'button button-primary',
			handler : function() {
				if (form && form.get('action') != '') {
					form.ajaxSubmit();
				} else {
					form && form.destroy();
					dialog.close();
				}
			}
		}, {
			text : lang('cancel'),
			elCls : 'button',
			handler : function() {
				form && form.destroy();
				dialog.close();
			}
		} ],
		mask : true
	});
	dialog.show();
}
/**
 * 
 */
if (typeof top.window.ESUI_windows == 'undefined') {
	top.window.ESUI_windows = {}; // 记录ESUI 弹出窗体的实例
}

var ESUI = {
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
                                this.close();
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

/**
 * 通过ES_frmId关闭弹出窗体
 * @param id 弹出窗体的ES_frmId
 */
function ui_closePopWindow(id) {
	if (typeof top.window.ESUI_windows[id] != 'undefined') {
		top.window.ESUI_windows[id].close();
		top.window.ESUI_windows[id] = undefined;
		delete top.window.ESUI_windows[id];
	}
}
/**
 * 关闭最顶层弹出窗口
 */
function ui_closeTopPopWindow() {
	var arr = [];
	for (var i in top.window.ESUI_windows) {
		arr.push(parseInt(i));
	}
	if (arr.length > 0) {
		var topId = Math.max.apply(null, arr);
		ui_closePopWindow(''+topId);
	}
}

/**
 * 获取顶层弹出窗体的id
 */
function ui_getTopPopWindowId() {
	var arr = [];
	for (var i in top.window.ESUI_windows) {
		arr.push(parseInt(i));
	}
	if (arr.length > 0) {
		return Math.max.apply(null, arr)+'';
	}
	return false;
}
/**
 * 通过iframe name获取顶级窗口中的iframe对象
 */
function getTopFrameByName(name) {
	var frame = null;
	// ie 通过frames[id]\frames[name] 都无法获取到frame
	if (typeof top.window.frames[name] == 'undefined') {
		for (var i = 0; i < top.window.frames.length; i++) {
			if (top.window.frames[i].name == name) {
				frame = top.window.frames[i];
			}
		}
	} else {
		frame = top.window.frames[name];
	}
	return frame;
}

function getTopFrameWindowByName(name) {
	var _frame = getTopFrameByName(name);
	if (typeof _frame != 'undefined' && _frame != null && typeof _frame.window != 'undefined') {
		return _frame.window;
	}
	
	return _frame.contentWindow;
}

var ES = {
	Format : {},
	Util: {
		getThemeUrl : function (url) {
			return ES_CONST.THEME_URL+url;
		}
	}
};
//判断浏览器类型
function myBrowser(){
    var userAgent = navigator.userAgent; //取得浏览器的userAgent字符串
    var isOpera = userAgent.indexOf("Opera") > -1;
    if (isOpera) {
        return "Opera"
    }; //判断是否Opera浏览器
    if (userAgent.indexOf("Firefox") > -1) {
        return "FF";
    } //判断是否Firefox浏览器
    if (userAgent.indexOf("Chrome") > -1){
        return "Chrome";
    }
    if (userAgent.indexOf("Safari") > -1) {
        return "Safari";
    } //判断是否Safari浏览器
    if (userAgent.indexOf("compatible") > -1 && userAgent.indexOf("MSIE") > -1 && !isOpera) {
        return "IE";
    }; //判断是否IE浏览器
}
function alert_message(message){
     BUI.use('bui/overlay',function(Overlay){
     var dialog = new Overlay.Dialog({
      // title:'非模态窗口',
       autoHideDelay : 1000,
       autoHide : true,
       effect:{effect:'fade'},
       cls : 'tips tips-success',
       mask:false,  //设置是否模态
       bodyContent:'这是一个非模态窗口,并且不带按钮',
       tpl : '<span class="x-icon x-icon-small x-icon-success"><i class="icon icon-white icon-ok"></i></span>\
        <div class="tips-content">{title}</div>'
     });
   dialog.show();
 });
}

ES.Format.mapChecked = function(value, row, index) {
	if (value == 1) {
		return '<img src="'+ES.Util.getThemeUrl('images/ok.png')+'" />';
	} else {
		return '<img src="'+ES.Util.getThemeUrl('images/no.gif')+'" />';	
	}
}

$(function() {
	if ($('.actions-bar').length > 0) {
		$('.actions-bar').prev().css('border', 'none');
	} else {
		$('form .row:last').css('border', 'none');
	}
});


BUI.use('bui/form',function (Form) {
    Form.Rules.add({
            name : 'passwordstrength1',
            msg : '6-16位，区分大小写，必须同时包含大写字母、小写字母、数字、特殊字符。只能使用字母、数字、特殊字符',
            validator : function(value, basevalue, formatMsg){
            	var s = value;
            	var ls = 0;
            	
            	if(s.length < 6 || s.length>16){
            	    ls++;
            	}
            	if(s.match(/([a-z])+/)){
            		ls++;
            	}
            	if(s.match(/([0-9])+/)){
            		ls++;  
            	}
            	if(s.match(/([A-Z])+/)){
            		ls++;
            	}
            	if(s.match(/[^a-zA-Z0-9]+/)){
            		ls++;
            	}
            	if (ls < 4) {
            		return formatMsg;
            	}
            }
        }); 

});

(function ($) {
    $(document).ready(function () {
        /** Coding Here */
    }).keydown(function (e) {
        if (e.which === 27) {
        	var id = ui_getTopPopWindowId();
        	if (id != false) {
	            if (confirm(lang('confirm_close_pop_window'))) {
	            	ui_closePopWindow(id);
	            }
         	}
        }
    });
	
})(jQuery);