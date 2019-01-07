<style>
    .power{
        width: 100%;
        height: 10%;
        position:absolute;
        top:40%;
        margin-left: -50px;
        text-align: center;
        font:400 large serif;
    }
    .tips_notes{
        font:small large serif !important;
        line-height: 30px !important;
        text-align: left !important;
    }
    .tips{
        position:absolute;
        top: 60%;
        left: 13%;
        width: 70%;
    }
</style>
<div class="power">
    <span><?php echo $response['tips']; ?></span>
    <span><?php echo empty($response['tips_name']) ? '' : ' >>> '; ?></span>
    <span><a href="javascript:openPage(window.btoa('<?php echo $response['tips_url']; ?>'), '<?php echo $response['tips_url']; ?>', '<?php echo $response['tab_name'] ?>');ui_closeTabPage('<?php echo $request['ES_frmId'] ?>');"><?php echo $response['tips_name']; ?></a></span>
</div>
<?php if ($response['tips_type'] == 'presell'): ?>
    <div class="tips tips-small tips-notice">
        <span class="x-icon x-icon-small x-icon-warning"><i class="icon icon-white icon-volume-up"></i></span>
        <div class="tips-content tips_notes">
            温馨提示：<br>
            系统支持2种模式识别预售单<br>
            1、在平台商品名称或者规格中加上‘预售’关键字，对应订单进入系统标识为预售单（以平台为准）<br>
            2、开启预售计划，维护预售商品以及预售库存，可以实现绑定店铺链接以及同步预售库存至平台，对应订单进入系统标识为预售单（以系统为准）
            若一笔订单包含预售商品和非预售商品，系统可以自动拆单【需要开启参数：转单自动预售拆单】</p>
        </div>
    </div>
<?php endif; ?>