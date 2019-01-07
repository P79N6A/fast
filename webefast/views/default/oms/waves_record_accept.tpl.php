<style>
    .offset3{ margin:20px 0 5px 0;}
    .offset3 .span6{ margin-left:0; font-size:16px; font-weight:bold;}
    .offset3 .span6 span{ color:#DC0404;}
    #msg{color:#e95513; padding:8px; font-size:20px; border:2px solid #fc9580; text-align:center; margin-bottom:20px;}
    #barcode{font-size:20px;font-weight:bold;padding:10px; width:400px; color:#351A50; border:1px solid #999;}
    #table1 thead th{ border-bottom:none;}
</style>
<div class="row">
    <div class="span20">
        <div class="span16">
            <div class="controls">
                <input type="text" id="barcode" name="barcode" value="" class="control-text search-query control-row4">
                &nbsp;&nbsp;&nbsp;
               <!--  <span id="msg" style="color: #ff0000; font-weight: bold;"></span>-->
               <label class="control-label"> <button class="button button-primary btn_opt_accept_confirm">强制验收</button></label>
            </div>
        </div>

       <!--   <div class="span4">
            <div class="controls">

                <label class="control-label"> <button class="button button-primary btn_opt_accept_confirm">强制验收</button></label>

            </div>
        </div>-->
    </div>
</div>
<div class="row offset3">
    <div class="span6">
        <label class="control-label">商品数量：</label>
        <span class="controls" id="count_num"></span>
    </div>
    <div class="span6">
        <label class="control-label">扫描数量：</label>
        <span class="controls" id="scan_num">0</span>
    </div>
    <div class="span6">
        <label class="control-label">差异数量：</label>
        <span class="controls" id="diff_num">0</span>
    </div>
</div>
<div id="msg"  style="display:none"></div>

<table cellspacing="0" class="table table-bordered" id="table1">
    <thead>
    <tr>
        <th class="hide">deliver_record_detail_id</th>
        <th>商品编码</th>
        <th>商品名称</th>
        <th>规格1</th>
        <th>规格2</th>
        <th style="display: none;">SKU</th>
        <th>商品条形码</th>
        <th>数量</th>
        <th>扫描数量</th>
    </tr>
    </thead>
    <tbody>
    <?php $sum = 0; foreach($response['deliver_list'] as $key=>$detail){?>
        <tr class="detail_<?php echo $detail['deliver_record_detail_id'];?>">
            <td class="deliver_record_detail_id hide"><?php echo $detail['deliver_record_detail_id'];?></td>
            <td class="goods_code"><?php echo $detail['goods_code'];?></td>
            <td class="goods_name"><?php echo $detail['goods_name'];?></td>
            <td class="spec1_name"><?php echo $detail['spec1_name'];?></td>
            <td class="spec2_name"><?php echo $detail['spec2_name'];?></td>
            <td class="sku" style="display: none;"><?php echo $detail['sku'];?></td>
            <td class="barcode"><?php echo $detail['barcode'];?></td>
            <td class="num"><?php echo $detail['num'];?></td>
            <td class="scan_num"><?php echo $detail['scan_num'];?></td>
        </tr>
    <?php $sum += $detail['num']; }?>
    </tbody>
</table>
<bgsound loop="false" autostart="false" id="bgsound_ie" src="" />
<audio controls="controls" id="bgsound_others" style="display:none;" src=""></audio>
<script>
    var sounds = {
        "error": "<?php echo $response['sound']['error']?>",
        "success": "<?php echo $response['sound']['success']?>"
    };

    //播放提示音
    function play_sound(typ){
        var wav = "<?php echo CTX()->get_app_conf('common_http_url');?>js/sound/"+ sounds[typ]+".wav";
        if (navigator.userAgent.indexOf('MSIE') >= 0){//IE
            document.getElementById('bgsound_ie').src = wav;
        } else {// Other borwses (firefox, chrome)
            var obj = document.getElementById('bgsound_others');
            obj.src = wav;
            obj.play();
        }
    }

    function submit1(){
        var isOK = false
        var obj = $("#table1").find("tbody").find("tr")
        if(obj.length > 0){
            isOK = true
        }

        obj.each(function(index, item){
            var vNum = parseInt($(item).find(".num").text())
            var vScanNum = parseInt($(item).find(".scan_num").text())
            if(vScanNum != vNum){
                isOK = false
            }
        })

        if(isOK){
            var params = {waves_record_id: "<?php echo $response['waves']['waves_record_id']?>",is_scan:1};
            $.post("?app_act=oms/waves_record/accept_action", params, function(data){
                if(data.status != 1){
                    messageBox(data.message) // $("#msg").html(data.message)
                } else {
                    //$("#msg").html("已完成扫描并自动通过验收")
                    display_err_tips("已完成扫描并自动通过验收");
                    play_sound("success")
                    setTimeout(function(){
                        ui_closePopWindow("<?php echo $request['ES_frmId']?>")
                    }, 1000)
                }
            }, "json")
        } else {
            $("input[name='barcode']").val("")
            $("input[name='barcode']").focus()
        }
    }

    function barcode_check(iSku, isWarring){
        var deliverRecordDetailID = 0
        var isFind = false
        var isOK = false
        //var iSku = $(this).val();
        $("#table1").find("tbody").find("tr").each(function(index, item){console.log(item)
            var vSku = $(item).find(".sku").text()
            var vBarcode = $(item).find(".barcode").text()
            var vNum = parseInt($(item).find(".num").text())
            var vScanNum = parseInt($(item).find(".scan_num").text());
            if(vBarcode == iSku || vSku == iSku){
                isFind = true
                if(vScanNum < vNum){
                    $(item).find(".scan_num").text(vScanNum+1)
                    //$("#msg").html("扫描成功")
                    $("#scan_num").html(parseInt($("#scan_num").html())+1)
                    $("#diff_num").html(parseInt($("#diff_num").html())-1)
                    deliverRecordDetailID = $(item).find(".deliver_record_detail_id").text()
                    $("#barcode").focus()
                    isOK = true
                    return false //Breaking loop.
                }
            }
        })

        if(!isFind && isWarring) {
            messageBox("条码不存在:"+iSku) // $("#msg").html("超出商品数量")
            return -1
        }

        if(!isOK && isWarring) {
            messageBox("超出商品数量:"+iSku) // $("#msg").html("超出商品数量")
            return -2
        }

        if(isWarring){
            play_sound("success")
        }

        return deliverRecordDetailID
    }

    function barcode_check_result(s, isSubmit){

    }

    $(document).ready(function(){
        $("#count_num").html('<?php echo $sum?>')
        $("#diff_num").html('<?php echo $sum?>')

        $("#barcode").keyup(function(event){
            $("#msg").html("")
            if(event.keyCode == 13) {
                var b = $(this).val();
                b = b.replace(/(^\s*)|(\s*$)/g, ""); 
                var deliverRecordDetailID = barcode_check(b, false)
                if(deliverRecordDetailID <= 0) {
                    // try to find sub barcode.
                    $.post("?app_act=oms/waves_record/get_sku_by_sub_barcode", {waves_record_id: "<?php echo $response['waves']['waves_record_id']?>", sub_barcode: b}, function(data){
                        if(data.status == 1) {
                            var r1 = 0
                            for( var i in data.data){
                                // 验证条码过程不发出声音或提示错误
                                r1 = barcode_check(data.data[i], false)
                                if(r1 > 0 ){
                                    break
                                }
                            }
                            // 此处统一处理扫描结果
                            if(r1 <= 0){
                                messageBox("条码不存在或超出商品数量:"+b)
                            } else {
                                play_sound("success")
                                submit1()
                            }
                        } else {
                            messageBox(data.message, 'error')
                        }
                    }, "json")
                } else {
                    play_sound("success")
                    submit1()
                }
            }
        })

        $('.btn_opt_accept_confirm').click(function(){
            var obj = $("#table1").find("tbody").find("tr")
            obj.each(function(index, item){
                var vNum = parseInt($(item).find(".num").text())
                var vScanNum = parseInt($(item).find(".scan_num").text())
                if(vScanNum != vNum){
                    isOK = false
                }
            })
            var params = {waves_record_id: "<?php echo $response['waves']['waves_record_id']?>",is_scan:0};
            $.post("?app_act=oms/waves_record/accept_action", params, function(data){
                if(data.status != 1){
                    messageBox(data.message) // $("#msg").html(data.message)
                } else {

                    BUI.Message.Alert('强制验收成功',function(){
                        ui_closePopWindow("<?php echo $request['ES_frmId']?>")
                    },'info');

                }
            }, "json")
        });
    });

    function messageErr(){
        var msgUrl = "?app_act=base/error_confirm_code/do_list"
        openPage(window.btoa(msgUrl),msgUrl,"错误确认码")
    }
    function display_err_tips($msg){
    	$("#msg").html($msg);
    	$("#msg").show();
    	setTimeout("$('#msg').hide()", 3000);
    }
    function messageBox(m) {
        BUI.use('bui/overlay',function(Overlay){
            var msg = '<div style="text-align: center"><h2>'+m+'</h2><p class="auxiliary-text" style="padding-top:10px;"><input type="text" class="msg_code" value="" style="width:240px;" placeholder="请扫描错误确认码，如CONFIRM，以确认此错误"></p><p style="padding-top:10px;">提示：如没有错误确认码，请到<a href="javascript:messageErr();">错误确认码</a>中打印以供扫描</p></div>';

            var dialog = new Overlay.Dialog({
                title:'扫描错误',
                width:500,
                height:210,
                bodyContent:msg,//配置DOM容器的编号
                buttons:[
                    {
                        text:'确定',
                        elCls : 'button button-primary',
                        handler : function(){
                            //do some thing
                            this.close();
                        }
                    }
                ]
            });

            dialog.show();

            play_sound("error")

            dialog.on("closed", function(event){
                $("input[name='barcode']").val("");
                $("input[name='barcode']").focus();
                dialog.close();
            })

            $(".msg_code").val("");
            $(".msg_code").focus();
            $(".msg_code").keyup(function(event){
                if(event.keyCode == 13) {
                    if($(this).val() == 'CONFIRM'){
                        $("input[name='barcode']").val("");
                        $("input[name='barcode']").focus();
                        dialog.close()
                    }
                }
            });
        });
    }
</script>