<style type="text/css">
    .well {
        min-height: 100px;
    }
</style>

<?php

render_control('PageHead', 'head1', array('title' => '仓库适配策略',

    'ref_table' => 'table'
));
?>

<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
        
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '仓库代码',
            		'field' => 'store_code',
            		'width' => '100',
            		'align' => '',
            	
            ),
            
               array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '仓库名称',
            		'field' => 'store_name',
            		'width' => '120',
            		'align' => '',
            	
            ),
            
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '发货区域',
            		'field' => 'area_names',
            		'width' => '450',
            		'align' => '',
            ),
           
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '优先级',
            		'field' => 'sort',
            		'width' => '80',
            		'editor'=> "{xtype:'number'}"
                
            ),
         array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '120',
                'align' => '',
                'buttons' => array(
		                array(
		                		'id' => 'set_area',
		                		'title' => '区域设置',
		                		'callback' => 'set_area'
		                ),
                		array(
                				'id' => 'clear_area',
                				'title' => '清空',
                				'callback' => 'clear_area',
                		),
          
                    
                    
                ),
            ),
         
        )
    ),
    'dataset' => 'op/PolicyStoreModel::get_by_page',
  //  'queryBy' => 'searchForm',
    'idField' => 'policy_store_area_id',
  'CellEditing'=>true,
));
?>

<script>
function set_area(_index, row){

        openPage('<?php echo base64_encode('?app_act=op/policy_store/set_area') ?>'+row.store_code,'?app_act=op/policy_store/set_area&store_code='+row.store_code,'区域设置:'+row.store_name);

}
function clear_area(_index, row){
    BUI.Message.Confirm('清空已设置的发货区域，系统默认发货区域为全国，请确认',function(){
        var url = '?app_act=op/policy_store/clear_area&app_fmt=json';
        var data = {};
        data.store_code=row.store_code;
        $.post(url,data,function(ret){
            var type = ret.status == 1 ? 'success' : 'error';
            if(ret.status>0){
                tableStore.load();
            }
            BUI.Message.Tip(ret.message,type);
            
        },'json');
    },'question');
}
$(function(){
        if(typeof tableCellEditing != "undefined"){
	    //列表区域,数量修改回调操作 +++++++++++++++++++++++++++++++++++++++++++
    	tableCellEditing.on('accept', function (record, editor) {
	        $.post('?app_act=op/policy_store/set_sort',
	        	{store_code: record.record.store_code, sort: record.record.sort},
	            function (result) {
	                tableStore.load();
	            }, 'json');
	    });
    }  
});
</script>
