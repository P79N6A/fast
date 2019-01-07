<div class="doc-content">
    <form class="form-horizontal">
  	<div class="row">
            <div class="control-group span12">
		<label class="control-label" style="width:100px">条码方案：</label>
		<div class="controls">
                    <select class="text_sketch" id="rule_code">
                	<option value="" selected="">请选择</option>
                        <?php foreach ($response['rule_list'] as $record_list):?>
                    	<option value="<?php echo $record_list['rule_code']?>"><?php echo $record_list['rule_name']?></option>
                        <?php endforeach;?>
                    </select>				   
                </div>
            </div>
               <!--div class="control-group span18">
 	<label class="control-label" style="width:100px">说明</label>
				    <div class="controls" id="desc">
                     

                                    </div>
</div-->
        <div class="control-group span12">
			<label class="control-label" style="width:100px"></label>
			<div class="controls">
		         <input type="checkbox"  name="is_cover" id="is_cover" value="1" >是否覆盖已经有条码	<br/>
		         <font color="red">（若勾选，则将条码方案生成的最新商品条形码覆盖现有商品条形码）</font>
			</div>
			
		</div>
          <div class="control-group span12" style=" text-align:center"><button type="button" class="button button-primary" id="create">生成</button></div>
	</div>   
    </form>
</div> 

<div class="doc-content span12" id="loading" style="text-align: center; ">

</div>

<script>
$(function (){
    var num = 0; // 累计生成条数
    var pageno = 1;
    $('#create').click(do_create);
    function do_create(){
        var url = '?app_act=prm/goods_barcode_rule/do_create_barcode&app_fmt=json';
        var data = {
            rule_code : $('#rule_code').val(),
            is_cover : $('#is_cover').attr('checked') ? 1 : 0,
            page : pageno
        };
        /** 判断是否勾选生成方案 */
        if('' === data.rule_code){
            $('#loading').html("请选择生成方案");
            return ;
        }
        /** 禁用页面元素 */
        $('#create').attr("disabled",true);
        $('#is_cover').attr("disabled",true);
        $('#rule_code').attr("disabled",true);
        if (1 === pageno) {
            $('#loading').html('开始生成...');
        }
        /** 递归分批请求生成条形码 */
        $.post(url, data, function(result){
            num = parseInt(result.data) + num; 
            var message = '成功生成条码：'+num+'条';
            $('#loading').html(message);
            if( 1 === result.status ){
                ++pageno;
                do_create();
            } else {
                $('#loading').append('生成完成！<br />');
                pageno = 1;
                num = 0;
                /** 启用页面元素 */
                $('#create').attr("disabled",false);
                $('#is_cover').attr("disabled",false);
                $('#rule_code').attr("disabled",false);
            }
        }, "json");    
    } 
});
</script>