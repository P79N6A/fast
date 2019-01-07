<?php
require_model('oms/SellRecordModel');

class SellRecordFixModel extends SellRecordModel
{
    public function fix_record($sellRecordCode){
        $record = $this->get_record_by_code($sellRecordCode);
        if(empty($record)){
            return array('status'=>-1, 'message'=>'订单不存在');
        }
        if($record['is_problem'] == '0'){
            return array('status'=>-1, 'message'=>'订单已转入');
        }

        $sql = "select * from oms_taobao_record where tid = :tid";
        $trade = $this->db->get_row($sql, array('tid'=>$record['deal_code']));
        if(empty($trade)){
            return array('status'=>-1, 'message'=>'平台订单不存在');
        }

        $shop = $this->db->get_row("select * from base_shop where shop_code = :code", array('code'=>$record['shop_code']));
        if(empty($shop)){
            return array('status'=>-1, 'message'=>'商店不存在:'.$record['shop_code']);
        }

        $this->begin_trans();
        try{
            //验证收货地址
            $this->fix_record_record($record, $trade, $shop);

            //验证商品明细
            $this->fix_detail($record, $trade);

            //刷新商品金额
            $this->refresh_record_price($record['sell_record_code']);

            $d = array(
                'is_problem' => '0',
                'is_problem_type' => '0',
                'is_problem_reason' => '',
            );
            $r = $this->db->update('oms_sell_record', $d, array('sell_record_code'=>$record['sell_record_code']));
            if($r !== true){
                throw new Exception('保存订单错误');
            }

            $this->commit();
            return array('status'=>1, 'message'=>'转入成功');
        } catch(Exception $e){
            $this->rollback();
            return array('status'=>-1, 'message'=>'转入失败:'.$e->getMessage());
        }
    }

    public function fix_detail($record, $trade){
        $detail_list = $this->get_detail_list_by_code($record['sell_record_code']);
        if (empty($detail_list)) {
            throw new Exception('订单明细不存在');
        }

        foreach($detail_list as $key=>$detail){
            $sql = "select * from goods_sku where barcode = :barcode";
            $barcode = $this->db->get_row($sql, array('barcode'=>$detail['barcode']));

            $sku_code = $detail['barcode'];
            if (!empty($barcode)) {
                $sku_code = $barcode['sku'];
            }
            $sql = "select * from goods_sku where sku = :sku";
            $sku = $this->db->get_row($sql, array('sku'=>$sku_code));
            if(empty($sku)){
                throw new Exception('商家编码不存在:'.$detail['barcode']);
            }

            //更新数据
            $sql = "select * from base_goods where goods_code = :goods_code";
            $goods = $this->db->get_row($sql, array('goods_code'=>$sku['goods_code']));

            $d['goods_code'] = $sku['goods_code'];
            $d['spec1_code'] = $sku['spec1_code'];
            $d['spec2_code'] = $sku['spec2_code'];
            $d['sku_id'] = $sku['sku_id'];
            $d['sku'] = $sku['sku'];
            $d['refer_price'] = $goods['price'];
            $d['goods_weigh'] = isset($goods['weight']) ? $goods['weight'] : 0;
            //$orderItem['sort_code'] = get_sort_code_by_goods_id($sku['goods_id']);

            $r = $this->db->update('oms_sell_record_detail', $d, array('sell_record_detail_id'=>$detail['sell_record_detail_id']));
            if($r !== true){
                throw new Exception('保存订单明细错误');
            }

            //锁定库存
            /*$info = array(
                'price_type' => 'sell_price',
                'money' => $detail['count_money'],
                'object_code' => $record['shop_code'],
                'relation_code' => $record['record_code'],
                'type' => '14',
                'remark' => '订单锁定库存',
            );
            $ret = load_model('prm/InvModel')->adjust(
                $record['store_code'],
                $d['sku'],
                array('lock_num'=>$detail['num']),
                $record['record_time'],
                $info
            );
            if($ret['status'] != '1') {
                throw new Exception('商品库存不足:'.$d['sku']);
            }
            $ret = $this->db->update('oms_sell_record_detail',
                array('lock_num'=>$detail['num'], 'lock_inv_status'=>'1'),
                array("sell_record_detail_id"=>$detail['sell_record_detail_id'])
            );
            if($ret != true){
                throw new Exception('更新订单明细失败');
            }*/
        }
    }

    public function fix_record_record($record, $trade, $shop){
        if(!empty($record['receiver_province'])
            && !empty($record['receiver_city'])
            && !empty($record['receiver_district'])
            && !empty($record['receiver_address'])){
            return; //数据正确直接返回
        }

        //解析省市区地址
        $region_arr['province'] = $trade['receiver_state'];
        $region_arr['city'] = $trade['receiver_city'];
        $region_arr['district'] = $trade['receiver_district'];
        $region = load_model('base/RegionModel')->get_region_id_by_name($region_arr);

        $d['receiver_country'] = 1;
        $d['receiver_province'] = $region['receiver_province'];
        $d['receiver_city'] = $region['receiver_city'];
        $d['receiver_district'] = $region['receiver_district'];
        $d['receiver_address'] = $trade['receiver_state'].' '.$trade['receiver_city'].' '.$trade['receiver_district'].' '.$trade['receiver_address'];
        if (empty($d['receiver_province']) || empty($d['receiver_city'])) {
            throw new Exception('省市区信息错误');
        }

        //发货仓库, 快递方式
        $d['store_code'] = $shop['send_store_code'];
        $d['express_code'] = $shop['express_code'];

        $r = $this->db->update('oms_sell_record', $d, array('sell_record_code'=>$record['sell_record_code']));
        if($r !== true){
            throw new Exception('保存省市区信息错误');
        }
    }
}