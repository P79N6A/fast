<style type="text/css">
    #time_start{width:100px;}
    #time_end{width:100px;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '批发销货单',
    'links' => array(
        array('url' => 'wbm/store_out_record/detail&app_scene=add', 'title' => '添加批发销货单', 'is_pop' => true, 'pop_size' => '500,550'),
    ),
    'ref_table' => 'table'
));
?>
<?php
$keyword_type = array();
$keyword_type['record_code'] = '单据编号';
$keyword_type['express'] = '物流单号';
$keyword_type['relation_code'] = '批发通知单号';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['goods_name'] = '商品名称';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['remark'] = '备注';
$keyword_type = array_from_dict($keyword_type);

$time_type = array();
$time_type['order_time'] = '下单时间';
$time_type['is_store_out_time'] = '验收时间';
$time_type = array_from_dict($time_type);
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
    /* array(
      'label' => '导出',
      'id' => 'exprot_list',
      ), */
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
            'id' => 'is_store_out',
            'data' => array(
                array('0', '未验收'), array('1', '已验收')
            )),
        array(
            'label' => array('id' => 'time_type', 'type' => 'select', 'data' => $time_type),
            'type' => 'group',
            'field' => 'time_type',
            'data' => $time_type,
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'time_start'),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'time_end'),
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
        	'label' => '批发类型',
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
                'width' => '208',
                'align' => '',
                'buttons' => array(
                    array(
                        'id' => 'view',
                        'title' => '查看',
                        'callback' => 'do_view'
                    ),
                    array(
                        'id' => 'enable',
                        'title' => '验收',
                        'callback' => 'do_shift_out',
                        'priv' => 'wbm/store_out_record/do_checkin',
                        'show_cond' => 'obj.is_store_out != 1'
                    ),
                    array(
                        'id' => 'shift_out_by_record_date',
                        'title' => '按业务日期验收',
                        'callback' => 'do_shift_out_by_record_date',
                        'priv' => 'wbm/store_out_record/do_checkin_time',
                        'show_cond' => 'obj.is_store_out != 1'
                    ),
                    array(
                        'id' => 'delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
                        'priv' => 'wbm/store_out_record/do_delete',
                        'show_cond' => 'obj.is_sure != 1',
                       // 'confirm' => '确认要删除此信息吗？'
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '验收',
                'field' => 'is_store_out',
                'width' => '50',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据编号',
                'field' => 'record_code',
                'width' => '130',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({store_out_record_id})" >{record_code}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '通知单号',
                'field' => 'relation_code',
                'width' => '160',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '业务类型',
                'field' => 'type_name',
                'width' => '80',
                'align' => ''
            ),
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
                'title' => '验收日期',
                'field' => 'is_store_out_time',
                'width' => '100',
                'align' => '',
                'format' => array('type' => 'time')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分销商',
                'field' => 'distributor_code_name',
                'width' => '100',
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
                'title' => '配送方式',
                'field' => 'express_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '快递单号',
                'field' => 'express',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '实际出库数',
                'field' => 'num',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '通知数',
                'field' => 'enotice_num',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '差异数',
                'field' => 'num_differ',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '总金额',
                'field' => 'money',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'remark',
                'width' => '180',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'wbm/StoreOutRecordModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'store_out_record_id',
    'customFieldTable'=> 'store_out_record_combine/table',
     //'export'=> array('id'=>'exprot_detail','conf'=>'store_out_record_list_detail','name'=>'批发销货'),
    'export' => array('id' => 'exprot_list', 'conf' => 'store_out_record_list', 'name' => '批发销货单','export_type' => 'file'),
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
        params = tableStore.get('params');
       
        params.ctl_type = 'export';
        params.ctl_export_conf = 'store_out_record_list_detail';
        params.ctl_export_name =  '批发销货单明细';
        <?php echo   create_export_token_js('wbm/StoreOutRecordModel::get_by_page');?>
        var obj = searchFormForm.serializeToObject();
          for(var key in obj){
                 params[key] =  obj[key];
	  } 
     
          for(var key in params){
                url +="&"+key+"="+params[key];
	  }
          params.ctl_type = 'view';
          window.open(url); 
       // window.location.href = url;
    });
});

  function do_delete(_index, row) {
    BUI.Message.Confirm('确认要删除吗？',function(){
        $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '<?php echo get_app_url('wbm/store_out_record/get_num_by_record_code'); ?>',
        data: {record_code: row.record_code},
        success: function (ret) {
            if (ret.status <0 ) {
                BUI.Message.Confirm('有出库数据，确认要删除吗？', function () {
                    $.ajax({
                        type: 'POST',
                        dataType: 'json',
                        url: '<?php echo get_app_url('wbm/store_out_record/do_delete'); ?>',
                        data: {store_out_record_id: row.store_out_record_id},
                        success: function (ret) {
                            var type = ret.status == 1 ? 'success' : 'error';
                            if (type == 'success') {
                                BUI.Message.Alert('删除成功!', type);
                                tableStore.load();
                            } else {
                                BUI.Message.Alert(ret.message, type);
                            }
                        }
                    });
                }, 'question');
            } else {
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: '<?php echo get_app_url('wbm/store_out_record/do_delete'); ?>',
                    data: {store_out_record_id: row.store_out_record_id},
                    success: function (ret) {
                        var type = ret.status == 1 ? 'success' : 'error';
                        if (type == 'success') {
                            BUI.Message.Alert('删除成功!', type);
                            tableStore.load();
                        } else {
                            BUI.Message.Alert(ret.message, type);
                        }
                    }
                });
            }
        }
        });
   },'question');
 }


    function  do_re_sure(_index, row) {
        url = '?app_act=wbm/store_out_record/do_sure';
        data = {id: row.store_out_record_id, type: 'disable'};
        _do_operate(url, data, 'table');
    }
    function  do_sure(_index, row) {

        url = '?app_act=wbm/store_out_record/do_sure';
        data = {id: row.store_out_record_id, type: 'enable'};
        _do_operate(url, data, 'table');
    }
    
    function check_diff_num (row, type) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?app_act=wbm/store_out_record/check_diff_num',
            data: {record_code: row.record_code},
            success: function (ret) {
                var sta = ret.status;
                if (sta == 1) {
                    BUI.Message.Confirm('是否确认验收？ ', function(){
                        if (type === 'normal') {
                            var url = '<?php echo get_app_url('wbm/store_out_record/do_shift_out_weipinhui_check'); ?>';
                            var params = {record_code: row.record_code};
                            $.post(url, params, function (data) {
                                if (data.status == -1) {
                                    BUI.Message.Confirm(data.message, function () {
                                        do_shift_out_action(row);
                                    });
                                }else if(data.status == 0){
                                    BUI.Message.Alert(data.message,'error');
                                } else {
                                    do_shift_out_action(row);
                                }
                            }, "json");
                        } else {
                            var url = '<?php echo get_app_url('wbm/store_out_record/do_shift_out_weipinhui_check'); ?>';
                            var params = {record_code: row.record_code};
                            $.post(url, params, function (data) {
                                if (data.status == -1) {
                                    BUI.Message.Confirm(data.message, function () {
                                        do_shift_out_by_record_date_action(row);
                                    });
                                }else if(data.status == 0){
                                    BUI.Message.Alert(data.message,'error');
                                }  else {
                                    do_shift_out_by_record_date_action(row);
                                }
                            }, "json");
                        }
                    }, 'question');
                    tableStore.load();
                } else if (sta == 2) {
                    BUI.Message.Confirm(ret.message, function(){
                        if (type === 'normal') {
                            var url = '<?php echo get_app_url('wbm/store_out_record/do_shift_out_weipinhui_check'); ?>';
                            var params = {record_code: row.record_code};
                            $.post(url, params, function (data) {
                                if (data.status == -1) {
                                    BUI.Message.Confirm(data.message, function () {
                                        do_shift_out_action(row);
                                    });
                                }else if(data.status == 0){
                                    BUI.Message.Alert(data.message,'error');
                                } else {
                                    do_shift_out_action(row);
                                }
                            }, "json");
                        } else {
                            var url = '<?php echo get_app_url('wbm/store_out_record/do_shift_out_weipinhui_check'); ?>';
                            var params = {record_code: row.record_code};
                            $.post(url, params, function (data) {
                                if (data.status == -1) {
                                    BUI.Message.Confirm(data.message, function () {
                                        do_shift_out_by_record_date_action(row);
                                    });
                                }else if(data.status == 0){
                                    BUI.Message.Alert(data.message,'error');
                                }  else {
                                    do_shift_out_by_record_date_action(row);
                                }
                            }, "json");
                        }
                    }, 'question');
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, 'error');
                }
            }
        });
    }
    
    //出库
    function do_shift_out(_index, row) {
        check_diff_num (row, 'normal');
    }

    //按业务日期验收
    function do_shift_out_by_record_date(_index, row) {
        check_diff_num (row, 'date');
    }

    function do_shift_out_by_record_date_action(row) {
        url = '?app_act=wbm/store_out_record/do_shift_out_by_record_date';
        data = {record_code: row.record_code};
        _do_operate(url, data, 'table');
    }

    function do_shift_out_action(row) {
        url = '?app_act=wbm/store_out_record/do_shift_out';
        data = {record_code: row.record_code};
        _do_operate(url, data, 'table');
    }

    /*
     function do_enable(_index, row) {
     _do_set_check(_index, row, 'enable');
     }
     */


    /**
     * 查看批发销货单详情
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
        openPage('<?php echo base64_encode('?app_act=wbm/store_out_record/view&store_out_record_id') ?>' + row.store_out_record_id, '?app_act=wbm/store_out_record/view&store_out_record_id=' + row.store_out_record_id, '批发销货单详情');
    }

    function view(store_out_record_id) {
        openPage('<?php echo base64_encode('?app_act=wbm/store_out_record/view&store_out_record_id') ?>' + store_out_record_id, '?app_act=wbm/store_out_record/view&store_out_record_id=' + store_out_record_id, '批发销货单详情');
    }
</script>
