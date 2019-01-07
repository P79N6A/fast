
    <style>
        .bui-tab-item{
            position: relative;
        }
        .bui-tab-item .bui-tab-item-text{
            padding-right: 25px;
        }

        .addr_tbl{border-collapse:collapse;border:1px #ccc solid;}
        .addr_tbl th,.addr_tbl td{padding:6px;border-collapse:collapse;border:1px #ccc solid;}
        .addr_tbl th{background: #eee;}
        tr{height:35px; }
		#p3{ padding-top:5px;}
    </style>

<div id="container">

    
    
 <div style="color:#f00">  说明：区域地址勾选即自动保存！</div>

                    <input type="hidden" id="store_code" name="store_code" value="<?php echo $request['store_code']?>"/>
       
                    <div id="sortTree">
                    </div>

    
      <script type="text/javascript">
        BUI.use(['bui/tree','bui/data'],function (Tree,Data) {

            //数据缓冲类
            var store = new Data.TreeStore({
                root : {
                    id : '1',
                    text : '中国',
                    checked : false
                },
                url : '<?php echo get_app_url('op/policy_store/get_nodes&app_fmt=json&store_code='.$request['store_code']);?>',
                autoLoad : true
            });

            var tree = new Tree.TreeList({
                render : '#sortTree',
                showLine : true,
                height:450,
                store : store,
                checkType : 'custom',
                showRoot : true
            });
            tree.render();


            var selecttext = '';
            tree.on('checkedchange',function(e){
            
                if(e.node.text === selecttext){
                        var area_data = {};
                        area_data.type = find_node_type(e.node,0);  
                        area_data.checked = e.checked?1:0;
                        area_data.id = e.node.id;
                        area_data.store_code = $('#store_code').val();
                        save_area(area_data);
                }
       
            });
            function save_area(area_data){
                    var url = "?app_act=op/policy_store/do_save_area"; 
                   $.post(url,area_data,function(ret){
                      
                    },'json');
            
            }
            
            
            function find_node_type(node,type){

               if( node!= null ){
                   type++;
                 return   find_node_type(node.parent,type);
               } 
               return type;
            }
            
            

            store.on('beforeprocessload',function(ev){
              setTimeout(function(){
                  nochange = 1;
                BUI.each(ev.data ,function(subNode,index){
                    var node = tree.findNode(subNode.id);
                        tree.setNodeChecked(node,subNode.checked); //勾选
                });   
                nochange = 0;
              },10);
          });
          
                     store.on('load',function(ev){
                         setTimeout(function(){
                               $('.x-tree-icon-checkbox').off('click');
                              $('.x-tree-icon-checkbox').on('click',function(){
                                 selecttext = $(this).parent().parent().text();
                              });
                      });
                        
                    }); 
            
        });
        
      
    </script>
</div>
