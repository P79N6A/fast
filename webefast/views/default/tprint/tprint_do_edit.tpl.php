<!DOCTYPE html>
<html>
    <?php if($response['new_clodop_print'] == 1){echo "<script src='http://127.0.0.1:8000/CLodopfuncs.js?proper=1'></script>";}?>
    <head>
        <title></title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="assets/css/dpl.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/bui.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/main-min.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/common.css" rel="stylesheet" type="text/css" />
        <link href="assets/tprint/tprint.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
        <?php include get_tpl_path('web_page_top'); ?>

        <div class="print_main">

            <div class="print_main_left">


                <div class="print_top">

                    <div class="print_control">
                        <div class="row">
                            <div class="control-group" style="width:125px">
                                <label class="control-label">字体大小</label>
                                <div  class="controls"><select id="label_font_size" style="width:60px;" class="input-small"></select></div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">位置</label>
                                <div style="width:100px;" class="controls">
                                    <div id="pay_status" class="button-group">
                                        <ul class="bui-bar button-group" role="toolbar" id="label_position" aria-disabled="false" aria-pressed="false">
                                            <li style="width:30px; text-align: left"  class="bui-bar-item button button-small  bui-inline-block" aria-disabled="false" value="left" aria-pressed="false">左</li>
                                            <li style="width:30px;"  class="bui-bar-item button button-small bui-inline-block" aria-disabled="false" value="center" aria-pressed="false">中</li>
                                            <li style="width:30px; text-align:right" class="bui-bar-item button button-small bui-inline-block" aria-disabled="false" value="right">右</li></ul></div>
                                </div>
                            </div>

                            <div class="control-group" style="width:60px">
                                <label class="control-label">边框</label>
                                <div  class="controls">   <label class="checkbox"><input id="label_border" type="checkbox" value="1"></label>  </div>
                            </div>
                            <div class="control-group" style="width:95px">
                                <label class="control-label">宽度</label>
                                <div  class="controls"><input id="label_width" style="width:45px"  class="input-small" />  </div>
                            </div>
                            <div class="control-group" style="width:95px">
                                <label class="control-label">高度</label>
                                <div  class="controls"><input id="label_height" style="width:45px"  class="input-small" />  </div>
                            </div>



                            <div class="control-group" style="width:150px;height: 30px;">
                                <label class="control-label">内容</label>
                                <div  class="controls">
                                    <div class=" bui-select">
                                        <input id="label_text" style="width:85px;"  class="input-small" /><span class="x-icon x-icon-normal" id="show_record">
                                            <i class="icon icon-caret icon-caret-down"></i>

                                        </span>



                                    </div>

                                </div>
                            </div>
                            <div class="control-group" style="width:100px">
                                <label class="control-label">类型</label>
                                <div  class="controls"><select id="text_type" style="width:60px;">
                                        <option value="0">文本</option>
                                        <option value="1">条码</option>
                                    </select></div>
                            </div>
                            <!--div class="control-group" style="width:100px">
                                    <button class="button button-primary" id="load">保存设置</button>
                                </div-->


<!--                        <div class="span4"> 字体大小<select id="label_font_size" class="input-small"></select></div>
        <div class="span4">位置<select id="label_font_size" class="input-small"></select></div>
        <div class="span4">宽度<select id="label_font_size" class="input-small"></select></div>
         <div class="span4">边框<select id="label_font_size" class="input-small"></select></div>
         <div class="span4">文本<select id="label_font_size" class="input-small"></select></div>
                            -->

                        </div>

                    </div>
                </div>
                <div class="print_content">
                    <div class="print_frame">
                        <iframe id="template" style="background-color:white;height:<?php echo $response['data']['paper_height']; ?>mm;width: <?php echo $response['data']['paper_width']; ?>mm;" src="?app_act=tprint/tprint/edit_template&print_templates_code=<?php echo $request['print_templates_code']; ?>"></iframe>
                    </div>
                </div>
            </div>

            <div class="print_main_right">
                <div class="print_info">


                    <br />

                    <fieldset >
                        <legend>单据信息</legend>
                        <div class="row paper">
                            <div class="span6">
                                模板名称: <input type="text" style="width: 150px;" id="print_templates_name" name="print_templates_name" value="<?php echo $response['data']['print_templates_name']; ?>">

                            </div>
                            <div class="span6">
                                纸张宽: <input type="text"  style="width: 100px;" id="paper_width"  name="paper_width" class="num" value="<?php echo $response['data']['paper_width']; ?>"> mm
                            </div>
                            <div class="span6">
                                纸张高: <input type="text" style="width: 100px;"  id="paper_height"  name="paper_height" class="num" value="<?php echo $response['data']['paper_height']; ?>">mm
                            </div>

                            <div class="span6">
                                上边距: <input type="text" style="width: 100px;"  id="report_top"  name="report_top" class="num" value="<?php echo $response['data']['template_val']['report_top']; ?>">mm
                            </div>
                            <div class="span6">
                                左右边距: <input type="text" style="width: 100px;"  id="report_left"  name="paper_height" class="num" value="<?php echo $response['data']['template_val']['report_left']; ?>">mm
                            </div>
                            <div class="span6">
                                打印机:
                                <select id="printer" name="printer" style="width:120px;">

                                </select>
                            </div>
                            <div class="span6" style="height: 30px;">
                                <span>是否自动分页<input id="page_next_type" type="checkbox" <?php
                                    if ($response['data']['template_val']['page_next_type'] == 1) {
                                        echo 'checked="checked"';
                                    }
                                    ?> /></span>
                            </div>
                            <div class="span6" style="height: 30px;">
                                <span>明细每页数量<input style="width:50px;" id="page_size" type="text" value="<?php echo $response['data']['template_val']['page_size']; ?>" /></span>
                            </div>
                    </fieldset>

                    <div class="row span6">
                        <ul class="toolbar">
                            <li>
                                <button class="button button-primary" id="preview">预览模版</button>
                                <button class="button button-primary" id="save">保存模版</button>
                            </li>
                            <!--                            <li>
                                                            <button class="button button-primary" id="load">恢复</button>
                                                        </li>-->
                        </ul>
                    </div>
                </div>
             <!--div>设置文件<input id="label_text" type="text"  /><button id="set_text">设置</button></div>
             <div>主数据源<select id="label_data"></select><button id="set_data">设置数据</button></div>
             <div>明细数据源<select id="label_data_detail"></select><button id="set_data_detail">设置数据</button></div>

               <div>宽度<input id="label_width" /> px <button id="set_width">设置宽度</button></div>
               <div>位置<select id="label_position"></select></div>
               <div>字体大小<select id="label_font_size"></select></div-->



            </div>

        </div>

    </div>

    <div id="data_source" style="display: none;">
        <div class="row" style="width: 270px;">
            <div class="span8 ">
                <h3 style="border-bottom: #bbb solid 1px; width: 270px;" >单据数据</h3>
                <div id="source_record">

                </div>

            </div>
            <div class="span8">
                <h3 style="border-bottom: #bbb solid 1px; width: 270px;" >明细数据</h3>
                <div id="source_detail">

                </div>
            </div>
        </div>
    </div>
    <input id="label_id" type="hidden" value="" >
    <input type="hidden" id="css" value="<?php echo $response['data']['template_val']['css']; ?>" />
    <input type="hidden" id="conf" value="<?php echo $response['data']['template_val']['conf']; ?>" />


<?php echo load_js('jquery-1.8.1.min.js'); ?>
<?php echo load_js('core.min.js'); ?>
    <script type="text/javascript" src="assets/tprint/tprint.js"></script>
    <script>
        var print_conf = <?php echo json_encode($response['print_conf']); ?>;
        var template_conf = <?php echo json_encode($response['template_conf']); ?>;
        var new_clodop_print = '<?php echo $response['new_clodop_print'];?>';
        var LODOP;
        $(function () {
            if(new_clodop_print != 1){
                LODOP = getLodop();
            }
            print_conf.templateid = 'template';
            ininPrinterList();
            jprint.init(print_conf, template_conf.init_group);

            $('#save').click(function () {
                var url = '?app_act=tprint/tprint/save_edit&app_fmt=json&print_templates_code=<?php echo $request['print_templates_code']; ?>';
                var data = {};
                data.css = $('#css').val();
                data.conf = $('#conf').val();
                data.paper_width = $('#paper_width').val();
                data.paper_height = $('#paper_height').val();
                data.printer = $('#printer').val();
                data.page_next_type = $('#page_next_type').attr('checked') ? 1 : 0;
                data.page_size = $('#page_size').val();
                data.report_top = $('#report_top').val();
                data.report_left = $('#report_left').val();
                data.print_templates_name = $('#print_templates_name').val();
                var get_temp_data = '';
                var temp = "<?php echo $request['print_templates_code']; ?>";
                if(temp == 'oms_waves_record_clothing' || temp == 'wbm_store_out_clothing'){
                    get_temp_data = jprint.get_temp_data_clothing();
                }else{
                    get_temp_data = jprint.get_temp_data();
                }
                // return ;
                data = $.extend(get_temp_data, data);
                $.post(url, data, function (ret) {
                    BUI.Message.Alert('保存成功', 'success');
                    window.location.reload();
                }, 'json');

            });
            
            $('#preview').click(function () {
                var template_body = jprint.get_temp_html();
                if(new_clodop_print != 1){
                    LODOP = getLodop();
                }
                LODOP.PRINT_INITA(0, 0, $('#paper_width').val() + 'mm', $('#paper_height').val() + 'mm', "预览");
                LODOP.NewPageA();
                LODOP.ADD_PRINT_HTM(0, 0, "100%", "100%", template_body);
                LODOP.PREVIEW();
            });
            
            function ininPrinterList() {
                var h = "<option>无</option>";
                var c = LODOP.GET_PRINTER_COUNT();
                for (var i = 0; i < c; i++) {
                    var n = LODOP.GET_PRINTER_NAME(i);
                    h += "<option value='" + n + "'>" + n + "</option>";
                }

                $("#printer").html(h);
                $("#printer").val("<?php echo $response['data']['printer'] ?>");
            }


        });
    </script>

<?php echo_print_plugin() ?>
</body>
</html>
