
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Document</title>
        <style>
            .display_style{display:none}
        </style>
        <?php echo load_js('jquery.cookie.js'); ?>
        <script>
            function skip_print() {
                parent.$('#<?php echo $request['iframe_id']; ?>').remove();
            }

            var cnpc;
            var wave_print = "<?php echo $response['wave_print']; ?>";//系统参数，打印之后是否刷新页面
            $(document).ready(function () {
<?php if ($resopnse['status'] != 1 && isset($response['message']) && !empty($response['message'])) { ?>
                parent.parent.BUI.Message.Alert('<?php echo $response['message'] ?>', 'error');
                skip_print();
<?php } else { ?>
                cnpc = new CNPrinterController();
                cnpc.doConnect();
                cnpc.getPrinters();
<?php } ?>
            });
            //菜鸟云打印
            function CNPrinterController() {
                var _socket;
                var _printer_address = '127.0.0.1:13528';
                this.deliver_record_ids_arr = '<?php echo $request['deliver_record_ids'] ?>';
                this.waybillNO = new Array();
                this.waybillNOSlice = new Array();
                this.billlength = 0;
                this.fail_num = 0;
                this.successNum = 0;
                this.failNum = 0;
                this.time = 0;
                this.requestID = '';
                var print_ty = <?php echo "'". $request['print_ty'] ."'"; ?>; //后置打单传递HZ
                this.doConnect = function () {
                    this._socket = new WebSocket('ws://' + _printer_address);
                    this._socket.onopen = function (event) {};
                    // 监听消息
                    this._socket.onmessage = function (event) {
                        var data = JSON.parse(event.data);
                        //获取打印机
                        if ("getPrinters" == data.cmd) {
                            var printer_info = data.printers;                          
                            var print_express_name = <?php echo "'".$request['print_express_name']."'" ?>; //现在打印的快递名称                                                      
                            default_printer = $.cookie(print_express_name); //上次打印的快递名称                           
                            var printer_name = Array();
                            for (var i = 0; i < printer_info.length; i++) {
                                if (printer_info[i].status === 'enable') {
                                    printer_name[i] = printer_info[i].name;
                                }
                            }
                            if ( print_ty == '' || default_printer == undefined ) {
                                new ESUI.PopWindow("?app_act=oms/deliver_record/choose_printer&printer_list=" + printer_name.join(",") + '&print_express_name=' + print_express_name  , {
                                    title: "打印配置",
                                    width: 500,
                                    height: 220,
                                    onBeforeClosed: function () {
                                    },
                                    onClosed: function () {
					if(typeof(this._socket)=='undefined'){
                                            //解决this._socket 意外断掉问题
                                            cnpc.doConnect();
                                            setTimeout(function(){cnpc.doPrint();},100);
					}else{
                                            cnpc.doPrint();
					}
        
                                    }
                                }).show();
                            } else {
                                cnpc.doPrint();
                            }
                        }
                        //打印操作
                        if ("print" == data.cmd) {
                            if (cnpc.time == cnpc.waybillNO.length) {
                                cnpc.getPrintersResult();
                            }
                        }
                        //获取打印结果状态 方法被取消
//                        if ("getDocumentStatus" == data.cmd && print_ty != 'HZ') {
//                            var result = JSON.stringify(data);
//                            var params = {result: result, fail_num: cnpc.fail_num};
//                            $.ajax({
//                                type: "POST",
//                                dataType: 'json',
//                                async: false,
//                                url: "?app_act=oms/deliver_record/update_cloud_print_express_status",
//                                data: params,
//                                success: function (result) {
//                                    cnpc.printResult(result.data);
//                                }
//                            });
//                        }
                    //获取打印结果状态
                        if ("notifyPrintResult" == data.cmd && print_ty != 'HZ'&&this.requestID !=data.requestID) {
                      
                            this.requestID = data.requestID;
                            var result = JSON.stringify(data);
                            var params = {result: result, fail_num: cnpc.fail_num};
                            $.ajax({
                                type: "POST",
                                dataType: 'json',
                                async: false,
                                url: "?app_act=oms/deliver_record/update_cloud_print_express_status",
                                data: params,
                                success: function (result) {
                                    cnpc.printResult(result.data);
                                }
                            });
                        }
                    };

                    // 监听Socket的关闭
                    this._socket.onclose = function (event) {
                        //BUI.Message.Alert('与打印组件的连接已断开', 'error');
                    };
                    this._socket.onerror = function (event) {
                        parent.parent.BUI.Message.Alert('无法连接到打印组件', 'error');
                    };
                };
                
                this.printResult = function (data){
					this.successNum+=data.success;
					this.failNum+=data.fail;
                    if(parseInt(this.successNum) + parseInt(this.failNum) === this.waybillNO.length) {
                        var msg = '打印完成，打印成功' + this.successNum + '单，打印失败' + this.failNum + '单';
                        parent.parent.BUI.Message.Alert(msg, 'success');
                        //系统参数，打印之后是否刷新页面
                       if(wave_print == '1'){
                          
                            parent.location.reload();
                       }
                    }
                };
                
                /**
                 * 打印电子面单
                 * waybillArray 要打印的电子面单的数组
                 */
                this.doPrint = function () {
                    parent.parent.BUI.use('bui/overlay', function (Overlay) {
                        var dialog = new Overlay.Dialog({
                            title: '快递单打印',
                            width: 300,
                            height: 130,
                            mask: true,
                            buttons: [
                                {
                                    text: '',
                                    elCls: 'bui-grid-cascade-collapse',
                                    handler: function () {
                                        this.close();
                                    }
                                }
                            ],
                            bodyContent: '获取打印数据中，请稍后...'
                        });
                        dialog.show();
                    });
                    var deliver_record_ids = new Array();
                    deliver_record_ids = this.deliver_record_ids_arr.split(",");
                    var ReturnData = this.getWaybillJson(deliver_record_ids);
                    if(ReturnData.success_num == 0 && ReturnData.fail_num != 0){
                         parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
                         parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
                         parent.parent.BUI.Message.Alert('请先获取物流单号和物流数据', 'error');
                         return;
                    }
                    var TotalbillData = ReturnData.print_data;
                    this.waybillNO = ReturnData.express_no;
                    this.waybillNOSlice = this.chunk(this.waybillNO, 10);
                    this.fail_num = ReturnData.fail_num;
                    for (var i = 0; i < TotalbillData.length; i++) {
                        var request = this.getRequestObject("print");
                        request.task = new Object();
                        request.task.taskID = this.getUUID(8, 10);
                        request.task.preview = false;                      
                        var print_express_name = <?php echo "'".$request['print_express_name']."'" ?> //现在打印的快递名称
                        default_printer = $.cookie(print_express_name); //上次打印的快递名称 
                        var print_ty = <?php echo "'". $request['print_ty'] ."'"; ?>; //后置打单传递HZ
                        if (print_ty=='HZ') {
                            request.task.printer = default_printer;
                        }else{
                            request.task.printer = $.cookie('could_printer');
                        }
                        this.print_data(TotalbillData[i], request);
                    }
                }

                this.print_data = function (TotalbillData, request) {
                    var documents = Array();
                    var TotalbillJson = JSON.parse(TotalbillData.print_data_json);
                    for (var j = 0; j < TotalbillJson.length; j++) {
                        documents.push(TotalbillJson[j]);
                        this.time++;
                    }
                    request.task.documents = documents;
                    parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
                    parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
                    this._socket.send(JSON.stringify(request));
                }
                /**
                 * 请求打印机列表
                 * */
                this.getPrinters = function () {
                    var request = this.getRequestObject("getPrinters");
                    this.sendMSG(JSON.stringify(request));
                }

                /**
                 * 请求打印机列表结果处理 方法别取消
                 * */
                this.getPrintersResult = function () {
                    var waybill = this.waybillNOSlice[this.billlength];
                    this.billlength++;
                    if (waybill.length === 0) {
                        return;
                    }
                    return ;
//                    
//                    var request = this.getRequestObject("getDocumentStatus");
//                    request.documentIDs = waybill;
//                    this._socket.send(JSON.stringify(request));
                };

                this.getWaybillJson = function (deliver_record_id) {
                    var param = {deliver_record_id: deliver_record_id};
                    var ret;
                    $.ajax({
                        type: "POST",
                        dataType: 'json',
                        async: false,
                        url: "?app_act=oms/deliver_record/get_cloud_print_express_data",
                        data: param,
                        success: function (result) {
                            ret = result;
                        }
                    });
                    return ret;
                };

                /***
                 * 
                 * 获取请求的UUID，指定长度和进制,如 
                 * getUUID(8, 2)   //"01001010" 8 character (base=2)
                 * getUUID(8, 10) // "47473046" 8 character ID (base=10)
                 * getUUID(8, 16) // "098F4D35"。 8 character ID (base=16)
                 *   
                 */
                this.getUUID = function (len, radix) {
                    var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.split('');
                    var uuid = [],
                            i;
                    radix = radix || chars.length;
                    if (len) {
                        for (i = 0; i < len; i++)
                            uuid[i] = chars[0 | Math.random() * radix];
                    } else {
                        var r;
                        uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
                        uuid[14] = '4';
                        for (i = 0; i < 36; i++) {
                            if (!uuid[i]) {
                                r = 0 | Math.random() * 16;
                                uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
                            }
                        }
                    }
                    return uuid.join('');
                }
				
                this.chunk = function (array, size) {
                        var result = [];
                        for (var x = 0; x < Math.ceil(array.length / size); x++) {
                                var start = x * size;
                                var end = start + size;
                                result.push(array.slice(start, end));
                        }
                        return result;
                }
				
                /***
                 * 构造request对象
                 */
                this.getRequestObject = function (cmd) {
                    var request = new Object();
                    request.requestID = this.getUUID(8, 16);
                    request.version = "1.0";
                    request.cmd = cmd;
                    return request;
                }

                //曲线实现PHP sleep()方法
                this.sleep = function (numberMillis) {
                    var now = new Date();
                    var exitTime = now.getTime() + numberMillis;
                    while (true) {
                        now = new Date();
                        if (now.getTime() > exitTime)
                            return;
                    }
                }

                /**
                 * 用于等待websocket握手的信息发送方法
                 * */
                this.sendMSG = function (msg) {
                    cnpc.waitForSocketConnection(cnpc._socket, function () {
                        cnpc._socket.send(msg);
                    });
                }

                /**
                 * 等待websocket建立连接
                 * */
                this.waitForSocketConnection = function (socket, callback) {
                    setTimeout(
                            function () {
                                if (socket.readyState === 1) {
                                    if (callback !== 'undefined') {
                                        callback();
                                    }
                                    return;
                                } else {
                                    cnpc.waitForSocketConnection(socket, callback);
                                }
                            }, 5);
                }
            }
        </script>
    </head>

</html>
