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
<div class="upload1">
    <div class="row form-actions actions-bar">
        <div class="span13 offset3 ">
            <?php if($response['import_type'] == 1){ ?>
                <a class="button" style="float: left;" target="_blank" href="<?php echo get_excel_url("planned_record.xlsx",1) ?>">模版下载</a>
            <?php }else{?>
                <a class="button" style="float: left;" target="_blank" href="<?php echo get_excel_url("planned_record_complete_num.xlsx",1) ?>">模版下载</a>
            <?php }?>

            <div style="float: left;margin-left: 10px;"><span id="J_Uploader" style="display: inline-block;"></span></div>
        </div>
        <div style="clear: both"></div>
    </div>
</div>
<?php if($response['import_type'] != 1) { ?>
<div class="control-group">
    <div class="controls">
        <label class="radio" ><input type="radio" checked="checked" name="complete" id="overlying" value="overlying">叠加完成数</label>&nbsp;&nbsp;&nbsp;&nbsp;
        <label class="radio" ><input type="radio" name="complete" id="cover" value="cover">覆盖完成数</label>
    </div>
</div>
<?php } ?>
<div class="result1" style="display: block">
    <font color="red"> 提示：<?php echo $response['import_type'] == 1 ? '若商品已存在，则覆盖数量' : '若采购订单生成了采购通知单，会更新完成数，是否继续' ?></font>
    <br />
    <font color="red" id="result2"> </font>
</div>
<script type="text/javascript">
    var id = "<?php echo $response['id']; ?>";
    BUI.use('bui/uploader', function (Uploader) {
        /**
         *  返回数据的格式
         *  默认是 {url : 'url'},否则认为上传失败
         *  可以通过isSuccess 更改判定成功失败的结构
         */
        var url = '?app_act=pur/planned_record/import_goods&app_fmt=json';
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
                var type = $('.controls input[name="complete"]:checked ').val();
                if(<?php echo $response['import_type']; ?> != 1){
                    if (type == null || type == undefined || type == '') {
                        BUI.Message.Alert("请选择导入方式", "error")
                        return false;
                    }
                }
                if (confirm('确定要导入数据吗')) {
                    var url = result.url;
                    var r = '?app_act=pur/planned_record/import_goods_upload&app_fmt=json&id=' + id + "&import_type=<?php echo $response['import_type'] ?>"+ '&type=' + type;
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
                console.log("error" + result);
                BUI.Message.Alert("失败", "error");
            }
        }).render();
    });
</script>
