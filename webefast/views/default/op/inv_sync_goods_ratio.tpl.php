<style>
    .goods_ratio{margin:20px;}
    .goods_ratio .row input[type='text']{width: 175px;}
    .goods_ratio .row:first-child{ margin-bottom:10px;}
    .goods_ratio select{width: 100px;}
</style>
<div class="goods_ratio">
    <div class="row">
        <form id="goods_form">
            <select class="input-small" id="category">
                <option value="">所有分类</option>
                <?php
                foreach ($response['category'] as $category) {
                    echo "<option value='" . $category['category_code'] . "'>" . $category['category_name'] . "</option>";
                }
                ?>
            </select>
            <select class="input-small" id="brand">
                <option value="">所有品牌</option>
                <?php
                foreach ($response['brand'] as $brand_code => $brand_name) {
                    echo "<option value='$brand_code'>$brand_name</option>";
                }
                ?>
            </select>
            <select class="input-small" id="year">
                <option value="">所有年份</option>
                <?php
                foreach ($response['year'] as $category) {
                    echo "<option value='" . $category['year_code'] . "'>" . $category['year_name'] . "</option>";
                }
                ?>
            </select>
            <select class="input-small" id="season">
                <option value="">所有季节</option>
                <?php
                foreach ($response['season'] as $category) {
                    echo "<option value='" . $category['season_code'] . "'>" . $category['season_name'] . "</option>";
                }
                ?>
            </select>&nbsp;&nbsp;
            <input type="text" placeholder="商品编码/商品名称/商品条形码" class="code_name" id="code_name"/>&nbsp;&nbsp;
            <select class="input-small" id="setted_sync_ratio">
                <option value="">同步设置</option>
                <option value=1>是</option>
                <option value=0>否</option>
            </select>&nbsp;&nbsp;
            <select class="input-small" id="warn_sku_status">
                <option value="">预警设置</option>
                <option value=1>是</option>
                <option value=0>否</option>
            </select>&nbsp;&nbsp;
            <!--<label class="checkbox"><input type="checkbox" name="setted_sync_ratio" id="setted_sync_ratio">已设置同步比例</label>&nbsp;&nbsp;-->
            <button type="button" class="button button-info" value="搜索" id="btnSearchGoods"><i class="icon-search icon-white"></i> 搜索</button>
            <button type="button" class="button button-info" id="resetSearch">重置搜索</button>
        </form>
    </div>
    <div class="row">
        <ul class="toolbar">
            <li><button class="button button-primary" id="btn_opt_one_set_ratio">一键设置同步比例</button></li>
            <li><button class="button button-primary" id="btn_opt_one_del_ratio">一键清除比例及预警设置</button></li>
            <li><button class="button button-primary" id="btn_opt_batch_del_ratio">批量清除比例及预警设置</button></li>
            <li><button class="button button-primary" id="btn_opt_batch_ratio">批量设置同步比例</button></li>
            <li><button class="button button-primary" id="btn_opt_import_ratio">Excel导入</button></li>
            <li><button class="button button-primary" id="btn_opt_export_ratio">Excel导出</button></li>
        </ul>
    </div>

    <?php
    $list = array(
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '商品编码',
            'field' => 'goods_code',
            'width' => '12%',
            'align' => '',
        ),
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '商品名称',
            'field' => 'goods_name',
            'width' => '19%',
            'align' => '',
        ),
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '商品条形码',
            'field' => 'barcode',
            'width' => '19%',
            'align' => '',
        ), array(
            'type' => 'text',
            'show' => 1,
            'title' => $response['spec']['goods_spec1'],
            'field' => 'spec1_name',
            'width' => '9%',
            'align' => '',
        ), array(
            'type' => 'text',
            'show' => 1,
            'title' => $response['spec']['goods_spec2'],
            'field' => 'spec2_name',
            'width' => '9%',
            'align' => '',
        ),
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '分类',
            'field' => 'category_name',
            'width' => '9%',
            'align' => '',
        ),
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '品牌',
            'field' => 'brand_name',
            'width' => '10%',
            'align' => '',
        ),
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '年份',
            'field' => 'year_name',
            'width' => '7%',
            'align' => ''
        ),
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '季节',
            'field' => 'season_name',
            'width' => '7%',
            'align' => ''
        ),
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '同步设置',
            'field' => 'setted_ratio',
            'width' => '10%',
            'align' => '',
        ),
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '设置预警库存',
            'field' => 'warn_sku_name',
            'width' => '10%',
            'align' => '',
        ),
    );
    if ($app['scene'] != 'view') {
        $list[] = array(
            'type' => 'button',
            'show' => 1,
            'title' => '操作',
            'field' => '_operate',
            'width' => '10%',
            'align' => '',
            'buttons' => array(
                array('id' => 'edit', 'title' => '同步设置', 'callback' => 'set_goods_ratio', 'show_name' => '设置同步比例',),
                array('id' => 'warn_edit', 'title' => '预警设置', 'callback' => 'set_warn_sku', 'show_name' => '预警设置', 'show_cond' => "obj.anti_oversold != 0"),
                array('id' => 'delete', 'title' => '清除', 'callback' => 'do_delete', 'show_cond' => "(obj.sync_ratio != null || obj.anti_oversold_status==1)"),
            ),
        );
    } else {
        $list[] = array(
            'type' => 'button',
            'show' => 1,
            'title' => '操作',
            'field' => '_operate',
            'width' => '10%',
            'align' => '',
            'buttons' => array(
                array('id' => 'detail', 'title' => '同步查看', 'callback' => 'set_goods_ratio',
//                    'show_cond' => "obj.status == 1"
                ),
                array('id' => 'warn_edit', 'title' => '预警查看', 'callback' => 'set_warn_sku', 'show_name' => '预警设置', 'show_cond' => "obj.anti_oversold != 0"),
            ),
        );
    }
    render_control('DataTable', 'table_goods', array(
        'conf' => array(
            'list' => $list
        ),
        'dataset' => 'op/InvSyncRatioModel::get_goods_by_page',
        'idField' => 'sku_id',
        'CheckSelection' => true,
        'params' => array('filter' => array('sync_code' => $request['sync_code'])),
        'init' => 'nodata',
    ));
    ?>
</div>
<script>
    var sync_code = '<?php echo $request['sync_code']; ?>';
    var sync_mode = '<?php echo $response['goods_ratio']['sync_mode']; ?>';
    var opts = ['one_set_ratio', 'one_del_ratio', 'batch_ratio', 'import_ratio', 'export_ratio', 'batch_del_ratio'];

    $(function () {
        btn_init();
    });

    $('#btnSearchGoods').on('click', function () {
        reload_goods_info();
    });
    $('#resetSearch').on('click', function () {
        document.getElementById("goods_form").reset();
        reload_goods_info();
    });

    function do_delete(_index, row) {
        var params = {'sync_code': sync_code, 'sku': row.sku};
        BUI.Message.Confirm('确认要清除同步比例及防超卖预警配置吗？', function () {
            $.post('?app_act=op/inv_sync/delete_goods_ratio', params, function (result) {
                if (result.status == 1) {
                    BUI.Message.Tip('清除成功', 'success');
                    reload_goods_info();
                } else {
                    BUI.Message.Tip('清除失败！', 'error');
                }
            }, 'json');
        }, 'question');
    }

    function set_goods_ratio(_index, row) {
        var sku = row.sku;
        url = "?app_act=op/inv_sync/shop_ratio&sync_code=" + sync_code + "&sku=" + sku + "&set_type=set&app_scene=<?php echo $app['scene'] ?>";
        new ESUI.PopWindow(url, {
            title: "设置商品同步比例",
            width: '700',
            height: sync_mode == 1 ? '400' : '500',
            buttons: [{
                    text: '关闭',
                    elCls: 'button button-primary',
                    handler: function () {
                        this.close();
                    }
                }
            ],
            onBeforeClosed: function () {
                reload_goods_info();
            },
            onClosed: function () {
                //刷新数据
            }
        }).show();
    }

    //预警设置
    function set_warn_sku(_index, row) {
        var sku = row.sku;
        url = "?app_act=op/inv_sync/sku_ratio&sync_code=" + sync_code + "&sku=" + sku + "&app_scene=<?php echo $app['scene'] ?>";
        new ESUI.PopWindow(url, {
            title: "条码预警设置",
            width: '700',
            height: sync_mode == 1 ? '400' : '500',
            buttons: [{
                    text: '关闭',
                    elCls: 'button button-primary',
                    handler: function () {
                        this.close();
                    }
                }
            ],
            onBeforeClosed: function () {
                reload_goods_info();
            },
            onClosed: function () {
                //刷新数据
            }
        }).show();
    }


    function reload_goods_info() {
        var category_code = $('#category').val();
        var brand_code = $('#brand').val();
        var year_code = $('#year').val();
        var season_code = $('#season').val();
        // var setted_sync_ratio = $("#setted_sync_ratio").attr("checked") ? 1 : 0;
        var setted_sync_ratio = $("#setted_sync_ratio").val();
        var warn_sku_status = $("#warn_sku_status").val();
        var code_name = $("#code_name").val();
        table_goodsStore.load({'code_name': code_name, 'category_code': category_code, 'brand_code': brand_code, 'year_code': year_code, 'season_code': season_code, 'setted_sync_ratio': setted_sync_ratio, 'warn_sku_status': warn_sku_status});
        clear_nodata();
    }

    function clear_nodata() {
        if ($('.nodata').length > 0) {
            $('.nodata').remove();
        }
    }

    //初始化按钮
    function btn_init() {
        //操作按钮
        for (var i in opts) {
            var f = opts[i];
            if (scene == 'view' && f != 'export_ratio') {
                $('#btn_opt_' + f).parent().remove();
                continue;
            }
            switch (f) {
                case "one_set_ratio":
                    btn_opt_one_set_ratio();
                    break;
                case "one_del_ratio":
                    btn_opt_one_del_ratio();
                    break;
                case "batch_ratio":
                    btn_opt_batch_ratio();
                    break;
                case "import_ratio":
                    btn_opt_import_ratio();
                    break;
                case "export_ratio":
                    btn_opt_export_ratio();
                    break;
                case "batch_del_ratio":
                    btn_opt_batch_del_ratio();
                    break;
                default:
                    break;
            }
        }
    }

    //一键设置同步比例
    function btn_opt_one_set_ratio() {
        $('#btn_opt_one_set_ratio').click(function () {
            var select_wh = {
                'code_name': $("#code_name").val(),
                'category_code': $('#category').val(),
                'brand_code': $('#brand').val(),
                'year_code': $('#year').val(),
                'season_code': $('#season').val(),
                'setted_sync_ratio': $("#setted_sync_ratio").val(),
                'sync_code': sync_code,
                'ctl_type': 'set_ratio'
            };
            var params = new Array();
            params.select_wh = select_wh;
            if (typeof (top.dialog) != 'undefined') {
                top.dialog.remove(true);
            }
            url = '?app_act=op/inv_sync/shop_ratio&set_type=one_set&sync_code=' + sync_code;

            top.BUI.use('bui/overlay', function (Overlay) {
                top.dialog = new Overlay.Dialog({
                    title: "一键设置商品同步比例（<span style='color:red;'>默认加载店铺同步比例</span>）",
                    width: '700',
                    height: sync_mode == 1 ? '400' : '500',
                    loader: {
                        url: url,
                        autoLoad: true, //不自动加载
                        params: params, //附加的参数
                        lazyLoad: false, //不延迟加载
                        dataType: 'text'   //加载的数据类型
                    },
                    align: {
                        //node : '#t1',//对齐的节点
                        points: ['tc', 'tc'], //对齐参考：http://dxq613.github.io/#positon
                        offset: [0, 20] //偏移
                    },
                    mask: true,
                    buttons: [
                        {
                            text: '关闭',
                            elCls: 'button button-primary',
                            handler: function () {
                                this.close();
                                reload_goods_info();
                            }
                        }
                    ]
                });
                top.dialog.on('closed', function (ev) {
                    reload_goods_info();
                });
                top.dialog.show();
            });
        });
    }

    //一键清除同步比例
    function btn_opt_one_del_ratio() {
        $('#btn_opt_one_del_ratio').click(function () {
            var params = {
                'code_name': $("#code_name").val(),
                'category_code': $('#category').val(),
                'brand_code': $('#brand').val(),
                'year_code': $('#year').val(),
                'season_code': $('#season').val(),
                'setted_sync_ratio': $("#setted_sync_ratio").val(),
                'sync_code': sync_code,
                'ctl_type': 'set_ratio',
                'set_type': 'one_del'
            };
            BUI.Message.Confirm('确认要清除列表商品同步比例吗？', function () {
                $.ajax({
                    url: '?app_act=op/inv_sync/delete_goods_ratio',
                    type: 'POST',
                    data: params,
                    dataType: 'json',
                    success: function (data) {
                        if (data.status == 1) {
                            BUI.Message.Tip(data.message, 'success');
                            reload_goods_info();
                        } else {
                            BUI.Message.Tip(data.message, 'error');
                        }
                    },
                    error: function () {
                        return;
                    }
                });
            }, 'question');

        });
    }

    //批量设置同步比例
    function btn_opt_batch_ratio() {
        $('#btn_opt_batch_ratio').click(function () {
            get_checked($(this), function (ids) {
                url = '?app_act=op/inv_sync/shop_ratio&set_type=batch_set&sync_code=' + sync_code + '&sku=' + ids;
                new ESUI.PopWindow(url, {
                    title: "批量设置商品同步比例（<span style='color:red;'>默认加载店铺同步比例</span>）",
                    width: '700',
                    height: sync_mode == 1 ? '400' : '500',
                    buttons: [{
                            text: '关闭',
                            elCls: 'button button-primary',
                            handler: function () {
                                this.close();
                            }
                        }
                    ],
                    onBeforeClosed: function () {
                        reload_goods_info();
                    },
                    onClosed: function () {
                        //刷新数据
                    }
                }).show();
            });
        });
    }

    //批量清除同步比例
    function btn_opt_batch_del_ratio() {
        $('#btn_opt_batch_del_ratio').click(function () {
            get_checked($(this), function (ids) {
                var url = '?app_act=op/inv_sync/delete_goods_ratio';
                var params = {"sync_code": sync_code, "sku": ids, "set_type": 'batch_del'};
                BUI.Message.Confirm('确认要清除选中商品同步比例吗？', function () {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: params,
                        dataType: 'json',
                        success: function (data) {
                            if (data.status == 1) {
                                BUI.Message.Tip(data.message, 'success');
                                reload_goods_info();
                            } else {
                                BUI.Message.Tip(data.message, 'error');
                            }
                        },
                        error: function () {
                            return;
                        }
                    });
                }, 'question');
            });
        });
    }


    //导入商品同步比例
    function btn_opt_import_ratio() {
        $('#btn_opt_import_ratio').click(function () {
            url = '?app_act=op/inv_sync/import_goods_ratio&sync_code=' + sync_code + '&sync_mode=' + sync_mode;
            new ESUI.PopWindow(url, {
                title: "导入商品比例",
                width: 500,
                height: 320,
                buttons: [{
                        text: '关闭',
                        elCls: 'button button-primary',
                        handler: function () {
                            this.close();
                        }
                    }
                ],
                onBeforeClosed: function () {
                    table_goodsStore.load();
                },
                onClosed: function () {
                    //刷新数据
                }
            }).show();
        });
    }

    //导出商品同步比例
    function btn_opt_export_ratio() {
        $('#btn_opt_export_ratio').click(function () {
            // var url = '?app_act=ctl/index/do_index&app_ctl=DataTable/do_get_data';
            var url = '?app_act=sys/export_csv/export_show'; //暂时不是框架级别
            var params = table_goodsStore.get('params');
            params.ctl_dataset = "op/InvSyncRatioModel::get_goods_by_page";

            if (sync_mode == 1) {
                params.ctl_export_conf = 'inv_sync_goods_globe_ratio_list';
            } else if (sync_mode == 2) {
                params.ctl_export_conf = 'inv_sync_goods_store_ratio_list';
            }
            params.ctl_type = 'export';
            params.ctl_export_name = '商品比例配置列表';
            params.sync_mode = sync_mode;
            params.setted_sync_ratio = 1;
            params.code_name = $("#code_name").val();
            params.category_code = $('#category').val();
            params.brand_code = $('#brand').val();
            params.year_code = $('#year').val();
            params.season_code = $('#season').val();
            //   params.warn_sku_status = $('#warn_sku_status').val();
<?php echo create_export_token_js('op/InvSyncRatioModel::get_goods_by_page'); ?>

            for (var key in params) {
                url += "&" + key + "=" + params[key];
            }

            window.open(url);
        });
    }

    //读取已选中项
    function get_checked(obj, func) {
        var ids = new Array();
        var rows = table_goodsGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Tip("请选择商品", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.sku);
        }
        ids.join(',');
        func.apply(null, [ids]);
    }
</script>