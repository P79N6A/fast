<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '添加配送方式',
));?>
<script src="assets/js/jquery.formautofill2.min.js"></script>

<style>
#form_tbl {width:680px;}
#form_tbl td{text-align:left;padding:4px;}
#form_tbl .tdlabel{width:120px;text-align:right};
</style>


<div id="panel" class="">
<form  id="form1" action="" method="post">
<table id="form_tbl">


<tr id="pt">
<td class="tdlabel">仓库：</td>
<td colspan="3">
<select id="wms_store" name="wms_store" >
    <?php echo $response['tpl_html'];?>
</select>

</td>
</tr>


<tr>
  <td class="tdlabel">变更时间：</td>
  <td colspan="3">
    <div class="control-group span8" style="width:350px;">
      <div class="controls">
        <input type="text" name="created_min" id="created_min" class="input-normal calendar" value="" style="width:150px;" />
          ~
        <input type="text" name="created_max" id="created_max" class="input-normal calendar" value="" style="width:150px;" />
        <label class="remark" for="created_max"></label>
      </div>
    </div>
  </td>
</tr>
<tr>
    <td class="tdlabel"></td>
    <td colspan="3"></td>
</tr>
<tr>
    <td class="tdlabel"></td>
    <td colspan="3"></td>
</tr>
<tr>
    <td class="tdlabel"></td>
    <td colspan="3"></td>
</tr>
<tr>
    <td class="tdlabel"></td>
    <td colspan="3" style="text-align:center;">
        <button id="btn_download" class="button button-primary" type="button">
            获取库存变更的SKU
        </button>
    </td>
</tr>

</table>
</form>
</div>
<div>
  <hr>
  <div class="control-group span8">
    <label class="control-label">显示获取日志</label>
    <div class="controls">
       
    </div>
  </div>
</div>
<div id="loading" style="display: none;">
    <div>
        <img src="assets/images/loadingGray.gif">
    </div>

    <div>库存变更中...</div>
</div>
<div id="result" style="display: none;">

<script type="text/javascript">


    $("#btn_download").click(function(){
        $("#loading").show()
        $("#result").hide()
result;

        var params = {
            "wms_store": $("#wms_store").val(),
            "created_min": $("#created_min").val(),
            "created_max": $("#created_max").val()
        }

        $.post("?app_act=oms/sell_record/download_action", params, function(data){
            $("#loading").hide()
            $("#result").show()
            $("#result").html("下载:<br>"+data.down.message+"<br>转入:<br>"+data.tran.message)
        }, "json")
    })

    BUI.use('bui/calendar',function(Calendar){
        new Calendar.DatePicker({
            trigger:'#created_min',
            autoRender : true
        });
        new Calendar.DatePicker({
            trigger:'#created_max',
            autoRender : true
        });
    });

var form1_data_source_v = $("#form1_data_source").html();
if (form1_data_source_v!=''){
    $('form#form1').autofill(eval("("+form1_data_source_v+")"));
}

if (form1_data_source_v!=''){
    
    $('form#form4').autofill(eval("("+form1_data_source_v+")"));
}

$(document).ready(function(){
    if ($("#status").val() == 1){
        $("#status_type").attr("checked",true);
    }
})  
function getQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]); return null;
}
function changeStatus() {
    if ($("#status_type").is(':checked') == true){
    $("#status").val(1);
    }
    else{
    $("#status").val(0);
    }
}

 var form =  new BUI.Form.HForm({
        srcNode : '#form1',
        submitType : 'ajax',
        callback : function(data){
                var type = data.status == 1 ? 'success' : 'error';
                BUI.Message.Alert(data.message, function() {
                    if (data.status == 1) {
                        ui_closePopWindow(getQueryString('ES_frmId'));
                        
                    }
                }, type);
        }
    }).render();

 var form =  new BUI.Form.HForm({
     srcNode : '#form2',
     submitType : 'ajax',
     callback : function(data){
                var type = data.status == 1 ? 'success' : 'error';
             BUI.Message.Alert(data.message, function() {
                if (data.status == 1) {
                    ui_closePopWindow(getQueryString('ES_frmId'));
                 }
             }, type);
        }
 }).render();


 var form =  new BUI.Form.HForm({
     srcNode : '#form4',
     submitType : 'ajax',
     callback : function(data){
                var type = data.status == 1 ? 'success' : 'error';
             BUI.Message.Alert(data.message, function() {
                if (data.status == 1) {
                    ui_closePopWindow(getQueryString('ES_frmId'));
                 }
             }, type);
        }
 }).render();


function pt_type() {

     $('#rm').hide(); 
     $('#pt').show();
     $('#df').show();
     $("#tab").find('li').eq(2).hide();
     
}

function zl_type() {
    $('#pt').hide();
    $('#df').hide();    
    $('#rm').show();
    $("#tab").find('li').eq(2).show(); 
}

var action = '<?php echo $response['action'];?>';
var next = '<?php echo $response['next'];?>';

if(action == 'do_add'){
    $("#tab").find('li').eq(1).hide();
    $("#tab").find('li').eq(2).hide();
}
if(action == 'do_edit_add'){
    $("#tab").find('li').eq(2).addClass("active");
    $("#tab").find('li').eq(0).removeClass("active");
}
if(action == 'do_edit'){
    var print_type = '<?php echo $response['data']['print_type'];?>';
    if(print_type == 0){
        $("#tab").find('li').eq(2).hide();
    }else{
        $('#pt').hide();
        $('#df').hide();    
        $('#rm').show();
    }
}
if(next == '1'){

}


BUI.use(['bui/tab','bui/mask'],function(Tab){
    var tab = new Tab.TabPanel({
    srcNode : '#tab',
    elCls : 'nav-tabs',
    itemStatusCls : {
    'selected' : 'active'
    },
    panelContainer : '#panel'//如果不指定容器的父元素，会自动生成
    //selectedEvent : 'mouseenter',//默认为click,可以更改事件
    });
    tab.render();
    });

</script>
<?php echo load_js('comm_util.js')?>
