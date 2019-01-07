<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-Type" content="text/html; charset=UTF-8" />
<title>宝塔网络运维平台</title>
<link href="assets/css/dpl.css" rel="stylesheet" type="text/css" />
<link href="assets/css/bui.css" rel="stylesheet" type="text/css" />
<link href="assets/css/main-min.css" rel="stylesheet" type="text/css" />
<link href="assets/css/common.css" rel="stylesheet" type="text/css" />
</head>
<body>

<script type="text/javascript" src="assets/js/jquery-1.8.1.min.js"></script>
<div id="container" style="text-align: center">
    <table cellpadding="5px" cellspacing="2px" style="margin-left: 50px;margin-top: 50px   ">
        <tr>
            <td>客户名称：</td><td><input type="input" id="clientname" value="" style="width:200px"/></td>
        </tr>
        <tr>
            <td height="10px"></td><td></td>
        </tr>
        <tr>
            <td>店铺名称：</td><td><input type="input" id="shopname" value=""  style="width:200px"/></td>
        </tr>
        <tr>
            <td height="10px"></td><td></td>
        </tr>
        <tr>
            <td><input type="button" value="添加" id="saveclient" /></td><td></td>
        </tr>
    </table>
</div>
<script type="text/javascript">
    
    $(document).ready(function(){
        $("#saveclient").click(function(){
                var clientname = $.trim($("#clientname").val());
                var shopname = $.trim($("#shopname").val());
          
                var params={'kh_name': clientname,};
                params.kh_shopinfo = [];
                //params.kh_shopinfo[0]['sd_name'] = shopname;
                var obj = {};
                obj.sd_name=shopname;
                params.kh_shopinfo.push(obj)
                
                $.ajax({ type: 'POST', dataType: 'json',  
                    url:"/fastapp/weboperate/web/?app_act=apiv2/router&m=api.Apikehuinfo.addclient", 
                    data: params,
                    success: function(ret) {
                        var type = ret.status == 1 ? 'success' : 'error';
                        if (type == 'success') {
                            alert(ret.message);
                            $("#saveclient").attr("disabled","disabled");
                        } else {
                            alert(ret.message);
                        }
                    }
                });
            }
        );
    });
    
</script>
</body>
</html>