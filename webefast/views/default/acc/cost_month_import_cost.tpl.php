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
        background: rgba(0, 0, 0, 0) -moz-linear-gradient(center top , #fdfefe, ) repeat scroll 0 0;
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
    .check-express-no{
        margin-bottom: 10px;
        margin-top: -15px;
    }
    .control-group .controls{margin-left:20px;}
</style>
<div class="control-group">
    <div class="controls">
        <label class="radio" ><input type="radio" name="adjust_cost" id="adjust_cost" value="adjust_cost">以调整成本导入</label><br>
        <label class="radio" ><input type="radio" name="adjust_cost" id="end_adjust_cost" value="end_adjust_cost">以调整后成本单价导入</label>
    </div>
</div>
<div class="upload1">
    <div class="row form-actions actions-bar">
        <div class="offset3 ">
            <a class="button" style="float: left;" target="_blank" href="<?php echo get_excel_url("cost_month_adjust.xlsx",1) ?>">模版下载</a>
            <div style="float: left;margin-left: 10px;"><span id="J_Uploader" style="display: inline-block;"></span></div>
            <input type="hidden" name="upload_url" id="upload_url" value="" >
        </div>
        <div style="clear: all"></div>
    </div>
</div>
<div class="result1" style="display: block">
    <font color="red"> 提示：覆盖导入商品的调整成本 或 调整后成本</font>
    <br />
    <font color="red" id="result2"> </font>
</div>
<script type="text/javascript">
    var record_code = "<?php echo $response['record_code']; ?>";
    var ymonth = "<?php echo $response['ymonth']; ?>";
    BUI.use('bui/uploader', function (Uploader) {
        /**
         *  返回数据的格式
         *  默认是 {url : 'url'},否则认为上传失败
         *  可以通过isSuccess 更改判定成功失败的结构
         */
        var url = '?app_act=pur/order_record/import_goods&app_fmt=json';
        var filetype = {
            ext: ['.csv,.xlsx,.xls', '文件类型只能为{0}'],
            maxSize: [2048, '文件大小不能大于2M'],
            //minSize: [1, '文件最小不能小于1k!'],
            max: [5, '文件最多不能超过{0}个！'],
            //min: [1, '文件最少不能少于{0}个!'],
        };

        var uploader = new Uploader.Uploader({
            'type': 'iframe',
            render: '#J_Uploader',
            url: url,
            rules: filetype,
            multiple: false,
            //可以直接在这里直接设置成功的回调
            success: function (result) {
                var type = $('.controls input[name="adjust_cost"]:checked ').val();
                if (type == null || type == undefined || type == '') {
                    BUI.Message.Alert("请选择导入方式", "error")
                    return false;
                }
                if (confirm('确定要导入数据吗')) {
                    var url = result.url;
                    var r = '?app_act=acc/cost_month/import_upload&app_fmt=json&ymonth=' + ymonth + '&record_code=' + record_code + '&type=' + type;
                    var params = {"url": url};
                    var msg = '';
                    $.post(r, params, function (data) {
                        var type = data.status == 1 ? 'success' : 'error';
                        $('#result2').html(data.message);
                    }, "json");
                }
            },
            //失败的回调
            error: function (result) {
                BUI.Message.Alert("失败", "error")
            }
        }).render();
    });
</script>              
