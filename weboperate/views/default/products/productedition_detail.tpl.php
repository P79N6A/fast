<?php render_control('PageHead', 'head1',
    array('title'=>isset($app['title']) ? $app['title'] : '编辑版本',
	'links'=>array(
            //array('url'=>'products/productedition/do_list','title'=>'版本列表'),
            )
));?>
<?php echo load_js('ueditor1_4_3/ueditor.config.js,ueditor1_4_3/ueditor.all.js')?>
<form  class="form-horizontal" id="form1" action="<?php echo '?app_act=products/productedition/do_'.$app["scene"] ?>" method="post">
<?php if($app['scene']=="add" || $app['scene']=="edit"){ ?>
    <div style="width:99%;margin-left:5px;text-align:right;margin-bottom:10px">
        <input type="submit" id="submit" name="submit"  value=" 保存 " class="button button-primary" />
        <input type="button" value=" 返回 " onclick="openPage('<?php echo base64_encode('products/productedition/do_list') ?>','?app_act=products/productedition/do_list','产品版本')" class="button button-success" />
    </div>
<?php }?>
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
                                array('title'=>'版本代码', 'type'=>'input', 'field'=>'pv_code', 'edit_scene'=>'add'),
                                array('title'=>'版本名称', 'type'=>'input', 'field'=>'pv_name', 'edit_scene'=>'add,edit'),
                                array('title'=>'版本号', 'type'=>'input', 'field'=>'pv_bh', 'edit_scene'=>'add,edit'),
                                array('title'=>'版本日期', 'type'=>'date', 'field'=>'pv_rq', ),
                                //array('title'=>'发布负责人', 'type'=>'input', 'field'=>'pv_fbr', ),
                                array('title'=>'发布负责人', 'type'=>'select_pop', 'field'=>'pv_fbr', 'select'=>'sys/orguser','show_scene'=>'add,edit'),
                                array('title'=>'发布负责人', 'type'=>'input', 'field'=>'pv_fbr_name','show_scene'=>'view'),
                                array('title'=>'产品', 'type'=>'select', 'field'=>'pv_cp_id','data'=>ds_get_select('chanpin')),
                                array('title'=>'版本类型', 'type'=>'select', 'field'=>'pv_type','data'=>ds_get_select_by_field('cpversion',3)),
                                array('title'=>'关联正式版本', 'type'=>'select_pop', 'field'=>'pv_relation_version','select'=>'products/edition','show_scene'=>'add,edit','eventtype'=>'custom'),
                                array('title'=>'创建人', 'type'=>'input', 'field'=>'pv_createuser_name','edit_scene'=>'','show_scene'=>'view,edit'),
                                array('title'=>'创建时间', 'type'=>'input','field'=>'pv_createdate','edit_scene'=>'','show_scene'=>'view,edit'),
                                array('title'=>'修改人', 'type'=>'input', 'field'=>'pv_updateuser_name', 'edit_scene'=>'','show_scene'=>'view,edit'),
                                array('title'=>'修改时间', 'type'=>'input','field'=>'pv_updatedate','edit_scene'=>'','show_scene'=>'view,edit' ),
                        ),      
                        'hidden_fields'=>array(array('field'=>'pv_id'),array('field'=>'pv_code')), 
                ), 
                'col'=>3,
                'data'=>$response['data'],
                'rules'=>'products/pversion_edit'        //有效性验证
        )); ?>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h3>详细信息</h3>
    </div>
    <?php if($app['scene']=="add" || $app['scene']=="edit"){ ?>
        <div class="panel-body">
            <?php render_control('Form', 'form2', array(
                    'noform'=>true,
                    'conf'=>array(
                            'fields'=>array(
                                    array('title'=>'版本发布路径', 'type'=>'input', 'field'=>'pv_path', 'edit_scene'=>'add,edit'),
                                    array('title'=>'版本特性介绍', 'type'=>'richinput', 'field'=>'pv_js','span'=>20,),
                                    array('title'=>'版本附件', 'type'=>'file', 'text'=>'新增','field'=>'file'),
                            ),
                    ), 
                    'data'=>$response['data'],
                    'rules'=>'products/pversion_edit'        //有效性验证
            )); ?>
        </div>
    <?php } else {?>
        <div class="panel-body">
            <?php render_control('Form', 'form2', array(
                    'noform'=>true,
                    'conf'=>array(
                            'fields'=>array(
                                    array('title'=>'版本发布路径', 'type'=>'input', 'field'=>'pv_path', 'edit_scene'=>'add,edit'),
                                    array('title'=>'版本特性介绍', 'type'=>'richinput', 'field'=>'pv_js','span'=>20,),
                            ),
                    ), 
                    'data'=>$response['data'],
                    'rules'=>'products/pversion_edit'        //有效性验证
            )); ?>
            <div class="row">
                <div class="control-group span11">
                    <label class="control-label span3">版本附件：</label>
                    <div class="span8 controls">
                        <?php foreach($response['data']['fjmx'] as $fjmx){ ?>
                            <a style="cursor:pointer" onclick="downFile('<?php echo $fjmx['pv_fj_path'] ?>','<?php echo $fjmx['pv_fj_name'] ?>')">
                                <?php echo $fjmx['pv_fj_name'] ?>
                            </a>
                            <br>
                        <?php } ?>
                    </div>
		</div>
            </div>
        </div>
    <?php } ?>
</div>
</form>
<script type="text/javascript">
    var form = new BUI.Form.HForm({
            srcNode : '#form1',
            submitType : 'ajax',
            callback : function(data){
                    var type = data.status == 1 ? 'success' : 'error';
                    BUI.Message.Alert(data.message, type);
            }
    }).render();
    
    //提交表单前验证事件
    form.on('beforesubmit', function (ev){
        //验证补丁版本必选字段
        if($("#pv_type").val()=='2')  //表示补丁版本
        {
            if($("#pv_relation_version").val()==""){
                BUI.Message.Alert("补丁版本所属关联正式版本不能为空", "warning");
                return false;
            }
        }
    });
    
    function downFile(filepath,downname){
        window.location="?app_act=common/file/download_upload_file&path="+filepath+"&name="+downname;
    }
    
    
    //绑定产品选择事件
    $("#pv_cp_id").change(function(){
       //清空关联版本
       $("#pv_relation_version_select_pop").val('');
       $("#pv_relation_version").val('');
    });
    var selectPopWindowpv_relation_version = {
        dialog: null,
        callback: function (value, id, code, name) {
            var nameArr = [], valueArr = [];
            for (var i = 0; i < value.length; i++) {
                nameArr.push('['+value[i][code]+']'+value[i][name]);
                valueArr.push(value[i][id]);
            }
            $('#pv_relation_version_select_pop').val(nameArr.join(','));
            $('#pv_relation_version').val(valueArr.join(','));
            if (selectPopWindowpv_relation_version.dialog != null) {
                selectPopWindowpv_relation_version.dialog.close();
            }
        }
    };
    
    $('#pv_relation_version_select_pop,#pv_relation_version_select_img').click(function() {
        if($("#pv_cp_id").val()==""){
            BUI.Message.Alert("先选择产品信息", "error");
            return;
        }
        selectPopWindowpv_relation_version.dialog = new ESUI.PopSelectWindow('?app_act=common/select/edition&cpid='+$("#pv_cp_id").val()+'&type=0', 'selectPopWindowpv_relation_version.callback', {title: '关联正式版本', width:800, height:500 ,ES_pFrmId:"<?php echo $request['ES_frmId'] ?>" }).show();
    });
</script>


