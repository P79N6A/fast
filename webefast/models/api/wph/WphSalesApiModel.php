<?php

require_model('tb/TbModel');
require_lib('apiclient/WphClient');

class WphSalesApiModel extends TbModel {

    private $wph_client = NULL;
    private $shop_code = '';
    private $sales_no = array();

    private function set_client() {
        $this->wph_client = new WphClient($this->shop_code);
    }

    /**
     * 获取专场数据
     * @return array 操作结果
     */
    function get_sales_data() {
        $shop = load_model('base/ShopModel')->get_wepinhuijit_shop();
        array_walk($shop, function($val) {
            $this->shop_code = $val['shop_code'];
            $this->set_client();
            $this->sync_sales();
            $this->get_sales_sku_data();
            $this->sync_upcoming_sales_sku();
            $this->sales_no = array();
        });

        return $this->format_ret(1);
    }

    /**
     * 同步专场信息
     * @param int $page_no 页码
     * @param int $page_size 页容量
     * @return array 操作结果
     */
    private function sync_sales($page_no = 1, $page_size = 50) {
        $items = $this->get_sales_list($page_no, $page_size);
        if (empty($items)) {
            return $this->format_ret(-1);
        }
        if ($items['total_num'] > 0 && empty($items['info'])) {
            return $this->format_ret(-1, '', '该页数据为空');
        }
        $data = array();

        if ($items['total_num'] > $page_size) {
            $page_num = ceil($items['total_num'] / $page_size);
            for ($page_no = 1; $page_no <= (int) $page_num; $page_no++) {
                $ret = $this->get_sales_list($page_no, $page_size);
                if (empty($items)) {
                    return $this->format_ret(-1);
                }
                $data = array_merge($data, $ret['info']);
                $this->sales_no = array_merge($this->sales_no, array_column($items['info'], 'sales_no'));
            }
        } else {
            $data = $items['info'];
            $this->sales_no = array_column($items['info'], 'sales_no');
        }

        $ret = load_model('api/wph/WphSalesModel')->save_sales_list($this->shop_code, $data);

        return $this->format_ret(1);
    }

    /**
     * 获取专场信息（调接口）
     * @param int $page_no
     * @param int $page_size
     * @return array 专场数据
     */
    private function get_sales_list($page_no, $page_size) {
        $params = array();
//        $params['st_query'] = 1484755200;
//        $params['st_query'] = strtotime("-60 day");
//        $params['et_query'] = strtotime("-30 day");
        $params['page'] = $page_no;
        $params['limit'] = $page_size;
        $ret = $this->wph_client->getSalesList($params);
        $items = array();
        $error = $this->analysis_error($ret);
        if ($error['status'] != 1) {
            return $items;
        }
        $items['info'] = $ret['result']['salesList'];
        $items['total_num'] = $ret['result']['total'];
        return $items;
    }

    /**
     * 获取专场SKU数据
     */
    private function get_sales_sku_data() {
        $sales_no = $this->sales_no;
        array_walk($sales_no, function($val) {
            $this->sync_sales_sku($val);
        });
    }

    /**
     * 同步专场SKU信息
     * @param int $page_no 页码
     * @param int $page_size 页容量
     * @return array 操作结果
     */
    private function sync_sales_sku($sales_no, $page_no = 1, $page_size = 100) {
        $items = $this->get_sales_sku_list($sales_no, $page_no, $page_size);
        if (empty($items)) {
            return $this->format_ret(-1);
        }
        if ($items['total_num'] > 0 && empty($items['info'])) {
            return $this->format_ret(-1, '', '该页数据为空');
        }
        $data = array();

        if ($items['total_num'] > $page_size) {
            $page_num = ceil($items['total_num'] / $page_size);
            for ($page_no = 1; $page_no <= (int) $page_num; $page_no++) {
                $ret = $this->get_sales_sku_list($sales_no, $page_no, $page_size);
                if (empty($items)) {
                    return $this->format_ret(-1);
                }
                $data = array_merge($data, $ret['info']);
            }
        } else {
            $data = $items['info'];
        }

        $ret = load_model('api/wph/WphSalesSkuModel')->save_sales_sku_list($this->shop_code, $sales_no, $data);
        return $this->format_ret(1);
    }

    /**
     * 获取专场SKU信息（调接口）
     * @param int $page_no
     * @param int $page_size
     * @return array 专场SKU数据
     */
    private function get_sales_sku_list($sales_no, $page_no, $page_size) {
        $params = array();
        $params['sales_no'] = $sales_no;
        $params['page'] = $page_no;
        $params['limit'] = $page_size;
        $ret = $this->wph_client->getSalesSkuList($params);
        $items = array();
        $error = $this->analysis_error($ret);
        if ($error['status'] != 1) {
            return $items;
        }
        $items['info'] = $ret['result']['skuList'];
        $items['total_num'] = $ret['result']['total'];
        return $items;
    }

    /**
     * 获取待售专场SKU信息（调接口）
     * @param int $page_no
     * @param int $page_size
     * @return array 结果
     */
    private function sync_upcoming_sales_sku($page_no = 1, $page_size = 100) {
        $items = $this->get_upcoming_sales_sku_list($page_no, $page_size);
        if (empty($items)) {
            return $this->format_ret(-1);
        }
        if ($items['total_num'] > 0 && empty($items['info'])) {
            return $this->format_ret(-1, '', '该页数据为空');
        }
        $data = array();

        if ($items['total_num'] > $page_size) {
            $page_num = ceil($items['total_num'] / $page_size);
            for ($page_no = 1; $page_no <= (int) $page_num; $page_no++) {
                $ret = $this->get_upcoming_sales_sku_list($page_no, $page_size);
                if (empty($items)) {
                    return $this->format_ret(-1);
                }
                $data = array_merge($data, $ret['info']);
            }
        } else {
            $data = $items['info'];
        }

        $ret = load_model('api/wph/WphSalesSkuModel')->save_upcoming_sales_sku_list($this->shop_code, $data);
        return $this->format_ret(1);
    }

    /**
     * 获取待售专场SKU信息（调接口）
     * @param int $page_no
     * @param int $page_size
     * @return array 待售专场SKU数据
     */
    private function get_upcoming_sales_sku_list($page_no, $page_size) {
        $params = array();
        $params['brand_id'] = '90018124';
        $params['page'] = $page_no;
        $params['limit'] = $page_size;
        $ret = $this->wph_client->getUpcomingSalesSkus($params);
        $items = array();
        $error = $this->analysis_error($ret);
        if ($error['status'] != 1) {
            return $items;
        }
        $items['info'] = $ret['result']['upcomingSalesSkus'];
        $items['total_num'] = $ret['result']['total'];
        return $items;
    }

    /**
     * 更新专场SKU的库存，目标库存是独享库存（调接口）
     * @param array $inventories 商品库存数据
     * @param boolean $is_full 是否全量
     * @return array 同步结果
     */
    private function update_sales_skus_inventory($shop_code, $inventories, $is_full) {
        $this->shop_code = $shop_code;
        $this->set_client();
        $params = array();
        $params['is_full'] = $is_full;
        $params['inventories'] = $inventories;
        $ret = $this->wph_client->updateSalesSkusInventory($params);
        $items = array();
        $error = $this->analysis_error($ret);
        if ($error['status'] != 1) {
            return $items;
        }
        $items['success_list'] = $ret['result']['successList'];
        $items['failed_list'] = $ret['result']['failedList'];
        return $items;
    }

    /**
     *
     * 通用识别错误
     * @param array $result 淘宝返回参数
     */
    function analysis_error($result) {
        if (empty($result) || (isset($result['code']) && $result['code'] == '-1')) {
            return $this->format_ret(-1, '唯品会JIT接口连接超时或数据解析错误.');
        }
        if (isset($result['returnCode'])) {
            if ($result['returnCode'] !== '0') {
                return $this->format_ret(-1, $result['returnMessage']);
            } else {
                return $this->format_ret(1);
            }
        }
        return $this->format_ret(-1);
    }

}
