<?php

require_model('tb/TbModel');

/**
 * 获取物流单号
 * @author WMH
 */
class DeliverLogisticsModel extends TbModel {

    private $print_type;
    private $is_more_package;
    private $message;
    private $sell_record_code;

    function __construct() {
        //是否开启多包裹
        $param_arr = load_model('sys/SysParamsModel')->get_val_by_code('is_more_deliver_package');
        $this->is_more_package = isset($param_arr['is_more_deliver_package']) ? $param_arr['is_more_deliver_package'] : 0;

        parent::__construct();
    }

    /**
     * 获取物流单号
     * @param string $print_type 打印类型 alpha-无界热敏
     * @param int $is_all 是否获取波次下所有订单快递号
     * @param array $param 波次单id、发货单id
     * @return array
     */
    public function get_logistics($print_type, $is_all, $param) {
        $method = 'getLogisticsBy' . ucfirst($print_type);
        if (!method_exists($this, $method)) {
            return $this->format_ret(-1, '', '所选打印方式不存在');
        }
        $this->print_type = $print_type;

        //校验参数
        $ret_check = $this->checkData($is_all, $param);
        if ($ret_check['status'] < 1) {
            return $ret_check;
        }
        $waves_id = $param['waves_record_id'];

        //获取订单数据
        $record_data = $this->getRecordData($waves_id, $ret_check['data']);
        if ($record_data['status'] < 1) {
            return $record_data;
        }
        //获取电子面单号
        $ret = $this->$method($record_data['data'], $param);

        return $ret;
    }

    /**
     * 校验接收数据
     * @param int $is_all 是否获取波次下所有订单快递号
     * @param array $param 波次单id、发货单id
     * @return array
     */
    private function checkData($is_all, $param) {
        if (empty($param['waves_record_id']) || ($is_all == 0 && empty($param['record_ids']))) {
            return $this->format_ret(-1, '', '系统参数有误');
        }
        $waves_id = $param['waves_record_id'];
        //校验波次单是否存在
        $wave_num = $this->db->get_value('SELECT COUNT(1) FROM oms_waves_record WHERE is_cancel = 0 AND waves_record_id=:_id', [':_id' => $waves_id]);
        if ($wave_num < 1) {
            return $this->format_ret(-1, '', '波次单不存在或已取消');
        }

        $deliver_id_arr = [];
        if ($is_all == 1) {
            $sql = 'SELECT deliver_record_id FROM oms_deliver_record WHERE waves_record_id=:_id AND is_cancel=0';
            $deliver_id_arr = $this->db->get_all_col($sql, [':_id' => $param['waves_record_id']]);
        } else {
            $deliver_id_arr = explode(',', $param['record_ids']);
        }
        $deliver_id_arr = is_array($deliver_id_arr) ? $deliver_id_arr : [$deliver_id_arr];

        return $this->format_ret(1, $deliver_id_arr);
    }

    /**
     * 获取发货单数据
     * @param int $waves_record_id 波次单id
     * @param array $deliver_id_arr 发货单id集合
     * @return array 发货单数据
     */
    private function getRecordData($waves_record_id, $deliver_id_arr) {
        $sql_values = [':_id' => $waves_record_id];
        $deliver_id_str = $this->arr_to_in_sql_value($deliver_id_arr, 'deliver_record_id', $sql_values);
        $sql_wh = " AND dr.is_cancel=0 AND dr.waves_record_id=:_id AND dr.deliver_record_id IN($deliver_id_str)";
        $select = 'dr.deliver_record_id,dr.sell_record_code,dr.deal_code,dr.deal_code_list,dr.sale_channel_code,dr.store_code,dr.shop_code,dr.pay_type,dr.pay_code,dr.goods_num,dr.record_time,dr.goods_weigh,dr.real_weigh,dr.weigh_express_money,dr.express_code,dr.express_no,dr.package_no,dr.waves_record_id,dr.order_money,dr.goods_money,dr.express_money,dr.delivery_money,dr.paid_money,dr.payable_money,dr.buyer_name,dr.receiver_name,dr.receiver_country,dr.receiver_province,dr.receiver_city,dr.receiver_district,dr.receiver_street,dr.receiver_address,dr.receiver_addr,dr.receiver_mobile,dr.receiver_phone';

        if ($this->is_more_package == 0) {
            $sql = "SELECT {$select} FROM oms_deliver_record AS dr WHERE dr.express_no='' {$sql_wh}";
            $data = $this->db->get_all($sql, $sql_values);
            if (empty($data)) {
                return $this->format_ret(-1, '', '请选择未匹配快递单号的订单');
            }
        } else {
            $sql = "SELECT {$select}, rp.express_no AS package_express_no FROM oms_deliver_record AS dr LEFT JOIN oms_deliver_record_package AS rp ON dr.sell_record_code=rp.sell_record_code AND dr.waves_record_id=rp.waves_record_id AND dr.package_no=rp.package_no WHERE 1 {$sql_wh} HAVING package_express_no='' OR package_express_no IS NULL";
            $data = $this->db->get_all($sql, $sql_values);
            if (empty($data)) {
                return $this->format_ret(-1, '', '请选择未匹配快递单号的订单');
            }
        }

        return $this->format_ret(1, $data);
    }

    /**
     * 获取无界热敏单号
     * @todo 一键获取多包裹运单号的可以单独写方法处理,京东电子面单支持一单获取多单号
     * @param array $data 发货单数据
     * @param array $other_param 其他配置参数
     * @return array
     */
    private function getLogisticsByAlpha($data, $other_param = []) {
        $this->message = ['success' => [], 'error' => []];
        //匹配订单收货地址
        $receiver_area = $this->getAreaArr($data);
        //获取特殊地址对照
        $jd_area = load_model('base/TaobaoAreaModel')->get_out_area_id('jingdong', 1);
        //京东销售平台
        $salePlatform = ['jingdong' => '0010001', 'taobao' => '0010002', 'other' => '0030001'];

        //接口请求前数据预处理
        $params = array();
        foreach ($data as $row) {
            $this->sell_record_code = $row['sell_record_code'];
            if ($row['pay_type'] === 'cod') {
                $this->setMessage(0, '暂不支持货到付款业务');
                continue;
            }
            //订单解密
            $record_decrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($row['sell_record_code']);
            if (empty($record_decrypt_info)) {
                $this->setMessage(0, '订单数据解密失败');
                continue;
            }
            $row = array_merge($row, $record_decrypt_info);
            $provider = load_model('remin/WujieModel')->get_provider_by_express_code($row['express_code']);
            if ($provider['status'] < 1) {
                $this->setMessage(0, $provider['message']);
                continue;
            }
            $provider = $provider['data'];
            $temp = [];
            $temp['waybillType'] = 1; //普通运单
            $temp['waybillCount'] = 1; //所需运单的数量，顺丰只能传1，非顺丰快递公司最多只能传99
            $temp['providerId'] = empty($provider['provider_id']) ? '' : $provider['provider_id']; //承运商id
            $temp['providerCode'] = empty($provider['provider_code']) ? '' : $provider['provider_code']; //承运商code
            $temp['branchCode'] = empty($provider['branch_code']) ? '' : $provider['branch_code']; //网点编码-加盟型快递
            $temp['settlementCode'] = empty($provider['settlement_code']) ? '' : $provider['settlement_code']; //财务结算编码-直营型快递
            $temp['salePlatform'] = isset($salePlatform[$row['sale_channel_code']]) ? $salePlatform[$row['sale_channel_code']] : $salePlatform['other']; //销售平台
            $temp['platformOrderNo'] = $row['deal_code_list']; //平台订单号
            $temp['vendorOrderCode'] = $row['sell_record_code'] . '_' . $row['package_no']; //商家自有订单号
            //获取发货人信息,地址使用京东承运商签约发货地址
            $store = load_model('base/ArchiveSearchModel')->get_archives_map('store', $row['store_code'], 2, 'shop_contact_person AS contact,contact_phone AS mobile,contact_phone AS phone');
            $store = $store[$row['store_code']];
            $temp['fromAddress'] = json_decode($provider['address_json'], TRUE); //京标发货地址
            $temp['fromAddress'] = array_merge($temp['fromAddress'], $store);
            $temp['toAddress'] = [
                'provinceId' => '',
                'provinceName' => isset($jd_area[$row['receiver_province']]) ? $jd_area[$row['receiver_province']] : $receiver_area[$row['receiver_province']],
                'cityId' => '',
                'cityName' => isset($jd_area[$row['receiver_city']]) ? $jd_area[$row['receiver_city']] : $receiver_area[$row['receiver_city']],
                'countryId' => '',
                'countryName' => isset($jd_area[$row['receiver_district']]) ? $jd_area[$row['receiver_district']] : $receiver_area[$row['receiver_district']],
                'countrysideId' => '',
                'countrysideName' => isset($jd_area[$row['receiver_street']]) ? $jd_area[$row['receiver_street']] : $receiver_area[$row['receiver_street']],
                'address' => mb_substr(addslashes($row['receiver_address']), 0, 100, 'UTF-8'),
                'contact' => $row['receiver_name'],
                'phone' => empty($row['receiver_phone']) ? $row['receiver_mobile'] : $row['receiver_phone'],
                'mobile' => empty($row['receiver_mobile']) ? $row['receiver_phone'] : $row['receiver_mobile'],
            ]; //京标收货地址
            $temp['weight'] = $row['real_weigh']; //重量
            $temp['volume'] = 0; //体积
            $temp['goodsName'] = $this->getGoodsInfo($row['deliver_record_id']); //商品名称
            $temp['goodsName'] = mb_substr($temp['goodsName'], 0, 100, 'UTF-8');
            $temp['promiseTimeType'] = 0; //承诺时效,无则默认0
            $temp['promiseOutStockTime'] = ''; //计划出库时间
            $temp['payType'] = 0; //0-在线支付,目前暂时不支持货到付款业务
            $temp['goodsMoney'] = round($row['goods_money'], 2); //商品金额
            $temp['shouldPayMoney'] = round($row['payable_money'], 2); //代收金额
            $temp['needGuarantee'] = FALSE; //是否要保价（系统暂不开放报价业务）
            $temp['guaranteeMoney'] = 0.0; //报价金额
            $temp['receiveTimeType'] = 0; //收货时间类型，0任何时间，1工作日2节假日
            $temp['remark'] = $row['order_remark']; //备注
            if ($row['express_code'] == 'SF') {//顺丰特殊字段值
                $temp['expressPayMethod'] = $other_param['express_pay_method']; //快递费付款方式(顺丰必填)
                $temp['expressType'] = $other_param['express_type']; //快件产品类别(顺丰必填)
            }
            $params[$provider['shop_code']][$row['deliver_record_id']] = $temp;
        }
        unset($data, $row);

        if (!empty($params)) {
            //接口请求
            require_lib('apiclient/JdClient');

            array_walk($params, [$this, "alphaClient"]);
        }

        $success_num = count($this->message['success']);
        $error_num = count($this->message['error']);
        if ($error_num > 0) {
            $msg = $this->create_fail_file('订单号,错误信息', $this->message['error'], '无界电子面单获取错误信息');
            return $this->format_ret(-1, '', "获取成功：{$success_num}单，失败：{$error_num}单{$msg}");
        }

        return $this->format_ret(1, '', '获取成功：' . $success_num . '单');
    }

    /**
     * 无界电子面单接口请求
     * @param array $record_arr 单据数据
     * @param string $shop_code 店铺代码
     * @param array $info 
     */
    public function alphaClient($record_arr, $shop_code) {
        $client = new JdClient($shop_code);
        $waybill_arr = $client->ldopAlphaWaybillReceive($record_arr);

        foreach ($waybill_arr as $_id => $waybill) {
            $code = explode('_', $record_arr[$_id]['vendorOrderCode']);
            $sell_record_code = $code[0];
            $this->sell_record_code = $sell_record_code;
            $waybill = json_decode($waybill, TRUE);
            if ($waybill == NULL) {
                $this->setMessage(0, '接口返回数据解析失败');
                continue;
            }
            if ($waybill['statusCode'] != 0) {
                $this->setMessage(0, $waybill['statusMessage']);
                continue;
            }
            if (empty($waybill['data']['waybillCodeList'])) {
                $this->setMessage(0, '接口未返回快递单号');
                continue;
            }
            //调动接口获取大头笔信息
            $waybillCode = $waybill['data']['waybillCodeList'][0];
            $params = ['waybillCode' => $waybillCode, 'providerId' => $record_arr[$_id]['providerId']];
            $bigshot = $client->ldopAlphaVendorBigshotQuery($params);
            /*
              if ($bigshot['status'] < 1) {
              $this->setMessage(0, '获取运单号成功,大头笔信息获取失败');
              continue;
              } */
            $bigshot = empty($bigshot) ? '{}' : json_encode($bigshot['data']);

            $this->begin_trans();
            try {
                $express_data = array('express_no' => $waybillCode, 'express_data' => $bigshot);
                load_model('oms/DeliverRecordModel')->save_sell_record_express_no($sell_record_code, $express_data, $type = 'alpha');

                $this->setMessage(1, (string) $express_data['express_no']);
                $this->commit();
            } catch (Exception $e) {
                $this->rollback();
                $this->setMessage(0, $e->getMessage());
            }
        }
    }

    /**
     * 匹配地址信息
     * @param array $data 订单数据
     * @return array
     */
    private function getAreaArr(&$data) {
        $area_fld = ['receiver_province', 'receiver_city', 'receiver_district', 'receiver_street'];
        $area_arr = [];
        foreach ($area_fld as $fld) {
            $area_arr = array_merge($area_arr, array_column($data, $fld));
        }
        $area_arr = array_unique($area_arr);
        $receiver_area = load_model('base/ArchiveSearchModel')->get_archives_map('area', $area_arr);

        return $receiver_area;
    }

    /**
     * 获取面单商品信息
     * @param int $deliver_record_id 发货单id
     * @return string
     */
    private function getGoodsInfo($deliver_record_id) {
        $sql = 'SELECT group_concat(bg.goods_name) FROM oms_deliver_record_detail rd,base_goods bg WHERE rd.goods_code=bg.goods_code AND deliver_record_id=:_id';
        return $this->db->get_value($sql, ['_id' => $deliver_record_id]);
    }

    private function setMessage($status, $msg) {
        if ($status == 1) {
            $this->message['success'][] = $msg;
        } else {
            $this->message['error'][] = [$this->sell_record_code, $msg];
        }
    }

    /**
     * 错误信息下载
     * @param $fail_top
     * @param $error_msg
     * @return string
     */
    private function create_fail_file($fail_top, $error_msg, $name = 'get_waybill_error') {
        $file_name = $this->api_create_import_fail_files($fail_top, $error_msg, $name);
        $url = set_download_csv_url($file_name, array('export_name' => 'error'));
        $message = "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        return $message;
    }

    /**
     * 写入错误信息
     * @param $fail_top 表头
     * @param $error_msg  内容
     * @return string
     */
    private function api_create_import_fail_files($fail_top, $error_msg, $name = 'get_waybill_error') {
        $file_str = is_array($fail_top) ? implode(",", $fail_top) . "\n" : $fail_top . "\n";
        foreach ($error_msg as $val) {
            $val_data = is_array($val) ? implode("\t,", $val) : $val;
            $file_str .= $val_data . "\r\n";
        }
        $filename = md5($name . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

    /**
     * 无界热敏解绑运单号
     * @param array $params
     * @return array
     */
    public function unbind_express($params) {
        $sql = "SELECT sr.sell_record_code,sr.deal_code_list,dr.express_code,dr.express_no,dr.express_data,dr.package_no FROM oms_sell_record sr INNER JOIN oms_deliver_record dr ON sr.sell_record_code = dr.sell_record_code AND sr.express_no=dr.express_no WHERE sr.sell_record_code = :_code AND dr.express_no <>''";
        $record = $this->db->get_row($sql, [':_code' => $params['sell_record_code']]);
        if (empty($record)) {
            return $this->format_ret(-1, '', '未找到订单');
        }
        if (empty($record['express_no'])) {
            return $this->format_ret(-1, '', '订单未获取无界热敏物流');
        }

        $ret = load_model('remin/WujieModel')->get_provider_by_express_code($record['express_code']);
        if ($ret['status'] < 1) {
            return $ret;
        }

        $sql_values = [':record_code' => $record['sell_record_code'], ':express_no' => $record['express_no']];

        $this->db->query("UPDATE oms_deliver_record SET express_no='',express_data='' WHERE sell_record_code=:record_code AND express_no=:express_no", $sql_values);
        $this->db->query("UPDATE oms_sell_record SET express_no='',express_data='' WHERE sell_record_code=:record_code AND express_no=:express_no", $sql_values);
        $sql_values[':package_no'] = $record['package_no'];
        load_model('oms/DeliverRecordModel')->handle_deliver_package_data($record);
        $this->db->query("DELETE FROM oms_deliver_record_package WHERE sell_record_code=:record_code AND package_no=:package_no AND express_no=:express_no", $sql_values);

        $params = [
            'waybillCode' => $record['express_no'],
            'providerCode' => $ret['data']['provider_code'],
            'shop_code' => $ret['data']['shop_code'],
            'sell_record_code' => $record['sell_record_code'],
            'express_no' => $record['express_no'],
        ];
        $this->wujie_wlbwaybillicancle($params);

        return $this->format_ret(1);
    }

    /**
     * 无界热敏解绑运单号调接口
     * @param array $params
     * @return array
     */
    public function wujie_wlbwaybillicancle($params) {
        //事务内暂存不处理,事务结束后调用接口
        if ($this->is_transaction() === true) {
            $this->add_transaction_after('oms/DeliverLogisticsModel', 'wujie_wlbwaybillicancle', $params);
            return $this->format_ret(1);
        }

        require_lib('apiclient/JdClient');
        $client = new JdClient($params['shop_code']);
        $api_params = ['waybillCode' => $params['express_no'], 'providerCode' => $params['providerCode']];
        $ret = $client->ldopAlphaWaybillApiUnbind($api_params);
        if ($ret['status'] < 1) {
            load_model('oms/SellRecordModel')->add_action($params['sell_record_code'], '解绑运单号', '快递单号:' . $params['express_no'] . ',无界热敏解绑运单号失败:' . $ret['message']);
            return $ret;
        }

        load_model('oms/SellRecordModel')->add_action($params['sell_record_code'], '解绑运单号', '快递单号:' . $params['express_no'] . ',无界热敏解绑运单号');

        return $this->format_ret(1, '解绑运单号成功');
    }

    /**
     * 运单号取消
     * @param $record 订单数据
     * @return array
     */
    public function cancel_waybill($record) {
        $ret = load_model('remin/WujieModel')->get_provider_by_express_code($record['express_code']);
        if ($ret['status'] == -1) {
            return $ret;
        }
        if ($ret['status'] == 1) {
            return $this->unbind_express($record);
        }

        $ret = load_model('oms/DeliverRecordModel')->cancle_cainiao_wlb_waybil_by_sell_record($record);
        return $ret;
    }

}
