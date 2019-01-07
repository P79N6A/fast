<style type="text/css">
    .well {
        min-height: 70px;
    }
    #order_time_start{
        width:100px;
    }
    #order_time_end{
        width:100px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '批发退货通知单列表',
    'links' => array(
        array('url' => 'wbm/return_notice_record/detail&app_scene=add', 'title' => '添加批发退货通知单', 'is_pop' => true, 'pop_size' => '500,550'),
    ),
    'ref_table' => 'table'
));
?>
<?php
$keyword_type = array();
$keyword_type['return_notice_code'] = '单据编号';
$keyword_type['jx_return_code'] = '经销退货单编号';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['remark'] = '备注';
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
            'title' => '',
            'type' => 'select_multi',
            'id' => 'custom_code',
            'data' => $response['fenxiao']
        ),
        array(
        	'label' => '仓库',
        	'type' => 'select_multi',
        	'id' => 'store_code',
        	'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
        array(
            'label' => '确认状态',
            'type' => 'select',
            'id' => 'check_status',
            'data' =>  ds_get_select_by_field('is_sure',1)
        ),
        array(
            'label' => '生成退货单',
            'type' => 'select',
            'id' => 'return_status',
            'data' =>  ds_get_select_by_field('is_build',1)
        ),
        array(
            'label' => '完成状态',
            'type' => 'select',
            'id' => 'finish_status',
            'data' =>  ds_get_select_by_field('finish_status',1)
        ),
        array(
            'label' => '下单时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'order_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'order_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '退货类型',
            'type' => 'select_multi',
            'id' => 'return_type_code',
            'data' => ds_get_select('record_type', 0, array('record_type_property' => 3))
        ),
    )
));
?>

<?php
$but[] = array(
    'id' => 'view',
    'title' => '查看',
    'callback' => 'do_view'
);
if (load_model('sys/PrivilegeModel')->check_priv('wbm/return_notice_record/do_delete')) {
    $but[] = array(
        'id' => 'delete',
        'title' => '删除',
        'callback' => 'do_delete',
        'show_cond' => 'obj.is_check == 0 && obj.is_return == 0 && obj.is_finish == 0 ',
        'confirm' => '确认要删除此信息吗？'
    );
}
if (load_model('sys/PrivilegeModel')->check_priv('wbm/return_notice_record/do_return')) {
    $but[] = array(
        'id' => 'execute',
        'title' => '生成退单',
        'callback' => 'do_return',
        'show_cond' => 'obj.is_finish == 0  && obj.is_check == 1 && obj.is_wms==0'
    );
}
if (load_model('sys/PrivilegeModel')->check_priv('wbm/return_notice_record/do_sure')) {
    $but[] = array(
        'id' => 'check1',
        'title' => '确认',
        'callback' => 'do_check',
        'show_cond' => 'obj.is_check == 0 && obj.is_return==0 && obj.is_finish==0'
    );
    $but[] = array(
        'id' => 'check2',
        'title' => '取消确认',
        'callback' => 'do_re_check',
        'show_cond' => 'obj.is_finish == 0 && obj.is_check == 1 && obj.is_return == 0 '
    );
}
if (load_model('sys/PrivilegeModel')->check_priv('wbm/return_notice_record/do_finish')) {
    $but[] = array(
        'id' => 'finish',
        'title' => '完成',
        'callback' => 'do_finish',
        'show_cond' => 'obj.is_check == 1 && obj.is_finish == 0 && obj.is_return == 1 && obj.is_wms==0'
    );
}

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
            'buttons' => $but
        ),
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '确认',
            'field' => 'is_check',
            'width' => '50',
            'align' => 'center',
            'format_js' => array('type' => 'map_checked')
        ),
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '生成退货单',
            'field' => 'is_return',
            'width' => '80',
            'align' => 'center',
            'format_js' => array('type' => 'map_checked')
        ),
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '完成',
            'field' => 'is_finish',
            'width' => '50',
            'align' => 'center',
            'format_js' => array('type' => 'map_checked')
        ),
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '单据编号',
            'field' => 'return_notice_code',
            'width' => '150',
            'align' => '',
            'format_js' => array(
                'type' => 'html',
//                    'value' => '<a href="' . get_app_url('pur/order_record/view') . '&order_record_id={order_record_id}">{record_code}</a>',
                    'value' => '<a href="javascript:view({return_notice_record_id})">{return_notice_code}</a>',
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
                'title' => '分销商',
                'field' => 'custom_code_name',
                'width' => '150',
                'align' => '',
                //'format' => array('type' => 'date')
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
                'width' => '60',
                'align' => ''
            ),
            array(
            	'type' => 'text',
            	'show' => 1,
            	'title' => '完成数',
            	'field' => 'finish_num',
            	'width' => '60',
            	'align' => ''
            ),
            array(
            	'type' => 'text',
            	'show' => 1,
            	'title' => '差异数',
            	'field' => 'different_num',
            	'width' => '60',
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
    'dataset' => 'wbm/ReturnNoticeRecordModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'return_notice_record_id',
    'params' => array('filter' => array('return_notice_code' => isset($response['data']['return_notice_code'])?$response['data']['return_notice_code']:'')),
    'export'=> array('id'=>'exprot_detail','conf'=>'wbm_return_notice_record','name'=>'批发退货通知单','export_type'=>'file'),
    //'RowNumber'=>true,
    'CheckSelection'=>true,
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>
<?php echo load_js("pur.js",true);?>
<script type="text/javascript">
    
    function do_delete(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('wbm/return_notice_record/do_delete'); ?>',
            data: {return_notice_code: row.return_notice_code},
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

    function  do_re_check(_index, row){
    	url = '?app_act=wbm/return_notice_record/do_check';
		data = {return_notice_code: row.return_notice_code,id:row.return_notice_record_id,type:'disable'};
        _do_operate(url,data,'table');
     }
    function  do_check(_index, row){
    	url = '?app_act=wbm/return_notice_record/do_check';
		data = {return_notice_code: row.return_notice_code,id:row.return_notice_record_id,type:'enable'};
        _do_operate(url,data,'table');
    }

    function  do_return(_index, row){
    	/*url = '?app_act=wbm/return_notice_record/create_return_record';
		data = {return_notice_record_id: row.return_notice_record_id,create_type:'create_return_unfinish'};
        _do_operate(url,data,'table');*/
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('wbm/return_notice_record/out_relation'); ?>',
            data: {id: row.return_notice_record_id},
            success: function (ret) {
                // alert(ret);
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    url = "?app_act=wbm/return_notice_record/do_return&return_notice_record_id="+row.return_notice_record_id.toString();
                    _do_execute(url, 'table');
                } else {
                    if (ret.status == '-1') {
                        BUI.Message.Confirm('存在未验收的批发退货单，是否继续？', function () {
                            url = "?app_act=wbm/return_notice_record/do_return&return_notice_record_id="+row.return_notice_record_id.toString();
                            _do_execute(url, 'table');
                        });
                    }
                }
            }
        });
    }
    
    function do_finish(_index, row){
    	url = '?app_act=wbm/return_notice_record/do_finish';
		data = {return_notice_code: row.return_notice_code,id:row.return_notice_record_id};
		 _do_operate(url,data,'table');
    }

    /**
     * 查看详情
     * @param _index
     * @param row
     */
    function do_view(_index, row) {
        location.href = "?app_act=wbm/return_notice_record/view&return_notice_record_id=" + row.return_notice_record_id;
    }
    
    //数据行双击打开新页面显示详情
    function showDetail(index, row) {
        openPage('<?php echo base64_encode('?app_act=wbm/return_notice_record/view&return_notice_record_id') ?>'+row.return_notice_record_id,'?app_act=wbm/return_notice_record/view&return_notice_record_id='+row.return_notice_record_id,'批发退货通知单详情');
    }

    function view(order_record_id) {
	    var url = '?app_act=wbm/return_notice_record/view&return_notice_record_id=' +order_record_id
	    openPage(window.btoa(url),url,'批发退货通知单详情');
    }

//     $('#exprot_list').click(function(){
//         var url="?app_act=wbm/order_record/export_main_list";

//         var obj = searchFormForm.serializeToObject();

//         for(var key in obj){
//             url +="&"+key+"="+obj[key];
//         }
//         window.location.href = url;
//     });

</script>

