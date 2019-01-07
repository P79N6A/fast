var DataTable = {
		getButtons:function(btnVars, field, obj, idFieldName) {
			var btns = btnVars[field];
			var str = '', isShow, i=0;
			for (i in btns) {
				btn = btns[i];
				if (typeof btn.show_cond !== 'undefined' && btn.show_cond != '') {
					isShow = (new Function('obj', 'return '+btn.show_cond+';'))(obj)
				} else {
					isShow = true;
				}
				if (isShow) {
					click = '';
					/*if (typeof btn.url != 'undefined') {
						value = obj[idFieldName];
						url = btn.url+'&_id='+value;
						click = 'onclick="openPage(\''+ES_PAGE_ID+'$'+btn.id+'$'+value+'\', \''+url+'\',\''+btn.show_name+'\')"';				
					}*/
					str += '<span class="grid-command '+btn.id+'" '+click+'>'+btn.title+'</span>';
				}
			}
			
			return str;
		}
}
var LANG = {	// 后面移到专门的语言文件中
	'ok' 		: '确认',
	'cancel' 	: '取消',
}
function lang(key) {
	return typeof LANG[key] != 'undefined' ? LANG[key] : key;
}

function openPage(_id, _url, _title) {
	top.topManager.openPage({ id : _id,  href : _url, title : _title});
}

function template(template, data){  
    var outPrint="";  
    var matchs = template.match(/\{[a-zA-Z_]+\}/gi);  
    if (!matchs) {
		return template;
	}
    var temp="";  
    for(var j = 0 ; j < matchs.length ;j++){  
        if(temp == "")  
            temp = template;  
        var re_match = matchs[j].replace(/[\{\}]/gi,"");  
        temp = temp.replace(matchs[j],data[re_match]);  
    }  
    outPrint += temp;  

    return outPrint;  
} 

function ui_getDefaultSize() {
	return {w: 400, h: 300}
}
function ui_showPopupForm(title, opts, callback) {
	top.__ui_showPopupForm(title, opts, callback);
}
function __ui_showPopupForm(title, opts, callback) {
	var _url = opts.url
	var w = opts.w, h = opts.h;
	var size = ui_getDefaultSize();
	if (typeof w == 'undefined') w = size.w;
	if (typeof h == 'undefined') h = size.h;

	var form;
	var dialog = new BUI.Overlay.Dialog({
		title: title,
		width: w,
		height: h,
		closeAction : 'destroy', //每次关闭dialog释放
		loader : {
			url : _url,
			autoLoad : true, //不自动加载
			lazyLoad : false,
			callback : function(){
				var node = dialog.get('el').find('form');//查找内部的表单元素
				
				form = new BUI.Form.HForm({
					srcNode : node,
					autoRender : true,
					callback : function(data){
						callback(data);
						form && form.destroy();
						dialog.close();
					}
				});
			}
		},
		buttons:[
			{
				text: lang('ok'),
				elCls : 'button button-primary',
				handler : function(){
					if (form && form.get('action') != '') {
						form.ajaxSubmit();
					} else {
						form && form.destroy();
						dialog.close();
					}
				}
			},
			{
				text: lang('cancel'),
				elCls : 'button',
				handler : function(){
					form && form.destroy();
					dialog.close();
				}
			}
        ],
		mask:true
	});
	dialog.show();
}