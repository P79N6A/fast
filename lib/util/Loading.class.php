<?php

/*
 * Copyright ©2013 Baison.com.cn
 * SQ365
 */

/**
 * 用于异步请求产生进度条
 *
 * @author jhua.zuo <jhua.zuo@baisonmail.com>
 * @date 2013-11-26
 */
class Loading {

    private $name = '';

    /**
     * 进度条资源
     * @param string $name 进度条名称
     * @param boolean $readonly 是否只读：true用于读取进度条 | false 用于生成进度条，需要清空session
     */
    public function __construct($name, $readonly = false) {
        $this->name = $name;
        if (!$readonly) {
            //$_SESSION['loading'][$name] = array();
            CTX()->db->insert('sys_mq', array(
                'mq_session' => session_id(),
                'mq_key' => 'loading-' . $name,
                'mq_context' => serialize(array()),
            ));
        }
        $this->clear_timeout();
    }

    /**
     * 向进度条数组中推入一条数据
     * @author jhua.zuo <jhua.zuo@baisonmail.com>
     * @date 2013-11-26 
     * @param int $now 当前记录条
     * @param int $total 待处理记录总数
     * @param string $result 当前记录处理结果
     * @param string $message 处理消息
     * @param float $discount 当前进度条在总进度条的占比(如果有总进度条的话) 
     */
    public function push($now, $total, $result = true, $message = '', $discount = 1) {
        $per = 0;
        if ($total != 0 && $now != 0) {
            $per = (1 - $discount) + ($now / $total) * $discount;
        }
        $array = array(
            'now' => $now,
            'total' => $total,
            'result' => $result,
            'message' => $message,
            'discount' => $discount,
            'per' => $per,
        );
        //$_SESSION['loading'][$this->name][] = $array;
        //立即保存session，不必等到php脚本执行结束
        //session_write_close();
        //session_start();
        //mq_session='" . session_id() . "' and
        $result = CTX()->db->get_row("select * from sys_mq where  mq_key='" . 'loading-' . $this->name . "' order by add_time desc");
        $message = unserialize($result['mq_context']);
        array_push($message, $array);
        CTX()->db->update('sys_mq', array('mq_context' => serialize($message)), " mq_key='" . 'loading-' . $this->name . "'");
        return $this;
    }

	/**
	 * 更新内容消息
	 * @param null $message
	 */
	public function update_content_message($message = array(), $index = 0) {

		$result = CTX()->db->get_row("select * from sys_mq where  mq_key='" . 'loading-' . $this->name . "' order by add_time desc");
		$content = unserialize($result['mq_context']);

		array_push($content[$index]['message'], $message);

		$_content = $content;

		CTX()->db->update('sys_mq', array('mq_context' => serialize($_content)), " mq_key='" . 'loading-' . $this->name . "'");
		return $this;
	}

	/**
	 * @param $now
	 * @param $index
	 * @return $this
	 */
	function update_content_now($now, $index = 0) {
		$result = CTX()->db->get_row("select * from sys_mq where  mq_key='" . 'loading-' . $this->name . "' order by add_time desc");
		$content = unserialize($result['mq_context']);

		$content[$index]['now'] = $now;

		$_content = $content;

		CTX()->db->update('sys_mq', array('mq_context' => serialize($_content)), " mq_key='" . 'loading-' . $this->name . "'");
		return $this;
	}

    /**
     * 进度条正常完成
     */
    public function done() {
        $this->push(1, 1, 1, 'done', 0);
    }

    /**
     * 获取指定记录ID到当前的增长记录
     * @param int $id 上次的记录key
     * @return mix 成功返回记录数组和当前数组最后一条的key, 失败返回false, 格式如下
     * array(
     *      'now'=>$now,
     *      'total'=>$total,
     *      'result'=>$result,
     *      'message'=>$message,
     *      'discount'=>$discount,
     *      'per'=>$per,
     * )
     */
    public function get_grow($id = 0) {
        //mq_session='" . session_id() . "' and
        $result = CTX()->db->get_row("select * from sys_mq where  mq_key='" . 'loading-' . $this->name . "' order by add_time desc");
        if(!isset($result['mq_context'])){
            return array('data' => array(), 'maxid' => 0, 'done' => false);
        }
        $message = unserialize($result['mq_context']);
        //全部记录长度
        //$lenth = count($_SESSION['loading'][$this->name]);
        //$load = empty($_SESSION['loading'][$this->name]) ? array() : $_SESSION['loading'][$this->name];
        //$data = array_slice($load, $id);
        
        $lenth = count($message);
        if($lenth <=0 ){
            return array('data' => array(), 'maxid' => 0, 'done' => false);
        }         
        $load = empty($message) ? array() : $message;
        $data = array_slice($load, $id);
      
        //是否已经全部完成
        $done = false;
        if ('done' == $message[$lenth - 1]['message']) {
            $done = true;
        }
        if (($id == $lenth) && !$done) {
            //无增长 但未完成
            return array('data' => array(), 'maxid' => 0, 'done' => false);
        } else {
            return array('data' => $data, 'maxid' => $lenth, 'done' => $done);
        }
    }

    /**
     * 清空当前进度条
     * @return type
     */
    public function clear() {
        //$_SESSION['loading'][$this->name] = NULL;
        //CTX()->db->update('sys_mq', array('sys_mq' => serialize(array()))," );
        //mq_session='" . session_id() . "' and
        CTX()->db->query("delete from sys_mq where mq_key='" . 'loading-' . $this->name . "'");
        return;
    }

	/**
	 * 清除指定key
	 * @param $key
	 */
	public function clear_by_key($key) {
		CTX()->db->query("delete from sys_mq where mq_key='" . 'loading-' . $key . "'");
		return;
	}
    
    /**
     * 清除临时表中超时的数据
     */
    private function clear_timeout(){
        //清除掉1天前的数据
        CTX()->db->query("delete from sys_mq where add_time <'".date('Y-m-d h:i:s',time()-86400)."'");
    }

}
