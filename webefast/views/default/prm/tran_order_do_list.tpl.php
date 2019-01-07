<script src="assets/js/jquery.formautofill2.min.js"></script>
<style>
    .panel-body {padding: 0;}
    .panel-body table {margin: 0; }
</style>
<?php render_control('PageHead', 'head1',
    array('title' => 'eFAST 3.0数据库配置',
        'links' => array(),
        'ref_table' => 'table'
    ));?>
  <div>
   <table cellspacing="0" class="table table-bordered">
   <tr>
   <td> 
      连接地址： <input type="text" name="db_host" value="" id="db_host" data-rules="{required : true}">
   </td>
   </tr>
   <tr>
   <td> 
      数据库： <input type="text" name="db_name" value="" id="db_name" data-rules="{required : true}">
   </td>
   </tr>
    <tr>
        <td> 用户名：<input type="text" name="db_user" value="" id="db_user" data-rules="{required : true}">  
     </td>
   </tr>

   <tr>
   <td>
    密码：<input type="password" name="db_pass" value="" id="db_pass" data-rules="{required : true}">  
   </td>
   </tr>

  <tr>
   <td>
    <button id="test_mysql_connect" class="button " type="button">数据库连接测试</button></td>
   </td>
   </tr>
   </div>
   </table>
   <table cellspacing="0" class="table table-bordered">
      <tr>
        <td colspan="3">下单时间：
          <div class="control-group span8" style="width:350px;">
            <div class="controls">
              <input type="text" name="created_min" id="created_min" class="input-normal calendar" value="" style="width:150px;" />
                ~
              <input type="text" name="created_max" id="created_max" class="input-normal calendar" value="" style="width:150px;" />
              <label class="remark" for="created_max"></label>
            </div>
          </div>
        </td>
        <td colspan="3">缺货状态：
          <div class="control-group span8" style="width:350px;">
            <div class="controls">
              <select name="short_store_status" id="short_store_status">
                <option value="all">全部</option>
                <option value="0">正常</option>
                <option value="1">缺货</option>
              </select>
            </div>
          </div>
        </td>
        <td colspan="3">发货状态：
          <div class="control-group span8" style="width:350px;">
            <div class="controls">
              <select name="send_store_status" id="send_store_status">
                <option value="all">全部</option>
                <option value="0">未发货</option>
                <option value="1">已发货</option>
              </select>
            </div>
          </div>
        </td>
      </tr>
   </table>
<div>
  <button id="search_order" class="button " type="button">订单检索</button></td>
  <div>
    本次检索订单数量为：<span id="order_count_efast3">0</span>&nbsp;&nbsp;单，
    请点击<button id="export_csv">导出</button>，保存原始数据
  </div>
</div>
<div style="margin-top:10px;">
  <button id="search_order" class="button " type="button">eFAST3.0订单转移到eFAST5.0</button></td>
</div>
   
<script type="text/javascript">
  $(document).ready(function(){
    $("#export_csv").click(function(){
      var created_start = $("#created_min").val();
      var created_end = $("#created_max").val();
      var short_store_status = $("#short_store_status").val();
      var send_store_status = $("#send_store_status").val();
      window.location.href = '?app_act=prm/tran_order/export&app_fmt=json&created_start='+created_start+"&created_end="+created_end+"&short_store_status="+short_store_status+"&send_store_status="+send_store_status;
     });


    $("#test_mysql_connect").click(function(){
      var url = '?app_act=prm/tran_order/test_connect&app_fmt=json';
      var params = {
          "db_host": $("#db_host").val(),
          "db_name": $("#db_name").val(),
          "db_user": $("#db_user").val(),
          "db_pass": $("#db_pass").val(),
      };
      if ($("#db_host").val().length == '') {
        alert("连接地址不能为空");
        return;
      };
      if ($("#db_name").val().length == '') {
        alert("数据库不能为空");
        return;
      };
      if ($("#db_user").val().length == '') {
        alert("用户名不能为空");
        return;
      };
      
      $.ajax({  
        type: "post",  
        url: url,  
        dataType: "json",  
        data: params,  
        success: function(data){  
          if (data.status == 1) {
            alert('连接成功');
          } else {
            alert('连接失败');
          }
        }  
      });
    });  

    $("#search_order").click(function(){
      var url = '?app_act=prm/tran_order/search_order&app_fmt=json';
      var params = {
          "created_start": $("#created_min").val(),
          "created_end": $("#created_max").val(),
          "short_store_status": $("#short_store_status").val(),
          "send_store_status": $("#send_store_status").val(),
      };

      $.ajax({  
        type: "post",  
        url: url,  
        dataType: "json",  
        data: params,  
        success: function(data){
          if (data.status == 1) {
            $("#order_count_efast3").html(data.order_count);
          } else {
            alert("订单检索失败");
          }
        }  
      });
    });  


    BUI.use('bui/calendar',function(Calendar){
        new Calendar.DatePicker({
            trigger:'#created_min',
            autoRender : true
        });
        new Calendar.DatePicker({
            trigger:'#created_max',
            autoRender : true
        });
    });


  });
</script>
<script type="text/javascript">  
$(function() {  
  $("#subbtn").click(function() {  
    var params = $("input").serialize();  
    
  });  
  
});  
  
</script> 
<?php echo load_js('comm_util.js')?>