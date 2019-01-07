<style type="text/css">
.well {
    min-height: 100px;
}

#container2{
    border:1px solid #dddddd;
    border-collapse:collapse;
    width:100%;
    height:40px;
    text-align:center;
}

#container2 td {
    width:100px;
	
    height:30px;
    border:1px solid #dddddd;  
}
#inner {
    width:100%;
    height:100px;
    border:1px solid green;
    border-collapse:collapse;
}
.divcss5-right{width:320px; height:50px;border:0px solid #F00;float:right} 
</style>
<?php render_control('PageHead', 'head1',
		array('title'=>'淘宝商品管理',
		array('url'=>'prm/goods/detail&app_scene=add', 'title'=>'添加商品', ),
				
				'ref_table'=>'table'
));?>

 
<?php
//状态
$approve_status = array('' => '全部',
		'onsale' => '在售',
		'instock' => '在库',
);
$approve_status = array_from_dict($approve_status);
//库存同步
$is_synckc = array('' => '全部',
		'0' => '否',
		'1' => '是',
);
$is_synckc = array_from_dict($is_synckc);
//物流宝
$is_wlb = array('' => '全部',
		'0' => '否',
		'1' => '是',
         );
$is_wlb = array_from_dict($is_wlb);
render_control ( 'SearchForm', 'searchForm', array (
    'cmd' => array (
        'label' => '查询',
        'label' => '查询',
        'id' => 'btn-search' 
    ),
    'fields' => array (
		    array (
		    		'label' => '商品标题',
		    		'type' => 'input',
		    		'id' => 'title',
		    ),
		    array (
		    		'label' => '商品外部ID',
		    		'type' => 'input',
		    		'id' => 'outer_id',
		    ),
		    array ('label' => '状态',
		    		'type' => 'select',
		    		'id' => 'approve_status',
		    		'data' => $approve_status,
		    ),
		    array (
		    		'label' => '店铺',
		    		'type' => 'select_multi',
		    		'id' => 'shop_code',
		    		'data'=>$response['shop'],
		    ),
		    array (
		    		'label' => 'SKU ID',
		    		'type' => 'input',
		    		'id' => 'sku_id',
		    		
		    ),
		    array (
		    		'label' => '库存同步',
		    		'type' => 'select',
		    		'id' => 'is_synckc',
		    		'data'=>$is_synckc,
		    ),
		    array (
		    		'label' => 'sku关联',
		    		'type' => 'select',
		    		'id' => 'aa',
		    		'data'=>$response['prop'],
		    ),
		    array (
		    		'label' => '移除',
		    		'type' => 'select',
		    		'id' => 'aa',
		    		'data'=>$response['prop'],
		    ),
    		array (
    				'label' => '物流宝',
    				'type' => 'select',
    				'id' => 'is_wlb',
    				'data'=>$is_wlb,
    		),	
    ) 
) );
?>



<div id="table1">

 
<div class="bui-grid-body" style="width: 100%;">
<table id="container2">
      <tr >
                <td style="font-weight: bold;">商品标题</td>
                <td style="font-weight: bold;">商品外部ID</td>
                <td style="font-weight: bold;">商品数字ID</td>
				<td style="font-weight: bold;">状态</td>
                <td style="font-weight: bold;">价格</td>
                <td style="font-weight: bold;">库存同步</td>
                <td style="font-weight: bold;">上架</td>
              
               
                <td style="font-weight: bold;">SKU ID</td>
                <td style="font-weight: bold;">SKU 商家编码</td>
                <td style="font-weight: bold;">SKU 库存</td>
                <td style="font-weight: bold;">预售</td>
                <td style="font-weight: bold;">承诺发货天数</td>
                <td style="font-weight: bold;">承诺发货时间</td>
                <td style="font-weight: bold;">库存同步</td>
                <td style="font-weight: bold;">物流宝</td>
                <td style="font-weight: bold;">操作</td>
               
               
         </tr>
         <tbody id="taodata">
           
          </tbody>  
        </table>

</div>
<div class="divcss5-right pagebar"><div>
</div>

<script type="text/javascript">
  //$("#searchForm").attr("action","prm/BrandModel::get_by_page");
 
  get_grid('1');
  searchFormForm.on('beforesubmit',function(ev) {	
			get_grid('1');
			return false;
		});
	function get_grid(page){
		var param = "&"+$("#searchForm").serialize()+"&page="+page;
		$.ajax({ type: 'GET', dataType: 'json',
	        url: '<?php echo get_app_url('api/taobao_item/do_list_js');?>'+param, 
	        success: function(data) {
		        if(data.status == '1'){
		        	 var len = data.data.data.length;
			       	 var html = '';
		        	
		        	 for (var i = 0; i < len; i++) {
			        	 html += '<tr>';
			        	 html += '<td>'+data.data.data[i].title+'</td>';
			        	 html += '<td>'+data.data.data[i].outer_id+'</td>';
			        	 html += '<td>'+data.data.data[i].num_iid+'</td>';
			        	 html += '<td>'+data.data.data[i].approve_status+'</td>';
			        	 html += '<td>'+data.data.data[i].price+'</td>';
			        	 html += '<td></td>';
			        	 if(data.data.data[i].is_sale == '1'){
			        	 	html += '<td> <span class="icon-ok"></span></td>';
			        	 }else{
			        		 html += '<td> <span class="icon-remove-mini"></span></td>';
				         }
			        	 html += '<td colspan="9">';
			        	 if(data.data.data[i].xq){
				        	 var xq_len = data.data.data[i].xq.length;
				        	 html += '<table id="inner">';
				        	 for (var j = 0; j < xq_len; j++) {
					        	 arr = data.data.data[i].xq;
					        	 //alert(arr[j]);
					        	 html += '<tr>';
					        	 html += '<td>'+arr[j].sku_id+'</td>';
					        	 html += '<td></td>';
					        	 html += '<td>'+arr[j].quantity+'</td>';
					        	 html += '<td></td>';
					        	 html += '<td></td>';
					        	 html += '<td></td>';
					        	 html += '<td></td>';
					        	 if(arr[j].is_wlb == '1'){
					        		 html += '<td> <span class="icon-ok"></span></td>';
					        	 }else{
					        		 html += '<td> <span class="icon-remove-mini"></span></td>';
						         }
					        	 html += '<td><a href="#" onClick="store_synchro(\''+arr[j].id+'\');">库存同步</a>&nbsp;&nbsp;<a href="#" onClick="store_move(\''+arr[j].id+'\');">移除</a></td>';
					        	 html += '</tr>';
				        	 }
				        	 html += '</table>';
			        	 } 
			        	 html += '</td>';
			            
			        	 html += '</tr>';
				     }
			         $("#taodata").html(html);
			         
			         page = data.data.filter.page;
			         page_size = data.data.filter.page_size;
			         page_count = data.data.filter.page_count;
			         record_count = data.data.filter.record_count; 
			         if(page >= record_count){
			        	 nextpage = page;
				     }else{
			         	nextpage = page + 1;
				     }
				     if(page <= 1){
				    	 prepage = page;
					 }else{
						 prepage = page -1;
					 }
			         
				     htmlpage = '';
				     if(record_count <= page_size){
				    	 htmlpage +='<a href="#" >首页</a>&nbsp;&nbsp;<a href="#" >前一页</a>&nbsp;&nbsp;第 '+page+'页&nbsp;&nbsp;<a href="#" >下一页</a>&nbsp;&nbsp;<a href="#" >尾页</a>&nbsp;&nbsp;共'+record_count+'条记录' ;
					 }else{
						 htmlpage +='<a href="#" onClick="get_grid(\'1\');">首页</a>&nbsp;&nbsp;<a href="#" onClick="get_grid(\''+prepage+'\');">前一页</a>&nbsp;&nbsp;第 '+page+'页&nbsp;&nbsp;<a href="#" onClick="get_grid(\''+nextpage+'\');">下一页</a>&nbsp;&nbsp;<a href="#" onClick="get_grid(\''+page_count+'\');">尾页</a>&nbsp;&nbsp;共'+record_count+'条记录' ; 
						 }
			         
				      $(".pagebar").html(htmlpage);
				         
			     }else{
			    	 $("#taodata").html("数据加载失败");
				  }
		        
	        }
	    	});
		}
	function view_detail(_index, row){
		var url = "<?php echo get_app_url('api/taobao_item/detail&action=do_edit');?>";
		url += "&goods_id="+row.goods_id;
		location.href= url;
		return;
	}
   function store_synchro(id){
	   BUI.use(['bui/overlay','bui/mask'],function(Overlay){
			var  dialog = new Overlay.Dialog({
		        title:'库存同步',
		        width:500,
		        height:300,
		        loader : {
		          url : '<?php echo get_app_url('api/taobao_item/store_synchro');?>&id='+id,
		          autoLoad : false, //不自动加载
		          params : {a : 'a'},//附加的参数
		          lazyLoad : false, //不延迟加载
		          
		        },
		        mask:true
		      });
			var count = 0;
			dialog.show();
			dialog.get('loader').load({a : count});
			count++;
		});
	    
   }
   function store_move(id){
	   
   }
</script>
 
