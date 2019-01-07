<!DOCTYPE html>
<html>
    <?php if($request['new_clodop_print'] == 1){echo "<script src='http://127.0.0.1:8000/CLodopfuncs.js?proper=1'></script>";}?>
    <head>
        <title></title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <?php include get_tpl_path('web_page_top'); ?>
        <iframe  id="content" name="content" src="" > </iframe>
            <?php echo load_js('jquery-1.8.1.min.js'); ?>
            <?php echo load_js('core.min.js'); ?>
        <script>
            var LODOP;
            var printer;
            var paper_width = '<?php echo $response['data']['paper_width']; ?>mm';
            var paper_height = '<?php echo $response['data']['paper_height']; ?>mm';
            var is_view = <?php echo!isset($request['view']) ? 0 : $request['view']; ?>;
            var new_clodop_print = <?php echo isset($request['new_clodop_print']) ? $request['new_clodop_print'] : 0;  ?>;
            if(new_clodop_print == 1){
               printer  = '<?php echo empty($request['clodop_printer']) ? '' : $request['clodop_printer']; ?>';
            } else {
               printer = '<?php echo empty($response['data']['printer']) ? '' : $response['data']['printer']; ?>';
            }
            var print_type = '<?php echo empty($request['print_type']) ? '' : $request['print_type']; ?>';
            $(function () {
                if(new_clodop_print != 1){
                    LODOP = getLodop();
                } else {
                    LODOP.SET_LICENSES("上海百胜软件","452547275711905623562384719084","","");
                }
                LODOP.PRINT_INIT("<?php echo $response['data']['print_templates_name']; ?>");
                var check = load_printer();

                if (check === false) {
                    // alert(printer);
                    return;
                }

                load_print_content();



            });
            var page_index = 0;
            var record_ids = <?php echo!empty($response['record_ids']) ? json_encode($response['record_ids']) : '[]'; ?>;
            var url = '<?php echo $response['print_url']; ?>';
            if('<?php echo $response['data']['print_templates_code']; ?>' == 'wbm_store_out_record_goods'){
                var sku = <?php echo!empty($response['sku']) ? json_encode($response['sku']) : '[]'; ?>;
            }
            function load_print_content() {
                if (page_index < record_ids.length) {
                    if (typeof record_ids[page_index] != "undefined") {
                        var new_url = url + "&record_ids=" + record_ids[page_index];
                        if('<?php echo $response['data']['print_templates_code']; ?>' == 'wbm_store_out_record_goods'){
                            new_url = new_url + "&sku=" + sku[page_index];
                        }
                        set_load(new_url);
                        page_index++;
                    } else {
                        if (is_view == 0) {
                            if(new_clodop_print == 1){
                                parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
                                parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
                            }
                            if(print_type != 'scan_box'){
                            parent.parent.BUI.Message.Alert('打印完成');                                
                            }
<?php if (isset($request['print_type'])): ?>
                                window.opener = null;
                                window.close();
<?php endif; ?>
                        }
                    }

                } else {
                    if (is_view == 0) {
                        if(new_clodop_print == 1){
                            parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
                            parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
                        }
                            if(print_type != 'scan_box'){
                            parent.parent.BUI.Message.Alert('打印完成');                                
                            }
<?php if (isset($request['print_type'])): ?>
                        <?php if ($request['print_type'] == 50): ?>
                        parent.parent.ui_closePopWindow("<?php echo $request['frm'] ?>")
                        <?php endif; ?>
                        window.opener = null;
                            window.close();
<?php endif; ?>

                    }
                }
            }





            function set_load(url) {
                var iframe = document.getElementById("content");
                $('#content').attr('src', url);
                if (iframe.attachEvent) {
                    iframe.attachEvent("onload", function () {

                        load_print(1);
                    });
                } else {
                    iframe.onload = function () {
                        load_print(1);
                    };
                }
            }

            function load_print(page) {
                // $(window.frames["iframeName"].document).find("#testId").html()


                var frame = $(window.frames["content"].document.documentElement);
                var barcode_list = frame.find('.barcode');
                var barcod_data = {};
                //读取tpl模板中设置的高度
                var tpl_height = frame.find('#tpl_height').html();
                if (tpl_height) {
                    paper_height = tpl_height;
                }
                $.each(barcode_list, function (i, barcode) {
                    var code = $(barcode).attr('title');
                    var top = $(barcode).offset().top;
                    var left = $(barcode).offset().left;
                    var width = $(barcode).width();
                    var height = $(barcode).height();
                    barcod_data[i] = {top: top.toFixed(0) + 'px', left: left.toFixed(0) + 'px', width: width + 'px', height: height + 'px', code: code};
                    //   LODOP.ADD_PRINT_BARCODE(top.toFixed(0) + 'px', left.toFixed(0) + 'px', width + 'px', height + 'px', '128A', code);
                    $(barcode).hide();
                });
                var page_size = parseInt(frame.find('#page_size').val());

                var index = 0;
                if (page_size > 0) {
                    var list = frame.find('.table tr');
                    var page_num = Math.ceil(parseInt(frame.find('#record_count').val()) / page_size);
                    while (page <= page_num) {
                        var f_page = page;

                        list.hide();
                        frame.find('.table tr').eq(0).show();
                        set_page_list(list, page_size, page);
                        LODOP.NEWPAGEA();
                            LODOP.SET_PRINT_PAGESIZE(0, paper_width, paper_height, "");
                        if (frame.find('.page').length > 0) {
                            frame.find('.page').html(page + '/' + page_num);
                        }
                        page++;
                        var html = $(frame).prop('outerHTML');
                        if ($.trim(html).length == 0) {
                            if(new_clodop_print == 1){
                                parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
                                parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
                            }
                            if(print_type != 'scan_box'){
                            parent.parent.BUI.Message.Alert('打印完成');                                
                            }
                            return;
                        }
                        LODOP.ADD_PRINT_HTM(0, 0, "100%", "100%", html);
                        if('<?php echo $response['data']['print_templates_code']; ?>' == 'wbm_store_out_record_goods'){
                        LODOP.SET_PRINT_MODE ("PRINT_PAGE_PERCENT","50%")
                        }
                        $.each(barcod_data, function (i, barcode) {
                            LODOP.ADD_PRINT_BARCODE(barcode.top, barcode.left, barcode.width, barcode.height, '128A', barcode.code);
                        });

                    }
                } else {
                    LODOP.NEWPAGEA();
                    var html = $(frame).prop('outerHTML');
                    if ($.trim(html).length == 0) {
                        if(new_clodop_print == 1){
                            parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
                            parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
                        }
                            if(print_type != 'scan_box'){
                            parent.parent.BUI.Message.Alert('打印完成');                                
                            }
                        parent.parent.$(".bui-ext-mask").next('div').remove();
                        return;
                    }
                    LODOP.SET_PRINT_PAGESIZE(0, paper_width, paper_height, "");
                    LODOP.ADD_PRINT_HTM(0, 0, "100%", "100%", html);
                    if('<?php echo $response['data']['print_templates_code']; ?>' == 'wbm_store_out_record_goods'){
                        LODOP.SET_PRINT_MODE ("PRINT_PAGE_PERCENT","50%")
                    }
                    // LODOP.ADD_PRINT_HTM("2mm","2mm","206mm","293mm",html);
                    $.each(barcod_data, function (i, barcode) {

                        LODOP.ADD_PRINT_BARCODE(barcode.top, barcode.left, barcode.width, barcode.height, '128A', barcode.code);
                    });
                }
                //打印图片
                img_print();
                var percent = frame.find('#percent').html();
                // alert(typeof(percent));
                if (percent) {
                    LODOP.SET_PRINT_MODE("PRINT_PAGE_PERCENT", percent);
                }
                print_conten();

            }

            function print_conten() {
                LODOP.SET_PRINTER_INDEX(printer);
                // LODOP.SET_PRINT_MODE("PRINT_PAGE_PERCENT","Full-Page");



                //LODOP.ADD_PRINT_URL(0,0, "100%","100%","http://localhost/efast5/webefast/web/?app_act=tprint/tprint/print_template&print_templates_code=deliver_record&_umdata=4095F0CC18C08D77FBA1CF90A33095E6BAD191029EDCD9DB48A172A8A05D548EDAED9D861A6BA8FAC30EC5E75E587721A54E3BD215C4A0A4A103CB136B81CF660C89DB8EB63A09AC&_ati=3367770789049&record_ids=483");
                if (is_view == 0) {
                    LODOP.PRINT();
                } else {
                    LODOP.PREVIEW();
                }
                //LODOP.PREVIEW();
                setTimeout(function () {
                    load_print_content();
                }, 100);

            }
            function set_page_list(list, page_size, page) {

                for (var i = 1; i <= page_size; i++) {
                    var index = (page - 1) * page_size + i;
                    if (index < list.length) {
                        list.eq(index).show();
                    } else {
                        break;
                    }
                }
            }

            function img_print() {
                var frame = $(window.frames["content"].document.documentElement);
                var img_list = frame.find('.img');
                var img_data = {};

                $.each(img_list, function (i, img) {

                    var url = $(img).attr('src');
                    var top = $(img).offset().top;
                    var left = $(img).offset().left;
                    var width = $(img).width();
                    var height = $(img).height();
                    img_data[i] = {top: top.toFixed(0) + 'px', left: left.toFixed(0) + 'px', width: width + 'px', height: height + 'px', url: url};
                    $(img).hide();
                });
                $.each(img_data, function (i, img) {
                    LODOP.ADD_PRINT_IMAGE(img.top, img.left, img.width, img.height, 'URL:' + img.url);
                    LODOP.SET_PRINT_STYLEA(0, "Stretch", 2);//(可变形)扩展缩放模式
                });

            }

            function load_printer() {
                // LODOP = getLodop();
                var printer_count = LODOP.GET_PRINTER_COUNT();
                if (printer_count < 1) {
                    alert('该系统未安装打印设备,请添加相应的打印设备');
                    return false;
                }
                //选择打印机
                var check_printer = 0;
                if (printer != '') {
                    var c = LODOP.GET_PRINTER_COUNT();
                    for (var i = 0; i < c; i++) {
                        if (LODOP.GET_PRINTER_NAME(i) == printer) {
                            check_printer = 1;
                            break;
                        }
                    }
                }
                if (printer == '' || check_printer == 0) {
                    var select_i = LODOP.SELECT_PRINTER();
                    if (select_i === -1) {
                        return false;
                    }
                    printer = LODOP.GET_VALUE('PRINTSETUP_PRINTER_NAME', 1);//当前选择的打印机名称
                }
                return true;

            }

        </script>
        <?php echo_print_plugin() ?>
    </body>
</html>