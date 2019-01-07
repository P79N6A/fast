<?php render_control('PageHead', 'head1',
            array('title'=>'生成数据库'));
?>
<form  class="form-horizontal" id="form1" action="?app_act=products/rdsextmanage/do_batch_createdb" method="post">
<?php render_control('Form', 'form1', array(
        'noform'=>true,
	'conf'=>array(
		'fields'=>array(
                        array('title'=>'产品', 'type'=>'select', 'field'=>'rem_cp_id','data'=>ds_get_select('chanpin')),
                        array('title'=>'产品版本', 'type'=>'select', 'field'=>'rem_db_sys_version','data' => ds_get_select_by_field('product_version', 2)),
                        array('title'=>'系统版本', 'type'=>'select_pop', 'field'=>'rem_db_version','select'=>'products/edition','eventtype'=>'custom'),
			array('title'=>'数量', 'type'=>'text', 'field'=>'rem_num'),
                        array('title'=>'备注', 'type'=>'textarea', 'field'=>'rem_remark'),
		), 
                'hidden_fields'=>array(array('field'=>'hdrdslist'),), 
	), 
	'buttons'=>array(
			array('label'=>'生成', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
        'act_add'=>'products/rdsextmanage/do_batch_createdb',
        'act_edit'=>'products/rdsextmanage/do_batch_createdb',
	'data'=>$response['data'],
        'rules'=>array(
            array('rem_cp_id', 'require'), 
            array('rem_db_version', 'require'),
            array('rem_db_sys_version', 'require'),
            array('rem_num', 'require'),
            array('rem_num', 'number'),
            array('rem_num', 'min', 'value'=>1),
            array('rem_num', 'max', 'value'=>9),)
)); ?>
</form>

<script type="text/javascript">
 var form =  new BUI.Form.HForm({
            srcNode : '#form1',
            submitType : 'ajax',
            callback : function(data){
                    var type = data.status == 1 ? 'success' : 'error';
                    if (data.status == 1) {
                        ui_closePopWindow('<?php echo $request['ES_frmId']?>'); 
                        window.location.reload();
                    } else {
                        BUI.Message.Alert(data.message, function() { }, type);
                    }

            }
    }).render();
</script>
<script type="text/javascript">
    
    var ctype='<?php echo $request["ctype"] ?>';
    var cdata='<?php echo $request['data'] ?>';
    if(ctype=="1"){
       $("#rem_cp_id").val(jQuery.parseJSON(cdata)[0]['rem_cp_id']);
       $("#rem_cp_id").attr("disabled", "disabled");
       $('#hdrdslist').val(cdata);
    }
    //绑定产品选择事件
    $("#rem_cp_id").change(function(){
       //清空关联版本
       $("#rem_db_version_select_pop").val('');
       $("#rem_db_version").val('');
    });
    
    var selectPopWindowrem_db_version = {
        dialog: null,
        callback: function (value, id, code, name) {
            var nameArr = [], valueArr = [];
            for (var i = 0; i < value.length; i++) {
                nameArr.push('['+value[i][code]+']'+value[i][name]);
                valueArr.push(value[i][id]);
            }
            $('#rem_db_version_select_pop').val(nameArr.join(','));
            $('#rem_db_version').val(valueArr.join(','));
            if (selectPopWindowrem_db_version.dialog != null) {
                selectPopWindowrem_db_version.dialog.close();
            }
        }
    };
    
    $('#rem_db_version_select_pop,#rem_db_version_select_img').click(function() {
        if($("#rem_cp_id").val()==""){
            BUI.Message.Alert("先选择产品信息", "error");
            return;
        }
        selectPopWindowrem_db_version.dialog = new ESUI.PopSelectWindow('?app_act=common/select/edition&cpid='+$("#rem_cp_id").val(), 'selectPopWindowrem_db_version.callback', {title: '选择版本', width:800, height:500 ,ES_pFrmId:"<?php echo $request['ES_frmId'] ?>" }).show();
    });
</script>