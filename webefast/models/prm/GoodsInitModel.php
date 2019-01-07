<?php

/**
 * 商品初始化
 * @author dfr
 */
require_model('tb/TbModel');
require_lang('prm');
require_lib('util/oms_util', true);

class GoodsInitModel extends TbModel {

    protected $goods_table = 'api_goods';

    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_main = "FROM {$this->goods_table} r1 INNER JOIN api_goods_sku r2 ON r1.goods_from_id=r2.goods_from_id WHERE 1";
        //商店权限
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('r1.shop_code', $filter_shop_code);
        //商品编码
        if (isset($filter['platform_status']) && !empty($filter['platform_status'])) {
            if($filter['platform_status'] == '1'){
                $sql_main .= " AND r2.sys_goods_barcode='' ";
            }else{
                $sql_main .= " AND r2.sys_goods_barcode<>'' ";
            }
        }
        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
            $sql_main .= " AND r1.goods_code = :goods_code ";
            $sql_values[':goods_code'] = $filter['goods_code'];
        }
        //sku
        if (isset($filter['goods_barcode']) && $filter['goods_barcode'] !== '') {
            $sql_main .= " AND r2.goods_barcode = :goods_barcode ";
            $sql_values[':goods_barcode'] = $filter['goods_barcode'];
        }
        //商品状态
        if (isset($filter['status']) && $filter['status'] !== '') {
            $sql_main .= " AND r1.status = :status ";
            $sql_values[':status'] = $filter['status'];
        }
        //商品初始化
        if (isset($filter['is_goods_init']) && $filter['is_goods_init'] !== '') {
            $sql_main .= " AND r2.is_goods_init = :is_goods_init ";
            $sql_values[':is_goods_init'] = $filter['is_goods_init'];
        }
        //库存初始化
        if (isset($filter['is_stock_init']) && $filter['is_stock_init'] !== '') {
            $sql_main .= " AND r2.is_stock_init = :is_stock_init ";
            $sql_values[':is_stock_init'] = $filter['is_stock_init'];
        }
        //仅支持淘宝平台
        $sql_main .= " AND r2.source='taobao' AND r1.invalid_status=1 ";
        $select = "r1.goods_from_id,r1.goods_name,r1.goods_code,r1.status,r2.shop_code,r2.sku_properties_name,r2.is_goods_init,r2.is_stock_init,r2.goods_barcode,r2.sys_goods_barcode,r2.api_goods_sku_id,r2.num";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$value) {
            $value['status'] = $value['status'] == 1 ? '在售' : '在库';
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function check_goods_code($api_goods_sku_id_str) {
        $sql = "SELECT 
                    r1.goods_code,r2.api_goods_sku_id,r2.goods_from_id
                FROM 
                    api_goods r1 
                INNER JOIN 
                    api_goods_sku r2 
                ON 
                    r1.goods_from_id=r2.goods_from_id 
                WHERE
                    r2.api_goods_sku_id IN('{$api_goods_sku_id_str}')";
        $ret = $this->db->get_all($sql);
        $count = 0;
        foreach ($ret as $value) {
            if (empty($value['goods_code'])) {
                $count++;
            }
        }
        if ($count == 0) {
            return array('status' => 1, 'data' => '', 'message' => '');
        } else {
            return array('status' => -1, 'data' => $count, 'message' => '');
        }
    }
    
    /**
     * @todo 获取商品数和商品库存数
     */
    function stock_init_sum($request = NULL) {
        $sql = "SELECT SUM(num) AS num,COUNT(sku_id) as goods_type FROM api_goods_sku WHERE source='taobao'";
        if(isset($request['type']) && $request['type'] == 'batch'){
            $api_goods_sku_id = join("','", explode(',', $request['api_goods_sku_id']));
            $sql .= " AND api_goods_sku_id IN('{$api_goods_sku_id}') ";
        }
        $data = $this->db->get_row($sql);
        return array('num' => $data['num'], 'goods_type' => $data['goods_type']);
    }

    /**
     * @todo 初始化库存,通过生成盘点单来初始化
     */
    function do_stock_init($request) {
        $res = load_model("stm/TakeStockRecordModel")->insert($request);
        if (isset($res['data']) && !empty($res['data'])) {
            $this->insert_log('未确认', '创建', $res['data']);
            $record = load_model("stm/TakeStockRecordModel")->get_take_stock_by_id($res['data']);
            if ($request['type'] == 'batch') {
                //批量初始化库存
                $ret = $this->create_stock_record_detail($res['data'], $record['record_code'], $request['store_code'], $request['api_goods_sku_id'], $request['type']);
            } else {
                //一键初始化库存
                $ret = $this->create_stock_record_detail($res['data'], $record['record_code'], $request['store_code']);
            }
        }else{
             return $this->format_ret(-1,'',$res['message']);
        }
        if (isset($ret['status']) && $ret['status'] == 1) {
            $this->insert_log('未确认', '增加明细', $res['data']);
            $re = load_model("stm/TakeStockRecordModel")->update_by_id($res['data'], array("take_stock_record_id" => $res['data']), array("status" => 1, "is_sure" => 1));
        } else {
            //删除主单据
            $this->delete_action($res['data'], 1);
            return $this->format_ret(-1,'',$ret['message']);
        }
        if (isset($re['status']) && $re['status'] == 1) {
            $this->insert_log('已确认', '验收', $res['data']);
            $store_date = date('Y-m-d') . ',' . $request['store_code'];
            //1为全盘，2为部分盘点
            $type = ($request['type'] == 'all') ? '1' : '2';
            $param = array('store_date' => $store_date, 'type' => $type, 'recode_code_list' => $record['record_code']);
            $d = load_model("stm/TakeStockRecordModel")->take_stock_inv($param);
        } else {
            //删除主单据和明细
            $this->delete_action($res['data'], 2);
            return $this->format_ret(-1,'',$re['message']);
        }
        if (!isset($d['status']) || $d['status'] != 1) {
            $this->delete_action($res['data'], 2);
            return $this->format_ret(-1, '', $d['message']);
        }  else {
            if ($request['batch'] == 'batch') {
                $api_goods_sku_id_str = deal_strs_with_quote($request['api_goods_sku_id']);
                $this->update_exp('api_goods_sku', array('is_stock_init' => 1), " api_goods_sku_id IN({$api_goods_sku_id_str}) AND sys_goods_barcode<>'' ");
            }else{
                $this->update_exp('api_goods_sku', array('is_stock_init' => 1), " sys_goods_barcode<>'' ");
            }
            return $d;
        }
    }

    /**
     * @初始化库存时自动创建盘点单明细
     */
    function create_stock_record_detail($take_stock_record_id, $record_code, $store_code, $api_goods_sku_id = NULL, $type = NULL) {
        $sql = "SELECT 
                    r1.goods_code, r1.spec1_code,r1.spec1_name,r1.spec2_code,r1.spec2_name,r1.sku,r2.price,r2.num,r3.goods_name,r2.sys_goods_barcode AS barcode,'{$take_stock_record_id}' AS pid,'{$record_code}' AS record_code
                FROM 
                    goods_sku r1 
                LEFT JOIN api_goods_sku r2 ON r2.goods_barcode=r1.barcode 
                INNER JOIN api_goods r3 ON r3.goods_from_id=r2.goods_from_id
                WHERE 
                    r2.sys_goods_barcode<>''";
        //批量初始化库存
        if ($type == 'batch') {
            $api_goods_sku_id_str = join("','", $api_goods_sku_id);
            $sql .= " AND api_goods_sku_id IN('{$api_goods_sku_id_str}')";
        }
        $data = $this->db->get_all($sql);
        if(empty($data)){
            return array('status' => -1);
        }
        require_model('prm/GoodsLofModel');
        require_model('stm/GoodsInvLofRecordModel');
        $mdl_goods_lof = new GoodsLofModel();
	    $mdl_inv_lof = new GoodsInvLofRecordModel();
        //批次档案维护
        $ret = $mdl_goods_lof->add_detail_action($take_stock_record_id,$data,'take_stock');
       if ($ret['status']<1) {
            return $ret;
       }
        //单据批次添加
        $ret = $mdl_inv_lof->add_detail_action($take_stock_record_id,$store_code, 'take_stock', $data);
        $update_str = "pid = VALUES(pid),goods_code = VALUES(goods_code),spec1_code = VALUES(spec1_code),spec2_code = VALUES(spec2_code),sku = VALUES(sku),price = VALUES(price),num = VALUES(num),record_code = VALUES(record_code)";
        $ret = $this->insert_multi_duplicate('stm_take_stock_record_detail', $data, $update_str);
        return $ret;
    }

    /**
     * @todo 添加操作日志
     */
    function insert_log($finish_status, $action_name, $pid) {
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "", 'finish_status' => $finish_status, 'action_name' => $action_name, 'module' => "take_stock_record", 'pid' => $pid);
        $r = load_model('pur/PurStmLogModel')->insert($log);
    }

    /**
     * @todo 初始化失败时删除盘点单主单据或明细
     */
    function delete_action($id, $type) {
        if ($type == 1) {
            //删除主单据
            $this->load_model("stm/TakeStockRecordModel")->delete_record($id);
        } else {
            //删除主单据和明细
            $this->load_model("stm/TakeStockRecordModel")->delete_record($id);
            $this->load_model("stm/TakeStockRecordModel")->delete_detail($id);
        }
    }
    
    /**
     * @todo 用于自动服务的商品初始化
     */
    function auto_goods_init(){
        $date = date('Y-m-d H:i:s', time());
        $sql = "SELECT
                    api_goods.goods_code,
                    api_goods.cat,
                    api_goods_sku.api_goods_sku_id
                FROM
                    api_goods,
                    api_goods_sku,
                    base_shop
                WHERE
                    api_goods.goods_from_id = api_goods_sku.goods_from_id
                AND api_goods.shop_code = base_shop.shop_code
                AND api_goods.goods_code != ''
                AND (
                    api_goods_sku.goods_barcode = ''
                    OR api_goods_sku.goods_barcode IS NULL
                )
                AND api_goods.cat != ''
                AND base_shop.authorize_date >= '{$date}'
                AND base_shop.authorize_state = 1
                LIMIT 1000";
        $ret = $this->db->get_all($sql);
        if(empty($ret)){
            return $this->format_ret(-1, '', '商品初始化数据为空');
        }
        foreach ($ret as $val){
            $r = load_model('api/taobao/GoodsModel')->opt_init_create($val['api_goods_sku_id']);
        }
        $this->auto_goods_init();
    }
}
