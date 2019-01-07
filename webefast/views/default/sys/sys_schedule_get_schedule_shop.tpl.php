<?php render_control('PageHead', 'head1',
	array('title' => '设置店铺',
		'ref_table' => 'table',
	));
?>




  <div class="demo-content">
    <div class="row">
      <div class="span16">
        <div id="grid">
          
        </div>
      </div>
    </div>




   <script type="text/javascript">
        //BUI.use(['bui/grid','bui/data'],function(Grid,Data){
        //    var Store = Data.Store,
        var Grid = BUI.Grid,
 Store = BUI.Data.Store,
 columns = [
            {title : '店铺',dataIndex :'shop_name', width:300},
            {title : '开启服务',dataIndex : 'd',width:200,renderer : function (value,obj) {
               var status_str = '启动状态';
              if(obj.status == 1){
                   status_str = '关闭状态';
              }
               return '<span class="grid-command "  row_id="'+obj.shop_id+'">'+status_str+'</span>';
           
            }}
          ],
          data = <?php echo json_encode($response['shop_list'])?>;
 
        var store = new Store({
            data : data
          }),
          grid = new Grid.Grid({
            render:'#grid',
            width:'510',
            forceFit : true,
            columns : columns,
            store : store
          });
 
        grid.render();
      
        grid.on('cellclick',function  (ev) {
	//var rowRet = tableGetTableRowByChild(this),
	//	  row = rowRet[1];
	
            target = $(ev.domTarget); //点击的元素
          if(target.hasClass('grid-command')){
           set_status(target);
          }

 
        });
        
     // });
     
     
         function set_status(obj) {  
        var shop_id = obj.attr('row_id');
        var id = $("#sys_schedule_id").val();
        var status = obj.text()=='开启状态'?1:0;
        $.ajax({ type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('sys/sys_schedule/set_schedule_shop'); ?>',
            data: {id: id,shop_id:shop_id, status: status},
            success: function(ret) { 
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                obj.text(status==0?'开启状态':'关闭状态')
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    </script>







<input type="hidden" id="sys_schedule_id" value="<?php echo  $response['id'];?>">






