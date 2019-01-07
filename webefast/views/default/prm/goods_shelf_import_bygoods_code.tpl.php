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
</style>

<div class="upload1">
    <!--     <div class="row"> -->
    <!--        <div class="control-group span11"> -->
    <!--         <label class="control-label span3">文件上传：</label> -->
    <!--         <div class="span8 controls"> -->
    <!--             <div id="J_Uploader"> -->
    <!--             </div> -->
    <!--         </div> -->
    <!--        </div>   -->

    <!--     </div> -->
    <!--     <div><input type="hidden" id="url" name="url">覆盖原有库位:<input type='checkbox' name='select_shelf' id='select_shelf'  value=1></div> -->
    <!--     <div class="row form-actions actions-bar"> -->
    <!-- 		<div class="span13 offset3 "> -->
    <!-- 		<button id="submit" class="button button-primary" type="submit">提交</button> -->
    <!-- 		<a class="button" target="_blank" href="?app_act=sys/file/get_file&type=1&name=goods_shelf_record.csv">模版下载</a> -->
    <!-- 		</div> -->
    <!--    </div> -->
    <div class="offset3" style="margin-bottom: 10px;">
        <div><span id="J_Uploader" style="display: inline-block;"></span></div>
        <input type="hidden" name="upload_url" id="upload_url" value="" >
    </div>
    <div><input type="hidden" id="url" name="url"><input type="hidden" id="is_upload" name="is_upload">覆盖原有库位:<input type='checkbox' name='select_shelf' id='select_shelf'  value=1><br>
        <span style="color:#ff3300">（勾选，解除系统中商品的现有绑定关系，以Excel文件中最新绑定关系为准）</span>
</div>
    <div class="row form-actions actions-bar">
        <div class="offset3 ">
            <?php
            $csv_file = ($response['lof_status'] == 0) ? 'goods_shelf_record_bygoods_code.xlsx' : 'goods_shelf_record_bygoods_code_lof.xlsx';
            ?>
            <a class="button" style="float: left;" target="_blank" href="<?php echo get_excel_url($csv_file,1) ?>">模版下载</a>


            <button style="float:left;" type="button" class="button button-success" value="商品库位导入" id="submit_import_goods" ><i class="icon-white"></i> 商品库位导入</button>
        </div>
        <div style="clear: all"></div>
    </div>
</div>
<div class="result1" style="display: block">

</div>
<script type="text/javascript">
    var uploader;
    BUI.use('bui/uploader', function (Uploader) {

        /**
         * 返回数据的格式
         *
         *  默认是 {url : 'url'},否则认为上传失败
         *  可以通过isSuccess 更改判定成功失败的结构
         */
// 			 uploader = new Uploader.Uploader({
//             'type':'iframe',
//             render: '#J_Uploader',
//             url: '?app_act=prm/goods_shelf/import_upload',
//             //可以直接在这里直接设置成功的回调
//             success: function(result){
//             	//console.log(result);
//             	//$("#url").val(result.url);
// 	             $(".upload1").hide()
// 	             $(".result1").show()

// 	             $(".result1").html("导入成功: "+result.success+"行<br>导入失败列表:<br>"+result.faild)
//             },
//             //isSuccess : function(result){},
//             //失败的回调
//             error: function(result){
//                 BUI.Message.Alert("失败", "error")
//             }
//         }).render();
        var url = '?app_act=oms/deliver_record/import_express&app_fmt=json';
        //     ?app_act=prm/goods/do_record_import
        var filetype = {
            ext: ['.csv,.xlsx,.xls', '文件类型只能为{0}'],
            maxSize: [2048, '文件大小不能大于2M'],
            // minSize: [1, '文件最小不能小于1k!'],
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
                $("#upload_url").val(result.url);
            },
            //失败的回调
            error: function (result) {
                console.log("error" + result);
                BUI.Message.Alert("失败", "error")
            }
        }).render();
    });
    $(document).ready(function () {
        $("#J_Uploader").click(function(){
            $("#is_upload").val('正在上传');
        })
        $("#submit_import_goods").click(function () {
            //  alert(r);
            var select_shelf = $("#select_shelf").is(':checked');
            if (select_shelf == true) {
                bischecked = 1;
            } else {
                bischecked = 0;
            }
            var url = $("#upload_url").val();
            if (url == '') {
                var is_upload = $('#is_upload').val();
                if(is_upload != '') {
                    BUI.Message.Alert('正在转换文件', 'success');
                    return;
                }
                BUI.Message.Alert('请先上传文件', 'error');
                return false;
            }
            $("#submit_import_goods").attr('disabled',true);
            var r = '?app_act=prm/goods_shelf/import_action&bischecked=' + bischecked + '&app_fmt=json'+'&type=goods_code';
            var params = {"url": url};
            var msg = '';
            $.post(r, params, function (data) {
                var type = data.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(data.message);
                } else {
                    BUI.Message.Alert(data.message);
                }
                $("#submit_import_goods").attr('disabled',false);
                $("#is_upload").val('');
            }, "json");
        });

    });
</script>