<style>
    .red{
    background: #fe0000 none repeat scroll 0 0;
    border-radius: 50%;
    display: inline-block;
    height: 15px;
   width: 15px;
    position: absolute;
    right: 40px;
    top: 8px; 
    }
     .green{
    background: #0087e8 none repeat scroll 0 0;
    border-radius: 50%;
    display: inline-block;
    height: 15px;
   width: 15px;
    position: absolute;
    right: 40px;
    top: 8px;
    }   
</style>

   <div class="row">
      <div class="span16">
        <div id="grid">
          
        </div>
      </div>


 <script type="text/javascript">
     $(function(){
         
         

        BUI.use('bui/grid',function(Grid){
            var Grid = Grid,
          columns = [
            {title : '',dataIndex :'title', width:'20%'},
            {title : '',dataIndex :'show_tip', width:'10%'},
            {title : '',dataIndex : 'desc',width:'70%'}
          ],
          data = <?php echo json_encode( $response['data']);?>;
 
        var grid = new Grid.SimpleGrid({
          render:'#grid',
          columns : columns,
          items : data,
          idField : 'title'
        });
 
        grid.render();
        
       $('#grid table>thead').hide();
       var td = $('#grid table>tbody>tr>td');
       td.eq(0).width(100);
        td.eq(1).width(100);
         td.eq(2).width(500);
        
      });
     });
    
    function link(type){
  window.location.href = "?app_act=moniter/mo_shop/do_list&mo_type="+type;
    }

</script>