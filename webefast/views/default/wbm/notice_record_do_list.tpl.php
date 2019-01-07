<style type="text/css">
    .well {
        min-height: 100px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '批发销货通知单',
    'links' => array(
        array('url' => 'wbm/notice_record/detail&app_scene=add', 'title' => '添加批发销货通知单', 'is_pop' => true, 'pop_size' => '500,550'),
    ),
    'ref_table' => 'table'
));
?>
<?php
$keyword_type = array();
$keyword_type['record_code'] = '单据编号';
$keyword_type['jx_code'] = '经销采购订单编号';
$keyword_type = array_from_dict($keyword_type);
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
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
            'label' => '分销商',
            'type' => 'group',
            'field' => 'custom',
            'child' => array(
                    array('type' => 'select_multi','field'=>'distributor_code','data' => $response['fenxiao'],'readonly'=>1,'remark' => "<a href='#' id = 'base_custom'><img src='assets/img/search.png'></a>"),
                ),
        ),
        array(
            'label' => '仓库',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store()
        ),
        array(
            'label' => '单据状态',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'is_stop',
            'data' => array(
                array('0', '未终止'), array('1', '已终止')
            )),
        array(
            'label' => '商品',
            'title' => '商品编码/商品名称',
            'type' => 'input',
            'id' => 'code_name'
        ),
        array(
            'label' => '商品条码',
            'title' => '商品条码',
            'type' => 'input',
            'id' => 'barcord'
        ),
        array(
            'label' => '下单时间',
            'type' => 'group',
            'field' => 'record_time',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'order_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'order_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '业务日期',
            'type' => 'group',
            'field' => 'record_time',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'record_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'record_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '是否有销货单',
            'type' => 'select',
            'id' => 'is_execute',
            'data' => ds_get_select_by_field('is_build',1)
        ),
//        array(
//        	'label' => '批发类型',
//        	'type' => 'select_multi',
//        	'id' => 'record_type_code',
//        	'data' => ds_get_select('record_type', 0, array('record_type_property' => 2))
//        ),
        array(
            'label' => '备注',
            'title' => '',
            'type' => 'input',
            'id' => 'remark',
        ),
        array(
            'label' => '业务类型',
            'type' => 'select_multi',
            'id' => 'record_type_code',
            'data' => ds_get_select('record_type', 0, array('record_type_property' => 2))
        ),
        array(
            'label' => '差异款',
            'type' => 'select',
            'id' => 'difference_models',
            'data' => array(
                array('', '全部'), array('1', '是'), array('0', '否')
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
                'width' => '120',
                'align' => '',
                'buttons' => array(
                    array(
                        'id' => 'view',
                        'title' => '查看',
                        'callback' => 'do_view'
                    ),
                    array(
                        'id' => 'check1',
                        'title' => '确认',
                        'callback' => 'do_sure',
                        'priv' => 'wbm/notice_record/do_sure',
                        'show_cond' => 'obj.is_sure == 0'
                    ),
                    array(
                        'id' => 'check2',
                        'title' => '取消确认',
                        'callback' => 'do_re_sure',
                        'priv' => 'wbm/notice_record/do_sure',
                        'show_cond' => 'obj.is_stop == 0 && obj.is_sure == 1 && obj.is_finish == 0 && obj.is_execute == 0 '
                    ),
                    array(
                        'id' => 'enable',
                        'title' => '生成销货单',
                        'callback' => 'do_execute_before',
                        'priv' => 'wbm/notice_record/do_execute',
                        'show_cond' => 'obj.is_stop == 0 && obj.is_sure == 1 && obj.is_wms != 1 && obj.jit_relation!=1 && obj.is_finish == 0'
                    ),
                    array(
                        'id' => 'stop',
                        'title' => '终止',
                        'callback' => 'do_stop',
                        'priv' => 'wbm/notice_record/do_stop',
                        'show_cond' => 'obj.is_stop == 0 && obj.is_sure == 1 && obj.is_finish == 0 && obj.is_wms != 1'
                    ),
                    array(
                        'id' => 'delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
                        'priv' => 'wbm/notice_record/do_delete',
                        'show_cond' => 'obj.is_sure != 1',
                        'confirm' => '确认要删除此信息吗？'
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '确认',
                'field' => 'is_sure',
                'width' => '50',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '生成销货单',
                'field' => 'is_execute',
                'width' => '100',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '终止',
                'field' => 'is_stop',
                'width' => '50',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据编号',
                'field' => 'record_code',
                'width' => '160',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({notice_record_id})">{record_code}</a>',
                ),
            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '业务类型',
//                'field' => 'type_name',
//                'width' => '80',
//                'align' => ''
//            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '下单时间',
                'field' => 'order_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '业务日期',
                'field' => 'record_time',
                'width' => '100',
                'align' => '',
                'format' => array('type' => 'date')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分销商',
                'field' => 'distributor_code_name',
                'width' => '120',
                'align' => '',
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
                'title' => '数量',
                'field' => 'num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '完成数量',
                'field' => 'finish_num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '差异数',
                'field' => 'diff_num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '总金额',
                'field' => 'money',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'remark',
                'width' => '100',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'wbm/NoticeRecordModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'store_out_record_id',
    // 'export'=> array('id'=>'exprot_list','conf'=>'return_record_list','name'=>'批发销货'),
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">    
    $("#base_custom").click(function () {
            show_select('custom');
        });
    function show_select(_type) {
            var param = {};
            if (typeof (top.dialog) != 'undefined') {
                top.dialog.remove(true);
            }
            var url = '?app_act=wbm/notice_record/select_custom';
            var buttons = [
                   {
                    text:'保存继续',
                    elCls : 'button button-primary',
                    handler: function () {
                        var data = top.SelectoGrid.getSelection();
                        if (data.length > 0) {
                            deal_data_1(data, _type);
                        }
                        auto_enter('#distributor_code');
                    }
                  },
                  {
                    text:'保存退出',
                    elCls : 'button button-primary',
                    handler: function () {
                        var data = top.SelectoGrid.getSelection();
                        if (data.length > 0) {
                            deal_data_1(data, _type);
                        }
                        auto_enter('#distributor_code');
                        this.close();
                    }
                  },
                  {
                    text:'重置',
                    elCls : 'button',
                    handler: function () {
                        reset_custom();
                    }
                  }
                ];
            top.BUI.use('bui/overlay', function (Overlay) {
                top.dialog = new Overlay.Dialog({
                    title: '选择分销商',
                    width: '700',
                    height: '550',
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
                top.dialog.on('closed', function (ev) {

                });
                top.dialog.show();
            });
        }
        function reset_custom(){
            $("#distributor_code").attr("value","");
            $("#distributor_code_select_multi .bui-select-input").attr("value","");
        }
        function deal_data_1(obj, _type) {
            var custom_name = new Array();
            var custom_code = new Array();
            var string_code = "";
            var string_name = "";
            string_code = $("#distributor_code").val();
            string_name = $("#distributor_code_select_multi .bui-select-input").val();
            $.each(obj, function (i, val) {
                custom_name[i] = val[_type + '_name'];
                custom_code[i] = val[_type + '_code'];
            });
                custom_name = custom_name.join(',');
                custom_code = custom_code.join(',');
            if(string_code == ""){   
                string_code =  custom_code;
                $("#distributor_code").val(string_code);
            }else{
                string_code = string_code + ','+ custom_code;
                $("#distributor_code").val(string_code);
            }
            if(string_name == ""){
                string_name =  custom_name;
                $("#distributor_code_select_multi .bui-select-input").val(string_name);
            }else{
                string_name = string_name + ','+ custom_name;
                $("#distributor_code_select_multi .bui-select-input").val(string_name);
            }
            
        }
        function auto_enter(_id) {
            var e = jQuery.Event("keyup");//模拟一个键盘事件
            e.keyCode = 13;//keyCode=13是回车
            $(_id).trigger(e);
        }
    //导出明细
    $(function(){
        $('#exprot_detail').click(function(){
            var url = '?app_act=sys/export_csv/export_show';
            //var url = tableStore.get('url');
            params = tableStore.get('params');

            params.ctl_type = 'export';
            params.ctl_export_conf = 'notice_record_list_detail';
            params.ctl_export_name =  '批发通知单明细';
            <?php echo   create_export_token_js('wbm/NoticeRecordModel::get_by_page');?>
            var obj = searchFormForm.serializeToObject();
            for(var key in obj){
                params[key] =  obj[key];
            }

            for(var key in params){
                url +="&"+key+"="+params[key];
            }
            params.ctl_type = 'view';
            window.open(url);
//             window.location.href = url;
        });
    });
    function do_delete(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('wbm/notice_record/do_delete'); ?>',
            data: {notice_record_id: row.notice_record_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功：', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }


    function  do_re_sure(_index, row) {
        url = '?app_act=wbm/notice_record/do_sure';
        data = {id: row.notice_record_id, type: 'disable'};
        _do_operate(url, data, 'table');
    }
    function  do_sure(_index, row) {

        url = '?app_act=wbm/notice_record/do_sure';
        data = {id: row.notice_record_id, type: 'enable'};
        _do_operate(url, data, 'table');
    }
    //终止
    function do_stop(_index, row) {

        url = '?app_act=wbm/notice_record/do_stop';
        data = {id: row.notice_record_id};

        _do_operate(url, data, 'table');
    }
    //生成销货单
    function do_execute_before(_index, row) {
        //判断是否唯品会通知单
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('wbm/notice_record/weipinhui_notice_record'); ?>',
            data: {notice_record_no: row.record_code},
            success: function (ret) {
                if (ret.status == 1) {
                    var tips = '此批发销货通知单是唯品会jit业务生成，创建时已自动创建对应销货单（' + ret.data.store_out_record_no + '），无需重新生成销货单！';
                    BUI.Message.Alert(tips, 'error');
                } else {
                    do_execute(_index, row);
                }
            }
        });
    }
    function do_execute(_index, row) {
        //判断是否有未入库销货单
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('wbm/notice_record/out_relation'); ?>',
            data: {id: row.notice_record_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    url = "?app_act==wbm/notice_record/execute&notice_record_id=" + row.notice_record_id.toString();
                    _do_execute(url, 'table');

                } else {

                    if (ret.status == '-1') {
                        BUI.Message.Confirm('存在未出库的批发销货单，是否继续？', function () {
                            url = "?app_act==wbm/notice_record/execute&notice_record_id=" + row.notice_record_id.toString();
                            _do_execute(url, 'table');
                        });
                    }

                    // BUI.Message.Alert(ret.message, type);
                }
            }
        });



    }
    /*
     function do_enable(_index, row) {
     _do_set_check(_index, row, 'enable');
     }
     */


    /**
     * 查看批发销货通知单详情
     * @param _index
     * @param row
     */
    function do_view(_index, row) {
        detail(_index, row)
    }

    //数据行双击打开新页面显示详情
    function showDetail(_index, row) {
        detail(_index, row)
    }
    function detail(_index, row) {
        openPage('<?php echo base64_encode('?app_act=wbm/notice_record/view&notice_record_id') ?>' + row.notice_record_id, '?app_act=wbm/notice_record/view&notice_record_id=' + row.notice_record_id, '销货通知单详情');
    }
    function view(notice_record_id) {
        openPage('<?php echo base64_encode('?app_act=wbm/notice_record/view&notice_record_id') ?>' + notice_record_id, '?app_act=wbm/notice_record/view&notice_record_id=' + notice_record_id, '销货通知单详情');
    }
</script>

