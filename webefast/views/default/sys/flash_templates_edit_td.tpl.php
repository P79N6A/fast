<!DOCTYPE html>
<html>
<head>
    <title>套打设计</title>
    <meta charset="UTF-8">
    <style type="text/css" media="screen">
        html, body, #flashContent { height:100%; }
        body { margin:0; padding:0; overflow:hidden; }
    </style>
    <?php echo load_js('MyReport27TD/swfobject.js,MyReport27TD/jquery-1.9.1.min.js')?>
    <script type="text/javascript">
        //一下脚本用于动态创建swf节点
        var swfVersionStr = "11.1.0";
        var xiSwfUrlStr = "<?php echo CTX()->get_app_conf('common_http_url')?>js/MyReport27TD/playerProductInstall.swf";
        var flashvars = {};
        var params = {};
        params.quality = "high";
        //params.bgcolor = "#ffffff";//去掉背景色
        params.allowscriptaccess = "sameDomain";
        params.allowScriptAccess = "always";
        params.allowfullscreen = "true";
        var attributes = {};
        attributes.id = "MyReportTDDesignApp";
        attributes.name = "MyReportTDDesignApp";
        attributes.align = "middle";
        swfobject.embedSWF("<?php echo CTX()->get_app_conf('common_http_url')?>js/MyReport27TD/MyReportTDDesignApp.swf", "flashContent", "100%", "100%", swfVersionStr, xiSwfUrlStr, flashvars, params, attributes);
    </script>
    <script type="text/javascript">
        $(document).ready(function () {
            onPageLoad();
        });

        var myReportAPI; //定义MyReport接口对象
        var myReportInit = false; //定义MyReport初始化变量

        //页面加载完成时调用
        function onPageLoad() {
            myReportAPI = document.getElementById("MyReportTDDesignApp");
            loadReport1();
        }

        //自定义加载方法1
        function loadReport1() {
            if (!myReportInit) return; //要先判断插件是否初始化

            var url = "?app_act=sys/flash_templates/get_template_body&template_id=<?php echo $request['template_id'];?>"; //报表路径
            myReportLoad(url, <?php echo json_encode(array($response['fields']));?>, <?php echo json_encode(array($response['fields_detail']));?>);
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
            //alert("MyReport关闭。");
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
            $.post('?app_act=sys/flash_templates/save&template_id=<?php echo $request['template_id'];?>', {template_body: xml}, function (ret) {
                alert(ret.message);
            }, "json");
        }

        /**
         * 加载报表和数据
         * (js to flash：被动调用，必须在onMyReportInitialized执行后调用)
         * @param url: 报表格式路径
         * @param paramList: 报表参数数据（多份数据），Array或者null
         * @param tableList: 报表表格数据（多份数据），Array或者null
         */
        function myReportLoad(url, paramList, tableList) {
            if (!myReportAPI || !myReportInit)
                return;
            myReportAPI.loadReport(url, paramList, tableList);
        }
    </script>
</head>

<body>
    <?php include get_tpl_path('web_page_top'); ?>
<div id="flashContent">
    <p>您的flash插件版本过低，请安装11.1.0以上版本的Adobe Flash Player</p>
    <script type="text/javascript">
        var pageHost = ((document.location.protocol == "https:") ? "https://" : "http://");
        document.write("<a href='http://www.adobe.com/go/getflashplayer'><img src='"
        + pageHost + "www.adobe.com/images/shared/download_buttons/get_flash_player.gif' alt='Get Adobe Flash player' /></a>");
    </script>
</div>
</body>
</html>