
<?php echo_print_plugin() ?>
<?php if($request['new_clodop_print'] == 1){echo "<script src='http://127.0.0.1:8000/CLodopfuncs.js?proper=1'></script>";}?>
<script type="text/javascript">
    var wave_print = 0;
    var new_clodop_print = '<?php echo $request['new_clodop_print'];?>';
    var print_type = '<?php echo empty($request['print_type']) ? '' : $request['print_type']; ?>';
    function over_print() {
        parent.$('#<?php echo $request['iframe_id']; ?>').remove();
    }
    //       function ininPrinterList() {
    //        var h = "<option>无</option>"
    //        var c = LODOP.GET_PRINTER_COUNT()
    //        for(var i = 0; i < c; i++) {
    //            var n = LODOP.GET_PRINTER_NAME(i)
    //            h += "<option value='"+n+"'>"+n+"</option>"
    //        }
    //
    //        $("#printer").html(h);
    //        $("#printer").val("");
    //    }


<?php if (isset($response['message'])): ?>
        parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
        parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
        parent.parent.BUI.Message.Alert('<?php echo $response['message'] ?>', 'error');
        over_print();
<?php else: ?>
        var LODOP;
        var p_express = new print_express();
        p_express.init(<?php echo json_encode($request); ?>);
        p_express.get_data();
        function print_express() {
            _this = this;
            _this.param = {};
            _this.page = 1;
            _this.page_size = 10;
            _this.page_max = 0;
            _this.num = 0;
            _this.print_num = 0;
            if(new_clodop_print == 1){
            LODOP.SET_LICENSES("上海百胜软件","452547275711905623562384719084","","");
            _this.printer = '<?php echo empty($request['clodop_printer']) ? '' : $request['clodop_printer']; ?>';
        }else{
            _this.printer = "<?php echo (empty($response['tpl']['pt']['printer'])) ? '' : $response['tpl']['pt']['printer']; ?>";
        }
            this.init = function (param) {
                _this.param = param;
                _this.param.page_size = _this.page_size;
                _this.param.page = _this.page;
                if(new_clodop_print == 1){
                    return true;
                } else{
                    this.set_lodop();
                }
            }
            this.set_page = function () {
                _this.page++;
                _this.param.page = _this.page;
            }
            this.get_data = function () {
                $.post('?app_act=b2b/box_record/get_print_express_data&app_fmt=json', _this.param, function (result) {
                    if (result.status != 1) {
                        parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
                        parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask"); //打印结束 或异常
                        parent.parent.BUI.Message.Alert('获取打印数据异常', 'error');
                        over_print();
                    } else {
                        _this.print_data(result.data);
                    }
                }, 'json');
            }
            this.print_data = function (data) {
                if (_this.page == 1) {
                    _this.page_max = data.filter.page_count;
                    _this.num = data.filter.record_count;
                }
                //模版初始化
                var timestamp = new Date().getTime();
                LODOP.PRINT_INIT('打印箱唛' + timestamp);
                LODOP.SET_PRINT_PAGESIZE(0, 100, 100, "");
    <?php
    if (isset($response['tpl']['pt']))
        echo $response['tpl']['pt']['top'];
    else if (isset($response['tpl']['df']))
        echo $response['tpl']['df']['top'];
    else if (isset($response['tpl']['rm']))
        echo $response['tpl']['rm']['top'];
    ?>
                for (var k in data.data) {
                    LODOP.NEWPAGEA();
                    print_status = this.set_print_data(data.data[k]);
                    if (print_status != true) {
                        parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
                        parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
                        parent.parent.BUI.Message.Alert('打印终止，打印模版异常', 'error');
                        over_print();
                        return false;
                    }
                    _this.print_num++;

                }
                LODOP.SET_PRINTER_INDEX(_this.printer);
                LODOP.PRINT();
                //LODOP.PRINT_DESIGN();
                if (data.filter.page != this.page_max) {
                    this.set_page();
                    setTimeout(function () {
                        _this.get_data();
                    }, 1000);//延迟1秒
                } else {
                    if (_this.print_num != _this.num) {
                        parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
                        parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");                         
                        parent.parent.BUI.Message.Alert('打印结束计划打印：' + _this.num + ',实际打印' + _this.print_num,
                                function () {
                                    parent.location.reload();
                                },
                                'error');
                        parent.location.reload();
                    } else {
                        parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
                        parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
                        if(print_type != 'scan_box'){
                                parent.parent.BUI.Message.Alert('打印完成', function () {
                                    parent.parent.$(".bui-ext-mask").next('div').remove();
                                    if (wave_print == '1') {
                                        parent.location.reload();
                                    }
                                }, 'info');                               
                            }

                    }
                }
            }
            this.set_print_data = function (c) {
                var print_status = false;
                try {
    <?php if (isset($response['tpl']['pt'])): ?>
                        if (c.pay_type != 'cod') { //pt
        <?php echo $response['tpl']['pt']['body']; ?>
                            print_status = true;
                        }
    <?php endif; ?>
    <?php if (isset($response['tpl']['df'])): ?>
                        if (c.pay_type == 'cod') { //df
        <?php echo $response['tpl']['df']['body']; ?>
                            print_status = true;
                        }
    <?php endif; ?>
    <?php if (isset($response['tpl']['rm'])): ?>
                        // rm
        <?php echo $response['tpl']['rm']['body']; ?>
                        print_status = true;
    <?php endif; ?>
                } catch (e) {
                    print_status = false;
                    //alert(e.name+": "+ e.lineNumber +" "+ e.message);
                }
                return print_status;
            }
            this.set_lodop = function () {
                LODOP = getLodop();
                var printer_count = LODOP.GET_PRINTER_COUNT();
                if (printer_count < 1) {
                    parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
                    parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");                    
                    parent.parent.BUI.Message.Alert('该系统未安装打印设备,请添加相应的打印设备', 'error');
                    return;
                }
                //选择打印机
                var check_printer = 0;
                if (_this.printer != '') {
                    var c = LODOP.GET_PRINTER_COUNT();
                    for (var i = 0; i < c; i++) {
                        if (LODOP.GET_PRINTER_NAME(i) == _this.printer) {
                            check_printer = 1;
                            break;
                        }
                    }
                }
                if (_this.printer == '' || check_printer == 0) {
                    LODOP.SELECT_PRINTER();
                    _this.printer = LODOP.GET_VALUE('PRINTSETUP_PRINTER_NAME', 1);//当前选择的打印机名称
                }
            }
        }
<?php endif; ?>
</script>