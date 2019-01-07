<?php

render_control('PageHead', 'head1', array('title' => '云打印模版',
    'links' => array(
        array('type' => 'js', 'js' => 'get_cloud_express_tpl()', 'title' => '下载&更新云打印模板'),
    ),
));
?>
<script type="text/javascript">

    function get_cloud_express_tpl() {
        var params = {};
        $.post("?app_act=sys/cloud_express_tpl/get_cloud_express_tpl", params, function(data) {
            if (data.status != 1) {
                BUI.Message.Alert(data.message, 'error');
            }
        }, "json")
    }
</script>