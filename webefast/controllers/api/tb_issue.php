<?php

require_lib('util/web_util', true);
require_model('api/item/TbItemModel');

class tb_issue {

    function do_list(array &$request, array &$response, array &$app) {
        $tb_shop = load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code('taobao');
        $response['tb_shop'] = array_merge(array(array('', '全部')), $tb_shop);
    }

    function new_do_list(array &$request, array &$response, array &$app) {
        $response['tb_shop'] = load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code('taobao');
    }

    /**
     * 获取宝贝上新列表数据
     */
    function get_issue_goods(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $ret = load_model('api/TbGoodsIssueModel')->get_issue_goods_by_page($request);
        $response['rows'] = $ret['data']['data'];
        $response['results'] = $ret['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }

    /**
     * 获取已发布的宝贝列表数据
     */
    function get_goods_list(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $ret = load_model('api/TbGoodsIssueModel')->get_by_page($request);
        $response['rows'] = $ret['data']['data'];
        $response['results'] = $ret['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }

    /**
     * 获取已发布宝贝列表SKU级信息
     */
    function get_goods_sku_list(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('api/TbGoodsIssueModel')->get_goods_sku_list($request);
    }

    function detail(array &$request, array &$response, array &$app) {
        switch ($app['scene']) {
            case 'add':
                $response['title'] = '创建宝贝';
                break;
            case 'edit':
                $response['title'] = '编辑宝贝';
                break;
            default:
                $response['title'] = '查看宝贝';
                break;
        }
        $param = get_array_vars($request, array('shop_code', 'goods_code'));
        $response['baseinfo_status'] = load_model('api/TbGoodsIssueModel')->get_status($param);
    }

    /**
     * 加载页签内容
     */
    function get_tab(array &$request, array &$response, array &$app) {
        $type = $request['type'];
        $param = get_array_vars($request, array('shop_code', 'goods_code', 'category_id'));

        require_model('api/TbGoodsIssueModel');
        $issue_mod = new TbGoodsIssueModel();
        if ($type != 'sell_prop') {
            $response[$type] = $issue_mod->get_edit_info($param, $type);
        }

        if ($type == 'baseinfo') {
            $response['baseinfo'] = json_encode($response[$type], true);
            $response['postage_template'] = $issue_mod->get_postage_template($param['shop_code']);
        } else if ($type == 'item_prop') {
            $response['item_element'] = load_model('api/TbGoodsIssueOptModel')->get_file_cache($param['category_id'], $type);
        } else if ($type == 'sell_prop') {
            $response = $issue_mod->get_goods_sku($param);
            $response['select_spec1'] = array_column($response['sku_data'], 'spec1_name', 'spec1_code');
            $response['select_spec2'] = array_column($response['sku_data'], 'spec2_name', 'spec2_code');
        } else if ($type == 'cowry_desc') {

        }

        ob_start();
        $path = get_tpl_path('api/tb_issue_' . $type);
        include $path;
        $ret = ob_get_contents();
        ob_end_clean();
        die($ret);
    }

    /**
     * 列表更新指定字段
     */
    function update_field(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $param[$request['field']] = $request['value'];
        $param['id'] = $request['id'];
        $response = load_model('api/TbGoodsIssueModel')->update_field($param, $request['type']);
    }

    /**
     * 基本信息编辑
     */
    function do_edit(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $data = get_array_vars($request, array('tab_type', 'shop_code', 'goods_code', 'category_id', 'title', 'sub_title', 'price', 'barcode', 'shelf_time', 'location', 'postage_template', 'weight', 'cubage'));
        $response = load_model('api/TbGoodsIssueModel')->update_data($data);
    }

    /**
     * 保存宝贝描述
     */
    function save_desc(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $param = get_array_vars($request, array('tab_type', 'shop_code', 'goods_code', 'desc'));
        $response = load_model('api/TbGoodsIssueModel')->update_data($param);
    }

    /**
     * 保存图片
     */
    function save_images(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $param = get_array_vars($request, array('tab_type', 'shop_code', 'goods_code', 'pic_url'));
        $pic_url = array();
        foreach ($param['pic_url'] as $key => $val) {
            $pic_url['item_image_' . $key] = $val['src'];
        }
        $param['pic_url'] = json_encode($pic_url);
        $response = load_model('api/TbGoodsIssueModel')->update_data($param);
    }

    /**
     * 保存类目属性
     */
    function save_item_prop(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('api/TbGoodsIssueModel')->save_item_prop($request);
    }

    /**
     * 保存销售属性
     */
    function save_sell_prop(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('api/TbGoodsIssueModel')->save_sell_prop($request);
    }

    /**
     * 选择所在地页面
     */
    function select_location(array &$request, array &$response, array &$app) {

    }

    /**
     * 获取选择所在地树形结构数据
     */
    function get_location(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('api/TbGoodsIssueModel')->get_location();
    }

    /**
     * 添加宝贝
     */
    function add_goods(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model("api/TbGoodsIssueModel")->add_multi_goods($request);
    }

    /**
     * 发布宝贝
     */
    function issue_goods(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model("api/TbGoodsIssueOptModel")->issue_goods($request['_param'], 'add');
    }

    /**
     * 批量发布宝贝
     */
    function batch_issue_goods(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model("api/TbGoodsIssueOptModel")->batch_issue_goods($request);
    }

    /**
     * 删除宝贝
     */
    function delete_goods(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model("api/TbGoodsIssueModel")->delete_goods($request['_param']);
    }

    /**
     * 获取宝贝类目
     */
    function get_items(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model("api/TbGoodsIssueModel")->get_items('shopping_attb');
    }

    /**
     * 选择宝贝类目页面
     */
    function select_itemcats(array &$request, array &$response, array &$app) {

    }

    /**
     * 根据类目ID获取子类目
     */
    function select_itemcats_child(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('api/TbGoodsIssueModel')->get_select_itemcats($request['id']);
    }

    /**
     * 下载更新数据
     */
    function down_update_goods(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('api/TbGoodsIssueOptModel')->down_update_data($request['_param']);
    }

    /**
     * 上传更新数据
     */
    function upload_update_goods(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model("api/TbGoodsIssueOptModel")->issue_goods($request['_param'], 'edit');
    }

    function select_img(array &$request, array &$response, array &$app) {

    }

    function get_pictures(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $param = get_array_vars($request, array('shop_code', 'current_page', 'title'));
        $param['page_size'] = 40; //默认页容量40
        $response['pictures'] = load_model('api/TbGoodsIssueOptModel')->get_api_data($param, 'pic');

        $param = get_array_vars($request, array('shop_code', 'title'));
        $ret = load_model('api/TbGoodsIssueOptModel')->get_api_data($param, 'pic_count');
        $response['pic_count'] = $ret['pic_count'];
    }

}
