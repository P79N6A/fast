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
  tr{height:35px; }
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
<?php
$tabs = array(
    array('title' => '基本信息', 'active' => true, 'id' => 'tabs_base'),
);
$abc = render_control('TabPage', 'TabPage1', array(
    'tabs' => $tabs,
    'for' => 'TabPage1Contents'
        ));
?>
<div id="TabPage1Contents">
    <div id="p1">
        <form id="form1" name="p1_form" action="?app_act=base/shop_entity/do_<?php echo $response['app_scene'] ?>&app_fmt=json" method="post">
            <table>
                <tr>
                    <td>店铺代码&nbsp</td>
                    <td>
                        <input type="text" name="shop_code" disabled class="input-normal" value=""  data-rules="{required: true}"/><b style="color:red"> *</b>
                    </td>
                </tr>
                <tr>
                    <td>店铺名称&nbsp</td>
                    <td>
                        <input type="text" id="shop_name" name="shop_name" class="input-normal" value=""  data-rules="{required: true}"/><b style="color:red"> *</b>
                    </td>
                </tr>
                <tr>
                    <td>店铺助记符&nbsp</td>
                    <td>
                        <input type="text" name="shop_user_nick" class="input-normal" value=""/>
                    </td>
                </tr>
                <tr>
                    <td>联系电话&nbsp</td>
                    <td>
                        <input type="text" name="tel" class="input-normal" value=""/>
                    </td>
                </tr>
                <tr>
                    <td>店铺地址 &nbsp</td>
                    <td >
                        <select name="province" id="province" style="width:100px;">
                            <option>省</option>
                        </select>
                        <select name="city" id="city" style="width:100px;">
                            <option>市</option>
                        </select>
                        <select name="district" id="district" style="width:100px;" data-rules="{required: true}">
                            <option>区</option>
                        </select>
                        <select name="street" id="street" style="width:100px;" >
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
                    <td>营业时间</td>
                    <td>
                        <input type="text" id="open_time" name="open_time" class="input-normal" value="09:00-18:00"/><b style="color:red"> *格式为：09:00-18:00</b>
                    </td>
                </tr>
                <tr>
                    <td>备注</td>
                    <td>
                        <input type="text" name="remark" class="input-normal" value="" />
                    </td>
                </tr>
                <input type="hidden" id="shop_id" name="shop_id"/>
                <input type="hidden"  name="shop_code" />
            </table>
            <div class="row form-actions actions-bar">
                <div class="span13 offset3 ">
                    <button type="submit" class="button button-primary" id="submit">提交</button>
                    <button type="reset" class="button " id="reset">重置</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php echo load_js('comm_util.js')?>
<script type="text/javascript">
    var shop_id = "<?php echo $response['data']['shop_id']?>";
    var scene = "<?php echo $app['scene']; ?>";
    
    //店铺代码生成
    if (scene == 'add') {
        var url = '?app_act=base/shop_entity/serial_num';
        $.ajax({
            type: "POST",
            url: url,
            data: {},
            dataType: "json",
            async: true,
            success: function (data) {
                if (data.status == 'success') {
                    $("#form1 input[name='shop_code']").attr('value',data.data);
                }
            }
        });
    }

    $(function(){
        if(scene=='edit'){
            var addr_row = {
                'province':"<?php echo !empty($response['data']['province'])?$response['data']['province']:'';?>",
                'city':"<?php echo !empty($response['data']['city'])?$response['data']['city']:'';?>",
                'district':"<?php echo !empty($response['data']['district'])?$response['data']['district']:'';?>",
                'street':"<?php echo !empty($response['data']['street'])?$response['data']['street']:'';?>"
            };
            op_area(addr_row);
            $("#form1 input[name='shop_id']").attr('value',"<?php echo !empty($response['data']['shop_id'])?$response['data']['shop_id']:'';?>");
            $("#form1 input[name='shop_code']").attr('value',"<?php echo !empty($response['data']['shop_code'])?$response['data']['shop_code']:'';?>");
            $("#form1 input[name='shop_name']").attr('value',"<?php echo !empty($response['data']['shop_name'])?$response['data']['shop_name']:'';?>");
            $("#form1 input[name='shop_user_nick']").attr('value',"<?php echo !empty($response['data']['shop_user_nick'])?$response['data']['shop_user_nick']:'';?>");
            $("#form1 input[name='tel']").attr('value',"<?php echo !empty($response['data']['tel'])?$response['data']['tel']:'';?>");
            $("#form1 input[name='address']").attr('value',"<?php echo !empty($response['data']['address'])?$response['data']['address']:'';?>");
            $("#form1 input[name='open_time']").attr('value',"<?php echo !empty($response['data']['open_time'])?$response['data']['open_time']:'';?>");
            $("#form1 input[name='remark']").attr('value',"<?php echo !empty($response['data']['remark'])?$response['data']['remark']:'';?>");
        }
    });
    
    function op_area(info){
        var url = '<?php echo get_app_url('base/shop_entity/get_area'); ?>';
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
        areaChange(1,0,url,function(){
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
    
    BUI.use('bui/form',function(Form){
        var form1  = new Form.HForm({
        srcNode : '#form1',
        submitType : 'ajax',
        callback : function(data){
            if(data.status==1){
                BUI.Message.Alert(data.message,'success');
                ui_closePopWindow('<?php echo $request['ES_frmId'] ?>');
                window.location.reload();
            }else{
                BUI.Message.Alert(data.message,'error');
            }
        }
      }).render();  
      
      form1.on('beforesubmit',function(){
          var re = /^([0-1][0-9]|[2][0-3])(:|：)([0-5][0-9])-([0-1][0-9]|[2][0-3])(:|：)([0-5][0-9])$/;
            if(!re.test($('#open_time').val())){	
                BUI.Message.Alert('营业时间格式为：09:00-18:00','error'); 
                return false;
            }
        });
    });
    
    
</script>
</div>
</body>
</html>


