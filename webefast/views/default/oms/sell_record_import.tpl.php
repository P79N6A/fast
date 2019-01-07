<div class="upload1">
    <div class="row">
        <div class="control-group span11">
            <label class="control-label span3">文件上传：</label>
            <div class="span8 controls">
                <div id="J_Uploader">
                </div>
            </div>
        </div>

    </div>
    <input type="hidden" id="url" name="url">
    <div class="row form-actions actions-bar">
        <div class="span13 offset3 ">
            <button id="submit" class="button button-primary" type="submit">提交</button>
            <a class="button" target="_blank" href="?app_act=oms/sell_record/import_tpl&code=sell_record_shipped">模版下载</a>
        </div>
    </div>
</div>
<div class="result1" style="display: block">

</div>
<script type="text/javascript">
    BUI.use('bui/uploader',function (Uploader) {

        /**
         * 返回数据的格式
         *
         *  默认是 {url : 'url'},否则认为上传失败
         *  可以通过isSuccess 更改判定成功失败的结构
         */
        var uploader = new Uploader.Uploader({
            'type':'iframe',
            render: '#J_Uploader',
            url: '?app_act=oms/sell_record/import_upload',
            //可以直接在这里直接设置成功的回调
            success: function(result){
                //console.log(result);
                $("#url").val(result.url);
                /*
                 $.post("?app_act=prm/goods_shelf/import_action", result, function(data){
                 $(".upload1").hide()
                 $(".result1").show()

                 $(".result1").html("导入成功: "+data.success+"行<br>导入失败列表:<br>"+data.faild)

                 }, "json")
                 */
            },
            //isSuccess : function(result){},
            //失败的回调
            error: function(result){
                BUI.Message.Alert("失败", "error")
            }
        }).render();
    });
    $(document).ready(function(){

        $("#submit").click(function(){
            var bischecked=$('#select_shelf').is(':checked');
            var url = $("#url").val();
            if(url == ''){
                BUI.Message.Alert('请上传excel文件', 'error');
                return false;
            }

            var result = {
                "url": url,
                "bischecked":bischecked
            };
//           $.post("?app_act=oms/sell_record/import_action", result, function(data){
//                $(".upload1").hide()
//                $(".result1").show()
//
//                $(".result1").html("导入成功: "+data.success+"行<br>导入失败列表:<br>"+data.faild)
//
//            }, "json")
       $.post("?app_act=oms/sell_record/import_action", result, function(data){
                var type = data.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(data.message);
                } else {
                    BUI.Message.Alert(data.message);
                }
            }, "json");
        })
    });
</script>