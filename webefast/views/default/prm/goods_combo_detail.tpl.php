<style type="text/css">
 .table_panel{
	width:800px;
 }
 .table_panel td {
    border-top: 0px solid #dddddd;
    line-height: 20px;
    padding: 6px;
    text-align: left;
    vertical-align: top;
}
.table_panel1 td {
    border:1px solid #dddddd;
    line-height: 20px;
    padding: 6px;
    text-align: left;
    vertical-align: top;
}
.scroll {
       /* width: 200px;                                     /*宽度*/
        height: 50px;                                   /*高度*/
        padding-left: 10px;                             /*层内左边距*/
        padding-right: 10px;                            /*层内右边距*/
        padding-top: 10px;                              /*层内上边距*/
        padding-bottom: 10px;                           /*层内下边距*/
        /*overflow-x: scroll;                             /*横向滚动条(scroll:始终出现;auto:必要时出现;具体参考CSS文档)*/
        overflow-y: scroll;                             /*竖向滚动条*/

        scrollbar-face-color: #D4D4D4;                  /*滚动条滑块颜色*/
        scrollbar-hightlight-color: #ffffff;                /*滚动条3D界面的亮边颜色*/
        scrollbar-shadow-color: #919192;                    /*滚动条3D界面的暗边颜色*/
        scrollbar-3dlight-color: #ffffff;               /*滚动条亮边框颜色*/
        scrollbar-arrow-color: #919192;                 /*箭头颜色*/
        scrollbar-track-color: #ffffff;                 /*滚动条底色*/
        scrollbar-darkshadow-color: #ffffff;                /*滚动条暗边框颜色*/
    }

    #form4 li{padding:4px;}
	#form4 li label{ display:inline-block; min-width:90px;}
    #spec1_html{height:43px; overflow:auto;}
    #spec2_html{height:43px; overflow:auto;}
</style>
<?php render_control('PageHead', 'head1',
array('title'=>'商品套餐编辑'));
?>

<ul class="nav-tabs oms_tabs">
    <li class="active"><a href="#"  >基本信息</a></li>
    <?php if($response['action'] == 'do_edit'){ ?>
    <li ><a href="#" onClick="do_page('view');" >套餐明细</a></li>
   <?php }?>
</ul>

<div id="panel" class="">

<form action="?app_act=prm/goods_combo/<?php echo $response['action'];?>" id="form2" method="post">
<div id='p2'>
<table class='table_panel1' style='width:100%'>
  <input type="hidden" id="goods_combo_id" name="goods_combo_id"  value="<?php echo $response['data']['goods_combo_id']; ?>">
  <tr>
      <td style="width:108px;">套餐编码：<b style="color:red"> *</b></td>
      <td>
          <?php if($response['is_use']) {?>
                <input id="goods_code" class="bui-form-field" type="text"  value="<?php echo $response['data']['goods_code']; ?>" name="goods_code" data-rules="{required : true}" aria-disabled="false" aria-pressed="false" disabled="disabled" >
          <?php } else { ?>
                <input id="goods_code" class="bui-form-field" type="text"  value="<?php echo $response['data']['goods_code']; ?>" name="goods_code" data-rules="{required : true}" aria-disabled="false" aria-pressed="false"   >
          <?php } ?>
      </td>
  </tr>
  <tr><td style="width:108px;">套餐名称：<b style="color:red"> *</b></td><td><input id="goods_name" class="bui-form-field" type="text"  value="<?php echo $response['data']['goods_name']; ?>" name="goods_name" data-rules="{required : true}" aria-disabled="false" aria-pressed="false" ></td></tr>
 <tr><td style="width:108px;">套餐价格：</td><td><input id="price" class="bui-form-field" type="text"  value="<?php echo $response['data']['price']; ?>" name="price"  aria-disabled="false" aria-pressed="false" ></td></tr>
 <tr><td style="width:108px;">套餐描述：</td><td>
 <textarea id="goods_desc" class="bui-form-field" style="width:184px; height: 80px;" cols="40" rows="10" name="goods_desc" aria-disabled="false" aria-pressed="false"><?php echo $response['data']['goods_desc']; ?></textarea>
</td></tr>
  <tr><td style="width:80px;" >
  <?php echo $response['goods_spec1_rename'];?>
  <?php if($response['spec_power']['spec_power'] == 1){?>
 <br><a href="#" id = 'goods_spec1'><img src='assets/img/search.png'>点我新增</a>
   <?php }?>
  <input type="hidden" id="spec1_code"  value="<?php echo isset($response['data']['goods_spec1_str_code'])?$response['data']['goods_spec1_str_code']:''; ?>" name="spec1_code" />
  <input type="hidden" id="spec1_name"  value="<?php echo isset($response['data']['goods_spec1_str_name'])?$response['data']['goods_spec1_str_name']:''; ?>" name="spec1_name" />
      </td><td style="width:1000px;" >
       <div align="left">
        <div class="scroll" id="spec1_html">
        	<?php foreach($response['spec1'] as $k=>$v){ ?>
        	    <?php if($k%12 == 0 && $k>0){ ?>
    			<?php }?>
        	 <div style="display:inline-block; padding-bottom:5px"><span>
        	 <input name="spec1[]" type="checkbox" 
        	 checked="checked"
                 <?php if(in_array($v['spec1_code'],$response['goods_spec1_disabled'],true)){?>
                 disabled="disabled" 
                <?php }?> 
                 value="<?php echo $v['spec1_code']; ?>" id='spec1_<?php echo $v['spec1_code']; ?>' onchange="spec_checked('spec1',this)" /></span><span style="display:inline-block; width:108px; height:20px; overflow:hidden; vertical-align:text-bottom;" title="<?php echo $v['spec1_name']; ?>"><?php echo $v['spec1_name']; ?></span></div>

            <?php } ?>
            <?php if($response['spec_power']['spec_power'] == 0){ ?>
                 <input type="hidden" name="spec1" id="spec1" value="<?php echo $v['spec1_code']; ?>"/>
            <?php } ?>     
        </div>
    </div>  </td><!--<td style="width:300px;">  <span class="spec1_html" ></span></td>-->
  <!--  <td> 没找到规格1信息，需要<a href="javascript:PageHead_show_dialog_type('?app_act=prm/spec1/detail&app_scene=add&app_show_mode=pop', '添加规格1', {w:500,h:400},'get_spec1')" onclick="">添加规格1</a>   找到规格1，
 </td>	-->
  </tr>
  <tr><td><?php echo $response['goods_spec2_rename'];?> <?php if($response['spec_power']['spec_power'] == 1){?><br><a href="#" id = 'goods_spec2'><img src='assets/img/search.png'>点我新增</a> <?php }?>
        <input type="hidden" id="spec2_code"  value="<?php echo isset($response['data']['goods_spec2_str_code'])?$response['data']['goods_spec2_str_code']:''; ?>" name="spec2_code" />
        <input type="hidden" id="spec2_name"  value="<?php echo isset($response['data']['goods_spec2_str_name'])?$response['data']['goods_spec2_str_name']:''; ?>" name="spec2_name" />
  </td><td>
 <div align="left">
        <div class="scroll" id="spec2_html" >
        	<?php foreach($response['spec2'] as $k=>$v){ ?>
        	    <?php if($k%12 == 0 && $k>0){ ?>
    			<?php }?>
        	 <div style="display:inline-block; padding-bottom:5px"><span>
        	 <input name="spec2[]" type="checkbox"   
        	 checked 
                 <?php if(in_array($v['spec2_code'],$response['goods_spec2_disabled'],true)){?>
                 disabled="disabled" 
                <?php }?> 
                 value="<?php echo $v['spec2_code']; ?>" id='spec2_<?php echo $v['spec2_code']; ?>' onchange="spec_checked('spec2',this)" /></span><span style="display:inline-block; width:108px; height:20px; overflow:hidden; vertical-align:text-bottom;" title="<?php echo $v['spec2_name']; ?>"><?php echo $v['spec2_name']; ?></span></div>
<?php } ?>
                <?php if($response['spec_power']['spec_power'] == 0){ ?>
                 <input type="hidden" name="spec2" id="spec2" value="<?php echo $v['spec2_code']; ?>"/>
                <?php } ?>
        </div>
    </div>
  </td>

  <!--  <td> 没找到规格2信息，需要<a href="javascript:PageHead_show_dialog_type('?app_act=prm/spec2/detail&app_scene=add&app_show_mode=pop', '添加规格2', {w:500,h:400},'get_spec2')" onclick="">添加规格2</a>   找到规格2，
 </td>-->
  </tr>
  <tr><td>商品条码</td><td>
  <table class='table_panel1' style='width:100%'>
   <tr><td style="width:5%;"><?php echo $response['goods_spec1_rename'];?></td><td style="width:5%;"><?php echo $response['goods_spec2_rename'];?></td><td style="width:10%;">套餐条形码</td><td style="width:5%;">吊牌价(元)</td></tr>
  <tbody id="tiaoma">
  <?php foreach($response['data']['barcode'] as $k=>$v){ ?>
     <tr><td><?php echo $v['spec1_code_name'] ?></td><td ><?php echo $v['spec2_code_name'] ?></td><input  id= "<?php echo $v['spec1_code'].'_'.$v['spec2_code'].'_sku'; ?>"  name= "<?php echo 'sku['.$v['spec1_code'].'_'.$v['spec2_code'].']'; ?>" value="<?php echo $v['sku'] ?>"  type='hidden' />

    <td>
        <span class="shuru" >
        <input id= "<?php echo $v['spec1_code'].'_'.$v['spec2_code'].'_barcode'; ?>" name="<?php echo 'barcode['.$v['spec1_code'].'_'.$v['spec2_code'].']'; ?>" type="text" onblur="inputbarcord(this);" style="width:80%;" value="<?php echo $v['barcode'] ?>"/>
        </span>
    </td>

    <td>
    <input class='zu_price' id= "<?php echo $v['spec1_code'].'_'.$v['spec2_code'].'_barcode_price'; ?>" name="<?php echo 'barcode_price['.$v['spec1_code'].'_'.$v['spec2_code'].']'; ?>" type="text" onblur="inputprice(this);" style="width:98%;" value="<?php echo $v['price'] ?>"/>
    </td>
</tr>
   <?php } ?>
  </tbody >
  </table>
  </td></tr>
  <tr><td style="width:80px;"></td><td><button type="submit" class="button button-primary">保存</button>&nbsp;&nbsp;&nbsp;<button type="reset" class="button button-primary">重置</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="hidden" name="msg" id="msg"></td></tr>
</table>
<div><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

<font color="red">提示：只能对已保存的商品规格设置套装明细，所以请设置商品规格先保存，然后在设置套装明细</font>

</div>
</div>
</form>

<div id="form3_div" style="">

</div>

<div id="form4_div" style="">

</div>

</div>
<?php echo load_js('comm_util.js')?>
<?php echo load_js('upload_img.js')?>

 <script type="text/javascript">
 var action = '<?php echo $response['action'];?>';
 var next = '<?php echo isset($response['next'])?$response['next']:'';?>';

 var type= '<?php echo isset($request['type'])?$request['type']:'';?>';
 
 var spec = '<?php echo $response['spec_power']['spec_power'];?>';
 
  var is_exisit = '<?php echo isset($response['data']['barcode'])?1:0;?>';
 if(action == 'do_add'){
 	$("#tab").find('li').eq(1).hide();
 	$("#tab").find('li').eq(2).hide();
 }
 if(next == '1'){

 }

    $(function(){
        if(spec == '0' && is_exisit == '0' && action == 'do_add'){
            tiaolist();
        }
        //选择规格1
        $("#goods_spec1").click(function(){
            var spec1_code_list = $("#spec1_code").val();
            new ESUI.PopWindow("?app_act=prm/goods/select_spec1&spec1_code_list="+spec1_code_list, {
                title: "选择规格1（<?php echo $response['goods_spec1_rename']; ?>）",
                width: 700,
                height: 600,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show();
        })
        //选择规格2
        $("#goods_spec2").click(function(){
            var spec2_code_list = $("#spec2_code").val();
            new ESUI.PopWindow("?app_act=prm/goods/select_spec2&spec2_code_list="+spec2_code_list, {
                title: "选择规格2（<?php echo $response['goods_spec2_rename']; ?>）",
                width: 700,
                height: 610,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show();
        })
    })
//    function spec1_check(_this){
//        if($('input[name="spec1[]"]').is('checked')) {
//            
//        } else {
//            tiaolist();
//            if(confirm("没被选择的规格会被移除，确定移除吗？")){
//                $(_this).parent().parent().remove();
//            }else{
//                $(_this).attr('checked',true);
//                spec_checked('spec1',_this);
//            }
//        }
//    }
    //给规格1赋值
    parent.add_spec1 = function(ids){
        $.each(ids, function(i,val){ 
            $("#spec1_html").append("<div style='display:inline-block; padding-bottom:5px'><span> <input id='spec1_"+val[0]+"'  name='spec1[]' checked aria-disabled='false' type='checkbox' value='"+val[0]+"'/> </span><span style='display:inline-block; width:108px; height:20px; overflow:hidden; vertical-align:text-bottom;' title = '"+val[1]+"'>"+val[1]+"</span></div>");
            $("#spec1_"+val[0]).click(function () {
                spec_checked('spec1',this);
            })
            $("#spec1_"+val[0]).trigger("click");
            $("#spec1_"+val[0]).attr('checked',true);
            
        });         
        tiaolist();
        spec1Check();
    }
    //给规格2赋值
    parent.add_spec2 = function(ids){
        $.each(ids, function(i,val){ 
            $("#spec2_html").append("<div style='display:inline-block; padding-bottom:5px'><span> <input name='spec2[]' checked type='checkbox' id='spec2_"+val[0]+"' value='"+val[0]+"' aria-disabled='false'/> </span><span style='display:inline-block; width:108px; height:20px; overflow:hidden; vertical-align:text-bottom;' title = '"+val[1]+"'>"+val[1]+"</span></div>");
            $("#spec2_"+val[0]).click(function () {
                spec_checked('spec2',this);
            })
            $("#spec2_"+val[0]).trigger("click");
            $("#spec2_"+val[0]).attr('checked',true);    
        }); 
        tiaolist();
        spec2Check();
    }
    function spec_checked(type,_this){
        if(type === 'spec1'){
            if ($(_this).attr('checked') === 'checked') {
                tiaolist('spec1', _this);
            } else {
                tiaolist();
                if(confirm("没被选择的规格会被移除，确定移除吗？")){
                    $(_this).parent().parent().remove();
                }else{
                    $(_this).attr('checked',true);
                    spec_checked('spec1',_this);
                }
            }
        }else{
            if ($(_this).attr('checked') === 'checked') {
                tiaolist('spec2', _this);
            } else {
                tiaolist();
                if(confirm("没被选择的规格会被移除，确定移除吗？")){
                    $(_this).parent().parent().remove();
                }else{
                    $(_this).attr('checked',true);
                    spec_checked('spec2',_this);
                }
            }
        }
    }
 

 form2 = new BUI.Form.HForm({
     srcNode: '#form2',
     submitType: 'ajax',
	 validators : {
		    '#goods_code' : function(value){ //读取input的表单字段 name

		      if(!value){
		    	  return '请添写商品编码';

		      }
		    },
		    
		    '#price' : function(value){ //读取input的表单字段 name
			    if(value){
			    	 var flag = valiFloat(value);
				      if (!flag){
						   return '吊牌价必须为数字或1-2位小数';
						}
			    }
			  },
	     '#msg' : function(value){ //输入条码是否重复
	    	  var spec1_code = $("#spec1_code").val();
		      var spec2_code = $("#spec2_code").val();
		      arr_spec1_code = spec1_code.split(",");
			  arr_spec2_code = spec2_code.split(",");
			  var arr_barcode = new Array();;
			  k = 0;
			  
			  if(spec1_code != '' && spec2_code != ''){
				  for (var i=0; i< arr_spec1_code.length; i++){
				      for(var j=0; j< arr_spec2_code.length; j++){
				    	  arr_barcode[k] = $("#"+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"_barcode").val();
				    	  if(!arr_barcode[k]){
					   		  // alert('商品条码不能为空');
					   		   return '商品条码不能为空';
					   	   }
				    	  k++;
					 }
				   }
			  }
			   len = arr_barcode.length;
			   for( i = 0; i < len; i ++) {
			        for(j = i + 1; j < len; j ++) {
				        if(arr_barcode[i] != '' && arr_barcode[i] != undefined && arr_barcode[j] != '' && arr_barcode[j] != undefined){
				            if (arr_barcode[i] == arr_barcode[j]) {
				            	 return '输入商品条码有重复';
				                break;
				            }
				        }
			        }
			    }


		    },
	 },
     callback: function (data) {
    	 var type = data.status == 1 ? 'success' : 'error';
  		if (data.status == 1) {
 			   BUI.Message.Alert(data.message, type);
  			  window.location.href = '?app_act=prm/goods_combo/view&goods_combo_id='+data.data ;
  		} else {
  			  BUI.Message.Alert(data.message, 'error');
  		}
         
        
     }
 }).render();
//form2
 form2.on('beforesubmit', function () {
     $("#goods_code").attr("disabled", false);
     
 });
//get规格1复选框值
function spec1Check(){
	 //spec1_code
	var str1="";
	var str2="";
	$('input[name="spec1[]"]:checked').each(function(){
		str1+=$(this).val()+",";
		str2+=$(this).parent().next().html()+",";
		  });
	str1 = str1.substring(0,str1.length-1);
	str2 = str2.substring(0,str2.length-1);
	$("#spec1_code").val(str1);
	$("#spec1_name").val(str2);
}
//get规格2复选框值
function spec2Check(){
	//spec2_code
	var str2="";
	var str3="";
	$('input[name="spec2[]"]:checked').each(function(){
		str2+=$(this).val()+",";
		str3+=$(this).parent().next().html()+",";
		  });
	str2 = str2.substring(0,str2.length-1);
	str3 = str3.substring(0,str3.length-1);
	$("#spec2_code").val(str2);
	$("#spec2_name").val(str3);
}
//只允许输入数字，支持两位小数
function valiFloat(value){
	var patrn=/^[1-9]\d*\.\d{1,2}|0\.\d{1,2}|^[1-9]\d*$/;
	 var patrn1 = /^[1-9]\d*\.\d{1,2}$/;
	 var patrn2 = /^[1-9]\d*$/;
	 var patrn3 = /0\.\d{1,2}$/;
	 var flag = false;
	 if(patrn1.exec(value)){
	  	 flag = true;
	  }
	 if(patrn2.exec(value)){
		 flag = true;
	  }
	 if(patrn3.exec(value)){
		 flag = true;
	  }
   return flag;
}
</script>

<script type="text/javascript">

	function PageHead_show_dialog_type(_url, _title, _opts,calljs) {

	    new ESUI.PopWindow(_url, {
	            title: _title,
	            width:_opts.w,
	            height:_opts.h,
	            onBeforeClosed: function() {            eval(calljs+"()");   // get_brand();
               if (typeof _opts.callback == 'function') _opts.callback();

	            }
	        }).show();
	}

   //条码联动
   $("#spec1_html :checkbox").click(function(){
	   tiaolist();
	});
   $("#spec2_html :checkbox").click(function(){
	   tiaolist();
  });
   function tiaolist(){
		 //alert($(this).val());
	   //alert($(this).parent().text());
	   $("#tiaoma").html("");
	   $("#spec1_code").val("");
	   $("#spec2_code").val("");
	   $("#spec1_name").val("");
	   $("#spec2_name").val("");
	   spec1Check();
	   spec2Check();
	   var spec1_code = $("#spec1_code").val();
	   var spec2_code = $("#spec2_code").val();
	   var spec1_name = $("#spec1_name").val();
	   var spec2_name = $("#spec2_name").val();

	   arr_spec1_code = spec1_code.split(",");
	   arr_spec2_code = spec2_code.split(",");
	   arr_spec1_name = spec1_name.split(",");
	   arr_spec2_name = spec2_name.split(",");

	   var html = '';
	   var sell_price = $("#sell_price").val();
	   var weight = $("#weight").val();

	   for (var i=0; i< arr_spec1_code.length; i++){
	      for(var j=0; j< arr_spec2_code.length; j++){
		      if(arr_spec1_code[i] != '' && arr_spec2_code[j] != ''){
			   barcord1 = '';
                           sku_remark = '';
                           price1 = '';
		      var sku = $("#goods_code").val()+arr_spec1_code[i]+arr_spec2_code[j]+"_sku";
		      <?php foreach($response['data']['barcode'] as $k=>$v){ ?>
		         sku1 = '<?php echo $v['sku'];?>';
		         if(sku == sku1){
		        	barcord1 = '<?php echo $v['barcode'];?>';
		        	price1 = '<?php echo $v['price'];?>';
                                sku_remark = '<?php echo isset($v['sku_remark'])?$v['sku_remark']:'';?>';
			     }
		      <?php } ?>
                      if(barcord1=='' && spec=='1'){
                          barcord1 = $('#goods_code').val()+arr_spec1_code[i]+arr_spec2_code[j];
                      }else if(barcord1=='' && spec=='0'){
                          barcord1 = $('#goods_code').val();
                      }
                      
		      html += "<tr><td >"+arr_spec1_name[i]+"</td><td >"+arr_spec2_name[j]+"</td><input  id='"+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"_sku' name= 'sku["+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"]' value='"+sku+"'  type='hidden' /><td ><span class='shuru' style='display:;'><input name='barcode["+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"]' id='"+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"_barcode' style='width:80%;' value='"+barcord1+"' onBlur= 'inputbarcord(this);' type='text' /></span></td><td ><input name='barcode_price["+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"]' id='"+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"_barcode_price' style='width:98%;' value='"+price1+"' onBlur= 'inputprice(this);' type='text' class='zu_price' /></td></tr>";
		      //html += "<tr><td >"+arr_spec1_name[i]+"</td><td >"+arr_spec2_name[j]+"</td><td >"+sku+"<input  id='"+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"_sku' name= '"+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"_sku' value='"+sku+"'  type='hidden' /></td><td ><span class='shuru' style='display:;'><input name='"+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"_barcode' id='"+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"_barcode' style='width:98%;' value='"+barcord1+"' onBlur= 'inputbarcord(this);' type='text' /></span></td><td >"+sell_price+"</td><td >"+weight+"</td><td><input name='"+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"_sku_remark' id='"+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"_sku_remark' style='width:98%;' value='"+sku_remark+"' type='text' /></td></tr>";
		      }
		 }
	   }
	   $("#tiaoma").html(html);
	   }
   function  inputprice(obj){
	   var value = $(obj).val();
	   var flag = valiFloat(value);
	      if (!flag){
			   alert('价格必须为数字或1-2位小数');
			   $(obj).val('');
			}
	   
   }
   function inputbarcord(obj){
	  // $(obj).parent().hide();
	   var value = $(obj).val();
	   var spec = $(obj).attr('id');
	   var good_code = $('#goods_code').val();
		if(value != ''){
			$.ajax({ type: 'POST', dataType: 'json',
			    url: '<?php echo get_app_url('prm/goods_combo/barcode_exist');?>',
			    data: {barcode: value, goods_code: good_code, spec: spec},
			    success: function(ret) {
			    	var type = ret.status == 1 ? 'success' : 'error';
			    	if (type == 'success') {
//				        BUI.Message.Alert(ret.message, type);
				        //$(obj).val('');
				        //value = '填写条码';
				       // $(obj).parent().parent().find(".biao").text('填写条码');
						//$(obj).parent().parent().find(".biao").show();
			    	} else {
				        BUI.Message.Alert(ret.message, type);
			        //BUI.Message.Alert(ret.message, type);
			    		//$(obj).parent().parent().find(".biao").text(value);
						//$(obj).parent().parent().find(".biao").show();
			    	}
			    }
				});
		}else{

			//value = '填写条码';
			//$(obj).parent().parent().find(".biao").text(value);
			//$(obj).parent().parent().find(".biao").show();
		}

	}
	function labelbarcord(obj){

		$(obj).hide();
		$(obj).parent().find(".shuru").show();
		return ;
	}
   $(".stage").click(function(){
	   var barcord = $(this).text();
	  // alert(barcord);
    });	
   function do_page(param) {
		if( $("#goods_combo_id").val() != ''){
			url = "?app_act=prm/goods_combo/"+param+"&goods_combo_id="+$("#goods_combo_id").val()+"&goods_code=" + $("#goods_code").val()+"&ES_frmId=<?php echo $request['ES_frmId'];?>";
			//alert(url);
			location.href  = url;
		}
		
	}
</script>
