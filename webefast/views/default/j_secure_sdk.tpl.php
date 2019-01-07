<?php if(CTX()->get_app_conf('is_strong_safe')&&!empty(CTX()->get_session('app_key',true))):?>
<script type="text/javascript" src="http://g.tbcdn.cn/sj/securesdk/0.0.3/securesdk_v2.js" id="J_secure_sdk_v2" data-appkey="<?php echo CTX()->get_session('app_key',true);?>"></script>
<?php endif;?>