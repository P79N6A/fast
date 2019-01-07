<?php echo load_js("pur.js", true); ?>
<?php
render_control('PageHead', 'head1', array('title' => '唯品会JIT档期管理',
    'links' => array(
        array('type' => 'js', 'js' => 'get_po()', 'title' => '获取档期'),
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
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => $response['shop'],
        ),
        array(
            'label' => '档期号',
            'type' => 'input',
            'id' => 'po_no'
        ),
        array(
            'label' => '库存锁定单号',
            'type' => 'input',
            'id' => 'notice_record_no'
        ),
        array(
            'label' => '开始时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'st_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'st_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '结束时间',
            'type' => 'group',
            'field' => 'daterange2',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'et_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'et_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '待拣货',
            'type' => 'select',
            'id' => 'notice_record_num',
            'data'=>array(array('', '全部'), array('2', '是'), array('1', '否')),
            'value'=>'2'
        ),
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
                'width' => '150',
                'align' => '',
                'buttons' => array(
                    array('id' => 'create_pick', 'title' => '生成拣货单', 'callback' => 'create_pick', 'priv' => 'sys/user/enable', 'show_cond' => ''),
                    array('id' => 'relation_pftzd', 'title' => '绑定', 'callback' => 'relation_notice', 'show_cond' => "obj.notice_id == 0 "),
                    array('id' => 'unrelation_notice', 'title' => '解绑', 'callback' => 'unrelation_notice', 'show_cond' => "obj.notice_id != 0"),
                    array('id' => 'get_pick', 'title' => '获取拣货单', 'callback' => 'get_pick', 'confirm' => '确认要获取该档期下的所有拣货单吗？', 'priv' => 'api/api_weipinhuijit_po/get_pick'),
                    array('id' => 'update_po', 'title' => '更新', 'callback' => 'update_po', 'confirm' => '确认要更新吗？', 'show_cond' => ''),
                ),
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
                'title' => '档期号',
                'field' => 'po_no',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '开始时间',
                'field' => 'sell_st_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '结束时间',
                'field' => 'sell_et_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '绑定单号',
                'field' => 'notice_record_no',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '虚拟总库存',
                'field' => 'stock',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '销售数',
                'field' => 'sales_volume',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '未拣货数',
                'field' => 'not_pick',
                'width' => '80',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '品牌名称',
                'field' => 'brand_name',
                'width' => '100',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'api/WeipinhuijitPoModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'CheckSelection' => true,
));
?>
<ul id="ToolBar1" class="toolbar frontool">
    <li class="li_btns"><button class="button button-primary opt_batch_update_po">批量更新</button></li>
</ul>
<script type="text/javascript">
    $(function () {
        $(".page-header1").append("<span class='page-link' style='padding-right:20px;padding-top:4px;'><a href='http://operate.baotayun.com:8080/efast365-help/?p=3118' target='_blank' style='color:red;font-weight:bold;text-decoration:underline;font-family:Microsoft YaHei;letter-spacing:3px;font-size:16px;'>上线必读！</a></span>");

        tools();
    });

    $("#tid").css('border', '1px solid red');
    //获取档期
    function get_po() {
        var d = {'app_fmt': 'json'};
        $("#get").attr('disabled', true);
        $.post('<?php echo get_app_url('api/api_weipinhuijit_po/get_po'); ?>', d, function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            $("#get").attr('disabled', false);
            tableStore.load();
        }, "json");
    }

    //更新档期
    function update_po(index, row) {
        $.post("?app_act=api/api_weipinhuijit_po/update_po", {'id': row.id}, function (data) {
            if (data.status == 1) {
                tableStore.load();
                BUI.Message.Tip(data.message, 'success');
            } else {
                BUI.Message.Alert(data.message, 'error');
            }
        }, "json");
    }

    //批量更新档期
    $(".opt_batch_update_po").click(function () {
        get_checked($(this), function (ids) {
            BUI.Message.Confirm('确认要更新吗？', function () {
                $.post("?app_act=api/api_weipinhuijit_po/update_po", {'id': ids}, function (data) {
                    var type = data.status == 1 ? 'success' : 'error';
                        BUI.Message.Alert(data.message, type);
                        tableStore.load();
                }, "json");
            }, 'question');
        });
    });

    //创建拣货单
    function create_pick(index, row) {
        var url = "?app_act=api/api_weipinhuijit_po/create_pick_by_warehouse&po_no=" + row.po_no+"&id="+row.id;
        _do_execute(url, 'table', '生成拣货单 <span style="color: red">（档期号：'+ row.po_no+ '&nbsp;&nbsp;&nbsp;未拣货数：'+row.not_pick+' ）</span>', 650, 500);
    }

    //获取档期下的所有拣货单
    function get_pick(index, row) {
        var d = {"po_id": row.id, 'app_fmt': 'json'};
        var a = $('span[es_btn_id="get_pick"]').eq(index).css('display', 'none');
        $.post('<?php echo get_app_url('api/api_weipinhuijit_po/get_pick'); ?>', d, function (data) {

            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            var a = $('span[es_btn_id="get_pick"]').eq(index).css('display', '');
            tableStore.load();
        }, "json");
    }
    function relation_notice(_index, row) {
//            url = "?app_act=api/api_weipinhuijit_po/relation_notice&po_id=" + row.id;
//            _do_execute(url, 'table', '绑定批发通知单', 550, 600);
            url = "?app_act=api/api_weipinhuijit_po/relation_lock&po_id=" + row.id;
            _do_execute(url, 'table', '绑定库存锁定单', 610, 600);
    }
    //解绑
    function unrelation_notice(index, row) {
        var d = {"po_id": row.id,"relation_type":row.relation_type, 'app_fmt': 'json'};
        $.post('<?php echo get_app_url('api/api_weipinhuijit_po/unrelation'); ?>', d, function (data) {

            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            tableStore.load();
        }, "json");
    }

    //读取已选中项
    function get_checked(obj, func) {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择档期", 'warning');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.id);
        }
        ids.join(',');
        func.apply(null, [ids]);
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