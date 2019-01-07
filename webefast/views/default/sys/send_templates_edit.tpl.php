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

            <OBJECT ID="LODOP" CLASSID="clsid:2105C259-1E0C-4534-8141-A753534CB4CA" width="852px" height="600px">
                <param name="Caption" value="">
                <param name="Border" value="1">
                <param name="CompanyName" value="上海百胜软件">
                <param name="License" value="452547275711905623562384719084">
                <embed id="LODOP_EM" TYPE="application/x-print-lodop" width="800px" height="600px" PLUGINSPAGE="">
            </OBJECT>
            <?php echo_print_plugin(0) ?>
        </td>
        <td style="width: 30%;">
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
                </div>
            </fieldset>
            <fieldset>
                <legend>发货订单主信息</legend>
                <div class="doc-content span16">
                    <div class="row show-grid ">
                        <?php $i = 0; foreach ($response['variables']['record'] as $k => $v) { $i++ ?>
                            <div class="span4">
                                <label style="width: 100px">
                                    <span title="<?php echo $k; ?>" class="print-item" ><?php echo $v['title']; ?></span>
                                </label>
                            </div>
                            <?php if ($i % 3 == 0) echo '</div><div class="row">'; ?>
                        <?php } ?>
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend>收货人信息</legend>
                <div class="doc-content span16">
                    <div class="row show-grid">
                        <?php $i = 0; foreach ($response['variables']['receiving'] as $k => $v) { $i++ ?>
                            <div class="span4">
                                <label style="width: 100px">
                                    <span title="<?php echo $k; ?>" class="print-item" ><?php echo $v['title']; ?></span>
                                </label>
                            </div>
                            <?php if ($i % 3 == 0) echo '</div><div class="row">'; ?>
                        <?php } ?>
                    </div>
                </div>
            </fieldset>


            <fieldset>
                <legend>商品明细</legend>
                <div class="doc-content span16">
                    <div class="row show-grid paper">
                     分页数
                    <input id="deteil_page" name="deteil_page" type="input" class="num" value="<?php  echo isset($response['template_val']['deteil_page'])?$response['template_val']['deteil_page']:0;?>"  />
                    </div>
      
                    <div class="row show-grid">
                        <div class="span4">  <label style="width: 100px"></label></div>
                        <?php $i = 0; foreach ($response['variables']['detail'] as $k => $v) { $i++ ?>
                            <div class="span4">
                                <label style="width: 100px">
                                    <span title="<?php echo $k; ?>" class="print-item" ><?php echo $v['title']; ?></span>
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
                                    <span title="<?php echo $k; ?>" class="print-item" ><?php echo $v['title']; ?></span>
                                </label>
                            </div>
                            <?php if ($i % 3 == 0) echo '</div><div class="row">'; ?>
                        <?php } ?>
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
                                <span title="" class="print-item">水平实线</span>
                            </label>
                        </div>
                        <div class="span4">
                            <label style="width: 100px">
                                <span title="" class="print-item">水平虚线</span>
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
                                <span title="" class="print-item">垂直实线</span>
                            </label>
                        </div>
                        <div class="span4">
                            <label style="width: 100px">
                                <span title="" class="print-item">垂直虚线</span>
                            </label>
                        </div>
                    </div>
                </div>
            </fieldset>
        </td>
    </tr>
</table>

<input id="detail_val" name="detail_val" class="itemval" type="hidden" value="<?php  echo isset($response['template_val']['detail_val'])?  implode('|', $response['template_val']['detail_val']):'';?>" />
  <div id="detail_lable"  style="display:none" ><?php echo isset($response['template_val']['detail']['html'])?$response['template_val']['detail']['html']:'';?></div>
  

<?php echo load_js('xlodop.js'); ?>
<script>
    var variables = <?php echo json_encode($response['variables_all']);?>;
    var record = <?php echo json_encode($response['record']);?>;

    $(function () {
        $("#tabs_100").addClass("active")
        $("#record_index").val("<?php echo $response['record_index']?>")

        setTimeout(function () {
            DisplayDesign();
            LODOP.SET_SHOW_MODE ('TEXT_SHOW_BORDER',1);
            ininPrinterList()
        }, 300);
        $('.print-item').click(function () {
            var itemName = $(this).attr("title");
            if(itemName == "custom_txt") {
                xlodop.ADD_PRINT_TEXTA(itemName, 50, 50, 150, 25, "自定义文本");
            } else if(itemName == "custom_rect") {
                LODOP.ADD_PRINT_RECT(50, 50, 100,60,0,1);
            } else {
                var selecti = itemName.indexOf("detail:");  
                if(selecti==0){    
                    var text = variables[itemName]['title'];
                    var detail_width = parseInt(variables[itemName]['width'])+20;
                    itemName = itemName.substr(7);
                    add_detail(itemName,text,detail_width);
                }else{
                    var text = variables[itemName].label;
                    xlodop.ADD_PRINT_TEXTA(itemName, 50, 50, 150, 25, text);
                }
            }
        });
    });
    
    function add_detail(itemName,text,detail_width){ 
                    var detailobj = $('#detail_lable');
                    var top = 50,left = 50,height =40,width = 0;
                   var name = LODOP.GET_VALUE ( 'ItemName','detail');
                     if(name==''){
                         $('#detail_val').val('');
                          $('#detail_lable').html('');
                     }
                    
                    if($('#detail_val').val()==''){
                         $('#detail_val').val(itemName);
                         width = detail_width;
                            var html = '<style type="text/css" >tr td{ border-color:#000000; font-size:12px}</style>';
                             html += '<table width="'+detail_width+'px" border="1" cellpadding="0" cellspacing="0"  style="font-size:12px;border-color:#000000;text-align:center" >';
                             html+='<tr>';   
                             html+='<td>'+text+'<td/>';
                             html+='</tr>'; 
                             html+='</table>'; 
                           $('#detail_lable').append(html);
                           $('#detail_lable').find('tbody').append('<!--$detail_list-->');
                    }else{ 
                         var detailItem=$('#detail_val').val();
                         var selecti = detailItem.indexOf(itemName);  
                         if(selecti>-1){
                             alert(text+'已经添加');
                             return false;
                         }
                         var detailobj = $('#detail_lable')
                         width = detailobj.find('table').width()+detail_width;
                         detailobj.find('table').width(width);
                         detailobj.find("tr").append('<td>'+text+'<td/>');
                         top =  parseInt(LODOP.GET_VALUE ( 'ItemTop','detail'));
                         left =  parseInt(LODOP.GET_VALUE ( 'ItemLeft','detail'));
                         height =   parseInt(LODOP.GET_VALUE ( 'ItemHeight','detail'));
                         width = width+20;
                         LODOP.SET_PRINT_STYLEA('detail','Deleted',true);
                         var newdetailItem = detailItem+"|"+itemName;
                         $('#detail_val').val(newdetailItem); 
                    }

                    detailobj.find("td:empty").remove();
                    LODOP.ADD_PRINT_HTML( top,left,width, height, detailobj.html());
                    LODOP.SET_PRINT_STYLEA(0,"ItemName","detail");
                    
    }
    
    
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
        var templates_val = {};
            if($("#detail_val").val()!=''){
                templates_val.detail_val = $("#detail_val").val();
                templates_val.detail = {};
                templates_val.detail.html =  $('#detail_lable').html();
                templates_val.detail.top =  parseInt(LODOP.GET_VALUE ( 'ItemTop','detail'));
                templates_val.detail.left = parseInt(LODOP.GET_VALUE ( 'ItemLeft','detail'));
                templates_val.detail.width = parseInt(LODOP.GET_VALUE ( 'ItemWidth','detail'));
                templates_val.detail.height = parseInt(LODOP.GET_VALUE ( 'ItemHeight','detail'));
                if($("#deteil_page").val()!=''){
                    templates_val.detail.page = $("#deteil_page").val();
                }
            }

             LODOP.SET_PRINT_STYLEA('detail','Deleted',true);
             
        var params = {
            print_templates_id: <?php echo $response['record']['print_templates_id']?>,
            print_templates_name: $("#print_templates_name").val(),
            offset_top: $("#offset_top").val(),
            offset_left: $("#offset_left").val(),
            paper_width: $("#paper_width").val(),
            paper_height: $("#paper_height").val(),
            printer: $("#printer").val(),
            templates_val: templates_val,
            template_body: getProgram()
        };

        $.post('?app_act=sys/send_templates/edit_send_action', params, function (data) {
            if(data.status == "1") {
                window.location = "?app_act=base/shipping/do_list"
            } else {
                //BUI.Message.Alert(data.message, "error");
                alert(data.message);
            }
        }, "json");
    }

    function saveAs() {
        var params = {
            //print_templates_id: <?php echo $response['record']['print_templates_id']?>,
            type: <?php echo $response['record']['type']?>,
          //  target_id: <?php //echo $response['record']['target_id']?>,
           // target_code: <?php //echo $response['record']['target_code']?>,
            print_templates_name: $("#print_templates_new_name").val(),
            offset_top: $("#offset_top").val(),
            offset_left: $("#offset_left").val(),
            paper_width: $("#paper_width").val(),
            paper_height: $("#paper_height").val(),
            printer: $("#printer").val(),
            template_body: getProgram(),
            template_body_default: $("#template_body_default").val()
        };

        $.post('?app_act=sys/send_templates/edit_send_saveas', params, function (data) {
            if(data.status == "1") {
              //  window.location.href = "?app_act=base/shipping/edit_express&express_id=<?php echo $request['_id']?>&record_type=<?php echo $response['record_type']?>&record_index="+data.data
            BUI.Message.Alert('保存成功','info');
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
        LODOP. SET_SHOW_MODE("TEXT_SHOW_BORDER",1);
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
