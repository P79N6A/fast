<div class="order_wrap">
    <?php include get_tpl_path('top')?>
</div>
<?php include get_tpl_path('login');?>
<?php include get_tpl_path('register');?>
<?php echo load_js('login_reg.js',true);?> 
<script>
$(function(){
        $(".gnjs a").each(function(i){
            $(this).click(function(){
                $(".gnjs a").removeClass("gnjs_on").eq(i).addClass("gnjs_on");
                $(".hxgn_cont .gnjs_box").hide().eq(i).show();
            });
        });
});
</script>