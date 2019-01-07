<?php echo load_js('jquery.cookie.js'); ?>
<style>
    table .span4 { float: left; width: 150px}
    table .span16 { float: left; width: 88%}
    .row { margin-left: 0px; }
    .paper input.num {width: 60px;}
    .print-item {cursor: pointer}
    legend {margin-bottom: 3px; margin-top: 10px;}
    .bui-uploader .bui-queue { display: none; }
    .chk_1 { 
        display: none; 
    } 
    .chk_1 + label {
        background-color: #FFF;
        border: 1px solid #C1CACA;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05), inset 0px -1px 1px -2px rgba(0, 0, 0, 0.05);
        padding: 9px;
        border-radius: 5px;
        display: inline-block;
        position: relative;
        margin-right: 3px;
        vertical-align: middle;
    }
    .chk_1 + label:active {
        box-shadow: 0 1px 2px rgba(0,0,0,0.05), inset 0px 1px 3px rgba(0,0,0,0.1);
    }

    .chk_1:checked + label {
        background-color: #ECF2F7;
        border: 1px solid #92A1AC;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05), inset 0px -15px 10px -12px rgba(0, 0, 0, 0.05), inset 15px 10px -12px rgba(255, 255, 255, 0.1);
        color: #243441;
    }

    .chk_1:checked + label:after {
        content: '\2714';
        position: absolute;
        top: 0px;
        left: 0px;
        color: #758794;
        width: 100%;
        text-align: center;
        font-size: 1.4em;
        vertical-align: text-top;
    }
</style>

<?php render_control('PageHead', 'head1',array('title'=>'编辑模板: '.$response['record']['print_templates_name'],'ref_table'=>'table'));?>

<textarea class="hide" id="template_body" name="template_body"><?php echo $response['record']['template_body'] ?></textarea>
<textarea class="hide" id="template_body_default" name="template_body_default"><?php echo $response['record']['template_body_default'] ?></textarea>

<table cellpadding="0" cellspacing="0" width="100%" style="table-layout:fixed;">
        <tr>
        <td style="width: 70%; height: 100%">
            <div class="row">
                <ul class="toolbar">

                    <li><button class="button button-primary" onclick="doPreview()">预览</button></li>
                    <li><button class="button button-primary" onclick="doSave()">保存</button></li>
                </ul>
            </div>
        </td>
    </tr>
    <tr>
        <td style="width: 40%; padding-left: 5px;">
            <fieldset>
                <legend>纸张信息</legend>
                <br>
                <div class="row paper">
                    向下偏: <input type="text" id="offset_top" name="offset_top" class="num" value="<?php echo $response['record']['offset_top'] ?>">
                    向右偏: <input type="text" id="offset_left" name="offset_left" class="num" value="<?php echo $response['record']['offset_left'] ?>">
                    纸张宽: <input type="text" id="paper_width" name="paper_width" class="num" value="<?php echo $response['record']['paper_width'] ?>">
                    纸张高: <input type="text" id="paper_height" name="paper_height" class="num" value="<?php echo $response['record']['paper_height'] ?>">
                    <br>
                    <br>

                    <!--打印机:-->
<!--                    <select id="clodop_printer" name="clodop_printer">

                    </select>-->
                    模板名称: <input type="text" id="print_templates_name" name="print_templates_name" value="<?php echo $response['record']['print_templates_name'] ?>">


                </div>
            </fieldset>
            <br>
            <br>
            <fieldset>
                <legend>模板信息</legend>
                <br>
                <div class="doc-content span16">
                    <div class="row show-grid">
                    <?php 
                        $i = 0;
                        foreach ($response['variables'] as $k => $v) {
                            $i++ 
                    ?>
                        <div class="span4">
                            <label style="width: 150px">
                                <input type="checkbox" class="chk_1" id="checkbox_a1_<?php echo $k; ?>" name="param-selecotr" value="<?php echo $k; ?>"/>
                                <label for="checkbox_a1_<?php echo $k; ?>"></label><label><?php echo $v; ?></label>
                            </label>
                        </div>
                    <?php if ($i % 5 == 0) {echo '</div><div class="row">';} ?>
                    <?php } ?>
                    </div>
                    <label style="width: 150px">
                        <input type="checkbox" class="chk_1" id="checkbox_a1_barcode_img" name="param-selecotr" value="checkbox_a1_barcode_img"/>
                        <label for="checkbox_a1_barcode_img"></label><label>条形码</label>
                    </label>
                </div>
            </fieldset>
            <br><br>
            <fieldset>
                <legend>上传图片</legend>
                    <div class="upload1">
                        <div style="float: left;">
                            <span title="upload_img" id="upload_img"></span>
                        </div>
                    </div><br>
                    <div class="tips tips-small tips-info" style=" margin-top: 8px;">
                        <span class="x-icon x-icon-small x-icon-info"><i class="icon icon-white icon-info"></i></span>
                        <div class="tips-content">附件支持jpg\png\gif格式，大小不超过2M</div>
                    </div>
            </fieldset>
    </tr>
</table>
<?php foreach ($response['template_val'] as $key => $val): ?>
    <input id="detail" name="<?php echo $key; ?>" class="itemval" type="hidden" value="<?php echo $val; ?>" />
<?php endforeach; ?>

<script src='http://127.0.0.1:8000/CLodopfuncs.js'></script>
<script>
    $(document).ready(function () {
        initWebSocket();
    });

    var socket;
    var initWebSocket = function () {
        if (window.WebSocket) {
            socket = new WebSocket("ws://127.0.0.1:8000");
            socket.onopen = function (event) {
                var default_clodop_printer = $.cookie('_clodop_printer');
                CLODOP.Create_Printer_List(document.getElementById('clodop_printer'));
                $("#clodop_printer option").each(function () {
                    if ($(this).text() == default_clodop_printer) {
                        $(this).attr("selected", "selected");
                    }
                });
            };
            socket.onerror = function (event) {
                var msg = '<h3>无法连接到Clodop打印组件</h3>' +
                        '<p style="text-align: center;"><a href="http://www.lodop.net/download/CLodop_Setup_for_Win32NT_2.090.zip">点击下载</a></p>';
                BUI.Message.Alert(msg, 'error');
                return;
            };
        } else {
            BUI.Message.Alert('浏览器不支持打印控件', 'error');
        }
    }
    
</script>

<script>
    var variables = <?php echo json_encode($response['variables_all']); ?>;
    var record = <?php echo json_encode($response['record']); ?>;
    var is_buildin =<?php echo $response['record']['is_buildin']; ?>;
    var print_templates_id = <?php echo $response['record']['print_templates_id'] ?>;
    var c = <?php echo json_encode($response['variables_all'])?>;
    
    $(document).ready(function () {
        $("#offset_top").keyup(function () {
            var lodop_code = "LODOP.SET_PRINT_STYLEA('PRINT_INIT', 'Top',";
            var lodop_str = lodop_code + $(this).val() + ")";
            var template_body = $("#template_body").val();
            change_lodop_code(lodop_code, lodop_str ,template_body);
        })

        $("#offset_left").keyup(function () {
            var lodop_code = "LODOP.SET_PRINT_STYLEA('PRINT_INIT', 'Left',";
            var lodop_str = lodop_code + $(this).val() + ")";
            var template_body = $("#template_body").val();
            change_lodop_code(lodop_code, lodop_str ,template_body);
        })

        $('input:checkbox[name=param-selecotr]').click(function () {
            
        });
        $('#add_detail').click(function () {
            if ($('#detail').val() == '') {
                alert('请先添加明细');
            } else {
                var detailItem = $('#detail').val();
                var all_text = LODOP.GET_VALUE('ItemContent', detailItem);
                xlodop.ADD_PRINT_TEXTA(detailItem, 50, 50, 150, 50, all_text);
            }
        });

    });
    
    var template_body = document.getElementById('template_body_default').value;
    function doPreview() {
        var lodop_str = '';
        var lodop_code;
        $('input:checkbox[name=param-selecotr]:checked').each(function (i) {
            var select_info_prop = $(this).val();
            var select_info_name = c[select_info_prop];
            if(select_info_prop == 'checkbox_a1_barcode_img'){
                lodop_str = "LODOP.ADD_PRINT_BARCODE(50, 50, 200, 80, '128B', 690123456789);"+'\n';
            } else {
                lodop_code =  'LODOP.ADD_PRINT_TEXTA("' + select_info_prop + '",';
                lodop_str = lodop_code + (10 + i * 20) + ',10,100,20, "' + select_info_name + '"); \n';
            }
            if(typeof(lodop_str) != "undefined"){
              template_body = change_lodop_code(lodop_code, lodop_str, template_body);
            }
        });
		eval(template_body);
		if (LODOP.CVERSION) {
            CLODOP.On_Return=function(TaskID,Value){
                document.getElementById('template_body').value = Value;
            }
        };
		document.getElementById('template_body').value = LODOP.PRINT_DESIGN();
    }

    function doSave() {
        var template=$("#template_body").val();
        //打印设计窗口如果没有保存和关闭，执行保存操作的话template为类似CLODOP.strWebPageID形式
        if(template.indexOf(CLODOP.strWebPageID)<0){
            BUI.Message.Confirm("修改保存之后，请确认信息可以正确打印！", function(){
                var templates_val = {};
                if ($("#detail").val() != '') {
                    templates_val.detail = $("#detail").val();
                    templates_val.deteil_row = ($("#deteil_row").attr("checked")) ? 1 : 0;
                }
                var type='clodop';
                var params = {
                    print_templates_id: print_templates_id,
                    print_templates_name: $("#print_templates_name").val(),
                    offset_top: $("#offset_top").val(),
                    offset_left: $("#offset_left").val(),
                    paper_width: $("#paper_width").val(),
                    paper_height: $("#paper_height").val(),
                    printer: $("#printer").val(),
                    templates_val: templates_val,
                    template_body: $("#template_body").val(),
                    template_body_default: $("#template_body").val(),
                    print_type:type,
                    is_buildin: is_buildin,
                    templates_type: 'print_barcode'
                };
                $.post('?app_act=sys/print_templates/edit_express_action', params, function (data) {
                    if (data.status == "1") {
                        location.reload();
                        alert("保存成功");
                    } else {
                        BUI.Message.Alert(data.message, "error");
                    }
                }, "json");
            },'question')
        }else{
            alert("打印设计窗口必须关闭才能保存");
        }

    }

    function saveAs() {
        var params = {
            print_templates_id: <?php echo $response['record']['print_templates_id'] ?>,
            company_code: $("#company_code").val(),
            offset_top: $("#offset_top").val(),
            offset_left: $("#offset_left").val(),
            paper_width: $("#paper_width").val(),
            paper_height: $("#paper_height").val(),
            printer: $("#printer").val(),
            template_body: getProgram(),
            is_buildin: is_buildin
        };

        $.post('?app_act=sys/print_templates/edit_express_saveas', params, function (data) {
            if (data.status == "1") {
                alert("保存成功");
                window.location.href = "?app_act=sys/print_templates/edit_express&_id=" + data.data
            } else {
                alert(data.message);
            }
        }, "json");
    }

    function doDelete() {
        var params = {
            print_templates_id: <?php echo $response['record']['print_templates_id'] ?>
        };

        $.post('?app_act=sys/print_templates/edit_express_deleting', params, function (data) {
            if (data.status == "1") {
                window.location = "?app_act=sys/print_templates/edit_express&express_id=<?php echo $request['_id'] ?>&record_type=<?php echo $response['record_type'] ?>"
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
        window.location = "?app_act=sys/print_templates/edit_express&express_id=<?php echo $request['_id'] ?>&record_type=" + recordType + "&record_index=" + recordIndex
    }

    function removePrintVar() {
        LODOP.SET_PRINT_STYLEA('Selected', 'Deleted', true);
    }
    
    function change_lodop_code (lodop_code, lodop_str, original_code) {
//        if(original_code.indexOf(lodop_code) < 0) {
             original_code += lodop_str;
//        }
        return original_code;
    }
    
    BUI.use('bui/uploader', function (Uploader) {
        var url = "<?php echo $response['upload_path']; ?>";
        var filetype = {
            ext: ['.jpg,.png,.gif', '文件类型只能为{0}'],
            maxSize: [2048, '文件大小不能大于2M'],
            //minSize: [1, '文件最小不能小于1k!'],
            max: [5, '文件最多不能超过{0}个！'],
            min: [1, '文件最少不能少于{0}个!'],
        };
        var uploader_change_bg = new Uploader.Uploader({
            //theme: 'imageView',
            'type': 'iframe',
            render: '#change_bg',
            rules: filetype,
            multiple: false,
            url: url,
            success: function (result) {
                LODOP.ADD_PRINT_SETUP_BKIMG("<img border='0' src='" + result.url + "' />");
//                var paper_width = $("#paper_width").val();
//                var paper_height = $("#paper_height").val();
		LODOP.SET_SHOW_MODE("BKIMG_LEFT",1);
                LODOP.SET_SHOW_MODE("BKIMG_TOP",1);
//                LODOP.SET_SHOW_MODE("BKIMG_WIDTH",'"' + paper_width + 'mm"');
//                LODOP.SET_SHOW_MODE("BKIMG_HEIGHT",'"' + paper_height+'mm"');
                //LODOP.ADD_PRINT_SETUP_BKIMG("<img border='0' src='" + result.url + "' />");
            },
            error: function (result) {
//                console.log(result)
                if(result.status < 0) {
//                    BUI.Message.Alert(result.message);
                    console.log("error" + result);
                    BUI.Message.Alert("上传失败", "error");
                }
            }
        }).render();

        var uploader_upload_img = new Uploader.Uploader({
            //theme: 'imageView',
            'type': 'iframe',
            render: '#upload_img',
            rules: filetype,
            multiple: false,
            url: url,
            success: function (result) {
                template_body += 'LODOP.ADD_PRINT_IMAGE(0,0,400,200,"<img border=\'0\' src=\'' + result.url + '\' />");\n'; 
		template_body += 'LODOP.SET_PRINT_STYLEA(0,"Stretch",2);\n';//按原图比例(不变形)缩放模式
                //LODOP.ADD_PRINT_SETUP_BKIMG("<img border='0' src='" + result.url + "' />");
            },
            error: function (result) {
                if(result.status < 0) {
                    console.log("error" + result);
                    BUI.Message.Alert("上传失败", "error");
                }
            }
        }).render();
    });
</script>
