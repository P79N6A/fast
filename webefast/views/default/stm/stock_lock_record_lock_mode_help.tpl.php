<style>
    .content{margin-top:10px;}
    .panel-body{border:1px solid #dddddd;padding-left: 30px;}
    .panel-header{border-radius: 0}
    .p-child{margin-left: 40px;}
    .key-point{color: #00A1DF;text-decoration: underline;}
    .tips{margin-top: 20px;}
    .multi{margin-top: -10px;}
</style>
<div class="content">
    <div class="panel">
        <div class="panel-header">
            <h3>场景一：库存策略为单供货仓库</h3>
        </div>
        <div class="panel-body">
            <p>
                <b>前提：</b>天猫店A，淘宝店B，商品SP001，电商仓库存100件（同步比例100%）
            </p>
            <p>
                <b>操作：</b>天猫店A新增锁定单，商品SP001锁定库存55，则该商品在电商仓剩余库存45
            </p>
            <p>
                <b>锁定：</b>
            </p>
            <p class="p-child">
                模式1：策略为<span class="key-point">全局模式</span>时将天猫店A-商品SP001的同步比例设为0；<span class="key-point">仓库模式</span>时将天猫店A-电商仓-商品SP001的同步比例设为0
            </p>
            <p class="p-child">
                模式2：不更新库存同步策略同步比例
            </p>
            <p>
                <b>同步：</b>
            </p>
            <p class="p-child">
                模式1：天猫店A同步库存=55，淘宝店B同步库存=45
            </p>
            <p class="p-child">
                模式2：天猫店A同步库存=55 + 45，淘宝店B同步库存=45
            </p>
        </div>
    </div>
    <div class="panel multi">
        <div class="panel-header">
            <h3>场景二：库存策略为多供货仓库</h3>
        </div>
        <div class="panel-body">
            <p>
                <b>前提：</b>天猫店A，淘宝店B，商品SP001，电商仓1库存100件（同步比例100%），电商仓2库存120件（同步比例100%）
            </p>
            <p>  
                <b>操作：</b>天猫店A新增锁定单，仓库为电商仓1，商品SP001锁定库存55，则该商品在电商仓1剩余库存为45
            </p>
            <p>
                <b>锁定：</b>
            </p>
            <p class="p-child">
                模式1：策略为<span class="key-point">全局模式</span>时将天猫店A-商品SP001的同步比例设为0；<span class="key-point">仓库模式</span>时将天猫店A-电商仓1-商品SP001的同步比例设为0
            </p>
            <p class="p-child">
                模式2：不更新库存同步策略同步比例
            </p>
            <p>
                <b>同步：</b>
            </p>
            <p class="p-child">
                模式1：策略为全局模式时，天猫店A同步库存=55，淘宝店B同步库存=45+120；仓库模式时，天猫店A同步库存=55+120，淘宝店B同步库存=45+120；
            </p>
            <p class="p-child">
                模式2：天猫店A同步库存=55+45+120，淘宝店B同步库存=45+120
            </p>
        </div>
    </div>

    <div class="tips tips-small tips-notice">
        <span class="x-icon x-icon-small x-icon-warning"><i class="icon icon-white icon-volume-up"></i></span>
        <div class="tips-content">锁定模式1更新商品同步比例时，请注意：
            <p>1、库存同步策略全局模式下，以 <店铺 + 商品> 维度更新同步比例</p>
            <p>2、库存同步策略仓库模式下，以 <店铺 + 仓库 + 商品> 维度更新同步比例</p>
        </div>
    </div>
</div>

