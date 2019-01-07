<?php

require_lib('util/web_util', true);
require_lib('business_util', true);

class tprint {

    function do_edit(array &$request, array &$response, array &$app) {

        $app['page'] = 'NULL';
        $data = load_model('sys/PrintTemplatesModel')->get_by_code($request['print_templates_code']);

        $data['template_val'] = json_decode($data['template_val'], true);
        $data['template_val']['report_top'] = isset($data['template_val']['report_top']) ? $data['template_val']['report_top'] : 5;
        $data['template_val']['report_left'] = isset($data['template_val']['report_left']) ? (int) $data['template_val']['report_left'] : 10;

        $response['data'] = $data;

        $response['print_conf'] = require_conf('tprint/' . $data['template_val']['conf']);
        $response['template_conf'] = require_conf('tprint/' . $response['print_conf']['template']);
        $arr = array('clodop_print');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['new_clodop_print'] = isset($ret_arr['clodop_print']) ? $ret_arr['clodop_print'] : 0;
        //b2b_box.conf
        //report.conf
    }

    function edit_template(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
        $data = load_model('sys/PrintTemplatesModel')->get_by_code($request['print_templates_code']);
        $data['template_val'] = json_decode($data['template_val'], true);
        $css = "assets/tprint/" . $data['template_val']['css'] . ".css";
        $detail_body = htmlspecialchars_decode($data['template_body_replace']);
        $response['data'] = $data;
//        if ($data['print_templates_code'] == 'pur_purchaser_new') {
//            $pur_purchaser_new_str = "<div id='report'><div title='报表头' class='group' id='report_top' style='float: left; margin: 0 35px; '><div class='row border' id='row_0' style='height: 60px;' nodel='1'><div class='column' id='column_0' style='width: 245px; height: 40px; text-align: center; line-height: 40px; font-size: 18px;'>采购入库单</div></div><div class='row border' id='row_9' style='float:left;width:220px;'><div class='column' id='column_117' style='height: 30px; line-height: 30px; width: 70px; text-align: right;'>单据编号：</div><div class='column' id='column_118' style='width: 100px; text-align: left; height: 30px; line-height: 30px;'>{@单据编号}</div></div><div class='row border' id='row_10' style='float:left;width:220px;'><div class='column' id='column_119' style='height: 30px; line-height: 30px; width: 70px; text-align: right;'>原单号：</div><div class='column' id='column_123' style='width: 100px; text-align: left; height: 30px; line-height: 30px;'>{@原单号}</div></div><div class='row border' id='row_11' style='float:left;width:243px;'><div class='column' id='column_120' style='height: 30px; line-height: 30px; width: 65px; text-align: right;'>下单时间：</div><div class='column' id='column_121' style='width: 145px; text-align: left; height: 30px; line-height: 30px;'>{@下单时间}</div></div><div class='row border' id='row_12' style='float:left;width:220px;'><div class='column' id='column_122' style='height: 30px; line-height: 30px; width: 70px; text-align: right;'>业务日期：</div><div class='column' id='column_124' style='width: 100px; text-align: left; height: 30px; line-height: 30px;'>{@业务日期}</div></div><div class='row border' id='row_13' style='float:left;width:220px;'><div class='column' id='column_125' style='height: 30px; line-height: 30px; width: 70px; text-align: right;'>供应商：</div><div class='column' id='column_126' style='width: 100px; text-align: left; height: 30px; line-height: 30px;'>{@供应商}</div></div><div class='row border' id='row_14' style='float:left;width:220px;'><div class='column' id='column_127' style='height: 30px; line-height: 30px; width: 70px; text-align: right;'>仓库：</div><div class='column' id='column_128' style='width: 100px; text-align: left; height: 30px; line-height: 30px;'>{@仓库}</div></div><div class='row border' id='row_15' style='float:left;width:220px;'><div class='column' id='column_129' style='height: 30px; line-height: 30px; width: 70px; text-align: right;'>总数量： </div><div class='column' id='column_130' style='width: 100px; text-align: left; height: 30px; line-height: 30px;'>{@总数量} </div></div><div class='row border' id='row_16' style='float:left;width:220px;'><div class='column' id='column_131' style='height: 30px; line-height: 30px; width: 70px; text-align: right;'>总金额：</div><div class='column' id='column_132' style='width: 100px; text-align: left; height: 30px; line-height: 30px;'>{@总金额}</div></div><div class='row border' id='row_17' style='float:left;width:220px;'><div class='column' id='column_133' style='height: 30px; line-height: 30px; width: 70px; text-align: right;'>备注：</div><div class='column' id='column_134' style='width: 100px; text-align: left; height: 30px; line-height: 30px;'>{@备注}</div></div></div><div title='表格' class='group' id='report_table_body' type='table' nodel='1' style='float: left; margin: 0 35px;'><table class='table' id='table_1' border='0' cellpadding='0' cellspacing='0'><tr><td class='td_title' style='width: 60px;'><div class='td_column' id='column_th_69' style='width: 60px; height: 30px; line-height: 30px;'>商品名称</div></td><td class='td_title' style='width: 80px;'><div class='td_column' id='column_th_70' style='width: 50px;'>商品编码</div></td><td class='td_title' style='width: 40px;'><div class='td_column' id='column_th_71' style='width: 40px;'>规格1</div></td><td class='td_title' style='width: 40px;'><div class='td_column' id='column_th_72' style='width: 40px; height: 20px; line-height: 20px;'>颜色 </div></td><td class='td_title' style='width: 60px;'><div class='td_column' id='column_th_144'>商品条形码 </div></td><td class='td_title' style='width: 60px;'><div class='td_column' id='column_th_144'>库位</div></td><td class='td_title' style='width: 60px;'><div class='td_column' id='column_th_144'>单价</div></td><td class='td_title' style='width: 60px;'><div class='td_column' id='column_th_144'>数量 </div></td><td class='td_title' style='width: 60px;'><div class='td_column' id='column_th_144'>金额</div></td><td class='td_title' style='width: 60px;'><div class='td_column' id='column_th_144'>通知数</div></td><td class='td_title'><div class='td_column' id='column_th_160'>吊牌价</div></td></tr><!--detail_list--></table></div><div title='表格尾' class='group' id='report_table_bottom' style='float:left; margin: 0 35px;'><div class='row border' id='row_2' nodel='1'><div class='column' id='column_6' style='width: 60px; text-align: left;'>合计：     </div><div class='column' id='column_22' style='width: 120px; text-align: right;'>{@总金额}        </div></div><div id='row_19' class='row border'><div style='height: 22px; line-height: 22px; width: 80px;' id='column_172' class='column'>总通知数        </div><div style='height: 22px; line-height: 22px; width: 100px;' id='column_173' class='column'>{@总通知数}        </div></div></div></div>";
//            $data['template_body'] = $pur_purchaser_new_str;
//        }
        if($data['print_templates_code'] === 'wbm_store_out_clothing' or $data['print_templates_code'] === 'oms_waves_record_clothing'){
            $str_body = load_model('sys/PrintTemplatesModel')->get_size_table();
            $data['template_body'] = str_replace('{#表格}',$str_body,$data['template_body']);
            $detail_body = load_model('sys/PrintTemplatesModel')->get_size_table_detail();
        }
        $response['body'] = htmlspecialchars_decode($data['template_body']);
        $response['body'] = str_replace('<!--detail_list-->', $detail_body, $response['body']);
        $response['css'] = $css;

        $response['report_top'] = isset($data['template_val']['report_top']) ? $data['template_val']['report_top'] : 5;
        $response['report_left'] = isset($data['template_val']['report_left']) ? (int) $data['template_val']['report_left'] * 2 : 20;
    }

    function save_edit(array &$request, array &$response, array &$app) {
        $data = array();
        $data['template_body'] = $request['template_body'];
        $data['paper_width'] = $request['paper_width'];
        $data['paper_height'] = $request['paper_height'];
        $data['print_templates_name'] = $request['print_templates_name'];
        $data['template_body_replace'] = $request['detail_body'];
        $data['printer'] = $request['printer'];

        $template_val['conf'] = $request['conf'];
        $template_val['page_next_type'] = $request['page_next_type'];
        $template_val['css'] = $request['css'];
        $template_val['page_size'] = $request['page_size'];
        $template_val['report_top'] = $request['report_top'];
        $template_val['report_left'] = $request['report_left'];

        $data['template_val'] = json_encode($template_val);

        $response = load_model('sys/PrintTemplatesModel')->update($data, array('print_templates_code' => $request['print_templates_code']));
    }

    function print_template(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';

        $app['tpl'] = 'tprint/tprint_edit_template';
        $response = load_model('sys/PrintTemplatesModel')->tprint_get_print_data($request);
        if (isset($response['tpl'])) {
            $app['tpl'] = $response['tpl'];
        }
        $response['report_top'] = isset($response['data']['template_val']['report_top']) ? $response['data']['template_val']['report_top'] : 5;
        $response['report_left'] = isset($response['data']['template_val']['report_left']) ? (int) $response['data']['template_val']['report_left'] * 2 : 20;
    }

    function do_print(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
        $response['data'] = load_model('sys/PrintTemplatesModel')->get_by_code($request['print_templates_code']);
        if($request['print_templates_code'] == 'pur_planned_record'){
        $response['print_url'] = "?app_act=tprint/tprint/planned_record_print";
        }else{
        $response['print_url'] = "?app_act=tprint/tprint/print_template";            
        }
        $key_arr = array('left_sim', 'PHPSESSID', 'fastappsid', 'record_ids');
        $response['record_ids'] = explode(',', $request['record_ids']);
        if($request['print_templates_code'] == 'wbm_store_out_record_goods'){
        $response['sku'] = explode(',', $request['sku']);    
        }
        foreach ($request as $key => $val) {
            if (!in_array($key, $key_arr)) {
                $response['print_url'].="&{$key}=" . $val;
            }
        }
    }
    function planned_record_print(array &$request, array &$response, array &$app){
        $app['page'] = 'NULL';
        $response['data'] = load_model("pur/PlannedRecordModel")->print_data_default_new($request);
        return $response;
    }
    function get_printer(array &$request, array &$response, array &$app){
        $ret = load_model('sys/PrintTemplatesModel')->get_printer($request['template']);
        exit_json_response($ret);
    }
    function save_printer(array &$request, array &$response, array &$app){
        $ret = load_model('sys/PrintTemplatesModel')->save_printer($request['clodop_printer'],$request['print_templates_code']);
        exit_json_response($ret);        
    }
}
