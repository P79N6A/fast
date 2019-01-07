<?php

require_lib('util/web_util', true);

class Index {

    function do_index(array & $request, array & $response, array & $app) {
        $app['page'] = 'null'; //取消引入默认页
        $app['title'] = "宝塔科技渠道门户";
        $app['tpl'] = 'index_do_index'; //显式指定view页面
        //未登录重定向到登录页
        if (!CTX()->get_session("IsLogin")) {
            CTX()->redirect('login/do_logout');
        }

        //获取主页菜单
        $user_model = load_model('sys/UserModel');
        $response['top_menu'] = $user_model->get_top_menu();
        $response['menu_tree'] = $user_model->get_menu_tree();
    }

    function do_welcome(array & $request, array & $response, array & $app) {
        
    }

    //问题反馈插件页面
    function do_xqfk_plug(array & $request, array & $response, array & $app) {
        //首先验证客户是否为合法efast5授权
        $app['page'] = 'null';   //取消引入默认页
        $khid = $request['khid'];
        if (!empty($khid)) {
            //验证客户授权
            $ret = load_model('servicenter/ProductxqissueModel')->ver_kehu_auth($khid);
            if (!$ret) {
                //exit_json_response(array('status' => '-1', 'data' => '', 'message' => '客户授权不合法'));
                $response['auth_state'] = "-1";
            } else {
                //exit_json_response(array('status' => '1', 'data' =>$ret, 'message' => '客户验证通过'));
                $response['auth_state'] = "1";
                $response['kh_id'] = $ret['kh_id'];
                $response['kh_name'] = $ret['kh_name'];
                $response['user_name'] = $request['user_name'];
                $response['user_code'] = $request['user_code'];
                //获取提单邮箱
                $response['xqsue_email'] = load_model('servicenter/ProductxqissueModel')->get_email_by_field($ret['kh_id'], $request['user_code']);
            }
        } else {
            //exit_json_response(array('status' => '-1', 'data' => '', 'message' => '客户授权不合法'));
            $response['auth_state'] = "-1";
        }
    }

    //问题反馈插件页面提交操作
    function do_subxqfk_plug(array & $request, array & $response, array & $app) {
        $xqissue['xqsue_kh_id'] = $request['kh_id'];
        $xqissue['xqsue_cp_id'] = $request['cp_id'];
        //获取产品默认版本
        $xqissue['xqsue_pv_id'] = load_model('servicenter/ProductxqissueModel')->getversion_bycp($request['cp_id']);
        //匹配模块
        $xqissue['xqsue_product_fun'] = load_model('servicenter/ProductxqissueModel')->getmod_bycp($request['cp_id'], $request['md_name']);
        //获取客户联系人和联系方式
        $kh_other = load_model('servicenter/ProductxqissueModel')->get_clients_other($request['kh_id']);
        $xqissue['xqsue_kh_contact'] = $kh_other['kh_itphone'];
        $xqissue['xqsue_kh_phone'] = $kh_other['kh_itname'];
        //提单来源—客户
        $xqissue['xqsue_submit_source'] = '2';
        $xqissue['xqsue_background'] = $request['title'];
        $xqissue['xqsue_title'] = $request['title'];
        $xqissue['xqsue_detail'] = $request['detail'];
        $xqissue['xqsue_email'] = $request['email'];
        $xqissue['xqsue_user_code'] = $request['user_code'];
        $xqissue['xqsue_user_name'] = $request['user_name'];
        $xqissue['xqsue_user'] = $request['user_name'];
        $xqissue['xqsue_status'] = '1';
        $xqissue['xqsue_submit_time'] = date('Y-m-d H:i:s');
        //处理附件
        $licenceimg = CTX()->get_app_conf('xqfkimg');
        $upfile = $licenceimg['upfile'];  //图片目录路径
        $_file = str_replace($upfile . '/', '', $request['file']);

        $ret = load_model('servicenter/ProductxqissueModel')->insert($xqissue, $_file);
        exit_json_response($ret);
    }

    //上传扫描件操作
    function uploadxqfkimg(array & $request, array & $response, array & $app) {
        $licenceimg = CTX()->get_app_conf('xqfkimg');
        $arrType = $licenceimg['arrType'];
        $max_size = $licenceimg['max_size'];      // 最大文件限制（单位：byte）
        $upfile = $licenceimg['upfile'];  //图片目录路径
        $file = $_FILES['upfile'];
        if (!is_uploaded_file($file['tmp_name'])) { //判断上传文件是否存在
            //文件不存在
            exit_json_response(array('status' => '-1', 'data' => '', 'message' => '上传失败'));
        }
        if ($file['size'] > $max_size) {  //判断文件大小是否大于5242880字节
            //上传文件太大;
            exit_json_response(array('status' => '-1', 'data' => '', 'message' => '上传文件超出大小'));
        }
        if (!in_array($file['type'], $arrType)) {  //判断图片文件的格式
            //上传文件格式不对;
            exit_json_response(array('status' => '-1', 'data' => '', 'message' => '上传文件格式不对'));
        }
        if (!file_exists($upfile)) {  // 判断存放文件目录是否存在
            mkdir($upfile, 0777, true);
        }
        $imageSize = getimagesize($file['tmp_name']);
        $img = $imageSize[0] . '*' . $imageSize[1];
        $fname = $file['name'];
        $ftype = explode('.', $fname);
        $picName = $upfile . "/" . uniqid() . "." . $ftype[1];
        if (file_exists($picName)) {
            //>同文件名已存在;
            exit_json_response(array('status' => '-1', 'data' => '', 'message' => '上传失败'));
        }
        if (!move_uploaded_file($file['tmp_name'], $picName)) {
            //移动文件出错;
            exit_json_response(array('status' => '-1', 'data' => '', 'message' => '上传失败'));
        } else {
            //exit_json_response(array('status' => '1', 'data' =>array('imgname'=>$fname,'imgpath'=>$picName), 'message' => 'success'));
            exit_json_response(array('status' => '1', 'data' => array('path' => json_encode(array($picName, $fname))), 'message' => 'success'));
            //上传成功，返回
            //echo "<font color='#FF0000'>图片文件上传成功！</font><br/>";
            //echo "<font color='#0000FF'>图片大小：$img</font><br/>";
            //echo "图片预览：<br><div style='border:#F00 1px solid; width:200px;height:200px'>
            //<img src=\"".$picName."\" width=200px height=200px>".$fname."</div>";
        }
    }

}
