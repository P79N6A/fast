<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '查看产品补丁',
	'links'=>array(
            array('url'=>'products/productpatch/do_list','title'=>'产品补丁列表'),
            )
));?>
<style>
    .panel-body {padding: 2px;}
    .panel-body table {margin: 0; }
    #upfileclick{padding: 4px 20px;border: 1px solid #ccc;border-radius: 3px;}
    #upfileclick:hover{background: #3071A9; color: #FFF; border-color:#3071A9; }
</style>
<form  class="form-horizontal" id="form1" action="<?php echo '?app_act=products/productpatch/patch_'.$app["scene"] ?>" method="post">
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left">补丁信息</h3>
        <?php if($app['scene']=="add" || $app['scene']=="edit"){ ?>
            <div class="pull-right">
                <button type="submit" class="button button-primary" id="submit">提交</button>
                <button type="reset" class="button " id="reset">重置</button>
            </div>
        <?php }?>
    </div>
    <div class="panel-body">
    <?php render_control('Form', 'form1', array(
            'noform'=>true,
            'conf'=>array(
                    'fields'=>array(
                        array('title'=>'产品名称', 'type'=>'select', 'field'=>'cp_id', 'data' => ds_get_select('chanpin', 2)),
//			array('title'=>'版本编号', 'type'=>'select', 'field'=>'version_no', 'data' => ds_get_select('pdt_bh', 2)),
                        array('title'=>'版本编号', 'type'=>'select_pop', 'field'=>'version_no','select'=>'products/edition','show_scene'=>'add,edit'),
                        array('title'=>'补丁编号', 'type'=>'input', 'field'=>'version_patch',),
                        //array('title'=>'包含SQL', 'type'=>'checkbox', 'field'=>'is_sql', ),
                       // array('title'=>'补丁包路径', 'type'=>'input', 'field'=>'version_file_path', ),
                       // array('title'=>'基础补丁路径', 'type'=>'input', 'field'=>'upgrade_patch', ),
                        array('title'=>'补丁附件 ', 'type'=>'html', 'field'=>'upfile','html'=>"<label style='text-align:left' class='control-label control-operate-label' id='file_name' name='file_name'>".$response['data']['version_file_name']."</label><input id='upfile' name='upfile' type='file' style='display:none' onchange='upfilechange(this);'/><br/><button id='upfileclick' type='button'>选择文件</button>"),
                        array('title'=>'创建时间', 'type'=>'input','field'=>'create_time','edit_scene'=>'','show_scene'=>'view,edit'),
                        array('title'=>'状态 ', 'type'=>'select', 'field'=>'is_exec','data' => ds_get_select_by_field('patch_status', 3)),
                    ),      
                    'hidden_fields'=>array(array('field'=>'id'),array('field'=>'version_file_path'),array('field'=>'version_file_name')), 
            ), 
            'col'=>2,
            'act_edit'=>'products/productinfo/product_edit', //edit,add,view
            'act_add'=>'products/productinfo/product_add',
            'data'=>$response['data'],
            'rules'=>'products/add_ptpatch',        //有效性验证
    )); ?>
    </div>
</div>
</form>
<?php echo load_js('ajaxfileupload.js',true);?>
<script type="text/javascript">
    new BUI.Form.HForm({
            srcNode : '#form1',
            submitType : 'ajax',
            callback : function(data){
                    var type = data.status == 1 ? 'success' : 'error';
                    BUI.Message.Alert(data.message, type);
            }
    }).render();
</script>

<script type="text/javascript">
    
    function PageHead_show_dialog_ref(_url, _title, _opts,refgrid) {

        new ESUI.PopWindow(_url, {
                title: _title,
                width:_opts.w,
                height:_opts.h,
                onBeforeClosed: function() { 
                    if(refgrid){
                        refgrid.load();
                    }
                    if (typeof _opts.callback == 'function') _opts.callback();
                }
            }).show();
    }
    
    //删除模块明细++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    function do_delete_detail(_index, row) {
	$.ajax({ type: 'POST', dataType: 'json',  
            url:"<?php echo get_app_url('products/productpcd/do_delete_sql');?>",
            data: {id: row.id}, 
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    tablemdStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
	});
    }
    
     //产品补丁-版本编号选择
    $("#cp_id").change(function(){
       //清空关联版本
       $("#version_no_select_pop").val('');
       $("#version_no").val();
    });
    
    $('#version_no_select_pop,#version_no_select_img').unbind('click');
    
    var selectPopWindowversion_no = {
    dialog: null,
    callback: function (value, id, code, name) {
        var nameArr = [], valueArr = [];
        for (var i = 0; i < value.length; i++) {
            nameArr.push('['+value[i]['pv_bh']+']'+value[i][name]);
            valueArr.push(value[i]['pv_bh']);
        }
        $('#version_no_select_pop').val(nameArr.join(','));
        $('#version_no').val(valueArr.join(','));
        if (selectPopWindowversion_no.dialog != null) {
            selectPopWindowversion_no.dialog.close();
        }
    }
};
$('#version_no_select_pop,#version_no_select_img').click(function() {
    if($("#cp_id").val()==""){
        BUI.Message.Alert("先选择产品信息", "error");
        return;
    }
    selectPopWindowversion_no.dialog = new ESUI.PopSelectWindow('?app_act=common/select/pversion&cpid='+$("#cp_id").val(), 'selectPopWindowversion_no.callback', {title: '版本', width: 900, height:500 ,ES_pFrmId:"<?php echo $request['ES_frmId'] ?>" }).show();
});
    
$("#upfileclick").click(function(){
    $("#upfile").click();
});

    //执行上传操作
function upfilechange(obj){
    var id = jQuery(obj).attr('id');
    var url = "?app_act=products/productpatch/patch_upload";
    jQuery.ajaxFileUpload({
        url: url,
        secureuri: false,
        fileElementId: id,
        dataType: 'json',
        success: function(data,status){
            var type = data.status == 1 ? 'success' : 'error';
            if (type == 'success'){
                var _path = $.parseJSON(data.data.path);
                $('#version_file_path').val(_path[0]);
                $('#version_file_name').val(_path[1]);
                $('#file_name').text(_path[1]);
            }else{
                alert(data.message);
            }
        },
        error: function(data,status,e){
            
        }
    });
}
    
</script>

