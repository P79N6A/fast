<style>
    .bui-tree-list{
        overflow: auto;
    }
</style>
<div class="row">
    <div class="span8 offset3">
        <div id="location">
        </div>
    </div>
</div>
<script type="text/javascript">
    BUI.use(['bui/tree', 'bui/data', 'bui/mask'], function (Tree, Data, Mask) {
        //数据缓冲类
        var store = new Data.TreeStore({
            proxy: {
                url: '?app_act=api/tb_issue/get_location',
                dataType: 'jsonp'
            },
            map: {
                isleaf: 'leaf',
                value: 'text'
            },
            autoLoad: true
        });

        var tree = new Tree.TreeList({
            render: '#location',
            showLine: true,
            height: 'auto',
            loadMask: new Mask.LoadMask({el: '#location'}),
            store: store,
//            checkType: 'all',
            multipleCheck: false
        });
        tree.render();


        tree.on('itemclick', function (ev) {
            var item = ev.item;
            parent.addlocation = item;
        });
    });
</script>