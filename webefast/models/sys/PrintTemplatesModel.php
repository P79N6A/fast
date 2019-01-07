<?php

require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lib('apiclient/TaobaoClient');

class PrintTemplatesModel extends TbModel {

    protected $table = 'sys_print_templates';
    private $clothing_data = array('wbm_store_out_clothing','oms_waves_record_clothing');
    public $tpl_val = array('detail' => '');
    public $variables = array(
        'delivery' => array(
            'initial' => '始发地',
            'end_point' => '目的地',
            'package_center_name' => '集包地',
            'sender_shop_name' => '商店',
            'sender' => '联系人',
            'sender_mobile' => '联系手机', //
            'sender_phone' => '联系电话', //
            'sender_addr' => '地址(无省市区)',
            'sender_address' => '地址(含省市区)',
            'sender_zip' => '邮编',
            'sender_street' => '街道',
            'sender_district' => '区/县',
            'sender_city' => '市', //新增
            'sender_province' => '省', //新增
            //'sender_date' => '发货时间',//新增
            'sender_operprint' => '打单员', //新增
            'shopID' => '商家编号（当当）',
            'shopName' => '商家名称（当当）',
            'shop_id_jd' => '商家编号(京东)',
            'initial_code' => '始发地代码',
            'end_point_code' => '目的地代码',
            'initial_order' => '包裹序列',
            'destination_site' => '目的站点',
            'road_area' => '路区',
        ),
        'receiving' => array(
            'buyer_name' => '买家昵称',
            'receiver_name' => '收货人姓名',
            'receiver_mobile' => '收货人手机',
            'receiver_phone' => '收货人电话',
            'receiver_province' => '收货省',
            'receiver_city' => '收货市',
            'receiver_district' => '收货区/县',
            'receiver_street' => '收货街道',
            'receiver_addr' => '收货地址(无省市区)',
            'receiver_address' => '收货地址(含省市区)',
            'receiver_zip_code' => '收收货邮编',
            'receiver_top_address' => '收货目的地', //新增
            'sendGoodsTime' => '送货要求（当当）',
        ),
        'detail' => array(
            'detail:goods_name' => '商品名称',
            'detail:goods_code' => '商品编码',
            'detail:goods_short_name' => '商品简称',
            'detail:spec1_code' => '规格代码1',
            'detail:spec1_name' => '规格1',
            'detail:spec2_code' => '规格代码2',
            'detail:spec2_name' => '规格2',
            'detail:num' => '数量',
            'detail:weigh' => '商品重量',
            'detail:shelf_name' => '库位名称',
            'detail:shelf_code' => '库位',
            'detail:barcode' => '条形码',
            'detail:platform_spec' => '平台规格',
            'detail:sku_remark' => 'SKU备注',
            'detail:category_name' => '商品分类',
        ),
        'record' => array(
            'buyer_remark' => '买家留言',
            'seller_remark' => '商家留言',
            'store_remark' => '仓库留言',
            'order_remark' => '订单备注',
            'express_no' => '快递单号',
            'express_no_pack_no' => '快递单号(京东)',
            'deal_code' => '交易号',
            'sell_record_code' => '订单号',
            'record_time' => '下单时间',
            'pay_time' => '付款时间',
            'print_time' => '打印时间',
            'goods_num' => '商品总数量',
            'goods_weigh' => '订单理论重量',
            'real_weigh' => '订单实际重量',
            'sell_money' => '商品总金额',
            'payable_money' => '应收金额',
            'big_payable_money' => '应收金额（大写）',
            'paid_money' => '实付金额',
            //'goods_rebate_money' => '订单:优惠总金额',
            'express_money' => '运费',
            'sort_no' => '蓝位号',
            'waves_record_code' => '波次单号',
            'invoice_title' => '发票抬头',
            'invoice_content' => '发票内容',
            'express_name' => '配送方式',
            'pay_code' => '支付方式',
        ),
    );
    public $sf_rm_variables = array(
        'delivery' => array(
            'originCode' => '原寄地（顺丰）',
            'destCode' => '目的地（顺丰）',
            'j_custid' => '月结账号',
            'express_type' => '寄件类型',
            'daishou' => '代收货款金额',
            //'' => '保价声明值',
        ),
        'receiving' => array(
        ),
    );
    private $tpl_replace = array();
    public $template_val = array();
    public $flashFields = array(
        'params' => array(
            '单据编号' => 'record_code',
            '供应商代码' => 'supplier_code',
            '供应商名称' => 'supplier_name',
            '业务员代码' => 'user_code',
            '业务员名称' => 'user_name',
            '业务时间' => 'record_time',
            '仓库代码' => 'store_code',
            '仓库名称' => 'store_name',
            '折扣' => 'rebate',
            '关联单号' => 'relation_code',
            '交货时间' => 'refer_time',
            '付款期限' => 'pay_time',
            '品牌代码' => 'brand_code',
            '品牌名称' => 'brand_name',
            '数量' => 'num',
            '金额' => 'money',
            '金额(大写)' => 'money_up',
            '应付款' => 'finance_pay_money',
            '应付款(大写)' => 'finance_pay_money_up',
            '结余款' => 'finance_balance_money',
            '结余款(大写)' => 'finance_balance_money_up',
            '累计欠款' => 'finance_debts_money',
            '累计欠款(大写)' => 'finance_debts_money_up',
            '上次欠款' => 'last_debt',
            '上次欠款(大写)' => 'last_debt_up',
            '当前欠款' => 'now_debt',
            '当前欠款(大写)' => 'now_debt_up',
            '新增人' => 'is_add_person',
            '新增时间' => 'is_add_time',
            '修改人' => 'is_edit_person',
            '修改时间' => 'is_edit_time',
            '确认人' => 'is_sure_person',
            '确认时间' => 'is_sure_time',
            '入库人' => 'is_store_in_person',
            '入库时间' => 'is_store_in_time',
            '作废人' => 'is_cancel_person',
            '作废时间' => 'is_cancel_time',
            '记账人' => 'is_keep_accounts_person',
            '记账时间' => 'is_keep_accounts_time',
            '结算人' => 'is_settlement_person',
            '结算时间' => 'is_settlement_time',
            '备注' => 'remark',
        ),
        'table' => array(
            Array
            (
                '商品代码' => 'goods_code',
                '商品名称' => 'goods_name',
                '商品简称' => 'goods_short_name',
                '颜色代码' => 'color_code',
                '颜色名称' => 'color_name',
                '尺码代码' => 'size_code',
                '尺码名称' => 'size_name',
                '单价' => 'price',
                '参考价' => 'refer_price',
                '折扣' => 'rebate',
                '数量' => 'num',
                '重量' => 'real_weigh',
                '金额' => 'money',
                '品牌代码' => 'brand_code',
                '品牌名称' => 'brand_name',
                '品类代码' => 'sort_code',
                '品类名称' => 'sort_name',
                '分类代码' => 'category_code',
                '分类名称' => 'category_name',
                '季节代码' => 'season_code',
                '季节名称' => 'season_name',
                '系列代码' => 'series_code',
                '系列名称' => 'series_name',
                '单位代码' => 'unit_code',
                '单位名称' => 'unit_name',
                '年份' => 'year',
                '属性1' => 'property_value1',
                '属性2' => 'property_value2',
                '属性3' => 'property_value3',
                '属性4' => 'property_value4',
                '属性5' => 'property_value5',
                '属性6' => 'property_value6',
            )
        ),
    );
    //批发快递单打印配置信息
    public $pf_variables = array(
        //发货信息
        'delivery' => array(
            'sender' => '寄件人',
            'contact_person' => '联系人',
            'sender_phone' => '联系电话',
            'sender_province' => '省',
            'sender_city' => '市',
            'sender_district' => '区/县',
            'sender_street' => '街道',
            'sender_addr' => '地址(无省市区)',
            'sender_zip' => '邮编',
        ),
        //收货人信息
        'receiving' => array(
            'receiver_name' => '收货人姓名',
            'receiver_mobile' => '收货人手机',
            'receiver_phone' => '收货人电话',
            'receiver_province' => '收货省',
            'receiver_city' => '收货市',
            'receiver_district' => '收货区/县',
            'receiver_street' => '收货地址(无省市区)',
            'custom_name' => '分销商名称',
            'receiver_company_name' => '公司名称',
            'receiver_zip_code' => '收货邮编',
            'receiver_fax' => '传真',
        ),
        //商品信息
        'detail' => array(
            'detail:goods_name' => '商品名称',
            'detail:goods_code' => '商品编码',
            'detail:barcode' => '条形码',
            'detail:spec1_name' => '规格1名称',
            'detail:spec1_code' => '规格1代码',
            'detail:spec2_name' => '规格2名称',
            'detail:spec2_code' => '规格2代码',
            'detail:enotice_num' => '通知数量',
            'detail:num' => '出库数',
            'detail:shelf_name' => '库位',
            'detail:pf_price' => '批发单价',
            'detail:pf_money' => '批发金额',
        ),
        //订单信息
        'record' => array(
            'record_code' => '批发销货单号',
            'relation_code' => '批发通知单号',
            'store_name' => '仓库',
            'print_time' => '打印时间',
            'order_time' => '下单时间',
            'record_time' => '业务时间',
            'remark' => '备注',
            'num' => '商品总数量',
            'money' => '商品总金额',
        ),
    );

    public $settable_goods_info = array(
        'goods_name' => '商品名称',
        'goods_code' => '商品编码',
        'goods_short_name' => '商品简称',
        'goods_barcode' => '商品条形码',
        'spec1_name' => '规格1',
        'spec2_name' => '规格2',
        'num' => '数量',
        'shelf_code' => '商品库位',
        'platform_spec' => '平台规格',
        'sku_remark' => '条码备注',
        'sku_weight' => '商品理论重量',
        'category_name' => '商品分类'
    );
    //京东无界模板特殊字段
    public $jd_express_info = array(
        'express_log' => '',//快递公司logo
        'add_code'=>'目的地代码',
        'maker' => '大头笔',
        'category_name'=>'产品类型',
        'jbd_name'=>'集包地名称',
        'jbd_barcode'=>'1234',
        'service_info'=>'服务信息',
        'waybill_code'=>'1234',
        'express_qr_code'=>'',//快递公司二维码
        'pr_express_log'=>'',//留存联物流公司logo
        'sale_qr_code'=>'',//平台预留二维码
        'reserve_zone'=>'平台预留区',


        'express_type_cf'=>'电商专配',
        'card_no'=>'卡号',
        'order_money'=>'金额',
        'receiver_company_name'=>'',
        'sender_type'=>'定时派送',
        'sender_get_type'=>'自寄 自取',
        'c_recerver'=>'收件员',
        'sender_date'=>'寄件日期',
        'c_sender'=>'派件员',
        'other_add'=>'第三方地区',
        'month_account'=>'月结账号',
        'time_to_sender'=>'定时派送时间',
        'protect_fee'=>'保价费用',
        'state_price'=>'声明价值',
        'price_weight'=>'计费重量',
        'package_money'=>'包装费用',
        'express_contact_log'=>'',//联系方式图片
    );


    /**
     * new biz class
     * @param $model
     * @return null
     */
    public function newClass($model) {
        require_model($model);
        $arr = explode('/', $model);
        $cls = array_pop($arr);
        if (empty($cls)) {
            return null;
        }

        return new $cls();
    }

    /**
     * 根据条件查询数据
     * @param array $filter
     * @return array
     */
    function get_by_page($filter = array()) {
        $sql_values = array();
        $sql_main = " FROM {$this->table} ";

        if (isset($filter['print_templates_name']) && $filter['print_templates_name'] != '') {
            $sql_main .= " AND print_templates_name LIKE :print_templates_name";
            $sql_values[':print_templates_name'] = $filter['print_templates_name'] . '%';
        }

        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_template_id($template_code) {
        $templates_id = '';
        if (!empty($template_code)) {
            $templates_id = $this->db->get_value("select print_templates_id from sys_print_templates where print_templates_code='{$template_code}' ");
        }
        return $this->format_ret(1, $templates_id);
    }

    /**
     * @param $id
     * @return array
     */
    function get_templates_by_id($id) {

        $ret = $this->get_row(array('print_templates_id' => $id));

        if (in_array($ret['data']['is_buildin'],array(1,4,5))) {
            $ret['data']['template_body'] = !empty($ret['data']['template_body']) ? $ret['data']['template_body'] : $ret['data']['template_body_default'];

            $template_data = $this->set_print_templates($ret['data']['template_body']);
            if (!empty($ret['data']['template_body_replace'])) {
                $replace_arr = json_decode($ret['data']['template_body_replace'], true);
                foreach ($replace_arr as $key => $val) {//替换打印块
                    $template_data['body'] = str_replace($key, $val, $template_data['body']);
                }
            }
        } else if ($ret['data']['is_buildin'] == 2) {
            $template_data['template_body'] = json_decode($ret['data']['template_body'], TRUE);
            $template_data['template_val'] = json_decode($ret['data']['template_val'], TRUE);
        }

        $template_data['printer'] = $ret['data']['printer'];
        $template_data['is_buildin'] = $ret['data']['is_buildin'];
        return $this->format_ret(1, $template_data);
    }

    function get_row_by_id($id) {
        $data = $this->db->get_row('select * from sys_print_templates where print_templates_id=:print_templates_id  ', array(':print_templates_id' => $id));
        $data['company_name'] = oms_tb_val('base_express_company', 'company_name', array('company_code' => $data['company_code']));

        return $this->format_ret(1, $data);
    }
    
    function get_templates_by_code($code) {
        $ret = $this->get_row(array('print_templates_code' => $code));
        $ret['data']['template_body'] = !empty($ret['data']['template_body']) ? $ret['data']['template_body'] : $ret['data']['template_body_default'];

        $template_data = $this->set_print_templates($ret['data']['template_body']);
        if (!empty($ret['data']['template_body_replace'])) {
            $replace_arr = json_decode($ret['data']['template_body_replace'], true);
            foreach ($replace_arr as $key => $val) {//替换打印块
                $template_data['body'] = str_replace($key, $val, $template_data['body']);
            }
        }
        $template_data['printer'] = $ret['data']['printer'];
        $template_data['is_buildin'] = $ret['data']['is_buildin'];
        return $this->format_ret(1, $template_data);
    }

    /**
     * 转换打印项内容，默认打印项文本内容转换为示例文本
     * @param $print
     * @param $print_var
     * @param null $callback
     * @return string
     */
    public function printCastContent2($print, $print_var, $callback = null,$line) {
        $print = $this->_printCastContent($print, $print_var, $callback, 'LODOP.ADD_PRINT_TEXTA(', 5,$line);
        $re = array('body' => '', 'replace' => $this->tpl_replace);
        $print = $this->_printCastContent($print, $print_var, $callback, 'LODOP.ADD_PRINT_BARCODEA(', 6);
        $re['body'] = $this->_printCastContent($print, $print_var, $callback, 'LODOP.ADD_PRINT_BARCODE(', 7);
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
        $print = $this->_printCastContent($print, $print_var, $callback, 'LODOP.ADD_PRINT_BARCODEA(', 6);
        return $this->_printCastContent($print, $print_var, $callback, 'LODOP.ADD_PRINT_BARCODE(', 8);
    }
    /**
     * 解析clodop图片
     * @param type $template_body_default
     * @param type $print_var
     * @param type $callback
     * @return type
     */
    public function printCastContent_clodop_img ($template_body_default, $print_var, $callback = null) {
        return $this->_printCastContent($template_body_default, $print_var, $callback, 'LODOP.ADD_PRINT_BARCODE(', 8);
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
    private function _printCastContent($print, $print_var, $callback = null, $_prefix, $txtIndex,$line=null) {
        $print_var2 = $print_var;
        /* foreach ( $print_var as $_key=>$v ) {
          $_key = "c['" . $_key . "']";
          $print_var2[$_key] = $v;
          } */
        if(!empty($line)){//编辑保存模版数据需要如下处理
            $print_line_arr = explode('*', str_replace("\n","*",$print));
        }else{
            $print_line_arr = explode("\r\n", $print);
        }

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
            if($txtIndex == 7) { //保存条形码
                $_k = substr($_arr[5], 1, -1);
            }else if($txtIndex == 8){ //显示条形码
                array_pop($_arr);
                $_k = "690123456789";
            } else {
                $_k = substr($_arr[0], 1, -1);
                $_k = explode('-', $_k);
                $_k = $_k[0];
            }
            $loop = '';
            if ($callback == null) {
                if (strpos($_k, ":") === FALSE) {
                    if($txtIndex == 8) {
                        $_arr[] = $_k;
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
     * @param $name
     * @param $print_var
     * @return string
     */
    function getDetailStr($name, $print_var) {
        $list = explode(' ', $name);
        $str = '';
        foreach ($list as $i => $v) {
            $k = array_search($v, $print_var);
            if ($k !== false) {
                $str .= '" "+detail[\"' . $k . '\"]';
            }
        }
        $str .= ';';
        return $str;
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
            if(in_array('detail:shelf_code',$list) && in_array('detail:shelf_name',$list)){
                $key1 = array_search('detail:shelf_code',$list);
                $key2 = array_search('detail:shelf_name',$list);
                if($key1<$key2){
                    $cont_key = $list[$key1];
                    $list[$key1] = $list[$key2];
                    $list[$key2] = $cont_key;
                    unset($cont_key);
                }
            }
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
            if($txtIndex != 7) {
                $_arr[$txtIndex] = 'c["' . $name . '"]';
            } else {
                array_pop($_arr);
                $_arr[] = 'c["' . $name . '"]';
            }
        }
        return $loop;
    }

    /**
     * @param $template_body
     * @return array
     */
    function set_print_templates($template_body) {
        $template_top = '';
        $arr = array();
        $pattern = "/LODOP.SET_PRINT_PAPER\([^\);]*\);/i";
        preg_match($pattern, $template_body, $arr);
        if (!empty($arr)) {
            $template_top = $arr[0] . "\n";
            $template_body = preg_replace($pattern, '', $template_body);
        }


        $pattern = "/LODOP.SET_PRINT_PAGESIZE\([^\)]*\);/i";
        preg_match($pattern, $template_body, $arr);
        if (!empty($arr)) {
            $template_top .= $arr[0] . "\n";
        }
        $pattern = "/LODOP.PRINT_INITA\([^\)]*\);/i";
        preg_match($pattern, $template_body, $arr);
        if (!empty($arr)) {
            $template_body = preg_replace($pattern, '', $template_body);
        }
        return array('top' => $template_top, 'body' => $template_body);
    }

    /**
     * 复制一行数据
     * @param $id
     * @return mixed
     */
    function copy_from($id) {
        $data = $this->db->get_row("select * from sys_print_templates where print_templates_id = :id", array(':id' => $id));
        unset($data['print_templates_id']);
        $r = $this->insert($data);

        return $r['data'];
    }

    function get_shop_key_by_temp_id($temp_id) {
        $sql = "select rm_shop_code from base_express where  rm_id='{$temp_id}' AND rm_shop_code<>''";
        $shop_code = $this->db->get_row($sql);
        $ret_data = array('app_key' => '', 'user_id' => '');
        if (!empty($shop_code['rm_shop_code'])) {
            $ret_data = $this->get_shop_key_by_shop_code($shop_code['rm_shop_code']);
        }
        return $this->format_ret(1, $ret_data);
    }

    function get_shop_key_by_shop_code($shop_code) {
        $sql = "select api  from base_shop_api where shop_code='{$shop_code}'";
        $api = $this->db->get_value($sql);
        $ret_data = array('app_key' => '', 'user_id' => '');
        if (!empty($api)) {
            $data = json_decode($api, true);
            $ret_data['app_key'] = $data['app_key'];
            if (isset($data['user_id'])) {
                $ret_data['user_id'] = $data['user_id'];
            } else {
                $ret_data['user_id'] = $this->get_user_id($shop_code);
            }
        }
        return $ret_data;
    }

    function get_user_id($shop_code) {
        require_lib('apiclient/TaobaoClient');
        $client = new TaobaoClient($shop_code);
        return $client->get_api_user_id();
    }

    function set_cainiao_templates_val($templates_val) {
        foreach ($this->variables['detail'] as $detail_key => $name) {
            list($detail, $item) = explode(':', $detail_key);
            $templates_val = str_replace($name, $item, $templates_val);
        }
        return $templates_val;
    }

    function get_by_code($code) {
        $sql = "select * from {$this->table} where print_templates_code = '{$code}'";
        return $this->db->get_row($sql);
    }

    //走秀网发货模板通过走秀接口平台获取到的
    function get_zouxiu_templates($deal_code) {
        $res = "";
        $sql = "select shipOrderUrl from api_zouxiu_trade where orderId='{$deal_code}'";
        $zouxiu_trade = $this->db->getRow($sql);
        if (empty($zouxiu_trade)) {
            $record_cont = "发货单{$deal_code}在走秀网不存在";
        } else {
            $url = $zouxiu_trade['shipOrderUrl'];
            if (@fopen($url, 'r')) {
                $record_cont = "<img class='img' src='" . $url . "' alt='" . $url . "' height='1220' width='862' />";
            } else {
                $record_cont = "请联系走秀网人员设置访问发货单模板的ip白名单";
            }
        }
        if (!empty($record_cont)) {
            $res = "<html>
    		<body style='margin-top:5px;'>
    		$record_cont
    		</body>
    		</html>";
        }

        return $res;
    }

    function tprint_get_print_data($request) {
        $data = $this->get_by_code($request['print_templates_code']);
        $data['template_val'] = json_decode($data['template_val'], true);
        if (strpos($data['body'], '{#批次号}') !== false) {
            $request['is_lof'] = 1;
        }

        $conf_path = "tprint/" . $request['print_templates_code'];
        $conf = require_conf($conf_path);
        $print_data = load_model($conf['data_source']['model'])->$conf['data_source']['method']($request);
        
        //拣获单特殊处理
        if ($request['print_templates_code'] == 'oms_waves_goods') {
            //   $res = $this->get_zouxiu_templates($print_data['record']['deal_code']);
            $ret_data['tpl'] = 'tprint/source_print/oms_waves_goods';
            $ret_data['data'] = $print_data;
            return $ret_data;
        }

        //走秀网接口订单发货模板
        if ($print_data['record']['sale_channel_code'] == 'zouxiu' && 'sell_return' != $request['print_templates_code']) {
            $res = $this->get_zouxiu_templates($print_data['record']['deal_code']);
            $ret_data['body'] = $res;
            return $ret_data;
        }
        
        //唯品会接口订单发货模板
        if ($print_data['record']['sale_channel_code'] == 'weipinhui' && 'sell_return' != $request['print_templates_code']) {
            $ret_data['body'] = $print_data;
            $ret_data['tpl'] = 'tprint/source_print/weipinhui';
            return $ret_data;
        }
        $ret_data = $this->get_tprint_template($data,$print_data);
        $count = count($print_data['detail']);

        $page_size = 0;
        if ($data['template_val']['page_next_type'] == 1 && $count != 0) {
            $page_size = $data['template_val']['page_size'];
            if ($page_size == 0) {
                $page_size = 20;
            }
            $page = ceil($count / $page_size);
        }

        foreach ($conf['record'] as $record_name => $record_key) {
            $record_name = "{@{$record_name}}";
            $record_val = $print_data['record'][$record_key];
            if ($record_name == '{@页码}') {
                $record_val = '<span class="page"></page>';
            }

            $ret_data['body'] = str_replace($record_name, $record_val, $ret_data['body']);
        }
        if(in_array($data['print_templates_code'],$this->clothing_data)){

            //当使用服装行业特性需要特殊处理
            $detail_body = $this->set_tprint_detail_html_clothing($print_data['detail'], $conf['detail'], $ret_data['detail_body']);
        }else{
            $detail_body = $this->set_tprint_detail_html($print_data['detail'], $conf['detail'], $ret_data['detail_body']);
        }
        $ret_data['body'] = str_replace('<!--detail_list-->', $detail_body, $ret_data['body']);
        $ret_data['body'].='<input type="hidden" id="record_count" value="' . $count . '" >';
        $ret_data['body'].='<input type="hidden" id="page_size" value="' . $page_size . '" >';

        return $ret_data;
    }

    private function set_tprint_detail_html($detail_data, $detail_conf, $detail_html) {

        $new_conf = array();
        foreach ($detail_conf as $detail_name => $detail_key) {
            $new_conf[$detail_key] = "{#{$detail_name}}";
        }
        $html = "";
        foreach ($detail_data as $row) {
            $new_detail = $detail_html;
            foreach ($row as $key => $val) {
                $new_detail = str_replace($new_conf[$key], $val, $new_detail);
            }
            $html.=$new_detail;
        }
        return $html;
    }
    private function set_tprint_detail_html_clothing($detail_data, $detail_conf, $detail_html) {
        $new_conf = array();
        foreach ($detail_conf as $detail_name => $detail_key) {
            $new_conf[$detail_key] = "{#{$detail_name}}";
        }
        $size_layer = load_model('sys/ParamsModel')->get_param_set('size_layer');
        $size_layer_arr = json_decode($size_layer,true);
        $size_count = count($size_layer_arr[0]);
        for($i = 1;$i<=$size_count;$i++){
            $size_name = '尺码'.$i;
            $new_conf['size'.$i] = "{#{$size_name}}";
        }
        $html = "";
        foreach ($detail_data as $row) {
            $new_detail = $detail_html;
            foreach ($row as $key => $val) {
                $new_detail = str_replace($new_conf[$key], $val, $new_detail);
            }
            $spec2_name_arr = explode(',',$row['spec2_name']);
            $spec2_num_arr = array();
            foreach ($spec2_name_arr as $spec_key=>$spec_val){
                $arr = explode('<|>',$spec_val);
                $spec2_num_arr[$arr[0]] = isset($spec2_num_arr[$arr[0]]) ? $spec2_num_arr[$arr[0]]+$arr[1] : $arr[1];
            }
            $spec2_arr = array();
            foreach ($size_layer_arr as $val){
                foreach ($val as $key=>$value){
                    $keys = 'size'.($key+1);
                    if(array_key_exists($value,$spec2_num_arr)){
                        $spec2_arr[$keys] = $spec2_num_arr[$value];
                    }else{
                        if(!isset($spec2_arr[$keys])){
                            $spec2_arr[$keys] = '';
                        }
                    }
                }
            }
            foreach ($spec2_arr as $keys=>$values){
                $new_detail = str_replace($new_conf[$keys], $values, $new_detail);
            }
            $html.=$new_detail;
        }
        return $html;
    }

    function get_tprint_template($data, $type = 0) {
        $css = "assets/tprint/" . $data['template_val']['css'] . ".css";
        $ret_data['data'] = $data;
        if(in_array($data['print_templates_code'],$this->clothing_data)){
            $str_body = $this->get_size_table();
            $data['template_body'] = str_replace('{#表格}',$str_body,$data['template_body']);
            $data['template_body_replace'] = $this->get_size_table_detail();
            $print_data['detail'] = $data['template_body_replace'];
        }
        $ret_data['detail_body'] = htmlspecialchars_decode($data['template_body_replace']);
        $ret_data['body'] = htmlspecialchars_decode($data['template_body']);

        if ($type == 1) {
            $ret_data['body'] = str_replace('<!--detail_list-->', $ret_data['detail_body'], $ret_data['body']);
        }
        $ret_data['css'] = $css;
        return $ret_data;
    }
    //获取服装特性信息
    public function get_size_table(){
        //获取系统尺码信息
        $size_layer = load_model('sys/ParamsModel')->get_param_set('size_layer');
        $size_layer_arr = json_decode($size_layer,true);
        $row_num = count($size_layer_arr) ? count($size_layer_arr)+1 : 2;
        if(count($size_layer_arr)){
            $span_num = count($size_layer_arr[0]);
        }else{
            $span_num = 1;
        }
        $str_body = '<table id="table_1" class="table" border="0" cellpadding="0" cellspacing="0">
    <tr>
	<td style="width: 60px;" class="td_title" rowspan="'.$row_num.'">
	<div style="width: 60px;" class="td_column" id="column_th_69">商品名称</div>
	</td>
	<td style="width: 60px;" class="td_title" rowspan="'.$row_num.'">
	<div style="width: 60px; text-align: left;" class="td_column" id="column_th_70">商品编码</div>
	</td>
	<td style="width: 60px;" class="td_title" rowspan="'.$row_num.'">
	<div style="width: 60px; text-align: left;" class="td_column" id="column_th_71">分类</div>
	</td>
	<td style="width: 60px;" class="td_title" rowspan="'.$row_num.'">
	<div style="width: 60px; text-align: left;" class="td_column" id="column_th_72">颜色</div>
	</td>
	<td style="width: 240px;align:center" class="td_title" colspan="'.$span_num.'">
		<div style="width: 60px; text-align: left;" class="td_column" id="column_th_73">尺码</div>
	</td>
	<td style="width: 60px;" class="td_title" rowspan="'.$row_num.'">
	<div style="width: 60px;" class="td_column" id="column_th_74">库位</div>
	</td>
</tr><!--size--><!--detail_list--></table>';
        $str = '';
        foreach($size_layer_arr as $keys=>$value){
            $str .= '<tr>';
            $i = 0;
            $count = count($value) ? count($value) : 1;
            foreach ($value as $val){
                $i++;
                $str .= '<td><div style="width: 60px; text-align: left;" class="td_column">'.$val.'</div></td>';
            }
            $str .= '</tr>';
        }
        $str_body = str_replace('<!--size-->',$str,$str_body);
        return $str_body;
    }
    public function get_size_table_detail(){
        //获取系统尺码信息
        $size_layer = load_model('sys/ParamsModel')->get_param_set('size_layer');
        $size_layer_arr = json_decode($size_layer,true);
        $row_num = count($size_layer_arr) ? count($size_layer_arr)+1 : 2;
        if(count($size_layer_arr)){
            $span_num = count($size_layer_arr[0]);
        }else{
            $span_num = 1;
        }
        $width = 240/$span_num;
        $detail_body = '<tr>
                                <td style="width: 60px;" class="td_detail" >
                                    <div style="width: 60px;" class="td_column" id="column_td_69">{#商品名称}</div>
                                </td>
                                <td style="width: 60px;" class="td_detail" >
                                    <div style="width: 60px;" class="td_column" id="column_td_70">{#商品编码}</div>
                                </td>
                                <td style="width: 60px;" class="td_detail" >
                                    <div style="width: 60px; text-align: left;" class="td_column" id="column_td_71">{#分类}</div>
                                </td>
                                <td style="width: 60px;" class="td_detail">
                                    <div style="width: 60px; text-align: left;" class="td_column" id="column_td_72">{#颜色}</div>
                                </td>';
        if($span_num > 1){
            foreach ($size_layer_arr[0] as $key=>$value){
                $detail_body .= '<td style="width: '.$width.'px;"><div style="width: 60px; text-align: left;" class="td_column">{#尺码'.($key+1).'}</div></td>';
            }
        }else{
            $detail_body .= '<td style="width:240px"><div style="width: 60px; text-align: left;" class="td_column">{#尺码1}</div></td>';
        }
        $detail_body .= '<td style="width: 60px;" class="td_detail" >
            <div style="width: 60px; text-align: left;" class="td_column" id="column_td_74">{#库位名称(库位代码)}</div>
         </td></tr>';
        return $detail_body;
    }

    function is_oms_waves_goods() {
        $sql = "select  1 from sys_print_templates where print_templates_code=:print_templates_code ";
        $check = $this->db->get_value($sql, array(':print_templates_code' => 'oms_waves_goods'));
        return ($check > 0);
    }

    /**
     * @todo 获取云模板
     * @param string $shop_code 店铺代码
     */
    function get_cloud_express_tpl($shop_code) {
        $client = new TaobaoClient($shop_code);
        $ret = $client->cloudPrintMyStdtemplatesGet();
        if (empty($ret['cainiao_cloudprint_mystdtemplates_get_response']) || !isset($ret['cainiao_cloudprint_mystdtemplates_get_response']) || empty($ret['cainiao_cloudprint_mystdtemplates_get_response']['result']['datas'])) {
            return $this->format_ret(-1, '', '店铺不含有快递模版');
        }
        //处理返回数据
        $params = $this->handle_standard_array($ret, $shop_code);
        foreach($params as $k => $v){
            $print_templates_code_arr[$k] = $v['print_templates_code'];
        }
        //查找已存在的云打印模板，存在即更新，不存在就写入(应将sys)print_templates表添加唯一键)
        $print_templates_code_str = deal_array_with_quote($print_templates_code_arr);
        $filter_sql = "SELECT print_templates_code FROM sys_print_templates WHERE print_templates_code IN({$print_templates_code_str})";
        $exist_code = $this->db->get_all($filter_sql);
        if(!empty($exist_code)){
            foreach($exist_code as $value){
                $exist_code_arr[] = $value['print_templates_code'];
            }
            foreach ($params as $key => &$value){
                if(in_array($value['print_templates_code'], $exist_code_arr)){
                    $sql_name .= sprintf("WHEN '%s' THEN '%s' ", $value['print_templates_code'], $value['print_templates_name']);
                    $sql_body_default .= sprintf("WHEN '%s' THEN '%s' ", $value['print_templates_code'], $value['template_body_default']);
                    $sql_body .= sprintf("WHEN '%s' THEN '%s' ", $value['print_templates_code'], $value['template_body']);
                    $sql_val .= sprintf("WHEN '%s' THEN '%s' ", $value['print_templates_code'], $value['template_val']);
                    unset($params[$key]);
                }
            }
            $exist_code_str = deal_array_with_quote($exist_code_arr);
            $sql = "UPDATE sys_print_templates
                    SET print_templates_name = CASE print_templates_code {$sql_name} END,
                        template_body_default = CASE print_templates_code {$sql_body_default} END,
                        template_body = CASE print_templates_code {$sql_body} END,
                        template_val = CASE print_templates_code {$sql_val} END
                    WHERE
                        print_templates_code IN ({$exist_code_str})";
            $update_result = $this->db->query($sql);
        }
        //存在更新的模板，写入
        if(!empty($params)){
            $r = $this->insert_multi($params);
        }
        if(!empty($params) && !empty($exist_code) && $r['status'] == 1 && $update_result == TRUE){
            return $this->format_ret(1, '', '下载成功');
        }
        if(!empty($params) && empty($exist_code) && $r['status'] == 1){
            return $this->format_ret(1, '', '下载成功');
        }
        if(empty($params) && !empty($exist_code) && $update_result == TRUE){
            return $this->format_ret(1, '', '下载成功');
        }
        return $this->format_ret(-1, '', '下载失败');

    }

    /**
     * @todo 处理标准模板返回是数组数据
     * @param array $ret 标准模板数组数据
     * @param string $shop_code 店铺代码
     */
    public function handle_standard_array($ret, $shop_code){
        $params = array();
        $i = 0;
        $client = new TaobaoClient($shop_code);
        $original_template_result = $ret['cainiao_cloudprint_mystdtemplates_get_response']['result']['datas']['user_template_result'];
        foreach ($original_template_result as $template_result){
            foreach($template_result['user_std_templates']['user_template_do'] as $user_template){
                $params[$i]['print_templates_code'] = $template_result['cp_code'] . $user_template['user_std_template_id'];
                $params[$i]['print_templates_name'] = '云打印_' . $user_template['user_std_template_name'];
                $params[$i]['company_code'] = $template_result['cp_code'];
                $params[$i]['type'] = 1;
                $params[$i]['is_buildin'] = 3;
                $params[$i]['template_body_default'] = $user_template['user_std_template_url'];
                $customeArea = $client->cloudPrintCustomaresGet($user_template['user_std_template_id']);//标准区域
                if(!empty($customeArea['cainiao_cloudprint_customares_get_response'])){
                    $custome_result = $this->handle_custome_array($customeArea['cainiao_cloudprint_customares_get_response']['result']['datas']['custom_area_result']);
                    foreach ($custome_result as $result){
                        $params[$i]['template_body'] = $result['custom_area_url'];
                        $params[$i]['template_val'] = $result['key_name'];
                        $i++;
                    }
                }
            }
        }
        return $params;
    }

    /**
     * @todo 处理自定义返回是数组数据
     * @param array $ret 自定义区域数组数据
     */
    public function handle_custome_array($ret){
        $result = array();
        foreach ($ret as $key => $value){
            $result[$key]['custom_area_url'] = $value['custom_area_url'];
            foreach ($value['keys']['key_result'] as $i => $keys){
                $keys_arr[$i] = $this->handle_key_name($keys['key_name']);
            }
            $result[$key]['key_name'] = implode(',', $keys_arr);
        }
        return $result;
    }

    public function handle_key_name($key_name) {
        $reg_arr = array("_data.", "_config.", "_context.");
        foreach ($reg_arr as $reg_key){
            $ret = preg_replace("/{$reg_key}/i", '', $key_name);
            if($ret != $key_name){
                return $ret;
            }
        }
    }

    /**
     * @todo 更新云打印的默认打印机
     */
    public function update_printer($printer, $print_templates_id) {
        $data = array('printer' => $printer);
        $where = array('print_templates_id' => $print_templates_id);
        $ret = parent::update($data, $where);
        return $ret;
    }

    /**
     * @todo 获取快递配置的商品信息
     */
    public function get_goods_info_tpl($print_templates_id) {
        $sql = "SELECT print_templates_id, template_body_replace_key, template_body_replace FROM {$this->table} WHERE print_templates_id = :print_templates_id";
        $sql_value = array(":print_templates_id" => $print_templates_id);
        $ret = $this->db->get_row($sql, $sql_value);
        $template_body_replace_key = json_decode($ret['template_body_replace_key'], true);
        foreach ($this->settable_goods_info as $key => $value){
            $ret['template_body_replace'] =  preg_replace("/$key/", $value, $ret['template_body_replace']);
        }
        return array(
            "print_templates_id" => $ret['print_templates_id'],
            "single_goods_a_line" => $template_body_replace_key['single_goods_a_line'],
            'selected_goods_info' => $template_body_replace_key['goods_info'],
            'input_goods_info' => $ret['template_body_replace'],
            'settable_goods_info' => $this->settable_goods_info
        );
    }

    /**
     * @todo 更新快递配置的商品信息
     */
    public function save_goods_info($param) {
        if(empty($param['selected_goods_info'])){
            return $this->format_ret(-1, '', '未选中商品信息');
        }
        preg_match_all("/\{(.*?)\}/",$param['input_goods_info'], $result);
        $setted_goods_info = $result[1];
        $settable_goods_info = array_values($this->settable_goods_info);
        $filp_settable_goods_info = array_flip($this->settable_goods_info);
        $selected_goods_info_arr = explode(',', $param['selected_goods_info']);
        foreach ($setted_goods_info as $goods_info_name){
            if(count($selected_goods_info_arr) != count($setted_goods_info)){
                return $this->format_ret(-1, '', '预览区商品信息与选择区商品信息不匹配');
            }
            if(!in_array($goods_info_name, $settable_goods_info)){
                return $this->format_ret(-1, '', '预览区商品信息配置有误，请勿删除已选中的商品信息');
            }
            if(!in_array($filp_settable_goods_info[$goods_info_name], $selected_goods_info_arr)){
                return $this->format_ret(-1, '', '预览区商品信息配置有误，请勿删除已选中的商品信息');
            }
        }
        foreach ($filp_settable_goods_info as $key => $value){
            $param['input_goods_info'] =  preg_replace("/$key/", $value, $param['input_goods_info']);
        }
        $template_body_replace_key = json_encode(array("single_goods_a_line" => $param['single_goods_a_line'], 'goods_info' => $param['selected_goods_info']));
        $template_body_replace = $param['input_goods_info'];
        $data = array("template_body_replace_key" => $template_body_replace_key, "template_body_replace" => $template_body_replace);
        $where = array('print_templates_id' => $param['print_templates_id']);
        $ret = parent::update($data, $where);
        if($ret['status'] == 1){
            return $this->format_ret(1, '', '设置商品信息成功');
        }else{
            return $this->format_ret(-1, '', '设置商品信息失败');
        }
    }
    function get_printer($template){
        $sql = "select printer from {$this->table} where print_templates_code=:template";
        $data = $this->db->get_row($sql,array(':template'=>$template));
        if(empty($data['printer'])){
            return $this->format_ret(-1, $data);
        }
        if($data['printer'] == '无'){
            return $this->format_ret(-1, $data);
        }
        return $this->format_ret(1, $data);
    }
    function save_printer($clodop_printer,$print_templates_code){
       $ret =  parent::update(array('printer'=>$clodop_printer),array('print_templates_code'=>$print_templates_code));
       return $ret;
    }
}
