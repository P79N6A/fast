<?php

require_once ROOT_PATH . 'boot/req_inc.php';

/**
 * 日志文件，建议调用context的log_debug,log_error函数。
 * @author zengjf
 *
 */
class Log implements ILog, IRequestTool {

    const ERROR = 1;
    const WARN = 2;
    const INFO = 3;
    const DEBUG = 4;

    public $threshold = self::ERROR;
    public $date_fmt = 'Y-m-d H:i:s';
    public $log_split = 0;
    public $level_hints = array(self::ERROR => 'req_log_error', self::WARN => 'req_log_warn', self::DEBUG => 'req_log_debug', self::INFO => 'req_log_info');

    //添加日志记录
    function error($msg) {
        $this->write_log($msg);
    }

    function warn($msg) {
        $this->write_log($msg, self::WARN);
    }

    function debug($msg) {
        $this->write_log($msg, self::DEBUG);
    }

    function info($msg) {
        $this->write_log($msg, self::INFO);
    }

    /**
     * 创建日志
     * @param $log_path 日志文件路径
     */
    function __construct($log_path) {
        $this->setLogPath($log_path);
    }

    public $log_path;
    private $enabled = false;

    function setLogPath($log_path) {
        $this->enabled = $log_path && is_dir($log_path);
        $this->log_path = $log_path;
    }

    /**
     * Write Log File
     * @param	string	the error level
     * @param	string	the error message
     */
    private function write_log($msg, $level = self::ERROR) {
        global $context;
        if ($this->enabled === false)
            return;
        if ($level > self::ERROR && $level > $this->threshold)
            return;

        if (!function_exists('get_client_ip'))
            require_lib('net/HttpEx');

        $level = $level < self::ERROR ? self::ERROR : $level > self::INFO ? self::INFO : $level;
        if ($level > self::ERROR)
            $filepath = $this->log_path . $context->app_name . '_debug_';
        else
            $filepath = $this->log_path . $context->app_name . '_error';

        if ($level == self::ERROR) {
            $this->set_log_error_path($filepath);
        }
        if ($this->log_split == 1)
            $filepath .= date('Y-m-d') . '.';
        elseif ($this->log_split == 2)
            $filepath .= date('Y-m') . '.';
        $filepath .= 'log';
        $message = "[" . date($this->date_fmt) . "]\t[" . get_client_ip() . "]\t[" . lang($this->level_hints[$level]) . "]\t{$msg}\n";
        file_put_contents($filepath, $message, FILE_APPEND);
        
        //记录错误日志
        if ($level == self::ERROR) {
              dev_log($message);
        } 
      
        
    }

    private function set_log_error_path(&$filepath) {
        //错误日志分客户
        if (defined('RUN_SAAS') && RUN_SAAS && isset(CTX()->saas)) {
            $filepath .= DIRECTORY_SEPARATOR;
            if (!file_exists($filepath)){
                        mkdir($filepath);
            }
            $filepath .= date('Y-m-d').DIRECTORY_SEPARATOR;
            if (!file_exists($filepath)){
                        mkdir($filepath);
            } 

            $saas_key = CTX()->saas->get_saas_key();
            if (!empty($saas_key)) {
                $filepath .= "error_".$saas_key . "_";
            }
        }
    }

    static function register($prop) {
        global $context;
        $app_log_path = $context->get_app_conf('log_path');
        $app_log_split = $context->get_app_conf('log_split');
        if (!$app_log_path) {
            $app_log_path = ROOT_PATH . "logs" . DIRECTORY_SEPARATOR;
            if (!file_exists($app_log_path))
                mkdir($app_log_path);
        }
        if (!isset($app_log_split))
            $app_log_split = true;
        $log = new Log($app_log_path);
        if (DEBUG)
            $log->threshold = Log::DEBUG;
        else
            $log->threshold = Log::ERROR;
        $log->log_split = $app_log_split;
        $log->debug('Log object create');
        return $log;
    }

}
