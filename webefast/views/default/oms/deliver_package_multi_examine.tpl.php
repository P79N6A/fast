<style>
    .toolbar{margin: 50px 0px 0px 50px;}
    .toolbar .control-group label{line-height: 26px;vertical-align: middle;margin-right: 5px;}
    .toolbar .button-group .active{color:#FFF;background-color: #7EC0EE;border-color:#7EC0EE;}
    .well{border-radius:0;padding: 15px 20px;}
    .well .scan-cricle{height:40px !important;font-weight: lighter;font-size:2.2em;background-color: #f3fdfe;padding: 2px 15px;}
    .well .opt-button{margin-left: -2%;width: 39% !important;}
    .well .opt-button button{line-height: 40px;width: 23%;margin-right: 1.2%;font-size: 1.3em;padding: 2px;}

    .well .scan-total .control-group:not(:last-child){width: 13%;}
    .well .scan-total .control-group:last-child{width: 27%;text-align: center;}
    .well .scan-total label{font-size: 1.3em;}
    .well .scan-total span{font-size: 1.1em;margin-top: 1px;}
    .well .scan-total #error_msg{font-size: 1.3em;color: red;}
    .well .curr-scan-total .control-group{width: 21% !important;}

    .custom-dialog .bui-stdmod-header{}
    .custom-dialog .bui-stdmod-footer{display: none;}

    #table_datatable .bui-grid-body{overflow-x:hidden}
</style>

<?php
render_control('PageHead', 'head1', array('title' => '多包裹验货（后置打单）',
    'links' => array(),
    'ref_table' => 'table'
));
?>

<bgsound loop="false" autostart="false" id="bgsound_ie" />
<audio controls="controls" id="bgsound_others" style="display:none;" src=""></audio>

<div class="toolbar">
    <div class="row">
        <div class="control-group span8">
            <label class="control-label">预设置包裹数</label>
            <div class="button-group" id="pre_set_num">
            </div>
        </div>
        <div class="control-group span8">
            <label class="control-label">自动打印物流单</label>
            <div class="button-group" id="auto_print_express"></div>
        </div>
        <div class="control-group span9">
            <label class="control-label">校验物流单是否重复</label>
            <div class="button-group" id="verify_express"></div>
        </div>
    </div>
</div>

<div class="well form-horizontal">
    <div class="row">
        <div class="control-group">
            <input type="text" class="control-text scan-cricle" id="sell_record_code" style="width:70% !important" placeholder="请扫描订单号">
        </div>
        <div class="control-group" style="margin-left:-6%;">
            <input type="text" class="control-text scan-cricle" id="barcode" disabled="disabled" style="width: 80% !important;" placeholder="请扫描商品条形码">
        </div>
        <div class="control-group opt-button">
            <button type="button" class="button" id="packet_print" disabled="disabled">封包打印</button>
            <button type="button" class="button" id="delivery" disabled="disabled">订单发货</button>
            <button type="button" class="button" id="scan_clear" disabled="disabled">重置扫描</button>
            <button type="button" class="button" id="show_all" disabled="disabled">显示全部</button>
        </div>
    </div>
    <hr style="margin-top:15px;margin-left:-20px;border:1px #FFF dotted;">
    <div class="row scan-total">
        <div class="control-group">
            <label>商品总数：</label>
            <span id="goods_num" class="badge badge-info">0</span>
        </div>
        <div class="control-group">
            <label>包裹总数：</label>
            <span id="package_num" class="badge badge-info">0</span>
        </div>
        <div class="control-group">
            <label>扫描总数：</label>
            <span id="scan_num" class="badge badge-success">0</span>
        </div>
        <div class="control-group">
            <label>差异总数：</label>
            <span id="diff_num" class="badge badge-error">0</span>
        </div>       
        <div class="control-group">
            <label>预扫描包裹：</label>
            <span id="curr_package_no" class="badge">0</span>
        </div>
        <div class="control-group">
            <span id="error_msg" style="letter-spacing:1px;"></span>
        </div>
    </div>
    <div class="row scan-total curr-scan-total" >

        <!--        <div class="control-group">
                    <label>当前商品总数：</label>
                    <span id="curr_goods_num" class="badge badge-info">0</span>
                </div>
                <div class="control-group">
                    <label>当前商品扫描数：</label>
                    <span id="curr_scan_num" class="badge badge-success">0</span>
                </div>
                <div class="control-group">
                    <label>当前商品差异数：</label>
                    <span id="curr_diff_num" class="badge badge-error">0</span>
                </div>-->
    </div>
</div>
<div>
    <?php
    render_control('DataTable', 'table', array(
        'conf' => array(
            'list' => array(
                array(
                    'type' => 'button',
                    'show' => 1,
                    'title' => '操作',
                    'field' => '_operate',
                    'width' => '5%',
                    'align' => 'center',
                    'buttons' => array(
                        array('id' => 'packet', 'title' => '封包', 'callback' => 'packet_package', 'show_cond' => 'obj.is_allow_delete==0 && obj.packet_status==0', 'priv' => ''),
                        array('id' => 'print', 'title' => '打印', 'callback' => 'print_express', 'show_cond' => 'obj.packet_status==1 && obj.print_status==0', 'priv' => ''),
                        array('id' => 'delete', 'title' => '删除', 'callback' => 'delete_package', 'show_cond' => 'obj.is_allow_delete==1', 'priv' => '')
                    ),
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '包裹序号',
                    'field' => 'package_no',
                    'width' => '10%',
                    'align' => 'center',
                ),
//                array(
//                    'type' => 'text',
//                    'show' => 1,
//                    'title' => '商品数量',
//                    'field' => 'goods_num',
//                    'width' => '10%',
//                    'align' => 'center'
//                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '扫描数量',
                    'field' => 'scan_num',
                    'width' => '10%',
                    'align' => 'center'
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '配送方式',
                    'field' => 'express_name',
                    'width' => '18%',
                    'align' => 'center'
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '快递单号',
                    'field' => 'express_no',
                    'width' => '18%',
                    'align' => 'center',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '包裹打印状态',
                    'field' => 'print_status_txt',
                    'width' => '10%',
                    'align' => 'center',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '包裹打印时间',
                    'field' => 'print_time',
                    'width' => '15%',
                    'align' => 'center',
                ),
            )
        ),
        'dataset' => 'oms/DeliverPackageModel::get_package_by_page',
        'idField' => 'package_record_id',
        'init' => 'nodata',
        'init_note_nodata' => '扫描订单号后加载数据',
        'CascadeTable' => array(
            'list' => array(
                array(),
                array('title' => '商品名称', 'type' => 'text', 'width' => '180', 'field' => 'goods_name'),
                array('title' => '商品编码', 'type' => 'text', 'width' => '150', 'field' => 'goods_code'),
                array('title' => '商品条形码', 'type' => 'text', 'width' => '180', 'field' => 'barcode'),
                array('title' => '商品规格', 'type' => 'text', 'width' => '180', 'field' => 'spec'),
//                array('title' => '商品数量', 'type' => 'text', 'width' => '150', 'align' => 'center', 'field' => 'goods_num'),
                array('title' => '扫描数量', 'type' => 'text', 'width' => '150', 'align' => 'center', 'field' => 'scan_num'),
            ),
            'page_size' => 50,
            'url' => get_app_url("oms/deliver_package/get_package_detail_by_page"),
            'params' => 'package_record_id,sell_record_code,package_no',
        ),
    ));
    ?>
</div>
<div id="set_package_num" style="visibility: hidden">
    <div class="row">
        <div class="control-group span10" style="margin-left: 20px;font-size:1.2em">
            <label>订单号：</label><span id="package_sell_code"></span>
            <label style="margin-left: 20px;">商品总数：</label><span id="package_goods_num"></span>
            <p style="margin-top: 10px;">设置包裹数：<input type="text" id="package_total" style="width: 50px;"/>，获取电子面单号</p>
        </div>
    </div>
    <div class="clearfix" style="text-align: center;margin-top: 10px;">
        <button class="button button-primary" id="btn_waybill_get">确定</button>
    </div>
</div>
<script>
    /*----注意：页面数据处理、操作均依赖record包中的数据，修改时请理清数据处理逻辑----*/

    var sounds = {
        "error": "<?php echo $response['sound']['error'] ?>",
        "success": "<?php echo $response['sound']['success'] ?>"
    };
    var pre_set_num, auto_print_express, verify_express;
    var record, set_dialog;
    /*--------扫描订单号----BEGIN----*/
    $("#sell_record_code").keyup(function (event) {
        if (event.keyCode == 13) {
            setErrMessage('');
            var sell_record_code = $(this).val().trim();
            if (sell_record_code == '') {
                play_sound('error');
                setErrMessage('请扫描订单号');
                return false;
            }
            $.post("?app_act=oms/deliver_package/check_scan_record", {sell_record_code: sell_record_code}, function (ret) {
                if (ret.status != 1) {
                    play_sound('error');
                    setErrMessage(ret.message);
                    return false;
                }
                play_sound('success');
                record = ret.data;
                if (record.package_num != 0 || (pre_set_num == 0 && record.package_num == 0)) {
                    setRecordData();
                    changeElementStatus('s_scan');
                    loadPackageData('all');
//                    $("#table_datatable .bui-grid-cascade-icon").trigger('click');
                    return false;
                }
                if (pre_set_num == 1) {
                    setPackageInfo();
                    set_dialog.show();
                    $("#package_total").focus();
                }
            }, "json");
        }
    });
    /*--------扫描订单号----END----*/

    /*--------预置包裹数----BEGIN----*/
    BUI.use(['bui/overlay', 'bui/form'], function (Overlay, Form) {
        set_dialog = new Overlay.Dialog({
            title: '包裹数设置',
            width: 380,
            height: 220,
            elCls: 'custom-dialog',
            contentId: 'set_package_num'
        });
        $("#package_total").keyup(function (event) {
            if (event.keyCode == 13) {
                get_waybill_multi();
            }
        });
        $('#btn_waybill_get').on('click', get_waybill_multi);

        function get_waybill_multi() {
            var package_total = parseInt($("#package_total").val());
            var re = /^[0-9]*[1-9][0-9]*$/;
            if (!re.test(package_total)) {
                BUI.Message.Tip('包裹数必须为正整数', 'warning');
                return false;
            }
            if (package_total > parseInt(record.goods_num)) {
                BUI.Message.Tip('包裹数不能大于商品总数', 'warning');
                return false;
            }
            var params = {sell_record_code: record.sell_record_code, package_num: package_total};
            $.post('?app_act=oms/deliver_package/get_waybill_multi', params, function (ret) {
                if (ret.status == 1) {
                    play_sound('success');
                    record.package_no = ret.data.package_no;
                    record.package_num = ret.data.package_num;
                    record.scan_num = ret.data.scan_num;

                    setRecordData();
                    set_dialog.close();
                    changeElementStatus('s_scan');
                    loadPackageData('all');
                } else {
                    play_sound('error');
                    BUI.Message.Tip(ret.message, 'error');
                }
            }, "json");
        }
    });
    /*--------扫描订单号----END----*/

    /*--------条码扫描----BEGIN----*/
    $("#barcode").keyup(function (event) {
        if (event.keyCode == 13) {
            setErrMessage('');
            var barcode = $(this).val().trim();
            if (barcode == '') {
                play_sound('error');
                setErrMessage('请扫描商品条形码');
                return false;
            }
            var params = {
                deliver_record_id: record.deliver_record_id,
                sell_record_code: record.sell_record_code,
                waves_record_id: record.waves_record_id,
                package_no: record.package_no,
                barcode: barcode,
                pre_set_num: pre_set_num
            };
            $.post("?app_act=oms/deliver_package/scan_barcode", {params: params}, function (ret) {
                if (ret.status == 1) {
                    play_sound('success');
                    record.package_no = ret.data.package_no;
                    record.package_num = ret.data.package_num;
                    record.scan_num = ret.data.scan_num;

                    setRecordData();
                    changeElementStatus('b_scan');
                    loadPackageData('one');
                } else {
                    play_sound('error');
                }
                setErrMessage(ret.message);
            }, "json");
        }
    });
    /*--------条码扫描----END----*/

    /*--------页面元素|数据处理方法----BEGIN----*/

    //加载包裹数据
    function loadPackageData(_type) {
        tableStore.on('beforeload', function (e) {
            e.params.sell_record_code = record.sell_record_code;
            e.params.waves_record_id = record.waves_record_id;
            if (_type == 'one') {
                e.params.package_no = record.package_no;
            }
            if (_type == 'all') {
                e.params.package_no = null;
            }
            tableStore.set("params", e.params);
        });
        tableStore.load();
        $(".nodata").remove();
    }
    //填充订单扫描数据
    function setRecordData() {
        $("#goods_num").text(record.goods_num);
        $("#package_num").text(record.package_num);
        $("#scan_num").text(record.scan_num);
        $("#diff_num").text(record.goods_num - record.scan_num);
        $("#curr_package_no").text(record.package_no);
    }
    //扫描后更新页面订单数据
    function setScanRecordData() {
        var scan_num = parseInt($("#scan_num").text());
        $("#scan_num").text(scan_num + 1);
        $("#diff_num").text(record.goods_num - scan_num);
    }
    //填充设置包裹数页面数据
    function setPackageInfo() {
        $("#package_sell_code").text(record.sell_record_code);
        $("#package_goods_num").text(record.goods_num);
        $("#package_total").val('');
    }
    //改变页面元素状态
    function changeElementStatus(_type) {
        $("#barcode").val('');
        $("#barcode").focus();
        switch (_type) {
            case 's_scan':
                $("#barcode").removeAttr('disabled');

                if (record.package_no != 0) {
                    $("#packet_print").removeAttr('disabled');
                    $("#scan_clear").removeAttr('disabled');
                }
                $("#delivery").removeAttr('disabled');
                $("#show_all").removeAttr('disabled');
                break;
            case 'b_scan':
                if (record.package_no != 0) {
                    $("#packet_print").removeAttr('disabled');
                }
                break;
            case 'packet':
                $("#barcode").val('');
                $("#barcode").focus();
                $("#packet_print").attr('disabled', 'disabled');
                break;
            case 'delivery':
                $("#sell_record_code").val('');
                $("#sell_record_code").focus();
                $("#barcode").attr('disabled', 'disabled');
                $(".well .opt-button").attr('disabled', 'disabled');
                $(".well .scan-total span").text(0);
                break;
        }

    }
    //设置错误信息
    function setErrMessage(msg) {
        $("#error_msg").text(msg);
    }
    /*--------页面元素|数据处理方法----END----*/

    /*--------页面操作配置项加载----BEGIN----*/
    BUI.use('bui/toolbar', function (Toolbar) {
        //预设置包裹数
        pre_set_num = getConfigCookie('pre_set_num');
        var g1 = new Toolbar.Bar({
            elCls: 'button-group',
            itemStatusCls: {
                selected: 'active' //选中时应用的样式
            },
            defaultChildCfg: {
                elCls: 'button button-small',
                selectable: true
            },
            children: [
                {content: '是', id: '1', selected: pre_set_num == 1 ? true : false},
                {content: '否', id: '0', selected: pre_set_num == 0 ? true : false}
            ],
            render: '#pre_set_num'
        });
        g1.render();
        g1.on('itemclick', function (ev) {
            pre_set_num = ev.item.get('id');
            setConfigCookie('pre_set_num', pre_set_num);
        });

        //自动打印物流单
        auto_print_express = getConfigCookie('auto_print_express');
        var g2 = new Toolbar.Bar({
            elCls: 'button-group',
            itemStatusCls: {
                selected: 'active' //选中时应用的样式
            },
            defaultChildCfg: {
                elCls: 'button button-small',
                selectable: true
            },
            children: [
                {content: '是', id: '1', selected: auto_print_express == 1 ? true : false},
                {content: '否', id: '0', selected: auto_print_express == 0 ? true : false}
            ],
            render: '#auto_print_express'
        });

        g2.render();

        g2.on('itemclick', function (ev) {
            auto_print_express = ev.item.get('id');
            setConfigCookie('auto_print_express', auto_print_express);
        });

        //校验物流单是否重复
        verify_express = getConfigCookie('verify_express');
        var g3 = new Toolbar.Bar({
            elCls: 'button-group',
            itemStatusCls: {
                selected: 'active' //选中时应用的样式
            },
            defaultChildCfg: {
                elCls: 'button button-small',
                selectable: true
            },
            children: [
                {content: '是', id: '1', selected: verify_express == 1 ? true : false},
                {content: '否', id: '0', selected: verify_express == 0 ? true : false}
            ],
            render: '#verify_express'
        });
        g3.render();
        g3.on('itemclick', function (ev) {
            verify_express = ev.item.get('id');
            setConfigCookie('verify_express', verify_express);
        });
    });
    /*--------页面操作配置项加载----END----*/

    /*--------页面缓存设置----BEGIN----*/
    //页面加载时，读取cookie,设置配置项状态
    function getConfigCookie(_name) {
        var cookie_val = $.cookie(_name + '_select');
        if (cookie_val == undefined) {
            cookie_val = 1;
        }
        return cookie_val;
    }

    //配置项状态状态更改时，设置cookie
    function setConfigCookie(_name, _status) {
        $.cookie(_name + '_select', _status, {expires: 30});
    }
    /*--------页面缓存设置----END----*/

    //播放提示音
    function play_sound(_type) {
        var wav = "<?php echo CTX()->get_app_conf('common_http_url'); ?>js/sound/" + sounds[_type] + ".wav";
        if (navigator.userAgent.indexOf('MSIE') >= 0) {//IE
            document.getElementById('bgsound_ie').src = wav;
        } else {// Other borwses (firefox, chrome)
            var obj = document.getElementById('bgsound_others');
            obj.src = wav;
            obj.play();
        }
    }

    /*--------页面操作方法----BEGIN----*/

    //删除空包裹
    function delete_package(_index, row) {
        BUI.Message.Confirm('确定要删除包裹' + row.package_no + '吗？', function () {
            var params = {sell_record_code: row.sell_record_code, package_no: row.package_no, waves_record_id: row.waves_record_id};
            $.post('?app_act=oms/deliver_package/delete_package', {params: params}, function (ret) {
                if (ret.status == 1) {
                    record.package_no = ret.data.package_no;
                    record.scan_num = ret.data.scan_num;
                    record.package_num = ret.data.package_num;

                    setRecordData();
                    loadPackageData('all');
                }
                setErrMessage(ret.message);
            }, "json");
        });
    }

    //封包打印
    $("#packet_print").on('click', function () {
        packet_main(record);
    });
    //列表封包
    function packet_package(_index, row) {
        packet_main(row);
    }
    //封包方法
    function packet_main(data) {
        setErrMessage('');
        var params = {
            sell_record_code: data.sell_record_code,
            waves_record_id: data.waves_record_id,
            package_no: data.package_no,
            pre_set_num: pre_set_num
        };
        $.post('?app_act=oms/deliver_package/packet_package', {params: params}, function (ret) {
            setErrMessage(ret.message);
            if (ret.status < 1) {
                return false;
            }
            record.package_no = ret.data.package_no;
            record.scan_num = ret.data.scan_num;
            record.package_num = ret.data.package_num;

            setRecordData();
            changeElementStatus('packet');
            if (record.package_no == 0) {
                loadPackageData('all');
            } else {
                loadPackageData('one');
            }
            //打印
            if (auto_print_express == 1) {
                print_express(0, params);
            }
            update_record_package_no(data.sell_record_code, record.package_no);
            if (ret.status == 2) {
                //发货
                BUI.Message.Confirm('订单已扫描完毕，确定要发货吗？', function () {
                    delivery();
                });
            }
        }, "json");
    }

    function update_record_package_no(sell_record_code, package_no) {
        var params = {sell_record_code: sell_record_code, package_no: package_no};
        $.post('?app_act=oms/deliver_package/update_record_package_no', {params: params}, function (ret) {

        }, "json");
    }

    //快递单打印
    function print_express(_index, _data) {
        new ESUI.PopWindow("?app_act=oms/deliver_record/choose_clodop_printer&new_clodop_print=1&record_ids=" + record.deliver_record_id + "&waves_record_ids=" + record.waves_record_id + "&is_print_express=1" + "&frame_id=print_express0&unable_printer=1", {
            title: "快递单打印",
            width: 500,
            height: 220,
            onBeforeClosed: function () {
            },
            onClosed: function () {
            }
        }).show();

        print_update(_data);
    }

    function print_update(_data) {
        var params = {sell_record_code: record.sell_record_code, package_no: _data.package_no};
        $.post('?app_act=oms/deliver_package/print_update', {params: params}, function (ret) {
            tableStore.load();
        }, "json");
    }

    //发货
    $("#delivery").on('click', delivery);
    function delivery() {
        var params = {sell_record_code: record.sell_record_code, deliver_record_id: record.deliver_record_id};
        $.post('?app_act=oms/deliver_package/delivery', {params: params}, function (ret) {
            if (ret.status == 1) {
                record.goods_num = record.package_num = record.scan_num = record.package_no = 0;
                setRecordData();
                changeElementStatus('delivery');
                loadPackageData('one');
            }
            setErrMessage(ret.message);
        }, "json");
    }

    //重置当前包裹扫描数据
    $("#scan_clear").on('click', function () {
        var params = {
            deliver_record_id: record.deliver_record_id,
            sell_record_code: record.sell_record_code,
            waves_record_id: record.waves_record_id,
            package_no: record.package_no
        };
        $.post('?app_act=oms/deliver_package/clear_curr_package', {params: params}, function (ret) {
            if (ret.status == 1) {
                record.scan_num = ret.data.scan_num;
                record.package_num = ret.data.package_num;

                setRecordData();
                loadPackageData('one');
            }
            changeElementStatus('clear');
            setErrMessage(ret.message);
        }, "json");
    });

    //显示列表全部数据
    $("#show_all").on('click', function () {
        loadPackageData('all');
    });
    /*--------页面操作方法----END----*/
</script>