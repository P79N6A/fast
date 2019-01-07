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
            .scan_div #scan_barcode{font-size:20px;font-weight:bold;padding:10px; width:400px; color:#351A50; border:1px solid #999;}
            #err_tips{color:#e95513; padding:8px; font-size:20px; border:2px solid #fc9580; text-align:center; margin-bottom:20px;}
            #force_recieve_btn,#close_btn{width:135px;height:40px;font-size:20px; cursor:pointer; margin-left:15px; background:#f2f2f2; color:#666; border:1px solid #999; border-radius:3px;}
            #force_recieve_btn:hover,#close_btn:hover{ background:#FFF;}
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
                <div class="tips tips-small  tips-success"> <span class="x-icon x-icon-small x-icon-success"><i class="icon icon-white icon-ok"></i></span>
                    <div class="tips-content">验收成功</div>
                </div>
            </div>


            <div class="scan_div">
                <input type="text" id="scan_barcode"/>
                <input type="button" id="force_recieve_btn" value="确认收货"/>
              <!--   <input type="button" id="close_btn" value="关 闭"/> -->
                <span id="msg" style="color: #ff0000; font-weight: bold;"></span>
            </div>
            <div class="scan_sl_info"> <span class="lab">总数: </span><span class="lab_v" id="total_sl"><?php echo $response['total_sl'] ?></span> <span class="lab">已扫描: </span><span class="lab_v" id="total_scan_sl"><?php echo $response['total_scan_sl'] ?></span> <span class="lab">差异: </span><span class="lab_v" id="diff_num"><?php echo $response['total_no_scan_sl'] ?> </span> </div>
            <div id="err_tips" style="display:none"></div>
            <div class="mx_tbl">
                <table id="sku_tbl">
                    <thead>
                        <tr>
                            <th>商品名称</th>
                            <th>商品编码</th>
                            <th>规格1</th>
                            <th>规格2</th>
                            <th>商品条形码</th>
                            <th>申请退货数</th>
                            <th>实退数量</th>
                            <th>库位</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($response['mx'] as $sub_mx) { ?>
                            <tr>
                                <td><?php echo $sub_mx['goods_name']; ?></td>
                                <td><?php echo $sub_mx['goods_code']; ?></td>
                                <td><?php echo $sub_mx['spec1_name']; ?></td>
                                <td><?php echo $sub_mx['spec2_name']; ?></td>
                                <td class='barcode'><?php echo $sub_mx['barcode']; ?></td>
                                <td class='note_num'><?php echo $sub_mx['note_num']; ?></td>
                                <td class="smsl" id="sku_num_<?php echo str_replace('#', '_', $sub_mx['sku']);?>"><?php echo "<span>{$sub_mx['recv_num']}</span>"; ?></td>
                                    <td ><?php echo $sub_mx['shelf_name']; ?></td>
                            </tr>
                        </tbody>
                    <?php } ?>
                </table>
            </div>

        </div>
        <script type="text/javascript">
            var g_total_sl = <?php echo $response['total_sl']; ?>;//总数量
            var g_total_scan_sl = <?php echo $response['total_scan_sl']; ?>;//已扫描数量
            var unique_status = "<?php echo $response['unique_status']; ?>";//唯一码是否开启
            var sell_return_code = "<?php echo $request['sell_return_code']; ?>";
            var return_package_code = "<?php echo $response['return_package_code']; ?>";
            var type = "<?php echo $request['type']; ?>";
            var detail = <?php if (empty($response['detail_key'])) {
                        echo '{}';
                    } else {
                        echo json_encode($response['detail_key']);
                    } ?>;
            var detail_mx = <?php echo json_encode($response['detail_key']); ?>;
            var unique_barcode = new Array();
            var sounds = {
                "error": "0",
                "success": "1"
            };
            $(document).ready(function () {
                $("#scan_barcode").bind('keypress', function (event) {
                    if (event.keyCode == "13")
                    {
                        barcode_check();
                    }
                });
                $("#scan_barcode").focus();
                // 	$("#close_btn").click(function(){
                // 		close_web_page();
                // 	});
            });

            function update_detail_num(scan_barcode) {
                var url;
                if (type == 'package' && sell_return_code == '') {
                    url = "?app_act=oms/sell_return/scan_barcode_no_return_code";
                } else {
                    url = "?app_act=oms/sell_return/scan_barcode&type=" + type;
                }
                var param = {app_fmt: 'json', record_code: sell_return_code, scan_barcode: scan_barcode, return_package_code: return_package_code};
                $.ajax({
                    type: "GET",
                    url: url,
//                    async: false,
                    data: param,
                    success: function (json_data) {
                        var result = eval('(' + json_data + ')');
                        if (result == undefined || result.status == undefined) {
                            display_err_tips('扫描出错： ' + json_data);
                        } else if (result.status < 0) {
                            display_err_tips('扫描出错： ' + result.message);
                        } else if (result.status == 3 || result.status == 9 || (result.status == 2 && result.message != '')) {
                            display_err_tips(result.message);
                        }
                        //验证barcode
                        verify_add_barcode(result);
                        //添加扫描数量
                        var sku = result.data;
                        if (sku.length > 0 && result.status > 0) {
                            add_scan_num(result, scan_barcode);
                        }
                        if (result.status != 3) {
                            check_each_sacn_num();
                        }
                    }
                });
            }
            //添加扫描数量
            function add_scan_num(result, iSku) {
                //var vNum = $("#sku_num_" + result.data).prev().text();
                //var vScanNum = $("#sku_num_" + result.data).text();
              //  $("#sku_num_" + result.data).parents('td').text();
              
              var scan_barcode = result.data.replace('#','_');

//                if (vScanNum < vNum) {
//                    if (result.status == 2) {
//                        unique_flag = 1;//标记为 唯一码
//                        insert_unique_barcode(iSku);
//                    }
//                }
                
                if ($.inArray(iSku, unique_barcode) < 0 && result.status == 2 ) {
                    unique_barcode.push(iSku);
                }
                var sku = scan_barcode;
                var sku_html = scan_barcode.replace('.', "\\.");//处理带点特殊处理
                var scaned_num = $("#sku_num_" + sku_html).text();
                $("#sku_num_" + sku_html).html(parseInt(scaned_num) + 1);
                detail[sku]['num'] = parseInt(scaned_num) + 1;
                $("#scan_barcode").val("");
                $("#total_scan_sl").html(parseInt($("#total_scan_sl").html()) + 1);
                $("#diff_num").html(parseInt($("#diff_num").html()) - 1);
                $("#msg").html("扫描成功");
                $("#scan_barcode").focus();
            }
            //验证barcode
            function verify_add_barcode(result) {
                if (result.status == 3) {
                    var html = '';
                    var data = result.data;
                    html = "<tr><td>" + data.goods_name + "</td><td>" + data.goods_code + "</td><td>" + data.spec1_name + "</td><td>" + data.spec2_name + "</td><td class='barcode'>" + data.barcode + "</td><td class='note_num'>" + data.apply_num + "</td><td  class='smsl' id='sku_num_" + data.sku + "'><span >" + data.num + "</span></td><td>" + data.shelf_name + "</td></tr>";
                    $("#sku_tbl").append(html);
                    detail[data.sku] = {};
                    detail[data.sku]['note_num'] = data.apply_num;
                    detail[data.sku]['num'] = data.num;
                    $("#total_scan_sl").html(parseInt($("#total_scan_sl").html()) + 1);
                    $("#diff_num").html(parseInt($("#diff_num").html()) - 1);
                }
            }
            function barcode_check() {
                var iSku = $("#scan_barcode").val().trim();
                if(iSku==''){
                    display_err_tips('扫描出错：未扫描商品');
                    return;
                }
                scan_barcode_ret = update_detail_num(iSku);
            }

//            function insert_unique_barcode(unique_code) {
//                var url = "?app_act=oms/sell_return/insert_unique_barcode";
//                var param = {app_fmt: 'json', record_code: sell_return_code, unique_code: unique_code};
//                $.ajax({
//                    type: "GET",
//                    url: url,
//                    async: false,
//                    data: param,
//                    success: function (json_data) {
//                    }
//                });
//            }
            function check_each_sacn_num() {
                var tag = true;
                $.each(detail, function (i, value) {
                    //扫描数量等于总数量自动收货
                    if (value.note_num == 0 || value.note_num > value.num) {
                        tag = false;
                    }
                });
                if (tag === true) {
                    submit_it('scan_barcode');
                }
            }


            $("#force_recieve_btn").click(function () {
                if (confirm("确定要强制确认收货嘛？")) {
                    submit_it('force_acceptance');
                    return;
                }
            })


            function submit_it(type) {
                var params = {sell_return_code: sell_return_code, type: type};
                params.return_package_code = return_package_code;
                $.post("?app_act=oms/sell_return/opt_return_shipping", params, function (data) {
                    if (data.status != 1) {
                        display_err_tips(data.message);
                    } else {
                        if(type == 'force_acceptance'){//强制确认收货
                            if (unique_status == 1) { //修改强制收获也更新唯一码
                                unique_code_log(unique_barcode);
                            }
                        }else{
                            if (unique_status == 1 && data.all_num==data.scan_num) {
                                unique_code_log(unique_barcode);
                             }
                        }
                        
                        $("#msg").html("扫描完成，收货成功");
                        $("#scan_barcode").attr('disabled', true);
                        play_sound("success")
                    }
                }, "json");
            }

            function unique_code_log(iSku) {
                if (iSku.length > 0) {
                    var params = {barcode: iSku, record_code: sell_return_code, record_type: 'sell_return', action_name: 'return_storage'}
                    $.post("?app_act=prm/goods_unique_code/unique_code_log", params, function (data) {
                        if (data.status != 1) {
                            BUI.Message.Alert(data.message, 'error');
                            return;
                        }
                    }, "json");
                }
                return;
            }

            function display_err_tips($msg) {
                $("#err_tips").html($msg);
                $("#err_tips").show();
                $("#scan_barcode").val('');
                play_sound("error");
                setTimeout("$('#err_tips').hide()", 3000);
            }

            function close_web_page() {
                if (navigator.userAgent.indexOf("MSIE") > 0) {
                    if (navigator.userAgent.indexOf("MSIE 6.0") > 0) {
                        window.opener = null;
                        window.close();
                    } else {
                        window.open('', '_top');
                        window.top.close();
                    }
                } else if (navigator.userAgent.indexOf("Firefox") > 0) {
                    window.location.href = 'about:blank ';
                    //window.history.go(-2);
                } else {
                    window.opener = null;
                    window.open('', '_self', '');
                    window.close();
                }
            }

            //播放提示音
            function play_sound(typ) {
                var wav = "../../webpub/js/sound/" + sounds[typ] + ".wav";
                if (navigator.userAgent.indexOf('MSIE') >= 0) {//IE
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