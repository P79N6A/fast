<style>
    .bui-tree-list{
        overflow: auto;
    }
</style>
<div class="row">
    <div class="span8 offset3">
        <div id="itemcats">
        </div>
    </div>
</div>

<script type="text/javascript">
    BUI.use(['bui/tree', 'bui/data'], function (Tree, Data) {
        //数据缓冲类
        var store = new Data.TreeStore({
            root: {
                id: '0',
                text: '宝贝类目',
                leaf: false
            },
            pidField: 'id',
            url: '?app_act=api/tb_issue/select_itemcats_child',
            autoLoad: false
        });

        var tree = new Tree.TreeList({
            render: '#itemcats',
            showLine: true,
            height: 'auto',
            store: store,
//            checkType: 'onlyLeaf',
            showRoot: true,
//            multipleCheck: false
        });
        tree.render();

        tree.on('itemclick', function (ev) {
            var item = ev.item;
            parent.additem = item;
        });
    });
</script>