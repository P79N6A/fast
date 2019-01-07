    function launch_ww(buyer_name){
        var site = 'cntaobao';
        !function() {
                openFunc = function() {
                    try {
                        window.open("", "_top"), window.opener = null, window.close()
                    } catch (openFunc) {}
                },
                launchFunc = function() {
                    window.location.href = "aliim:sendmsg?touid=" + site + buyer_name + "&site=" + site + "&status=1", setTimeout(function() {
                        openFunc()
                    }, 6e3)
                };
                window.isInstalled ? window.isInstalled(function(openFunc) {
                if (openFunc) {
                    launchFunc();
                }else {
                    BUI.Message.Confirm("检测到你未安装千牛或者阿里旺旺客户端,是否要跳转到官网下载?", function(){
                        window.open("https://wangwang.taobao.com");
                    });
                }
            }) : launchFunc()
        }();
    }
    
    ! function() {
    function a(a, b, c) { a || b();
        var d = +new Date,
            e = document.createElement("script"),
            f = "JSONP" + d,
            g = document.getElementsByTagName("head")[0];
        window[f] = function() { window[f] = null, b && b() }, a += (a.indexOf("?") > -1 ? "&" : "?") + "callback=" + f, e.onerror = function() { c && c() }, g.insertBefore(e, g.firstChild), e.src = a }

    function b(b, c, d) {
        var e = location.protocol,
            f = null;
        d = d || ("https:" == e ? "4013" : "4012"), f = setTimeout(function() { c && c() }, 1500), a(e + "//localhost.wwbizsrv.alibaba.com:" + d, function() { clearTimeout(f), b && b() }, function() { clearTimeout(f), c && c() }) }

    function c() {
        var a = navigator.platform.indexOf("Mac") > -1;
        return a ? !0 : g.ie ? d() : e() }

    function d() {
        var a, b = !0;
        try { a = new ActiveXObject("aliimx.wangwangx") } catch (c) { b = !1 } finally { a = null }
        return b }

    function e() {
        var a = !0,
            b = navigator.mimeTypes["application/ww-plugin"];
        if (!b) return !1;
        var c = document.createElement("embed");
        return c.setAttribute("type", "application/ww-plugin"), c.style.visibility = "hidden", c.style.width = "0px", c.style.height = "0px", document.body.appendChild(c), "function" != typeof c.NPWWVersion && (a = !1), a }

    function f(a) {
        return document && document.body ? void(window.__IsClientInstalled ? a && a(!0) : window.__IsClientInstalled === !1 ? a && a(!1) : c() ? (window.__IsClientInstalled = !0, a && a(!0)) : b(function() { window.__IsClientInstalled = !0, a && a(!0) }, function() { b(function() { window.__IsClientInstalled = !0, a && a(!0) }, function() { window.__IsClientInstalled = !1, a && a(!1) }, "https:" == location.protocol ? "4813" : "4812") })) : void(window.__checkInterval = setInterval(function() { document && document.body && (clearInterval(window.__checkInterval), f(a)) }, 500)) }
    var g = function() {
        var a = {},
            b = window.navigator.userAgent,
            c = b.match(/Web[kK]it[\/]{0,1}([\d.]+)/),
            d = b.match(/Chrome\/([\d.]+)/) || b.match(/CriOS\/([\d.]+)/),
            e = b.match(/Edge\/([\d.)\/) || ua.match(\/MSIE\s([\d.]+)/) || b.match(/Trident\/[\d](?=[^\?]+).*rv:([0-9.].)/),
            f = b.match(/Firefox\/([\d.]+)/);
        return c && (a.webkit = c[1]), d && (a.chrome = d[1]), e && (a.ie = e[1]), f && (a.firefox = f[1]), a }();
    window.isInstalled = f }();