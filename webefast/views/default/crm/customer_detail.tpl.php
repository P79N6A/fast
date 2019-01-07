<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-Type" content="text/html; charset=UTF-8" />
<title></title>
<link rel="icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
<link href="assets/css/dpl.css" rel="stylesheet" type="text/css" />
<link href="assets/css/bui.css" rel="stylesheet" type="text/css" />
<link href="assets/css/main-min.css" rel="stylesheet" type="text/css" />
<link href="assets/css/common.css" rel="stylesheet" type="text/css" />
<style>
  .bui-tab-item{
    position: relative;
  }
  .bui-tab-item .bui-tab-item-text{
    padding-right: 25px;
  }

  .addr_tbl{border-collapse:collapse;border:1px #ccc solid;}
  .addr_tbl th,.addr_tbl td{padding:6px;border-collapse:collapse;border:1px #ccc solid;}
  .addr_tbl th{background: #eee;}
.addr_class td{height: 35px;}
</style>
</head>
<body style="overflow-x:hidden;">
    <?php include get_tpl_path('web_page_top'); ?>
<script type="text/javascript" src="../../webpub/js/jquery-1.8.1.min.js"></script>
<script type="text/javascript" src="../../webpub/js/bui/bui.js"></script>
<script type="text/javascript" src="../../webpub/js/util/date.js"></script>
<script type="text/javascript" src="../../webpub/js/common.js"></script>
<script type="text/javascript" src="?app_act=common/js/index"></script>
<script src="assets/js/jquery.formautofill2.min.js"></script>
<div id="container">

<div id="tab">
	<ul>
		<li class="bui-tab-panel-item active"><a href="#">个人信息</a></li>
		<li class="bui-tab-panel-item"><a href="#">收货地址</a></li>
	</ul>
</div>

<div id="form1_data_source" style="display:none;"><?php if(isset($response['form1_data_source'])){ echo $response['form1_data_source']; }?></div>
<div id="panel" class="">
  <div id="p1">
    <form  class="form-horizontal" id="form1" action="?app_act=crm/customer/do_<?php echo $response['app_scene']?>&app_fmt=json" method="post"  onsubmit="return check_all_info(0);">
      <input type="hidden" id="app_scene" name="app_scene" value="" />
      <input type="hidden" id="customer_id" name="customer_id" value=""/>
      <input type="hidden" id="customer_code" name="customer_code" value=""/>
      <input type="hidden" id="pre_shop_code" name="pre_shop_code" value="<?php if(isset($response['shop_code'])){ echo $response['shop_code']; }?>"/>
       <div class="row">
        <div class="control-group span15">
          <label class="control-label span3">会员名称：</label>
          <div class="span10 controls" >
            <input type="text" name="customer_name" id="customer_name" class="input-normal" value=""  data-rules="{required: true}"/>
            <b style="color:red"> * <a id="show_name" stype="display:none" href="javascript:void(0);">显示</a></b>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="control-group span15">
          <label class="control-label span3">昵称：</label>
          <div class="span10 controls" >
            <input type="text" name="nickname" id="nickname" class="input-normal" value=""  />
          </div>
        </div>
      </div>
      <div class="row">
        <div class="control-group span15">
          <label class="control-label span3">来源：</label>
          <div class="span10 controls">
            <select id="sale_channel" name="sale_channel">
            <option value="">-请选择-</option>
            <?php foreach($response["sale_channel"] as $val):  ?>
              <option value="<?php echo $val[0];?>"><?php echo $val[1];?></option>
              <?php endforeach;?>
            </select>&nbsp;&nbsp;&nbsp;
            <select id="shop_code" name="shop_code" data-rules="{required: true}">

            </select><b style="color:red"> *</b>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="control-group span15">
          <label class="control-label span3">性别：</label>
          <div class="span10 controls" >
           
            <input type="radio" name="customer_sex" value="1"/> 男
            <input type="radio" name="customer_sex" value="2"/> 女
            <input type="radio" name="customer_sex" value="3" checked="checked" /> 保密
          
          </div>
        </div>
      </div>
      <div class="row">
        <div class="control-group span15">
          <label class="control-label span3">手机：</label>
          <div class="span10 controls" >
            <input type="text" name="tel" id="tel" class="input-normal" value=""  />
          </div>
        </div>
      </div>
      <div class="row">
        <div class="control-group span15">
          <label class="control-label span3">座机：</label>
          <div class="span10 controls" >
            <input type="text" name="home_tel" id="home_tel" class="input-normal" value=""  />
          </div>
        </div>
      </div>
      <div class="row">
        <div class="control-group span15">
          <label class="control-label span3">生日：</label>
          <div class="span10 controls" >
            <input type="text" name="birthday" id="birthday" class="input-normal calendar" value=""  />
          </div>
        </div>
      </div>
      <div class="row">
        <div class="control-group span15">
          <label class="control-label span3">E-mail：</label>
          <div class="span10 controls" >
            <input type="text" name="email" id="email" class="input-normal" value=""  />
          </div>
        </div>
      </div>
      <div class="row">
        <div class="control-group span15">
          <label class="control-label span3">QQ：</label>
          <div class="span10 controls" >
            <input type="text" name="qq" id="qq" class="input-normal" value=""  />
          </div>
        </div>
      </div>
      
      <div class="row">
        <div class="control-group span15">
          <label class="control-label span3">黑名单：</label>
          <div class="span10 controls" >
          <input type="checkbox" name="status_type" onclick = "changeStatus()" id="status_type" class="input-normal checkbox" value=""  style="width:10px;"/>
          <input type="hidden" value="1" class="bui-form-field" id="type" name="type" />
          </div>
        </div>
      </div>
      
      <div class="row form-actions actions-bar">
        <div class="span13 offset3 ">
          <button type="submit" class="button button-primary" id="submit">提交</button>
          <button type="reset" class="button " id="reset">重置</button>
        </div>
      </div>
    </form>
  </div>
  
  
  <div id="p2">
    <div id="p2_form" >
      <form  id="form2" name="p2_form" action="?app_act=crm/customer/addr_do_save" method="post" onsubmit="check_all_info(1);">
        <input type="hidden" id="customer_address_id" name="customer_address_id" value=""/>
        <input type="hidden" name="customer_code" value=""/>
        <table class="addr_class">
        <tr><td style="color:red;">新增收货地址&nbsp</td><td>&nbsp;&nbsp手机、固定电话选填一项</td></tr>
        <tr>
        <td>所在地区 &nbsp</td>
        <td >
            <select name="country" id="country" style="width:100px;">
                <option value ="">国家</option>
                <?php
                require_lib("util/oms_util");
                $list = oms_tb_all('api_taobao_area', array('type'=>'1'));
                foreach($list as $k=>$v){ ?>
                    <option value="<?php echo $v['id']?>"><?php echo $v['name']?></option>
                <?php } ?>
            </select>
            <select name="province" id="province" style="width:100px;">
                <option>省</option>
            </select>
            <select name="city" id="city" style="width:100px;">
                <option>市</option>
            </select>
            <select name="district" id="district" style="width:100px;">
                <option>区</option>
            </select>
            <select name="street" id="street" style="width:100px;" data-rules="{required: true}">
                <option value="">街道</option>
            </select>
        </td>
        </tr>
        <tr>
        <td>详细地址&nbsp</td>
        <td>
              <input type="text" name="address" class="input-normal" value=""  data-rules="{required: true}"/><b style="color:red"> *</b>
        </td>
        </tr>
        <tr>
               <td>邮编</td>
        <td>
              <input type="text" name="zipcode" class="input-normal" value=""/>
        </td>
        </tr>
        
        <tr>
        <td>收货人</td>
        <td>
              <input type="text" name="name" class="input-normal" value=""  data-rules="{required: true}"/><b style="color:red"> *</b>
        </td>
        </tr>

		<tr>
		        <td>手机</td>
        <td>
              <input type="text" name="tel"  id="address_tel" class="input-normal"   value="" />
        </td>
		</tr>
        <tr>
        <td>固定电话</td>
        <td>
              <input type="text" name="home_tel"  id="address_home_tel" class="input-normal" value=""/>
        </td>
        </tr>
        
        <tr>
        <td>默认收货地址</td>
        <td>
              <input type="checkbox" name="is_default"  class="input-normal checkbox" value="1"/>
        </td>
        </tr>


        </table>
        <div class="row form-actions actions-bar">
          <div class="span13 offset3 ">
            <button type="submit" class="button button-primary" id="submit2">提交</button>
            <button type="reset" class="button " id="reset">重置</button>
          </div>
        </div>
      </form>
    </div>

    <div id="p2_list">

    </div>
  </div>

</div>

<div id="pps">


</div>
<?php if( $response['app_scene']=='edit' &&!empty($response['data']['customer_code'])):?>
<div class="panel">
    <div class="panel-header">
        <h3 class="">地址查看日志 <i class="icon-folder-open toggle"></i></h3>
    </div>
    <div class="panel-body">

            <?php
            render_control('DataTable', 'log', array(
                'conf' => array(
                    'list' => array(
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '用户',
                            'field' => 'user_code',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '单据类型',
                            'field' => 'record_type',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '单据编号',
                            'field' => 'record_code',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '描述',
                            'field' => 'action_note',
                            'width' => '200',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '时间',
                            'field' => 'action_time',
                            'width' => '150',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'sys/security/CustomersSecurityLogModel::get_by_page',
                'idField' => 'id',
                'params' => array('filter' => array('customer_code' => $response['data']['customer_code'])),
            ));
            ?>

    </div>
</div>
<?php endif;?>
<script type="text/javascript">
var form1_data_source_v = $("#form1_data_source").html();
if (form1_data_source_v!=''){
	$('form#form1').autofill(eval("("+form1_data_source_v+")"));
        $('#show_name').show();
}

var form2_data_source_v = $("#form1_data_source").html();
if (form2_data_source_v!=''){
	$('form#form2').autofill(eval("("+form2_data_source_v+")"));
    $("#form2 input[name='home_tel']").val('');
    $("#form2 input[name='tel']").val('');
    $("#form2 input[name='address']").val('');
}

// $response["sale_channel"]
//sale_channel
$(function(){
	$(".radio").css("width","130px");
	$(".input-normal").css("width","160px");
	$(".checkbox").css("width","35px");
        
        
    $('#sale_channel').change(function(){

        var sale_channel_code = $("#sale_channel").val();
        if(sale_channel_code==''){
         return false;
        }
        var url = "?app_act=base/shop/get_shop_list";
     	$.ajax({ type: 'POST', dataType: 'json',
		url: url, data: {sale_channel_code: sale_channel_code},
		success: function(data) {
                    if(data.status == 1){
                       $("#shop_code option").remove();
                       $("<option value=''>请选择</option>").appendTo("#shop_code");
                       for (var i = 0; i < data.data.data.length; i++) {
                            $("<option value='"+data.data.data[i].shop_code+"'>"+data.data.data[i].shop_name+"</option>").appendTo("#shop_code");
			}		
                   var app_scene = '<?php echo $response['app_scene'];?>';
                   if(app_scene == 'edit'){
                   var pre_shop_code = $("#pre_shop_code").val();
                   $("#shop_code").val(pre_shop_code);
                   }		
                    }
                }});
    });

	if ($("#type").val() == 2){
		$("#status_type").attr("checked",true);
	}
    
//     $('#submit').click(function(){
//         var data = {};
//         data.customer_name = $('#customer_name').val();
//         data.customer_sex = $('#customer_sex').val();
//         data.tel = $('#tel').val();
//         data.home_tel = $('#home_tel').val();
//         data.birthday = $('#birthday').val();
//         data.email = $('#email').val();
//         data.qq = $('#qq').val();
//         data.customer_id = $('#customer_id').val();
//         data.shop_code = $('#shop_code').val();
//         var url = "?app_act=crm/customer/do_add";
//        if($('#customer_id').val()!=''){
//            var url = "?app_act=crm/customer/do_edit";
//        }
////        if(check_all_info()===false){
////            return false;
////        }
//
//     	$.ajax({ type: 'POST', dataType: 'json',
//		url: url, data: data,
//		success: function(data) { //$id
//		var type = data.status == 1 ? 'success' : 'error';
////                    BUI.Message.Alert(data.message, function() {
////                    	if (data.status == 1) {
////                        	ui_closePopWindow(getQueryString('ES_frmId'));
////                        }
////                    }, type);
//                }});
//
//     });
//     $('#submit2').click(function(){
//          if($('#customer_id').val()==''){
//                 $('#submit').click();
//                 return true;
//          }
//
//          var data = {};
//         data.address = $("#form2 input[name='address']").val();
//         data.country = $("#form2 input[name='country']").val();
//         data.province = $("#form2 input[name='province']").val();
//         data.city = $("#form2 input[name='city']").val();
//         data.district = $("#form2 input[name='district']").val();
//         data.street = $("#form2 input[name='street']").val();
//         data.zipcode = $("#form2 input[name='street']").val();
//         data.tel = $("#form2 input[name='tel']").val();
//         data.home_tel = $("#form2 input[name='home_tel']").val();
//         data.name = $("#form2 input[name='name']").val();
//
//         var url = "?app_act=crm/customer/do_add";
//        if($('#customer_address_id').val()==''){
//            data.customer_id = $('#customer_id').val();
//        }else{
//             data.customer_address_id = $('#customer_address_id').val();
//        }
//     	$.ajax({ type: 'POST', dataType: 'json',
//		url: url, data: data,
//		success: function(data) { //$id
//                   if($('#customer_address_id').val()==''){
//                       //添加
//                   }else{
//                         $("#p2_form").css("display","none");
//                         get_addr_list();
//                   }
//                }});
//
//     });
    $('#country').change();
    var app_scene = '<?php echo $response['app_scene'];?>';
    if(app_scene == 'edit'){
        var sale_channel_code = '<?php if(isset($response['sale_channel_code'])){ echo $response['sale_channel_code']; }?>';
        $("#sale_channel").val(sale_channel_code);
        $('#sale_channel').change();
        $('#customer_name').attr("disabled",true);
        $('#sale_channel').attr("disabled",true);
         $('#shop_code').attr("disabled",true);
         
       $('#show_name').click(function(){
           var param = {customer_code:$("#customer_code").val()};
           var url="?app_act=crm/customer/show_name&app_fmt=json";
           $.post(url,param,function(ret){
               $('#customer_name').val(ret.data);
           },'json');
           
   
       });
         
         
    }
    
    
    
});

function changeStatus() {
	if ($("#status_type").is(':checked') == true){
	$("#type").val(2);
	}
	else{
	$("#type").val(1);
	}
}

function check_all_info(from_id){

//    if($('#customer_name').val()==""||$('#shop_code').val()==""){
//        $('#tab a').eq(0).click();
//        return false;
//    }
//    if($('#customer_address_id').val()!=''||$('#customer_id').val()==''){
//       if($("#form2 input[name='name']").val()==""||$('#form2 input[name="tel"]').val()==""){
//            $('#tab a').eq(1).click();
//
//             return false;
//        }
//    }
    if(from_id==1){

           if($('#customer_id').val()==""){
            $('#submit').click();
            return false;
           }
    }
   return true;

}


function del_addr(customer_address_id){
  $.get("?app_act=crm/customer/addr_do_delete&customer_address_id="+customer_address_id, function(ret){
      if(ret.status<1){
          BUI.Message.Alert(ret.message,'error');
      }else{
          get_addr_list();
      }
    
  },'json');
}

$(".flag").live("click",function(){
  var customer_address_id = $(this).attr("id");
  $.get("?app_act=crm/customer/set_default&customer_address_id="+customer_address_id, function(data){
    alert('设置成功');
    get_addr_list();
  });
});

function get_addr_list(){
  var customer_code = $("#customer_code").val();
  if (customer_code!=''){
    $.get("?app_act=crm/customer/get_addr_list&customer_code="+customer_code, function(data){
      $("#p2_list").html(data);
    });
  }
}
get_addr_list();

function edit_addr(customer_address_id){
    $.getJSON("?app_act=crm/customer/edit_addr&customer_address_id="+customer_address_id, function(ret){
       if(ret.status<1){
           BUI.Message.Alert(ret.message,'error');
           return ;
       }
      var info = ret.data;
      var addr_row = {'country':info.country,'province':info.province,'city':info.city,'district':info.district,'street':info.street};
      op_area(addr_row);

      //console.log(info);
      //console.log(addr_row);

      $("#form2 input[name='name']").val(info.name);
      $("#form2 input[name='tel']").val(info.tel);
      $("#form2 input[name='home_tel']").val(info.home_tel);
      $("#form2 input[name='zipcode']").val(info.zipcode);
      $("#form2 input[name='address']").val(info.address);
      $("#form2 input[name='customer_address_id']").val(info.customer_address_id);
      $("#form2 input[name='customer_code']").val($('#customer_code').val());
      $("#form2").attr('action','?app_act=crm/customer/update_customer_address');
      if(info.is_default == 1)
      $("#form2 input[name='is_default']").attr("checked",true);
      else
      $("#form2 input[name='is_default']").attr("checked",false);

      $("#p2_form").css("display","block");

    });
}
function  show_data(customer_address_id){
    var url = "?app_act=crm/customer/get_show_addr&app_fmt=json";
    var param = {};
    param.customer_address_id = customer_address_id;
    $.post(url,param,function(ret){
       var row = $('#'+ret.customer_address_id+"_row").find('td');
        row.eq(1).text(ret.name);
        row.eq(2).text(ret.address);
        row.eq(3).text(ret.tel);
        row.eq(5).text(ret.home_tel);
    },"json");
}

BUI.use('bui/form',function(Form){
var form1  = new Form.HForm({
    srcNode : '#form2',
    submitType : 'ajax',
    callback : function(data){
     $('#customer_address_id').val('');
//     $("#p2_form").css("display","none");
     alert('操作成功！');
     $("#form2 input[name='name']").val('');
     $("#form2 input[name='home_tel']").val('');
     $("#form2 input[name='zipcode']").val('');
     $("#form2 input[name='address']").val('');
     $("#form2 select[name='province']").val('');
     $("#form2 select[name='city']").val('');
     $("#form2 select[name='district']").val('');
     $("#form2 select[name='street']").val('');
     $("#form2 input[name='tel']").val('');
     $("#form2").attr('action','?app_act=crm/customer/addr_do_save');
      get_addr_list();
    }
  }).render();

form1.on('beforesubmit',function(){
//    if(!editing.isValid()){
//      return false;
//    }
	if($('#address_home_tel').val()== "" && $('#address_tel').val() == "" )
	{	
     alert("手机、固定电话需选填一项");   
     return false;
  	}
  });

  
});
//
//BUI.use('bui/form',function(Form){
//new Form.HForm({
//    srcNode : '#form1',
//    submitType : 'ajax',
//    callback : function(data){
//         if($('#customer_id').val()==""){
//             $("#form2 input[name='customer_code']").val(data.data);
//             $('#submit2').click();
//         }
//         if($('#customer_address_id').val()!=''){
//             $('#submit2').click();
//         }
//    }
//  }).render();
//});


 BUI.use(['bui/tab','bui/mask'],function(Tab){
        var tab = new Tab.TabPanel({
          srcNode : '#tab',
          elCls : 'nav-tabs',
          itemStatusCls : {
            'selected' : 'active'
          },
          panelContainer : '#panel'//如果不指定容器的父元素，会自动生成
          //selectedEvent : 'mouseenter',//默认为click,可以更改事件

        });
        tab.render();
      });

function getQueryString(name) {
  var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
  var r = window.location.search.substr(1).match(reg);
  if (r != null) return unescape(r[2]); return null;
}

 var form =  new BUI.Form.HForm({
            srcNode : '#form1',
            submitType : 'html',
            callback : function(data){
					var type = data.status == 1 ? 'success' : 'error';

                    BUI.Message.Alert(data.message, function() {
                    	if (data.status == 1) {
                        	ui_closePopWindow(getQueryString('ES_frmId'));
                        }
                    }, type);
				}
    }).render();
</script>

<?php echo load_js('comm_util.js')?>
<script type="text/javascript">

var action = '<?php echo $response['app_scene'];?>';
if(action == 'add'){
	$("#tab").find('li').eq(1).hide();
	$("#tab").find('li').eq(2).hide();
}else{
	$("#form2_data_source").hide();
	
}

function op_area(info){
    var url = '<?php echo get_app_url('base/store/get_area');?>';
    $('#country').change(function(){
        var parent_id = $(this).val();
        areaChange(parent_id,0,url);
    });
    $('#province').change(function(){
        var parent_id = $(this).val();
        areaChange(parent_id,1,url);
    });
    $('#city').change(function(){
        var parent_id = $(this).val();
        areaChange(parent_id, 2, url);
    });
    $('#district').change(function(){
        var parent_id = $(this).val();
        areaChange(parent_id, 3, url);
    });

  //  $("#country").val(info.country);
    areaChange($("#country").val(),0,url,function(){
        $("#province").val(info.province);
        areaChange($("#province").val(),1,url,function(){
            $('#city').val(info.city);
            areaChange($("#city").val(),2,url,function(){
                $('#district').val(info.district);
                areaChange($("#district").val(),3,url,function(){
                    $('#street').val(info.street);
                });
            });
        });
    });
}
op_area();


BUI.use('bui/calendar',function(Calendar){
	var datepicker = new Calendar.DatePicker({
	trigger:'.calendar',
	autoRender : true
	});
	});
</script>
</div>
</body>
</html>
