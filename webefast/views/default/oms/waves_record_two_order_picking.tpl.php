<?php echo load_js('jquery.cookie.js') ?>

<style>
    .panel-body{padding-top: 1px;}
    #waves_record_code,#sum_num{
        padding-top: 10px;
    }
    form.form-horizontal {
        position: relative;
        padding: 5px 0px 5px;
        overflow: hidden;
    }
    .form-horizontal .control-label {width: auto;}
    .span8 { width: auto; }
    #record_waves_code{width: 240px;height: 30px;}
    #barcode{width:240px;height: 30px; }
    .control-text{font-size: 25px;}
    .spant{margin-right: 9px;}
    #scan_num,#diff_num,#count_num{font-size: 20px;}
    .result{height: 48px;}
    #demo12 {
        border: 10px solid transparent;
        border-left: 30px solid #090;
        width: 0;
        height: 0px;
        margin-top: 13px;
    }
    .offset3 .span6{ font-size:20px; font-weight:bold;}
    .offset3 .span6 span{ color: #2ca02c;}   
    #info_output{
        text-align: center;
        margin-top: 100px;
    }
</style>

<?php
render_control('PageHead', 'head1', array('title' => '二次分拣',
    'links' => array(
    ),
    'ref_table' => 'table'
));
?>
<bgsound loop="false" autostart="false" id="bgsound_ie" src="" />
<audio controls="controls" id="bgsound_others" style="display:none;" src=""></audio>
<form class="form-horizontal">
    <div class="panel">
        <div class="panel-body">
            <div class="row">
                <div class="control-group span8 spant">
                    <label class="control-label" style="height: 46px;line-height: 46px;">波次号/订单编号:</label>
                    <div class="controls">
                        <input type="text" class="control-text" placeholder="请扫描波次号或者订单编号" style="height:40px;font-weight:bold;font-size:20px;" id="record_waves_code">
                    </div>
                </div>
                <div class="control-group span8 spant">
                    <label class="control-label" style="height: 46px;line-height: 46px;">商品条形码:</label>
                    <div class="controls">
                        <input type="text" class="control-text" placeholder="请扫描商品条形码" style="height:40px;font-weight:bold;font-size:20px;" id="barcode">
                    </div>
                    <div class="controls">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="waves_record_id" />
</form>
<div>
    <span id="waves_record_code" style='height:40px;font-weight:bold;font-size:20px;padding-left:1%;'></span>
</div>
<div id="sum_num" style="padding-left:1%">
</div>
<div id='info_output'>
    <span id="deliver_info" style="font-size:50px;font-weight:bold;height:50px;color: #2ca02c"></span>
</div>
<script>
    var err_info = '';
    var sounds = {
        "error": "<?php echo $response['sound']['error'] ?>",
        "success": "<?php echo $response['sound']['success'] ?>"
    };
    //播放提示音
    function play_sound(typ) {
        var wav = "<?php echo CTX()->get_app_conf('common_http_url'); ?>js/sound/" + sounds[typ] + ".wav";
        if (navigator.userAgent.indexOf('MSIE') >= 0) {//IE
            document.getElementById('bgsound_ie').src = wav;
        } else {// Other borwses (firefox, chrome)
            var obj = document.getElementById('bgsound_others');
            obj.src = wav;
            obj.play();
        }
    }
    $("#barcode").attr('disabled', true);
    $(document).ready(function () {
        $("#record_waves_code").focus();
        $("#record_waves_code").keyup(function (event) {
            if (event.keyCode == 13) {
                var params = {record_waves_code: $(this).val()};
                $.post("?app_act=oms/deliver_record/check_record_waves", params, function (data) {
                    if (data.status == 1) { 
                        $("#deliver_info").html('');
                        $("#barcode").attr('disabled', false);
                        $("#record_waves_code").attr('disabled', true);
                        play_sound("success");
                        var result = data.data;
                        $('#waves_record_code').html("波次单号：<span id='record_code' style='margin-right: 40px;' >" + result.record_code + "</span><a href='#' onclick='get_deliver_detail()' >查看商品</a>");
                        $('#waves_record_id').val(result.waves_record_id);
                        $("#barcode").focus();
                        set_num(result);
                    } else {
                        $("#deliver_info").html('');
                        play_sound("error");
                        $("#record_waves_code").val("")
                        err_info = '<span id="deliver_info" style="font-size:50px;font-weight:bold;height:50px;color: red">'+data.message+'</span>';
                        display_tips(err_info);
                    }
                }, "json");
            }
        })
        $("#barcode").keyup(function (event) {
            if (event.keyCode == 13) {
                var record_code = $('#record_code').text();
                var params = {record_code: record_code, barcode: $(this).val()};
                $.post("?app_act=oms/deliver_record/check_barcode", params, function (data) {
                    if (data.status == 1) {
                        play_sound("success");
                        var result = data.data;
                        $("#barcode").focus();
                        set_num(result);
                        var is_complete = false;
                        if (result.valide_goods_count == result.sum_picking_num) {
                            //扫描完毕就清空数据
                            is_complete = true;
                        }
                        err_info = '<span id="deliver_info" style="font-size:50px;font-weight:bold;height:50px;color: #2ca02c">篮位号(序号)：'+result.sort_no+'</span>';
                        display_tips(err_info,is_complete);
                    } else {
                        $("#deliver_info").html('');
                        play_sound("error");
                        $("#barcode").val("");
                        err_info = '<span id="deliver_info" style="font-size:50px;font-weight:bold;height:50px;color: red">'+data.message+'</span>';
                        display_tips(err_info);
                    }
                }, "json");
            }
        })
    })
    //清空数据
    function wipe_data() {
        var record_code = $('#record_code').text();
        err_info = '<span id="deliver_info" style="font-size:50px;font-weight:bold;height:50px;color: #2ca02c">'+record_code+'波次单已分拣完毕</span>';
        $("#info_output").html(err_info);
        $("#barcode").attr('disabled', true);
        $("#record_waves_code").attr('disabled', false);
        $("#record_waves_code").val('');
        $('#sum_num').html('');
        $('#waves_record_code').html("");
        $("#record_waves_code").focus();
    }
    function display_tips(msg,is_complete = false) {
        $("#info_output").html(msg);
        $("#barcode").val('');
        if(is_complete == true) {
            setTimeout(wipe_data,2000)
        }
        //setTimeout("$('#deliver_info').html('')", 2000);
    }

    function set_num(result) {
        var difference_num = result.valide_goods_count - result.sum_picking_num;
        $('#sum_num').html('<div class="row offset3"><div class="span6"><label class="control-label">商品数量：</label><span class="controls" id="goods_count">' + result.valide_goods_count + '</span></div><div class="span6"><label class="control-label">分拣数量：</label><span class="controls" id="scan_num">' + result.sum_picking_num + '</span></div><div class="span6"><label class="control-label">差异数量：</label><span class="controls" id="difference_num">' + difference_num + '</span></div></div>');
        return;
    }
    function get_deliver_detail() {
        var waves_record_id = $('#waves_record_id').val();
        var record_code = $('#record_code').text();
        var url = '?app_act=oms/waves_record/picking_view&waves_record_id=' + waves_record_id+'&record_code='+record_code;
            openPage(window.btoa(url), url, '波次单详情');
    }
</script>