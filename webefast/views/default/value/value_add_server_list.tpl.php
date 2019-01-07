<style>
    .print_type_btn {
        border: 1px solid #1695ca;
        background: #FFF;
        color: #1695ca;
        margin-right: 2px;
        border-radius: 3px;
    }
</style>
<?php echo load_js('comm_util.js') ?>
<?php
$links = array(
    array('url' => 'value/value_add/shopping_cart', 'title' => '购物车', 'is_pop' => false, 'pop_size' => '500,400'),
);
render_control('PageHead', 'head1', array('title' => '服务订购列表',
    'links' => $links,
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'id' => 'btn-search',
    ),
    'fields' => array(
        array(
            'label' => '服务名称',
            'title' => '服务名称',
            'type' => 'input',
            'id' => 'value_name',
        ),
    )
));
?>
<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '全部', 'active' => true, 'id' => 'tabs_all'),
        array('title' => '平台接口', 'active' => false, 'id' => 'tabs_source'),
        array('title' => '仓储接口', 'active' => false, 'id' => 'tabs_store'), // 默认选中active=true的页签
        array('title' => 'ERP接口', 'active' => false, 'id' => 'tabs_erp'),
    ),
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '图片',
                'field' => 'pic_path_img',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '服务名称',
                'field' => 'value_name',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '已对接的功能',
                'field' => 'dock_function',
                'width' => '250',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '价格（元/年）',
                'field' => 'value_price',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '操作',
                'field' => 'operate_status',
                'width' => '200',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'value/ValueServerModel::get_value_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'value_id',
        //'RowNumber'=>true,
        // 'CheckSelection'=>true,
));
?>
<script type="text/javascript">
    $(document).ready(function () {
        //TAB选项卡
        $("#TabPage1 a").click(function () {
            tableStore.load();
        });
        tableStore.on('beforeload', function (e) {
            e.params.tabs_type = $("#TabPage1").find(".active").find("a").attr("id");
            tableStore.set("params", e.params);
        });
        tableStore.load();
    })

    //添加购物车
    function add_shopping_cat(value_id) {
        $.ajax({
            type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('value/value_add/add_shopping_cart'); ?>', data: {value_id: value_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('添加成功！', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    //立即订购
    function add_server_order(value_id) {
        $.ajax({
            type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('value/value_add/add_server_order'); ?>', data: {value_id: value_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    ali_pay(ret);
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    //获取返回的URL
    function ali_pay(data) {
        window.open(data.data);
        BUI.Message.Show({
            title: '提示',
            msg: '是否支付成功?',
            icon: 'question',
            buttons: [
                {
                    text: '支付成功',
                    elCls: 'button button-primary',
                    handler: function () {
                        check_pay_status(data.message, this);
                    }
                },
                {
                    text: '支付失败',
                    elCls: 'button',
                    handler: function () {
                        open_new_page();
                        this.close();
                        location.reload();
                    }
                }
            ]
        });
    }


    //验证充值是否成功
    function check_pay_status(pay_out_trade_no, _this) {
        $.ajax({
            type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('value/server_order/check_pay_status'); ?>', data: {pay_out_trade_no: pay_out_trade_no},
            success: function (ret) {
                _this.close();
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('订购成功！', function () {
                        location.reload();
                        var url = "?app_act=value/server_order/do_list&tabs_type=tabs_remark";
                        openPage(window.btoa(url), url, '我的订单');
                    }, type);
                } else {
                    BUI.Message.Alert('已生成订单，支付宝支付失败！', function () {
                        location.reload();
                        var url = "?app_act=value/server_order/do_list";
                        openPage(window.btoa(url), url, '我的订单');
                    }, type);
                }
            }
        });
    }

    function open_new_page() {
        var url = '?app_act=value/server_order/do_list';
        openPage(window.btoa(url), url, '我的订单');
    }
</script>
