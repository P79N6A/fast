<?php
require_model('tb/TbModel');
require_lang('sys');

class FormSelectSourceModel extends TbModel {
	public function __construct($table = '', $db = '') {
		parent::__construct($table);
	}

	function process_comm_list($tbl,$code_fld,$name_fld,$status_fld){
		$sql = "select {$code_fld},{$name_fld} from {$tbl} where {$status_fld} = 1";
		$db_arr = ctx()->db->get_all($sql);
		return $db_arr;
	}

	/*
    *public $order_status = array(
    *   0 => '未确认',
    *    1 => '已确认',
    *    3 => '已作废',
    *    5 => '已完成',
    *);
    *转换成
    *array(0=>array(0,'未确认'),1=>array(1,'已确认'))
	*/
	function get_select_data_by_arr($arr){
		$result = array();
		foreach($arr as $sub_arr){
			$result[] = array($sub_arr[0],$sub_arr[1]);
		}
		return $result;
	}

    public function __call($name, $arguments) {
        if (strpos($name, 'get_comm_') == 0) {
            $get_name = str_ireplace('get_comm_', '', $name);
            $tbl = 'base_'.$get_name;
            $code_fld = $get_name.'_code';
            $name_fld = $get_name.'_name';
            $status_fld = 'is_active';
            if ($tbl == 'base_express'){
            	$status_fld = 'status';
            }
            $ret = $this->process_comm_list($tbl,$code_fld,$name_fld,$status_fld);
            return $ret;
        }
        if (strpos($name, 'get_sell_') == 0) {
            $get_name = str_ireplace('get_sell_', '', $name);
	     	$ret = load_model('oms/SellRecordModel')->$name;
	    	$ret = $this->get_select_data_by_arr($ret);
	    	return $ret;
        }
        return array();
    }

    public function get_shop(){
    	$ret = load_model('base/ShopModel')->get_purview_shop();
    	return $ret;
    }

    public function get_store(){
    	$ret = load_model('base/StoreModel')->get_purview_store();
    	return $ret;    	
    }

    public function get_sell_change_record(){
    	$arr = array();
    	$arr[] = array('1','换货单');
    	$arr[] = array('-1','非换货单');
    	return $arr;
    }

    public function get_sell_invoice_status(){
    	$arr = array();
    	$arr[] = array('1','有发票');
    	$arr[] = array('-1','无发票');
    	return $arr;
    }

	public function get_country(){
		$sql = "select id,name from base_area where type = 1";
		$db_arr = ctx()->db->get_all($sql);
    	$arr = array();
    	foreach($db_arr as $sub_arr){
			$arr[] = array($sub_arr['id'],$sub_arr['name']);
	    }		
		return $arr;
	}
	
	function get_mdl_status_sel($mdl_name,$mdl_status){
		
	}
	
    function get_return_package_order_status(){
	    $return_order_status_arr = load_model('ReturnPackageModel')->return_order_status;
    	$arr = array();
    	foreach($return_order_status_arr as $k=>$v){
	    	$arr[] = array($k,$v);
    	}
    	return $arr;	    
    }

	function get_return_package_tag(){
	    $tag = load_model('ReturnPackageModel')->return_order_status;
	    
	}
        
    public function get_seller_flag(){
    	$arr = array();
    	$arr[] = array('0','无旗帜');
    	$arr[] = array('1','红');
    	$arr[] = array('2','黄');
    	$arr[] = array('3','绿');
    	$arr[] = array('4','蓝');
    	$arr[] = array('5','紫');
    	return $arr;
    }

 	function get_catagory(){
		$sql = "select category_code,category_name from base_category";
		$db_ct = ctx()->db->get_all($sql);
		return $db_ct;
	}

	function get_brand(){
		$sql = "select brand_code,brand_name from base_brand";
		$db_brand = ctx()->db->get_all($sql);
		return $db_brand;
	}

	function get_season(){
		$sql = "select season_code,season_name from base_season";
		$db_season = ctx()->db->get_all($sql);
		return $db_season;
	}

	
    public function sell_record_attr(){
    	$arr = array();
                $arr[] = array('is_problem','正常单');
		$arr[] = array('attr_lock','锁订单');
		$arr[] = array('attr_pending','挂起单');
		$arr[] = array('attr_problem','问题单');
		$arr[] = array('attr_bf_quehuo','部分缺货订单');
		$arr[] = array('attr_all_quehuo','完全缺货订单');
		$arr[] = array('attr_combine','合并单');
		$arr[] = array('attr_split','拆分单');
		$arr[] = array('attr_change','换货单');
		$arr[] = array('attr_handwork','手工单');
		$arr[] = array('attr_copy','复制单');
		$arr[] = array('attr_presale','预售单');
                $arr[] = array('attr_fenxiao','分销单');
                $arr[] = array('is_rush','加急单');
        $arr[] = array('is_replenish','补单');
    	return $arr;
    }
    //销售数据分析
    public function sell_record_attr_xs(){
    	$arr = array();
		$arr[] = array('attr_lock','锁订单');
		$arr[] = array('attr_combine','合并单');
		$arr[] = array('attr_split','拆分单');
		$arr[] = array('attr_change','换货单');
		$arr[] = array('attr_handwork','手工单');
		$arr[] = array('attr_copy','复制单');
		$arr[] = array('attr_presale','预售单');
                $arr[] = array('attr_fenxiao','分销单');
                $arr[] = array('is_rush','加急单');
        $arr[] = array('is_replenish','补单');
    	return $arr;
    }
    
    public function sell_record_fenxiao(){
    	$arr = array();
                $arr[] = array('3','请选择');
                $arr[] = array('0','普通订单');
		$arr[] = array('1','淘宝分销订单');
		$arr[] = array('2','普通分销订单');
    	return $arr;
    }
    
    public function sell_record_attr_new(){
    	$arr = array();
                $arr[] = array('is_problem','正常单');
		$arr[] = array('attr_lock','锁订单');
		$arr[] = array('attr_combine','合并单');
		$arr[] = array('attr_split','拆分单');
		$arr[] = array('attr_change','换货单');
		$arr[] = array('attr_handwork','手工单');
		$arr[] = array('attr_copy','复制单');
		$arr[] = array('attr_presale','预售单');
                $arr[] = array('attr_fenxiao','分销单');
                $arr[] = array('is_rush','加急单');
        $arr[] = array('is_replenish','补单');
    	return $arr;
    }
    
    public function question_list(){
                $arr[] = array('attr_lock','锁订单');
                $arr[] = array('attr_combine','合并单');
                $arr[] = array('attr_split','拆分单');
		$arr[] = array('attr_change','换货单');
        $arr[] = array('attr_pending','挂起单');
		$arr[] = array('attr_handwork','手工单');
		$arr[] = array('attr_copy','复制单');
		$arr[] = array('attr_presale','预售单');
                $arr[] = array('attr_fenxiao','分销单');
                $arr[] = array('out_of_stock','缺货单');
        $arr[] = array('is_replenish','补单');
        return $arr;
    }
    public function order_nature(){
        $arr = array();
        $arr[] = array('attr_lock','锁定单');
        $arr[] = array('attr_fenxiao','分销单');
        $arr[] = array('attr_change','换货单');
        $arr[] = array('attr_presale','预售单');
        $arr[] = array('is_replenish','补单');
        return $arr;
    }

    /**商品毛利报表
     * @return array
     */
    public function sell_goods_profit() {
        $arr = array();
        $arr[] = array('attr_combine', '合并单');
        $arr[] = array('attr_split', '拆分单');
        $arr[] = array('attr_change', '换货单');
        $arr[] = array('attr_handwork', '手工单');
        $arr[] = array('attr_copy', '复制单');
        $arr[] = array('attr_presale', '预售单');
        $arr[] = array('attr_fenxiao', '分销单');
        $arr[] = array('is_replenish','补单');
        return $arr;
    }

}