<?php
render_control('PageHead', 'head1', array('title' => '商品信息'));
?>
<style>
    #panel_goodsinfo{border: 1px solid #cccccc;border-radius: 10px;}

    .goodsinfo-title{font:normal bold 18px/20px arial,sans-serif; margin: 10px 0 15px 0;}

    .chk_1 { 
        display: none; 
    } 
    .chk_1 + label {
        background-color: #FFF;
        border: 1px solid #C1CACA;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05), inset 0px -1px 1px -2px rgba(0, 0, 0, 0.05);
        padding: 9px;
        border-radius: 5px;
        display: inline-block;
        position: relative;
        margin-right: 3px;
        vertical-align: middle;
    }
    .chk_1 + label:active {
        box-shadow: 0 1px 2px rgba(0,0,0,0.05), inset 0px 1px 3px rgba(0,0,0,0.1);
    }

    .chk_1:checked + label {
        background-color: #ECF2F7;
        border: 1px solid #92A1AC;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05), inset 0px -15px 10px -12px rgba(0, 0, 0, 0.05), inset 15px 10px -12px rgba(255, 255, 255, 0.1);
        color: #243441;
    }

    .chk_1:checked + label:after {
        content: '\2714';
        position: absolute;
        top: 0px;
        left: 0px;
        color: #758794;
        width: 100%;
        text-align: center;
        font-size: 1.4em;
        padding: 1px 0 0 0;
        vertical-align: text-top;
    }

    .button-place-style{margin: 50px 0 0 38%}
</style>
<div>
    <div class="goods_info">
        <div class="goodsinfo-title">
            设置区
        </div>
        <div class="panel-body" id="panel_goodsinfo">
            <div style="margin-bottom:10px;">
                <input type="checkbox"  class="chk_1 single_goods_a_line" id="checkbox_a1_single_goods" <?php if ($response['single_goods_a_line'] == 1) { ?> checked="checked"   <?php } ?> value="single_goods"/>
                <label for="checkbox_a1_single_goods"></label>
                <label>每行一个商品</label>
            </div>
            <div width="100%">
                <?php foreach ($response['settable_goods_info'] as $r_k => $r_v) { ?>
                    <div style="float:left;width: 120px; margin-bottom: 10px;">
                        <input type="checkbox" class="chk_1" id="checkbox_a1_<?php echo $r_k; ?>" name="param-selecotr" value="<?php echo $r_k; ?>"/>
                        <label for="checkbox_a1_<?php echo $r_k; ?>"></label><label><?php echo $r_v; ?></label>
                    </div>
                <?php } ?>

            </div>
        </div>
    </div>
    <div>
        <div class="goodsinfo-title">
            预览区
        </div>
        <div class="control-group">
            <div class="controls  control-row-auto">
                <textarea name="" class="control-row4 input-large" id="input-goods-info" style="width:550px;resize:none;border-radius: 10px;"></textarea>
            </div>
        </div>
    </div>
    <div class="button-place-style">
        <button class="button button-primary button-submit">提交</button>
        <button class="button button-primary button-cancel">关闭</button>
    </div>
</div>

<script>
    $(function () {
        var print_templates_id = '<?php echo $response['print_templates_id']?>';
        var single_goods_a_line = '<?php echo $response['single_goods_a_line']?>';
        var input_goods_info = '<?php echo $response['input_goods_info']?>';
        var selected_goods_info = '<?php echo $response['selected_goods_info']?>';
        if(selected_goods_info != '' && input_goods_info != ''){
            var selected_goods_info_arr = selected_goods_info.split(',');
            for(var i = 0; i <= selected_goods_info_arr.length; i++ ){
                $("#checkbox_a1_" + selected_goods_info_arr[i]).attr("checked", "checked");
            }
            if(single_goods_a_line == 1){
                $("#checkbox_a1_single_goods").attr("checked", "checked");
            }
            $("#input-goods-info").val(input_goods_info);
        }
        
        $(".chk_1").change(function () {
            var content;
            var goods_info;
            if(this.value == 'single_goods') {return;}//屏蔽“每行一个商品”
            if ($("#checkbox_a1_" + this.value).prop("checked") !== true) {
                $("#checkbox_a1_" + this.value).removeAttr("checked");
                goods_info = $("#checkbox_a1_" + this.value).next().next().html();
                content = $("#input-goods-info").val();
                if (content.indexOf(goods_info) > -1) {
                    var newcontent = content.replace(eval("/{" + goods_info + "}/gi"), '');
                    $("#input-goods-info").val(newcontent);
                }
            } else {
                $("#checkbox_a1_" + this.value).attr("checked", "checked");
                goods_info = $("#checkbox_a1_" + this.value).next().next().html();
                content = $("#input-goods-info").val();
                if (content.indexOf(goods_info) === -1) {
                    content += "{" + goods_info + "} ";
                    $("#input-goods-info").val(content);
                }
            }
        });

        $(".button-cancel").click(function () {
            ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
        });

        $(".button-submit").click(function () {
            single_goods_a_line = $(".single_goods_a_line").prop("checked") === true ? 1 : 0;
            input_goods_info = $("#input-goods-info").val();
            $('input:checkbox[name=param-selecotr]:checked').each(function (i) {
                if (i == 0) {
                    selected_goods_info = $(this).val();
                } else {
                    selected_goods_info += ("," + $(this).val());
                }
            });
            var trimed_input_goods_info = input_goods_info.replace(/(^\s*)|(\s*$)/g, "");
            var reg = /\{(.*?)\}/g;
            var matched_info = input_goods_info.match(reg);
            if(matched_info == null && trimed_input_goods_info != ''){
                BUI.Message.Alert("预览区商品信息配置有误，请勿删除已选中的商品信息", "error");
                return;
            }
            var params = {single_goods_a_line: single_goods_a_line, selected_goods_info: selected_goods_info, input_goods_info: input_goods_info, print_templates_id : print_templates_id};
            $.post("?app_act=sys/express_tpl/save_goods_info", params, function (data) {
                var type = data.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    parent.BUI.Message.Alert(data.message, type);
                    ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
                } else {
                    BUI.Message.Alert(data.message, type);
                }
            }, "json")
        })
    });
</script>