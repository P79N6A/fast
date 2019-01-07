<style type="text/css">
    .well {min-height: 100px;}
    #supplier_name{width:150px}
    #clear_supplier{position: absolute;right: 31px;top: 1px;border:none;border-left:1px solid rgba(128, 128, 128, 0.64);height: 24px;}
    .icon-remove{position: absolute;right: 4px;top: 4px;}
    /*#searchForm .control-label{width:6em;}*/
</style>
<div class="page-header1" style="width:98%; display: block; clear: both; position: fixed; top:0px; left:0px; background-color: #FFF; padding: 4px 1%; z-index: 9999; box-shadow:0px 0px 5px #ccc">
    <span class="page-title">
        <h2>商品列表</h2>
    </span>
    <span class="page-link">
        <?php if (load_model('sys/PrivilegeModel')->check_priv('prm/goods/detail&action=do_add')) { ?>
            <span class="action-link">
                <a class="button button-primary" href="javascript:openPage('P2FwcF9hY3Q9cHJtL2dvb2RzL2RldGFpbCZhY3Rpb249ZG9fYWRk','?app_act=prm/goods/detail&ES_frmId=prm/goods/detail&action=do_add','添加商品')" >添加商品</a>
            </span>
        <?php } ?>
        <?php if (load_model('sys/PrivilegeModel')->check_priv('prm/goods/increment')) { ?>
            <span class="action-link">
                <a class="button button-primary" href="<?php echo $response['goods_issue'] == true ? '?app_act=increment' : 'http://www.baotayun.com' ?>" target="_blank" rel="noopener noreferrer" >商品上新</a>
            </span>
        <?php } ?>
        <button  class="button button-primary" onclick="javascript:location.reload();"><i class="icon-refresh icon-white"></i> 刷新</button>
    </span>
</div>
<div class="clear" style="margin-top: 40px; "></div>
<div>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('prm/goods/opt_update_active')) { ?>
        <ul id="ToolBar1" class="toolbar frontool">
            <li class="li_btns"><button class="button button-primary btn_opt_pending" id="opt_enable" >批量启用</button></li>
            <li class="li_btns"><button class="button button-primary btn_opt_pending" id="opt_disable" >批量停用</button></li>
            <div class="front_close">&lt;</div>
        </ul>
    <?php } ?>
    <script>
        $(function () {
            function tools() {
                $(".frontool").css({left: '0px'});
                $(".front_close").click(function () {
                    if ($(this).html() == "&lt;") {
                        $(".frontool").animate({left: '-100%'}, 1000);
                        $(this).html(">");
                        $(this).addClass("close_02").animate({right: '-10px'}, 1000);
                    } else {
                        $(".frontool").animate({left: '0px'}, 1);
                        $(this).html("<");
                        $(this).removeClass("close_02").animate({right: '0'}, 1000);
                    }
                });
            }
            tools();
        });
    </script>
</div>
<script type="text/javascript">
    var ES_PAGE_ID = 'prm/goods/do_list';
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
$diy = array(
    '0' => '否',
    '1' => '是',
);
$keyword_type = array();
$keyword_type['goods_code'] = '商品编码';
$keyword_type['goods_name'] = '商品名称';
$keyword_type['goods_short_name'] = '商品简称';
$keyword_type['goods_produce_name'] = '出厂名称';
$keyword_type = array_from_dict($keyword_type);
$buttons = array(
    array(
        'label' => '查询',
        'id' => 'btn-search',
        'type' => 'submit'
    ),
);
if (load_model('sys/PrivilegeModel')->check_priv('prm/goods/export_list')) {
    $buttons[] = array(
        'label' => '导出',
        'id' => 'exprot_list',
    );
}
$diy = array_from_dict($diy);
render_control('SearchForm', 'searchForm', array(
    'buttons' => $buttons,
//    'show_row' => 4,
    'hidden_fields' => array(
        array(
            'type' => 'text',
            'field' => 'supplier_code',
        ),
    ),
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
            'help' => '商品编码支持多个查询，用逗号分隔'
        ),
        array(
            'label' => '分类',
            'type' => 'select_multi',
            'id' => 'category_code',
            'data' => $response['category'],
        ),
        array(
            'label' => '品牌',
            'type' => 'select_multi',
            'id' => 'brand_code',
            'data' => $response['brand'],
        ),
        array(
            'label' => '年份',
            'type' => 'select_multi',
            'id' => 'year_code',
            'data' => ds_get_select('year'),
        ),
        array(
            'label' => '季节',
            'type' => 'select_multi',
            'id' => 'season_code',
            'data' => ds_get_select('season_code'),
        ),
        array(
            'label' => '商品属性',
            'type' => 'select_multi',
            'id' => 'goods_prop',
            'data' => $response['prop'],
        ),
        array(
            'label' => '启用状态',
            'type' => 'select',
            'id' => 'status',
            'data' => $response['status']
        ),
        array(
            'label' => '商品状态',
            'type' => 'select_multi',
            'id' => 'state',
            'data' => $response['state'],
        ),
        /*array(
            'label' => '组装商品',
            'type' => 'select_multi',
            'id' => 'diy',
            'data' => $diy,
        ),*/
        array(
            'label' => '更新时间',
            'type' => 'group',
            'field' => 'daterange2',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'lastchanged_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'lastchanged_end',),
            )
        ),
        array(
            'label' => '供应商',
            'type' => 'group',
            'field' => 'supplier',
            'child' => array(
                array(
                    'type' => 'input',
                    'field' => 'supplier_name',
                    'readonly' => 1,
                    'remark' => "<span class='x-icon x-icon-normal' id = 'clear_supplier' title='清除选中供应商' ><i class='icon-remove'></i></span><a href='#' id = 'base_supplier'><img src='assets/img/search.png' >"
                ),
            ),
        ),
    )
));
?>

<?php
$list = array(
    array(
        'type' => 'button',
        'show' => 1,
        'title' => '操作',
        'field' => '_operate',
        'width' => '150',
        'align' => '',
        'buttons' => array(
            array('id' => 'edit', 'title' => '编辑', 'priv' => 'prm/goods/detail&action=do_edit', 'callback' => 'do_edit', 'show_cond' => 'obj.status != 1'),
            array('id' => 'delete', 'title' => '删除', 'priv' => 'prm/goods/delete_goods',
                'callback' => 'do_delete', 'show_cond' => 'obj.status == 1', 'confirm' => '确认要删除吗？'),
            array('id' => 'enable', 'title' => '停用', 'priv' => 'prm/goods/update_active',
                'callback' => 'do_enable', 'show_cond' => 'obj.status != 1', 'confirm' => '确认要停用吗？'),
            array('id' => 'disable', 'title' => '启用', 'priv' => 'prm/goods/update_active',
                'callback' => 'do_disable', 'show_cond' => 'obj.status == 1',
                'confirm' => '确认要启用吗？'),
        ),
    ),
    array(
        'title' => '商品图片',
        'show' => 1,
        'type' => 'text',
        'width' => '100',
        'field' => 'goods_thumb_img',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '商品编码',
        'field' => 'goods_code',
        'width' => '200',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '商品名称',
        'field' => 'goods_name',
        'width' => '200',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '商品简称',
        'field' => 'goods_short_name',
        'width' => '200',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '分类',
        'field' => 'category_name',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '品牌',
        'field' => 'brand_name',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '季节',
        'field' => 'season_name',
        'width' => '50',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '年份',
        'field' => 'year_name',
        'width' => '50',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '商品状态',
        'field' => 'state',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '商品属性',
        'field' => 'goods_prop',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '吊牌价格',
        'field' => 'sell_price',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '成本价',
        'field' => 'cost_price',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '批发价',
        'field' => 'trade_price',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '进货价',
        'field' => 'purchase_price',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '最低售价',
        'field' => 'min_price',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '生产周期',
        'field' => 'goods_days',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '商品描述',
        'field' => 'goods_desc',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '重量',
        'field' => 'weight',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '是否组装商品',
        'field' => 'diy',
        'width' => '100',
        'align' => '',
        'format_js' => array(
            'type' => 'map',
            'value' => array(
                '0' => '否',
                '1' => '是',
            ),
        ),
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '出厂名称',
        'field' => 'goods_produce_name',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '供应商',
        'field' => 'supplier_name',
        'width' => '100',
        'align' => ''
    ),
);
if (!empty($response['proprety'])) {
    foreach ($response['proprety'] as $val) {
        $list[] = array('title' => $val['property_val_title'],
            'show' => 1,
            'type' => 'text',
            'width' => '80',
            'field' => $val['property_val']);
    }
}
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => $list
    ),
    'dataset' => 'prm/GoodsModel::get_by_page',
    'params' => array('filter' => array('user_id' => $response['user_id'],'diy'=>'0')),
    'queryBy' => 'searchForm',
    'idField' => 'goods_id',
    'customFieldTable' => 'goods_do_list/table',
    'export' => array('id' => 'exprot_list', 'conf' => 'goods_record_list', 'name' => '商品列表', 'export_type' => 'file'),
    //'RowNumber'=>true,
    'CheckSelection' => true,
));
?>
<script type="text/javascript">
    //读取已选中项
    function get_checked(obj, func, type) {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请先选择数据！", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.goods_id);
        }
        ids.join(',');
        BUI.Message.Show({
            title: '批量操作',
            msg: '是否执行批量操作?',
            icon: 'question',
            buttons: [
                {
                    text: '是',
                    elCls: 'button button-primary',
                    handler: function () {
                        func.apply(null, [ids]);
                        this.close();
                    }
                },
                {
                    text: '否',
                    elCls: 'button',
                    handler: function () {
                        this.close();
                    }
                }
            ]
        });
    }

    $("#opt_enable").click(function () {
        opt_set_active('enable');
    });
    $("#opt_disable").click(function () {
        opt_set_active('disable');
    });
    function opt_set_active(active) {
        get_checked($(this), function (ids) {
            $.ajax({type: 'POST', dataType: 'json',
                url: '<?php echo get_app_url('prm/goods/opt_update_active'); ?>',
                data: {id: ids, type: active},
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
        })
    }

    function do_edit(_index, row) {
        openPage('<?php echo base64_encode('?app_act=prm/goods/detail&action=do_edit&goods_id=') ?>' + row.goods_id, '?app_act=prm/goods/detail&action=do_edit&goods_id=' + row.goods_id, '编辑');
        return;
    }
    function do_enable(_index, row) {
        _do_set_active(_index, row, 'enable');
    }
    function do_disable(_index, row) {
        _do_set_active(_index, row, 'disable');
    }
    function _do_set_active(_index, row, active) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('prm/goods/update_active'); ?>',
            data: {id: row.goods_id, type: active},
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

    /*
     * 商品删除
     * 仅针对未启用的且未产生销售记录和库存记录的商品进行删除操作
     */
    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('prm/goods/do_delete'); ?>',
            data: {goods_code: row.goods_code},
            success: function (ret) {
                if (ret.status == 1) {
                    BUI.Message.Alert(ret.message);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message);
                }
            }
        });
    }
    
    $(function () {
        $("body").on('mouseover', 'td>div>span>img', function (e) {
            var img_src = $(this).data('goods-img');
            var tooltip = "<div id='tooltipimg' style='position:fixed;top:25%;left:25%;'> <img  width='500px' height='auto' src='" + img_src + "' alt='原图'/> </div>";
            //创建 div 元素
            $('tbody').parent().parent().parent().parent().append(tooltip);
        }).mouseout(function () {
            $("#tooltipimg").remove(); //移除
        });
    });

    $("#base_supplier").click(function () {
        show_select('supplier');
    });

    $("#clear_supplier").click(function () {
        $("#supplier_code").attr("value", "");
        $("#supplier_name").attr("value", "");
    });

    function show_select(_type) {
        var param = {};
        var url = '?app_act=pur/planned_record/select_supplier';
        var title = '请选择供应商';

        if (typeof (top.dialog) !== 'undefined') {
            top.dialog.remove(true);
        }
        var buttons = [
            {
                text: '保存继续',
                elCls: 'button button-primary',
                handler: function () {
                    var data = top.tablesGrid.getSelection();
                    if (data.length > 0) {
                        deal_data_1(data, _type);
                    }
                    auto_enter('#supplier_code');
                    top.tablesStore.load();
                    var supplier_name = $("#supplier_name").val();
                    var supplier_code = $("#supplier_code").val();
                    if (supplier_name !== '') {
                        d_supplier_name(supplier_name, 'name');
                        d_supplier_name(supplier_code, 'code');
                    }
                }
            },
            {
                text: '保存退出',
                elCls: 'button button-primary',
                handler: function () {
                    var data = top.tablesGrid.getSelection();
                    if (data.length > 0) {
                        deal_data_1(data, _type);
                    }
                    auto_enter('#supplier_code');
                    var supplier_name = $("#supplier_name").val();
                    var supplier_code = $("#supplier_code").val();
                    if (supplier_name !== '') {
                        d_supplier_name(supplier_name, 'name');
                        d_supplier_name(supplier_code, 'code');
                    }
                    this.close();
                }
            },
            {
                text: '取消',
                elCls: 'button',
                handler: function () {
                    this.close();
                }
            }
        ];
        top.BUI.use('bui/overlay', function (Overlay) {
            top.dialog = new Overlay.Dialog({
                title: title,
                width: '680',
                height: '500',
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
    //去重
    function d_supplier_name(string_name, type) {
        string = string_name.split(',');
        var hash = [], arr = [];
        for (var i = 0, elem; (elem = string[i]) != null; i++) {
            if (!hash[elem]) {
                arr.push(elem);
                hash[elem] = true;
            }
        }
        if (type === 'code') {
            $("#supplier_code").val(arr.join(','));
        } else {
            $("#supplier_name").val(arr.join(','));
        }
    }
    function deal_data_1(obj, _type) {
        var supplier_code = new Array();
        var supplier_name = new Array();
        var string_code = "";
        var string_name = "";
        string_code = $("#supplier_code").val();
        string_name = $("#supplier_name").val();
        $.each(obj, function (i, val) {
            supplier_code[i] = val[_type + '_code'];
            supplier_name[i] = val[_type + '_name'];
        });
        supplier_code = supplier_code.join(',');
        supplier_name = supplier_name.join(',');
        if (string_code === "") {
            string_code = supplier_code;
            string_name = supplier_name;
            $("#supplier_code").val(string_code);
            $("#supplier_name").val(string_name);
        } else {
            string_code = string_code + ',' + supplier_code;
            string_name = string_name + ',' + supplier_name;
            $("#supplier_code").val(string_code);
            $("#supplier_name").val(string_name);
        }
    }
    function auto_enter(_id) {
        var e = jQuery.Event("keyup");//模拟一个键盘事件
        e.keyCode = 13;//keyCode=13是回车
        $(_id).trigger(e);
    }
</script>
