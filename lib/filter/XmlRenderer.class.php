<?php
require_once ROOT_PATH . 'boot/req_inc.php';
/**
 * 用于xml数据渲染。
 * 根据$app['fmt']!=='xml'来决定是否采用json渲染。
 * json渲染结果包括resp_error，resp_data两种情况。
 */
class XmlRenderer implements IReponseRenderer {
    function render(array & $request, array & $response, array & $app) {
        if (strtolower($app['fmt']) !== 'xml') {
            return;
        }
        
        require_lib('xml/Array2XML');
        if ($app['err_no'] !== 0) {
            $data = array(
                    'app_err_no' => $app['err_no'],
                    'app_err_msg' => $app['err_msg']
            );
            $ret = Array2XML::createXML('resp_error', $data)->saveXML();
        } else {
            $ret = Array2XML::createXML('root', $response)->saveXML();
        }
        
        echo $ret;
        
        return true;
    }
}