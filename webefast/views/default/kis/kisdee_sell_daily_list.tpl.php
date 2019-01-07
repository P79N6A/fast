<style>
    #upload_time_start,#upload_time_end{
        width: 100px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '零售日报',
    'links' => array(
        array('url' => 'kis/kisdee/sell_daily_build', 'title' => '生成日报', 'is_pop' => true, 'pop_size' => '450,510'),
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
            'label' => '单据编号',
            'type' => 'input',
            'id' => 'record_code'
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => $response['shop']
        ),
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => $response['store']
        ),
        array(
            'label' => '单据类型',
            'type' => 'select',
            'id' => 'record_type',
            'data' => $response['record_type']
        ),
        array(
            'label' => '上传时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'upload_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'upload_time_end', 'remark' => ''),
            )
        ),
    )
));
?>

<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '未上传', 'active' => true, 'id' => 'upload_not'),
        array('title' => '已上传', 'active' => false, 'id' => 'upload_success'),
        array('title' => '上传失败', 'active' => false, 'id' => 'upload_fail'),
    ),
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<div id="TabPage1Contents">
    <?php if ($response['is_on_config'] == 1): ?>
        <div>
            <ul id="ToolBar1" class="toolbar frontool">
                <li class="li_btns"><button class="button button-primary" onclick="opt_upload_batch(this)">批量上传</button></li>
                <li class="front_close">&lt;</li>
            </ul>
        </div>
        <div></div>
        <div>
            <ul id="ToolBar1" class="toolbar frontool">
                <li class="li_btns"><button class="button button-primary" onclick="opt_upload_batch(this)">批量上传</button></li>
                <li class="front_close">&lt;</li>
            </ul>
        </div>
    <?php endif; ?>
</div>

<?php
if ($response['is_on_config'] == 1) {
    $buttons = array(
        array('id' => 'upload', 'title' => '上传', 'callback' => 'opt_upload', 'show_cond' => 'obj.upload_status!=1'),
    );
}
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '50',
                'align' => '',
                'buttons' => $buttons,
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据编号',
                'field' => 'record_code',
                'width' => '140',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({id})">{record_code}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据类型',
                'field' => 'record_type_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'store_code_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '总数量',
                'field' => 'quantity',
                'width' => '60',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '总金额',
                'field' => 'amount',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '业务日期',
                'field' => 'record_date',
                'width' => '90',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '生成时间',
                'field' => 'create_time',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '上传时间',
                'field' => 'upload_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '上传失败原因',
                'field' => 'fail_cause',
                'width' => '115',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'kis/KisdeeModel::get_sell_daily_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'params' => array('filter' => array('upload_tab' => 'upload_not')),
    'CheckSelection' => true,
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>

<script>
    $(function () {
        //TAB选项卡
        $("#TabPage1 a").click(function () {
            tableStore.load();
        });
        //加载下方按钮工具栏
        tools();
    });

    tableStore.on('beforeload', function (e) {
        e.params.upload_tab = $("#TabPage1").find(".active").find("a").attr("id");
        tableStore.set("params", e.params);
    });

    function opt_upload(index, row) {
        $.post("?app_act=kis/kisdee/opt_upload", {record_code: row.record_code, record_type: row.record_type}, function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            tableStore.load();
        }, "json");
    }

    var ids = new Array();
    function opt_upload_batch(_this) {
        get_checked($(_this), function () {
            //校验是否绑定批发通知单
            var data = {params: ids};
            $.post("?app_act=kis/kisdee/opt_upload_batch", data, function (data) {
                var type = data.status == 1 ? 'success' : 'error';
                BUI.Message.Alert(data.message, type);
                tableStore.load();
            }, "json");
        });
    }

    //读取已选中项
    function get_checked(obj, func) {
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择单据", 'warning');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            var p = {};
            p.record_code = row.record_code;
            p.record_type = row.record_type;
            ids.push(p);
        }

        BUI.Message.Show({
            title: obj.text(),
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

    //数据行双击打开详情页
    function showDetail(_index, row) {
        view(row.id);
    }
    //单击单据编号打开详情页
    function view(_id) {
        var url = '?app_act=kis/kisdee/sell_daily_detail&id=' + _id;
        openPage(window.btoa(url), url, '零售日报详情');
    }

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
</script>



