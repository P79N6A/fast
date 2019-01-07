<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
       <title><?php echo $response['title'];?>打印预览</title>
           <meta charset="UTF-8">
    <style type="text/css" media="screen">
        html, body, #flashContent { height:100%; }
        body { margin:0; padding:0; overflow:hidden; }
    </style>
    <?php echo load_js('MyReport27/swfobject.js,MyReport27/jquery-1.9.1.min.js')?>
    <script type="text/javascript">
        var swfVersionStr = "11.1.0";
        var xiSwfUrlStr = "<?php echo CTX()->get_app_conf('common_http_url')?>js/MyReport27/playerProductInstall.swf";
        var flashvars = {};
        var params = {};
        params.quality = "high";
        params.bgcolor = "#ffffff";
        params.allowscriptaccess = "sameDomain";
        params.allowScriptAccess = "always";
        params.allowfullscreen = "true";
        var attributes = {};
        attributes.id = "MyReportApp";
        attributes.name = "MyReportApp";
        attributes.align = "middle";
        swfobject.embedSWF("<?php echo CTX()->get_app_conf('common_http_url')?>js/MyReport27/MyReportApp.swf", "flashContent", "100%", "100%", swfVersionStr, xiSwfUrlStr, flashvars, params, attributes);
    </script>
    <script type="text/javascript">
        $(document).ready(function () {
            onPageLoad();//该方法在myreport.js实现
        });
        var myReportAPI; //定义MyReport接口对象
var myReportInit = false; //定义MyReport初始化变量
 
//页面加载完成时调用
function onPageLoad(){
    myReportAPI = document.getElementById("MyReportApp");
    loadReport1();
}
function onMyReportInitialized(){
    myReportInit = true;
    //以下是自定义代码
    //alert("MyReport初始化。");
    //loadReport1();
    loadReport2();
}
function onMyReportClosed() {
    //以下是自定义代码
    alert("MyReport关闭。");
}
function onMyReportPrinted() {
    //以下是自定义代码
    //alert("MyReport打印。");
}
function myReportLoad(url, params, table) {
    if (!myReportAPI || !myReportInit)
        return;
    myReportAPI.loadReport(url, params, table);
}
 
//自定义加载方法1
function loadReport1() {
    if (!myReportInit)// 要先判断插件是否初始化
        return;
    var url = "<?php echo CTX()->get_app_conf('common_http_url')?>js/MyReport27/xml/ReportStyle1.xml"; //报表路径
    
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
    for (var i = 0; i < 25; i++){
        table.push({ID: i, 名称: "商品信息XXX 规格XXX 型号XXX", 数量: i+1, 金额: (i+1)*10, 日期: new Date()});
}
    myReportLoad(url, params, table);

}
 
//自定义加载方法1
function loadReport2() {
    if (!myReportInit)// 要先判断插件是否初始化
        return;
   var url = "?app_act=sys/flash_templates/get_template_body&template_id=<?php echo $request['template_id']?>"; //报表路径
    var list = <?php echo json_encode($response['data']); ?>;

            //var title = []
            //var tpl = []
          //  var record = []
        //    var detail = []

            for(var i in list) {
               // alert(i);
                //title.push("第"+i+"页")
                //tpl.push(url)
//                record.push(list[i]["record"])
//                detail.push(list[i]["detail"])
                      myReportLoad(url, list[i]["record"], list[i]["detail"]);
                      break;
                     
            }
      

}
 
    </script>
</head>
 
<body>
<?php include get_tpl_path('web_page_top'); ?>
<div id="flashContent">
    <p>
        To view this page ensure that Adobe Flash Player version
        11.1.0 or greater is installed.
    </p>
    <script type="text/javascript">
        var pageHost = ((document.location.protocol == "https:") ? "https://" : "http://");
        document.write("<a href='http://www.adobe.com/go/getflashplayer'><img src='"
                + pageHost + "www.adobe.com/images/shared/download_buttons/get_flash_player.gif' alt='Get Adobe Flash player' /></a>" );
    </script>
</div>
</body>
</html>