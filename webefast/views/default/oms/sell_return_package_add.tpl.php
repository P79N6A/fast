<style>
.panel-body {padding: 0;}
.panel-body table {margin: 0; }
/*#panel_baseinfo input{width: 140px;}*/
#panel_baseinfo select{width: 145px;}
.sear_ico{ top:3px;}
.form-horizontal .controls {margin-left: 0px;float:none;}
.shdz .valid-text{ display:inline-block; width:194px;}
</style>

<?php render_control('PageHead', 'head1',
    array('title' => '退货包裹单列表',
//	    'links' => array(
//	        array('url' => 'oms/sell_record/import_trade', 'title' => '订单导入', 'is_pop' => false),
//	    ),
        'ref_table' => 'table'
    ));
?>
<form id="form1" method="post" action="?app_act=oms/sell_return/check_express_code_and_no" tabindex="0" style="outline: none;">
    <div class="panel">
        <div class="panel-header clearfix">
            <h3 class="pull-left">退货包裹单</h3>
            <div class="pull-right"></div>
        </div>
        <div class="panel-body" id="panel_baseinfo">
            <table cellspacing="0" class="table table-bordered">
                <tbody>
                <tr >
                    <td >单据编号 ：</td>
                    <td><input id="return_package_code" type="text" value="<?php echo $response['return_package_code'];?>" name="return_package_code" readonly="readonly"></td>
                    
                    <td>业务日期：</td>
                        <td><input id="stock_date" type="text" value="<?php echo date('Y-m-d');?>" name="stock_date" data-rules="{required : true}" class="input-normal calendar"></td>
                </tr>
                <tr>
                    <td >退货仓库：</td>
                    <td>
                        <select name="store_code" id="store_code" data-rules="{required : true}">
                            <option value ="">请选择</option>
                            <?php foreach($response['store'] as $v){ ?>
                                <option value="<?php echo $v['store_code']?>"><?php echo $v['store_name']?></option>
                            <?php } ?>
                        </select>
                    </td>
                    
                    <td>无名包裹：</td>
                    <td>
                        <input type="radio" name="tag" value="1"/> 是
                        <input type="radio" name="tag" value="2"/> 否
                    </td>            
                </tr>
                <tr>
                    <td>店铺：</td>
                    <td>
                        <select name="shop_code" id="shop_code">
                            <option value ="">请选择</option>
                            <?php foreach ($response['shop'] as $key => $val){ ?>
                                <option value="<?php echo $val['shop_code']?>"><?php echo $val['shop_name']?></option>
                            <?php }?>
                        </select>
                    </td>
                    <td>买家昵称：</td>
                    <td><input type="text" name="buyer_name" id="buyer_name" value=""></td>
                </tr>
                <tr>
                    <td>配送方式：</td>
                    <td>
                        <select name="return_express_code" id="return_express_code" data-rules="{required : true}">
                            <option value ="">请选择</option>
                            <?php $list = oms_tb_all('base_express', array('status'=>1)); 
                            foreach($list as $k=>$v){ ?>
                                <option value="<?php echo $v['express_code']?>"><?php echo $v['express_name']?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <td>快递单号 ：</td>
                    <td><input id="return_express_no" type="text" value="" name="return_express_no"></td>
                </tr>
                <tr>
                    <td>退货人姓名 ：</td>
                    <td>
                        <input id="return_name" type="text" value="" name="return_name" >
                    </td>
                    <td>退货人手机 ：</td>
                    <td><input id="return_mobile" type="text" value="" name="return_mobile" data-rules="{number : true}"></td>
                </tr>
                <tr>
                    <td>退货地址 ：</td>
                    <td colspan="3">
                        <select id="country" name="country" data-rules="{required : true}">
                            <option value ="">请选择国家</option>
                            <?php foreach($response['area']['country'] as $k=>$v){ ?>
                                <option  value ="<?php echo $v['id']; ?>"  ><?php echo $v['name']; ?></option>
                            <?php } ?>
                        </select>
                        <select id="province" name="province" data-rules="{required : true}"></select>
                        <select id="city" name="city" data-rules="{required : true}"></select>
                        <select id="district" name="district"></select>
                        <!--<select id="street" name="street"></select>-->
                        <input id="receiver_addr" type="text" name="receiver_addr" data-rules="{required : true}">
                    </td>
                </tr>
                <tr>
                    <td>关联退单号 ：</td>
                    <td>
                        <input id="sell_return_code" type="text" value="" name="sell_return_code">
                    </td>
                    <td>关联交易号 ：</td>
                    <td><input id="deal_code" type="text" value="" name="deal_code"></td>
                </tr>
                <tr>
                    <td>备注 ：</td>
                    <td colspan="3">
                        <input id="remark" type="text" name="remark" >
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div style="text-align: center;">
        <button class="button button-primary" type="submit" >提交</button>
    </div>
</form>
<?php echo load_js('comm_util.js')?>

<script>
var selectPopWindowshelf_code = {
	    dialog: null,
	    callback: function (value, id, code, name) {
                $.ajax({
                    type: "GET",
                    url: "?app_act=crm/customer/get_default_addr",
                    async: false,
                    data: {customer_code:value[0]['customer_code'],app_fmt:'json'},
                    dataType: "json",
                    success: function(data){
                        if(data.status==1){
                            $("#receiver_name").val(data.data['name']);
                            $("#receiver_mobile").val(data.data['tel']);
//                            $("#receiver_phone").val(data.data['home_tel']);
                            $("#receiver_addr").val(data.data['address']);
                            $("#receiver_zip_code").val(data.data['zipcode']);
                            $.ajaxSetup({async:false});
                            fill_select(change_after,'country',data.data['country']);
                            fill_select(change_after,'province',data.data['province']);
                            fill_select(change_after,'city',data.data['city']);
                            fill_select(change_after,'district',data.data['district']);
//                            fill_select(change_after,'street',data.data['street']);
                            $.ajaxSetup({async:true});
                        }
                    }
                });
	    }
	};
        function change_after(param){
            $("#"+param).change();
        }
        function fill_select(callback,param,value){
            $("#"+param).val(value);
            callback(param);
        }
</script>


<script type="text/javascript">
    var ES_frmId  = '<?php echo $request['ES_frmId'];?>';
    var searchFormForm;
    var flag = true;
    $(function(){
        $("#sell_return_code").blur(function(){
            if($("#sell_return_code").val() != '') {
                var params = {
                    "sell_return_code": $("#sell_return_code").val(),
                    "app_fmt": "json",
                };
                $.post("?app_act=oms/sell_return/is_return_code", params, function(data){
                    if(data.status == 1){
                        var shop_code = data.data.shop_code;
                        var buyer_name = data.data.buyer_name
                        $("#shop_code").val(shop_code);
                        $("#buyer_name").val(buyer_name);
                        $("#shop_code").attr('disabled',true);
                        $("#buyer_name").attr('disabled',true);
                    } else {
                        $("#shop_code").attr('disabled',true);
                        $("#buyer_name").attr('disabled',true);
                    }
                },'json')
            } else {
                $("#shop_code").attr('disabled',false);
                $("#buyer_name").attr('disabled',false);
            }
        })
    })
    /*BUI.use('bui/form',function (Form) {
        var form1 = new BUI.Form.HForm({
            srcNode : '#form1',
            submitType : 'ajax',
            callback : function(data){
                if(data.status != '1'){
                    BUI.Message.Alert(data.message, 'error');
                    return;
                }
                window.location = "?app_act=oms/sell_return/package_detail&return_package_code="+data.data+"&ES_frmId="+ES_frmId
            }
        }).render();

        form1.on('beforesubmit', function () {
            var buyer_name = $("#buyer_name").val();
            var tag = $('input:radio[name="tag"]:checked').val();
            if (buyer_name == '' && tag == 2) {
                BUI.Message.Alert('买家昵称不能为空', 'error');
                return false;
            }
        });
?app_act=oms/sell_return/add_package_action

    });*/
    /*=======================*/
    BUI.use('bui/form',function (Form) {
        var form1 = new BUI.Form.HForm({
            srcNode : '#form1',
            submitType : 'ajax',
            callback : function(ret){
                if(ret.status=='exist'){
                    BUI.Message.Confirm('快递单号'+ret.data+'已经存在，确认继续吗！',function(){
                    do_submit();
                    },'question');
                }else{
                    do_submit();
                }
            }
        }).render();
        form1.on('beforesubmit', function () {
            var buyer_name = $("#buyer_name").val();
            var tag = $('input:radio[name="tag"]:checked').val();
            if (buyer_name == '' && tag == 2) {
                BUI.Message.Alert('买家昵称不能为空', 'error');
                return false;
            }
        });

    });
    function do_submit(){
        var url='?app_act=oms/sell_return/add_package_action';
        $.post(url,$('#form1').serialize(),function(data){
            if(data.status != '1'){
                BUI.Message.Alert(data.message, 'error');
                return;
            }
            window.location = "?app_act=oms/sell_return/package_detail&return_package_code="+data.data+"&ES_frmId="+ES_frmId
        },'json')

    }

  /* =============================*/
    BUI.use('bui/calendar',function(Calendar){
        var datepicker = new Calendar.DatePicker({
            trigger:'.calendar',
           // showTime:true,
            autoRender : true
        });
    });

    var url = '<?php echo get_app_url('base/store/get_area');?>';
    $(document).ready(function(){
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
            areaChange(parent_id,2,url);
        });
        $('#district').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,3,url);
        });
//        $("#shop_code").change(function(){
//            $("#express_code").val(shopExpressList[$(this).val()]);
//            $("#store_code").val(shopStoreList[$(this).val()]);
//        });
        $("#sale_channel_code").change(function(){
            var html = '<option value ="">请选择</option>';
            if(typeof channelShopList[$(this).val()]!=='undefined'){
                $.each(channelShopList[$(this).val()],function(n,v){
                    html += "<option value='"+n+"'>"+v+"</option>";
                });
            }
//            $("#shop_code").html(html);
        });
       
        $("#country").find("option[value=1]").attr("selected","selected");
        $("#country").change();
        $("#sale_channel_code").change();
        $("#cod_type").hide();
    })
</script>
