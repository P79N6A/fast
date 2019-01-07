<style>
	#J_indexTree{ background:#f6f6f6;}
    .tb_index_message{ width: 150px;}
    .tb_index_message tr th{width:50%; text-align: right; font-size:1.2em; font-weight: bold; padding: 10px 5px;}
    .tb_index_message tr td{width:50%; padding: 10px 5px;}
	
	.message{ display:block; width:31px; height:31px; border:2px solid #1695ca; border-radius:100%; position:absolute; top:0; right:-2px; background:#FFF; text-align:center; line-height:31px; font-weight:normal; font-size:16px;}
</style>
<?php if($response['login_type'] != 2) { ?>
<ul class="bui-side-menu bui-menu" aria-disabled="false" aria-pressed="false" style="overflow: hidden;">
<li class="bui-menu-item menu-second" aria-disabled="false" data-id="menu-item3" aria-pressed="false">
    <div class="bui-menu-title"><s></s>
        <span class="bui-menu-title-text">快捷菜单</span>
    </div>
    <ul class="bui-menu" aria-disabled="false" aria-pressed="false">
        <?php 
            foreach ($response['shortcut_menu'] as $key => $value) {
            ?>
            <li class="bui-menu-item menu-leaf" aria-disabled="false" data-id="oms/sell_record/do_list" aria-pressed="false">
                <a href="javascript:openPage('indexShortUr<?php echo $value['action_id']?>','?app_act=<?php echo $value['action_code']?>','<?php echo $value['action_name']?>');"><em><?php echo $value['action_name']?></em></a>
            </li>
        <?php
             }
        ?>
<!--        <li class="bui-menu-item menu-leaf" aria-disabled="false" data-id="oms/sell_record/do_list" aria-pressed="false">
            <a href="javascript:openPage('indexShortUrl1','?app_act=oms/sell_record/do_list','订单查询');"><em>订单查询</em></a>
        </li>
        <li class="bui-menu-item menu-leaf" aria-disabled="false" data-id="prm/inv/do_list" aria-pressed="false">
            <a href="javascript:openPage('indexShortUrl2','?app_act=prm/inv/do_list','库存查询');"><em>库存查询</em></a>
        </li>
        <li class="bui-menu-item menu-leaf" aria-disabled="false" data-id="prm/inv/do_list" aria-pressed="false">
            <a href="javascript:openPage('indexShortUrl2','?app_act=sys/echarts/view','双十一看板');"><em>双十一看板</em></a>
        </li>
        <li class="bui-menu-item menu-leaf" aria-disabled="false" data-id="oms/sell_return/do_list" aria-pressed="false">
            <a href="javascript:openPage('indexShortUrl2','?app_act=oms/sell_return/after_service_list','售后服务单');"><em>退单查询</em></a>
        </li>-->
    </ul>
</li>
</ul>
<?php } ?>