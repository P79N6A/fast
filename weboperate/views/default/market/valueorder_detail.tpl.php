<?php render_control('PageHead', 'head1', array(
    'title'=>isset($app['title']) ? $app['title'] : '编辑产品订购',
    'links'=> array(
            array('url'=>'market/valueorder/do_list','title'=>'增值订购列表')
        )
));?>
<?php $view_fields = array(
                        array('title'=>'订购编号', 'type'=>'input', 'field'=>'val_num','edit_scene'=>'add','show_scene'=>'view'),
                        array('title'=>'销售渠道', 'type'=>'select_pop', 'field'=>'val_channel_id','select'=>'basedata/sellchannel','selecttype'=>'tree','show_scene'=>'add,edit'),
                        array('title'=>'销售渠道', 'type'=>'input', 'field'=>'val_channel_id_name','show_scene'=>'view'),
                        array('title'=>'客户名称', 'type'=>'select_pop', 'field'=>'val_kh_id', 'select'=>'clients/clientinfo','show_scene'=>'add,edit'),
                        array('title'=>'客户名称', 'type'=>'input', 'field'=>'val_kh_id_name','show_scene'=>'view','edit_scene'=>''),
                        array('title'=>'产品名称', 'type'=>'select', 'field'=>'val_cp_id','data'=>ds_get_select('chanpin',2)),
                        array('title'=>'产品版本',  'type'=>'select', 'field'=>'val_pt_version','data' => ds_get_select_by_field('product_version', 2)),
                        array('title'=>'增值服务', 'type'=>'select_pop', 'field'=>'val_serverid','select'=>'market/valueserver','show_scene'=>'add,edit','eventtype'=>'custom'),
                        array('title'=>'增值服务', 'type'=>'input', 'field'=>'val_serverid_name','show_scene'=>'view','edit_scene'=>''),
                        array('title'=>'标准价格', 'type'=>'input', 'field'=>'val_standard_price','remark'=>'元'),
                        array('title'=>'让利', 'type'=>'input', 'field'=>'val_cheap_price','remark'=>'元' ),
                        array('title'=>'实际价格', 'type'=>'input', 'field'=>'val_actual_price', 'remark'=>'元'),
                        array('title'=>'使用周期', 'type'=>'input', 'field'=>'val_hire_limit','remark'=>'月'),
                        array('title'=>'销售经理', 'type'=>'select_pop', 'field'=>'val_seller', 'select'=>'sys/orguser','show_scene'=>'add,edit'),
                        array('title'=>'销售经理', 'type'=>'input', 'field'=>'val_seller_name','show_scene'=>'view'),
                        array('title'=>'付款状态', 'type'=>'checkbox', 'field'=>'val_pay_status', 'show_scene'=>'view'),
                        array('title'=>'付款时间', 'type'=>'input', 'field'=>'val_paydate', 'show_scene'=>'view'),
                        array('title'=>'审核状态', 'type'=>'checkbox', 'field'=>'val_check_status', 'show_scene'=>'view'),
                        array('title'=>'审核时间', 'type'=>'input', 'field'=>'val_checkdate', 'show_scene'=>'view'),
                        array('title'=>'订购日期', 'type'=>'input', 'field'=>'val_orderdate', 'show_scene'=>'view'),
                        array('title'=>'描述', 'type'=>'textarea', 'field'=>'val_desc', ),
        		) ?>
<?php if(isset($response['data']['api'])) array_push($view_fields, array('title'=>'密钥', 'type'=>'input', 'field' => 'api'));?>
<?php if(isset($response['data']['api']) && empty($response['data']['api'])): ?>
<form  class="form-horizontal" id="form1" action="<?php echo '?app_act=market/valueorder/openapi&val_kh_id='.$response['data']['val_kh_id']; ?>" method="post">
<div style="width:99%;margin-left:5px;text-align:right;margin-bottom:10px">
    <input type="submit" id="submit" name="submit"  value=" 生成api " class="button button-primary" />
</div>
<?php endif; ?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=> $view_fields,      
		'hidden_fields'=>array(array('field'=>'val_num'),), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
        'col'=>2,
	'act_edit'=>'market/valueorder/valorders_edit', //edit,add,view
	'act_add'=>'market/valueorder/valorders_add',
	'data'=>$response['data'],
        'rules'=>array(
                    array('val_channel_id', 'require'), 
                    array('val_kh_id', 'require'),
                    array('val_cp_id', 'require'),
                    array('val_pt_version', 'require'),
                    array('value_cp_id','require'),
                    array('val_serverid','require'),
                    array('val_cheap_price','number'),
                    array('val_hire_limit','number'))  
)); ?>
<script type="text/javascript">
    
    $("#val_standard_price").val(0);
    $("#val_standard_price").attr("readonly", true);
    $("#val_actual_price").val(0);
    $("#val_actual_price").attr("readonly",true);
    
    $("#val_cheap_price").change(function(){
        var price1 = $("#val_standard_price").val();
        var price2 = $("#val_cheap_price").val();
        var real_price = parseFloat(price1)-parseFloat(price2);
        $("#val_actual_price").val(real_price);
    });
    
    $("#val_cp_id").change(function(){
       $("#val_serverid_select_pop").val('');
       $("#val_serverid").val();
    });
    
    var selectPopWindowval_serverid = {
    dialog: null,
    callback: function (value, id, code, name) {
            var nameArr = [], valueArr = [];
            for (var i = 0; i < value.length; i++) {
                nameArr.push('['+value[i][code]+']'+value[i][name]);
                valueArr.push(value[i][id]);
            }
            $('#val_serverid_select_pop').val(nameArr.join(','));
            $('#val_serverid').val(valueArr.join(','));
            $('#val_standard_price').val(value[0]["value_price"]);
            $('#val_actual_price').val(value[0]["value_price"]);
            $('#val_cheap_price').val(0);
            $('#val_hire_limit').val(value[0]["value_cycle"]);
            $('#val_desc').val(value[0]["value_desc"]);
            if (selectPopWindowval_serverid.dialog != null) {
                selectPopWindowval_serverid.dialog.close();
            }
        }
    };

    $('#val_serverid_select_pop,#val_serverid_select_img').click(function() {
        if($("#val_cp_id").val()==""){
            BUI.Message.Alert("请选择产品信息", "error");
            return;
        }
//        if($("#val_pt_version").val()==""){
//            BUI.Message.Alert("请选择产品版本信息", "error");
//            return;
//        }
        var cpid = $("#val_cp_id").val();
        //var cpversion = $("#val_pt_version").val();
        selectPopWindowval_serverid.dialog = new ESUI.PopSelectWindow('?app_act=common/select/valueserver&cpid='+cpid+'&enable=1', 'selectPopWindowval_serverid.callback', {title: '增值服务', width: 900, height:500 ,ES_pFrmId:"<?php echo $request['ES_frmId'] ?>" }).show();
    });
</script>