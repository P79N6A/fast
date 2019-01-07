<div class="doc-content">
    <form class="form-horizontal">
  <div class="row">
      <div class="control-group span18">
	<label class="control-label" style="width:100px">盘点日期_仓库</label>
				    <div class="controls">
                            <select class="text_sketch" id="store_date">
                              <option value="" selected="">请选择</option>
                              <?php foreach ($response['record_list'] as $record_list):?>
                              <option value="<?php echo $record_list['take_stock_time']?>,<?php echo $record_list['store_code']?>"><?php echo $record_list['take_stock_time']?>_<?php echo get_store_name_by_code($record_list['store_code'])?></option>
                              <?php endforeach;?>
                            </select>				   
                                    </div>
           </div>
   
            <div class=" span18">
 	<label class="control-label" style="width:100px">盘点类型</label>
				    <div class="controls">
<!--                                     <input type="radio"  name="type" value="1" checked="checked">全盘	 -->
<!--                                    <input type="radio"  name="type" value="2" >部分盘点（SKU级） -->
                         <select class="text_sketch" id="take_stock_type" name= 'type'>
                              <option value="" selected="">请选择</option>
                         <?php if (load_model('sys/PrivilegeModel')->check_priv('stm/take_stock_record/stock_part')) { ?>
                              <option value="2" >部分盘点（SKU级）</option>
                         <?php } ?>
                         <?php if (load_model('sys/PrivilegeModel')->check_priv('stm/take_stock_record/stock_all')) { ?>
                              <option value="1" >全盘</option>
                         <?php } ?>
                         </select>		
                    </div>
               
       
</div>
           <div class="controlgroup span18"  style="text-align:left">
               <div id="recode_info" style="display:none"><br/>
                   <div class="span12">当前盘点日期：<label id="take_stock_time"></label>  </div><br/>
                <div class="span12">当前盘点仓库：<label id="store"></label> </div><br/>
                <div class="span12">当前盘点商品总数量：<label id="take_stock_num"></label> </div>
                 <div class="span18" >
                     <div>可盘点单据: <span id="recode_code_list"></span></div>
                    
                 </div>
               </div>

       </div>
          <div class="control-group span18" style=" text-align:center"><button type="button" class="button button-primary" id="stock">开始盘点</button></div>
 </div>   
        </form>
   </div> 

<div class="doc-content span18" id="message" style="text-align: center">

</div>
<div class="doc-content span18" style="text-align:left;color:red;">
<B>提醒：</B><br/>
一键盘点之前需要提前创建并验收盘点单。<br />
[开始盘点]后，系统自动生成相应调整单，并刷新系统库存。<br />
<B>说明：</B><br/>
<B>全盘：</B>对仓库中的所有商品进行盘点，未录入的商品，实物库存默认为0。<br />
<B>SKU级盘点：</B>只针对盘点单中出现的SKU进行盘点，未录入的SKU，不做盘点，实物库存保持不变。<br />
</div>

<script>
var load_data ={};
$(function (){
    var store_date = '';
    var recode_code_list = '';
    $('#stock').click(function(){
        var url = '?app_act=stm/take_stock_record/take_stock_inv&app_fmt=json';
        var data = {};
        data.store_date= $('#store_date').val();
        if(data.record_info==''){
            top.BUI.Message.Alert("请选择盘点单据", 'error');
            return ;
        }
        
        data.type=$("#take_stock_type").val(); 
        if(data.type==''){
            top.BUI.Message.Alert("请选择盘点类型", 'error');
            return ;
        }
          recode_code_list = '';
        if($('#recode_code_list input[name="record_code"]:checked').length>0){
            $.each($('#recode_code_list input[name="record_code"]:checked'),function(i,item){
                recode_code_list += $(item).val()+',';
            });
        }
        if(recode_code_list==''){
            top.BUI.Message.Alert("请选择判断单据", 'error');
            return ;
        }else{
          
             recode_code_list = recode_code_list.substr(0,recode_code_list.length-1);    
        }

        data.recode_code_list = recode_code_list;
         set_status(true);
         $('#message').html('盘点开始...');
        $.post(url, data, function(result){
            if(result.status<0){
                   set_status(false);
                  $('#message').html(result.message);
            }else{
                store_date = data.store_date;
                get_take_stock_status();
            }
        }, "json");
    });
    $('#store_date').change(function(){
        var url = '?app_act=stm/take_stock_record/get_take_stock_info&app_fmt=json';
        var data = {};
        data.store_date= $('#store_date').val();  
        $.post(url, data, function(result){
        if(result.status<1){
             top.BUI.Message.Alert(result.message, 'error');
         }else{
                $('#take_stock_time').html(result.data.take_stock_time);
                $('#store').html(result.data.store_name+'('+result.data.store_code+')');
                $('#take_stock_num').html(result.data.goods_num);
                $('#recode_info').show();
                var record_list_html = '';
                load_data = result.data;
                $.each(result.data.record_list,function(i,record_code){
                    record_list_html +=record_code+'<input type="checkbox" name="record_code" checked="checked" value="'+record_code+'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                });
                $('#recode_code_list').html(record_list_html);
                 $('#recode_info').show();
                 $('#recode_code_list input').click(function(){
                     var num = parseInt($('#take_stock_num').text()) ;
                     var record_num = parseInt(load_data['num_list'][$(this).val()]);
                     if($(this).attr('checked')){
                         num+=record_num;
                     }else{
                         num-=record_num;
                     }
                      $('#take_stock_num').html(num);
                 });
                 
                 

         }
        }, "json");  
    });
    function set_status(status){
           $('#stock').attr("disabled",status);
           $('#store_date').attr("disabled",status);
           $('#take_stock_type').attr("disabled",status);
           $('#recode_code_list input[name="record_code"]').attr("disabled",status);
    }

    function get_take_stock_status(){
        if(recode_code_list!=''){
          var url = '?app_act=stm/take_stock_record/get_take_stock_inv&app_fmt=json';
            var data = {};
            data.recode_code_list= recode_code_list;         
            $.post(url, data, function(result){
            if(result.status<1){
                  top.BUI.Message.Alert(result.message, 'error');
            }else{
                 set_status_info(result.data,result.message);
                 if(result.data<5){
                    setTimeout(function(){get_take_stock_status();},3000);
                 }else{
                    set_status(false);
                 }
                 if(result.data==5){
                     $("#record_info option[value='"+store_date+"']").remove(); 
                 }
            }
        }, "json");     
        }
    }   
    function set_status_info(status,message){
        var msg = '';
        status = parseInt(status);
        switch(status){
          case 1:
          msg = "正在进行库存维护...";
           break;
          case 2:
          msg = "正在计算商品的系统账面数量...";
          break;
          case 3:
          msg = "正在生成调整单...";
          break;
          case 4:
          msg = "正在调整库存...";
          break;
          case 5:
          msg = "盘点完成";
          break;
          default:
              msg = message;
        }
        $('#message').html(msg);
    }
});
</script>