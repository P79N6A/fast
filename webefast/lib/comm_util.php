<?php

/**
 * 快速产生单据编号
 *
 * @access  public
 * @param   string   $bill_type   单据类型
 * @param   int      $length      流水号长度
 * @return  string   返回单据编号
 */
function create_fast_bill_sn($bill_type, $length = 4) {
    static $arr_type = array('spjhd', 'sdfpd', 'sddbd', 'sms_dj', 'cgjhd', 'check', '', 'return');
    if (!in_array($bill_type, $arr_type))
        return false;

    $prefix = '';
    switch ($bill_type) {
        default:
            return false;
            break;
        case 'spjhd':
            $prefix = 'JH';
            break;
        case 'sddbd':
            $prefix = 'db';
            break;
        case 'sdfpd':
            $prefix = 'FP';
            break;
        case 'enter':
            $prefix = 'RK';
            break;
        case 'return':
            $prefix = 'TH';
            break;
        case 'check':
            $prefix = 'PD';
            break;
        case 'loss':
            $prefix = 'YK';
            break;
        case 'rectify':
            $prefix = 'TZ';
            break;
        case 'balance':
            $prefix = 'JS';
            break;
        case 'sms_dj':
            $prefix = 'DX';
            break;
        case 'cgjhd':
            $prefix = 'CGJH';
            break;
    }
    return create_bill_sn($bill_type, $prefix . date('Ymd'), $length);
}

/**
 * 自动补0 （6位）
 * @param   $num	传入数字
 * @param   $count	需要位数 （默认6位）
 */
//by hhl 默认补6位 改成3位
function add_zero($num, $count = 3) {
    if (!$num) {
        return "";
    }
    $add_str = "";
    $num_count = strlen($num);
    $add_zero_count = $count - $num_count;
    if ($add_zero_count <= 0) {
        return $num;
    }
    if ($add_zero_count) {
        for ($ii = 0; $ii < $add_zero_count; $ii++) {
            $add_str .= "0";
        }
    }
    return $add_str . $num;
}

/**
 * 单据编号生成
 *
 * @access  public
 * @param   string   $bill_type   单据类型
 * @param   string   $prefix      单据前缀符
 * @param   int      $length      流水号长度
 * @return  string   返回单据编号
 */
function create_bill_sn($bill_type, $prefix = '', $length = 4) {
    $sn = empty($prefix) ? generate_rand($length) : ($prefix . generate_rand($length));
    return $sn;
}

/**
 * 随机流水号生成
 *
 * @access  public
 * @param   int   $length 随机编号长度
 * @return  string
 */
function generate_rand($length = 4) {
    $chars = '0123456789';

    for ($i = 0, $count = strlen($chars); $i < $count; $i++) {
        $arr[$i] = $chars[$i];
    }

    mt_srand((double) microtime() * 1000000);
    shuffle($arr);
    return substr(implode('', $arr), 0, $length);
}

/**
 * 获取打印类型名称
 * @param $print_data_type
 * @return mixed
 */
function get_print_data_type_name($print_data_type) {
    $print_data_type_arr = array(
        'goods_barcode' => '商品条码',
        'goods_barcode_hangtag' => '商品吊牌',
        'base_color' => '颜色',
        'pur_purchaser_record' => '采购进货订单',
        'pur_purchaser_record_barcode' => '采购进货订单条码',
        'pur_store_in_record' => '采购入库单',
        'pur_return_record' => '采购退货单',
        'wbm_wholesale_record' => '批发销货订单',
        'wbm_store_out_record' => '批发销货单',
        'wbm_return_record' => '批发退货单',
        'dim_allocate_order_record' => '商店配货订单',
        'dim_allocate_record' => '商店配货单',
        'dim_return_record' => '商店退货单',
        'rbm_sale_record' => '零售销货单',
        'rbm_return_record' => '零售退货单',
        'stm_store_shift_record' => '商品移仓单',
        'stm_take_stock_record' => '库存盘点单',
        'stm_stock_adjust_record' => '库存调整单',
        'stm_stock_lock_record' => '库存锁定单',
        'rbm_receipt_record' => '小票',
        'pur_store_in_record_barcode' => '采购入库单条码',
        'order_pick_up_record' => '拣货单',
        'order_sell_record' => '订单',
        'dim_allocate_order_record_barcode' => '商店配货订单条码',
        'dim_allocate_record_barcode' => '商店配货单条码',
        'pur_store_in_record_barcode_hangtag' => '采购入库单吊牌',
        'acc_buy_pay_record' => '进货付款单',
        'acc_sell_receivable_record' => '销货收款单'
    );
    return $print_data_type_arr[$print_data_type];
}

/**
 * 获取打印纸张名称
 * @param $page_style
 * @return string
 */
function get_page_style_name($page_style) {
    if ('custom_pager' == $page_style)
        return '自定义纸张';
    else
        return $page_style;
}

function not_null(& $key) {
    if (is_array($key))
        return TRUE;
    return (isset($key) && '' !== trim($key) && !is_null($key));
}

/**
 * 格式化数字
 * @param $value
 * @return string
 */
function format_money($value) {
    return sprintf("%.2f", $value);
}

//格式化时间格式
function format_day_time($parameter) {
    $time = strtotime($parameter);
    if ($time && $time > 0) {
        return date("Y-m-d", $time);
    }
}

function read_csv($file, $start_line, &$data = array(), &$head = array(), $key_arr = array()) {
    $file = fopen($file, "r");
    $i = 0;
    $header = array();
    $data = array();
    while (!feof($file)) {
        if ($i >= $start_line) {
            $row = fgetcsv($file);
            if (!empty($key_arr)) {
                foreach ($key_arr as $k => $n_k) {
                    $n_row[$n_k] = $row[$k];
                }
                $data[] = $n_row;
            } else {
                $data[] = $row;
            }
        } else {
            $header[] = fgetcsv($file);
        }
        $i++;
    }
    fclose($file);
}

//截取字符串
function left($str, $len, $charset = "utf-8") {
    //如果截取长度小于等于0，则返回空
    if (!is_numeric($len) or $len <= 0) {
        return "";
    }

    //如果截取长度大于总字符串长度，则直接返回当前字符串
    $sLen = strlen($str);
    if ($len >= $sLen) {
        return $str;
    }

    //判断使用什么编码，默认为utf-8
    if (strtolower($charset) == "utf-8") {
        $len_step = 3; //如果是utf-8编码，则中文字符长度为3
    } else {
        $len_step = 2; //如果是gb2312或big5编码，则中文字符长度为2
    }

    //执行截取操作
    $len_i = 0;
    //初始化计数当前已截取的字符串个数，此值为字符串的个数值（非字节数）
    $substr_len = 0; //初始化应该要截取的总字节数

    for ($i = 0; $i < $sLen; $i++) {
        if ($len_i >= $len)
            break; //总截取$len个字符串后，停止循环
//判断，如果是中文字符串，则当前总字节数加上相应编码的中文字符长度
        if (ord(substr($str, $i, 1)) > 0xa0) {
            $i += $len_step - 1;
            $substr_len += $len_step;
        } else { //否则，为英文字符，加1个字节
            $substr_len ++;
        }
        $len_i ++;
    }
    $result_str = substr($str, 0, $substr_len);
    return $result_str;
}

/**
 *
 * 方法名       get_file_extension
 *
 * 功能描述     获取文件扩展名
 *
 * @author      BaiSon PHP R&D
 * @date        2015-07-23
 * @param       string $file
 *
 * @return      string $file_ext_name [扩展名]
 */
function get_file_extension($file) {
    $temp_arr = explode('.', $file);
    $file_ext = array_pop($temp_arr);
    $file_ext = trim($file_ext);
    $file_ext = strtolower($file_ext);
    return $file_ext;
}

/**
 *
 * 方法名       detect_encoding
 *
 * 功能描述     检测文件编码
 *
 * @author      BaiSon PHP R&D
 * @date        2015-07-29
 * @param       string $file
 *
 * @return      string $file_ext_name [扩展名]
 */
function detect_encoding($file) {
    $list = array('GBK', 'UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1');
    $str = file_get_contents($file);
    foreach ($list as $item) {
        $tmp = mb_convert_encoding($str, $item, $item);
        if (md5($tmp) == md5($str)) {
            return $item;
        }
    }
    return null;
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

function create_import_fail_files($msg, $name) {
    $fail_top = array('错误信息');
    $file_str = implode(",", $fail_top) . "\n";
    $fail_data = explode(",", $msg);
    foreach ($fail_data as $key => $val) {
        $file_str .= $val . "\r\n";
    }
    $filename = md5($name . time());
    $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
    file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
    //var_dump($file_str);die;
    return $filename;
}

function import_upload() {
    set_time_limit(0);
    $files = array();
    $url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/";

    $fileInput = 'fileData';
    $dir = ROOT_PATH . 'webefast/uploads/';
    $type = $_POST['type'];

    $isExceedSize = false;
    $files_name_arr = array($fileInput);
    $is_max = 0;
    $is_file_type = 0;
    $file_type = array('csv', 'xlsx', 'xls');
    $upload_max_filesize = 2097152;
    foreach ($files_name_arr as $k => $v) {
        $pic = $_FILES[$v];
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
            // 解决中文文件名乱码问题
            $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
            $result = move_uploaded_file($pic['tmp_name'], $dir . $new_file_name);
            if (true == $result && ('xlsx' == $file_ext || 'xls' == $file_ext)) {
                $result = excel2csv($dir . $new_file_name, $file_ext);
                $new_file_name = str_replace('.' . $file_ext, '.csv', $new_file_name);
            }
        }
    }

    if ($is_max) {
        $ret = array(
            'status' => 0,
            'type' => $type,
            'name' => $_FILES[$fileInput]['name'],
            'msg' => str_replace('{0}', substr(ini_get('upload_max_filesize'), 0, -1) * 1024, lang('upload_msg_maxSize'))
        );
    } else if ($is_file_type) {
        $ret = array(
            'status' => 0,
            'type' => $type,
            'name' => $_FILES[$fileInput]['name'],
            'msg' => str_replace('{0}', implode(',', $file_type), lang('upload_msg_ext'))
        );
    } else if (!$isExceedSize && $result) {
        $ret = array(
            'status' => 1,
            'type' => $type,
            'name' => $_FILES[$fileInput]['name'],
            'url' => $dir . $new_file_name
        );
    } else if ($isExceedSize) {
        $ret = array(
            'status' => 0,
            'type' => $type,
            'msg' => str_replace('{0}', $upload_max_filesize / 1024, lang('upload_msg_maxSize'))
        );
    } else {
        $ret = array(
            'status' => 0,
            'type' => $type,
            'msg' => "未知错误！" . $result
        );
    }
    return $ret;
}

/**
 * 去除特殊字符
 * @param array/string $data 数组或字符串
 * @param array $special 特殊字符
 * @return array/string 处理结果
 */
function _deal_special_char(&$data, $special = array()) {
    if (empty($special)) {
        $special = array('@', '#', '$', '%', '^', '*', '<', '>', '{', '}', '(', ')', '[', ']', '=', '+', '~', '`');
    }
    if (!is_array($data)) {
        return str_replace($special, '', $data);
    }
    while (list($key, $value) = each($data)) {
        if (is_array($value)) {
            $data[$key] = deal_special_char($value);
        } else {
            $data[$key] = str_replace($special, '', $value);
        }
    }
    return $data;
}

/**
 * 去除特殊字符
 * @param array/string $data 数组或字符串
 * @param array $special 特殊字符
 * @return array/string 处理结果
 */
function deal_special_char(&$data, $filter, $special = array()) {
    if (empty($special)) {
        $special = array('@', '#', '$', '%', '^', '*', '<', '>', '{', '}', '(', ')', '[', ']', '=', '+', '~', '`', '&');
    }
    if (!is_array($data)) {
        return str_replace($special, '', $data);
    }
    foreach ($data as $k => &$v) {
        if (!in_array($k, $filter)) {
            continue;
        }
        $v = str_replace($special, '', $v);
    }
    return $data;
}

function server_ip() {
    $ip = '';
    if (!CTX()->is_in_cli()) {
        $ip = CTX()->get_session('server_ip', TRUE);
    }

    if (empty($ip)) {
        $ifconfig = shell_exec('/sbin/ifconfig');
        $match = array();
        preg_match_all('/addr:([\d\.]+)/', $ifconfig, $match);
        $ips = $match[1];
        for ($i = 0; $i < count($ips); $i++) {
            if (!preg_match("/^(10|172\.16|192\.168)\./", $ips[$i])) {
                $ip = $ips[$i];
                break;
            }
        }
        $ip = trim($ip);
        if (empty($ip)) {
            $ip = gethostbyname($_SERVER["SERVER_NAME"]);
        }
        CTX()->set_session('server_ip', $ip, TRUE);
    }
    return $ip;
}

/**
 * 正则校验数值
 * @param string $value 数值
 * @param string $type 类型
 * @return boolean 校验成功返回TRUE，失败返回FALSE
 */
function check_value_valid($value, $type) {
    $pattern = array(
        'number' => '/^([1-9]\d*|0)?(\.\d+)?$/', //正数+0
        'pint' => '/^[1-9]\d*$/', //正整数
    );
    if (preg_match($pattern[$type], $value)) {
        return TRUE;
    }
    return FALSE;
}
