<style>
    .add-goods{margin-top:50px;}
</style>
<?php echo load_js("baison.js,record_table.js", true); ?>
<?php
render_control('PageHead', 'head1', array('title' => '订单包含指定商品',
    'links' => array(
        array('type' => 'js', 'js' => 'show_select_goods()', 'title' => '新增商品'),
        array('type' => 'js', 'js' => 'delete_all()', 'title' => '一键清空'),
    ),
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit',
        )
    ),
    'fields' => array(
        array(
            'label' => '商品编码',
            'type' => 'input',
            'id' => 'content',
            'title' => '支持模糊查询'
        )
    )
));
?>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '80',
                'align' => '',
                'buttons' => array(
                    array(
                        'id' => 'delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
                        'confirm' => '确定要删除这条辛辛苦苦录入的数据吗？',
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品编码',
                'field' => 'goods_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品名称',
                'field' => 'goods_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '规格1',
                'field' => 'spec1_name',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '规格2',
                'field' => 'spec2_name',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品条形码',
                'field' => 'barcode',
                'width' => '150',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'oms/OrderCheckStrategyDetailModel::get_by_page',
    'idField' => 'id',
    'queryBy' => 'searchForm',
    'params' => array('filter' => array('check_strategy_code' => $response['check_strategy_code'])),
));
?>

<script type="text/javascript">
    var check_strategy_code = "<?php echo $request['check_strategy_code']; ?>";
    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('oms/order_check_strategy/goods_do_delete'); ?>', data: {id: row.id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    function delete_all() {
        BUI.Message.Confirm('确认要删除全部商品么？', function () {
            $.ajax({type: 'POST', dataType: 'json',
                url: '<?php echo get_app_url('oms/order_check_strategy/goods_do_delete_all'); ?>', data: {},
                success: function (ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        BUI.Message.Alert('删除成功', type);
                        tableStore.load();
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                }
            });
        }, 'question');
        return;
    }
    var is_create = 0;
    function show_select_goods() {
        if (is_create == 0) {
            create_select_goods();
        }
        top.dialog.show();
    }

    function create_select_goods() {
        var is_select = 1;
        var param = {store_code: '', is_diy: 0};
        var url = '?app_act=prm/goods/goods_select_tpl&is_select=' + is_select;
        if (typeof top.dialog !== 'undefined') {
            top.dialog.remove(true);

        }

        var buttons = [
            {
                text: '保存继续',
                elCls: 'button button-primary',
                handler: function () {
                    addgoods(this, 1);
                }
            },
            {
                text: '保存退出',
                elCls: 'button button-primary',
                handler: function () {
                    addgoods(this, 0);
                }
            }, {
                text: '取消',
                elCls: 'button',
                handler: function () {
                    this.close();
                }
            }
        ];

        top.BUI.use('bui/overlay', function (Overlay) {
            top.dialog = new Overlay.Dialog({
                title: '选择商品',
                width: '80%',
                height: 450,
                loader: {
                    url: url,
                    autoLoad: true, //不自动加载
                    params: param, //附加的参数
                    lazyLoad: false, //不延迟加载
                    dataType: 'text'   //加载的数据类型
                },
                align: {
                    //node : '#t1',//对齐的节点
                    points: ['tc', 'tc'], //对齐参考：http://dxq613.github.io/#positon
                    offset: [0, 20] //偏移
                },
                mask: true,
                buttons: buttons
            });

        });
        is_create = 1;

    }
    var select_is_select = 1;
    function addgoods(obj, type) {

        var select_data = {};
        var _thisDialog = obj;
        select_data = top.SelectoGrid.getSelection();

        if (select_data.length < 1) {
            _thisDialog.close();
            return;
        }

        var url = '?app_act=oms/order_check_strategy/do_add_goods&app_fmt=json&check_strategy_code=' + check_strategy_code;
        $.post(url, {data: select_data}, function (result) {
            if (result.status != 1) {
                //添加失败
                top.BUI.Message.Alert(result.message, function () {
                }, 'error');
            } else {
                if (type == 1) {
                    top.skuSelectorStore.load();
                } else {
                    _thisDialog.close();
                }
                tableStore.load();
            }
        }, 'json');
    }
</script>
