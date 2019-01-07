<div class="upload1">
    <div class="row">
        <div class="control-group span11">
            <label class="control-label span3">文件上传：</label>
            <div class="span8 controls">
                <div id="J_Uploader"></div>
            </div>
        </div>   
    </div>
    <div>
        <input type="hidden" id="url" name="url">
    </div>
    <div class="row form-actions actions-bar">
        <div class="span13 offset3 ">
            <button id="submit" class="button button-primary" type="submit">提交</button>
            <a class="button" target="_blank" href="<?php echo get_excel_url("express_customer.xlsx",1) ?>">模版下载</a>
        </div>
   </div>
</div>
<div class="result1" style="display: block">
    <font color="red">
        <p>提示：“会员昵称”为必填项，若模板中的“会员昵称”未填，则不会被导入</p>
        <p style="text-indent: 4em;">如果会员已经指定快递，则会被覆盖</p>
    </font>
</div>
<script type="text/javascript">
    var express_code = "<?php echo $request['express_code']; ?>";
    BUI.use('bui/uploader',function (Uploader) {
        /**
         * 返回数据的格式
         *
         *  默认是 {url : 'url'},否则认为上传失败
         *  可以通过isSuccess 更改判定成功失败的结构
         */
        var uploader = new Uploader.Uploader({
            type:'iframe',
            render: '#J_Uploader',
//          url: '?app_act=crm/express_strategy/import_users&app_fmt=json',
            url: '?app_act=prm/goods/import_goods&app_fmt=json',
            //可以直接在这里直接设置成功的回调
            success: function(result){
            	$("#url").val(result.url);
            },
            //失败的回调
            error: function(result){
                BUI.Message.Alert("失败", "error");
            }
        }).render();
    });
	
    $("#submit").click(function() {
        var url = $("#url").val();
      	var msg = '';
        if(url == ''){
            BUI.Message.Alert('请先上传文件', 'error');
            return false;
        }
//      	var r = '?app_act=op/gift_strategy/customer_import_action&strategy_code='+strategy_code+'&op_gift_strategy_detail_id='+op_gift_strategy_detail_id+'&app_fmt=json';
    $.post(
        '?app_act=crm/express_strategy/import_user&express_code=' + express_code,
        {"url": url},
        function(data){
            var type = data.status == 1 ? 'success' : 'error';
            if (type == 'success') {
                if((typeof data.data.fail!= "undefined")&&data.data.fail!='') {
                        msg +="<br />失败SKU:"+data.data.fail;
                }
                BUI.Message.Alert('导入成功'+msg);
                location.reload();
            } else {
                BUI.Message.Alert(data.msg, type);
            }
        },
        "json");
    });
          
</script>              
