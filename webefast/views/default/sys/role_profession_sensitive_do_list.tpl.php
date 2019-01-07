 <table class='table_panel1' >
	    	<tr><td> 模糊化配置</td> <td> 示例值</td><td> 说明</td>
	    	</tr>
	    	
	    	 <?php foreach($response['sensitive_list'] as $k2=>$v2){ ?>
	    	<tr>
	    	<td> 	    	<input name="<?php echo 'sensi['.$v2['sensitive_code'].']'; ?>" type="checkbox"   <?php if($v2['sys_role_sensitive_data_id'] <> ''){ ?> checked    <?php } ?>  value="<?php echo $v2['sensitive_code'] ?>" />   <?php echo $v2['sensitive_name'] ?></td><td><?php echo $v2['example'] ?></td><td> <?php echo $v2['desc'] ?></td>
	    	 
	    	</tr>
	    	<?php } ?>
	    	
	    	
	    	</table>