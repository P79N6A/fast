<style>
    input[type="checkbox"] {
        margin-top: 6px;
    }
    .form-horizontal .control-label {
        width: 150px;
    }
    .row {
        margin-bottom: 10px;
    }
    .panel-body {
        padding: 0;
    }
    .panel-body table {
        margin: 0;
    }
    a {
        cursor: pointer;
    }
</style>
<?php
$links = array(//array('url' => 'value/value_add/shopping_cart', 'title' => '购物车', 'is_pop' => false, 'pop_size' => '500,400'),
);
render_control('PageHead', 'head1', array('title' => '购买明细',
    'links' => $links,
    'ref_table' => 'table'
));
?>
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="">购买者：<?php echo $response['user_code']?>（<?php echo $response['kh_name'] ?>）</h3>
    </div>
    <div class="panel-body" id="panel_shopping">
        <table cellspacing="0" class="table table-bordered" id="shopping_cart_data">
        </table>
        <table cellspacing="0" class="table table-bordered" id="">
            <tr>
                <td><strong>总计</strong>&nbsp;&nbsp;&nbsp;付款总计：<span id="money_all"></span>元&nbsp;&nbsp;&nbsp; 优惠：<span id="discount_all"></span>元</td>
            </tr>
        </table>
        <br />
        <div>
            &nbsp; <strong>应付总金额：<span id="pay_money_all"></span>元</strong>
        </div>
    </div>
</div>
<br />
<div class="row">
    <div class="control-group span11" style="width:1000px;">
        <div class="controls bui-form-group ">
            <input type="checkbox" name="sever_remind" id="sever_remind" class="field" checked value="1"/>&nbsp;服务到期前提醒
        </div>
    </div>
</div>
<div class="row">
    <div class="control-group span11" style="width:1000px;">
        <div class="controls bui-form-group ">
            <input type="checkbox" name="user_agree" id="user_agree" class="field"  value="1"/>&nbsp;<a id="user_note">同意用户协议</a>
        </div>
    </div>
</div>
<div class="row form-actions actions-bar">
    <div class="span13 offset3 ">
        <button type="button" class="button button-primary" id="immediate_pay" disabled >立即支付</button>
        <button type="button" class="button button-primary" style="margin-left: 10px;" id="continue_order"">继续订购</button>
    </div>
</div>
<div  id="content" class="hide">
    <div class="content" style="overflow:scroll; width:730px; height:430px;">
        <div width="680">
            <p class=MsoTitle><span style='font-family:"微软雅黑",sans-serif'>宝塔</span><span
                        lang=X-NONE style='font-family:"微软雅黑",sans-serif'>eFAST365软件</span><span
                        style='font-family:"微软雅黑",sans-serif'>协议</span><span style='font-size:10.5pt;
                        font-weight:normal'> </span></p>

            <p class=MsoNormal style='text-indent:21.0pt;line-height:20.0pt'><span
                        style='font-family:"微软雅黑",sans-serif'>甲乙双方，经友好协商一致，遵循诚实信用的原则，双方申明，双方都已理解并认可了本协议的所有内容，同意承担各自应承担的权利和义务，忠实地履行本协议，并共同遵守下列条款：</span></p>

            <p class=MsoNormal style='margin-top:7.8pt;margin-right:0cm;margin-bottom:7.8pt;
                   margin-left:42.0pt;text-indent:-42.0pt;line-height:20.0pt'><b><span lang=EN-US
                                                                                       style='font-family:"微软雅黑",sans-serif'>第一条<span style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;
                            </span></span></b><b><span style='font-family:"微软雅黑",sans-serif'>软件使用范围</span></b></p>

            <p class=MsoBodyTextIndent style='margin-left:42.5pt;line-height:20.0pt'><span
                        lang=X-NONE style='font-family:"微软雅黑",sans-serif'>本</span><span
                        style='font-family:"微软雅黑",sans-serif'>协议</span><span lang=X-NONE
                                                                             style='font-family:"微软雅黑",sans-serif'>许可的是软件使用权，许可使用的软件产品版权受《中华人民共和国著作权法》和其他有关法律、法规的保护。</span></p>

            <p class=MsoNormal style='text-indent:42.55pt;line-height:20.0pt'><span
                        style='font-family:"微软雅黑",sans-serif'>甲方根据自身的业务和管理需要，向乙方租用乙方的软件：</span></p>

            <p class=MsoNormal style='text-indent:42.55pt;line-height:20.0pt'><b><span
                            style='font-family:"微软雅黑",sans-serif;color:black'>宝塔<span lang=EN-US>eFAST 365</span>电子商务管理软件<span
                                lang=EN-US>V2.0</span></span></b></p>

            <p class=MsoNormal style='margin-left:42.0pt;text-indent:-21.0pt;line-height:
                   20.0pt'><span lang=EN-US style='font-family:"微软雅黑",sans-serif'>1.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        style='font-family:"微软雅黑",sans-serif'>如果甲方在产品使用后因业务增长需要单独部署，需订购云服务，单独签订云服务合同。</span></p>

            <p class=MsoNormal style='margin-top:7.8pt;margin-right:0cm;margin-bottom:7.8pt;
                   margin-left:42.0pt;text-indent:-42.0pt;line-height:20.0pt'><b><span lang=EN-US
                                                                                       style='font-family:"微软雅黑",sans-serif'>第二条<span style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;
                            </span></span></b><b><span style='font-family:"微软雅黑",sans-serif'>付款方式</span></b></p>

            <p class=MsoNormal style='margin-top:7.8pt;margin-right:0cm;margin-bottom:7.8pt;
                   margin-left:42.55pt;text-indent:-21.55pt;line-height:20.0pt'><b><span
                            lang=EN-US style='font-family:"微软雅黑",sans-serif'>1.<span style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;
                            </span></span></b><b><span style='font-family:"微软雅黑",sans-serif'>付款方式</span></b></p>

            <p class=chart style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;
                   margin-left:63.0pt;margin-bottom:.0001pt;text-indent:-21.0pt;line-height:20.0pt'><span
                        lang=EN-US style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>1.1.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>甲方租用乙方软件，采用账号汇款的方式付款。</span></p>

            <p class=chart style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;
                   margin-left:63.0pt;margin-bottom:.0001pt;text-indent:-21.0pt;line-height:20.0pt'><span
                        lang=EN-US style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>1.2.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>甲方于合同生效之日起<span
                            lang=EN-US>5</span>个工作日内，向乙方支付合同项下的套餐费用。</span></p>

            <p class=chart style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;
                   margin-left:63.0pt;margin-bottom:.0001pt;text-indent:-21.0pt;line-height:20.0pt'><span
                        lang=EN-US style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>1.3.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>上门指导（如需）按照上门培训收费标准进行收费，上门培训费用另外收取。</span></p>

            <p class=chart style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;
                   margin-left:63.0pt;margin-bottom:.0001pt;text-indent:-21.0pt;line-height:20.0pt'><span
                        lang=EN-US style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>1.4.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>合同期即将到期的最后一个月需提前一个月进行续费。</span></p>

            <p class=MsoNormal style='margin-top:7.8pt;margin-right:0cm;margin-bottom:7.8pt;
                   margin-left:42.55pt;text-indent:-21.55pt;line-height:20.0pt'><b><span<b><span lang=EN-US style='font-family:"微软雅黑",sans-serif'>2.<span
                                    style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span></b><b><span
                                style='font-family:"微软雅黑",sans-serif'>甲方同意以转账方式将本合同项下的费用支付至乙方指定下列银行账户，乙方不接受现金付款。如甲方以现金支付或未将款项支付至乙方指定银行账户，导致乙方未收到相关费用，视为甲方未支付。</span></b></p>

            <p class=MsoNormal style='margin-top:7.8pt;margin-right:0cm;margin-bottom:7.8pt;
                   margin-left:42.0pt;text-indent:-42.0pt;line-height:20.0pt'><b><span lang=EN-US
                                                                                       style='font-family:"微软雅黑",sans-serif'>第三条<span style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;
                            </span></span></b><b><span style='font-family:"微软雅黑",sans-serif'>交付内容、交付期限、交付方式</span></b></p>

            <p class=MsoBodyTextIndent style='margin-left:42.0pt;text-indent:-21.0pt;
                   line-height:20.0pt'><span lang=X-NONE style='font-family:"微软雅黑",sans-serif'>1.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        lang=X-NONE style='font-family:"微软雅黑",sans-serif'>交付内容：软件产品在协议内的使用及在线服务。</span></p>

            <p class=MsoBodyTextIndent style='margin-left:42.0pt;text-indent:-21.0pt;
                   line-height:20.0pt'><span lang=X-NONE style='font-family:"微软雅黑",sans-serif'>2.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        lang=X-NONE style='font-family:"微软雅黑",sans-serif'>交付期限：甲方到款日起的<u> 10 </u>个工作日。</span></p>

            <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:42.3pt;
                   margin-bottom:.0001pt;text-indent:-21.0pt;line-height:20.0pt'><span lang=EN-US
                                                                                       style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>3.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>在软件租用过程中，乙方默认提供产品远程部署与远程支持，如需上门服务需另外收费。</span></p>

            <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:42.3pt;
                   margin-bottom:.0001pt;text-indent:-21.0pt;line-height:20.0pt'><span lang=EN-US
                                                                                       style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>4.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>如果需要上门实施交付可另外签署上门实施合同。</span></p>

            <p class=MsoNormal style='margin-top:7.8pt;margin-right:0cm;margin-bottom:7.8pt;
                   margin-left:0cm;line-height:20.0pt'><b><span style='font-family:"微软雅黑",sans-serif'>第四条<span
                                lang=EN-US>&nbsp;&nbsp; </span>甲方的权利和义务</span></b></p>

            <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:42.55pt;
                   margin-bottom:.0001pt;text-indent:-21.25pt;line-height:20.0pt'><span
                        lang=EN-US style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>1.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>在软件租用过程中，甲方有责任与乙方相互配合，保证有专人负责甲、乙双方的沟通工作。</span></p>

            <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:42.55pt;
                   margin-bottom:.0001pt;text-indent:-21.25pt;line-height:20.0pt'><span
                        lang=EN-US style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>2.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>禁止出售、转售或复制、开发试用权限，禁止基于商业目的模仿租用软件的产品和服务；禁止复制和模仿租用软件的设计理念、界面、功能和图表。甲方只能处于公司商业范围内使用服务，禁止发送兜售信息和违反法律的信息，禁止发送和储存带有病毒的、蠕虫的、木马的和其他有害的计算机代码、文件、脚本和程序。</span></p>

            <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:42.55pt;
                   margin-bottom:.0001pt;text-indent:-21.25pt;line-height:20.0pt'><span
                        lang=EN-US style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>3.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>甲方应对其账号下所发生的任何行为负责并遵守地方、国家所有与适用本服务有关的适用法律、法规；有责任通知乙方任何未经许可使用密码和帐号和破坏数据安全的行为。</span></p>

            <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:42.55pt;
                   margin-bottom:.0001pt;text-indent:-21.25pt;line-height:20.0pt'><span
                        lang=EN-US style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>4.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>甲方在使用本系统时，应当注意符合国家法律规定和社会公共利益，遵守《计算机信息网络国际联网安全保护管理办法》、《中华人民共和国计算机信息网络国际联网管理暂行规定》、《互联网信息服务管理办法》等。</span></p>

            <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:42.55pt;
                   margin-bottom:.0001pt;text-indent:-21.25pt;line-height:20.0pt'><span
                        lang=EN-US style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>5.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>甲方应按照合同约定，及时向乙方支付软件租用及服务费用。如甲方在<span
                            lang=EN-US>3</span>个工作日内未履行付款义务，乙方有权利终止甲方软件租用服务，由此造成的后果由甲方自行承担。</span></p>

            <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:42.55pt;
                   margin-bottom:.0001pt;text-indent:-21.25pt;line-height:20.0pt'><span
                        lang=EN-US style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>6.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>甲方订购的套餐超出该套餐单量限制，造成系统崩溃等任何问题，甲方应承担一切损失乙方不承担甲方的任何损失。</span></p>

            <p class=MsoNormal style='margin-top:7.8pt;margin-right:0cm;margin-bottom:7.8pt;
                   margin-left:0cm;line-height:20.0pt'><b><span style='font-family:"微软雅黑",sans-serif'>第五条<span
                                lang=EN-US>&nbsp;&nbsp; </span>乙方权利和义务</span></b></p>

            <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:21.0pt;
                   margin-bottom:.0001pt;text-indent:.3pt;line-height:20.0pt'><span lang=EN-US
                                                                                    style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>1.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>乙方应按照合同约定，向甲方提供合同约定的软件租用服务。
                    </span></p>

            <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:21.0pt;
                   margin-bottom:.0001pt;text-indent:.3pt;line-height:20.0pt'><span lang=EN-US
                                                                                    style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>2.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>在软件租用过程中，乙方有义务保障系统稳定运行。</span></p>

            <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:21.0pt;
                   margin-bottom:.0001pt;text-indent:.3pt;line-height:20.0pt'><span lang=EN-US
                                                                                    style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>3.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>经甲方同意，本合同的签署意味着甲方授权乙方在履行本合同时可以使用甲方的名称、商标、域名、企业标志等，使用范围仅限于乙方自身宣传使用，不得作为其他用途，且在使用中不能损害甲方的利益。</span></p>

            <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:21.0pt;
                   margin-bottom:.0001pt;text-indent:.3pt;line-height:20.0pt'><span lang=EN-US
                                                                                    style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>4.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>乙方有义务在甲方合同期即将到期的最后一个月内，提醒甲方尽快充值。</span></p>

            <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:21.0pt;
                   margin-bottom:.0001pt;text-indent:.3pt;line-height:20.0pt'><span lang=EN-US
                                                                                    style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>5.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>如果因为甲方原因造成乙方部署的聚石塔服务器集群整体宕机，乙方有权向甲方索取经济赔偿。</span></p>

            <p class=MsoNormal align=left style='text-align:left;line-height:20.0pt'><b><span
                            style='font-family:"微软雅黑",sans-serif'>第六条<span lang=EN-US>&nbsp; </span>用户数据的保存、销毁与下载
                        </span></b></p>

            <p class=MsoBodyText2 align=left style='margin-top:0cm;margin-right:0cm;
                   margin-bottom:0cm;margin-left:21.3pt;margin-bottom:.0001pt;text-align:left;
                   text-indent:0cm;line-height:20.0pt'><span lang=X-NONE style='font-family:"微软雅黑",sans-serif'>1.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        lang=X-NONE style='font-family:"微软雅黑",sans-serif'>为服务甲方的目的，乙方可能通过使用甲方数据，向甲方提供服务，包括但不限于向甲方发出产品和服务信息。</span></p>

            <p class=MsoBodyText2 align=left style='margin-top:0cm;margin-right:0cm;
                   margin-bottom:0cm;margin-left:21.3pt;margin-bottom:.0001pt;text-align:left;
                   text-indent:0cm;line-height:20.0pt'><span lang=X-NONE style='font-family:"微软雅黑",sans-serif'>2.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        lang=X-NONE style='font-family:"微软雅黑",sans-serif'>甲方的用户数据将在下述情况下部分或全部被披露：</span></p>

            <p class=MsoNormal style='margin-left:63.0pt;text-indent:-21.0pt;line-height:
                   20.0pt'><span lang=EN-US style='font-family:"微软雅黑",sans-serif'>2.1.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp; </span></span><span
                        style='font-family:"微软雅黑",sans-serif'>经甲方同意，向第三方披露；</span></p>

            <p class=MsoNormal style='margin-left:63.0pt;text-indent:-21.0pt;line-height:
                   20.0pt'><span lang=EN-US style='font-family:"微软雅黑",sans-serif'>2.2.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp; </span></span><span
                        style='font-family:"微软雅黑",sans-serif'>根据法律的有关规定，或者行政或司法机构的要求，向第三方或者行政、司法机构披露；</span></p>

            <p class=MsoNormal style='margin-left:63.0pt;text-indent:-21.0pt;line-height:
                   20.0pt'><span lang=EN-US style='font-family:"微软雅黑",sans-serif'>2.3.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp; </span></span><span
                        style='font-family:"微软雅黑",sans-serif'>如果甲方出现违反中国有关法律法规的情况，需要向第三方披露；为提供甲方所要求的软件或服务，而必须和第三方分享甲方数据；</span></p>

            <p class=MsoNormal style='margin-left:63.0pt;text-indent:-21.0pt;line-height:
                   20.0pt'><span lang=EN-US style='font-family:"微软雅黑",sans-serif'>2.4.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp; </span></span><span
                        style='font-family:"微软雅黑",sans-serif'>除乙方和甲方另行约定外，自甲方协议期满或协议提前终止之日起<span
                            lang=EN-US>3</span>日内，乙方应继续免费存储甲方数据，逾期乙方将不再保留甲方数据。甲方需自行承担其数据被销毁后引发的一切后果。</span></p>

            <p class=MsoBodyText2 align=left style='margin-top:0cm;margin-right:0cm;
                   margin-bottom:0cm;margin-left:21.3pt;margin-bottom:.0001pt;text-align:left;
                   text-indent:0cm;line-height:20.0pt'><span lang=X-NONE style='font-family:"微软雅黑",sans-serif'>3.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        lang=X-NONE style='font-family:"微软雅黑",sans-serif'>除乙方和甲方另行约定外，在以下期限内乙方承诺向甲方提供免费数据下载服务，甲方可自行至乙方指定的服务器下载其数据：</span></p>

            <p class=MsoBodyText2 align=left style='margin-top:0cm;margin-right:0cm;
                   margin-bottom:0cm;margin-left:63.0pt;margin-bottom:.0001pt;text-align:left;
                   text-indent:-21.0pt;line-height:20.0pt'><span lang=X-NONE style='font-family:
                        "微软雅黑",sans-serif'>3.1.<span style='font:7.0pt "Times New Roman"'>&nbsp; </span></span><span
                        lang=X-NONE style='font-family:"微软雅黑",sans-serif'>本协议期限内；</span></p>

            <p class=MsoBodyText2 align=left style='margin-top:0cm;margin-right:0cm;
                   margin-bottom:0cm;margin-left:63.0pt;margin-bottom:.0001pt;text-align:left;
                   text-indent:-21.0pt;line-height:20.0pt'><span lang=X-NONE style='font-family:
                        "微软雅黑",sans-serif'>3.2.<span style='font:7.0pt "Times New Roman"'>&nbsp; </span></span><span
                        lang=X-NONE style='font-family:"微软雅黑",sans-serif'>本协议期满或协议提前终止之日起3日内。</span></p>

            <p class=MsoNormal style='margin-top:7.8pt;margin-right:0cm;margin-bottom:7.8pt;
                   margin-left:0cm;line-height:20.0pt'><b><span style='font-family:"微软雅黑",sans-serif'>第七条<span
                                lang=EN-US>&nbsp; </span>与第三方网站的对接</span></b></p>

            <p class=MsoNormal style='margin-left:28.5pt;text-indent:26.25pt;line-height:
                   20.0pt'><span style='font-family:"微软雅黑",sans-serif'>乙方提供与第三方网站的对接仅仅为了给甲方带来方便。如果甲方使用这些接口，将离开乙方范围。乙方对任何这些站点及其内容或它们的保密政策不进行控制，也不负任何责任。因此，乙方对这些网站上的任何信息、软件以及其它产品或材料，或者通过使用它们可能获得的任何结果不予认可，也不作任何表述。如果甲方决定使用第三方接口，其风险完全由甲方自己承担。若第三方网站需要另行交付订购费用，参照网站公告。
                    </span></p>

            <p class=MsoNormal style='margin-top:7.8pt;margin-right:0cm;margin-bottom:7.8pt;
                   margin-left:0cm;line-height:20.0pt'><b><span style='font-family:"微软雅黑",sans-serif'>第八条<span
                                lang=EN-US>&nbsp; </span>协议的变更与解除</span></b></p>

            <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:42.55pt;
                   margin-bottom:.0001pt;text-indent:-21.25pt;line-height:20.0pt'><span
                        lang=EN-US style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>1.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>合同履行过程中，任何一方欲对合同期限、项目内容、费用等合同内容或条款进行变更或补充的，应与对方协商一致并签定补充协议进行确定。否则，视为未作变更或补充，双方仍应按照原合同的约定履行。</span></p>

            <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:42.55pt;
                   margin-bottom:.0001pt;text-indent:-21.25pt;line-height:20.0pt'><span
                        lang=EN-US style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>2.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>合同履行过程中，如甲方欲提前解除合同的，应提前<span
                            lang=EN-US>30</span>日通知乙方。甲方提前解除合同的，已支付的套餐费用不予退还。</span></p>

            <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:42.55pt;
                   margin-bottom:.0001pt;text-indent:-21.25pt;line-height:20.0pt'><span
                        lang=EN-US style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>3.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>如乙方因自身原因需提前解除合同的，应提前<span
                            lang=EN-US>30</span>日通知甲方，并返还乙方未履行而甲方已支付的从解除合同之日起的软件租用套餐费用。</span></p>

            <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:42.55pt;
                   margin-bottom:.0001pt;text-indent:-21.25pt;line-height:20.0pt'><span
                        lang=EN-US style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>4.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>任何一方在履行中发现或者有证据表明对方已经、正在或将要违约，可以中止履行本合同，但应及时通知对方。若对方继续不履行、履行不当或者违反本合同，该方可以解除本合同并要求对方赔偿直接损失。</span></p>

            <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:42.55pt;
                   margin-bottom:.0001pt;text-indent:-21.25pt;line-height:20.0pt'><span
                        lang=EN-US style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>5.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>甲方应在合同期到期一个月内自动进行合同续费，在服务到期日如甲方未及时续费乙方将停止服务，乙方不承担甲方任何损失。</span></p>

            <p class=MsoNormal style='margin-top:7.8pt;margin-right:0cm;margin-bottom:7.8pt;
                   margin-left:0cm;line-height:20.0pt'><b><span style='font-family:"微软雅黑",sans-serif'>第九条<span
                                lang=EN-US>&nbsp; </span>不可抗力及责任承担</span></b></p>

            <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:42.55pt;
                   margin-bottom:.0001pt;text-indent:-21.25pt;line-height:20.0pt'><span
                        lang=EN-US style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>1.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>如果出现不可抗力，双方在本协议中的义务在不可抗力影响范围及其持续期间内将中止履行。任何一方均不会因此而承担责任。</span></p>

            <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:42.55pt;
                   margin-bottom:.0001pt;text-indent:-21.25pt;line-height:20.0pt'><span
                        lang=EN-US style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>2.<span
                            style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
                        style='font-size:10.5pt;font-family:"微软雅黑",sans-serif'>由此而产生的套餐费用，按实际使用情况结算，双方协商，余款退回。</span></p>

        </div>
    </div>
</div>

</div>
<script>
    var kh_id =<?php echo $response['kh_id']?>;
    //初始化数据
    component();

    function component() {
        var params = {
            "kh_id": kh_id,
        };
        $.post("?app_act=value/value_add/component&app_fmt=json", params, function (data) {
            var html = "<tr><th class='table_title'>序号</th><th class='table_title'>操作</th><th class='table_title'>服务名称</th><th class='table_title'>订购周期（单位：月）</th><th class='table_title'>添加时间</th><th class='table_title'>价格</th><th class='table_title'>优惠</th><th class='table_title'>实付</th></tr>";
            var ret_data=data.data;
            if (data.status == 1) {
                html += data_append(ret_data);
                $("#shopping_cart_data").html(html);
                $("#money_all").html(ret_data.money_all);
                $("#discount_all").html(ret_data.discount_all);
                $("#pay_money_all").html(ret_data.pay_money_all);
            }else{
                $("#shopping_cart_data").html(html);
                $("#money_all").html(0.00);
                $("#discount_all").html(0.00);
                $("#pay_money_all").html(0.00);
            }
        }, "json");
    }


    function data_append(data) {
        var list_data = data.cart_data;
        var html = '';
        $.each(list_data, function (i, val) {
            html += "<tr>";
            html += "<td>" + (i + 1) + "</td><td><a onclick='do_delete("+val.shopping_id+")'>删除</a></td><td>" + val.value_name + "</td><td>" + val.value_cycle + "</td><td>" + val.create_time + "</td><td>" + val.money + "</td><td>" + val.discount + "</td><td>" + val.pay_money + "</td>"
            html += "</tr>";
        });
        return html;
    }

    //立即支付
    $("#immediate_pay").click(function () {
        $("#immediate_pay").attr("disabled", true);
        //服务到期提醒
        var sever_remind = ($("#sever_remind").attr("checked")) ? 1 : 0;
        var params = {
            "kh_id": kh_id,
            "server_remind":sever_remind
        };
        $.post("?app_act=value/server_order/immediate_pay&app_fmt=json", params, function (data) {
            if (data.status == 1) {
                ali_pay(data);
            } else {
                $("#immediate_pay").attr("disabled", false);
                BUI.Message.Alert(data.message, 'error');
            }
        }, "json");
    });

    //继续订购
    $("#continue_order").click(function () {
        var url = '?app_act=value/value_add/server_list';
        openPage(window.btoa(url), url, '服务订购');
    });


    //获取返回的URL
    function ali_pay(data) {
        window.open(data.data);
        BUI.Message.Show({
            title: '提示',
            msg: '是否支付成功?',
            icon: 'question',
            buttons: [
                {
                    text: '支付成功',
                    elCls: 'button button-primary',
                    handler: function () {
                        check_pay_status(data.message, this);
                    }
                },
                {
                    text: '支付失败',
                    elCls: 'button',
                    handler: function () {
                        open_new_page();
                        this.close();
                        location.reload();
                    }
                }
            ]
        });
    }

    //验证充值是否成功
    function check_pay_status(pay_out_trade_no, _this) {
        $.ajax({
            type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('value/server_order/check_pay_status'); ?>', data: {pay_out_trade_no: pay_out_trade_no},
            success: function (ret) {
                _this.close();
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('订购成功！',function () {
                        location.reload();
                        var url = "?app_act=value/server_order/do_list&tabs_type=tabs_remark";
                        openPage(window.btoa(url), url, '我的订单');
                    } ,type);
                } else {
                    BUI.Message.Alert('已生成订单，支付宝支付失败！',function () {
                        location.reload();
                        var url = '?app_act=value/server_order/do_list';
                        openPage(window.btoa(url), url, '我的订单');
                    } ,type);
                }
            }
        });
    }

    //删除购物车
    function do_delete(shopping_id) {
        BUI.Message.Confirm('确定要删除吗？',function () {
            $.ajax({
                type: 'POST', dataType: 'json',
                url: '<?php echo get_app_url('value/value_add/do_delete'); ?>', data: {shopping_id: shopping_id},
                success: function (ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        component();
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                }
            });
        });
    }

    //用户协议
    BUI.use('bui/overlay', function (Overlay) {
        $('#user_note').on('click', function () {
            var dialog = new Overlay.Dialog({
                title: '用户协议',
                width: 750,
                height: 480,
                closeAction: 'destroy',
                contentId: 'content',
                success: function () {
                    this.close();
                }
            });
            dialog.show();
        });
    });

    $("#user_agree").change(function () {
        if (!$("#user_agree").attr("checked")) {
            $("#immediate_pay").attr("disabled", true);
        } else if ($("#user_agree").attr("checked")) {
            $("#immediate_pay").attr("disabled", false);
        }
    })

    function open_new_page() {
        var url = '?app_act=value/server_order/do_list';
        openPage(window.btoa(url), url, '我的订单');
    }
</script>

