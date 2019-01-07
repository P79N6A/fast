<style>
    .main-inspect{
        margin-top: 50px;
        margin-left: 10px;
    }
    .filter_addr,.filter_barcode{
        width: 100px;
    }
    #filter_option label{
        margin-right: 15px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '快速审单', 'links' => array(), 'ref_table' => 'table'));
?>
<div class="main-inspect">
    <div class="row">

    </div>
    <div class="row">
        <div class="control-group span8" >
            <div class="controls">
                <div id="shop_name">
                    <input type="hidden" id="shop_code" value="all" name="shop_code">
                </div>
            </div>
        </div>
    </div>
    <div class="row" style="margin-top: 10px;">
        <div class="control-group span13">
            <div class="controls">
                <div id="filter_group"></div>
            </div>
        </div>
    </div>
    <div class="row" style="margin-top: 10px;">
        <div class="control-group span14">
            <div class="controls">
                <label class="checkbox"><input type="checkbox" name="all" value="all" id='select_all' onclick="selectAll();"> 全选</label>
            </div>
        </div>
    </div>
    <div class="row" style="margin-top: 10px;">
        <div class="control-group span24">
            <div class="controls">
                <div class="well" style="width:100%; height: 70px;padding:10px;">
                    <div id="filter_option"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row form-actions actions-bar">
        <div class="span13 offset3 ">
            <button type="button" class="button button-primary" onclick="onekey_inspect_record()">一键确认（审单）</button>
            <button type="button" class="button button-primary" style="margin-left: 10px;" onclick="get_filter_data(1)">刷新</button>
        </div>
    </div>
    <div class="row" style="color: #ff0033">
        <span>说明：快速审单仅支持正常单操作，筛选项仅显示排名前10的组合</span><br/><br/>
        <span>注意：使用 <快速审单> 功能请关闭自动确认定时器！</span>
    </div>
</div>
<script>
    var shop_code, filter_type;
    //加载店铺
    BUI.use(['bui/select', 'bui/data'], function (Select, Data) {
        var store = new Data.Store({
            url: '?app_act=base/shop/get_purview_shop',
            autoLoad: true
        });
        var select = new Select.Select({
            render: '#shop_name',
            valueField: '#shop_code',
            multipleSelect: false,
            store: store
        });
        select.render();
        select.on('change', function (ev) {
            shop_code = ev.item.value;
            get_filter_data(0);
        });
    });
    //加载筛选工具条
    BUI.use('bui/toolbar', function (Toolbar) {
        var g1 = new Toolbar.Bar({
            elCls: 'button-group',
            itemStatusCls: {
                selected: 'active' //选中时应用的样式
            },
            defaultChildCfg: {
                elCls: 'button button-small',
                selectable: true //允许选中
            },
            children: [
                {content: '区域筛选', id: 'filter_addr', width: 150, selected: true},
                {content: '商品筛选（条码级）', id: 'filter_barcode', width: 150}
            ],
            render: '#filter_group'
        });

        g1.render();
        g1.on('itemclick', function (ev) {
            filter_type = ev.item.get('id');
            get_filter_data(0);
        });
    });

    //获取筛选数据
    function get_filter_data(is_refresh) {
        shop_code = $('#shop_code').val();
        filter_type = $('#filter_group li.active').attr('id');
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('oms/sell_record/get_filter_data'); ?>',
            data: {shop_code: shop_code, filter_type: filter_type, is_refresh: is_refresh},
            success: function (ret) {
                if (ret.status == 1) {
                    add_html_option(ret.data);
                } else {
                    alert_msg(ret.status, ret.message);
                }
            }
        });
    }

    function add_html_option(data) {
        var option = '<div class="label-check">';
        $.each(data, function (i, val) {
            if (i == 5) {
                option += '</div><br><div>';
            }
            option += '<label class="checkbox"><input name="filter_data[]" type="checkbox" value="' + val['value'] + '">' + val['text'] + '(' + val['num'] + ')</label>';
        });
        option += '</div>';
        $('#filter_option').html(option);
    }

    //一键审单
    function onekey_inspect_record() {
        var _filter_data = [];
        $("#filter_option input:checkbox[name='filter_data[]']:checked").each(function (i) {
            _filter_data.push($(this).val());
        });
        if (_filter_data.length < 1) {
            BUI.Message.Alert('请选择筛选项', 'warning');
            return false;
        }
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('oms/sell_record/onekey_inspect_record'); ?>',
            data: {shop_code: shop_code, filter_type: filter_type, filter_data: _filter_data},
            success: function (ret) {
                alert_msg(ret.status, ret.message);
            }
        });
    }

    //全选
    function selectAll() {
        if ($("#select_all").attr("checked")) {
            $(":checkbox").attr("checked", true);
        } else {
            $(":checkbox").attr("checked", false);
        }
    }

    function alert_msg(_status, _msg) {
        if (_status == 1) {
            BUI.Message.Show({
                msg: _msg,
                icon: 'success',
                buttons: [],
                autoHide: true
            });
        } else {
            BUI.Message.Show({
                msg: _msg,
                icon: 'error',
                buttons: [],
                autoHide: true
            });
        }
    }
</script>

