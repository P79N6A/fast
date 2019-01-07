<?php echo load_js('comm_util.js') ?>
<style>
#process_batch_task_tips div{height:300px;overflow-y:scroll;}
</style>
<div class="page-header1" style="width: 98%; display: block; clear: both; position: fixed; top:0px; left:0px; background-color: #FFF; padding: 4px 1%; z-index: 9999; box-shadow:0px 0px 5px #ccc;">
    <span class="page-title"><h2>外包仓库存</h2></span>
    <span class="page-link">
    <span class="action-link">
  <!--   <?php #if (load_model('sys/PrivilegeModel')->check_priv('oms/api_order/down')) { ?>
        <a href="javascript:PageHead_show_dialog('?app_act=wms/wms_trade/update_sku&app_show_mode=pop', '获取库存变更的SKU', {w:700,h:450})" class="button button-primary">
            获取库存变更的SKU
        </a>
       <?php #} ?> -->
        </span>
        <button class="button button-primary" onclick="javascript:location.reload();"><i class="icon-refresh icon-white"></i> 刷新</button>
    </span>
</div>
<div class="clear" style="margin-top: 40px; "></div>  
<?php


render_control('SearchForm', 'searchForm', array(
    'buttons' =>array(
       array(
            'label' => '查询',
            'id' => 'btn-search',
               'type'=>'submit'
        ),
        array(
            'label' => '导出',
            'id' => 'exprot_list',
        )
    ) ,

    'show_row'=>3,
    'fields' => array(
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'efast_store_code',
            'data' => load_model('wms/WmsTradeModel')->get_wms_store(),
        ),
        array (
          'label' => '商品编码',
          'type' => 'input',
          'id' => 'goods_code'
        ),
        array(
          'label' => '商品条形码',
          'type' => 'input',
          'id' => 'barcode',
          'width' => '350',
        ),
        array (
          'label' => '库存状态',
          'type' => 'select',
          'id' => 'is_sync',
          'data'=>load_model('wms/WmsTradeModel')->get_wms_sync(),
        ),
        array(
          'label' => '库存获取时间',
          'type' => 'group',
          'field' => 'daterange1',
          'child' => array(
              array('title' => 'start', 'type' => 'date', 'field' => 'down_time_start',),
              array('pre_title' => '~', 'type' => 'date', 'field' => 'down_time_end', 'remark' => ''),
          )
        ),
    )
));
?>

<?php

render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '120',
                'align' => '',
                'buttons' => array (
                   array('id'=>'down_wms_stock', 'title' => '获取WMS库存', 'callback'=>'down_wms_stock','confirm'=>'确认要更新本地库存吗？',
                        'priv'=>'wms/wms_mgr/down_wms_stock_by_barcode',
                        'show_cond' => ''),
                   array('id'=>'update_efast_stock', 'title' => '更新本地库存', 'callback'=>'update_efast_stock','confirm'=>'确认要更新本地库存吗？',
                        'priv'=>'wms/wms_mgr/update_efast_stock_by_barcode',
                        'show_cond' => ''),
                ),
            ),

            array(
                'type' => 'text',
                'show' => 1,
                'title' => '本地库存状态',
                'field' => 'sync_status',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => 'eFAST仓库',
                'field' => 'efast_store_code_name',
                'width' => '120',
                'align' => '',
                
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '对接仓储系统',
                'field' => 'wms_store_out_name',
                'width' => '120',
                'align' => '',
                
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => 'WMS仓库',
                'field' => 'wms_store_out_code',
                'width' => '120',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '商品条形码',
                'field' => 'barcode',
                'width' => '140',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => 'WMS库存',
                'field' => 'num',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => 'WMS库存最后获取时间',
                'field' => 'down_time',
                'width' => '150',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '本地库存最后更新时间',
                'field' => 'sync_time',
                'width' => '150',
                'align' => ''
            ),
        ),
    ),
    'dataset' => 'wms/WmsTradeModel::inv_list_by_page',
    'export' => array('id' => 'exprot_list', 'conf' => 'wms_trade_inv', 'name' => '外包仓库存', 'export_type' => 'file'),
    'queryBy' => 'searchForm',
    'init' => 'nodata',
    'idField' => 'id',
    
));
?>
<div id="msg_id"></div>
<script type="text/javascript">

    function PageHead_show_dialog(_url, _title, _opts) {
        new ESUI.PopWindow(_url, {
            title: _title,
            width:_opts.w,
            height:_opts.h,
            onBeforeClosed: function() {
                if (typeof _opts.callback == 'function') _opts.callback();
            }
        }).show();
    }

    function get_checked(obj,act) {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择订单", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.sell_record_code);
        }
        $("#tbl_muil_check_ids").val(ids.join(','));

        BUI.Message.Show({
            title: '自定义提示框',
            msg: '是否执行订单' + obj.text() + '?',
            icon: 'question',
            buttons: [
                {
                    text: '是',
                    elCls: 'button button-primary',
                    handler: function() {
                        show_process_batch_task_plan(obj.text(),'<div id="process_batch_task_tips"><div>处理中，请稍等......');
                        process_batch_task(act);
                        this.close();
                    }
                },
                {
                    text: '否',
                    elCls: 'button',
                    handler: function() {
                        this.close();
                    }
                }
            ]
        });
    }

    function process_batch_task(act){
        var ids = $("#tbl_muil_check_ids").val();

        if (ids == ''){
            $("#process_batch_task_tips div").append("<br/><span style='color:red'>批量任务执行完成。</span>");
            return;
        }
        var ids_arr = ids.split(',');
        var cur_id = ids_arr.pop();
        console.log(cur_id);
        $("#tbl_muil_check_ids").val();
        $("#tbl_muil_check_ids").val(ids_arr.join(','));
        if (act == 'cancel_upload'){
             var ajax_url = "?app_fmt=json&app_act=wms/wms_trade/"+act+"&id="+cur_id;
        }
        if (act == 'short_split'){
            var ajax_url = "?app_fmt=json&app_act=oms/sell_record/split&sell_record_code="+cur_id;
        }
        $.get(ajax_url,function(result){
            var result_obj = eval('('+result+')');
            $("#process_batch_task_tips div").append("<br/>"+cur_id+' '+result_obj.message);
            process_batch_task(act);                
        });
    }

    function show_process_batch_task_plan(title,content){
        BUI.use('bui/overlay',function(Overlay){
            var dialog = new Overlay.Dialog({
                title:title,
                width:500,
                height:400,
                mask:true,
                buttons:[],
                bodyContent:content
            });
            dialog.show();
        });
    }


    function update_efast_stock(index, row){
        var d = {"efast_store_code": row.efast_store_code, "barcode":row.barcode, "app_fmt": 'json'};
        $.post('<?php echo get_app_url('wms/wms_mgr/update_efast_stock');?>', d, function(data){
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
             tableStore.load();
        }, "json");
    }

    function down_wms_stock(index, row){
        var d = {"efast_store_code": row.efast_store_code, "barcode":row.barcode, "app_fmt": 'json'};
        $.post('<?php echo get_app_url('wms/wms_mgr/down_wms_stock_by_barcode');?>', d, function(data){
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
             tableStore.load();
        }, "json");
    }

</script>
