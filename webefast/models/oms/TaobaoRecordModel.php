<?php
require_model('tb/TbModel');
require_model('base/ShopApiModel');
require_model('oms/TaobaoTransferModel', true);

class TaobaoRecordModel extends TbModel {
    /**
     * @var string 表名
     */
    protected $table = 'oms_taobao_record';

    /**
     * @var array 交易状态
     */
    public $status = array(
        'TRADE_NO_CREATE_PAY' => '没有创建支付宝交易',
        'WAIT_BUYER_PAY' => '等待买家付款',
        'SELLER_CONSIGNED_PART' => '卖家部分发货',
        'WAIT_SELLER_SEND_GOODS' => '等待卖家发货',
        'WAIT_BUYER_CONFIRM_GOODS' => '等待买家确认收货',
        'TRADE_BUYER_SIGNED' => '买家已签收',
        'TRADE_FINISHED' => '交易成功',
        'TRADE_CLOSED' => '付款后交易关闭',
        'TRADE_CLOSED_BY_TAOBAO' => '付款前交易关闭',
        'WAIT_SELLER_AGREE' => '等待卖家同意',
    );

    /*
     * 交易下载(云)
     */
    function download_cloud($request){
        //$taskId = 2000;
        $mdl_shop_api = new ShopApiModel();
        $shopList = $mdl_shop_api->get_shop_api(9);
        $msgList = '';

        foreach ($shopList as $k => $shop) {
            if(empty($shop['api']['nick'])){
                continue;
            }

            $ignores = 0;
            $success = 0;
            $err  = '无';

            //读取最近下载时间
            //and status = 'WAIT_SELLER_SEND_GOODS'
            /*$sql = "select max(modified) from oms_taobao_record
            where seller_nick = :seller_nick
            ";

            $lastModified = $this->db->get_value($sql, array(':seller_nick' => $shop['api']['nick']));
            if(empty($lastModified)) $lastModified = '2014-01-01 00:00:00';*/

            //FIXME: sql inject.
            $status = '';
            if(!empty($request['status'])){
                $str = implode("','", explode(',', $request['status']));
                $status = " and a.status in ('{$str}') "; ;
            }

            //下载交易
            //and a.tid = '828563782139032'
            //and a.status = 'WAIT_SELLER_SEND_GOODS'
            //LIMIT 100
            $sql = "select a.* from sys_info.jdp_tb_trade a
            where a.seller_nick = :seller_nick
            {$status}
            and a.created >= :created_min
            and a.created <= :created_max
            ORDER BY a.created ASC
            ";
            $val = array(
                'seller_nick' => $shop['api']['nick'],
                //'modified' => $lastModified,
                'created_min' => $request['created_min'],
                'created_max' => $request['created_max'],
            );

            $tradeList = $this->db->get_all($sql, $val);


            foreach ($tradeList as $key=>$data) {
                $_sql = "select * from oms_taobao_record where tid = :tid";
                $_trade = $this->db->get_row($_sql, array('tid'=>$data['tid']));
                if(!empty($_trade)) {
                    $ignores++;
                    continue;
                } //交易已下载

                $this->begin_trans();
                try{
                    // taobao bug,number >2^32
                    $data['jdp_response'] = preg_replace('/(":)(\d{9,})/i', '${1}"${2}"', $data['jdp_response']);
                    $trade = json_decode($data['jdp_response'], true);
                    $trade = $trade['trade_fullinfo_get_response']['trade'];
                    $trade['shop_code'] = $shop['shop_code'];
                    $goodsList = $trade['orders']['order'];
                    unset($trade['orders']);

                    //保存
                    $ret = $this->db->insert("oms_taobao_record", $trade);
                    if($ret !== true){
                        throw new Exception('保存平台订单出错');
                    }
                    $recordId = $this->db->insert_id();
                    foreach ($goodsList as $goods) {
                        $goods['pid'] = $recordId;
                        $goods['tid'] = $trade['tid'];
                        $ret = $this->db->insert("oms_taobao_record_detail", $goods);
                        if($ret !== true){
                            throw new Exception('保存平台订单出错');
                        }
                    }

                    $this->commit();

                    $success++;
                }
                catch(Exception $e){
                    $this->rollback();
                    $err = $e->getMessage();
                    //break; //出错后必须终止当前店铺的后续交易下载
                }
            }

            //记录当前店铺的下载成功失败情况: 执行时间, 店铺, 下载起始时间, 下载记录数量, 转入跳过数量, 转入成功数量, 错误
            $msg = sprintf(
                "下载时间范围:%s ~ %s, 下载记录数量:%s, 下载跳过数量:%s, 下载成功数量:%s",
                $request['created_min'], $request['created_max'], count($tradeList), $ignores, $success
            );

            /*$this->insert('cli_task_log', array(
                'task_id' => $taskId,
                'run_time' => date('Y-m-d H:i:s'),
                'shop_code' => $shop['shop_code'],
                'result_msg' => $msg,
                'result_err' => $err,
            ));*/

            $msgList .= $shop['shop_name']. ': ' . $msg . ' ';
        }

        $s = empty($msgList) ? '当前没有平台订单需要下载' : $msgList;

        return array('status'=>1, 'message'=>$s, 'data'=>'');
    }
    

    /*
     * 交易转入
     */
    function transfer($request){
        //$taskId = 1000;
        $mdl_transfer = new TaobaoTransferModel();
        $mdl_shop_api = new ShopApiModel();
        $shopList = $mdl_shop_api->get_shop_api(9);
        $msgList = '';

        foreach ($shopList as $k => $shop) {
            $faiured = 0;
            $success = 0;
            $err  = '';

            //下载交易
            //and a.tid = '828563782139032'
            //LIMIT 100
            $sql = "select a.record_id, a.tid from oms_taobao_record a
            where shop_code = :shop_code
            and a.status = 'WAIT_SELLER_SEND_GOODS'
            and a.created >= :created_min
            and a.created <= :created_max
            and a.is_change = 0
            ORDER BY a.modified ASC
            ";
            $val = array(
                'shop_code' => $shop['shop_code'],
                'created_min' => $request['created_min'],
                'created_max' => $request['created_max'],
            );
            $tradeList = $this->db->get_all($sql, $val);

            foreach ($tradeList as $key=>$data) {
                $ret = $mdl_transfer->transfer_by_id($data['record_id']);
                if($ret['status'] == '1'){
                    $success++;
                }
                else{
                    $faiured++;
                    $err .= $data['tid'].':'.$ret['message'].',';
                }
            }

            //记录当前店铺的转入成功失败情况: 执行时间, 店铺, 转单数量, 失败数量, 成功数量, 错误
            $msg = sprintf(
                "转单数量:%s, 失败数量:%s, 成功数量:%s",
                count($tradeList), $faiured, $success
            );

            /*$this->insert('cli_task_log', array(
                'task_id' => $taskId,
                'run_time' => add_time(),
                'shop_code' => $shopCode,
                'result_msg' => $msg,
                'result_err' => $err,
            ));*/

            $msgList .= $shop['shop_name']. ': ' . $msg . ' ';
        }

        $s = empty($msgList) ? '当前没有平台订单需要转入' : $msgList;

        return array('status'=>1, 'message'=>$s, 'data'=>'');
    }
}