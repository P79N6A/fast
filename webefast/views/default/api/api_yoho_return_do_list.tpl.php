<style>
    #create_time_start{width:85px;}
    #create_time_end{width:85px;}
</style>
<?php echo load_js('comm_util.js') ?>
<?php echo load_js("pur.js", true); ?>
<?php
render_control('PageHead', 'head1', array('title' => '有货采购退单管理',
    'links' => array(
        array('type' => 'js', 'js' => 'get_yoho_return()', 'title' => '获取采购退单'),
    ),
    'ref_table' => 'table'
));
?>
<?php
$keyword_type['purchase_no'] = '采购单号';
$keyword_type['return_notice_code'] = '退货通知单号';
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
            'label' => '生成通知单',
            'type' => 'select',
            'id' => 'is_execute',
            'data' => ds_get_select_by_field('is_build', 1),
        ),
        array(
            'label' => '创建时间',
            'type' => 'group',
            'field' => 'create_time',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'create_time_start'),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'create_time_end', 'remark' => ''),
            )
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
                    array('id' => 'create_out_record', 'title' => '生成退货单', 'callback' => 'create_return', 'show_cond' => 'obj.is_execute==0'),
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
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '采购单号',
                'field' => 'purchase_no',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退货数量',
                'field' => 'numbers',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '创建时间',
                'field' => 'create_time',
                'width' => '150',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'api/YohoReturnModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'CheckSelection' => true,
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
   /// 'params' => array('filter' => array('jit_version' => 1)),
    'export' => array('id' => 'exprot_list', 'conf' => 'api_yoho_return_do_list', 'name' => '有货采购退单管理','export_type' => 'file'),//
    'CascadeTable' => array(
        'list' => array(
            array('title' => '关联退货通知单', 'width' => '150', 'field' => 'record_code', 'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view_notice_record({return_notice_record_id})">{return_notice_code}</a>',
                ),
            ),
            array('title' => 'eFAST仓库', 'field' => 'store_name'),
            array('title' => '已通知数量', 'field' => 'num'),
            array('title' => '入库数量', 'field' => 'finish_num'),
            array('title' => '创建时间', 'width' => '150', 'field' => 'order_time'),
        ),
        'page_size' => 50,
        'url' => get_app_url('api/api_yoho_return/get_notice_record_by_purchase&app_fmt=json'),
        'params' => 'purchase_no',
    ),
));
?>
<ul id="ToolBar1" class="toolbar frontool">
    <li class="li_btns"><button class="button button-primary multi_create_return">生成退货单</button></li>
</ul>
<script>
    $(function () {
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
</script>
<script type="text/javascript">
    //数据行双击打开新页面显示详情
    function showDetail(index, row) {
        var url = '?app_act=api/api_yoho_return/view&id=' + row.id;
        openPage(window.btoa(url), url, '有货采购退单详情');
    }

    //生成退货
    function create_return(index, row) {
        var url = "?app_act=api/api_yoho_return/create_view&id=" + row.id + "&shop_code=" + row.shop_code;
        _do_execute(url, 'table', '生成批发退货单', 670, 300);
    }

    //批发通知单详情
    function view_notice_record(return_notice_record_id) {
        var url = '?app_act=wbm/return_notice_record/view&return_notice_record_id=' + return_notice_record_id;
        openPage(window.btoa(url), url, '批发退货通知单');
    }

    //调用接口
    function get_yoho_return(){
        var url = "?app_act=api/api_yoho_return/get_yoho_view";
        _do_execute(url, 'table', '获取采购退单', 550, 495);
    }

    //读取已选中项
    function get_checked(obj, func) {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择采购退单！", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.id);
        }
        ids.join(',');
        func.apply(null, [ids]);
    }


    $(".multi_create_return").click(function () {
        get_checked($(this), function (ids) {
            var params = {"return_ids": ids.toString(), 'app_fmt': 'json'};
            $.post("?app_act=api/api_yoho_return/check_return_more", params, function (data) {
                var type = data.status == 1 ? 'success' : 'error';
                if (type == 'error') {
                    BUI.Message.Alert(data.message, type);
                } else {
                    var url = "?app_act=api/api_yoho_return/create_view&id=" + ids.toString();
                    _do_execute(url, 'table', '生成批发退货单', 670, 300);
                }
            }, "json");
        });
    });



</script>



