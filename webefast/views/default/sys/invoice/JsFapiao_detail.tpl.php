<style>
    td {
        line-height: 20px;
        padding: 4px;
        text-align: left;
        vertical-align: top;
    }

    .form_tbl,.form_tbl tr,.form_tbl td{border: 1px solid #dddddd;}

    .add_btn {float:right}
    .minus_btn{ display:inline-block; float:right; cursor:pointer; margin:0;}
    .add_btn img,
    .minus_btn img{ vertical-align:text-bottom; margin-right:5px;}
</style>

<?php require_lib('util/oms_util', true); ?>
<script src="assets/js/jquery.formautofill2.min.js"></script>
<?php
$shop_arr = $response['shop_tb'];
$sh_select = '<option value="">请选择</option>';
foreach ($shop_arr as $key => $val) {
    if($val['shop_code']==$response['gk_dp']){
        $sh_select.='<option value="' . $val['shop_code'] . '" selected="selected" >' . $val['shop_name'] . '</option>';
    }else{
        $sh_select.='<option value="' . $val['shop_code'] . '">' . $val['shop_name'] . '</option>';
    }
}

?>

<div id="form1_data_source" style="display:none;"><?php
    if (isset($response['form1_data_source'])) {
        echo $response['form1_data_source'];
    }
    ?></div>
<form  id="form1" action="?app_act=sys/invoice/JsFapiao/do_<?php echo $response['app_scene'] ?>&app_fmt=json" method="post">
    <table class="form_tbl">
        <tr style="background-color:#f5f5f5;">
            <td class="tdlabel">&nbsp;&nbsp;基本信息</td>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td class="tdlabel" width="220px" style="text-align:right;">配置名称</td>
            <td width="500px"><input type="hidden" id="invoice_id" name="invoice_id" value="<?php echo $response['invoice_id'];?>"/>
                <input type="text" value="<?php echo $response['invoice_config_name'];?>" class="input-normal bui-form-field" id="invoice_config_name" name="invoice_config_name" param="check" data-rules="{required: true}"/>
            </td>
        </tr>
    </table>
    <br/>

    <table class="form_tbl" id="tb">
        <tr style="background-color:#f5f5f5;">
            <td class="tdlabel" width="100px" >参数配置</td>
            <td ></td>
         
        </tr>
        <tr id="pzlx">        
            <td class="tdlabel" width="220px" style="text-align:right;">配置类型</td>
            <td width="500px" >
                <select class="tdlabel" name="config_type" data-rules="{required: true}" id="type_config">
                    <option value="">请选择</option>
                     <option value="1" <?php if($response['config_type']==1) echo 'selected="selected"'?>>阿里</option>
                      <option value="2" <?php if($response['config_type']==2) echo 'selected="selected"'?>>航信</option>
                </select>
            </td>
        </tr>
        <tr id="guakao" style="display: none">
            <td class="tdlabel" width="220px" style="text-align:right;" >挂靠店铺</td>
            <td width="500px"><select name="gk_dp" id="gk_dp" ><?php echo $sh_select;?></select>
            </td>
        </tr>

        <tr>
          
            <td class="tdlabel" width="220px" style="text-align:right;" >企业识别号</td>
            <td width="500px">
                    <input type="text" name="nsrsbh" value="<?php echo $response['nsrsbh'];?>"  data-rules="{required: true}"/>
            </td>
        </tr>
        <tr>
           
            <td class="tdlabel" width="220px"  style="text-align:right;">企业名称</td>
            <td width="500px">
                <input type="text" name="nsrmc" value="<?php echo $response['nsrmc'];?>" data-rules="{required: true}"/>
            </td>
        </tr>
        <tr id="goods_upload">
            <td class="tdlabel" width="220px" style="text-align:right;">代开标志</td>
            <td width="500px">
                <select name="dkbz">
                    <option value="0" <?php if($response['dkbz']==0) echo 'selected="selected"'?>>自开(0)</option>
                    <option value="1" <?php if($response['dkbz']==1) echo 'selected="selected"'?>>代开(1)</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="tdlabel" width="220px" style="text-align:right;">销货方地址</td>
            <td width="500px">
                <input type="text" name="xhf_dz" value="<?php echo $response['xhf_dz'];?>" data-rules="{required: true}"/>
            </td>
        </tr>
        <tr>
            <td class="tdlabel" width="220px" style="text-align:right;">销货方电话</td>
            <td width="500px">
                <input type="text" name="xhf_dh" value="<?php echo $response['xhf_dh'];?>" data-rules="{required: true}"/>
            </td>
        </tr>
        <tr id="goods_upload">
            <td class="tdlabel" width="220px" style="text-align:right;">含税价标志</td>
            <td width="500px">
                <select name="hsj_bz">
                    <option value="0" <?php if($response['hsj_bz']==0) echo 'selected="selected"';?>>不含税价</option>
                    <option value="1" <?php if($response['hsj_bz']==1) echo 'selected="selected"';?>>含税价</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="tdlabel" width="220px" style="text-align:right;">平台编码</td>
            <td width="500px">
                <input type="text" name="username" value="<?php echo $response['username'];?>" data-rules="{required: true}"/>
            </td>
        </tr>
         <tr>
            <td class="tdlabel" width="220px" style="text-align:right;">密码</td>
            <td width="500px">
                <input type="text" name="password" value="<?php echo $response['password'];?>" data-rules="{required: true}"/>
            </td>
        </tr>
         <tr>
            <td class="tdlabel" width="220px" style="text-align:right;">纳税人授权码</td>
            <td width="500px">
                <input type="text" name="authorizationcode" value="<?php echo $response['authorizationcode'];?>" data-rules="{required: true}"/>
            </td>
        </tr>
         <tr>
            <td class="tdlabel" width="220px" style="text-align:right;">开票员</td>
            <td width="500px">
                <input type="text" name="kpy" value="<?php echo $response['kpy'];?>" data-rules="{required: true}"/>
            </td>
        </tr>
        <tr>
            <td class="tdlabel" width="720px" style="text-align:center;" colspan="2">是否所有商品税率相同？&nbsp;&nbsp;
                <input type="radio" id="yes" name="is_same_tax" value="1" <?php if($response['is_same_tax']==1) echo 'checked="checked"';?>/>是
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="radio" id="no" name="is_same_tax" value="0" <?php if($response['is_same_tax']==0) echo 'checked="checked"';?>/>否
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;税率
                <select style="width: 100px;" name="tax_rate">
                    <option value="0" <?php if($response['tax_rate']==0) echo 'selected="selected"';?>>0</option>
                    <option value="3" <?php if($response['tax_rate']==3) echo 'selected="selected"';?>>3</option>
                    <option value="4" <?php if($response['tax_rate']==4) echo 'selected="selected"';?>>4</option>
                    <option value="5" <?php if($response['tax_rate']==5) echo 'selected="selected"';?>>5</option>
                    <option value="6" <?php if($response['tax_rate']==6) echo 'selected="selected"';?>>6</option>
                    <option value="11" <?php if($response['tax_rate']==11) echo 'selected="selected"';?>>11</option>
                    <option value="13" <?php if($response['tax_rate']==13) echo 'selected="selected"';?>>13</option>
                    <option value="17" <?php if($response['tax_rate']==17) echo 'selected="selected"';?>>17</option>
                </select>
            </td>  
        </tr>
        <tr>
            <td class="tdlabel" width="220px" style="text-align:right;">销方开户行：</td>
            <td><input type="text" name="xhf_yhmc" value="<?php echo $response['xhf_yhmc'];?>" data-rules="{required: true}" /></td>
        </tr>
        <tr>
            <td class="tdlabel" width="220px" style="text-align:right;">销方开户账号：</td>
            <td><input type="text" name="xhf_yhzh" value="<?php echo $response['xhf_yhzh'];?>" data-rules="{required: true}" /></td>
        </tr>
        <tr id="ghfsj" style="display: none">
            <td class="tdlabel" width="220px" style="text-align:right;">默认手机号：</td>
            <td><input type="text" style="width:100px" name="ghf_sj" value="<?php echo $response['ghf_sj'];?>"  />
                <span id = "mrsj" style="color:red;"></span>
                <div style="color:red;">         针对开票抬头类型为个人的订单，若订单中购货人手机号不为11位数字<br>（包含为空的情况），则取此参数的值作为购货人手机号</div>

   
            </td>
        </tr>
        <tr id="extra_display">
            <td class="tdlabel" width="220px" style="text-align:right;">发票需额外显示字段：</td>
            <td>
                <input type="checkbox" class="bui-form-field"  value="1"  name="is_unit" <?php if($response['is_unit']==1){ ?> checked="checked" <?php }?>/>单位(Unit)<br>
                <input type="checkbox" class="bui-form-field"  value="1"  name="is_spec" <?php if($response['is_spec']==1){ ?> checked="checked" <?php }?>/>规格型号(范例：白色16)
            </td>
        </tr>
    </table>
    
    <!--店铺-->
    <table id="shop" class="form_tbl" >
        <?php if ($response['app_scene'] == 'add') { ?>
            <tr style="background-color:#f5f5f5;">
                <td class="tdlabel" style="width:720px">&nbsp;&nbsp;主体挂靠店铺 
                <p class="add_btn" onclick = "add('shop')"> <img src="assets/images/plus.png" />添加</p>
                    
                </td>
            </tr>
        <?php } ?>
        <?php if ($response['app_scene'] == 'edit') { ?>
            <tr style="background-color:#f5f5f5;">
               <td class="tdlabel" style="width:720px">&nbsp;&nbsp;主体挂靠店铺 
                <p class="add_btn" onclick = "add('shop')"> <img src="assets/images/plus.png" />添加</p>
                    
                </td>
            </tr>
            <?php
            foreach ($response['shop_pz'] as $key => $value) {
                $shop_select = '<option value="">请选择</option>';
                $shop_code = !empty($value['shop_code']) ? $value['shop_code'] : '';
                $_id = $value['id'];
                foreach ($response['shop'] as $k => $v) {
                    if ($v['shop_code'] == $value['shop_code']) {
                        $shop_select.='<option value="' . $v['shop_code'] . '" selected="selected" >' . $v['shop_name'] . '</option>';
                    } else {
                        $shop_select.='<option value="' . $v['shop_code'] . '">' . $v['shop_name'] . '</option>';
                    }
                }
                echo '<tr>
            <td class="tdlabel" width="300px" ;">
                <select   name="shop[' . $key . '][shop_code]" style="width:200px;">' . $shop_select . ' </select> 
                eFAST店铺代码：<span>'.$shop_code.'</span>
                <p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p>
            </td>
        </tr>';
            }
            ?>
        <?php } ?>
    </table>
 <br/>
 <div>
          <div id="tab">
            <ul class="nav">
              <li class="bui-tab-panel-item active"><a href="#">电子发票</a></li>
              <li class="bui-tab-panel-item"><a href="#">纸质发票</a></li>
              
            </ul>
          </div>
          <div id="panel" class="">
              <div id="p1">
                  <table class="form_tbl">
                      <tr><td width="220px">URL</td><td width="500px"><input type="text" name="electron_url" value="<?php echo $response['electron_url'];?>" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" onclick="api_test(this)" class="button button-primary" value="测试"/></td></tr>
                      <tr><td colspan="2">是否设定单张发票最高额度？&nbsp;&nbsp;<input class="is_max" type="radio" name="is_electron_max"  value="1" <?php if($response['is_electron_max']==1) echo 'checked="checked"';?>/>是&nbsp;&nbsp;<input type="radio" name="is_electron_max"  value="0" <?php if($response['is_electron_max']==0) echo 'checked="checked"';?>/>否</td></tr>
                      <tr><td>单张发票最高额度：</td><td><input type="text" name="electron_max" value="<?php echo $response['electron_max'];?>" /></td></tr>
                  </table>
              </div>

              <div id="p2">
                  <table class="form_tbl">
                    <tr><td width="220px" >URL</td><td width="500px"><input type="text" name="paper_url" value="<?php echo $response['paper_url'];?>" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" onclick="api_test(this)" class="button button-primary" value="测试"/></td></tr>

                    <tr><td colspan="2">是否设定单张发票最高额度？&nbsp;&nbsp;<input class="is_max" type="radio" name="is_paper_max"  value="1" <?php if($response['is_paper_max']==1) echo 'checked="checked"';?>/>是&nbsp;&nbsp;<input type="radio" name="is_paper_max"  value="0" <?php if($response['is_paper_max']==0) echo 'checked="checked"';?>/>否</td></tr>
                    <tr><td>单张发票最高额度：</td><td><input type="text" name="paper_max" value="<?php echo $response['paper_max'];?>" /></td></tr>
                    <tr><td colspan="2">是否海洋石油发票？&nbsp;&nbsp;<input type="radio" name="is_sea"  value="1" <?php if($response['is_sea']==1) echo 'checked="checked"';?>/>是&nbsp;&nbsp;<input type="radio" name="is_sea"  value="0" <?php if($response['is_sea']==0) echo 'checked="checked"';?>/>否</td></tr>
                  </table>
              </div>
            </div>
 </div>
<!-- script start --> 

    <table>
        <tr>
            <td class="tdlabel"><button id="submit" class="button button-primary" type="button" onclick="check_list()" >提交</button></td>
            <td colspan="3"><button id="reset" class="button"  type="reset">重置</button></td>
        </tr>
    </table>
</form>

<?php 
$list = $response['shop'];
$shop_select = '<option value="">请选择</option>';
foreach ($list as $k => $v) {
    $shop_select.='<option value="' . $v['shop_code'] . '">' . $v['shop_name'] . '</option>';
}

?>
<script type="text/javascript">
    var app_scene = "<?php echo $response['app_scene']; ?>";
    var invoice_id = "<?php echo $request['_id']; ?>";
    var shop_select = '<?php echo $shop_select ?>';
    var select_shop;
    var json_id;
    

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
     
        

      
    var form1_data_source_v = $("#form1_data_source").html();
    if (form1_data_source_v != '') {
        $('form#form1').autofill(eval("(" + form1_data_source_v + ")"));
    }
    function getQueryString(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
        var r = window.location.search.substr(1).match(reg);
        if (r != null)
            return unescape(r[2]);
        return null;
    }


    var form = new BUI.Form.HForm({
        srcNode: '#form1',
        submitType: 'ajax',
        callback: function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, function () {
                if (data.status == 1) {
                    //parent._reload_page();
                    ui_closeTabPage(getQueryString('ES_frmId'));
                }
            }, type);
        }
    }).render();

    //增加
    var store_select = '<?php echo $store_select ?>';
 
    function add(type) {
      
            var i = $("#shop").find("tr").length - 1;
            $("#shop").append('<tr><td class="tdlabel" width="300px" ;"><select id=se_'+i+' name="shop[' + i + '][shop_code]" style="width:200px;">' +
                    shop_select + '</select>eFAST店铺代码：<span></span><p class="minus_btn"  onclick="del(this);" ><img src="assets/images/minus.png">删除</p></td></tr>');
            set_change_shop();
        
    }

    $(function(){
        set_change_shop();
        var type_config = $('#type_config').val();
        if(type_config==1){
                $('#guakao').show();
                   $('#ghfsj').hide();
            }else{
               $('#guakao').hide();
                $('#ghfsj').show();
            }
    
            
        set_change_gkdp();
    })
    function set_change_shop() {
        var select = $('#shop').find('select');
        select.off("change");

        select.on("change", function () {
            var name = $(this).attr("name");
            var shop_code = $(this).val();
            var check = get_other_shop(name, shop_code);
            var check_js_shop = get_js_shop( shop_code);
            if (check == 1 || check_js_shop == 1) {
                BUI.Message.Alert("店铺已经被选择，请选择其他店铺", 'error');
                $(this).find("option[value='']").attr('selected', true);
                $(this).next().html('');
            }else{
                $(this).next().html(shop_code);
            }
           //change_shop(shop_code,i);
           
        });
    }
    //和已经配置的店铺进行对比
    function get_js_shop(shop_code){
        check_js = 0;
        $.ajax({ type: 'POST', dataType: 'json',async : false,
         url: "?app_act=sys/invoice/JsFapiao/check_shop_code", data: {"shop_code": shop_code},
          success: function(ret) {
           if(ret.status < 0){
              check_js = 1;
             }
           }
         });
         //console.log(check_js);
       return check_js;
    }
    

    function check_list(){
        var type_config = $('#type_config').val();
        if(type_config == 1){ //阿里
            var gk_dp = $('#gk_dp').val();
            if(gk_dp == ''){
                BUI.Message.Alert('请选择挂靠店铺', 'error');
                return false;
            }
        }else{ //航信
            var sj_obj = $('#ghfsj').find("input[name='ghf_sj']");
            var ghf_sj_val = sj_obj.val();//默认手机
            if(ghf_sj_val == ''){
                BUI.Message.Alert('请填写默认手机号', 'error');
                return false;
            }
                var mesg = '<span class="x-icon x-icon-mini x-icon-error">!</span> 此参数必须为11位数字!';
                var reg = /^1[0-9]{10}$/;//检验手机号
                if(!reg.test(ghf_sj_val)){
                    $("#mrsj").html(mesg);//添加错误信息
                    return false;
                }

        }
       $('#form1').submit();
    }
    
    function set_change_gkdp(){
        var pzlx = $('#pzlx').find('select');
        pzlx.off("change");
        pzlx.on('change',function(){
            var type = $(this).val();
            if(type==1){
                $('#guakao').show();
                          $('#ghfsj').hide();
            }else{
               $('#guakao').hide();
                         $('#ghfsj').show();
            }
        });
    }
    
   
    function get_other_shop(name, shop_code) {
        var select = $('#shop').find('select');
        var check = 0;
        $.each(select, function (i, item) {
            if (name != $(item).attr("name")) {
                if ($(item).val() == shop_code) {
                    check = 1;
                }
            }
        });
        return check;
    }

    function check() {
        var all = form.get('children');
        for (var f in all) {
            if (all[f]['__attrVals']['param'] == 'check') {
                var element = all[f]['__attrVals'];
                element['error'] = '不能为空';
                element['rules'] = {required: true};
            }
        }
    }

    function del(item) {
        $(item).parent("td").parent("tr").remove();
    }


  
    /*--- API连通测试 ---*/
    function api_test(item) {
        var api_url = $(item).prev().val();
        if (api_url == '') {
            BUI.Message.Tip('请设置URL', 'warning');
            return false;
        }
        $.ajax({
            url: '<?php echo get_app_url('sys/invoice/JsFapiao/api_test'); ?>',
            type: 'POST',
            dataType: 'json',
            data: {api_url: api_url},
            success: function (ret) {
                if (ret.status == 1) {
                    BUI.Message.Tip(ret.message, 'success');
                } else {
                    BUI.Message.Tip(ret.message, 'error');
                }
            }
        });
    }

</script>
<?php echo load_js('comm_util.js') ?> 