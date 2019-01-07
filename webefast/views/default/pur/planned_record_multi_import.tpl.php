<style type="text/css">
    .bui-uploader-button-text {
        color: #33333;
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
        /* 	background:none; */
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
</style>

<?php render_control('PageHead', 'head1', array('title' => '多采购订单导入','ref_table' => 'table'
));?>
<div class="upload1">
    <div class="row form-actions actions-bar">
        <div class="span13 offset3 ">
            <a class="button" target="_blank" style="float: left;" href="<?php echo get_excel_url("planned_record_multi_import.xlsx",1) ?>">模版下载</a>
            <div style="float: left;margin-left: 10px;"><span id="J_Uploader" style="display: inline-block;"></span></div>
        </div>
        <div style="clear: all"></div>
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
<div  id='p1'>
    <table class='table_panel'  >
        <tr><td style="width:10%">列名</td><td style="width:10%">是否必填项</td><td style="width:30%;">描述</td><td style="width:10%;">实例</td></tr>
        <tr>
            <td>采购批次<sup style="color: red;">*</sup></td><td>是</td><td class="new_td">商品入库的批次记录，不同批次的一个供应商商品，会生成两张采购订单，批次会保存到采购订单的备注中</td>
            <td>1</td></tr>
        <tr><td>供应商代码<sup style="color: red;">*</sup></td><td>是</td><td class="new_td">描述商品采购来源，可在供应商档案查看相应代码，不同的供应商会分别生成不同的采购订单</td>
            <td>G01</td></tr>
        <tr><td>计划日期<sup style="color: red;">*</sup></td><td>是</td><td class="new_td">可能的入库时间</td><td>2016-07-28</td></tr>
        <tr><td>仓库代码<sup style="color: red;">*</sup></td><td>是</td><td class="new_td">描述采购的目标仓库，可在仓库档案查看相应代码</td><td>CK001</td></tr>
        <tr><td>商品条形码<sup style="color: red;">*</sup></td><td>是</td><td class="new_td">描述商品SKU的最小单位</td><td>ABC001001</td></tr>
        <tr><td>商品数量<sup style="color: red;">*</sup></td><td>是</td><td  class="new_td">描述采购的数量，填写大于0的整数</td><td>1000</td></tr>
        <tr><td>进货单价</td><td>否</td><td class="new_td">描述商品的采购进货单价，填写大于0的数字</td><td>100.99</td></tr>
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
            min: [1, '文件最少不能少于{0}个!'],
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
                    var r = '?app_act=pur/planned_record/multi_import_record&app_fmt=json';
                    var params = {"url": url};
                    $.post(r, params, function (data) {
                        BUI.Message.Alert(data.message, 'info');
                    }, "json");
                }
            },
            //失败的回调
            error: function (result) {
                BUI.Message.Alert("失败", "error");
            }
        }).render();
    });

</script>              
