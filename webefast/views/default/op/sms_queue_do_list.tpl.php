<style>
    #send_time_start,#send_time_end{width:100px;}
    .bui-dialog .bui-stdmod-footer{text-align: center;}
    .bui-stdmod-body .td_prev{width:80px;text-align:right;display: block;}
</style>
<?php render_control('PageHead', 'head1',
    array('title' => '短信任务列表',
        'links' => array(),
        'ref_table' => 'table'
    ));

?>
<?php
$buttons = array(
    array('label' => '查询', 'id' => 'btn-search', 'type' => 'submit'),
);
if ($response['priv']['op/sms_queue/export_list']) {
    $buttons[] = array('label' => '导出', 'id' => 'exprot_list');
}
render_control('SearchForm', 'searchForm', array(
    'buttons' => $buttons,
    'fields' => array(
       array('label' => '任务ID',
            'title' => '',
            'type' => 'input',
            'id' => 'task_id',
        ),
       array('label' => '会员',
            'title' => '',
            'type' => 'input',
            'id' => 'buyer_name',
        ),
       array('label' => '手机号码',
            'title' => '',
            'type' => 'input',
            'id' => 'tel',
        ),
       array('label' => '发送内容',
            'title' => '',
            'type' => 'input',
            'id' => 'sms_info',
        ),
        array(
            'label' => '计划发送时间',
            'type' => 'group',
            'field' => 'daterange2',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'plan_send_time_start'),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'plan_send_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '任务状态',
            'title' => '',
            'type' => 'select',
            'id' => 'status',
        	'data'=> $response['select']['sms_status'],
        ),
       array(
            'label' => '短信类型',
             'title' => '',
            'type' => 'select',
            'id' => 'sms_type',
        	'data'=> $response['select']['sms_type'],
        ),
    )
));

?>
<div style="padding: 3px;">
    <table style="width: 85%">
        <tr>
            <td style="text-align: right; width: 150px;">总任务数：</td>
            <td><span id="record_num_all"></span></td>

            <td style="text-align: right;">成功任务数：</td>
            <td><span id="record_num_success"></span></td>

            <td style="text-align: right;">失败任务数：</td>
            <td><span id="return_num_fail"></span></td>

            <td style="text-align: right;">消费短信条数：</td>
            <td><span id="record_num_used"></span></td>
        </tr>
    </table>
</div>
<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(// 默认选中active=true的页签
        array('title' => '全部', 'active' => true, 'id' => 'tabs_all'),
        array('title' => '未发送', 'active' => false, 'id' => 'tabs_unsend'),
        array('title' => '发送中', 'active' => false, 'id' => 'tabs_is_sending'),
        array('title' => '发送成功', 'active' => false, 'id' => 'tabs_send_success'),
        array('title' => '发送失败', 'active' => false, 'id' => 'tabs_send_fail'),
        array('title' => '终止', 'active' => false, 'id' => 'tabs_over'),
    ),
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<?php
$operate_buttons = array();
if ($response['priv']['op/sms_queue/do_preview']){
    $operate_buttons[] = array('id' => 'preview_sms', 'title' => '查看', 'callback' => 'do_preview');
}
if ($response['priv']['op/sms_queue/opt_send_sms']){
    $operate_buttons[] = array('id' => 'send_sms', 'title' => '发送','callback' => 'send_sms', 'show_cond' => 'obj.status == 0');
    $operate_buttons[] = array('id' => 'resend_sms', 'title' => '重新发送','callback' => 'send_sms', 'show_cond' => 'obj.status == 2');
}
if ($response['priv']['op/sms_queue/opt_over_sms']){
    $operate_buttons[] = array('id' => 'over_sms', 'title' => '终止','callback' => 'over_sms', 'show_cond' => 'obj.status == 2 || obj.status == 0');
}
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array('type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '150',
                'align' => '',
                'buttons' => $operate_buttons,
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '任务ID',
                'field' => 'id',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '短信类型',
                'field' => 'sms_type_name',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '会员',
                'field' => 'buyer_name',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '手机号码',
                'field' => 'tel',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '短信内容',
                'field' => 'sms_info_sub',
                'width' => '400',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '使用短信数',
                'field' => 'sms_num',
                'width' => '80',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '发送时间段',
                'field' => 'send_time_range',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '计划发送时间',
                'field' => 'plan_send_time',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '发送时间',
                'field' => 'send_time',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '任务状态',
                'field' => 'status_name',
                'width' => '100',
                'align' => '',
            ),
        )
    ),
    'export' => array('id' => 'exprot_list', 'conf' => 'sms_queue_list', 'name' => '短信任务列表', 'export_type' => 'file'),
    'dataset' => 'op/SmsQueueModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'ColumnResize' => true,
    'CheckSelection' => true,
));

?>
<div id="TabPage1Contents">
    <!--全部-->
    <div></div>
    <!--待发送-->
    <div>
        <ul id="ToolBar2" class="toolbar frontool">
            <?php if ($response['priv']['op/sms_queue/opt_send_sms']){ ?>
                <li class="li_btns"><button class="button button-primary btn_batch_send_sms">批量发送</button></li>
                <li class="li_btns"><button class="button button-primary btn_batch_send_all_sms">一键发送</button></li>
            <?php }?>
            <?php if ($response['priv']['op/sms_queue/opt_over_sms']){ ?>
                <li class="li_btns"><button class="button button-primary btn_batch_over_sms">批量终止</button></li>
            <?php }?>
            <div class="front_close">&lt;</div>
        </ul>
    </div>
    <!--发送中-->
    <div></div>
    <!--发送成功-->
    <div></div>
    <!--发送失败-->
    <div>
        <ul id="ToolBar2" class="toolbar frontool">
            <?php if ($response['priv']['op/sms_queue/opt_send_sms']){ ?>
                <li class="li_btns"><button class="button button-primary btn_batch_send_sms">批量发送</button></li>
                <li class="li_btns"><button class="button button-primary btn_batch_send_all_sms">一键发送</button></li>
            <?php }?>
            <?php if ($response['priv']['op/sms_queue/opt_over_sms']){ ?>
                <li class="li_btns"><button class="button button-primary btn_batch_over_sms">批量终止</button></li>
            <?php }?>
            <div class="front_close">&lt;</div>
        </ul>
    </div>
    <!--终止-->
    <div></div>
</div>
<?php include_once (get_tpl_path('common/process_batch_task')); ?>
<script type="text/javascript">
    $(function () {
        //TAB选项卡
        $("#TabPage1 a").click(function () {
            tableStore.load();
        });
        tableStore.on('beforeload', function (e) {
            e.params.list_tab = $("#TabPage1").find(".active").find("a").attr("id");//页签参数
            tableStore.set("params", e.params);
            get_statistical_data();
        });
        //获取统计数据
        searchFormFormListeners['beforesubmit'].push(function (ev) {
            get_statistical_data();
        });
        get_statistical_data();//默认加载
        //底部操作按钮
        tools();
        $('.btn_batch_send_sms').click(btn_batch_send_sms);//批量发送
        $('.btn_batch_send_all_sms').click(btn_batch_send_all_sms);//一键发送
        $('.btn_batch_over_sms').click(btn_batch_over_sms);//批量终止
    });
    //底部操作按钮
    function tools(){
        $(".frontool").css({left:'0px'});
        $(".front_close").click(function(){
            if($(this).html()=="&lt;"){
                $(".frontool").animate({left:'-100%'},1000);
                $(this).html(">");
            $(this).addClass("close_02").animate({right:'-10px'},1000);
            }else{
                $(".frontool").animate({left:'0px'},1);
                $(this).html("<");
        $(this).removeClass("close_02").animate({right:'0'},1000);
            }
        });
    }
    //获取统计数据
    function get_statistical_data() {
        var obj = searchFormForm.serializeToObject();
        $.post("?app_act=op/sms_queue/get_statistical_data", obj, function (ret) {
            $("#record_num_all").html(ret.data.record_num_all);
            $("#record_num_success").html(ret.data.record_num_success);
            $("#return_num_fail").html(ret.data.return_num_fail);
            $("#record_num_used").html(ret.data.record_num_used);
        }, "json");
    }
    //查看
    function do_preview(_index, row){
        var html = '<table  style="margin:10px;height:228px;overflow-y:scroll;display: block;">';
        html += '<tr><td class="td_prev">会员：&nbsp;</td><td>'+row.buyer_name+'</td></tr>';
        html += '<tr><td class="td_prev">手机号：&nbsp;</td><td>'+row.tel+'</td></tr>';
        html += '<tr><td class="td_prev">内容：&nbsp;</td><td>'+row.sms_info+'</td></tr>';
        html += '<tr><td class="td_prev">消费点数：&nbsp;</td><td>'+row.sms_num+'</td></tr>';
        html += '<tr><td class="td_prev">发送状态：&nbsp;</td><td>'+row.status_name+'</td></tr>';
        html += '</table>';
        BUI.use('bui/overlay',function(Overlay){
            var dialog = new Overlay.Dialog({
                title:'查看',
                width:500,
                height:350,
                buttons:[
                  {
                    text:'确认',
                    elCls : 'button button-primary',
                    handler : function(){
                      //do some thing
                      this.close();
                    }
                  }
                ],
                bodyContent:html
              });
            dialog.show();
        });
    }
    //发送
    function send_sms(_index, row){
        $.post("?app_act=op/sms_queue/send_sms", {'id':row.id}, function (ret) {
            if (ret.status == 1) {
                BUI.Message.Alert(ret.message, 'info');
                tableStore.load();
            } else {
                BUI.Message.Alert(ret.message, 'error');
            }
        }, "json");
    }
    //批量发送
    function btn_batch_send_sms() {
        get_checked($(this), function (ids) {
            var params = [];
            $.each(ids, function (_key,_code) {
                var p = {};
                p.id = _code;
                params.push(p);
            });
            var act = 'op/sms_queue/send_sms';
            process_batch_task(act, '批量发送', params, 'id', 0, 'btn_batch_send_sms');
        });
    }
    //一键发送
    function btn_batch_send_all_sms() {
        //获取所有记录id
        var obj = searchFormForm.serializeToObject();
        obj.list_tab = $("#TabPage1").find(".active").find("a").attr("id");//页签参数
        $.post("?app_act=op/sms_queue/get_all_sms_id", obj, function (ret) {
            if (ret.status == 1) {
                var params = [];
                $.each(ret.data, function (_key,_code) {
                    var p = {};
                    p.id = _code;
                    params.push(p);
                });

                var act = 'op/sms_queue/send_sms';
                process_batch_task(act, '一键发送', params, 'id', 0);
            } else {
                BUI.Message.Alert(ret.message, 'error');
            }
        }, "json");
        
    }
    //终止
    function over_sms(_index, row){
        $.post("?app_act=op/sms_queue/over_sms", {'id':row.id}, function (ret) {
            if (ret.status == 1) {
                BUI.Message.Alert(ret.message, 'info');
                tableStore.load();
            } else {
                BUI.Message.Alert(ret.message, 'error');
            }
        }, "json");
    }
    //批量终止
    function btn_batch_over_sms() {
        get_checked($(this), function (ids) {
            var params = [];
            $.each(ids, function (_key,_code) {
                var p = {};
                p.id = _code;
                params.push(p);
            });
            var act = 'op/sms_queue/over_sms';
            process_batch_task(act, '批量终止', params, 'id', 0);
        });
    }
    //读取已选中项
    function get_checked(obj, func, type) {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择记录", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.id);
        }
        func.apply(null, [ids]);
    }
</script>