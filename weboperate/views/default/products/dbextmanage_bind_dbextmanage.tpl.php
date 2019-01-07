<?php render_control('PageHead', 'head1');?>
<form  class="form-horizontal" id="form1" action="?app_act=products/dbextmanage/do_bind_dbextmanage" method="post">
<?php render_control('Form', 'form1', array(
        'noform'=>true,
	'conf'=>array(
		'fields'=>array(
                    array('title'=>'RDS连接地址', 'type'=>'input', 'field'=>'rem_rds_id_name','edit_scene'=>''),
                    array('title'=>'关联产品', 'type'=>'input', 'field'=>'rem_cp_id_name','edit_scene'=>''),
                    array('title'=>'数据库名称', 'type'=>'input', 'field'=>'rem_db_name','edit_scene'=>''),
                    array('title'=>'产品版本', 'type'=>'select', 'field'=>'rem_db_sys_version','data' => ds_get_select_by_field('product_version', 2),'edit_scene'=>''),
                    array('title'=>'客户名称', 'type'=>'select_pop', 'field'=>'rem_db_khid', 'select'=>'clients/clientinfo','show_scene'=>'add','eventtype'=>'custom'),
                ),
                'hidden_fields'=>array(array('field'=>'rem_db_id'),array('field'=>'rem_rds_id')), 
	), 
	'buttons'=>array(
			array('label'=>'确认', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
   ),
	'act_add'=>'products/dbextmanage/do_bind_dbextmanage',
	'data'=>$response['data'], 
        'rules'=>array(
                array('rem_db_khid', 'require'),),
        'event'=>array('beforesubmit'=>'formBeforesubmit'),
)); 
?>
</form>
<script type="text/javascript">
        
    var form =  new BUI.Form.HForm({
       srcNode : '#form1',
       submitType : 'ajax',
       callback : function(data){
            var type = data.status == 1 ? 'success' : 'error';
            if (data.status == 1) {
                ui_closePopWindow('<?php echo CTX()->request['ES_frmId']?>'); 
                window.location.reload();
            } else {
                BUI.Message.Alert(data.message, function() { }, type);
            }
        }
   }).render();
   
    function formBeforesubmit() {
	return true; // 如果不想让表单继续提交，则return false
    }
    
    var selectPopWindowrem_db_khid = {
        dialog: null,
        callback: function (value, id, code, name) {
            var nameArr = [], valueArr = [];
            for (var i = 0; i < value.length; i++) {
                nameArr.push('['+value[i][code]+']'+value[i][name]);
                valueArr.push(value[i][id]);
            }
            $('#rem_db_khid_select_pop').val(nameArr.join(','));
            $('#rem_db_khid').val(valueArr.join(','));
            if (selectPopWindowrem_db_khid.dialog != null) {
                selectPopWindowrem_db_khid.dialog.close();
            }
        }
    };
    
    $('#rem_db_khid_select_pop,#rem_db_khid_select_img').click(function() {
        selectPopWindowrem_db_khid.dialog = new ESUI.PopSelectWindow('?app_act=common/select/clientinfo&is_auth=1', 'selectPopWindowrem_db_khid.callback', {title: '选择客户', width: 900, height:500 ,ES_pFrmId:"<?php echo $request['ES_frmId'] ?>" }).show();
    });
</script>
