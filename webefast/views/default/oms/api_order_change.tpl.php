<?php echo load_js("baison.js",true);?>
<?php echo load_js("business/order/order_translate.js",true);?>
<style>
#search_platform li{
    float:left;
    list-style:none;
    width:120px;
}

.api_button{
    width:120px;
    height:50px;
}

.api_button_operate{
    width:500px;
    margin:auto;
}
</style>
<div class="search">
    <label class="control-label">销售平台</label>
    <div id="search_platform">
        <li><input type="checkbox" checked name="all_platform" onclick="check_box_choose(jQuery('#search_platform'))">全部</li>
        <li class="platform_taobao"><input type="checkbox" checked name="platform" value="taobao">淘宝</li>
        <li class="platform_jingdong"><input type="checkbox" checked name="platform" value="jingdong">京东</li>
        <li class="platform_weipinhui"><input type="checkbox" checked name="platform" value="weipinhui">唯品会</li>
        <li class="platform_mogujie"><input type="checkbox" checked name="platform" value="mogujie">蘑菇街</li>
        <li class="platform_jumei"><input type="checkbox" checked name="platform" value="jumei">聚美</li>
    </div>
    <br />
    <br />
    <label class="control-label">店铺</label>
    <div id="search_shop">
        <input type="checkbox" checked name="shop_all" onclick="check_box_choose(jQuery('#search_shop'))">全部
        <?php
            $source = array("taobao","jingdong","weipinhui","mogujie","jumei");
            foreach ($source as $s){
                echo "<li class='shop_".$s."'>";
                echo "<img src='assets/images/".$s."_ico.png' style='width:32px;height:32px;'>";
                foreach ($response['shop_api'] as $shop_api){
                    if($shop_api['source'] == $s){
                        echo "<input checked name='shop_code' value='".$shop_api['shop_code']."' type='checkbox'>";
                        echo get_shop_name_by_code($shop_api['shop_code']);
                    }
                }
                echo "</li>";
            }
        ?>
    </div>
</div>
<br />
<div class="api_button_operate">
<!-- <input type="button" class="api_button" value="后台下载" onclick="down()"> -->
<input type="button" class="api_button" id="change_button" value="转销售单" onclick="task_change()">
<!-- <input type="button" class="api_button" value="暂停" onclick="pause()">
<input type="button" class="api_button" value="继续" onclick="goon()"> -->
</div>
<div id="loading" style="text-align: center;">

</div>
<div id="result" style="display: none;">

</div>