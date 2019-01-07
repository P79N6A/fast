<?php

class BizTaobaoModel extends BaseModel
{

	private $shop_code;
	function __construct($shop_code)
	{
		$this->shop_code = $shop_code;
		parent::__construct();
	}

	/**
	 * 淘宝业务统一调用，可识别错误信息
	 * @param unknown_type $params
	 */
	function invoke($ret)
	{
		$this->clear_error();
		if (empty($ret)){
			return $this->put_error(-1,'efast_api return empty');
		}
		$resp_error = $ret['resp_error'];
		$app_err_no = $ret['app_err_no'];
		$app_err_msg = $ret['app_err_msg'];

		if(isset($resp_error))
		{
			if (isset($app_err_msg)){
				return $this->put_error(-1,'efast_api error.'.$app_err_msg);
			}else{
				return $this->put_error(-1,'efast_api error.'.$api_result);				
			}
		}

		if(!isset($ret['resp_data']) && empty($ret['resp_data']))
		{
          return $this->put_error(-1,$params['app_act'].' error.'.$api_result);
		}
		$resp_data = $ret['resp_data'];
		if($this->is_err($resp_data)){
			return FALSE;
		}
		return $resp_data;
	}	

	function taobao_trade_get($tid){
       require_model('taobao/taobao_trade_model',null,'efast_api');
        $tb_trade_obj = new Taobao_trade_model($this->shop_code);
        $ret = $tb_trade_obj->taobao_trade_get($tid);
        if ($ret === false){
            $err = $tb_trade_obj->get_error();
            return $this->put_error(-1,$err['msg']);            
        }
        return $ret;
	}

	function taobao_trade_memo_update($tid,$memo){
       require_model('taobao/taobao_trade_model',null,'efast_api');
        $tb_trade_obj = new Taobao_trade_model($this->shop_code);
        $ret = $tb_trade_obj->taobao_trade_memo_update($tid,$memo);
        if ($ret === false){
            $err = $tb_trade_obj->get_error();
            return $this->put_error(-1,$err['msg']);            
        }
        return $ret;
	}

	function put_error($c, $m){
		//FIXME: Place holder that I don't know what to do.
		return true;
	}
}