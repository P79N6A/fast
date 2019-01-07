<?php

/**
 * 营销中心相关业务
 *
 * @author wzd
 *
 */
require_model('tb/TbModel');
require_lib('util/oms_util', true);
require_lang('base');
require_lib("comm_util");
require_lib('util/web_util', true);

class ValueorderMainModel extends TbModel {
    public $pay_status=array(
        0=>'未支付',
        1=>'已支付',
    );
            
    
    function get_table() {
        return 'osp_valueorder_main';
    }

    function insert($data) {
        $check = $this->is_exists($data['order_code']);
        if ($check['status'] == 1) {
            return $this->format_ret('-1', '', '订购单号已存在！');
        }
        $data['val_orderdate'] = date('Y-m-d H:i:s');
        $ret = parent::insert($data);
        //日志
        load_model('market/ValueorderMainLogModel')->log(CTX()->get_session("user_code"), $ret['data'], $data['order_code'], $data['kh_id'], '新增订单', '未支付');
        return $ret;
    }

    private function is_exists($value, $field_name = 'order_code') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    function create_order_code() {
        $order_code = create_fast_bill_sn('ZZDGBH');
        return $order_code;
    }

    function get_by_id($id) {
        $data = $this->get_row(array('id' => $id));
        if ($data['status'] != 1) {
            return $data;
        }
        $data['data']['pay_status_name']=$this->pay_status[$data['data']['pay_status']];
        filter_fk_name($data['data'], array('kh_id|osp_kh', 'val_channel_id|org_channel', 'val_cp_id|osp_chanpin'));
        return $data;
    }
    
    function edit_order_action($params, $id) {
        $order = $this->get_by_id($id);
        $order_data = $order['data'];
        if ($order_data['pay_status'] != 0) {
            return $this->format_ret('-1','','订单已付款，不能编辑！');
        }
        $params['discount'] = empty($params['discount']) ? 0.00 : $params['discount'];
        $params['val_desc'] = empty($params['val_desc']) ? '' : str_replace(array("\r\n", "\r", "\n"), '', $params['val_desc']);
        if($params['discount']>$order_data['order_money']){
            return $this->format_ret('-1','','优惠金额超过订单金额！');
        }
        $params['server_money'] = $order_data['order_money'] - $params['discount'];
        $ret=$this->update($params, array('id'=>$id));
        if($ret['status']!=1){
             return $this->format_ret('-1','','更新失败！');
        }
        $mes='';
        if($params['discount']!=$order_data['discount']){
            $mes.='订单优惠金额由'.$order_data['discount'].'修改为'.$params['discount'];
        }
        if($params['val_desc']!=$order_data['val_desc']){
            $mes.='  订单描述由'.$order_data['val_desc'].'修改为'.$params['val_desc'];
        }
        if(!empty($mes)){
            //添加日志
         load_model('market/ValueorderMainLogModel')->log(CTX()->get_session("user_code"), $order_data['id'], $order_data['order_code'], $order_data['kh_id'], '编辑', '未支付',$mes);
        }
        return $this->format_ret('1','','更新成功！');
    }

    /**
     * 前端查询主单
     * @param type $params
     * @return type
     */
    function get_order_info($params) {
        $ret = $this->get_by_id($params['id']);
        return $ret;
    }

    /**
     * 前端操作日志
     */
    function get_log_by_page($filter) {
        $ret = load_model('market/ValueorderMainLogModel')->getLogByPage($filter);
        return $ret;
    }

    /**
     * 前端详情编辑
     * @param type $filter
     */
    function front_edit_order_action($params) {
        $order = $this->get_row(array('id' => $params['id']));
        if ($order['status'] != 1) {
            return $this->format_ret('-1', '', '单据不存在！');
        }
        if ($order['data']['pay_status'] != 0) {
            return $this->format_ret('-1', '', '该单据已支付，不能编辑！');
        }
        $params['val_desc'] = empty($params['val_desc']) ? '' : str_replace(array("\r\n", "\r", "\n"), '', $params['val_desc']);
        if ($order['data']['val_desc'] == $params['val_desc']) {
            return $this->format_ret('1', '', '');
        }
        $update_data = array(
            'val_desc' => $params['val_desc']
        );
        $ret = $this->update($update_data, array('id' => $params['id']));
        if ($ret['status'] != 1) {
            return $this->format_ret('-1', '', '编辑失败！');
        }
        //日志
        $mes = "订单备注由{$order['data']['val_desc']}修改为{$params['val_desc']}";
        load_model('market/ValueorderMainLogModel')->log($params['user_code'], $order['data']['id'], $order['data']['order_code'], $order['data']['kh_id'], '编辑', '未支付', $mes, 0);
        return $this->format_ret('1', '', '编辑成功！');
    }


    /**
     * 上传
     * @param $request
     * @param $upload_files
     * @return array
     */
    function import_upload($request, $upload_files) {
        $app['fmt'] = 'json';
        $files = array();
        $url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/";
        $fileInput = 'fileData';
        $dir = ROOT_PATH . 'weboperate/uploads/';
        $type = $_POST['type'];

        $isExceedSize = false;
        $files_name_arr = array($fileInput);
        $is_max = 0;
        $is_file_type = 0;
        $file_type = array('csv', 'xlsx', 'xls');
        $upload_max_filesize = 5242880;
        foreach ($files_name_arr as $k => $v) {
            $pic = $upload_files[$v];
            if (!isset($pic['tmp_name']) || empty($pic['tmp_name'])) {
                $is_max = 1;
                continue;
            }
            $file_ext = get_file_extension($pic['name']);
            if (!in_array($file_ext, $file_type)) {
                $is_file_type = 1;
                continue;
            }
            $isExceedSize = $pic['size'] > $upload_max_filesize;
            if (!$isExceedSize) {
                if (file_exists($dir . $pic['name'])) {
                    @unlink($dir . $pic['name']);
                }
                $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
                $result = move_uploaded_file($pic['tmp_name'], $dir . $new_file_name);
                if (true == $result && ('xlsx' == $file_ext || 'xls' == $file_ext)) {
                    $result = $this->excel2csv($dir . $new_file_name, $file_ext);
                    $new_file_name = str_replace('.' . $file_ext, '.csv', $new_file_name);
                }
            }
        }
        if ($is_max) {
            return array(
                'status' => 0,
                'type' => $type,
                'name' => $upload_files[$fileInput]['name'],
                'msg' => str_replace('{0}', substr(ini_get('upload_max_filesize'), 0, -1) * 1024, lang('upload_msg_maxSize'))
            );
        } else if ($is_file_type) {
            return array(
                'status' => 0,
                'type' => $type,
                'name' => $upload_files[$fileInput]['name'],
                'msg' => str_replace('{0}', implode(',', $file_type), lang('upload_msg_ext'))
            );
        } else if (!$isExceedSize && $result) {
            return array(
                'status' => 1,
                'type' => $type,
                'name' => $upload_files[$fileInput]['name'],
                'url' => $dir . $new_file_name
            );
        } else if ($isExceedSize) {
            return array(
                'status' => 0,
                'type' => $type,
                'msg' => str_replace('{0}', $upload_max_filesize / 1024, lang('upload_msg_maxSize'))
            );
        } else {
            return array(
                'status' => 0,
                'type' => $type,
                'msg' => "未知错误！" . $result
            );
        }
    }


    /**
     *
     * 方法名       excel2csv
     *
     * 功能描述     excel转换csv文件
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-24
     * @param       string $file
     * @param       string $extends
     *
     * @return      string $data
     */
    function excel2csv($file, $extends) {
        require_lib('PHPExcel', true);
        try {
            $time3 = time();
            $PHPExcel = PHPExcel_IOFactory::load($file);
            $time4 = time();
            $objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'CSV');
            $objWriter->setUseBOM(true);
            $objWriter->setPreCalculateFormulas(false);
            $objWriter->save(str_replace('.' . $extends, '.csv', $file));
            $time5 = time();
        } catch (Exception $e) {
            return false;
        }
        return true;
    }


    /**
     * 淡入增值服务
     * @param $file
     * @return mixed
     */
    function imoprt_detail($file) {
        //读出数据
        $this->read_csv_sku($file, $value_code_arr, $value_order_info);
        //验证客户
        $kh_id_arr = array_column($value_order_info, 'kh_id');
        $kh_id_arr = array_unique($kh_id_arr);
        $sql_value = array();
        $kh_id_str = $this->arr_to_in_sql_value($kh_id_arr, 'kh_id', $sql_value);
        $kh_sql = "SELECT kh_id,kh_name FROM osp_kehu WHERE kh_id IN({$kh_id_str})";
        $kh_ret = $this->db->get_all($kh_sql, $sql_value);
        $kh_info = array();
        foreach ($kh_ret as $kh_item) {
            $kh_info[$kh_item['kh_id']] = $kh_item;
        }
        //验证增值服务
        $sql_value=array();
        $value_code_str = $this->arr_to_in_sql_value($value_code_arr, 'value_code', $sql_value);
        $sql = "SELECT * FROM  osp_valueserver WHERE value_code IN({$value_code_str})";
        $value_server = $this->db->get_all($sql, $sql_value);
        $server_info=array();
        foreach ($value_server as $server) {
            $server_info[$server['value_code']] = $server;
        }
        $error_msg = array();//错误信息
        $kh_order_info = array();//组装最终导入信息
        foreach ($value_order_info as $order_info) {
            if (!isset($kh_info[$order_info['kh_id']])) {
                $error_msg[] = array('kh_id' => $order_info['kh_id'], 'value_code' => $order_info['value_code'], 'mes' => '客户在系统中不存在！');
                continue;
            }
            $kh_order_key = $order_info['kh_id'] . ',' . $order_info['value_code'];
            if (!array_key_exists($order_info['value_code'], $server_info)) {
                $error_msg[] = array('kh_id' => $order_info['kh_id'], 'value_code' => $order_info['value_code'], 'mes' => '增值服务在系统中不存在！');
                continue;
            }
            if(empty($order_info['pay_time'])){
                $error_msg[] = array('kh_id' => $order_info['kh_id'], 'value_code' => $order_info['value_code'], 'mes' => '请填写创建时间！');
                continue;
            }
            $server_data = $server_info[$order_info['value_code']];
            //判断是否过期
            $now_data = date('Y-m-d H:i:s');
            $end_data = date('Y-m-d H:i:s', strtotime("+" . $server_data['value_cycle'] . 'month', strtotime($order_info['pay_time'])));
            if ($now_data > $end_data) {
                $error_msg[] = array('kh_id' => $order_info['kh_id'], 'value_code' => $order_info['value_code'], 'mes' => '授权已到期！');
                continue;
            }
            if($server_data['value_publish_status']!=1){
                $error_msg[] = array('kh_id' => $order_info['kh_id'], 'value_code' => $order_info['value_code'], 'mes' => '增值服务未发布！');
                continue;
            }
            if($server_data['value_enable']!=1){
                $error_msg[] = array('kh_id' => $order_info['kh_id'], 'value_code' => $order_info['value_code'], 'mes' => '增值服务未启用！');
                continue;
            }

            //组装订单信息，相同客户组成新的订单
            $kh_order_info[$kh_order_key]['detail']= array(
                'val_kh_id' => $order_info['kh_id'],
                'val_cp_id' => $server_data['value_cp_id'],
                'val_serverid' => $server_data['value_id'],
                'val_standard_price' => $server_data['value_price'],
                'val_cheap_price' => $server_data['discount'],
                'val_actual_price' => $server_data['value_price']-$server_data['discount'],
                'val_hire_limit' => $server_data['value_cycle'],
                'server_num' => 1,
                'server_code' => $server_data['value_code'],
                'val_num' => $this->create_order_code(),
                'val_channel_id' => '86'//销售渠道默认自营
            );
            $kh_order_info[$kh_order_key]['pay_date'] = $order_info['pay_time'];
        }

        if (!empty($kh_order_info)) {
            //组装主单
            foreach ($kh_order_info as $key => &$order_detail) {
                $new_order_code = $this->create_order_code();
                $order_main = array(
                    'order_code' => $new_order_code,
                    'server_num' => $order_detail['detail']['server_num'],
                    'server_money' => $order_detail['detail']['val_actual_price'],
                    'kh_id' => $order_detail['detail']['val_kh_id'],
                    'server_remind' => 1,
                    'val_channel_id' => '86',//销售渠道默认自营
                    'order_money' => $order_detail['detail']['val_standard_price'],
                    'discount' => $order_detail['detail']['val_cheap_price'],
                    'val_cp_id' => '21',
                );
                $kh_order_info[$key]['order_main'] = $order_main;
                $order_detail['detail']['order_code'] = $new_order_code;
            }

            //插入
            foreach ($kh_order_info as $key => $order) {
                $kh_order_main = $order['order_main'];
                $kh_order_detail = $order['detail'];
                $this->begin_trans();
                $ret = load_model('market/ValueModel')->add_order_info($kh_order_main, array($kh_order_detail));
                if ($ret['status'] != 1) {
                    $this->rollback();
                    $error_msg[] = array('kh_id' => $kh_order_detail['val_kh_id'], 'value_code' => $kh_order_detail['server_code'], 'mes' => $ret['message']);
                    continue;
                }
                $pid = $ret['data'];
                $pay_date = $order['pay_date'];
                //付款
                $ret = load_model('market/ValueorderModel')->update_order_pay($pid, $pay_date);
                if ($ret['status'] != 1) {
                    $this->rollback();
                    $error_msg[] = array('kh_id' => $kh_order_detail['val_kh_id'], 'value_code' => $kh_order_detail['server_code'], 'mes' => $ret['message']);
                    continue;
                }
                $this->commit();
            }
        }

        $ret['status'] = '1';
        $ret['data'] = '';
        if (!empty($error_msg)) {
            $all_num = count($value_order_info);
            $err_num = count($error_msg);
            $success_num = $all_num - $err_num;
            $message = '导入成功' . $success_num;
            $ret['status'] = '-1';
            $message .= ',' . '失败数量:' . $err_num;
            $fail_top = array('客户id', '增值服务编码', '错误信息');
            $file_name = $this->create_import_fail_files($fail_top, $error_msg);
            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";

        }
        $ret['message'] = $message;
        return $ret;
    }

    /**
     * 读取数据
     * @param type $file
     * @param type $sku_arr
     * @param type $sku_num
     */
    function read_csv_sku($file, &$value_code_arr, &$value_order_info) {
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i > 0) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $value_code = trim($row[1]);
                    $value_code_arr[] = $value_code;
                    $value_order_info[$i]['kh_id'] = trim($row[0]);
                    $value_order_info[$i]['value_code'] = trim($row[1]);
                    $value_order_info[$i]['pay_time'] = trim($row[2]);
                }
            }
            $i++;
        }
        fclose($file);
    }

    /**
     * 处理特殊字符
     * @param type $row
     */
    private function tran_csv(&$row) {
        if (!empty($row)) {
            foreach ($row as &$val) {
                //   $val = iconv('gbk', 'utf-8', $val);
                $val = str_replace('"', '', $val);
            }
        }
    }


    function create_import_fail_files($fail_top, $error_msg) {
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($error_msg as $key => $val) {
            $val_data = array();
            foreach ($val as $item) {
                $val_data[] = $item;
            }
            $file_str .= implode(",", $val_data) . "\r\n";
        }
        $filename = md5("value_order" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

}
