<?php
render_control('PageHead', 'head1', array('title' => '唯品会JIT退供管理',
    'links' => array(
        //array('type' => 'js', 'js' => 'get_return()', 'title' => '获取退货单'),
        array('url' => 'api/api_weipinhuijit_return/down', 'title' => '获取退货单', 'is_pop' => true, 'pop_size' => '800,480'),
    ),
    'ref_table' => 'table'
));
?>

<?php
$keyword_type['return_sn'] = '退供单号';
$keyword_type['return_record_no'] = '批发退货单号';
$keyword_type['product_name'] = '商品名称';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['box_no'] = '箱号';//2017-12-12 task#1936 查询条件下拉菜单中增加查询条件“箱号”
$keyword_type = array_from_dict($keyword_type);
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
        array(
            'label' => '导出',
            'id' => 'exprot_list',
        ),
        array(
            'label' => '导出明细',
            'id' => 'exprot_detail',
        ),
    ),
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => $response['shop'],
        ),
        array(
            'label' => '唯品会仓库',
            'type' => 'select_multi',
            'id' => 'warehouse',
            'data' => load_model('api/WeipinhuijitWarehouseModel')->get_warehouse_select(),
        ),
        array(
            'label' => '生成退货单',
            'type' => 'select',
            'id' => 'is_execute',
            'data' => ds_get_select_by_field('is_build', 1),
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
                'width' => '150',
                'align' => '',
                'buttons' => array(
                    array('id' => 'create_out_record', 'title' => '生成退货单', 'callback' => 'create_return', 'show_cond' => 'obj.is_execute != 1'),
                    array('id' => 'view', 'title' => '查看', 'callback' => 'showDetail'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '已生成',
                'field' => 'is_execute',
                'width' => '80',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退供单号',
                'field' => 'return_sn',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退供仓库',
                'field' => 'warehouse_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退货数',
                'field' => 'num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '总箱数',
                'field' => 'box_num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '创建时间',
                'field' => 'insert_time',
                'width' => '150',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'api/WeipinhuijitReturnModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'export' => array('id' => 'exprot_list', 'conf' => 'wph_return_list', 'name' => '唯品会JIT退货','export_type'=>'file'),
    'CheckSelection' => true,
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
    'CascadeTable' => array(
        'list' => array(
            array('title' => '关联批发退货单', 'width' => '150', 'field' => '', 'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view_return_record({return_record_id})">{record_code}</a>',
                ),
            ),
            array('title' => 'eFAST仓库', 'field' => 'store_name'),
            array('title' => '已通知数量', 'field' => 'enotice_num'),
            array('title' => '已入库数量', 'field' => 'num'),
            array('title' => '创建时间', 'width' => '150', 'field' => 'order_time'),
            array('title' => '退货单验收状态', 'field' => 'is_store_in', 'format_js' => array('type' => 'map_checked')),
        ),
        'page_size' => 50,
        'url' => get_app_url('api/api_weipinhuijit_return/get_return_record_by_sn&app_fmt=json'),
        'params' => 'return_sn',
    ),
));
?>
<ul id="ToolBar1" class="toolbar frontool">
    <li class="li_btns"><button class="button button-primary create_wbm_return_record">生成退货单</button></li>
    <div class="front_close">&lt;</div>
</ul>
<?php echo load_js("pur.js", true); ?>
<script>
    //获取退货单
    function get_return() {
        var d = {'app_fmt': 'json'};
        $("#get").attr('disabled', true);
        $.post('<?php echo get_app_url('api/api_weipinhuijit_return/get_return'); ?>', d, function (data) {

            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            $("#get").attr('disabled', false);
            tableStore.load();//刷新
        }, "json");
    }


    $(function () {
        tools();
        $('#exprot_detail').click(function(){
            var url = '?app_act=sys/export_csv/export_show', //暂时不是框架级别
            params = tableStore.get('params');
            params.is_detail = 1;
            params.ctl_type = 'export';
            params.ctl_export_conf = 'wph_return_detail';
            params.ctl_export_name =  '唯品会JIT退货';
            <?php echo   create_export_token_js('api/WeipinhuijitReturnModel::get_by_page');?>
            var obj = searchFormForm.serializeToObject();
            for(var key in obj){
                params[key] =  obj[key];
            }

            for(var key in params){
                url +="&"+key+"="+params[key];
            }
            params.ctl_type = 'view';
            //window.location.href = url;
            window.open(url);
            params.is_detail = 0;
        });
    })

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

    //生成退货单
    function create_return(index, row) {
        var d = {"return_id": row.id, 'app_fmt': 'json'};
        var url = "?app_act=api/api_weipinhuijit_return/create_view&return_id=" + row.id;
        _do_execute(url, 'table', '生成批发退货单', 670, 250);
    }

    //数据行双击打开新页面显示详情
    function showDetail(index, row) {
        var url = '?app_act=api/api_weipinhuijit_return/view&return_sn=' + row.return_sn;
        openPage(window.btoa(url), url, '唯品会JIT退供单详情');
    }

    //批发退货单详情
    function view_return_record(return_record_id) {
        var url = '?app_act=wbm/return_record/view&return_record_id=' + return_record_id;
        openPage(window.btoa(url), url, '批发退货单');
    }

//批量生成退货单
    $(".create_wbm_return_record").click(function () {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择订单", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.id);
        }
        ids.join(',');
        //校验是否绑定批发通知单
        var d = {"return_ids": ids.toString(), 'app_fmt': 'json'};
        $.post("?app_act=api/api_weipinhuijit_return/check_return_more", d, function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            if (type == 'error') {
                BUI.Message.Alert(data.message, type);
            } else {
                var url = "?app_act=api/api_weipinhuijit_return/create_view&return_id=" + ids.toString();
                _do_execute(url, 'table', '生成批发退货单', 670, 250);
            }
        }, "json");
    })

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
            ids.push(row.id);
        }
        ids.join(',');
        BUI.Message.Show({
            title: '自定义提示框',
            msg: '是否执行拣货单' + obj.text() + '?',
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