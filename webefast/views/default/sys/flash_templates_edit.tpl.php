<!DOCTYPE html>
<html>
<head>
    <title>报表打印设计</title>
    <meta charset="UTF-8">
    <style type="text/css" media="screen">
        html, body, #flashContent { height:100%; }
        body { margin:0; padding:0; overflow:hidden; }
        .nav-tabs{ padding-top:10px; margin-bottom:10px;}
    </style>
    <link href="assets/css/dpl.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/bui.css" rel="stylesheet" type="text/css" />
    <?php echo load_js('MyReport27/swfobject.js,MyReport27/jquery-1.9.1.min.js,base64.js,bui/bui.js')?>
    <script type="text/javascript">
        //一下脚本用于动态创建swf节点
        var swfVersionStr = "11.1.0";
        var xiSwfUrlStr = "<?php echo CTX()->get_app_conf('common_http_url')?>js/MyReport27/playerProductInstall.swf";
        var flashvars = {};
        var params = {};
        params.quality = "high";
        //params.bgcolor = "#ffffff";//去掉背景色
        params.allowscriptaccess = "sameDomain";
        params.allowScriptAccess = "always";
        params.allowfullscreen = "true";
        var attributes = {};
        attributes.id = "MyReportDesignApp";
        attributes.name = "MyReportDesignApp";
        attributes.align = "middle";
        swfobject.embedSWF("<?php echo CTX()->get_app_conf('common_http_url')?>js/MyReport27/MyReportDesignApp.swf", "flashContent", "100%", "100%", swfVersionStr, xiSwfUrlStr, flashvars, params, attributes);
    </script>
    <script type="text/javascript">
        $(document).ready(function () {
            onPageLoad();
        });

        var myReportAPI; //定义MyReport接口对象
        var myReportInit = false; //定义MyReport初始化变量

        //页面加载完成时调用
        function onPageLoad() {
            myReportAPI = document.getElementById("MyReportDesignApp");
            loadReport1();
        }

        //自定义加载方法1
        function loadReport1() {
            if (!myReportInit) return; // 要先判断插件是否初始化

            var url = "?app_act=sys/flash_templates/get_template_body&template_id=<?php echo $request['template_id'];?>"; //报表路径
            myReportLoad(url, <?php echo json_encode($response['fields']['record']);?>, <?php echo json_encode($response['fields']['detail']);?>);
        }

        /**
         * 加载完成时调用，通知外部初始化加载已完成
         * (flash to js：主动调用)
         */
        function onMyReportInitialized() {
            myReportInit = true;
            //以下是自定义代码
            loadReport1();
        }

        /**
         * 关闭时调用，通知外部点击了关闭按钮
         * (flash to js：主动调用)
         */
        function onMyReportClosed() {
            //以下是自定义代码
            alert("MyReport关闭。");
        }

        /**
         * 打印时调用，通知外部执行了打印功能
         * (flash to js：主动调用)
         */
        function onMyReportPrinted() {
            //以下是自定义代码
            //alert("MyReport打印。");
        }

        /**
         * 保存时调用，通知外部执行了保存功能
         * (flash to js：主动调用)
         */
        function onMyReportSave(xml) {
            //以下是自定义代码
            //alert(xml);
            //var b = new Base64();
            //var str = b.encode(xml);alert(str);
            $.post('?app_act=sys/flash_templates/save&template_id=<?php echo $request['template_id'];?>', {template_body: xml}, function (ret) {
                alert(ret.message);
            }, "json");
        }

        /**
         * 加载报表和数据
         * (js to flash：被动调用，必须在onMyReportInitialized执行后调用)
         * @param url: 报表格式路径
         * @param params: 报表参数数据，Object或者null
         * @param table: 报表表格数据，Array或者null
         */
        function myReportLoad(url, params, table) {
            if (!myReportAPI || !myReportInit) return;

            myReportAPI.loadReport(url, params, table);
        }
    </script>
</head>

<body>
  <?php include get_tpl_path('web_page_top'); ?>
<!--   <?php 
  
//   if(isset($request['tabs'])&&isset($response['tabs'][$request['tabs']])):
   ?>
 <ul class="nav-tabs oms_tabs">
     <?php // foreach($response['tabs'][$request['tabs']] as $key=>$tab):?>
     <?php // if($key==$request['template_code']):?> <li class="active"><a href="#"> <?php // else:?>   <li ><a href="?app_act=<?php // echo $tab['url'];?> "  >
    <?php // endif;?>  
              <?php // echo $tab['title'];?></a></li>
   <?php // endforeach;?>  
             <div onclick="window.open(window.location.href);">弹出页面</div>
             
             <div onclick="$('.oms_tabs').hide();">隐藏选项卡</div>
  </ul>
 <?php // endif;?>   -->
<div id="flashContent">
    <p>您的flash插件版本过低，请安装11.1.0以上版本的Adobe Flash Player</p>
    <script type="text/javascript">
        var pageHost = ((document.location.protocol === "https:") ? "https://" : "http://");
        document.write("<a href='http://www.adobe.com/go/getflashplayer'><img src='"
        + pageHost + "www.adobe.com/images/shared/download_buttons/get_flash_player.gif' alt='Get Adobe Flash player' /></a>");
    </script>
</div>
</body>
</html>