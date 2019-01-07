<?php echo load_js("baison.js", true); ?>
<?php include_once(get_tpl_path('process_batch_task')); ?>
<script>
    function get_checkbox_id() {
        var str = "";
        var check_id_arr = tableGrid.getSelection();
        for (var i = 0; i < check_id_arr.length; i++) {
            str += check_id_arr[i].api_goods_id + ",";
        }
        str = str.substring(0, str.length - 1);
        return str;
    }

    function sync(index, row) {
        if (typeof row == "undefined") {
            var id = get_checkbox_id();
        } else {
            var id = row.api_goods_id;
        }
        var url = "?app_act=api/sys/goods/sync_goods_inv&id=" + id;
        ajax_post({
            url: url,
            async: false,
            alert: false,
            callback: function (data) {
                var type = data.status == "1" ? 'success' : 'error';
                BUI.Message.Alert(data.message, type);
            }
        })
    }

    //批量库存同步
    function sync_multi(index, row) {
        var task_info = {};
        task_info['act'] = 'app_act=api/sys/goods/sync_goods_inv';
        task_info['obj_name'] = '批量库存同步';
        task_info['ids_params_name'] = 'id';
        var task_name = '批量库存同步';
        process_batch_task(task_info['act'], task_name, task_info['obj_name'], task_info['ids_params_name'], 0, undefined, undefined, undefined, 'sync_goods_inv');
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
            ids.push(row.api_goods_id);
        }
        ids.join(',');
        func.apply(null, [ids]);
    }
    
    function active(type) {
        get_checked($(this), function () {
            var id = '';
            var check_id_arr = tableGrid.getSelection();
            for (var i = 0; i < check_id_arr.length; i++) {
                id += check_id_arr[i].goods_from_id + ",";
            }
            id = id.substring(0, id.length - 1);
            ajax_post({
                url: "?app_act=oms/api_goods/p_update_active",
                data: {id: id, type: type, ids:'pt'},
                async: false,
                alert: false,
                callback: function (data) {
                    var type = data.status == "1" ? 'success' : 'error';
                    BUI.Message.Alert(data.message, type);                   
                    tableStore.load();  
                }
            });
        });       
    }


    function allow_onsale(type) {
        var id = '';
        var check_id_arr = tableGrid.getSelection();
        for (var i = 0; i < check_id_arr.length; i++) {
            id += check_id_arr[i].goods_from_id + ",";
        }
        id = id.substring(0, id.length - 1);
        ajax_post({
            url: "?app_act=oms/api_goods/p_update_onsale",
            data: {id: id, type: type},
            async: false,
            alert: false,
            callback: function (data) {
                var type = data.status == "1" ? 'success' : 'error';
                BUI.Message.Alert(data.message, type);
                if (type == 'success') {
                    tableStore.load();
                }
            }
        })
    }

//批量删除
    function batch_delete() {
        var id = '';
        var check_id_arr = tableGrid.getSelection();
        for (var i = 0; i < check_id_arr.length; i++) {
            id += check_id_arr[i].api_goods_id + ",";
        }
        id = id.substring(0, id.length - 1);
        if (id == '') {
            BUI.Message.Alert('请选择删除商品！', 'info');
        } else {
            BUI.Message.Confirm('确定删除这些商品吗？', function () {
                $.post('?app_act=oms/api_goods/batch_delete', {api_goods_id: id}, function (ret) {
                    var type = ret.status == "1" ? 'success' : 'error';
                    BUI.Message.Alert(ret.message, type);
                    if (type == 'success') {
                        tableStore.load();
                    }
                }, 'json');
            }, 'warning');
        }
    }


</script>
<?php
$links = array(array('url' => 'api/sys/goods/down&app_scene=add', 'title' => '商品下载', 'is_pop' => true, 'pop_size' => '800,500'));
if($response['service_export_api_goods']==TRUE){
    $links[] =array('url' => 'api/sys/goods/import', 'title' => '商品导入', 'is_pop' => true, 'pop_size' => '600,350');
}
render_control('PageHead', 'head1', array('title' => '平台商品列表',
//    'links' => array(
//        array('url' => 'api/sys/goods/down&app_scene=add', 'title' => '商品下载', 'is_pop' => true, 'pop_size' => '800,500'),
//        array('url' => 'api/sys/goods/import', 'title' => '商品导入', 'is_pop' => true, 'pop_size' => '600,350'),
//    //  array('url' => 'api/sys/goods/upload&app_scene=add', 'title' => '一键库存同步', 'is_pop' => true, 'pop_size' => '800,600'),
//    ),
    'links' =>$links,
    'ref_table' => 'table'
));
?>

<?php
//库存同步
$is_synckc = array(
    '0' => '否',
    '1' => '是',
);
$is_synckc = array_from_dict($is_synckc);
//商品状态
$status = array(
    '0' => '在库',
    '1' => '在售',
);
$status = array_from_dict($status);
//商品基本信息
$keyword_type = array();
$keyword_type['goods_code'] = '平台商品编码';
$keyword_type['goods_barcode'] = '平台规格编码';
$keyword_type['goods_name'] = '平台商品名称';
$keyword_type['goods_from_id'] = '平台商品ID';
$keyword_type['sku_id'] = '平台SKUID';
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
    ),
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
            'help' => '平台商品编码和平台规格编码支持多个查询，用逗号隔开；平台商品编码、平台规格编码支持模糊查找',
            'tip_cls' => 'tip',
            'tip_align' => 'bottom-left',
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => $response['shop_code'],
        ),
        array(
            'label' => '商品状态',
            'type' => 'select_multi',
            'id' => 'status',
            'data' => $status,
        ),
        array(
            'label' => '是否库存同步',
            'type' => 'select_multi',
            'id' => 'is_sync_inv',
            'data' => $is_synckc,
        ),
        array(
            'label' => '是否允许上架',
            'type' => 'select_multi',
            'id' => 'is_allow_onsale',
            'help' => '配合淘系参数：商品库存同步且上架使用，默认仅库存同步，如果在库商品需要上架请开启参数。',
            'tip_cls' => 'tip',
            'tip_align' => 'bottom-right',
            'data' => $is_synckc,
        ),
        array(
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'sale_channel_code',
            'data' => load_model('base/SaleChannelModel')->get_my_select(),
        ),
    )
));
?>
<ul class="toolbar frontool">
    <?php if (load_model('sys/PrivilegeModel')->check_priv('api/sys/goods/sync_goods_inv_pt')) { ?>
        <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="sync_multi()">批量库存同步</button></li>
    <?php } ?>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/api_goods/p_update_active_pt')) { ?>
        <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="active('enable')">批量允许库存同步</button></li>
    <?php } ?>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/api_goods/p_update_active/ban_pt')) { ?>
        <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="active('disable')">批量禁止库存同步</button></li>
    <?php } ?>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/api_goods/p_update_onsale')) { ?>
        <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="allow_onsale('enable')">批量允许上架</button></li>
    <?php } ?>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/api_goods/p_update_onsale/ban')) { ?>
        <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="allow_onsale('disable')">批量禁止上架</button></li>
    <?php } ?>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('api/sys/goods/delete')) { ?>
        <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="batch_delete( )">批量删除</button></li>
    <?php } ?>
    <div class="front_close">&lt;</div>
</ul>
<script>
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
</script>

<?php
$cascade_list = array(
    array('title' => '平台SKUID', 'field' => 'sku_id', 'width' => '110'),
    array('title' => '平台规格编码', 'field' => 'goods_barcode_html', 'width' => '120'),
    array('title' => '平台商品属性', 'field' => 'sku_properties_name', 'width' => '120','format_js' => array('type' => 'function', 'value' => "detail_show_goods_link",),),
    array('title' => '平台售价(元)', 'field' => 'price', 'width' => '90'),
    array('title' => '平台库存', 'field' => 'num', 'width' => '70'),
    array('title' => '最后同步库存数量', 'width' => '110', 'field' => 'last_sync_inv_num'),
    array('title' => '最后同步库存时间', 'width' => '150', 'field' => 'inv_up_time'),
    array('title' => '允许同步库存', 'field' => 'is_allow_sync_inv', 'width' => '90', 'format_js' => array('type' => 'function', 'value' => 'get_is_allow_sync_inv'), 'align' => 'center'),
    array('title' => '平台已删除', 'field' => 'sku_status', 'width' => '80', 'format_js' => array('type' => 'map_checked', 'value' => 'sku_status'), 'align' => 'center'),

);
if ($response['presell_plan'] == 1) {
    $cascade_list[] = array('title' => '预售状态<img height="23" width="23" src="assets/images/tip.png" data-align="top-right" class="tip" title="若平台商品在预售计划中已设置为预售商品，并且预售计划已同步库存，<br>则不允许平台库存同步，通过预售计划同步预售库存。" />', 'field' => 'presell_status', 'width' => '90', 'format_js' => array('type' => 'map_checked', 'value' => 'sku_status'), 'align' => 'center');
    $cascade_list[] = array('title' => '预售结束时间', 'field' => 'presell_end_time', 'width' => '145');
}
    $cascade_list[]  =  array('title' => '同步日志', 'field' => '', 'width' => '80', 'format_js' => array('type' => 'function', 'value' => 'show_api_log'), 'align' => 'center');
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '120',
                'align' => 'center',
                'buttons' => array(
                    array('id' => 'send_again', 'priv' => 'api/sys/goods/sync_goods_inv_pt', 'title' => '库存同步', 'callback' => 'sync', 'show_cond' => ''),
                    array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'confirm' => '确认要删除此平台商品吗？', 'priv' => 'api/sys/goods/delete'),

                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品图片',
                'field' => 'goods_img_view',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '销售平台',
                'field' => 'source_name',
                'width' => '100',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台商品ID',
                'field' => 'goods_from_id',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台商品编码',
                'field' => 'goods_code',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台商品名称',
                'field' => 'goods_name',
                'width' => '100',
                'align' => '',
                'format_js' => array(
                    'type' => 'function',
                    'value' => "show_goods_link",
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台售价(元)',
                'field' => 'price',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '是否有SKU',
                'field' => 'has_sku',
                'width' => '80',
                'align' => 'center',
                'format_js' => array(
                    'type' => 'map',
                    'value' => array(
                        '1' => '是',
                        '0' => '否',
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '库存扣减模式<img height="23" width="23" data-align="top-right" class="tip" src="assets/images/tip.png" title="拍下减库存：以系统库存扣减未付款商品数量同步线上；<br>付款减库存：按系统库存同步线上" />',
                'field' => 'stock_type',
                'width' => '120',
                'align' => 'center',
                'format_js' => array(
                    'type' => 'map',
                    'value' => array(
                        '1' => '拍下减库存',
                        '2' => '付款减库存 ',
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品状态',
                'field' => 'status',
                'width' => '80',
                'align' => 'center',
                'format_js' => array(
                    'type' => 'map',
                    'value' => array(
                        '1' => '在售',
                        '0' => '在库 ',
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '允许上架',
                'width' => '90',
                'align' => 'center',
                'field' => 'is_allow_onsale',
                'help' => '配合淘系参数：商品库存同步且上架使用，默认仅库存同步，如果在库商品需要上架请开启参数。',
                'format_js' => array(
                    'type' => 'function',
                    'value' => 'get_is_allow_onsale'
                )
            ),
        )
    ),
    'dataset' => 'api/sys/ApiGoodsModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'api_goods_id',
    'export' => array('id' => 'exprot_list', 'conf' => 'api_goods_list', 'name' => '平台商品', 'export_type' => 'file'),
    'CheckSelection' => true,
    'init' => 'nodata',
    'CascadeTable' => array(
        'list' => $cascade_list,
        'page_size' => 1000,
        'url' => get_app_url('oms/api_goods/get_sku_list_by_item_id'),
        'params' => 'goods_from_id,is_allow_sync_inv_value',
    ),
));
?>


<script type="text/javascript">
    function show_goods_link(value, row, index) {
        if (row.source == 'taobao') {
            return "<a href='http://item.taobao.com/item.htm?id=" + row.goods_from_id + "' target='_blank'>" + row.goods_name + "</a>";
        }else{
            return row.goods_name;
        }
    }
    //sku级跳转
    function detail_show_goods_link(value, row, index) {
        if (row.source == 'jingdong') {
            return "<a href='https://item.jd.com/" + row.sku_id + ".html' target='_blank'>" + row.sku_properties_name + "</a>";
            //    https://item.jd.com/11727092516.html
        } else {
            return row.sku_properties_name;
        }
    }



    function check_api_goods_sku_id_render(value, row, index) {
        return "<input type='checkbox' value='" + row.api_goods_sku_id + "' class='chk_api_goods_sku_id'/>";
    }
    function get_is_allow_sync_inv(value, row, index) {
        if (value == 1) {
<?php if (load_model('sys/PrivilegeModel')->check_priv('oms/api_goods/p_update_active/ban')) { ?>
                return '<a href="javascript:void(0)" onclick="sku_is_sync_inv(this,' + row.api_goods_sku_id + ',' + value + ')"><img  src="' + ES.Util.getThemeUrl('images/ok.png') + '" /></a>';
<?php } else { ?>
                return '<img  src="' + ES.Util.getThemeUrl('images/ok.png') + '" />';
<?php } ?>
        } else {
            if (row.presell_status == 0) {
<?php if (load_model('sys/PrivilegeModel')->check_priv('oms/api_goods/p_update_active')) { ?>
                    return '<a href="javascript:void(0)" onclick="sku_is_sync_inv(this,' + row.api_goods_sku_id + ',' + value + ')"><img  src="' + ES.Util.getThemeUrl('images/no.gif') + '" /></a>';
<?php } else { ?>
                    return '<img  src="' + ES.Util.getThemeUrl('images/no.gif') + '" />';
<?php } ?>
            } else {
                return '<img  src="' + ES.Util.getThemeUrl('images/no.gif') + '" title="预售计划已同步库存，不允许平台库存同步" />';
            }
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


    function do_enable(_index, row) {
        _do_set_active(_index, row, 'enable');
    }
    function do_disable(_index, row) {
        _do_set_active(_index, row, 'disable');
    }
    function _do_set_active(_index, row, active) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('oms/api_goods/update_active'); ?>',
            data: {goods_from_id: row.goods_from_id, type: active},
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
    function is_sync(_index, row) {
        ajax_post({
            url: "?app_act=sys/task/send_order&id=" + id,
            async: false,
            alert: false,
            data: {"type": "0"},
            callback: function (data) {
                var type = data.status == "1" ? 'success' : 'error';
                BUI.Message.Alert(data.message, type);
            }
        })

    }


    function get_is_allow_onsale(value, row, index) {
        if (value == 1) {
<?php if (load_model('sys/PrivilegeModel')->check_priv('oms/api_goods/p_update_onsale/ban')) { ?>
                return '<a href="javascript:void(0)" onclick="goods_is_onsale(this,' + row.api_goods_id + ',' + value + ')"><img  src="' + ES.Util.getThemeUrl('images/ok.png') + '" /></a>';
<?php } else { ?>
                return '<img  src="' + ES.Util.getThemeUrl('images/ok.png') + '" />';
<?php } ?>
        } else {
<?php if (load_model('sys/PrivilegeModel')->check_priv('oms/api_goods/p_update_onsale')) { ?>
                return '<a href="javascript:void(0)" onclick="goods_is_onsale(this,' + row.api_goods_id + ',' + value + ')"><img  src="' + ES.Util.getThemeUrl('images/no.gif') + '" /></a>';
<?php } else { ?>
                return '<img  src="' + ES.Util.getThemeUrl('images/no.gif') + '" />';
<?php } ?>
        }
    }


    function goods_is_onsale(_this, api_goods_id, value) {
        value = (value == 0) ? 1 : 0;
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('oms/api_goods/update_goods_onsale&app_fmt=json'); ?>',
            data: {id: api_goods_id, type: value},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    var row = {api_goods_id: api_goods_id};
                    var html = get_is_allow_onsale(value, row, 1);
                    $(_this).parent().html(html);
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });



    }

    //删除
    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('oms/api_goods/do_delete'); ?>', data: {api_goods_id: row.api_goods_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功!', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    function show_api_log(value, row, index) {
        if(row.source=='taobao'||row.source=='weipinhui'||row.source=='jingdong'){
               return '<a href="javascript:void(0)" onclick="show_log('+row.api_goods_sku_id+')">查看日志</a>';
        }else{
            return '';
        }

    }
       function show_log(id) {
            PageHead_show_log('?app_act=api/sys/goods/show_sku_quantity_update&id='+id+'&app_show_mode=pop', '库存同步日志', {w:800,h:500});
        
       }
    
    
//修改关联编码
    function update_goods_barcode(id) {
        var goods_barcode = $('#' + id).text();
        $('#' + id).parent().html("<input type='text'  style='width:100%;height:5%;' onblur=update_goods_barcode_after('" + id + "','" + goods_barcode + "') id='" + id + "' class='goods_barcode'>");
        $('#' + id).focus();
        $('#' + id).css("text-decoration", "none");
        $('#' + id).val(goods_barcode);
    }

    function update_goods_barcode_after(id, pre_goods_barcode) {
        var goods_barcode = $("#" + id).val();
        if (pre_goods_barcode === goods_barcode) {//条形码不变
            var pre_html = '<div class="goods_barcode" onclick="update_goods_barcode(' + id + ')" id="' + id + '"><span style="width:100%;height:5%;">' + pre_goods_barcode + '</span></div>'
            $('#' + id).parent().html(pre_html);
            return;
        }
        var url = '?app_act=oms/api_goods/update_goods_barcode';
        var params = {api_goods_sku_id: id, goods_barcode: goods_barcode};
        $.post(url, params, function (data) {
            var status = data.status;
            switch (status) {
                case -1 ://条码不存在
                    BUI.Message.Alert(data.message, 'error');
                    goods_barcode = pre_goods_barcode;
                    break;
                case 1 :
                    msg_show('修改成功', 'success');
                    break;
                case 2 ://不变
                    goods_barcode = pre_goods_barcode;
                    break;
                case 3 ://填空
                    goods_barcode = pre_goods_barcode;
                    BUI.Message.Alert(data.message, 'error');
                    break;
            }
            var pre_html = '<div class="goods_barcode" onclick="update_goods_barcode(' + id + ')" id="' + id + '"><span style="width:100%;height:5%;">' + goods_barcode + '</span></div>'
            $('#' + id).parent().html(pre_html);
            return;
        }, 'json')
    }

    function msg_show(message, type) {
        BUI.Message.Show({
            msg: message,
            icon: type,
            buttons: [{
                    text: '确认',
                    elCls: 'button button-primary',
                    handler: function () {
                        this.close();
                    }
                }, ],
            autoHide: true,
            autoHideDelay: 1000
        });
    }
</script>
<style>
    .goods_barcode{cursor:pointer;text-decoration:underline;}
</style>

<script>
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