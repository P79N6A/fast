<?php echo load_js('comm_util.js') ?>
<div class="demo-content">
    <div class="row" style="height: 40px;" >
        <div class="span22">
            <button class="button button-primary" id="test">自动服务测试</button>
        </div>
    </div>
        <div class="row" style="height: 40px;" >
        <div class="span22">
            <div style="color:red;"  id="msg"></div>
        </div>
    </div>
</div>
<script>
$(function(){
    var task_id = 0;
    $('#test').click(function(){
        if(task_id>0){
            alert('正在测试中');
            return ;
        }
        var url = "?app_act=sys/sys_schedule/c_task&app_fmt=json";
                var data = {};
                $.post(url,data,function(result){
                    $('#msg').html('等待测试结果，预计15秒');
                   task_id = result.data;
                   get_message();
                },'json');
    });
    
    var all_time = 0;
    function get_message(){
              var url = "?app_act=sys/sys_schedule/get_test_task&app_fmt=json";
                var data = {};
                all_time = all_time+2;
                data.task_id = task_id;
                $.post(url,data,function(result){
                    if(all_time>20){
                      $('#msg').html('任务异常');
                        task_id =0;
                        return ;
                    }
                     if(result.data==1){
                         setTimeout(function(){get_message()},2000);
                     }else if(result.data==2){
                           $('#msg').html('测试成功，任务系统正常！');
                     }else{
                           $('#msg').html('任务异常');
                        task_id =0;
                        return ;
                     }
                },'json');  
    }
    
    
});
</script>