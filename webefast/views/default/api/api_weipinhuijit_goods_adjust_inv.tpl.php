<style type="text/css">
    body,html{overflow-y: hidden;}
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
    .bui-queue {
        float:left;
    }

    .controls{
        float:right;
        margin-right: 30px;
    }

</style>
<div class="row" style="display:inline">

    <span class="control-label">选择同步店铺：<b style="color:red"> *</b></span>

    <div class="controls">
        <div id="shop_name">
            <input type="hidden" id="shop_code" value="">
        </div>
    </div>
</div>
<div class="upload1">
    <div class="row form-actions actions-bar">
        <div class="offset3 ">

            <a class="button" style="float: left;" target="_blank" href="<?php echo get_excel_url("weipinhuijit_inv_adjust.xlsx",1) ?>">模版下载</a>
            <div style="float: left;margin-left: 10px;"><span id="J_Uploader" style="display: inline-block;"></span></div>
            <input type="hidden" name="upload_url" id="upload_url" value="" >
        </div>
        <div style="clear: both"></div>
    </div>
</div>
<div class="result1" style="margin-top: 20px; height: 100px; overflow:auto;">
    <font color="red"> 友情提示：导入库存，即以导入模版中库存数量直接更新唯品会平台，且同步成功后，需要将商品设置为不允许库存同步</font>
    <br />
    <font color="red" id="result2" style="margin-top: 20px;"> </font>
</div>
<script type="text/javascript">
    BUI.use(['bui/select', 'bui/data'], function (Select, Data) {
        var store = new Data.Store({
            url: '?app_act=api/api_weipinhuijit_goods/get_wepinhuijit_shop',
            autoLoad: true
        });
        var select = new Select.Select({
            render: '#shop_name',
            valueField: '#shop_code',
            multipleSelect: false,
            store: store
        });
        select.render();
    });

    BUI.use('bui/uploader', function (Uploader) {
        /**
         * 返回数据的格式
         *
         *  默认是 {url : 'url'},否则认为上传失败
         *  可以通过isSuccess 更改判定成功失败的结构
         */
        var url = '?app_act=pur/order_record/import_goods&app_fmt=json';
        //     ?app_act=prm/goods/do_record_import
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
                var shop_code = $('#shop_code').val();
                if (shop_code == '') {
                    BUI.Message.Tip('请选择同步店铺', 'warning');
                    return;
                }
                BUI.Message.Confirm('确定要导入数据吗？', function () {
                    var url = result.url;
                    var r = '?app_act=api/api_weipinhuijit_goods/import_stock_data';
                    var params = {"url": url, "shop_code": shop_code};
                    var msg = '';
                    $('#result2').html('导入同步中···');
                    $.post(r, params, function (data) {
                        var type = data.status == 1 ? 'success' : 'error';
                        $('#result2').html(data.message);
                    }, "json");
                }, 'question');
            },
            //失败的回调
            error: function (result) {
                BUI.Message.Alert("失败", "error");
            }
        }).render();
    });
</script>              
