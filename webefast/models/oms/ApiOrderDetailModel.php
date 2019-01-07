<?php
require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');
class ApiOrderDetailModel extends TbModel {
	function get_table() {
		return 'api_order_detail';
	}
	function get_by_id($id) {
	
		return  $this->get_row(array('api_order_detail_id'=>$id));
	}
	/**
	 * 通过field_name查询所有
	 *
	 * @param  $ :查询field_name
	 * @param  $select ：查询返回字段
	 * @return array (status, data, message)
	 */
	public function get_by_field_all($field_name,$value, $select = "*") {
		$sql = "SELECT {$select} FROM {$this->table} WHERE {$field_name} = :{$field_name}";
		$data = $this -> db -> get_all($sql, array(":{$field_name}" => $value));
		return $data;
	}
        public function get_by_field_all_refund($field_name,$value) {
		$sql = "SELECT r1.*,r2.spec1_name,r2.spec2_name FROM api_refund_detail r1 LEFT JOIN goods_sku r2 ON r1.goods_barcode = r2.barcode WHERE {$field_name} = :{$field_name}";
		$data = $this -> db -> get_all($sql, array(":{$field_name}" => $value));
                foreach ($data as &$value) {
                    $value['sku_properties'] = empty($value['spec1_name']) && empty($value['spec2_name']) ? '' : '颜色:'.$value['spec1_name'].'; 尺码:'.$value['spec2_name'];
                }
		return $data;
	}
	//保存barcode
	public function save($b){
		$this->begin_trans();
		try{
			foreach($b as $id => $barcode){
				/*
				$sql = "select * from goods_barcode where barcode = :barcode";
				$sku = $this->db->get_row($sql, array('barcode'=>$barcode));
				if(empty($sku)){
					$sql = "select * from goods_sku where sku = :sku";
					$sku = $this->db->get_row($sql, array('sku'=>$barcode));
				}
				if(empty($sku)){
					throw new Exception('保存失败,条码不存在: '.$barcode);
				}
				*/
				$r = $this->db->update('api_order_detail', array('goods_barcode'=>$barcode), array('detail_id'=>$id));
				if($r !== true){
					throw new Exception('保存失败');
				}
			}
	
			$this->commit();
			return array('status'=>1, 'message'=>'更新成功');
		} catch(Exception $e){
			$this->rollback();
			return array('status'=>-1, 'message'=>$e->getMessage());
		}
	}
    
    /* 
    * 删除记录
    * */
    function td_delete($detail) {
        $this->begin_trans();
        try {
            //查询明细表商品数量
            $sql = "SELECT title,num,avg_money FROM api_order_detail WHERE detail_id = '".$detail['detail_id']."';";
            $detail_num = $this->db->get_row($sql);
            //主表商品数量
            $sql = "SELECT num,order_money FROM api_order WHERE tid = '".$detail['tid']."';";
            $api_order_num = $this->db->get_row($sql);
            $num = $api_order_num['num'] - $detail_num['num'];
            //$price = $api_order_num['order_money'] - $detail_num['avg_money'];
            $price =  bcsub($api_order_num['order_money'], $detail_num['avg_money'], 2);
            //修改主表数量、金额
            $r = $this->db->update('api_order', array('num'=>$num,'order_money'=>$price), array('tid'=>$detail['tid']));
            if ($r !== true) {
		$this->rollback();
            }
            //删除明细表信息
            $ret = parent::delete(array('detail_id'=>$detail['detail_id']));
            if ($ret['status'] < 0) {
		$this->rollback();
		return $ret;
            }
            //添加系统日志
            $module = '网络订单'; //模块名称
            $yw_code = $detail['tid']; //业务编码
            $operate_type = '删除';
            $log_xq = "删除交易号为：".$detail['tid']."中的".$detail_num['title']."商品";
            $log = array('user_id'=>CTX()->get_session('user_id'),'user_code'=>CTX()->get_session('user_code'),'ip'=>'','add_time'=>date('Y-m-d H:i:s'),'module'=>$module,'yw_code'=>$yw_code,'operate_type'=>$operate_type,'operate_xq'=>$log_xq);
             load_model('sys/OperateLogModel')->insert($log);
            $this->commit();
            return $ret;
        }catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', '删除失败:' . $e->getMessage());
        }
    }
    
    /**
     * @todo 通过交易号获取交易明细，用于平台交易列表明细展开
     */
    function get_detail_by_tid_arr($tid_arr){
        $tid_str = deal_array_with_quote($tid_arr);
        $sql = "SELECT * FROM api_order_detail WHERE tid IN ({$tid_str})";
        $ret = $this->db->get_all($sql);
        $new_ret = array();
        foreach($ret as $key => $value){
            $new_ret[$value['tid']][] = $value;
        }
        return $new_ret;
    }
    /**
     * @todo 通过refund_id获取交易明细，用于平台退单列表明细展开
     */
    function get_detail_by_tid_arr_refund($tid_arr){
        $tid_str = deal_array_with_quote($tid_arr);
        $sql = "SELECT r1.*,r2.spec1_name,r2.spec2_name FROM api_refund_detail r1 LEFT JOIN goods_sku r2 ON r1.goods_barcode = r2.barcode WHERE refund_id IN ({$tid_str})";
        $ret = $this->db->get_all($sql);
        $new_ret = array();
        foreach($ret as $key => $value){
            $value['sku_properties'] = empty($value['spec1_name']) && empty($value['spec2_name']) ? '' : '颜色:'.$value['spec1_name'].'; '.'尺码:'.$value['spec2_name'];
            $new_ret[$value['refund_id']][] = $value;
        }
        return $new_ret;
    }
}
