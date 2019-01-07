<?php render_control('PageHead', 'head1');?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
                    array('title'=>'RDS连接地址', 'type'=>'select_pop', 'field'=>'rem_db_pid','select'=>'products/dbextlinks','show_scene'=>'add,edit','eventtype'=>'custom'),
                    array('title'=>'数据库名称', 'type'=>'input', 'field'=>'rem_db_name', ),
//                    array('title'=>'绑定客户', 'type'=>'input', 'field'=>'rem_db_is_bindkh', ),
//                    array('title'=>'客户名称', 'type'=>'input', 'field'=>'rem_db_khid', ),
                    array('title'=>'RDS管理库', 'type'=>'checkbox', 'field'=>'rem_db_sys','remark'=>'数据库名称sysdb'),
                    array('title'=>'产品版本', 'type'=>'select', 'field'=>'rem_db_sys_version','data' => ds_get_select_by_field('product_version', 2)),
                    array('title'=>'系统版本', 'type'=>'select_pop', 'field'=>'rem_db_version','select'=>'products/edition','show_scene'=>'add,edit','eventtype'=>'custom'),
                    ),
                'hidden_fields'=>array(array('field'=>'cpid')), 
	), 
	'buttons'=>array(
			array('label'=>'确认', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
   ),
	'act_add'=>'products/dbextmanage/do_add_dbextmanage',
	'data'=>$response['data'], 
        'rules'=>array(
                array('rem_db_pid', 'require'), 
                array('rem_db_name', 'require'),
                array('rem_db_sys_version','require')),
        'event'=>array('beforesubmit'=>'formBeforesubmit'),
)); 
?>
<script type="text/javascript">
        
    function formBeforesubmit() {
        if(!$("#rem_db_sys").attr("checked")){
            if($("#rem_db_version").val()==''){
                BUI.Message.Alert("版本不能为空","error");
                return false;
            }
        }
	return true; // 如果不想让表单继续提交，则return false
    }
    
    //绑定rds连接地址
    $("#rem_db_pid").change(function(){
       //清空版本号
       $("#rem_db_version_select_pop").val('');
       $("#rem_db_version").val();
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
    if($("#cpid").val()==""){
        BUI.Message.Alert("请先选择RDS连接信息", "error");
        return;
    }else{
        selectPopWindowrem_db_version.dialog = new ESUI.PopSelectWindow('?app_act=common/select/edition&cpid='+$("#cpid").val(), 'selectPopWindowrem_db_version.callback', {title: '版本号', width: 900, height:500 ,ES_pFrmId:"<?php echo $request['ES_frmId'] ?>" }).show();
    }
    });
      
      
      
      
    var selectPopWindowrem_db_pid = {
    dialog: null,
    callback: function (value, id, code, name) {
        var nameArr = [], valueArr = [],cpArr=[];
        for (var i = 0; i < value.length; i++) {
            nameArr.push('['+value[i][code]+']'+value[i][name]);
            valueArr.push(value[i][id]);
            cpArr.push(value[i]["rem_cp_id"]);
        }
        $('#rem_db_pid_select_pop').val(nameArr.join(','));
        $('#rem_db_pid').val(valueArr.join(','));
        $('#cpid').val(cpArr.join(','));
        if (selectPopWindowrem_db_pid.dialog != null) {
            selectPopWindowrem_db_pid.dialog.close();
        }
    }
    };  
    $('#rem_db_pid_select_pop,#rem_db_pid_select_img').click(function() {
        selectPopWindowrem_db_pid.dialog = new ESUI.PopSelectWindow('?app_act=common/select/dbextlinks', 'selectPopWindowrem_db_pid.callback', {title: 'RDS连接信息', width: 900, height:500 ,ES_pFrmId:"<?php echo $request['ES_frmId'] ?>" }).show();
    });
    
    $('#rem_db_sys').click(function() {
        if(!$("#rem_db_sys").attr("checked")){
            $("#rem_db_name").val('');
            $("#rem_db_name").attr('readonly',false);
        }else{
            $("#rem_db_name").val('sysdb');
            $("#rem_db_name").attr('readonly',true);
        }
    });

</script>
