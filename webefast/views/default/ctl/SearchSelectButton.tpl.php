
<div id="<?php echo $id; ?>" style="display: none">
	<div class="row">
            <?php foreach($options['fields'] as $button): ?>
		<div class="control-group">
			<label class="control-label"><?php echo $button['title']; ?></label>
                        <div class="controls" style="<?php echo isset($options['style'])?$options['style']:''; ?>">
				<div class="button-group" id="<?php echo $button['id']; ?>">
				</div>
			</div>
		</div>
            <?php endforeach; ?>
	</div>
</div>
<script>
function ToolbarMaker(Toolbar,field){
    var g1 = new Toolbar.Bar({
	elCls : 'button-group',
	itemStatusCls  : {
		selected : 'active' //选中时应用的样式
	},
	defaultChildCfg : {
		elCls : 'button button-small',
		selectable : true //允许选中
	},
	children : field.children,
	render : '#'+field.id,
    });
    g1.render();
    g1.on('itemclick',function(ev) {
        //$('#l1').text(ev.item.get('id') + ':' + ev.item.get('content'));
    });
}
$(function(){
    
	$("#<?php echo $options['for']; ?>").find(".row").eq(0).before($("#<?php echo $id; ?>").html());
	$("#<?php echo $id; ?>").remove();
       var field = $.parseJSON('<?php echo json_encode($options['fields']); ?>');
	BUI.use('bui/toolbar',function(Toolbar){
            for(var f in field){
                ToolbarMaker(Toolbar,field[f]);
            }
	});
	tableStore.on('beforeload', function(e) {
            for(var f in field){
                var option = field[f];
                var id = option.id;
                e.params[id] = $("#"+id).find(".active").attr("id");
            }
            tableStore.set("params", e.params);
	});

});
</script>
