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
        attributes.id = "MyReportTDApp";
        attributes.name = "MyReportTDApp";
        attributes.align = "middle";
        swfobject.embedSWF("<?php echo CTX()->get_app_conf('common_http_url')?>js/MyReport27TD/MyReportTDApp.swf", "flashContent", "100%", "100%", swfVersionStr, xiSwfUrlStr, flashvars, params, attributes);
    </script>
    <script type="text/javascript">
        $(document).ready(function () {
            onPageLoad();
        });

        var myReportAPI; //定义MyReport接口对象
        var myReportInit = false; //定义MyReport初始化变量

        //页面加载完成时调用
        function onPageLoad() {
            myReportAPI = document.getElementById("MyReportTDApp");
            loadReport1();
        }

        //自定义加载方法1
        function loadReport1() {
            if (!myReportInit) return;//要先判断插件是否初始化

            var url = "?app_act=sys/flash_templates/get_template_body&template_id=<?php echo $request['template_id']?>"; //报表路径
            var params = <?php echo json_encode($response['data']);?>;
            var tableList = <?php echo json_encode($response['data_goods']);?>;
            //报表参数数据，这里为了测试方便使用了静态的数据，实际使用时应该向服务端动态请求数据。
            /**
             var params = {};
             //用中文名作为属性名称，便于设计时显示
             params["寄件人客户编码"] = "";
             params["寄件公司"] = "aaaaaaa有限公司";
             params["寄件联络人"] = "张三三";
             params["寄件地址"] = "广州xxx区xxxxx路xxx号xxxx";
             params["寄件区号"] = "100000";
             params["寄件联系电话"] = "18900000000";

             params["收件人客户编码"] = "";
             params["收件公司"] = "bbbbbbb有限公司";
             params["收件联络人"] = "李四四";
             params["收件地址"] = "深圳xxxxxxxxxx路xxx号xxxx";
             params["收件区号"] = "200000";
             params["收件联系电话"] = "13988888888";

             params["托寄物内容"] = "文件";
             params["托寄物数量"] = 1;

             params["原寄地"] = "广州";
             params["目的地"] = "深圳";

             params["寄方付"] = "√";
             params["收方付"] = "";
             */
                //只传一张单据数据
            myReportLoad(url, params, tableList);
        }

        /**
         * 加载完成时调用，通知外部初始化加载已完成
         * (flash to js：主动调用)
         */
        function onMyReportInitialized() {
            myReportInit = true;
            //以下是自定义代码
            //alert("MyReport初始化。");
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
            alert(xml);
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