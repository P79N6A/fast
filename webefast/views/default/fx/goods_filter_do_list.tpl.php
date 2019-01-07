<style>
    .panel-body {
        padding-top: 1px;
    }

    form.form-horizontal {
        position: relative;
        padding: 5px 0px 5px;
        overflow: hidden;
    }

    .form-horizontal .control-label {
        width: auto;
    }

    .span8 {
        width: auto;
    }

    .control-text {
        font-size: 25px;
    }

    .spant {
        margin-right: 9px;
    }

    .offset3 .span6 {
        font-size: 20px;
        font-weight: bold;
    }

    .offset3 .span6 span {
        color: #2ca02c;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '分销多品牌权限设置',
    'links' => array(),
    'ref_table' => 'table'
));
?>
<form class="form-horizontal">
    <div class="panel">
        <div class="panel-body">
            <div class="row">
                <div class="control-group span8 spant">
                    <label class="control-label" style="height: 46px;line-height: 46px;">权限设置:</label>
                    <div class="controls">
                        <textarea  class="control-text" value="<?php echo $response['data']['filter_code'];?>" style="width:150%;height:120px;font-weight:bold;font-size:20px;" id="filter_code"><?php echo $response['data']['filter_code'];?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row form-actions actions-bar">
        <div class="span13 offset3 ">
            <button type="button" class="button button-primary" style="margin-left: 10px;" id="save">保存</button>
        </div>
    </div>
    <div class="row" style="color: #ff0033">
        <span>说明</span><br/><br/>
        <span>1、请设置授权给分销商的品牌，分销商若代理多品牌商品，系统仅下载设置的品牌商品数据；</span><br/><br/>
        <span>2、设置品牌关键字后，需要分销商在销售平台上对销售商品也要设置关键字；
比如品牌方仅授权A品牌，但是分销商代理了A品牌，B品牌，需要分销商在A品牌商品标题上加上A关键字，系统处理时仅处理A品牌商品。多品牌，分号；隔开。</span>
    </div>
</form>
<script>
    $("#save").click(function () {
        var filter_code = $("#filter_code").val();
        var params = {filter_code: filter_code};
        $.post("?app_act=fx/goods_filter/save_filter_code", params, function (data) {
            if (data.status == 1) {
                BUI.Message.Alert('修改成功！', function () {
                    $("#filter_code").text(filter_code);
                }, 'success');
            } else {
                BUI.Message.Alert(data.message, 'error');
            }
        }, "json");
    });
</script>

