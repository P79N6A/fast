<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '查看主机明细',
	'links'=>array(
		array('url'=>'products/vhost_manage/do_list','title'=>'主机信息')
	)
));?>

<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'所属主机IP', 'type'=>'select_pop', 'field'=>'asa_vm_id', 'select'=>'products/vhostinfo','show_scene'=>'add,edit'),
                        array('title'=>'所属主机IP', 'type'=>'input', 'field'=>'asa_vm_id_name','show_scene'=>'view','edit_scene'=>''),
                        array('title'=>'所属RDS地址', 'type'=>'select_pop', 'field'=>'asa_rds_id','select'=>'products/dbextlinks','show_scene'=>'add,edit','eventtype'=>'custom'),
                        array('title'=>'所属RDS地址', 'type'=>'input', 'field'=>'asa_rds_id_name','show_scene'=>'view','edit_scene'=>''),
                        array('title'=>'关联产品',  'type'=>'select', 'field'=>'asa_cp_id','data'=>ds_get_select('chanpin',2)),
                        array('title'=>'产品版本',  'type'=>'select', 'field'=>'asa_product_version','data' => ds_get_select_by_field('product_version', 2)),
                        array('title'=>'系统版本', 'type'=>'select', 'field'=>'asa_cp_version_id', 'data' => ds_get_select('issue_chanpin_version', 2)),
                        array('title'=>'创建日期','type'=>'input', 'field'=>'asa_createdate','edit_scene'=>'','show_scene'=>'view' ),
                        array('title'=>'修改日期', 'type'=>'input', 'field'=>'asa_updatedate','edit_scene'=>'','show_scene'=>'view' ),
                    ),      
		'hidden_fields'=>array(array('field'=>'asa_id')), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
        'col'=>2,
	'act_edit'=>'products/autotask_manage/do_atask_edit', //edit,add,view
	'act_add'=>'products/autotask_manage/do_atask_add',
	'data'=>$response['data'],
//        'rules'=>'products/add_vhost',        //对应方法在conf/validator/clients_conf.php,新建店铺必填字段验证。
)); ?>

<script>
      var selectPopWindowasa_rds_id = {
    dialog: null,
    callback: function (value, id, code, name) {
        var nameArr = [], valueArr = [],cpArr=[];
        for (var i = 0; i < value.length; i++) {
            nameArr.push('['+value[i][code]+']'+value[i][name]);
            valueArr.push(value[i][id]);
            cpArr.push(value[i]["rem_cp_id"]);
        }
        $('#asa_rds_id_select_pop').val(nameArr.join(','));
        $('#asa_rds_id').val(valueArr.join(','));
        $('#cpid').val(cpArr.join(','));
        if (selectPopWindowasa_rds_id.dialog != null) {
            selectPopWindowasa_rds_id.dialog.close();
        }
    }
    };  
    $('#asa_rds_id_select_pop,#asa_rds_id_select_img').click(function() {
        selectPopWindowasa_rds_id.dialog = new ESUI.PopSelectWindow('?app_act=common/select/dbextlinks', 'selectPopWindowasa_rds_id.callback', {title: 'RDS连接信息', width: 900, height:500 ,ES_pFrmId:"<?php echo $request['ES_frmId'] ?>" }).show();
    }); 
   
</script>

