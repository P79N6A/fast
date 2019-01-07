<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="content-Type" content="text/html; charset=UTF-8" />
        <title></title>
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
        </style>
        <?php echo load_js('bui.js', true) ?>
        <?php echo load_css('bui.css', true) ?>
        <?php echo load_css('dpl.css', true) ?>
        <script type="text/javascript" src="../../webpub/js/upload_img.js"></script>
        <script type="text/javascript" src="../../webpub/js/uploader-min.js"></script>
    </head>
    <body>
        <div class="upload1">
            <div class="row form-actions actions-bar">
                <div style="float: left;"><span id="J_Uploader" style="display: inline-block;"></span></div>
                <input type="hidden" name="upload_url" id="upload_url" value="" >
            </div>
        </div>
        
        
        
        <script type="text/javascript">
             document.domain = 'baotayun.com';
            BUI.use('bui/uploader', function (Uploader) {
                var url = '?app_act=/upload_images/upload_img&app_fmt=json';
                var filetype = {
                    ext: ['.jpg,.png,.gif', '文件类型只能为{0}'],
                    maxSize: [2048, '文件大小不能大于2M'],
                    //minSize: [1, '文件最小不能小于1k!'],
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
                        $("#upload_url").val(result.url);
                        BUI.Message.Alert("图片上传成功", "success");
                        parent.get_img(result.url);
                        
                    },
                    //失败的回调
                    error: function (result) {
                        console.log("error" + result);
                        BUI.Message.Alert("上传失败", "error");
                    }
                }).render();
            });


        </script>
    </body>
</html>
