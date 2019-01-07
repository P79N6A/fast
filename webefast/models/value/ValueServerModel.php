<?php

/**
 * 增值服务相关业务
 * @author dfr
 *
 */
require_model('tb/TbModel');

class ValueServerModel extends TbModel {


    /**服务订购列表
     * @param $filter
     * @return array
     */
    function get_value_by_page($filter) {
        $filter['kh_id']=CTX()->saas->get_saas_key();
        //从运营平台查取数据
        $ret = load_model('sys/sysServerModel')->osp_server('value.server.list', array($filter));
        return $ret;
    }

    /**购物车添加数据
     * @param $request
     * @return mixed
     */
    function add_shopping_cart($request) {
        $params = get_array_vars($request, array(
            'value_id',
            'num',
            'kh_id',
            'user_code',
        ));
        //向运营平台添加购物车数据
        $ret = load_model('sys/sysServerModel')->osp_server('add.shopping.cart', array($params));
        return $ret;
    }

    /**立即订购
     * @param $request
     * @return mixed
     */
    function add_server_order($request) {
        $params = get_array_vars($request, array(
            'value_id',
            'kh_id',
            'user_code'
        ));
        $params['get_url']=$this->get_url();
        //向运营平台添加立即订购
        $ret = load_model('sys/sysServerModel')->osp_server('add.server.order', array($params));
        return $ret;
    }

    /**获取订购服务订单
     * @param $filter
     * @return mixed
     */
    function get_server_order_by_page($filter) {
        //从运营平台查取数据
        $ret = load_model('sys/sysServerModel')->osp_server('server.order.list', array($filter));
        return $ret;
    }

    /**支付宝充值
     * @param $params
     * @return array  跳转的url
     */
    function order_ali_pay($params) {
        $params['get_url']=$this->get_url();
        $data = $this->alipay_recharge($params);
        return $data;
    }

    /**具体支付处理
     * @param $data
     * @return array|void
     */
    function alipay_recharge($filter) {
        $ret = load_model('sys/sysServerModel')->osp_server('server.order.ali.pay', array($filter));
        return $ret;
    }

    //获取当前URL路径
    function get_url() {
        $i = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $w = strstr($i, '/web/', true);
        $str = 'http://' . $w . '/web/';
        return $str;
    }

    /**更新支付状态
     * @param $filter
     * @return array
     */
    function handle_info($filter) {
        $ret = load_model('sys/sysServerModel')->osp_server('server_pay.handle.info', array($filter));
        return $ret;
    }

    /**获取购物车数据
     * @param $filter
     * @return mixed
     */
    function get_shopping_cart($filter){
        $ret = load_model('sys/sysServerModel')->osp_server('get.shopping.cart', array($filter));
        return $ret;
    }

    /**立即支付
     * @param $filter
     * @return mixed
     */
    function immediate_pay($filter){
        $filter['get_url']=$this->get_url();
        $ret = load_model('sys/sysServerModel')->osp_server('shopping.immediate.pay', array($filter));
        return $ret;
    }

    /**购物车删除
     * @param $filter
     * @return mixed
     */
    function delete_shopping_cart($filter){
        $ret = load_model('sys/sysServerModel')->osp_server('delete.shopping.cart', array($filter));
        return $ret;
    }

    /**获取客户名称
     * @return bool|mixed
     */
    function get_kh_name(){
        $sql = "select value from sys_auth where code = 'company_name'";
        $kh_name=$this->db->get_value($sql);
        return $kh_name;
    }

    /**验证支付是否成功
     * @param $filter
     * @return mixed
     */
    function check_pay_status($filter){
        $ret = load_model('sys/sysServerModel')->osp_server('check.pay.status', array($filter));
        return $ret;
    }

    function add_order_remark($filter){
        $ret = load_model('sys/sysServerModel')->osp_server('add.order.remark', array($filter));
        return $ret;
    }

    /**获取客户id
     * @return bool|mixed
     */
    function get_kh_id(){
        $sql = "select value from sys_auth where code = 'kh_id'";
        $kh_id=$this->db->get_value($sql);
        return $kh_id;
    }
    
        /**获取订单明细
     * @param $order_code
     * @return mixed
     */
    public function get_detail_list_by_order_code($order_code) {
        $params = array(
            'order_code'=>$order_code,
            'kh_id'=>CTX()->saas->get_saas_key(),
        );
        $ret = load_model('sys/sysServerModel')->osp_server('get.order.detail', array($params));
        return $ret;
    }
    
        /**
     * 删除订单
     * @param type $id
     * @return type
     */
    public function do_order_delete($id){
        $params = array(
            'id'=>$id,
        );
        $ret = load_model('sys/sysServerModel')->osp_server('do.order.delete', array($params));
        return $ret;
    }
    
    /**
     * 获取订单详情
     * @param type $paramsview
     * @return type
     */
    function get_order_info($params) {
        $ret = load_model('sys/sysServerModel')->osp_server('get.order.info', array($params));
        return $ret;
    }
    
    /**
     * 获取订单明细
     * @param type $params
     * @return type
     */
        function get_order_detail_info($params) {
        $ret = load_model('sys/sysServerModel')->osp_server('get.order_detail.info', array($params));
        return $ret;
    }
    
    /**
     * 删除订单明细
     * @param type $params
     * @return type
     */
    function do_delete_order_detail($params) {
        $ret = load_model('sys/sysServerModel')->osp_server('do.order_detail.delete', array($params));
        return $ret;
    }
    
    /**
     * 显示前端操作日志
     */
    function getLogByPage($filter) {
        $ret = load_model('sys/sysServerModel')->osp_server('get.log.by_page', array($filter));
        return $ret;
    }
    
    /**
     * 前端增加订单明细
     * @param type $filter
     * @return type
     */
        function add_deatil_action($filter) {
        $ret = load_model('sys/sysServerModel')->osp_server('add.detail.action', array($filter));
        return $ret;
    }

    /**
     * 增值选择
     * @param type $filter
     * @return type
     */
    function get_service_goods($filter){
      $ret = load_model('sys/sysServerModel')->osp_server('get.service.goods', array($filter));   
      return $ret;
    }
    
    /**
     * 查询客户已订购服务
     * @param type $filter
     * @return type
     */
    function get_kh_server_by_page($filter) {
        $ret = load_model('sys/sysServerModel')->osp_server('get.kh.server', array($filter));
        return $ret;
    }

    /**
     * 续费
     * @param type $filter
     * @return type
     */
    function renew_ali_pay($filter) {
        $filter['get_url'] = $this->get_url();
        $ret = load_model('sys/sysServerModel')->osp_server('renew.ali.pay', array($filter));
        return $ret;
    }

    /**
     * 详情编辑
     * @param type $filter
     * @return type
     */
    function edit_order_action($filter) {
        $ret = load_model('sys/sysServerModel')->osp_server('edit.order.action', array($filter));
        return $ret;
    }

}
