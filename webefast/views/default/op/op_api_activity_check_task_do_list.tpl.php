<style type="text/css">
    .well {
        min-height: 30px;
    }
    .xiaoyan {
        line-height: 50px;
    }
    .top_title{
        height:50px;
    }
    #shop_select{
        height:40px;
        line-height:40px;
    }
    form.form-horizontal{ 
        margin-top:30px;padding:20px; border:1px solid #ded6d9;
    }
    #top_button{
        margin-top:20px;
    }
</style>
<?php echo load_js('jquery.cookie.js') ?>
<?php echo load_js('comm_util.js') ?>

<div>
        <h2>生成效验任务</h2>
</div>
<form class="form-horizontal">
<div class='top_title'>
    <h3>活动店铺：</h3>
</div>
<div id='shop_select'>
    
</div>
<div class="top_title">
    <h3>异常效验项：</h3> 
</div>
    <div>
        <div class='xiaoyan'><input type='checkbox' checked='checked' disabled="disabled"/> 商家编码效验：系统将以店铺活动商品为主核对店铺线上商品，默认匹配SKU级商家编码</div>
    <div class='xiaoyan'><input type='checkbox' checked='checked' disabled="disabled"/> 库存对比效验：系统将对比线上和系统的库存</div>
    <div class='xiaoyan'><input type='checkbox' checked='checked' disabled="disabled"/> 价格对比效验：系统将对比活动商品售价与店铺双十一促销价一致性</div>
    </div>
    <div id="top_button">
        <input type='button' class='button button-primary' value='生成效验任务'/><!--span style='color:red'>当前有执行中的效验任务，请等待执行完成生成效验任务</span-->
    </div>
</form>

<script type="text/javascript">
    $("#shop_select").ready(function(){
        $.post("?app_act=op/op_api_activity_check/shop_select","",function(result){
            content = '';
            for(var key in result.name){
                   content += '<span style="margin-left:30px">';
        	   content += '  <input class="check_shop" type="checkbox" checked="checked" value="'+result.code[key]+'">';
        	   content += result.name[key];
                   content += '</span>';
            }
            $("#shop_select").append(content);
        },"json");
        
    });
    $('.button').click(function(){
        var params = {};
        var shop_code = new Array();
        $(".check_shop").each(function(i){  
                        if($(this).is(":checked"))  
                        {  
                            shop_code.push($(this).val());
                        }  
                    });
        params.shop_code = shop_code;
        $.post("?app_act=op/op_api_activity_check/start_efficacy",params,function(data){
          if(data.status== 1){
              BUI.Message.Alert("创建成功");
          }else{
              BUI.Message.Alert("创建失败");
          }
        },"json");
    });
</script>