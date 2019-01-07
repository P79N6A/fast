<?php render_control('PageHead', 'head1',array('title'=>'行政区域对照'));?>
<!--输出树结构-->
<div class="demo-content">
    <div id="t1" style="width:25%; float:left; margin-right:1%;">

    </div>
    <script type="text/javascript">
    BUI.use(['bui/tree','bui/data'],function (Tree,Data) {
        //数据缓冲类
        var store = new Data.TreeStore({
            root : {
            id : '0', //默认中国
            text : '全世界',
            //checked : false
            },
            pidField : 'pid',
            url: '<?php echo get_app_url('basedata/area/getList');?>',
            autoLoad : true
        });
        
        var tree = new Tree.TreeList({
            render : '#t1',
            showLine : true,
            height:550,
            store : store,
            //checkType : 'all',
            showRoot : false
        });
        tree.render();
    
        tree.on('itemclick',function(ev){
            var item = ev.item;
            //$('.log').text(item.text);
            //通过id,请求行政区域详细信息
            $('#t2').html("<iframe id='t2' width='100%' height='100%' src='?app_act=basedata/area/detail&_id="+item.id+"&ES_frmId=<?php echo base64_encode('app_act=basedata/area/detail') ?>' />");
        });
        $('#reload').on('click',function(){
            var node = tree.getSelected();
            if(node){
                store.reloadNode(node);
            } 
        });
    });

    </script>
    <div id="t2" style="border: 1px solid #c3c3d6; width:73.5%; height: 550px; float:right;">
        <iframe id="t2" width="100%" height="100%" src="?app_act=basedata/area/detail&_id=1&ES_frmId=<?php echo base64_encode("app_act=basedata/area/detail") ?>" />
    </div>
</div>
