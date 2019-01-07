<?php
/**
 * 验证API调用签名
 * @param   request   $request   参数集合
 * @access  public
 * @return  bool   返回签名状态
 */
function ver_sign($request){
    $app_key = "yishang";
    $app_secret = "yishang123456";
    //时间戳有效期
    $new_timestamp = strtotime('+10 minute', strtotime($request['timestamp']));
    if ($new_timestamp < time()) {
        return array(
            'status'=>-1,
            'message'=>'请求时间失效',
            'data'=> array()
        );
    }
    //解析参数
    $data = array();
    $data['key'] = $request['key'];               //key
    $data['secret'] = $request['secret'];         //secret
    $data['app_act'] = $request['app_act'];       //method
    $data['timestamp'] =$request['timestamp'];    //时间戳(日期格式)
    //排序
    ksort($data);
    $sign = "";
    foreach ($data as $k=>$v){
        $sign .= $k.$v;
    }
    //md5加密
    $sign = strtoupper(md5($app_key . $sign . $app_secret));
    if($sign==$request['sign']){
        return array(
            'status'=>1,
            'message'=>'签名验证通过',
            'data'=> array()
        );
    }
    else{
        return array(
            'status'=>-2,
            'message'=>'签名无效',
            'data'=> array()
        );
    }
}

