<style type="text/css">
    #time_start{width:100px;}
    #time_end{width:100px;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '批发退货单',
    'links' => array(
        array('url' => 'wbm/return_record/detail&app_scene=add', 'title' => '添加批发退货单', 'is_pop' => true, 'pop_size' => '500,550'),
    ),
    'ref_table' => 'table'
));
?>
<?php
$keyword_type = array();
$keyword_type['record_code'] = '单据编号';
$keyword_type['relation_code'] = '通知单号';
$keyword_type['code_name'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['init_code'] = '原单号';
$keyword_type = array_from_dict($keyword_type);

$time_type = array();
$time_type['order_time'] = '下单时间';
$time_type['is_store_in_time'] = '验收时间';
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
            'title' => '',
            'type' => 'select_multi',
            'id' => 'distributor_code',
            'data' => $response['fenxiao']
        ),
        array(
            'label' => '仓库',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_select()
        ),
        array(
            'label' => '单据状态',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'is_store_in',
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
            'label' => '备注',
            'title' => '',
            'type' => 'input',
            'id' => 'remark',
        ),
         array(
            'label' => '退货类型',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'type_code',
            'data' => ds_get_select('record_type', 0, array('record_type_property' => 3))
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
                'width' => '120',
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
                        'callback' => 'check_diff_num',
                        'priv' => 'wbm/return_record/do_shift_in',
                        'show_cond' => 'obj.is_store_in != 1'
                    ),
                    array(
                        'id' => 'delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
                        'priv' => 'wbm/return_record/do_delete',
                        'show_cond' => 'obj.is_store_in != 1',
                        'confirm' => '确认要删除此信息吗？'
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '验收',
                'field' => 'is_store_in',
                'width' => '50',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据编号',
                'field' => 'record_code',
                'width' => '150',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({return_record_id})" >{record_code}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '通知单号',
                'field' => 'relation_code',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '业务类型',
                'field' => 'type_name',
                'width' => '100',
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
                'field' => 'is_store_in_time',
                'width' => '100',
                'align' => '',
                'format' => array('type' => 'time')
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
                'title' => '实际入库数',
                'field' => 'num',
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
    'dataset' => 'wbm/ReturnRecordModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'return_record_id',
   'export'=> array('id'=>'exprot_list','conf'=>'wbm_return_record_list','name'=>'批发退货','export_type' => 'file'),
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    //导出明细
    $(function(){
          $('#exprot_detail').click(function(){
            var url = '?app_act=sys/export_csv/export_show';
            params = tableStore.get('params');

            params.ctl_type = 'export';
            params.ctl_export_conf = 'return_record_list_detail';
            params.ctl_export_name =  '批发退货单明细';
            <?php echo   create_export_token_js('wbm/ReturnRecordModel::get_by_page');?>
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
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('wbm/return_record/do_delete'); ?>',
            data: {return_record_id: row.return_record_id},
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
        url = '?app_act=wbm/return_record/do_sure';
        data = {id: row.return_record_id, type: 'disable'};
        _do_operate(url, data, 'table');
    }
    function  do_sure(_index, row) {

        url = '?app_act=wbm/return_record/do_sure';
        data = {id: row.return_record_id, type: 'enable'};
        _do_operate(url, data, 'table');
    }
    
    function check_diff_num (_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?app_act=wbm/return_record/check_diff_num',
            data: {record_code: row.record_code},
            success: function (ret) {
                var sta = ret.status;
                if (sta == 1) {
                    BUI.Message.Confirm('是否确认验收？ ', function(){
                        do_shift_in(_index, row);
                    }, 'question');
                    tableStore.load();
                } else if (sta == 2) {
                    BUI.Message.Confirm(ret.message, function(){
                        do_shift_in(_index, row);
                    }, 'question');
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, 'error');
                }
            }
        });
    }
    
    //出库
    function do_shift_in(_index, row) {
        url = '?app_act=wbm/return_record/do_shift_in';
        data = {record_code: row.record_code};
        _do_operate(url, data, 'table');
    }

    /**
     * 查看批发退货单详情
     * @param _index
     * @param row
     */
    function do_view(_index, row) {
        detail(_index, row);
    }

    //数据行双击打开新页面显示详情
    function showDetail(_index, row) {
        detail(_index, row);
    }
    function detail(_index, row) {
        openPage('<?php echo base64_encode('?app_act=wbm/return_record/view&return_record_id=') ?>' + row.return_record_id, '?app_act=wbm/return_record/view&return_record_id=' + row.return_record_id, '批发退货单详情');
    }

    function view(return_record_id) {
        openPage('<?php echo base64_encode('?app_act=wbm/return_record/view&return_record_id=') ?>' + return_record_id, '?app_act=wbm/return_record/view&return_record_id=' + return_record_id, '批发退货单详情');
    }
</script>
