<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="content-Type" content="text/html; charset=UTF-8" />
        <title>宝塔eFAST 365</title>
        <script type="text/javascript" src="../../webpub/js/jquery-1.8.1.min.js"></script>
        <script type="text/javascript" src="../../webpub/js/bui/bui.js"></script>
<!--        <script type="text/javascript" src="../../webpub/js/util/date.js"></script>-->
        <script type="text/javascript" src="../../webpub/js/common.js"></script>
<!--        <script type="text/javascript" src="?app_act=common/js/index"></script>-->
<!--        <script src="assets/js/jquery.formautofill2.min.js"></script>-->
        <script type="text/javascript" src="../../webpub/js/jquery.cookie.js"></script>
        <link href="assets/css/bui.css" rel="stylesheet" type="text/css"></link>
        <link href="assets/css/main-min.css" rel="stylesheet" type="text/css"></link>
        <link href="assets/css/dpl.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/common.css" rel="stylesheet" type="text/css" />
        <style>
            /*reset*/
            body,div,a,p,ul,li,img,h1,h2,h3,h4,h5,h6,ol,table,tr,td,form,input,button{ margin:0; padding:0;font-family: "Microsoft YaHei","微软雅黑","Arial","宋体","Times New Roman",Times,serif;}
            a{ text-decoration:none;}
            li{ list-style:none;}
            img{ border:none;}

            .scan_wrap{ padding:15px 1.5%; color:#333;}
            .scan_wrap .dj_info .lab_v{ margin-right:100px;}
            #total_no_scan_sl{color:red;cursor: pointer;}
            .mx_tbl table{ width:100%; border-collapse:collapse; background:#FFF;}
            .mx_tbl th,.mx_tbl td{padding:4px;border:1px #ccc solid;}
            .mx_tbl th{background:#f2f2f2;}
            .mx_tbl td{ text-align:center;}
            .mx_tbl td.smsl{ color:#008000;}
            .scan_div{ padding:20px 0;}
            .scan_div #scan_barcode{font-size:20px;font-weight:bold;padding:10px; width:400px; color:#351A50; border:1px solid #999;}
            #err_tips{color:#e95513; padding:8px; font-size:20px; border:2px solid #fc9580; text-align:center; margin-bottom:20px;}
            #ys_box_record_and_print,#ys_box_task,#clean_scan,#cancel_box,#box_view,#print_aggr_box{width:120px;height:50px;font-size:20px; cursor:pointer; margin-left:15px; background:#f2f2f2; color:#666; border:1px solid #999; border-radius:3px;}

            #ys_box_record_and_print:hover,#ys_box_task:hover,#clean_scan:hover,#cancel_box:hover,#print_aggr_box:hover{ background:#FFF;}
            #success_tips{ padding:8px 0; color:#3c763d; font-size:20px; background:#fdffe1; border:2px solid #cfdba1; text-align:center; margin-bottom:5px;}
            #ys_tips{ padding:8px 0; color:red;text-indent:24px; font-size:14px; background:#fdffe1; border:2px solid #cfdba1; text-align:left; margin-bottom:5px;}
            .scan_wrap .scan_sl_info{ padding:10px 0 20px; font-size:18px;}
            .scan_wrap .scan_sl_info .lab{ font-weight:bold;}
            .scan_wrap .scan_sl_info .lab_v{ display:inline-block; width:150px; text-indent:50px; color:#e95513;}
            .sku_num{width: 50px;cursor:pointer;text-decoration:underline;}
            .update_num{width: 50px;cursor:pointer;text-decoration:underline;color:#008000;font-size: 15px}
            .big-font-num, .smsl{font-size: 25px;}
            .big-font-word{font-size: 20px;}
        </style>
    </head>
    <body style="overflow-x:hidden; background:#f6f6f6;">
        <?php include get_tpl_path('web_page_top'); ?>
        <?php echo load_js('core.min.js');?>
        <div class="scan_wrap">

            <div id="success_tips" style="display:none">
                <div class="tips tips-small  tips-success"> <span class="x-icon x-icon-small x-icon-success"><i class="icon icon-white icon-ok"></i></span>
                    <div class="tips-content">装箱任务验收成功</div>
                </div>
            </div>

            <div class="scan_sl_info">
                <span class="lab big-font-word">当前箱商品总数: </span><span class="lab_v big-font-num" id="cur_scan_sl">0</span>
                <span class="lab big-font-word">商品总数量: </span><span class="lab_v big-font-num" id="total_sl"><?php echo $response['total_sl'] ?></span>
                <span class="lab big-font-word">装箱商品总数: </span><span class="lab_v big-font-num" id="total_scan_sl"><?php echo $response['total_scan_sl'] ?></span>
                <span class="lab big-font-word">差异数: </span><span class="lab_v big-font-num" id="total_no_scan_sl"> </span>
            </div>

            <div class="dj_info">
                <span class="lab"><b>箱号</b>: </span><span class="lab_v" id="box_code"> <?php echo $response['dj_info']['box_code']; ?></span>
                <span class="lab">装箱任务号: </span><span class="lab_v"> <?php echo $response['dj_info']['task_code']; ?></span>
                <span class="lab"><?php echo $response['dj_info']['dj_type_name'] ?> : </span><span class="lab_v"><?php echo $response['dj_info']['record_code'] ?></span>
            </div>

            <div class="dj_info">
                <span class="lab">创建日期: </span><span class="lab_v"><?php echo $response['dj_info']['order_time'] ?></span>
                <span class="lab">仓库: </span><span class="lab_v"><?php echo $response['dj_info']['store_name'] ?></span>
                <span class="lab">分销商: </span><span class="lab_v"><?php echo $response['dj_info']['custom_name'] ?></span>
            </div>

            <div id="ys_tips">
                <div class="tips tips-small  tips-success"> <span class="x-icon x-icon-small x-icon-success"><i class="icon icon-white icon-ok"></i></span>
                    <div class="tips-content">请在装箱任务全部结束后，点击 [任务验收] 按钮，验收当前的装箱任务。&nbsp&nbsp&nbsp&nbsp&nbsp系统提供装箱码：6666，扫描枪扫描可代替点击‘下一箱’操作</div>
                </div>
            </div>

            <div class="scan_div">
                <input type="text" id="scan_barcode"/>
                <input type="button" id="ys_box_record_and_print" value="下一箱"/>
                <input type="button" id="ys_box_task" value="任务验收"/>
                <input type="button" id="clean_scan" value="清除扫描记录"/>
                <input type="button" id="box_view" value="查看装箱单"/>
                <input type="button" id="cancel_box" value="取消装箱" title="仅限未实物装箱操作"/>
<!--                --><?php //if ($request['dj_type'] == 'wbm_store_out'): ?>
<!--                    <input type="button" id="print_aggr_box" value="打印汇总单"/>-->
<!--                --><?php //endif; ?>
                <!--
                <a href="###">更改绑定的打印机</a>
                -->
            </div>
            <div>
                <span><input name="auto_print_box" id="auto_print_box"  type="checkbox" checked >自动打印装箱单</input></span>
                <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                <?php if ($response['wph_jit_service_status'] == true && $response['dj_info']['connection_jit'] == true) : ?>
                    <span><input name="auto_print_jit_box" id="auto_print_jit_box" type="checkbox" checked >自动打印JIT箱唛</input></span>
                <?php else: ?>
                    <span><input name="auto_print_general_box" id="auto_print_general_box" type="checkbox" checked >自动打印普通箱唛</input></span>
                <?php endif; ?>
                <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                <span><input name="auto_print_aggr_box" id="auto_print_aggr_box"  type="checkbox"  >自动打印汇总单</input></span>
                <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                <?php if($response['new_clodop_print'] == 1){?>
                <span id='choose_printer' style='color: #3366cc;cursor: pointer;display: inline-block;'>修改打印机</span>
                <?php }?>
            </div>
            <div id="err_tips" style="display:none"></div>

            <div class="mx_tbl">
                <table id="sku_tbl">
                    <tr>
                        <th>商品名称</th>
                        <th>商品编码</th>
                        <th><?php echo $response['base_spec1_name']; ?></th>
                        <th><?php echo $response['base_spec2_name']; ?></th>
                        <th>商品条形码</th>
                        <th>扫描数量</th>
                    </tr>
                    <?php foreach ($response['box_scan_data'] as $sub_mx) { ?>
                        <tr class="sku_tr">
                            <td><?php echo $sub_mx['goods_name']; ?></td>
                            <td><?php echo $sub_mx['goods_code']; ?></td>
                            <td><?php echo $sub_mx['spec1_name']; ?></td>
                            <td><?php echo $sub_mx['spec2_name']; ?></td>
                            <td><?php echo $sub_mx['barcode']; ?></td>
                            <td class="smsl big-font-num"><?php echo "<span onclick=update_goods_num('{$sub_mx['sku']}') id='sku_num_{$sub_mx['sku']}' class='sku_num'>{$sub_mx['num']}</span>"; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

        </div>

        <iframe src="" id="print_iframe" style="width:100px;height:100px;border:0px red solid;visibility: hidden;" scrolling="no"></iframe>

        <script type="text/javascript">
            var g_total_sl = <?php echo $response['total_sl']; ?>;//总数量
            var g_total_scan_sl = <?php echo $response['total_scan_sl']; ?>;//已扫描数量
            var g_mx_info = <?php echo json_encode($response['scan_data_js']); ?>;//SKU的明细信息 sku=>已扫数量
            var g_scan_barcode_map = <?php echo json_encode($response['scan_barcode_map']); ?>;//扫描过的条码和SKU的对应关系
            var g_must_scan_mx = <?php echo json_encode($response['must_scan_mx']); ?>;//完成单的SKU的明细信息，如果完成单的明细sum(num)=0,那么取通知单的明细
            var g_dj_info = <?php echo json_encode($response['dj_info']); ?>;
            var new_clodop_print = "<?php echo $response['new_clodop_print'];?>";
            var ids = new Array();
            var sounds = {
                "error": "0",
                "success": "1"
            };
            $("#choose_printer").click(function(){
                var select  = new Array();
                $('input[type="checkbox"]').each(function(){                   
                   select.push($(this).attr('id'));
                });
                printer_select(select);
            })
            //查看差异商品
            $("#total_no_scan_sl").click(function () {
                var diff_num = g_total_sl - g_total_scan_sl;
                window.open("?app_act=wbm/store_out_record/diff_detail&record_code=<?php echo $response['record_code']; ?>&store_name=" + g_dj_info['store_name'] + "&custom_name=" + g_dj_info['custom_name'] + "&diff_num=" + diff_num);
            });            
            if(new_clodop_print == 1){
            $('#auto_print_box').change(function(){
                if($(this).is(':checked')){
                    printer_check(['auto_print_box']);
                }
            });
            $('#auto_print_jit_box').change(function(){
                if($(this).is(':checked')){
                    printer_check(['auto_print_jit_box']);
                }
            });
            $('#auto_print_general_box').change(function(){
                if($(this).is(':checked')){
                    printer_check(['auto_print_general_box']);
                }
            });
            $('#auto_print_aggr_box').change(function(){
                if($(this).is(':checked')){
                    printer_check(['auto_print_aggr_box']);
                }
            });
        }
            var p_time = 0;
            function print_express(w, _type,printer) {
            var id = "print_express" + p_time;
            var iframe = $('<iframe id="' + id + '" width="0" height="0"  style="border:0px red solid;" scrolling="no"></iframe>').appendTo('body');
            if(new_clodop_print == 1){
                    var url = "?app_act=b2b/box_record/print_express&iframe_id=" + id + "&ids=" + w + "&print_templates_code=" + _type + '&new_clodop_print=' + new_clodop_print + '&clodop_printer=' + $.cookie(printer + '_printer') + '&print_type=scan_box';
                    var iframe = $('<iframe id="' + id + '" width="0" height="0"  style="border:0px red solid;" scrolling="no"></iframe>').appendTo('body');
                    iframe.attr('src', url);     
            }else{
                    var url = "?app_act=b2b/box_record/print_express&iframe_id=" + id + "&ids=" + w + "&print_templates_code=" + _type + '&print_type=scan_box';
                    iframe.attr('src', url);             
            }
            p_time++;
            }


            function js_total_no_scan_sl() {
                $('#total_no_scan_sl').html(g_total_sl - g_total_scan_sl);
            }

            //更新当前箱商品总数
            function update_cur_scan_sl() {
                var scan_num = 0;
                $('#sku_tbl tr').each(function (i, e) {
                    if (i != 0) {
                        scan_num += parseInt($(this).children('td:eq(-1)').text());
                    }
                });
                $('#cur_scan_sl').text(scan_num);
            }

            function update_total_scan_sl() {
                var total_scan_sl = 0;
                for (var i in g_mx_info) {
                    total_scan_sl += parseInt(g_mx_info[i]);
                }
                $("#total_scan_sl").html(total_scan_sl);
                g_total_scan_sl = total_scan_sl;
                js_total_no_scan_sl();
            }
            js_total_no_scan_sl();

            function scan_barcode() {
                $('#err_tips').hide();
                var scan_barcode = $("#scan_barcode").val();
                var find_barcode = g_scan_barcode_map[scan_barcode];
                var barcode_is_exist = 1;
                if (find_barcode == undefined) {
                    barcode_is_exist = -1;
                }
                var url = "?app_act=common/record_scan_box/save_scan";
                var dj_type = g_dj_info['dj_type'];
                var relation_code = g_dj_info['relation_code'];
                var task_code = g_dj_info['task_code'];
                var param = {app_fmt: 'json', dj_type: dj_type, record_code: g_dj_info['record_code'], task_code: g_dj_info['task_code'], box_code: g_dj_info['box_code'], scan_barcode: scan_barcode, barcode_is_exist: barcode_is_exist, tzd_code: relation_code};
                //alert('ss');
                //console.log(param);
                //console.log(g_dj_info);
                //return;
                $.get(url, param,
                        function (json_data) {
                            try {
                                var result = eval('(' + json_data + ')');
                            } catch (e) {
                            }
                            if (result == undefined || result.status == undefined) {
                                display_err_tips('扫描出错： ' + json_data);
                                return;
                            }
                            if (result.status < 0) {
                                display_err_tips('扫描出错： ' + result.message);
                                //act_check_scan_end();
                            } else {
                                var data = result.data;
                                if (data.scan_barcode == undefined || data.sku == undefined || data.num == undefined) {
                                    display_err_tips('扫描出错： ' + json_data);
                                    //act_check_scan_end();
                                    return;
                                }
                                var data = result.data;
                                scan_barcode_update(data);
                                update_cur_scan_sl();
                            }
                        });
            }

            function scan_barcode_update(data) {
                var sku = data.sku;
                var scan_barcode = data.barcode;
                g_mx_info[sku] = parseInt(data.num);
                var sku_id = sku.replace('.', "\\.");//处理带点特殊处理
                if (g_must_scan_mx[sku]) {
                    g_must_scan_mx[sku]['num'] = parseInt(data.num);
                }
                if ($("#sku_num_" + sku_id).text() != '') {
                    //  if (data.barcode_is_exist == 1) {
                    $("#sku_num_" + sku_id).html(data.box_num);
                } else {
                    g_scan_barcode_map[scan_barcode] = sku;
                    var html = "<tr class='sku_tr'><td>" + data.goods_name + "</td><td>" + data.goods_code + "</td><td>" + data.spec1_name + "</td><td>" + data.spec2_name + "</td><td>" + data.barcode + "</td><td class='smsl big-font-num'><span onclick=update_goods_num('" + data.sku + "') class='sku_num' id='sku_num_" + data.sku + "'>" + data.box_num + "</span></td></tr>";
                    $('#sku_tbl tr').eq(0).after(html);
                }

                update_total_scan_sl();
                $("#scan_barcode").val('');
                $("#scan_barcode").focus();
                play_sound("success");
                //act_check_scan_end();
            }

            function act_check_scan_end() {
                if (check_scan_end()) {
                    if (confirm("商品数量符合，是否验收？")) {
                        ys_record();
                        return;
                    }
                }
            }

            function check_scan_end() {
                var is_end = 1;
                for (var i in g_must_scan_mx) {
                    if (parseInt(g_must_scan_mx[i]['enotice_num']) > parseInt(g_must_scan_mx[i]['num'])) {
                        is_end = 0;
                        break;
                    }
                }
                return is_end;
            }

            //验收
            function ys_record() {
                var dj_type = g_dj_info['dj_type'];
                if (!check_scan_end()) {
                    BUI.Message.Confirm('还未扫描完毕，商品数量存在差异，是否验收？', function () {
                        if (dj_type == 'wbm_store_out') {
                            weipinhui_ys_act();
                        } else {
                            ys_box_record_and_print(2);
                        }
                    });
                    return;
                }
                ys_box_record_and_print(2);
                //ys_box_task();
            }

            //唯品会生产的批发销货单是否差异出库
            function weipinhui_ys_act() {
                var url = '<?php echo get_app_url('wbm/store_out_record/do_shift_out_weipinhui_check&type=1'); ?>';
                var params = {record_code: g_dj_info['record_code']};
                $.post(url, params, function (data) {
                    if (data.status != 1) {
                        BUI.Message.Confirm(data.message, function () {
                            ys_box_record_and_print(2);
                        });
                    } else {
                        ys_box_record_and_print(2);
                    }
                }, "json");
            }

            //展示错误信息
            function display_err_tips($msg) {
               // $("#err_tips").html($msg);
               // $("#err_tips").show();
                messageBox($msg);
                //$("#scan_barcode").val('');
                //$("#scan_barcode").focus();
                //play_sound("error");
//                setTimeout("$('#err_tips').hide()", 3000);
            }

            $(document).ready(function () {
                $("#scan_barcode").bind('keypress', function (event) {
                    if (event.keyCode == "13")
                    {
                        if ('6666' === $('#scan_barcode').val()) {
                            ys_box_record_and_print(1);
                            $('#scan_barcode').val('');
                            return;
                        }
                        scan_barcode();
                    }
                });
                $("#scan_barcode").focus();
                $("#ys_box_record_and_print").click(function () {
                    ys_box_record_and_print(1);
                });
                $("#ys_box_task").click(function () {
                    ys_record();
                });
                $("#clean_scan").click(function () {
                    clean_scan();
                });
                $("#box_view").click(function () {
                    url = '?app_act=b2b/box_record/do_list&view_type=1&is_print=all&task_code=' + g_dj_info['task_code'] + '&is_jit_execute=<?php echo $response['is_jit_execute'] ?>';
                    window.open(url);
                });
                $("#cancel_box").click(function () {
                    cancel_box();
                });
                auto_print_check('auto_print_box');
                $("#auto_print_box").change(function () {
                    auto_print_change('auto_print_box');
                });
                //保存cookie
                auto_print_check('auto_print_aggr_box');
                $("#auto_print_aggr_box").change(function () {
                    auto_print_change('auto_print_aggr_box');
                });
<?php if ($response['wph_jit_service_status'] == true && $response['dj_info']['connection_jit'] == true) : ?>
                    auto_print_check('auto_print_jit_box');
                    $("#auto_print_jit_box").change(function () {
                        auto_print_change('auto_print_jit_box');
                    });
<?php else: ?>
                    auto_print_check('auto_print_general_box');
                    $("#auto_print_general_box").change(function () {
                        auto_print_change('auto_print_general_box');
                    });
<?php endif; ?>
                if(new_clodop_print == 1){
                      $('input[type="checkbox"]:checked').each(function(){                   
                            ids.push($(this).attr('id'));
                        });
                      printer_check(ids); 
                }
            });
            function printer_check(printer_id){
                var flg = true;
                for(var i in printer_id){
                    if ($.cookie(printer_id[i] + '_printer') == null){
                        flg = false;
                    }
                }
                if(flg == false){
                    printer_select(printer_id);
                }
            }
            //页面加载时，读取cookie,设置自动打印选中状态
            function auto_print_check(checkbox_name) {
                if ($.cookie(checkbox_name + '_check') == 1) {
                    if ($.cookie(checkbox_name) == 'checked') {
                        $('#' + checkbox_name).attr('checked', 'checked');
                    } else {
                        $('#' + checkbox_name).removeAttr('checked');
                    }
                }
            }

            //自动打印选中状态更改时，设置cookie
            function auto_print_change(checkbox_name) {
                $.cookie(checkbox_name, $('#' + checkbox_name).attr('checked'), {expires: 30});
                $.cookie(checkbox_name + '_check', 1, {expires: 30});
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
            //create_box_record_flag=0 不打印下一箱 1 打印下一箱 2 任务验收
            function ys_box_record_and_print(create_box_record_flag) {
                var url = "?app_act=common/record_scan_box/ys_box_record";
                var dj_type = g_dj_info['dj_type'];
                var relation_code = g_dj_info['relation_code'];

                var param = {app_fmt: 'json', dj_type: dj_type, record_code: g_dj_info['record_code'], task_code: g_dj_info['task_code'], box_code: g_dj_info['box_code']};
                //console.log(param);return;
                $.get(url, param,
                        function (json_data) {
                            try {
                                var result = eval('(' + json_data + ')');
                            } catch (e) {
                            }
                            if (result == undefined || result.status == undefined) {
                                display_err_tips('装箱单验收出错： ' + json_data);
                                return;
                            }
                            if (result.status < 0) {
                                display_err_tips('装箱单验收出错： ' + result.message);
                            } else {
                                //alert('装箱单验收成功');
                                if (create_box_record_flag == 1) {
                                    create_box_record();
                                } else if (create_box_record_flag == 2) {
                                    //任务验收
                                    ys_box_task();
                                }
                                $("#scan_barcode").focus();
                                $('#cur_scan_sl').text(0);
                            }
                        });
            }

            function create_box_record() {
                var url = "?app_act=common/record_scan_box/create_box_record";
                var param = {app_fmt: 'json', dj_type: g_dj_info['dj_type'], task_code: g_dj_info['task_code'], store_code: g_dj_info['store_code']};
                $.get(url, param,
                        function (json_data) {
                            try {
                                var result = eval('(' + json_data + ')');
                            } catch (e) {
                            }
                            if (result == undefined || result.status == undefined) {
                                display_err_tips('新建装箱单出错： ' + json_data);
                                return;
                            }
                            if (result.status < 0) {
                                display_err_tips('新建装箱单出错： ' + result.message);
                            } else {
                                //console.log(result);
                                var old_box_id = g_dj_info['box_id'];
                                g_dj_info['box_code'] = result.data.box_code;
                                g_dj_info['box_id'] = result.data.box_id;
                                //console.log(g_dj_info);
                                $(".sku_tr").remove();
                                g_scan_barcode_map = {};
                                //g_mx_info = {};
                                update_total_scan_sl();
                                //print_box(old_box_id);
                                $("#box_code").html(result.data.box_code);
                                $('#sku_tbl .sku_tr').remove();
                                auto_print(old_box_id);
                            }
                        });
            }
            function auto_print(box_id) {
                //var box_id = g_dj_info['box_id'];
                    if ($("#auto_print_box").attr("checked")) {
                        print_box(box_id,'printer');
                    }                
                    if ($("#auto_print_jit_box").attr("checked")) {
                        setTimeout("print_express(" + box_id + ",'weipinhuijit_box_print','auto_print_jit_box')", 100);
                    }                
                    if ($("#auto_print_general_box").attr("checked")) {
                        setTimeout("print_express(" + box_id + ",'general_box_print','auto_print_general_box')", 100);
                    }                             
            }

            //判断是否选中打印汇总单
            function auto_aggr_box() {
                if ($("#auto_print_aggr_box").attr("checked")) {
                    setTimeout("print_aggr_box()",200);
                }
            }
            function print_box(box_id,printer) {               
                if(new_clodop_print == 1){
                        var u = '?app_act=tprint/tprint/do_print&print_templates_code=b2b_box&record_ids=' + box_id + '&clodop_printer=' + $.cookie('auto_print_box_printer') + '&new_clodop_print=' + new_clodop_print + '&print_type=scan_box';
                        $("#print_iframe").attr('src', u);
                }else{
                 //var url = "?app_act=sys/flash_print/do_print&template_id=20&model=b2b/BoxRecordModel&typ=default&record_ids="+box_id;                   
                var url = "?app_act=tprint/tprint/do_print&print_templates_code=b2b_box&&record_ids=" + box_id + '&print_type=scan_box';
                //console.log(url);
                //$("#print_iframe")[0].src = url;
                $('#print_iframe').attr('src', url);
                //window.open(u);
                }
            }
            function printer_select(code){
                new ESUI.PopWindow("?app_act=common/record_scan_box/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&print_templates_code="+code, {
                        title: "请完善打印机选择",
                        width: 500,
                        height: 280,
                        onBeforeClosed: function () {
                        },
                        onClosed: function () {
                        }
                    }).show()
            }
            function ys_box_task() {
                //ys_box_record_and_print(0);
                var url = "?app_act=common/record_scan_box/ys_box_task";
                var dj_type = g_dj_info['dj_type'];
                var relation_code = g_dj_info['relation_code'];
                var task_code = g_dj_info['task_code'];
                var param = {app_fmt: 'json', dj_type: dj_type, record_code: g_dj_info['record_code'], task_code: g_dj_info['task_code'], box_code: g_dj_info['box_code']};
                $.post(url, param,
                        function (json_data) {
                            try {
                                var result = eval('(' + json_data + ')');
                            } catch (e) {
                            }
                            if (result == undefined || result.status == undefined) {
                                display_err_tips('装箱任务验收出错： ' + json_data);
                                return;
                            }
                            if (result.status < 0) {
                                display_err_tips('装箱任务验收出错： ' + result.message);
                            } else {
                                auto_print(g_dj_info['box_id']);
                                auto_aggr_box();
                                $(".scan_div").add(".mx_tbl").css('display', 'none');
                                $("#success_tips").css('display', 'block');
                            }
                        });
            }

            function clean_scan() {
                var url = "?app_act=common/record_scan_box/clean_scan";
                var dj_type = g_dj_info['dj_type'];
                var relation_code = g_dj_info['relation_code'];
                var task_code = g_dj_info['task_code'];
                var param = {app_fmt: 'json', dj_type: dj_type, record_code: g_dj_info['record_code'], task_code: g_dj_info['task_code'], box_code: g_dj_info['box_code']};
                $.post(url, param,
                        function (json_data) {
                            try {
                                var result = eval('(' + json_data + ')');
                            } catch (e) {
                            }
                            if (result == undefined || result.status == undefined) {
                                display_err_tips('清除扫描出错： ' + json_data);
                                return;
                            }
                            if (result.status < 0) {
                                display_err_tips('清除扫描出错： ' + result.message);
                            } else {
                                location.reload(true);
                            }
                        });
            }

            function cancel_box() {
                var url = "?app_act=common/record_scan_box/cancel_box_record";
                var dj_type = g_dj_info['dj_type'];
                var task_code = g_dj_info['task_code'];
                var param = {app_fmt: 'json', dj_type: dj_type, task_code: g_dj_info['task_code'], box_code: g_dj_info['box_code']};
                $.post(url, param,
                    function (json_data) {
                        try {
                            var result = eval('(' + json_data + ')');
                        } catch (e) {
                        }
                        if (result == undefined || result.status == undefined) {
                            display_err_tips('取消装箱出错： ' + json_data);
                            return;
                        }
                        if (result.status < 0) {
                            display_err_tips('取消装箱出错： ' + result.message);
                        } else {
                            window.close();
                        }
                    });
            }

            function update_goods_num(sku) {
                  var sku_id = sku.replace('.', "\\.");//处理带点特殊处理
                var num = $("#sku_num_" + sku_id).text();
                $("#sku_num_" + sku_id).parent().html("<input type='text' onblur=update_goods_scan_num('" + sku + "') id='sku_num_" + sku + "' class='update_num'>");
                $("#sku_num_" + sku_id).focus();
                $("#sku_num_" + sku_id).val(num);
                $("#sku_num_" + sku_id).keypress(function (event) {
                    if (event.keyCode == "13") {
                        update_goods_scan_num(sku);
                    }
                })
            }
            //修改商品扫描数量
            function update_goods_scan_num(sku) {
                 var sku_id = sku.replace('.', "\\.");//处理带点特殊处理
                var record_code = '<?php echo $response['record_code']; ?>';
                var dj_type = g_dj_info['dj_type'];
                var scan_num = $("#sku_num_" + sku_id).val();
                if (scan_num == '') {
                    display_err_tips('扫描数量不能为空');
                    return false;
                }
                var url = '?app_act=common/record_scan_box/update_goods_scan_num';
                var params = {app_fmt: 'json', record_code: record_code, sku: sku, task_code: g_dj_info['task_code'], box_code: g_dj_info['box_code'], dj_type: dj_type, scan_num: scan_num};
                $.post(url, params, function (data) {
                    if (data.status != 1) {
                        display_err_tips(data.message);
                    } else {
                        $("#sku_num_" + sku_id).parent().html("<span onclick=update_goods_num('" + sku + "') class='sku_num' id='sku_num_" + sku + "'>" + scan_num + "</span>");
                        g_mx_info[sku] = parseInt(data.data);
                        if (g_must_scan_mx[sku]) {
                            g_must_scan_mx[sku]['num'] = parseInt(data.data);
                        }
//                        display_err_tips(data.message);
                        update_cur_scan_sl();
                        update_total_scan_sl();
                    }
                }, 'json')
            }

            //打印汇总单
            function print_aggr_box() {
             var id = "print_aggr_box";
                 if(new_clodop_print == 1){
                        var url = "?app_act=tprint/tprint/do_print&print_templates_code=aggr_box&&record_code=" + g_dj_info['record_code'] + '&new_clodop_print=' + new_clodop_print + '&clodop_printer=' + $.cookie('auto_print_aggr_box_printer') + '&print_type=scan_box';
                        var iframe = $('<iframe id="' + id + ' width="0" height="0"></iframe>').appendTo('body');
                        iframe.attr('src', url);                         
                }else{
                var url = "?app_act=tprint/tprint/do_print&print_templates_code=aggr_box&&record_code=" + g_dj_info['record_code'] + '&print_type=scan_box';
                var iframe = $('<iframe id="' + id + ' width="0" height="0" style="border:0px red solid;" scrolling="no"></iframe>').appendTo('body');
                iframe.attr('src', url);
                }
            }

            function messageErr() {
                var msgUrl = "?app_act=base/error_confirm_code/do_list";
                openPage(window.btoa(msgUrl), msgUrl, "错误确认码")
            }

            function messageBox(m) {
                BUI.use('bui/overlay', function (Overlay) {
                    var msg = '<div style="text-align: center"><h2>' + m + '</h2><p class="auxiliary-text" style="padding-top:10px;"><input type="text" class="msg_code" value="" style="width:240px;" placeholder="请扫描错误确认码，如CONFIRM，以确认此错误"></p><p style="padding-top:10px;">提示：如没有错误确认码，请到<a href="javascript:openPage(window.btoa('+"'?app_act=base/error_confirm_code/do_list'"+'),'+"'?app_act=base/error_confirm_code/do_list'"+','+"'错误确认码'"+')">错误确认码</a>中打印以供扫描</p></div>';

                    var dialog = new Overlay.Dialog({
                        title: '扫描错误',
                        width: 500,
                        height: 210,
                        bodyContent: msg, //配置DOM容器的编号
                        buttons: [{
                            text: '确定',
                            elCls: 'button button-primary',
                            handler: function () {
                                //do some thing
                                this.close();
                            }
                        }
                        ]
                    });

                    dialog.show();

                    play_sound("error");

                    dialog.on("closed", function (event) {
                        $("#scan_barcode").val("");
                        $("#scan_barcode").focus();
                        dialog.close();
                    })

                    $(".msg_code").val("");
                    $(".msg_code").focus();
                    $(".msg_code").keyup(function (event) {
                        if (event.keyCode == 13) {
                            var len = $(this).val().length;
                            if ($(this).val() == 'CONFIRM' || len == 0) {
                                $("#scan_barcode").val("");
                                $("#scan_barcode").focus();
                                dialog.close();
                            }
                        }
                    });
                });
            }



        </script>
        <bgsound loop="false" autostart="false" id="bgsound_ie" src="" />
        <audio controls="controls" id="bgsound_others" style="display:none;" src=""></audio>
    </body>
</html>