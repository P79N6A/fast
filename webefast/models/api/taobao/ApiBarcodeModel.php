<?php

require_lib('net/HttpClient');
require_model('tb/TbModel');

class ApiBarcodeModel extends TbModel {

    private $api_data = array();

    function set_api_update($shop_code, $goods_code = '') {
        $sql = "select g.goods_code,g.goods_from_id from api_goods g "
                . "inner join base_goods b ON g.goods_code =  b.goods_code  "
                . " where g.shop_code='{$shop_code}'  ";

        if (!empty($goods_code)) {
            $sql .= " AND g.goods_code='{$goods_code}'  ";
        }

        $data = $this->db->get_all($sql);

        $this->api_data = array();
        foreach ($data as $goods) {
            $barcode_arr = $this->get_barcode_by_goods_code($goods['goods_code']);
            $api_sku_arr = $this->get_api_sku_by_goods_from_id($goods['goods_from_id']);

            $this->get_update_data($barcode_arr, $api_sku_arr);
        }
        $this->api_update_barcode($shop_code);
    }

    function api_update_barcode($shop_code) {
        $this->set_shop_api($shop_code);
        foreach ($this->api_data as $val) {

            $ret = $this->request_send('taobao.item.sku.update', $val);



            var_dump('参数' . var_export($val, true), '结果' . var_export($ret, true));
            die;
            sleep(1);
        }
    }

    private $app_key = '';
    private $secret = '';
    private $session = '';

    function set_shop_api($shop_code) {
        $row = $this->db->get_row("select * from base_shop_api where shop_code=:shop_code", array(':shop_code' => $shop_code));
        $api = json_decode($row['api'], TRUE);
        $this->app_key = $api['app_key'];
        $this->secret = $api['app_secret'];
        $this->session = $api['session'];
    }

    public $gate = 'http://gw.api.taobao.com/router/rest';
    public $https_gate = 'https://eco.taobao.com/router/rest';

    public function request_send($api, $param = array(), $https = false) {
        //增加系统级参数
        $data['method'] = $api;
        $data['timestamp'] = date('Y-m-d H:i:s');
        $data['format'] = 'json';
        $data['app_key'] = $this->app_key;
        $data['v'] = '2.0';
        $data['sign_method'] = 'md5';
        $data['session'] = $this->session;
        //$data['nick'] = $this->seller_nick;
        //封装签名
        $data = array_merge($data, $param);
        $sign = $this->sign($data);
        $data['sign'] = $sign;
        //发送请求
        $url = $https ? $this->https_gate : $this->gate;
        $result = $this->exec($url, $data);
        //dump($data);
        return $result;
    }

    public function exec($url, $parameters, $type = 'post', $headers = array()) {
        $h = new HttpClient();
        $h->newHandle('0', $type, $url, $headers, $parameters);
        $h->exec();

        $result = $h->responses();
        if (!isset($result['0'])) {
            throw new Exception('请求出错, 返回结果错误');
        }

        return $result['0'];
    }

    /**
     * 生成淘宝API请求签名
     * @author 
     * @date 
     * @todo 签名方法
     * @param array $param 待签名参数
     * @return string 返回签名
     */
    public function sign($param = array()) {
        $sign = $this->secret;
        ksort($param);
        foreach ($param as $k => $v) {
            if ("@" != substr($v, 0, 1)) {
                $sign .= "$k$v";
            }
        }
        unset($k, $v);
        $sign .= $this->secret;

        return strtoupper(md5($sign));
    }

    function get_barcode_by_goods_code($goods_code) {
        $sql = "select b.barcode,b.spec1_code,b.spec2_code,b.spec1_name,b.spec2_name    from  goods_sku b "

                . " where b.goods_code = '{$goods_code}' ";

        $data = $this->db->get_all($sql);
        $ret_data = array();
        foreach ($data as $val) {
            $spec1_name = trim($val['spec1_name']);
            $spec2_name = trim($val['spec2_name']);
            $ret_data[$spec1_name . $spec2_name] = $val;
        }
        return $ret_data;
    }

    function get_api_sku_by_goods_from_id($goods_from_id) {
        $sql = "select b.sku_properties,b.sku_properties_name,sku_id,b.goods_from_id   from  api_goods_sku b "
                . " where b.goods_from_id = '{$goods_from_id}' ";

        return $this->db->get_all($sql);
    }

    function get_update_data(&$barcode_arr, &$api_sku_arr) {

        //1627207:132069:颜色分类:啡色2971F;148242406:3609877:尺码:120cm
        foreach ($api_sku_arr as $sku_val) {
            $this->set_api_data($sku_val, $barcode_arr);
        }
    }

    private function set_api_data(&$sku_val, &$barcode_arr) {

        $name_arr = $this->get_api_properties_name($sku_val['sku_properties_name']);

        if (count($name_arr) > 1) {
            $key1 = $name_arr[0] . $name_arr[1];
            $key2 = $name_arr[1] . $name_arr[0];
            $barcode = '';
            if (isset($barcode_arr[$key1])) {
                $barcode = $barcode_arr[$key1]['barcode'];
            } elseif (isset($barcode_arr[$key2])) {
                $barcode = $barcode_arr[$key1]['barcode'];
            }
            if ($barcode != '') {
                $this->api_data[] = array('num_iid' => $sku_val['goods_from_id'], 'properties' => $sku_val['sku_properties'], 'outer_id' => $barcode);
            }
        }
    }

    function get_api_properties_name($sku_properties_name) {

        $properties_arr = explode(';', $sku_properties_name);

        $name_arr = array();
        if (count($properties_arr) > 1) {
            foreach ($properties_arr as $properties) {
                $arr = explode(':', $properties);
                $count = count($arr);
                if ($count > 1) {
                    $name_arr[] = $arr[$count - 1];
                }
            }
        }
        return (count($name_arr) > 1) ? $name_arr : array();
    }

}
