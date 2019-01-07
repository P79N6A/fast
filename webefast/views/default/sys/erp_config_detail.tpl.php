<?php require_lib('util/oms_util', true);?>
<script src="assets/js/jquery.formautofill2.min.js"></script>
<div id="form1_data_source" style="display:none;"><?php if(isset($response['form1_data_source'])){ echo $response['form1_data_source']; }?></div> 
<form  id="form1" action="?app_act=sys/erp_config/do_<?php echo $response['app_scene']?>&app_fmt=json" method="post">

<table id="form_tbl" border="1px" bordercolor="#dddddd">
<tr style="background-color:#f5f5f5;">
<td class="tdlabel">&nbsp;&nbsp;基本信息</td>
<td colspan="3">
</td>
</tr>

<tr>
<td class="tdlabel" width="300px" style="text-align:right;">ERP配置名称&nbsp;&nbsp;</td>
<td width="700px">
<input type="hidden" id="erp_config_id" name="erp_config_id" value=""/>
<input type="text" value="" class="input-normal bui-form-field" id="erp_config_name" name="erp_config_name" data-rules="{required: true}"/>
</td>
</tr>
<tr>
<td class="tdlabel" width="300px" style="text-align:right;">ERP应用上线日期&nbsp;&nbsp;</td>
<td width="700px">
<?php if ($response['app_scene'] == 'add'){?>
<input id="online_time"  type="text" value="<?php echo date('Y-m-d');?>" name="online_time" data-rules="{required : true}" class="calendar">
<?php } else { ?>
<input id="online_time"  type="text"  name="online_time" data-rules="{required : true}" class="calendar">
<?php }  ?>
<span style="color:red;">设置后，只有上线日期（包含当天）发货或收货的单据才会被上传</span>
</td>
</tr>
</table>
<br/>
<table id="form_tbl" border="1px"  bordercolor="#dddddd">
<tr style="background-color:#f5f5f5;">
<td class="tdlabel">&nbsp;&nbsp;参数配置</td>
<td colspan="3">
</td>
</tr>

<tr>
<td class="tdlabel" width="300px" style="text-align:right;">对接ERP系统&nbsp;&nbsp;</td>
<td width="700px">
<!-- <input type="radio" value="0" class="bui-form-field"  name="erp_system" checked   />BSERP2-->
<input type="radio" <?php if($response['app_scene'] === 'edit'){ ?> disabled="disabled" <?php }?> onchange="deliver('<?php echo $response['app_scene']?>')" value="1" class="bui-form-field"  name="erp_system"  <?php if ($response['info']['erp_system']== 1) {?> checked <?php }?> />BS3000J
<input type="radio" <?php if($response['app_scene'] === 'edit'){ ?> disabled="disabled" <?php }?> onchange="deliver('<?php echo $response['app_scene']?>')" value="0" class="bui-form-field"  name="erp_system"  <?php if ($response['info']['erp_system']== 0) {?> checked <?php }?> />BSERP2
<input type="radio" <?php if($response['app_scene'] === 'edit'){ ?> disabled="disabled" <?php }?> onchange="deliver('<?php echo $response['app_scene']?>')" value="2" class="bui-form-field"  name="erp_system"  <?php if ($response['info']['erp_system']== 2) {?> checked <?php }?> />BSERP3
<div id="3000j_version" <?php if ($response['info']['erp_system']== 0 || $response['info']['erp_system']== 2) {?> style="display:none;" <?php }?> >（版本最低要求：BS3000+V3.0.1-R[20140926]）</div>
<div id="erp_version" <?php if ($response['info']['erp_system']== 1) {?> style="display:none;" <?php }?> >（版本最低要求：BSERP2V4.0.2-R[20150430] ）</div>
</td>
</tr>


    <tr class="erp_params">
        <td class="tdlabel" width="300px" style="text-align:right;">对接方式&nbsp;&nbsp;</td>
        <td width="700px">
            <div class="button-group" id="erp_type_view"></div>
            <input type="hidden" id="erp_type" name="erp_type" value=""/>
        </td>
    </tr>

<tr>
<td class="tdlabel" width="300px" style="text-align:right;">ERP地址&nbsp;&nbsp;</td>
<td width="700px">
<input type="text" value="<?php echo $response['erp_params']['api_url'];?>"  class="input-normal bui-form-field" id="erp_address" name="erp_address" data-rules="{required: true}"/>
<input type="button" value="测试" onclick="test();" />
</td>
</tr>

    <tr style="display: none" class="erp_key_view">
        <td class="tdlabel" width="300px" style="text-align:right;">ERP密钥&nbsp;&nbsp;</td>
        <td width="700px">
            <input type="text" value="<?php echo $response['erp_params']['api_key']; ?>" class="input-normal bui-form-field" id="erp_key" name="erp_key"/>
            <span class="valid-text" id="erp_key_error"></span>
        </td>
    </tr>

    <tr class="qm_erp_params" style="display: none">
        <td class="tdlabel" width="300px" style="text-align:right;">目标AppKey&nbsp;&nbsp;</td>
        <td width="700px">
            <input type="text" value="<?php echo $response['info']['target_key']; ?>" class="input-normal bui-form-field" id="target_key" name="target_key"/>
            <span class="valid-text" id="rem_error"></span>
        </td>
    </tr>
    <tr class="qm_erp_params" style="display: none">
        <td class="tdlabel" width="300px" style="text-align:right;">Customer ID&nbsp;&nbsp;</td>
        <td width="700px">
            <input type="text" value="<?php echo $response['info']['customer_id']; ?>" class="input-normal bui-form-field" id="customer_id" name="customer_id"/>
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
<?php if ($response['app_scene'] == 'add'){?>
<table id="shop" border="1px" bordercolor="#dddddd">
<tr style="background-color:#f5f5f5;">
<td class="tdlabel">&nbsp;&nbsp;系统店铺列表</td>
<td colspan="3">&nbsp;&nbsp;外部ERP的店铺代码
<p class="add_btn" onclick = "add('shop')"> <img src="assets/images/plus.png" />添加</p>
</td>
 
</tr>

<tr>
<td class="tdlabel" width="300px">&nbsp;&nbsp;<select name="shop[0][shop_store_code]" style="width:200px;" >
<option value="">请选择</option>
 <?php $list = $response['shop'] ; 
	foreach($list as $k=>$v){ ?>
		<option value="<?php echo $v['shop_code']?>"><?php echo $v['shop_name']?></option>
<?php } ?>
</select></td>
<td width="700px">
&nbsp;&nbsp;
<input type="text" value="" class="input-normal bui-form-field"  name="shop[0][outside_code]" />
<span style="color:red;">请填写ERP门店代码，建议为总部直属门店</span>
</td>
</tr>

<input type='hidden' value="<?php echo $response['app_scene']?>" id="app_scene">
</table>
<?php }?>
<?php if ($response['app_scene'] == 'edit'){?>

<table id="shop" border="1px" bordercolor="#dddddd">
<tr style="background-color:#f5f5f5;">
<td class="tdlabel">&nbsp;&nbsp;系统店铺列表</td>
<td colspan="3">&nbsp;&nbsp;外部ERP的店铺代码
<p class="add_btn" onclick = "add('shop')"> <img src="assets/images/plus.png" />添加</p>
</td>
</tr>
<?php 

foreach($response['erp_shop'] as $key=>$value){ 
	$shop_select = '<option value="">请选择</option>';
	foreach($response['shop'] as  $k => $v){
		if($v['shop_code']==$value['shop_store_code']){
			$shop_select.='<option value="'.$v['shop_code'].'" selected="selected" >'.$v['shop_name'].'</option>';
		}else{
			$shop_select.='<option value="'.$v['shop_code'].'">'.$v['shop_name'].'</option>';
		}
	}
	
echo '<tr>
<td class="tdlabel" width="300px" ;">&nbsp;&nbsp;<select  name="shop['.$key.'][shop_store_code]" style="width:200px;">
'.$shop_select.'
</select></td>
<td width="700px">
&nbsp;&nbsp;
<input type="text" value="'.$value["outside_code"].'" class="input-normal bui-form-field"  name="shop['.$key.'][outside_code]"  param="check" />
<span style="color:red;">请填写ERP门店代码，建议为总部直属门店</span>
<p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p>
</td>
</tr>';	
	
	
}

?>
</table>
<?php }?>
<br/>
<?php if ($response['app_scene'] == 'add'){?>
<table id="store" border="1px" bordercolor="#dddddd">
<tr style="background-color:#f5f5f5;">
<td class="tdlabel">&nbsp;&nbsp;系统仓库列表</td>
<td>&nbsp;&nbsp;外部ERP的仓库代码</td>
<td>更新库存
    <p class="add_btn" id="storep" onclick = "add('store')"> <img src="assets/images/plus.png" />添加</p>
</td>
</tr>

<tr>
<td class="tdlabel" width="300px" >&nbsp;&nbsp;<select  name="store[0][shop_store_code]"  data-rules="{required: true}" style="width:200px;" >
 	<option value="">请选择</option>
    <?php $list = $response['store'] ; 
	foreach($list as $k=>$v){ ?>
    <option value="<?php echo $v['store_code']?>"><?php echo $v['store_name']?></option>
    <?php } ?>
</select></td>
<td width="430px" >
&nbsp;&nbsp;
<input type="text" value="" class="input-normal bui-form-field"  name="store[0][outside_code]" data-rules="{required: true}"/>
<span style="color:red;" id="store_spe_red">请填写ERP仓库代码，建议为总部直属总仓</span></td>
<td width="270px">
    <input class="bui-form-field"  value="1" checked type="checkbox" name="store[0][update_stock]" />
</td>
</tr>

</table>
<?php }?>
<?php if ($response['app_scene'] == 'edit'){?>
<table id="store" border="1px" bordercolor="#dddddd">
<tr style="background-color:#f5f5f5;">
<td class="tdlabel">&nbsp;&nbsp;系统仓库列表</td>

<?php 
if($response['info']['erp_system'] == 2){
?>  
    <td>&nbsp;&nbsp;外部ERP的仓库发布规则代码/仓库代码</td>
    <td width='110px'>更新库存
    </td>
    <td width='160px'>门店发货
        <p class="add_btn" id="storep" onclick = "add('store')"> <img src="assets/images/plus.png" />添加</p></td>
    </tr>
<?php
} else {
?>
    <td>&nbsp;&nbsp;外部ERP的仓库代码</td>
    <td width='270px'>更新库存
        <p class="add_btn" id="storep" onclick = "add('store')"> <img src="assets/images/plus.png" />添加</p></td>
    </td>
<?php
}
?>
<?php 

foreach($response['erp_store'] as  $key => $value){
	
$list =   $response['store'] ; 
$store_select = '<option value="">请选择</option>';
foreach($list as $k=>$v){ 
     if($v['store_code']==$value['shop_store_code']){
            $store_select.='<option value="'.$v['store_code'].'" selected="selected" >'.$v['store_name'].'</option>';
     }else{
          $store_select.='<option value="'.$v['store_code'].'">'.$v['store_name'].'</option>';
     }
	
}
$checked = $value["update_stock"] == 1?'checked':'';	
$o2o_checked = $value['o2o_store'] == 1?'checked':'';
echo '<tr>
<td class="tdlabel" width="300px" ;">&nbsp;&nbsp;<select  name="store['.$key.'][shop_store_code]" style="width:200px;">
'.$store_select.'
</select></td>';

if($response['info']['erp_system']== 2){
echo '<td width="430px" >&nbsp;&nbsp;
        <input type="text" value='.$value["outside_code"].' class="input-normal bui-form-field"  name="store['.$key.'][outside_code]"  param="check"  data-rules="{required: true}"/>
	<span style="color:red;">请填写ERP仓库发布规则代码/仓库代码</span></td><td width="110px">
        <input class="bui-form-field" value="1" '.$checked.' type="checkbox" name="store['.$key.'][update_stock]" />
    </td>
    <td width="150px">
        <input class="bui-form-field" value="1" '.$o2o_checked.' type="checkbox" name="store['.$key.'][o2o_store]" />
        <p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p>
    </td>';
}else{
    echo '<td width="430px" >&nbsp;&nbsp;
        <input type="text" value='.$value["outside_code"].' class="input-normal bui-form-field"  name="store['.$key.'][outside_code]"  param="check"  data-rules="{required: true}"/>
        <span style="color:red;">请填写ERP仓库代码，建议为总部直属总仓</span></td>
        <td width="270px">
        <input class="bui-form-field" value="1" '.$checked.' type="checkbox" name="store['.$key.'][update_stock]" />
        <p class="minus_btn" onclick="del(this);" id="stoer_editp" ><img src="assets/images/minus.png">删除</p>
    </td>';
}
echo '</tr>';	
	
}

?>
</table>
<?php }?>
<br/>
<?php if ($response['app_scene'] == 'add'){?>
<table id="fx" border="1px" bordercolor="#dddddd">
    <tr style="background-color:#f5f5f5;">
        <td class="tdlabel">&nbsp;&nbsp;系统分销商列表</td>
        <td colspan="3">&nbsp;&nbsp;外部ERP的客户代码
            <p class="add_btn" onclick = "add('fx')"> <img src="assets/images/plus.png" />添加</p>
        </td>
    </tr>
    <tr>
        <td class="tdlabel" width="300px">&nbsp;&nbsp;
            <select name="fx[0][custom_code]" style="width:200px;" >
                <option value="">请选择</option>
                    <?php $list = $response['fx'] ; 
                        foreach($list as $k=>$v){ ?>
                            <option value="<?php echo $v['custom_code']?>"><?php echo $v['custom_name']?></option>
                    <?php } ?>
            </select>
        </td>
        <td width="700px">&nbsp;&nbsp;
            <input type="text" value="" class="input-normal bui-form-field"  name="fx[0][outside_code]"/>
            <span style="color:red;" class="fx">请填写BSERP2客户代码</span>
        </td>
    </tr>
    <input type='hidden' value="<?php echo $response['app_scene']?>" id="app_scene">
</table>
<?php }?>

<?php if ($response['app_scene'] == 'edit'){?>
<table id="fx" border="1px" bordercolor="#dddddd">
    <tr style="background-color:#f5f5f5;">
        <td class="tdlabel">&nbsp;&nbsp;系统分销商列表</td>
        <td colspan="3">&nbsp;&nbsp;外部ERP的客户代码
            <p class="add_btn" onclick = "add('fx')"> <img src="assets/images/plus.png" />添加</p>
        </td>
    </tr>
<?php 
foreach($response['erp_fx'] as $key => $value){ 
    $outside_code = !empty($value['outside_code']) ? $value['outside_code'] : '';
	$fx_select = '<option value="">请选择</option>';
	foreach($response['fx'] as  $k => $v){
		if($v['custom_code']==$value['custom_code']){
			$fx_select.='<option value="'.$v['custom_code'].'" selected="selected" >'.$v['custom_name'].'</option>';
		}else{
			$fx_select.='<option value="'.$v['custom_code'].'">'.$v['custom_name'].'</option>';
		}
	}
        if ($response['info']['erp_system'] == 1) {
            $name = '请填写BS3000J客户代码';
        } else if ($response['info']['erp_system'] == 2) {
            $name = '请填写BSERP3客户代码';
        } else {
            $name = '请填写BSERP2客户代码';
        }
        echo '<tr>
            <td class="tdlabel" width="300px" ;">&nbsp;&nbsp;
                <input type="hidden" value="' . $value['api_fx_id'] . '" name="fx[' . $key . '][api_fx_id]" id="api_fx_id_' . $key . '">
                <select  name="fx['.$key.'][custom_code]" style="width:200px;">'.$fx_select.'</select>
            </td>
            <td width="700px">&nbsp;&nbsp;
                <input type="text" value="' . $outside_code . '" class="input-normal bui-form-field"  name="fx['.$key.'][outside_code]"  param="check" />
                <span style="color:red;" class="fx">&nbsp;&nbsp;' . $name . '</span>
                <p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p>
            </td>
        </tr>';	
    }

?>
</table>
<?php }?>
<br/>
<table id="form_tbl" border="1px" bordercolor="#dddddd">
<tr style="background-color:#f5f5f5;">
<td class="tdlabel">&nbsp;&nbsp;自动服务设置</td>
<td colspan="3">
</td>
</tr>

<tr>
<td class="tdlabel" width="300px" style="text-align:right;">档案获取&nbsp;&nbsp;</td>
<td width="700px">
<?php if ($response['app_scene'] == 'add' || $response['info']['item_infos_download'] ==1 ){?>
<input type="checkbox" class="bui-form-field"  value="1" id="item_infos_download" name="item_infos_download" checked/>（支持商品基本信息、商品颜色、商品尺码、大类等下载，不支持条码自动生成）
<?php } else {?>
<input type="checkbox" class="bui-form-field"  value="1" id="item_infos_download" name="item_infos_download" />（支持商品基本信息、商品颜色、商品尺码、大类等下载，不支持条码自动生成）
<?php }?>
</td>
</tr>
<tr>
<td class="tdlabel" width="300px" style="text-align:right;">库存拉取并覆盖系统库存&nbsp;&nbsp;</td>
<td width="700px">
<?php if ($response['app_scene'] == 'add' || $response['info']['manage_stock'] ==1 ){?>
<input type="checkbox"  value="1"  class="bui-form-field" id="manage_stock"  name="manage_stock" checked />（ 支持商品库存获取，并覆盖系统库存，每次为全量商品库存获取）
<?php } else {?>
<input type="checkbox"  value="1"  class="bui-form-field" id="manage_stock"  name="manage_stock"  />（ 支持商品库存获取，并覆盖系统库存，每次为全量商品库存获取）
<?php }?>
</td>
</tr>
<tr>
<td class="tdlabel" width="300px" style="text-align:right;">单据同步&nbsp;&nbsp;</td>
<td width="700px">
<?php if ($response['app_scene'] == 'add' || $response['info']['trade_sync'] ==1 ){?>
<input type="checkbox" value="1" class="bui-form-field" id="trade_sync"  name="trade_sync"  checked/>（ 支持网络订单和售后服务单同步到ERP中）
<?php } else {?>
<input type="checkbox" value="1"  class="bui-form-field" id="trade_sync"  name="trade_sync" />（ 支持网络订单和售后服务单同步到ERP中）
<?php }?>
</td>
</tr>

</table>

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
    var app_scene = '<?php echo $response['app_scene'] ?>';
    var erp_type = <?php echo $response['erp_type'] ?>;
    set_erp_type(erp_type);
    BUI.use('bui/toolbar', function (Toolbar) {
        //先扫后称模式
       // erp_type = 1;
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
        if (erp_type == 1) {
            $(".qm_erp_params").show();
            $(".erp_key_view").hide();
            // $(".qm_erp_params").remove();
            // $('.qm_erp_params').find('input').attr('disabled',false);
        } else {
            $(".qm_erp_params").hide();
            $(".erp_key_view").show();
          //  $(".qm_erp_params").remove();
           // $(".qm_erp_params").empty();
            //$('.qm_erp_params').find('input').attr('disabled', 'disabled');
            //$('.qm_erp_params').find('input').val(1);
        }
    }


function deliver(app_scene) {
    if(app_scene == 'add') {
        switch($("input[name=erp_system]:checked").attr('value')){
            case '0':
                $('#store tr:not(:lt(2))').remove();
                $('#storep').attr('oncilck');
                $('#storep').html('<img src="assets/images/plus.png" />添加');
                $("#store_tlast").remove();
                $("#store_last").remove();
                $("#store tr:eq(1) td:eq(2)").attr('width','270px');
                $('#store tr:eq(0) td:eq(1)').html('外部ERP的仓库代码');
                $('#store_spe_red').html('请填写ERP仓库代码，建议为总部直属总仓');
                $('.fx').html('请填写BSERP2客户代码');
            break;
            case '1':
                $('#store tr:not(:lt(2))').remove();
                $('#storep').attr('oncilck');
                $('#storep').html('<img src="assets/images/plus.png" />添加');
                $("#store_tlast").remove();
                $("#store_last").remove();
                $("#store tr:eq(1) td:eq(2)").attr('width','270px');
                $('#store tr:eq(0) td:eq(1)').html('外部ERP的仓库代码');
                $('#store_spe_red').html('请填写ERP仓库代码，建议为总部直属总仓');
                $('.fx').html('请填写BS3000J客户代码');
            break;
            case '2':
                $('#store tr:not(:lt(2))').remove();
                $('#storep').removeAttr("oncilck");
                $('#storep').html("");
                $("#store tr:eq(0)").append("<td id='store_last'>门店发货<p class='add_btn' id='storep' onclick = "+'add("store")'+"> <img src='assets/images/plus.png' />添加</p></td>");
                $('#store tr:eq(1)').append("<td width='150px' id='store_tlast'><input class='bui-form-field'  value='1' checked type='checkbox' name='store[0][o2o_store]' /></td>");

                $("#store tr:eq(1) td:eq(2)").attr('width','110px');
                $('#store tr:eq(0) td:eq(1)').html('外部ERP的仓库发布规则代码/仓库代码');
                $('#store_spe_red').html('请填写ERP仓库发布规则代码/仓库代码');
                $('.fx').html('请填写BSERP3客户代码');
            break;
        }
    }
}

BUI.use('bui/calendar',function(Calendar){
    var datepicker = new Calendar.DatePicker({
        trigger:'.calendar',
        showTime:true,
        autoRender : true
    });
});
var form1_data_source_v = $("#form1_data_source").html();
if (form1_data_source_v!=''){
	$('form#form1').autofill(eval("("+form1_data_source_v+")"));
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
        var error_img = "<span class='estate error'><span class='x-icon x-icon-mini x-icon-error'>!</span><em>不能为空！</em></span>";
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
            $("#customer_error").html('');
        }
        if ($("#erp_key").val() == '' && erp_type == 0) {
            $("#erp_key_error").html(error_img);
            return false;
        } else {
            $("#erp_key_error").html('');
        }

        return true; // 如果不想让表单继续提交，则return false
    });
    
 //增加
 <?php  
//	 $list =   $response['store'] ;
//	 $store_select = '<option value="">请选择</option>';
//	 foreach($list as $k=>$v){
//	 	$store_select.='<option value="'.$v['store_code'].'">'.$v['store_name'].'</option>';
//	 }
//	 $list =   $response['shop'] ;
//	 $shop_select = '<option value="">请选择</option>';
//	 foreach($list as $k=>$v){
//	 	$shop_select.='<option value="'.$v['shop_code'].'">'.$v['shop_name'].'</option>';
//	 }
//	 $fx_list =   $response['fx'] ;
//	 $fx_select = '<option value="">请选择</option>';
//	 foreach($fx_list as $k=>$v){
//	 	$fx_select.='<option value="'.$v['custom_code'].'">'.$v['custom_name'].'</option>';
//	 }
?>

// var store_select =  '<?php //echo  $store_select?>//';
// var shop_select =  '<?php //echo  $shop_select?>//';
// var fx_select =  '<?php //echo  $fx_select?>//';

var erp_store_select =  <?php echo $response['store_select']?>;
var store_select = '<option value="">请选择</option>';
$.each(erp_store_select, function (i, v) {
    store_select += '<option value="' + v.store_code + '">' + v.store_name + '</option>';
});

var erp_shop_select =  <?php echo $response['shop_select']?>;
var shop_select = '<option value="">请选择</option>';
$.each(erp_shop_select, function (i, v) {
    shop_select += '<option value="' + v.shop_code + '">' + v.shop_name + '</option>';
});

var erp_fx_select =  <?php echo $response['fx_select']?>;
var fx_select = '<option value="">请选择</option>';
$.each(erp_fx_select, function (i, v) {
    fx_select += '<option value="' + v.custom_code + '">' + v.custom_name + '</option>';
});

 function add(type){
     var erp_system = $("input[name=erp_system]:checked").attr('value');
	if(type == 'shop'){
            var i = $("#shop").find("tr").length - 1;
            $("#shop").append('<tr><td class="tdlabel" width="300px" ;">&nbsp;&nbsp;<select  name="shop['+i+'][shop_store_code]" style="width:200px;">'+

            shop_select+'</select></td><td width="700px">&nbsp;&nbsp;'+
            '<input type="text" value="" class="input-normal bui-form-field"  name="shop['+i+'][outside_code]"/><span style="color:red;">请填写ERP门店代码，建议为总部直属门店</span><p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p></td></tr>');            
        }	

	if(type == 'store'){
            var i = $("#store").find("tr").length - 1;
            
            if(erp_system == 2) {
		$("#store").append('<tr><td class="tdlabel" width="300px" ;">&nbsp;&nbsp;<select  name="store['+i+'][shop_store_code]" style="width:200px;">'+
		store_select+'</select></td><td width="430px">&nbsp;&nbsp;'+
		'<input type="text" value="" class="input-normal bui-form-field" name="store['+i+'][outside_code]" data-rules="{required: true}"/><span style="color:red;">请填写ERP仓库发布规则代码/仓库代码</span></td>'+
		'<td width="110px"><input class="bui-form-field"  checked value="1" name="store['+i+'][update_stock]" type="checkbox"  /></td><td><input class="bui-form-field"  value="1" checked type="checkbox" name="store['+i+'][o2o_store]" /><p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p></td></tr>');
            } else {
		$("#store").append('<tr><td class="tdlabel" width="300px" ;">&nbsp;&nbsp;<select  name="store['+i+'][shop_store_code]" style="width:200px;">'+
		store_select+'</select></td><td width="430px">&nbsp;&nbsp;'+
		'<input type="text" value="" class="input-normal bui-form-field" name="store['+i+'][outside_code]" data-rules="{required: true}"/><span style="color:red;">请填写ERP仓库代码，建议为总部直属总仓</span></td>'+
		'<td><input class="bui-form-field"  checked value="1" name="store['+i+'][update_stock]" type="checkbox"  /><p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p></td></tr>');
            }
        }
    if(type == 'fx') {
        var i = $("#fx").find("tr").length - 1;
        if(erp_system == 1) {
            $("#fx").append('<tr><td class="tdlabel" width="300px" ;">&nbsp;&nbsp;<select  name="fx['+i+'][custom_code]" style="width:200px;">'+
            fx_select+'</select></td><td width="700px">&nbsp;&nbsp;'+
            '<input type="text"  class="input-normal bui-form-field"  name="fx['+i+'][outside_code]"/><span style="color:red;" class="fx">&nbsp;&nbsp;请填写BS300J客户代码</span><p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p></td></tr>');            
        }else if(erp_system == 2){
            $("#fx").append('<tr><td class="tdlabel" width="300px" ;">&nbsp;&nbsp;<select  name="fx['+i+'][custom_code]" style="width:200px;">'+
            fx_select+'</select></td><td width="700px">&nbsp;&nbsp;'+
            '<input type="text"  class="input-normal bui-form-field"  name="fx['+i+'][outside_code]" /><span style="color:red;" class="fx">&nbsp;&nbsp;请填写BSERP3客户代码</span><p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p></td></tr>');  
        }else{
            $("#fx").append('<tr><td class="tdlabel" width="300px" ;">&nbsp;&nbsp;<select  name="fx['+i+'][custom_code]" style="width:200px;">'+
            fx_select+'</select></td><td width="700px">&nbsp;&nbsp;'+
            '<input type="text"  class="input-normal bui-form-field"  name="fx['+i+'][outside_code]"/><span style="color:red;" class="fx">&nbsp;&nbsp;请填写BSERP2客户代码</span><p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p></td></tr>');
        }
    }
	 
 }
 function del(item){
	 $(item).parent("td").parent("tr").remove();
}
 function test(){
	 var api_url = $("#erp_address").val();
	 var api_key = $("#erp_key").val();
	 var erp_system = $("input:radio:checked[name='erp_system']").val();
	$.ajax({ type: 'POST', dataType: 'json',
	    url: '<?php echo get_app_url('sys/erp_config/test');?>',
    data: {erp_address: api_url, erp_key: api_key,erp_system:erp_system},
    success: function(ret) {
    	var type = ret.status == 1 ? 'success' : 'error';
    	if (type == 'success') {
        	BUI.Message.Alert(ret.message, type);
    	} else {
       		BUI.Message.Alert(ret.message, type);
    	}
    }
	});
}



$(document).ready(function(){
	$("input:radio[name='erp_system']").change(function(){
		var erp_sys = $("input:radio:checked[name='erp_system']").val();
		if (erp_sys == 1){
			//3000+
			$("#3000j_version").css('display','');
			$("#erp_version").css('display','none');
		} else{
			$("#3000j_version").css('display','none');
			$("#erp_version").css('display','');
		} 
	});
});
</script>
<?php echo load_js('comm_util.js')?>
