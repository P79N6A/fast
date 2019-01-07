<style type="text/css">
    .well {
        min-height: 100px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '仓库调整单列表',
    'links' => array(
        array('url' => 'stm/stock_adjust_record/detail&app_scene=add', 'title' => '添加仓库调整单', 'is_pop' => true, 'pop_size' => '500,550'),
    ),
    'ref_table' => 'table'
));
?>
<?php
$keyword_type = array();
$keyword_type['record_code'] = '单据编号';
$keyword_type['code_name'] = '商品编码';
$keyword_type['code_sku'] = '商品条形码';
$keyword_type['relation_code'] = '关联单号';
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
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
        array(
            'label' => '类型',
            'type' => 'select_multi',
            'id' => 'adjust_type',
            'data' => $response['adjust_type'],
        ),
        array(
            'label' => '验收',
            'type' => 'select_multi',
            'id' => 'is_check_and_accept',
            'data' => array(
                array('1', '已验收'), array('0', '未验收')
            )
        ),
        /**
         * array(
         * 'label' => '原单号',
         * 'type' => 'input',
         * 'id' => 'init_code'
         * ),* */
        array(
            'label' => '下单日期',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'is_add_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'is_add_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '调整原因',
            'type' => 'input',
            'id' => 'remark',
        ),
        array(
            'label' => '业务日期',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'record_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'record_time_end', 'remark' => ''),
            )
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
                        'id' => 'enable',
                        'title' => '验收',
                        'callback' => 'do_enable',
                        'show_cond' => 'obj.is_check_and_accept != 1'
                    ),
                    array(
                        'id' => 'delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
                        'show_cond' => 'obj.is_check_and_accept != 1',
                        'confirm' => '确认要删除此信息吗？'
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '验收',
                'field' => 'is_check_and_accept',
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
//                    'value' => '<a href="' . get_app_url('stm/stock_adjust_record/view') . '&stock_adjust_record_id={stock_adjust_record_id}">{record_code}</a>',
                    'value' => '<a href="javascript:view({stock_adjust_record_id})">{record_code}</a>',
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
                'title' => '关联单号',
                'field' => 'relation_code',
                'width' => '100',
                'align' => ''
            ),
            /**
             * array(
             * 'type' => 'text',
             * 'show' => 1,
             * 'title' => '下单时间',
             * 'field' => 'is_add_time',
             * 'width' => '180',
             * 'align' => ''
             * ),* */
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '业务时间',
                'field' => 'record_time',
                'width' => '100',
                'align' => '',
                'format' => array('type' => 'date')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '调整类型',
                'field' => 'adjust_type_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '调整原因',
                'field' => 'remark',
                'width' => '250',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'store_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '数量',
                'field' => 'num',
                'width' => '60',
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
//             array(
//                 'type' => 'text',
//                 'show' => 1,
//                 'title' => '金额',
//                 'field' => 'money',
//                 'width' => '100',
//                 'align' => ''
//             ),
        )
    ),
    'dataset' => 'stm/StockAdjustRecordModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'stock_adjust_record_id',
    'export' => array('id' => 'exprot_list', 'conf' => 'stock_adjust_record_list', 'name' => '调整单','export_type' => 'file'),
    //'CheckSelection'=>true,
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>
<script type="text/javascript">
    // tableStore.set('pageSize', 2);
    // tableStore.load(); 


    //var obj = {"type":"list","page_size":"2"};
    //obj.start = 1;
    //tableStore.set('pageSize', 2);
    //tableStore.load(obj);
    //导出明细
$(function(){
      $('#exprot_detail').click(function(){
        var url =  '?app_act=sys/export_csv/export_show';   
        params = tableStore.get('params');
       
        params.ctl_type = 'export';
        params.ctl_export_conf = 'stock_adjust_record_list_detail';
        <?php echo   create_export_token_js('stm/StockAdjustRecordModel::get_by_page');?>
        params.ctl_export_name =  '仓库调账单明细';
        var obj = searchFormForm.serializeToObject();
          for(var key in obj){
                 params[key] =  obj[key];
	  } 
     
          for(var key in params){
                url +="&"+key+"="+params[key];
	  }
          window.open(url); 
       // window.location.href = url;
    });
});
    function do_delete(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('stm/stock_adjust_record/do_delete'); ?>',
            data: {stock_adjust_record_id: row.stock_adjust_record_id},
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

    function do_enable(_index, row) {
        _do_set_check(_index, row, 'enable');
    }

    /**
     * 验收调整单
     * @param _index
     * @param row
     * @param active
     * @private
     */
    function _do_set_check(_index, row, active) {
        $.ajax({
            type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('stm/stock_adjust_record/do_checkin'); ?>',
            data: {id: row.stock_adjust_record_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }


    /**
     * 查看调整单详情
     * @param _index
     * @param row
     */
    function do_view(_index, row) {
        view(row.stock_adjust_record_id);
    }

    //数据行双击打开新页面显示详情
    function showDetail(_index, row) {
        view(row.stock_adjust_record_id);
    }
    function view(stock_adjust_record_id) {
        var url = '?app_act=stm/stock_adjust_record/view&stock_adjust_record_id=' + stock_adjust_record_id
        openPage(window.btoa(url), url, '调整单详情');
    }
</script>