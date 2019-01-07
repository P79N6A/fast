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
</style>
<div class="upload1">
    <div class="row form-actions actions-bar">
        <div class="span13 offset3 ">
            <a class="button" style="float: left;" target="_blank" href="<?php echo get_excel_url("wms_costum_goods.xlsx", 1) ?>">模版下载</a>
            <div style="float: left;margin-left: 10px;"><span id="J_Uploader" style="display: inline-block;"></span></div>	
            <input type="hidden" name="upload_url" id="upload_url" value="" >
        </div>
        <div style="clear: all"></div>
    </div>
</div>

<script type="text/javascript">
    var wms_config_id = "<?php echo $request['wms_config_id']; ?>";
    BUI.use('bui/uploader', function (Uploader) {
        var url = '?app_act=sys/wms_config/upload_file&app_fmt=json';
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
            success: function (result) {
                BUI.Message.Confirm('确定要导入商品数据吗？', function () {
                    var url = result.url;
                    var r = '?app_act=sys/wms_config/do_import_goods&app_fmt=json&id=' + wms_config_id;
                    var params = {"url": url, "wms_config_id" : wms_config_id};
                    $.post(r, params, function (data) {
                        var type = data.status == 1 ? 'success' : 'error';
                        if (type == 'success') {
                            BUI.Message.Alert('导入成功', function () {
                                ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
                            }, type);
                        } else {
                            BUI.Message.Alert(data.message, function(){
                                ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
                            }, type);
                        }
                    }, "json");
                });
            },
            error: function (result) {
                BUI.Message.Alert("导入失败", "error")
            }
        }).render();
    });
</script>              
