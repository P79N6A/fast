<style type="text/css">
    .well {
        min-height: 100px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '商品移仓单',
    'links' => array(
        array('url' => 'stm/store_shift_record/detail&app_scene=add', 'title' => '添加商品移仓单', 'is_pop' => true, 'pop_size' => '500,550'),
    ),
    'ref_table' => 'table'
));
?>
<?php
$keyword_type = array();
$keyword_type['record_code'] = '单据编号';
$keyword_type['init_code'] = '原单号';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
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
            'label' => '移出仓库',
            'type' => 'select_multi',
            'id' => 'shift_out_store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
        array(
            'label' => '移入仓库',
            'type' => 'select_multi',
            'id' => 'shift_in_store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
        array(
            'label' => '下单日期',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'order_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'order_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '移入日期',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'shift_in_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'shift_in_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '移出日期',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'shift_out_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'shift_out_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '单据状态',
            'title' => '',
            'type' => 'select',
            'id' => 'shift_status',
            'data' => array(array('', '全部'), array('0', '未确认'), array('1', '已确认未出库'), array('2', '已出库未入库')
                , array('3', '已出库已入库')
            )),
        array(
            'label' => '备注',
            'type' => 'input',
            'id' => 'remark'
        ),
        array(
            'label' => '创建人',
            'type' => 'input',
            'id' => 'add_person'
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
                        'priv' => 'stm/store_shift_record/confirm',
                        'show_cond' => 'obj.is_sure == 0'
                    ),
                    array(
                        'id' => 'check2',
                        'title' => '取消确认',
                        'callback' => 'do_re_sure',
                        'priv' => 'stm/store_shift_record/confirm',
                        'show_cond' => 'obj.is_shift_out != 1 && obj.is_sure == 1 '
                    ),
                    array(
                        'id' => 'enable',
                        'title' => '出库',
                        'callback' => 'do_shift_out',
                        'priv' => 'stm/store_shift_record/output',
                        'show_cond' => 'obj.is_shift_out != 1 && obj.is_sure == 1 && (obj.is_wms_out==0 || obj.is_same_outside_code==1) '
                    ),
                    array(
                        'id' => 'delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
                        'priv' => 'stm/store_shift_record/delete',
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
                'title' => '出库',
                'field' => 'is_shift_out',
                'width' => '50',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '入库',
                'field' => 'is_shift_in',
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
                    'value' => '<a href=javascript:view({shift_record_id})>{record_code}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '原单号',
                'field' => 'init_code',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '业务时间（出库）',
                'field' => 'record_time',
                'width' => '120',
                'align' => '',
                'format' => array('type' => 'date')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '业务时间（入库）',
                'field' => 'shift_in_time',
                'width' => '120',
                'align' => '',
                'format' => array('type' => 'date')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '移出日期',
                'field' => 'is_shift_out_time',
                'width' => '100',
                'align' => '',
                'format' => array('type' => 'date')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '移入日期',
                'field' => 'is_shift_in_time',
                'width' => '100',
                'align' => '',
                'format' => array('type' => 'date')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '移出仓库',
                'field' => 'shift_out_store_code_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '移入仓库',
                'field' => 'shift_in_store_code_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '移出数量',
                'field' => 'out_num',
                'width' => '70',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '移入数量',
                'field' => 'in_num',
                'width' => '70',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '差异数量',
                'field' => 'difference_num',
                'width' => '70',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '移出金额',
                'field' => 'out_money',
                'width' => '80',
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
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '创建人',
                'field' => 'is_add_person',
                'width' => '100',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'stm/StoreShiftRecordModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'shift_record_id',
    'params' => array('filter' => array('user_id' => $response['user_id'])),
    //'RowNumber'=>true,
    //'CheckSelection'=>true,
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">

$(function(){
      $('#exprot_detail').click(function(){
        var url =  '?app_act=sys/export_csv/export_show';   
        params = tableStore.get('params');
       
        params.ctl_type = 'export';
        params.ctl_export_conf = 'store_shift_record_list_detail';
        params.ctl_export_name =  '仓库调账单明细';
        <?php echo   create_export_token_js('stm/StoreShiftRecordModel::get_by_page');?>
        var obj = searchFormForm.serializeToObject();
          for(var key in obj){
                 params[key] =  obj[key];
	  } 
     
          for(var key in params){
                url +="&"+key+"="+params[key];
	  }
          window.open(url); 
    });
});
    function do_delete(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('stm/store_shift_record/do_delete'); ?>',
            data: {shift_record_id: row.shift_record_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

//取消确认
    function  do_re_sure(_index, row) {
        url = '?app_act=stm/store_shift_record/do_sure';
        data = {id: row.shift_record_id, type: 'disable'};
        _do_operate(url, data, 'table');
    }
    //确认
    function  do_sure(_index, row) {
        url = '?app_act=stm/store_shift_record/do_sure';
        data = {id: row.shift_record_id, type: 'enable'};
        _do_operate(url, data, 'table');
    }
//出库
    function do_shift_out(_index, row) {

        url = '?app_act=stm/store_shift_record/do_shift_out';
        data = {id: row.shift_record_id};

        _do_operate(url, data, 'table');
    }
    /**
     * 查看移仓单详情
     * @param _index
     * @param row
     */
    function do_view(_index, row) {
        view(row.shift_record_id);
    }

    //数据行双击打开新页面显示详情
    function showDetail(_index, row) {
        view(row.shift_record_id);
    }
    function view(shift_record_id) {
        openPage('<?php echo base64_encode('?app_act=stm/store_shift_record/view&shift_record_id') ?>' + shift_record_id, '?app_act=stm/store_shift_record/view&shift_record_id=' + shift_record_id, '移仓单详情');
    }
</script>
