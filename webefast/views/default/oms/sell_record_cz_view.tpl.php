<?php echo load_js('comm_util.js')?>
<?php echo load_js('jquery.cookie.js') ?>
<style>
    .error_msg{
        position: absolute;
        left: 55%;
        top: 2.4%;
        font:normal bold 1.5em/1.6em arial,sans-serif;
        color: red;
    }
    #MSComm1{
        margin-top:-140px;
    }
    .button-group .active{color:#FFF;background-color: #7EC0EE;border-color:#7EC0EE;}
    .span8{width: 430px}
    #remark{
        color:red;
        float: right;
        font-family: "Microsoft YaHei","微软雅黑","Arial","宋体","Times New Roman",Times,serif;
    }
</style>
<script>
var weight_different = "<?php echo $response['weight_info']['weight_different'];?>";
var weight_different_notice = "<?php echo $response['weight_info']['weight_different_notice'];?>";
var pre_set_num;
var sounds = {
	    "error": "0",
	    "success": "1"
	};

//播放提示音
function play_sound(typ){
    var wav = "../../webpub/js/sound/"+ sounds[typ]+".wav";
    if (navigator.userAgent.indexOf('MSIE') >= 0){//IE
        document.getElementById('bgsound_ie').src = wav;
    } else {// Other borwses (firefox, chrome)
        var obj = document.getElementById('bgsound_others');
        obj.src = wav;
        obj.play();
    }
}

function hotkey(event) 
{
	if(event.keyCode ==13) 
	{ 
	   if (event.target.id=="express_no")
	   {
                    $(".error_msg").html("");
		    if (document.getElementById("express_no").value =='')
		    {   
                        play_sound("error");
                        $(".error_msg").html("物流单号不能为空");
                        document.getElementById("express_no").focus();
                        return;
		    }
           if (pre_set_num == 0) {//选择先称后扫
               warn_weight_check('express_no');
           } else {
               searchSellRecord();
           }
	   }

	}
}


function hotkey_cz(event) {
    var reg = /^\d+(\.\d+)?$/;
    if (event.keyCode == 13) {
        if (($("#cz_weight").val()).replace(/(^\s*)|(\s*$)/g, "") == '') {
            play_sound("error");
            $(".error_msg").html("重量不能为空");
            document.getElementById("cz_weight").focus();
            return;
        } else if (!reg.test(($("#cz_weight").val()).replace(/(^\s*)|(\s*$)/g, ""))) {
            play_sound("error");
            $(".error_msg").html("称重重量不能为非数字或负数");
            document.getElementById("cz_weight").focus();
            return;
        }else{
            $(".error_msg").html("");
        }
        warn_weight_check('cz_weight');
    }
}

//和系统参数的预警重量比较
function warn_weight_check(type) {
    var url = '<?php echo get_app_url('oms/sell_record_cz/warn_weight_check'); ?>';
    var cz_weight = $("#cz_weight").val();
    var params = {'cz_weight': cz_weight};
    $.post(url, params, function (data) {
        if (data.status != 1) {
            play_sound("error");
            $(".error_msg").html(data.message);
            document.getElementById("cz_weight").focus();
         //   BUI.Message.Alert(data.message, 'error');
        } else {
            $(".error_msg").html("");
            $("#express_no").focus();
            //选择先扫后称模式
            if (pre_set_num == 1) {
                get_cz_express_money();
            }
            //选择先称后扫模式
            if (pre_set_num == 0 && type == 'express_no') {
                searchSellRecord();
            }
        }
    }, "json");
}


function searchSellRecord(){
	var params = {
            "express_no": $("#express_no").val(),
            "app_fmt": "json"
        };

        $.post("?app_act=oms/sell_record_cz/search_sell_record", params, function(data){
        	var type = data.status == 1 ? 'success' : 'error';
             if(type == 'error'){
                 if(data.status == -1){
                    play_sound("error");
                    $(".error_msg").html("找不到物流单号:" + $("#express_no").val() + "对应的系统订单")
                    $("#express_no").val("");
                    $("#express_no").blur();
                 }
                 if (data.status == -2){
                     play_sound("error");
                     BUI.Message.Confirm('订单已称重，需要重复称重，是则继续称重，否则退出称重？',function(){
                     	searchSellRecordCallback(data.data);
                     },'warning');
                 }
                $("#express_no").focus();
            }else {
            	searchSellRecordCallback(data.data);
            }
        }, "json")
	
}
function searchSellRecordCallback(data){
    document.getElementById("cz_weight").focus();
    $("#sell_record_code").val(data.sell_record_code);
    $("#express_code").val(data.express_code);
    $('.table_record_info').css("display","block");
    var html = '';
    html += "<table cellspacing='0' class='table table-bordered'>";
    html += "<thead><tr> <th>商品名称</th> <th>商品编码</th><th>规格1</th><th>规格2</th> <th>商品条形码</th><th>商品数量</th> </tr> </thead> <tbody>";
    $.each(data.detail,function(k,v){
        html += "<tr><td>"+v.goods_name+"</td><td>"+v.goods_code+"</td><td>"+v.spec1_name+"</td><td>"+v.spec2_name+"</td><td>"+v.barcode+"</td><td>"+v.num+"</td></tr>";
    });
    html += "</tbody></table>";
    
    html +="<table cellspacing='0' class='table table-bordered'>";
    html += "<tr><td>收货人</td><td>"+data.receiver_name+"</td><td>手机</td><td>"+data.receiver_mobile+"</td><tr>";
    html += "<tr><td>发货仓库</td><td>"+data.store_name+"</td><td>发货地址</td><td>"+data.receiver_address+"</td><tr>";
    html += "<tr><td>配送方式</td><td>"+data.express_name+"</td><td>快递单号</td><td>"+data.express_no+"</td><tr>";
    html += "</table>";
    $("#record_info").html(html);
    
    var auto_js_yf = document.getElementById("auto_js_yf");
    var is_auto_cz = document.getElementById("is_auto_cz");
    if(auto_js_yf.checked == true && is_auto_cz.checked == true){
        var wait_time = $("#wait_time").val();
        setTimeout("get_cz_express_money()",wait_time * 1000);
        return;
    }
    //选择先称后扫
    if (pre_set_num == 0) {
        get_cz_express_money();
    }
    return;
}
//计算称重运费
function get_cz_express_money(){
    if (document.getElementById("express_no").value =='')
    {
         play_sound("error");
         $(".error_msg").html("物流单号不能为空");
         document.getElementById("express_no").focus();
         return;

    }
    if (($("#cz_weight").val()).replace(/(^\s*)|(\s*$)/g, "") == '' || ($("#cz_weight").val()).replace(/(^\s*)|(\s*$)/g, "") == 0)
    {
        play_sound("error");
        $(".error_msg").html("重量不能为空");
         document.getElementById("cz_weight").focus();
         return;

    }
    //预警校验
    if(weight_different_notice == 1){
    	var params = {
                "sell_record_code": $("#sell_record_code").val(),
                "app_fmt": "json",
            };
    	$.post("?app_act=oms/sell_record_cz/get_record_goods_weight", params, function(data){
        	var record_weight = data.data;
        	var real_weight = ($("#cz_weight").val()).replace(/(^\s*)|(\s*$)/g, "");
        	var real_diffent_weight = Math.abs(real_weight-record_weight).toFixed(3);
        	$("#goods_weight").val(record_weight);
        	$("#different_weight").val(real_diffent_weight);
            if(real_diffent_weight > Math.abs(weight_different)){
            	play_sound("error");
            	BUI.Message.Confirm('重量差异超出设置偏差范围！确定则继续称重，取消则退出称重？',function(){
                    CZAction();
                },'warning');
            	return;
            }
            CZAction();
  	  	}, "json")
    	
    	
    } else {
    	CZAction();
    }             
}
function CZAction(){
	var params = {
            "express_no": $("#express_no").val(),
            "cz_weight": ($("#cz_weight").val()).replace(/(^\s*)|(\s*$)/g, ""),
            "sell_record_code": $("#sell_record_code").val(),
            "app_fmt": "json",
        };
	$.post("?app_act=oms/sell_record_cz/get_cz_express_money", params, function(data){
    	var type = data.status == 1 ? 'success' : 'error';
         if(data.status == -1){//适配策略
            play_sound("error");
            $(".error_msg").html("物流单号："+ $("#express_no").val() +"对应的订单收货地址" + data.data.area_name + "没有在订单快递适配策略-"+ data.data.express_name +"中设置");
            $("#express_no, #cz_weight, #yunfei, #goods_weight, #different_weight, #sell_record_code").val('');
            $('.table_record_info').css("display","none");
            $("#express_no").focus();
        }else if(data.status == -2){
            play_sound("error");
            $(".error_msg").html(data.message);
            $("#express_no, #cz_weight, #yunfei, #goods_weight, #different_weight, #sell_record_code").val('');
            $('.table_record_info').css("display","none");
            $("#express_no").focus();
        }else{
        	$("#yunfei").val(data.data);
          //自动确认
        	confirmAction();
        }
    }, "json")
}
function confirmAction(){
	var params = {
            "sell_record_code": $("#sell_record_code").val(),
            "cz_weight": ($("#cz_weight").val()).replace(/(^\s*)|(\s*$)/g, ""),
            "express_no": $("#express_no").val(),
            "yunfei": $("#yunfei").val(),
            "app_fmt": "json",
        };
	$.post("?app_act=oms/sell_record_cz/confirm", params, function(data){
    	var type = data.status == 1 ? 'success' : 'error';
         if(type == 'error'){
            BUI.Message.Alert(data.message, 'error');
         } else {
             play_sound("success");
             //BUI.Message.Alert("称重成功", 'error');
             clear_record();
         }
    }, "json")
}

function clear_record(){
	$("#express_no").val('');
	$("#sell_record_code").val('');
	$("#express_code").val('');
	$("#cz_weight").val('');
    $("#yunfei").val('');
    $(".error_msg").html("");
    if (pre_set_num == 1) {
        document.getElementById('express_no').focus();
    } else {
        $("#cz_weight").focus();
    }

}
function cz_config(){
	url = "?app_act=oms/sell_record_cz/config";
    _do_execute(url, '','称重器设置',450,400);
	
}

function _do_execute(url, ref,title,width,height) {
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
    }).show();
    
}
</script>
<script>
<!-- 
String.prototype.Blength = function(){  
    var arr = this.match(/[^\x00-\xff]/ig);  
   return  arr == null ? this.length : this.length + arr.length;  
}  
ComName  = "3";//com端口号,
BaudRate = "9600";//波特率,
CheckBit = "N";//校验位,
DataBits = "8";//数据位,
StopBits = "1";//停止位

var tempTime;

//配置串口:com端口号,波特率,校验位,数据位,停止位

  function ConfigPort(ComName,BaudRate,CheckBit,DataBits,StopBits){
	try{
		MSComm1.CommPort=ComName;
		MSComm1.Settings=BaudRate+","+CheckBit+","+DataBits+","+StopBits;
		MSComm1.OutBufferCount =0;           //清空发送缓冲区
		MSComm1.InBufferCount = 0;           //滑空接收缓冲区                         
		//alert("已配置串口COM"+MSComm1.CommPort+"\n 参数:"+MSComm1.Settings);       
	 }catch(ex){alert(ex.message);}
  }
	//打开/关闭串口
    function OperatePort(){
    //alert(MSComm1.PortOpen);
      if(MSComm1.PortOpen==true){
		try{
			MSComm1.PortOpen=false;	
			//document.getElementById("OperateButton").value="打开串口";
			//clearInterval(tempTime);
       	}catch(ex){alert(ex.message);}       
      }else{
       try{
			//ConfigPort(ComName,BaudRate,CheckBit,DataBits,StopBits);
			MSComm1.PortOpen=true;
			if(typeof(MSComm1.Input) != "unknown"){
				$("#cz_msg").html("连接成功");
			}
       }catch(ex){alert(ex.message);}     
      }
   }
   //获取数据
    /*
  function   getData(){
	if(MSComm1.PortOpen==true)
		return MSComm1_OnComm();
	else 
		return '';
  }
	
   function  MSComm1_OnComm(){   
		try{   
			return parse_weigh_value(MSComm1.Input); 
		}catch(ex){return ''}     
	}*/

	
	
	function txt_get_value()
	{
		//document.getElementById('cz_weight').value=parse_weigh_value(MSComm1.Input); 
		var cz_weight = parse_weigh_value(MSComm1.Input);
		var old_cz_weight = ($("#cz_weight").val()).replace(/(^\s*)|(\s*$)/g, "");
		if (cz_weight !="" && old_cz_weight !== cz_weight){
			document.getElementById('cz_weight').value= cz_weight;
		}
	}
	function parse_weigh_value(inputvalue) {
		if (inputvalue.indexOf('Kg') < 0) {
		   return '';
		}
	    var arr = inputvalue.split(',');
		if(arr.length<3) return "";
		
		var w = arr[2].replace(/(^\s*)|(\s*$)/g, "");
		var f = w.replace('Kg','');
		return isNaN(f) ? '' : f;
	}
	//--> 
	
	
</script>
 <SCRIPT   ID=clientEventHandlersJS   LANGUAGE=javascript> 
  <!--   
  function   MSComm1_OnComm()   
  {      
      switch(MSComm1.CommEvent)
     {
       case 1:{ window.alert("Send OK！"); break;}  //发送事件
        case 2: {txt_get_value();break;} //接收事件
        default: alert("Event Raised!"+MSComm1.CommEvent);;
      }       
 }  
 //--> 
 
 </SCRIPT>
 
 <SCRIPT   LANGUAGE=javascript   FOR=MSComm1   EVENT=OnComm> 
   <!--
  // MSComm1控件每遇到 OnComm 事件就调用 MSComm1_OnComm()函数
          MSComm1_OnComm();
	
   //--> 
  </SCRIPT> 
<script>

$(document).ready(function(){
	is_auto_cz();
});

/* 连接电子称 */
function is_auto_cz()
{
	var chk_is_auto_cz=document.getElementById("is_auto_cz");
    
	if(chk_is_auto_cz.checked==true)
	{
		$("#cz_msg").html("连接失败");
		if(MSComm1.PortOpen==false)
		{
			
			OperatePort();
		}
	}
	else
	{
		if(MSComm1.PortOpen==true)
		{
			OperatePort();
		}
	}
}

</script>
<script>
/*
window.onbeforeunload = function (e) {
	alert("关闭窗口");
	//OperatePort();
}*/
window.onunload = function (e) {
	OperatePort();
}

$(function(){
    $("#wait_time").val('0');
    $('.table input[name="is_auto_cz"]').on('change', function() {
        if($('input[name=is_auto_cz]:checked', '.table').val() == 1){
            $("#cz_weight").attr('disabled','disabled');
            $("#auto_check").attr('style','display:block');
            $("#cz_weight").val('');
            $("#wait_time").val('2');
            $("#auto_js_yf").prop("checked",true);
        }
        if($('input[name=is_auto_cz]:checked', '.table').val() == 2){
            $("#cz_weight").attr('disabled',false);
            $("#auto_check").attr('style','display:none');
            $("#wait_time").val('0');
            $("#auto_js_yf").prop("checked",false);
        }
    });
})
</script>
<OBJECT id=MSComm1 CLASSID="clsid:648A5600-2C6E-101B-82B6-000000000014" codebase="MSCOMM32.OCX" type="application/x-oleobject"  >
     <PARAM   NAME="CommPort"   VALUE="<?php echo $response['config']['cz_com_name'];?>"/> 
     <PARAM   NAME="DataBits"   VALUE="8"/> 
     <PARAM   NAME="StopBits"   VALUE="1"/> 
     <PARAM   NAME="BaudRate"   VALUE="<?php echo $response['config']['cz_baud_rate'];?>"/> 
     <PARAM   NAME="Settings"   VALUE="<?php echo $response['config']['cz_baud_rate'];?>,N,8,1"/>     
     <PARAM   NAME="RTSEnable"   VALUE="1"/> 
     <PARAM   NAME="DTREnable"   VALUE="1"/> 
     <PARAM   NAME="Handshaking"   VALUE="0"/> 
     <PARAM   NAME="NullDiscard"   VALUE="0"/> 
     <PARAM   NAME="ParityReplace"   VALUE="?"/>
     <PARAM   NAME="EOFEnable"   VALUE="0"/>       
     <PARAM   NAME="InputMode"   VALUE="0"/>    
     <PARAM   NAME="InBufferSize"   VALUE="1024"/>       
     <PARAM   NAME="InputLen"   VALUE="0"/>     
     <PARAM   NAME="OutBufferSize"   VALUE="512"/> 
     <PARAM   NAME="SThreshold"   VALUE="0"/> 
     <PARAM   NAME="RThreshold"   VALUE="1"/> 
</OBJECT>
<div class="page-header1" style="width: 98%; display: block; clear: both; position: fixed; top:0px; left:0px; background-color: #FFF; padding: 4px 1%; z-index: 9999; box-shadow:0px 0px 5px #ccc;">
	<span class="page-title"><h2>订单称重</h2></span>
	<span class="page-link">
        <span class="action-link">
            <a target="_blank" href="https://detail.tmall.com/item.htm?spm=0.0.0.0.5UW6FK&id=527312037887" ><button class="button button-primary"  id="config"> 购买称重器</button></a>
            	<!-- <button class="button button-primary" onclick="cz_config();" id="config"> 称重器设置</button> -->
        </span>
    </span>
</div> 
<div class="clear" style="margin-top: 40px; "></div>
    <div class="panel">
        <div class="panel-body">
            <div class="control-group span8">
                <label class="control-label">先扫后称模式:</label>
                <div class="button-group" id="pre_set_num"></div>
                <div id="remark">*目前仅支持USB型电子秤</div>
            </div>
        </div>
        <div class="panel-body" style="display:none">
        	<table cellspacing="1" class="table" style="border:solid 1px #dddddd;">
                <tbody>
                <tr>
                    <td style="font-size:15px;">
	        	连接称重器&nbsp;&nbsp;&nbsp;
                        <input type="hidden" value="2" name="is_auto_cz" checked id="no_auto_cz"/>
	        	<label for="no_auto_cz">USB型电子秤</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="hidden"  value="1" name="is_auto_cz" id="is_auto_cz"/>
                    <!--     <label for="is_auto_cz">串口型电子秤<span style="vertical-align:super;color:red;font-size:14px"><strong> 即将下线!</strong></span></label>
	        	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id="cz_msg" style="color:red;"></span>
	            </td> -->
                    <td id="auto_check" style="display:none">
	        	称重后自动计算运费&nbsp;&nbsp;&nbsp;
                        <input type="radio" value="1" name="auto_js_yf" id="auto_js_yf"/>
	        	<label for="auto_js_yf">自动计算 </label>
                        <input type="number" name="wait_time" id="wait_time" value="0" size="1" />秒后自动计算运费
	            </td>
	            </tr>
                </tbody>
            </table>
            
        </div>
        <div class="panel-body">
        <form  class="form-horizontal" id="form1" >
        <div class="row">				
	        <div class="control-group span11">
				<label class="control-label span3">物流单号:</label>
				<div class="controls control-row3 " >
				  <input type="text" name="express_no" style="height:40px;font-weight:bold;font-size:30px;" id="express_no" class="input-large" onKeyDown = "hotkey(event)"  />    
				</div>
			</div>
			<div class="control-group span11">
				<div class="controls" style="padding-top:10px;">
				  <input type="button" class="button button  button-primary" onclick="clear_record();" value="清除扫描记录"/>    
				</div>
			</div>
		</div>
            <div class="error_msg"></div>
		 <div class="row">				
	        <div class="control-group span11">
				<label class="control-label span3">实际重量:</label>
				<div class="controls control-row3" >
                    <input type="text" style="height:40px;font-weight:bold;font-size:30px;color:red;" onKeyDown = "hotkey_cz(event)" name="cz_weight" id="cz_weight" value="" class="input-large"/>    千克
                </div>
			</div>
             <div class="control-group span11">
                 <div class="controls" style="padding-top:10px;">
                     <strong>( 预警重量：<span id="warn_wight"><?php echo $response['weight_info']['warn_weight'];?></span> 千克 )</strong>
                 </div>
             </div>
		</div>
		 <div class="row">				
	        <div class="control-group span11">
				<label class="control-label span3">运费:</label>
		                   
				<div class="controls " >
				  <input type="text" name="yunfei" id="yunfei" class="input-large" disabled />    元
				</div>
			</div>
		</div>
		<?php if ($response['weight_info']['weight_different_notice'] == 1){?>
			<div class="row">				
		        <div class="control-group span11">
					<label class="control-label span3">理论重量:</label>
					<div class="controls " >
					  <input type="text" name="goods_weight" id="goods_weight" class="input-large" disabled />    千克
					</div>
				</div>
			</div>
			<div class="row">				
		        <div class="control-group span11">
					<label class="control-label span3">重量差异:</label>
					<div class="controls " >
					  <input type="text" name="different_weight" id="different_weight" value="" class="input-large" disabled  />   千克
					</div>
				</div>
			</div>
		<?php }?>
		 <div class="row">				
	        <div class="control-group span11">
				<label class="control-label span3">订单号:</label>
		                   
				<div class="controls " >
				  <input type="text" name="sell_record_code" id="sell_record_code" class="input-large" disabled/>    
				</div>
			</div>
			
		</div>
		<input type="hidden" name="express_code" id="express_code"/>
<!--                <div class="control-group span11" style="display: none" id="record_info"></div>-->
                <div class="row table_record_info" style="margin-top: 20px;">
                    <div class="span20 doc-content" id="record_info">

                    </div>
                </div>
        </form>
       
       </div>          
    </div>
<bgsound loop="false" autostart="false" id="bgsound_ie" src="" />
<audio controls="controls" id="bgsound_others" style="display:none;" src=""></audio>
<script>

    BUI.use('bui/toolbar', function (Toolbar) {
        //先扫后称模式
        pre_set_num = getConfigCookie('pre_set_num');
        var g1 = new Toolbar.Bar({
            elCls: 'button-group',
            itemStatusCls: {
                selected: 'active' //选中时应用的样式
            },
            defaultChildCfg: {
                elCls: 'button button-small',
                selectable: true
            },
            children: [
                {content: '是', id: '1', selected: pre_set_num == 1 ? true : false},
                {content: '否', id: '0', selected: pre_set_num == 0 ? true : false}
            ],
            render: '#pre_set_num'
        });
        g1.render();
        g1.on('itemclick', function (ev) {
            pre_set_num = ev.item.get('id');
            setConfigCookie('pre_set_num', pre_set_num);
            set_params_focus(pre_set_num);
        });
        set_params_focus(pre_set_num);
    });

    /*--------页面缓存设置----BEGIN----*/
    //页面加载时，读取cookie,设置配置项状态
    function getConfigCookie(_name) {
        var cookie_val = $.cookie(_name + '_select');
        if (cookie_val == undefined) {
            cookie_val = 1;
        }
        return cookie_val;
    }

    //配置项状态状态更改时，设置cookie
    function setConfigCookie(_name, _status) {
        $.cookie(_name + '_select', _status, {expires: 30});
    }
    /*--------页面缓存设置----END----*/


    //光标定位
    function set_params_focus(check) {
        if (check == 1) {
            $("#express_no").focus();
        } else {
            $("#cz_weight").focus();
        }
    }
    
    
</script>