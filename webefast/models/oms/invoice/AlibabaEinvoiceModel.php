<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AlibabaEinvoiceModel
 *
 * @author wq
 */
require_model('tb/TbModel');
require_lib('apiclient/TaobaoClient');

class AlibabaEinvoiceModel extends TbModel {

    private $invoice_info = array();
    private $invoice_record_info = array();
    private $record_info = array();
    private $invoice_param = array();
    private $old_invoice_record_info = array();
    private $symbol = 1;
    private $tax_all = 0;

    function __construct() {
        parent::__construct('js_fapiao');
        $this->tb_xml = new tb_xml();
    }

    function get_shop_invoice_info($shop_code) {
        $sql = "select * from js_shop where shop_code=:shop_code ";
        return $this->db->get_row($sql, array(':shop_code' => $shop_code));
    }

    function create_invoice($id) {

        $this->invoice_record_info = load_model('oms/invoice/OmSellInvoiceRecordModel')->get_invoice_record($id);
        $sell_record_code = $this->invoice_record_info['sell_record_code'];
        $this->invoice_info = load_model('oms/invoice/OmsSellInvoiceModel')->get_sell_invoice($sell_record_code);
        $this->record_info = load_model('oms/SellRecordModel')->get_record_by_code($sell_record_code);

        $this->old_invoice_record_info = array();
        if ($this->invoice_record_info['is_red'] == 1) {
            $this->old_invoice_record_info = load_model('oms/invoice/OmSellInvoiceRecordModel')->get_invoice_record_zheng($sell_record_code);
            if (empty($this->old_invoice_record_info)) {
                return $this->format_ret(-1, '', '数据异常，没对应正票信息！');
            }
        }
        $record_decrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($this->record_info['sell_record_code']);
        if (empty($record_decrypt_info)) {
            return $this->format_ret(-1, '', '数据解密失败，稍后尝试！');
        }
        $this->record_info = array_merge($this->record_info, $record_decrypt_info);

        $ret = $this->get_invoice_param_by_shop_code($this->record_info['shop_code']);
        if ($ret['status'] < 1) {
            return $ret;
        }
        $this->invoice_param = $ret['data'];
        if ($this->invoice_record_info['is_red'] == 1) {
            $this->symbol = -1;
        }

        //business_type
        $data = array();
        $data['business_type'] = $this->invoice_info['is_company'];  //是否企业 
        $data['erp_tid'] = $this->record_info['sell_record_code'];
        //淘宝要判断下
        $sale_channel_code = $this->record_info['sale_channel_code'];


        $data['platform_code'] = $this->get_platform_code($sale_channel_code);
        $data['platform_tid'] = $this->record_info['deal_code_list'];
        $no = $this->invoice_record_info['sell_record_code'] . $this->invoice_record_info['id'];

        $data['serial_no'] = $this->get_no($no); //发票请求唯一流水号
        $data['payee_address'] = $this->invoice_param['xhf_dz']; //开票方地址
        $data['payee_name'] = $this->invoice_param['nsrmc'];


        $data['payee_bankaccount'] = 1;
        //开票方银行及 帐号
        if ($this->invoice_record_info['invoice_type'] == 2) {
            $data['payee_bankaccount'] = $this->invoice_param['xhf_yhzh']; //销货方银行账号  
        } else {
            $data['payee_bankaccount'] = $this->invoice_param['xhf_yhmc'] . " " . $this->invoice_param['xhf_yhzh']; //销货方银行账号  
        }
        //    $data['payer_register_no'] = ''; 
        if ($this->invoice_info['is_company'] == 1) {
            $data['payer_register_no'] = $this->invoice_info['taxpayers_code']; //购货方识别号
        }


        $data['payee_operator'] = $this->invoice_param['kpy']; //	开票人
        $data['invoice_amount'] = sprintf("%.2f", round($this->symbol * $this->invoice_record_info['invoice_amount'], 2)); //开票金额

        $data['invoice_memo'] = $this->invoice_record_info['invoice_remark']; //发票备注
        $data['invoice_time'] = date('Y-m-d H:i:s'); //开票日期
        $data['invoice_type'] = ($this->invoice_record_info['is_red'] == 1) ? 'red' : 'blue'; //蓝票blue,红票red，默认blue
        if ($this->invoice_record_info['is_red'] == 1) {
            $data['normal_invoice_code'] = $this->old_invoice_record_info['fp_dm']; //原发票代码((开红票时传入)
            $data['normal_invoice_no'] = $this->old_invoice_record_info['invoice_no']; //	原发票号码(开红票时传入)
        }


        $data['payee_register_no'] = $this->invoice_param['nsrsbh'];  //收款方税务登记证号
        if($this->invoice_record_info['invoice_type'] == 2){//纸质发票
            $data['payer_address'] = $this->invoice_info['receiver_address']; //消费者地址
        }
        //$data['payer_address'] = ($this->invoice_record_info['invoice_type'] == 1) ?'': $this->invoice_info['receiver_address']; //消费者地址
        //$data['payer_bankaccount'] = $this->invoice_param['nsrmc']; //付款方开票开户银行及账号

         $data['payer_name'] = !empty($this->record_info['invoice_title']) ? $this->record_info['invoice_title'] : '个人'; //
        // $data['payer_phone'] = $this->invoice_param['nsrmc']; //消费者联系电话,
        //tax_rate = invoice_amount
          //invoice_amount*(tax_rate/100)/1+(tax_rate/100) = sum_price
        //sprintf("%.2f", round(
        $data['sum_tax'] = sprintf("%.2f",$this->symbol*$this->invoice_record_info['invoice_amount']*($this->invoice_param['tax_rate']/100)/(1+($this->invoice_param['tax_rate']/100))); //合计金额(
        $data['sum_price'] = sprintf("%.2f", bcsub($data['invoice_amount'], $data['sum_tax'], 2));

        $ret_items = $this->get_invoice_items($data); //电子发票明细
        if ($ret_items['status'] < 0) {
            return $ret_items;
        }
        $data['invoice_items'] = json_encode($ret_items['data']); //电子发票明细
        $shop_code = $this->invoice_param['gk_dp'];
        $client = new TaobaoClient($shop_code);

        $ret_api = $client->alibabaEinvoiceCreatereq($data);
        if (!empty($ret_api['error_response'])) {
            if(empty($ret_api['message'])){
                $ret_api['message'] = '接口请求异常';
            }
            return $this->format_ret(-1, '', $ret_api['message']);
        }

        if ($ret_api['is_success'] === true) {
            return $this->format_ret(1);
        }


        return $this->format_ret(-1, '', '接口异常');
    }

    private function get_invoice_items($main_data) {
        $order_detail = load_model('oms/SellRecordModel')->get_detail_list_by_code($this->record_info['sell_record_code']);
        $all_momey = 0;
        $new_order_detail = array();
        foreach ($order_detail as $v) {
            $all_momey += $v['avg_money'];
            if (!isset($new_order_detail[$v['sku']])) {
                $s_key = array('goods_name', 'barcode', 'goods_code', 'spec1_name', 'spec2_name', 'price');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($v['sku'], $s_key);
                $v = array_merge($v, $sku_info);
                $new_order_detail[$v['sku']] = $v;
            } else {
                $new_order_detail[$v['sku']]['num'] += $v['num'];
                $new_order_detail[$v['sku']]['avg_money'] += $v['num'];
            }
        }

        $num = count($new_order_detail);
        $items_arr = array();
        $i = 0;
        //  $this->tax_all  
        $invoice_amount = $this->invoice_record_info['invoice_amount'];
        $sum_tax = $main_data['sum_tax'];
        $sum_price = $main_data['sum_price'];
        foreach ($new_order_detail as $val) {
            $i++;

            $items['item_name'] = $val['goods_name'];
            $items['item_name'] = str_replace('-', '',  $items['item_name']);
            $tax_goods_arr = $this->get_tax_code($val['sku']);//获取商品税务编码和单位
            if (empty($tax_goods_arr['tax_code'])) {
                return $this->format_ret(-1, '', $val['barcode'] . "找不到商品税务编码");
            }
            $items['item_no'] = $tax_goods_arr['tax_code'];//商品税务编码
            $items['quantity'] = $this->symbol *$val['num'];
            $items['row_type'] = 0;
            $items['sum_price'] = 0;
            if ($val['is_gift'] == 0) {
                if ($i == $num) {
                    $item_invoice_amount = $invoice_amount;
                } else {
                    $item_invoice_amount = round(($this->invoice_record_info['invoice_amount'] * $val['avg_money']) / $all_momey, 2);
                }
            } else {
                $item_invoice_amount = $val['num'] * $val['price'];
            }
            $items['sum_price'] = 0;

            $items['sum_price'] = sprintf("%.2f", round($this->symbol * $item_invoice_amount/(1+($this->invoice_param['tax_rate']/100)) , 2));
            $items['tax'] = sprintf("%.2f", round($this->symbol *   $items['sum_price'] * $this->invoice_param['tax_rate'] / 100, 2));  //税额； 
            $items['tax_rate'] = sprintf("%.2f", round($this->invoice_param['tax_rate'] / 100, 2));  //税率。税率只能为0或0.03或0.04或0.06或0.11或0.13或0.17  
        
            if ($val['is_gift'] == 0) {
                if ($i == $num) {
                    $items['tax'] = $sum_tax;
                    $items['sum_price'] = $sum_price;
                } else {
                    $sum_tax = bcsub($sum_tax, $items['tax'], 2);
                    $sum_price = bcsub($sum_price, $items['sum_price'], 2);
                }
            }
            

            $items['price'] = sprintf("%.6f", abs(round( $items['sum_price']/ $items['quantity'], 6)));
            $items['amount'] = sprintf("%.2f", round($this->symbol * $item_invoice_amount, 2));        //价税合计。(等于sumPrice和tax之和) 当开红票时，该字段为负数
            //   发票行性质。0表示正常行，1表示折扣行，2表示被折扣行。比如充电器单价100元，折扣10元，
            //   则明细为2行，充电器行性质为2，折扣行性质为1。如果充电器没有折扣，则值应为0
            if($this->invoice_param['is_spec'] == 1){
                    $items['specification'] = $val['spec1_name'] . ' ' . $val['spec2_name'];//规格型号
            }
            if ($val['is_gift'] == 0) {
                $invoice_amount = bcsub($invoice_amount, $item_invoice_amount, 2);
            } else {
                unset($items['price']);
                $items['row_type'] = 2;
                if($this->invoice_param['is_unit'] == 1){//是否增加单位字段
                    $items = $this->check_unit($items,$tax_goods_arr);
                }
                $items_arr[] = $items;
                $items['row_type'] = 1;
                $items['sum_price'] = - sprintf("%.2f", $items['sum_price'], 2);
                $items['tax'] = - sprintf("%.2f", $items['sum_price'], 2);
                $items['amount'] = - sprintf("%.2f", $items['sum_price'], 2);
            }
            if($this->invoice_param['is_unit'] == 1){
                $items = $this->check_unit($items,$tax_goods_arr);
            }
            $items_arr[] = $items;
        }
        return $this->format_ret(1, $items_arr);
    }
    /**
     * 组装税务编码和单位
     * @param type $data
     * @param type $tax_goods_arr
     * @return type
     */
    function check_unit($data,$tax_goods_arr){
        if($data['sum_price'] > 0){
            $data['unit'] = $tax_goods_arr['unit'];
        }else{
            if(isset($data['unit'])){
                unset($data['unit']);
            }
        }
        return $data;
    }
        
    function get_by_id($id) {
        return $this->get_row(array('id' => $id));
    }

    function get_tax_code($sku) {
        $sql = " select tax_code,unit from goods_tax where sku=:sku";
        $arr = $this->db->get_row($sql, array(':sku' => $sku));
        $arr['tax_code'] = sprintf('%-019s', $arr['tax_code']);
        return $arr;
    }

    function get_invocie_result() {
        $sql = "select id,sell_record_code,shop_code from oms_sell_invoice_record where status=0";
        $data = $this->db->get_all($sql);

        foreach ($data as $val) {
            $this->get_api_invocie($val);
        }

        return $this->format_ret(1);
    }

    function get_api_invocie($invoice_record) {

        $ret_param = $this->get_invoice_param_by_shop_code($invoice_record['shop_code']);

        if ($ret_param['status'] < 1) {
            return $this->format_ret(-1, '', '未找到店铺对应的发票配置');
        }


        //流水号
        $no = $invoice_record['sell_record_code'] . $invoice_record['id'];
        $id_no = $this->get_no($no); //发票请求唯一流水号


        $data = array(
            'serial_no' => $id_no,
            'payee_register_no' => $ret_param['data']['nsrsbh'],
        );
        $shop_code = $ret_param['data']['gk_dp'];
        $client = new TaobaoClient($shop_code);


        $ret = $client->alibabaEinvoiceCreateResultGet($data);
        if (!empty($ret['error_response'])) {
            return $this->format_ret(-1, '', $ret['error_response']['sub_msg']);
        }


        //invoice_result
       $invoice_result = &$ret['invoice_result_list']['invoice_result'][0];
        if ($invoice_result['status'] == 'waiting') {
            return $this->format_ret(-1, '', '开票中,请等待...');
        }
        if ($invoice_result['status'] == 'create_failed') {


            return $this->format_ret(-10, '', $invoice_result['biz_error_msg']);
        }
        //biz_error_msg

        $ret_data['fpqqlsh'] = $invoice_result['serial_no'];
        $ret_data['kplsh'] = $invoice_result['serial_no'];
        $ret_data['invoice_no'] = $invoice_result['invoice_no'];
        $ret_data['fp_dm'] = $invoice_result['invoice_code'];
        $ret_data['kprq'] = $invoice_result['invoice_date'];
        $ret_data['ewm'] = $invoice_result['qr_code'];
        $ret_data['fwm'] = $invoice_result['anti_fake_code'];
        $ret_data['pdf_url'] = $invoice_result['file_path'];
        $ret_data['pdf_file'] = $invoice_result['ciphertext']; //demosdffsd-32432发票密文，密码区的字符串
        $ret_data['czdm'] = $invoice_result['device_no'];
       
        return $this->format_ret(1, $ret_data);
    }

    function get_platform_code($sale_channel_code) {
        $platform_arr = array(
            'taobao' => 'TB',
            'tmall' => 'TM',
            'jingdong' => 'JD',
            'dangdang' => 'DD',
            'paipai' => 'PP',
            'yamaxun' => 'AMAZON',
            'suning' => 'SN',
            // 'taobao'=>'GM',
            'weipinhui' => 'WPH',
            'jumei' => 'JM',
            'lefeng' => 'LF',
            'mogujie' => 'MGJ',
            //  'taobao'=>'JS',
            'paixie' => 'PX',
            'yintai' => 'YT',
            'yihaodian' => 'YHD',
            //   'taobao'=>'VANCL',
            // 'taobao'=>'YL',
            'yougou' => 'YG',
            'alibaba' => '1688',
        );
        if ($sale_channel_code == 'taobao') {
            $sql = "select tb_shop_type from base_shop_api where shop_code=:shop_code ";
            $tb_shop_type = $this->db->get_value($sql, array(':shop_code' => $this->record_info['shop_code']));
            if ($tb_shop_type == 'C') {
                $sale_channel_code = 'tmall';
            }
            //$tb_shop_type
        }

        return isset($platform_arr[$sale_channel_code]) ? $platform_arr[$sale_channel_code] : 'OTHER';
    }

    function get_invoice_param_by_shop_code($shop_code) {
        static $invoice_param = null;
        if (!isset($invoice_param[$shop_code])) {
            $shop_invoice = $this->get_shop_invoice_info($shop_code);
            if (empty($shop_invoice)) {
                return $this->format_ret(-1, '', '未找到店铺对应的发票配置');
            }
            $ret = $this->get_by_id($shop_invoice['p_id']);
            $invoice_param[$shop_code] = $ret['data'];
        } else {
            $ret = $this->format_ret(1, $invoice_param[$shop_code]);
        }

        return $ret;
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        //var_dump($filter);diel;
        $sql_values = array();
        $sql_main = "FROM {$this->table} r1  WHERE 1";

        //店铺查询
        if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
            $sql_main .= " AND :shop_code in (select shop_code from js_shop r2 where r1.id = r2.p_id)";
            $sql_values[':shop_code'] = $filter['shop_code'];
        }
        //企业名称查询
        if (isset($filter['nsrmc']) && $filter['nsrmc'] != '') {
            $sql_main .= " AND r1.nsrmc =:nsrmc ";
            $sql_values[':nsrmc'] = $filter['nsrmc'];
        }
        $select = 'r1.*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        //var_dump($data);die;
        return $this->format_ret($ret_status, $ret_data);
    }

    //获取发票对应的商店代码
    function get_shop_code_by_id($id) {
        $sql = "select a.* from js_shop a left join js_fapiao b on a.p_id = b.id where b.id = :id";
        $data = $this->db->get_all($sql, array(':id' => $id));
        //var_dump($data);die;
        return $data;
    }

    //获取所有店铺名称
    function get_all_shop() {
        $sql = "select shop_code from js_shop";
        $ret = $this->db->get_all($sql);
        return $ret;
    }

    function get_shop_id($shop_code) {
        $sql = "select id from js_shop where shop_code = :shop_code";
        $data = $this->db->get_all($sql, array(':shop_code' => $shop_code));
        if (empty($data)) {
            return $this->format_ret(-1, '', '未找到店铺对应代码');
        }
        return $data;
    }

    /**
     * 删除记录
     */
    function delete($id) {
        $ret = parent :: delete(array('id' => $id));
        return $ret;
    }

    //获取所有的企业名称
    function get_nsrmc() {
        $sql = "select nsrmc as company_name,nsrmc from js_fapiao";
        $data = $this->db->get_all($sql);
        //var_dump($data);die;
        return $data;
    }

    function get_no($no, $max = 20) {
        $z = '';
        $len = strlen($no);
        if ($max > $len) {
            for ($i = 0; $i < $max - $len; $i++) {
                $z = "0" . $z;
            }
        }

        return $z . $no;
    }

}
