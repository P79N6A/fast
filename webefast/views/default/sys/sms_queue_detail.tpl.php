<?php render_control('PageHead', 'head1',
    array('title' => isset($app['title']) ? $app['title'] : '短信发送详情',
        'links' => array(
            'sys/sms_queue/do_list' => '短信发送列表',
        )
    ));
    //echo '<hr/>$response<xmp>'.var_export($response,true).'</xmp>';
    ?>

<form action="" class="form-horizontal form-horizontal-simple">
  <div class="control-group">
    <label class="control-label">会员：</label>
    <div class="controls">
    <span class="control-text"><?php echo $response['data']['user_nick'];?></span>
    </div>
  </div>
  <div class="control-group">
    <label class="control-label">手机号：</label>
    <div class="controls">
    <span class="control-text"><?php echo $response['data']['tel'];?></span>
    </div>
  </div>
  <div class="control-group">
    <label class="control-label">内&nbsp;
&nbsp;
容：</label>
    <div class="controls">
    <span class="control-text"><?php echo $response['data']['msg_content'];?></span>
    </div>
  </div>
  <div class="control-group">
    <label class="control-label">发送状态：</label>
    <div class="controls">
    <span class="control-text"><?php echo $response['data']['status_exp'];?></span>
    </div>
  </div>
</form>