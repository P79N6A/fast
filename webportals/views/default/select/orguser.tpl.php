<?php render_control('PageHead', 'head1', array('title' => '用户列表',)); ?>
<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => '关键字',
            'title' => '登录名/真实名',
            'type' => 'input',
            'id' => 'keyword'
        ),
        array(
            'label' => '有效',
            'title' => '是否有效',
            'type' => 'select',
            'id' => 'user_active',
            'data' => ds_get_select_by_field('boolstatus')
        )
    )
));
?>
<!--输出树结构-->
<div class="demo-content">
    <div id="t1" style="width:24%">

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
                height: 290,
                store: store,
                //checkType : 'all',
                showRoot: false
            });
            tree.render();

            tree.on('itemclick', function (ev) {
                var item = ev.item;
                //$('.log').text(item.text);
                //通过id,请求组织机构详细信息
                if (item.leaf == true) {
                    var data = {orgid: item.id};
                    tableStore.load(data);
                }
            });
            tree.on('itemdblclick', function (ev) {
                var item = ev.item;
                //表示子节点
//            if(item.leaf==true){
//                var data={orgid:item.id};
//                tableStore.load(data);
//            }   
            });
        });
    </script>
</div>
<div id="t2" style="width:75%; height: 350px; position: absolute; right: 0; top: 95px;">
    <?php
    render_control('DataTable', 'table', array(
        'conf' => array(
            'list' => array(
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '登录名',
                    'field' => 'user_code',
                    'width' => '100',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '真实名',
                    'field' => 'user_name',
                    'width' => '200',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '直属上级',
                    'field' => 'user_highedrup_name',
                    'width' => '200',
                    'align' => ''
                ),
                array(
                    'type' => 'checkbox',
                    'show' => 1,
                    'title' => '是否有效',
                    'field' => 'user_active',
                    'width' => '100',
                    'align' => ''
                ),
            )
        ),
        'dataset' => 'sys/UserModel::get_by_page',
        'params' => array('filter' => array('page_size' => 10)),
        'queryBy' => 'searchForm',
        'idField' => 'user_id',
        'CheckSelection' => isset($request['multi']) && $request['multi'] = 1 ? true : false,
    ));
    ?>
</div>
<?php echo_selectwindow_js($request, 'table', array('id' => 'user_id', 'code' => 'user_code', 'name' => 'user_name')) ?>
