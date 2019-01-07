<script type="text/javascript">
    // act=oms/sell_record/opt_confirm task_name=批量确认 obj_name=订单 ids_params_name = sell_record_code

    function process_batch_task(act, task_name, obj_name, ids_params_name, submit_all_ids_flag, process_batch_task_ids, task_name_tips, btn_id, task) {
        if (process_batch_task_ids == undefined) {
            var ids = new Array();
            var sell_record_codes = new Array();//订单号
            var rows = tableGrid.getSelection();//读取选中列表
            if (rows.length == 0) {
                BUI.Message.Alert("请选择" + obj_name, 'error');
                return;
            }

            for (var i in rows) {
                var row = rows[i];
                if (task == 'order_send') {
                    ids.push(row.api_order_send_id);
                    sell_record_codes[row.api_order_send_id] = row.sell_record_code;//订单号
                } else if (task == 'api_refund') {
                    if (ids_params_name === 'id') {
                        ids.push(row.id);
                        sell_record_codes[row.id] = row.refund_id;//退单号
                    } else {
                        ids.push(row.refund_id);
                    }
                } else if (task == 'wms_trade') {
                    ids.push(row.id);
                    sell_record_codes[row.id] = row.record_code;//订单号
                } else if(task == 'sync_goods_inv'){//批量库存同步
                    ids.push(row.api_goods_id);
                    sell_record_codes[row.api_goods_id] = row.goods_from_id;//订单号
                } else if (task == 'ag_sync'||task == 'ag_check') {//ag同步,审核
                    ids.push(row.refund_id);
                    sell_record_codes[row.refund_id] = row.refund_id;//退单
                } else {
                    ids.push(row.sell_record_code);
                }

            }
            $("body").data("process_batch_task_ids", ids.join(','));
        } else {
            $("body").data("process_batch_task_ids", process_batch_task_ids);
        }

        var tips_msg = '是否执行' + task_name + '?';
        if (task_name_tips != undefined) {
            tips_msg += task_name_tips;
        }
        BUI.Message.Show({
            title: '自定义提示框',
            msg: tips_msg,
            icon: 'question',
            buttons: [
                {
                    text: '是',
                    elCls: 'button button-primary',
                    handler: function () {
                        $("#process_batch_task_tips").remove();
                        show_process_batch_task_plan(task_name, '<div id="process_batch_task_tips" style="height:300px;overflow-y:scroll;"><div>处理中，请稍等......</div></div>');
                        //console.log('==11');
                        process_batch_task_act(act, ids_params_name, submit_all_ids_flag, btn_id, task, sell_record_codes);
                        this.close();
                    }
                },
                {
                    text: '否',
                    elCls: 'button',
                    handler: function () {
                        if (btn_id != undefined) {
                            $("#" + btn_id).removeAttr('disabled');
                        }
                        this.close();
                    }
                }
            ]
        });
    }

    function process_batch_task_act(act, ids_params_name, submit_all_ids_flag, btn_id, task, sell_record_codes) {
        var ids_v = $("body").data("process_batch_task_ids");
        //console.log(ids_v);
        if (ids_v == '') {//判断是否为最后一次提交
            if (btn_id != undefined) {
                $("#" + btn_id).removeAttr('disabled');
            }
            $("#process_batch_task_tips").append("<div style='color:blue'>批量任务执行完成。</div>");
            if (tableStore != undefined) {
                tableStore.load();
            }
            return;
        }

        //判断是一次性提交还是通过递归实现
        if (submit_all_ids_flag == 1) {
            var cur_id = ids_v;
        } else {
            var ids = ids_v.split(',');
            var cur_id = ids.pop();
            $("body").data("process_batch_task_ids", ids.join(','));
        }
        //组装url
        var ajax_url = "?app_fmt=json&" + act + "&" + ids_params_name + "=" + cur_id;
        //组装提示信息
        if (task == 'order_send') {
            cur_id = cur_id + "(订单号：" + sell_record_codes[cur_id] + ")";
        } else if (task == 'api_refund') {
            if (ids_params_name === 'id') {
                cur_id = cur_id + "(退单编号：" + sell_record_codes[cur_id] + ")";
            } else {
                cur_id = "退单编号：" + cur_id;
            }
        } else if (task == 'wms_trade') {
            cur_id = cur_id + "(订单号：" + sell_record_codes[cur_id] + ")";
        } else if (task == 'sync_goods_inv') {//批量库存同步
            cur_id = "(平台商品ID：" + sell_record_codes[cur_id] + ")";
        } else if (task == 'ag_sync' || task == 'ag_check') {
            cur_id = "(平台退单号：" + sell_record_codes[cur_id] + ")";
        }

        $.get(ajax_url, function (result) {
            try {
                var result_obj = eval('(' + result + ')');
            } catch (e) {
            }
            if (result_obj == undefined) {
                $("#process_batch_task_tips").append("<div style='color:red'>" + cur_id + '：' + result + "</div>");
            } else {
                if (result_obj.status == undefined) {
                    $("#process_batch_task_tips").append("<div style='color:red'>" + cur_id + '：' + result + "</div>");
                }
                if (result_obj.resp_error != undefined) {
                    $("#process_batch_task_tips").append("<div style='color:red'>" + cur_id + '：' + result_obj.resp_error.app_err_msg + "</div>");
                }
            }
            if (result_obj != undefined) {
                if (submit_all_ids_flag != 1) {
                    if (result_obj.status != undefined) {
                        if (result_obj.status < 0) {
                            $("#process_batch_task_tips").append("<div style='color:red'>" + cur_id + '：' + result_obj.message + "</div>");
                            //$("#process_batch_task_tips").append("测试超长信息<br/>测试超长信息<br/>测试超长信息<br/>");
                        } else {
                            $("#process_batch_task_tips").append("<div style='color:#999'>" + cur_id + '：' + result_obj.message + "</div>");
                        }
                    }
                } else {
                    $("#process_batch_task_tips").append("<div style='color:#999'>" + result_obj.message + "</div>");
                }
            }


            if (submit_all_ids_flag != 1) {
                process_batch_task_act(act, ids_params_name, submit_all_ids_flag, btn_id, task, sell_record_codes);
            } else {
                if (tableStore != undefined) {
                    tableStore.load();
                }
            }
        });
    }

    function show_process_batch_task_plan(title, content) {
        BUI.use('bui/overlay', function (Overlay) {
            var dialog = new Overlay.Dialog({
                title: title,
                width: 500,
                height: 400,
                mask: true,
                buttons: [
                    {
                        text: '关闭',
                        elCls: 'button',
                        handler: function () {
                            this.close();
                            tableStore.load();
                        }
                    }
                ],
                bodyContent: content
            });
            dialog.show();
        });
    }

    $("._sys_batch_task_btn").click(function () {
        var task_info = eval('(' + $(this).attr('task_info') + ')');
        var task_name = $(this).text();
        process_batch_task(task_info['act'], task_name, task_info['obj_name'], task_info['ids_params_name'], task_info['submit_all_ids_flag']);
    });
</script>

