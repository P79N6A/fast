<?php render_control('PageHead', 'head1',
    array('title'=>isset($app['title']) ? $app['title'] : '问题提单',
	'links'=>array(
            )
));?>
<?php echo load_js('ueditor1_4_3/ueditor.config.js,ueditor1_4_3/ueditor.all.js')?>
<form  class="form-horizontal" id="form1" action="<?php echo '?app_act=servicenter/productissue/do_'.$app["scene"] ?>" method="post">

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
//                                array('title'=>'产品', 'type'=>'input', 'field'=>'sue_cp_id', ),
                                array('title'=>'产品', 'type'=>'select', 'field'=>'sue_cp_id','data'=>ds_get_select('chanpin')),
//                                array('title'=>'版本号', 'type'=>'select', 'field'=>'sue_pv_id','data'=>ds_get_select('issue_chanpin_version')),
                                array('title'=>'版本号', 'type'=>'select_pop', 'field'=>'sue_pv_id','select'=>'products/edition','show_scene'=>'add,edit','eventtype'=>'custom'),
                                array('title'=>'版本号', 'type'=>'input', 'field'=>'sue_pv_id_name','show_scene'=>'view'),
                                array('title'=>'其他版本号', 'type'=>'input', 'field'=>'sue_other_pv', ),
                                array('title'=>'客户名称', 'type'=>'select_pop', 'field'=>'sue_kh_id', 'select'=>'clients/clientinfo','show_scene'=>'add,edit','eventtype'=>'custom'),
                                array('title'=>'客户名称', 'type'=>'input', 'field'=>'sue_kh_id_name','show_scene'=>'view','edit_scene'=>''),
                                array('title'=>'客户联系人', 'type'=>'input', 'field'=>'sue_kh_contact'),
                                array('title'=>'客户联系方式', 'type'=>'input', 'field'=>'sue_kh_phone'),
                                array('title'=>'提单人员','type'=>'select', 'field'=>'sue_user','data'=>ds_get_select('users'),'show_scene'=>'view'),
//                                array('title'=>'提单来源', 'type'=>'input', 'field'=>'sue_submit_source'),
                                array('title'=>'提单来源', 'type'=>'select', 'field'=>'sue_submit_source','data'=>ds_get_select_by_field('issue_source','3')),
                                array('title'=>'提单创建时间', 'type'=>'input','field'=>'sue_submit_time','show_scene'=>'view'),
//                                array('title'=>'产品模块', 'type'=>'input', 'field'=>'sue_product_fun', 'edit_scene'=>'add,edit'),
                                array('title'=>'产品模块', 'type'=>'select_pop', 'field'=>'sue_product_fun','select'=>'products/productmodule','show_scene'=>'add,edit','eventtype'=>'custom'),
                                array('title'=>'产品模块', 'type'=>'input', 'field'=>'sue_product_fun_name','show_scene'=>'view'),
                                array('title'=>'产品访问URL', 'type'=>'input', 'field'=>'sue_product_url'),
                                
                        ),      
                        'hidden_fields'=>array(array('field'=>'sue_number')), 
                ), 
                'col'=>3,
                'data'=>$response['data'],
                'rules'=>'servicenter/issue_add'            //有效性验证
        )); ?>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h3>提单详情</h3>
    </div>
<?php if($app['scene']=="add" || $app['scene']=="edit"){ ?>    
        <div class="panel-body">
            <?php render_control('Form', 'form2', array(
                    'noform'=>true,
                    'conf'=>array(
                            'fields'=>array(
                                    array('title'=>'问题标题', 'type'=>'input', 'field'=>'sue_title'),
//                                    array('title'=>'问题详情', 'type'=>'textarea', 'field'=>'sue_detail'),
                                    array('title'=>'问题详情', 'type'=>'richinput', 'field'=>'sue_detail','span'=>20,),
                                    array('title'=>'问题附件', 'type'=>'file','field'=>'file','text'=>'新增附件'),
                            ),
                    ), 
                     'col'=>1,
                    'data'=>$response['data'],
                   'rules'=>'servicenter/issue_add'        //有效性验证
            )); ?>
        </div>

<?php } else{ ?>    
    <div class="panel-body">
            <?php render_control('Form', 'form2', array(
                    'noform'=>true,
                    'conf'=>array(
                            'fields'=>array(
                                    array('title'=>'问题标题', 'type'=>'input', 'field'=>'sue_title'),
                                    array('title'=>'问题详情', 'type'=>'richinput', 'field'=>'sue_detail','span'=>20,),
                            ),
                    ), 
                     'col'=>1,
                    'data'=>$response['data'],
                   'rules'=>'servicenter/issue_add'        //有效性验证
            )); ?>
    </div>
    <div class="row">
        <div class="control-group span11">
            <label class="control-label span3">问题附件：</label>
            <div class="span8 controls">
                <?php foreach ($response['data']['fjmx'] as $fjmx) { ?>
                    <a style="cursor:pointer" onclick="downFile('<?php echo $fjmx['nex_path'] ?>','<?php echo $fjmx['nex_name'] ?>')">
                        <?php echo $fjmx['nex_name'] ?>
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
        <input type="button" value=" 返回 " onclick="openPage('<?php echo base64_encode('servicenter/productissue/do_list') ?>','?app_act=servicenter/productissue/do_list','问题提单列表')" class="button button-success" />
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
//                    location.href="servicenter/productissue/detail&app_scene=view";
                    var scene='<?php echo $app['scene'] ?>';
                    var id="";
                    var tab_id="";
                    var edittype="<?php echo $response['edittype'] ?>";
                    if(scene=="add"){
                        id=data.data;
                        tab_id="<?php echo base64_encode('servicenter/productissue/detail&app_scene=add') ?>";
                    }
                    else{
                        id=$("#sue_number").val();
                        tab_id="servicenter/productissue/do_list$edit$"+id;
                        if(edittype=="comedit")
                            tab_id="servicenter/productissue/do_list$comedit$"+id;
                    }
                    //打开详细
                    ui_openTabPage("servicenter/productissue/do_list$view$"+id, "?app_act=servicenter/productissue/detail&app_scene=view&_id="+id, "查看问题提单");
                    //关闭当前选项卡
                    ui_closeTabPage(tab_id);
                    //window.location="?app_act=servicenter/productissue/detail&app_scene=view&_id="+id;
            }
    }).render();
</script>


<?php if($app['scene']=="view"){ ?>
<div class="panel">
    <div class="panel-header">
        <h3>问题处理结果</h3>
    </div>
    <div class="panel-body">
        <?php render_control('Form', 'form1', array(
                'noform'=>true,
                'conf'=>array(
                        'fields'=>array(
                                array('title'=>'处理状态', 'type'=>'select', 'field'=>'sue_status', 'edit_scene'=>'add','data'=>ds_get_select_by_field('issue_type',2)),
                                array('title'=>'处理完成时间', 'type'=>'date', 'field'=>'sue_solve_time', 'edit_scene'=>'edit'),
                                array('title'=>'受理人','type'=>'select', 'field'=>'sue_idea_user','data'=>ds_get_select('users'),'show_scene'=>'view'),
                                array('title'=>'研发处理人员', 'type'=>'select', 'field'=>'sue_research','data'=>ds_get_select('users')),
                                array('title'=>'受理时间', 'type'=>'input', 'field'=>'sue_accept_time','show_scene'=>'view'),
                                array('title'=>'处理意见', 'type'=>'input', 'field'=>'sue_idea', ),
                        ),      
                ), 
                'col'=>4,
                'data'=>$response['data'],
        )); ?>
    </div>
</div>

<div class="clearfix" style="padding: 0 0 15px 0; text-align: center;">
    <button class="button button-primary" id="btn_opt_accept" onclick="do_accept();" <?php echo $response['stateinfo']['acceptstate'] ?>>受理</button>
    <!--<button class="button button-primary" id="btn_opt_commu" onclick="do_btncommu();"<?php echo $response['stateinfo']['commu'] ?>>沟通</button>-->
    <button class="button button-primary" id="btn_opt_deny" onclick="do_btnpass('3');"<?php echo $response['stateinfo']['denystatus'] ?>>拒绝</button>
    <button class="button button-primary" id="btn_opt_research" onclick="do_btnresearch();"<?php echo $response['stateinfo']['researchstatus'] ?>>研发介入</button>
    <button class="button button-primary" id="btn_opt_unable" onclick="do_btnpass('1');"<?php echo $response['stateinfo']['unablestatus'] ?>>无法解决</button>
    <!--<button class="button button-primary" id="btn_opt_require" onclick="do_btnrequire();"<?php echo $response['stateinfo']['requirestatus'] ?>>转需求</button>-->
    <button class="button button-primary" id="btn_opt_pass" onclick="do_btnpass('2');"<?php echo $response['stateinfo']['passtatus'] ?>>已解决</button>
    <input type="button" value=" 返回 " onclick="openPage('<?php echo base64_encode('servicenter/productissue/do_list') ?>','?app_act=servicenter/productissue/do_list','问题提单列表')" class="button button-primary" />
</div>
<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '操作人',
                'field' => 'log_operater_name',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '操作名称',
                'field' => 'log_operate_detail',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '操作时间',
                'field' => 'log_operate_date',
                'width' => '150',
                'align' => '' ,
//                'format'=>array('type'=>'map', 'value'=>ds_get_field('channel_type'))
                ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '提单状态',
                'field' => 'log_sue_status',
                'width' => '150',
                'align' => '', 
                'format'=>array('type'=>'map', 'value'=>ds_get_field('issue_type'))
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'log_notes',
                'width' => '150',
                'align' => '',   
            ),
        ) 
    ),
    'dataset' => 'servicenter/IssuelogModel::get_log_info',
    'params' => array('filter'=>array('log_sue_number'=>$request['_id'])),
    'queryBy' => 'searchForm',
    'idField' => 'log_sue_number',
    //'RowNumber'=>true,
//    'CheckSelection'=>true,
) );
?>
<?php } ?>
<script type="text/javascript">
    
    //无法解决,已解决
    function do_btnpass(type){
        //得到单据编号;
        var DJBH='<?php echo $request['_id'] ?>';
        //btn_show_dialog('','',null);
        var url='?app_act=servicenter/productissue/do_show_unable&djbh='+DJBH;
        var title="无法解决理由";
        if(type=="2"){
            url='?app_act=servicenter/productissue/do_show_unable_pass&djbh='+DJBH;
            title="解决理由";
        }
        if(type=="3"){
            url='?app_act=servicenter/productissue/do_show_unable_deny&djbh='+DJBH;
            title="拒绝理由";
        }
        btn_show_dialog(url,title, {w:800,h:400});
    }

   //研发介入
    function do_btnresearch(){
        //得到单据编号;
        var DJBH='<?php echo $request['_id'] ?>';
        //btn_show_dialog('','',null);
        btn_show_dialog('?app_act=servicenter/productissue/do_show_research&djbh='+DJBH,'研发处理人', {w:800,h:400});
    }
    
    //问题受理
    function do_accept() {
        var url='<?php echo get_app_url('servicenter/productissue/do_issue_accept'); ?>';
        $.ajax({ type: 'POST', dataType: 'json',  
            url:url,
            data: {sue_number: '<?php echo $request['_id'] ?>',}, 
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
    
     
    function btn_show_dialog(_url, _title, _opts) {
        new ESUI.PopWindow(_url, {
            title: _title,
            width:_opts.w,
            height:_opts.h,
            onBeforeClosed: function() {                 
//                tableStore.load();  
                  location.reload();
                if (typeof _opts.callback == 'function') 
                    _opts.callback();
            }
        }).show();
    }
    
    //附件下载js
    function downFile(filepath,downname){
        window.location="?app_act=common/file/download_upload_file&path="+filepath+"&name="+downname;
    }
    
    var selectPopWindowsue_kh_id = {
    dialog: null,
    callback: function (value, id, code, name) {
        var nameArr = [], valueArr = [];
        for (var i = 0; i < value.length; i++) {
            nameArr.push('['+value[i][code]+']'+value[i][name]);
            valueArr.push(value[i][id]);
        }
        $('#sue_kh_id_select_pop').val(nameArr.join(','));
        $('#sue_kh_id').val(valueArr.join(','));
        if (selectPopWindowsue_kh_id.dialog != null) {
            selectPopWindowsue_kh_id.dialog.close();
        }
        //get lxfs
        $.ajax({ type: 'POST', dataType: 'json',  
//            url:"servicenter/productissue/get_clients_info",
            url:"<?php echo get_app_url('servicenter/productissue/get_clients_info'); ?>",
            data: {sue_kh_id: $('#sue_kh_id').val(),}, 
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                   $('#sue_kh_contact').val(ret.data.kh_itname);
                   $('#sue_kh_phone').val(ret.data.kh_itphone);
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
};
$('#sue_kh_id_select_pop,#sue_kh_id_select_img').click(function() {
    selectPopWindowsue_kh_id.dialog = new ESUI.PopSelectWindow('?app_act=common/select/clientinfo', 'selectPopWindowsue_kh_id.callback', {title: '客户名称', width: 900, height:500 ,ES_pFrmId:"<?php echo $request['ES_frmId'] ?>" }).show();
});


     //绑定问题提单-版本号选择事件
    $("#sue_cp_id").change(function(){
       //清空关联版本
       $("#sue_pv_id_select_pop").val('');
       $("#sue_pv_id").val();
    });

    var selectPopWindowsue_pv_id = {
    dialog: null,
    callback: function (value, id, code, name) {
        var nameArr = [], valueArr = [];
        for (var i = 0; i < value.length; i++) {
            nameArr.push('['+value[i][code]+']'+value[i][name]);
            valueArr.push(value[i][id]);
        }
        $('#sue_pv_id_select_pop').val(nameArr.join(','));
        $('#sue_pv_id').val(valueArr.join(','));
        if (selectPopWindowsue_pv_id.dialog != null) {
            selectPopWindowsue_pv_id.dialog.close();
        }
    }
};
$('#sue_pv_id_select_pop,#sue_pv_id_select_img').click(function() {
    if($("#sue_cp_id").val()==""){
        BUI.Message.Alert("先选择产品信息", "error");
        return;
    }
    selectPopWindowsue_pv_id.dialog = new ESUI.PopSelectWindow('?app_act=common/select/edition&cpid='+$("#sue_cp_id").val(), 'selectPopWindowsue_pv_id.callback', {title: '版本', width: 900, height:500 ,ES_pFrmId:"<?php echo $request['ES_frmId'] ?>" }).show();
});
    
 
 
 
 
     //绑定问题提单-产品模块选择事件
    $("#sue_cp_id").change(function(){
       //清空关联版本
       $("#sue_product_fun_select_pop").val('');
       $("#sue_product_fun").val();
    });
    
        var selectPopWindowsue_product_fun = {
    dialog: null,
    callback: function (value, id, code, name) {
        var nameArr = [], valueArr = [];
        for (var i = 0; i < value.length; i++) {
            nameArr.push('['+value[i][code]+']'+value[i][name]);
            valueArr.push(value[i][id]);
        }
        $('#sue_product_fun_select_pop').val(nameArr.join(','));
        $('#sue_product_fun').val(valueArr.join(','));
        if (selectPopWindowsue_product_fun.dialog != null) {
            selectPopWindowsue_product_fun.dialog.close();
        }
    }
};
$('#sue_product_fun_select_pop,#sue_product_fun_select_img').click(function() {
    if($("#sue_cp_id").val()==""){
        BUI.Message.Alert("先选择产品信息", "error");
        return;
    }
    selectPopWindowsue_product_fun.dialog = new ESUI.PopSelectWindow('?app_act=common/select/productmodule&cpid='+$("#sue_cp_id").val(), 'selectPopWindowsue_product_fun.callback', {title: '产品模块', width: 900, height:500 ,ES_pFrmId:"<?php echo $request['ES_frmId'] ?>" }).show();
});
    
    
</script>



