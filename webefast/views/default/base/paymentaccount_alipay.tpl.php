<style>
    #miyao{
        height:200px;
        width:430px;
    }
    .msg{
        color:red;
    }
</style>
<div>
支付宝企业账户：<br/>
<input type='text' class='account'>
</div>
<div>
合作者身份（Partner ID）：<br/>
<input type='text' class='id'>
</div>
<div>
安全校验码（Key）：<br/>
<input type='text' class='key'>
</div>
<div>
状态
<input type='radio' name='status' class='status' checked>启用
<input type='radio' name='status' class='status'>停用
</div>
<div>
    <p class='msg'>重要提醒：设置账号前，请先确认您已签约支付宝 “即时到账收款”与“移动支付”收款服务</p><a href='' >查看帮助</a>
</div>
<p>如何设置支付宝签约信息？</p>
<div>
    1.访问支付宝商户服务中心（<a href=''>b.alipay.com</a>）,用你的签约支付宝账号登录。<br/>
2.在“我的商家服务”中，点击“查询PID,Key”,将查询的相应信息填写到上面相应输入框中。<br/>
3.在上述打开的支付宝页面的“合作伙伴密钥管理”下，点击“RSA加密”后的“添加密钥”。<br/>
  把下面的密钥复制到弹出框内（注意不要多余的空格）：
</div>
<input type="text" name="miyao" id="miyao" /><br/><br/>
<div class="clearfix" style="text-align: center;">
<button class="button button-primary" id="btn_pay_ok">确定</button>
<button class="button button-primary" id="">取消</button>
</div>

