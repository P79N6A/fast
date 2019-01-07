<?php

require_model('erp/bserp/BserpQimenBaseModel');

class BserpWbmQimenModel extends BserpQimenBaseModel {

    function __construct($erp_config_id) {
        parent::__construct();
        $this->get_erp_config($erp_config_id);
    }
    
    function erp_upload_wbm() {
        $this->create_client();
        //该配置仓库及店铺
        $efast_cks = array_keys($this->config_store);
        $efast_ck_str = "'" . join("','", $efast_cks) . "'";
        $bserp_trade_tb = "api_{$this->tb_key}_wbm_record";
        
        //获取未上传的批发销货单
        $record_sql = "select r.record_code as orderCode,r.record_code as orderId,'B2BCK' as orderType,r.record_time,r.money as amount,r.store_code,r.num as number,r.remark,IFNULL(w.upload_status,0) as upload_status  from wbm_store_out_record r left join {$bserp_trade_tb} as w on r.record_code=w.record_code and w.order_type=1 where r.is_store_out=1 ";
        $sql = "select * FROM ($record_sql) as tmp WHERE record_time>= '{$this->config['online_time']}' and upload_status != 1 and store_code in($efast_ck_str)";
        $record_arr = $this->db->getAll($sql);
        
        //获取未上传的批发退货单
        $return_sql = "select r.record_code as orderCode,r.record_code as orderId,'B2BRK' as orderType,r.record_time,r.money as amount,r.store_code,r.num as number,r.remark,IFNULL(w.upload_status,0) as upload_status  from wbm_return_record r left join {$bserp_trade_tb} as w on r.record_code=w.record_code and w.order_type=2 where r.is_store_in=1  ";
        $sql = "select * FROM ($return_sql) as tmp WHERE record_time>= '{$this->config['online_time']}' and upload_status != 1 and store_code in($efast_ck_str)";
        $return_arr = $this->db->getAll($sql);
        
        $recode_code_arr = array();
        $wbm_record_arr = array();
        $wbm_record_arr_log = array();
        $return_code_arr = array();
        $wbm_return_arr = array();
        $wbm_return_arr_log = array();
        
         //批发销货单
        foreach ($record_arr as $val) {
            $recode_code_arr[] = $val['orderId'];
            $remark_record[$val['orderId']] = $val['remark'];
            //上传数据拼装
            $wbm_record_arr[$val['orderId']] = $val;
            unset($wbm_record_arr[$val['orderId']]['record_time']);
            unset($wbm_record_arr[$val['orderId']]['store_code']);
            unset($wbm_record_arr[$val['orderId']]['upload_status']);
            unset($wbm_record_arr[$val['orderId']]['remark']);
            //记录日志数据
            $wbm_record_arr_log[$val['orderId']]['order_type'] = 1;
            $wbm_record_arr_log[$val['orderId']]['record_code'] = $val['orderId'];
            $wbm_record_arr_log[$val['orderId']]['store_code'] = $val['store_code'];
            $wbm_record_arr_log[$val['orderId']]['erp_store_code'] = $this->config_store[$val['store_code']];
            $wbm_record_arr_log[$val['orderId']]['upload_time'] = date("Y-m-d H:i:s");
        }
        
        //批发退货单
        foreach ($return_arr as $val) {
            $return_code_arr[] = $val['orderId'];
            $remark_return[$val['orderId']] = $val['remark'];
            //上传数据拼装
            $wbm_return_arr[$val['orderId']] = $val;
            unset($wbm_return_arr[$val['orderId']]['record_time']);
            unset($wbm_return_arr[$val['orderId']]['store_code']);
            unset($wbm_return_arr[$val['orderId']]['upload_status']);
            unset($wbm_return_arr[$val['orderId']]['remark']);
            //记录日志数据
            $wbm_return_arr_log[$val['orderId']]['order_type'] = 2;
            $wbm_return_arr_log[$val['orderId']]['record_code'] = $val['orderId'];
            $wbm_return_arr_log[$val['orderId']]['store_code'] = $val['store_code'];
            $wbm_return_arr_log[$val['orderId']]['erp_store_code'] = $this->config_store[$val['store_code']];
            $wbm_return_arr_log[$val['orderId']]['upload_time'] = date("Y-m-d H:i:s");
        }
        
        //获取批发销货单明细
        if (!empty($recode_code_arr)){
            $recode_code_str = "'".join("','",$recode_code_arr)."'";
            $sql = "select m.record_code,m.num as number,m.money as amount,b.barcode as styleCode,m.spec1_code as colorCode,m.spec2_code as sizeCode,m.rebate as discount, m.price as stdprice from wbm_store_out_record_detail m left join goods_barcode b on m.sku = b.sku where m.record_code in($recode_code_str)";
            $record_detail_ret = $this->db->getAll($sql);
            $record_detail = array();
            foreach ($record_detail_ret as $detail_row) {
                $detail_row['remark'] = $remark_record[$detail_row['record_code']];
                $record_code = $detail_row['record_code'];
                unset($detail_row['record_code']);
                $record_detail[$record_code][] = $detail_row;
            }
        }
        
        //获取批发退单明细
        if (!empty($return_code_arr)){
            $return_code_str = "'".join("','",$return_code_arr)."'";
            $sql = "select m.record_code,m.num as number,m.money as amount,b.barcode as styleCode,m.spec1_code as colorCode,m.spec2_code as sizeCode,m.rebate as discount, m.price as stdprice from wbm_return_record_detail m left join goods_barcode b on m.sku = b.sku where m.record_code in($return_code_str)";
            $return_detail_ret = $this->db->getAll($sql);
            $return_detail = array();
            foreach ($return_detail_ret as $detail_row) {
                $detail_row['remark'] = $remark_return[$detail_row['record_code']];
                $return_code = $detail_row['record_code'];
                unset($detail_row['record_code']);
                $return_detail[$return_code][] = $detail_row;
            }
        }
        
        //上传批发销货单
        foreach ($wbm_record_arr as $val) {
            //todo 数据格式转换
            $row['order'] = json_encode($val);
            $row['items'] = json_encode($record_detail[$val['orderId']]);
            //单据上传
            $ret = $this->upload_wbm($row);
            if ($ret['status'] == -1) {
                $wbm_record_arr_log[$val['orderId']]['upload_status'] = 2;
                $wbm_record_arr_log[$val['orderId']]['upload_msg'] = $ret['message'];
            } else {
                $wbm_record_arr_log[$val['orderId']]['upload_status'] = 1;
                $wbm_record_arr_log[$val['orderId']]['upload_msg'] = $ret['message'];
            }
            //更新上传表状态
            $update_str = "upload_status = VALUES(upload_status),upload_time = VALUES(upload_time),upload_msg = VALUES(upload_msg)";
            $this->insert_multi_duplicate("api_{$this->tb_key}_wbm_record", $wbm_record_arr_log, $update_str);
        }
        
        //上传批发退货单
        foreach ($wbm_return_arr as $val) {
            //todo 数据格式转换
            $row['order'] = json_encode($val);
            $row['items'] = json_encode($return_detail[$val['orderId']]);
            //单据上传
            $ret = $this->upload_wbm($row);
            if ($ret['status'] == -1) {
                $wbm_return_arr_log[$val['orderId']]['upload_status'] = 2;
                $wbm_return_arr_log[$val['orderId']]['upload_msg'] = $ret['message'];
            } else {
                $wbm_return_arr_log[$val['orderId']]['upload_status'] = 1;
                $wbm_return_arr_log[$val['orderId']]['upload_msg'] = $ret['message'];
            }
            //更新上传表状态
            $update_str = "upload_status = VALUES(upload_status),upload_time = VALUES(upload_time),upload_msg = VALUES(upload_msg)";
            $this->insert_multi_duplicate("api_{$this->tb_key}_wbm_record", $wbm_return_arr_log, $update_str);
        }
    }
    
    //上传
    function upload_wbm($api_data) {
        $ret = $this->erp_client->sync_entryorder($api_data);
        return $ret;
    }

}
