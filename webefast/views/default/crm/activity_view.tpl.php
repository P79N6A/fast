<style>
    input[type="checkbox"], input[type="radio"] {margin-top: 6px;}
    .form-horizontal .control-label { width:150px;}
    .row{ margin-bottom:10px;}
    .nav-tabs{ padding-top:10px; margin-bottom:10px;}
    form.form-horizontal{ padding:20px; border:1px solid #ded6d9;}
    input.input-normal{ width:auto;}
    select.input-normal {width: 155px;}
    #child_barcode{display: none;}
</style>
<ul class="nav-tabs oms_tabs">
    <li class="active"><a href="#"  >基本信息</a></li>
    <li ><a href="#" onClick="do_page();" >参与活动商品</a></li>
    <li><a href ="#" onClick="do_page_log();">操作日志</a></li>
</ul>

<form  class="form-horizontal" id="form1" action="" method="post">
    <input type="hidden" id="app_scene" name="app_scene" value="<?php echo $response['app_scene']; ?>" />
    <input type="hidden" id="activity_id" name="activity_id" value="<?php echo $response['data']['activity_id']; ?>"/>
    <div class="row">				
        <div class="control-group span11">
            <label class="control-label span3">活动编码：                </label>
            <div class="controls " >
                <input type="text" name="activity_code" id="activity_code" class="input-normal" value="<?php echo $response['data']['activity_code']; ?>"   />	    </div>
        </div>
    </div>				
    <div class="row">				
        <div class="control-group span11">
            <label class="control-label span3">活动名称：                </label>
            <div class="controls " >
                <input type="text" name="activity_name" id="activity_name" class="input-normal" value="<?php echo $response['data']['activity_name']; ?>" data-rules="{required: true}"  /><b style="color:red"> *</b>	    
            </div>
        </div>
    </div>				
    <div class="row">				
        <div class="control-group span11">
            <label class="control-label span3">活动开始时间：                </label>
            <div class="controls " >
                <input type="text" name="start_time" id="start_time" class="input-normal calendar calendar-time"  value="<?php echo $response['data']['start_time']; ?>" data-rules="{required: true}" /><b style="color:red"> *</b>	   	    
            </div>
        </div>
    </div>				
    <div class="row">				
        <div class="control-group span11">
            <label class="control-label span3">活动结束时间：                </label>

            <div class="controls " >
                <input type="text" name="end_time" id="end_time" class="input-normal calendar calendar-time"  value="<?php echo $response['data']['end_time']; ?>" data-rules="{required: true}" /><b style="color:red"> *</b>	   	    
            </div>
        </div>
    </div>	
    <div class="row">
        <div class="control-group span11">
            <label class="control-label span3">店铺名称：</label>
            <div class="controls">
                <select id="shop_code" name="shop_code">
                    <option class="" value="">请选择</option>
                    <?php foreach ($response['shop'] as $key => $shop_row) { ?>				
                        <option class=""  id="<?php echo $shop_row['shop_code']; ?>" value="<?php echo $shop_row['shop_code']; ?>" ><?php echo $shop_row['shop_name']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
    </div>
    <div class="row">				
        <div class="control-group span11">
            <label class="control-label span3">备注：                </label>
            <div class="controls " >
                <textarea  name="event_desc" id="event_desc"  value="<?php echo $response['data']['event_desc']; ?>"></textarea>	    
            </div>
        </div>
    </div>	
</form>

<div>
    <div id="TabPage1Submit" class="row form-actions actions-bar">
        <div class="span13 offset3 ">
            <?php if ($response['data']['status'] == 0 && $response['data']['is_first'] == 0) { ?>
                <button type="submit" class="button button-primary" id="submit">保存</button>
            <?php } ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    var form;
//$(function() {       
    form = new BUI.Form.HForm({
        srcNode: '#form1',
        submitType: 'ajax',
        callback: function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            if (data.status == 1) {
                //window.location.reload();
            } else {
                BUI.Message.Alert(data.message, function () { }, type);
            }

        }
    }).render();
    //});
</script>
<script type="text/javascript">
    $("#activity_code").attr("disabled", "disabled");
    if ($("#app_scene").val() == 'edit') {
        $("#shop_code").val("<?php echo $response['data']['shop_code']; ?>");
        $("#shop_code").attr("disabled", "disabled");
    }

    form.on('beforesubmit', function () {
        $("#activity_code").attr("disabled", false);

    });

</script>
<script type="text/javascript">
    $(function () {
        $(".control-label").css("width", "200px;");
        $("#TabPage1Submit").find("#submit").click(function () {
            var url = '';
            if ($("#activity_name").val() == '' || $("#start_time").val() == '' || $("#end_time").val() == '' || $("#shop_code").val() == "") {
                alert('活动名称、活动时间、店铺名称都不能为空');
                return false;
            }
            if ($("#app_scene").val() == 'edit') {
                url = '<?php echo get_app_url('crm/activity/do_edit'); ?>';
            } else {
                url = '<?php echo get_app_url('crm/activity/do_add'); ?>';
            }
            $("#activity_code").attr("disabled", false);
            var data = $('#form1').serialize();
            if ($("#app_scene").val() == 'edit') {
                data = data + "&shop_code=" + $("#shop_code").val();
            }
            $.post(url, data, function (data) {
                var type = data.status == 1 ? 'success' : 'error';
                if (data.status == 1) {
                    BUI.Message.Alert(data.message, function () { }, type);
                    activity_code = $("#activity_code").val();
                    ids = data.data;
                    url = "?app_act=crm/activity/view&_id=" + ids + "&app_scene=edit&activity_code=" + activity_code + "&ES_frmId=<?php echo $request['ES_frmId']; ?>";
                    location.href = url;
                } else {
                    BUI.Message.Alert(data.message, function () { }, type);
                }
            }, "json");

        });

    });

    function do_page() {
        var activity_code = $("#activity_code").val();
        var id = '<?php echo $response['_id']; ?>';
        var shop_code = '<?php echo $response['data']['shop_code']; ?>';

        if (activity_code != '' && id != '') {
            var url = "?app_act=crm/activity/goods_stock_do_list&_id=" + id + "&show=1&activity_code=" + activity_code + "&shop_code=" + shop_code;
            location.href = url;
        }

    }
    function do_page_log() {
        var activity_code = $("#activity_code").val();
        var id = '<?php echo $response['_id']; ?>';
        var shop_code = '<?php echo $response['data']['shop_code']; ?>';
        if (activity_code != '' && id != '') {
            location.href = "?app_act=crm/activity/goods_log&app_scene=edit&_id=" + id + "&show=1&activity_code=" + activity_code;
        }
    }
</script>
