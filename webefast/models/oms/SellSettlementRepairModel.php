<?php

require_model('tb/TbModel');
require_lang('oms');
require_lib('util/oms_util', true);

/**
 * Description of SellSettlementRepairModel
 *
 * @author user
 */
class SellSettlementRepairModel extends TbModel {
    protected $oms_sell_table = 'oms_sell_record';
    protected $oms_sell_detail_table = 'oms_sell_record_detail';
    protected $oms_return_table = 'oms_sell_return';
    protected $oms_return_detail_table = 'oms_sell_return_detail';
    protected $oms_settlement_total_table = 'oms_sell_settlement';
    protected $oms_settlement_table = 'oms_sell_settlement_record';
    protected $oms_settlement_detail_table = 'oms_sell_settlement_detail';
    
    /**
     *
     * 方法名       do_sell_list_by_page
     *
     * 功能描述     获取已经发货订单数据未插入到零售结算明细表的订单
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-28
     * @param       array $filter
     * @return      json
     */
    public function do_sell_list_by_page($filter) {
        $sql_values = array();
        $sql_join = "INNER JOIN {$this->oms_sell_detail_table} osrd ON osr.sell_record_code=osrd.sell_record_code ";
        //$sql_join .= "LEFT JOIN {$this->oms_settlement_total_table} ost ON osrd.deal_code=ost.deal_code ";
        $sql_main = "FROM {$this->oms_sell_table} osr $sql_join WHERE 1 AND osr.is_fenxiao=0 ";
        $sql_main .= "AND osr.shipping_status=4 AND osr.delivery_time<>'0000-00-00 00:00:00' ";
        $sql_main .= "AND osrd.deal_code NOT IN (SELECT ost.deal_code FROM {$this->oms_settlement_total_table} ost) ";
        $sql_main .= "GROUP BY osr.sell_record_code ";
        $select = 'osr.*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        return $this->format_ret(1, $data);
    }
    
    /**
     *
     * 方法名       do_return_list_by_page
     *
     * 功能描述     获取退款退货（已经收货）|仅退款订单未插入到零售结算明细表的订单
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-28
     * @param       array $filter
     * @return      json
     */
    public function do_return_list_by_page($filter) {
        $sql_values = array();
        $sql_join = "INNER JOIN {$this->oms_return_detail_table} osrd ON osr.sell_return_code=osrd.sell_return_code ";
        //$sql_join .= "LEFT JOIN {$this->oms_settlement_total_table} ost ON osrd.deal_code=ost.deal_code ";
        $sql_main = "FROM {$this->oms_return_table} osr $sql_join WHERE 1 ";
        $sql_main .= "AND ((osr.return_type=3 AND osr.return_shipping_status=1 AND osr.receive_time<>'0000-00-00 00:00:00') OR (osr.return_type=1 AND osr.finance_check_status=1)) ";
        $sql_main .= "AND osrd.deal_code NOT IN (SELECT ost.deal_code FROM {$this->oms_settlement_total_table} ost) ";
        $sql_main .= "GROUP BY osr.sell_return_code ";
        $select = 'osr.*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        return $this->format_ret(1, $data);
    }    
    
    /**
     *
     * 方法名       float_amout_round
     *
     * 功能描述     浮点数整分方法
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-20
     * @param       float $price [运费]
     * @param       int $divisor [运费需要均摊的商品个数]
     * @param       int $mulriple [放大倍数（尽量给出10的整数倍）]
     * @return      string
     */
    private function float_amout_round($price, $divisor, $mulriple = 1000) {
        $price = $price * $mulriple;
        $mod = fmod(floatval($price), $divisor);
        $mod = $mod/$mulriple;
        $lcm = intval($price/$divisor)/$mulriple;
        return array('price' => $price, 'mod' => $mod, 'lcm' => $lcm, 'divisor' => $divisor);
    }
}
