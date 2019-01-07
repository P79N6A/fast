<style>
    p.MsoNormal, li.MsoNormal, div.MsoNormal
    {margin:0cm;
     margin-bottom:.0001pt;
     text-align:justify;
     text-justify:inter-ideograph;
     font-size:10.5pt;
     font-family:"Times New Roman",serif;}
    p.MsoCommentText, li.MsoCommentText, div.MsoCommentText
    {
        margin:0cm;
        margin-bottom:.0001pt;
        font-size:10.5pt;
        font-family:"Times New Roman",serif;}
    p.MsoHeader, li.MsoHeader, div.MsoHeader
    {
        margin:0cm;
        margin-bottom:.0001pt;
        text-align:center;
        layout-grid-mode:char;
        border:none;
        padding:0cm;
        font-size:9.0pt;
        font-family:"Times New Roman",serif;}
    p.MsoFooter, li.MsoFooter, div.MsoFooter
    {
        margin:0cm;
        margin-bottom:.0001pt;
        layout-grid-mode:char;
        font-size:9.0pt;
        font-family:"Times New Roman",serif;}
    p.MsoTitle, li.MsoTitle, div.MsoTitle
    {
        margin-top:12.0pt;
        margin-right:0cm;
        margin-bottom:3.0pt;
        margin-left:0cm;
        text-align:center;
        font-size:16.0pt;
        font-family:"Cambria",serif;
        font-weight:bold;}
    p.MsoBodyText, li.MsoBodyText, div.MsoBodyText
    {
        margin-top:0cm;
        margin-right:0cm;
        margin-bottom:6.0pt;
        margin-left:0cm;
        text-align:justify;
        text-justify:inter-ideograph;
        line-height:15.6pt;
        font-size:10.5pt;
        font-family:"Times New Roman",serif;}
    p.MsoBodyTextIndent, li.MsoBodyTextIndent, div.MsoBodyTextIndent
    {
        margin-top:0cm;
        margin-right:0cm;
        margin-bottom:6.0pt;
        margin-left:21.0pt;
        text-align:justify;
        text-justify:inter-ideograph;
        line-height:15.6pt;
        font-size:10.5pt;
        font-family:"Times New Roman",serif;}
    p.MsoBodyText2, li.MsoBodyText2, div.MsoBodyText2
    {
        margin-top:0cm;
        margin-right:0cm;
        margin-bottom:6.0pt;
        margin-left:0cm;
        text-align:justify;
        text-justify:inter-ideograph;
        line-height:200%;
        font-size:10.5pt;
        font-family:"Times New Roman",serif;}
    p.MsoBodyTextIndent2, li.MsoBodyTextIndent2, div.MsoBodyTextIndent2
    {
        margin-top:0cm;
        margin-right:0cm;
        margin-bottom:6.0pt;
        margin-left:21.0pt;
        text-align:justify;
        text-justify:inter-ideograph;
        line-height:200%;
        font-size:10.5pt;
        font-family:"Calibri",sans-serif;}
    p.MsoDocumentMap, li.MsoDocumentMap, div.MsoDocumentMap
    {margin:0cm;
     margin-bottom:.0001pt;
     text-align:justify;
     text-justify:inter-ideograph;
     background:navy;
     font-size:10.5pt;
     font-family:"Times New Roman",serif;}
    p.MsoCommentSubject, li.MsoCommentSubject, div.MsoCommentSubject
    {
        margin:0cm;
        margin-bottom:.0001pt;
        font-size:10.5pt;
        font-family:"Times New Roman",serif;
        font-weight:bold;}
    p.MsoAcetate, li.MsoAcetate, div.MsoAcetate
    {
        margin:0cm;
        margin-bottom:.0001pt;
        text-align:justify;
        text-justify:inter-ideograph;
        font-size:9.0pt;
        font-family:"Times New Roman",serif;}
    p.MsoListParagraph, li.MsoListParagraph, div.MsoListParagraph
    {margin:0cm;
     margin-bottom:.0001pt;
     text-align:justify;
     text-justify:inter-ideograph;
     text-indent:21.0pt;
     font-size:10.5pt;
     font-family:"Times New Roman",serif;}
    p.chart, li.chart, div.chart
    {
        margin-top:6.0pt;
        margin-right:0cm;
        margin-bottom:6.0pt;
        margin-left:24.1pt;
        text-align:justify;
        text-justify:inter-ideograph;
        text-indent:-24.1pt;
        font-size:14.0pt;
        font-family:宋体;}
    span.apple-converted-space
    {mso-style-name:apple-converted-space;}
    span.style21
    {mso-style-name:style21;
     color:#408080;}

    div.WordSection1
    {page:WordSection1;}
    /* List Definitions */

</style>
<div class="order_wrap">
    <?php include get_tpl_path('top') ?>
    <div class="choose_wrap">
        <!--div class="onlysorry">
            <img src="assets/img/onlysorry.png" width="30" height="30">当前只有年租版，请谅解。<i class="clo">&times;</i>
        </div-->   
        <div class="choose">
            <p class="options" id="pro_cp_id">
                <label>产品名称</label>
                <a class="curr"  href="javascript:void(0)"  product="<?php echo $response['data']['chanpin']['cp_id'] ?>"><?php echo $response['data']['chanpin']['cp_name'] ?></a>
            </p>
            <p class="options" id="pro_product_version">
                <label>产品版本</label>
                <a class="curr" href="javascript:void(0)" pversion="1">标准版</a>
                <a href="javascript:void(0)" pversion="2">企业版</a>
                <!--            <a href="javascript:void(0)" pversion="3">旗舰版</a>-->
            </p>
            <p class="options" id="pro_st_id">
                <label>购买类型</label>
                <?php foreach ($response['data']['market'] as $market) { ?>  
                    <a class="<?php if ($market['st_id'] == 2) echo 'curr' ?>" href="javascript:void(0)" stid="<?php echo $market['st_id'] ?>"><?php echo $market['st_name'] ?></a>
                <?php } ?>
            </p>
            <!--p class="options" id="pro_hire_limit">
                <label>租用期限</label>
                <a class="curr" href="javascript:void(0)" prolimit="1">1个月</a>
                <a href="javascript:void(0)" prolimit="3">3个月</a>
                <a href="javascript:void(0)" prolimit="6">6个月</a>
                <a href="javascript:void(0)" prolimit="12">12个月</a>
            </p-->
            <p class="options" id="pro_hire_limit">
                <label>默认期限</label>
                <a class="curr" href="javascript:void(0)" prolimit="<?php echo $response['data']['plan'][0]['price_default_limit'] ?>"><?php
                    if (isset($response['data']['plan'][0]['price_default_limit'])) {
                        echo $response['data']['plan'][0]['price_default_limit'];
                    } else {
                        echo '0';
                    }
                    ?>个月</a>
            </p>
            <p class="options" id="pro_dot_num">
                <label>默认点数</label>
                <?php if (!empty($response['data']['plan'])) { ?>
                    <a class="curr" href="javascript:void(0)" prodnum="<?php echo $response['data']['plan'][0]['price_dot'] ?>"><?php echo $response['data']['plan'][0]['price_dot'] ?></a>
                    <span class="msg">如果您想购买更多点数请联系客服</span>
                <?php } else { ?>
                    <span class='msg'>暂无报价点数</span>
<?php } ?>
            </p>
            <div class="buynow">
                <input type="hidden" value="<?php echo $response['data']['plan'][0]['price_base'] ?>" id="price_one"  />
                <p class="price">总价 ￥ 
                    <span id="pro_price">
                        面议
                    </span>
                </p>
                <p class="btns">
                    <button class="ljgm" id="pay_order" type="button">立即购买</button>
                    <!--button class="jrqd" type="button">加入清单</button-->
                </p>
            </div>
        </div>
    </div>
    <div class="order_bottom">
        <p><span>百胜官网：www.baison.com.cn</span><span>400-680-9510</span><span>地址：上海市浦东新区峨山路91弄100号陆家嘴软件园2号楼5楼（200127）</span></p>
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
        <div style="position:relative;left: 45%;top: 2%;">
            <input type="checkbox" name="checkbox" id="check"> <span>同意用户协议</span>
        </div>
    </div>

</div>

<?php include get_tpl_path('login'); ?>
<?php include get_tpl_path('register'); ?>
<?php echo load_js('login_reg.js', true); ?>  
<?php echo load_js('bui.js', true); ?> 
<?php echo load_css('bui.css', true); ?> 
<?php echo load_css('dpl.css', true); ?> 
<style>
    .button-primary {
        color: #FFF;
        background-color: #E95513;
        border-color: #E95513;
    }
</style>
<script>
    BUI.use('bui/overlay', function (Overlay) {
        $('#pay_order').on('click', function () {
            var dialog = new Overlay.Dialog({
                title: '用户协议',
                width: 750,
                height: 550,
                closeAction: 'destroy',
                contentId: 'content',
                success: function () {
                    this.close();
                }
            });
            dialog.show();
        });
        $('#pay_order').on('click', function () {
            $(".button-primary").first().attr("disabled", true);
            $(".bui-stdmod-footer").css("padding", "15px 40% 15px 0px");
        });
    });
    $(document).ready(function () {
        $("#check").attr("checked", false);
        $("#check").change(function () {
            if (!$("#check").attr("checked")) {
                $(".button-primary").first().attr("disabled", true);
            } else if ($("#check").attr("checked")) {
                $(".button-primary").first().attr("disabled", false);
                $(".button-primary").first().click(function () {
                    $.ajax({type: "POST", dataType: 'json',
                        url: "?app_act=product/soonbuy/check_user_info",
                        data: {username: $("#username").val(), },
                        success: function (ret) {
                            var type = ret.status == 1 ? 'success' : 'error';
                            if (type == 'success') {
                                var cpid = $("#pro_cp_id").find("a").filter(".curr").attr("product");
                                var cpname = $("#pro_cp_id").find("a").filter(".curr").text();
                                var p_version = $("#pro_product_version").find("a").filter(".curr").attr("pversion");
                                var pro_hire_limit = $("#pro_hire_limit").find("a").filter(".curr").attr("prolimit");
                                var pro_st_id = $("#pro_st_id").find("a").filter(".curr").attr("stid");
                                var pro_st_id_name = $("#pro_st_id").find("a").filter(".curr").text();
                                //var pro_hire_limit = $("#prolimit").val();
                                var pro_dot_num = $("#pro_dot_num").find("a").filter(".curr").attr("prodnum");
                                var pro_price = $("#pro_price").text();
                                var pro_price_id = $("#pro_price_id").find("a").filter(".curr").attr("price");
                                $.ajax({type: "POST", dataType: 'json',
                                    url: "?app_act=product/soonbuy/show_order_info_go",
                                    data: {cpid: cpid, cpname: cpname,
                                        p_version: p_version, pro_hire_limit: pro_hire_limit,
                                        pro_dot_num: pro_dot_num, pro_price: pro_price,
                                        stid: pro_st_id, stname: pro_st_id_name, priceid: pro_price_id},
                                    success: function (ret) {
                                        var type = ret.status == 1 ? 'success' : 'error';
                                        if (type == 'success') {
                                            location.href = '?app_act=product/soonbuy/show_order_info';
                                        }
                                    }
                                });
                            } else {
                                $("#account_login").slideDown(600);
                            }
                        }
                    });
                });
            }
            ;
        });
    });

    $(".onlysorry .clo").click(function () {
        $(this).parent().hide();
    });

    var num = $(".num").val();
    $(".add").click(function () {
        if (parseInt(num) < 24) {
            num = parseInt(num) + 1;
            $(".num").val(num);
        }
    })
    $(".reduce").click(function () {
        if (parseInt(num) > 1) {
            num = parseInt(num) - 1;
            $(".num").val(num);
        }
    })

    options_each();
    function options_each() {
        $(".options a").each(function (i) {
            $(this).unbind('click');   //非常重要
            $(this).bind('click', function () {
                $(this).siblings().removeClass("curr");
                $(this).addClass("curr");

                var clickid = $(this).parent().attr("id");
                //选择产品版本，购买类型事件
                if (clickid == "pro_product_version" || clickid == "pro_st_id") {
                    var pro_product_version = $("#pro_product_version").find("a").filter(".curr").attr("pversion");
                    var pro_st_id = $("#pro_st_id").find("a").filter(".curr").attr("stid");
                    var pro_cp_id = $("#pro_cp_id").find("a").filter(".curr").attr("product");
                    if (pro_st_id == "1") {  //买断
                        $("#pro_hire_limit").hide("normal");
                    }
                    if (pro_st_id == "2") {  //租用
                        $("#pro_hire_limit").show("normal");
                    }
                    $.ajax({type: "POST", dataType: 'json',
                        url: "?app_act=product/soonorder/get_planprice_by",
                        data: {pro_product_version: pro_product_version, pro_st_id: pro_st_id, pro_cp_id: pro_cp_id},
                        success: function (ret) {
                            var type = ret.status == 1 ? 'success' : 'error';
                            if (type == 'success') {
                                $("#pro_price_id").html(" <label>报价类型</label>");
                                $.each(ret.data, function (i, item) {
                                    //绑定报价
                                    if (i == 0) {
                                        $("#pro_price_id").append(" <a class='curr' href='javascript:void(0)' price='" + item.price_id + "'>" + item.price_name + "</a>");
                                        //绑定点数
                                        $("#pro_dot_num").html(" <label>默认点数</label>");
                                        $("#pro_dot_num").append(" <a class='curr' href='javascript:void(0)' prodnum='" + item.price_dot + "'>" + item.price_dot + "</a>");
                                        $("#pro_dot_num").append(" <span class='msg'>如果您想购买更多点数请联系客服</span>");
                                        //绑定价格
                                        if (pro_st_id == "2") { //租用
                                            var default_limit = item.price_default_limit;
                                            if (default_limit == "" || default_limit == null) {
                                                default_limit = "0";
                                            }
                                            $("#pro_hire_limit").html(" <label>默认期限</label>");
                                            $("#pro_hire_limit").append(" <a class='curr' href='javascript:void(0)' prolimit='" + default_limit + "'>" + default_limit + "个月</a>");
                                            //var pro_hire_limit = $("#pro_hire_limit").find("a").filter(".curr").attr("prolimit");
                                            //$("#pro_price").html(item.price_base*pro_hire_limit);
                                            if (pro_product_version == 1) { //标准版
                                                $("#pro_price").html("面议");
                                                $("#price_one").val(item.price_base);
                                            } else {
                                                $("#pro_price").html("面议");
                                                $("#price_one").val('0');
                                            }
                                        } else {  //买断
                                            $("#pro_price").html("面议");
                                            $("#price_one").val('0');
                                        }
                                    } else {
                                        $("#pro_price_id").append(" <a class='' href='javascript:void(0)' price='" + item.price_id + "'>" + item.price_name + "</a>");
                                    }
                                    options_each();
                                });
                                $("#pay_order").removeAttr("disabled");
                            } else {
                                $("#pro_price_id").html(" <label>报价类型</label>");
                                $("#pro_price_id").append(" <span class='msg'>暂无报价</span>");
                                //绑定点数
                                $("#pro_dot_num").html(" <label>默认点数</label>");
                                $("#pro_dot_num").append(" <span class='msg'>暂无报价点数</span>");
                                $("#pro_price").html("面议");
                                $("#price_one").val('0');
                            }
                        }
                    });
                }
                //选择报价模版的事件
                if (clickid == "pro_price_id") {
                    var pro_st_id = $("#pro_st_id").find("a").filter(".curr").attr("stid");
                    var pro_price_id = $("#pro_price_id").find("a").filter(".curr").attr("price");
                    $.ajax({type: "POST", dataType: 'json',
                        url: "?app_act=product/soonorder/get_planprice_one",
                        data: {pro_price_id: pro_price_id, },
                        success: function (ret) {
                            var type = ret.status == 1 ? 'success' : 'error';
                            if (type == 'success') {
                                //绑定点数
                                $("#pro_dot_num").html(" <label>默认点数</label>");
                                $("#pro_dot_num").append(" <a class='curr' href='javascript:void(0)' prodnum='" + ret.data['price_dot'] + "'>" + ret.data['price_dot'] + "</a>");
                                $("#pro_dot_num").append(" <span class='msg'>如果您想购买更多点数请联系客服</span>");
                                //绑定价格
                                if (pro_st_id == "2") { //租用
                                    var default_limit = ret.data['price_default_limit'];
                                    if (default_limit == "" || default_limit == null) {
                                        default_limit = "0";
                                    }
                                    $("#pro_hire_limit").html(" <label>默认期限</label>");
                                    $("#pro_hire_limit").append(" <a class='curr' href='javascript:void(0)' prolimit='" + default_limit + "'>" + default_limit + "个月</a>");
                                    //var pro_hire_limit = $("#pro_hire_limit").find("a").filter(".curr").attr("prolimit");
                                    //$("#pro_price").html(item.price_base*pro_hire_limit);
                                    $("#pro_price").html(ret.data['price_base']);
                                    $("#price_one").val(ret.data['price_base']);
                                }
                                if (pro_st_id == "1") {  //买断
                                    $("#pro_price").html(ret.data['price_base']);
                                    $("#price_one").val(ret.data['price_base']);
                                }
                            } else {

                            }
                        }
                    });
                }
                //选择租用期限的事件
                if (clickid == "pro_hire_limit") {
                    //var pro_hire_limit = $("#pro_hire_limit").find("a").filter(".curr").attr("prolimit");
                    //$("#pro_price").html(pro_hire_limit*$("#price_one").val());
                }
            });
        });
    }

</script>

