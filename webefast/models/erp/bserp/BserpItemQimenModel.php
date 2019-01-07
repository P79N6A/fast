<?php

require_model('erp/bserp/BserpQimenBaseModel');

class BserpItemQimenModel extends BserpQimenBaseModel {

//$tb_key
    private $type_code = 'erp_item_time';
    function __construct($erp_config_id) {
        parent::__construct();
        $this->get_erp_config($erp_config_id);

        
    }
/**
 * 基础信息同步，OMS从ERP获取商品档案
 * @return type
 */
    function erp_item_get() {
        $this->create_client();
        //调用接口下载数据
        //todo $api_param
        $time = time();
        $api_param = array();
        $api_param['page'] = 1;
        $api_param['pageSize'] = 100;
        $this->get_download_time();
        while (1) {
            $api_data = $this->erp_client->get_item($api_param);
            if($api_data['status'] < 0){
                return $api_data;
            }
            $this->save_item_list($api_data);
            if($api_data['data']['total'] < 100){//总数100条跳出循环
                  break; 
            }
            $api_param['page'] ++;
        }
        $this->save_download_time($time);
        return $this->format_ret(1);
    }

    /**
     * 保存商品用新表 api_erp_item
     * @param type $api_data
     */
    function save_item_list($api_data) {
        $arr_list = array();
        if(!empty($api_data['data']['item']['item']) && count($api_data['data']['item']['item']) > 0){
            foreach ($api_data['data']['item']['item'] as $item) {
                $new_item = $this->save_item($item);
                $arr_list[] = $new_item;
            }
            $this->insert_multi_exp('api_erp_item', $arr_list, true);
        }
    }
    /**
     * 处理保存商品信息
     * @param type $item
     */
    function save_item($item) {
        $item_arr = array(
            'supplier_code' => empty($item['supplierCode']) ? '' : $item['supplierCode'],//供应商编号
            'supplier_name' => empty($item['supplierName']) ? '' : $item['supplierName'],//供应商名称
            'item_code' => empty($item['itemCode']) ? '' : $item['itemCode'],//商品编码
            'item_id' => empty($item['itemId']) ? '' : $item['itemId'],//商品id
             'item_name' => empty($item['itemName']) ? '' : $item['itemName'],//商品名
             'short_name' => empty($item['shortName']) ? '' : $item['shortName'],//商品简称
            'english_name' => empty($item['englishName']) ? '' : $item['englishName'],//英文名
            'bar_code' => empty($item['barCode']) ? '' : $item['barCode'],//条形码{可多个用分号（;）隔开}
            'sku_property' => empty($item['skuProperty']) ? '' : $item['skuProperty'],//商品属性 (如红色XXL)
            'stock_unit' => empty($item['stockUnit']) ? '' : $item['stockUnit'],//商品计量单位
            'length' => empty($item['length']) ? '' : $item['length'],//长 (厘米)
            'width' => empty($item['width']) ? '' : $item['width'],//宽 (厘米)
            'height' => empty($item['height']) ? '' : $item['height'],//高 (厘米)
            'volume' => empty($item['volume']) ? '' : $item['volume'],//体积 (升)
            'gross_weight' => empty($item['grossWeight']) ? '' : $item['grossWeight'],//毛重 (千克)
            'net_weight' => empty($item['netWeight']) ? '' : $item['netWeight'],//净重 (千克)
            'color' => empty($item['color']) ? '' : $item['color'],//颜色
             'size' => empty($item['size']) ? '' : $item['size'],//尺寸
            'title' => empty($item['title']) ? '' : $item['title'],//渠道中的商品标题
            'category_id' => empty($item['categoryId']) ? '' : $item['categoryId'],//商品类别ID
            'category_name' => empty($item['categoryName']) ? '' : $item['categoryName'],//商品类别名称
            'pricing_category' => empty($item['pricingCategory']) ? '' : $item['pricingCategory'],//计价货类
            'safety_stock' => empty($item['safetyStock']) ? 0 : $item['safetyStock'],//安全库存
            'item_type' => empty($item['itemType']) ? '' : $item['itemType'],//商品类型 (ZC=正常商品)
            'tag_price' => empty($item['tagPrice']) ? 0 : (float)$item['tagPrice'],//吊牌价
            'retail_price' => empty($item['retailPrice']) ? 0 : (float)$item['retailPrice'],//零售价
            'cost_price' => empty($item['costPrice']) ? 0 : (float)$item['costPrice'],//成本价
            'purchase_price' => empty($item['purchasePrice']) ? 0 : (float)$item['purchasePrice'],//采购价
            'season_code' => empty($item['seasonCode']) ? '' : $item['seasonCode'],//季节编码
            'season_name' => empty($item['seasonName']) ? '' : $item['seasonName'],//季节名称
            'brand_code' => empty($item['brandCode']) ? '' : $item['brandCode'],//品牌代码
            'brand_name' => empty($item['brandName']) ? '' : $item['brandName'],//品牌名称
            'is_snmgmt' => $item['$item'] == 'N' ? 0 : 1,//是否需要串号管理{Y/N (默认为N)}
            'product_date' => $item['productDate'],//生产日期
            'expire_date' => $item['expireDate'],//过期日期
            'is_shelf_life_mgmt' => $item['isShelfLifeMgmt'] == 'N' ? 0 : 1,//是否需要保质期管理{Y/N (默认为N)}
            'shelf_life' => empty($item['shelfLife']) ? 0 : $item['shelfLife'],//保质期 (小时)
            'reject_lifecycle' => empty($item['rejectLifecycle']) ? 0 : $item['rejectLifecycle'],//保质期禁收天数
            'lockup_lifecycle' => empty($item['lockupLifecycle']) ? 0 : $item['lockupLifecycle'],//保质期禁售天数
            'advent_lifecycle' => empty($item['adventLifecycle']) ? 0 : $item['adventLifecycle'],//保质期禁售天数
            'batch_code' => empty($item['batchCode']) ? '' : $item['batchCode'],//批次代码
            'batch_remark' => empty($item['batchRemark']) ? '' : $item['batchRemark'],//批次备注
            'pack_code' => empty($item['packCode']) ? '' : $item['packCode'],//包装代码
            'pcs' => empty($item['pcs']) ? '' : $item['pcs'],//箱规
            'origin_address' => empty($item['originAddress']) ? '' : $item['originAddress'],//商品的原产地
            'approval_number' => empty($item['approvalNumber']) ? '' : $item['approvalNumber'],//批准文号
            'is_fragile' => $item['isFragile'] == 'N' ? 0 : 1,//是否易碎品 {Y/N (默认为N)}
            'is_hazardous' => $item['isHazardous'] == 'N' ? 0 : 1,//是否危险品{Y/N (默认为N)}
            'remark' => empty($item['remark']) ? '' : $item['remark'],//备注
            'create_time' => $item['createTime'],//创建时间
            'update_time' => $item['updateTime'],//更新时间
            'is_valid' => $item['isValid'] == 'N' ? 0 : 1,//是否有效{Y/N (默认为Y)}
            'is_sku' => $item['isSku'] == 'N' ? 0 : 1,//是否sku bool {Y/N (默认为Y)}
            'package_material' => empty($item['packageMaterial']) ? '' : $item['packageMaterial'],//商品包装材料类型
            'extend_props' => empty($item['extendProps']) ? '' : $item['extendProps'],//扩展属性
            'down_time'=>date('Y-m-d H:i:s'),//下载时间
        );
        return $item_arr;
    }

    function get_download_time() {

        $time = $this->get_record_time($this->type_code);
        return empty($time) ? date('Y-m-d H:i:s', $time - 300) : null;
    }

    function save_download_time($time) {
        $this->save_record_time($this->type_code, $time);
    }
}
