<script type="text/javascript">
    function process_batch_task(act, task_name, task_param, task_param_id, submit_type, btn_id, task_tips) {
        if (btn_id != undefined) {
            $("#" + btn_id).attr('disabled', 'disabled');
        }

        $("body").data("task_param", task_param);

        var tips_msg = '是否执行' + task_name + '?';
        if (task_tips != undefined) {
            tips_msg += task_tips;
        }
        BUI.Message.Show({
            title: '批量操作',
            msg: tips_msg,
            icon: 'question',
            buttons: [
                {
                    text: '是',
                    elCls: 'button button-primary',
                    handler: function () {
                        $("#task_msg").remove();
                        show_process_batch_task_plan(task_name, '<div id="task_msg" style="margin:35px 20px;height:250px;overflow-y:scroll;"><div>处理中，请稍等......</div></div>');
                        process_batch_task_act(act, task_param_id, submit_type, btn_id);
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

    function process_batch_task_act(act, task_param_id, submit_type, btn_id) {
        var task_param = $("body").data("task_param");
        if (task_param == '') {
            if (btn_id != undefined) {
                $("#" + btn_id).removeAttr('disabled');
            }
            $("#task_msg").append("<div style='color:blue'>批量任务执行完成。</div>");
            return false;
        }

        var curr_param;
        if (submit_type == 1) {
            curr_param = task_param;
        } else {
            curr_param = task_param.pop();
            $("body").data("task_param", task_param);
        }

        $.post("?app_act=" + act, {params: curr_param, app_fmt: 'json'}, function (ret) {
            var code = curr_param[task_param_id];
            var ret = eval('(' + ret + ')');
            if (ret.status < 0) {
                $("#task_msg").append("<div style='color:red'>" + code + '：' + ret.message + "</div>");
            } else {
                $("#task_msg").append("<div style='color:#999'>" + code + '：' + ret.message + "</div>");
            }

            if (submit_type != 1) {
                process_batch_task_act(act, task_param_id, submit_type, btn_id);
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
</script>

