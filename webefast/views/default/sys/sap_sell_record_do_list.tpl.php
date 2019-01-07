<style>
    #upload_date_start,#upload_date_end{
        width: 90px;
    }
</style>
<div class="page-header1" style="width:98%; display: block; clear: both; position: fixed; top:0px; left:0px; background-color: #FFF; padding: 4px 1%; z-index: 9999; box-shadow:0px 0px 5px #ccc">
    <span class="page-title"><h2>零售单据</h2></span>
    <span class="page-link">
        <span class="action-link"><a onclick="insert_record()" href="#" class="button button-primary">

                更新单据</a>
        </span>
        <button class="button button-primary" onclick="javascript:location.reload();"><i class="icon-refresh icon-white"></i> 刷新</button>
    </span>
</div>
<div class="clear" style="margin-top: 40px; "></div>
<?php
$record_type = array(
    'all' => '全部',
    '1' => '销售退单',
    '0' => '销售订单',
    '2' => '月度积分'
);

$record_type = array_from_dict($record_type);
render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
        'id' => 'btn-search',
    ),
    'fields' => array(
        array(
            'label' => '订/退单号',
            'title' => '',
            'type' => 'input',
            'id' => 'record_code',
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_sap_shop(),
        ),
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_sap_store(),
        ),
        array(
            'label' => '单据类型',
            'type' => 'select',
            'id' => 'order_type',
            'data' => $record_type,
        ),
        array(
            'label' => '上传时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'upload_date_start'/* , 'value' => date('Y-m-01 00:00:00') */),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'upload_date_end', 'remark' => ''),
            )
        ),
    )
));
?>
<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '未上传', 'active' => true, 'id' => 'no_upload'),
        array('title' => '已上传', 'active' => false, 'id' => 'ok_upload'),
    ),
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<?php
render_control('DataTable', 'table', array('conf' => array('list' => array(
            array('type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '60',
                'align' => '',
                'buttons' => array(
                    array(
                        'id' => 'edit',
                        'title' => '上传',
                        'show_name' => '上传',
                        'callback' => 'do_execute',
                        'priv' => 'sys/sap_sell_record/do_upload',
                        'show_cond' => 'obj.order_status == 0 || obj.order_status == 2 ',
                    ),
//                    array('id' => 'delete', 'title' => '删除',
//                        'callback' => 'do_delete',
//                        'confirm' => '确定要删除这条辛辛苦苦录入的数据吗？'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据类型',
                'field' => 'order_type',
                'width' => '80',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订/退单号',
                'field' => 'record_code',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '销售平台',
                'field' => 'sale_channel_code_name',
                'width' => '80',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'store_code_name',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '总数量',
                'field' => 'goods_num',
                'width' => '60',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '总金额',
                'field' => 'payable_money',
                'width' => '80',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '上传失败信息',
                'field' => 'upload_info',
                'width' => '200',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '上传时间',
                'field' => 'upload_date',
                'width' => '200',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'sys/SapSellRecordModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'sap_record_id',
//    'CheckSelection' => true,
));
?>

<script type="text/javascript">
    var fullMask;
    $(document).ready(function () {
        //TAB选项卡
        $("#TabPage1 a").click(function () {
            tableStore.load();
        });
        tableStore.on('beforeload', function (e) {
            e.params.ex_list_tab = $("#TabPage1").find(".active").find("a").attr("id")
            tableStore.set("params", e.params);
        });
        BUI.use(['bui/mask'],function(Mask){
            fullMask = new Mask.LoadMask({
                el : 'body',
                msg : '请稍后...'
            });
        });
    });
    function insert_record(_index, row) {
        fullMask.show();  
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('sys/sap_sell_record/insert_record');
?>', data: {},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    fullMask.hide();  
                    BUI.Message.Alert(ret.message, type);
                    tableStore.load();
                } else {
                    fullMask.hide();  
                    BUI.Message.Alert(ret.message, type);
                    tableStore.load();
                }
            }
        });
    }
    //单个上传
    function do_execute(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('sys/sap_sell_record/do_upload');
?>', data: {sap_record_id: row.sap_record_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                    tableStore.load();
                }
            }
        });
    }
</script>
<!--<div>
    <ul  class="toolbar frontool"  id="ToolBar3">
        <li class="li_btns"><button class="button button-primary btn_opt_edit_order_remark">批量上传</button></li>
        <div class="front_close">&lt;</div>
    </ul>
</div>-->
<script>
    $(function () {
        function tools() {
            $(".frontool").css({left: '0px'});
            $(".front_close").click(function () {
                if ($(this).html() == "&lt;") {
                    $(".frontool").animate({left: '-100%'}, 1000);
                    $(this).html(">");
                    $(this).addClass("close_02").animate({right: '-10px'}, 1000);
                } else {
                    $(".frontool").animate({left: '0px'}, 1);
                    $(this).html("<");
                    $(this).removeClass("close_02").animate({right: '0'}, 1000);
                }
            });
        }
        tools();
    });
    //批量上传
//    $(".btn_opt_edit_order_remark").click(function () {
//        get_checked(false,$(this), function (ids) {
//            var params = {"sap_record_id": ids};
//            $.post("?app_act=sys/sap_sell_record/all_upload", params, function (data) {
//                if (data.status == '1') {
//                    //刷新数据
//                    BUI.Message.Alert('上传成功', 'success');
//                    tableStore.load()
//                } else {
//                    BUI.Message.Alert('上传失败', 'error');
//                }
//            }, "json");
//
//        })
//    })
    //读取已选中项
    function get_checked(isConfirm, obj, func) {
        /*var ids = $("[name=ckb_record_id]:checkbox:checked").map(function(){
         return $(this).val()
         }).get()*/

        var ids = []
        var selecteds = tableGrid.getSelection();
        for (var i in selecteds) {
            ids.push(selecteds[i].sap_record_id)
        }

        if (ids.length == 0) {
            BUI.Message.Alert("请选择订单", 'error');
            return
        }
        func.apply(null, [ids]);
    }
</script>