<style>
    .btn_tpl_var{margin: 2px 0;} 
    #btn_preview,#btn_sms_num,#btn_send_test{color: #FFF;background-color: #1695ca;border-color: #1695ca;}
    .table td{padding:10px;}
    .table .first_td{text-align: right; width: 100px;}
    .bui-dialog .bui-stdmod-footer{text-align: center;}
</style>

<?php
render_control('PageHead', 'head1', array('title' => $app['title'],
    'links' => array(),
    'ref_table' => 'table'
));
?>
<form id="form1" method="post" action="<?php echo $app['scene'] == 'edit' ? '?app_act=op/sms_tpl/do_edit' : '?app_act=op/sms_tpl/do_add';?>" tabindex="0" style="outline: none;">
    <div class="panel">
        <div class="panel-body table_sms_tpl" id="table_sms_tpl">
            <input type="hidden" name="id" value="<?php echo isset($request['_id']) ? $request['_id'] : '';?>">
            <table cellspacing="0" class="table table-bordered" >
                <tbody>
                    <tr>
                        <td class="first_td">短信模板类型</td>
                        <td>
                            <select name = "tpl_type" id = "tpl_type" data-rules="{required : true}" <?php if($app['scene'] == 'edit'){echo  'disabled="disabled"';}?>>
                                <?php foreach ($response['select']['sms_tpl_type'] as $k => $v) { ?>
                                <option  value ="<?php echo $v[0]; ?>" <?php if (isset($response['data']['tpl_type']) && $response['data']['tpl_type'] === $v[0]) { ?> selected <?php } ?> ><?php echo $v[1]; ?></option>
                                <?php } ?>
                            </select>
                            <b style="color:red"> *</b>
                        </td>
                    </tr>
                    <tr>
                        <td class="first_td">短信模板名称</td>
                        <td>
                            <input id="tpl_name" class="bui-form-field" type="text" name="tpl_name" data-rules="{required : true}" value="<?php echo isset($response['data']['tpl_name']) ? $response['data']['tpl_name'] : ''; ?>">
                            <b style="color:red"> *</b>
                        </td>
                    </tr>
                    <tr>
                        <td class="first_td">短信签名</td>
                        <td>
                            <input id="sms_sign" class="bui-form-field" type="text" name="sms_sign" value="<?php echo isset($response['data']['sms_sign']) ? $response['data']['sms_sign'] : ''; ?>">
                        &nbsp;&nbsp;注: 设定签名后，会在短信内容前自动添加[签名内容]
                        </td>
                    </tr>
                    <tr>
                        <td class="first_td">模板内容</td>
                        <td>
                            <textarea name="sms_info" id="sms_info" class="bui-form-field" style="width: 700px;height: 100px;"><?php echo isset($response['data']['sms_info']) ? $response['data']['sms_info'] : '【{@店铺名称}】亲爱的会员{@会员昵称}，您的订单{@平台交易号}已发货啦，{@快递公司名称}快递单号为{@快递单号} ，请注意查收。'; ?></textarea>
                            <?php echo $response['select']['sms_tpl_var_str']?>
                        </td>
                    </tr>
                    <tr>
                        <td class="first_td">描述</td>
                        <td>
                            <textarea name="remark" id="remark" class="bui-form-field" style="width: 500px;height: 80px;"><?php echo isset($response['data']['remark']) ? $response['data']['remark'] : ''; ?></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div style="padding-left: 200px;">
        <button class="button button-primary" type="submit">保存</button>&nbsp;&nbsp;
        <?php if ($response['priv']['op/sms_tpl/do_preview']){ ?>
        <button class="button button-primary" type="button" id='btn_preview'>预览</button>&nbsp;&nbsp;
        <?php }?>
        <?php if ($response['priv']['op/sms_tpl/opt_word_count']){ ?>
        <button class="button button-primary" type="button" id='btn_word_count'>预计算字数及条数</button>&nbsp;&nbsp;
        <?php }?>
        <?php if ($response['priv']['op/sms_tpl/opt_send_test']){ ?>
        <button class="button button-primary" type="button" id='btn_send_test'>发送测试</button>
        <?php }?>
    </div>
</form>
<?php echo load_js('comm_util.js') ?>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    $(function(){
        //添加模板变量
        $.fn.extend({
            insertAtCaret: function (myValue) {
                var $t = $(this)[0];
                if (document.selection) {
                    this.focus();
                    sel = document.selection.createRange();
                    sel.text = myValue;
                    this.focus();
                } else
                    if ($t.selectionStart || $t.selectionStart == '0') {
                        var startPos = $t.selectionStart;
                        var endPos = $t.selectionEnd;
                        var scrollTop = $t.scrollTop;
                        $t.value = $t.value.substring(0, startPos) + myValue + $t.value.substring(endPos, $t.value.length);
                        this.focus();
                        $t.selectionStart = startPos + myValue.length;
                        $t.selectionEnd = startPos + myValue.length;
                        $t.scrollTop = scrollTop;
                    } else {
                        this.value += myValue;
                        this.focus();
                    }
            }
        });
        $(".btn_tpl_var").click(function () {
            var btn_name = $(this).attr('char-value');
            $("#sms_info").insertAtCaret(btn_name);
        });
        //预览
        $("#btn_preview").click(function () {
            do_preview();
        });
        //预计算字数及条数
        $("#btn_word_count").click(function () {
            word_count();
        });
        //发送测试
        $("#btn_send_test").click(function () {
            send_test();
        });
    });
    
    //表单提交事件
    var ES_frmId = '<?php echo $request['ES_frmId']; ?>';
    var scan = '<?php echo isset($app['scene']) ? $app['scene'] : "";?>';
    BUI.use('bui/form', function (Form) {
        var form1 = new BUI.Form.HForm({
            srcNode: '#form1',
            submitType: 'ajax',
            callback: function (data) {
                if (data.status != '1') {
                    BUI.Message.Alert(data.message, 'error');
                }else{
                    BUI.Message.Alert(data.message,function(){
                        if(scan == 'add'){//新增保存成功后, 关闭当前页
                            close_page();
                        }
                    }, 'success');
                }
                 return;
            }
        }).render();
        form1.on('beforesubmit', function () {
            //验证
            return true;
        });
    });
    BUI.use('bui/calendar', function (Calendar) {
        var datepicker = new Calendar.DatePicker({
            trigger: '.calendar',
            showTime: true,
            autoRender: true
        });
    });
    //关闭当前页
    function close_page(){
        $(window.parent.document).find(".tab-nav-actived .tab-item-close").click();
    }
    //预览
    function do_preview(){
        var sms_sign = $('#sms_sign').val();
        var sms_info = $('#sms_info').val();
        var html = '<div style="margin:10px;height:178px;overflow-y:scroll;">';
        if ('' != sms_sign){
            html += '【' + sms_sign + '】';
        }
        html += sms_info+'</div>';
        BUI.use('bui/overlay',function(Overlay){
            var dialog = new Overlay.Dialog({
                title:'预览',
                width:500,
                height:300,
                mask: true,
                buttons:[
                  {
                    text:'确认',
                    elCls : 'button button-primary',
                    handler : function(){
                      //do some thing
                      this.close();
                    }
                  }
                ],
                bodyContent:html
              });
            dialog.show();
        });
    }
    /**
     * 预计算字数及条数：英文字符、数字、汉字、中文字符占1个长度，每70个字符为一条短信
     * @returns {undefined}
     */
    function word_count(){
        var sms_info = $('#sms_info').val();
        sms_info = sms_info.replace(/\n|\r/gi,"");
        var sms_length = sms_info.length;
        var sms_num = Math.ceil(sms_length/70);
        var html = '预估长度【' + sms_length + '】,短信条数【' + sms_num + '】';
        BUI.use('bui/overlay',function(Overlay){
            var dialog = new Overlay.Dialog({
                title:'预计算字数及条数',
                width:400,
                height:200,
                mask: true,
                buttons:[
                  {
                    text:'确认',
                    elCls : 'button button-primary',
                    handler : function(){
                      //do some thing
                      this.close();
                    }
                  }
                ],
                bodyContent:html
              });
            dialog.show();
        });
    }
    //测试发送
    function send_test(){
        var params_str = $('#form1').serialize();
        new ESUI.PopWindow('?app_act=op/sms_tpl/send_test&app_scene=edit&' + params_str, {
            title: '发送测试',
            width:'800',
            height:'370',
            onBeforeClosed: function() {
            }
        }).show();
    }
</script>