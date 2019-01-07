<style>
<!--
table th{color:red;}
.advise-submit-button {
border-radius: 5px;
    margin-bottom: 20px;
    margin-top: 20px;
    padding: 19px 20px 20px;
    text-align:center;
}
-->
</style>

<div class="row">
  <div class="span18 offset3 doc-content">
      <table cellspacing="0" class="table table-bordered advise_num">
        <thead>
          <tr>
            <th colspan="3" style="text-align:center;">补货数=（近30天平均销量*百分比% + 近7天平均销量*百分比）*补货天数-（实物库存-已付款未发货数）-在途数</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($response['data'] as $data){?>
        	<tr>
	            <td><?php echo $data['param_name'];?></td>
	            <td>
	            	<input type="text" name="<?php echo $data['param_code'];?>" id="<?php echo $data['param_code'];?>" value="<?php echo $data['value'];?>">
	            	<?php if ($data['param_code'] == 'pur_advise_day'){echo '天';}else{echo '%';}?>
	            </td>
	            <td><?php echo $data['remark']?></td>
          	</tr>
        <?php }?>
        </tbody>
      </table>
  </div>

</div>
<div class="row form-actions">
	<div class="span18 offset3 advise-submit-button">
		<button type="button" class="button button-primary" id="btn_submit">提交</button>
		<button type="button" class="button" id=btn_close>关闭</button>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#btn_submit").click(function(){
            var params = {
            	"week_proportion":$('#week_proportion').val(),
                "month_proportion": $('#month_proportion').val(),
                "pur_advise_day": $("#pur_advise_day").val()
            };
            $.post("?app_act=op/pur_advise/save_param&app_fmt=json", params, function(data){
                BUI.Message.Alert(data.message, 'info')
                //ui_closePopWindow("<?php echo $request['ES_frmId']?>")
            }, "json")
        })

        $("#btn_close").click(function(){
        	ui_closePopWindow('<?php echo $request['ES_frmId']; ?>');
        });
    })
    
  
</script>