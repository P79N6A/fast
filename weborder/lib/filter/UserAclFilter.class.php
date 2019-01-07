<?php
require_once ROOT_PATH . 'boot/req_inc.php';

class UserAclFilter implements IRequestFilter {
  function handle_before(array &$request, array &$response, array &$app) {
    $app_act = $app['grp'] . '/' . $app['act'];

    if (isset($app['path'])) $app_act = $app['path'] . $app_act;

    if ($app ['mode'] == 'func') {
      if ('wms_server' == $app['act']) return ;

      if (isset($request['task_type']) && $request['task_type'] == 'schedule') {
        $client_ip = get_client_ip();
        if ($client_ip != '127.0.0.1') {
          echo 'INVALID REQUEST:' . $client_ip;
          exit;
        }
        return ; // 验证通过
      }
      // 如果没有传递data数据的话,直接返回xml格式的
      // 系统接口默认返回xml格式的数据
      if (!isset($request['data'])) {
        echo array2xml(array('status' => 'INVALID_PARAMS', 'data' => '', 'message' => '参数不完整'), 'ERROR');
        exit;
      }
      // 获得传过来的数据的格式
      $format = api_get_request_format($request['data']);

      if (!isset($request['key']) || !isset($request['requestTime']) || !isset($request['version']) || !isset($request['serviceType']) || !isset($request['data'])) {
        $result = array('status' => 'INVALID_PARAMS', 'data' => '', 'message' => '参数不完整');
        api_format_response($result, $format, 'ERROR');
        // echo array2xml(array('status'=>'INVALID_PARAMS', 'data'=>'', 'message'=>'参数不完整'), 'ERROR');
        // exit;
      }
      $valid = false;
      $row = CTX() -> db -> get_row('SELECT * FROM api_user WHERE app_key=:key', array(':key' => $request['key']));

      if (!$row) {
        $result = array('status' => 'INVALID_KEY', 'data' => '', 'message' => '无效ID');
        api_format_response($result, $format, $request['serviceType']);
        // echo array2xml(array('status'=>'INVALID_KEY', 'data'=>'', 'message'=>'无效ID'), $request['serviceType']);
        // exit;
      }
      $sign_data = sprintf('key=%s&requestTime=%s&secret=%s&version=%s&serviceType=%s&data=%s',
        $request['key'], $request['requestTime'], $row['app_secret'], $request['version'], $request['serviceType'], $request['data']
        );

      $sign = md5($sign_data);

      if ($sign != $request['sign']) {
        $result = array('status' => 'INVALID_SIGNATURE', 'data' => '', 'message' => '无效签名，非法请求');
        api_format_response($result, $format, $request['serviceType']);
        // echo array2xml(array('status'=>'INVALID_SIGNATURE', 'data'=>'', 'message'=>'无效签名，非法请求'), 'ERROR');
        // exit;
      }
    } else {
      // 不需要启用acl的act列表
      $filename = ROOT_PATH . CTX() -> app_name . '/conf/uncheck_login_act_list.conf.php';
      $uncheck_act_list = array();
      if (file_exists($filename)) {
        $uncheck_act_list = include $filename;
      }
      $uncheck_act_list[] = 'index/login';

      if (in_array($app_act, $uncheck_act_list)) return;

      $sf_id = CTX() -> get_session('sf_id', true);

      if (null == CTX() -> get_session('sf_id', true)) {
        if (isset($request['window'])) {
          CTX() -> redirect('index/login');
        } else if (isset($request['ajax']) || isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
          $data = array('location' => '?app_act=index/login');
          exit_json_response(401, $data, lang('op_no_login'));
        } else {
          CTX() -> redirect('index/login');
        }
      }
      // 检查对应权限
      require_model('sys/UserAcl');
      $acl_obj = new UserAcl();
      if (!$acl_obj -> check_priv($sf_id, $app_act)) {
        if ((isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
          exit_json_response(-401, '', '无权进行此操作');
        } else {
          exit_no_priv_page('无权进行此操作', '请确保您使用的帐号【' . CTX() -> get_session('sf_name', true) . '】有执行此操作的权限！');
        }
      }
    }
  }
}

/**
 * 获得请求数据的格式
 *
 * @param string $string {xml|json string}
 * @return string {xml json}
 */
function api_get_request_format($string) {
  // 获得传过来的数据的格式
  $format = '';
  if (strpos($string, '<?xml') === 0) {
    $format = 'xml';
  } else {
    $format = 'json';
  }

  return $format;
}

/**
 * api接口返回格式化的结果
 *
 * @param array $data
 * @param string $type {json,xml}
 * @param string $tag 与xml共用
 * @return void
 */
function api_format_response($data, $type = 'json', $tag = '') {
  $data = array('status' => (!empty($data['status']) ? $data['status'] : 0),
    'message' => (!empty($data['message']) ? $data['message'] : ''),
    'data' => (!empty($data['data']) ? $data['data'] : ''),
    );
  if ($type == 'xml') {
    echo array2xml($data, $tag);
  } else {
    echo json_encode($data);
  }

  exit;
}

/**
 * 解析api传过来的参数
 *
 * @param string $data 接口接收到的数据
 * @param string $type {json,xml}
 * @return array
 */
function api_parse_params($data, $type = 'json') {
  if ($type == 'xml') {
    $ret = array();
    xml2array($data, $ret);
    $tmp = each($ret);
    return $tmp['value'];
  } else {
    $data = json_decode($data, true);
    return $data;
  }
}

/**
 * 记录api访问日志
 *
 * @param array $request
 * @return void
 */
function api_log_request($request) {
  unset($request['data']); // 较大，暂不记录业务数据
  $str = '';
  foreach ($request as $k => $v) {
    $str .= "&$k=$v";
  }
  $str = substr($str, 1);
  $client_ip = get_client_ip();
  error_log(date(OPM_TIME_FORMAT, OPM_TIME) . " [$client_ip] {$request['serviceType']} $str\n", 3, 'api_log.log');
}