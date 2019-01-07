<style>
    .panel-body{ padding:0;}
    .table{ margin-bottom:0;}
    .table tr{ padding:5px 0;}
    .table th, .table td{ border:1px solid #dddddd; padding:3px 0; vertical-align:middle;}
    .table th{ width:15.3%; text-align:center;}
    .table td{ width:30%; padding:0 1%;}
    .row{ margin-left:0; padding: 2px 8px; border: 1px solid #ddd;}
    .bui-grid-header{ border-top:none;}
    p{ margin:0;}
    b{ vertical-align:middle;}
    .panel{ border:1px solid #dddddd;}
    .bui-grid-row-odd{width: 87%;}
    #no_batch_pager{display:none;}
</style>
<?php echo load_js("baison.js,record_table.js", true); ?>
<?php
render_control('PageHead', 'head1', array('title' => '订单运费核销明细',
    'ref_table' => 'table'
));
?>

<script>
    var data = [
        {
            "name": "dz_code",
            "title": "对账编号",
            "value": "<?php echo $response['data']['dz_code'] ?>",
            "type": "input"

        },
        {
            "name": "dz_month",
            "title": "对账月份",
            "value": "<?php echo $response['data']['dz_month'] ?>",
            "type": "time"
        },
        {
            "name": "store_name",
            "title": "仓库",
            "value": "<?php echo $response['data']['store_name'] ?>"
        },
        {
            "name": "create_time",
            "title": "创建时间",
            "value": "<?php echo $response['data']['create_time'] ?>"
        }
    ];

    $(function () {
        var rt = new record_table();
        rt.init({
            "id": "panel_html",
            "td_num": "2",
            "data": data,
            "is_edit": false,
            "load_url": "",
            "load_callback": function () {
                logStore.load();
            }
        });
    });
</script>

<div class="panel record_table" id="panel_html">
</div>

<div class="panel">
    <div class="panel-header">
        <h3 class="express-cost">快递运费汇总（系统运费合计：<span style="color:red"><?php echo $response['detail']['sum_cost']; ?></span>）<i class="icon-folder-open toggle"></i></h3>
    </div>
    <?php
    render_control('DataTable', 'no_batch', array(
        'conf' => array(
            'list' => array(
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '配送方式',
                    'field' => 'express_name',
                    'width' => '250',
                    'align' => 'center',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '所属快递公司',
                    'field' => 'company_name',
                    'width' => '250',
                    'align' => 'center',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '系统运费合计',
                    'field' => 'weigh_express_money',
                    'width' => '250',
                    'align' => 'center',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '运费占比',
                    'field' => 'express_percent',
                    'width' => '370',
                    'align' => 'center',
                ),
            )
        ),
        'dataset' => 'acc/OrderExpressDzDetailModel::get_by_page',
        'idField' => 'detail_dz_id',
        'params' => array('filter' => array('dz_code' => $response['data']['dz_code'])),
        'CascadeTable' => array(
            'list' => array(
                array('title' => '省份', 'width' => '300', 'field' => 'province_name'),
                array('title' => '运费', 'width' => '300', 'field' => 'money'),
            ),
            'page_size' => 50,
            'url' => get_app_url('acc/order_express_detail/get_express_by_dz_code&app_fmt=json&dz_code=' . $response['data']['dz_code']),
            'params' => 'express_code',
        ),
    ));
    ?>
</div>
<div class="panel">
    <div class="panel-body">
        <div class="row">
            <select id="express_code">
                <option value=''>请选择配送方式</option>
                <?php
                foreach ($response['detail']['express'] as $key => $value) {
                    echo "<option value='{$key}'>{$value}</option>";
                }
                ?>
            </select>
            <input type="text" class="input" value="" placeholder="订单号/快递单号"  id="codes"  aria-disabled="false" aria-pressed="false"/>
            <select id="hx_status">
                <option value=''>请选择核销状态</option>
                <option value='0'>未核销</option>
                <option value='1'>已核销</option>
            </select>
            <select id="province" style="width: 120px">
                <option value=''>请选择省份</option>
                <?php
                foreach ($response['detail']['province'] as $key => $value) {
                    echo "<option value='{$key}'>{$value}</option>";
                }
                ?>
            </select>
            <button type="button" class="button button-success" value="查询" id="btnSearch"> 查询</button> &nbsp;
            <button type="button" class="button button-success" value="" id="btnRefresh" >称重数据刷新</button> &nbsp;
            <button type="button" class="button button-success" value="新增商品" id="btnimport">导入快递运费（标准模板）</button>
        </div>
    </div>
    <?php
    render_control('DataTable', 'batch', array('conf' => array(
            'list' => array(
                array(
                    'type' => 'button',
                    'show' => 1,
                    'title' => '操作',
                    'field' => '_operate',
                    'width' => '75',
                    'align' => 'center',
                    'buttons' => array(
                        array(
                            'id' => 'sd_hx',
                            'title' => '手动核销',
                            'callback' => 'do_hx',
                            'show_cond' => 'obj.hx_status != 1'
                        ),
                    ),
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '核销状态',
                    'field' => 'hx_status_type',
                    'width' => '80',
                    'align' => 'center',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '配送方式',
                    'field' => 'express_name',
                    'width' => '115',
                    'align' => 'center',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '快递单号',
                    'field' => 'express_no',
                    'width' => '160',
                    'align' => 'center',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '系统运费',
                    'field' => 'weigh_express_money',
                    'width' => '70',
                    'align' => 'center',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '重量',
                    'field' => 'real_weigh',
                    'width' => '70',
                    'align' => 'center',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '快递运费',
                    'field' => 'express_money',
                    'width' => '70',
                    'align' => 'center',
                    'format_js' => array(
                        'type' => 'html',
                        'value' => '<span title="快递公司提供的运费数据">{express_money}</span>',
                    ),
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '仓库',
                    'field' => 'store_name',
                    'width' => '95',
                    'align' => 'center',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '订单编号',
                    'field' => 'sell_record_code',
                    'width' => '120',
                    'align' => 'center',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '省份',
                    'field' => 'province_name',
                    'width' => '70',
                    'align' => 'center',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '城市',
                    'field' => 'city_name',
                    'width' => '70',
                    'align' => 'center',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '地区',
                    'field' => 'district_name',
                    'width' => '80',
                    'align' => 'center',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '街道',
                    'field' => 'street_name',
                    'width' => '100',
                    'align' => 'center',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '详细地址',
                    'field' => 'receiver_addr',
                    'width' => '180',
                    'align' => 'center',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '发货时间',
                    'field' => 'delivery_time',
                    'width' => '150',
                    'align' => 'center',
                ),
            )
        ),
        'dataset' => 'acc/OrderExpressDzDetailModel::get_detail_by_page',
        'idField' => 'detail_dz_id',
        'params' => array('filter' => array('store_code' => $response['data']['store_code'], 'dz_month' => $response['data']['dz_month'], 'dz_code' => $response['data']['dz_code'])),
        'init' => 'nodata'
        )
    );
    ?>
</div>

<ul id="tool" class="toolbar frontool frontool_center">
    <li class="li_btns">
        <button type="button" class="button button-info" style="background-color: #1695ca;"  onclick="export_excel()"  value="导出" id="btn-csv">导出</button>
    </li>
    <div class="front_close">&lt;</div>
</ul>

<script>
    var dz_code = '<?php echo $response['data']['dz_code'] ?>';
    var dz_month = '<?php echo $response['data']['dz_month'] ?>';
    var store_code = '<?php echo $response['data']['store_code'] ?>';
    $(function () {
        tools();

        //查询
        $('#btnSearch').click(function () {
            batchStore.load({
                'code_name': $('#codes').val(),
                'express_code': $('#express_code').val(),
                'hx_status': $('#hx_status').val(),
                'receiver_province': $('#province').val()
            });
            $(".nodata").hide();
        });

        //称重数据刷新
        $('#btnRefresh').on('click', function () {
            BUI.Message.Show({
                title: '数据刷新',
                msg: '刷新数据将导致原核销状态重置，您确定刷新称重数据吗?',
                icon: 'question',
                buttons: [
                    {
                        text: '确定',
                        elCls: 'button button-primary',
                        handler: function () {
                            $.ajax({
                                type: 'POST',
                                dataType: 'json',
                                url: '<?php echo get_app_url('acc/order_express_detail/refesh_record'); ?>',
                                data: {dz_code: dz_code, dz_month: dz_month, store_code: store_code},
                                success: function (ret) {
                                    if (ret.status == 1) {
                                        window.location.reload();
                                    } else {
                                        BUI.Message.Alert(ret.message, 'error');
                                    }
                                }
                            });
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
        });
    });

    //导入标准模板
    $('#btnimport').click(function () {
        var url = "?app_act=acc/order_express_detail/import&dz_code=" + '<?php echo $response['data']['dz_code'] ?>';
        new ESUI.PopWindow(url, {
            title: "导入快递运费数据",
            width: 480,
            height: 400,
            onBeforeClosed: function () {
                location.reload();
            },
            onClosed: function () {

            }
        }).show();
    });

    //导出为excel
    function export_excel() {
        var param = "";
        var code_name = $('#codes').val();
        var express_code = $('#express_code').val();
        var hx_status = $('#hx_status').val();
        var dz_code = '<?php echo $response['data']['dz_code']; ?>';
        var receiver_province = $('#province').val();
        param = param + "&code_name=" + code_name + "&express_code=" + express_code + "&hx_status=" + hx_status + "&dz_code=" + dz_code + "&receiver_province="+receiver_province+"&type=export&app_fmt=json";
        var url = "?app_act=acc/order_express_detail/export_csv_list" + param;
        window.location.href = url;
    }

    //手动核销
    function do_hx(_index, row) {
        new ESUI.PopWindow("?app_act=acc/order_express_detail/do_hx&detail_dz_id=" + row.detail_dz_id, {
            title: "手动核销",
            width: 600,
            height: 250,
            onBeforeClosed: function () {
                location.reload();
            },
            onClosed: function () {
                location.reload();
            }
        }).show();
    }

    function tools() {
        $(".frontool").animate({left: '0px'}, 1000);
        $(".front_close").click(function () {
            if ($(this).html() === "&lt;") {
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

</script>