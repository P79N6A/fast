<style>
    table .span4 { float: left; }
    .row { margin-left: 0px; }
    .paper input.num {width: 60px;}
    .print-item {cursor: pointer}
    legend {margin-bottom: 3px; margin-top: 10px;}
    .bui-uploader .bui-queue { display: none; }
</style>
<?php render_control('PageHead', 'head1',
    array('title'=>'编辑快递模板: '.$response['record']['print_templates_name'],
        'links'=>array(
            //array('url'=>'sys/print_templates/do_list', 'title'=>'模板列表', 'is_pop'=>false, 'pop_size'=>'800,600'),
            //array('type'=>'js', 'js'=>'doSave()', 'title'=>'保存'),
            //array('type'=>'js', 'js'=>'doPreview()', 'title'=>'预览'),
            //array('type'=>'js', 'js'=>'removePrintVar()', 'title'=>'移除选中'),
        ),
        'ref_table'=>'table'
    ));?>

<textarea class="hide" id="template_body" name="template_body"><?php echo $response['record']['template_body']?></textarea>
<textarea class="hide" id="template_body_default" name="template_body_default"><?php echo $response['record']['template_body_default']?></textarea>
<!--ul class="nav-tabs">
    <li id="tabs_100"><a href="javascript:doLink(100, 0)">款到发货模板</a></li>
    <li id="tabs_101"><a href="javascript:doLink(101, 0)">货到付款模板</a></li>
    <li id="tabs_102"><a href="javascript:doLink(102, 0)">快递公司热敏模板</a></li>
    <li id="tabs_103"><a href="javascript:doLink(103, 0)">云栈热敏模板</a></li>
</ul-->

<table cellpadding="0" cellspacing="0" width="100%" style="table-layout:fixed;">
    <tr>
        <td style="width: 70%; height: 100%">
            <div class="row">
                <ul class="toolbar">
                    <li><label class="checkbox">
                            <select id="record_index" name="record_index" onchange="doLink(<?php echo $response['record_index']?>, $(this).val())">
                                <?php foreach($response['record_list'] as $k => $v) {?>
                                    <option value="<?php echo $v['print_templates_id']?>"><?php echo $v['print_templates_name']?></option>
                                <?php }?>
                            </select>
                        </label></li>
                    <li><button class="button button-primary" onclick="saveAs()">另存为</button></li>
                    <li><button class="button button-primary" onclick="resetDefault()">恢复默认</button></li>
                    <li><button class="button button-primary" onclick="doPreview()">预览</button></li>
                    <li><button class="button button-primary" onclick="doSave()">保存</button></li>
                    <!--li><button class="button button-primary" onclick="doDelete()">删除</button></li-->
                    <li><button class="button button-primary" onclick="removePrintVar()">移除选中控件</button></li>
                </ul>
            </div>

            <OBJECT ID="LODOP" CLASSID="clsid:2105C259-1E0C-4534-8141-A753534CB4CA" width="100%" height="800px">
                <param name="Caption" value="">
                <param name="Border" value="1">
                <param name="CompanyName" value="上海百胜软件">
                <param name="License" value="452547275711905623562384719084">
                <embed id="LODOP_EM" TYPE="application/x-print-lodop" width="100%" height="800px" PLUGINSPAGE="">
            </OBJECT>
            <?php echo_print_plugin(0) ?>
        </td>
        <td style="width: 30%; padding-left: 5px;">
            <fieldset>
                <legend>纸张信息</legend>
                <div class="row paper">
                    向下偏: <input type="text" id="offset_top" name="offset_top" class="num" value="<?php echo $response['record']['offset_top']?>">
                    向右偏: <input type="text" id="offset_left" name="offset_left" class="num" value="<?php echo $response['record']['offset_left']?>">
                    <br>
                    纸张宽: <input type="text" id="paper_width" name="paper_width" class="num" value="<?php echo $response['record']['paper_width']?>">
                    纸张高: <input type="text" id="paper_height" name="paper_height" class="num" value="<?php echo $response['record']['paper_height']?>">
                    <br>
                    打印机:
                    <select id="printer" name="printer">

                    </select>
                    <br>
                    模板名称: <input type="text" id="print_templates_name" name="print_templates_name" value="<?php echo $response['record']['print_templates_name']?>">
                              <?php $company_data =  ds_get_select('express_company');?><br>
                  快递公司
                    <select id="company_code" name="company_code">
                        <?php foreach($company_data as $val):?>
                        <option value="<?php echo $val['company_code']; ?>" <?php if($response['record']['company_code']==$val['company_code']) {'select="select"';}?> ><?php echo $val['company_name']; ?></option>
                        <?php endforeach;?>
                    </select>
                
                
                </div>
            </fieldset>

            <fieldset>
                <legend>收货人信息</legend>
                <div class="doc-content span16">
                    <div class="row show-grid">
                        <?php $i = 0; foreach ($response['variables']['receiving'] as $k => $v) { $i++ ?>
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
                        <?php $i = 0; foreach ($response['variables']['delivery'] as $k => $v) { $i++ ?>
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
                    <input id="deteil_row" name="deteil_row" type="checkbox" <?php if(isset($response['template_val']['deteil_row'])&&$response['template_val']['deteil_row']==1) echo 'checked="checked"'; ?> />每行一条商品
                    <a href="javascript:void(0)" id="add_detail">多条明细</a>
                    <div class="row show-grid">
                        <div class="span4">  <label style="width: 100px"></label></div>
                        <?php $i = 0; foreach ($response['variables']['detail'] as $k => $v) { $i++ ?>
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
                <legend><?php if($response['record']['is_buildin']==4){echo '销货单信息';}else{ echo '订单信息';}?></legend>
                <div class="doc-content span16">
                    <div class="row show-grid">
                        <?php $i = 0; foreach ($response['variables']['record'] as $k => $v) { $i++ ?>
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
                <legend>修改背景</legend>
                <div class="doc-content span16">
                    <div class="row show-grid">
                        <div class="span4">
                            <label style="width: 100px">
                                <span title="change_bg" id="change_bg"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>上传图片</legend>
                <div class="doc-content span16">
                    <div class="row show-grid">
                        <div class="span4">
                            <label style="width: 100px">
                                <span title="upload_img" id="upload_img"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>自定义项</legend>
                <div class="doc-content span16">
                    <div class="row show-grid">
                        <div class="span4">
                            <label style="width: 100px">
                                <span title="custom_txt" class="print-item">自定义文本</span>
                            </label>
                        </div>
                        <div class="span4">
                            <label style="width: 100px">
                                <span title="custom_level" class="print-item">水平实线</span>
                            </label>
                        </div>
                        <div class="span4">
                            <label style="width: 100px">
                                <span title="custom_dotted_line" class="print-item">水平虚线</span>
                            </label>
                        </div>
                    </div>
                    <div class="row show-grid">
                        <div class="span4">
                            <label style="width: 100px">
                                <span title="custom_rect" class="print-item">柜形框</span>
                            </label>
                        </div>
                        <div class="span4">
                            <label style="width: 100px">
                                <span title="custom_vertical" class="print-item">垂直实线</span>
                            </label>
                        </div>
                        <div class="span4">
                            <label style="width: 100px">
                                <span title="custom_dotted_vertical" class="print-item">垂直虚线</span>
                            </label>
                        </div>
                    </div>
                </div>
            </fieldset>
        </td>
    </tr>
</table>
<?php foreach($response['template_val'] as $key=>$val):?>
<input id="detail" name="<?php echo  $key;?>" class="itemval" type="hidden" value="<?php echo  $val;?>" />
<?php endforeach;?>
<?php echo load_js('xlodop.js'); ?>
<script>
    var variables = <?php echo json_encode($response['variables_all']);?>;
    var record = <?php echo json_encode($response['record']);?>;
    var is_buildin=<?php echo $response['record']['is_buildin'];?>;
    $(document).ready(function(){
        $("#offset_top").keyup(function(evt){
            LODOP.SET_PRINT_STYLEA('PRINT_INIT','Top',$(this).val())
        })

        $("#offset_left").keyup(function(evt){
            LODOP.SET_PRINT_STYLEA('PRINT_INIT','Left',$(this).val())
        })

        $("#tabs_100").addClass("active")
        $("#record_index").val("<?php echo $response['record_index']?>")

        /*setTimeout(function () {
        
        }, 300);*/
        DisplayDesign();
        ininPrinterList()

        $('.print-item').click(function () {
            var itemName = $(this).attr("title");
            if(itemName == "custom_txt") {
                xlodop.ADD_PRINT_TEXTA(itemName+":", 50, 50, 150, 25, "自定义文本");
             } else if(itemName == "custom_rect") {
                LODOP.ADD_PRINT_RECT(50, 50, 100,60,0,1);
             } else if(itemName == "custom_level") {
                LODOP.ADD_PRINT_LINE(50, 50, 51, 250,0,1);
            }else if(itemName == "custom_vertical") {
                LODOP.ADD_PRINT_LINE(50, 50, 250, 51,0,1);
            }
            else if(itemName == "custom_dotted_line") {
                 LODOP.ADD_PRINT_LINE(50, 50, 51, 250,2,1);
            }  else if(itemName == "custom_dotted_vertical") {
                LODOP.ADD_PRINT_LINE(50, 50, 250, 51,2,1);
            }
            else {
                var text = variables[itemName];
                var selecti = itemName.indexOf("detail:");
                if(selecti==0){
                    //var detailname = itemName.substr(7);
                    if($('#detail').val()==''){
                        $('#detail').val(itemName);
                        xlodop.ADD_PRINT_TEXTA(itemName, 50, 50, 150, 50, text);
                    }else{
                        var detailItem =$('#detail').val();
                        // $('#detail').val( $('#detail').val()+","+detailname);
                        var all_text = LODOP.GET_VALUE ('ItemContent', detailItem);
                        LODOP.SET_PRINT_STYLEA(detailItem,'Content',all_text+" "+text);
                        var newdetailItem = detailItem+"|"+itemName;
                        LODOP.SET_PRINT_STYLEA(detailItem,'ItemName',newdetailItem);
                        $('#detail').val(newdetailItem);
                    }
                }else{
                    xlodop.ADD_PRINT_TEXTA(itemName, 50, 50, 150, 25, text);
                }
            }
        });
        $('#add_detail').click(function(){
                    if($('#detail').val()==''){
                       alert('请先添加明细');
                    }else{
                           var detailItem =$('#detail').val();
                           var all_text = LODOP.GET_VALUE ('ItemContent', detailItem);
                            xlodop.ADD_PRINT_TEXTA(detailItem, 50, 50, 150, 50, all_text);
                    }
        });
        
    });

    $(function () {

    });

    function ininPrinterList() {
        var h = "<option>无</option>"
        var c = LODOP.GET_PRINTER_COUNT()
        for(var i = 0; i < c; i++) {
            var n = LODOP.GET_PRINTER_NAME(i)
            h += "<option value='"+n+"'>"+n+"</option>"
        }

        $("#printer").html(h);
        $("#printer").val("<?php echo $response['record']['printer']?>");
    }

    BUI.use('bui/uploader',function (Uploader) {
        var uploader_change_bg = new Uploader.Uploader({
            //theme: 'imageView',
            'type':'iframe',
            render: '#change_bg',
            url: '?app_act=sys/print_templates/edit_express_upload',
            success: function(result){
                LODOP.ADD_PRINT_SETUP_BKIMG("<img border='0' src='"+result.url+"' />");
            },
            error: function(result){
                console.log(result)
            }
        }).render();

        var uploader_upload_img = new Uploader.Uploader({
            //theme: 'imageView',
            'type':'iframe',
            render: '#upload_img',
            url: '?app_act=sys/print_templates/edit_express_upload',
            success: function(result){
                LODOP.ADD_PRINT_IMAGE(50, 50, "100%", "100%","<img border='0' src='"+result.url+"' />");
            },
            error: function(result){
                console.log(result)
            }
        }).render();
    });

    //标识是否修改了信息(即是否需要离开页面时的提示)
    var g_isModified = false;
    function cb_isModified() {
        return !($('#form').text() == getProgram());
    }

    function doPreview(){
        eval(<?php echo json_encode($response['preview_data'])?>)
        LODOP.PREVIEW();
        DisplayDesign();
    }

    function doSave() {
    	if (confirm("修改保存之后，请确认信息可以正确打印！")){
    		var templates_val = {};
            if($("#detail").val()!=''){
                templates_val.detail = $("#detail").val();
                templates_val.deteil_row = ($("#deteil_row").attr("checked"))?1:0;
                
            }
	        var params = {
	            print_templates_id: <?php echo $response['record']['print_templates_id']?>,
	            print_templates_name: $("#print_templates_name").val(),
	            offset_top: $("#offset_top").val(),
	            offset_left: $("#offset_left").val(),
	            paper_width: $("#paper_width").val(),
	            paper_height: $("#paper_height").val(),
	            printer: $("#printer").val(),
	            templates_val: templates_val,
	            template_body: getProgram(),
                    is_buildin:is_buildin
	        };
	
	        $.post('?app_act=sys/print_templates/edit_express_action', params, function (data) {
	            if(data.status == "1") {
	                window.location = "?app_act=sys/express_tpl/do_list"
	            } else {
	                //BUI.Message.Alert(data.message, "error");
	                alert(data.message);
	            }
	        }, "json");
    	} else {
    	   return;
    	}
        
    }

    function saveAs() {
        var params = {
            print_templates_id: <?php echo $response['record']['print_templates_id']?>,
            //type: "<?php echo $response['record']['type']?>",
            // target_id: <?php //echo $response['record']['target_id']?>,
            // target_code: <?php //echo $response['record']['target_code']?>,
            print_templates_name: $("#print_templates_new_name").val(),
                    company_code:$("#company_code").val(),
            offset_top: $("#offset_top").val(),
            offset_left: $("#offset_left").val(),
            paper_width: $("#paper_width").val(),
            paper_height: $("#paper_height").val(),
            printer: $("#printer").val(),
            template_body: getProgram(),
            is_buildin:is_buildin
            //template_body_default: $("#template_body_default").val()
        };

        $.post('?app_act=sys/print_templates/edit_express_saveas', params, function (data) {
            if(data.status == "1") {
                //BUI.Message.Alert('保存成功','info');
                alert("保存成功");
                window.location.href = "?app_act=sys/print_templates/edit_express&_id="+data.data
            } else {
                //BUI.Message.Alert(data.message, "error");
                alert(data.message);
            }
        }, "json");
    }

    function doDelete() {
        var params = {
            print_templates_id: <?php echo $response['record']['print_templates_id']?>
        };

        $.post('?app_act=sys/print_templates/edit_express_deleting', params, function (data) {
            if(data.status == "1") {
                window.location = "?app_act=sys/print_templates/edit_express&express_id=<?php echo $request['_id']?>&record_type=<?php echo $response['record_type']?>"
            } else {
                //BUI.Message.Alert(data.message, "error");
                alert(data.message);
            }
        }, "json");
    }

    function resetDefault() {
        eval($("#template_body_default").val());
        xlodop.setShowMode(ShowModeList.design);
        LODOP.PRINT_DESIGN();
    }

    function doLink(recordType, recordIndex) {
        window.location = "?app_act=sys/print_templates/edit_express&express_id=<?php echo $request['_id']?>&record_type="+recordType+"&record_index="+recordIndex
    }
</script>

<script type="text/javascript">

    var LODOP;
    var c = <?php echo json_encode($response['variables_all'])?>;

    function CreatePage() {
        LODOP = getLodop();

        //初始化数据
        eval($("#template_body").val());
    }

    function DisplayDesign() {
        CreatePage();
        xlodop.setShowMode(ShowModeList.design);

        //LODOP.PRINT_DESIGN();
        LODOP.PRINT_SETUP();
    }

    function getProgram() {
        LODOP = getLodop();
        return LODOP.GET_VALUE("ProgramCodes", 0);
    }

    function prn_Preview() {
        LODOP = getLodop();
        eval(document.getElementById('form').value);
        LODOP.PREVIEW();
        LODOP = getLodop();
    }

    //修正toFixed的bug
    Number.prototype.toFixed = function (exponent) {
        return parseInt(this * Math.pow(10, exponent) + 0.5) / Math.pow(10, exponent);
    }

    function addPrintVar() {
        LODOP = getLodop();
        var title = $('#vars').val();
        var varName = title;
        window._u_hint = false;
        LODOP.ADD_PRINT_TEXTA(title, 10, 250, 175, 30, varName);
        setTimeout(function () {
            window._u_hint = true;
        }, 200);
    }

    function removePrintVar() {
        LODOP.SET_PRINT_STYLEA('Selected', 'Deleted', true);
    }
</script>
