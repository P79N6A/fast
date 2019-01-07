<?php
require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');
class ApiOrderSendModel extends TbModel {
    protected $table = 'api_order_send';
}