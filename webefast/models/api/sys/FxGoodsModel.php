<?php
require_lib('util/oms_util', true);
require_model('tb/TbModel');
class FxGoodsModel extends TbModel {
	function get_table() {
		return 'api_taobao_fx_product';
	}
    function sync_goods_inv_action($goods){
        $fun = 'taobao_api/fenxiao_item_quantity_sync';
        $params = array('shop_code'=>$goods['shop_code'],'pid' =>$goods['pid']);
        $result = load_model('sys/EfastApiModel')->request_api($fun, $params);
        if($result['resp_data']['code'] == '0'){
            $ret['status'] = '1';
            $ret['message'] = '同步成功';
        }else{
            $ret['status'] = '-1';
            $ret['message'] = $result['resp_data']['msg'];
        }
        return $ret;
    }
    
    function fenxiao_sync_goods_inv($id){
        $id_arr = explode(",", $id);
        $id_str = "'".implode("','", $id_arr)."'";
        $sql = "select status,pid,shop_code from {$this->table} where pid in($id_str)";
        $goods = $this->db->get_all($sql);
        $msg = "";
        foreach ($goods as $row) {
            $sql2 = "select outer_id from api_taobao_fx_product_sku  where pid in ('".$row['pid']."')";
            $v=$this->db->get_all($sql2);
            $barcode_arr=array();
            $shop_code=$row['shop_code'];
            foreach($v as $key=>$value){
                $barcode_arr[]=$value['outer_id'];
            }
            load_model('api/BaseInvModel')->update_inv_increment_fenxiao($shop_code,$barcode_arr,1);
            $ret = $this->sync_goods_inv_action($row);
            if($ret['status'] == '-1'){
                $msg .= $ret['message'];
            }
        }
        if(!empty($msg)){
            return $this->format_ret(-1,'',$msg);
        }
        return $this->format_ret(1,'');
    }

    /**
     * 批量库存同步
     * @param $ids
     */
    function multi_fenxiao_sync_goods_inv($id_arr) {
        $error_msg = array();
        foreach ($id_arr as $id) {
            $ret = $this->fenxiao_sync_goods_inv($id);
            if ($ret['status'] != 1) {
                $error_msg[] = array($id. "\t" => $ret['message']);
            }
        }
        //错误信息导出
        if (!empty($error_msg)) {
            $sum = count($id_arr);
            $error_num = count($error_msg);
            $success = $sum - $error_num;
            $msg = $this->create_fail_file($error_msg,array('产品ID', '错误信息'));
            return $this->format_ret(-1, '', '同步成功:' . $success . ', 失败:' . $error_num . $msg);
        }
        return $this->format_ret(1, '', '同步成功！');
    }


    function create_fail_file($error_msg, $fail_top) {
        $fail_top = (empty($fail_top)) ? array('平台商品条码', '错误信息') : $fail_top;
        $file_name = load_model('wbm/StoreOutRecordModel')->create_import_fail_files($fail_top, $error_msg);
        $message = '';
//        $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
        $url = set_download_csv_url($file_name,array('export_name'=>'error'));
        $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        return $message;
    }

}










