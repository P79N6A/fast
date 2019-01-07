<?php render_control('PageHead', 'head1',array('title'=>'销售渠道'));?>
<!--输出树结构-->
<div class="demo-content">
    <div id="t1">

    </div>
    <script type="text/javascript">
    //产品销售渠道
    BUI.use(['bui/tree','bui/data'],function (Tree,Data) {
        //数据缓冲类
        var store = new Data.TreeStore({
            root : {
                id : '0',
                text : '产品销售渠道',
                //checked : false
            },
            data : [{id : '1',text :'直营',leaf :false},{id : '2',text :'渠道',leaf :false}],
            pidField : 'pid',
            url: '<?php echo get_app_url('basedata/sellchannel/getList');?>',
            autoLoad : true
        });
        
        var tree = new Tree.TreeList({
            render : '#t1',
            showLine : true,
            height:380,
            store : store,
            //checkType : 'all',
            showRoot : false
        });

        tree.render();
    
        tree.on('itemclick',function(ev){
            var item = ev.item;
            //$('.log').text(item.text);
            //通过id,请求组织机构详细信息
        });
        tree.on('itemdblclick',function(ev){
            var item = ev.item;
            if(item.leaf==true || item.leaf=="true"){
                var data = [];
                var obj = {};
                obj.ch_id=item.sid;
                obj.ch_code=item.sid;
                obj.ch_name=item.text;
                data.push(obj)

                <?php if ($request['callback']) { 
                   if (!isset($request['ES_pFrmId']) || $request['ES_pFrmId'] == 'undefined') {
                        echo  "parent.{$request['callback']}(data, 'ch_id', 'ch_code', 'ch_name')";
                   } else {
                        echo "getTopFrameWindowByName('{$request['ES_pFrmId']}').{$request['callback']}(data, 'ch_id', 'ch_code', 'ch_name')";
                   }
                }
                ?>
            }  
        });
    });
    </script>
</div>