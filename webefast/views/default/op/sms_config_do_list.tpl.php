<?php render_control('PageHead', 'head1',
    array('title' => '短信参数设置',
        'links' => array(
            
        ),
        'ref_table' => 'table'
    ));

?>
<ul class="nav-tabs oms_tabs">
    <li class='bui-tab-panel-item11 active'><a href="#" id="sms_config_common" >通用设置</a></li>
    <li class='bui-tab-panel-item11 '><a href="#" id="sms_config_marketing" >营销群发设置</a></li>
</ul>
<!-- 通用设置 -->
<form id="form1" method="post" action="?app_act=op/sms_config/save_sms_config_common" tabindex="0" style="outline: none;" >
 <table cellspacing="0" style="width:90%" class="table table-bordered" id="form_tbl">
       <?php foreach($response['sms_config_common'] as $k=>$v){ ?>
            <?php foreach($v  as $value){ ?>
            <tr>
                <td width="20%" ><?php echo $value['param_name']; ?></td>
                 <?php  switch ($value['type'])  {
                     	     case "text":
                     	     	?>
                    <td style="width:100px;"><input type="text"  data-rules="{required: true}" value="<?php echo $value['value']; ?>" name="<?php echo $value['param_code']; ?>"> <?php echo $value['remark']; ?></td>
                             <?php break;
                            case  "time":
                            ?>
                    <td style="width:100px;"><input type="text" style="width:150px;" class= ' calendar ' id = "code"  data-rules="{required: true}" value="<?php echo $value['value']; ?>" name="<?php echo $value['param_code']; ?>"></td>
                            <?php break; ?>

                            <?php case  "select":?>
                            <td style="width:100px;">
                             <select class="<?php echo $value['param_code']; ?>" name="<?php echo $value['param_code']; ?>" >
                             <?php foreach($value['form_desc']  as $r_k => $r_v){ ?>
                                 <option  value ="<?php echo $r_k; ?>" <?php if($value['value'] == $r_k ) { ?> selected <?php } ?> ><?php echo $r_v; ?></option>
                             <?php } ?>
                              </select>
                            </td>
                            <?php break; ?>
                            <?php case  "radio":?>
                             <td style="width:400px;">
                             <?php foreach($value['form_desc']  as $r_k => $r_v){ ?>
                             <input type="radio" style="width:40px;" class="<?php echo $value['param_code']; ?>" name="<?php echo $value['param_code']; ?>"
                              value="<?php echo $r_k; ?>" <?php if ($value['value'] == $r_k){  ?> checked="checked"   <?php } ?> /><?php echo $r_v; ?>
                             <?php } ?>
                               </td>
                           <?php break; ?>
                     <?php }//switch ?>
                <td width="45%" ><?php echo isset($value['memo'])?$value['memo']:''; ?></td>

            </tr>
            <?php } ?>
        <?php } ?>
 </table>
<?php
?>
<div style="text-align: center;">
    <button class="button button-primary" type="submit">保存</button>
    <button id="reset" class="button button-primary" type="reset">重置</button>
</div>
</form>

<!-- 营销群发设置 -->
<form id="form2" method="post" action="?app_act=op/sms_config/do_save" tabindex="0" style = "display:none;" >
 <table cellspacing="0" style="width:90%" class="table table-bordered" id="form_tbl">
       <?php foreach($response['sms_config_marketing'] as $k=>$v){ ?>
            <?php foreach($v  as $value){ ?>
            <tr>
                <td width="30%" ><?php echo $value['param_name']; ?></td>
                 <?php  switch ($value['type'])  {
                     	     case "text":
                     	     	?>

                    <td style="width:100px;"><input type="text"  data-rules="{required: true}" value="<?php echo $value['value']; ?>" name="<?php echo $value['param_code']; ?>"> <?php if($value['param_code']=='warn_weight'){?>千克<?php }?></td>
                             <?php break;
                            case  "time":
                            ?>
                    <td style="width:100px;"><input type="text" style="width:150px;" class= ' calendar ' id = "code"  data-rules="{required: true}" value="<?php echo $value['value']; ?>" name="<?php echo $value['param_code']; ?>"></td>
                            <?php break; ?>

                            <?php case  "select":?>
                            <td style="width:100px;">
                             <select class="<?php echo $value['param_code']; ?>" name="<?php echo $value['param_code']; ?>" >
                             <?php foreach($value['form_desc']  as $r_k => $r_v){ ?>
                                 <option  value ="<?php echo $r_k; ?>" <?php if($value['value'] == $r_k ) { ?> selected <?php } ?> ><?php echo $r_v; ?></option>
                             <?php } ?>
                              </select>
                            </td>
                            <?php break; ?>
                            <?php case  "radio":?>

                             <td style="width:400px;">


                             <?php foreach($value['form_desc']  as $r_k => $r_v){ ?>
                             <input type="radio" style="width:40px;" class="<?php echo $value['param_code']; ?>" name="<?php echo $value['param_code']; ?>"
                              value="<?php echo $r_k; ?>" <?php if ($value['value'] == $r_k){  ?> checked="checked"   <?php } ?> /><?php echo $r_v; ?>
                             <?php } ?>

                               </td>
                           <?php break; ?>
                     <?php }//switch ?>
                <td width="45%" ><?php echo isset($value['memo'])?$value['memo']:''; ?></td>

            </tr>
            <?php } ?>
        <?php } ?>

 </table>
<?php
?>
<div style="text-align: center;">
        <button class="button button-primary" type="submit">保存</button>
        <button id="reset" class="button button-primary" type="reset">重置</button>
</div>
<!--  <div style="text-align: left;">
          <font color="#0000FF ">说明:</font><br>
       <?php //echo isset($response['shuoming'])?$response['shuoming']:''; ?>
</div>-->
</form>

<?php echo load_js('print/lodop/LodopFuncs.js'); ?>
<script type="text/javascript">
$(document).ready(function(){
	//切换页签
	$(".oms_tabs a").click(function(){
        change_tabs($(this));
	});
});
    BUI.use('bui/calendar',function(Calendar){
          var datepicker = new Calendar.DatePicker({
            trigger:'.calendar',
            autoRender : true
          });
        });
    BUI.use('bui/form',function (Form) {
        var form_sms_config_common = new BUI.Form.HForm({
            srcNode : '#form1',
            submitType : 'ajax',
            callback : function(data){
                if(data.status != '1'){
                    BUI.Message.Alert(data.message, 'error');
                    return
                }else{
                 BUI.Message.Alert(data.message, 'success');
                    if(data.data.length>0){
                        for(var i in data.data){
                             top.updatemenu(data.data[i]);
                        }
                     }
                }
            }
        }).render();
    });
    var form2 =  new BUI.Form.HForm({
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
    function getQueryString(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
        var r = window.location.search.substr(1).match(reg);
        if (r != null) return unescape(r[2]); return null;
    }
    //切换页签
    function change_tabs(obj){
        $(".oms_tabs").find(".active").removeClass("active");//活动页签class
		obj.parent("li").addClass("active");
        var active_id = obj.attr('id');
		if (active_id == "sms_config_common"){
			$("#form1").show();
            $("#form2").hide();
		}else if(active_id == "sms_config_marketing"){
            $("#form1").hide();
			$("#form2").show();
		}
    }
</script>
