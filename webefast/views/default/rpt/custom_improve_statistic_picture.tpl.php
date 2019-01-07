<?php echo load_js('util/echarts.common.min.js'); ?>
<style type="text/css">
    .container {
        margin: 5px 5px;
        padding: 0px;
    }
    .pull_top {
        /*margin-left: 400px;*/
        float: right;
        margin-bottom: 1px;
        font-size: 16px;
    }
</style>
<div id="container_div" class="container">
    <div class="pull_top">
    </div>
    <table style="width:100%">
        <tr>
            <td id="td_charts" style="width:100%">
                <div id="charts"></div>
            </td>
        </tr>
    </table>
</div>
<script type="text/javascript">
    $(function () {
        // 根据页面跳转画布大小
        $('#charts').css({
            height: (parseInt($(window).height()-150)) + 'px',
            width: parseInt($('#td_charts').width()),
        });
    });
</script>