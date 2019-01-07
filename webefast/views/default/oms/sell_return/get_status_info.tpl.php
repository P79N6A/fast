<style>
    body,div,a,p,ul,li,img,h1,h2,h3,h4,h5,h6,ol,table,tr,td,form,input,button{ margin:0; padding:0;font-family: "Microsoft YaHei","微软雅黑","Arial","宋体","Times New Roman",Times,serif;}
    a{ text-decoration:none;}
    li{ list-style:none;}
    img,input{ border:none;}
    .ordernode{ padding:5px 0 0; overflow:hidden;}
    .ordernode .adaptive{ width:100%; overflow:hidden;}
    .ordernode .adaptive li{ float:left; width:17.24%; height:27px; text-align:center; margin-left:-0.7%; position:relative;}
    .ordernode .nodes li{ background:url(assets/img/ordernode/nodebg02.png) no-repeat; background-size:100% 100%; font-size:15px; line-height:27px; color:#666; text-indent:2%;}
    .ordernode .nodes li:last-child{ background-image:url(assets/img/ordernode/nodebg03.png);}
    .ordernode .nodes li:first-child{ background-image:url(assets/img/ordernode/nodebg01.png); margin-left:0;}
    .ordernode .nodes li .icon{ display:inline-block; width:33px; height:27px; background:#333; position:absolute; left:14%; top:2;}
    .ordernode .nodes li .icon01{ background:url(assets/img/ordernode/icon01.png) no-repeat center 2px;}
    .ordernode .nodes li .icon02{ background:url(assets/img/ordernode/icon03.png) no-repeat center 2px;}
    .ordernode .nodes li .icon03{ background:url(assets/img/ordernode/icon07.png) no-repeat center 2px;}
    .ordernode .nodes li .icon04{ background:url(assets/img/ordernode/icon04.png) no-repeat center 2px;}
    .ordernode .nodes li .icon05{ background:url(assets/img/ordernode/icon09.png) no-repeat center 2px;}
    .ordernode .nodes li .icon06{ background:url(assets/img/ordernode/icon02.png) no-repeat center 2px;}
    .ordernode .nodes li .icon07{ background:url(assets/img/ordernode/icon05.png) no-repeat center 2px;}
    .ordernode .nodes li .icon08{ background:url(assets/img/ordernode/icon11.png) no-repeat center 2px;}
    .ordernode .nodes li.curr{ background-image:url(assets/img/ordernode/nodebgcurr02.png); color:#FFF;}
    .ordernode .nodes li.curr .icon{ background-position: center -85px;}
    .ordernode .nodes li.curr:last-child{ background-image:url(assets/img/ordernode/nodebgcurr03.png);}
    .ordernode .nodes li.curr:first-child{ background-image:url(assets/img/ordernode/nodebgcurr01.png);}
    .ordernode .nodes li.past{ background-image:url(assets/img/ordernode/nodebgpast02.png);}
    .ordernode .nodes li.past:last-child{ background-image:url(assets/img/ordernode/nodebgpast03.png);}
    .ordernode .nodes li.past:first-child{ background-image:url(assets/img/ordernode/nodebgpast01.png);}
    .date li{ font-size:14px; color:#999;}
    .date li.curr{ color:#1695ca};
</style>
<div class="ordernode">
    <ul class="adaptive nodes">
        <li><i class="icon icon01"></i><span>创建</span></li>
        <li><i class="icon icon02"></i><span>确认</span></li>
        <li><i class="icon icon03"></i><span>已上传WMS</span></li>
        <li><i class="icon icon04"></i><span>收货</span></li>
        <li><i class="icon icon05"></i><span>WMS收货</span></li>
        <li><i class="icon icon06"></i><span>退款</span></li>
        <li><i class="icon icon07"></i><span>完成</span></li>
        <li><i class="icon icon08"></i><span>作废</span></li>
    </ul> 
    <ul class="adaptive date">
        <?php foreach ($response['data']['status_info'] as $val): ?>
            <li><span><?php echo $val['time'][0]; ?></span> <span><?php echo $val['time'][1]; ?></span></li>
        <?php endforeach; ?>
    </ul>
</div>
<script>
    $(function () {
        show_status_info(<?php echo count($response['data']['status_info']); ?>);
    });

    function show_status_info(i) {
        var order_status_arr = $.parseJSON('<?php echo json_encode($response['data']['status_info']); ?>');
        var is_invalid = $.parseJSON('<?php
        $response['data']['is_invalid'] = isset($response['data']['is_invalid']) ? $response['data']['is_invalid'] : '';
        echo json_encode($response['data']['is_invalid']);
        ?>');
        var is_wms = "<?php echo $response['data']['is_wms']; ?>";

        var return_type = "<?php echo isset($response['data']['baseinfo']['return_type']) ? $response['data']['baseinfo']['return_type'] : ''; ?>";
        var order_key = new Array();
        var i = 0;
        $.each(order_status_arr, function (k, value) {
            order_key[i] = parseInt(k);
            i++;
        });
        var nodes_arr = $(".nodes li");

        if (is_invalid == 1) {
            $.each(nodes_arr, function (k, value) {
                var index = $.inArray(k + 1, order_key);
                if (index < 0) {
                    nodes_arr[k].remove();
                }
            });
        } else {
            if (return_type == 1) {
                nodes_arr[2].remove();
                nodes_arr[3].remove();
                nodes_arr[4].remove();
                nodes_arr[7].remove();
            }
            if (return_type == 2) {
                if (is_wms == 1) {
                    nodes_arr[3].remove();
                } else {
                    nodes_arr[2].remove();
                    nodes_arr[4].remove();
                }
                nodes_arr[5].remove();
                nodes_arr[7].remove();
            }
            if (return_type == 3) {
                if (is_wms == 1) {
                    nodes_arr[3].remove();
                } else {
                    nodes_arr[2].remove();
                    nodes_arr[4].remove();
                }
                nodes_arr[7].remove();
            }
        }

        if ($(".nodes li").length == 7) {
            $(".adaptive li").css("width", "14.88%");
        } else if ($(".nodes li").length == 8) {
            $(".adaptive li").css("width", "13.11%");
        } else if ($(".nodes li").length == 5) {
            $(".adaptive li").css("width", "20.55%");
        } else if ($(".nodes li").length == 4) {
            $(".adaptive li").css("width", "25.52%");
        } else if ($(".nodes li").length == 3) {
            $(".adaptive li").css("width", "33.79%");
        } else if ($(".nodes li").length == 2) {
            $(".adaptive li").css("width", "33.79%");
        } else if ($(".nodes li").length == 1) {
            $(".adaptive li").css("width", "33.79%");
        }
        var obj_arr = $(".nodes li");
        var obj = obj_arr.eq(i - 1);

        obj.addClass("curr").siblings().removeClass("curr");
        obj.prevAll().addClass("past").end().removeClass("past").nextAll().removeClass("past");
        $(".date li").eq(i - 1).addClass("curr").siblings().removeClass("curr");
    }
</script>