<?php

require_model('tb/TbModel');
require_lib('util/oms_util', true);

class PurAdviseModel extends TbModel {

    public $pur_advise_param = array();
    public $record_date = '';

    function __construct() {
        parent::__construct();
        $this->pur_advise_param = array('week_proportion', 'month_proportion', 'pur_advise_day');
        $this->record_date = date('Y-m-d', strtotime('-1 day'));
        //$this->record_date = date('Y-m-d');
    }

    function get_record_new() {
        $sql = "select * from pur_advide_record where record_date=:record_date";
        $data = $this->db->get_row($sql, array(':record_date' => $this->record_date));
        return $this->format_ret(1, $data);
        //$this->record_date
    }

    /*
     * 获取补货数据列表
     */

    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_main = " FROM pur_advide_detail d "
                . " INNER JOIN base_goods g ON g.goods_code=d.goods_code "
                . " INNER JOIN pur_advide_inv i  ON i.sku=d.sku WHERE d.record_date=:record_date ";
        $sql_values = array(':record_date' => $this->record_date);
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('i.store_code', $filter_store_code);

        if (isset($filter['brand_code']) && $filter['brand_code'] != '') {
            $sql_main .= " AND g.brand_code =:brand_code ";
            $sql_values[':brand_code'] = $filter['brand_code'];
        }
        //季节
        if (isset($filter['brand_code']) && $filter['brand_code'] != '') {
            $brand_code_arr = explode(',', $filter['brand_code']);
            if (!empty($brand_code_arr)) {
                $sql_main .= " AND (";
                foreach ($brand_code_arr as $key => $value) {
                    $param_brand = 'param_brand' . $key;
                    if ($key == 0) {
                        $sql_main .= " g.brand_code = :{$param_brand} ";
                    } else {
                        $sql_main .= " or g.brand_code = :{$param_brand} ";
                    }

                    $sql_values[':' . $param_brand] = $value;
                }
                $sql_main .= ")";
            }
        }
        if (isset($filter['category_code']) && $filter['category_code'] != '') {
            $category_code_arr = explode(',', $filter['category_code']);
            if (!empty($category_code_arr)) {
                $sql_main .= " AND (";
                foreach ($category_code_arr as $key => $value) {
                    $param_category = 'param_category' . $key;
                    if ($key == 0) {
                        $sql_main .= " g.category_code = :{$param_category} ";
                    } else {
                        $sql_main .= " or g.category_code = :{$param_category} ";
                    }

                    $sql_values[':' . $param_category] = $value;
                }
                $sql_main .= ")";
            }
        }


        //季节
        if (isset($filter['season_code']) && $filter['season_code'] != '') {
            $season_code_arr = explode(',', $filter['season_code']);
            if (!empty($season_code_arr)) {
                $sql_main .= " AND (";
                foreach ($season_code_arr as $key => $value) {
                    $param_season = 'param_season' . $key;
                    if ($key == 0) {
                        $sql_main .= " g.season_code = :{$param_season} ";
                    } else {
                        $sql_main .= " or g.season_code = :{$param_season} ";
                    }

                    $sql_values[':' . $param_season] = $value;
                }
                $sql_main .= ")";
            }
        }
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND g.goods_code like :goods_code ";
            $sql_values[':goods_code'] = "%" . $filter['goods_code'] . "%";
        }

        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND d.barcode like :barcode ";
            $sql_values[':barcode'] = "%" . $filter['barcode'] . "%";
        }

        //store_code
        //stock_num
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $sql_main .= " AND i.store_code =:store_code ";
            $sql_values[':store_code'] = $filter['store_code'];
        }

        if (isset($filter['stock_num_min']) && $filter['stock_num_min'] != '') {
            $sql_main .= " AND i.stock_num >=:stock_num_min ";
            $sql_values[':stock_num_min'] = $filter['stock_num_min'];
        }
        if (isset($filter['stock_num_max']) && $filter['stock_num_max'] != '') {
            $sql_main .= " AND i.stock_num <=:stock_num_max ";
            $sql_values[':stock_num_max'] = $filter['stock_num_max'];
        }

//        if (isset($filter['sale_week_num_min']) && $filter['sale_week_num_min'] != '') {
//            $sql_main .= " AND d.sale_week_num >=:sale_week_num_min ";
//            $sql_values[':sale_week_num_min'] = $filter['sale_week_num_min'];
//        }
//        if (isset($filter['sale_week_num_max']) && $filter['sale_week_num_max'] != '') {
//            $sql_main .= " AND d.sale_week_num <=:sale_week_num_max ";
//            $sql_values[':sale_week_num_max'] = $filter['sale_week_num_max'];
//        }
//
//
//        if (isset($filter['sale_month_num_min']) && $filter['sale_month_num_min'] != '') {
//            $sql_main .= " AND d.sale_month_num >=:sale_month_num_min ";
//            $sql_values[':sale_month_num_min'] = $filter['sale_month_num_min'];
//        }
//        if (isset($filter['sale_month_num_max']) && $filter['sale_month_num_max'] != '') {
//            $sql_main .= " AND d.sale_month_num <=:sale_month_num_max ";
//            $sql_values[':sale_month_num_max'] = $filter['sale_month_num_max'];
//        }

        if (isset($filter['num_start']) && $filter['num_start'] != '') {
            switch ($filter['is_num']) {
                case 'sale_week_num':  //7天
                    $sql_main .= " AND d.sale_week_num >=:num_start ";
                    $sql_values[':num_start'] = $filter['num_start'];
                    break;
                case 'sale_month_num':  //30天
                    $sql_main .= " AND d.sale_month_num >=:num_start ";
                    $sql_values[':num_start'] = $filter['num_start'];
                    break;
                case 'sale_two_month_num':  //60天
                    $sql_main .= " AND d.sale_two_month_num >=:num_start ";
                    $sql_values[':num_start'] = $filter['num_start'];
                    break;
                case 'sale_three_month_num':  //90天
                    $sql_main .= " AND d.sale_three_month_num >=:num_start ";
                    $sql_values[':num_start'] = $filter['num_start'];
                    break;
            }
            ;
        }
        if (isset($filter['num_end']) && $filter['num_end'] != '') {
            switch ($filter['is_num']) {
                case 'sale_week_num':  //7天
                    $sql_main .= " AND d.sale_week_num <=:num_end ";
                    $sql_values[':num_end'] = $filter['num_end'];
                    break;
                case 'sale_month_num':  //30天
                    $sql_main .= " AND d.sale_month_num <=:num_end ";
                    $sql_values[':num_end'] = $filter['num_end'];
                    break;
                case 'sale_two_month_num':  //60天
                    $sql_main .= " AND d.sale_two_month_num <=:num_end ";
                    $sql_values[':num_end'] = $filter['num_end'];
                    break;
                case 'sale_three_month_num':  //90天
                    $sql_main .= " AND d.sale_three_month_num <=:num_end ";
                    $sql_values[':num_end'] = $filter['num_end'];
                    break;
            }
        }

        if (isset($filter['total_num_start']) && $filter['total_num_start'] != '') {
            switch ($filter['num_total']) {
                case 'sale_week_num_all':  //7天
                    $sql_main .= " AND d.sale_week_num_all >=:num_start_total ";
                    $sql_values[':num_start_total'] = $filter['total_num_start'];
                    break;
                case 'sale_month_num_all':  //30天
                    $sql_main .= " AND d.sale_month_num_all >=:num_start_total ";
                    $sql_values[':num_start_total'] = $filter['total_num_start'];
                    break;
                case 'sale_two_month_num_all':  //60天
                    $sql_main .= " AND d.sale_two_month_num_all >=:num_start_total ";
                    $sql_values[':num_start_total'] = $filter['total_num_start'];
                    break;
                case 'sale_three_month_num_all':  //90天
                    $sql_main .= " AND d.sale_three_month_num_all >=:num_start_total ";
                    $sql_values[':num_start_total'] = $filter['total_num_start'];
                    break;
            }
            ;
        }
        if (isset($filter['total_num_end']) && $filter['total_num_end'] != '') {
            switch ($filter['num_total']) {
                case 'sale_week_num_all':  //7天
                    $sql_main .= " AND d.sale_week_num_all <=:total_num_end ";
                    $sql_values[':total_num_end'] = $filter['total_num_end'];
                    break;
                case 'sale_month_num_all':  //30天
                    $sql_main .= " AND d.sale_month_num_all <=:total_num_end ";
                    $sql_values[':total_num_end'] = $filter['total_num_end'];
                    break;
                case 'sale_two_month_num_all':  //60天
                    $sql_main .= " AND d.sale_two_month_num_all <=:total_num_end ";
                    $sql_values[':total_num_end'] = $filter['total_num_end'];
                    break;
                case 'sale_three_month_num_all':  //90天
                    $sql_main .= " AND d.sale_three_month_num_all <=:total_num_end ";
                    $sql_values[':total_num_end'] = $filter['total_num_end'];
                    break;
            }
        }

        if (isset($filter['pur_num_min']) && $filter['pur_num_min'] != '') {
            $sql_main .= " AND i.pur_num >=:pur_num_min ";
            $sql_values[':pur_num_min'] = $filter['pur_num_min'];
        }
        if (isset($filter['pur_num_max']) && $filter['pur_num_max'] != '') {
            $sql_main .= " AND i.pur_num <=:pur_num_max ";
            $sql_values[':pur_num_max'] = $filter['pur_num_max'];
        }

        $sql_main .= " order by i.pur_num desc ";


        $select = "d.detail_id,d.sku,i.pur_num,d.sale_week_num_all,d.sale_month_num_all,d.sale_two_month_num_all,d.sale_three_month_num_all,g.goods_name,g.goods_code,d.spec1_code,d.spec2_code,d.barcode,d.sale_week_num,d.sale_month_num,d.sale_two_month_num,d.sale_three_month_num,i.stock_num,i.road_num,i.wait_deliver_num,g.brand_code,g.category_code,g.season_code,g.sell_price ";

        $ret_data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        // var_dump($sql_main, $sql_values, $select);die;
        // filter_fk_name($ret_data['data'], array('spec1_code|spec1_code', 'spec2_code|spec2_code', 'brand_code|brand_code', 'category_code|category_code', 'season_code|season_code'));

        $code_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('goods_spec1', 'goods_spec2'));
        foreach ($ret_data['data'] as &$val) {
            $inv_num = load_model('prm/InvModel')->get_sku_inv(array("store_code" => $filter['store_code'], "sku" => $val['sku']));
            $val['inv_num'] = empty($inv_num['data']['data'][0]['available_mum']) ? 0 : $inv_num['data']['data'][0]['available_mum'];
            $key_arr = array('goods_code', 'barcode', 'spec1_code', 'spec2_code', 'spec1_name', 'spec2_name', 'goods_name', 'brand_name', 'category_name', 'season_name', 'price');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], $key_arr);
            if ($sku_info['price'] != 0) {
                $val['sell_price'] = $sku_info['price'];
                unset($sku_info['price']);
            }
            $val = array_merge($val, $sku_info);
            $val['spec_name'] = $code_arr['goods_spec1'] . ":" . $val['spec1_name'] . ";" . $code_arr['goods_spec2'] . ":" . $val['spec2_name'];
            $val['sale_week_num_all'] = empty($val['sale_week_num_all']) ? 0 : $val['sale_week_num_all'];
            $val['sale_month_num_all'] = empty($val['sale_month_num_all']) ? 0 : $val['sale_month_num_all'];
        }
        //print_r($ret_data);
        // var_dump($ret_data);die;
        return $this->format_ret(1, $ret_data);
    }

    /*
     * 删除前天数据
     */

    function clear_data() {
        //  $pre_date = $pre_data = $this->get_date_by_day(1);
        $sql = "delete from pur_advide_detail ";
        $this->query($sql);
        $sql = "delete from pur_advide_record ";
        $this->query($sql);
    }

    /*
     * 生存补货数据
     */

    function create_pur_advise_data() {
        $now_time = time();
        $skip_time = strtotime(date('Y-m-d', $now_time) . " 4:00:00");
        if ($now_time < $skip_time) {
            return TRUE;
        }

        $ret = $this->insert_record();
        if ($ret['status'] > 0) {
            $this->clear_data();
            $this->create_detail(7);
            $this->create_detail(30);
            $this->create_detail(60);
            $this->create_detail(90);
        }
        $this->create_detail_inv();
        $this->update_record(array('end_time' => time()));
    }

    function create_detail($day = 7) {

        $pre_data = $this->get_date_by_day($day);
        $sale_key = 'sale_week_num';
        if ($day == 30) {
            $sale_key = 'sale_month_num';
        }
        if ($day == 60) {
            $sale_key = 'sale_two_month_num';
        }
        if ($day == 90) {
            $sale_key = 'sale_three_month_num';
        }
        $sale_key_all = $sale_key . "_all";
        $kh_id = CTX()->saas->get_saas_key();


//echo $day.'===='.$pre_data.'===='.$this->record_date;die;

        $sql = "INSERT  INTO  pur_advide_detail (goods_code,spec1_code,spec2_code,sku,barcode,record_date,{$sale_key_all},{$sale_key})
		select t.goods_code,t.spec1_code,t.spec2_code,b.sku,t.goods_barcode,'{$this->record_date}' as  record_date ,
		SUM(t.sale_count) as {$sale_key_all},round(SUM(t.sale_count)/{$day},2) as {$sale_key} from report_base_goods_collect t
		INNER JOIN goods_sku b ON t.sku=b.sku
		WHERE biz_date<='{$this->record_date}' AND biz_date>'{$pre_data}' GROUP BY t.sku  
		ON DUPLICATE KEY UPDATE {$sale_key} = VALUES({$sale_key}),{$sale_key_all} = VALUES({$sale_key_all})";

        return $this->db->query($sql);
    }

    private function get_date_by_day($day) {

        $pre_date_time = strtotime($this->record_date) - $day * 24 * 3600;
        //	date('Y-m-d', $pre_date_time)
        //	echo $day.'----'.$this->record_date.'---'.date('Y-m-d', $pre_date_time);die;
//echo date('Y-m-d', $pre_date_time);die;
        return date('Y-m-d', $pre_date_time);
    }

    function create_detail_inv() {

        $v = $this->get_param_value();
        //计算补货公式  -（实物库存-已付款未发货数）-缺货库存-在途数
        $pur_num_str = "({$v['week_proportion']}*d.sale_week_num/100+{$v['month_proportion']}*d.sale_month_num/100)*{$v['pur_advise_day']}-i.stock_num+i.lock_num+i.out_num-i.road_num";
        $sql = "INSERT  INTO  pur_advide_inv (store_code,sku,stock_num,road_num,wait_deliver_num,out_num,pur_num)
            SELECT i.store_code,i.sku,i.stock_num,i.road_num,(i.lock_num+i.out_num) as wait_deliver_num,i.out_num, {$pur_num_str} as pur_num 
            from  pur_advide_detail d
           INNER JOIN goods_inv i ON d.sku=i.sku 
            ON DUPLICATE KEY UPDATE stock_num= VALUES(stock_num),road_num= VALUES(road_num),wait_deliver_num= VALUES(wait_deliver_num),out_num=VALUES(out_num),pur_num=VALUES(pur_num)";
        $this->db->query($sql);
        $sql = "update pur_advide_inv set pur_num=0 where pur_num<0 ";
        return $this->db->query($sql);
    }

    function insert_record() {

        $row = $this->get_record_data();
        if (empty($row)) {
            $data['record_date'] = $this->record_date;
            $data['start_time'] = time();
            return $this->insert_exp('pur_advide_record', $data);
        }

        return $this->format_ret(-1);
    }

    function update_record($data) {

        $where = array('record_date' => $this->record_date);
        return $this->update_exp('pur_advide_record', $data, $where);
    }

    function get_record_data() {
        $sql = "select * from pur_advide_record where   record_date=:record_date";
        $sql_values = array(':record_date' => $this->record_date);
        return $this->db->get_row($sql, $sql_values);
    }

    /*
     * 获取补货参数值
     */

    function get_param_value() {
        return load_model('sys/SysParamsModel')->get_val_by_code($this->pur_advise_param);
    }

    /*
     * 获取补货参数
     */

    function get_param() {
        $sql = "select * from sys_params where parent_code = :parent_code ";
        $data = $this->db->get_all($sql, array(':parent_code' => 'pur_advise'));
        return $this->format_ret(1, $data);
    }

    /*
     * 保存参数
     */

    function save_param($param) {
        foreach ($this->pur_advise_param as $key) {
            if (isset($param[$key])) {
                load_model('sys/SysParamsModel')->update(array('value' => $param[$key]), array("param_code" => $key));
            }
        }
        return $this->format_ret(1);
    }

    /**
     * 获取动销率和售罄率
     * @param array $param store_code 仓库
     * @return array
     */
    public function get_rate($param) {
        if (!isset($param['store_code']) || $param['store_code'] === '') {
            return $this->format_ret(-1, [], '仓库有误');
        }

        $sql_values = [':store_code' => $param['store_code']];
        //有销量的产品数量:获取已发货状态的订单中，商品种类总计
        $sql_1 = 'SELECT COUNT(DISTINCT rd.goods_code) goods_count,SUM(rd.num) num FROM oms_sell_record sr,oms_sell_record_detail rd WHERE sr.shipping_status=4 AND sr.store_code=:store_code AND sr.sell_record_code=rd.sell_record_code';
        //在仓的产品数量：所有有库存记录的商品种类总计
        $sql_2 = 'SELECT COUNT(DISTINCT goods_code) goods_count FROM goods_inv WHERE store_code=:store_code';
        //采购总数量：所有已入库的采购入库单中入库数的总和
        $sql_3 = 'SELECT SUM(finish_num) num FROM pur_purchaser_record WHERE is_check_and_accept=1 AND store_code=:store_code';
        if (!empty(strtotime($param['count_date_start']))) {
            $sql_1 .= ' AND sr.delivery_time>=:start_time';
            $sql_2 .= ' AND record_time>=:start_time';
            $sql_3 .= ' AND enter_time>=:start_time';
            $sql_values[':start_time'] = $param['count_date_start'] . ' 00:00:00';
        }
        if (!empty(strtotime($param['count_date_end']))) {
            $sql_1 .= ' AND sr.delivery_time<=:end_time';
            $sql_2 .= ' AND record_time<=:end_time';
            $sql_3 .= ' AND enter_time<=:end_time';
            $sql_values[':end_time'] = $param['count_date_end'] . ' 23:59:59';
        }

        $goods_data = $this->db->get_row($sql_1, $sql_values);
        $inv_count = $this->db->get_value($sql_2, $sql_values);
        $pur_num = $this->db->get_value($sql_3, $sql_values);

        $rate_data = [];
        //动销率
        $rate_data['dynamic_pin_rate'] = empty($goods_data['goods_count']) || empty($inv_count) ? 0 . ' %' : round($goods_data['goods_count'] / $inv_count * 100) . ' %';
        //售罄率
        $rate_data['sell_through_rate'] = empty($goods_data['num']) || empty($pur_num) ? 0 . ' %' : round($goods_data['num'] / $pur_num * 100) . ' %';

        return $this->format_ret(1, $rate_data);
    }

}
