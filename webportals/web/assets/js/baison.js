var _this=this;
var selectHashtable;//弹窗
var listHashtable;//列表
var submitUrlHashtable;//列表
var browser;
var clientHeightIE7;
var clientWidthIE7;
var clientHeightFF;
var clientWidthFF;
var iframeName = "rightIframe";
var iframe = true;
var root_js_path;

/********解决ie低版本问题************/
if(!String.prototype.trim){
	String.prototype.trim = function() { return this .replace(/^\s /, '' ).replace(/\s $/, '' );}
}
/********解决ie低版本问题************/
/********需要手动配置************/
function get_js_path(){
	var js_path = jQuery("script").last().attr("src");
    var path_arr = js_path.split("/");
    var num = path_arr.length;
    js_path = js_path.replace(path_arr[num-1],"");
    root_js_path = js_path;
    return js_path;
}

/********需要手动配置************/

function clientWidthAndHeight(){
	if(typeof clientHeightIE7 == "undefined" && typeof clientHeightFF == "undefined"){
		if(document.documentElement.scrollTop)
		    clientHeightIE7 = document.documentElement.clientHeight+document.documentElement.scrollTop+5;
		else
			clientHeightIE7 = document.documentElement.clientHeight;
			clientWidthIE7 = document.documentElement.clientWidth;
		if(document.body.scrollTop)
		    clientHeightFF = document.body.clientHeight+document.body.scrollTop+5;
		else
			clientHeightFF = document.body.clientHeight;
			clientWidthFF = document.body.clientWidth;
	}
}
function browser_version(){
	if(typeof browser == "undefined"){
		if(jQuery.browser.msie && jQuery.browser.version ==6){
			browser = "ie6";
		}
		
		if(jQuery.browser.msie && jQuery.browser.version ==7){
			browser = "ie7";
		}
		
		if(jQuery.browser.msie && jQuery.browser.version ==8){
			browser = "ie8";
		}
		
		if(jQuery.browser.msie && jQuery.browser.version ==9){
			browser = "ie9";
		}
		
		if(jQuery.browser.msie && jQuery.browser.version ==10){
			browser = "ie10";
		}
		
		if(jQuery.browser.msie && jQuery.browser.version ==11){
			browser = "ie11";
		}
		
		if(jQuery.browser.mozilla){
			browser = "ff";
		}
		
		if(jQuery.browser.safari){
		  if(window.navigator.userAgent.indexOf("Chrome") != -1)
		    browser = "chrome";
		  else
		    browser = "safari";
		}
	}
}

function Hashtable() {
	this._hash = new Object();
	this.add = function (key, value) {
		if (typeof (key) != "undefined") {
			if (this.contains(key) === false) {
				this._hash[key] = typeof (value) == "undefined" ? null : value;
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	};
	this.remove = function (key) {
		delete this._hash[key];
	};
	this.count = function () {
		var i = 0;
		for (var k in this._hash) {
			i=i+1;
		}
		if(i==0)return "0";
		return i;
	};
	this.items = function (key) {
		return this._hash[key];
	};
	this.contains = function (key) {
		return typeof (this._hash[key]) != "undefined";
	};
	this.clear = function () {
		for (var k in this._hash) {
			delete this._hash[k];
		}
	};
}

function obj_length(obj){
	var i = 0;
	for (var k in obj) {
		i=i+1;
	}
	if(i==0)return "0";
	return i;
}
//设置cookie
function setCookie(c_name,value,expiredays){
    var exdate=new Date();
    exdate.setDate(exdate.getDate()+expiredays);
    document.cookie=c_name+ "=" +escape(value)+((expiredays==null) ? "" : "; expires="+exdate.toGMTString());  
}

//读取cookie
function getCookie(cookieName){//获取指定名称的cookie的值
   var arrStr = document.cookie.split("; ");
   for(var i = 0;i < arrStr.length;i ++){
    var temp = arrStr[i].split("=");
    if(temp[0] == cookieName) 
    return unescape(temp[1]);
   }
}

function js_error(message){
	browser_version();
	if(browser == "ff"){
		setTimeout(function(){alert(message);throw new Error(message);},100);
	}else
		throw new Error(message);
}

/**
 * 
 * @param div jquery对象
 */
function checkBoxAll(div){
	div.find("input[type='checkbox']").attr("checked",true);
}

/**
 * 
 * @param div jquery对象
 */
function checkBoxNoAll(div){
	div.find("input[type='checkbox']").attr("checked",false);
}

function check_box_choose(div){
	var ckeck = div.find("input[type='checkbox']:first").attr("checked");
	if(typeof ckeck == "undefined")
		ckeck = false;
	
	div.find("input[type='checkbox']").attr("checked",ckeck);
}

function check_box_choose_by_id(id){
	var ckeck = jQuery("#"+id).find("input[type='checkbox']:first").attr("checked");
	if(typeof ckeck == "undefined")
		ckeck = false;
	
	jQuery("#"+id).find("input[type='checkbox']").attr("checked",ckeck);
}

function alert_msg(message){
	alert(message);
}

function refresh(){
	window.location.reload();
}

function reset(){
	jQuery("form").each(function(){
		this.reset();
	})
}

//获取2点之间的距离
function get_distance(lat1,lng1,lat2,lng2){
	lat1 = Math.round(lat1*10000)/10000;
	lng1 = Math.round(lng1*10000)/10000;
	lat2 = Math.round(lat2*10000)/10000;
	lng2 = Math.round(lng2*10000)/10000;
	var EARTH_RADIUS = 6378137.0;    //单位M
    var PI = Math.PI;
    
    function getRad(d){
        return d*PI/180.0;
    }
    
    var f = getRad((lat1 + lat2)/2);
    var g = getRad((lat1 - lat2)/2);
    var l = getRad((lng1 - lng2)/2);
    
    var sg = Math.sin(g);
    var sl = Math.sin(l);
    var sf = Math.sin(f);
    
    var s,c,w,r,d,h1,h2;
    var a = EARTH_RADIUS;
    var fl = 1/298.257;
    
    sg = sg*sg;
    sl = sl*sl;
    sf = sf*sf;
    
    s = sg*(1-sl) + (1-sf)*sl;
    c = (1-sg)*(1-sl) + sf*sl;
    
    w = Math.atan(Math.sqrt(s/c));
    r = Math.sqrt(s*c)/w;
    d = 2*w*a;
    h1 = (3*r -1)/2/c;
    h2 = (3*r +1)/2/s;
    
    return Math.round(d*(1 + fl*(h1*sf*(1-sg) - h2*(1-sf)*sg)));
}

function get_url_parameter(){
	var parameterUrl = new Object();
	var parameterUrlArray = document.location.href.split('&');
	for(var i=1;i < parameterUrlArray.length;i++){
		var temp = parameterUrlArray[i].split('=');
		temp[1] = temp[1].replace(/#[\s\S]*/g,"");
		parameterUrl[temp[0]] = temp[1];
	}
	//处理第一个
	var temp = parameterUrlArray[0].split('=');
	if(typeof temp[1] != "undefined"){
		temp[1] = temp[1].replace(/#[\s\S]*/g,"");
		var first_parameter = temp[0].split('?');
		parameterUrl[first_parameter[1]] = temp[1];
	}

	return parameterUrl;
}

function getParameter(divClass){
	var data=new Object();
	var parameter = new Object();
	var parameterUrl = new Object();
	if(typeof window.frames[iframeName] != "undefined"){
		var parameterUrlArray = window.frames[iframeName].document.location.href.split('&');
	}else{
		var parameterUrlArray = document.location.href.split('&');
	}
	for(var i=1;i < parameterUrlArray.length;i++){
		var temp = parameterUrlArray[i].split('=');
		temp[1] = temp[1].replace(/#[\s\S]*/g,"");
		parameterUrl[temp[0]] = temp[1];
	}
	
    jQuery("."+divClass).find("textarea").each(function(){
		if(jQuery(this).val() != null && typeof jQuery(this).attr("name") != "undefined")	{
			if(jQuery(this).attr("name") != "")
				parameter[jQuery(this).attr("name")] = jQuery(this).val();
		}		
			 
	})
    
	jQuery("."+divClass).find("input[type='text']").each(function(){
		if(jQuery(this).val() != null && typeof jQuery(this).attr("name") != "undefined" && jQuery(this).attr("name").indexOf("valueClass")<0)	{
			if(jQuery(this).attr("name") != "")
				parameter[jQuery(this).attr("name")] = jQuery(this).val();
		}		
			 
	})
	
	jQuery("."+divClass).find("input[type='hidden']").each(function(){
		if(jQuery(this).val() != null && typeof jQuery(this).attr("name") != "undefined" && jQuery(this).attr("name").indexOf("hideClass")<0){
			if(jQuery(this).attr("name") != "")
				parameter[jQuery(this).attr("name")] = jQuery(this).val();
		}
	})
	
	jQuery("."+divClass).find("input[type='password']").each(function(){
		if(jQuery(this).val() != null && typeof jQuery(this).attr("name") != "undefined" && jQuery(this).attr("name").indexOf("hideClass")<0)
			parameter[jQuery(this).attr("name")] = jQuery(this).val(); 
	})
	
	jQuery("."+divClass).find("input[type='checkbox']").each(function(){
		if(jQuery(this).attr("checked")){
			if(parameter[jQuery(this).attr("name")]){
				if(typeof jQuery(this).attr("check") != "undefined"){
					parameter[jQuery(this).attr("name")] = parameter[jQuery(this).attr("name")] + "," + jQuery(this).attr("check");
				}else{
					parameter[jQuery(this).attr("name")] = parameter[jQuery(this).attr("name")] + "," + jQuery(this).val();
				}
				
			}else{
				if(typeof jQuery(this).attr("check") != "undefined"){
					parameter[jQuery(this).attr("name")] = jQuery(this).attr("check");
				}else{
					parameter[jQuery(this).attr("name")] = jQuery(this).val();
				}
				
			}
		}else{
			if(typeof jQuery(this).attr("no_check") != "undefined"){
				if(!parameter[jQuery(this).attr("name")]){
					parameter[jQuery(this).attr("name")] = jQuery(this).attr("no_check");
				}
				
			}
		}
	})
	
	jQuery("."+divClass).find("input[type='radio']").each(function(){
		if(jQuery(this).attr("checked")){
			if(parameter[jQuery(this).attr("name")]){
				parameter[jQuery(this).attr("name")] = parameter[jQuery(this).attr("name")] + "," + jQuery(this).val();
			}else{
				parameter[jQuery(this).attr("name")] = jQuery(this).val();
			}
		}	
	})
	
	jQuery("."+divClass).find("select").each(function(){
		if(jQuery(this).val() != null)
			parameter[jQuery(this).attr("name")] = jQuery(this).val(); 
	})
	
	jQuery("."+divClass).find("textarea").each(function(){
		if(jQuery(this).val() != null && jQuery(this).attr("name") != "")
			parameter[jQuery(this).attr("name")] = jQuery(this).val(); 
	})
	
	//fck编辑器值
	
	if(typeof(CKEDITOR) != "undefined" && typeof(CKEDITOR.instances.text) != "undefined" && jQuery("."+divClass).find("textarea[name='text']").length > 0){
		parameter['text'] = CKEDITOR.instances.text.getData();
	}
	if(typeof(CKEDITOR) != "undefined" && typeof(CKEDITOR.instances.text) != "undefined" && jQuery("."+divClass).find("textarea[name='text1']").length > 0){
		parameter['text1'] = CKEDITOR.instances.text1.getData();
	}
	data['parameter'] = parameter;
	data['parameterUrl'] = parameterUrl;
	return data;
}


function detail(){
	if(jQuery(".addList").length > 0){
		jQuery(".addList").find("input[type='text']").each(function(){
			jQuery(this).replaceWith("<span>"+jQuery(this).val()+"</span>");
		})
		jQuery(".addList").find("select").each(function(){
			if(jQuery(this).find("option:selected").text() == "请选择"){
				jQuery(this).replaceWith("<span></span>");
			}else{
				jQuery(this).replaceWith("<span>"+jQuery(this).find("option:selected").text()+"</span>");
			}
		})
		jQuery(".addList").find("textarea").each(function(){
			jQuery(this).replaceWith("<span>"+jQuery(this).val()+"</span>");
		})
	}
	if(jQuery(".goods").length > 0){
		jQuery(".goods").find("input[type='text']").each(function(){
			jQuery(this).replaceWith("<span>"+jQuery(this).val()+"</span>");
		})
		jQuery(".goods").find("select").each(function(){
			if(jQuery(this).find("option:selected").text() == "请选择"){
				jQuery(this).replaceWith("<span></span>");
			}else{
				jQuery(this).replaceWith("<span>"+jQuery(this).find("option:selected").text()+"</span>");
			}
		})
		jQuery(".goods").find("textarea").each(function(){
			jQuery(this).replaceWith("<span>"+jQuery(this).val()+"</span>");
		})
	}
	
	jQuery("input[type='checkbox']").attr("disabled", true);  
	jQuery(".addListSubmit").remove();
}

function cutoverUrl(url,parent){
	var href;
	var url;
	var url_parameter = get_url_parameter();
	
	if(parent)
		href = window.parent.location.href;
	else
		href = window.location.href;
	var temp = href.split("?");
	
	if(temp.length >1){
		var href2 = temp[1].replace(temp[1].replace(/.*\//g,""),url);
		url = temp[0]+"?"+href2;
	}else{
		url = href.replace(href.replace(/.*\//g,""),url);
	}
	if(typeof url_parameter['s'] != "undefined"){
		url += "&s="+url_parameter['s'];
	}
	return url;
}

function get_app_url(action,parent){
	var href;
	var url;
	var url_parameter = get_url_parameter();
	
	if(parent)
		href = window.parent.location.href;
	else
		href = window.location.href;
	var temp = href.split("?");
	
	if(temp.length >1){
		var href2 = temp[1].replace(/app_act=.*/g,"app_act="+action);///app_act=[\w/]+/g
		url = temp[0]+"?"+href2;
	}else{
		url = href.replace(/app_act=.*/g,"app_act="+action);
	}
	if(typeof url_parameter['s'] != "undefined"){
		url += "&s="+url_parameter['s'];
	}
	return url;
}

//showModalDialog时用
function obj_to_obj(obj){
	var return_obj = new Object();
	
	for (var k in obj) {
		return_obj[k] = obj[k];
	}
	return return_obj;
}

function get_path(){
	var href = window.location.href;
	var arr = href.split("/");
	return arr[arr.length-1];
}

function urlJump(url,parameter,filter){
	url = url.replace(/#.*/g,"");
	
	if(typeof parameter != "undefined" && parameter){
		var parameterUrlArray = document.location.href.split('&');
		for(var i=1;i < parameterUrlArray.length;i++){
			var temp = parameterUrlArray[i].split('=');
			temp[1] = temp[1].replace(/#(.*?)/g,"");
			//parameterUrl[temp[0]] = temp[1];
			if(jQuery.inArray(temp[0], filter) == -1){
				url += "&"+temp[0]+"="+temp[1];
			}
		}
	}
	window.location.href=url;
		
}

function urlJumpTab(title,url,parameter){
	url = url.replace(/#.*/g,"");
	
	if(typeof parameter != "undefined" && parameter){
		var parameterUrlArray = document.location.href.split('&');
		for(var i=1;i < parameterUrlArray.length;i++){
			var temp = parameterUrlArray[i].split('=');
			temp[1] = temp[1].replace(/#(.*?)/g,"");
			//parameterUrl[temp[0]] = temp[1];
			url += "&"+temp[0]+"="+temp[1];
		}
	}

	addTab(title,url);
}

function ajax_post(parameter){
	var load = true;
	if(typeof parameter['dataType'] == "undefined")
		parameter['dataType'] = "json";

	if(typeof parameter['alert'] == "undefined")
		parameter['alert'] = true;
	
	if(typeof parameter['load'] != "undefined" && parameter['load'] == false)
		load = false;
	
	if(typeof parameter['async'] == "undefined")
		parameter['async'] = false;
	
	if(typeof parameter['timeout'] == "undefined")
		parameter['timeout'] = 0;
	
	/*if(load){
		var loadDiv = "<div class='loadImage'><img src='"+project+"webpub/js/opendiv/images/loadingGray.gif'></div>";
		jQuery("body").append(loadDiv);
	}*/
	if(typeof parameter.data == "undefined"){
		parameter.data = new Object();
	}
	
	if(parameter['dataType'] == "json"){
		parameter.data['app_fmt'] = "json";
	}
	
	jQuery.ajax({
  	    type: "POST",
		cache: false,
		url: parameter.url,
		data:parameter.data,
		//data:parameter,
		dataType:parameter['dataType'],
		async:parameter['async'],
		timeout:parameter['timeout'],
		success: function(value){
			if(typeof value != "object" && parameter['dataType'] == "json"){
				alert("程序内部错误");
				return;
			}
			jQuery(".loadImage").remove();
			if(parameter['dataType'] == "html"){
				parameter.callback(value);
			}else{
				if(value == null || value == "" || typeof value == "undefined")
					return;
				
				value = eval(value);
				if(typeof value.message != "undefined" && value.message != ""){
					if(typeof value.message == "string" && parameter['alert'])
						alert(value.message);
				}
				if(typeof parameter.callback == "function")
					parameter.callback(value);
				if(typeof value.data != "undefined" && value.data != ""){
					if(value.data == "refresh")
						window.location.reload();
					else if(typeof value.data == "String")
						window.location.href=value.data;
				}
			}
		},
		error:function(value){
			jQuery(".loadImage").remove();
		}
	 })
}

function bind_select(id,url,callback,parameter,pageCallback){
	setTimeout(function(){
		var bind_id = id;
		var style = 1;
		
		if(typeof parameter != "undefined" && typeof parameter['style'] != "undefined"){
			style = parameter['style'];
		}
		
		if(typeof parameter != "undefined" && typeof parameter['id'] != "undefined")
			delete parameter['id'];
		else
			parameter = new Object();
		
		
		jQuery("#"+bind_id).click(function(){
			if(typeof parameter['beforeCallback'] != "undefined"){
				parameter = parameter['beforeCallback'](parameter);
			}
			
			if(typeof callback != "function" || callback == null)
				callback = null;
			
			parameter['url_parameter'] = get_url_parameter();

			var listurl = "?app_act=common/select/"+url+"&id="+bind_id;
			var open_select = new opendiv();
			//open_select.parameter = parameter;
			open_select.init({
				"id":"open_select"+bind_id,
				"action":"url",
				"html":listurl,
				"width":800,
				"height":500,
				"parameter":parameter,
				"async":false,
				"style":style,
				"callback":function(){
					select.prototype = new page();//继承page
					select.prototype.mode = "ajax";
					select.prototype.parameter = parameter;
					
					var open_select_list = new select();
					
					open_select_list.init("select_page_table"+bind_id,listurl,open_select_list.select_bind);
					open_select_list.select_init("open_select"+bind_id,callback);
					if(typeof parameter['tpl'] != "undefined")
						open_select_list.parameter['tpl'] = parameter['tpl'];
					
					var obj = new Object();
					obj['open_select'] = open_select;
					obj['open_select_list'] = open_select_list;
					if(typeof selectHashtable == "undefined")
						selectHashtable = new Hashtable();
					selectHashtable.add(bind_id,obj);
					
					//判断是否是树形结构
					jQuery("#"+"open_select"+bind_id).find("#tree_search").click(function(){
						open_select_list.parameter['tree'] = get_all_tree_value();
						open_select_list.search();
					})
					
					jQuery("#"+"open_select"+bind_id).find("#tree_search_clear").click(function(){
						jQuery("#"+"open_select"+bind_id+" .sq_tree").find("input[type='checkbox']").attr("checked",false);
						delete open_select_list.parameter['tree'];
						open_select_list.search();
					})
				}
			})
			if(typeof pageCallback == "function")
				pageCallback();
		})
	},1);
}

//id,url,value,parameter,callback
function bind_input(parameter){
	var _this_parameter = parameter;
    
	var id = parameter['id'];
	var url = parameter['url'];
	
	var hideClassHtml = "";
	var valueClassHtml = "";
	
	var value = "";
	
	if(typeof parameter['value'] != "undefined")
		value = parameter['value'];
	
	if(typeof parameter['parameter'] == "undefined" || parameter['parameter'] == "")
		parameter = {id:id};
	
	if(typeof _this_parameter['tpl'] != "undefined")
		parameter['tpl'] = _this_parameter['tpl'];
	
	if(typeof _this_parameter['enter'] != "undefined")
		parameter['enter'] = _this_parameter['enter'];
	else
		parameter['enter'] = false;
	
	if(typeof _this_parameter['beforeCallback'] != "undefined")
		parameter['beforeCallback'] = _this_parameter['beforeCallback'];
	
	if(jQuery("#"+id).val() != ""){
		parameter['key'] = jQuery("#"+id).val();
	}
	setTimeout(function(){
		if(jQuery("."+id).length > 0){
			jQuery("."+id).remove();
		}
		
		var randomNum = id;
		var valueClass = randomNum+"_valueClass";
		var hideClass = randomNum+"_hideClass";
		
		var inputHtml = "<input class='"+id+"_value' type='hidden' name='"+id+"_value' value='"+value+"'>";
		inputHtml += "<input id='"+valueClass+"' disabled='true' class='"+valueClass+" "+id+"' type='text' name='"+valueClass+"' value='"+value+"'>";
		inputHtml += "<input id='"+id+"_button' class='bind_input_button' type='button' />";
		if(typeof _this_parameter['clear'] == "undefined" || _this_parameter['clear'])
			inputHtml += "<span class='"+valueClass+"clear "+id+" search_clear'>清除</span>";
		inputHtml += "<input class='"+hideClass+" "+id+"' type='hidden' name='"+hideClass+"'>";
		
		jQuery("#"+id).before(inputHtml);
		jQuery("#"+id).hide();
		var useragent = navigator.userAgent; 
		if(useragent.indexOf('iPhone') != -1||useragent.indexOf('Android') != -1){
			jQuery("#"+id+"_button").css({
				width:"28px",
				height:"25px",
				display:"inline-block",
				border:"0px",
				background:"#fff url("+project+"webpub/style/images/mobile/search_bg.jpg) no-repeat"
			})
		}else{
			jQuery("#"+id+"_button").css({
				width:"20px",
				height:"20px",
				display:"inline-block",
				background:"#fff url("+project+"webpub/images/search.gif) no-repeat"
			})
		} 
		
		/*jQuery("#"+id+"_button").css({
			width:"20px",
			height:"20px",
			display:"inline-block",
			background:"#fff url("+project+"webpub/images/search.gif) no-repeat"
		})*/
		
		//赋显示值
		if(typeof list != "undefined" && typeof list.parameter != 'undefined' && typeof list.parameter["search"] != "undefined"){
			if(list.parameter["search"][""+id+"_value"] != "" && typeof list.parameter["search"][""+id+"_value"] != "undefined"){
				jQuery("#"+valueClass).val(list.parameter["search"][""+id+"_value"]);
				jQuery("input[name='"+id+"_value']").val(list.parameter["search"][""+id+"_value"]);
			}
		}
		
		//清除显示的值
		if(jQuery("#"+id).val() == ""){
			jQuery("."+valueClass).val("");
		}
		
		jQuery("."+valueClass+"clear").bind("click",function(){
			jQuery("."+valueClass).val("");
			jQuery("."+hideClass).val("");
			jQuery("#"+id).val("");
			jQuery("."+id+"_value").val("");
		})
		
		//点击框
		bind_select(id+"_button",url,function(value){
			hideClassHtml = "";
			valueClassHtml = "";
			for(var i=0;i < value.length;i++){
				if(value[i] instanceof jQuery){//返回jquery对象时
					hideClassHtml += value[i].find("#key").text()+",";
					if(value[i].find("#value").length > 0)
						valueClassHtml += value[i].find("#value").text()+",";
					else
						valueClassHtml += value[i].find("#key").text()+",";
				}else{
					hideClassHtml += value[i]['key']+",";
					if(typeof value[i]['value'] != "undefined")
						valueClassHtml += value[i]['value']+",";
					else
						valueClassHtml += value[i]['key']+",";
				}
			}
			
			hideClassHtml=hideClassHtml.slice(0,-1);
			valueClassHtml=valueClassHtml.slice(0,-1);
			jQuery("."+hideClass).val(hideClassHtml);
			jQuery("."+valueClass).val(valueClassHtml+"["+hideClassHtml+"]");
			jQuery("input[name='"+id+"_value']").val(valueClassHtml);
			jQuery("#"+id).val(hideClassHtml);
			if(typeof _this_parameter['callback'] == "function")
				_this_parameter['callback'](value);
		},parameter);
		
		//联想输入
		if(!parameter['enter']){
			jQuery("#"+valueClass).keyup(function(event){
				var inputs = jQuery("body").find("input[type='text']"); // 获取表单中的所有输入框  
				var input_num = inputs.index(this); // 获取当前焦点输入框所处的位置 
				
				if(event.keyCode == 13){
					hideClassHtml = "";
					valueClassHtml = "";
					var search = new Object();
					search[jQuery("#"+id).attr('name')] = jQuery("#"+valueClass).val();
					ajax_post({
						url:"?app_act=common/select/"+url,
						data:{search:search},
						callback:function(value){
							value = value.data;
							var num = value.length;
							if(num > 1){
								alert("数据超过2条");
							}else if(num == 0){
								alert("没有数据");
							}else if(num == 1){
								hideClassHtml=value[0].baisonkey;
								valueClassHtml=value[0].baisonvalue;
								jQuery("."+hideClass).val(hideClassHtml);
								jQuery("."+valueClass).val(valueClassHtml+"["+hideClassHtml+"]");
								jQuery("input[name='"+id+"_value']").val(valueClassHtml);
								jQuery("#"+id).val(hideClassHtml);
								
								
								var table_hide = "<table id='bind_input_table' style='display:none'><tr>";
								for(var i=0;i < value.length;i++){
									for (var k in value[i]){
										table_hide += "<td class='"+k+"'>"+value[i][k]+"</td>";
									}
								}
								
								table_hide += "</tr></table>";
								
								if(jQuery("#bind_input_table").size() > 0)
									jQuery("#bind_input_table").remove();
								
								jQuery("body").append(table_hide);
								
								var back_value = new Array();
								jQuery("#bind_input_table").find("tr").each(function(){
									back_value.push(jQuery(this));
								})

								jQuery("#bind_input_table").remove();
								if(typeof _this_parameter['callback'] == "function")
									_this_parameter['callback'](back_value);
								
								setTimeout(function(){
									jQuery("input[type='text']").eq(input_num+2).focus();
									jQuery("input[type='text']").eq(input_num+2).select();
								},10);
								
							}
						}
					});
				}
			})
		}
		
		//焦点事件
		jQuery("#"+valueClass).focus(function(){
			if(hideClassHtml != ""){
				jQuery("#"+valueClass).val(hideClassHtml);
			}
		})
		jQuery("#"+valueClass).blur(function(){
			if(hideClassHtml != ""){
				jQuery("#"+valueClass).val(valueClassHtml+"["+hideClassHtml+"]");
			}
		})
	},1);
}

function bind_input_open(parameter){
	var _this_parameter = parameter;
    
	var id = parameter['id'];
	var url = parameter['url'];
	
	var value = "";
	
	if(typeof parameter['value'] != "undefined")
		value = parameter['value'];
	
	if(typeof parameter['parameter'] == "undefined" || parameter['parameter'] == "")
		parameter = {id:id};
	
	if(typeof _this_parameter['tpl'] != "undefined")
		parameter['tpl'] = _this_parameter['tpl'];
	
	if(typeof _this_parameter['enter'] != "undefined")
		parameter['enter'] = _this_parameter['enter'];
	else
		parameter['enter'] = false;
	
	if(jQuery("#"+id).val() != ""){
		parameter['key'] = jQuery("#"+id).val();
	}
	setTimeout(function(){
		if(jQuery("."+id).length > 0){
			jQuery("."+id).remove();
		}
		var randomNum = id;
		//点击框
		bind_select(id,url,function(value){
                    var returnData = [];
                    for(var i=0;i < value.length;i++){
                        var tmpData = [];
                        if(value[i] instanceof jQuery){
                            //返回jquery对象时
                                tmpData['key'] = value[i].find("#key").text();
                                if(value[i].find("#value").length > 0)
                                        tmpData['value'] = value[i].find("#value").text();
                                else
                                        tmpData['value'] = value[i].find("#key").text();
                        }else{
                                tmpData['key'] = value[i]['key'];
                                if(typeof value[i]['value'] != "undefined")
                                        tmpData['value'] = value[i]['value'];
                                else
                                        tmpData['value'] = value[i]['key'];
                        }
                        returnData.push(tmpData);
                    }
 
                    if(typeof _this_parameter['callback'] == "function")
                        _this_parameter['callback'](returnData);
		},parameter);
		
	},1);
}

function clear_bind_input(id){
	jQuery("#"+id).show();
	jQuery("."+id+"_value").remove();
	jQuery("#"+id+"_valueClass").remove();
	jQuery("#"+id+"_button").remove();
	jQuery(".id").remove();
}

function replace_select(html){
	html = html.replace("<div>","");
	html = html.replace("<\/div>","");
	html = html.replace("<DIV>","");
	html = html.replace("<\/DIV>","");
	return html;
}

function add_detail(parameter){
	parameter_url = get_url_parameter();
	jQuery.ajax({
	    type: "POST",
    	cache: false,
    	url: parameter['url'],
    	data:{"parameter":parameter['parameter'],"parameter_url":parameter_url},
    	dataType: 'json',	
    	success: function(value){
    		var html = "";
    		value = eval(value);
    		if(typeof value.status != "undefined"){
    			alert(value.message);
    			return;
    		}
    		
    		for(var i=0;i<value.length;i++){
    			html += "<tr>";
    			for(var j=0;j < value[i].length;j++){
    				html += "<td>"+value[i][j]+"</td>";
    			}
    			html += "</tr>";
    		}
    		jQuery("#"+parameter['table_id']).find("tr:last").after(html);
    	}
    })
}

function checkbox_choose(div,_this){
	if(jQuery(_this).attr("checked")){
		checkBoxAll(div);
	}else{
		checkBoxNoAll(div);
	}
}

function view_list(parameter){
	var loadDiv = "<div class='loadImage'><img src='"+project+"webpub/js/opendiv/images/loadingYellow.gif'></div>";
	jQuery("body").append(loadDiv);
	jQuery.ajax({
  	    type: "POST",
		cache: false,
		async:false,
		url: parameter['url'],
		success: function(html){
			if(html == "")
				return;

			html = html.replace(/<script(.*?)<\/script>|<link(.*?)><\/link>|<script>[\s\S]*?<\/script>/gi,"");
				
			jQuery("#"+parameter['table_id']).append(html);
			
			var list = new page();
			list.change_exist = false;
			if(typeof parameter['callback'] == "function"){
				parameter['callback']();
				list.init(parameter['table_id'],parameter['url'],parameter['callback']);
			}	
			else
				list.init(parameter['table_id'],parameter['url']);
			
		}
	 })
	 jQuery(".loadImage").remove();
}

function is_edit(id){
	jQuery("#"+id).find("input").attr("disabled",false);
	jQuery("#"+id).find("select").attr("disabled",false);
	jQuery("#"+id).find("textarea").attr("disabled",false);
	jQuery("#"+id).find(".search_clear").show();
	jQuery("#"+id).find(".bind_input_button").show();
	if(jQuery("#edit_button").length > 0){
		jQuery("#edit_button").attr("disabled",true);
	}
	if(jQuery("#cancel_button").length > 0){
		jQuery("#cancel_button").attr("disabled",false);
	}
	if(jQuery("#save_button").length > 0){
		jQuery("#save_button").attr("disabled",false);
	}
	if(jQuery("#operate_button").length > 0){
		jQuery("#operate_button").hide();
	}
}

function cancel(id){
	jQuery("form").each(function(){
		this.reset();
	})
	setTimeout(function(){
		jQuery("#"+id).find("input").attr("disabled",true);
		jQuery("#"+id).find("select").attr("disabled",true);
		//jQuery("#"+id).find("textarea").attr("disabled",true);
		jQuery("#"+id).find(".search_clear").hide();
		jQuery("#"+id).find(".bind_input_button").hide();
		if(jQuery("#edit_button").length > 0){
			jQuery("#edit_button").attr("disabled",false);
		}
		if(jQuery("#cancel_button").length > 0){
			jQuery("#cancel_button").attr("disabled",true);
		}
		if(jQuery("#save_button").length > 0){
			jQuery("#save_button").attr("disabled",true);
		}
		if(jQuery("#operate_button").length > 0){
			jQuery("#operate_button").show();
		}
	},50);
}

function get_all_tree_value(){
	var ret_value_obj = new Object();
	ret_value_obj['checkbox'] = new Object();
	ret_value_obj['checkbox']['last'] = new Object();
	ret_value_obj['checkbox']['all'] = new Object();
	ret_value_obj['radio'] = new Array();
	for (var k in tree_hash._hash){
		var obj = tree_hash._hash[k];
		var id = obj.id;
		var value_arr = new Array();
		jQuery("#"+id).find(".last").each(function(){
			var checked = jQuery(this).find(".tree_checkbox").attr("checked");
			if(typeof checked != "undefined"){
				var value_obj = new Object();
				value_obj['key'] = jQuery(this).attr("name");
				value_obj['value'] = jQuery(this).attr("id");
				value_arr.push(value_obj);
			}
		})
		ret_value_obj['checkbox']['last'][id] = value_arr;
		
		var value_arr = new Array();
		jQuery("#"+id).find("input[type='checkbox']").each(function(){
			var checked = jQuery(this).attr("checked");
			if(typeof checked != "undefined"){
				var value_obj = new Object();
				value_obj['key'] = jQuery(this).parent().attr("name");
				value_obj['value'] = jQuery(this).parent().attr("alt");
				value_obj['name'] = jQuery.trim(jQuery(this).parent().text());
				value_arr.push(value_obj);
			}
		})
		
		ret_value_obj['checkbox']['all'][id] = value_arr;
		
		var value_arr = new Array();
		jQuery("#"+id).find("input[type='radio']").each(function(){
			var checked = jQuery(this).attr("checked");
			if(checked){
				ret_value_obj['radio']['key'] = jQuery(this).parent().attr("alt");
				ret_value_obj['radio']['value'] = jQuery.trim(jQuery(this).parent().text());
			}
		})
	}
	return ret_value_obj;
}

function close_tab(parameter){
	if(typeof parameter == "undefined")
		parameter = new Object();
	
	if(jQuery("#titlebar",parent.document).length == 0){
		window.close(); 
		return;
	}
	
	var url;
	jQuery("#right",parent.document).find("iframe").each(function(){
		if(jQuery(this).css("display") == "block")
			url = jQuery(this).attr("src");
	});
	
	if(typeof parent.tabHashtable != "undefined"){
		setTimeout(function(){
			var hashtab =parent.tabHashtable.items(url);
			hashtab.close(parameter);
		},300);
	}
}

function enter_to_tab(cls,id){
	jQuery("."+cls).find("input[type='text'],select").keyup(function(event){
		if(event.keyCode == 13){
			var inputs = jQuery("."+cls).find("input[type='text'],select"); // 获取表单中的所有输入框  
			var input_num = inputs.index(this); // 获取当前焦点输入框所处的位置 
			input_num++;
			jQuery("."+cls).find("input[type='text'],select").eq(input_num).focus();
		}
		
		if(event.ctrlKey && event.which == 13){
			jQuery("#"+id).trigger("click");
		}
	})
}
/************UI js**********/
function iframeHeight(){
	var wHeight = $(window).height();
	jQuery("#right iframe",parent.document).height(wHeight-98);
	jQuery("#right iframe",parent.document).contents().find('#content').height(wHeight-131);
}

/*
 * api手台手工运行
 * */
function api_run(url){
	var loadDiv = "<div class='loadImage'><img src='webpub/js/opendiv/images/loadingYellow.gif'></div>";
	jQuery("body").append(loadDiv);
	jQuery.ajax({
  	    type: "POST",
		cache: false,
		url: "?app_act="+url,
		dataType: 'json',
		success: function(value){
			jQuery(".loadImage").remove();
			value = eval(value);
			if(typeof value.message != "undefined")
				alert(value.message);
		}
	})
}