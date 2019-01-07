<?php render_control('PageHead', 'head1', array('title' => '组织机构')); ?>
<!--输出树结构-->
<div class="demo-content">
    <div id="t1">

    </div>
    <script type="text/javascript">
        BUI.use(['bui/tree', 'bui/data'], function (Tree, Data) {
            //数据缓冲类
            var store = new Data.TreeStore({
                root: {
                    id: '0',
                    text: '百胜软件组织架构',
                    //checked : false
                },
                pidField: 'pid',
                url: '<?php echo get_app_url('sys/org/getList'); ?>',
                autoLoad: true
            });

            var tree = new Tree.TreeList({
                render: '#t1',
                showLine: true,
                height: 380,
                store: store,
                //checkType : 'all',
                showRoot: false
            });
            tree.render();

            tree.on('itemclick', function (ev) {
                var item = ev.item;
                //$('.log').text(item.text);
                //通过id,请求组织机构详细信息
            });
            tree.on('itemdblclick', function (ev) {
                var item = ev.item;
                var mb = myBrowser();
                var data = [];
                var obj = {};
                obj.org_id = item.id;
                obj.org_code = item.code;
                obj.org_name = item.text;
                data.push(obj)

<?php
if ($request['callback']) {
    if (!isset($request['ES_pFrmId']) || $request['ES_pFrmId'] == 'undefined') {
        echo "parent.{$request['callback']}(data, 'org_id', 'org_code', 'org_name')";
    } else {
        echo "getTopFrameWindowByName('{$request['ES_pFrmId']}').{$request['callback']}(data, 'org_id', 'org_code', 'org_name')";
    }
}
?>
            });
        });
    </script>
</div>