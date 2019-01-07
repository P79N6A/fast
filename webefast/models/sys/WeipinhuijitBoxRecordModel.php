<?php

/**
 * 箱唛打印
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('stm');

class WeipinhuijitBoxRecordModel extends TbModel {

    protected $table = 'sys_print_templates';
    private $tpl_replace = array();
    public $tpl_val = array('detail' => '');
    public $variables = array(
        'record' => array(
            'brand_name' => '商品品牌',
            'warehouse_name' => '送货仓库',
            'storage_no' => '入库单号',
            'record_code' => '箱号',
            'arrival_time' => '要求到货时间',
            'carrier_name' => '承运商',
            'delivery_no' => '运单号',
            'box_order' => '箱序号',
        ),
        'barcode_info' => array(
            'storage_no_code' => '入库单号条形码',
            'record_code_code' => '箱号条形码',
            'delivery_no_code' => '运单号条形码',
            'arrival_time_code' => '要求到货时间条形码',
        ),
    );
    public $variables_general = array(
        'record' => array(
            'record_code' => '批发单号',
            'custom_name' => '分销商',
            'box_order' => '箱序号',
            'num' => '商品总数量',
            'sku_num' => 'SKU种类数',
            'remark' => '备注',
        ),
        'delivery_info' => array(
            'destination_city' => '目的城市',
            'name' => '联系人',
            'tel' => '联系电话',
//            'arrival_time_code' => '省',
//            'arrival_time_code' => '市',
//            'arrival_time_code' => '区/县',
//            'arrival_time_code' => '街道',
            'address' => '地址(含省市区)',
        ),
    );

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_values = array();
        //唯品会jit箱唛模版 type 为10
        $sql_main = "FROM {$this->table} t WHERE t.type = 10";
        $select = 't.*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $is_buildin_arr = array('0' => '自定义', '1' => '系统内置', '2' => '云栈');
        foreach ($data['data'] as &$val) {
            $val['is_buildin_name'] = $is_buildin_arr[$val['is_buildin']];
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_row_by_id($id) {
        $data = $this->db->get_row('select * from sys_print_templates where print_templates_id=:print_templates_id  ', array(':print_templates_id' => $id));
        return $this->format_ret(1, $data);
    }

    /**
     * 转换打印项内容，默认打印项文本内容转换为示例文本
     * @param $print
     * @param $print_var
     * @param null $callback
     * @return string
     */
    public function printCastContent2($print, $print_var, $callback = null) {
        $print = $this->_printCastContent($print, $print_var, $callback, 'LODOP.ADD_PRINT_TEXTA(', 5);
        $re = array('body' => '', 'replace' => $this->tpl_replace);
        $re['body'] = $this->_printCastContent($print, $print_var, $callback, 'LODOP.ADD_PRINT_BARCODE(', 5);
        return $re;
    }

    /**
     * 转换打印项内容，默认打印项文本内容转换为示例文本
     * @param $print
     * @param $print_var
     * @param null $callback
     * @return string
     */
    public function printCastContent($print, $print_var, $callback = null) {
        $print = $this->_printCastContent($print, $print_var, $callback, 'LODOP.ADD_PRINT_TEXTA(', 5);
        return $this->_printCastContent($print, $print_var, $callback, 'LODOP.ADD_PRINT_BARCODE(', 5);
    }

    /**
     * 转换打印项内容，默认打印项文本内容转换为示例文本
     * @param $print
     * @param $print_var
     * @param null $callback
     * @param $_prefix
     * @param $txtIndex
     * @return string
     */
    private function _printCastContent($print, $print_var, $callback = null, $_prefix, $txtIndex) {

        $print_var2 = $print_var;
        $print_line_arr = explode("\r\n", $print);
        $print_arr = array();
        $this->tpl_replace = array();
        //$_prefix = 'LODOP.ADD_PRINT_TEXTA(';
        $_suffix = ");";
        $_start = strlen($_prefix);
        $_end = - strlen($_suffix);
        foreach ($print_line_arr as $line_key => $line_value) {
            if (empty($line_value) || strpos($line_value, $_prefix) === false || strpos($line_value, '_txt:') !== false // 自定义文本
            ) {
                $print_arr[] = $line_value;
                continue;
            }
            $_arr = explode(',', substr($line_value, $_start, $_end));
            $_k = substr($_arr[0], 1, -1);
            $_k = explode('-', $_k);
            $_k = $_k[0];
            $loop = '';
            if ($callback == null) {
                if (strpos($_k, ":") === FALSE) {
                    if ($_prefix == 'LODOP.ADD_PRINT_BARCODE(') {
                        if ($_arr[$txtIndex] == 'c["' . 'storage_no_code' . '"]') {
                            $_arr[$txtIndex] = '"' . '入库单号条形码' . '"';
                        } elseif ($_arr[$txtIndex] == 'c["' . 'record_code_code' . '"]') {
                            $_arr[$txtIndex] = '"' . '箱号条形码' . '"';
                        } elseif ($_arr[$txtIndex] == 'c["' . 'delivery_no_code' . '"]') {
                            $_arr[$txtIndex] = '"' . '运单号条形码' . '"';
                        } elseif ($_arr[$txtIndex] == 'c["' . 'arrival_time_code' . '"]') {
                            $_arr[$txtIndex] = '"' . '要求到货时间条形码' . '"';
                        }
                    } else {
                        $_arr[$txtIndex] = !isset($print_var2[$_k]) ? "'{$_k}'" : '"' . $print_var2[$_k] . '"';
                    }
                }
            } else {
                $loop = $this->$callback($_arr, $_k, $print_var, $txtIndex);
            }

            if ($loop != "") {
                $print_arr[] = $line_value;
                $new_value[] = 'var ' . $loop . 'str="";';
                $new_value[] = 'for(var i in c["' . $loop . '"]){';
                $new_value[] = 'var ' . $loop . '=c["' . $loop . '"][i];';
                $new_value[] = $loop . 'str+=' . $_arr[$txtIndex];
                //$new_value[] =  $loop.'str+='.$this->getDetailStr($_arr[$txtIndex], $print_var);
                if (isset($this->template_val['deteil_row']) && $this->template_val['deteil_row'] == 1) {
                    $new_value[] = $loop . 'str+="\n"';
                }
                $new_value[] = '}';
                $_arr[$txtIndex] = $loop . 'str';
                $new_value[] = $_prefix . implode(',', $_arr) . $_suffix;
                $this->tpl_replace[$line_value] = implode("\n", $new_value);
            } else {
                $print_arr[] = $_prefix . implode(',', $_arr) . $_suffix;
            }
        }
        //die;
        return implode("\r\n", $print_arr);
    }

    /**
     * 将打印项文本内容转换为变量
     * @param array $_arr 单行打印代码
     * @param $name
     * @return string
     */
    private function printCastContent_ToVar(&$_arr, $name, $print_var, $txtIndex) {
        $loop = '';
        if (strpos($name, "|") != FALSE) {
            $list = explode("|", $name);
            $r_arr = array();
            foreach ($list as $val) {
                if (strpos($val, ":") != FALSE) {
                    list($k, $v) = explode(":", $val);
                    $loop = $k;
                    $r_arr[$print_var[$val]] = '"+' . $k . '["' . $v . '"]+" ';
                } else {
                    $r_arr[$print_var[$val]] = '"+' . $k . '["' . $val . '"]+" ';
                }
            }
            $namestr = $_arr[$txtIndex];
            foreach ($r_arr as $k_name => $k_v) {
                $namestr = str_replace($k_name, $k_v, $namestr);
            }
            $_arr[$txtIndex] = $namestr;
        } else {
            if ($_arr[$txtIndex] == '"' . "入库单号条形码" . '"') {
                $_arr[$txtIndex] = 'c["storage_no_code"]';
            } elseif ($_arr[$txtIndex] == '"' . "箱号条形码" . '"') {
                $_arr[$txtIndex] = 'c["record_code_code"]';
            } elseif ($_arr[$txtIndex] == '"' . "运单号条形码" . '"') {
                $_arr[$txtIndex] = 'c["delivery_no_code"]';
            } elseif ($_arr[$txtIndex] == '"' . "要求到货时间条形码" . '"') {
                $_arr[$txtIndex] = 'c["arrival_time_code"]';
            } elseif ($_arr[$txtIndex] == 'c["' . 'storage_no_code' . '"]') {
                $_arr[$txtIndex] = '"' . '入库单号条形码' . '"';
            } elseif ($_arr[$txtIndex] == 'c["' . 'record_code_code' . '"]') {
                $_arr[$txtIndex] = '"' . '箱号条形码' . '"';
            } elseif ($_arr[$txtIndex] == 'c["' . 'delivery_no_code' . '"]') {
                $_arr[$txtIndex] = '"' . '运单号条形码' . '"';
            } elseif ($_arr[$txtIndex] == 'c["' . 'arrival_time_code' . '"]') {
                $_arr[$txtIndex] = '"' . '要求到货时间条形码' . '"';
            } else {
                $_arr[$txtIndex] = 'c["' . $name . '"]';
            }
        }
        return $loop;
    }

    private function is_exists($value, $field_name = 'print_templates_name') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    /**
     * 添加新纪录
     */
    function insert($supplier) {
        $status = $this->valid($supplier);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        //        $ret = $this->is_exists($supplier['supplier_code']);
        //        if ($ret['status'] > 0 && !empty($ret['data'])) {
        //            return $this->format_ret('sms_supplier_error_unique_code');
        //        }

        $ret = $this->is_exists($supplier['print_templates_name'], 'print_templates_name');
        if ($ret['status'] > 0 && !empty($ret['data'])) {
            return $this->format_ret('express_tpl_error_unique_name');
        }

        return parent::insert($supplier);
    }

    /**
     * 删除记录
     */
    function delete($id) {
        $ret = parent :: delete(array('print_templates_id' => $id));
        return $ret;
    }

    function update_active($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        $ret = parent::update(array('is_active' => $active), array('id' => $id));
        return $ret;
    }

}
