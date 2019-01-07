<style>
.account_rev_wrap{ margin-top:82px; border-top:1px solid #e5e5e5;}
.account_rev_wrap .account_rev{ width:990px; margin:0 auto; text-align:center; padding-top:115px;}
.account_rev_wrap .account_rev .lightning{ height:145px; background:url(assets/img/clouds.png) no-repeat center top;}
.account_rev_wrap .account_rev .lightning .light{ display:inline-block; -webkit-animation:flash 1s .5s ease both; -moz-animation:flash 1s .5s ease both; margin-top:60px;}
.account_rev_wrap .account_rev strong{ display:inline-block; padding:60px 0 10px; font-size:24px; color:#e95513; font-weight:normal;}
.account_rev_wrap .account_rev .word{ font-size:18px; color:#666; padding-bottom:25px;}
.account_rev_wrap .account_rev .btns button.btn_01{ padding:7px 35px; border:3px solid #999; background:#FFF; color:#666; font-size:20px; cursor:pointer;}
.account_rev_wrap .account_rev .btns button.btn_01:hover{color:#ee571b; border-color:#ee571b;}
</style>
<div class="order_wrap">
    <?php include get_tpl_path('top')?>
    <div class="account_rev_wrap">
        <div class="account_rev">
            <p class="lightning">
                <span class="flashes light"><img src="assets/img/lightning.png" width="54" height="84"></span>
            </p>
            <strong>对不起，您的账户还没有通过审核！</strong>
            <p class="word">我们会尽快对您的账户信息进行审核，请您耐心等待。</p>
            <p class="btns"><button class="btn_01" type="button" onclick="window.history.back();">返&nbsp; 回</button></p>
        </div>
    </div>
</div>
<?php include get_tpl_path('login');?>
<?php include get_tpl_path('register');?>
<?php echo load_js('login_reg.js',true);?> 
<script>
    $('body').css("background","#fafafa");
</script> 