<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '查看授权',
	'links'=>array(
		array('url'=>'clients/shopwarrant/do_list','title'=>'店铺授权列表')
	)
));?>
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left">基本信息</h3>
    </div>
<div class="panel-body">
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
                        array('title'=>'产品', 'type'=>'select', 'field'=>'sw_cp_id','data'=>ds_get_select('chanpin'),'edit_scene'=>'add',),
                        array('title'=>'客户', 'type'=>'select_pop', 'field'=>'sw_kh_id', 'select'=>'clients/clientinfo','show_scene'=>'add',),
			array('title'=>'客户', 'type'=>'input', 'field'=>'sw_kh_id_name','edit_scene'=>'','show_scene'=>'view,edit'),
                        array('title'=>'平台', 'type'=>'select', 'field'=>'sw_pt_id','data'=>ds_get_select('shop_platform'),'edit_scene'=>'add',),
			array('title'=>'店铺', 'type'=>'select_pop', 'field'=>'sw_sd_id','select'=>'clients/shopinfo','show_scene'=>'add','eventtype'=>'custom'),
                        array('title'=>'店铺', 'type'=>'input', 'field'=>'sw_sd_id_name','edit_scene'=>'','show_scene'=>'view,edit'),
			array('title'=>'店铺SESSION', 'type'=>'input', 'field'=>'sw_shop_session',),
                        array('title'=>'更新时间', 'type'=>'input', 'field'=>'sw_update_date','show_scene'=>'view'),
                        array('title'=>'到期时间', 'type'=>'time', 'field'=>'sw_valid_date',),
		), 
		'hidden_fields'=>array(array('field'=>'sw_id'),), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
        'col'=>'2',
	'act_edit'=>'clients/shopwarrant/do_edit', //edit,add,view
	'act_add'=>'clients/shopwarrant/do_add',
	'data'=>$response['data'],
        'rules'=>array(
            array('sw_cp_id', 'require'), 
            array('sw_kh_id', 'require'), 
            array('sw_pt_id', 'require'), 
            array('sw_sd_id', 'require'), 
            array('sw_shop_session', 'require'), 
            array('sw_valid_date', 'require'), 
            ) ,        //有效性验证
)); ?>
    </div>
</div>

<div>
<?php
 if($app['scene']!="add" && $app['scene']!="edit"){
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '日志ID',
                'field' => 'sl_id',
                'width' => '300',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '刷新时间',
                'field' => 'sw_update_date',
                'width' => '300',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => 'SESSION值',
                'field' => 'sl_shop_session',
                'width' => '300',
                'align' => '' ,
                ),

        ) 
    ),
    'dataset' => 'clients/ShopwarrantModel::get_shop_warrant_log',
    'params' => array('filter'=>array('id'=>$request['_id'])),
    'queryBy' => 'searchForm',
    'idField' => 'sl_id',
) );
 }
?>
</div>
<script type="text/javascript">
    
    //绑定店铺-客户选择事件
    $("#sw_kh_id").change(function(){
       //清空店铺
       $("#sw_sd_id_select_pop").val('');
       $("#sw_sd_id").val();
    });
    
    var selectPopWindowsw_sd_id = {
        dialog: null,
        callback: function (value, id, code, name) {
            var nameArr = [], valueArr = [];
            for (var i = 0; i < value.length; i++) {
                nameArr.push('['+value[i][code]+']'+value[i][name]);
                valueArr.push(value[i][id]);
            }
            $('#sw_sd_id_select_pop').val(nameArr.join(','));
            $('#sw_sd_id').val(valueArr.join(','));
            if (selectPopWindowsw_sd_id.dialog != null) {
                selectPopWindowsw_sd_id.dialog.close();
            }
        }
    };
    $('#sw_sd_id_select_pop,#sw_sd_id_select_img').click(function() {
        if($("#sw_kh_id").val()==""){
            BUI.Message.Alert("先选择客户", "error");
            return;
        }
        if($("#sw_pt_id").val()==""){
            BUI.Message.Alert("先选择平台", "error");
            return;
        }
        selectPopWindowsw_sd_id.dialog = new ESUI.PopSelectWindow('?app_act=common/select/shopinfo&khid='+$("#sw_kh_id").val()+'&ptid='+$("#sw_pt_id").val(), 'selectPopWindowsw_sd_id.callback', {title: '客户店铺', width: 900, height:500 ,ES_pFrmId:"<?php echo $request['ES_frmId'] ?>" }).show();
    });
</script>