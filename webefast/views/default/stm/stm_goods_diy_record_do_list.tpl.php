<style type="text/css">
    .well {
        min-height: 100px;
    }
</style>
<div class="page-header1" style="width:98%; display: block; clear: both; position: fixed; top:0px; left:0px; background-color: #FFF; padding: 4px 1%; z-index: 9999; box-shadow:0px 0px 5px #ccc">
    <span class="page-title">
        <h2>商品组装单</h2>
    </span>
    <span class="page-link">
        <span class="action-link">
            <?php if (load_model('sys/PrivilegeModel')->check_priv('stm/stm_goods_diy_record/detail')) { ?>
                <a class="button button-primary" href="javascript:PageHead_show_dialog('?app_act=stm/stm_goods_diy_record/detail&app_scene=add&app_show_mode=pop', '添加商品组装单', {w:500,h:550})"> 添加商品组装单</a>
            <?php } ?>
        </span>
        <button class="button button-primary" onclick="javascript:location.reload();">
            <i class="icon-refresh icon-white"></i>
            刷新
        </button>
    </span>
</div>
<div class="clear" style="margin-top: 40px; "></div>
<script type="text/javascript">

    var ES_PAGE_ID = 'stm/stm_goods_diy_record/do_list';

    function PageHead_show_dialog(_url, _title, _opts) {

        new ESUI.PopWindow(_url, {
            title: _title,
            width: _opts.w,
            height: _opts.h,
            onBeforeClosed: function () {
                tableStore.load();
                if (typeof _opts.callback == 'function')
                    _opts.callback();
            }
        }).show();
    }
</script>
<?php
/*
  render_control('PageHead', 'head1', array('title' => '商品组装单',
  'links' => array(
  array('url' => 'stm/stm_goods_diy_record/detail&app_scene=add', 'title' => '添加商品组装单', 'is_pop' => true, 'pop_size' => '500,550'),
  ),
  'ref_table' => 'table'
  ));
 */
?>
<?php
$fenxiao = ds_get_select('supplier', 2);
unset($fenxiao[0]);
$keyword_type = array();
$keyword_type['record_code'] = '单据编号';
$keyword_type['relation_code'] = '关联调整单';
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
            'id' => 'is_sure',
            'data' => array(
                array('0', '未确认'), array('1', '已确认')
            )),
        array(
            'label' => '是否审核',
            'title' => '',
            'type' => 'select',
            'id' => 'is_check',
            'data' => ds_get_select_by_field('cost_month_check', 1)
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
            'label' => '单据类型',
            'title' => '',
            'type' => 'select',
            'id' => 'record_type',
            'data' => array(
                array('','请选择'),array('0','组装'), array('1','拆分')
            )),
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
                        'priv' => 'stm/stm_goods_diy_record/view&id={goods_diy_record_id}',
                        'callback' => 'do_view'
                    ),
                    array(
                        'id' => 'check2',
                        'title' => '审核',
                        'callback' => 'do_check',
                        'priv' => 'stm/stm_goods_diy_record/do_check',
                        'show_cond' => 'obj.is_sure == 0 && obj.is_check == 0'
                    ),
                    array(
                        'id' => 'check1',
                        'title' => '确认',
                        'callback' => 'do_sure',
                        'priv' => 'stm/stm_goods_diy_record/do_sure',
                        'show_cond' => 'obj.is_sure == 0 && obj.is_check == 1'
                    ),
                    /*
                      array(
                      'id' => 'check2',
                      'title' => '取消确认',
                      'callback' => 'do_re_sure',
                      'priv'=>'stm/stm_goods_diy_record/do_sure',
                      'show_cond' => 'obj.is_stop == 0 && obj.is_sure == 1 && obj.is_finish == 0 && obj.is_execute == 0 '
                      ),
                     */
                    array(
                        'id' => 'delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
                        'priv' => 'stm/stm_goods_diy_record/do_delete',
                        'show_cond' => 'obj.is_sure == 0 && obj.is_check == 0',
                        'confirm' => '确认要删除此信息吗？'
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '审核',
                'field' => 'is_check',
                'width' => '50',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
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
                'title' => '单据编号',
                'field' => 'record_code',
                'width' => '160',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href=javascript:view({goods_diy_record_id})>{record_code}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '关联调整单',
                'field' => 'relation_code',
                'width' => '160',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view_stock({stock_adjust_record_id})">{relation_code}</a>',
                ),
            /*
              'format_js' => array(
              'type' => 'html',
              'value' => '<a href="' . get_app_url('stm/stock_adjust_record/view') . '&stock_adjust_record_id={stock_adjust_record_id}">{relation_code}</a>',
              ),
             */
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据类型',
                'field' => 'record_type_name',
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
                'title' => '仓库',
                'field' => 'store_code_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '总数量',
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
        )
    ),
    'dataset' => 'stm/StmGoodsDiyRecordModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'goods_diy_record_id',
    // 'export'=> array('id'=>'exprot_list','conf'=>'return_record_list','name'=>'批发销货'),
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">

    function do_delete(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('stm/stm_goods_diy_record/do_delete'); ?>',
            data: {goods_diy_record_id: row.goods_diy_record_id},
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
        url = '?app_act=pur/return_notice_record/do_sure';
        data = {id: row.return_notice_record_id, type: 'disable'};
        _do_operate(url, data, 'table');
    }
    
    function do_check(_index, row){
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('stm/stm_goods_diy_record/do_check'); ?>',
            data: {id: row.goods_diy_record_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('审核成功！', type);
                   tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    
    function  do_sure(_index, row) {

        params = {id: row.goods_diy_record_id, type: 'enable'};
        $.post("?app_act=stm/stm_goods_diy_record/do_sure", params, function (data) {
            var msg = data.msg;
            var stock_adjust_record_id = data.stock_adjust_record_id;
            BUI.Message.Show({
                title: '自定义提示框',
                msg: msg,
                icon: 'question',
                buttons: [
                    {
                        text: '是',
                        elCls: 'button button-primary',
                        handler: function () {
                            var url = '?app_act=stm/stock_adjust_record/view&stock_adjust_record_id=' + stock_adjust_record_id;
                            openPage(window.btoa(url), url, '调整单');
                            this.close();
                            ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
                        }
                    },
                    {
                        text: '否',
                        elCls: 'button',
                        handler: function () {
                            _do_operate(url, data, 'table');
                            // this.close();
                            //ui_closePopWindow("<?php //echo $request['ES_frmId']     ?>");  
                        }
                    }
                ]
            });

        }, "json");

        //url = '?app_act=pur/return_notice_record/do_sure';
        //data = {id: row.return_notice_record_id,type:'enable'};
        //_do_operate(url,data,'table');
    }


    /**
     * 查看组装单详情
     * @param _index
     * @param row
     */
    function do_view(_index, row) {
        view(row.goods_diy_record_id);
    }

    //数据行双击打开新页面显示详情
    function showDetail(_index, row) {
        view(row.goods_diy_record_id);
    }
    function view(goods_diy_record_id) {
        var url = '?app_act=stm/stm_goods_diy_record/view&goods_diy_record_id=' + goods_diy_record_id
        openPage(window.btoa(url), url, '组装单详情');
    }
    function view_stock(stock_adjust_record_id) {
        var url = '?app_act=stm/stock_adjust_record/view&stock_adjust_record_id=' + stock_adjust_record_id
        openPage(window.btoa(url), url, '关联调整单详情');
    }
</script>



