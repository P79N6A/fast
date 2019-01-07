<?php require_lib('util/oms_util', true);?>
<script src="assets/js/jquery.formautofill2.min.js"></script>
<div id="form1_data_source" style="display:none;"><?php if(isset($response['form1_data_source'])){ echo $response['form1_data_source']; }?></div> 
<form  id="form1" action="?app_act=mid/mid/do_<?php echo $response['app_scene']?>&app_fmt=json" method="post">

<table id="form_tbl" border="1px" bordercolor="#dddddd">
<tr style="background-color:#f5f5f5;">
<td class="tdlabel">&nbsp;&nbsp;基本信息</td>
<td colspan="3">
</td>
</tr>

<tr>
<td class="tdlabel" width="300px" style="text-align:right;">配置名称&nbsp;&nbsp;</td>
<td width="700px">
<input type="hidden" id="id" name="id" value=""/>
<input type="hidden" id="mid_code" name="mid_code" value=""/>
<input type="text" value="" class="input-normal bui-form-field" id="api_name" name="api_name" data-rules="{required: true}"/>
</td>
</tr>
<tr>
<td class="tdlabel" width="300px" style="text-align:right;">应用上线日期&nbsp;&nbsp;</td>
<td width="700px">
<?php if ($response['app_scene'] == 'add'){?>
<input id="online_time"  type="text" value="<?php echo date('Y-m-d');?>" name="online_time" data-rules="{required : true}" class="calendar">
<?php } else { ?>
<input id="online_time"  type="text"  name="online_time" data-rules="{required : true}" class="calendar" <?php if ($response['is_edit_onlinetime'] == '0'){ echo "disabled=disabled"; } ?>>
<?php }  ?>


<span style="color:red;">设置后，只有上线日期（包含当天）发货或收货的单据才会被上传</span>
</td>
</tr>
</table>
<br/>
<table id="form_tbl" border="1px"  bordercolor="#dddddd">
    <tr style="background-color:#f5f5f5;">
      <td class="tdlabel" width="300px" >&nbsp;&nbsp;参数配置</td>
      <td ></td>
    </tr>
    <tr>
      <td class="tdlabel" width="300px" style="text-align:right;">对接系统&nbsp;&nbsp;</td>
      <td width="700px">
            <input type="hidden" id="api_product_flg" name="api_product_flg" value=""/>            
            <?php  foreach( $response['service_data']  as $code=>$val ): ?>     
                <input type="radio" <?php if($response['app_scene'] === 'edit'){ ?> disabled="disabled" <?php }?> value="<?php echo $code; ?>" class="bui-form-field mid_system"  name="api_product"  <?php if ($response['info']['api_product']== $code) {?> checked <?php }?> /><?php echo $val; ?>
            <?php endforeach;?>
                <button id="apitest" type="button" onclick="test_api()">接口测试</button>
      </td>
    </tr>

    <tr class="erp_params" style="display: none">
        <td class="tdlabel" width="300px" style="text-align:right;">对接方式&nbsp;&nbsp;</td>
        <td width="700px">
            <div class="button-group" id="erp_type_view"></div>
            <input type="hidden" id="erp_type" name="erp_type" value=""/>
        </td>
    </tr>

  </table>
  <table id='mid_params'  border="1px"  bordercolor="#dddddd">
      
  </table>

    <table border="1px"  bordercolor="#dddddd">
        <tr class="qm_erp_params" style="display: none">
            <td class="tdlabel" width="300px" style="text-align:right;">目标AppKey&nbsp;&nbsp;</td>
            <td width="700px">
                <input type="text" style="width:120px;" value="<?php echo $response['info']['target_key']; ?>" class="input-normal bui-form-field" id="target_key" name="target_key"/>
                <span class="valid-text" id="rem_error"></span>
            </td>
        </tr>
        <tr class="qm_erp_params" style="display: none">
            <td class="tdlabel" width="300px" style="text-align:right;">Customer ID&nbsp;&nbsp;</td>
            <td width="700px">
                <input type="text" style="width:120px;" value="<?php echo $response['info']['customer_id']; ?>" class="input-normal bui-form-field" id="customer_id" name="customer_id"/>
                <span class="valid-text" id="customer_error"></span>
            </td>
        </tr>
    </table>
  <style>
.add_btn,
.minus_btn{ display:inline-block; float:right; cursor:pointer; margin:0;}
.add_btn img,
.minus_btn img{ vertical-align:text-bottom; margin-right:5px;}
</style>
<br/>
<br/>

<?php if($response['info']['api_product'] != 'mes' || $response['app_scene'] == 'add'){ ?>

<?php }?>
<table id="store" border="1px" bordercolor="#dddddd">
    <tr style = "background-color:#f5f5f5;">
      <td class="tdlabel" style="width:200px" >&nbsp;&nbsp;系统仓库代码</td>
      <td colspan="3" style="width:700px">&nbsp;&nbsp;外部<span class='type_name'></span>的仓库代码
        <p class="add_btn" onclick = "add()"> <img src="assets/images/plus.png" />添加</p></td>
    </tr>
</table>
<br/>
<br/>
<?php if($response['info']['api_product'] != 'mes' || $response['app_scene'] == 'add'){ ?>
<table id="shop"  border="1px" bordercolor="#dddddd">
    <tr style = "background-color:#f5f5f5;">
      <td class="tdlabel" style="width:200px" >&nbsp;&nbsp;系统店铺代码</td>
      <td colspan="3" style="width:700px">&nbsp;&nbsp;外部<span class='type_name'></span>的店铺代码
        <p class="add_btn" onclick = "add_shop()"> <img src="assets/images/plus.png" />添加</p></td>
    </tr>
</table>
<br/>
<br/>

<table id="custom" type='' border="1px" bordercolor="#dddddd">
    <tr style = "background-color:#f5f5f5;">
      <td class="tdlabel" style="width:200px" >&nbsp;&nbsp;系统分销商代码</td>
      <td colspan="3" style="width:700px">&nbsp;&nbsp;外部<span class='type_name'></span>的分销商代码
        <p class="add_btn" onclick = "add_custom()"> <img src="assets/images/plus.png" />添加</p></td>
    </tr>
</table>
<?php } ?>
<table>
<tr>
<td class="tdlabel"><button id="submit" class="button button-primary" type="submit">提交</button></td>

<td colspan="3"><button id="reset" class="button " type="reset">重置</button></td>
</tr>
</table>
</form>
<style>

 td {
    border-top: 1px solid #dddddd;
    line-height: 20px;
    padding: 4px;
    text-align: left;
    vertical-align: top;
}
</style>
<script type="text/javascript">
    var app_scene = "<?php echo $response['app_scene'];?>";
    var config_id = "<?php echo $request['_id'];?>";
    var erp_type = <?php echo $response['erp_type'] ?>;
    set_erp_type(erp_type);
var form1_data_source_v = $("#form1_data_source").html();
if (form1_data_source_v!=''){
	$('form#form1').autofill(eval("("+form1_data_source_v+")"));
}

    BUI.use('bui/toolbar', function (Toolbar) {
        //先扫后称模式
         //erp_type = 1;
        var g1 = new Toolbar.Bar({
            elCls: 'button-group',
            itemStatusCls: {
                selected: 'active' //选中时应用的样式
            },
            defaultChildCfg: {
                elCls: 'button button-small',
                selectable: true
            },
            children: [
                {content: '直连', id: '0', selected: erp_type == 0 ? true : false},
                {content: '奇门', id: '1', selected: erp_type == 1 ? true : false}
            ],
            render: '#erp_type_view'
        });
        g1.render();
        g1.on('itemclick', function (ev) {
            erp_type = ev.item.get('id');
            set_erp_type(erp_type);
        });
    });

    function set_erp_type(erp_type) {
        $("#erp_type").val(erp_type);
        var api_product=$("input[name='api_product']:checked").val();
        if (api_product == 'bserp2' && erp_type == 1) {
            $(".qm_erp_params").show();
        } else {
            $(".qm_erp_params").hide();
        }
    }


$(function(){
    var mid_system_code_1 = $("input[name='api_product'][checked]").val();
    $('.type_name').html(mid_system_code_1);
    if(mid_system_code_1 == 'mes' && app_scene=='add'){
        $('#shop').css('display','none');
        $('#custom').css('display','none');
    }
    if (mid_system_code_1 == 'bserp2') {
        $('.erp_params').show();
    } else {
        $('.erp_params').hide();
    }
    get_tab_param(mid_system_code_1);
    $("input[name=api_product]").change(function(){
        var mid_system_code = $(this).val();
        $('.type_name').html(mid_system_code);
        if(mid_system_code != 'mes' && app_scene=='add'){
            $('#shop').css('display','');
            $('#custom').css('display','');
        }
        if(mid_system_code == 'mes' && app_scene=='add'){
            $('#shop').css('display','none');
            $('#custom').css('display','none');
        }

        if (mid_system_code == 'bserp2') {
            $('.erp_params').show();
        } else {
            $('.erp_params').hide();
        }
        get_tab_param(mid_system_code);
        set_online_time();
        set_erp_type(erp_type);
    });
    init_store();
    init_shop();
    init_custom();
});



function get_tab_param(mid_system_code){
    var url = '?app_act=mid/mid/get_mid_system&app_fmt=json';
    var data = {};
    data.system_code=mid_system_code;
    data.config_id = config_id;
    $.post(url,data,function(result){
       if(result.status==1){
           $("#mid_params").empty();
           var i=1;
           content = '';
           var flg = result.data;
           for(var key in flg){
        	   content += '<tr>';
        	   content += '<td class="tdlabel" width="300px" style="text-align:right;">';
        	   content +=  flg[key]['name']+'</td>';
        	   content += get_type_html(flg[key],key,i);
        	   content += flg[key].desc;
        	   content += '  </tr>';
               	   i++;
           }
           $("#mid_params").append(content);
           
           
       }else{
           $("#mid_params").empty();
       }
    },'json');
}
function getQueryString(name) {
	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
	var r = window.location.search.substr(1).match(reg);
	if (r != null) return unescape(r[2]); return null;
}
 var form =  new BUI.Form.HForm({
        srcNode : '#form1',
        submitType : 'ajax',
        callback : function(data){
                var type = data.status == 1 ? 'success' : 'error';
                BUI.Message.Alert(data.message, function() {
                	if (data.status == 1) {
                        parent.reload_parent_page();
                    	ui_closeTabPage('<?php echo $request['ES_frmId'] ?>'); 
                    }
                }, type);
		}
    }).render();


    form.on('beforesubmit', function () {
        var api_product=$("input[name='api_product']:checked").val();
        var error_img = "<span class='estate error'><span class='x-icon x-icon-mini x-icon-error'>!</span><em>不能为空！</em></span>";
        if (api_product == 'bserp2') {
            if ($("#target_key").val() == '' && erp_type == 1) {
                $("#rem_error").html(error_img);
                return false;
            } else {
                $("#rem_error").html('');
            }
            if ($("#customer_id").val() == '' && erp_type == 1) {
                $("#customer_error").html(error_img);
                return false;
            } else {
                $("#rem_error").html('');
            }
        }
        return true; // 如果不想让表单继续提交，则return false
    });
 
function get_type_html(data,key,i){ 
    var html = ''; 
    if(typeof(data.type) =='undefined'){
        if(app_scene == 'add') {
            if(key == 'connection_mode'){
                html ='<td width="700px"><input  name="' + key + '" id="param'+i+'_val" value="1" type="radio" checked="checked">单据模式<input  name="' + key + '" id="param'+i+'_val" value="2" type="radio" class="cm_2">日报模式&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:red">单据对接模式设定后将无法修改</span></td>';
            }else{
                html ='<td width="700px"><input style="width:120px;" class="input-small control-text" data-rules="{required: true}" name="' + key + '" id="param'+i+'_val" value="" type="text"></td>';
            }
        } else {
            if(key == 'password'){
                html ='<td width="700px"><input style="width:120px;" class="input-small control-text" data-rules="{required: true}" name="' + key + '" id="param'+i+'_val" value="'+data.val+'" type="password"></td>';
            }else if(key == 'connection_mode'){
                if(data.val == 1) {
                     html ='<td width="700px"><input  name="' + key + '" id="param'+i+'_val" value="1" type="radio" checked="checked" <?php if($response['app_scene'] === 'edit'){ echo ' disabled="disabled"'; } ?> >单据模式<input  name="' + key + '" id="param'+i+'_val" value="2" type="radio" <?php if($response['app_scene'] === 'edit'){ echo ' disabled="disabled"'; } ?> >日报模式</td>';
                } else {
                     html ='<td width="700px"><input  name="' + key + '" id="param'+i+'_val" value="1" type="radio" <?php if($response['app_scene'] === 'edit'){ echo 'disabled="disabled"';} ?>>单据模式<input  name="' + key + '" id="param'+i+'_val" value="2" type="radio" checked="checked" <?php if($response['app_scene'] === 'edit'){ echo ' disabled="disabled"'; } ?> >日报模式</td>';
                }
            } else {
                html ='<td width="700px"><input style="width:120px;" class="input-small control-text" data-rules="{required: true}" name="' + key + '" id="param'+i+'_val" value="'+data.val+'" type="text"></td>';  
            }
        }  
    }
    return html;
  }
BUI.use('bui/calendar',function(Calendar){
    var datepicker = new Calendar.DatePicker({
        trigger:'.calendar',
        showTime:true,
        autoRender : true
    });
});

 function add(){
    var tr= add_store_select(store_i,'');
    store_i++;
    $('#store').append(tr);
    set_change_store();
 }
 function add_shop(){
    var tr= add_shop_select(shop_i,'');
    shop_i++;
    $('#shop').append(tr);
    set_change_shop();
 }
 function add_custom(){
    var tr= add_custom_select(custom_i,'');
    custom_i++;
    $('#custom').append(tr);
    set_change_custom();
 }
 function set_change_store(){
    var select = $('#store').find('select');
     select.off("change");
     
     select.on("change",function(){ 
        var name = $(this).attr("name")
        var store_code = $(this).val();
        var check = get_other_store(name,store_code);
         if(check==1 && $(this).val()!='请选择'){
             $(this).val("请选择");
             BUI.Message.Alert("仓库已经被选择，请选择其他仓库",'error');         
         }
          
     });

 }
 function set_change_shop(){
    var select = $('#shop').find('select');
     select.off("change");
     
     select.on("change",function(){ 
        var name = $(this).attr("name")
        var shop_code = $(this).val();
        var check = get_other_store(name,shop_code);
         if(check==1 && $(this).val()!='请选择'){
             $(this).val("请选择");
             BUI.Message.Alert("店铺已经被选择，请选择其他店铺",'error');         
         }
          
     });

 }
 function set_change_custom(){
    var select = $('#custom').find('select');
     select.off("change");
     
     select.on("change",function(){ 
        var name = $(this).attr("name")
        var custom_code = $(this).val();
        var check = get_other_custom(name,custom_code);
         if(check==1 && $(this).val()!='请选择'){
             $(this).val("请选择");
             BUI.Message.Alert("分销商已经被选择，请选择其他分销商",'error');         
         }
          
     });

 }
 
 function get_other_store(name,store_code){
      var select = $('#store').find('select');
      var check = 0;
        $.each(select, function(i, item){
            if(name!=$(item).attr("name")){
                if($(item).val()==store_code){
                      check = 1;
                }
            }
        });
        return check;
 }
 function get_other_custom(name,custom_code){
      var select = $('#custom').find('select');
      var check = 0;
        $.each(select, function(i, item){
            if(name!=$(item).attr("name")){
                if($(item).val()==custom_code){
                      check = 1;
                }
            }
        });
        return check;
 }
 function check(){
	 var all=form.get('children');
		for(var f in all){
      if(all[f]['__attrVals']['param']=='check'){
      	var element = all[f]['__attrVals'];
          element['error']='不能为空';
          element['rules']={required:true};
          }
			}
	 }

 function del(item){
	
	 $(item).parent("td").parent("tr").remove();
}




$(function(){
    set_change_store();
    set_online_time();
});

  var store_list = <?php  echo json_encode($response['store']) ;?>;
  var select_store_data =  <?php if(!empty($response['mid_store'])){ echo json_encode($response['mid_store']) ;}else{ echo "''";}?>;
  var store_i = 0;
  var store_list_str = '';
  function init_store(){
        if(store_list_str==''){
            store_list_str+='<option val="">请选择</option>';
         $.each(store_list,function(i,val){
              store_list_str+='<option value="'+val.store_code+'" >'+val.store_name+'</option>';
          });
        }
      
      if(select_store_data!=''){
          $.each(select_store_data,function(index,obj){
              var tr = add_store_select(store_i,obj);
              $('#store').append(tr);
              store_i++;
          });
      }else{
          var tr =add_store_select(store_i,'');
          $('#store').append(tr);
          store_i++;
      }
       set_change_store();
  }
  function add_store_select(i,obj){
      var html ='';
        html+='<tr>';
        html+='<td class="tdlabel"><select  class="store['+i+']" name="store['+i+'][shop_store_code]" style="width:200px;">'+store_list_str+'</select></td>';
        html+='<td>&nbsp;&nbsp;';
        html+='<input type="text" value="" class="input-normal bui-form-field"  name="store['+i+'][outside_code]"  param="check"  data-rules="{required: true}"/>';
        html+='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:red;">请填写外部对接仓库代码</span>';
        html+="</td>";
        html+="</tr>";
        var tr = $(html);
         tr.find('td').last().append('<p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p>');    
         if(obj!=''){
             tr.find('select option[value="'+obj.join_sys_code+'"]').attr("selected",true);
              tr.find('input[param="check"]').val(obj.outside_code);
         }else{
               tr.find('input[value="1"]').attr("checked",true);
         }
         return tr;
  }
  
  var shop_list = <?php  echo json_encode($response['shop']) ;?>;
  var select_shop_data =  <?php if(!empty($response['mid_shop'])){ echo json_encode($response['mid_shop']) ;}else{ echo "''";}?>;
  var shop_i = 0;
  var shop_list_str = '';
  function init_shop(){
        if(shop_list_str==''){
            shop_list_str+='<option val="">请选择</option>';
         $.each(shop_list,function(i,val){
              shop_list_str+='<option value="'+val.shop_code+'" >'+val.shop_name+'</option>';
          });
        }
      
      if(select_shop_data!=''){
          $.each(select_shop_data,function(index,obj){
              var tr = add_shop_select(shop_i,obj);
              $('#shop').append(tr);
              shop_i++;
          });
      }else{
          var tr =add_shop_select(shop_i,'');
          $('#shop').append(tr);
          shop_i++;
      }
       set_change_shop();
  }
  function add_shop_select(i,obj){
      var html ='';
        html+='<tr>';
        html+='<td class="tdlabel"><select  class="shop['+i+']" name="shop['+i+'][shop_shop_code]" style="width:200px;">'+shop_list_str+'</select></td>';
        html+='<td>&nbsp;&nbsp;';
        html+='<input type="text" value="" class="input-normal bui-form-field"  name="shop['+i+'][outside_code]"  param="check"  data-rules="{required: true}"/>';
        html+='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:red;">请填写外部对接店铺代码</span>';
        html+="</td>";
        html+="</tr>";
        var tr = $(html);
         tr.find('td').last().append('<p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p>');    
         if(obj!=''){
             tr.find('select option[value="'+obj.join_sys_code+'"]').attr("selected",true);
              tr.find('input[param="check"]').val(obj.outside_code);
         }else{
               tr.find('input[value="1"]').attr("checked",true);
         }
         return tr;
  }
  var custom_list = <?php  echo json_encode($response['custom']) ;?>;
  var select_custom_data =  <?php if(!empty($response['mid_custom'])){ echo json_encode($response['mid_custom']) ;}else{ echo "''";}?>;
  var custom_i = 0;
  var custom_list_str = '';
  function init_custom(){
        if(custom_list_str==''){
            custom_list_str+='<option val="">请选择</option>';
         $.each(custom_list,function(i,val){
              custom_list_str+='<option value="'+val.custom_code+'" >'+val.custom_name+'</option>';
          });
        }
      
      if(select_custom_data!=''){
          $.each(select_custom_data,function(index,obj){
              var tr = add_custom_select(custom_i,obj);
              $('#custom').append(tr);
              custom_i++;
          });
      }else{
          var tr =add_custom_select(custom_i,'');
          $('#custom').append(tr);
          custom_i++;
      }
       set_change_custom();
  }
  function add_custom_select(i,obj){
      var html ='';
        html+='<tr>';
        html+='<td class="tdlabel"><select  class="custom['+i+']" name="custom['+i+'][custom_custom_code]" style="width:200px;">'+custom_list_str+'</select></td>';
        html+='<td>&nbsp;&nbsp;';
        html+='<input type="text" value="" class="input-normal bui-form-field"  name="custom['+i+'][outside_code]"  param="check"  data-rules="{required: true}"/>';
        html+='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:red;">请填写外部对接分销商代码</span>';
        html+="</td>";
        html+="</tr>";
        var tr = $(html);
         tr.find('td').last().append('<p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p>');    
         if(obj!=''){
             tr.find('select option[value="'+obj.join_sys_code+'"]').attr("selected",true);
              tr.find('input[param="check"]').val(obj.outside_code);
         }else{
               tr.find('input[value="1"]').attr("checked",true);
         }
         return tr;
  }
  function set_online_time(){
     var api_product = $("input[name='api_product'][checked]").val();
      if(api_product=='bserp2'){
            $("#online_time").addClass("calendar-time");
        }else{
             $("#online_time").removeClass("calendar-time");
        }
  }
  function test_api(){
      var url = "?app_act=mid/mid/test_api&app_fmt=json";
      var param = {};
      param.api_product =  $("input[name='api_product']:checked").val();

      var num = $('#mid_params input').length;
      var i = 0;
      param['api_param_json'] = {};
      while(i<num){
        var item =   $('#mid_params input').eq(i);
        var name = item.attr('name');
        var value = item.val();
          param['api_param_json'][name] = value;
          i++;
      }
      $.post(url,param,function(ret){
          if(ret.status>0){
               BUI.Message.Alert('接口测试成功!','info');
          }else{
              BUI.Message.Alert(ret.message,'error');
          }
          
      },'json');
  }
  
  
</script>
<?php echo load_js('comm_util.js')?>


