<?php

require_model('api/TbGoodsIssueModel');

/**
 * 淘宝商品发布业务
 * @author wmh
 */
class TbGoodsIssueOptModel extends TbGoodsIssueModel {

    //edit,add
    private $_type = '';

    /**
     * 下载更新数据
     * @param array $params 参数
     * @return array 成功状态
     */
    function down_update_data($params) {
        $this->_type = 'edit';
        $_param = $this->check_params($params);
        if ($_param['status'] != 1) {
            return $_param;
        }
        $data = $this->deal_tb_data($_param['data']);
        $ret = $this->update_down_data($data['data_group'], $_param['data']);
        return $ret;
    }

    /**
     * 下载添加数据
     * @param array $params 参数
     * @return array 成功状态
     */
    function down_add_data($params) {
        $this->_type = 'add';
        $_param = $this->check_params($params);
        if ($_param['status'] != 1) {
            return $_param;
        }
        $data = $this->deal_tb_data($_param['data']);
        return $data;
    }

    /**
     * 检查参数
     * @param array $params 参数
     * @return array 校验结果，成功返回参数数组
     */
    function check_params($params) {
        $check_fld = array('shop_code' => '店铺代码', 'goods_code' => '商品编码');
        if ($this->_type == 'edit') {
            $check_fld['item_id'] = '商品ID';
        } else {
            $check_fld['category_id'] = '类目ID';
        }
        foreach ($check_fld as $key => $val) {
            if (!array_key_exists($key, $params) || empty($params[$key])) {
                return $this->format_ret(-1, '', '请刷新页面重试');
            }
        }
        $where['shop_code'] = $params['shop_code'];
        $where['goods_code'] = $params['goods_code'];
        unset($params['shop_code'], $params['goods_code']);
        return $this->format_ret(1, array('where' => $where, 'param' => $params));
    }

    /**
     * 下载最新数据，更新到库
     * @param array $data 更新数据
     * @param array $where shop_code,goods_code
     * @return array 更新状态
     */
    private function update_down_data($data, $where) {
        //array('淘宝字段 '=> '系统字段')
        $goods_fld = array('title' => 'title', 'sub_title' => 'sub_title', 'quantity' => 'quantity', 'price' => 'price', 'descForPC' => 'desc', 'postage_template' => 'postage_template', 'startTime' => 'shelf_time', 'time' => 'timing', 'city' => 'city', 'prov' => 'prov', 'weight' => 'weight', 'cubage' => 'cubage');
//        $sku_fld = array('sku_outerId' => 'sku', 'sku_price' => 'sku_price', 'prop_1627207' => 'spec1_code', 'prop_20509' => 'spec2_code');
        try {
            $sku_arr = $data['sku'];
            unset($data['sku']);
            $goods_arr = array();
            $item_arr = array();
            //处理商品主数据
            foreach ($data as $key => $val) {
                if (strpos($key, 'prop_') === 0) {
                    $item_arr[$key] = $val;
                    continue;
                }
                if (!array_key_exists($key, $goods_fld)) {
                    continue;
                }
                if ($key == 'item_iamges') {
                    $val = json_encode($val);
                }
                $goods_arr[$goods_fld[$key]] = $val;
            }
            $goods_arr['item_prop'] = json_encode($item_arr);
            $goods_arr = array_merge($goods_arr, $where['where']);
            unset($data);

            $this->begin_trans();
            $ret = $this->update_field($goods_arr, 'goods');
            if ($ret['status'] != 1) {
                throw new Exception($ret['message']);
            }
            $sku_prop = array();
            //处理SKU属性数据
            foreach ($sku_arr as $key => $val) {
                $sku = array(
                    'num_iid' => $where['param']['item_id'],
                    'sku' => $val['sku_outerId'],
                    'spec1_code' => $val['prop_1627207'],
                    'spec2_code' => $val['prop_20509'],
                    'sku_price' => $val['sku_price'],
                    'sku_quantity' => $val['sku_quantity'],
                );
                $sku_prop[] = $sku;
            }
            $update_str = 'sku_price=VALUES(sku_price),sku_quantity=VALUES(sku_quantity)';
            $ret = $this->insert_multi_duplicate($this->sku_table, $sku_prop, $update_str);
            if ($ret['status'] != 1) {
                throw new Exception($ret['message']);
            }
            $this->write_goods_quantity($where['where']);
            $this->commit();
            return $this->format_ret(1, '', '下载更新成功');
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    /**
     * 处理从淘宝获取的添加、编辑规则数据
     * @param array $param 接口参数
     * @return array 数据
     */
    private function deal_tb_data($param) {
        if ($this->_type == 'add') {
            $cid = $param['param']['category_id'];
            //判断文件是否有效
            $ret = $this->file_is_valid($cid);
            if ($ret['status'] == 1) {
                return true;
            }
        }
        //获取规则数据
        $xml_data = $this->get_xml_schema($param);
        //XML解析
        $tb_arr = $this->get_xml_array_data($xml_data);
        //处理规则数据
        $data = load_model("api/item/TbTemplateModel")->get_data($tb_arr, $this->_type);

        if ($this->_type == 'add') {
            $this->put_file_cache($data, $cid);
            return true;
        }
        return $data;
    }

    /**
     * 获取添加或编辑规则
     * @param array $param 接口参数
     * @return array xml规则数据
     */
    private function get_xml_schema($param) {
        require_model('api/item/TbItemModel');
        $mod = new TbItemModel($param['where']['shop_code']);
        $act = $this->_type == 'edit' ? 'get_update_schema' : 'get_add_schema';
        $xml_data = $mod->$act($param['param']);
        return $xml_data;
    }

    /**
     * 获取XML转换数组数据
     * @param xml $xml_data xml数据
     * @return array 转换的数组数据
     */
    private function get_xml_array_data($xml_data) {
        require_lib('tb_xml');
        $xmlobj = new tb_xml();
        $arr = array();
        $xmlobj->xml2array($xml_data, $arr);
        return $arr;
    }

    /**
     * 判断文件缓存是否过期
     * @param string $cid 类目ID
     * @return array 过期状态
     */
    private function file_is_valid($cid) {
        $path = ROOT_PATH . CTX()->app_name . '/' . 'data/tb_item/tb_item_add/';
        $path_old = $path . $cid . '.xml';
        $path_temp = $path . $cid . '_temp';
        $time_diff = time() - filemtime($path_old);
        if (!file_exists($path_old) || !file_exists($path_temp)) {
            return $this->format_ret(-1, '', '文件不存在');
        } else if ($time_diff > 86400) {
            unlink($path_old);
            unlink($path_temp);
            return $this->format_ret(-1, '', '文件超时');
        }
        return $this->format_ret(1);
    }

    /**
     * 写入文件缓存
     * @param string $cid 类目ID
     */
    private function put_file_cache($data, $cid) {
        $path = ROOT_PATH . CTX()->app_name . '/' . 'data/tb_item/tb_item_add/' . $cid . '_temp';
        file_put_contents($path, serialize($data));
    }

    /**
     * 获取属性缓存
     * @param string $cid 类目ID
     * @param string $type 属性类型 item_prop,sell_prop
     */
    public function get_file_cache($cid, $type) {
        $path = ROOT_PATH . CTX()->app_name . '/' . 'data/tb_item/tb_item_add/' . $cid . '_temp';
        $arr = file_get_contents($path);
        $arr = unserialize($arr);
        $keys = array('item_prop' => 'item_list', 'sell_prop' => 'sku_list');
        $arr = $arr[$keys[$type]];
        $data = array();
        if ($type == 'item_prop') {
            $data['key_prop'] = array('prop_20000' => $arr['prop_20000']);
            unset($arr['prop_20000'], $arr['prop_13021751']);
            $data['no_key_prop'] = $arr;
        } else if ($type == 'sell_prop') {
            $data = $arr['spec_list'];
        }

        return $data;
    }

    /**
     * 发布宝贝
     */
    function issue_goods($param, $type) {
        $mod = new TbItemModel($param['shop_code']);
        $xml = $mod->get_add_schema(array('category_id' => $param['category_id']));

        $xmlobj = new tb_xml();
        $field = array();
        $xmlobj->xml2array($xml, $field);
        $spec = array();
        $spec1 = 'prop_1627207';
        $spec2 = 'prop_20509';
        foreach ($field['itemRule']['field'] as $val) {
            if ($val['@attributes']['name'] == 'SKU') {
                foreach ($val['fields']['field'] as $v) {
                    if ($v['@attributes']['name'] == '颜色分类') {
                        $spec1 = $v['@attributes']['id'];
                    }
                    if ($v['@attributes']['name'] == '尺码') {
                        $spec2 = $v['@attributes']['id'];
                    }
                }
                break;
            }
        }

        $fld = '`title`, `sub_title` AS subTitle, price, quantity, barcode, outer_id AS outerId, item_prop, shelf_time AS startTime, `timing` AS time, `prov`, `city`, postage_template, weight, cubage, `desc` AS descForPC, pic_url AS item_images';
        $sql = "SELECT {$fld} FROM {$this->table} WHERE shop_code=:shop_code AND goods_code=:goods_code";
        $data = $this->db->get_row($sql, array(':shop_code' => $param['shop_code'], ':goods_code' => $param['goods_code']));

        if ($data['startTime'] != 'setted') {
            unset($data['time']);
        }

        //所在地
        $data['location'] = array('city' => $data['city'], 'prov' => $data['prov']);
        unset($data['city'], $data['prov']);

        //类目属性
        $item_prop = json_decode($data['item_prop'], TRUE);
        foreach ($item_prop as $key => $val) {
            $data[$key] = $val;
        }
        unset($data['item_prop']);

        //图片
        $images = json_decode($data['item_images'], TRUE);
        foreach ($images as $key => $val) {
            $data[$key] = $val;
        }
        unset($data['item_images']);

        //sku属性
        $sql = "SELECT spec1_code AS {$spec1},spec2_code AS {$spec2},sku_price,sku_quantity,sku_outer_id AS sku_outerId,sku_barcode FROM {$this->sku_table} WHERE shop_code=:shop_code AND goods_code=:goods_code";
        $sku_data = $this->db->get_all($sql, array(':shop_code' => $param['shop_code'], ':goods_code' => $param['goods_code']));
        $data['sku'] = $sku_data;

        $arr_data = load_model('api/item/TbItemDataModel')->update_item($data, $field);
        $api_param['xml'] = $xmlobj->array2xml($arr_data, 'itemParam');
        $api_param['name'] = $data['outerId'];
        $api_param['category_id'] = $param['category_id'];

        if ($type == 'add') {
            $ret = $mod->add_item($api_param);
            if (isset($ret['status']) && $ret['status'] == '-1') {
                $update_param = array(
                    'shop_code' => $param['shop_code'],
                    'goods_code' => $param['goods_code'],
                    'issue_status' => 0,
                    'fail_reason' => $ret['message']
                );
            } else {
                $item_id = $this->_cut_string('<item_id>', '</item_id>', $ret['item_schema_add_response']['add_result']);
                $update_param = array(
                    'shop_code' => $param['shop_code'],
                    'goods_code' => $param['goods_code'],
                    'issue_status' => 1,
                    'num_iid' => $item_id,
                    'fail_reason' => ''
                );
                $this->update_field($update_param, 'sku_issue');
                $ret = array('status' => 1);
            }
            $this->update_field($update_param);
            return $ret;
        } else if ($type == 'edit') {
            $api_param['item_id'] = $param['item_id'];
            $ret = $mod->save_update_schema($api_param);
            if (isset($ret['status']) && $ret['status'] == '-1') {
                $update_param = array(
                    'shop_code' => $param['shop_code'],
                    'goods_code' => $param['goods_code'],
                    'fail_reason' => $ret['message']
                );
                $this->update_field($update_param);
                return $ret;
            } else {
                return $this->format_ret(1);
            }
        }
        return $this->format_ret(1);
    }

    /**
     * 批量发布宝贝
     */
    function batch_issue_goods($param) {
        $success = 0;
        $fail = 0;
        foreach ($param as $val) {
            $ret = $this->issue_goods($val);
            if ($ret['status'] == 1) {
                $success++;
            } else {
                $fail++;
            }
        }
        return $this->format_ret(1, '', '成功数量：' . $success . '，失败数量：' . $fail);
    }

    /**
     * 截取指定两个字符之间字符串
     * @param string $begin  开始字符串
     * @param string $end    结束字符串
     * @param string $str    需要截取的字符串
     * @return string
     */
    function _cut_string($begin, $end, $str) {
        $b = mb_strpos($str, $begin) + mb_strlen($begin);
        $e = mb_strpos($str, $end) - $b;

        return mb_substr($str, $b, $e);
    }

    /**
     * 调用接口返回数据
     */
    function get_api_data($param, $api_act) {
        $mod = new TbItemModel($param['shop_code']);
        unset($param['shop_code']);

        $act_map = array(
            'pic' => 'get_pictures',
            'pic_count' => 'get_pictures_count',
        );
        $this->_deal_empty_param($param);
        $data = $mod->$act_map[$api_act]($param);
        return $data;
    }

    function _deal_empty_param(&$param) {
        foreach ($param as $k => $v) {
            if (empty($v)) {
                unset($param[$k]);
            }
        }
    }

}
