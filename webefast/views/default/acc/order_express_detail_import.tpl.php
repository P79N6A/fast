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
    .row{margin-bottom: 15px;}
</style>
<div class="upload1">
    <div class="row form-actions actions-bar">
        <div class=" offset3 " >
            <div class="row">
                <select id='express_type'>
                    <option value="0">请选择快递类型</option>
                    <?php
                    foreach ($response['express'] as $express_code => $express_name) {
                        echo "<option value=\"$express_code\">$express_name</option>";
                    }
                    ?>
                </select>
            </div>
            <a style="float: left;" class="button" target="_blank" href="<?php echo get_excel_url("order_express_dz.xlsx",1) ?>">模版下载</a>
            <div style="float: left;margin-left: 10px;"><span id="J_Uploader" style="display: inline-block;"></span></div>
            <input type="hidden" name="upload_url" id="upload_url" value="" >
        </div>
        <div style="clear: all"></div>
    </div>
</div>
<div class="result1" style="display: block">


</div>
<br>
<div class="result2" style="display: block">
    <span style="color:red">注意：导入快递运费后原核销状态和快递运费将被重置。</span>
</div>
<script type="text/javascript">
    $(function () {
        var express_code = $("#express_type").val();
        if(express_code == '0'){
            $(".result1").html("<font color='red'>请您先选择快递</font>");
            $("#J_Uploader").attr('style','display:none');
        }
        $("#express_type").change(function () {
            var express_code = $("#express_type").val();
            if (express_code == '0') {
                $(".result1").html("<font color='red'>请您先选择快递</font>");
                $("#J_Uploader").attr('style','display:none');
            } else {
                $(".result1").html("");
                $("#J_Uploader").attr('style','display:inline-block');
            }
        });
   });
    BUI.use('bui/uploader', function (Uploader) {
        /**
         * 返回数据的格式
         *
         *  默认是 {url : 'url'},否则认为上传失败
         *  可以通过isSuccess 更改判定成功失败的结构
         */
        var url = '?app_act=acc/order_express_detail/do_import&app_fmt=json';
        var filetype = {
            ext: ['.csv,.xlsx,.xls', '文件类型只能为{0}'],
            maxSize: [2048, '文件大小不能大于2M'],
            minSize: [1, '文件最小不能小于1k!'],
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
                var dz_code = "<?php echo $response['dz_code']; ?>";
                var express_code = $("#express_type").val();
                if (confirm('确定要导入数据吗')) {
                    var url = result.url;
                    var r = '?app_act=acc/order_express_detail/do_import_detail&dz_code=' + dz_code + '&express_type=' + express_code + '&app_fmt=json';
                    var params = {"url": url};
                    $.post(r, params, function (data) {
                        var type = data.status == 1 ? 'success' : 'error';
                        if (type == 'success') {
                            BUI.Message.Alert(data.message, type);
                        } else {
                            BUI.Message.Alert(data.message, type);
                        }
                    }, "json");
                }
            },
            //失败的回调
            error: function (result) {
                console.log("error" + result);
                BUI.Message.Alert("失败", "error")
            }
        }).render();
        });
</script>              
