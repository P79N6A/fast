

<?php if(CTX()->get_app_conf('is_strong_safe')&&!empty($response['data'])):?>
<script type="text/javascript" src="http://g.tbcdn.cn/sj/securesdk/0.0.3/securesdk_v2.js" id="J_secure_sdk_v2" data-appkey="<?php echo $response['data'];?>"></script>
<?php endif;?>