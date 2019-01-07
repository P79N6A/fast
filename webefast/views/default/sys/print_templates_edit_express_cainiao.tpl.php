<style>
    table .span4 { float: left; }
    .row { margin-left: 0px; }
    .paper input.num {width: 60px;}
    .print-item {cursor: pointer}
    legend {margin-bottom: 3px; margin-top: 10px;}
    .bui-uploader .bui-queue { display: none; }
</style>
<script src="assets/js/CaiNiaoPrintFuncs.js"></script>
<?php
render_control('PageHead', 'head1', array('title' => '编辑快递模板: ' . $response['record']['print_templates_name'],
    'links' => array(
    //array('url'=>'sys/print_templates/do_list', 'title'=>'模板列表', 'is_pop'=>false, 'pop_size'=>'800,600'),
    //array('type'=>'js', 'js'=>'doSave()', 'title'=>'保存'),
    //array('type'=>'js', 'js'=>'doPreview()', 'title'=>'预览'),
    //array('type'=>'js', 'js'=>'removePrintVar()', 'title'=>'移除选中'),
    ),
    'ref_table' => 'table'
));
?>
<object id="CaiNiaoPrint_OB" classid="clsid:09896DB8-1189-44B5-BADC-D6DB5286AC57" width=0 height=0> 
    <embed id="CaiNiaoPrint_EM" TYPE="application/x-cainiaoprint" width=0 height=0  ></embed>
</object> 
<textarea class="hide" id="self_body" name="template_body"><?php echo $response['template_body']['self_body'] ?></textarea>


<table cellpadding="0" cellspacing="0" width="100%" style="table-layout:fixed;">
    <tr>
        <td style="width: 70%; height: 100%">
            <div class="row">
                <ul class="toolbar">
                    <li><label class="checkbox">
                            <select id="record_index" name="record_index" onchange="doLink(<?php echo $response['record_index'] ?>, $(this).val())">
                                <?php foreach ($response['record_list'] as $k => $v) { ?>
                                    <option value="<?php echo $v['print_templates_id'] ?>"><?php echo $v['print_templates_name'] ?></option>
<?php } ?>
                            </select>
                        </label></li>
                    <!--li><button class="button button-primary" onclick="resetDefault()">恢复默认</button></li-->
                    <li><button class="button button-primary" onclick="doPreview()">预览</button></li>
                    <li><button class="button button-primary" onclick="doSave()">保存</button></li>
                    <li><button class="button button-primary" onclick="removePrintVar()">移除选中控件</button></li>
                </ul>
            </div>


            
          <object id="CaiNiaoPrint_OB_2" classid="clsid:09896DB8-1189-44B5-BADC-D6DB5286AC57" width="100%" height="800px">
  <param name="Caption" value="内嵌显示区域">
  <param name="Border" value="1">
  <param name="Color" value="#C0C0C0">
  <embed id="CaiNiaoPrint_EM_2" TYPE="application/x-cainiaoprint" width="100%" height="800px">
</object>   
            

        </td>
        <td style="width: 30%; padding-left: 5px;">
            <fieldset>
                <legend>纸张信息</legend>
                <div class="row paper">
 
                 打印机:
                    <select id="printer" name="printer">

                    </select>
                    <br>
                    模板名称: <input type="text" id="print_templates_name" name="print_templates_name" value="<?php echo $response['record']['print_templates_name'] ?>">
                    <br />
                    快递公司
                    <input type="text"   id="company_name" name="company_name" value="<?php  echo $response['record']['company_name']?>" disabled="disabled" /><br />
                     快递类型
                     <select  id="express_type" name="express_type"  >
                         <option value="">请选择</option>
                         <?php foreach($response['express_type'] as $val):?>
                         <option value="<?php echo $val;?>" <?php if($response['template_body']['express_type']==$val){ echo 'selected="selected"';} ?>  ><?php echo $val;?></option>
                         <?php    endforeach;?>
                     </select>
                             <br />
                             签收联logo打印  <input type="checkbox"  id="ali_waybill_cp_logo_up" name="ali_waybill_cp_logo_up" <?php if($response['template_body']['ali_waybill_cp_logo_up']==0) echo 'checked="checked"'; ?>  />       <br />
留存联logo打印 <input type="checkbox"  id="ali_waybill_cp_logo_down" name="ali_waybill_cp_logo_down"  <?php if($response['template_body']['ali_waybill_cp_logo_down']==0) echo 'checked="checked"'; ?>  />     
                </div>
            </fieldset>

            <fieldset>
                <legend>收货人信息</legend>
                <div class="doc-content span16">
                    <div class="row show-grid">
<?php $i = 0;
foreach ($response['variables']['receiving'] as $k => $v) {
    $i++ ?>
                            <div class="span4">
                                <label style="width: 100px">
                                    <span title="<?php echo $k; ?>" class="print-item"><?php echo $v; ?></span>
                                </label>
                            </div>
    <?php if ($i % 3 == 0) echo '</div><div class="row">'; ?>
<?php } ?>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>发货信息</legend>
                <div class="doc-content span16">
                    <div class="row show-grid">
                        <?php $i = 0;
                        foreach ($response['variables']['delivery'] as $k => $v) {
                            $i++ ?>
                            <div class="span4">
                                <label style="width: 100px">
                                    <span title="<?php echo $k; ?>" class="print-item"><?php echo $v; ?></span>
                                </label>
                            </div>
    <?php if ($i % 3 == 0) echo '</div><div class="row">'; ?>
<?php } ?>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>商品信息（组合明细信息）</legend>
                <div class="doc-content span16">
                    <input id="detail_row" name="detail_row" type="checkbox" <?php if (isset($response['template_val']['detail_row']) && $response['template_val']['detail_row'] == 1) echo 'checked="checked"'; ?> />每行一条商品
                    <a href="javascript:void(0)" id="add_detail">多条明细</a>
                    <div class="row show-grid">
                        <div class="span4">  <label style="width: 100px"></label></div>
                        <?php $i = 0;
                        foreach ($response['variables']['detail'] as $k => $v) {
                            $i++ ?>
                            <div class="span4">
                                <label style="width: 100px">
                                    <span title="<?php echo $k; ?>" class="print-item"><?php echo $v; ?></span>
                                </label>
                            </div>
                            <?php if ($i % 3 == 0) echo '</div><div class="row">'; ?>
                        <?php } ?>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>订单信息</legend>
                <div class="doc-content span16">
                    <div class="row show-grid">
<?php $i = 0;
foreach ($response['variables']['record'] as $k => $v) {
    $i++ ?>
                            <div class="span4">
                                <label style="width: 100px">
                                    <span title="<?php echo $k; ?>" class="print-item"><?php echo $v; ?></span>
                                </label>
                            </div>
    <?php if ($i % 3 == 0) echo '</div><div class="row">'; ?>
<?php } ?>
                    </div>
                </div>
            </fieldset>


        </td>
    </tr>
</table>

<input id="detail" name="detail" type="hidden" value="<?php echo  $response['template_val']['detail'];?>" />
<input id="itemkey" name="itemkey"  type="hidden" value="<?php echo  $response['template_val']['itemkey'];?>" />

<input type="hidden" id="CP_CODE" value="<?php echo $response['template_body']['cp_code'];?>">
<input type="hidden" id="CONFIG" value="<?php echo $response['template_body']['config'];?>" >
<input type="hidden" id="AppKey" value="<?php echo $response['shop_info']['app_key'];?>">
<input type="hidden" id="Seller_ID" value="<?php echo $response['shop_info']['user_id'];?>" >
<script>
    $(function(){
        var status = login();
        if(status){
            DisplayDesign();
           init_print_item();
           ininPrinterList();
        }
    });
    
    var CNPrint; //声明为全局变量 
    function CreatePage() {
        CNPrint = getCaiNiaoPrint(document.getElementById('CaiNiaoPrint_OB_2'), document.getElementById('CaiNiaoPrint_EM_2'));
        CNPrint.PRINT_INITA(0, 0, 400, 800, "打印控件功能演示_CNPrint功能_在线编辑获得程序代码");
        var cp_code = $('#CP_CODE').val();
        var config =  $('#CONFIG').val();
        CNPrint.SET_PRINT_MODE("CAINIAOPRINT_MODE", "CP_CODE=" + cp_code + "&CONFIG=" + config);

    }
    ;
    function DisplayDesign() {
        CreatePage();
        CNPrint.SET_SHOW_MODE("DESIGN_IN_BROWSE", 1);
        if($('#self_body').val()!=''){
        CNPrint.ADD_PRINT_DATA("ProgramData", $('#self_body').val());
        }
     //----------替换变量--------------------------------------------------------------		
		CNPrint.SET_PRINT_CONTENT("ali_waybill_product_type","代收货款");//单据类型
		CNPrint.SET_PRINT_CONTENT("ali_waybill_short_address","021D-123-789");
		CNPrint.SET_PRINT_CONTENT("ali_waybill_package_center_name","黑龙江齐齐哈尔集散");//集散地名称
		CNPrint.SET_PRINT_CONTENT("ali_waybill_package_center_code","053277886278");//集散地条码
		CNPrint.SET_PRINT_CONTENT("ali_waybill_waybill_code","053277886278");
		CNPrint.SET_PRINT_CONTENT("ali_waybill_cod_amount","FKFS=到付;PSRQ=2015-07-10");//服务
		CNPrint.SET_PRINT_CONTENT("ali_waybill_consignee_name","齐齐哈尔沐鱼");
		CNPrint.SET_PRINT_CONTENT("ali_waybill_consignee_phone","15605883677");
		CNPrint.SET_PRINT_CONTENT("ali_waybill_consignee_address","黑龙江省齐齐哈尔市建华区文化大街42号齐齐哈尔大学计算机工程学院计算机001班");//收件人地址
		CNPrint.SET_PRINT_CONTENT("ali_waybill_send_name","浙江杭州行者");
		CNPrint.SET_PRINT_CONTENT("ali_waybill_send_phone","180000980909");
		CNPrint.SET_PRINT_CONTENT("ali_waybill_shipping_address","浙江省杭州市余杭区文一西路1001号阿里巴巴淘宝城5号小邮局");
		CNPrint.SET_PRINT_CONTENT("EWM","123456789012");
		//--------------------------------------------------------------------------
        CNPrint.PRINT_DESIGN();//打开ISV设计模式
    }
    ;
    function login() {	//登录提供appkey 和seller_id
        var AppKey = $('#AppKey').val();
        var Seller_ID = $('#Seller_ID').val();
        if (AppKey == '' || Seller_ID == '') {
            parent.BUI.Message.Alert('请把对应配送方式绑定淘宝店铺','error');
            return false;
        }

      try{

            CNPrint = getCaiNiaoPrint(document.getElementById('CaiNiaoPrint_OB_2'), document.getElementById('CaiNiaoPrint_EM_2'));
            CNPrint.SET_PRINT_IDENTITY("AppKey=" + AppKey + "&Seller_ID=" + Seller_ID);//登陆appkey、seller_id 验证
            return true;
      } catch (e) {
                    no_install();
                return false;
      }   
    }
    
    function getP() {
        $('#self_body').val(getProgram()); // 可以应用于C/S及B/S程序调用，功能类似 CNPrint.GET_VALUE("ProgramCodes",63) 
    }

   function getProgram(){
       return CNPrint.GET_VALUE("CustomProgramData", 0);
   }

    function ininPrinterList() {
        var h = "<option>无</option>";
        var c = CNPrint.GET_PRINTER_COUNT();
        for (var i = 0; i < c; i++) {
            var n = CNPrint.GET_PRINTER_NAME(i);
            h += "<option value='" + n + "'>" + n + "</option>";
        }

        $("#printer").html(h);
        $("#printer").val("<?php echo $response['record']['printer'] ?>");
    }



    //标识是否修改了信息(即是否需要离开页面时的提示)
    var g_isModified = false;


    function doPreview() {
//        LODOP.PREVIEW();
//        DisplayDesign();
    }

    function doSave() {
        var templates_val = {};
          var      template_body ={};
            if($("#detail").val()!=''){
                templates_val.detail = $("#detail").val();
                templates_val.detail_val = CNPrint.GET_VALUE ('ItemContent',$("#detail").val());
                templates_val.detail_row = ($("#detail_row").attr("checked"))?1:0;
            }

             templates_val.itemkey = $("#itemkey").val();
            
            
            template_body.cp_code = $('#CP_CODE').val();
            template_body.config = $('#CONFIG').val();
            template_body.self_body = getProgram();
            template_body.express_type = $('#express_type').val();
            template_body.ali_waybill_cp_logo_up = $('#ali_waybill_cp_logo_up').attr('checked')?0:1 ; //ali_waybill_cp_logo_down
            template_body.ali_waybill_cp_logo_down  = $('#ali_waybill_cp_logo_down').attr('checked')?0:1 ; //ali_waybill_cp_logo_down
        var params = {
            print_templates_id: <?php echo $response['record']['print_templates_id'] ?>,
            print_templates_name: $("#print_templates_name").val(),
            printer: $("#printer").val(),
            templates_val: templates_val,
            template_body: template_body
        };

        $.post('?app_act=sys/print_templates/edit_express_cainiao&app_fmt=json', params, function(data) {
            if (data.status == "1") {
                 alert('保存成功！');
            } else {
                //BUI.Message.Alert(data.message, "error");
                alert(data.message);
            }
        }, "json");
    }
 
    function doLink(recordType, recordIndex) {
        window.location = "?app_act=sys/print_templates/edit_express&express_id=<?php echo $request['_id'] ?>&record_type=" + recordType + "&record_index=" + recordIndex
    }

function init_print_item(){
           var variables = <?php echo json_encode($response['variables_all']);?>;
        $('.print-item').click(function () {
            var itemName = $(this).attr("title");
          
            if(itemName === "custom_txt") {
                CNPrint.ADD_PRINT_TEXTA(itemName+":",50, 50, 80, 50, "自定义文本");
             }
            else {
                var text = variables[itemName];
                var selecti = itemName.indexOf("detail:");
           
                if(selecti===0){
                    //var detailname = itemName.substr(7);
                    if($('#detail').val()===''){   
                        $('#detail').val(itemName);
                        CNPrint.ADD_PRINT_TEXTA(itemName,50, 50, 80, 50,text);
                    }else{      
                        var detailItem =$('#detail').val();
                        // $('#detail').val( $('#detail').val()+","+detailname);
                        var all_text = CNPrint.GET_VALUE ('ItemContent', detailItem);
                      
                        CNPrint.SET_PRINT_STYLEA(detailItem,'Content',all_text+" "+text);
                        var newdetailItem = detailItem+"|"+itemName;
                        CNPrint.SET_PRINT_STYLEA(detailItem,'ItemName',newdetailItem);
                        $('#detail').val(newdetailItem);
                    }
                }else{
                    CNPrint.ADD_PRINT_TEXTA(itemName,50, 50, 80, 20, text);
                    var itemkey_str = $('#itemkey').val();
                    if(itemkey_str==''){
                         $('#itemkey').val(itemName);
                    }else{
                        $('#itemkey').val(itemkey_str+","+itemName);
                    }
                }
            }
        });
        $('#add_detail').click(function(){
                    if($('#detail').val()===''){
                       alert('请先添加明细');
                    }else{
                           var detailItem =$('#detail').val();
                           var all_text = CNPrint.GET_VALUE('ItemContent', detailItem);
                            CNPrint.ADD_PRINT_TEXTA(detailItem, 50, 50, 150, 50, all_text);
                    }
        });
    
}
  function removePrintVar() {
        CNPrint.SET_PRINT_STYLEA('Selected', 'Deleted', true);
        if($('#detail').val()!=''&&CNPrint.GET_VALUE('ItemIsDeleted',$('#detail').val())){
            $('#detail').val('');
        }
    }
        function doPreview(){
                
       
		CNPrint.SET_PRINT_STYLEA("ali_waybill_product_type","CONTENT","代收货款");//单据类型
		CNPrint.SET_PRINT_STYLEA("ali_waybill_short_address","CONTENT","EYB大头笔长8字");//大头笔
		CNPrint.SET_PRINT_STYLEA("ali_waybill_package_center_name","CONTENT","EYB集散地无限制");//集散地名称
		CNPrint.SET_PRINT_STYLEA("ali_waybill_package_center_code","CONTENT","053277886278");//集散地条码
		CNPrint.SET_PRINT_STYLEA("ali_waybill_waybill_code","CONTENT","1234555");
		CNPrint.SET_PRINT_STYLEA("ali_waybill_cod_amount","CONTENT","FKFS=到付;PSRQ=2015-07-10");//服务
		CNPrint.SET_PRINT_STYLEA("ali_waybill_consignee_name","CONTENT","齐齐哈尔沐鱼");
		CNPrint.SET_PRINT_STYLEA("ali_waybill_consignee_phone","CONTENT","15605883677");
		CNPrint.SET_PRINT_STYLEA("ali_waybill_consignee_address","CONTENT","黑龙江省齐齐哈尔市建华区文化大街42号齐齐哈尔大学计算机工程学院计算机001班");//收件人地址
		CNPrint.SET_PRINT_STYLEA("ali_waybill_send_name","CONTENT","浙江杭州行者");
		CNPrint.SET_PRINT_STYLEA("ali_waybill_send_phone","CONTENT","180000980909");
		CNPrint.SET_PRINT_STYLEA("ali_waybill_shipping_address","CONTENT","浙江省杭州市余杭区文一西路1001号阿里巴巴淘宝城5号小邮局");
		CNPrint.SET_PRINT_STYLEA("EWM","CONTENT","123456789012");
		CNPrint.SET_PRINT_STYLEA("dabiao","CONTENT","");
		CNPrint.SET_PRINT_STYLEA("ali_waybill_cp_logo_up","PreviewOnly",0);
		CNPrint.SET_PRINT_STYLEA("ali_waybill_cp_logo_down","PreviewOnly",0);
		CNPrint.PREVIEW();
    }
    function no_install(){
        var str_html = "<font color='#FF00FF'>打印组件未安装!<a href='http://www.taobao.com/market/cainiao/eleprint.php' target='_blank'>请点击这里</a>";
        parent.BUI.Message.Alert(str_html,'error');
       
    }
</script>
