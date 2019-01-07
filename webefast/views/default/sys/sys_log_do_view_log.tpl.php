<style>
    .data_show{
        height: 390px;
        border-bottom: solid 1px #E5EBEE;
        border-radius: 0;
        resize: none;
        overflow-y: scroll;
        outline: none;
        position: relative;
        font-size: 12px;
        font-family: Menlo,Monaco,Consolas,'微软雅黑', monospace, Arial,sans-serif,'黑体';
        margin-top: 10px;
    }

</style>

<span>日志ID：<?php echo $response['data']['id']; ?></span>
<br><br>
<input type="button" class="button button-primary" id="params" value="请求参数"/>
<input type="button" class="button button-primary" id="php_post_data" value="请求业务参数"/>
<input type="button" class="button button-primary" id="return_data" value="返回参数"/>

<div class="data_show" id="php_return_data">
    <xmp>
        <?php echo $response['data']['return_data']; ?>
    </xmp>
</div>
<div class="data_show" id="post_data">
    <xmp>
        <?php echo $response['data']['post_data']; ?>
    </xmp>
</div>
<div class="data_show" id="php_params" >
    <xmp>
        <?php echo $response['data']['params']; ?>
    </xmp>
</div>

<input type="button" class="button button-primary" id="close_pop" value="关闭"/>
<script>
//    var params = '<?php // echo $response['data']['params'];   ?>';
//    var return_data = '<?php // echo $response['data']['return_data'];   ?>';
    $(function () {
        $('#php_params').show();
        $('#php_return_data').hide();
        $('#post_data').hide();
        $('#params').click(function () {
            $('#php_params').show();
            $('#post_data').hide();
            $('#php_return_data').hide();
//            data_show(params);
        });
        $('#return_data').click(function () {
            $('#php_params').hide();
            $('#post_data').hide();
            $('#php_return_data').show();
//            data_show(return_data);
        });
        $('#php_post_data').click(function () {
            $('#php_params').hide();
            $('#post_data').show();
            $('#php_return_data').hide();
        });
        $('#close_pop').click(function () {
            ui_closePopWindow('<?php echo $request['ES_frmId'] ?>');
        });
    })
    /*function data_show(data) {
     for(var p in data){
     console.log(data[p]);
     //            str = str+obj[p]+',';
     //            return str;
     }
     }*/

</script>