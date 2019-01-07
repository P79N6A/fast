<?php
render_control(
    'PageHead',
    'head1',
    array(
        'title' => '手持管理',
	'links' => array()
));?>
<div class="row">
    <div class="span16 doc-content">
        <div class="row">
            <div class="control-group span12">
                <label style="padding-top: 5px;" class="control-label span3">硬件编号：</label>
                <div class="controls">
                    <input id="hardware_num" type="text" class="control-text">
                    <button onclick="get_code();" type="button" class="button">点击生成</button>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group span12">
                <label style="padding-top: 10px;" class="control-label span3">验证码生成：</label>
                <div style="padding-top: 5px;" class="controls">
                    <textarea style="display: inline-block; resize: none; height: 1.5em;" id="code"></textarea>
                    <button onclick="copy();" class="button">复制</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function copy()
    {
        var e = document.getElementById("code");//对象是contents 
        e.select(); //选择对象 
        document.execCommand("Copy");
        BUI.Message.Alert("复制成功");
    }
    
    function get_code()
    {
        $.post(
            '?app_act=products/code_manage/gen_code',
            {hardware_num: $('#hardware_num').val()},
            function (res) {
                $('#code').val(res.data.code);
            },
            'json'
        );
    }
</script>
