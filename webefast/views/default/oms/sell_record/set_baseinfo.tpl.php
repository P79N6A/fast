<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="10%" align="right">订单编号：</td>
        <td width="40%">
        <?php 
        if (!empty($response['record']['problem_desc'])){ 
            echo '<span style="color:red>'.$response['record']['sell_record_code'].'&nbsp;问题单:('.$response['record']['problem_desc'].')</span>';
        }else{
            echo $response['record']['sell_record_code'];            
        }
        ?>
        </td>
        <td width="10%" align="right">订单状态：</td>
        <td width="40%"><?php echo $response['record']['status'];?></td>
    </tr>
    <tr>
        <td width="10%" align="right">订单来源：</td>
        <td width="40%"><?php echo $response['record']['sale_channel_name'];?></td>
        <td width="10%" align="right">下单时间：</td>
        <td width="40%"><?php echo $response['record']['add_time'];?></td>
    </tr>
    <tr>
        <td width="10%" align="right">店铺：</td>
        <td width="40%"><?php echo $response['record']['shop_name'];?></td>
        <td width="10%" align="right">会员昵称：</td>
        <td width="40%"><?php echo $response['record']['buyer_name'];?></td>
    </tr>
    <tr>
        <td width="10%" align="right">支付方式：</td>
        <td width="40%"><?php echo $response['record']['pay_name'];?></td>
        <td width="10%" align="right">支付类型：</td>
        <td width="40%"><?php echo $response['record']['pay_type_name'];?></td>
    </tr>
    <tr>
        <td width="10%" align="right">支付时间：</td>
        <td width="40%">
            <select name="pay_code" id="pay_code">
                <?php $list = oms_tb_all('base_pay_type', array('status'=>1)); foreach($list as $k=>$v){ ?>
                    <option value="<?php echo $v['pay_type_code']?>"><?php echo $v['pay_type_name']?></option>
                <?php } ?>
            </select>
            <script>$("#pay_code").val("<?php echo $response['record']['pay_code']?>")</script>
        </td>
        <td width="10%" align="right">订单回写：</td>
        <td width="40%"><?php echo $response['record']['is_back_time'];?></td>
    </tr>
    <tr>
        <td width="10%" align="right">配送方式：</td>
        <td width="40%">
            <select name="express_code" id="express_code">
                <?php $list = oms_tb_all('base_express', array('status'=>1)); foreach($list as $k=>$v){ ?>
                    <option value="<?php echo $v['express_code']?>"><?php echo $v['express_name']?></option>
                <?php } ?>
            </select>
            <script>$("#express_code").val("<?php echo $response['record']['express_code']?>")</script>
        </td>
        <td width="10%" align="right">快递单号：</td>
        <td width="40%"><?php echo $response['record']['express_no'];?></td>
    </tr>
    <tr>
        <td width="10%" align="right">订单备注：</td>
        <td width="40%"><?php echo $response['record']['order_remark'];?></td>
        <td width="10%" align="right">客户留言：</td>
        <td width="40%"><?php echo $response['record']['buyer_remark'];?></td>
    </tr>
    <tr>
        <td width="10%" align="right">商家备注：</td>
        <td width="40%" colspan="3">
            <textarea name="seller_remark" id="seller_remark" style="width: 100%; height: 39px;"><?php echo $response['record']['seller_remark'];?></textarea>
        </td>
    </tr>
</table>