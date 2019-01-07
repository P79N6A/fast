<!DOCTYPE html>
<html>
<head>
    <title><?php echo $response['title'];?>打印</title>
    <?php echo load_js('MyReport27/swfobject.js,MyReport27/jquery-1.9.1.min.js')?>
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
        attributes.id = "MyReportPrintApp";
        attributes.name = "MyReportPrintApp";
        attributes.align = "middle";
        swfobject.embedSWF("<?php echo CTX()->get_app_conf('common_http_url')?>js/MyReport27/MyReportPrintApp.swf", "flashContent", "10px", "10px", swfVersionStr, xiSwfUrlStr, flashvars, params, attributes);
    </script>
    <script type="text/javascript">
        $(document).ready(function () {
            onPageLoad();
        });

        ////////////////////////////////////////////////////////////////////////////////
        //
        //  MyReport打印控件接口 v1.0
        //  Copyright 蔡小汉(Hunk Cai)
        //  All Rights Reserved.
        //
        ////////////////////////////////////////////////////////////////////////////////

        var myReportAPI; //定义MyReport接口对象
        var myReportInit = false; //定义MyReport初始化变量

        //页面加载完成时调用
        function onPageLoad() {
            myReportAPI = document.getElementById("MyReportPrintApp");
            setTimeout(loadAndPrint2, 500);
        }

        //自定义打印方法1
        function loadAndPrint1(){
            var url = "<?php echo CTX()->get_app_conf('common_http_url')?>js/MyReport27/xml/ReportStyle1.xml"; //报表模板请求地址

            //报表参数数据，这里为了测试方便使用了静态的数据，实际使用时应该向服务端动态请求数据。
            var params = {};
            params["单据编号"] = "KA06417033944";
            params["单据日期"] = new Date();
            params["主标题"] = "销售单";
            params["公司名称"] = "XXXX贸易公司";
            params["经手人"] = "某某某";
            params["公司地址"] = "广州市天河区天河路xx号 xx大厦 xx楼";
            params["公司电话"] = "66866888";
            params["公司"] = { "地址": "广州市天河区天河路xx号 xx大厦 xx楼", "电话": "66866888" };

            //报表表格数据，这里为了测试方便使用了静态的数据，实际使用时应该向服务端动态请求数据。
            var table = new Array();
            for (var i = 0; i < 25; i++) {
                table.push({ ID: i, 名称: "商品信息XXX 规格XXX 型号XXX", 数量: i + 1, 金额: (i + 1) * 10, 日期: new Date() });
            }

            myReportLoadAndPrint(url,params,table);
        }

        //自定义打印方法2
        function loadAndPrint2(){
            /*
            //报表模板请求地址
            var url = "xml/ReportStyle1.xml";

            //报表参数数据，这里为了测试方便使用了静态的数据，实际使用时应该向服务端动态请求数据。
            var params = {};
            params["单据编号"] = "KA06417033944";
            params["单据日期"] = new Date();
            params["主标题"] = "销售单";
            params["公司名称"] = "XXXX贸易公司";
            params["经手人"] = "某某某";
            params["公司地址"] = "广州市天河区天河路xx号 xx大厦 xx楼";
            params["公司电话"] = "66866888";
            params["公司"] = { "地址": "广州市天河区天河路xx号 xx大厦 xx楼", "电话": "66866888" };

            //报表表格数据，这里为了测试方便使用了静态的数据，实际使用时应该向服务端动态请求数据。
            var table = new Array();
            for (var i = 0; i < 25; i++) {
                table.push({ ID: i, 名称: "商品信息XXX 规格XXX 型号XXX", 数量: i + 1, 金额: (i + 1) * 10, 日期: new Date() });
            }

            //连打2份数据
            myReportLoadAndPrint2(url,[params, params],[table, table]);*/

            var url = "?app_act=sys/flash_templates/get_template_body&template_id=<?php echo $request['template_id']?>"; //报表路径

            var list = <?php echo json_encode($response['data']); ?>;

            //var title = []
            //var tpl = []
            var record = []
            var detail = []

            for(var i in list) {
                //title.push("第"+i+"页")
                //tpl.push(url)
                record.push(list[i]["record"])
                detail.push(list[i]["detail"])
            }

            myReportLoadAndPrint2(url, record, detail);
        }

        /**
         * 加载完成时调用，通知外部初始化加载已完成
         * (flash to js：主动调用)
         */
        function onMyReportInitialized() {
            myReportInit = true;
            //以下是自定义代码
            //alert("MyReport初始化。");
        }

        /**
         * 打印时调用，通知外部执行了打印功能
         * (flash to js：主动调用)
         */
        function onMyReportPrinted() {
            //以下是自定义代码
            alert("<?php echo $response['title'];?>打印完成。");
            window.close()
        }

        /**
         * 加载报表和数据并且打印
         * (js to flash：被动调用，必须在onMyReportInitialized执行后调用)
         * @param url: 报表格式路径
         * @param params: 报表参数数据（多份数据），Object或者null
         * @param table: 报表表格数据（多份数据），Array或者null
         */
        function myReportLoadAndPrint(url, params, table) {
            if (!myReportAPI || !myReportInit) {
                alert("打印控件未就绪！")
                return;
            }
            myReportAPI.loadAndPrint(url, params, table);
        }

        var rt = 0;

        /**
         * 加载报表和数据并且打印
         * (js to flash：被动调用，必须在onMyReportInitialized执行后调用)
         * @param url: 报表格式路径
         * @param paramList: 报表参数数据（多份数据），Array
         * @param tableList: 报表表格数据（多份数据），Array
         */
        function myReportLoadAndPrint2(url, paramList, tableList) {
            if (!myReportAPI || !myReportInit) {
                rt++;
                // alert("打印控件未就绪, 重试:"+rt)
                if(rt < 2) {
                    setTimeout(function(){
                        myReportLoadAndPrint2(url, paramList, tableList)
                    }, 500);
                }
                return;
            }
            myReportAPI.loadAndPrint2(url, paramList, tableList);
        }
    </script>
</head>

<body>
    <?php include get_tpl_path('web_page_top'); ?>
<!--<p style=" text-align:center">该示例演示在页面嵌入MyReport打印控件，使用js与flash插件进行交互，实现js直接打印</p>

<div style=" text-align:center"><button type="button" onclick="loadAndPrint1()">直接打印</button> </div>
<br/>
<div style=" text-align:center"><button type="button" onclick="loadAndPrint2()">直接打印(连打)</button> </div>
-->
<div id="flashContent" style="visibility: hidden">

</div>
</body>
</html>