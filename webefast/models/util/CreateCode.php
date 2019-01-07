<?php
require_model('tb/TbModel');

class CreateCode extends TbModel {
	public function __construct() {
		parent::__construct();
	}

	function get_code($record_type){
		$tag = $record_type.'_seq';
		$ret = $this->new_code($tag);
		return $ret;
	}
    /**
     * create new sell_record code
     * @return string
     */
    function new_code($tag = 'oms_sell_return_seq') {
        $num = $this->db->get_seq_next_value($tag);
        $time = date('ymd', time());

        $num = sprintf('%06s', $num);
        $length = strlen($num);
        $num = substr($num, $length - 6, 6);
        $str = $time . $num;

        $str = $this->sku_check_code($str);
        return $str;
    }

    /**
     * 获得13位sku的校验码
     * @param string $code
     * @return string|string
     */
    function sku_check_code($code) {
        $ncode = $code;
        $length = strlen($ncode);
        $lsum = $rsum = 0;
        for($i=0; $i < $length; $i++) {
            if($i % 2) {
                $lsum += intval($ncode[$i]);
            } else {
                $rsum += intval($ncode[$i]);
            }
        }
        $tsum = $lsum * 3 + $rsum;
        $code .= (10-($tsum % 10)) % 10;
        return $code;
    }

}