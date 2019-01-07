<style type="text/css">
    .bui-uploader-button-text {
        color: '#33333';
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
</style>
<div class="upload1">
    <div class="row form-actions actions-bar">
        <div class="span13 offset3 ">
            <?php if($response['lof_status']==1){?>  
                <a class="button" style="float: left;" target="_blank" href="<?php echo get_excel_url("{$response['excel_tpl']}_lof.xlsx",1) ?>">模版下载</a>
            <?php }else{ ?>
                <a class="button" style="float: left;" target="_blank" href="<?php echo get_excel_url("{$response['excel_tpl']}.xlsx",1) ?>">模版下载</a>
            <?php } ?> 
            <div style="float: left;margin-left: 10px;"><span id="J_Uploader" style="display: inline-block;"></span></div>
            <input type="hidden" name="upload_url" id="upload_url" value="" >
            <!--<button style="float:left;" type="button" class="button button-success" value="新增商品导入" id="submit_import_goods" ><i class="icon-plus-sign icon-white"></i> 商品导入</button>-->
        </div>
        <div style="clear: all"></div>
    </div>
</div>
<div class="result1" style="display: block">
    <font color="red"> 提示：若商品已存在，则覆盖</font>
    <br />
    <font color="red" id="result2"> </font>
</div>
<script type="text/javascript">
    BUI.use('bui/uploader', function (Uploader) {
        /**
         * 返回数据的格式
         *
         *  默认是 {url : 'url'},否则认为上传失败
         *  可以通过isSuccess 更改判定成功失败的结构
         */
        var url = '?app_act=prm/goods/import_goods&app_fmt=json';
        //     ?app_act=prm/goods/do_record_import
        var filetype = {
            ext: ['.csv,.xlsx,.xls', '文件类型只能为{0}'],
            maxSize: [2048, '文件大小不能大于2M'],
            minSize: [1, '文件最小不能小于1k!'],
            max: [5, '文件最多不能超过{0}个！'],
            min: [1, '文件最少不能少于{0}个!'],
        };

        var uploader = new Uploader.Uploader({
            'type': 'iframe',
            render: '#J_Uploader',
            url: url,
            rules: filetype,
            multiple: false,
            //可以直接在这里直接设置成功的回调
            success: function (result) {
                if (confirm('确定要导入数据吗')) {
                    $("#J_Uploader .bui-uploader-button-text").html("上传中...");
                    var url = result.url;
                    var r = '?app_act=prm/goods/do_record_import&id=<?php echo $request['id'] ?>&sort=<?php echo $request['sort'] ?>&is_lof=<?php echo $response['lof_status'] ?>&act_type=<?php echo $request['type'] ?>&app_fmt=json';
                    var params = {"url": url};
                    var msg = '';
                    $.post(r, params, function (data) {
                        var type = data.status == 1 ? 'success' : 'error';
//                        if (type == 'success') {
//                            $("#J_Uploader .bui-uploader-button-text").html("上传文件");
//                            BUI.Message.Alert('导入成功' + data.data.success_num + "条");
//                        } else {
//                            $("#J_Uploader .bui-uploader-button-text").html("上传文件");
//                            BUI.Message.Alert(data.message, type);
//                        }
                        $("#J_Uploader .bui-uploader-button-text").html("上传文件");
                        $('#result2').html(data.message);
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

    $(document).ready(function () {
        $("#submit_import_goods").click(function () {
            //  alert(r);
            var url = $("#upload_url").val();
            if (url == '') {
                BUI.Message.Alert('请先上传文件', 'error');
                return false;
            }
            var r = '?app_act=prm/goods/do_record_import&id=<?php echo $request['id'] ?>&sort=<?php echo $request['sort'] ?>&is_lof=<?php echo $response['lof_status'] ?>&act_type=<?php echo $request['type'] ?>&app_fmt=json';
            var params = {"url": url};
            var msg = '';
            $.post(r, params, function (data) {
                var type = data.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('导入成功' + data.data + "条");
                } else {
                    BUI.Message.Alert(data.msg, type);
                }
            }, "json");
        });
    });
</script>              
