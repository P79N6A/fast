<ul class="nav-tabs oms_tabs">
    <li class='bui-tab-panel-item11 '><a href="#" id="order" >网络订单</a></li>
    <li class='bui-tab-panel-item11 '><a href="#" id="waves" >配发货</a></li>
    <li class='bui-tab-panel-item11 '><a href="#" id="pur">进销存</a></li>
    <li class='bui-tab-panel-item11 '><a href="#" id="finance">财务</a></li>
    <li class="bui-tab-panel-item11 "><a href="#"  id="op" >运营</a></li>
    <li class='bui-tab-panel-item11 '><a href="#" >会员管理</a></li>
    <li class='bui-tab-panel-item11 '><a href="#" id="params_goods">商品管理</a></li>
    <li class="bui-tab-panel-item11  active"><a href="#"  id="sys" >系统管理</a></li>
    <li class="bui-tab-panel-item11 "><a href="#"  id="second" >二次开发</a></li>
    <li class="bui-tab-panel-item11 "><a href="#"  id="app" >高级应用</a></li>

</ul>



<form  id="form2"  action="?app_act=sys/params/update_params&app_fmt=json" method="post" style = "display:none;">
<!--<table cellspacing="0" style="width:90%" class="table table-bordered" id="form_tbl">-->
<!---->
<!--<tr><td>s001_001 低于以下金额,订单需要财审</td><td><input type ="text" name ="fanance_money"  value="" />元<br/>-->
<!--注：只能配置0或大于0的整数<td>财审为非必须流程，左边配置项默认为0，表示不启用财审，若配置大于0的数字，当系统订单的应收金额低于配置金额，则订单需要财审，才能通知配货</td>-->
<!--</td></tr>-->
<!---->
<!--<tr><td>S001_002 订单确认操作后(无需财审)，系统自动通知配货 </td>-->
<!---->
<!--<td><input type ="radio" name ="oms_notice" value ="1" id="circul" onclick ='javascript:$("#tips").show()'/>开启<input type ="radio" name ="oms_notice"  value ="0" id="nocircul" onclick ='javascript:$("#tips").hide()'/>关闭-->
<!--<br/>-->
<!--<div id="tips" style="display:none;">-->
<!--系统自动通知截止发货时间：-->
<!--<input type="text" name="off_deliver_time" value=""/>天-->
<!--</div>-->
<!--</td>-->
<!--<td>-->
<!---->
<!--开启后：-->
<!---->
<!--1.财审操作后的订单，系统自动通知配货，无需客服作。-->
<!---->
<!--2.自动通知配货假定设置了3天，那么所有计划发货时间3天内的订单将自动通知配货，超过3天的订单不会自动通知配货。适用预售场景-->
<!---->
<!--3.通知配货操作后，系统将自动解锁相应的订单-->
<!--</td>-->
<!--</tr>-->
<!--</table>-->


<table cellspacing="0" style="width:90%" class="table table-bordered" id="form_tbl">

<?php
$param_code_arr = array();
foreach($response['order'] as $k=>$v){

foreach($v  as $value){
    $param_code_arr[] = $value["param_code"];
if($value["param_code"] == 'off_deliver_time' || $value["param_code"] == 'return_order_to_delete'){
    echo '<tr>
            <td style="vertical-align:middle;">'.$value["remark"].'</td>
            <td style="vertical-align:middle;">
            <input type="text" name="'.$value["param_code"].'" value="'.$value["value"].'" />天
            </td>
            <td>'.$value["memo"].'</td>
          </tr>';
    if($value["param_code"] == 'return_order_to_delete'){
        echo '<input type="hidden" name="sjs_delete" value="'.$value["value"].'" />';
    }
}else if($value["param_code"] == 'is_allowed_exceed'){//不允许超过订单商品数
echo '<tr><td>'.$value["param_name"].'</td><td> ';
foreach ($value['form_desc'] as $r_k => $r_v){
	echo '<input type="radio" style="width:40px;" class="'. $value['param_code'].'" name="is_allowed_exceed"
                              value="'.$r_k.'"';
	if ($r_k == $value['value']){ echo  'checked="checked"';}
	echo  '/>'.$r_v;
}

echo '<td>'.$value["memo"].'</td></tr>';
}else {

if($value["param_code"] == 'tmall_return' || $value["param_code"] == 'order_link' || $value["param_code"] == 'fanance_money' )
	continue;

echo '<tr><td>'.$value["param_name"].'</td>';
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

 }
 }?>

</table>
<input type="hidden" id="param_code_all" name="param_code_all"  value="<?php echo implode(",", $param_code_arr); ?>" />
<div style="text-align: center;">
        <button class="button button-primary" type="submit">保存</button>
        <button id="reset" class="button button-primary" type="reset">重置</button>
</div>

</form>

<!-- 配发货 -->
<form id="form3" method="post" action="?app_act=sys/params/do_save" tabindex="0" style = "display:none;" >
 <table cellspacing="0" style="width:90%" class="table table-bordered" id="form_tbl">
       <?php foreach($response['waves'] as $k=>$v){ ?>
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

<!-- 系统管理 -->

<form id="form1" method="post" action="?app_act=sys/params/do_save" tabindex="0" style="outline: none;" >
 <table cellspacing="0" style="width:90%" class="table table-bordered" id="form_tbl">
       <?php foreach($response['data'] as $k=>$v){ ?>
            <?php foreach($v  as $value){ ?>
            <tr>
                <td width="30%" ><?php echo $value['param_name']; ?></td>
                 <?php  switch ($value['type'])  {
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

<!-- 二次开发 -->
<form id="second_develop" method="post" action="?app_act=sys/params/do_save" tabindex="0" style = "display:none;" >
 <table cellspacing="0" style="width:90%" class="table table-bordered" id="form_tbl">
       <?php foreach($response['second'] as $k=>$v){ ?>
            <?php foreach($v  as $value){ ?>
            <tr>
                <td width="30%" ><?php echo $value['param_name']; ?></td>
                 <?php  switch ($value['type'])  {
                     	     case "text":
                     	     	?>

                    <td style="width:100px;"><input type="text"  data-rules="{required: true}" value="<?php echo $value['value']; ?>" name="<?php echo $value['param_code']; ?>"> </td>
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

<!-- 高级应用 -->
<form id="high_app" method="post" action="?app_act=sys/params/do_save" tabindex="0" style = "display:none;" >
 <table cellspacing="0" style="width:90%" class="table table-bordered" id="form_tbl">
       <?php foreach($response['app'] as $k=>$v){ ?>
            <?php foreach($v  as $value){ ?>
            <tr>
                <td width="30%" ><?php echo $value['param_name']; ?></td>
                 <?php  switch ($value['type'])  {
                     	     case "text":
                     	     	?>

                    <td style="width:100px;"><input type="text"  data-rules="{required: true}" value="<?php echo $value['value']; ?>" name="<?php echo $value['param_code']; ?>"> <?php echo isset($value['remark'])?$value['remark']:''; ?></td>
                             <?php break;
                            case  "time":
                            ?>
                    <td style="width:100px;"><input type="text" style="width:150px;" class= ' calendar ' id = "code"  data-rules="{required: true}" value="<?php echo $value['value']; ?>" name="<?php echo $value['param_code']; ?>"></td>
                            <?php break; ?>
                             <?php break;
                            case  "datetime":
                            ?>
                    <td style="width:100px;"><input type="text" style="width:150px;" class= ' calendar calendar-time' id = "code"  data-rules="{required: true}" value="<?php echo $value['value']; ?>" name="<?php echo $value['param_code']; ?>"></td>
                            <?php break; ?>

                            <?php case  "select":?>
                            <td style="width:100px;">
                                <?php if ($value['param_code'] == 'cainiao_intelligent_shop') { ?>
                                    <select class="<?php echo $value['param_code']; ?>" name="<?php echo $value['param_code']; ?>" >
                                        <?php foreach($response['shop']  as $r_k => $r_v){ ?>
                                            <option  value ="<?php echo $r_v['shop_code']; ?>" <?php if($value['value'] == $r_v['shop_code'] ) { ?> selected <?php } ?> ><?php echo $r_v['shop_name']; ?></option>
                                        <?php } ?>
                                    </select>
                                <?php } else { ?>
                             <select class="<?php echo $value['param_code']; ?>" name="<?php echo $value['param_code']; ?>" >
                             <?php foreach($value['form_desc']  as $r_k => $r_v){ ?>
                                 <option  value ="<?php echo $r_k; ?>" <?php if($value['value'] == $r_k ) { ?> selected <?php } ?> ><?php echo $r_v; ?></option>
                             <?php } ?>
                              </select>
                            <?php }?>
                            </td>
                            <?php break; ?>
                            <?php case  "radio":?>

                             <td style="width:400px;">


                             <?php foreach($value['form_desc']  as $r_k => $r_v){ ?>
                             <input type="radio" style="width:40px;" class="<?php echo $value['param_code']; ?>" name="<?php echo $value['param_code']; ?>"
                                    value="<?php echo $r_k; ?>" <?php if ($value['value'] == $r_k){  ?> checked="checked"   <?php } ?> <?php if($value['param_code'] == 'default_invoice'){ ?> onclick="change_invoice_params(this,'<?php echo $value['data']; ?>')"  <?php }?>/><?php echo $r_v; ?>
                             <?php if($value['param_code'] == 'default_invoice' && $r_k == 1){?>
                             <div class="_invoice"></div>
                             <?php }?>
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


<!--进销存-->
<form id="pur_purchase" method="post" action="?app_act=sys/params/do_save" tabindex="0" style = "display:none;" >
 <table cellspacing="0" style="width:90%" class="table table-bordered" id="form_tbl">
       <?php foreach($response['pur'] as $k=>$v){ ?>
            <?php foreach($v  as $value){ ?>
            <tr>
                <td width="30%" ><?php echo $value['param_name']; ?></td>
                 <?php  switch ($value['type'])  {
                     	     case "text":	
                     	     	?>
                    	
                    <td style="width:100px;"><input type="text"  data-rules="{required: true}" value="<?php echo $value['value']; ?>" name="<?php echo $value['param_code']; ?>"> <?php echo isset($value['remark'])?$value['remark']:''; ?></td>
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


<!--财务-->
<form id="finance_tab" method="post" action="?app_act=sys/params/do_save" tabindex="0" style = "display:none;" >
 <table cellspacing="0" style="width:90%" class="table table-bordered" id="form_tbl">
       <?php foreach($response['finance'] as $k=>$v){ ?>
            <?php foreach($v  as $value){ ?>
            <tr>
                <td width="30%" ><?php echo $value['param_name']; ?></td>
                 <?php  switch ($value['type'])  {
                     	     case "text":	
                     	     	?>
                    	
                    <td style="width:100px;"><input type="text"  data-rules="{required: true}" value="<?php echo $value['value']; ?>" name="<?php echo $value['param_code']; ?>"> <?php echo isset($value['remark'])?$value['remark']:''; ?></td>
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

<!-- 运营 -->
<form id="op_moudle" method="post" action="?app_act=sys/params/do_save" tabindex="0" style = "display:none;" >
 <table cellspacing="0" style="width:90%" class="table table-bordered" id="form_tbl">
       <?php foreach($response['op'] as $k=>$v){ ?>
            <?php foreach($v  as $value){ ?>
            <tr>
                <td width="30%" ><?php echo $value['param_name']; ?></td>
                 <?php  switch ($value['type'])  {
                     	     case "text":
                     	     	?>

                    <td style="width:100px;"><input type="text"  data-rules="{required: true}" value="<?php echo $value['value']; ?>" name="<?php echo $value['param_code']; ?>"> <?php echo isset($value['remark'])?$value['remark']:''; ?></td>
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
<!-- 配发货 -->
<form id="form_goods" method="post" action="?app_act=sys/params/goods_do_save" tabindex="0" style = "display:none;margin-top:6px" >
	<table cellspacing="0" style="width:90%" class="table table-bordered" id="form_tbl">
		<tr>
			<th>店铺</th>
			<th>库存同步百分比<img height="23" width="23" src="assets/images/tip.png" title="同步库存数 =（实物库存 - 实物锁定 - 缺货数 - 安全库存）* 同步比例 - 平台未转单数" /></th>
		</tr>
		<?php
			foreach($response['kc_sync_cfg'] as $sub_cfg){
				echo "<tr><td>{$sub_cfg['shop_name']}</td><td><input type='text' name='kc_sync_cfg_{$sub_cfg['shop_code']}' value='{$sub_cfg['value']}'/>%</td></tr>";
			}
		?>
	</table>
	<div style="text-align: center;">
	        <button class="button button-primary" type="submit">保存</button>
	        <button id="reset" class="button button-primary" type="reset">重置</button>
	</div>
    <div class="row" style="color: #ff0033;margin-top: 50px">
        <span>说明：同步库存数 =（实物库存 - 实物锁定 - 缺货数 - 安全库存）* 同步比例 - 平台未转单数</span>
    </div>
</form>
<?php echo load_js('print/lodop/LodopFuncs.js'); ?>
 <script type="text/javascript">
        BUI.use('bui/calendar',function(Calendar){
          var datepicker = new Calendar.DatePicker({
            trigger:'.calendar',
            autoRender : true
          });
        });
    </script>

<script type="text/javascript">
BUI.use('bui/form',function (Form) {
    var form1 = new BUI.Form.HForm({
        srcNode : '#form1',
        submitType : 'ajax',
        callback : function(data){
            if(data.status != '1'){
                BUI.Message.Alert(data.message, 'error')
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
$(document).ready(function(){
    var cainiao_url="https://z.cainiao.com/delivery/sdeStrategyConfig.htm";
	//TAB选项卡
	$(".oms_tabs a").click(function(){
		$(".oms_tabs").find(".active").removeClass("active")
		$(this).parent("li").addClass("active")
		if ($(this).attr('id') == "order"){
			$("#form1").hide();
			$("#form3").hide();
			$("#form_goods").hide();
			$("#second_develop").hide();
			$("#high_app").hide();
                        $("#op_moudle").hide();
			$("#form2").show();
                        $("#pur_purchase").hide();
                        $("#finance_tab").hide();
		}
		if ($(this).attr('id') == "waves"){
			$("#form1").hide();
			$("#form2").hide();
			$("#form_goods").hide();
			$("#second_develop").hide();
			$("#high_app").hide();
                        $("#op_moudle").hide();
			$("#form3").show();
                        $("#pur_purchase").hide();
                        $("#finance_tab").hide();
		}
		if ($(this).attr('id') == "sys"){
			$("#form1").show();
			$("#form2").hide();
			$("#form3").hide();
			$("#second_develop").hide();
			$("#form_goods").hide();
			$("#high_app").hide();
                        $("#op_moudle").hide();
                        $("#pur_purchase").hide();
                        $("#finance_tab").hide();
		}
		if ($(this).attr('id') == "params_goods"){
			$("#form1").hide();
			$("#form2").hide();
			$("#form3").hide();
			$("#second_develop").hide();
			$("#form_goods").show();
			$("#high_app").hide();
                        $("#op_moudle").hide();
                        $("#pur_purchase").hide();
                        $("#finance_tab").hide();
		}
		if ($(this).attr('id') == "second"){
			$("#form1").hide();
			$("#form2").hide();
			$("#form3").hide();
			$("#form_goods").hide();
			$("#second_develop").show();
			$("#high_app").hide();
                        $("#op_moudle").hide();
                        $("#pur_purchase").hide();
                        $("#finance_tab").hide();
		}
		if ($(this).attr('id') == "app"){
			$("#form1").hide();
			$("#form2").hide();
			$("#form3").hide();
			$("#form_goods").hide();
			$("#second_develop").hide();
			$("#high_app").show();
                        $("#op_moudle").hide();
                        $("#pur_purchase").hide();
                        $("#finance_tab").hide();
                        var raido_type = $("#high_app input[name='default_invoice']:checked").val();
                        var invoice_type = '<?php echo $response['invoice_data'] ?>';
                        append_html(invoice_type,raido_type);
		}
                if ($(this).attr('id') == "op"){
			$("#form1").hide();
			$("#form2").hide();
			$("#form3").hide();
			$("#form_goods").hide();
			$("#second_develop").hide();
			$("#high_app").hide();
                        $("#op_moudle").show();
                        $("#pur_purchase").hide();
                        $("#finance_tab").hide();
		}
                                //进销存页签
                 if ($(this).attr('id') == "pur"){
			$("#form1").hide();
			$("#form2").hide();
			$("#form3").hide();
			$("#form_goods").hide();
			$("#second_develop").hide();
			$("#high_app").hide();
                        $("#op_moudle").hide();
                        $("#pur_purchase").show();
                        $("#finance_tab").hide();
		}
                            //进销存页签
                 if ($(this).attr('id') == "finance"){
			$("#form1").hide();
			$("#form2").hide();
			$("#form3").hide();
			$("#form_goods").hide();
			$("#second_develop").hide();
			$("#high_app").hide();
                        $("#op_moudle").hide();
                        $("#pur_purchase").hide();
                        $("#finance_tab").show();
		}
	});
                var page_no = '<?php echo $response['page_no']?>';
        if(page_no == 'app'){
            	$(".oms_tabs").find(".active").removeClass("active")
		$("#app").parent("li").addClass("active")
                $("#form1").hide();
                $("#form2").hide();
                $("#form3").hide();
                $("#form_goods").hide();
                $("#second_develop").hide();
                $("#high_app").show();
                $("#op_moudle").hide();
                $("#pur_purchase").hide();
                $("#finance_tab").hide();
        }else if( page_no =='op'){
            $(".oms_tabs a#op").click();
        }else if( page_no =='waves'){
            $(".oms_tabs a#waves").click();
        }
        $('input:radio[name="fx_finance_account_manage"]').click(function (){
            var fx_account = $('input:radio[name="fx_finance_account_manage"]:checked').val();
            if(fx_account == 0) {
                BUI.Message.Alert("您将关闭预存款账户功能！</br>此账户交易流水及账户余额信息将在您保存设置后全部清空。");
            }
        });
        $('input:radio[name="inv_sync"]').click(function (){
            var fx_account = $('input:radio[name="inv_sync"]:checked').val();
            if(fx_account == 1) {
                BUI.Message.Alert("请至运营->策略管理->库存同步策略中新增策略并启用，否则不会生效！");
            }
        });
        $('input:radio[name="presell_plan"]').click(function (){
            var fx_account = $('input:radio[name="presell_plan"]:checked').val();
            if(fx_account == 1) {
                BUI.Message.Alert("请至运营->预售管理->预售计划，新增并配置预售计划");
            }
        });
        $('input:radio[name="express_ploy"]').click(function (){
            var fx_account = $('input:radio[name="express_ploy"]:checked').val();
            if(fx_account == 1) {
                BUI.Message.Alert("请至运营->策略管理->快递适配策略中进行新增并配置快递策略");
            }
        });
//	var oms_notice = <?php //echo $response['oms_notice']['oms_notice'] ?>
//
//	if(oms_notice == 1){
//		$("#circul").attr("checked","checked");
//		$("#tips").show();
//	}else{
//		$("#nocircul").attr("checked","checked");
//	}


    $("input[name='cainiao_intelligent_delivery']").change(function () {
        var obj = $("input[name='cainiao_intelligent_delivery']:checked").val();
        //选择开启时弹窗
        if (obj == 1) {
            BUI.Message.Show({
                title: '提示',
                msg: '启用后，需要前往菜鸟订购服务并且填写发货策略。是否继续？',
                icon: 'question',
                buttons: [
                    {
                        text: '继续',
                        elCls: 'button button-primary',
                        handler: function () {
                            window.open(cainiao_url);
                            this.close();
                        }
                    },
                    {
                        text: '放弃',
                        elCls: 'button',
                        handler: function () {
                            $("input[name='cainiao_intelligent_delivery']").eq(0).attr("checked", "checked");
                            this.close();
                        }
                    }
                ]
            });
        }
    });
    download_clodop();

});


function getQueryString(name) {
	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
	var r = window.location.search.substr(1).match(reg);
	if (r != null) return unescape(r[2]); return null;
}


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
var form3 =  new BUI.Form.HForm({
    srcNode : '#form3',
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


var form_goods =  new BUI.Form.HForm({
    srcNode : '#form_goods',
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
var second_develop =  new BUI.Form.HForm({
    srcNode : '#second_develop',
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
var high_app =  new BUI.Form.HForm({
    srcNode : '#high_app',
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
var op =  new BUI.Form.HForm({
    srcNode : '#op_moudle',
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
//进销存
var pur =  new BUI.Form.HForm({
    srcNode : '#pur_purchase',
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
//财务
var pur =  new BUI.Form.HForm({
    srcNode : '#finance_tab',
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
function download_clodop(){
        var strHtmInstall="http://www.mtsoftware.cn/download/CLodop_Setup_for_Win32NT_2.112.zip";;	
        document.getElementById("down_lodop").href=strHtmInstall
}
$('input[name="return_order_to_delete"]').blur(function(){
    var num = $('input[name="return_order_to_delete"]').val();
    if((/^(\+|-)?\d+$/.test( num )) && num > 0){
        return true;
    }else{
        BUI.Message.Alert('未确认的售后服务单自动作废的天数必须为正整数','error');
        var default_num = $('input[name="sjs_delete"]').val();
        $('input[name="return_order_to_delete"]').val(default_num);
    }
})
//修改发票信息的参数
function change_invoice_params(obj,invoice_type){
    var radio_val = $(obj).val();
    append_html(invoice_type,radio_val);
}
//追加html标签
function append_html(invoice_type,radio_val){
    var html = '';
    if(invoice_type == 'vat_invoice'){ //纸质发票
         html = '<span>&nbsp;&nbsp;&nbsp;&nbsp;开票抬头：个人</span><div>&nbsp;&nbsp;&nbsp;&nbsp;发票类型：<select style="width:100px" name="invoice_msg"><option value="pt_invoice">电子发票</option><option value="vat_invoice" selected="selected">纸质发票</option></select></div>';
    }else{
        html = '<span>&nbsp;&nbsp;&nbsp;&nbsp;开票抬头：个人</span><div>&nbsp;&nbsp;&nbsp;&nbsp;发票类型：<select style="width:100px" name="invoice_msg"><option value="pt_invoice" selected="selected">电子发票</option><option value="vat_invoice">纸质发票</option></select></div>';
    }
    if(radio_val == 1){//单选状态
        $("#high_app ._invoice").html(html);
    }else{
        $("#high_app ._invoice").html('');
    }
}
</script>
