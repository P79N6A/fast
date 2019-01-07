<?php

class Bserp2ArchiveModel extends TbModel {

    protected $api_mod;
    protected $record_model;
    protected $config;
    private $api_record = array();

    function __construct(&$record_model, $config) {
        parent::__construct();
        $this->record_model = $record_model;
        $this->config = $config;

        $this->create_api($config['api_config']);
    }

    function create_api($api_conf) {
        require_lib('apiclient/BserpClient');
        $this->api_mod = new BserpClient($api_conf);
    }

    function sync_info($param) {
        //page 、$page_size;

        $api_param = $this->get_api_param($param);
        $api_data = $this->api_mod->get_goods($api_param);
        $ret = array();
        if (isset($api_data['response']) && $api_data['response']['flag'] == 'success') {
            $items_data = &$api_data['response']['items']['item'];

            if (isset($items_data['goodsCode'])) {//一条数据处理
                $items_data = array($items_data);
            }
            $data = array();


            foreach ($items_data as $val) {
                $data[] = array(
                    'mid_code' => $this->config['mid_code'],
                    'api_product' => $this->config['api_product'],
                    'type_code' => 'sku',
                    'api_code' => $val['itemCode'],
                    'api_name' => $val['itemName'],
                    'sys_code' => $val['itemCode'],
                    'goods_code' => $val['goodsCode'],
                    'spec1_code' => $val['color'],
                    'spec2_code' => $val['size'],
                    'api_update_time' => $val['updateTime'],
                    'down_time' => date('Y-m-d H:i:s'),
                    'api_json_data' => json_encode($val),
                );
            }

            //保存本次下载时间
            $totel = (int) $api_data['response']['total'];
            $items_num = count($items_data);
            $down_num = ($param['page'] - 1) * $param['page_size'] + $items_num;
            if ($param['page_size'] > $items_num || $totel == $down_num) {
                $this->save_api_record();
            }

            $ret = $this->format_ret(1, $data);
        } else {
            $msg = isset($api_data['response']['message']) ? $api_data['response']['message'] : '接口数据异常';
            $ret = $this->format_ret(-1, $api_data, $msg);
        }



        return $ret;
    }

    private function get_api_param($param) {

        $api_param = array(
            'page' => $param['page'],
            'pageSize' => $param['page_size'],
        );
        //todo:如果出现很大时间差 可以用 档案表的 api_update_time
        if (empty($this->api_record)) {
            $api_name = 'get.Goods';
            $this->api_record = load_model('mid/MidBaseModel')->get_api_record($this->config['join_config']['mid_code'], $api_name);
            if (!empty($this->api_record) && !empty($this->api_record['end_time'])) {
                $api_param['start_time'] = $this->api_record['end_time'];
            } else {
                $this->api_record = array(
                    'mid_code' => $this->config['join_config']['mid_code'],
                    'api_product' => 'bserp',
                    'api_name' => $api_name,
                    'start_time' => '',
                    'end_time' => '',
                    'request_data' => '',
                    'api_request_time' => time(),
                );
            }
        }
        return $api_param;
    }

    private function save_api_record() {
        $this->api_record['end_time'] = date('Y-m-d H:i:s');
        load_model('mid/MidBaseModel')->save_api_record($this->api_record);
    }

    /**
     * 转换同步信息
     * @param type $api_data
     */
    function conversion_info($api_data) {
        $data = array();
        $data['goods'] = array(
            'goods_code' => $api_data['goodsCode'],
            'goods_name' => $api_data['itemName'],
            'goods_short_name' => $api_data['shortName'],
            'unit_code' => $api_data['stockUnit'],
            'weight' => $api_data['netWeight'],
            'category_name' => $api_data['categoryName'],
            'category_code' => $api_data['categoryId'],
            'sell_price' => $api_data['retailPrice'], //零售
            'cost_price' => $api_data['costPrice'], //吊牌
            'buy_price' => $api_data['purchasePrice'],
            'season_code' => $api_data['seasonCode'],
            'season_name' => $api_data['seasonName'],
            'brand_code' => $api_data['brandCode'],
            'brand_name' => $api_data['brandName'],
            'year_code' => $api_data['year'],
            'year_name' => $api_data['year'],
            'status' => $api_data['isValid'] == 'N' ? 1 : 0, //商品停用状态 N 表示停用 Y 表示未停用
        );
        $api_data['barCode'] = str_replace('；', ';', $api_data['barCode']);
        $sku_arr = explode(';', $api_data['barCode']);
        //  var_dump( $api_data['barCode'],$sku_arr);
        $data['sku'] = array(
            'goods_code' => $api_data['goodsCode'],
            'spec1_code' => $api_data['color'],
            'spec2_code' => $api_data['size'],
        );
        $data['sku']['sku'] = !empty($sku_arr[0]) ? $sku_arr[0] : $api_data['itemCode'];
        //条码不存在用SKU 代替条码
        $data['sku']['barcode'] = !empty($sku_arr[1]) ? $sku_arr[1] : $data['sku']['sku'];


        $skuProperty = explode(';', $api_data['skuProperty']);
        $data['spec1'] = array(
            'spec1_code' => $api_data['color'],
            'spec1_name' => $skuProperty[0],
        );
        $data['spec2'] = array(
            'spec2_code' => $api_data['size'],
            'spec2_name' => $skuProperty[1],
        );
        if (!empty($api_data['brandCode'])) {
            $data['brand'] = array(
                'brand_code' => $api_data['brandCode'],
                'brand_name' => $api_data['brandName'],
            );
        }
        if (!empty($api_data['categoryName'])) {
            $data['category'] = array(
                'category_name' => $api_data['categoryName'],
                'category_code' => $api_data['categoryId'],
            );
        }
        if (!empty($api_data['seasonName'])) {
            $data['season'] = array(
                'season_code' => $api_data['seasonCode'],
                'season_name' => $api_data['seasonName'],
            );
        }
        if (!empty($api_data['year'])) {
            $data['year'] = array(
                'year_code' => $api_data['year'],
                'year_name' => $api_data['year'],
            );
        }
        return $data;
    }

}
