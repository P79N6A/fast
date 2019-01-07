
<style>
    #add{
        cursor:pointer;
        color: #0096e8;
        text-decoration:underline;
    }
    

</style>
<?php
if ($response['login_type'] == 2) {
    $title = '分销商品列表';
    $url = array();
} else {
    $title = '分销商品定义';
    if (load_model('sys/PrivilegeModel')->check_priv('fx/goods/add_goods_fx')) {
        $url[] = array('type' => 'js', 'js' => 'add_fx_goods()', 'title' => '添加分销商品');
    }
    if(load_model('sys/PrivilegeModel')->check_priv('fx/goods/import_goods_fx')){
        $url[] = array('url' => 'fx/goods/import', 'title' => '导入分销商品', 'is_pop' => true, 'pop_size' => '500,400');
    }
}
render_control('PageHead', 'head1', array('title' => $title,
    'links' =>$url,
    'ref_table' => 'table'
));
?>
<?php
$keyword_type = array();
$keyword_type['goods_code'] = '商品编码';
$keyword_type['goods_name'] = '商品名称';
$keyword_type['goods_short_name'] = '商品简称';
$keyword_type = array_from_dict($keyword_type);
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
$list = array(
//    array(
//        'type' => 'text',
//        'show' => 1,
//        'title' => '分销款',
//        'field' => 'is_custom_money',
//        'width' => '60',
//        'align' => '',
//        'format_js' => array(
//            'type' => 'function',
//            'value' => 'get_is_allow_onsale'
//        )
//    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '商品编码',
        'field' => 'goods_code',
        'width' => '150',
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
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '年份',
        'field' => 'year_name',
        'width' => '100',
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
);
if ($response['login_type'] != 2) {
    $operate = array(
        'type' => 'text',
        'show' => 1,
        'title' => '指定分销商',
        'field' => 'is_custom',
        'width' => '80',
        'align' => '',
        'format_js' => array('type' => 'map_checked')
    );
    array_unshift($list, $operate);
    $operate = array(
        'type' => 'button',
        'show' => 1,
        'title' => '操作',
        'field' => '_operate',
        'width' => '120',
        'align' => '',
        'buttons' => array(
            array('id' => 'is_custom', 'title' => '指定分销商', 'callback' => 'add_fx_custom', 'show_cond' => ''),
            array('id' => 'disable', 'title' => '清除', 'callback' => 'set_custom_money_alone', 'show_cond' => ''),
        ),
    );
    array_unshift($list, $operate);
}
$cascade_table = array();
$fields = array(
    array(
        'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
        'type' => 'input',
        'title' => '支持模糊查询',
        'data' => $keyword_type,
        'id' => 'keyword',
    )
);
if ($response['login_type'] != 2) {
    //复选框
    $checkselection = TRUE;
    $fields[] = array(
        'label' => '指定分销商',
        'type' => 'select',
        'id' => 'is_custom',
        'data' => ds_get_select_by_field('is_custom',1),
        'help' => '指定分销商，即指定分销商才能看到此商品。',
        'tip_cls' => 'tip',
        'tip_align' => 'bottom-right',
    );
    $fields[] = array(
        'label' => '分销商',
        'type' => 'select_pop',
        'id' => 'custom_code',
        'select' => 'base/custom_multi'
    );
    $fields[] = array(
        'label' => '分类',
        'type' => 'select_multi',
        'id' => 'category_code',
        'data' => $response['category'],
    );
    $fields[] = array(
        'label' => '品牌',
        'type' => 'select_multi',
        'id' => 'brand_code',
        //'data'=>ds_get_select('brand_code'),
        'data' => $response['brand'],
    );
    $fields[] = array(
        'label' => '年份',
        'type' => 'select_multi',
        'id' => 'year_code',
        'data' => ds_get_select('year'),
    );
    $fields[] = array(
        'label' => '季节',
        'type' => 'select_multi',
        'id' => 'season_code',
        'data' => ds_get_select('season_code'),
    );
    $fields[] = array(
        'label' => '更新时间',
        'type' => 'group',
        'field' => 'daterange2',
        'child' => array(
            array('title' => 'start', 'type' => 'date', 'field' => 'lastchanged_start',),
            array('pre_title' => '~', 'type' => 'date', 'field' => 'lastchanged_end',)
        )
    );
}

render_control('SearchForm', 'searchForm', array(
    'buttons' => $buttons,
    'show_row' => 4,
    'fields' => $fields,
));
if ($response['login_type'] == 2) {
    //复选框
    $checkselection = false;
    /*$list[] = array(
        'type' => 'text',
        'show' => 1,
        'title' => '分销价',
        'field' => 'fx_price',
        'width' => '100',
        'align' => ''
    );*/
    $cascade_table = array(
        'list' => array(
            array('title' => '商品条形码', 'type' => 'text', 'width' => '250', 'field' => 'barcode'),
            array('title' => '系统规格', 'type' => 'text', 'width' => '200', 'field' => 'spec_str'),
            array('title' => '分销价', 'type' => 'text', 'width' => '150', 'field' => 'fx_price'),
        ),
        'page_size' => 10,
        'url' => get_app_url('fx/goods/get_goods_barcode_list&app_fmt=json'),
        'params' => 'goods_code,is_custom,fx_price',
    );
} else {
//    render_control('TabPage', 'TabPage1', array(
//        'tabs' => array(
//            array('title' => '全部', 'active' => FALSE, 'id' => 'tabs_all'),
//            array('title' => '非分销款', 'active' => FALSE, 'id' => 'no_custom_money'),
//            array('title' => '分销款', 'active' => TRUE, 'id' => 'custom_money'),
//            array('title' => '指定分销商', 'active' => false, 'id' => 'custom_goods'),
//        ),
//        'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
//    ));
    $cascade_table = array(
        'list' => array(
            array('title' => '指定分销商', 'type' => 'text', 'width' => '150', 'field' => 'custom_name'),
            array('title' => '分销价', 'type' => 'text', 'width' => '100', 'field' => 'fx_price',
                'format_js' => array(
                    'type' => 'function',
                    'value' => 'fx_price'
                )),
            array('title' => '分销折扣', 'type' => 'text', 'width' => '100', 'field' => 'fx_rebate',
                'format_js' => array(
                    'type' => 'function',
                    'value' => 'fx_rebate'
                )),
            array('title' => '修改人', 'type' => 'text', 'width' => '150', 'field' => 'modify_name'),
            array('title' => '修改时间', 'type' => 'text', 'width' => '200', 'field' => 'lastchanged'),
            array('title' => '操作', 'type' => 'text', 'width' => '100', 'field' => 'operation',
                'format_js' => array(
                    'type' => 'function',
                    'value' => 'operation'
                )),
        ),
        'page_size' => 10,
        'url' => get_app_url('fx/goods/get_goods_custom_list&app_fmt=json'),
        'params' => 'goods_code',
    );
}
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => $list
    ),
    'dataset' => 'fx/GoodsModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'goods_id',
    'export' => array('id' => 'exprot_list', 'conf' => 'fx_goods_record_list', 'name' => '商品列表','export_type'=>'file'),
    'CascadeTable' => $cascade_table,
    'CheckSelection' => $checkselection,
));
if ($response['login_type'] != 2) {
    ?>

    <div id="TabPage1Contents">
        <div>
            <ul  class="toolbar frontool"  id="ToolBar1">
<!--                <li class="li_btns"><button class="button button-primary btn_opt_custom_money" onclick="set_custom_money(1)">批量设置分销款</button></li>-->
                <li class="li_btns"><button class="button button-primary btn_opt_goods_custom" onclick="set_goods_custom()">批量指定分销商</button></li>
                <li class="li_btns"><button class="button button-primary btn_opt_custom_money" onclick="set_custom_money(0)">批量清除</button></li>
                <?php if(load_model('sys/PrivilegeModel')->check_priv('fx/goods/remove_all_goods')) { ?> 
                <li class="li_btns"><button class="button button-primary btn_opt_custom_money" onclick="remove_all_goods()" title="根据检索条件清除商品">一键清除</button></li>
                <?php } ?>
                <!--<li class="li_btns"><button class="button button-primary btn_opt_edit_store_code ">批量修改发货仓库</button></li>-->

                <?php // if (load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_edit_express_code')) {  ?>
                <!--<li class="li_btns"><button class="button button-primary btn_opt_edit_express_code ">批量修改配送方式</button></li>-->
                <?php // }  ?>
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
        </div>
<!--        <div>-->
<!--            <ul  class="toolbar frontool"  id="ToolBar2">-->
<!--                <li class="li_btns"><button class="button button-primary btn_opt_settlement" onclick="set_custom_money(1)">批量设置分销款</button></li>-->
<!--                <div class="front_close">&lt;</div>-->
<!--            </ul>-->
<!--        </div>-->
<!--        <div>-->
<!--            <ul  class="toolbar frontool"  id="ToolBar3">-->
<!--                <li class="li_btns"><button class="button button-primary btn_opt_edit_order_remark" onclick="set_goods_custom()">批量指定分销商</button></li>-->
<!--                <li class="li_btns"><button class="button button-primary btn_opt_custom_money" onclick="set_custom_money(0)">批量设置正常款</button></li>-->
<!--            </ul>-->
<!--        </div>-->
<!--        <div>-->
<!--            <ul  class="toolbar frontool"  id="ToolBar4">-->
<!--                <li class="li_btns"><button class="button button-primary btn_opt_edit_order_remark" onclick="set_goods_custom()">批量指定分销商</button></li>-->
<!--                <li class="li_btns"><button class="button button-primary btn_opt_custom_money" onclick="set_custom_money(0)">批量设置正常款</button></li>-->
<!--            </ul>-->
<!--        </div>-->
<!--    </div>-->
<?php } ?>

<script>
    $('#exprot_detail').click(function(){
        var params = '';
        var url = '?app_act=sys/export_csv/export_show'; //暂时不是框架级别
//        var url = '?app_act=ctl/index/do_index&app_ctl=DataTable/do_get_data';
        params = tableStore.get('params');
        params.ctl_type = 'export';
        params.ctl_export_conf = 'fx_goods_record_detail';
        params.ctl_export_name =  '分销商品定义';
        <?php echo   create_export_token_js('fx/GoodsModel::get_by_page');?>
        var obj = searchFormForm.serializeToObject();
        for(var key in obj){
            params[key] =  obj[key];
	}

        for(var key in params){
            url +="&"+key+"="+params[key];
        }
        params.ctl_type = 'view';
        window.open(url);
    });
    
    var login_type = <?php echo $response['login_type']; ?>;
    function get_is_allow_onsale(value, row, index) {
        if (value == 1) {
            if (login_type != 2) {
                return '<a href="javascript:void(0)" onclick="goods_is_custom_money(' + "'" + row.goods_code + "'" + ',' + value + ')"><img  src="' + ES.Util.getThemeUrl('images/ok.png') + '" /></a>';
            } else {
                return '<img  src="' + ES.Util.getThemeUrl('images/ok.png') + '" />';
            }
        } else {
            if (login_type != 2) {
                return '<a href="javascript:void(0)" onclick="goods_is_custom_money(' + "'" + row.goods_code + "'" + ',' + value + ')"><img  src="' + ES.Util.getThemeUrl('images/no.gif') + '" /></a>';
            } else {
                return '<img  src="' + ES.Util.getThemeUrl('images/no.gif') + '" />';
            }
        }
    }
    function operation(value, row, index) {
        if (login_type != 2) {
            return "<a href='javascript:void(0)' style = 'text-decoration:underline' onclick='delete_custom(" + '"' + row.goods_code + '"' + "," + '"' + row.custom_code + '"' + ")'>删除</a>";
        }
    }

    function fx_price(value, row, index) {
        if (login_type != 2) {
            return "<div id = '" + row.goods_code + '_' + row.custom_code + "_price'><a href='javascript:void(0)' style = 'text-decoration:underline' onclick='set_fx_price(" + '"' + value + '"' + "," + '"' + row.goods_code + '"' + "," + '"' + row.custom_code + '"' + "," + '"' + row.fx_rebate + '"' + ")'>" + value + "</a></div>";
        } else {
            return "<span>" + value + "<span>";
        }
    }

    function set_fx_price(fx_price, goods_code, custom_code,fx_rebate) {
        var id = goods_code + "_" + custom_code + "_price";
        $('#' + id).html("<input type = 'text' value = '" + fx_price + "' style = 'width:60px;' onblur = 'save_fx_price(this.value," + '"' + goods_code + '"' + "," + '"' + custom_code + '"' + "," + '"' + fx_price + '"' + "," + '"' + fx_rebate+ '"' + ")' name = 'fx_price'>");
        $('input[name=fx_price]').focus();
    }
    
    function fx_rebate(value, row, index) {
        if (login_type != 2) {
            return "<div id = '" + row.goods_code + '_' + row.custom_code + "_rebate'><a href='javascript:void(0)' style = 'text-decoration:underline' onclick='set_fx_rebate(" + '"' + value + '"' + "," + '"' + row.goods_code + '"' + "," + '"' + row.custom_code + '"' + "," + '"' + row.fx_price+ '"' + ")'>" + value + "</a></div>";
        } else {
            return "<span>" + value + "<span>";
        }
    }
    function set_fx_rebate(fx_rebate, goods_code, custom_code,fx_price) {
        var id = goods_code + "_" + custom_code + "_rebate";
        $('#' + id).html("<input type = 'text' value = '" + fx_rebate + "' style = 'width:60px;' onblur = 'save_fx_rebate(this.value," + '"' + goods_code + '"' + "," + '"' + custom_code + '"' + "," + '"' + fx_rebate + '"' + "," + '"' + fx_price+ '"' + ")' name = 'fx_rebate'>");
        $('input[name=fx_rebate]').focus();
    }
    /**
     * 保存分销价
     * @param {type} fx_price
     * @param {type} goods_code
     * @param {type} custom_code
     * @param {type} initial_price
     * @returns {Boolean}
     */
    function save_fx_price(fx_price, goods_code, custom_code, initial_price,fx_rebate) {
        var id = goods_code + "_" + custom_code + "_price";
        var rebate_id = goods_code + "_" + custom_code + "_rebate";
        var a = /^[0-9]*(\.[0-9]{1,2})?$/;
        if (!a.test(fx_price) || fx_price == undefined || fx_price == '')
        {
            BUI.Message.Alert('金额格式不正确', 'error');
            $('#' + id).html("<a href='javascript:void(0)' style = 'text-decoration:underline' onclick='set_fx_price(" + '"' + initial_price + '"' + "," + '"' + goods_code + '"' + "," + '"' + custom_code + '"' + "," + '"' + fx_rebate + '"' + ")'>" + initial_price + "</a>");
            return false;
        }
        if(initial_price == fx_price) {
            $('#' + id).html("<a href='javascript:void(0)' style = 'text-decoration:underline' onclick='set_fx_price(" + '"' + fx_price + '"' + "," + '"' + goods_code + '"' + "," + '"' + custom_code + '"' + "," + '"' + fx_rebate + '"' + ")'>" + fx_price + "</a>");
            return false;
        }
        var url = "?app_act=fx/goods/save_fx_price";
        $.post(url, {fx_price: fx_price, goods_code: goods_code, custom_code: custom_code}, function (data) {
            var price = 0;
            var rebate = rebate;
            if (data.status < 0) {
                BUI.Message.Alert(data.message, 'error');
                price = initial_price;
                rebate = fx_rebate;
            } else {
                BUI.Message.Alert(data.message, 'success');
                price = data.data['fx_price'];
                rebate = data.data['fx_rebate'];
            }
            $('#' + id).html("<a href='javascript:void(0)' style = 'text-decoration:underline' onclick='set_fx_price(" + '"' + price + '"' + "," + '"' + goods_code + '"' + "," + '"' + custom_code + '"' + "," + '"' + rebate + '"' + ")'>" + price + "</a>");
            $('#' + rebate_id).html("<a href='javascript:void(0)' style = 'text-decoration:underline' onclick='set_fx_rebate(" + '"' + rebate + '"' + "," + '"' + goods_code + '"' + "," + '"' + custom_code + '"' + "," + '"' + fx_price+ '"' + ")'>" + rebate + "</a>");
            //刷新数据
//            tableStore.load();
        }, 'json');
    }
    /**
     * 保存分销折扣
     */
    function save_fx_rebate(fx_rebate, goods_code, custom_code, initial_rebate,fx_price) {
        var id = goods_code + "_" + custom_code + "_price";
        var rebate_id = goods_code + "_" + custom_code + "_rebate";
        if (fx_rebate > 1 || fx_rebate < 0 || isNaN(fx_rebate))
        {
            BUI.Message.Alert('折扣格式不正确', 'error');
            $('#' + rebate_id).html("<a href='javascript:void(0)' style = 'text-decoration:underline' onclick='set_fx_rebate(" + '"' + initial_rebate + '"' + "," + '"' + goods_code + '"' + "," + '"' + custom_code + '"' + "," + '"' + fx_price+ '"' + ")'>" + initial_rebate + "</a>");
            return false;
        }
        if(fx_rebate == initial_rebate) {
            $('#' + rebate_id).html("<a href='javascript:void(0)' style = 'text-decoration:underline' onclick='set_fx_rebate(" + '"' + initial_rebate + '"' + "," + '"' + goods_code + '"' + "," + '"' + custom_code + '"' + "," + '"' + fx_price+ '"' + ")'>" + initial_rebate + "</a>");
            return false;
        }
        var url = "?app_act=fx/goods/save_fx_rebate";
        $.post(url, {fx_rebate: fx_rebate, goods_code: goods_code, custom_code: custom_code}, function (data) {
            var price = 0;
            var rebate = rebate;
            if (data.status < 0) {
                BUI.Message.Alert(data.message, 'error');
                price = fx_price;
                rebate = initial_rebate;
            } else {
                BUI.Message.Alert(data.message, 'success');
                price = data.data['fx_price'];
                rebate = data.data['fx_rebate'];
            }
            $('#' + id).html("<a href='javascript:void(0)' style = 'text-decoration:underline' onclick='set_fx_price(" + '"' + price + '"' + "," + '"' + goods_code + '"' + "," + '"' + custom_code + '"' + "," + '"' + rebate + '"' + ")'>" + price + "</a>");
            $('#' + rebate_id).html("<a href='javascript:void(0)' style = 'text-decoration:underline' onclick='set_fx_rebate(" + '"' + rebate + '"' + "," + '"' + goods_code + '"' + "," + '"' + custom_code + '"' + "," + '"' + price+ '"' + ")'>" + rebate + "</a>");
            //刷新数据
//            tableStore.load();
        }, 'json');
    }
    
    $(document).ready(function () {
        $("#ipt_fx_price")
        //TAB选项卡
        $("#TabPage1 a").click(function () {
            tableStore.load();
        });

        tableStore.on('beforeload', function (e) {
            e.params.list_tab = $("#TabPage1").find(".active").find("a").attr("id");
            tableStore.set("params", e.params);
        });
    })
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
            ids.push(row.goods_code);
        }
        ids.join(',');
        func.apply(null, [ids]);
    }
    //批量指定分销商
    function set_goods_custom() {
        get_checked($(this), function (ids) {
            var goods_list = ids.toString();
            show_select('custom', goods_list);
        });
    }
    //批量设置分销款
    function set_custom_money(is_goods_custom) {
        var info = '';
        if(is_goods_custom) {
            info = "确定设置为分销款？";
        } else {
            info = "清除后，分销商无法看到此商品，请谨慎操作！";
        }
        BUI.Message.Confirm(info,function(){
            get_checked($(this), function (ids) {
                var ids = ids.toString();
                var url = '?app_act=fx/goods/set_custom_money';
                var params = {};
                params.goods_list = ids;
                params.is_goods_custom = is_goods_custom;
                $.post(url, params, function (data) {
                    BUI.Message.Alert(data.message, 'success');
                    //刷新数据
                    tableStore.load();
                }, 'json');
            });
        },'question');
    }
    function show_select(_type, goods_list) {
        var param = {};
        if (typeof (top.dialog) != 'undefined') {
            top.dialog.remove(true);
        }
        var url = '?app_act=wbm/notice_record/select_custom&list_type=fx_goods_list';
        var buttons = [
            {
                text: '保存继续',
                elCls: 'button button-primary',
                handler: function () {
                    var data = top.SelectoGrid.getSelection();
                    if (data.length > 0) {
                        deal_data_1(data, _type, goods_list);
                    }
                }
            },
            {
                text: '保存退出',
                elCls: 'button button-primary',
                handler: function () {
                    var data = top.SelectoGrid.getSelection();
                    if (data.length > 0) {
                        deal_data_1(data, _type, goods_list);
                    }
                    this.close();
                }
            },
        ];
        top.BUI.use('bui/overlay', function (Overlay) {
            top.dialog = new Overlay.Dialog({
                title: '选择分销商',
                width: '700',
                height: '550',
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
    function deal_data_1(obj, _type, goods_list) {
        var custom_code = new Array();
        $.each(obj, function (i, val) {
            custom_code[i] = val[_type + '_code'];
        });
        custom_code = custom_code.join(',');
        $.post("?app_act=fx/goods/set_goods_custom", {goods_list: goods_list, custom_code: custom_code}, function (data) {
            if (data.status < 0) {
                BUI.Message.Alert(data.message, 'error');
            } else {
                BUI.Message.Alert(data.message, 'success');
            }
            tableStore.load();
        }, "json");
    }
    function goods_is_custom_money(goods_code, is_goods_custom) {
        var msg = is_goods_custom == 1 ? '取消分销款后将删除该商品下绑定的分销商，是否删除？' : '是否开启分销款';
        BUI.Message.Show({
            title: '提示',
            msg: msg,
            icon: 'question',
            buttons: [
                {
                    text: '确认',
                    elCls: 'button button-primary',
                    handler: function () {
                        var url = '?app_act=fx/goods/set_custom_money';
                        var params = {};
                        params.goods_list = goods_code;
                        params.is_goods_custom = is_goods_custom == 1 ? 0 : 1;
                        $.post(url, params, function (data) {
                            BUI.Message.Alert(data.message, 'success');
                            //刷新数据
                            tableStore.load();
                        }, 'json');
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
            ]
        });
    }
    function delete_custom(goods_code, custom_code) {
        BUI.Message.Show({
            title: '提示',
            msg: '确定要删除吗?',
            icon: 'question',
            buttons: [
                {
                    text: '是',
                    elCls: 'button button-primary',
                    handler: function () {
                        $.post("?app_act=fx/goods/delete_custom", {goods_code: goods_code, custom_code: custom_code}, function (data) {
                            if (data.status < 0) {
                                BUI.Message.Alert(data.message, 'error');
                            } else {
                                BUI.Message.Alert(data.message, 'success');
                            }
                            tableStore.load();
                        }, "json");
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

//添加分销商品
    function add_fx_goods() {
        var param = {};
        if (typeof (top.dialog) != 'undefined') {
            top.dialog.remove(true);
        }
        var url = '?app_act=fx/goods/select_fx_goods';
        var buttons = [
            {
                text: '保存继续',
                elCls: 'button button-primary',
                handler: function () {
                    deal_fx_goods(this, 1);
                }
            },
            {
                text: '保存退出',
                elCls: 'button button-primary',
                handler: function () {
                    deal_fx_goods(this, 0);
                    tableStore.load();
                    // this.close();
                }
            },
        ];
        <?php if(load_model('sys/PrivilegeModel')->check_priv('fx/goods/set_all_goods_fx')) { ?>
            buttons.push({
                text: '一键添加',
                elCls: 'button button-primary one_add_goods',
                handler: function () {
//                    BUI.Message.Confirm('是否一键添加？',function(){
                        var filter = top.form.serializeToObject();
                        set_all_goods_fx(this,filter);
                        tableStore.load();
//                    },'question');
                }
            });
        <?php } ?>
        <?php if(load_model('sys/PrivilegeModel')->check_priv('fx/goods/add_goods_fx') && $response['login_type'] != 2 ) { ?>
        top.BUI.use('bui/overlay', function (Overlay) {
            top.dialog = new Overlay.Dialog({
                title: '添加分销商品',
                width: '80%',
                height: '550',
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
        <?php }else{ ?>
            BUI.Message.Alert('请先获取权限！', 'error');
        <?php } ?>
    }

    function deal_fx_goods(obj, type) {
        var select_data = {};
        select_data = top.SelectoGrid.getSelection();
        var _thisDialog = obj;
        var arr = Object.keys(select_data);
        if (arr.length == 0) {
            _thisDialog.close();
            return;
        }
        $.post("?app_act=fx/goods/add_fx_goods", {data: select_data}, function (data) {
            if (data.status < 0) {
                top.BUI.Message.Alert(data.message, 'error');
            } else {
                if (type == 1) {
                    top.skuSelectorStore.load();
                } else {
                    tableStore.load();
                    _thisDialog.close();

                }
            }
            tableStore.load();
        }, "json");
    }

    //单个指定分销商
    function add_fx_custom(index, row) {
        var goods_list = row.goods_code;
        show_select('custom', goods_list);
    }

    //单个清除
    function set_custom_money_alone(index,row) {
        set_custom_money_action(row.goods_code,0);
    }


  function set_custom_money_action(goods_code,is_goods_custom) {
      var info = '';
      if(is_goods_custom) {
          info = "确定设置为分销款？";
      } else {
          info = "清除后，分销商即无法看到此商品，请谨慎操作！";
      }
      BUI.Message.Confirm(info,function(){
          var ids = goods_code;
          var url = '?app_act=fx/goods/set_custom_money';
          var params = {};
          params.goods_list = ids;
          params.is_goods_custom = is_goods_custom;
          $.post(url, params, function (data) {
              BUI.Message.Alert(data.message, 'success');
              //刷新数据
              tableStore.load();
          }, 'json');
      },'question');
  }

    var selectPopWindowcustom_code = {
        dialog: null,
        callback: function (value) {
            var custom_code = [];
            var custom_name = [];
            $.each(value, function (i, v) {
                custom_code.push(v['custom_code']);
                custom_name.push(v['custom_name']);
            });
            $('#custom_code_select_pop').val(custom_name.join());
            $('#custom_code').val(custom_code.join());
            if (selectPopWindowcustom_code.dialog != null) {
                selectPopWindowcustom_code.dialog.close();
            }
        }
    };
    
    function remove_all_goods() {
        var obj = searchFormForm.serializeToObject();
        info = "是否一键清除商品？";
        BUI.Message.Confirm(info,function(){
            var url = '?app_act=fx/goods/remove_all_goods';
            var params = {'obj' :　obj};
            $.post(url, params, function (data) {
                BUI.Message.Alert(data.message, 'success');
                //刷新数据
                tableStore.load();
            }, 'json');
        },'question');
    }
    
    function set_all_goods_fx(obj, filter) {
//        info = "是否一键添加所有商品？";
//        BUI.Message.Confirm(info,function(){
            var url = '?app_act=fx/goods/set_all_goods_fx';
            var params = {'filter' : filter};
            $.post(url, params, function (data) {
                var _thisDialog = obj;
                _thisDialog.close();
//                BUI.Message.Alert(data.message, 'success');
                top.skuSelectorStore.load();
                //刷新数据
                tableStore.load();
            }, 'json');
//        },'question');
    }
</script>