<style>
    #start_time, #end_time{width: 100px;}
</style>

<?php echo load_js('comm_util.js') ?>
<?php
render_control('PageHead', 'head1', array('title' => '商品调价单',
    'links' => array(
        array('url' => 'fx/goods_adjust_price/detail&app_scene=add', 'title' => '添加调价单', 'is_pop' => true , 'pop_size' => '450,550'),
    ),
    'ref_table' => 'table'
));
?>

<?php
$keyword_type = array();
$keyword_type['record_code'] = '单据编号';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type = array_from_dict($keyword_type);
$time_type = array();
$time_type['add_time'] = '创建时间';
$time_type['start_time'] = '开始时间';
$time_type['end_time'] = '结束时间';
$time_type = array_from_dict($time_type);
$buttons = array(
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
    )
);

render_control('SearchForm', 'searchForm', array(
    'buttons' =>$buttons,
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => array('id' => 'time_type', 'type' => 'select', 'data' => $time_type),
            'type' => 'group',
            'field' => 'time_type',
            'data' => $time_type,
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'start_time', 'class' => 'input-small'),
                array('pre_title' => '', 'type' => 'time', 'field' => 'end_time', 'class' => 'input-small'),
            )
        ),
        array('label'=>'分销商', 'type'=>'select_pop', 'id'=>'p_code', 'select'=>'base/custom' ),
        array(
            'label' => '分销商分类',
            'type' => 'select_multi',
            'id' => 'grade_code',
            'data' => load_model('base/CustomGradesModel')->get_all_grades(2),
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
                        'id' => 'do_view',
                        'title' => '查看',
                        'callback' => 'do_view',
                        'show_cond' => 'obj.record_status == 1'
                    ),
                    array(
                        'id' => 'editor',
                        'title' => '编辑',
                        'callback' => 'do_editor',
                        'show_cond' => 'obj.record_status == 0'
                    ),
                    array(
                        'id' => 'do_enable',
                        'title' => '启用',
                        'callback' => 'do_enable',
                        'priv' => 'fx/goods_adjust_price/do_enable',
                        'show_cond' => 'obj.record_status == 0'
                    ),
                    array(
                        'id' => 'do_disable',
                        'title' => '停用',
                        'callback' => 'do_disable',
                        'priv' => 'fx/goods_adjust_price/do_disable',
                        'show_cond' => 'obj.record_status == 1'
                    ),
                    array(
                        'id' => 'do_delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
                        'priv' => 'fx/goods_adjust_price/do_delete',
                        'show_cond' => 'obj.record_status == 0',
                        'confirm' => '确认要删除此信息吗？'
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'title' => '单据编号',
                'field' => 'record_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'title' => '调价对象',
                'field' => 'object_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'title' => '结算价格',
                'field' => 'settlement_price_type',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'title' => '调价开始时间',
                'field' => 'start_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'title' => '调价结束时间',
                'field' => 'end_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'title' => '创建时间',
                'field' => 'add_time',
                'width' => '150',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'fx/GoodsAdjustPriceModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'adjust_price_record_id',
    'export' => array('id' => 'exprot_list', 'conf' => 'goods_adjust_price_do_list', 'name' => '商品调价单','export_type' => 'file'),
));
?>
<div style=" margin-top: 10px;">
    <span style="color: red">温馨提示：若同一个商品存在于多个调价单中，优先取调价开始时间最近的金额参与计算。</span>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        var html = '';
        html += '<input type="hidden" name="custom_code" value="" id="custom_code">';
        $("#p_code_select_pop").parent().append(html);
//        if(login_type == 2){
//            $("#searchForm #p_code_select_pop").attr("disabled","true");
//            $("#searchForm #p_code_select_img").unbind();
//        }
    });
     var selectPopWindowp_code = {
        dialog: null,
        callback: function(value) {
            var custom_code = value[0]['custom_code'];
            var custom_name = value[0]['custom_name'];
            $('#p_code_select_pop').val(custom_name);
            $('#custom_code').val(custom_code);
            if (selectPopWindowp_code.dialog != null) {
                selectPopWindowp_code.dialog.close();
            }
        }
    };
    function do_editor(_index, row) {
        var url = '?app_act=fx/goods_adjust_price/view&id=' + row.adjust_price_record_id;
        openPage(window.btoa(url),url,'调价单详情');
    }
    
    function do_enable(_index, row) {
        update_active(row.adjust_price_record_id, 'enable');
    }
    
    function do_disable(_index, row) {
        update_active(row.adjust_price_record_id, 'disable');
    }
    
    function update_active(id, active) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('fx/goods_adjust_price/update_active'); ?>',
            data: {id: id, type: active},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Tip(ret.message);
                    tableStore.load();
                } else {
                    BUI.Message.Tip(ret.message);
                }
            }
        });
    }
    
    function do_delete(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('fx/goods_adjust_price/do_delete'); ?>',
            data: {id: row.adjust_price_record_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Tip(ret.message);
                    tableStore.load();
                } else {
                    BUI.Message.Tip(ret.message);
                }
            }
        });
    }
    parent.tableload = function(){
        tableStore.load();
    }
    
    function do_view(_index, row) {
        var url = '?app_act=fx/goods_adjust_price/view&id=' + row.adjust_price_record_id;
        openPage(window.btoa(url),url,'调价单详情');
    }
    $('#exprot_detail').click(function(){
        var params='';
        var url = '?app_act=sys/export_csv/export_show', //暂时不是框架级别
        //var url = '?app_act=ctl/index/do_index&app_ctl=DataTable/do_get_data';
        params = tableStore.get('params');
        params.ctl_type = 'export';
        params.ctl_export_conf = 'goods_adjust_price_do_list_detail';
        params.ctl_export_name =  '调价单明细';
        <?php echo   create_export_token_js('fx/GoodsAdjustPriceModel::get_by_page');?>
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
    });
</script>




