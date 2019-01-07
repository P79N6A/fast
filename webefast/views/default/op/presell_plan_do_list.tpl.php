<style>
    #sale_st_start,#sale_st_end{width: 120px;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '预售计划列表',
    'links' => array(
        array('url' => 'op/presell/plan_add', 'title' => '新增计划', 'is_pop' => FALSE),
    ),
    'ref_table' => 'table'
));
?>

<?php
$keyword['plan_name'] = '预售名称';
$keyword['barcode'] = '商品条形码';
$keyword['goods_code'] = '商品编码';
$keyword['plan_code'] = '预售编码';
$keyword = array_from_dict($keyword);
$keyword_date['end_time'] = '结束时间';
$keyword_date['start_time'] = '开始时间';
$keyword_date['create_time'] = '创建时间';
$keyword_date = array_from_dict($keyword_date);
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
//        array(
//            'label' => '导出',
//            'id' => 'exprot_list',
//        )
    ),
    'fields' => array(
        array(
            'label' => array('id' => 'keyword', 'type' => 'select', 'data' => $keyword),
            'type' => 'input',
            'title' => '',
            'data' => $keyword,
            'id' => 'keyword_value',
        ),
        array(
            'label' => array('id' => 'keyword_date', 'type' => 'select', 'data' => $keyword_date),
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => $response['shop'],
        ),
    )
));
?>
<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '全部', 'active' => true, 'id' => 'all'),
        array('title' => '未开始', 'active' => false, 'id' => 'no_start'),
        array('title' => '进行中', 'active' => false, 'id' => 'starting'),
    ),
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<?php
$curr_time = time();
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
                    array('id' => 'view', 'title' => '查看', 'act' => 'op/presell/plan_view&app_scene=view&plan_code={plan_code}', 'show_name' => '查看预售计划', 'show_cond' => ''),
                    array('id' => 'edit', 'title' => '编辑', 'act' => 'op/presell/plan_edit&app_scene=edit&plan_code={plan_code}', 'show_name' => '编辑预售计划', 'priv' => 'op/presell/plan_edit', 'show_cond' => "obj.end_time>{$curr_time}&&obj.exit_status==0"),
                    array('id' => 'cancel', 'title' => '删除', 'callback' => 'plan_delete', 'priv' => 'op/presell/do_delete', 'show_cond' => "obj.is_allow_delete==1&&obj.exit_status==0"),
                    array('id' => 'sync', 'title' => '同步', 'callback' => 'plan_sync_check', 'priv' => 'op/presell/plan_sync_check', 'show_cond' => "obj.is_allow_sync==1&&obj.exit_status==0"),
                    array('id' => 'exit', 'title' => '立即终止', 'callback' => 'exit_now', 'priv' => 'op/presell/plan_exit_check', 'show_cond' => "obj.exit_show==1",'confirm'=>'预售计划一旦手动终止，不允许重新启用或是编辑，请确认是否要立即终止?')
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '预售编码',
                'field' => 'plan_code',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '预售名称',
                'field' => 'plan_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '预售开始时间',
                'field' => 'plan_start_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '预售结束时间',
                'field' => 'plan_end_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '预售店铺',
                'field' => 'plan_shop',
                'width' => '180',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '同步次数',
                'field' => 'sync_num',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '创建人',
                'field' => 'create_person',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '创建时间',
                'field' => 'create_time',
                'width' => '150',
                'align' => ''
            )
        )
    ),
    'dataset' => 'op/presell/PresellModel::get_presell_plan_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
//    'export' => array('id' => 'exprot_list', 'conf' => 'ex_record_list', 'name' => '订单列表', 'export_type' => 'file'),
));
?>

<script type="text/javascript">
    $(function () {
        //Tab页签数据加载
        $("#TabPage1 a").click(function () {
            tableStore.load();
        });
    });

    tableStore.on('beforeload', function (e) {
        e.params.presell_status = $("#TabPage1").find(".active").find("a").attr("id");
        tableStore.set("params", e.params);
    });
    
    //同步预售库存检查
    function plan_sync_check(index, row) {
        $.post("?app_act=op/presell/plan_sync_check", {plan_code: row.plan_code}, function (ret) {
            var txt;
            if (ret.status == -1) {
                BUI.Message.Tip(ret.message, 'error');
                return;
            } else if (ret.status == 2) {
                txt = row.plan_code + ' 计划将于 ' + ret.data + ' 开始预售，确认要同步吗？';
            } else {
                txt = '确认要同步预售库存吗？';
            }
            plan_sync(row.plan_code, txt);
        }, "json");
    }

    //预售库存同步
    function plan_sync(plan_code, txt) {
        BUI.Message.Confirm(txt + '<br><span style="color:red;">注意：该操作会同时禁止预售关联的平台商品的库存同步，并在预售结束后更新为允许同步</span>', function () {
            $.post("?app_act=op/presell/plan_sync", {plan_code: plan_code}, function (data) {
                if (data.status == 1) {
                    tableStore.load();
                    BUI.Message.Tip(data.message, 'success');
                } else {
                    BUI.Message.Tip(data.message, 'error');
                }
            }, "json");
        }, 'question');
    }

    //预售计划删除
    function plan_delete(index, row) {
        BUI.Message.Confirm('确认要删除吗？', function () {
            $.post("?app_act=op/presell/do_delete", {plan_code: row.plan_code}, function (data) {
                if (data.status == 1) {
                    tableStore.load();
                    BUI.Message.Tip(data.message, 'success');
                } else {
                    BUI.Message.Tip(data.message, 'error');
                }
            }, "json");
        }, 'question');
    }

    //立即终止
    function exit_now(index, row) {
        $.post("?app_act=op/presell/exit_now", {plan_code: row.plan_code}, function (data) {
            if (data.status == 1) {
                tableStore.load();
                BUI.Message.Tip(data.message, 'success');
            } else {
                BUI.Message.Tip(data.message, 'error');
            }
        }, "json");
    }


</script>