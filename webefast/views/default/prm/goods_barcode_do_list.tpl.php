<style type="text/css">
    .well {
        min-height: 100px;
    }
    .bar-style{width:95% !important;height:20px !important;cursor: pointer;display:inline-block;}
</style>
<?php
$link[] = array('type' => 'js', 'js' => "openPage(window.btoa('?app_act=prm/goods_barcode_child/do_list'),'?app_act=prm/goods_barcode_child/do_list','商品子条码');", 'title' => '商品子条码',);
if (load_model('sys/PrivilegeModel')->check_priv('prm/goods_barcode_rule/create_barcode')) {
    $link[] = array('url' => 'prm/goods_barcode_rule/create_barcode', 'title' => '条码生成', 'is_pop' => true, 'pop_size' => '500,350');
}
render_control('PageHead', 'head1', array('title' => '商品条码管理',
    'links' => $link,
    'ref_table' => 'table'
));
?>

<?php
$keyword_type = array();
$keyword_type['barcode'] = '商品条形码';
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
);
if (load_model('sys/PrivilegeModel')->check_priv('prm/goods_barcode/export_list')) {
    $buttons[] = array(
        'label' => '导出',
        'id' => 'exprot_list',
    );
}

render_control('SearchForm', 'searchForm', array(
    'buttons' => $buttons,
    'fields' => array(
        array('label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '商品分类',
            'type' => 'select_multi',
            'id' => 'category_code',
            'data' => $response['category'],
        ),
        array(
            'label' => '商品品牌',
            'type' => 'select_multi',
            'id' => 'brand_code',
            'data' => load_model('prm/BrandModel')->get_purview_brand(),
        ),
        array(
            'label' => '商品年份',
            'type' => 'select_multi',
            'id' => 'year_code',
            'data' => ds_get_select('year'),
        ),
        array(
            'label' => '商品季节',
            'type' => 'select_multi',
            'id' => 'season_code',
            'data' => ds_get_select('season_code'),
        ),
        array(
            'label' => '国标码',
            'type' => 'input',
            'id' => 'gb_code'
        ),
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
            'label' => '是否生成条码',
            'type' => 'select_multi',
            'id' => 'is_create_barcode',
            'data' => load_model('prm/GoodsBarcodeModel')->get_create_barcode_status(),
        ),
        array(
            'label' => '商品条码备注',
            'type' => 'input',
            'id' => 'remark'
        ),
    )
));
?>

<ul class="toolbar frontool" id="tool">
    <li class="li_btns"><button class="button button-primary " onclick="do_print()">打印</button></li>
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
//$result = load_model('sys/GoodsRuleModel')->get_by_ids(array(1, 2));
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
//			array (
//                'type' => 'button',
//                'show' => 1,
//                'title' => '操作',
//                'field' => '_operate',
//                'width' => '150',
//                'align' => '',
//                'buttons' => array (
//
//                   array('id'=>'delete', 'title' => '删除', 'callback'=>'do_delete','confirm'=>'确认要删除此信息吗？'),
//
//                ),
//            ),
            array(
                'title' => '打印数量',
                'show' => 1,
                'type' => 'text',
                'width' => '100',
                'field' => 'barcode_print_num',
                'align' => '',
                'format_js' => array(
                    'type' => 'function',
                    'value' => 'barcode_print_num'
                )
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
                'title' => '商品编码',
                'field' => 'goods_code',
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
                'title' => $response['goods_spec1_rename'] . '编码',
                'field' => 'spec1_code',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品' . $response['goods_spec1_rename'],
                'field' => 'spec1_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => $response['goods_spec2_rename'] . '编码',
                'field' => 'spec2_code',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品' . $response['goods_spec2_rename'],
                'field' => 'spec2_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品条形码',
                'field' => 'barcode_html',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '系统SKU码',
                'field' => 'sku',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品国标码',
                'field' => 'gb_code',
                'width' => '150',
                'align' => '',
                'editor' => "{xtype:'text'}"
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '吊牌价',
                'field' => 'price',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品重量（克）',
                'field' => 'weight',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品条码备注',
                'field' => 'remark',
                'width' => '100',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'prm/GoodsBarcodeModel::get_by_page',
    'customFieldTable' => 'goods_barcode_do_list/table',
    'queryBy' => 'searchForm',
    'export' => array('id' => 'exprot_list', 'conf' => 'goods_barcode_list', 'name' => '商品条码', 'export_type' => 'file'),
    'idField' => 'sku_id',
    //'RowNumber'=>true,
    'CheckSelection' => true,
    'CellEditing' => $response['is_edit'],
));
?>
</div>
<script type="text/javascript">
    var new_clodop_print = '<?php echo $response['new_clodop_print']; ?>';
    var barcode_template = '<?php echo $response['barcode_template']; ?>';

    function barcode_print_num(value, row, index) {
        return "<input type = 'text' value = '1' name = 'print_num' style = 'width:50px;' id = '" + row.sku_id + "_sku_id' />";
    }


//读取已选中项
    function get_checked(isConfirm, func) {
        var ids = [];
        var selecteds = tableGrid.getSelection();
        var re = /^[1-9]\d*$/;
        for (var i in selecteds) {
            var print_num = $('#' + selecteds[i].sku_id + '_sku_id').val();
            if (print_num == '') {
                print_num = 1;
            }
            if (!re.test(print_num)) {
                continue;
            }
            ids.push(selecteds[i].sku_id + '_' + print_num);
        }

        if (ids.length == 0) {
            BUI.Message.Alert("请选择条码或者正确设置打印数量", 'error');
            return;
        }

        if (isConfirm) {
            BUI.Message.Show({
                title: '自定义提示框',
                msg: '是否执行条码打印?',
                icon: 'question',
                buttons: [
                    {
                        text: '是',
                        elCls: 'button button-primary',
                        handler: function () {
                            func.apply(null, [ids])
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
        } else {
            func.apply(null, [ids])
        }
    }
    var i = 0;
    function do_print() {
        get_checked(false, function (ids) {
            var iframe_id = 'print_express' + i;
            if (new_clodop_print == 1) {
                new ESUI.PopWindow("?app_act=prm/goods_barcode/print_barcode_clodop&new_clodop_print=" + new_clodop_print + "&record_ids=" + ids.toString() + "&frame_id=" + iframe_id + "&list_type=2", {
                    title: "条码打印",
                    width: 500,
                    height: 220,
                    onBeforeClosed: function () {
                    },
                    onClosed: function () {
                    }
                }).show()
            } else {
                if (barcode_template == 0) {
                    var u = '?app_act=sys/flash_print/do_print_td';
                    u += '&template_id=11&model=prm/GoodsBarcodeModel&typ=default&record_ids=' + ids.toString();
                    window.open(u);
                } else {
                    var url = "?app_act=sys/record_templates/print_barcode&iframe_id=" + iframe_id + "&record_ids=" + ids.toString() + "&list_type=2";
                    var iframe = $('<iframe id="' + iframe_id + ' width="0" height="0"></iframe>').appendTo('body');
                    iframe.attr('src', url);
                    i++;
                }
            }
        });
    }

    $(function () {
        if (typeof tableCellEditing != "undefined") {
            //列表区域,数量修改回调操作 +++++++++++++++++++++++++++++++++++++++++++
            tableCellEditing.on('accept', function (record, editor) {
                var param = {};
                param.sku = record.record.sku;
                param.barcode = record.record.barcode;
                param.gb_code = record.record.gb_code;
                param.is_check = 1;
                save_barcode_gb_code(param);

            });
        }
    });

    function save_barcode_gb_code(param) {
        var url = "?app_act=prm/goods_barcode/edit_barcode_gb_code&app_fmt=json";
        $.post(url, param, function (ret) {
            if (ret.status == -2) {
                BUI.Message.Confirm(ret.message, function () {
                    param.is_check = 0;
                    save_barcode_gb_code(param);
                }, 'question');
            } else if (ret.status == 0) {
            } else {
                BUI.Message.Tip(ret.message, 'success');
            }
        }, 'json');
    }

    function changeElement(_this) {
        var barcode = $(_this).text();
        var sku = $(_this).attr('sku');
        var gb_code = $(_this).attr('gb_code');
        var input_html = '<input type="text" id="barcode_edit" class="bar-style" onblur="updateBarcode(this)" sku="' + sku + '" gb_code="' + gb_code + '" value="" />';
        $(_this).parent().html(input_html);
        $("#barcode_edit").focus();
        $("#barcode_edit").val(barcode);
        $("#barcode_edit").keydown(function (e) {
            if (e.keyCode == 13) {
                updateBarcode(this);
            }
        });
    }

    function updateBarcode(_this) {
        var param = {};
        param.barcode = $(_this).val();
        param.sku = $(_this).attr('sku');
        param.gb_code = $(_this).attr('gb_code');
        param.is_check = 1;
        save_barcode_gb_code(param);

        var input_html = '<label onclick="changeElement(this)" class="bar-style" sku="' + param.sku + '" gb_code="' + param.gb_code + '">' + htmlEncode(param.barcode) + '</label>';
        $(_this).parent().html(input_html);
    }

    function htmlEncode(html) {
        var temp = document.createElement("div");
        (temp.textContent != null) ? (temp.textContent = html) : (temp.innerText = html);
        var output = temp.innerHTML;
        temp = null;
        return output;
    }

    function htmlDecode(text) {
        var temp = document.createElement("div");
        temp.innerHTML = text;
        var output = temp.innerText || temp.textContent;
        temp = null;
        return output;
    }
</script>



