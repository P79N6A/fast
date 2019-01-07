<?php echo load_js('comm_util.js') ?>
<?php echo load_js("pur.js", true); ?>
<?php
render_control('PageHead', 'head1', array('title' => '道讯ERP单据同步',
    'links' => array(// array('url' => 'erp/bserp/create_daily_report', 'title' => '生成日报', 'is_pop' => true, 'pop_size' => '490,510'),
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
            'type' => 'submit'
        ),
    ),
    'fields' => array(
        array(
            'label' => '订/退单编号',
            'type' => 'input',
            'id' => 'sell_record_code'
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
    )
));
?>

<?php
//render_control('TabPage', 'TabPage1', array(
//    'tabs' => array(
//        array('title' => '未上传', 'active' => true, 'id' => 'no_upload'),
//        array('title' => '已上传', 'active' => false, 'id' => 'upload'),
//    ),
//    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
//));
//?>
<!--<div id="TabPage1Contents">-->
<!--</div>-->
<?php render_control('DataTable', 'table', array(
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
                    array('id' => 'upload', 'title' => '上传', 'callback' => 'do_upload', 'confirm' => '确定上传该单据?', 'show_cond' => 'obj.upload_status!=1'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订/退单编号',
                'field' => 'sell_record_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据类型',
                'field' => 'order_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '150',
                'align' => ''
            ),

            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'store_code_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据金额',
                'field' => 'payable_money',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '发货/收货时间',
                'field' => 'delivery_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'upload_status_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '上传失败消息',
                'field' => 'upload_msg',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '上传时间',
                'field' => 'upload_time',
                'width' => '150',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'erp/DxerpModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'CheckSelection' => true,
));
?>
<ul id="ToolBar1" class="toolbar frontool">
    <li class="li_btns">
        <button class="button button-primary btn_upload_record">批量上传</button>
    </li>
    <div class="front_close">&lt;</div>
</ul>
<script>
    $(function () {
        //TAB选项卡
        $("#TabPage1 a").click(function () {
            tableStore.load();
        });
        $("input[name='is_normal']").change(function () {
            tableStore.load();
        });
        tableStore.on('beforeload', function (e) {
            e.params.upload_tab = $("#TabPage1").find(".active").find("a").attr("id");
            tableStore.set("params", e.params);
        });

        function tools() {
            $(".frontool").animate({left: '0px'}, 1000);
            $(".front_close").click(function () {
                if ($(this).html() == "&lt;") {
                    $(".frontool").animate({left: '-100%'}, 1000);
                    $(this).html(">");
                    $(this).addClass("close_02").animate({right: '-10px'}, 1000);
                } else {
                    $(".frontool").animate({left: '0px'}, 1000);
                    $(this).html("<");
                    $(this).removeClass("close_02").animate({right: '0'}, 1000);
                }
            });
        }

        tools();
    })

    //单据上传
    function do_upload(_index, row) {
        var params = {"record_code": row.sell_record_code, "order_type": row.order_type, 'app_fmt': 'json'};
        $.post("?app_act=erp/dxerp/do_upload", params, function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            tableStore.load();
        }, "json");
    }

    //批量上传
    $(".btn_upload_record").click(function () {
        get_checked($(this), function (ids) {
            //校验是否绑定批发通知单
            var params = {"record_code_order_type": ids.toString(), 'app_fmt': 'json'};
            $.post("?app_act=erp/dxerp/do_upload_multi", params, function (data) {
                var type = data.status == 1 ? 'success' : 'error';
                BUI.Message.Alert(data.message, type);
                tableStore.load();
            }, "json");

        })
    });

    //读取已选中项
    function get_checked(obj, func) {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择订单", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            var key = row.sell_record_code + '_' + row.order_type;
            ids.push(key);
        }
        ids.join(',');
        BUI.Message.Show({
            title: '温馨提示',
            msg: '是否确定要执行' + obj.text() + '?',
            icon: 'question',
            buttons: [
                {
                    text: '是',
                    elCls: 'button button-primary',
                    handler: function () {
                        func.apply(null, [ids]);
                        this.close();
                    }
                },
                {
                    text: '否',
                    elCls: 'button',
                    handler: function () {
                        this.close();
                    }
                }
            ]
        });
    }
</script>



