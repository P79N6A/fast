<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '产品订购详细',
	'links'=>array(
		array('url'=>'market/productorder/do_list','title'=>'产品订购列表')
	)
));?>
<?php 

$product_area_data = ds_get_select_by_field('product_area',1);
$product_area_data[0] = array('','请选择业务范围');
render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
                        array('title'=>'订购编号', 'type'=>'input', 'field'=>'pro_num','edit_scene'=>'add','show_scene'=>'view' ),
                        array('title'=>'销售渠道', 'type'=>'select_pop', 'field'=>'pro_channel_id','select'=>'basedata/sellchannel','selecttype'=>'tree','show_scene'=>'add,edit'),
                        array('title'=>'销售渠道', 'type'=>'input', 'field'=>'pro_channel_id_name','show_scene'=>'view'),
                        array('title'=>'客户名称', 'type'=>'select_pop', 'field'=>'pro_kh_id', 'select'=>'clients/clientinfo','show_scene'=>'add,edit'),
                        array('title'=>'客户名称', 'type'=>'input', 'field'=>'pro_kh_id_name','show_scene'=>'view','edit_scene'=>''),
                        array('title'=>'产品名称',  'type'=>'select', 'field'=>'pro_cp_id','data'=>ds_get_select('chanpin',2)),
                        array('title'=>'营销类型', 'type'=>'select', 'field'=>'pro_st_id','data'=>ds_get_select('market',2) ),
                        array('title'=>'产品版本',  'type'=>'select', 'field'=>'pro_product_area','data' => $product_area_data),
                        array('title'=>'',  'type'=>'select', 'field'=>'pro_product_version','data' => array(array('','请选择具体版本'))),
            
//                        array('title'=>'报价方案', 'type'=>'select_pop', 'field'=>'pro_price_id', 'select'=>'market/planprice','show_scene'=>'add,edit'),
                        array('title'=>'报价方案', 'type'=>'select_pop', 'field'=>'pro_price_id','show_scene'=>'add,edit','eventtype'=>'custom'),
                        array('title'=>'报价方案', 'type'=>'input', 'field'=>'pro_price_id_name','show_scene'=>'view','edit_scene'=>''),
                        array('title'=>'标准售价', 'type'=>'input', 'field'=>'pro_sell_price', ),
                        array('title'=>'折扣价', 'type'=>'input', 'field'=>'pro_rebate_price', ),
                        array('title'=>'实际价格', 'type'=>'input', 'field'=>'pro_real_price', ),
                        array('title'=>'租用期限', 'type'=>'input', 'field'=>'pro_hire_limit','remark'=>'月'),
                        array('title'=>'点数', 'type'=>'input', 'field'=>'pro_dot_num', ),
                        array('title'=>'销售经理', 'type'=>'select_pop', 'field'=>'pro_seller', 'select'=>'sys/orguser','show_scene'=>'add,edit'),
                        array('title'=>'销售经理', 'type'=>'input', 'field'=>'pro_seller_name','show_scene'=>'view'),
                        array('title'=>'付款状态', 'type'=>'checkbox', 'field'=>'pro_pay_status', 'show_scene'=>'view'),
                        array('title'=>'付款日期', 'type'=>'input', 'field'=>'pro_paydate','show_scene'=>'view'),
                        array('title'=>'审核状态', 'type'=>'checkbox', 'field'=>'pro_check_status', 'show_scene'=>'view'),
                        array('title'=>'审核日期', 'type'=>'input', 'field'=>'pro_checkdate', 'show_scene'=>'view'),
                        array('title'=>'订购日期', 'type'=>'input', 'field'=>'pro_orderdate', 'show_scene'=>'view'),
                       array('title'=>'订购淘宝应用', 'type'=>'select', 'field'=>'pro_app_key','data'=>$response['taobao_app'] ,),
                        array('title'=>'描述', 'type'=>'input', 'field'=>'pro_desc', ),
        		),      
		'hidden_fields'=>array(array('field'=>'pro_num')), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
        'col'=>2,
	'act_edit'=>'market/productorder/porders_edit', //edit,add,view
	'act_add'=>'market/productorder/porders_add',
	'data'=>$response['data'],
        'rules'=>'market/productorder',  
)); ?>


<script type="text/javascript">
    
    var acttype = "<?php echo $app["scene"] ?>";
    if (acttype == "add") {
        $("#pro_sell_price").val(0);
        $("#pro_sell_price").attr("readonly", true);
        $("#pro_real_price").val(0);
        $("#pro_real_price").attr("readonly", true);
    } else if (acttype == "edit") {
        var pro_product_area='<?php echo $response['data']['pro_product_area']?>';
        var pro_product_version='<?php echo $response['data']['pro_product_version']?>';
        $("#pro_sell_price").attr("readonly", true);
        $("#pro_real_price").attr("readonly", true);
        $("#pro_product_area").val(pro_product_area);
        var product_version_arr = [{},{1:'标准版',2:'企业版',3:'旗舰版'},{1:'标准版',2:'企业版'},{2:'企业版'}];
        var data = product_version_arr[pro_product_area] ;
        $('#pro_product_version option').remove();
        $('#pro_product_version').append('<option value="">请选择具体版本</option>');
        for(var i in data){
            $('#pro_product_version').append('<option value="'+i+'">'+data[i]+'</option>');
        }
        $("#pro_product_version").val(pro_product_version);
    }

    $("#pro_rebate_price").change(function(){
        var price1 = $("#pro_sell_price").val();
        var price2 = $("#pro_rebate_price").val();
        var real_price = parseFloat(price1)-parseFloat(price2);
            $("#pro_real_price").val(real_price);
    });
    
    //绑定问题提单-版本号选择事件
    $("#pro_cp_id").change(function(){
       //清空报价方案
       $("#pro_price_id_select_pop").val('');
       $("#pro_price_id").val();
    });
    
    $("#pro_product_area").change(function(){
        var product_version_arr = [{},{1:'标准版',2:'企业版',3:'旗舰版'},{1:'标准版',2:'企业版'},{2:'企业版'}];
        var product_area = $(this).val();
        var data = product_version_arr[product_area] ;
        $('#pro_product_version option').remove();
        $('#pro_product_version').append('<option value="">请选择具体版本</option>');
        for(var i in data){
            $('#pro_product_version').append('<option value="'+i+'">'+data[i]+'</option>');
        }
    });
    
    $("#pro_st_id").change(function(){
       //清空报价方案
       $("#pro_price_id_select_pop").val('');
       $("#pro_price_id").val();
       $("#pro_hire_limit").removeAttr("disabled");
    });

    var selectPopWindowpro_price_id = {
    dialog: null,
    callback: function (value, id, code, name) {
        var nameArr = [], valueArr = [];
        for (var i = 0; i < value.length; i++) {
            nameArr.push('['+value[i][code]+']'+value[i][name]);
            valueArr.push(value[i][id]);
        }
        $('#pro_price_id_select_pop').val(nameArr.join(','));
        $('#pro_price_id').val(valueArr.join(','));
        $('#pro_sell_price').val(value[0]["price_base"]);
        $('#pro_real_price').val(value[0]["price_base"]);
        $('#pro_rebate_price').val(0);
        $('#pro_dot_num').val(value[0]["price_dot"]);
        $('#pro_desc').val(value[0]["price_note"]);
        if (selectPopWindowpro_price_id.dialog != null) {
            selectPopWindowpro_price_id.dialog.close();
        }
    }
};

$('#pro_price_id_select_pop,#pro_price_id_select_img').click(function() {
    if($("#pro_cp_id").val()==""){
        BUI.Message.Alert("请选择产品信息", "error");
        return;
    }else if($("#pro_st_id").val()==""){
        BUI.Message.Alert("请选择营销类型", "error");
        return;
    }
    var cpid = $("#pro_cp_id").val();
    var stid = $("#pro_st_id").val();
    if (stid == "1"){
         $("#pro_hire_limit").attr("disabled", "disabled");
    }
    selectPopWindowpro_price_id.dialog = new ESUI.PopSelectWindow('?app_act=common/select/market_price&cpid='+cpid+'&stid='+stid, 'selectPopWindowpro_price_id.callback', {title: '报价方案', width: 900, height:500 ,ES_pFrmId:"<?php echo $request['ES_frmId'] ?>" }).show();
});

    












    
</script>

