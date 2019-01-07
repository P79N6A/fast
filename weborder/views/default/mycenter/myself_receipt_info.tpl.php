<style>
.top{ position:relative;}
.top .ljdg{ position:absolute; top:10px; right:12px; display:inline-block;padding:5px 10px; background:#f25216; color:#FFF;}
.top .ljdg1{position:absolute; top:10px; right:5%; display:inline-block;padding:5px 10px; background:#f25216; color:#FFF;}
.top .ljdg:hover{ background:#f86a37;}
</style>
<div class="order_wrap">
    <?php include get_tpl_path('top') ?>
    <div class="person_wrap">
        <div class="person">
            <div class="sidebar">
                <p class="person_pic"><img src="assets/img/person_pic.png"></p>
                <p class="person_name"><?php echo CTX()->get_session("kh_name") ?></p>
                <ul class="person_options" id="person_options">
                    <li class="li_01 "><a href='?app_act=mycenter/myself/self_info'>账号信息</a></li>
                    <li class="li_02"><a href='?app_act=mycenter/myself/order_info'>我的订单</a></li>
                    <li class="li_03 curr">发票信息</li>
                </ul>
            </div>
            <div class="content" style="display: block;">
                <div class="tabs_cont dgjl" style="display:block;">
                    <ul class="top">
                        <a class="ljdg" href="?app_act=mycenter/myself/apply_receipt">申请开票</a>
                    </ul>
                    <div class="details">
                        <h3>发票申请记录</h3>
                        <table class="cpdg">
                            <tr>
                                <th>序号</th>
                                <th>发票抬头</th>
                                <th>发票金额</th>
                                <th>申请时间</th>
                                <th>状态</th>
                                <th>开票时间</th>
                                <th>操作</th>
                            </tr>
                            <?php if (!empty($response)) { ?>
                                <?php foreach ($response as $i => $receiptinfo) { ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td><?php echo $receiptinfo['kh_name']; ?></td>
                                        <td><?php echo $receiptinfo['receipt_money']; ?></td>
                                        <td><?php echo $receiptinfo['applied_time']; ?></td>
                                        <td><?php echo $receiptinfo['status_name']; ?></td>
                                        <td><?php echo $receiptinfo['check_time']; ?></td>
                                        <td class="operate_td">
                                            <a href="?app_act=mycenter/myself/apply_receipt&_id=<?php echo $receiptinfo['receipt_id']?>&scene=view">详情</a><br>
                                            <?php if ($receiptinfo['status'] == '1') { ?>
                                                <a href="?app_act=mycenter/myself/apply_receipt&_id=<?php echo $receiptinfo['receipt_id']?>&scene=edit">编辑</a><br>
                                                <a href="javascript:delete_receipt('<?php echo $receiptinfo['receipt_id'];?>')">删除</a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr><td colspan="10">暂无信息</td></tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="order_bottom">
        <p><span>百胜官网：www.baison.com.cn</span><span>400-680-9510</span><span>地址：上海市浦东新区峨山路91弄100号陆家嘴软件园2号楼5楼（200127）</span></p>
    </div>
</div>
<script  type="text/javascript">
    function delete_receipt(receipt_id) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: "<?php echo (get_app_url('mycenter/myself/delete_receipt')); ?>",
            data: {receipt_id: receipt_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    alert('删除成功');
                    window.location.reload();
                } else {
                    alert('删除失败');
                }
            }
        });
    }
</script>