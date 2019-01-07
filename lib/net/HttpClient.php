<?php

/**
 * Class HttpClient
 * @author liud
 */
class HttpClient
{
    /**
     * @var array
     */
    private $options = array();

    /**
     * @var array
     */
    private $responses = array();

    /**
     * @var array
     */
    private $handles = array();

    /**
     * @var
     */
    private $mh;

    /**
     * @var
     */
    private $chs = array();

    /**
     * @var bool
     */
    private $isRunning = false;

    /**
     * @param array $options
     */
    public function __construct($options = array()) {
        $this->options = $options;
    }

    /**
     * Insert a handle into the end of the list.
     * @param string $type post or get
     * @param $url
     * @param array $headers
     * @param array $body
     * @return int
     * @throws Exception
     */
    public function appendHandle($type, $url, $headers = array(), $body = array()){
        $key = count($this->handles);
        $found = false;
        foreach($this->handles as $k=>$v){
            if(is_numeric($k)){
                if($k > $key) {
                    $key = $k;
                    $found = true;
                }
            }
        }
        if($found){
            $key++;
        }

        return $this->newHandle($key, $type, $url, $headers, $body);
    }

    /**
     * Insert a handle into the end of the list.
     * @param string $key
     * @param string $type post or get
     * @param $url
     * @param array $headers
     * @param array $body
     * @return int
     * @throws Exception
     */
     public function newHandle($key, $type, $url, $headers = array(), $body = array(),$handle_other = array()){
        if(empty($url)) {
            throw new Exception('URL address cannot be empty.');
        }

        $part = parse_url($url);
        if($part === false){
            throw new Exception('Invalid URL: '.$url);
        }
        if(empty($part['scheme']) || !in_array($part['scheme'], array('http', 'https'))){
            throw new Exception('Supports only http and https: '.$url);
        }

        $handle = array(
            'type'=> $type,
            'url'=>$url,
            'headers'=>$headers,
            'body'=>$body,
        );
        if(!empty($handle_other)){
            $handle = array_merge($handle_other, $handle);
        }
        $this->handles[$key] = $handle;
        return count($this->handles) - 1;
    }

    /**
     * Deleting handle
     * @param $key
     */
    public function removeHandle($key) {
        if($this->isRunning === true) {
            return;
        }

        if(isset($this->handles[$key])) {
            unset($this->handles[$key]);
        }

        if(isset($this->chs[$key])) {
            curl_close($this->chs[$key]);
            unset($this->chs[$key]);
        }

        if(isset($this->responses[$key])) {
            unset($this->responses[$key]);
        }
    }

    /**
     * Processes each of the handles in the stack.
     * This method can be called whether or not a handle needs to read or write data.
     * @throws Exception
     */
    public function exec() {
        $time1 = date("Y-m-d H:i:s");
        $time_num = time();
        $this->isRunning = true;

        $this->chs = array();
        $this->responses = array();

        //create the multiple cURL handle
        $this->mh = curl_multi_init();

        //add the two handles
        foreach($this->handles as $key => &$handle) {
            $this->addHandle($key, $handle);
        }

        $active = null;
        //execute the handles
        do {
            $mrc = curl_multi_exec($this->mh, $active);
            /*$info = curl_multi_info_read($this->mh);
            if (false !== $info) {
                var_dump($info);
            }*/
        } while ($mrc == CURLM_CALL_MULTI_PERFORM || $active);

        //Blocks until there is activity on any of the curl_multi connections.
        while ($active && $mrc == CURLM_OK) {
            $a = curl_multi_select($this->mh);
            if ($a != -1) {
                do {
                    $mrc = curl_multi_exec($this->mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        //close the handles
        foreach($this->chs as $key => &$ch) {
            $this->getContent($key, $ch);
        }

       // if(defined('DEBUG') && DEBUG){
            $time2 = date("Y-m-d H:i:s");
            foreach($this->chs as $key => &$ch) {
                $req = var_export($this->handles[$key], true);
                $res = var_export($this->responses[$key], true);
                $logPath = $this->get_log_path();
         
                $cha_time = time()-$time_num;
                error_log(date("Y-m-d H:i:s").":({$time1}-{$time2} 耗时：{$cha_time}) \n".$req."\n".$res."\n\n", 3, $logPath);
            }
      //  }

        curl_multi_close($this->mh);

        $this->isRunning = false;
    }
    
     function  get_log_path(){
          static $logPath = NULL;
          if( $logPath === NULL ){
            $date = date("Y-m-d");
            $logPath = ROOT_PATH."logs".DIRECTORY_SEPARATOR;

            if (defined('RUN_SAAS') && RUN_SAAS) {
                 $logPath .= "http_client".DIRECTORY_SEPARATOR;
                  if (!file_exists($logPath)){
                              mkdir($logPath);
                  }
                  $logPath .= $date.DIRECTORY_SEPARATOR;
                  if (!file_exists($logPath)){
                              mkdir($logPath);
                  }   
                 $logPath .= "http_client_";
                 $saas_key = CTX()->saas->get_saas_key();
                 if (!empty($saas_key)) {
                     $logPath .= $saas_key."_";
                 }       

                $logPath .=$date.".log";
            }else{
                $logPath .= "http_client".$date.".log";
            }
          }
           return $logPath;
    }



    /**
     * @return array
     */
    public function responses() {
        return $this->responses;
    }

    /**
     * Adds the ch handle to the multi handle $this->mh.
     * @param $key
     * @param $handle
     */
    private function addHandle($key, &$handle) {
        // create both cURL resources
        $this->chs[$key] = curl_init();

        // set URL and other appropriate options
        curl_setopt($this->chs[$key], CURLOPT_URL, $handle['url']);
        curl_setopt($this->chs[$key], CURLOPT_FAILONERROR, false);
        curl_setopt($this->chs[$key], CURLOPT_RETURNTRANSFER, true);
        
        if(isset($handle['timeout'])){
        //超时设置
            curl_setopt($this->chs[$key], CURLOPT_TIMEOUT, $handle['timeout']);
        }
        //curl_setopt($this->chs[$key], CURLOPT_HEADER, 1);

        if(is_array($handle['headers']) && count($handle['headers']) > 0){
            //$header = array ();
           // $header[] = $handle['headers']['header'];
            $header = $handle['headers'];
            curl_setopt($this->chs[$key], CURLOPT_HTTPHEADER, $header);
        }

        //https
        if(strlen($handle['url']) > 5 && strtolower(substr($handle['url'],0,5)) == "https" ) {
            curl_setopt($this->chs[$key], CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->chs[$key], CURLOPT_SSL_VERIFYHOST, false);
        }

        if (strtolower($handle['type']) == 'post') {
            $postMultipart = false;
            
            if(is_array($handle['body'])){
                foreach ($handle['body'] as $k => $v) {
                    if("@" == substr($v, 0, 1)) { //判断是不是文件上传
                        $postMultipart = true;
                        break;
                    }
                }
            }else{
                  $postMultipart = true;
            }
            
            if(is_array($handle['headers']) && count($handle['headers']) > 0){
                $postMultipart = true;
            }
            curl_setopt($this->chs[$key], CURLOPT_POST, true);

            if($postMultipart) {//有文件上传
                curl_setopt($this->chs[$key], CURLOPT_POSTFIELDS, $handle['body']);
            } else {
                $postBodyString = "";
                foreach ($handle['body'] as $k => $v) {
                    $postBodyString .= "$k=" . urlencode($v) . "&";
                }
                $postBodyString = rtrim($postBodyString, '&');
                curl_setopt($this->chs[$key], CURLOPT_POSTFIELDS, $postBodyString);
            }
        }

        //add the two handles
        curl_multi_add_handle($this->mh, $this->chs[$key]);
    }

    /**
     * Return the content of a cURL handle if CURLOPT_RETURNTRANSFER is set.
     * @param $key
     * @param $ch
     * @throws Exception
     */
    private function getContent($key, &$ch) {
        $err = curl_errno($ch);
        if ($err != '') {
            $this->responses[$key] = $err;
        }

        /*$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (200 !== $httpStatusCode) {
            throw new Exception($this->responses[$key], $httpStatusCode);
        }*/

        $this->responses[$key] = curl_multi_getcontent($ch);
        curl_multi_remove_handle($this->mh, $ch);
    }
}