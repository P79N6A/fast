<?php
require_model('tb/TbModel');
class SendTemplatesModel extends TbModel
{
    protected $table = 'sys_print_templates';
    public  $tpl_val = array('detail'=>'');
    public $variables = array(
        'record' => array(
            'express_no'=> array('title'=>'快递单号','label'=>'快递单号:[$data]'),
            'deal_code_list' =>array('title'=>'交易号','label'=>'交易号:[$data]'),
            'sell_record_code' =>array('title'=>'系统订单号','label'=>'系统订单号:[$data]'),
            'record_time' => array('title'=>'下单时间','label'=>'下单时间:[$data]'),
            'pay_time' =>array('title'=>'付款时间','label'=>'付款时间:[$data]'),
            'print_time' => array('title'=>'打印时间','label'=>'打印时间:[$data]'),
            'num' =>  array('title'=>'商品总数量','label'=>'商品总数量:[$data]'),
            'goods_weigh' => array('title'=>'商品总重量','label'=>'商品总重量:[$data]'),
            'sell_money' =>array('title'=>'商品总金额','label'=>'商品总金额:[$data]'),
            'payable_money' =>array('title'=>'应收金额','label'=>'应收金额:[$data]'),
            'paid_money' => array('title'=>'实付金额','label'=>'实付金额:[$data]'),
            'goods_rebate_money' => array('title'=>'优惠总金额','label'=>'优惠总金额:[$data]'),
            'express_money' =>array('title'=>'运费','label'=>'运费:[$data]'),
            'buyer_remark'=>array('title'=>'买家留言','label'=>'买家留言:[$data]'),
            'seller_remark'=>array('title'=>'卖家备注','label'=>'卖家备注:[$data]'),
        ),
        'receiving' => array(
            'buyer_name' => array('title'=>'买家昵称','label'=>'买家昵称:[$data]'),
            'receiver_name' => array('title'=>'收货人姓名','label'=>'收货人姓名:[$data]'),
            'receiver_mobile' => array('title'=>'收货手机','label'=>'收货手机:[$data]'),
            'receiver_phone' => array('title'=>'收货电话','label'=>'收货电话:[$data]'),
            'receiver_province' =>array('title'=>'目的省','label'=>'目的省:[$data]'),
            'receiver_city' => array('title'=>'目的市','label'=>'目的市:[$data]'),
            'receiver_district' => array('title'=>'目的区/县','label'=>'目的区/县:[$data]'),
            'receiver_street' =>array('title'=>'目的街道','label'=>'目的街道:[$data]'),
            'receiver_addr' =>array('title'=>'收件地址(无省市区)','label'=>'收件地址:[$data]'),
            'receiver_address' =>array('title'=>'收件地址(含省市区)','label'=>'收件地址:[$data]'),
            'receiver_zip_code' => array('title'=>'收货邮编','label'=>'收货邮编:[$data]'),

        ),
        'delivery' => array(

            'sender_shop_name' => array('title'=>'店铺名称','label'=>'店铺名称:[$data]'),
            'sender' => array('title'=>'寄件人','label'=>'寄件人:[$data]'),
            'sender_tel' =>array('title'=>'寄件人手机','label'=>'寄件手机:[$data]'),
            'sender_phone' => array('title'=>'联系电话','label'=>'联系电话:[$data]'),
            'sender_address' => array('title'=>'发货地址(无省市区)','label'=>'发货地址:[$data]'),
            'sender_zip' => array('title'=>'发货邮编','label'=>'发货邮编:[$data]'),
            'senderr_district' =>array('title'=>'发货区/县','label'=>'发货区/县:[$data]'),
            'sender_city' => array('title'=>'发货市','label'=>'发货市:[$data]'),
            'sender_province' =>array('title'=>'发货省','label'=>'发货省:[$data]'),
            'sender_date' => array('title'=>'发货时间','label'=>'发货时间:[$data]'),
            'sender_operprint' => array('title'=>'打单员','label'=>'打单员:[$data]'),

        ),
        'detail' => array(
            'detail:id' =>  array('title'=>'序号','width'=>'60'),
            'detail:goods_name' =>  array('title'=>'商品名称','width'=>'100'),
            'detail:goods_code' =>  array('title'=>'商品编码','width'=>'100'),
            'detail:goods_short_name' =>  array('title'=>'商品简称','width'=>'100'),
            'detail:spec1_name' => array('title'=>'规格1名称','width'=>'100'),
            'detail:spec2_name' => array('title'=>'规格2名称','width'=>'100'),
            'detail:goods_price' => array('title'=>'单价','width'=>'80'),
            'detail:num' => array('title'=>'数量','width'=>'80'),
            'detail:avg_money' =>  array('title'=>'总金额','width'=>'80'),
            'detailpage' => array('title'=>'页码','label'=>'页码'),
        ),

    );
    public $tpl_replace = array();
    public $template_val = array();
    public $templates_id_arr = array();
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
        $data = $this -> get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this -> format_ret($ret_status, $ret_data);
    }

    function get_templates_by_id($id){
        if(!isset($this->templates_id_arr[$id])){
            $ret = $this->get_row(array('print_templates_id'=>$id));

            $ret['data']['template_body'] = !empty($ret['data']['template_body'])?$ret['data']['template_body']:$ret['data']['template_body_default'];
            $template_data = $this->set_template_data($ret['data']);
            $this->templates_id_arr[$id] = $template_data;
        }
        return $this->format_ret(1,$this->templates_id_arr[$id]);
    }

    function get_templates_all($ids=''){
        $sql = "select * from {$this->table} where type=2";
        if(!empty($ids)){
            $sql.=" and print_templates_id in($ids)";
        }
        $data = $this->db->get_all($sql);
        $check_print = TRUE;
        $template_data = array();
        if(!empty ($data)){
            foreach($data as $val){
                $template_data_val= $this->set_template_data($val);
                if(!empty($template_data_val['top'])&&!isset($template_data['top'])){
                    $template_data['top'] = $template_data_val['top'];
                }
                if(!empty($val['printer'])&&$check_print===true){
                    if(isset($template_data['printer'])&&$template_data['printer']!=$template_data_val['printer']){
                        $check_print = false;
                        $template_data['printer'] = '';
                    }else{
                        $template_data['printer'] = $template_data_val['printer'];
                    }
                }
                $template_data['data'][] = $template_data_val;
            }
        }
        if(empty($template_data)){
            return   $this->format_ret(-1,'','没找打印模版');
        }
        return  $this->format_ret(1,$template_data);
    }
    private function set_template_data($template_val){

        $template_data = $this->set_print_templates($template_val['template_body']);
        if(!empty($template_val['template_body_replace'])){
            $replace_arr = json_decode($template_val['template_body_replace'],true);
            foreach($replace_arr as $key=>$val){//替换打印块
                $template_data['body'] = str_replace($key, $val, $template_data['body']);
            }
        }
        $template_data['printer'] = $template_val['printer'];
        $template_data['template_val'] =  !empty($template_val['template_val'])?json_decode($template_val['template_val'],true):'';
        $template_data['id'] = $template_val['print_templates_id'];
        return $template_data;
    }



    /**
     * 转换打印项内容，默认打印项文本内容转换为示例文本
     * @param $print
     * @param $print_var
     * @param null $callback
     * @return string
     */
    public function printCastContent($print, $print_var, $callback=null) {
        $print_var2 = $print_var;
        /*foreach ( $print_var as $_key=>$v ) {
             $_key = "c['" . $_key . "']";
             $print_var2[$_key] = $v;
         }*/

        $print_line_arr = explode("\r\n", $print);
        $print_arr = array();
        $this->tpl_replace = array();
        $_prefix = 'LODOP.ADD_PRINT_TEXTA(';
        $_suffix = ");";
        $_start = strlen($_prefix);
        $_end = - strlen($_suffix);
        foreach ( $print_line_arr as $line_key => $line_value ) {
            if (empty($line_value)
                || strpos($line_value, $_prefix) === false
                || substr($line_value, 0, strlen($_prefix.'"_txt:')) == $_prefix.'"_txt:' // 自定义文本
            ) {
                $print_arr[] = $line_value;
                continue;
            }
            $_arr = explode(',', substr($line_value, $_start, $_end));
            $_k = substr($_arr[0], 1, -1);
            $_k = explode('-', $_k);
            $_k = $_k[0];
            if ($callback == null) {
                if(strpos($_k, ":")===FALSE){
                    $val_str = '"+c["'.$_k.'"]+"';
                    $_arr[5] = !isset($print_var2[$_k]) ?  $_arr[5]: str_replace($val_str, '[$data]', $_arr[5]);
                }
            } else {
                $this->$callback($_arr, $_k,$print_var);
            }
            $print_arr[] = $_prefix.implode(',', $_arr).$_suffix;
        }


        //die;
        return implode("\r\n", $print_arr);
    }

    /**
     * 将打印项文本内容转换为变量
     * @param array $_arr 单行打印代码
     * @param $name
     */
    private function printCastContent_ToVar(&$_arr, $name,$print_var) {

        if(strpos($_arr['5'], '[$data]')!=FALSE){
            $val_str = '"+c["'.$name.'"]+"';
            $_arr['5'] =str_replace('[$data]', $val_str, $_arr['5']);
        }else{
            if(isset($print_var[$name])&&strpos($_arr['5'], $print_var[$name]['title'])!=FALSE){
                $name_v = $print_var[$name];
                $val_str = '"+c["'.$name.'"]+"';
                $_arr['5'] =str_replace($name_v, $val_str, $_arr['5']);
            }
        }
    }



    function set_print_templates($template_body){
        $template_top = '';
        $arr = array();
        $pattern ="/LODOP.SET_PRINT_PAPER\([^\);]*\);/i";
        preg_match($pattern, $template_body,$arr);
        if(!empty($arr)){
            $template_top = $arr[0]."\n";
            $template_body = preg_replace($pattern, '', $template_body);
        }


        $pattern = "/LODOP.SET_PRINT_PAGESIZE\([^\)]*\);/i";
        preg_match($pattern, $template_body,$arr);
        if(!empty($arr)){
            $template_top .= $arr[0]."\n";
        }
        $pattern = "/LODOP.PRINT_INITA\([^\)]*\);/i";
        preg_match($pattern, $template_body,$arr);
        if(!empty($arr)){
            $template_body = preg_replace($pattern, '', $template_body);
        }
        return array('top'=>$template_top,'body'=>$template_body);

    }



}