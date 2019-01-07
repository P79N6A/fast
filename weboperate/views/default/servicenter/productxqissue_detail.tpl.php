<?php render_control('PageHead', 'head1',
    array('title'=>isset($app['title']) ? $app['title'] : '查看需求提单',
	'links'=>array(
            )
));?>
<?php echo load_js('ueditor1_4_3/ueditor.config.js,ueditor1_4_3/ueditor.all.js')?>
<form  class="form-horizontal" id="form1" action="<?php echo '?app_act=servicenter/productxqissue/do_'.$app["scene"] ?>" method="post">
<!--表单区域-->
<div class="panel">
    <div class="panel-header">
        <h3>基本信息</h3>
    </div>
    <div class="panel-body">
        <?php render_control('Form', 'form1', array(
                'noform'=>true,
                'conf'=>array(
                        'fields'=>array(
                                array('title'=>'产品', 'type'=>'select', 'field'=>'xqsue_cp_id','data'=>ds_get_select('chanpin')),
                                array('title'=>'提单编号', 'type'=>'input', 'field'=>'xqsue_number','show_scene'=>'view'),
                                array('title'=>'版本号', 'type'=>'select_pop', 'field'=>'xqsue_pv_id','select'=>'products/edition','show_scene'=>'add,edit','eventtype'=>'custom'),
                                array('title'=>'版本号', 'type'=>'input', 'field'=>'xqsue_pv_id_name','show_scene'=>'view'),
                                array('title'=>'其他版本号', 'type'=>'input', 'field'=>'xqsue_other_pv', ),
                                array('title'=>'客户名称', 'type'=>'select_pop', 'field'=>'xqsue_kh_id', 'select'=>'clients/clientinfo','show_scene'=>'add,edit','eventtype'=>'custom'),
                                array('title'=>'客户名称', 'type'=>'input', 'field'=>'xqsue_kh_id_name','show_scene'=>'view','edit_scene'=>''),
                                array('title'=>'客户联系方式', 'type'=>'input', 'field'=>'xqsue_kh_contact'),
                                array('title'=>'客户联系人', 'type'=>'input', 'field'=>'xqsue_kh_phone'),
//                                array('title'=>'提单人员','type'=>'select', 'field'=>'xqsue_user','data'=>ds_get_select('users'),'show_scene'=>'view',),
                                array('title'=>'提单人员','type'=>'input', 'field'=>'xqsue_user','show_scene'=>'view',),
                                array('title'=>'提单邮箱', 'type'=>'input', 'field'=>'xqsue_email','show_scene'=>'view'),   
                                array('title'=>'提单来源', 'type'=>'select', 'field'=>'xqsue_submit_source','data'=>ds_get_select_by_field('issue_source','3')),
                                array('title'=>'提单创建时间', 'type'=>'input','field'=>'xqsue_submit_time','show_scene'=>'view'),
                                array('title'=>'产品模块', 'type'=>'select_pop', 'field'=>'xqsue_product_fun','select'=>'products/productmodule','show_scene'=>'add,edit','eventtype'=>'custom'),
                                array('title'=>'产品模块', 'type'=>'input', 'field'=>'xqsue_product_fun_name','show_scene'=>'view'),
                        ),      
                        'hidden_fields'=>array(array('field'=>'xqsue_number')), 
                ), 
                'col'=>3,
                'data'=>$response['data'],
                'rules'=>'servicenter/xqissue_add'            //有效性验证
        )); ?>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h3>需求提单详情</h3>
    </div>
<?php if($app['scene']=="add" || $app['scene']=="edit"){ ?>    
        <div class="panel-body">
            <?php render_control('Form', 'form2', array(
                    'noform'=>true,
                    'conf'=>array(
                            'fields'=>array(
                                    array('title'=>'需求标题', 'type'=>'input', 'field'=>'xqsue_title'),
                                    array('title'=>'业务背景', 'type'=>'textarea', 'field'=>'xqsue_background',),
                                    array('title'=>'需求详情', 'type'=>'richinput', 'field'=>'xqsue_detail','span'=>20,),
                                    array('title'=>'需求附件', 'type'=>'file','field'=>'file','text'=>'新增附件'),
                            ),
                    ), 
                     'col'=>1,
                    'data'=>$response['data'],
                   'rules'=>'servicenter/xqissue_add'        //有效性验证
            )); ?>
        </div>

<?php } else{ ?>    
    <div class="panel-body">
            <?php render_control('Form', 'form2', array(
                    'noform'=>true,
                    'conf'=>array(
                            'fields'=>array(
                                    array('title'=>'需求标题', 'type'=>'input', 'field'=>'xqsue_title'),
                                    array('title'=>'业务背景', 'type'=>'textarea', 'field'=>'xqsue_background',),
                                    array('title'=>'需求详情', 'type'=>'richinput', 'field'=>'xqsue_detail','span'=>20,),
                            ),
                    ), 
                    'col'=>1,
                    'data'=>$response['data'],
                    'rules'=>'servicenter/xqissue_add'        //有效性验证
            )); ?>
    </div>
    <div class="row">
        <div class="control-group span11">
            <label class="control-label span3">需求附件：</label>
            <div class="span8 controls">
                <?php foreach ($response['data']['fjmx'] as $fjmx) { ?>
                    <a style="cursor:pointer" onclick="downFile('<?php echo $fjmx['xqnex_path'] ?>','<?php echo $fjmx['xqnex_name'] ?>')">
                        <?php echo $fjmx['xqnex_name'] ?>
                    </a>
                    <br>
                <?php } ?>
            </div>
        </div>
    </div>
<?php } ?>
    
    
</div>
<?php if($app['scene']=="add" || $app['scene']=="edit"){ ?>
    <div style="width:99%;margin-left:5px;text-align:center;margin-bottom:10px">
        <input type="submit" id="submit" name="submit"  value=" 提交 " class="button button-primary" />
        <input type="button" value=" 返回 " onclick="openPage('<?php echo base64_encode('servicenter/productxqissue/do_list') ?>','?app_act=servicenter/productxqissue/do_list','需求提单列表')" class="button button-success" />
    </div>
<?php }?>
</form>

<script type="text/javascript">
    new BUI.Form.HForm({
            srcNode : '#form1',
            submitType : 'ajax',
            callback : function(data){
                    var type = data.status == 1 ? 'success' : 'error';
                    BUI.Message.Alert(data.message, type);
                    var scene='<?php echo $app['scene'] ?>';
                    var id="";
                    var tab_id="";
                    var edittype="<?php echo $response['edittype'] ?>";
                    if(scene=="add"){
                        id=data.data;
                        tab_id="<?php echo base64_encode('servicenter/productxqissue/detail&app_scene=add') ?>";
                    }
                    else{
                        id=$("#xqsue_number").val();
                        tab_id="servicenter/productxqissue/do_list$edit$"+id;
                        if(edittype=="comedit")
                            tab_id="servicenter/productxqissue/do_list$comedit$"+id;
                    }
                    //打开详细
                    ui_openTabPage("servicenter/productxqissue/do_list$view$"+id, "?app_act=servicenter/productxqissue/detail&app_scene=view&_id="+id, "查看需求提单");
                    //关闭当前选项卡
                    ui_closeTabPage(tab_id);
                    //window.location="?app_act=servicenter/productissue/detail&app_scene=view&_id="+id;
            }
    }).render();
</script>


<?php if($app['scene']=="view"){ ?>
<div class="panel">
    <div class="panel-header">
        <h3>需求处理结果</h3>
    </div>
    <div class="panel-body">
        <?php render_control('Form', 'form1', array(
                'noform'=>true,
                'conf'=>array(
                    'fields' => array(
                        array('title' => '提单状态', 'type' => 'select', 'field' => 'xqsue_status', 'edit_scene' => 'add', 'data' => ds_get_select_by_field('xqissue_type', 2)),
                        array('title' => '受理人', 'type' => 'select', 'field' => 'xqsue_accept_user', 'data' => ds_get_select('users'), 'show_scene' => 'view'),
                        array('title' => '受理时间', 'type' => 'date', 'field' => 'xqsue_accept_time', 'edit_scene' => 'edit'),
                        array('title' => '业务类型', 'type' => 'input', 'field' => 'xqsue_service_type_name', 'edit_scene' => 'edit'),
                        array('title' => '计划周次', 'type' => 'input', 'field' => 'xqsue_plan_week_name', 'edit_scene' => 'edit'),
                        array('title' => '需求类型', 'type' => 'select', 'field' => 'xqsue_xqtype', 'data' => ds_get_select_by_field('xqsuetype', 3)),
                        array('title' => '处理方式', 'type' => 'select', 'field' => 'xqsue_processtype', 'data' => ds_get_select_by_field('xqsue_processtype', 3)),
                        array('title' => '预返时间', 'type' => 'date', 'field' => 'xqsue_return_time', 'edit_scene' => 'edit'),
                        array('title' => '审批人', 'type' => 'select', 'field' => 'xqsue_idea_user', 'data' => ds_get_select('users'), 'show_scene' => 'view'),
                        array('title' => '审批时间', 'type' => 'date', 'field' => 'xqsue_idea_time', 'edit_scene' => 'edit'),
                        array('title' => '审批意见', 'type' => 'textarea', 'field' => 'xqsue_idea', 'edit_scene' => 'edit'),
                        array('title' => '紧急程度', 'type' => 'input', 'field' => 'xqsue_urgency', 'edit_scene' => 'edit'),
                        array('title' => '难易度', 'type' => 'input', 'field' => 'xqsue_difficulty_name', 'edit_scene' => 'edit'),
                        array('title' => '备注', 'type' => 'textarea', 'field' => 'xqsue_remark', 'edit_scene' => 'edit'),

                    ),
                ), 
                'col'=>3,
                'data'=>$response['data'],
        )); ?>
    </div>
</div>
    <div id="copy_main" style="">
        <input type="text" id="copy_value" style="opacity:0.0;cursor: default;width:1px;height:1px;" readonly="readonly" value="<?php echo $response['data']['xqsue_number'].' '.$response['data']['xqsue_detail'];?>"/>
    </div>

<div class="clearfix" style="padding: 0 0 15px 0; text-align: center;">
    <button class="button button-primary" id="btn_opt_accept" onclick="do_accept();" <?php echo $response['stateinfo']['acceptstate'] ?>>受理</button>
    <button class="button button-primary" id="btn_opt_deny" onclick="do_btnpass('3');" <?php echo $response['stateinfo']['denystatus'] ?>>拒绝</button>
    <button class="button button-primary" id="btn_opt_research" onclick="do_btnidea();" <?php echo $response['stateinfo']['ideastatus'] ?>>需求审批</button>
    <button class="button button-primary" id="btn_opt_unable" onclick="do_btnpass('1');" <?php echo $response['stateinfo']['unablestatus'] ?>>无法解决</button>
    <button class="button button-primary" id="btn_opt_pass" onclick="do_btnpass('2');" <?php echo $response['stateinfo']['passtatus'] ?>>已解决</button>
    <button class="button button-primary" id="btn_opt_onlie" onclick="do_btnpass('4');" <?php echo $response['stateinfo']['onlinestatus'] ?>>需求上线</button>
    <button class="button button-primary" id="btn_opt_edit" onclick="do_edit();">需求延期</button>
    <button class="button button-primary" id="btn_opt_plan_week" onclick="do_plan_week();">计划周次</button>
    <button class="button button-primary" id="btn_opt_plan_week" onclick="do_remark();">附注</button>
    <button class="button button-primary" id="btn_opt_copy" onclick="do_copy();">复制</button>
    <input type="button" value=" 返回 " onclick="openPage('<?php echo base64_encode('servicenter/productxqissue/do_list') ?>','?app_act=servicenter/productxqissue/do_list','需求提单列表')" class="button button-primary" />
</div>
<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '操作人',
                'field' => 'xqlog_operater_name',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '操作名称',
                'field' => 'xqlog_operate_detail',
                'width' => '300',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '操作时间',
                'field' => 'xqlog_operate_date',
                'width' => '150',
                'align' => '' ,
                ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '提单状态',
                'field' => 'xqlog_operate_state',
                'width' => '150',
                'align' => '', 
                'format'=>array('type'=>'map', 'value'=>ds_get_field('xqissue_type'))
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'xqlog_notes',
                'width' => '150',
                'align' => '',   
            ),
        ) 
    ),
    'dataset' => 'servicenter/XqissuelogModel::get_log_info',
    'params' => array('filter'=>array('xqlog_number'=>$request['_id'])),
    'queryBy' => 'searchForm',
    'idField' => 'xqlog_number',
) );
?>
<?php } ?>
<script type="text/javascript">
    
    //设置需求标题和业务背景的样式
    var scene='<?php echo $app['scene'] ?>';
    var xqsue_number = '<?php echo $response['data']['xqsue_number'] ?>';
   /* if(scene=="add" || scene=="edit"){
        $("#xqsue_title").attr("style","width:780px");
        $("#xqsue_background").attr("style","width:780px");
    }*/

    function btn_show_dialog(_url, _title, _opts) {
        new ESUI.PopWindow(_url, {
            title: _title,
            width:_opts.w,
            height:_opts.h,
            onBeforeClosed: function() {                  
                location.reload();
                if (typeof _opts.callback == 'function') 
                    _opts.callback();
            }
        }).show();
    }
    
    
    //需求受理
    function do_accept() {
        var url='<?php echo get_app_url('servicenter/productxqissue/do_xqissue_accept'); ?>';
        $.ajax({ type: 'POST', dataType: 'json',  
            url:url,
            data: {xqsue_number: '<?php echo $request['_id'] ?>',}, 
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    //tableStore.load();
                    location.reload();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    //需求上线
    function do_online() {
        var url='<?php echo get_app_url('servicenter/productxqissue/do_xqissue_online'); ?>';
        $.ajax({ type: 'POST', dataType: 'json',  
            url:url,
            data: {xqsue_number: '<?php echo $request['_id'] ?>',}, 
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    //tableStore.load();
                    location.reload();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    
    //无法解决,已解决,拒绝
    function do_btnpass(type){
        //得到单据编号;
        var DJBH='<?php echo $request['_id'] ?>';
        var url='?app_act=servicenter/productxqissue/do_show_unable&djbh='+DJBH;
        var title="无法解决描述";
        if(type=="2"){
            url='?app_act=servicenter/productxqissue/do_show_unable_pass&djbh='+DJBH;
            title="解决描述";
        }
        if(type=="3"){
            url='?app_act=servicenter/productxqissue/do_show_unable_deny&djbh='+DJBH;
            title="拒绝理由描述";
        }
        if(type=="4"){
            url='?app_act=servicenter/productxqissue/do_show_online&type=1&xqsue_number='+DJBH;
            title="上线描述";
        }
        btn_show_dialog(url,title, {w:800,h:450});
    }
    
    //需求审批
    function do_btnidea(){
         //得到单据编号;
        var DJBH='<?php echo $request['_id'] ?>';
        var url='?app_act=servicenter/productxqissue/do_show_idea&djbh='+DJBH;
        var title="需求审批";
        btn_show_dialog(url,title, {w:800,h:600});
    }
    
    
    //附件下载js
    function downFile(filepath,downname){
        window.location="?app_act=common/file/download_upload_file&path="+filepath+"&name="+downname;
    }
    
    var selectPopWindowxqsue_kh_id = {
    dialog: null,
    callback: function (value, id, code, name) {
        var nameArr = [], valueArr = [];
        for (var i = 0; i < value.length; i++) {
            nameArr.push('['+value[i][code]+']'+value[i][name]);
            valueArr.push(value[i][id]);
        }
        $('#xqsue_kh_id_select_pop').val(nameArr.join(','));
        $('#xqsue_kh_id').val(valueArr.join(','));
        if (selectPopWindowxqsue_kh_id.dialog != null) {
            selectPopWindowxqsue_kh_id.dialog.close();
        }
        //get lxfs
        $.ajax({ type: 'POST', dataType: 'json',  
            url:"<?php echo get_app_url('servicenter/productxqissue/get_clients_info'); ?>",
            data: {xqsue_kh_id: $('#xqsue_kh_id').val(),}, 
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                   $('#xqsue_kh_contact').val(ret.data.kh_itname);
                   $('#xqsue_kh_phone').val(ret.data.kh_itphone);
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
};
$('#xqsue_kh_id_select_pop,#xqsue_kh_id_select_img').click(function() {
    selectPopWindowxqsue_kh_id.dialog = new ESUI.PopSelectWindow('?app_act=common/select/clientinfo', 'selectPopWindowxqsue_kh_id.callback', {title: '客户名称', width: 900, height:500 ,ES_pFrmId:"<?php echo $request['ES_frmId'] ?>" }).show();
});


//绑定需求提单-版本号选择事件
    $("#xqsue_cp_id").change(function(){
       //清空关联版本
       $("#xqsue_pv_id_select_pop").val('');
       $("#xqsue_pv_id").val();
    });

    var selectPopWindowxqsue_pv_id = {
    dialog: null,
    callback: function (value, id, code, name) {
        var nameArr = [], valueArr = [];
        for (var i = 0; i < value.length; i++) {
            nameArr.push('['+value[i][code]+']'+value[i][name]);
            valueArr.push(value[i][id]);
        }
        $('#xqsue_pv_id_select_pop').val(nameArr.join(','));
        $('#xqsue_pv_id').val(valueArr.join(','));
        if (selectPopWindowxqsue_pv_id.dialog != null) {
            selectPopWindowxqsue_pv_id.dialog.close();
        }
    }
};
$('#xqsue_pv_id_select_pop,#xqsue_pv_id_select_img').click(function() {
    if($("#sue_cp_id").val()==""){
        BUI.Message.Alert("先选择产品信息", "error");
        return;
    }
    selectPopWindowxqsue_pv_id.dialog = new ESUI.PopSelectWindow('?app_act=common/select/edition&cpid='+$("#xqsue_cp_id").val(), 'selectPopWindowxqsue_pv_id.callback', {title: '版本', width: 900, height:500 ,ES_pFrmId:"<?php echo $request['ES_frmId'] ?>" }).show();
});
    
 
 
 
 
//绑定需求提单-产品模块选择事件
    $("#xqsue_cp_id").change(function(){
       //清空关联版本
       $("#xqsue_product_fun_select_pop").val('');
       $("#xqsue_product_fun").val();
    });
    
    var selectPopWindowxqsue_product_fun = {
    dialog: null,
    callback: function (value, id, code, name) {
        var nameArr = [], valueArr = [];
        for (var i = 0; i < value.length; i++) {
            nameArr.push('['+value[i][code]+']'+value[i][name]);
            valueArr.push(value[i][id]);
        }
        $('#xqsue_product_fun_select_pop').val(nameArr.join(','));
        $('#xqsue_product_fun').val(valueArr.join(','));
        if (selectPopWindowxqsue_product_fun.dialog != null) {
            selectPopWindowxqsue_product_fun.dialog.close();
        }
    }
};
$('#xqsue_product_fun_select_pop,#xqsue_product_fun_select_img').click(function() {
    if($("#xqsue_cp_id").val()==""){
        BUI.Message.Alert("先选择产品信息", "error");
        return;
    }
    selectPopWindowxqsue_product_fun.dialog = new ESUI.PopSelectWindow('?app_act=common/select/productmodule&cpid='+$("#xqsue_cp_id").val(), 'selectPopWindowxqsue_product_fun.callback', {title: '产品模块', width: 900, height:500 ,ES_pFrmId:"<?php echo $request['ES_frmId'] ?>" }).show();
});


//修改期反时间
    function do_edit() {
            var url='?app_act=servicenter/productxqissue/do_edit_return_time&app_scene=edit&xqsue_number='+xqsue_number;
            var title="需求延期";
            btn_show_dialog(url,title, {w:600,h:550});
    }

    //复制
    function do_copy() {
        var Url2 = document.getElementById("copy_value");
        Url2.select(); // 选择对象
        document.execCommand("Copy"); // 执行浏览器复制命令
        BUI.Message.Alert('已复制好，可贴粘!', function () {

        }, 'success');
    }


    //计划周次
    function do_plan_week() {
        var url='?app_act=servicenter/productxqissue/do_plan_week&app_scene=edit&xqsue_number='+xqsue_number;
        var title="计划周次";
        btn_show_dialog(url,title, {w:570,h:300});
    }

    function do_remark() {
        var url='?app_act=servicenter/productxqissue/do_remark&app_scene=edit&xqsue_number='+xqsue_number;
        var title="附注";
        btn_show_dialog(url,title, {w:800,h:600});
    }
</script>



