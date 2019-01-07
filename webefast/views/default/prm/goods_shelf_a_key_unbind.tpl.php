<style>
    div{
        text-align:center;
    }   
</style>
<div>
    仓库：<select name="store_code" id="store_code">
                <option value=''>请选择</option>
                <?php $list = oms_tb_all('base_store', array('status'=>1)); foreach($list as $k=>$v){ ?>
                    <option value="<?php echo $v['store_code']?>"><?php echo $v['store_name']?></option>
                <?php } ?>
        </select>
</div>
<br/>
<div>
    <input type="checkbox" name="unbind_type" id='unbind_type'/>&nbsp;&nbsp;&nbsp;<span style='font-weight:bold'>仅解除实物库存为0的商品库位关联</span><br/>
    <span style='color:red'>（不勾选，则解除仓库所有商品库位关联）</span>
</div>
<br/>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="btn_pay_ok">确定</button>
</div>
<script type="text/javascript">
    $('#btn_pay_ok').click(function () {
        if($('#store_code').val() == ''){
            BUI.Message.Alert('请选择仓库');
            return;
        }
             var type=$('#unbind_type').is(':checked');
             var store = $('#store_code').val();
             var params = {
                "type": type,
                "store": store
             };
            $.post('?app_act=prm/goods_shelf/a_key_unbind_new', params, function (data) {
                var type = data.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('解绑成功');
                } else {
                    BUI.Message.Alert(data.message,'error');
                }
            },"json");
        });
</script>