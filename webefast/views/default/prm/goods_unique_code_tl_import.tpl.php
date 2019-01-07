<?php
$list = $response['store'];
$store_select = '<option value="">请选择</option>';
foreach ($list as $k => $v) {
    $store_select.='<option value="' . $v['store_code'] . '">' . $v['store_name'] . '</option>';
}
?>
<div class="upload1">
    <div>仓库：<select id='sel' name="store_code" style="width:200px;"><?php echo $store_select;?></select></div><br />
    <div class="row">
        <div class="control-group span11">
            <label class="control-label span3">文件上传：</label>
            <div class="span8 controls">
                <div id="J_Uploader">
                </div>
            </div>
        </div>  
    </div>
    <div><input type="hidden" id="url" name="url"></div>
    <div><input type="hidden" id="_name" name="_name"></div>
    <div class="row form-actions actions-bar">
        <div class="span13 offset3 ">
            <button id="submit" class="button button-primary" type="submit">提交</button>
            <a class="button" target="_blank" href="<?php echo get_excel_url("goods_unique_code_tl.xlsx",1) ?>">模版下载</a>
        </div>
    </div>
</div>
<div class="result1" style="display: block">

</div>
<script type="text/javascript">
    BUI.use('bui/uploader', function (Uploader) {
        /**
         *  返回数据的格式
         *  默认是 {url : 'url'},否则认为上传失败
         *  可以通过isSuccess 更改判定成功失败的结构
         */
        var uploader = new Uploader.Uploader({
            'type': 'iframe',
            render: '#J_Uploader',
            url: '?app_act=prm/goods_unique_code_tl/import_upload',
            //可以直接在这里直接设置成功的回调
            success: function (result) {
                //console.log(result);
                $("#url").val(result.url);
                $("#_name").val(result.name);
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
            error: function (result) {
                BUI.Message.Alert("失败", "error")
            }
        }).render();
    });
    $(document).ready(function () {
        $("#submit").click(function () {
            var url = $("#url").val();
            var name = $("#_name").val();
            var store = $("#sel  option:selected").val();
            if (url == '') {
                BUI.Message.Alert('请上传excel文件', 'error');
                return false;
            }
            if(store==''){
                BUI.Message.Alert('请选择仓库', 'error');
                return false;
            }
            var result = {
                "url": url,
                "name": name,
                "store":store,
            };
            $.post("?app_act=prm/goods_unique_code_tl/import_action", result, function (data) {
                var type = data.status == 1 ? 'success' : 'error';
                BUI.Message.Alert(data.message, type);
            }, "json");
        });
    });
</script>