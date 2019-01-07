<?php

/*
 * 基础数据-产品销售渠道
 */

class sellchannel {
    
    //产品销售渠道列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }
    
    //新建、编辑产品销售渠道显示页面的方法
    function detail(array & $request, array & $response, array & $app) {
		$title_arr = array('edit'=>'编辑销售渠道', 'add'=>'新建销售渠道');
		$app['title'] = $title_arr[$app['scene']];
		$ret = load_model('basedata/SellchannelModel')->get_by_id($request['_id']);
                $response['data'] = $ret['data'];
	}
    //编辑销售渠道
    function do_edit(array & $request, array & $response, array & $app) {
		$channel = get_array_vars($request, 
                            array( 
                                'channel_name',
                                'channel_type',
                                'channel_mode',
                                'channel_desc',
                                ));
		$ret = load_model('basedata/SellchannelModel')->update($channel, $request['channel_id']);
		exit_json_response($ret);
	}
        
    //添加销售渠道。    
    function do_add(array & $request, array & $response, array & $app) {
		$channel = get_array_vars($request, 
                           array( 
                                'channel_name',
                                'channel_type',
                                'channel_mode',
                                'channel_desc',
                                ));
		$ret = load_model('basedata/SellchannelModel')->insert($channel);
		exit_json_response($ret);
	}
    
    
    
        //获取销售渠道json数据
        function getList(array & $request, array & $response, array & $app){
            if(!isset($request['pid'])){
                $cid="0"; //默认一级机构数据
            }else{
                $cid=$request['pid'];
            }
            if($cid!="0"){
                $ret = load_model('basedata/SellchannelModel')->getSellCListById($cid);
                /*foreach($ret as & $data){
                    if($data['leaf']=="0"){
                        $data['leaf']=false;
                    }else{
                        $data['leaf']=true;
                    }
                }*/
                exit_json_response($ret);
            }else{
                $retmr=array(array('id'=>'1','text'=>'直营','leaf'=>false),array('id'=>'2','text'=>'渠道','leaf'=>false));
                exit_json_response($retmr);
            }
        }
    
}