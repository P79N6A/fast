<?php

require_model('tb/TbModel');
require_lib('tb_xml', true);
require_model('api/jsfapiao/JsFapiaoApiModel');

class JsFapiaoModel extends TbModel {

    private $invoice_info = array();
    private $invoice_record_info = array();
    private $record_info = array();
    private $invoice_param = array();
    private $old_invoice_record_info = array();
    private $symbol = 1;
    protected $tb_xml;
    private $goods_se = 0; //商品税额

    function __construct() {
        parent::__construct('js_fapiao');
        $this->tb_xml = new tb_xml();
    }

    function get_shop_invoice_info($shop_code) {
        $sql = "select * from js_shop where shop_code=:shop_code ";
        return $this->db->get_row($sql, array(':shop_code' => $shop_code));
    }

    function create_invoice($id) {
        $tag = 'REQUEST_FPKJXX';
        $data['@attributes'] = array('class' => 'REQUEST_FPKJXX');
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
        //   $this->goods_se = 0;
        $data['FPKJXX_FPTXX'] = $this->set_invocie_main();
        $k_id = CTX()->saas->get_saas_key();
        if($k_id == 2575){ //通灵
            $invoice_detail = $this->set_invoice_detail_tl();
        }else{
            $invoice_detail = $this->set_invoice_detail();//通用
        }
        if ($invoice_detail['status'] < 0) {
            return $invoice_detail;
        }
        if (empty($data['FPKJXX_FPTXX']['KPXM'])) {
            $data['FPKJXX_FPTXX']['KPXM'] = $invoice_detail['FPKJXX_XMXXS']['FPKJXX_XMXX'][0]['XMMC'];
        }


        $data['FPKJXX_XMXXS'] = $invoice_detail['FPKJXX_XMXXS'];
        $data['FPKJXX_DDXX'] = $this->set_invocie_order();
        $data['FPKJXX_DDMXXXS'] = $invoice_detail['FPKJXX_DDMXXXS'];


        $data['FPKJXX_ZFXX'] = $this->set_invocie_pay();
        $data['FPKJXX_WLXX'] = $this->set_invocie_shipping();

        $data_xml = $this->create_tag_xml($data, $tag);
        $fp = new JsFapiaoApiModel($this->invoice_param);
        $result = $fp->request_api('ECXML.FPKJ.BC.E_INV', $data_xml);
        //$ret_data = $this->xml2array($result);
        if ($result['returnCode'] != '0000') {
            $result['returnMessage'] = empty($result['returnMessage'])?'参数不正确':$result['returnMessage'];
            $ret = $this->format_ret(-1, $result, $result['returnMessage']);
        } else {
            $ret = $this->format_ret(1, '', '');
        }
        return $ret;
    }

    //发票主信息
    private function set_invocie_main() {
        $FPKJXX_FPTXX = array('@attributes' =>
            array('class' => 'FPKJXX_FPTXX'),
        );
        $id = $this->invoice_record_info['id'];
//        if ($this->invoice_record_info['is_red'] == 1) {
        //    $id = $this->get_id($id, $this->invoice_record_info['sell_record_code']);
        //  }
        $no = $this->invoice_record_info['sell_record_code'] . $id;
        $FPKJXX_FPTXX['FPQQLSH'] = $this->get_no($no); //发票请求唯一流水号
        $FPKJXX_FPTXX['DSPTBM'] = $this->invoice_param['username']; //
        $FPKJXX_FPTXX['NSRSBH'] = $this->invoice_param['nsrsbh']; //开票方识别号 
        $FPKJXX_FPTXX['NSRMC'] = $this->invoice_param['nsrmc']; //开票方名称
        $FPKJXX_FPTXX['NSRDZDAH'] = '';  //开票方电子档案号
        $FPKJXX_FPTXX['SWJG_DM'] = ''; //税务机构代码
        $FPKJXX_FPTXX['DKBZ'] = $this->invoice_param['dkbz']; //代开标志 
        $FPKJXX_FPTXX['PYDM'] = '000001'; //票样代码
        //主要开票商品，或者第一条商品，取项目信息中第一条数据的项目名称（或传递大类例如：办公用品）
        $FPKJXX_FPTXX['KPXM'] = $this->record_info['invoice_content']; //主要开票项目

        $FPKJXX_FPTXX['BMB_BBH'] = '12.0'; //编码表版本号
        //填写第 3 项中的开票方识别号  ???存在疑问
        $FPKJXX_FPTXX['XHF_NSRSBH'] = $this->invoice_param['nsrsbh']; //销货方识别号  
        //纳税人名称 
        $FPKJXX_FPTXX['XHFMC'] = $this->invoice_param['nsrmc']; //销货方名称 
        $FPKJXX_FPTXX['XHF_DZ'] = $this->invoice_param['xhf_dz']; //销货方地址             
        $FPKJXX_FPTXX['XHF_DH'] = $this->invoice_param['xhf_dh']; //销货方电话      
        //纸质发票用 
        if ($this->invoice_record_info['invoice_type'] == 2) {
            $FPKJXX_FPTXX['XHF_YHZH'] = $this->invoice_param['xhf_yhzh']; //销货方银行账号  
        } else {
            $FPKJXX_FPTXX['XHF_YHZH'] = $this->invoice_param['xhf_yhmc'] . " " . $this->invoice_param['xhf_yhzh']; //销货方银行账号  
        }

        $FPKJXX_FPTXX['GHFMC'] = !empty($this->record_info['invoice_title']) ? $this->record_info['invoice_title'] : '个人'; //购货方名称 发票抬头
        $FPKJXX_FPTXX['GHF_NSRSBH'] = ''; //购货方识别号
        $FPKJXX_FPTXX['GHF_SF'] = ''; //购货方省份  
        $FPKJXX_FPTXX['GHF_DZ'] = ''; //购货方地址  
        $FPKJXX_FPTXX['GHF_GDDH'] = $this->record_info['receiver_phone']; //购货方固定电话 
        $reg = "/^1[0-9]{10}$/";
        if(empty($this->record_info['receiver_mobile']) || !preg_match($reg,$this->record_info['receiver_mobile'])){
            $FPKJXX_FPTXX['GHF_SJ'] = $this->invoice_param['ghf_sj'];
        }else{
            $FPKJXX_FPTXX['GHF_SJ'] = $this->record_info['receiver_mobile'];
        }
        //$FPKJXX_FPTXX['GHF_SJ'] = empty($this->record_info['receiver_mobile']) ? $this->invoice_param['ghf_sj'] : $this->record_info['receiver_mobile']; //购货方手机  receiver_mobile
        $FPKJXX_FPTXX['GHF_EMAIL'] = ''; //购货方邮箱
        //01:企业02：机关事业单位03：个人04：其它 todo:单据有
        $FPKJXX_FPTXX['GHF_YHZH'] = ''; //购货方银行账号 
        //增值发票当企业发票
        if($this->invoice_info['is_company'] == 1){//企业发票
            $FPKJXX_FPTXX['GHFQYLX'] = '01'; //购货方企业类型 
            $FPKJXX_FPTXX['GHF_NSRSBH'] = $this->invoice_info['taxpayers_code']; //购货方识别号
            if($this->record_info['invoice_type'] == 'vat_invoice'){//纸质发票
                $FPKJXX_FPTXX['GHF_SF'] = oms_tb_val('base_area', 'name', array('id' => $this->invoice_info['registered_province'])); //购货方识别号
                $FPKJXX_FPTXX['GHF_DZ'] = $this->invoice_info['registered_addr']; //购货方识别号
                $FPKJXX_FPTXX['GHF_SJ'] = empty($this->invoice_info['phone'])?$FPKJXX_FPTXX['GHF_SJ']:$this->invoice_info['phone']; //购货方识别号
                $FPKJXX_FPTXX['GHF_YHZH'] = $this->invoice_info['bank_account']; //购货方银行账号
            }
        }else{//个人发票
            $FPKJXX_FPTXX['GHFQYLX'] = '03';
        }
        
        $FPKJXX_FPTXX['HY_DM'] = ''; //行业代码
        $FPKJXX_FPTXX['HY_MC'] = ''; //行业名称
        $FPKJXX_FPTXX['KPY'] = $this->invoice_param['kpy']; //开票员
        $FPKJXX_FPTXX['SKY'] = ''; //收款员
        $FPKJXX_FPTXX['FHR'] = ''; //FHR
        $FPKJXX_FPTXX['KPRQ'] = date('Y-m-d H:i:s'); //开票日期
        $FPKJXX_FPTXX['KPLX'] = $this->invoice_record_info['is_red'] == 0 ? 1 : 2; //开票类型  1 正票、 2 红票
        if ($this->invoice_record_info['is_red'] == 1) {
            $FPKJXX_FPTXX['YFP_DM'] = $this->old_invoice_record_info['fp_dm']; //原发票代码
            $FPKJXX_FPTXX['YFP_HM'] = $this->old_invoice_record_info['invoice_no']; //原发票号码
            //特殊冲红标志:0 正常冲红(电子发票) 1 特殊冲红(冲红纸质等)
            $FPKJXX_FPTXX['TSCHBZ'] = $this->invoice_record_info['invoice_type'] == 1 ? 0 : 1;
        } else {
            $FPKJXX_FPTXX['YFP_DM'] = ''; //原发票代码
            $FPKJXX_FPTXX['YFP_HM'] = ''; //原发票号码   
        }
        //操作代码
        //10 正票正常开具11 正票错票重开20 退货折让红票、21 错票重开红票、22 换票冲红（全冲红电子发票，开具纸质发票）
        //临时处理czdm，区分南京和上海，后续再优化
        $k_id = CTX()->saas->get_saas_key();
        if($k_id == 2575){//通灵id
            $FPKJXX_FPTXX['CZDM'] = $this->invoice_record_info['is_red'] == 0 ? '10' : '22'; //操作代码
        } else {
            $FPKJXX_FPTXX['CZDM'] = $this->invoice_record_info['is_red'] == 0 ? '10' : '20'; //操作代码
        }
        
        //  $FPKJXX_FPTXX['QD_BZ'] = '0'; //清单标志
        //  $FPKJXX_FPTXX['QDXMMC'] = '01'; //清单发票项目名称

        $FPKJXX_FPTXX['CHYY'] = ''; //冲红原因
        if ($this->invoice_record_info['is_red'] == 1) {
            $FPKJXX_FPTXX['CHYY'] = $this->invoice_record_info['chyy'];
        }
        //
        //   $FPKJXX_FPTXX['FJH'] = '01'; //冲红原因
        $FPKJXX_FPTXX['TSCHBZ'] = 0; //特殊冲红标志

        $FPKJXX_FPTXX['KPHJJE'] = $this->symbol * $this->invoice_record_info['invoice_amount']; //价税合计金额
        $FPKJXX_FPTXX['HJBHSJE'] = round($FPKJXX_FPTXX['KPHJJE'] / (1 + $this->invoice_param['tax_rate'] / 100), 2); //合计不含税金额
        $FPKJXX_FPTXX['HJSE'] = bcsub($FPKJXX_FPTXX['KPHJJE'], $FPKJXX_FPTXX['HJBHSJE'], 2); //不含税金额
        if ($this->invoice_record_info['is_red'] == 1) {
            $FPKJXX_FPTXX['BZ'] = "对应正数发票代码:{$this->old_invoice_record_info['fp_dm']}号码:{$this->old_invoice_record_info['invoice_no']}"; //备注 
        } else {
            $FPKJXX_FPTXX['BZ'] = $this->invoice_record_info['invoice_remark']; //备注   
        }


        $FPKJXX_FPTXX['QDBZ'] = 0;
        $FPKJXX_FPTXX['QD_BZ'] = 0;
        $FPKJXX_FPTXX['QDXMMC'] = '';
        $FPKJXX_FPTXX['BYZD2'] = '';
        $FPKJXX_FPTXX['BYZD3'] = '';
        $FPKJXX_FPTXX['BYZD4'] = '';
        $FPKJXX_FPTXX['BYZD5'] = '';
        return $FPKJXX_FPTXX;
    }

    private function init_goods_info() {

        $sql = "select unique_code,sku from goods_unique_code_log where record_code=:record_code AND record_type=:record_type";
        $unique_code_data = $this->db->get_all($sql, array(':record_code' => $this->record_info['sell_record_code'], 'record_type' => 'sell_record'));
        $unique_code_arr = array();
        $sku_arr = array();
        foreach ($unique_code_data as $val) {
            $unique_code_arr[] = $val['unique_code'];
            $sku_arr[] = $val['sku'];
        }
        $sql_values = array();
        $unique_code_str = $this->arr_to_in_sql_value($unique_code_arr, 'unique_code', $sql_values);

        $sql_unque = "select unique_code,goods_name,total_price,sku from goods_unique_code_tl where 1 AND unique_code IN ({$unique_code_str}) ";
        $data = $this->db->get_all($sql_unque, $sql_values);
        $unique_data = array();
        $all_money = 0;
        foreach ($data as $val) {
            $all_money += $val['total_price'];
            $unique_data[$val['sku']][$val['unique_code']] = $val;
        }
        return array('all_money' => $all_money, 'unique_data' => $unique_data);
    }

    //发票明细
    private function set_invoice_detail_tl() {

        $goods_list = array();
        $order_detail = load_model('oms/SellRecordModel')->get_detail_list_by_code($this->record_info['sell_record_code']);


        $goods_info = $this->init_goods_info();
        // array('all_money'=>$all_money,'unique_data'=>$unique_data);
        $unique_data = $goods_info['unique_data'];
        $all_money = $goods_info['all_money'];
        if (empty($unique_data)) {
            return $this->format_ret(-1, '', '未找到对应的出库唯一码！');
        }
        $sell_momey = $this->invoice_record_info['invoice_amount'];
        $goods_num = 0;
        $no_gift_goods_num = 0;
        $unique_data_temp = $unique_data;
        foreach ($order_detail as $val) {
            $key = $val['sku'] . "|" . $val['is_gift'];
            //  $sell_momey+=$val['avg_money'];
            $g_num = $val['num'];
            //赠品减去统计金额
            while ($val['is_gift'] == 1 && $g_num > 0) {
                $unique_data_sku = $unique_data_temp[$val['sku']];
                $unique_code_arr = array_keys($unique_data_sku);
                $unique_code = $unique_code_arr[0];
                $unique_data_row = $unique_data_sku[$unique_code];
                //减去之前计算金额
                $all_money = $all_money - $unique_data_row['total_price'];
                $g_num--;
                unset($unique_data_temp[$val['sku']][$unique_code]);
            }


            $goods_num +=$val['num'];
            $no_gift_goods_num +=($val['is_gift'] == 1) ? 0 : $val['num'];

            if (isset($goods_list[$key])) {
                $goods_list[$val['sku']]['num'] +=$val['num'];
                $goods_list[$val['sku']]['avg_money'] +=$val['avg_money'];
                continue;
            }
            $goods_list[$key] = array(
                'num' => $val['num'],
                'sku' => $val['sku'],
                'is_gift' => $val['is_gift'],
                'goods_code' => $val['goods_code'],
                'avg_money' => $val['avg_money'],
            );
        }

        $FPKJXX_XMXXS = array(
            '@attributes' =>
            array('class' => 'FPKJXX_XMXX;', 'size' => $goods_num),
        );
        $FPKJXX_DDMXXXS = array(
            '@attributes' =>
            array('class' => 'FPKJXX_DDMXXX;', 'size' => $goods_num),
        );

        $FPKJXX_DDMXXX_LIST = array();


        $FPKJXX_XMXX_LIST = array();
        $js_sell_momey = $sell_momey;

        foreach ($goods_list as $v) {
            $goods_num--;
            while ($v['num'] > 0) {
                if($v['is_gift'] == 1&&!isset($unique_data[$v['sku']])){
                    $v['num'] = $v['num'] - 1;
                    continue;
                }
                
                $unique_data_sku = $unique_data[$v['sku']];
                $unique_code_arr = array_keys($unique_data_sku);
                $unique_code = $unique_code_arr[0];
                $unique_data_row = $unique_data_sku[$unique_code];

                $avg_money = 0;
                if ($v['is_gift'] == 0) {//非赠品
                    $no_gift_goods_num--;
                    if ($no_gift_goods_num > 0) {
                        $avg_money = round(($unique_data_row['total_price'] * $sell_momey / $all_money), 2);
                        $js_sell_momey = bcsub($js_sell_momey, $avg_money, 2);
                    } else {
                        $avg_money = $js_sell_momey;
                    }
                } else {
                    $avg_money = $unique_data_row['total_price'];
                }


                $FPKJXX_XMXX = array();
                $FPKJXX_XMXX['XMMC'] = $unique_data_row['goods_name']; //项目名称
                $FPKJXX_XMXX['XMDW'] = '件';
                $FPKJXX_XMXX['GGXH'] = '';
                $FPKJXX_XMXX['XMSL'] = $this->symbol * 1;

                $FPKJXX_XMXX['HSBZ'] = 1;
                $FPKJXX_XMXX['XMDJ'] = $avg_money; //项目单价
                $FPKJXX_XMXX['FPHXZ'] = 0; //发票行性质 0正常行、 1折扣行、2被折扣行
                //获取唯一码档案中的商品税收编码
                $FPKJXX_XMXX['SPBM'] = $this->get_unique_revenue_code($unique_code);
                //$FPKJXX_XMXX['SPBM'] = $unique_code; //商品编码
                $FPKJXX_XMXX['ZXBM'] = '';
                $FPKJXX_XMXX['YHZCBS'] = 0; //优惠政策标识0：不使用， 1：使用
                $FPKJXX_XMXX['LSLBS'] = '';
                $FPKJXX_XMXX['ZZSTSGL'] = '';
                $FPKJXX_XMXX['KCE'] = '';
                $FPKJXX_XMXX['XMBM'] = '';
                $FPKJXX_XMXX['XMJE'] = $this->symbol * $avg_money; //项目金额
               // $goods_tax_arr = load_model('oms/invoice/AlibabaEinvoiceModel')->get_tax_code($unique_data_row['sku']);//获取税务编码和单位
                $FPKJXX_XMXX['SL'] = round($this->invoice_param['tax_rate'] / 100, 2); //税率 如果税率为 0，表示免税
                $SE = round($avg_money - $avg_money / (1 + $this->invoice_param['tax_rate'] / 100), 2); //不含税金额
                $FPKJXX_XMXX['SE'] = $this->symbol * $SE; // todo 税额
                $FPKJXX_XMXX['SPHXZ'] = 0; //todo
                $FPKJXX_XMXX['ZKHS'] = ''; //todo
                $FPKJXX_XMXX['BYZD1'] = '';
                $FPKJXX_XMXX['BYZD2'] = '';
                $FPKJXX_XMXX['BYZD3'] = '';
                $FPKJXX_XMXX['BYZD4'] = '';
                $FPKJXX_XMXX['BYZD5'] = '';
                //  $this->goods_se +=  $FPKJXX_XMXX['SE'];
                if ($v['is_gift'] == 1) {
                    $FPKJXX_XMXX['FPHXZ'] = 2;
                    //$FPKJXX_XMXX = $this->check_unit($FPKJXX_XMXX,$goods_tax_arr);
                    $FPKJXX_XMXX_LIST[] = $FPKJXX_XMXX;
                    $FPKJXX_XMXX['FPHXZ'] = 1;
                    $FPKJXX_XMXX['XMJE'] = -$FPKJXX_XMXX['XMJE'];
                    $FPKJXX_XMXX['SE'] = - $FPKJXX_XMXX['SE'];
                    //  $this->goods_se +=  $FPKJXX_XMXX['SE'];
                    $FPKJXX_XMXX['XMSL'] = $this->symbol * (-1);
                }
                //$FPKJXX_XMXX = $this->check_unit($FPKJXX_XMXX,$goods_tax_arr);
                $FPKJXX_XMXX_LIST[] = $FPKJXX_XMXX;
                $FPKJXX_DDMXXX = array();
                $FPKJXX_DDMXXX['DDMC'] = $unique_data_row['goods_name'];
                $FPKJXX_DDMXXX['DW'] = '件';
                $FPKJXX_DDMXXX['GGXH'] = '';
                $FPKJXX_DDMXXX['SL'] = 1;
                $FPKJXX_DDMXXX['DJ'] = $avg_money;
                $FPKJXX_DDMXXX['JE'] = $avg_money;
                $FPKJXX_DDMXXX['BYZD1'] = '';
                $FPKJXX_DDMXXX['BYZD2'] = '';
                $FPKJXX_DDMXXX['BYZD3'] = '';
                $FPKJXX_DDMXXX['BYZD4'] = '';
                $FPKJXX_DDMXXX['BYZD5'] = '';

                $FPKJXX_DDMXXX_LIST[] = $FPKJXX_DDMXXX;
                $v['num'] = $v['num'] - 1;
                //删除一个唯一码
                unset($unique_data[$v['sku']][$unique_code]);
            }
        }
        $FPKJXX_XMXXS['FPKJXX_XMXX'] = $FPKJXX_XMXX_LIST;
        $FPKJXX_XMXXS['@attributes']['size'] = count($FPKJXX_XMXX_LIST);
        $FPKJXX_DDMXXXS['FPKJXX_DDMXXX'] = $FPKJXX_DDMXXX_LIST;
        $FPKJXX_DDMXXXS['@attributes']['size'] = count($FPKJXX_DDMXXX_LIST);

        return array('FPKJXX_XMXXS' => $FPKJXX_XMXXS, 'FPKJXX_DDMXXXS' => $FPKJXX_DDMXXXS);
    }
    
    /**
     * 航信发票明细通用
     * @return type
     */
    private function set_invoice_detail() {
        $order_detail = load_model('oms/SellRecordModel')->get_detail_list_by_code($this->record_info['sell_record_code']);
        $all_momey = 0;
        $new_order_detail = array();
        $goods_num = 0;
        foreach ($order_detail as $v) {
            $all_momey += $v['avg_money'];
            $goods_num+=$v['num'];
            if (!isset($new_order_detail[$v['sku']])) {
                $s_key = array('goods_name', 'barcode', 'goods_code', 'spec1_name', 'spec2_name', 'price');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($v['sku'], $s_key);
                $v = array_merge($v, $sku_info);
                $new_order_detail[$v['sku']] = $v;
            } else {
                $new_order_detail[$v['sku']]['num'] += $v['num'];
                $new_order_detail[$v['sku']]['avg_money'] += $v['avg_money'];
            }
        }
        $FPKJXX_XMXXS = array(
            '@attributes' =>
            array('class' => 'FPKJXX_XMXX;', 'size' => $goods_num),
        );
        $FPKJXX_DDMXXXS = array(
            '@attributes' =>
            array('class' => 'FPKJXX_DDMXXX;', 'size' => $goods_num),
        );
        $FPKJXX_DDMXXX_LIST = array();
        $FPKJXX_XMXX_LIST = array();
        $num = count($new_order_detail);
        $invoice_amount = $this->invoice_record_info['invoice_amount'];//发票金额
        foreach ($new_order_detail as $val) {
            $i++;
            $tax_goods_arr = load_model('oms/invoice/AlibabaEinvoiceModel')->get_tax_code($val['sku']);//获取商品税务编码和单位
            if (empty($tax_goods_arr['tax_code'])) {
                return $this->format_ret(-1, '', $val['barcode'] . "找不到商品税务编码");
            }
            if ($val['is_gift'] == 0) {
                if ($i == $num) {
                    $item_invoice_amount = $invoice_amount;
                } else {
                    $item_invoice_amount = round(($this->invoice_record_info['invoice_amount'] * $val['avg_money']) / $all_momey, 2);
                }
            } else {
                $item_invoice_amount = $val['num'] * $val['price'];
            }
            if($this->invoice_record_info['is_red'] == 1 && $val['is_gift'] == 1){//开红票时去除赠品信息
                continue;
            }
            $FPKJXX_XMXX = array();
                $FPKJXX_XMXX['XMMC'] = $val['goods_name']; //项目名称
                //是否开启规格型号
                if($this->invoice_param['is_spec'] == 1){
                    $FPKJXX_XMXX['GGXH'] = $val['spec1_name'] . ' ' . $val['spec2_name'];
                }
                
                $FPKJXX_XMXX['XMSL'] = $this->symbol * 1;

                $FPKJXX_XMXX['HSBZ'] = 1;
                $FPKJXX_XMXX['XMDJ'] = $item_invoice_amount; //项目单价
                $FPKJXX_XMXX['FPHXZ'] = 0; //发票行性质 0正常行、 1折扣行、2被折扣行
                $FPKJXX_XMXX['SPBM'] = $tax_goods_arr['tax_code'];//商品税收编码
                $FPKJXX_XMXX['ZXBM'] = '';
                $FPKJXX_XMXX['YHZCBS'] = 0; //优惠政策标识0：不使用， 1：使用
                $FPKJXX_XMXX['LSLBS'] = '';
                $FPKJXX_XMXX['ZZSTSGL'] = '';
                $FPKJXX_XMXX['KCE'] = '';
                $FPKJXX_XMXX['XMBM'] = '';
                $FPKJXX_XMXX['XMJE'] = $this->symbol * $item_invoice_amount; //项目金额
                $FPKJXX_XMXX['SL'] = round($this->invoice_param['tax_rate'] / 100, 2); //税率 如果税率为 0，表示免税
                $SE = round($item_invoice_amount - $item_invoice_amount / (1 + $this->invoice_param['tax_rate'] / 100), 2); //不含税金额
                $FPKJXX_XMXX['SE'] = $this->symbol * $SE; // todo 税额
                $FPKJXX_XMXX['SPHXZ'] = 0; //todo
                $FPKJXX_XMXX['ZKHS'] = ''; //todo
                $FPKJXX_XMXX['BYZD1'] = '';
                $FPKJXX_XMXX['BYZD2'] = '';
                $FPKJXX_XMXX['BYZD3'] = '';
                $FPKJXX_XMXX['BYZD4'] = '';
                $FPKJXX_XMXX['BYZD5'] = '';
                //  $this->goods_se +=  $FPKJXX_XMXX['SE'];
                
                if ($val['is_gift'] == 1) {
                    $FPKJXX_XMXX['FPHXZ'] = 2;
                    if($this->invoice_param['is_unit'] == 1){//是否增加单位字段
                        $FPKJXX_XMXX = $this->check_unit($FPKJXX_XMXX,$tax_goods_arr);//给金额为正的加上单位
                    }
                    $FPKJXX_XMXX_LIST[] = $FPKJXX_XMXX;
                    $FPKJXX_XMXX['FPHXZ'] = 1;
                    $FPKJXX_XMXX['XMJE'] = -$FPKJXX_XMXX['XMJE'];
                    $FPKJXX_XMXX['SE'] = - $FPKJXX_XMXX['SE'];
                    //  $this->goods_se +=  $FPKJXX_XMXX['SE'];
                    $FPKJXX_XMXX['XMSL'] = $this->symbol * (-1);
                }
                if($this->invoice_param['is_unit'] == 1){
                    $FPKJXX_XMXX = $this->check_unit($FPKJXX_XMXX,$tax_goods_arr);//给金额为正的加上单位
                }
                $FPKJXX_XMXX_LIST[] = $FPKJXX_XMXX;
                $FPKJXX_DDMXXX = array();
                $FPKJXX_DDMXXX['DDMC'] = $val['goods_name'];
                $FPKJXX_DDMXXX['DW'] = '件';
                //是否开启规格型号
                if($this->invoice_param['is_spec'] == 1){
                    $FPKJXX_DDMXXX['GGXH'] = $val['spec1_name'] . ' ' . $val['spec2_name'];
                }
                $FPKJXX_DDMXXX['SL'] = 1;
                $FPKJXX_DDMXXX['DJ'] = $item_invoice_amount;
                $FPKJXX_DDMXXX['JE'] = $item_invoice_amount;
                $FPKJXX_DDMXXX['BYZD1'] = '';
                $FPKJXX_DDMXXX['BYZD2'] = '';
                $FPKJXX_DDMXXX['BYZD3'] = '';
                $FPKJXX_DDMXXX['BYZD4'] = '';
                $FPKJXX_DDMXXX['BYZD5'] = '';

                $FPKJXX_DDMXXX_LIST[] = $FPKJXX_DDMXXX;
        }
        $FPKJXX_XMXXS['FPKJXX_XMXX'] = $FPKJXX_XMXX_LIST;
        $FPKJXX_XMXXS['@attributes']['size'] = count($FPKJXX_XMXX_LIST);
        $FPKJXX_DDMXXXS['FPKJXX_DDMXXX'] = $FPKJXX_DDMXXX_LIST;
        $FPKJXX_DDMXXXS['@attributes']['size'] = count($FPKJXX_DDMXXX_LIST);
        return array('FPKJXX_XMXXS' => $FPKJXX_XMXXS, 'FPKJXX_DDMXXXS' => $FPKJXX_DDMXXXS);
    }
    /**
     * 根据唯一码获取唯一码档案中对应的商品税务编码
     * @param type $unique_code
     * @return type
     */
    function get_unique_revenue_code($unique_code) {
        $sql = "select unique_code,good_revenue_code from goods_unique_code_tl where unique_code = :unique_code";
        $arr = $this->db->getRow($sql, ['unique_code' => $unique_code]);
        return $arr['good_revenue_code'];
    }
    
    
    /**
     * 单位和税务编码
     * @param type $data
     * @param type $tax_goods_arr
     * @return type
     */
    function check_unit($data,$tax_goods_arr){
        if($data['XMJE'] > 0){
            $data['XMDW'] = isset($tax_goods_arr['unit'])?$tax_goods_arr['unit']:'';
        }else{
            if(isset($data['XMDW'])){
                unset($data['XMDW']);
            }
        }
        return $data;
    }
    
    //发票单据
    private function set_invocie_order() {
        $FPKJXX_DDXX = array('@attributes' =>
            array('class' => 'FPKJXX_DDXX'),);
        $FPKJXX_DDXX['DDH'] = $this->record_info['sell_record_code'];
        $FPKJXX_DDXX['DDLX'] = '9999';
        $FPKJXX_DDXX['THDH'] = '';
        $FPKJXX_DDXX['DDDATE'] = '';

//        $FPKJXX_DDXX['DDLX'] = $this->record_info['sell_record_code'];
//          <DDH>订单号</DDH>  
//    <DDLX>订单类型</DDLX>  
//    <THDH>退货单号</THDH>  
//    <DDDATE>订单时间</DDDATE>
        return $FPKJXX_DDXX;
    }

    //发票商品
    private function set_invocie_goods() {
        $FPKJXX_DDMXXXS = array(
            '@attributes' =>
            array('class' => 'FPKJXX_DDMXXX;', 'size' => '1'),
        );
        $FPKJXX_DDMXXX = array();
        $FPKJXX_DDMXXXS['FPKJXX_DDMXXX'] = $FPKJXX_DDMXXX;



        return $FPKJXX_DDMXXXS;
    }

    //发票支付
    private function set_invocie_pay() {
        $FPKJXX_ZFXX = array('@attributes' =>
            array('class' => 'FPKJXX_ZFXX'),);
        $FPKJXX_ZFXX['ZFFS'] = '';
        $FPKJXX_ZFXX['ZFLSH'] = '';
        $FPKJXX_ZFXX['ZFPT'] = '';

        return $FPKJXX_ZFXX;
    }

    //发票发货信息
    function set_invocie_shipping() {

        $FPKJXX_WLXX = array('@attributes' =>
            array('class' => 'FPKJXX_WLXX'),);
        $FPKJXX_WLXX['CYGS'] = '';
        $FPKJXX_WLXX['SHSJ'] = '';
        $FPKJXX_WLXX['WLDH'] = '';
        $FPKJXX_WLXX['SHDZ'] = '';
// <FPKJXX_WLXX class=" FPKJXX_WLXX"> 
//    <CYGS>承运公司</CYGS>  
//    <SHSJ>送货时间</SHSJ>  
//    <WLDH>物流单号</WLDH>  
//    <SHDZ>送货地址</SHDZ> 
//  </FPKJXX_WLXX> 
        return $FPKJXX_WLXX;
    }

    function create_tag_xml($data, $tag) {


        return $this->tb_xml->array2xml($data, $tag);
    }

    function xml2array($xml) {
        $data = array();
        $this->tb_xml->xml2array($xml, $data);
        return $data;
    }

    function check_invoice_amount($shop_code, $invoice_type, $amount) {
        $shop_invoice_info = $this->get_shop_invoice_info($shop_code);

        $ret = $this->get_by_id($shop_invoice_info['p_id']);
        $invoice = $ret['data'];
        $check = true;
        $max = $invoice['electron_max'];
        if ($invoice_type == 1 && $invoice['is_electron_max'] == 1) {
            $check = $invoice['electron_max'] > $amount ? true : false;
        } else if ($invoice_type == 2 && $invoice['is_paper_max'] == 1) {
            $check = $invoice['paper_max'] > $amount ? true : false;
        }
        return $check === true ? $this->format_ret(1) : $this->format_ret(-1, '开票金额' . $amount . '超过单张发票最高金额' . $max);
    }

    function get_by_id($id) {
        return $this->get_row(array('id' => $id));
    }

    function get_invocie_result() {
        $sql = "select id,sell_record_code,shop_code from oms_sell_invoice_record where status=0";
        $data = $this->db->get_all($sql);

        foreach ($data as $val) {
            $this->get_api_invocie($val);
        }

        return $this->format_ret(1);
    }

    function get_id($id, $sell_record_code) {
        $sql = "select id from oms_sell_invoice_record where sell_record_code =:sell_record_code  and  is_red=0 order by id ";
        $new_id = $this->db->get_value($sql, array(':sell_record_code' => $sell_record_code));
        if (!empty($new_id)) {
            return $new_id;
        }
        return $id;
    }

    function get_api_invocie($invoice_record) {
        static $fp_all = null;
        $ret_param = $this->get_invoice_param_by_shop_code($invoice_record['shop_code']);

        if ($ret_param['status'] < 1) {
            return $this->format_ret(-1, '', '未找到店铺对应的发票配置');
        }
        if (!isset($fp_all[$invoice_record['shop_code']])) {
            $fp_all[$invoice_record['shop_code']] = new JsFapiaoApiModel($ret_param['data']);
        }

        $fp = $fp_all[$invoice_record['shop_code']];
        //流水号
        // $id = $this->get_id($id,$invoice_record['sell_record_code']);
        $id = $invoice_record['id'];
        $no = $invoice_record['sell_record_code'] . $id;
        $id_no = $this->get_no($no); //发票请求唯一流水号
        $data = array(
            '@attributes' =>
            array('class' => 'REQUEST_FPXXXZ_NEW'),
            'FPQQLSH' => $id_no,
            'DSPTBM' => $ret_param['data']['username'],
            'NSRSBH' => $ret_param['data']['nsrsbh'],
            'DDH' => $invoice_record['sell_record_code'],
            'PDF_XZFS' => '1',
        );
        $data_xml = $this->create_tag_xml($data, 'REQUEST_FPXXXZ_NEW');


        $result = $fp->request_api('ECXML.FPXZ.CX.E_INV', $data_xml);
        if ($result['returnCode'] != '0000') {
            return $this->format_ret(-1, $result, $result['returnMessage']);
        }

        $ret_data = $this->xml2array($result['content']);
        if(isset($ret_data['REQUEST_FPKJXX_FPJGXX_NEW']['FP_HM']) && !empty($ret_data['REQUEST_FPKJXX_FPJGXX_NEW']['FP_HM'])){//上海航信特殊处理
            $ret_data['RESPONSE_FPXXXZ_NEW'] = $ret_data['REQUEST_FPKJXX_FPJGXX_NEW'];//替换节点名称
            unset($ret_data['REQUEST_FPKJXX_FPJGXX_NEW']);
        }
        
        if (isset($ret_data['RESPONSE_FPXXXZ_NEW']['FP_HM']) && !empty($ret_data['RESPONSE_FPXXXZ_NEW']['FP_HM'])) {

            $data = array();
            $key_arr = array('fpqqlsh', 'kplsh', 'fwm', 'ewm', 'fpzl_dm', 'fp_dm', 'kprq', 'kplx', 'hjbhsje', 'kphjse', 'pdf_file', 'pdf_url', 'czdm');
            foreach ($ret_data['RESPONSE_FPXXXZ_NEW'] as $key => $val) {
                $key = strtolower($key);
                if (in_array($key, $key_arr)) {
                    $data[$key] = $val;
                }
            }

            $data['invoice_no'] = $ret_data['RESPONSE_FPXXXZ_NEW']['FP_HM'];


            return $this->format_ret(1, $data);
        }
        if(isset($ret_data['REQUEST_FPKJXX_FPJGXX_NEW']['RETURNMESSAGE']) && !empty($ret_data['REQUEST_FPKJXX_FPJGXX_NEW']['RETURNMESSAGE'])){
            $message = $ret_data['REQUEST_FPKJXX_FPJGXX_NEW']['RETURNMESSAGE'];//上海航信报错信息
        }else if (isset($ret_data['RESPONSE_FPXXXZ_NEW']['RETURNMESSAGE'])){
            $message = $ret_data['RESPONSE_FPXXXZ_NEW']['RETURNMESSAGE'];//南京航信报错信息
        }else{
            $message = '返回数据异常';
        }
       // $message = isset($ret_data['RESPONSE_FPXXXZ_NEW']['RETURNMESSAGE']) ? $ret_data['RESPONSE_FPXXXZ_NEW']['RETURNMESSAGE'] : '返回数据异常';
        return $this->format_ret(-1, $ret_data, $message);
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

        $sql_values = array();
        $sql_main = "FROM {$this->table} r1 left join js_shop r2 on r1.id = r2.p_id where 1";

        //店铺查询
        if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
            $arr = explode(',', $filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $sql_main .= " AND r2.shop_code in ( " . $str . " ) ";
        }
        //企业名称查询
        if (isset($filter['nsrmc']) && $filter['nsrmc'] != '') {
            $arr = explode(',', $filter['nsrmc']);
            $str = $this->arr_to_in_sql_value($arr, 'nsrmc', $sql_values);
            $sql_main .= " AND r1.nsrmc in ( " . $str . " ) ";
        }
        $select = 'DISTINCT r1.*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    //获取发票对应的商店代码
    function get_shop_code_by_id($id) {
        $sql = "select a.* from js_shop a inner join js_fapiao b on a.p_id = b.id where b.id = :id";
        $data = $this->db->get_all($sql, array(':id' => $id));

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

    //添加系统集成参数
    function add_info($request) {
        $invoice_params = array();
        $invoice_params['invoice_config_name'] = isset($request['invoice_config_name']) ? $request['invoice_config_name'] : '';
        $invoice_params['nsrsbh'] = isset($request['nsrsbh']) ? $request['nsrsbh'] : '';
        $invoice_params['nsrmc'] = isset($request['nsrmc']) ? $request['nsrmc'] : '';
        $invoice_params['dkbz'] = isset($request['dkbz']) ? $request['dkbz'] : '0';
        $invoice_params['xhf_dz'] = isset($request['xhf_dz']) ? $request['xhf_dz'] : '';
        $invoice_params['xhf_dh'] = isset($request['xhf_dh']) ? $request['xhf_dh'] : '';
        $invoice_params['hsj_bz'] = isset($request['hsj_bz']) ? $request['hsj_bz'] : '1';
        $invoice_params['username'] = isset($request['username']) ? $request['username'] : '';
        $invoice_params['password'] = isset($request['password']) ? $request['password'] : '';
        $invoice_params['authorizationcode'] = isset($request['authorizationcode']) ? $request['authorizationcode'] : '';
        $invoice_params['kpy'] = isset($request['kpy']) ? $request['kpy'] : '';
        $invoice_params['is_same_tax'] = isset($request['is_same_tax']) ? $request['is_same_tax'] : '0';
        $invoice_params['tax_rate'] = isset($request['tax_rate']) ? $request['tax_rate'] : '0';
        $invoice_params['electron_url'] = isset($request['electron_url']) ? $request['electron_url'] : '';
        $invoice_params['is_electron_max'] = isset($request['is_electron_max']) ? $request['is_electron_max'] : '';
        $invoice_params['electron_max'] = isset($request['electron_max']) ? $request['electron_max'] : '';
        $invoice_params['paper_url'] = isset($request['paper_url']) ? $request['paper_url'] : '';
        $invoice_params['xhf_yhmc'] = isset($request['xhf_yhmc']) ? $request['xhf_yhmc'] : '';
        $invoice_params['xhf_yhzh'] = isset($request['xhf_yhzh']) ? $request['xhf_yhzh'] : '';
        $invoice_params['is_paper_max'] = isset($request['is_paper_max']) ? $request['is_paper_max'] : '0';
        $invoice_params['paper_max'] = isset($request['paper_max']) ? $request['paper_max'] : '';
        $invoice_params['paper_max'] = isset($request['is_sea']) ? $request['is_sea'] : '0';
        $invoice_params['config_type'] = isset($request['config_type']) ? $request['config_type'] : '0';
        $invoice_params['ghf_sj'] = isset($request['ghf_sj']) ? $request['ghf_sj'] : '';
        //发票额外字段
        $invoice_params['is_unit'] = isset($request['is_unit']) ? $request['is_unit'] : 0;//单位
        $invoice_params['is_spec'] = isset($request['is_spec']) ? $request['is_spec'] : 0;//型号规格
        if ($request['config_type'] == 1) {//阿里
            $invoice_params['gk_dp'] = $request['gk_dp'];
        } else {
            $invoice_params['gk_dp'] = '';
        }

        $this->begin_trans();

        $ret = $this->insert($invoice_params);

        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', "添加失败");
        }
        if (isset($request['shop']) && !empty($request['shop'])) {
            foreach ($request['shop'] as $key => $value) {
                $inv['shop_code'] = $value['shop_code'];
                if (empty($inv['shop_code'])) {
                    $this->rollback();
                    return $this->format_ret(-1, '', "店铺不能为空");
                }
                $inv['p_id'] = $ret['data'];
                $shop_data[] = $inv;
            }
            $result = load_model('sys/invoice/JsShopModel')->insert($shop_data);
        }
        $this->commit();
        return $ret;
    }

    //修改系统发票集成参数
    function edit_info($request) {
        if ($request['config_type'] != 1) {
            $request['gk_dp'] = '';
        }
        $data = get_array_vars($request, array('invoice_config_name', 'nsrsbh', 'nsrmc', 'dkbz', 'xhf_dz', 'xhf_dh', 'hsj_bz', 'username', 'password', 'authorizationcode', 'kpy', 'is_same_tax', 'tax_rate', 'electron_url', 'is_electron_max', 'electron_max', 'paper_url', 'xhf_yhmc', 'xhf_yhzh', 'is_paper_max', 'paper_max', 'is_sea', 'config_type', 'gk_dp','is_unit','is_spec'));
        if ($request['config_type'] == 2) {
            $data['ghf_sj'] = isset($request['ghf_sj']) ? $request['ghf_sj'] : '';
        }


        $this->begin_trans();
        $ret = $this->update($data, "id={$request['invoice_id']}");
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', "编辑失败");
        }
        if (isset($request['shop']) && !empty($request['shop'])) {
            foreach ($request['shop'] as $key => $value) {
                $inv['shop_code'] = $value['shop_code'];
                if (empty($inv['shop_code'])) {
                    $this->rollback();
                    return $this->format_ret(-1, '', "店铺不能为空");
                }
                $inv['p_id'] = $request['invoice_id'];
                $shop_data[] = $inv;
            }
            $ret1 = load_model('sys/invoice/JsShopModel')->delete_shop_config($request['invoice_id']);
            $result = load_model('sys/invoice/JsShopModel')->insert_multi($shop_data);
        } else {
            $ret1 = load_model('sys/invoice/JsShopModel')->delete_shop_config($request['invoice_id']);
        }
        $this->commit();
        return $ret;
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
        $sql = "SELECT DISTINCT nsrmc  AS company_name,nsrmc FROM `js_fapiao`";

        $data = $this->db->get_all($sql);

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

    /**
     * 主题店铺选择时检测是否已经被挂靠
     * @param type $shop_code
     * @return type
     */
    function check_shop_code($shop_code) {
        $sql = "SELECT * FROM `js_shop` WHERE shop_code = :shop_code";
        $data = $this->db->getAll($sql, array('shop_code' => $shop_code));
        if (!empty($data)) {
            return $this->format_ret(-1, '', "此店铺已被选择");
        }
        return $this->format_ret(1);
    }

}
