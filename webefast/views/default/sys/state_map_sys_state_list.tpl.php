<?php echo load_js('comm_util.js') ?>
全链路状态
<br/>
<span style="color:red;"><?php echo $response['link_name']?></span>
<br/>
系统状态
<br/>
<!--<form  method="post" action="?app_act=sys/state_map/do_edit&app_fmt=json"  >-->
<?php
$sys_state = require_conf('sys/link_state');

//print_r($sys_state);exit;
foreach($sys_state  as $key => $value){
	
	echo '<label><input name="sys_state" type="radio" value="'.$key.'" />'.$value.'</label>';

}

?>
        <div class="row form-actions actions-bar">
          <div class="span13 offset3 ">
           <input type="hidden" id="id" name="id" value="<?php echo $response['id']?>"/>
            <button type="submit" class="button button-primary" id="submit2">提交</button>
            <button type="reset" class="button " id="reset">重置</button>
          </div>
        </div>

<!--</form>-->

<script type="text/javascript">        
    $('#submit2').click(function(){
    	get_checked();
    });   


    //读取选中项
    function get_checked() {

        var  id = $("#id").val();
        var sys_state = $("input[name=sys_state]:checked").val();

        var url = "?app_act=sys/state_map/do_edit&app_fmt=json";
     	$.ajax({ type: 'POST', dataType: 'json',
		url: url, data: {sys_state: sys_state,id: id},
		success: function(data) {

			if(data.status == 1){
               top.n_tableStore.load();
               ui_closePopWindow(<?php echo $response['ES_frmId'];?>);
             }
        }});
    }  

</script>       