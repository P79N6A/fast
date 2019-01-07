
<div style="font-weight:bold;font-size:18px;margin:10px 0;">&nbsp;&nbsp;扫描错误确认码</div>
<hr/>
<div style="width:900px;margin-top:20px;">
    扫描错误确认码：CONFIRM <img style="margin-left:50px;" src='assets/images/confirm.png'/>

    <div style="float:right;margin-top:20px;">
        <!--左边距(mm)：<input id="print_left" style="width:50px;margin-right:30px;" type="text" value="3" />
        右边距(mm)：<input id="print_right" style="width:50px;margin-right:30px;" type="text" value="3" />-->
        纸张宽(mm)：<input id="print_width" style="width:50px;margin-right:30px;" type="text" value="39" />
        纸张高(mm)：<input id="print_height" style="width:50px;margin-right:30px;" type="text" value="30" />
        打印数量：<input id="print_num" style="width:50px;margin-right:30px;" type="text" value="1" />
        <button  onclick = 'MyPreview()' style="width:100px;height:40px;background-color:orange;color:white;font-size:20px;">打印</button>
    </div>
</div>
<br>
<div style="border:1px solid gray;margin-top:50px;width:900px;">
    <br/>
    <span style="color:blue;">说明：</span>此错误确认码用于仓库扫描验货发生错误时，扫描此错误确认码，替代【确认】按钮，以减少仓库人员操作鼠标或键盘<br/>
    <div style="margin:10px 0 0 75px;">请打印此二维码，帖于 扫描验货 人员工作地方，当发生错误时，直接扫描此条码，即可确认此错误</div>
    <br/>
</div>
<script>
    var lodopRoot = "<?php echo CTX()->get_app_conf('common_http_url')?>js/print/lodop/";
</script>
<script language="javascript" src="<?php echo CTX()->get_app_conf('common_http_url')?>js/print/lodop/LodopFuncs.js"></script>
<script type="text/javascript">

    $("#print_num").blur(function(){

        if(this.value % 1 === 0 && this.value > 0){
        }else{
            alert('打印数量只能输入大于等于1的正整数');this.value='1';
        }
    });

    /*var LODOP; //声明为全局变量
     function prn_Preview() {
     CreatePrintPage();
     LODOP.PREVIEW();
     };

     function CreatePrintPage() {
     LODOP=getLodop(document.getElementById('LODOP1'),document.getElementById('LODOP_EM1'));
     LODOP.ADD_PRINT_BARCODE("1%","1%","99%","99%","Code39","CONFIRM");
     };*/
</script>


<script language="javascript" type="text/javascript">
    var LODOP; //声明为全局变量
    function MyPreview() {
        LODOP=getLodop(document.getElementById('LODOP1'),document.getElementById('LODOP_EM1'));
        LODOP.PRINT_INIT("CONFIRM");
        var w = parseFloat($("#print_width").val())
        var h = parseFloat($("#print_height").val())
        LODOP.SET_PRINT_PAGESIZE(1,w+"mm",h+"mm","");
        CreateAllPages(w, h);
        LODOP.PREVIEW();
    };
    function CreateAllPages(w, h){
        var c = parseInt($("#print_num").val())
        for (i = 1; i <= c; i++) {
            LODOP.NewPage();
            LODOP.ADD_PRINT_BARCODE("2mm","2mm","100%","80%","Code39","CONFIRM");
        }

    };
</script>


