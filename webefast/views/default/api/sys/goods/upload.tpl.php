<?php echo load_js("baison.js",true);?>
<?php echo load_js("business/goods/upload.js",true);?>
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

#goods_status li{
    float:left;
    width:120px;
    list-style:none;
    display:block;
}

#goods_inv_status li{
    float:left;
    width:120px;
    list-style:none;
    display:block;
}
</style>
<script>
jQuery(function(){
	BUI.use('bui/calendar',function(Calendar){
    	var datepicker = new Calendar.DatePicker({
	    	trigger:'.calendar',
	    	autoRender : true
    	});
   });   
})
</script>
<div class="search">    
    <label class="control-label">商品状态</label>
    <div id="goods_status">
        <li><input type="radio" checked name="status" value="all">全部</li>
        <li><input type="radio" name="status" value="onsale">在售</li>
        <li><input type="radio" name="status" value="inv">在库</li>
    </div>
    <br /><br />
    <label class="control-label">商品</label>
    <div id="goods_inv_status">
        <li><input type="radio" checked name="goods_inv_status" value="all">全部商品</li>
        <li><input type="radio" name="goods_inv_status" value="change">有库存变更的商品</li>
    </div>
    <br /><br />
    <label class="control-label">销售平台</label>
    <div id="search_platform">
        <li><input type="checkbox" checked name="all_platform" onclick="check_box_choose(jQuery('#search_platform'))">全部</li>
        <li class="platform_taobao"><input type="checkbox" checked name="platform" value="taobao">淘宝</li>
        <li class="platform_jingdong"><input type="checkbox" checked name="platform" value="jingdong">京东</li>
        <li class="platform_weipinhui"><input type="checkbox" checked name="platform" value="weipinhui">唯品会</li>
    </div>
    <br />
    <br />
    <label class="control-label">店铺</label>
    <div id="search_shop">
        <input type="checkbox" checked name="shop_all" onclick="check_box_choose(jQuery('#search_shop'))">全部
        <?php 
            $source = array("taobao","jingdong","weipinhui");
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
<input type="button" class="api_button" id="down_button" value="库存同步" onclick="upload_goods_inv()">
<!-- <input type="button" class="api_button" value="暂停" onclick="pause()">
<input type="button" class="api_button" value="继续" onclick="goon()"> -->
</div>
<div id="loading" style="text-align: center;">

</div>
<div id="result" style="display: none;">

</div>