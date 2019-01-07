<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '产品授权详情',
	'links'=>array(
		array('url'=>'products/productorderauth/do_list',title=>'产品订购授权列表')
	)
));?>
<style>
    .panel-body {padding: 2px;}
    .panel-body table {margin: 0; }
</style>
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left">订购授权详情</h3>
    </div>
<div class="panel-body">   
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
                        array('title'=>'客户名称', 'type'=>'input', 'field'=>'pra_kh_id_name'),
                        array('title'=>'产品名称', 'type'=>'input', 'field'=>'pra_cp_id_name',),
                        array('title'=>'产品版本',  'type'=>'select', 'field'=>'pra_product_version','data' => ds_get_select_by_field('product_version', 2)),
                        array('title'=>'授权KEY', 'type'=>'input', 'field'=>'pra_authkey',),
                        array('title'=>'授权点数', 'type'=>'input', 'field'=>'pra_authnum',),
                        array('title'=>'产品订购编号', 'type'=>'input', 'field'=>'pra_pro_num',),
                        array('title'=>'店铺总数量', 'type'=>'input', 'field'=>'pra_shopnum',),
                        array('title'=>'开始时间', 'type'=>'input', 'field'=>'pra_startdate',),
                        array('title'=>'结束时间', 'type'=>'input', 'field'=>'pra_enddate','data'=>ds_get_select_by_field('pay_type',2)),
                        array('title'=>'授权状态', 'type'=>'checkbox', 'field'=>'pra_state', ),
                        ),      
		'hidden_fields'=>array(array('field'=>'pra_id')), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
        'col'=>2,
	'act_edit'=>'products/productorderauth/do_edit', //edit,add,view
	'act_add'=>'products/productorderauth/do_add',
	'data'=>$response['data'],
)); ?>
      <div class="row">			
            <div class="control-group span20">
                <label class="control-label span3" style=" text-align: right;">登录地址： </label>
                   
		<div class="controls bui-form-group " >
                    <input type="text" style="width:580px" name="pra_serverpath" id="pra_serverpath" class="field" value="<?php echo $response['data']['pra_serverpath'];?>"/>
                    <button id="up_url" class="button button-primary" type="button">更新地址</button>
                </div>
		</div>
			</div>
    </div>
    
  
    
    
</div>
<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '授权平台', 'active' => true), // 默认选中active=true的页签
    ),
    'for' => 'TabPageContents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<div id="TabPageContents">
    <div class="panel">
        <div class="panel-body">
            <?php
            render_control('DataTable', 'table1', array(
                'conf' => array(
                    'list' => array(
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '平台名称',
                            'field' => 'pra_shop_pfid_name',
                            'width' => '150',
                            'align' => '',
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '店铺数量',
                            'field' => 'pra_shop_num',
                            'width' => '150',
                            'align' => '',
                        ),
                    )
                ),
                'dataset' => 'products/ProductorderauthModel::get_ptshop_num',
                'params' => array('filter' => array('pra_id' => $request['_id'])),
                'idField' => 'pra_shop_pfid',
                'CheckSelection' => false,
            ));
            ?>
        </div>
    </div>
</div>


<script type="text/javascript">
function pt_logoUploader_success(result) {
	var url = '<?php echo get_app_url('common/file/img')?>&f='+$.parseJSON(result.data.path)[0];
	$('#pt_logoUploader .bui-queue-item-success .success').html('<img src="'+url+'" style="width:100px; height:100px"/>')
}

$(function(){
    $('#up_url').click(function(){
        var url = "?app_act=products/productorderauth/update_server&app_fmt=json";
        var param = {};
        
         param.pra_id = $('#pra_id').val();
        param.pra_serverpath = $('#pra_serverpath').val();
        $.post(url,param,function(ret){
            if(ret.status>0){
                BUI.Message.Alert('更新成功','info');
            }else{
                 BUI.Message.Alert(ret.message,'error');
            }

        },'json');
        
        
    });
});


</script>