<?php
render_control('PageHead', 'head1',
    array('title' => 'IWMS切换奇门删除功能',
          'ref_table' => 'table'
    ));

render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
    'id' => 'btn-search',
),
    'fields' => array(
       array('label' => 'WMS配置名称',
            'title' => '',
            'type' => 'input',
            'id' => 'erp_config_name',
        ),
    )
));
$button = array(
  
            array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'confirm' => '确定要删除这条辛辛苦苦录入的数据吗？'),
        );
$ex_button = array(array('id' => 'upload_goods', 'title' => '下发商品', 'act' => 'sys/wms_config/add_upload_goods', 'show_name' => '下发商品', 'show_cond'=>'obj.wms_system_code=="qimen"'));
$param = load_model('sys/SysParamsModel')->get_val_by_code('wms_split_goods_source');
$arr = $param['wms_split_goods_source'] == 1 ? array_merge($button, $ex_button): $button;

render_control('DataTable', 'table', array('conf' => array('list' => array(
	
    array('type' => 'button',
        'show' => 1,
        'title' => '操作',
        'field' => '_operate',
        'width' => '200',
        'align' => '',
        'buttons' => $arr
    ),
	array('type' => 'text',
        'show' => 1,
        'title' => 'WMS配置名称',
        'field' => 'wms_config_name',
        'width' => '200',
        'align' => '',
    ),

)
),
    'dataset' => 'sys/WmsConfigModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'wms_config_id',
));

?>

<script type="text/javascript">
function do_delete (_index, row) {
     
        if(row.wms_system_code=='iwms'||row.wms_system_code=='iwmscloud'){
 
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('sys/wms_config/do_new_delete');
?>', data: {id: row.wms_config_id},
    success: function(ret) {
    	var type = ret.status == 1 ? 'success' : 'error';
    	if (type == 'success') {
        BUI.Message.Alert('删除成功', type);
        tableStore.load();
    	} else {
        BUI.Message.Alert(ret.message, type);
    	}
    }
	});
        }else{   
             alert('仅支持百胜iWMS和胜景WMS365删除');
             return ;
        }
}


function do_enable(_index, row) {
	_do_set_active(_index, row, 'enable');
}
function do_disable(_index, row) {
	_do_set_active(_index, row, 'disable');
}
function no_edit(_index, row){
      BUI.Message.Alert('开启批次不允许设置WMS参数!','error');
}
function _do_set_active(_index, row, active) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('sys/erp_config/update_active');

?>',
    data: {id: row.erp_config_id, type: active},
    success: function(ret) {
    	var type = ret.status == 1 ? 'success' : 'error';
    	if (type == 'success') {
        BUI.Message.Alert(ret.message, type);
        tableStore.load();
    	} else {
        BUI.Message.Alert(ret.message, type);
    	}
    }
	});
}

parent._reload_page = function(){
    tableStore.load();
}

$(function(){
	$(".control-label").css("width","110px");
})

</script>