<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-Type" content="text/html; charset=UTF-8" />
<title>宝塔eFAST 365</title>
    <link href="assets/css/dpl.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/bui.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/main-min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/common.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../../webpub/js/jquery-1.8.1.min.js"></script>
<style>
/*reset*/
body,div,a,p,ul,li,img,h1,h2,h3,h4,h5,h6,ol,table,tr,td,form,input,button{ margin:0; padding:0;font-family: "Microsoft YaHei","微软雅黑","Arial","宋体","Times New Roman",Times,serif;}
a{ text-decoration:none;}
li{ list-style:none;}
img,input{ border:none;}

.scan_wrap{ padding:15px 1.5%; color:#333;}
.scan_wrap .dj_info .lab_v{ margin-right:100px;}
#total_no_scan_sl{color:red;}
.mx_tbl table{ width:100%; border-collapse:collapse; background:#FFF;}
.mx_tbl th,.mx_tbl td{padding:4px;border:1px #ccc solid;}
.mx_tbl th{background:#f2f2f2;}
.mx_tbl td{ text-align:center;}
.mx_tbl td.smsl{ color:#008000;}
.scan_div{ padding:20px 0;}
.scan_div #scan_barcode{font-size:20px;font-weight:bold;padding:10px; width:400px; color:#351A50; border:1px solid #999;}
.scan_div #scanned_barcode{font-size:20px; padding:10px; width:400px; color:#008000; margin-left:150px; background:#f6f6f6;}
.update-smsl {color:#008000; border:1px solid #999;width:60px;font-weight:bold;}
#err_tips{color:#e95513; padding:8px; font-size:20px; border:2px solid #fc9580; text-align:center; margin-bottom:20px;}
#ys_btn,#close_btn,#scan_over{width:100px;height:50px;font-size:20px; cursor:pointer; margin-left:15px; background:#f2f2f2; color:#666; border:1px solid #999; border-radius:3px;}
#ys_btn:hover,#scan_over,#close_btn:hover{ background:#FFF;}
#success_tips{ padding:8px 0; color:#3c763d; font-size:20px; background:#fdffe1; border:2px solid #cfdba1; text-align:center; margin-bottom:15px;}
.scan_wrap .scan_sl_info{ padding:10px 0 20px; font-size:18px;}
.scan_wrap .scan_sl_info .lab{ font-weight:bold;}
.scan_wrap .scan_sl_info .lab_v{ display:inline-block; width:150px; text-indent:50px; color:#e95513;}
.big-font-word{font-size: 20px;}
.big-font-num{font-size: 25px;}
</style>
</head>
<body style="overflow-x:hidden; background:#f6f6f6;">
<?php include get_tpl_path('web_page_top'); ?>
<?php echo load_js('core.min.js');?>
<div class="scan_wrap">
	
<div id="success_tips" style="display:none">
	<div class="tips tips-small  tips-success"> <span class="x-icon x-icon-small x-icon-success"><i class="icon icon-white icon-ok"></i></span>
	<div class="tips-content">扫描成功</div>
	</div>
</div>

<div class="dj_info"> <span class="lab">单据类型: </span><span class="lab_v"><?php echo $response['dj_info']['dj_type_name']?></span> <span class="lab">单据编号: </span><span class="lab_v"><?php echo $response['dj_info']['record_code']?></span> <span class="lab">创建日期: </span><span class="lab_v"><?php echo $response['dj_info']['order_time']?></span> </div>
  
<div class="scan_div">
  <input type="text" id="scan_barcode"/>
  <?php if ($request['type'] == 'add_goods'){?>
   <input type="button" id="scan_over" value="扫描完成"/>
  <?php }?>
  <input type="button" id="scanned_barcode" style="display:none" readonly="true"/>
</div>
<div class="scan_sl_info"> <span class="lab big-font-word">总数: </span><span class="lab_v big-font-num" id="total_sl"><?php echo $response['total_sl']?></span> <span class="lab big-font-word">已扫描: </span><span class="lab_v big-font-num" id="total_scan_sl"><?php echo $response['total_scan_sl']?></span> <span class="lab big-font-word">差异: </span><span class="lab_v big-font-num" id="total_no_scan_sl"> </span> </div>
<div id="err_tips" style="display:none"></div>
<div class="mx_tbl">
  <table id="sku_tbl">
    <tr>
      <th>商品名称</th>
      <th>商品编码</th>
      <th><?php echo $response['base_spec1_name'];?></th>
      <th><?php echo $response['base_spec2_name'];?></th>
      <th>商品条形码</th>
      <th>扫描数量</th>
      <?php if($response['dj_info']['dj_type'] != 'shift_out'){?>
        <th>库位</th>
      <?php }?>
    </tr>
    <?php foreach($response['scan_data'] as $sub_mx) {?>
    <tr>
      <td><?php echo $sub_mx['goods_name'];?></td>
      <td><?php echo $sub_mx['goods_code'];?></td>
      <td><?php echo $sub_mx['spec1_name'];?></td>
      <td><?php echo $sub_mx['spec2_name'];?></td>
      <td><?php echo $sub_mx['barcode'];?></td>
      <td class="smsl big-font-num"><?php echo "<span id='sku_num_{$sub_mx['sku']}'>{$sub_mx['num']}</span>";?></td>
      <?php if($response['dj_info']['dj_type'] != 'shift_out'){?>
        <td><?php echo $sub_mx['shelf_name'];?></td>
      <?php }?>
    </tr>
    <?php }?>
  </table>
</div>

</div>
<script type="text/javascript">
var g_total_sl = <?php echo $response['total_sl'];?>;//总数量
var g_total_scan_sl = <?php echo $response['total_scan_sl'];?>;//已扫描数量
var g_mx_info = <?php echo json_encode($response['scan_data_js']);?>;//SKU的明细信息 sku=>已扫数量
var g_scan_barcode_map = <?php echo json_encode($response['scan_barcode_map']);?>;//扫描过的条码和SKU的对应关系
var g_must_scan_mx = <?php echo json_encode($response['must_scan_mx']);?>;//完成单的SKU的明细信息，如果完成单的明细sum(num)=0,那么取通知单的明细
var g_dj_info = <?php echo json_encode($response['dj_info']);?>;
var type = "<?php echo $request['type'];?>";
// console.log(g_mx_info);
// console.log(g_scan_barcode_map);
// console.log(g_must_scan_mx);
// console.log(g_dj_info);
var sounds = {
    "error": "0",
    "success": "1"
};
var current_scan_num = 0;
$(document).ready(function (){
	update_smsl();
	
})

function update_smsl(){
	$(".smsl").click(function (e){
		var dj_type = g_dj_info['dj_type'];
		if($(this).hasClass("stop") || (dj_type != 'take_stock'&&dj_type != 'adjust'&&dj_type != 'shift_out')){
			return;
		}
		var id = $(this).children("span").attr('id');
		current_scan_num = $(this).children("span").html();
		var content = $("<input type='text' class='update-smsl' value='"+current_scan_num+"' name='scan_num' id='input_"+id+"' >");
		$(this).addClass("stop");
		$("#"+id).hide();
		$(this).append(content);
		$("#input_"+id).focus();
		var num = $("#input_"+id).val();//更新之后的数量
		$("#input_"+id).blur(function (){
			$("#input_"+id).remove();
			$("#"+id).show();
			$(".smsl").removeClass("stop");
		})	
		$("#input_"+id).keydown(function(e) {
            if (e.keyCode == "13") {//keyCode=13是回车键
            	var num = $("#input_"+id).val();
            	update_scan_num(num,id,current_scan_num);
            }
        });
	})
}

function update_scan_num(num,id,current_scan_num){
	var dj_type = g_dj_info['dj_type'];
	var url = g_dj_info['dj_update_scan_num_url'];
	var param = {app_fmt:'json',num:num,id:id};
	$.get(url, param,function(json_data){
		try{
		   var result = eval('('+json_data+')');
		}catch (e){}
		if (result == undefined || result.status == undefined){
			alert('更新出错： '+json_data);
			$("#input_"+id).remove();
			$("#"+id).show();
			$(".smsl").removeClass("stop");
			return;
		}
		if (result.status<0 || result.status == false){
			alert('更新出错： '+result.message);
			$("#input_"+id).remove();
			$("#"+id).show();
			$(".smsl").removeClass("stop");
		}else{
			$("#input_"+id).remove();
            var sku_arr= new Array(); //定义一数组
            sku_arr=id.split("_");
            var sku=sku_arr[2];
            g_mx_info[sku] = parseInt(num);
			$("#"+id).show();
			$("#"+id).html(num);
			var change_num = num - current_scan_num;
			var total_scan_sl = $("#total_scan_sl").html();
            var total_no_scan_sl = $("#total_no_scan_sl").html();
			$("#total_scan_sl").html(parseInt(total_scan_sl) + parseInt(change_num));
            $("#total_no_scan_sl").html(total_no_scan_sl - change_num);
			$(".smsl").removeClass("stop");
		}
	});		

}

function js_total_no_scan_sl(){
	$('#total_no_scan_sl').html(g_total_sl - g_total_scan_sl);
}

//更新扫描数
function update_total_scan_sl(){
	var total_scan_sl = 0;
	for(var i in g_mx_info){
            if(g_mx_info[i]>0){
              total_scan_sl += parseInt(g_mx_info[i]); 
            }
	}
	$("#total_scan_sl").html(total_scan_sl);
	g_total_scan_sl = total_scan_sl;
	js_total_no_scan_sl();
}
js_total_no_scan_sl();

function scan_barcode(){
	var scan_barcode = $("#scan_barcode").val();
	var scan_str = "当前已扫描：" + $("#scan_barcode").val();
    $("#scanned_barcode").show();
    $("#scanned_barcode").val(scan_str);
    $('#scanned_barcode').removeAttr("disabled");
	var find_barcode = g_scan_barcode_map[scan_barcode];
	var barcode_is_exist = 1;
	if (find_barcode == undefined){
		barcode_is_exist = -1;
	}
	var url = "?app_act=common/record_scan_common/save_scan";
	var dj_type = g_dj_info['dj_type'];
	var relation_code = g_dj_info['relation_code'];
	var param = {app_fmt:'json',type:type,dj_type:dj_type,record_code:g_dj_info['record_code'],scan_barcode:scan_barcode,barcode_is_exist:barcode_is_exist,tzd_code:relation_code};
	$.get(url, param,
	  function(json_data){
		try{
		   var result = eval('('+json_data+')');
		}catch (e){}
		if (result == undefined || result.status == undefined){
			display_err_tips('扫描出错： '+json_data);
			return;
		}
		if (result.status<0){
			display_err_tips('扫描出错： '+result.message);
                        return;
		}else{
			var data = result.data;
			if (data.scan_barcode == undefined || data.sku == undefined || data.num == undefined){
				display_err_tips('扫描出错： '+json_data);
				return;
			}
			var data = result.data;
			scan_barcode_update(data);
		}
	  });
}

function scan_barcode_update(data){
	var sku = data.sku;
	var scan_barcode = data.barcode;
	g_mx_info[sku] = parseInt(data.num);
	if (g_must_scan_mx[sku]){
		g_must_scan_mx[sku]['num'] = parseInt(data.num);
	}
            var sku_id = sku.replace('.', "\\.");//处理带点特殊处理
	var find_barcode = g_scan_barcode_map[scan_barcode];
	if (find_barcode !== undefined){
		data.barcode_is_exist = 1;
	}
	if (data.barcode_is_exist == 1){
		$("#sku_num_"+sku_id).html(g_mx_info[sku]);
	}else{
        //$('#sku_tbl tr').eq(1).css('color', 'black');
		g_scan_barcode_map[scan_barcode] = sku;
        var dj_type = g_dj_info['dj_type'];
        if (dj_type == 'shift_out'){
            var html = "<tr><td>"+data.goods_name+"</td><td>"+data.goods_code+"</td><td>"+data.spec1_name+"</td><td>"+data.spec2_name+"</td><td>"+data.barcode+"</td><td  class='smsl big-font-num'><span id='sku_num_"+data.sku+"'>"+data.num+"</span></td></tr>";
        } else {
            var html = "<tr><td>"+data.goods_name+"</td><td>"+data.goods_code+"</td><td>"+data.spec1_name+"</td><td>"+data.spec2_name+"</td><td>"+data.barcode+"</td><td  class='smsl big-font-num'><span id='sku_num_"+data.sku+"'>"+data.num+"</span></td><td>"+data.shelf_name+"</td></tr>";
        }

        $('#sku_tbl tr').eq(0).after(html);
        //$('#sku_tbl tr').eq(1).css('color', 'red');

		if(dj_type == 'take_stock'||dj_type == 'adjust'||dj_type == 'shift_out'){
			update_smsl();
		}
	}

	update_total_scan_sl();
	$("#scan_barcode").val('');
	play_sound("success");
}


//验收
function ys_act(){
	$("#ys_btn").attr('disable','true');
	var url = g_dj_info['dj_ys_url'];
	$.get(url,function(json_data){
		try{
		   var result = eval('('+json_data+')');
		}catch (e){}
		if (result == undefined || result.status == undefined){
			//alert('验收出错： '+json_data);
            BUI.Message.Alert('验收出错： '+json_data, 'error');
			$("#ys_btn").attr('disable','false');
			return;
		}
		if (result.status<0 || result.status == false){
			//alert('验收出错： '+result.message);
            BUI.Message.Alert('验收出错： '+result.message, 'error');
			$("#ys_btn").attr('disable','true');
		}else{
			$(".scan_div").add(".mx_tbl").css('display','none');
			$("#success_tips").css('display','block');
		}
	  });
}

function display_err_tips($msg){
	$("#err_tips").html($msg);
	$("#err_tips").show();
	$("#scan_barcode").val('');
	play_sound("error");
	setTimeout("$('#err_tips').hide()", 3000);
}

$(document).ready(function(){
	$("#scan_barcode").bind('keypress',function(event){
	    if(event.keyCode == "13")
	    {
	        scan_barcode();
	    }
	});
	$("#scan_barcode").focus();
	$("#scan_over").click(function (){
		ys_act();
	})
	$("#close_btn").click(function(){
		close_web_page();
	});
});

function close_web_page() {
    if (navigator.userAgent.indexOf("MSIE") > 0) {
        if (navigator.userAgent.indexOf("MSIE 6.0") > 0) {
            window.opener = null; window.close();
        }
        else {
            window.open('', '_top'); window.top.close();
        }
    }
    else if (navigator.userAgent.indexOf("Firefox") > 0) {
        window.location.href = 'about:blank ';
        //window.history.go(-2);
    }
    else {
        window.opener = null;
        window.open('', '_self', '');
        window.close();
    }
}

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
</script>
<bgsound loop="false" autostart="false" id="bgsound_ie" src="" />
<audio controls="controls" id="bgsound_others" style="display:none;" src=""></audio>
</body>
</html>