<style type="text/css">
    .upload1{margin-top: 50px;}
    .bui-uploader-button-text {
        color: #333333;
        font-size: 14px;
        line-height: 20px;
        text-align: center;
    }

    .bui-uploader .bui-uploader-button-wrap .file-input-wrapper {
        display: block;
        height: 20px;
        left: 0;
        overflow: hidden;
        position: absolute;
        top: 0;
        width: 60px;
        z-index: 300;
    }
    .defaultTheme .bui-uploader-button-wrap {
        background: rgba(0, 0, 0, 0) -moz-linear-gradient(center top , #fdfefe ) repeat scroll 0 0;
        border-radius: 4px;
        color: #333;
        display: inline-block;
        font-size: 14px;
        height: 20px;
        line-height: 20px;
        margin-right: 10px;
        overflow: hidden;
        padding: 0;
        position: relative;
        text-align: center;
        text-decoration: none;
        z-index: 500;
        padding: 2px 12px;
    }
    .bui-uploader-htmlButton {
        float:left;
    }
    .bui-simple-list {
        float:left;
    }

    .table_panel td {
        border:1px solid #dddddd;
        line-height: 20px;
        padding: 6px;
        text-align: center;
        width:350px;
        vertical-align: top;
    }
    .table_panel .new_td{
        text-align: left;
    }
    a{text-decoration: none;cursor: pointer;}
</style>

<?php
render_control('PageHead', 'head1', array('title' => '二维表导入采购单', 'ref_table' => 'table'
));
?>
<div class="upload1">
    <div class="row form-actions actions-bar">
        <div class="span13 offset3 ">
            <a class="button" target="_blank" style="float: left;" onclick="getExcelTpl()">模版下载</a>
            <div style="float: left;margin-left: 10px;"><span id="J_Uploader" style="display: inline-block;"></span></div>
        </div>
    </div>
</div>
<div class="result1" style="display: block">
    <font color="red" id="result2"> </font>
</div>
<br />
<div>
    <font color="red">提示：以下为模版详细说明</font>
</div>
<br/>
<div id='p1'>
    <table class='table_panel'  >
        <tr>
            <td style="width:8%">列名</td><td style="width:7%">是否必填项</td><td style="width:35%;">描述</td><td style="width:10%;">示例</td>
        </tr>
        <tr>
            <td>仓库代码<sup style="color: red;">*</sup></td><td>是</td><td class="new_td">采购的目标仓库，可在仓库档案查看相应代码，不同的仓库会生成不同的采购订单</td><td>CK001</td>
        </tr>
        <tr>
            <td>计划日期<sup style="color: red;">*</sup></td><td>是</td><td class="new_td">可能的入库时间</td><td>2018-01-01</td>
        </tr>
        <tr>
            <td>商品编码<sup style="color: red;">*</sup></td><td>是</td><td  class="new_td">采购商品编码</td><td>G0001</td>
        </tr>
        <tr>
            <td>商品颜色<sup style="color: red;">*</sup></td><td>是</td><td  class="new_td">采购商品的颜色名称</td><td>红色</td>
        </tr>
        <tr>
            <td>商品尺码<sup style="color: red;">*</sup></td><td>是</td><td  class="new_td">表头为采购商品的尺码名称(尺码层，以第一层为准)，对应的列值为该商品的数量，没有则填0或不填</td><td>90</td>
        </tr>
        <tr>
            <td>进货单价</td><td>否</td><td class="new_td">描述商品的采购进货单价，填写大于0的数字</td><td>100.99</td>
        </tr>
    </table>
</div>
<script type="text/javascript">
    BUI.use('bui/uploader', function (Uploader) {
        /**
         * 返回数据的格式
         *  默认是 {url : 'url'},否则认为上传失败
         *  可以通过isSuccess 更改判定成功失败的结构
         */
        var url = '?app_act=pur/planned_record/import_goods&app_fmt=json';
        var filetype = {
            ext: ['.csv,.xlsx,.xls', '文件类型只能为{0}'],
            maxSize: [2048, '文件大小不能大于2M'],
            max: [5, '文件最多不能超过{0}个！'],
            min: [1, '文件最少不能少于{0}个!']
        };

        var uploader = new Uploader.Uploader({
            type: 'iframe',
            render: '#J_Uploader',
            url: url,
            rules: filetype,
            multiple: false,
            //可以直接在这里直接设置成功的回调
            success: function (result) {
                if (confirm('确定要导入数据吗')) {
                    var url = result.url;
                    var r = '?app_act=pur/planned_record/layer_import_action&app_fmt=json';
                    var params = {"url": url};
                    $.post(r, params, function (ret) {
                        if (ret.status == 1) {
                            BUI.Message.Alert(ret.message, 'success');
                        } else {
                            BUI.Message.Alert(ret.message, 'warning');
                        }
                    }, "json");
                }
            },
            //失败的回调
            error: function (result) {
                BUI.Message.Alert("失败", "error");
            }
        }).render();
    });

    function getExcelTpl() {
        $.post('?app_act=pur/planned_record/get_excel_tpl', [], function (ret) {
            if (ret.status == 1) {
                window.open('<?php echo get_excel_url("planned_record_layer_import.xlsx", 1) ?>');
            } else if (ret.status == -2) {
                var msg = ret.message + '<b>，前往<a onclick="view(1)"> 开启</a></b>';
                BUI.Message.Alert(msg, 'warning');
            } else if (ret.status == -3) {
                var msg = ret.message + '<b>，前往<a onclick="view(2)"> 设置</a></b>';
                BUI.Message.Alert(msg, 'warning');
            } else {
                BUI.Message.Tip(ret.message, 'warning');
            }
        }, "json");
    }

    function view(_type) {
        if (_type === 1) {
            openPage('<?php echo base64_encode('?app_act=sys/params/industry&activetab=clothing') ?>', '?app_act=sys/params/industry&activetab=clothing', '行业特性设置');
        } else {
            openPage('<?php echo base64_encode('?app_act=prm/size_layer/set') ?>', '?app_act=prm/size_layer/set', '尺码层设置');
        }
    }
</script>              
