<ul class="nav-tabs oms_tabs">
    <li class="bui-tab-panel-item11 active"><a href="#"  id="common" >通用</a></li>
    <li class="bui-tab-panel-item11 "><a href="#"  id="taobao" >淘宝</a></li>
    <li class='bui-tab-panel-item11 '><a href="#" id="jingdong">京东</a></li>
 	<li class='bui-tab-panel-item11 '><a href="#" id="erp" >ERP</a></li>
    <?php if ($response['ag_check'] == 1) { ?>
    <li class='bui-tab-panel-item11 '><a href="#" id="ag" >AG</a></li>
    <?php } ?>
    <li class='bui-tab-panel-item11 '><a href="#" id="siku" >寺库</a></li>
</ul>


<form  id="form1"  action="?app_act=sys/platform_params/update_params&app_fmt=json" method="post" >


<table cellspacing="0" style="width:90%" class="table table-bordered" id="form_tbl">

<?php 
$param_code_arr = array();
foreach($response['common'] as $k=>$v){ 

foreach($v  as $value){
    $param_code_arr[] = $value["param_code"];	
   if($value["param_code"] == 'oms_notice' || $value["param_code"] == 'fanance_money' || $value["param_code"] == 'order_return_huo' || $value["param_code"] == 'off_deliver_time' || $value['param_code'] == 'is_allowed_exceed' || $value['param_code'] == 'order_tag')
	continue;
echo '<tr><td style="width:200px;">'.$value["param_name"].'</td>';
?>		
<?php
 switch ($value['type'])  { 
                     	     case "text":	
                     	     	?>
                    	
                    <td style="width:100px;"><input type="text"  data-rules="{required: true}" value="<?php echo $value['value']; ?>" name="<?php echo $value['param_code']; ?>"> 月</td>
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
   
                             
                           <?php break; ?>
                     <?php }//switch ?>
                     <?php 
                     echo '<td>'.$value["memo"].'</td></td></tr>';
}	
	
 }?>

</table>
<input type="hidden" id="param_code_all" name="param_code_all"  value="<?php echo implode(",", $param_code_arr); ?>" />
<div style="text-align: center;">
        <button class="button button-primary" type="submit">保存</button>
        <button id="reset" class="button button-primary" type="reset">重置</button>
</div>

</form>




<form  id="form2"  action="?app_act=sys/platform_params/update_params&app_fmt=json" method="post"  style = "display:none;">


<table cellspacing="0" style="width:90%" class="table table-bordered" id="form_tbl">

<?php 
$param_code_arr = array();
foreach($response['order'] as $k=>$v){ 

foreach($v  as $value){
    $param_code_arr[] = $value["param_code"];
if($value["param_code"] == 'oms_notice' || $value["param_code"] == 'fanance_money' || $value["param_code"] == 'order_return_huo' || $value["param_code"] == 'off_deliver_time' || $value['param_code'] == 'is_allowed_exceed' || $value['param_code'] == 'order_tag')
	continue;
	
echo '<tr><td style="width:200px;">'.$value["param_name"].'</td>';
?>		
<?php
 switch ($value['type'])  { 
                     	     case "text":	
                     	     	?>
                    	
                    <td style="width:100px;"><input type="text"  data-rules="{required: true}" value="<?php echo $value['value']; ?>" name="<?php echo $value['param_code']; ?>"> 月</td>
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
   
                             
                           <?php break; ?>
                     <?php }//switch ?>
                     <?php 
                     echo '<td>'.$value["memo"].'</td></td></tr>';
}	
	
 }?>

</table>
<input type="hidden" id="param_code_all" name="param_code_all"  value="<?php echo implode(",", $param_code_arr); ?>" />
<div style="text-align: center;">
        <button class="button button-primary" type="submit">保存</button>
        <button id="reset" class="button button-primary" type="reset">重置</button>
</div>

</form>
<form  id="form_erp"  action="?app_act=sys/platform_params/update_params&app_fmt=json" method="post" style = "display:none;">


<table cellspacing="0" style="width:90%" class="table table-bordered" id="form_tbl">

<?php 
$param_code_arr = array();
foreach($response['erp'] as $k=>$v){ 

foreach($v  as $value){
    $param_code_arr[] = $value["param_code"];
if($value["param_code"] == 'oms_notice' || $value["param_code"] == 'fanance_money' || $value["param_code"] == 'order_return_huo' || $value["param_code"] == 'off_deliver_time' || $value['param_code'] == 'is_allowed_exceed' || $value['param_code'] == 'order_tag')
	continue;
	
echo '<tr><td style="width:200px;">'.$value["param_name"].'</td>';
?>		
<?php
 switch ($value['type'])  { 
                     	     case "text":	
                     	     	?>
                    	
                    <td style="width:100px;"><input type="text"  data-rules="{required: true}" value="<?php echo $value['value']; ?>" name="<?php echo $value['param_code']; ?>"> 月</td>
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
   
                             
                           <?php break; ?>
                     <?php }//switch ?>
                     <?php 
                     echo '<td>'.$value["memo"].'</td></td></tr>';
}	
	
 }?>

</table>
<input type="hidden" id="param_code_all" name="param_code_all"  value="<?php echo implode(",", $param_code_arr); ?>" />
<div style="text-align: center;">
        <button class="button button-primary" type="submit">保存</button>
        <button id="reset" class="button button-primary" type="reset">重置</button>
</div>

</form>



<form  id="form_jd"  action="?app_act=sys/platform_params/update_params&app_fmt=json" method="post" style = "display:none;">
<table cellspacing="0" style="width:90%" class="table table-bordered" id="form_tbl">
<?php 
$param_code_arr = array();
foreach($response['jingdong'] as $k=>$v){ 

foreach($v  as $value){
    $param_code_arr[] = $value["param_code"];	
   if($value["param_code"] == 'oms_notice' || $value["param_code"] == 'fanance_money' || $value["param_code"] == 'order_return_huo' || $value["param_code"] == 'off_deliver_time' || $value['param_code'] == 'is_allowed_exceed' || $value['param_code'] == 'order_tag')
	continue;
echo '<tr><td style="width:200px;">'.$value["param_name"].'</td>';
?>		
<?php
 switch ($value['type'])  { 
                     	     case "text":	
                     	     	?>
                    	
                    <td style="width:100px;"><input type="text"  data-rules="{required: true}" value="<?php echo $value['value']; ?>" name="<?php echo $value['param_code']; ?>"> 月</td>
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
   
                             
                           <?php break; ?>
                     <?php }//switch ?>
                     <?php 
                     echo '<td>'.$value["memo"].'</td></td></tr>';
}	
	
 }?>

</table>
<input type="hidden" id="param_code_all" name="param_code_all"  value="<?php echo implode(",", $param_code_arr); ?>" />
<div style="text-align: center;">
        <button class="button button-primary" type="submit">保存</button>
        <button id="reset" class="button button-primary" type="reset">重置</button>
</div>
</form>

<!--淘宝ag-->
<form  id="form_ag"  action="?app_act=sys/platform_params/update_params&app_fmt=json" method="post" style = "display:none;">
    <table cellspacing="0" style="width:90%" class="table table-bordered" id="form_tbl">
        <?php
        $param_code_arr = array();
        foreach($response['ag'] as $k=>$v){

        foreach($v  as $value){
        $param_code_arr[] = $value["param_code"];
        echo '<tr class=' . $value["param_code"] . '_th><td style="width:200px;">' . $value["param_name"] . '</td>';
        ?>
        <?php
        switch ($value['type'])  {
        case "text":
            ?>
            <td style="width:100px;"><input type="text" data-rules="{required: true}"
                                            value="<?php echo $value['value']; ?>"
                                            name="<?php echo $value['param_code']; ?>"> 月
            </td>
            <?php break;
        case  "time":
            ?>
            <td style="width:100px;"><input type="text" style="width:150px;" class=' calendar ' id="code"
                                            data-rules="{required: true}" value="<?php echo $value['value']; ?>"
                                            name="<?php echo $value['param_code']; ?>"></td>
            <?php break; ?>
        <?php case  "select": ?>
            <td style="width:100px;">
                <select class="<?php echo $value['param_code']; ?>" name="<?php echo $value['param_code']; ?>">
                    <?php foreach ($value['form_desc'] as $r_k => $r_v) { ?>
                        <option value="<?php echo $r_k; ?>" <?php if ($value['value'] == $r_k) { ?> selected <?php } ?> ><?php echo $r_v; ?></option>
                    <?php } ?>
                </select>
            </td>
            <?php break; ?>
        <?php case  "radio": ?>
        <td style="width:400px;">
            <?php foreach ($value['form_desc'] as $r_k => $r_v) { ?>
                <input type="radio" style="width:40px;" class="<?php echo $value['param_code']; ?>"
                       name="<?php echo $value['param_code']; ?>"
                       value="<?php echo $r_k; ?>" <?php if ($value['value'] == $r_k) { ?> checked="checked"   <?php } ?> /><?php echo $r_v; ?>
            <?php } ?>
            <?php break; ?>
            <?php }//switch
            ?>
            <?php
            echo '<td>' . $value["memo"] . '</td></td></tr>';
            if ($value["param_code"] == 'aligenius_enable') {
                echo '<tr class=taobao_ag_shop_th><td>启用AG店铺(淘宝)</td><td style="width:200px;" colspan="2">';
                foreach ($response['taobao_shop'] as $key => $shop_row) {
                    $checked = (in_array($shop_row['shop_code'], $response['taobao_ag_shop'])) ? 'checked' : '';
                    echo "<div class='controls span6'><input type='checkbox' class='' name='ag_shop[]' id=".$shop_row['shop_code']." value=".$shop_row['shop_code'].' '.$checked.">".$shop_row['shop_name']."</div>";
                }
                echo '</td></tr>';
            }
            }
            } ?>

    </table>
    <input type="hidden" id="param_code_all" name="param_code_all"  value="<?php echo implode(",", $param_code_arr); ?>" />
    <div style="text-align: center;">
        <button class="button button-primary" type="submit">保存</button>
        <button id="reset" class="button button-primary" type="reset">重置</button>
    </div>
</form>

<!--寺库-->
<form  id="form_siku"  action="?app_act=sys/platform_params/update_params&app_fmt=json" method="post" style = "display:none;">
    <table cellspacing="0" style="width:90%" class="table table-bordered" id="form_tbl">
        <?php
        $param_code_arr = array();
        foreach($response['siku'] as $k=>$v){

        foreach($v  as $value){
        $param_code_arr[] = $value["param_code"];
        echo '<tr><td style="width:200px;">'.$value["param_name"].'</td>';
        ?>
        <?php
        switch ($value['type'])  {
        case "text":
            ?>

            <td style="width:100px;"><input type="text"  data-rules="{required: true}" value="<?php echo $value['value']; ?>" name="<?php echo $value['param_code']; ?>"> 月</td>
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


            <?php break; ?>
            <?php }//switch ?>
            <?php
            echo '<td>'.$value["memo"].'</td></td></tr>';
            }

            }?>

    </table>
    <input type="hidden" id="param_code_all" name="param_code_all"  value="<?php echo implode(",", $param_code_arr); ?>" />
    <div style="text-align: center;">
        <button class="button button-primary" type="submit">保存</button>
        <button id="reset" class="button button-primary" type="reset">重置</button>
    </div>
</form>


<script type="text/javascript">
    $(document).ready(function () {
        //TAB选项卡
        $(".oms_tabs a").click(function () {
            $(".oms_tabs").find(".active").removeClass("active")
            $(this).parent("li").addClass("active")
            if ($(this).attr('id') == "taobao") {
                $("#form_erp").hide();
                $("#form2").show();
                $("#form1").hide();
                $("#form_jd").hide();
                $("#form_ag").hide();
                $("#form_siku").hide();
            }
            if ($(this).attr('id') == "erp") {
                $("#form_erp").show();
                $("#form2").hide();
                $("#form1").hide();
                $("#form_jd").hide();
                $("#form_ag").hide();
                $("#form_siku").hide();
            }
            if ($(this).attr('id') == "common") {
                $("#form_erp").hide();
                $("#form2").hide();
                $("#form1").show();
                $("#form_jd").hide();
                $("#form_ag").hide();
                $("#form_siku").hide();
            }
            if ($(this).attr('id') == "jingdong") {
                $("#form_erp").hide();
                $("#form2").hide();
                $("#form1").hide();
                $("#form_jd").show();
                $("#form_ag").hide();
                $("#form_siku").hide();
            }
            if ($(this).attr('id') == "ag") {
                $("#form_erp").hide();
                $("#form2").hide();
                $("#form1").hide();
                $("#form_jd").hide();
                $("#form_siku").hide();
                $("#form_ag").show();
            }
            if ($(this).attr('id') == "siku") {
                $("#form_erp").hide();
                $("#form2").hide();
                $("#form1").hide();
                $("#form_jd").hide();
                $("#form_siku").show();
                $("#form_ag").hide();
            }
        })
        set_ag_param_show();
        if($('[name=aligenius_refunds_check]:checked').val() == 0) {
            $('[name=aligenius_upload_check]').parent().parent().hide();
        }
        $('[name=aligenius_refunds_check]').change(function() {
           if($(this).val() == 0) {
                $('[name=aligenius_upload_check]').parent().parent().hide();
                $('[name=aligenius_upload_check] :first').attr('checked', true);
           } else {
                $('[name=aligenius_upload_check]').parent().parent().show();               
           }
        });
    });


function getQueryString(name) {
	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
	var r = window.location.search.substr(1).match(reg);
	if (r != null) return unescape(r[2]); return null;
}


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

var form_erp =  new BUI.Form.HForm({
    srcNode : '#form_erp',
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

var form_siku =  new BUI.Form.HForm({
    srcNode : '#form_siku',
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

    var form_ag = new BUI.Form.HForm({
        srcNode: '#form_ag',
        submitType: 'ajax',
        callback: function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, function () {
                if (data.status == 1) {
                    ui_closePopWindow(getQueryString('ES_frmId'));

                }
            }, type);
        }
    }).render();

var form1 =  new BUI.Form.HForm({
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

var form_jd =  new BUI.Form.HForm({
    srcNode : '#form_jd',
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

    var ag_params = ['aligenius_sendgoods_cancel_th', 'aligenius_refunds_check_th', 'aligenius_warehouse_update_th', 'aligenius_deliver_refunds_check_th','taobao_ag_shop_th', 'aligenius_upload_check_th'];
    $(".aligenius_enable").click(function () {
        set_ag_param_show();
    });
    
    function set_ag_param_show() {
        if ($(".aligenius_enable:checked").val() == 0) {
            for (var i in ag_params) {
                var param_id = ag_params[i];
                $("." + param_id).css('display', 'none');
            }
        } else {
            for (var i in ag_params) {
                var param_id = ag_params[i];
                $("." + param_id).css('display', '');
            }
        }
        set_ag_check_show();
    }
    function set_ag_check_show() {
        if($('[name=aligenius_refunds_check]:checked').val() == 0) {
            $('[name=aligenius_upload_check]').parent().parent().hide();
        }
    }
</script>
