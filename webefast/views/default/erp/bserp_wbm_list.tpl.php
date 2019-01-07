<?php
render_control('PageHead', 'head1', array('title' => 'BSERP2批发单据', 'ref_table' => 'table'));


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
            'label' => '仓库',
            'type' => 'select',
            'id' => 'store_code',
            'data' => load_model('erp/BserpModel')->get_erp_store_code()
        ),
        array(
            'label' => '单据类型',
            'type' => 'select',
            'id' => 'order_type',
            'data' => array(
                array('all', '全部'), array('1', '批发销货单'), array('2', '批发退货单')
            )
        ),
    ),
));
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '未上传', 'active' => true, 'id' => 'no_upload'),
        array('title' => '已上传', 'active' => false, 'id' => 'upload'),
    ),
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<div id="TabPage1Contents">

</div>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '100',
                'align' => 'center',
                'buttons' => array(
                    array('id' => 'view', 'title' => '上传', 'callback' => 'do_upload', 'show_cond' => 'obj.upload_status != 1'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'upload_status_name',
                'width' => '100',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据类型',
                'field' => 'order_type',
                'width' => '150',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据编号',
                'field' => 'record_code',
                'width' => '220',
                'align' => 'center',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({record_code_str})">{record_code}</a>'
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'store_name',
                'width' => '220',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '总数量',
                'field' => 'num',
                'width' => '80',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '金额',
                'field' => 'money',
                'width' => '150',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '上传时间',
                'field' => 'upload_time',
                'width' => '330',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '上传日志',
                'field' => 'upload_msg',
                'width' => '220',
                'align' => 'center'
            )
        ),
    ),
    'dataset' => 'erp/BserpModel::get_wbm_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'CheckSelection' => true,
));
?>
<ul id="ToolBar1" class="toolbar frontool">
    <li class="li_btns"><button class="button button-primary btn_opt_upload">批量上传</button></li>
    <div class="front_close">&lt;</div>
</ul>
<script>
    $(function () {
        //TAB选项卡
        $("#TabPage1 a").click(function () {
            tableStore.load();
        });
        tableStore.on('beforeload', function (e) {
            e.params.upload_tab = $("#TabPage1").find(".active").find("a").attr("id");
            tableStore.set("params", e.params);
        });
        function tools() {
            $(".frontool").animate({left: '0px'}, 1000);
            $(".front_close").click(function () {
                if ($(this).html() === "&lt;") {
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
        var default_opts = ['opt_upload'];
        for (var i in default_opts) {
            var f = default_opts[i];
            btn_init_opt("ToolBar1", f);
        }
        var custom_opts = $.parseJSON('');
        for (var j in custom_opts) {
            var g = custom_opts[j];
            $("#ToolBar1 .btn_" + g['id']).click(eval(g['custom']));
        }
    });

    function upload(record_codes) {
        record_code = record_codes.toString();
        var params = {"record_code": record_code};
        $.post("?app_act=erp/bserp/wbm_upload", params, function (data) {
            if (data.status == 1) {
                BUI.Message.Alert('上传成功', 'success');
                tableStore.load();
            } else {
                BUI.Message.Alert('上传失败', 'success');
            }
        }, "json");
    }

    function do_upload(_index, row) {
        upload(row.record_code);
        
    }


    function view(record_code_str) {
        var arr = record_code_str.split(',');
        var str = arr[0].substr(0, 2)
        if(str == 'PF'){
            openPage('<?php echo base64_encode('?app_act=wbm/store_out_record/view&store_out_record_id') ?>' + arr[1], '?app_act=wbm/store_out_record/view&store_out_record_id=' + arr[1], '批发销货单详情');
        }else{
            openPage('<?php echo base64_encode('?app_act=wbm/return_record/view&return_record_id=') ?>' + arr[1], '?app_act=wbm/return_record/view&return_record_id=' + arr[1], '批发退货单详情');
        }
        
    }

    //初始化批量操作按钮
    function btn_init_opt(tab_id, id) {
        $("#" + tab_id + " .btn_" + id).click(function () {
            get_checked($(this), function (ids) {
                if (id === 'opt_upload') {
                    $("#btn_opt_upload").attr('disabled', 'disabled');
                    upload(ids);
                }
            })
        });
    }
    //读取已选中项
    function get_checked(obj, func) {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择单据", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.record_code);
        }
        ids.join(',');
        BUI.Message.Show({
            title: '批量初始化',
            msg: '是否执行' + obj.text() + '?',
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