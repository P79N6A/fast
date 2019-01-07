
<ul  class="toolbar" style="margin-top: 10px;" id="<?php echo $id; ?>">
    <?php foreach($button as $btn): ?>
    <li><button class="button button-primary btn_<?php echo $btn['id']; ?> <?php if(isset($btn['hide'])&&$btn['hide'])echo 'hide'; ?>"><?php echo $btn['value']; ?></button></li>
    <?php 
    if(isset($btn['custom'])){
        $this->btn_custom_js[] = array('id'=>$btn['id'],'custom'=>$btn['custom']);
    }else {
        $this->btn_default_js[] = $btn['id'];
    }
    ?>
    <?php endforeach; ?>
    <?php if(!empty($check_box)):?>
    <?php foreach($check_box as $check):?>
    <li  style="float:right"><input type="checkbox" id="<?php echo $check['id']; ?>"><?php echo $check['value']; ?></li>
    <?php endforeach;?>
    <?php endif;?>
</ul>
<script>
    $(function(){
        var default_opts = [<?php echo "'".implode("','", $this->btn_default_js)."'"; ?>];
        <?php if(!$custom_js): ?>
        for(var i in default_opts){
	    var f = default_opts[i];
	    $("#<?php echo $id; ?> .btn_"+f).click(f);
	}
        <?php else: ?>
        for(var i in default_opts){
	    var f = default_opts[i];
	    <?php echo $custom_js; ?>("<?php echo $id; ?>",f);
	}
        <?php endif; ?>
        var custom_opts = $.parseJSON('<?php echo empty($this->btn_custom_js)?'':json_encode($this->btn_custom_js); ?>');
        for(var j in custom_opts){
            var g = custom_opts[j];
            $("#<?php echo $id; ?> .btn_"+g['id']).click(eval(g['custom']));
        }
    });
</script>
