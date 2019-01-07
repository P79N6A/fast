<style type="text/css">
    .well {
        min-height: 30px;
    }
    form.form-horizontal{ 
        margin-top:30px;padding:20px; border:1px solid #ded6d9;
    }
    div{
        line-height: 60px;
    }
    .inv_bottom{
        color:red;
    }
    .upload1{
        display:none;
    }
</style>

<?php echo load_js('jquery.cookie.js') ?>
<?php echo load_js('comm_util.js') ?>

<form class="form-horizontal">
<div class="page-header1" style="margin-top: 4px;">
    <span class="page-title">
        <h2>店铺库存手工导入</h2>
    </span>
</div>
<div class="clear"></div>
<hr>
<div>
活动店铺:<select class='inv_shop'>
        <option>请选择</option>
            <?php 
               $ret = load_model('base/ShopModel')->get_purview_shop_tianmao();
               foreach($ret as $value){
                   echo "<option value='{$value['shop_code']}'>{$value['shop_name']}</option>";
               }
            ?>
        </select>
</div>
<div>
<input type='radio' name='type_select' id='activity_select' value='activity_select'>按活动库存同步<br/>
<input type='radio' name='type_select' id='execl_select' value='execl_select'>按手工EXCEL表格导入的库存同步
<div class="upload1">
    <div class="row form-actions actions-bar">
        <div class="offset3 ">            
                <a class="button" style="float: left;" target="_blank" href="<?php echo get_excel_url("op_activity_inv_update.xlsx",1) ?>">模版下载</a>
            <div style="float: left;margin-left: 10px;"><span id="J_Uploader" style="display: inline-block;"></span></div>
            <input type="hidden" name="upload_url" id="upload_url" value="" >
        </div>
        <div style="clear: all"></div>
    </div>
</div>
</div>
<div>
    <input type='button' class='button button-primary' id='inv_mession_start' disabled='true' value='执行库存同步'/>
</div>
<div class='inv_bottom'>注：此功能库存同步为全量覆盖，请注意天猫禁止全量同步日期</div>
</form>

<script type="text/javascript">
    $('#activity_select').click(function(){
        $("#inv_mession_start").attr('disabled',false); 
    });
    $('#execl_select').change(function(){ 
        $("#inv_mession_start").attr('disabled',true); 
        if($('.inv_shop').val() == '请选择'){
            BUI.Message.Alert('请先选择店铺', 'error');
        }else{
            $('.upload1').css("display","inline");
        }
    });
    $('#activity_select').change(function(){
        $('.upload1').css("display","none");
        if($('.inv_shop').val() == '请选择'){
            BUI.Message.Alert('请先选择店铺', 'error');
        }
    });
    
        var data_inv = new Array();
	BUI.use('bui/uploader', function (Uploader) {
        /**
         * 返回数据的格式
         *
         *  默认是 {url : 'url'},否则认为上传失败
         *  可以通过isSuccess 更改判定成功失败的结构
         */
        var url = '?app_act=op/op_activity_goods/import_goods&app_fmt=json';
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
                var params = {"url": result.url,"shop":$('.inv_shop').val()};
                $.post('?app_act=op/op_activity_goods/check_sku&app_fmt=json', params, function (data) {
                        var type = data.status == 1 ? 'success' : 'error';
                            if(type=='success'){
                                 data_inv = data.message;
                                 $("#upload_url").val(result.url);
                                 $("#inv_mession_start").attr('disabled',false); 
                            }else{
                                BUI.Message.Alert(data.message, type);
                                tableStore.load();
                            }
                    }, "json");  
            },
            //失败的回调
            error: function (result) {
                BUI.Message.Alert("失败", "error")
            }
        }).render();
    });
          $("#inv_mession_start").click(function(){
              
                    var url = $("#upload_url").val();
                    var r = '?app_act=op/op_activity_goods/inv_update&app_fmt=json';
                    if($("#upload_url").val() == null){
                        var params = {"type":$('input[name="type_select"]:checked ').val(),"inv_shop":$('.inv_shop').val(),"data":data_inv};
                    }
                    else{
                        var params = {"type":$('input[name="type_select"]:checked ').val(),"inv_shop":$('.inv_shop').val(),"url":url,"data":data_inv};
                    }
                    var msg = '';
                    $.post(r, params, function (data) {
                        var type = data.status == 1 ? 'success' : 'error';
                        if (type == 'success') {
                        BUI.Message.Alert('同步成功', type);
                        tableStore.load();
                        } else {
                        BUI.Message.Alert('同步失败', type);
                        tableStore.load();
                        }
                    }, "json");
          });
</script>              
</script>