<style>
.status_btn{ border:1px solid #efefef; background:#FFF; color:#666; margin-right:2px; border-radius:3px;}
#table_list{ margin-top:8px;}
#service_intro a:hover{TEXT-DECORATION:underline}
#service_intro a{color:red}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '订单合并规则',
    'links' => array(
    ),
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
	                'title' => '规则说明',
	                'field' => 'rule_desc',
	                'width' => '600',
	                'align' => '',
	            ),
	  
	            array(
	                'type' => 'text',
	                'show' => 1,
	                'title' => '适用场景',
	                'field' => 'rule_value_html',
	                'width' => '250',
	                'align' => '',
	            ),
	        )
	    ),
	   'dataset' => 'oms/OrderCombineStrategyModel::get_by_page',
	    'idField' => 'id',
	   // 'params' => array('filter' => array('type' => $response['type'])),
	
	));
?>
<div style="clear: all"></div>
<div style="font-size:12px;color:red;margin-top:30px; border:1px sold;">
温馨提示：<br>
1.系统订单合并有两个应用场景，一个是合并订单列表中，手工合并订单；另一种是开启系统的自动服务，系统自动完成订单合并；
</div>


<script type="text/javascript">

function changeType(id,value,type) {

	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('oms/order_combine_strategy/update_active');?>',
    data: {id: id, value: value,type:type},
    success: function(ret) {
    	var type = ret.status == 1 ? 'success' : 'error';
                      
    	if (type == 'success') {

                   BUI.Message.Tip(ret.message,type);

    	} else {
                tableStore.load();

        	BUI.Message.Alert(ret.message,type );
    	}
    }

	});
}
	
       $(function(){

        tableStore.on('load',function(ex){
            $('#table input[type="checkbox"]').click(function(){
               var value =  $(this).attr('checked')?1:0;
              var  id = $(this).attr('name');
              var type  = $(this).hasClass('rule_status_value')?'rule_status_value':'rule_scene_value';
              
                changeType(id,value,type);
                
            });
        });
       });
        
        
        
      
        
        
        
</script>