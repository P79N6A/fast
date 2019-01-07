<?php

require_model('tb/TbModel');

abstract class MidAbs extends TbModel {

    abstract function get_order_info($record_code);

    abstract function get_order_detail($record_code);

    abstract function get_mid_data($record_code, $base_info = array());

    abstract function order_shipping(&$order_info) ;
}
