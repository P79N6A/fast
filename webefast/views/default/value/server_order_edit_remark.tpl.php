<style>
    body,li,p,ul { 
        margin: 0;
        padding: 0;
        font: 12px/1 Tahoma, Helvetica, Arial, "\5b8b\4f53", sans-serif;
    }
    ul, li, ol { list-style: none; }
    /* 重置文本格式元素 */
    a { text-decoration: none; cursor: pointer; color:#333333; font-size:14px;}
    a:hover { text-decoration: none; }
    .clearfix::after{ display:block; content:''; height:0; overflow:hidden; clear:both;} 
    /*星星样式*/
    .content{ width:600px; margin:0 auto; padding-top:20px;}
    .title{ font-size:14px; background:#dfdfdf; padding:10px; margin-bottom:10px;}
    .block{ width:100%; margin:0 0 20px 0; padding-top:10px; padding-left:50px; line-height:21px;}
    .block .star_score{ float:left;}
    .star_list{height:21px;margin:50px; line-height:21px;}
    .block p,.block .attitude{ padding-left:20px; line-height:21px; display:inline-block;}
    .block p span{ color:#C00; font-size:16px; font-family:Georgia, "Times New Roman", Times, serif;}
    .star_score { background:url(assets/images/startScore/stark2.png); width:160px; height:21px;  position:relative; }
    .star_score a{ height:21px; display:block; text-indent:-999em; position:absolute;left:0;}
    .star_score a:hover{ background:url(assets/images/startScore/stars2.png);left:0;}
    .star_score a.clibg{ background:url(assets/images/startScore/stars2.png);left:0;}
    #starttwo .star_score { background:url(assets/images/startScore/starky.png);}
    #starttwo .star_score a:hover{ background:url(assets/images/startScore/starsy.png);left:0;}
    #starttwo .star_score a.clibg{ background:url(assets/images/startScore/starsy.png);left:0;}
    /*星星样式*/
    .show_number{ padding-left:50px; padding-top:20px;}
    .show_number li{ width:240px; border:1px solid #ccc; padding:10px; margin-right:5px; margin-bottom:20px;}
    .atar_Show{background:url(assets/images/startScore/stark2.png); width:160px; height:21px;  position:relative; float:left; }
    .atar_Show p{ background:url(assets/images/startScore/stars2.png);left:0; height:21px; width:134px;}
    .show_number li span{ display:inline-block; line-height:21px;}
</style>
<?php echo load_js('startScore/startScore.js'); ?>
<table cellspacing="0" class="table table-bordered">
    <tr>
    <div id="starttwo" class="block clearfix">
<!--                <span  style="float:left;" class="scord">评价：</span>-->
        <div  class="star_score"></div>
        <p style="float:left;">您的评分：<span class="fenshu"></span> 分</p>
        <div class="attitude"></div>
    </div>
</tr>
<tr>
    <td>
        <textarea id="remark" style="width:90%;height:100px;"></textarea>
    </td>
</tr>
</table>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" disabled="disabled" id="btn_psending_ok">确定</button>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#btn_psending_ok").click(function () {
            var remark = $("#remark").val();
            if (remark.length <= 0) {
                BUI.Message.Alert('评价不能为空！', 'error');
                return;
            }
            var score = $(".fenshu").text();
            if (score == '') {
                return;
            }
            var params = {'app_fmt': 'json', id: <?php echo $request['id'] ?>, remark: remark, score: score};
            $.post("?app_act=value/server_order/add_remark", params, function (data) {
                if (data.status != "1") {
                    BUI.Message.Alert(data.message, function () {
                        // ui_closePopWindow("<?php //echo $request['ES_frmId']           ?>");
                    }, 'error');
                } else {
                    BUI.Message.Alert(data.message, function () {
                        ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
                    }, 'success');
                }
            }, "json");
        });
    });

    scoreFun($("#starttwo"), {
        fen_d: 22, //每一个a的宽度
        ScoreGrade: 5//a的个数 10或者
    })

    $(".star_score").click(function () {
        $("#btn_psending_ok").attr("disabled", false);
    })

</script>