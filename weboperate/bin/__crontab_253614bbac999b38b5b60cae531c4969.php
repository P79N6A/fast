<?php
header("Content-type: text/html; charset=UTF-8");
define('RUN_FROM_INDEX',true);
include dirname(dirname(dirname(__FILE__))).'/boot/req_init.php';
require_lib('util/common_util');
$log_name = ROOT_PATH . "logs/crontab.log";



// 是否为windows系统
if (substr(strtolower(PHP_OS), 0, 3) == 'win') {
	define('_OS_WIN_', true);
} else {
	define('_OS_WIN_', false);
}

$cron_num = get_cron_running_count();

if ($cron_num > 1) {
	exit("schedule should be one instance! so exit\r\n");
}

$sql = "SELECT * FROM `schedule` WHERE status=1";
if (isset($filter['id']) && (intval($filter['id']) >1)) {
	$sql .= " AND id='" . intval($filter['id']) . "'";
}

$crons = CTX()->db->get_all($sql);

if (empty($crons)) {
	exit("no task to run. so exit.\r\n");
}



$php_bin = get_php_bin();



$timestamp = time();
$success = array();

$schedule_id = $timestamp; // 用当前时间戳作为调度id

echo "[".date('Y-m-d H:i:s')."] [INFO] <{$schedule_id}> schedule start...\r\n";
foreach ((array)$crons as $_cron) {
	$is_success = -1;
	$curr_num = get_task_running_count($_cron);

	//如果已经执行了系统允许的最大个数的话,直接退出
	if ($_cron['max_num'] <= $curr_num) {
		continue;
	}

	$_cron['path'] = ROOT_PATH . strtolower($_cron['path']) . '/web/index.php';

	$script = get_script_str($php_bin, $_cron, $log_name);
	if (empty($script)) {
		echo "[".date('Y-m-d H:i:s')."] [ERROR] <{$schedule_id}> script is empty, skip task: CODE={$_cron['code']}\r\n";
		continue;
	}
	
	if (!should_run_script($_cron, $timestamp)) {
		continue;
	}

	echo "[".date('Y-m-d H:i:s')."] [INFO] <{$schedule_id}> task: CODE={$_cron['code']} start to run.\r\n";
	$sql = "UPDATE schedule SET last_run_time='".date('Y-m-d H:i:s', $timestamp)."' WHERE id='{$_cron['id']}'";
	CTX()->db->query($sql);
	//调度计划任务
	$result = system($script, $is_success);

	if ($is_success >= 0) {		
		log_crontab("task: {$_cron['name']}_{$_cron['code']} run success!\r\n");
		echo "[".date('Y-m-d H:i:s')."] [INFO] <{$schedule_id}> task: CODE={$_cron['code']} run success!\r\n";
	} else {
		log_crontab_error($result);
		echo "[".date('Y-m-d H:i:s')."] [ERROR] <{$schedule_id}> task: CODE={$_cron['code']} success!\r\n";
	}
}
exit("[".date('Y-m-d H:i:s')."] [INFO] <{$schedule_id}> schedule complete.\r\n");
/////////////////////////////////////////////////////////////////////////////////
//////////////辅助函数，支持WINDOWS\LINUX
/////////////////////////////////////////////////////////////////////////////////
function get_cron_running_count() {
	if (_OS_WIN_) {
		$cmd = 'tasklist /V /FO CSV|find "php crontab.php"';
		$tmp_arr = array();
		exec($cmd, $tmp_arr);
		$curr_num = count($tmp_arr);
	} else {
		//获得正在运行的计划任务的个数
		$cmd = 'ps aux|grep "crontab.php"|grep -v "crontab.php"|wc -l';		
		$curr_num = exec($cmd);
	}
	
	return $curr_num;
}
function get_task_running_count($_cron) {
	if (_OS_WIN_) {
		$cmd = 'tasklist /V /FO CSV|find "S_CODE='.$_cron['code'].'"';
		$tmp_arr = array();
		exec($cmd, $tmp_arr);
		$curr_num = count($tmp_arr);
	} else {
		//获得正在运行的计划任务的个数
		$filter = 'ps aux|grep "S_CODE=s_'.$_cron['code'].'"|grep -v "grep S_CODE=s_'.$_cron['code'].'"|wc -l';
		//echo $filter;
		$curr_num = exec($filter);
	}
	
	return $curr_num;
}
function should_run_script($_cron, $timestamp) {
	/*
	 * 1-循环间隔执行，2-每天定点执行，3-每周定点执行，4-每月定点执行
	 */
	if ($_cron['type'] == 1) {

		$cron_times = explode('|', $_cron['value']);

		$time_value = intval(trim($cron_times[0]));

		$cron_time_start = $cron_time_end = 0;
		if (isset($cron_times[1])) {
			$cron_time_spans = explode('-', $cron_times[1]);
			if (count($cron_time_spans) == 2) {
				$cron_time_start = strtotime(date('Y-m-d', $timestamp) . $cron_time_spans[0]);
				$cron_time_end = strtotime(date('Y-m-d', $timestamp) . $cron_time_spans[1]);
			}
		}
		//不在可执行时间范围内
		if ($timestamp >= $cron_time_start && $timestamp <= $cron_time_end) {
			return false;
		}
		//还没有到执行时间
		if (($timestamp - strtotime($_cron['last_run_time'])) < ($time_value * 60)) {
			return false;
		}

	} else if ($_cron['type'] == 2) {
		//每天定点执行 已经执行时间比设置时间小
		$time_value = trim($_cron['value']);
		$time_str = date('Y-m-d ', $timestamp) . $time_value . ':00';
		$time_str = strtotime($time_str);

		//还没有到执行时间
		if (abs($time_str - $timestamp) > 30) {
			return false;
		}

	} else if ($_cron['type'] == 3) {
		//每周定点执行 已经执行时间比设置时间小
		$time_value = trim($_cron['value']);

		$week_day = substr($time_value, 0, 1);
		//不是指定可以运行的时间
		if (date('N', $timestamp) != $week_day) {
			return false;
		}

		$time_value = substr($time_value, strlen($time_value) - 5, 5);
		$time_str = date('Y-m-d ', $timestamp) . $time_value . ':00';
		$time_str = strtotime($time_str);

		//还没有到执行时间
		if (abs($time_str - $timestamp) > 30) {
			return false;
		}

	} else if ($_cron['type'] == 4) {
		//每月定点执行 已经执行时间比设置时间小
		$time_value = trim($_cron['value']);

		$month_day = substr($time_value, 0, 2);
		//不是指定可以运行的日期
		if (date('d', $timestamp) != $month_day) {
			return false;
		}

		$time_value = substr($time_value, strlen($time_value) - 5, 5);
		$time_str = date('Y-m-d ', $timestamp) . $time_value . ':00';
		$time_str = strtotime($time_str);

		//还没有到执行时间
		if (abs($time_str - $timestamp) > 30) {
			return false;
		}
	}
	
	return true;
}
// 获取要执行的脚本
function get_script_str($php_bin, $_cron, $log_name) {
	$ext = substr($_cron['path'], strlen($_cron['path']) - 3, 3);
	if ($ext == 'php') {
		$_prefix = _OS_WIN_ ? '' : 'nohup';

		$script = "{$_prefix} {$php_bin} -f {$_cron['path']} {$_cron['params']} S_CODE=s_{$_cron['code']} app_mode=func task_type=schedule > {$log_name} 2>&1 &";
	} else if ($ext == '.sh') {
		$script = "nohup {$_cron['path']} S_CODE=s_{$_cron['code']} > {$log_name} 2>&1 &";
	} else {
		$script = '';
	}
	return $script;
}
// 获取php执行文件绝对路径
function get_php_bin() {
	//如果定义了_PHP_BIN_ 的话,直接使用,不然的话,自动识别是linux还是win,根据PHP预定义常量获得phpbin执行的路径
	if (defined('_PHP_BIN_') && _PHP_BIN_ != '') {
		$php_bin = _PHP_BIN_;
	} else if (_OS_WIN_) {
		$php_bin = PHP_BINDIR . '/php.exe';
	} else {
		$php_bin = PHP_BINDIR . '/php';
	}
	if (!file_exists($php_bin)) {
		exit("[{$php_bin}]不存在,请确认php已经正确安装并且php预定义常量PHP_BINDIR设置正确或者直接定义(define)_PHP_BIN_常量");
	}
	
	return $php_bin;
}
function log_crontab_error($msg) {
	error_log("[".date('Y-m-d H:i:s')."] ".$msg, 3, ROOT_PATH . "/logs/crontab_error.log");
}
function log_crontab($msg) {
	error_log("[".date('Y-m-d H:i:s')."] ".$msg . "\r\n", 3, ROOT_PATH . "/logs/crontab_success.log");
}


