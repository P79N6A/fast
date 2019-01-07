<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-Type" content="text/html; charset=UTF-8" />
<title>宝塔eFAST 365</title>
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
.scan_div #scan_barcode{font-size:20px;font-weight:bold;padding:10px; width:300px; color:#351A50; border:1px solid #999;}
.scan_div #scanned_barcode{font-size:20px; padding:10px; color:#008000; margin-left:20px; background:#FFF;}
#err_tips{color:#e95513; padding:8px; font-size:20px; border:2px solid #fc9580; text-align:center; margin-bottom:20px;}
#ys_btn,#close_btn{width:100px;height:50px;font-size:20px; cursor:pointer; margin-left:15px; background:#f2f2f2; color:#666; border:1px solid #999; border-radius:3px;}
#clean_scan{width:160px;height:50px;font-size:20px; cursor:pointer; margin-left:15px; background:#f2f2f2; color:#666; border:1px solid #999; border-radius:3px;}
#ys_btn:hover,#close_btn:hover{ background:#FFF;}
#clean_scan:hover,#close_btn:hover{ background:#FFF;}
#success_tips{ padding:8px 0; color:#3c763d; font-size:20px; background:#fdffe1; border:2px solid #cfdba1; text-align:center; margin-bottom:15px;}
.scan_wrap .scan_sl_info{ padding:10px 0 20px; font-size:18px;}
.scan_wrap .scan_sl_info .lab{ font-weight:bold;}
.scan_wrap .scan_sl_info .lab_v{ display:inline-block; width:150px; text-indent:50px; color:#e95513;}
</style>
</head>
<body style="overflow-x:hidden; background:#f6f6f6;">
    <?php include get_tpl_path('web_page_top'); ?>
<div class="scan_wrap">
	
<div id="success_tips" style="display:none">
	<div class="tips tips-small  tips-success"> <!--  <span class="x-icon x-icon-small x-icon-success"></span>-->
	<div class="tips-content">扫描入库成功</div>
	</div>
</div>

<div class="dj_info"> <span class="lab">单据类型: </span><span class="lab_v">移仓单</span> <span class="lab">单据编号: </span><span class="lab_v"><?php echo $response['record_code']?></span>  </div>
  
<div class="scan_div">
  <input type="text" id="scan_barcode" />
  <input type="button" id="ys_btn" value="扫描入库"/>
  <!-- <input type="button" id="close_btn" value="关 闭"/>-->
    <input type="button" id="clean_scan" value="清除扫描记录">
  <input type="button" id="scanned_barcode" style="display:none"  readonly="true"/>
</div>
<div class="scan_sl_info"> <span class="lab">总数: </span><span class="lab_v" id="total_sl"><?php echo $response['total_sl']?></span> <span class="lab">已扫描: </span><span class="lab_v" id="total_scan_sl"><?php echo $response['total_scan_sl']?></span> <span class="lab">差异: </span><span class="lab_v" id="total_no_scan_sl"> </span> </div>
<div id="err_tips" style="display:none"></div>
<div class="mx_tbl">
  <table id="sku_tbl">
    <tr>
      <th>商品名称</th>
      <th>商品编码</th>
      <th><?php echo $response['base_spec1_name'];?></th>
      <th><?php echo $response['base_spec2_name'];?></th>
      <th>商品条形码</th>
      <th>移出数量</th>
      <th>扫描数量</th>
      <th>库位</th>
    </tr>
    <?php foreach($response['scan_data'] as $sub_mx) {?>
    <tr>
      <td><?php echo $sub_mx['goods_name'];?></td>
      <td><?php echo $sub_mx['goods_code'];?></td>
      <td><?php echo $sub_mx['spec1_name'];?></td>
      <td><?php echo $sub_mx['spec2_name'];?></td>
      <td><?php echo $sub_mx['barcode'];?></td>
      <td><?php echo "<span name='out_num_{$sub_mx['barcode']}'>{$sub_mx['out_num']}</span>";?><?php echo "<span name='gb_out_num_{$sub_mx['gb_code']}' style='display:none'>{$sub_mx['out_num']}</span>";?></td>
      <td class="smsl"><?php echo "<span name='sku_num_{$sub_mx['barcode']}'>{$sub_mx['in_num']}</span>";?><?php echo "<span name='gb_sku_num_{$sub_mx['gb_code']}' style='display:none'>{$sub_mx['in_num']}</span>";?></td>
      <td><?php echo $sub_mx['shelf_name'];?></td>
    </tr>
    <?php }?>
  </table>
</div>

</div>
<script type="text/javascript">
var pid = '<?php echo $response['pid'];?>';
var in_store = '<?php echo $response['in_store'];?>';
var record_code = '<?php echo $response['record_code'];?>';
var g_total_sl = <?php echo $response['total_sl'];?>;//总数量
var g_total_scan_sl = <?php echo $response['total_scan_sl'];?>;//已扫描数量
var g_mx_info = <?php echo json_encode($response['scan_data_js']);?>;//SKU的明细信息 sku=>已扫数量
var g_scan_barcode_map = <?php echo json_encode($response['scan_barcode_map']);?>;//扫描过的条码和SKU的对应关系
var g_must_scan_mx = <?php echo json_encode($response['must_scan_mx']);?>;//完成单的SKU的明细信息，如果完成单的明细sum(num)=0,那么取通知单的明细

var g_dj_info = <?php echo json_encode($response['dj_info']);?>;

var sounds = {
    "error": "0",
    "success": "1"
};
    
function js_total_no_scan_sl(){
	$('#total_no_scan_sl').html(g_total_sl - g_total_scan_sl);
}

//更新扫描总数
function update_total_scan_sl() {
    var total_scan_sl = 0;
    for (var i in g_must_scan_mx) {
        total_scan_sl += parseInt(g_must_scan_mx[i]['in_num']);
    }
    $("#total_scan_sl").html(total_scan_sl);
    g_total_scan_sl = total_scan_sl;
    js_total_no_scan_sl();
}

js_total_no_scan_sl();

function scan_barcode(){
	var scan_barcode = $("#scan_barcode").val();
	//var find_barcode = g_scan_barcode_map[scan_barcode];
    var scan_str = "当前已扫描：" + $("#scan_barcode").val();
    $("#scanned_barcode").show();
    $("#scanned_barcode").val(scan_str);
    $('#scanned_barcode').removeAttr("disabled");
	var find_barcode = scan_barcode;
	var barcode_is_exist = 1;
	var dj_type = "<?php echo isset($response['dj_type']) ? $response['dj_type'] : '';?>";
	var sku_num_scan = $("span[name='sku_num_"+scan_barcode+"']").html();
	var out_num = $("span[name='out_num_"+scan_barcode+"']").html();
    if(sku_num_scan && sku_num_scan == out_num){
        alert("该商品条形码的扫描数量不能大于移出数量！");
        die;
    }
    var gb_num = $("span[name='gb_sku_num_"+scan_barcode+"']").html();
    var gb_out_num = $("span[name='gb_out_num_"+scan_barcode+"']").html();
    if($(sku_num_scan).html() == undefined){
        if(gb_num && gb_num >= gb_out_num){
            alert("该商品条形码的扫描数量不能大于移出数量！");
            die;
        }
    }

	if (find_barcode == undefined){
		barcode_is_exist = -1;
	}
	//var url = "?app_act=common/record_scan/save_scan";
	//var dj_type = 'wbm_store_out';
	//var relation_code = g_dj_info['relation_code'];
	//var param = {app_fmt:'json',dj_type:dj_type,record_code:record_code,scan_barcode:scan_barcode,barcode_is_exist:barcode_is_exist,tzd_code:relation_code};
	var url = '<?php echo get_app_url('stm/store_shift_record/scan'); ?>';
	var param = {pid: pid,barcord:scan_barcode,in_store:in_store,dj_type:dj_type};
	$.get(url, param,
	  function(json_data){
		try{
		   var result = eval('('+json_data+')'); 
		}catch (e){}
		if (result == undefined || result.status == undefined){
			display_err_tips('扫描出错:商品条形码(国标码)不存在 ');
			return;
		}
		if (result.status<0){
			display_err_tips('扫描出错： '+result.message);
			act_check_scan_end();
		}else{
			var data = result.data;
			/*
			if (data.scan_barcode == undefined || data.sku == undefined || data.num == undefined){
				display_err_tips('扫描出错： '+json_data);
				act_check_scan_end();
				return;
			}*/
			var data = result.data;
			scan_barcode_update(data);
		}
	  });
}

function scan_barcode_update(data){
	var sku = data.sku;
	var scan_barcode = data.barcode;
	var scan_gb_code = data.gb_code;
	//g_mx_info[sku] = parseInt(data.num);
	if (g_must_scan_mx[sku]){
		g_must_scan_mx[sku]['in_num'] = parseInt(data.num);
	}
    $("span[name='sku_num_"+scan_barcode+"']").html(g_must_scan_mx[sku]['in_num']);
    $("span[name='gb_sku_num_"+scan_barcode+"']").html(g_must_scan_mx[sku]['in_num']);
//	$("#total_scan_sl").html(g_must_scan_mx[sku]['in_num']);
//	$("#total_no_scan_sl").html(g_total_sl - g_must_scan_mx[sku]['in_num']);
	/*
	if (data.barcode_is_exist == 1){
		$("#sku_num_"+sku).html(g_mx_info[sku]);
	}else{
		g_scan_barcode_map[scan_barcode] = sku;
		var html = "<tr><td>"+data.goods_name+"</td><td>"+data.goods_code+"</td><td>"+data.spec1_name+"</td><td>"+data.spec2_name+"</td><td>"+data.barcode+"</td><td><span id='sku_num_"+data.sku+"'>"+data.num+"</span></td></tr>";
		$("#sku_tbl").append(html);
	}
    */

	$("#scan_barcode").val('');
    update_total_scan_sl();
	play_sound("success");
	act_check_scan_end();
}

function act_check_scan_end(){
	if (check_scan_end()){
		if (confirm("商品数量符合，是否验收？")){
			ys_record();
            return;
		}
	}
}

function check_scan_end(){
	var is_end = 1;
	for(var i in g_must_scan_mx){
		if (parseInt(g_must_scan_mx[i]['out_num'])>parseInt(g_must_scan_mx[i]['in_num'])){
			is_end = 0;
			break;
		}
	}
	return is_end;
}

//验收
function ys_record(){
	if (!check_scan_end()){
		if (confirm('还未扫描完毕，商品数量存在差异，是否验收？')){
			ys_act();
		}
        return;
	}
	ys_act();
}

function ys_act(){
	$("#ys_btn").attr('disable','true');
	var param = {id: pid};
	var url = '<?php echo get_app_url('stm/store_shift_record/do_shift_in'); ?>';
	$.get(url,param,function(json_data){
		try{
		   var result = eval('('+json_data+')');
		}catch (e){}
		if (result == undefined || result.status == undefined){
			alert('扫描入库出错： '+json_data);
			$("#ys_btn").attr('disable','false');
			return;
		}
		if (result.status<0 || result.status == false){
			alert('扫描入库出错： '+result.message);
			$("#ys_btn").attr('disable','true');
		}else{
			$(".scan_div").add(".mx_tbl").css('display','none');
			$("#success_tips").css('display','block');
			alert('扫描入库成功');
			 ui_closePopWindow("<?php echo $request['ES_frmId']?>")
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
	$("#ys_btn").click(function(){
		ys_record();
	});
	$("#close_btn").click(function(){
		close_web_page();
	});
    $("#clean_scan").click(function(){
        clean_scan();
    });
});
function clean_scan(){
    BUI.Message.Confirm('确认要清除所有扫描记录吗？',function(){
        var url = '?app_act=stm/store_shift_record/clean_scan';
        data = {id: pid,record_code:record_code,out_type:'scan_in'};
        $.ajax({
            type: 'POST', dataType: 'json',
            url: url,
            data: data,
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    location.reload(true);
                } else {
                    BUI.Message.Alert(ret.message);
                }
            }
        });
    },'question')
}
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
