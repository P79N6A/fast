<?php

/*
 * 基础数据-行政区域对照
 */

class Area {
    
    function do_list(array & $request, array & $response, array & $app) {
        
    }
    
    //行政区域信息显示
    function detail(array & $request, array & $response, array & $app) {
        $title_arr = array('view'=>'查看行政区域');
        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('basedata/AreaModel')->get_by_id($request['_id']);
        $response['data'] = $ret['data'];
    }
    
    //获取行政区域json数据
    function getList(array & $request, array & $response, array & $app){
        if(!isset($request['pid'])){
            $areaid="0"; //默认一级机构数据
        }else{
            $areaid=$request['pid'];
        }
        $ret = load_model('basedata/AreaModel')->getAreaListById($areaid);
        //$response['data'] = $ret['data'];
        foreach($ret as & $data){
            if($data['leaf']=="0"){
                $data['leaf']=false;
            }else{
                $data['leaf']=true;
            }
        }
        exit_json_response($ret);
    }
    
    //保存平台对照名称
    function area_save(array & $request, array & $response, array & $app){
        $arealist=array(
            'pac_pt_code'=>$request['pac_pt_code'],
            'pac_base_area_id'=>$request['pac_base_area_id'],
            'pac_pt_area_name'=>$request['pac_pt_area_name'],
            'pac_type'=>$request['pac_type'],
        );
        $ret = load_model('basedata/AreaModel')->save_pt_area($arealist);
        exit_json_response($ret);
    }
}