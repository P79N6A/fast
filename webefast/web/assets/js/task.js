function user_task(param, type,callback) {
    _this = this;
    _this.param = param;
    _this.type = type;
    _this.task_id;
    _this.time = 0;
    _this.callback = callback;
    this.set_task = function() {
        var url = "?app_act=sys/user_task/create_task&app_fmt=json&type="+ _this.type;
        $.post(url, _this.param, function(ret) {
            if (ret.status === -1) {
                BUI.Message.Alert(ret.message, 'info');
            } else if (ret.status === -2) {
                BUI.Message.Confirm(ret.message + ',是否前台等待', function() {
                    _this.task_id = ret.data.task_id;
                    _this.show_task_box(ret.data.title);
                }, 'question');

            } else {
                    _this.task_id = ret.data.task_id;
                    _this.show_task_box(ret.data.title);
            }
        }, 'json');
            _this.time = Date.parse(new Date());
    };
    this.show_task_box = function(title) {
        var message="等待任务执行结果...";
        var content = '<div id="task_message" style="height:300px;overflow-y:auto;">' + message + '<br ></div>';
        var dialog;
        BUI.use('bui/overlay', function(Overlay) {
             dialog = new Overlay.Dialog({
                title: title,
                width: 500,
                height: 400,
                mask: true,
                buttons: [
                    {
                        text: '关闭',
                        elCls: 'button',
                        handler: function() {
                            this.close();
                        
                        }
                    }
                ],
                bodyContent: content
            });
            dialog.show();
        });
        
         dialog.on('closed',function(){
             if( typeof (_this.callback)==='undefined'){
                 location.reload();
             }else{
                 _this.callback();
             }
             dialog.remove();
            $('#task_message').remove();
        });
  
        _this.check_task();
    };
        this.check_task = function() {
                var url = "?app_act=sys/user_task/get_status&app_fmt=json";
               var param = {};
                param.task_id = _this.task_id;
                param.type = _this.type;
                param.time = _this.time;
                $.post(url,param, function(ret) {
                    if (ret.status === 1) {
                            setTimeout(function(){_this.check_task();},5000);
                    } else {
                         $('#task_message').append(ret.data);
                    }
                }, 'json');   
        };
         this.set_task();
}