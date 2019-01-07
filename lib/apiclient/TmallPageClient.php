<?php

class TmallPageClient {

    protected $phantomjs_path = "/lib/apiclient/phantomjs/";
    protected $phantomjs = "";
    protected $file_path = "";
    protected $run_i = 0;

    function __construct() {

        $this->phantomjs_path = ROOT_PATH . $this->phantomjs_path;
        $this->phantomjs = $this->phantomjs_path . "phantomjs";
        $this->file_path = ROOT_PATH . CTX()->app_name . "/temp/tmall_item/";
    }

    function get_item_page_info($item_id) {
        $item_url = "https://detail.tmall.com/item.htm?id=" . $item_id;
        $file_path = $this->file_path . "{$item_id}.log";

        $command = $this->phantomjs . " " . $this->phantomjs_path . "tm_item.js " . "'{$item_url}' '{$file_path}'";
        //echo $command;die;
        exec($command);

        $data = array();
        $content = file_get_contents($file_path);
        if (!empty($content)) {
            $this->get_item_json($content);
            $data = json_decode($content, TRUE);
            // $data = json_decode($content, TRUE,512, JSON_BIGINT_AS_STRING);
        }
        if(empty($data)&&$this->run_i<3){
            $this->run_i++;
            return  $this->get_item_page_info($item_id);
        }
        
        $this->run_i = 0;

        return $data;
    }

    private function get_item_json(&$content) {
        $replace_arr = array(
            '<html><head></head><body><pre style="word-wrap: break-word; white-space: pre-wrap;">',
            "setMdskip\n",
            ')</pre></body></html>'
        );
        foreach ($replace_arr as $str) {
            $content = str_replace($str, '', $content);
        }

        $content = substr(trim($content), 1);
        return $content;
    }

}
