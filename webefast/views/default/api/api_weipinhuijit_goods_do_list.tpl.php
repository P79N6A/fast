<?php echo load_js("pur.js", true); ?>
<?php echo load_js("baison.js", true); ?>
<?php
render_control('PageHead', 'head1', array('title' => '唯品会商品列表',
    'links' => array(
        array('url' => 'api/sys/goods/down&app_scene=add&sale_channel=weipinhui&type=jit', 'title' => '获取商品', 'is_pop' => true, 'pop_size' => '800,500'),
//        array('type' => 'js', 'js' => 'adjust_inv()', 'title' => '导入同步库存', 'priv' => 'api/api_weipinhuijit_goods/adjust_inv'),
    ),
    'ref_table' => 'table'
));

?>
 
<?php
$keyword_goods['goods_barcode'] = '平台商品条码';
$keyword_goods['goods_from_id'] = '平台货号';

$keyword_goods = array_from_dict($keyword_goods);


//库存同步
$is_synckc = array(
    '0' => '否',
    '1' => '是',
);
$is_synckc = array_from_dict($is_synckc);
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
    ),
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_goods),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_goods,
            'id' => 'keyword_goods_value',
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => $response['shop'],
        ),
        array(
            'label' => '平台状态',
            'type' => 'select',
            'id' => 'status',
            'data' => array(
                array('content' => '', 'id' => '全部'),
                array('content' => '0', 'id' => '未售'),
                array('content' => '1', 'id' => '在售'),
            )
        ),
        array(
            'label' => '是否允许同步',
            'type' => 'select_multi',
            'id' => 'is_sync_inv',
            'data' => $is_synckc,
        ),
        array(
            'label' => '最后同步时间',
            'type' => 'group',
            'field' => 'latest_update_time',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'latest_update_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'latest_update_time_end', 'remark' => ''),
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
                'width' => '80',
                'align' => '',
                'buttons' => array(
                    array('id' => 'kc_sync', 'title' => '库存同步', 'callback' => 'kc_sync', 'priv' => 'sys/user/enable', 'show_cond' => ''),
                    array('id' => 'api_log','title' => '同步日志','callback'=> 'show_api_log', ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '允许库存同步',
                'field' => 'is_allow_sync_inv',
                'width' => '100',
                'align' => '',
                'format_js' => array(
                    'type' => 'function',
                    'value' => 'get_is_allow_sync_inv'
                )
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台商品条码',
                'field' => 'goods_barcode',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台商品名称',
                'field' => 'goods_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台货号',
                'field' => 'goods_from_id',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台状态',
                'field' => 'status',
                'width' => '100',
                'align' => '','format_js' => array(
                'type' => 'map',
                'value' => array(
                    '1' => '在售',
                    '0' => '未售 ',
                ),
            ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '唯品会仓库',
                'field' => 'warehouse_name',
                'width' => '100',
                'align' => ''
            ),
            /*array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台可售库存',
                'field' => 'weipinhui_num',
                'width' => '100',
                'align' => ''
            ),*/
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '最后同步库存数',
                'field' => 'weipinhui_last_sync_inv_num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '最后同步时间',
                'field' => 'weipinhui_inv_up_time',
                'width' => '100',
                'align' => ''
            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '熔断值',
//                'field' => 'circuit_break_value',
//                'width' => '100',
//                'align' => ''
//            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '唯品会订单占用库存数',
                'field' => 'order_item_sum',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '购物车占用库存数',
                'field' => 'current_hold',
                'width' => '150',
                'align' => ''
            ),

        )
    ),
    'dataset' => 'api/WeipinhuijitGoodsModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'CheckSelection' => true,
    'init' => 'nodata',
    'export'=> array('id'=>'exprot_list','conf'=>'api_weipinhuijit_goods_do_list','name'=>'唯品会商品列表','export_type'=>'file'),//
));
?>
<ul class="toolbar frontool">
    <?php if (load_model('sys/PrivilegeModel')->check_priv('api/api_weipinhuijit_goods/opt_enable_inv')) { ?>
        <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="active('enable')">批量允许库存同步</button></li>
    <?php } ?>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('api/api_weipinhuijit_goods/opt_disable_inv')) { ?>
        <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="active('disable')">批量禁止库存同步</button></li>
    <?php } ?>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('api/api_weipinhuijit_goods/once_enable_inv')) { ?>
        <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="once_active('enable')">一键允许库存同步</button></li>
    <?php } ?>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('api/api_weipinhuijit_goods/once_disable_inv')) { ?>
        <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="once_active('disable')">一键禁止库存同步</button></li>
    <?php } ?>
    <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="multi_kc_sync()">批量库存同步</button></li>
    <div class="front_close">&lt;</div>
</ul>

<script type="text/javascript">

    $(function () {
        function tools() {
            $(".frontool").animate({left: '0px'}, 1000);
            $(".front_close").click(function () {
                if ($(this).html() == "&lt;") {
                    $(".frontool").animate({left: '-100%'}, 1000);
                    $(this).html(">");
                    $(this).addClass("close_02").animate({right: '-10px'}, 1000);
                } else {
                    $(".frontool").animate({left: '0px'}, 1000);
                    $(this).html("<");
                    $(this).removeClass("close_02").animate({right: '0'}, 1000);
                }
            });
        }

        tools();
    })

    $("#tid").css('border', '1px solid red');

    function get_checkbox_id() {
        var str = "";
        var check_id_arr = tableGrid.getSelection();
        for (var i = 0; i < check_id_arr.length; i++) {
            str += check_id_arr[i].id + ",";
        }
        str = str.substring(0, str.length - 1);
        return str;
    }

    //库存同步
    function kc_sync(index, row) {
        var id = row.api_goods_sku_id;
        kc_sync_action(id);
    }

    function kc_sync_action(id) {
        var url = "?app_act=api/sys/goods/sync_goods_inv&api_name=weipinhuijit&id=" + id;
        ajax_post({
            url: url,
            async: false,
            alert: false,
            callback: function (data) {
                var type = data.status == "1" ? 'success' : 'error';
                BUI.Message.Alert(data.message, type);
            }
        });
    }

    function adjust_inv() {
        url = "?app_act=api/api_weipinhuijit_goods/adjust_inv";
        new ESUI.PopWindow(url, {
            title: "导入同步库存",
            width: 500,
            height: 400,
            onBeforeClosed: function () {
                tableStore.load();
            },
            onClosed: function () {
            }
        }).show();
    }

    function get_is_allow_sync_inv(value, row, index) {
        if (value == 1) {
            return '<a href="javascript:void(0)" onclick="sku_is_sync_inv(this,' + row.api_goods_sku_id + ',' + value + ')"><img  src="' + ES.Util.getThemeUrl('images/ok.png') + '" /></a>';
        } else {
                return '<a href="javascript:void(0)" onclick="sku_is_sync_inv(this,' + row.api_goods_sku_id + ',' + value + ')"><img  src="' + ES.Util.getThemeUrl('images/no.gif') + '" /></a>';
        }
    }

    function sku_is_sync_inv(_this, api_goods_sku_id, value) {
        value = (value == 0) ? 1 : 0;
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('oms/api_goods/update_active_sku&app_fmt=json'); ?>',
            data: {id: api_goods_sku_id, type: value},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    var row = {api_goods_sku_id: api_goods_sku_id};
                    var html = get_is_allow_sync_inv(value, row, 1);
                    $(_this).parent().html(html);
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    
    //一键库存操作
    function once_active(type) {
        var str = type == 'enable' ? '是否确定一键允许唯品会所有商品库存同步' : '是否确定一键禁止唯品会所有商品库存同步';
        BUI.Message.Confirm( str,function(){       
            ajax_post({
                url: "?app_act=oms/api_goods/once_update_active",
                data: {type: type},
                async: true,
                alert: false,
                callback: function (data) {
                    var type = data.status == "1" ? 'success' : 'error';
                    BUI.Message.Alert(data.message, type);
                    tableStore.load();                   
                }
            })
        },'question');
    }

    function active(type) {
        get_checked($(this), function (ids) {
            var id = '';
            var check_id_arr = tableGrid.getSelection();        
            for (var i = 0; i < check_id_arr.length; i++) {
                id += check_id_arr[i].goods_from_id + ",";
            }
            id = id.substring(0, id.length - 1);
            ajax_post({
                url: "?app_act=oms/api_goods/p_update_active",
                data: {id: id, type: type, ids:ids},
                async: false,
                alert: false,
                callback: function (data) {
                    var type = data.status == "1" ? 'success' : 'error';
                    BUI.Message.Alert(data.message, type);                   
                        tableStore.load();                   
                }
            })           
        });       
    }


    /**
     * 批量库存同步
     * @param index
     * @param row
     */
    function multi_kc_sync() {
        get_checked($(this), function (ids) {
            var id = ids.toString();
            multi_kc_sync_action(id);
        });
    }


    function multi_kc_sync_action(id) {
        BUI.use('bui/overlay', function (Overlay) {
            var dialog = new Overlay.Dialog({
                width: 450,
                height: 120,
                elCls: 'custom-dialog',
                bodyContent: '<p style="font-size:15px">正在批量库存同步，请稍后...</p>',
                buttons: []
            });
            dialog.show();
        });
        var url = "?app_act=api/sys/goods/weipinhui_multi_sync_goods_inv&id=" + id;
        ajax_post({
            url: url,
            async: false,
            alert: false,
            callback: function (data) {
                if (data.status == 1) {
                    //刷新
                    $(".bui-ext-close .bui-ext-close-x").click();
                    BUI.Message.Alert(data.message, function () {
                        tableStore.load();
                    }, 'success')
                } else {
                    $(".bui-ext-close .bui-ext-close-x").click();
                    BUI.Message.Alert(data.message, 'error')
                }
            }
        });
    }


    //读取已选中项
    function get_checked(obj, func) {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择商品", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.api_goods_sku_id);
        }
        ids.join(',');
        func.apply(null, [ids]);
    }
    function show_api_log(_index, row)  {
         show_log(row.api_goods_sku_id);
    }
   function show_log(id) {
            PageHead_show_log('?app_act=api/sys/goods/show_sku_quantity_update&id='+id+'&app_show_mode=pop', '库存同步日志', {w:800,h:500});
        
       }

function PageHead_show_log(_url, _title, _opts) {
	
    new ESUI.PopWindow(_url, {
            title: _title,
            width:_opts.w,
            height:_opts.h,
            onBeforeClosed: function() {   
            }
        }).show();
}
</script>