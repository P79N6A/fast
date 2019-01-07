<style type="text/css">
    /*---选择框-begin--*/
    .check_custom{visibility: hidden;}
    .check_custom + label{
        cursor: pointer;
        margin: 3px 8px 4px -12px;
        background-color: white;
        border-radius: 5px;
        border:1px solid #d3d3d3;
        width:20px;
        height:20px;
        display: inline-block;
        text-align: center;
        vertical-align: middle;
        line-height: 20px;
    }
    .check_custom:checked + label{
        background-color: #eee;
    }
    .check_custom:checked + label:after{
        content:"\2714";
    }

    [type="radio"] + label{
        border-radius: 10px;
    }
    /*---选择框-end--*/
    .custom-dialog .bui-stdmod-header,.custom-dialog .bui-stdmod-footer{display: none;}
    #exprot_list {width:120px;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '库存锁定单列表',
    'links' => array(
        array('url' => 'stm/stock_lock_record/detail&app_scene=add', 'title' => '添加锁定单', 'is_pop' => true, 'pop_size' => '500,550'),
    ),
    'ref_table' => 'table'
));
?>
<?php
$keyword_type = array();
$keyword_type['record_code'] = '单据编号';
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
            'id' => 'exprot_list',
        ),
//        array(
//            'label' => '导出明细',
//            'id' => 'exprot_detail',
//        ),
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
            'label' => '下单日期',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'is_add_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'is_add_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
        array(
            'label' => '状态',
            'type' => 'select',
            'id' => 'status',
            'data' => ds_get_select_by_field('order_status'),
        ),
        array(
            'label' => '锁定对象',
            'type' => 'select',
            'id' => 'lock_obj',
            'data' => ds_get_select_by_field('lock_obj'),
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
                        'title' => '锁定',
                        'callback' => 'record_lock',
                        'show_cond' => 'obj.order_status==0',
                    ),
                    array(
                        'id' => 'delete',
                        'title' => '释放',
                        'callback' => 'record_unlock',
                        'show_cond' => 'obj.order_status==1',
                    ),
                    array(
                        'id' => 'do_delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
                        'show_cond' => 'obj.order_status==0',
                        'confirm' => '确认要删除此订单吗？'
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'status',
                'width' => '80',
                'align' => ''
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
                    'value' => '<a href="javascript:view({stock_lock_record_id})">{record_code}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '下单时间',
                'field' => 'is_add_time',
                'width' => '160',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '锁定对象',
                'field' => 'lock_obj_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'store_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '锁定数量',
                'field' => 'lock_num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '释放数量',
                'field' => 'release_num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '可用锁定数量',
                'field' => 'available_num',
                'width' => '100',
                'align' => '',
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
    'dataset' => 'stm/StockLockRecordModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'stock_lock_record_id',
    'export' => array('id' => 'exprot_list', 'conf' => 'stock_lock_record_list', 'name' => '库存锁定单', 'export_type' => 'file'), //,'export_type' => 'file'
    //'CheckSelection'=>true,
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>
<div id="panel_lock_sync" style="visibility: hidden">
    <div class="row">
        <div class="control-group span10" style="margin-top: 20px;margin-left: 20px;">
            <label style="font-size:1.2em">系统支持2种模式来同步库存，请选择一种：</label><br>
            <input type="radio" name="lock_sync_mode" id="lock_sync_mode1" class="check_custom" value="1" checked="checked" style="margin-top: 10px;"><label class="radio" for="lock_sync_mode1"></label><label for="lock_sync_mode1" style="cursor: pointer;">以锁定库存同步</label><br>
            <input type="radio" name="lock_sync_mode" id="lock_sync_mode2" class="check_custom" value="2"><label class="radio" for="lock_sync_mode2"></label><label for="lock_sync_mode2"  style="cursor: pointer;">以锁定库存同步 + 剩余可用库存同步</label>
            <a href="javascript:lock_sync_mode_help()" style="margin-left: 10px;color: red">模式详解（必看）</a>
        </div>
    </div>
    <div class="clearfix" style="text-align: center;margin-top: 5px;">
        <button class="button button-primary" id="btn_lock_sync">确定</button>
    </div>
</div>
<div id="panel_unlock_sync" style="visibility: hidden">
    <div class="row">
        <div class="control-group span9" style="margin-top: 20px;margin-left: 20px;">
            <span>请必须保证锁定商品同步比例大于0，否则会有下架的风险</span><br><br>
            <span>一键更新锁定商品同步比例为</span>
            <input type="text" id="sync_ratio" style="width:50px;"> %<span style="color:red;">（必须为正整数）</span>
        </div>
    </div>
    <div class="clearfix" style="text-align: center;margin-top: 10px;">
        <button class="button button-primary" id="btn_unlock_sync">确定</button>
    </div>
</div>
<script type="text/javascript">
    /**
     * 查看锁定单详情
     * @param _index
     * @param row
     */
    function do_view(_index, row) {
        view(row.stock_lock_record_id);
    }

    //数据行双击打开新页面显示详情
    function showDetail(_index, row) {
        view(row.stock_lock_record_id);
    }
    function view(stock_adjust_record_id) {
        var url = '?app_act=stm/stock_lock_record/view&stock_lock_record_id=' + stock_adjust_record_id
        openPage(window.btoa(url), url, '锁定单详情');
    }

    /*----------锁定功能------START-----*/
    var dialog1, dialog2, record_id, sync_code;
    BUI.use(['bui/overlay', 'bui/form'], function (Overlay, Form) {
        dialog1 = new Overlay.Dialog({
            title: '',
            width: 430,
            height: 250,
            elCls: 'custom-dialog',
            contentId: 'panel_lock_sync'
        });

        $('#btn_lock_sync').on('click', function () {
            var lock_sync_mode = $("input[name='lock_sync_mode']:checked").val();
            opt_record_lock(record_id, lock_sync_mode, sync_code);
            dialog1.close();
        });
    });

    //锁定
    function record_lock(_index, row) {
        record_id = row.stock_lock_record_id;
        //锁定对象不是网络店铺直接做锁定操作
        if (row.lock_obj != 1 || row.shop_code == '') {
            BUI.Message.Confirm('确定锁定库存吗？', function () {
                opt_record_lock(record_id, 2, '');
            });
            return;
        }
        //锁定对象是网络店铺需要进行库存同步策略判断和设置
        var params = {shop_code: row.shop_code, store_code: row.store_code};
        $.post('?app_act=stm/stock_lock_record/check_inv_sync', {params: params}, function (ret) {
            if (ret.status == 1) {
                sync_code = ret.data;
                dialog1.show();
                return;
            }
            var msg, btn_ok, set_type;
            if (ret.status == -3) {
                msg = '为保证店铺以库存锁定单数量来同步，请为店铺设置并启用库存同步策略';
                btn_ok = '前往设置';
                set_type = 1;
            } else if (ret.status == -2) {
                msg = '为保证店铺以库存锁定单数量来同步，请为对应店铺仓库开启并设置库存同步策略';
                btn_ok = '前往开启';
                set_type = 2;
            }
            BUI.Message.Show({title: '友情提示', msg: msg, icon: 'warning', buttons: [
                    {text: btn_ok, elCls: 'button button-primary', handler: function () {
                            open_inv_sync_page(set_type);
                            this.close();
                        }
                    },
                    {text: '考虑一下', elCls: 'button', handler: function () {
                            this.close();
                        }
                    }
                ]
            });
        }, 'json');
    }

    //打开库存同步策略
    function open_inv_sync_page(_type) {
        if (_type == 1) {
            openPage('<?php echo base64_encode('?app_act=op/inv_sync/do_list') ?>', '?app_act=op/inv_sync/do_list', '库存同步策略');
        } else {
            openPage('<?php echo base64_encode('?app_act=sys/params/do_list&page_no=op') ?>', '?app_act=sys/params/do_list&page_no=op', '系统参数设置');
        }
    }

    //锁定同步两种模式区别提示
    function lock_sync_mode_help() {
        openPage('<?php echo base64_encode('?app_act=stm/stock_lock_record/lock_mode_help') ?>', '?app_act=stm/stock_lock_record/lock_mode_help', '锁定模式详解');
    }

    function opt_record_lock(record_id, lock_sync_mode, sync_code) {
        var params = {id: record_id, lock_sync_mode: lock_sync_mode, sync_code: sync_code};
        $.post('?app_act=stm/stock_lock_record/record_lock_action&app_fmt=json', {params: params}, function (ret) {
            if (ret.status == 1) {
                BUI.Message.Tip(ret.message, 'success');
                tableStore.load();
            } else {
                BUI.Message.Alert(ret.message, 'error');
            }
        }, 'json');
    }
    /*----------锁定功能------END----*/

    /*----------释放功能------START----*/
    BUI.use(['bui/overlay', 'bui/form'], function (Overlay, Form) {
        dialog2 = new Overlay.Dialog({
            title: '',
            width: 430,
            height: 230,
            elCls: 'custom-dialog',
            contentId: 'panel_unlock_sync'
        });

        $('#btn_unlock_sync').on('click', function () {
            var sync_ratio = $("#sync_ratio").val();
            var re = /^[0-9]*[1-9][0-9]*$/;
            if (!re.test(sync_ratio)) {
                BUI.Message.Tip('同步比例必须为大于0的正整数', 'warning');
                return false;
            }
            opt_record_unlock(record_id, sync_ratio);
            dialog2.close();
        });
    });
    function record_unlock(_index, row) {
        record_id = row.stock_lock_record_id;
        //锁定对象不是网络店铺直接做锁定操作
        if (row.lock_obj != 1 || row.sync_code == '') {
            BUI.Message.Confirm('确定释放库存吗？', function () {
                opt_record_unlock(record_id, '');
            });
            return;
        }
        var params = {sync_code: row.sync_code, shop_code: row.shop_code, store_code: row.store_code};
        $.post('?app_act=stm/stock_lock_record/get_sync_ratio', {params: params}, function (ret) {
            $("#sync_ratio").val(ret.data);
        }, 'json');
        dialog2.show();
    }

    function opt_record_unlock(record_id, sync_ratio) {
        var params = {id: record_id, sync_ratio: sync_ratio};
        $.post('?app_act=stm/stock_lock_record/record_unlock_action&app_fmt=json', {params: params}, function (ret) {
            if (ret.status == 1) {
                BUI.Message.Tip(ret.message, 'success');
                tableStore.load();
            } else {
                BUI.Message.Alert(ret.message, 'error');
            }
        }, 'json');
    }
    /*----------释放功能------END----*/

    //删除
    function do_delete(_index, row) {
        $.post('?app_act=stm/stock_lock_record/do_delete_action' + '&app_fmt=json', {id: row.stock_lock_record_id}, function (result) {
            if (result.status == 1) {
                BUI.Message.Alert(result.message, function () {
                    tableStore.load();
                }, 'success');
            } else {
                BUI.Message.Alert(result.message, function () {
                }, 'error');
            }
        }, 'json');
    }

</script>