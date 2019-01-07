<?php

require_model('tb/TbModel');
require_lib('apiclient/TaobaoClient');

/**
 * 商品发布
 */
class TbItemModel extends TbModel {

    private $shop_code = '';
    protected $client;

    function __construct($shop_code) {
        parent::__construct();
        $this->shop_code = $shop_code;
        $this->client = new TaobaoClient($shop_code);
    }

    /**
     * 商品发布
     */
    function add_item($param) {
        $path = $this->add_item_cat_xml_path($param['category_id'], $param['name']);
        $this->save_xml($path, $param['xml']);
        $api_param = array(
            'category_id' => $param['category_id'],
            'xml_data' => $param['xml'],
        );

        return $this->client->taobaoItemSchemaAdd($api_param);
    }

    /**
     * 获取添加规则
     */
    function get_add_schema($param) {
        $xml_path = $this->get_add_item_cat_xml_path($param['category_id']);
        $xml = $this->get_xml($xml_path);
        if ($xml === false) {
            $xml = $this->client->taobaoItemAddSchemaGet($param);
            if (!empty($xml)) {
                $this->save_xml($xml_path, $xml);
            }
        }
        return $xml;
    }

    /**
     * 获取商品编辑规则
     */
    function get_update_schema($param) {
        $xml = $this->client->taobaoItemUpdateSchemaGet($param);
        $path = $this->get_edit_item_cat_xml_path($param['category_id']);
        $this->save_xml($path, $xml);
        $xml = $this->get_xml($path);

        return $xml;
    }

    /**
     * 商品编辑数据上传
     */
    function save_update_schema($param) {
        $path = $this->edit_item_cat_xml_path($param['category_id'], $param['name']);
        $this->save_xml($path, $param['xml']);
        $api_param = array(
            'category_id' => $param['category_id'],
            'xml_data' => $param['xml'],
            'item_id' => $param['item_id'],
        );

        return $this->client->taobaoItemSchemaUpdate($api_param);
    }

    /**
     * 生成发布规则保存路径
     */
    private function get_add_item_cat_xml_path($cid) {
        $path = ROOT_PATH . CTX()->app_name . "/" . "data/tb_item/tb_item_add/";
        mkdir($path, 0777, true);
        $path.="/" . $cid . ".xml";
        return $path;
    }

    /**
     * 生成发布数据保存路径
     */
    private function add_item_cat_xml_path($cid, $name) {
        $path = ROOT_PATH . CTX()->app_name . "/" . "data/tb_item/tb_item_add_data/";
        mkdir($path, 0777, true);
        $path.="/" . $cid . "_{$name}.xml";
        return $path;
    }

    /**
     * 生成编辑规则保存路径
     */
    private function get_edit_item_cat_xml_path($cid) {
        $path = ROOT_PATH . CTX()->app_name . "/" . "data/tb_item/tb_item_edit/";
        mkdir($path, 0777, true);
        $path.="/" . $cid . ".xml";
        return $path;
    }

    /**
     * 生成编辑数据保存路径
     */
    private function edit_item_cat_xml_path($cid, $name) {
        $path = ROOT_PATH . CTX()->app_name . "/" . "data/tb_item/tb_item_edit_data/";
        mkdir($path, 0777, true);
        $path.="/" . $cid . "_{$name}.xml";
        return $path;
    }

    /**
     * 写入xml数据
     */
    function save_xml($path, $xml) {
        return file_put_contents($path, $xml, LOCK_EX);
    }

    /**
     * 读取xml数据
     */
    function get_xml($path) {
        return file_get_contents($path);
    }

    /**
     * 获取商品类目
     */
    function get_itemcats($param) {
        $data = $this->client->getItemCats($param);
        $itemcats = array();
        if (isset($data['itemcats_get_response']['item_cats']['item_cat'])) {
            $itemcats = $data['itemcats_get_response']['item_cats']['item_cat'];
        }
        return $itemcats;
    }

    /**
     * 获取增量更新规则
     */
    function get_increment_update_schema($param) {
        return $this->client->taobaoItemIncrementUpdateSchemaGet($param);
    }

    /**
     *  增量更新商品
     */
    function update_increment_item($param) {
        return $this->client->taobaoItemSchemaIncrementUpdate($param);
    }

    /**
     * 获取图片
     */
    function get_pictures($param) {
        $data = $this->client->taobaoPicturePicturesGet($param);
        $arr = array();
        if (isset($data['pictures']['picture'])) {
            foreach ($data['pictures']['picture'] as $key => $val) {
                $arr[$key]['picture_path'] = $val['picture_path'];
                $arr[$key]['title'] = $val['title'];
            }
        }
        return $arr;
    }

    /**
     * 获取图片总数
     */
    function get_pictures_count($param) {
        $data = $this->client->taobaoPicturePicturesCount($param);
        $arr = array();
        if (isset($data['totals'])) {
            $arr['pic_count'] = $data['totals'];
        }
        return $arr;
    }

    /**
     * 获取运费模板
     */
    function get_postage_template($params) {
        $data = $this->client->taobaoDeliveryTemplatesGet($params);
        return $data;
    }

}
