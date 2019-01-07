<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>订单流程</title>
<style>
/*reset*/
body,div,p,h1,h2,h3,h4,h5,h6,a,ul,li,ol,span,img,input,marquee{ margin:0; padding:0;font-family: "Microsoft YaHei","微软雅黑","Arial","宋体","Times New Roman",Times,serif;}
li{ list-style:none;}
a,a:hover,a:focus,a:active{ text-decoration:none;}
input,img{ border:none;}

.flowchart_wrap{ min-width:960px;}
.flowchart_wrap .ddcl_wrap{ padding:16px 3% 32px; border:1px dashed #d8d8d8; margin-top:20px; position:relative;}
.flowchart_wrap .ddcl_wrap .title{ display:block; width:96px; height:26px; line-height:24px; text-align:center; color:#FFF; background:#3e4b59; position:absolute; left:-1px; top:-1px;}
.flowchart_wrap .ddcl_wrap .remark_icon{ display:inline-block; width:74px; height:54px; background:url(assets/images/remark_icon.png) no-repeat; position:absolute; left:14.3%; bottom:10px;}
.flowchart_wrap .ddcl_wrap .ddcl{ width:100%; height:122px; position:relative;}
.flowchart_wrap .ddcl_wrap .ddcl .line{ width:100%; height:3px; background:#1695ca; position:absolute; left:0; top:70px; z-index:1;}
.flowchart_wrap .ddcl_wrap .ddcl .pane{ width:34%; height:81px; border:3px solid #1695ca; background:#FFF; position:absolute; top:28px; right:20%; z-index:2; border-radius:10px;}
.flowchart_wrap .ddcl_wrap .ddcl .strat{ display:block; width:72px; height:72px; line-height:70px; text-align:center; color:#333; font-size:22px; border:3px dashed #1695ca; background:#f1fbff; border-radius:50%; position:absolute; left:0; bottom:11px; z-index:3;}
.flowchart_wrap .ddcl_wrap .ddcl .end{ display:block; width:72px; height:72px; line-height:70px; text-align:center; color:#999; font-size:22px; border:3px dashed #d8d8d8; background:#fafafa; border-radius:50%; position:absolute; right:-6px; bottom:11px; z-index:3;}
.flowchart_wrap .ddcl_wrap .ddcl .nodes{ display:block; color:#666; font-size:14px; text-align:center; position:absolute; z-index:4;}
.flowchart_wrap .ddcl_wrap .ddcl .nodes .icon01{ display:block; width:18px; height:18px; background:#edb03b; border:3px solid #1695ca; border-radius:50%; margin:4px auto 0;}
.flowchart_wrap .ddcl_wrap .ddcl .nodes .icon02{display:block; width:18px; height:18px; background:#1695ca; border-radius:50%; margin:6px auto 0;}
.flowchart_wrap .ddcl_wrap .ddcl .xiaz{left:10.5%; top:36px;}
.flowchart_wrap .ddcl_wrap .ddcl .zhuand{left:25.1%; top:36px;}
.flowchart_wrap .ddcl_wrap .ddcl .quer{left:40.2%; top:36px;}
.flowchart_wrap .ddcl_wrap .ddcl .tzph{left:46.5%; top:-6px;}
.flowchart_wrap .ddcl_wrap .ddcl .fhhx{left:83%; top:36px;}
.flowchart_wrap .ddcl_wrap .ddcl .scbc{ left:54%; top:-6px;}
.flowchart_wrap .ddcl_wrap .ddcl .ddkdd{ left:62.7%; top:-6px;}
.flowchart_wrap .ddcl_wrap .ddcl .smyh{ left:75%; top:-6px;}
.flowchart_wrap .ddcl_wrap .ddcl .sgfh{ left:60.4%; top:80px;}

.flowchart_wrap .ddcl_wrap .ddcl .nodes02{ display:block; color:#666; font-size:14px; padding:0 8px; background:#f1fbff; border:2px dashed #1695ca; border-radius:20px; position:absolute; line-height:19px; z-index:4;}
.flowchart_wrap .ddcl_wrap .ddcl .ptjy{ left:15.3%; top:60px;}
.flowchart_wrap .ddcl_wrap .ddcl .xtdd{ left:30.5%; top:60px;}

.flowchart_wrap .thh_wrap{padding:16px 3%; border:1px dashed #d8d8d8; margin-top:20px; position:relative;}
.flowchart_wrap .thh_wrap .title{display:block; width:96px; height:26px; line-height:24px; text-align:center; color:#FFF; background:#3e4b59; position:absolute; left:-1px; top:-1px;}
.flowchart_wrap .thh_wrap .remark_icon{ display:inline-block; width:74px; height:54px; background:url(images/remark_icon.png) no-repeat; position:absolute; left:14.3%; bottom:18px;}
.flowchart_wrap .thh_wrap .thh{width:100%; height:224px; position:relative;}
.flowchart_wrap .thh_wrap .thh .line{ width:100%; height:3px; background:#1695ca; position:absolute; left:0; top:149px; z-index:2;}
.flowchart_wrap .thh_wrap .thh .pane{ width:45.8%; height:121px; border:3px solid #1695ca; position:absolute; top:87px; right:4.7%; z-index:1; border-radius:10px; background:#FFF;}
.flowchart_wrap .thh_wrap .thh .strat{display:block; width:72px; height:72px; line-height:70px; text-align:center; color:#333; font-size:22px; border:3px dashed #1695ca; background:#f1fbff; border-radius:50%; position:absolute; left:0; bottom:35px; z-index:3;}
.flowchart_wrap .thh_wrap .thh .nodes{ display:block; color:#666; font-size:14px; text-align:center; position:absolute; z-index:4;}
.flowchart_wrap .thh_wrap .thh .nodes .text{ display:inline-block; background:#FFF;}
.flowchart_wrap .thh_wrap .thh .nodes .icon01{ display:block; width:18px; height:18px; background:#edb03b; border:3px solid #1695ca; border-radius:50%; margin:4px auto 0;}
.flowchart_wrap .thh_wrap .thh .nodes .icon02{display:block; width:18px; height:18px; background:#1695ca; border-radius:50%; margin:7px auto 0;}
.flowchart_wrap .thh_wrap .thh .nodes .icon03{display:block; width:12px; height:12px; background:#FFF; border-radius:50%; margin:8px auto 0; border:3px solid #333;}
.flowchart_wrap .thh_wrap .thh .xiaz{left:10.5%; top:114px;}
.flowchart_wrap .thh_wrap .thh .ztd{left:26.1%; top:114px;}
.flowchart_wrap .thh_wrap .thh .quer{left:43.2%; top:114px;}
.flowchart_wrap .thh_wrap .thh .tzck_up{left:65.7%; top:52px;}
.flowchart_wrap .thh_wrap .thh .schhd{left:76.3%; top:0px; margin-left:-35px;}
.flowchart_wrap .thh_wrap .thh .tzcw_up{left:81.9%; top:52px;}
.flowchart_wrap .thh_wrap .thh .tzcw_middle{left:75.5%; top:114px;}
.flowchart_wrap .thh_wrap .thh .tzck_down{left:75.5%; top:176px;}
.flowchart_wrap .thh_wrap .thh .bjxx{left:55%; top:10px; margin-left:-42px;}
.flowchart_wrap .thh_wrap .thh .quer_up{ left:59%; top:52px; margin-left:-14px;}
.flowchart_wrap .thh_wrap .thh .cksh_up{ left:76.3%; top:52px; margin-left:-28px;}
.flowchart_wrap .thh_wrap .thh .cwtk_up{ left:89.6%; top:52px;}
.flowchart_wrap .thh_wrap .thh .quer_middle{ left:63.3%; top:114px;}
.flowchart_wrap .thh_wrap .thh .cwtk_middle{ left:88%; top:114px;}
.flowchart_wrap .thh_wrap .thh .quer_down{ left:63.3%; top:176px;}
.flowchart_wrap .thh_wrap .thh .cksh_down{ left:88%; top:176px;}
.flowchart_wrap .thh_wrap .thh .end{ right:-6px; top:114px;}
.flowchart_wrap .thh_wrap .thh .thtk{left:51%; top:52px; color:#000; margin-left:-28px;}
.flowchart_wrap .thh_wrap .thh .jtk{left:51.7%; top:114px; color:#000;}
.flowchart_wrap .thh_wrap .thh .jth{left:51.7%; top:176px; color:#000;}

.flowchart_wrap .thh_wrap .thh .nodes02{ display:block; color:#666; font-size:14px; padding:0 8px; background:#f1fbff; border:2px dashed #1695ca; border-radius:20px; position:absolute; line-height:19px; z-index:4;}
.flowchart_wrap .thh_wrap .thh .tdsq{ left:15.3%; top:139px;}
.flowchart_wrap .thh_wrap .thh .shfwd{ left:31.5%; top:139px;}

.flowchart_wrap .thh_wrap .thh .halfcircle{ display:block; width:8%; height:85px; border:3px solid #1695ca; position:absolute; z-index:0; border-radius:50%; left:50.8%; top:45px;}
.flowchart_wrap .thh_wrap .thh .verticalbar{ display:block; width:3px; height:60px; background:#1695ca; position:absolute; z-index:0; left:76.3%; top:30px; margin-left:-2px;}
</style>
</head>

<body>
<div class="flowchart_wrap">
	<div class="ddcl_wrap">
    	<span class="title">订单处理</span>
    	<div class="ddcl">
        	<div class="line"></div>
            <div class="pane"></div>
            <span class="strat">平台</span>
            <span class="nodes xiaz">下载<i class="icon01"></i></span>
            <span class="nodes02 ptjy">平台交易</span>
            <span class="nodes zhuand">转单<i class="icon01"></i></span>
            <span class="nodes02 xtdd">系统订单</span>
            <span class="nodes quer">确认<i class="icon01"></i></span>
            <span class="nodes tzph">通知配货<i class="icon01"></i></span>
            <span class="nodes scbc">生成波次<i class="icon02"></i></span>
            <span class="nodes ddkdd">打印快递单/拣货<i class="icon02"></i></span>
            <span class="nodes smyh">扫描验货<i class="icon02"></i></span>
            <span class="nodes sgfh">手工发货<i class="icon02"></i></span>
            <span class="nodes fhhx">发货状态回写<i class="icon01"></i></span>
            <span class="end">平台</span>
        </div>
        <span class="remark_icon"></span>
    </div>
    <div class="thh_wrap">
    	<span class="title">退换货</span>
    	<div class="thh">
        	<div class="line"></div>
            <div class="pane"></div>
            <span class="strat">平台</span>
            <span class="nodes xiaz">下载<i class="icon01"></i></span>
            <span class="nodes02 tdsq">平台退单申请</span>
            <span class="nodes ztd">转退单<i class="icon01"></i></span>
            <span class="nodes02 shfwd">售后服务单</span>
            <span class="nodes quer">确认<i class="icon01"></i></span>
            <span class="nodes thtk"><span class="text">退货退款</span><i class="icon03"></i></span>
            <span class="nodes bjxx">编辑换货信息<i class="icon02"></i></span>
            <span class="nodes quer_up"><span class="text">确认</span><i class="icon02"></i></span>
            <span class="nodes tzck_up">通知仓库<i class="icon01"></i></span>
            <span class="nodes cksh_up"><span class="text">仓库收货</span><i class="icon02"></i></span>
            <span class="nodes schhd">生成换货单<i class="icon01"></i></span>
            <span class="nodes tzcw_up">通知财务<i class="icon01"></i></span>
            <span class="nodes cwtk_up">财务退款<i class="icon02"></i></span>
            <span class="nodes jtk">仅退款<i class="icon03"></i></span>
            <span class="nodes quer_middle">确认<i class="icon02"></i></span>
            <span class="nodes tzcw_middle">通知财务<i class="icon01"></i></span>
            <span class="nodes cwtk_middle">财务退款<i class="icon02"></i></span>
            <span class="nodes jth">仅退货<i class="icon03"></i></span>
            <span class="nodes quer_down">确认<i class="icon02"></i></span>
            <span class="nodes tzck_down">通知仓库<i class="icon01"></i></span>
            <span class="nodes cksh_down">仓库收货<i class="icon02"></i></span>
            <span class="nodes end">结束<i class="icon02"></i></span>
            
            <span class="halfcircle"></span>
            <span class="verticalbar"></span>            
        </div>
        <span class="remark_icon"></span>
    </div>
</div>
</body>
</html>
