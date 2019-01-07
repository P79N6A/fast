<style>
        .bui-tab-item{
        position: relative;
    }
    .bui-tab-item .bui-tab-item-text{
        padding-right: 25px;
    }
    li{height:30px;}
</style>
<?php $list = $response['all_store'];
$store_select = '<option value="">请选择</option>';
foreach ($list as $k => $v) {
    $store_select.='<option value="' . $v['store_code'] . '">' . $v['store_name'] . '</option>';
}
?>
<form class="form-horizontal well" style="padding: 5px 0 10px;" id="edit_store_info">
    <div id="edit_warehouse" class="edit_warehouse">
            <ul>
                <li class="note">移出日期：<input type="text" id="is_shift_out_time" name="is_shift_out_time" class="calendar" value="<?php echo $response['start_time']?>"></li>
                <li class="note">移入日期：<input type="text" id="is_shift_in_time" id="is_shift_in_time" class="calendar" value="<?php echo $response['end_time']?>"></li>
                <li class="note">移出仓库：<?php echo $response['store']['store_name']?></li>
<!--                <li class="note">rr：<input type="text" id="aa" class="aa" value="pp"></li>-->
                <li class="note">移入仓库：<select id='sel_op' name="shift_in_store_code" style="width:200px;"><?php echo $store_select;?></select></li>
            </ul>
        </div>
    <div id="unedit_store" class="unedit_store">
              <ul>
                <li class="note">原仓库：<?php echo $response['store']['store_name']?></li>
                <li class="note">更改仓库：<select id='sel_lt' name="store_code" style="width:200px;"><?php echo $store_select;?></select></li>
            </ul>
        
    </div>
    <input type="hidden" name="edit_type" id="edit_type" value="<?php echo $response['type']; ?>">
    <input type="hidden" name="old_store" id="old_store" value="<?php echo $response['store']['store_code']; ?>">
    <input type="hidden" name="jewelry_id" id="jewelry_id" value="<?php echo $response['id']; ?>">
</form>
         <div class="row form-actions actions-bar">
                <div class="span13 offset3 ">
                    <button type="confirm" class="button button-primary" id="confirm">确认</button>
                    <button type="cancle" class="button" id="cancle" >取消</button>
                </div>
            </div>

<script type="text/javascript">
    $(document).ready(function (){
        var edit_type = $("#edit_type").val();
        var shift_out_store_code = $('#old_store').val();//出库
        var jewelry_id = $('#jewelry_id').val();
        //console.log(jewelry_id);
        if(edit_type=='edit_warehouse'){
            $('#edit_warehouse').show();
            $('#unedit_store').hide();
        }else{
            $('#edit_warehouse').hide();
            $('#unedit_store').show();
        }
        $('#confirm').on('click',function(){
            if(edit_type=='unedit_store'){
               var store = $("#sel_lt  option:selected").val();
               if(store==''){
                      BUI.Message.Alert('请选择仓库', 'error');
                      return false;
                  }
               if(store==shift_out_store_code){
                      BUI.Message.Alert('请选择不同仓库', 'error');
                      return false;
                  }
                  var result = {
                      "store":store,
                      "jewelry_id":jewelry_id,
                  };
                  //console.log(result);
                  $.post("?app_act=prm/create_store_tl/save_store", result, function (data) {
                      if (data.status < 0) {
                           BUI.Message.Alert(data.message, 'error');
                       } else {
                           BUI.Message.Alert(data.message);
                            
                       }
                  }, "json");
            }else{
                 var shift_in_store_code = $("#sel_op  option:selected").val(); //下拉选择库
                  var is_shift_in_time = $('#is_shift_in_time').val(); //移入日期
                  var is_shift_out_time = $('#is_shift_out_time').val();//移出日期
                  
                    if(shift_in_store_code==''){
                           BUI.Message.Alert('请选择仓库', 'error');
                           return false;
                       }
                    if(shift_in_store_code==shift_out_store_code){
                           BUI.Message.Alert('请选择不同仓库', 'error');
                           return false;
                       }

                       var param = {
                           "is_shift_in_time":is_shift_in_time,
                           "is_shift_out_time":is_shift_out_time,
                           "shift_in_store_code":shift_in_store_code,
                           "shift_out_store_code":shift_out_store_code,
                           "jewelry_id":jewelry_id,
                          
                       };
                       
                       $.post("?app_act=prm/create_store_tl/transfer_warehouse", param, function (data) {
                           if (data.status < 0) {
                                BUI.Message.Alert(data.message, 'error');
                            } else {
                                BUI.Message.Alert(data.message);
                                
                            }
                       }, "json");
            }

        })
    });
      //取消关闭弹窗
       $('#cancle').click(function () {
            ui_closeTopPopWindow();

        });
    </script>
    <script type="text/javascript">
        BUI.use('bui/calendar',function(Calendar){
          var datepicker = new Calendar.DatePicker({
            trigger:'.calendar',
            autoRender : true
          });
        });
    </script>

