<?php

require_model('tb/TbModel');
require_lib('util/oms_util', true);
require_lib('apiclient/TaobaoClient');

class InvoiceRecordModel extends TbModel {

    /**
     * @var string 表名
     */
    protected $table = 'oms_deliver_record';
    
    /**
     * @var array 打印发货单模板所需字段
     */
    public $print_fields_default = array(
        'record' => array(
        	'买家昵称' => 'buyer_name',
            '交易号' => 'deal_code_list',
            '订单号' => 'sell_record_code',
            '运费' => 'express_money',
        	'开票抬头' => 'invoice_title',
        	'开票内容' => 'invoice_content',
        	'开票金额小写' => 'invoice_money',
        	'开票金额大写' => 'invoice_money_zh',
        	'开票时间' => 'invoice_time',
        	'开票人' => '开票人',
        	'天猫积分抵扣金额' => 'real_point_fee',
                '税号识别码' => 'taxpayers_code',
        ),
        'detail' => array(
            array(
                '商品名称' => 'goods_name',
                '数量' => 'num',
            	'条形码' => 'barcode',
                '均摊金额' => 'avg_money',
            ),
        ),
    );

    /**
     * 默认打印数据
     * @param $id
     * @return array
     */
    public function print_data_default($id){
        $r = array();
        $sql1 = "SELECT d.*,s.taxpayers_code FROM oms_deliver_record d INNER JOIN oms_sell_record s on d.sell_record_code = s.sell_record_code WHERE d.deliver_record_id = :id";
        $r['record'] = $this->db->get_row($sql1, array('id' => $id));

        $sql = "select d.sell_record_code
                ,d.goods_code
                ,d.sku
                ,num
                ,d.goods_price,d.avg_money,d.platform_spec
                from oms_deliver_record_detail d
                where d.deliver_record_id=:deliver_record_id";
        $r['detail'] = $this->db->get_all($sql, array(':deliver_record_id' => $id));
        $sku_array = array();
        foreach ($r['detail'] as $key => $detail){//合并同一sku
//               ,d.goods_code,d.num,goods_name,g.goods_short_name,s1.spec1_name,s2.spec2_name
//                ,d.sku,d.barcode 
            $key_arr = array('goods_name','goods_short_name','spec1_name','spec2_name','barcode');
             $sku_info =  load_model('goods/SkuCModel')->get_sku_info($detail['sku'],$key_arr);
             $detail = array_merge($detail,$sku_info);
             $r['detail'][$key] = $detail;
        	if (in_array($detail['sku'], $sku_array)){
        		$exist_key = array_keys($sku_array,$detail['sku']);
        		$r['detail'][$exist_key[0]]['num'] += $detail['num'];
        		$r['detail'][$exist_key[0]]['avg_money'] += $detail['avg_money'];
        		unset($r['detail'][$key]);
        	} else {
        		$sku_array[$key] = $detail['sku'];
        	}
        }
        // 数据替换
        $this->print_data_escape($r['record'], $r['detail']);
        $d = array('record'=>array(), 'detail'=>array());
        foreach($r['record'] as $k => $v) {
            // 键值对调
            $nk = array_search($k, $this->print_fields_default['record']);
            $nk = $nk === false ? $k : $nk;
            $d['record'][$nk] = is_null($v) ? '' : $v; //$v; //
        }
        foreach($r['detail'] as $k1 => $v1) {
        	// 键值对调
        	foreach($v1 as $k => $v){
        		$nk = array_search($k, $this->print_fields_default['detail'][0]);
        		$nk = $nk === false ? $k : $nk;
        		$d['detail'][$k1][$nk] = is_null($v) ? '' : $v; //$v; //
        	}
        }
        
          $d['detail'] = array_values($d['detail']);
        // 更新状态
        $this->print_update_status($r['record']['deliver_record_id']);
        return $d;
    }

    /**
     * 替换待打印数据字段
     * @param $record
     * @param $detail
     */
    public function print_data_escape(&$record, &$detail){
		//发票抬头
		if (empty($record['invoice_title'])){
			$record['invoice_title'] = $record['receiver_name'];
		}
		if (!empty($record['invoice_content'])){
			$sql = "select value from sys_params where param_code='invoice_content'";
			$invoice_content = $this->db->getOne($sql);
			$record['invoice_content'] = $invoice_content;
		}
		//天猫积分
		$deal_arr = explode(',', $record['deal_code_list']);
		$deal_list_str = "'".join("','",$deal_arr)."'";
		$sql = "select sum(real_point_fee) as real_point_fee from api_taobao_trade where tid in($deal_list_str)";
		$real_point_fee = $this->db->getOne($sql);
		$record['real_point_fee'] = $real_point_fee/100;
		//开票金额=应收款-天猫积分
		$record['invoice_money'] = $record['payable_money']-$record['real_point_fee'];
		//开票金额大写
		$record['invoice_money_zh'] = $this->num_to_rmb($record['invoice_money']);
		//开票时间
		$record['invoice_time'] = date('Y-m-d H:i:s');
    }

    /**
     * 更新打印状态
     * @param $id
     * @param $type
     * @return array
     * @throws Exception
     */
    public function print_update_status($id) {
        $record = $this->db->get_row("select * from oms_deliver_record where deliver_record_id = :id", array('id' => $id));
        if(empty($record)) {
            return array('status'=>'-1', 'message'=>'发货单不存在');
        }
        //发票打印日志
        load_model('oms/SellRecordModel')->add_action($record['sell_record_code'], '发票打印');
        // 更新发票打印状态
        $this->db->update('oms_deliver_record',array('is_print_invoice' => 1), array('deliver_record_id'=>$record['deliver_record_id']));
        // 更新订单表的发票打印状态
        $this->db->update('oms_sell_record', array('is_print_invoice' => 1), array('sell_record_code'=>$record['sell_record_code']));
        return array('status'=>'1', 'message'=>'更新完成');
    }

    /**
     * @param $status
     * @param string $message
     * @param string $data
     * @return array
     */
    function return_value($status, $message = '', $data = '') {
        $message = $status == 1 && $message == '' ? '操作成功' : $message;

        return array('status' => $status, 'message' => $message, 'data' => $data);
    }
    /**
     *数字金额转换成中文大写金额的函数
     *String Int  $num  要转换的小写数字或小写字符串
     *return 大写字母
     *小数位为两位
     **/
    function num_to_rmb($num){
    	$c1 = "零壹贰叁肆伍陆柒捌玖";
    	$c2 = "分角元拾佰仟万拾佰仟亿";
    	//精确到分后面就不要了，所以只留两个小数位
    	$num = round($num, 2);
    	//将数字转化为整数
    	//$num = $num * 100;
        	$num = bcmul($num,100);
    	if (strlen($num) > 10) {
    		return "金额太大，请检查";
    	}
    	$i = 0;
    	$c = "";
    	while (1) {
    		if ($i == 0) {
    			//获取最后一位数字
    			$n = substr($num, strlen($num)-1, 1);
    		} else {
    			$n = $num % 10;
    		}
    		//每次将最后一位数字转化为中文
    		$p1 = substr($c1, 3 * $n, 3);
    		$p2 = substr($c2, 3 * $i, 3);
    		if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
    			$c = $p1 . $p2 . $c;
    		} else {
    			$c = $p1 . $c;
    		}
    		$i = $i + 1;
    		//去掉数字最后一位了
    		$num = $num / 10;
    		$num = (int)$num;
    		//结束循环
    		if ($num == 0) {
    			break;
    		}
    	}
    	$j = 0;
    	$slen = strlen($c);
    	while ($j < $slen) {
    		//utf8一个汉字相当3个字符
    		$m = substr($c, $j, 6);
    		//处理数字中很多0的情况,每次循环去掉一个汉字“零”
    		if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
    			$left = substr($c, 0, $j);
    			$right = substr($c, $j + 3);
    			$c = $left . $right;
    			$j = $j-3;
    			$slen = $slen-3;
    		}
    		$j = $j + 3;
    	}
    	//这个是为了去掉类似23.0中最后一个“零”字
    	if (substr($c, strlen($c)-3, 3) == '零') {
    		$c = substr($c, 0, strlen($c)-3);
    	}
    	//将处理的汉字加上“整”
    	if (empty($c)) {
    		return "零元整";
    	}else{
    		return $c;
    	}
    }
    
    
}