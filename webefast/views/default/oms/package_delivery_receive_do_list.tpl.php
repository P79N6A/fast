<style>
    .well{border-radius:0;padding: 50px 20px;height: 180px;}
    .well .row{margin-left: 5%;}
    .well .scan-cricle{height:40px !important;font-weight: lighter;font-size:2.2em;background-color: #f3fdfe;padding: 2px 15px;width:90% !important}
    .well .opt-button{width: 12% !important;margin-left: 20px;}
    .well .opt-button button{line-height: 40px;width: 100%;margin-right: 1.2%;font-size: 1.3em;padding: 2px;letter-spacing:2px;}
    .well .control-message{width: 50%;}
    #msg{line-height: 40px;margin-left: 12%;font-size: 1.5em;letter-spacing:2px !important;}
    .well .error{height: 50px;}
    .well .error .control-group{width: 100%;}
    #err_msg{line-height: 50px;width: 600px;font-size: 1.2em;vertical-align: middle;}

    #data-table {width: 700px;padding: 0;margin: 0;}
    #data-table th {
        font: bold 11px "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif;
        color: #4f6b72;
        border-right: 1px solid #C1DAD7;
        border-bottom: 1px solid #C1DAD7;
        border-top: 1px solid #C1DAD7;
        letter-spacing: 2px;
        text-transform: uppercase;
        text-align: left;
        padding: 6px 6px 6px 12px;
        background: #CAE8EA;
        letter-spacing:3px;
    }
    #data-table td {
        border-right: 1px solid #C1DAD7;
        border-bottom: 1px solid #C1DAD7;
        background: #fff;
        font-size:11px;
        padding: 6px 6px 6px 12px;
        color: #4f6b72;
        height: 20px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '包裹快递交接',
    'links' => array(
        array('url' => 'oms/package_delivery_receive/count_list', 'title' => '包裹快递交接统计', 'is_pop' => false,),
    ),
    'ref_table' => 'table'
));
?>
<div class="well form-horizontal">
    <div class="row">
        <div class="control-group">
            <input type="text" class="control-text scan-cricle" id="express_no" style="" placeholder="请扫描快递单号">
        </div>
        <div class="control-group opt-button">
            <button type="button" class="button" id="express_receive" disabled="disabled">快递交接</button>
        </div>
        <div class="control-group control-message">
            <span id="msg" style="letter-spacing:1px;"></span>
        </div>
    </div>
    <div class="row error">
        <div class="control-group">
            <span id="err_msg" style="color:red;"></span>
        </div>
    </div>
    <hr style="margin-top:5px;margin-left:-20px;border:1px #FFF dotted;">
    <div class="row data-table">
        <div class="control-group" id="info">
            <table id="data-table" cellspacing="0">
                <tr>
                    <th>订单号</th>
                    <th>配送方式</th>
                    <th>快递单号</th>
                    <th>发货时间</th>
                    <th>商品数量</th>
                </tr>
                <tr>
                    <td id="sell_record_code_txt" style="width:150px;"></td>
                    <td id="express_name_txt" style="width:200px;"></td>
                    <td id="express_no_txt" style="width:200px;"></td>
                    <td id="delivery_time_txt" style="width:150px;"></td>
                    <td id="goods_num_txt" style="width:150px;"></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<bgsound loop="false" autostart="false" id="bgsound_ie" src="" />
<audio controls="controls" id="bgsound_others" style="display:none;" src=""></audio>

<script type="text/javascript">
    var sounds = {
        "error": "<?php echo $response['sound']['error'] ?>",
        "success": "<?php echo $response['sound']['success'] ?>"
    };
    //播放提示音
    function play_sound(typ) {
        var wav = "<?php echo CTX()->get_app_conf('common_http_url'); ?>js/sound/" + sounds[typ] + ".wav";
        if (navigator.userAgent.indexOf('MSIE') >= 0) {//IE
            document.getElementById('bgsound_ie').src = wav;
        } else {// Other borwses (firefox, chrome)
            var obj = document.getElementById('bgsound_others');
            obj.src = wav;
            obj.play();
        }
    }

    $(function () {
        $("#express_no").focus();
        // 扫描交接
        $("#express_no").keyup(function (event) {
            if (event.keyCode == 13) {
                express_receive();
            }
        });

        $("#express_receive").click(function () {
            express_receive();
        });
    });

    $("#express_no").bind("input propertychange", function () {
        if ($(this).val() == '') {
            $("#express_receive").attr('disabled', 'disabled');
        } else {
            $("#express_receive").removeAttr('disabled');
        }
    });

    function express_receive() {
        clearTabData();
        var express_no = $("#express_no").val();
        if (express_no == '') {
            return false;
        }
        var url = "?app_act=oms/package_delivery_receive/scan_receive";
        var param = {express_no: express_no};
        $.post(url, param, function (data) {
            if (data.status == 1) {
                play_sound("success");
                fillData(data.data);
                $("#msg").css('color', 'green');
                $("#msg").text('快递交接成功');
                $("#err_msg").text('');
            } else if (data.status == -2) {
                play_sound("error");
                fillData(data.data);
                $("#msg").css('color', 'red');
                $("#msg").text('快递交接失败');
                $("#err_msg").text('错误信息：' + data.message);
            } else {
                play_sound("error");
                $("#msg").css('color', 'red');
                $("#msg").text(data.message);
            }
            $("#express_no").val("");
            $("#express_receive").attr('disabled', 'disabled');
            $("#express_code").focus();
        }, "json");
    }

    function clearTabData() {
        $("#data-table tr:eq(1)").find("td").each(function (obj) {
            $(this).text("");
        });
    }

    function fillData(data) {
        $.each(data, function (key, val) {
            $("#" + key + "_txt").text(val);
        });
    }
</script>
