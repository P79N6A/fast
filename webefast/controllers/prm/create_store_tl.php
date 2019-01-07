<?php
require_lib ( 'util/web_util', true );
require_lib('comm_util', true);
require_model('prm/GoodsUniqueCodeTLModel', true);
class create_store_tl{
    function do_list(array & $request, array & $response, array & $app){
        
    }
    function edit_warehouse(array & $request, array & $response, array & $app){
        
        $response['start_time'] = date('Y-m-d');
        $response['end_time'] = date('Y-m-d');
        $response['store'] = load_model('prm/GoodsUniqueCodeTLModel')->get_store($request['id']);
        $response['type'] = $request['type'];
        $response['id'] = $request['id'];
        
          //获取所有仓库
         $response['all_store']= load_model('base/StoreModel')->get_purview_store();
    }
    //不影响库存直接保存仓库代码
    function save_store(array & $request, array & $response, array & $app){
        
        $data = load_model('prm/GoodsUniqueCodeTLModel')->unsave_store($request['store'],$request['jewelry_id']);
        if(isset($data['status']) && $data['status']<>'1'){
         $ret = array(
                    'status' => -1,
                    'data' => '',
                    'message' => '更改失败',
                );
            } else {
                $ret = array(
                    'status' => 1,
                    'data' => '',
                    'message' => '更改成功',
                );
             }
            exit_json_response($ret);
         }
         //判断仓库是否相同
   function judge_store(array & $request, array & $response, array & $app){
       $app['fmt'] = 'json';
       $mdl = new GoodsUniqueCodeTLModel();
       $response = $mdl->opt_store($request['jewelry_id']);
     }
     //生成移仓单
     function transfer_warehouse(array & $request, array & $response, array & $app){
        $arr['record_code'] = load_model('stm/StoreShiftRecordModel')->create_fast_bill_sn();//单据编号
       
        $array = array(
            'is_shift_in_time'=>$request['is_shift_in_time'],
             'is_shift_out_time'=>$request['is_shift_out_time'],
             'shift_in_store_code'=>$request['shift_in_store_code'],
             'shift_out_store_code'=>$request['shift_out_store_code'],
            'record_code'=>$arr['record_code'],
            'remark' => '由唯一码更改仓库生成',
            'rebate'=> 1,//折扣
            'is_add_person' => CTX()->get_session('user_name')
        );
        
        $goods_data = load_model('prm/GoodsUniqueCodeTLModel')->get_ware_detail($request['jewelry_id']);//获取详情
        $ret = load_model('stm/StoreShiftRecordModel')->do_add_record($array);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "", 'finish_status' => '未确认', 'action_name' => "创建", 'module' => "store_shift_record", 'pid' => $ret['data']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        
        $res = load_model('prm/GoodsLofModel')->add_detail_action($ret['data'], $goods_data);
        //单据批次添加
        $res = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($ret['data'], $request['shift_out_store_code'], 'shift_out', $goods_data);
        if($res['status']<1){
                        return $res;
                     }        
        //增加明细
        $res = load_model('stm/StoreShiftRecordDetailModel')->add_detail_action($ret['data'], $goods_data);
        $pid = $ret['data'];
        //确认
        $type_arr = array('enable' => 1, 'disable' => 0);
        $res = load_model('stm/StoreShiftRecordModel')->update_sure($type_arr['enable'], 'is_sure', $ret['data']);

        $ret = load_model('stm/StoreShiftRecordModel')->shift_out($ret['data']);
        if ($ret['status'] == '1') {
            $data = load_model('prm/GoodsUniqueCodeTLModel')->unsave_store($request['shift_in_store_code'],$request['jewelry_id']);
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '出库', 'action_name' => '出库', 'module' => "store_shift_record", 'pid' => $pid);
            $ret2 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
     }
     

}
