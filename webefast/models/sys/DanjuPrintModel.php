<?php
require_model('tb/TbModel');
require_lang('sys');
require_lib ( 'comm_util', true );
require_lib('phpQuery',true);

class DanjuPrintModel extends TbModel {

    var $table = 'danju_print';
    var $pk = 'print_id';
    var $mapper;
    var $shop_table = 'danju_shop_print';
    var $shop_pk = 'shop_print_id';
    var $shop_mapper;

	//打印配置
    var $danju_print_conf = array(
        'fhd' => array(
            'title' => '发货单',
            'basic_conf' => array(
                'page_top' => array(
                    'title' => '页眉',
                    'page_title' => '发货单'
                ),
                'page_bottom' => array(
                    'title' => '页脚',
                ),
            ),
            'main_conf' => array(
                //主数据区
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '主数据区',
                    'table' => 'fhd',
                    'class' => 'MDLFhd',
                    'class_path' => 'fhd',
                    'class_function' => 'get_fhd_info_by_id',
                    'data' => array(
                        'fhd_djbh' => '发货单单据编号[条码]',
                        'buyer_nick' => '买家呢称',
                        'receiver_name' => '收货人的姓名',
                        'receiver_state' => '收货人的所在省份',
                        'receiver_city' => '收货人的所在城市',
                        'receiver_district' => '收货人的所在地区',
                        'receiver_address' => '收货人的详细地址',
                        'receiver_zip' => '收货人的邮编',
                        'receiver_mobile' => '收货人的手机号码',
                        'receiver_phone' => '收货人的电话号码',
                        'num' => '商品数',
                        'total_fee' => '商品金额',
                        'wuliu_code' => '物流公司code',
                        'wuliu_name' => '物流公司名称',
                        'created' => '创建时间',
                        'fh_remark' => '发货备注'
                    ),
                    'extra_process_data' => array(
                        'fhd_djbh' => array(
                            'type' => 'barcode'
                        ),
                    )
                ),
                //明细区
                'detail_area' => array(
                    'type' => 'list',
                    'title' => '明细区',
                    'table' => 'fhd_item_mx',
                    'class' => 'MDLFhdItemMx',
                    'class_path' => 'fhd_item_mx',
                    'class_function' => 'get_all_item_mx_by_fhd_id',
                    'data' => array(
                        'item_sn' => '商品编码',
                        'item_title' => '商品标题',
                        'wangdian_title' => '网店商品标题',
                        'price' => '商品价格',
                        'total_fee' => '商品金额',
                        'deliver_num' => '发货数量'
                    )
                ),
                'sub_main_area' => array(
                    'type' => 'grid',
                    'title' => '小结数据区',
                    'table' => 'fhd',
                    'class' => 'MDLFhd',
                    'class_path' => 'fhd',
                    'class_function' => 'get_fhd_info_by_id',
                    'data' => array(
                        'fhd_djbh' => '发货单单据编号[条码]',
                        'buyer_nick' => '买家呢称',
                        'receiver_name' => '收货人的姓名',
                        'receiver_state' => '收货人的所在省份',
                        'receiver_city' => '收货人的所在城市',
                        'receiver_district' => '收货人的所在地区',
                        'receiver_address' => '收货人的详细地址',
                        'receiver_zip' => '收货人的邮编',
                        'receiver_mobile' => '收货人的手机号码',
                        'receiver_phone' => '收货人的电话号码'
                    )
                ),
                //明细区
                'detail_area_1' => array(
                    'type' => 'list',
                    'title' => '明细区1',
                    'table' => 'fhd_item_mx',
                    'class' => 'MDLFhdItemMx',
                    'class_path' => 'fhd_item_mx',
                    'class_function' => 'get_all_item_mx_by_fhd_id',
                    'data' => array(
                        'item_sn' => '商品编码1',
                        'item_title' => '商品标题1',
                        'wangdian_title' => '网店商品标题1',
                        'price' => '商品价格1',
                    )
                )
            )
        ),
        //商品条码
        'goods_barcode' => array(
            'title' => '商品条码',
            'main_conf' => array(
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '数据区域',
                    'table' => 'goods_barcode',
                    'class' => 'MdlGoodsBarcode',
                    'class_path' => 'base/goods_barcode',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码代码',
                        'size_name' => '尺码名称',
                        'brand_code' => '品牌代码',
                        'brand_name' => '品牌名称',
                        'sort_code' => '品类代码',
                        'sort_name' => '品类名称',
                        'category_code' => '分类代码',
                        'category_name' => '分类名称',
                        'season_code' => '季节代码',
                        'season_name' => '季节名称',
                        'series_code' => '系列代码',
                        'series_name' => '系列名称',
                        'unit_code' => '单位代码',
                        'unit_name' => '单位名称',
                        'year' => '年份',
                        'property_value1' => '属性1',
                        'property_value2' => '属性2',
                        'property_value3' => '属性3',
                        'property_value4' => '属性4',
                        'property_value5' => '属性5',
                        'property_value6' => '属性6',
                        'sell_price' => '标准价',
                    ),
                    'extra_process_data' => array(
                        'barcode' => array(
                            'type' => 'barcode'
                        ),
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'size_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'size_code',
                            'function' => 'get_size_name_by_code',
                        ),
                        'brand_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'brand_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'brand_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_brand_name_by_goods_code'
                        ),
                        'sort_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'sort_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'sort_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_sort_name_by_goods_code'
                        ),
                        'category_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'category_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'category_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_category_name_by_goods_code'
                        ),
                        'season_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'season_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'season_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_season_name_by_goods_code'
                        ),
                        'series_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'series_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'series_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_series_name_by_goods_code'
                        ),
                        'unit_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'unit_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'unit_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_unit_name_by_goods_code'
                        ),
                        'year' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_year_by_goods_code',
                        ),
                        'property_value1' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value1_by_goods_id'
                        ),
                        'property_value2' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value2_by_goods_id'
                        ),
                        'property_value3' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value3_by_goods_id'
                        ),
                        'property_value4' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value4_by_goods_id'
                        ),
                        'property_value5' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value5_by_goods_id'
                        ),
                        'property_value6' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value6_by_goods_id'
                        ),
                        'sell_price' => array(
                            'type' => 'process_get_by_relation_model', //关联model获取数据
                            'function' => 'format_money',
                            'get_col' => 'sell_price',
                            'relation_model_params' => 'goods_code, color_code, size_code',
                            'relation_model_class' => 'MdlGoodsPrice',
                            'relation_model_path' => 'base/goods_price',
                            'relation_model_function' => 'get_goods_price_info_by_code'
                        ),
                    )
                ),
                'barcode_area' => array(
                    'type' => 'grid',
                    'title' => '商品条码',
                    'table' => 'goods_barcode',
                    'class' => 'MdlGoodsBarcode',
                    'class_path' => 'base/goods_barcode',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'barcode' => '商品条码[条码]',
                    ),
                    'extra_process_data' => array(
                        'barcode' => array(
                            'type' => 'barcode'
                        ),
                    )
                ),
            )
        ),
	    'goods_barcode1' => array(
		    'title' => '商品条码',
		    'main_conf' => array(
			    'main_area' => array(
				    'type' => 'grid',
				    'title' => '数据区域',
				    'table' => 'goods_barcode',
				    'class' => 'MdlGoodsBarcode',
				    'class_path' => 'base/goods_barcode',
				    'class_function' => 'get_info_by_id',
				    'data' => array(
					    'goods_code' => '商品代码',
					    'goods_name' => '商品名称',
					    'goods_short_name' => '商品简称',
					    'color_code' => '颜色代码',
					    'color_name' => '颜色名称',
					    'size_code' => '尺码代码',
					    'size_name' => '尺码名称',
					    'brand_code' => '品牌代码',
					    'brand_name' => '品牌名称',
					    'sort_code' => '品类代码',
					    'sort_name' => '品类名称',
					    'category_code' => '分类代码',
					    'category_name' => '分类名称',
					    'season_code' => '季节代码',
					    'season_name' => '季节名称',
					    'series_code' => '系列代码',
					    'series_name' => '系列名称',
					    'unit_code' => '单位代码',
					    'unit_name' => '单位名称',
					    'year' => '年份',
					    'property_value1' => '属性1',
					    'property_value2' => '属性2',
					    'property_value3' => '属性3',
					    'property_value4' => '属性4',
					    'property_value5' => '属性5',
					    'property_value6' => '属性6',
					    'sell_price' => '标准价',
				    ),
				    'extra_process_data' => array(
					    'barcode' => array(
						    'type' => 'barcode'
					    ),
					    'goods_name' => array(
						    'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
						    'relation_col' => 'goods_code', //关联字段
						    'function' => 'get_goods_name_by_code', //获取的方法
					    ),
					    'goods_short_name' => array(
						    'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
						    'relation_col' => 'goods_code', //关联字段
						    'function' => 'get_goods_short_name_by_code', //获取的方法
					    ),
					    'color_name' => array(
						    'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
						    'relation_col' => 'color_code', //关联字段
						    'function' => 'get_color_name_by_code', //获取的方法
					    ),
					    'size_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'size_code',
						    'function' => 'get_size_name_by_code',
					    ),
					    'brand_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'brand_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'brand_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_brand_name_by_goods_code'
					    ),
					    'sort_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'sort_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'sort_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_sort_name_by_goods_code'
					    ),
					    'category_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'category_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'category_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_category_name_by_goods_code'
					    ),
					    'season_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'season_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'season_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_season_name_by_goods_code'
					    ),
					    'series_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'series_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'series_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_series_name_by_goods_code'
					    ),
					    'unit_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'unit_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'unit_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_unit_name_by_goods_code'
					    ),
					    'year' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_year_by_goods_code',
					    ),
					    'property_value1' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value1_by_goods_id'
					    ),
					    'property_value2' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value2_by_goods_id'
					    ),
					    'property_value3' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value3_by_goods_id'
					    ),
					    'property_value4' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value4_by_goods_id'
					    ),
					    'property_value5' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value5_by_goods_id'
					    ),
					    'property_value6' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value6_by_goods_id'
					    ),
					    'sell_price' => array(
						    'type' => 'process_get_by_relation_model', //关联model获取数据
						    'function' => 'format_money',
						    'get_col' => 'sell_price',
						    'relation_model_params' => 'goods_code, color_code, size_code',
						    'relation_model_class' => 'MdlGoodsPrice',
						    'relation_model_path' => 'base/goods_price',
						    'relation_model_function' => 'get_goods_price_info_by_code'
					    ),
				    )
			    ),
			    'barcode_area' => array(
				    'type' => 'grid',
				    'title' => '商品条码',
				    'table' => 'goods_barcode',
				    'class' => 'MdlGoodsBarcode',
				    'class_path' => 'base/goods_barcode',
				    'class_function' => 'get_info_by_id',
				    'data' => array(
					    'barcode' => '商品条码[条码]',
				    ),
				    'extra_process_data' => array(
					    'barcode' => array(
						    'type' => 'barcode'
					    ),
				    )
			    ),
		    )
	    ),
	    'goods_barcode2' => array(
		    'title' => '商品条码',
		    'main_conf' => array(
			    'main_area' => array(
				    'type' => 'grid',
				    'title' => '数据区域',
				    'table' => 'goods_barcode',
				    'class' => 'MdlGoodsBarcode',
				    'class_path' => 'base/goods_barcode',
				    'class_function' => 'get_info_by_id',
				    'data' => array(
					    'goods_code' => '商品代码',
					    'goods_name' => '商品名称',
					    'goods_short_name' => '商品简称',
					    'color_code' => '颜色代码',
					    'color_name' => '颜色名称',
					    'size_code' => '尺码代码',
					    'size_name' => '尺码名称',
					    'brand_code' => '品牌代码',
					    'brand_name' => '品牌名称',
					    'sort_code' => '品类代码',
					    'sort_name' => '品类名称',
					    'category_code' => '分类代码',
					    'category_name' => '分类名称',
					    'season_code' => '季节代码',
					    'season_name' => '季节名称',
					    'series_code' => '系列代码',
					    'series_name' => '系列名称',
					    'unit_code' => '单位代码',
					    'unit_name' => '单位名称',
					    'year' => '年份',
					    'property_value1' => '属性1',
					    'property_value2' => '属性2',
					    'property_value3' => '属性3',
					    'property_value4' => '属性4',
					    'property_value5' => '属性5',
					    'property_value6' => '属性6',
					    'sell_price' => '标准价',
				    ),
				    'extra_process_data' => array(
					    'barcode' => array(
						    'type' => 'barcode'
					    ),
					    'goods_name' => array(
						    'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
						    'relation_col' => 'goods_code', //关联字段
						    'function' => 'get_goods_name_by_code', //获取的方法
					    ),
					    'goods_short_name' => array(
						    'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
						    'relation_col' => 'goods_code', //关联字段
						    'function' => 'get_goods_short_name_by_code', //获取的方法
					    ),
					    'color_name' => array(
						    'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
						    'relation_col' => 'color_code', //关联字段
						    'function' => 'get_color_name_by_code', //获取的方法
					    ),
					    'size_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'size_code',
						    'function' => 'get_size_name_by_code',
					    ),
					    'brand_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'brand_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'brand_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_brand_name_by_goods_code'
					    ),
					    'sort_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'sort_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'sort_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_sort_name_by_goods_code'
					    ),
					    'category_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'category_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'category_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_category_name_by_goods_code'
					    ),
					    'season_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'season_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'season_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_season_name_by_goods_code'
					    ),
					    'series_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'series_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'series_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_series_name_by_goods_code'
					    ),
					    'unit_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'unit_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'unit_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_unit_name_by_goods_code'
					    ),
					    'year' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_year_by_goods_code',
					    ),
					    'property_value1' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value1_by_goods_id'
					    ),
					    'property_value2' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value2_by_goods_id'
					    ),
					    'property_value3' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value3_by_goods_id'
					    ),
					    'property_value4' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value4_by_goods_id'
					    ),
					    'property_value5' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value5_by_goods_id'
					    ),
					    'property_value6' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value6_by_goods_id'
					    ),
					    'sell_price' => array(
						    'type' => 'process_get_by_relation_model', //关联model获取数据
						    'function' => 'format_money',
						    'get_col' => 'sell_price',
						    'relation_model_params' => 'goods_code, color_code, size_code',
						    'relation_model_class' => 'MdlGoodsPrice',
						    'relation_model_path' => 'base/goods_price',
						    'relation_model_function' => 'get_goods_price_info_by_code'
					    ),
				    )
			    ),
			    'barcode_area' => array(
				    'type' => 'grid',
				    'title' => '商品条码',
				    'table' => 'goods_barcode',
				    'class' => 'MdlGoodsBarcode',
				    'class_path' => 'base/goods_barcode',
				    'class_function' => 'get_info_by_id',
				    'data' => array(
					    'barcode' => '商品条码[条码]',
				    ),
				    'extra_process_data' => array(
					    'barcode' => array(
						    'type' => 'barcode'
					    ),
				    )
			    ),
		    )
	    ),
        //商品吊牌
        'goods_barcode_hangtag' => array(
            'title' => '商品吊牌',
            'main_conf' => array(
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '商品吊牌',
                    'table' => 'goods_barcode',
                    'class' => 'MdlGoodsBarcode',
                    'class_path' => 'base/goods_barcode',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码代码',
                        'size_name' => '尺码名称',
                        'sell_price' => '标准价',
                        'label1' => '吊牌标签1',
                        'label2' => '吊牌标签2',
                        'label3' => '吊牌标签3',
                        'label4' => '吊牌标签4',
                        'label5' => '吊牌标签5',
                        'label6' => '吊牌标签6',
                        'label7' => '吊牌标签7',
                        'label8' => '吊牌标签8',
                        'label9' => '吊牌标签9',
                        'label10' => '吊牌标签10',
                        'brand_code' => '品牌代码',
                        'brand_name' => '品牌名称',
                        'sort_code' => '品类代码',
                        'sort_name' => '品类名称',
                        'category_code' => '分类代码',
                        'category_name' => '分类名称',
                        'season_code' => '季节代码',
                        'season_name' => '季节名称',
                        'series_code' => '系列代码',
                        'series_name' => '系列名称',
                        'unit_code' => '单位代码',
                        'unit_name' => '单位名称',
                        'year' => '年份',
                        'property_value1' => '属性1',
                        'property_value2' => '属性2',
                        'property_value3' => '属性3',
                        'property_value4' => '属性4',
                        'property_value5' => '属性5',
                        'property_value6' => '属性6',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'size_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'size_code',
                            'function' => 'get_size_name_by_code',
                        ),
                        'sell_price' => array(
                            'type' => 'process_get_by_relation_model', //关联model获取数据
                            'function' => 'format_money',
                            'get_col' => 'sell_price',
                            'relation_model_params' => 'goods_code, color_code, size_code',
                            'relation_model_class' => 'MdlGoodsPrice',
                            'relation_model_path' => 'base/goods_price',
                            'relation_model_function' => 'get_goods_price_info_by_code'
                        ),
                        'label1' => array(
                            'type' => 'process_get_info_by_relation_params', //关联获取数据,有多个参数
                            'get_col' => 'label1',
                            'relation_params' => 'goods_code', //关联字段
                            'function' => 'get_hangtag_by_goods_code'
                        ),
                        'label2' => array(
                            'type' => 'process_get_info_by_relation_params', //关联获取数据,有多个参数
                            'get_col' => 'label2',
                            'relation_params' => 'goods_code', //关联字段
                            'function' => 'get_hangtag_by_goods_code'
                        ),
                        'label3' => array(
                            'type' => 'process_get_info_by_relation_params', //关联获取数据,有多个参数
                            'get_col' => 'label3',
                            'relation_params' => 'goods_code', //关联字段
                            'function' => 'get_hangtag_by_goods_code'
                        ),
                        'label4' => array(
                            'type' => 'process_get_info_by_relation_params', //关联获取数据,有多个参数
                            'get_col' => 'label4',
                            'relation_params' => 'goods_code', //关联字段
                            'function' => 'get_hangtag_by_goods_code'
                        ),
                        'label5' => array(
                            'type' => 'process_get_info_by_relation_params', //关联获取数据,有多个参数
                            'get_col' => 'label5',
                            'relation_params' => 'goods_code', //关联字段
                            'function' => 'get_hangtag_by_goods_code'
                        ),
                        'label6' => array(
                            'type' => 'process_get_info_by_relation_params', //关联获取数据,有多个参数
                            'get_col' => 'label6',
                            'relation_params' => 'goods_code', //关联字段
                            'function' => 'get_hangtag_by_goods_code'
                        ),
                        'label7' => array(
                            'type' => 'process_get_info_by_relation_params', //关联获取数据,有多个参数
                            'get_col' => 'label7',
                            'relation_params' => 'goods_code', //关联字段
                            'function' => 'get_hangtag_by_goods_code'
                        ),
                        'label8' => array(
                            'type' => 'process_get_info_by_relation_params', //关联获取数据,有多个参数
                            'get_col' => 'label8',
                            'relation_params' => 'goods_code', //关联字段
                            'function' => 'get_hangtag_by_goods_code'
                        ),
                        'label9' => array(
                            'type' => 'process_get_info_by_relation_params', //关联获取数据,有多个参数
                            'get_col' => 'label9',
                            'relation_params' => 'goods_code', //关联字段
                            'function' => 'get_hangtag_by_goods_code'
                        ),
                        'label10' => array(
                            'type' => 'process_get_info_by_relation_params', //关联获取数据,有多个参数
                            'get_col' => 'label10',
                            'relation_params' => 'goods_code', //关联字段
                            'function' => 'get_hangtag_by_goods_code'
                        ),
                        'brand_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'brand_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'brand_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_brand_name_by_goods_code'
                        ),
                        'sort_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'sort_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'sort_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_sort_name_by_goods_code'
                        ),
                        'category_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'category_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'category_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_category_name_by_goods_code'
                        ),
                        'season_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'season_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'season_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_season_name_by_goods_code'
                        ),
                        'series_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'series_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'series_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_series_name_by_goods_code'
                        ),
                        'unit_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'unit_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'unit_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_unit_name_by_goods_code'
                        ),
                        'year' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_year_by_goods_code',
                        ),
                        'property_value1' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value1_by_goods_id'
                        ),
                        'property_value2' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value2_by_goods_id'
                        ),
                        'property_value3' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value3_by_goods_id'
                        ),
                        'property_value4' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value4_by_goods_id'
                        ),
                        'property_value5' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value5_by_goods_id'
                        ),
                        'property_value6' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value6_by_goods_id'
                        ),
                    )
                ),
                'barcode_area' => array(
                    'type' => 'grid',
                    'title' => '商品条码',
                    'table' => 'goods_barcode',
                    'class' => 'MdlGoodsBarcode',
                    'class_path' => 'base/goods_barcode',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'barcode' => '商品条码[条码]',
                    ),
                    'extra_process_data' => array(
                        'barcode' => array(
                            'type' => 'barcode'
                        ),
                    )
                ),
            )
        ),
        //颜色
        'base_color' => array(
            'title' => '颜色',
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                //明细区
                'detail_area' => array(
                    'type' => 'list',
                    'title' => '明细区',
                    'table' => 'base_color',
                    'class' => 'MdlColor',
                    'class_path' => 'base/color',
                    'class_function' => '',
                    'data' => array(
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'color_outer_code' => '外部编码',
                        'barcode' => '条码对照码',
                    )
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
            ),
        ),
        //采购单标准打印
        'pur_purchaser_record' => array(
            'title' => '采购单',
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '采购单信息',
                    'table' => 'pur_purchaser_record',
                    'class' => 'MdlPurchaserRecord',
                    'class_path' => 'pur/purchaser_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'supplier_code' => '供应商代码',
                        'supplier_name' => '供应商名称',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'rebate' => '折扣',
                        'relation_code' => '关联单号',
                        'refer_time' => '交货时间',
                        'pay_time' => '付款期限',
                        'brand_code' => '品牌代码',
                        'brand_name' => '品牌名称',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'finance_pay_money' => '应付款',
                        'finance_pay_money_up' => '应付款(大写)',
                        'finance_balance_money' => '结余款',
                        'finance_balance_money_up' => '结余款(大写)',
                        'finance_debts_money' => '累计欠款',
                        'finance_debts_money_up' => '累计欠款(大写)',
	                    'before_debts_money' => '上次欠款',
	                    'before_debts_money_up' => '上次欠款(大写)',
	                    'after_debts_money' => '当日欠款',
	                    'after_debts_money_up' => '当日欠款(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_check_person' => '审核人',
                        'is_check_time' => '审核时间',
                        'is_execute_person' => '执行人',
                        'is_execute_time' => '执行时间',
                        'is_stop_person' => '终止人',
                        'is_stop_time' => '终止时间',
                        'is_finish_person' => '完成人',
                        'is_finish_time' => '完成时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'refer_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'refer_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'brand_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'brand_code',
                            'function' => 'get_brand_name_by_code'
                        ),
                        'supplier_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'supplier_code',
                            'function' => 'get_supplier_name_by_code'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'finance_pay_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_supplier_general_ledger_by_code'
                        ),
                        'finance_pay_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_supplier_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_balance_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_supplier_general_ledger_by_code'
                        ),
                        'finance_balance_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_supplier_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_debts_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_supplier_general_ledger_by_code'
                        ),
                        'finance_debts_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_supplier_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_check_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_check_time',
                            'function' => 'format_day_time'
                        ),
                        'is_execute_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_execute_time',
                            'function' => 'format_day_time'
                        ),
                        'is_stop_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_stop_time',
                            'function' => 'format_day_time'
                        ),
                        'is_finish_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_finish_time',
                            'function' => 'format_day_time'
                        ),
	                    'before_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code'
	                    ),
	                    'before_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),
	                    'after_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code'
	                    ),
	                    'after_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),
                    ),
                ),
                //供应商信息
                'supplier_area' => array(
                    'type' => 'grid',
                    'title' => '供应商信息',
                    'table' => 'pur_purchaser_record',
                    'class' => 'MdlPurchaserRecord',
                    'class_path' => 'pur/purchaser_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'supplier_code' => '供应商代码',
                        'supplier_name' => '供应商名称',
                        'supplier_contact_person' => '供应商联系人',
                        'supplier_email' => '供应商邮箱',
                        'supplier_phone' => '供应商电话',
                        'supplier_tel' => '供应商手机',
                        'supplier_address' => '供应商地址',
                        'supplier_fax' => '供应商传真',
                        'supplier_zipcode' => '供应商邮编',
                        'supplier_website' => '供应商公司网站',
                        'supplier_bank' => '供应商开户银行',
                        'supplier_account' => '供应商账号',
                        'supplier_account_name' => '供应商账号名称',
                        'supplier_legal_person' => '供应商法人',
                    ),
                    'extra_process_data' => array(
                        'supplier_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'supplier_code',
                            'function' => 'get_supplier_name_by_code'
                        ),
                        'supplier_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'email',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'address',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'website',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'account',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_legal_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'legal_person',
                            'function' => 'get_supplier_info_by_code'
                        ),
                    ),
                ),
                //打印明细
                'detail_area' => array(
                    'type' => 'list',
                    'title' => '明细区',
                    'table' => 'pur_purchaser_record_detail',
                    'class' => 'MdlPurchaserRecord',
                    'class_path' => 'pur/purchaser_record',
                    'class_function' => 'get_detail_list_by_pid',
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码代码',
                        'size_name' => '尺码名称',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
	                    'brand_code' => '品牌代码',
	                    'brand_name' => '品牌名称',
	                    'sort_code' => '品类代码',
	                    'sort_name' => '品类名称',
	                    'category_code' => '分类代码',
	                    'category_name' => '分类名称',
	                    'season_code' => '季节代码',
	                    'season_name' => '季节名称',
	                    'series_code' => '系列代码',
	                    'series_name' => '系列名称',
	                    'unit_code' => '单位代码',
	                    'unit_name' => '单位名称',
	                    'year' => '年份',
	                    'custom_col1' => '自定义列1',
	                    'custom_col2' => '自定义列2',
	                    'custom_col3' => '自定义列3',
	                    'custom_col4' => '自定义列4',
	                    'custom_col5' => '自定义列5',
	                    'property_value1' => '属性1',
	                    'property_value2' => '属性2',
	                    'property_value3' => '属性3',
	                    'property_value4' => '属性4',
	                    'property_value5' => '属性5',
	                    'property_value6' => '属性6',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'size_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'size_code',
                            'function' => 'get_size_name_by_code',
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
	                    'brand_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'brand_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'brand_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_brand_name_by_goods_code'
	                    ),
	                    'sort_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'sort_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'sort_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_sort_name_by_goods_code'
	                    ),
	                    'category_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'category_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'category_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_category_name_by_goods_code'
	                    ),
	                    'season_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'season_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'season_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_season_name_by_goods_code'
	                    ),
	                    'series_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'series_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'series_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_series_name_by_goods_code'
	                    ),
	                    'unit_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'unit_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'unit_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_unit_name_by_goods_code'
	                    ),
	                    'year' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_year_by_goods_code',
	                    ),
	                    'property_value1' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value1_by_goods_id'
	                    ),
	                    'property_value2' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value2_by_goods_id'
	                    ),
	                    'property_value3' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value3_by_goods_id'
	                    ),
	                    'property_value4' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value4_by_goods_id'
	                    ),
	                    'property_value5' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value5_by_goods_id'
	                    ),
	                    'property_value6' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value6_by_goods_id'
	                    ),
                    ),
                ),
                //打印财务明细
                'finance_area' => array(
                    'type' => 'list',
                    'title' => '账务信息',
                    'table' => 'acc_supplier_general_journal',
                    'class' => 'MdlSupplierGeneralJournal',
                    'class_path' => 'acc/supplier_general_journal',
                    'class_function' => 'get_list_by_supplier_code',
                    'relation_table' => array(
                        'table' => 'pur_purchaser_record',
                        'class' => 'MdlPurchaserRecord',
                        'class_path' => 'pur/purchaser_record',
                        'class_function' => 'get_info_by_id',
                        'relation_col' => 'supplier_code',
                    ),
                    'data' => array(
                        'supplier_code' => '供应商代码',
                        'supplier_name' => '供应商名称',
                        'record_type' => '单据类型',
                        'relation_code' => '关联单号',
                        'before_pay_money' => '期初应付款',
                        'change_pay_money' => '变动应付款',
                        'after_pay_money' => '期末应付款',
                        'before_balance_money' => '期初余额',
                        'change_balance_money' => '变动余额',
                        'after_balance_money' => '期末余额',
                        'before_debts_money' => '期初欠款',
                        'change_debts_money' => '变动欠款',
                        'after_debts_money' => '期末欠款'
                    ),
                    'extra_process_data' => array(
                        'supplier_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'supplier_code',
                            'function' => 'get_supplier_name_by_code'
                        ),
                        'record_type' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'record_type', //关联字段
                            'function' => 'get_record_type_name', //获取的方法
                        ),
                        'before_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_pay_money',
                            'function' => 'format_money',
                        ),
                        'change_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_pay_money',
                            'function' => 'format_money',
                        ),
                        'after_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_pay_money',
                            'function' => 'format_money'
                        ),
                        'before_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_balance_money',
                            'function' => 'format_money',
                        ),
                        'change_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_balance_money',
                            'function' => 'format_money',
                        ),
                        'after_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_balance_money',
                            'function' => 'format_money'
                        ),
                        'before_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_debts_money',
                            'function' => 'format_money',
                        ),
                        'change_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_debts_money',
                            'function' => 'format_money',
                        ),
                        'after_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_debts_money',
                            'function' => 'format_money'
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'pur_purchaser_record',
                    'class' => 'MdlPurchaserRecord',
                    'class_path' => 'pur/purchaser_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_check_person' => '审核人',
                        'is_check_time' => '审核时间',
                        'is_execute_person' => '执行人',
                        'is_execute_time' => '执行时间',
                        'is_stop_person' => '终止人',
                        'is_stop_time' => '终止时间',
                        'is_finish_person' => '完成人',
                        'is_finish_time' => '完成时间'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_check_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_check_time',
                            'function' => 'format_day_time'
                        ),
                        'is_execute_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_execute_time',
                            'function' => 'format_day_time'
                        ),
                        'is_stop_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_stop_time',
                            'function' => 'format_day_time'
                        ),
                        'is_finish_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_finish_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),
        //采购单扩展打印
        'ext_pur_purchaser_record' => array(
            'title' => '采购单',
            'property' => 'record_ext', //单据明细扩展打印
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '采购单信息',
                    'table' => 'pur_purchaser_record',
                    'class' => 'MdlPurchaserRecord',
                    'class_path' => 'pur/purchaser_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'supplier_code' => '供应商代码',
                        'supplier_name' => '供应商名称',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'rebate' => '折扣',
                        'relation_code' => '关联单号',
                        'refer_time' => '交货时间',
                        'pay_time' => '付款期限',
                        'brand_code' => '品牌代码',
                        'brand_name' => '品牌名称',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'finance_pay_money' => '应付款',
                        'finance_pay_money_up' => '应付款(大写)',
                        'finance_balance_money' => '结余款',
                        'finance_balance_money_up' => '结余款(大写)',
                        'finance_debts_money' => '累计欠款',
                        'finance_debts_money_up' => '累计欠款(大写)',
	                    'before_debts_money' => '上次欠款',
	                    'before_debts_money_up' => '上次欠款(大写)',
	                    'after_debts_money' => '当日欠款',
	                    'after_debts_money_up' => '当日欠款(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_check_person' => '审核人',
                        'is_check_time' => '审核时间',
                        'is_execute_person' => '执行人',
                        'is_execute_time' => '执行时间',
                        'is_stop_person' => '终止人',
                        'is_stop_time' => '终止时间',
                        'is_finish_person' => '完成人',
                        'is_finish_time' => '完成时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'refer_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'refer_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'brand_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'brand_code',
                            'function' => 'get_brand_name_by_code'
                        ),
                        'supplier_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'supplier_code',
                            'function' => 'get_supplier_name_by_code'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'finance_pay_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_supplier_general_ledger_by_code'
                        ),
                        'finance_pay_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_supplier_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_balance_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_supplier_general_ledger_by_code'
                        ),
                        'finance_balance_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_supplier_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_debts_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_supplier_general_ledger_by_code'
                        ),
                        'finance_debts_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_supplier_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_check_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_check_time',
                            'function' => 'format_day_time'
                        ),
                        'is_execute_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_execute_time',
                            'function' => 'format_day_time'
                        ),
                        'is_stop_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_stop_time',
                            'function' => 'format_day_time'
                        ),
                        'is_finish_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_finish_time',
                            'function' => 'format_day_time'
                        ),
	                    'before_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code'
	                    ),
	                    'before_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),
	                    'after_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code'
	                    ),
	                    'after_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),
                    ),
                ),
                //供应商信息
                'supplier_area' => array(
                    'type' => 'grid',
                    'title' => '供应商信息',
                    'table' => 'pur_purchaser_record',
                    'class' => 'MdlPurchaserRecord',
                    'class_path' => 'pur/purchaser_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'supplier_code' => '供应商代码',
                        'supplier_name' => '供应商名称',
                        'supplier_contact_person' => '供应商联系人',
                        'supplier_email' => '供应商邮箱',
                        'supplier_phone' => '供应商电话',
                        'supplier_tel' => '供应商手机',
                        'supplier_address' => '供应商地址',
                        'supplier_fax' => '供应商传真',
                        'supplier_zipcode' => '供应商邮编',
                        'supplier_website' => '供应商公司网站',
                        'supplier_bank' => '供应商开户银行',
                        'supplier_account' => '供应商账号',
                        'supplier_account_name' => '供应商账号名称',
                        'supplier_legal_person' => '供应商法人',
                    ),
                    'extra_process_data' => array(
                        'supplier_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'supplier_code',
                            'function' => 'get_supplier_name_by_code'
                        ),
                        'supplier_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'email',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'address',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'website',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'account',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_legal_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'legal_person',
                            'function' => 'get_supplier_info_by_code'
                        ),
                    ),
                ),
                //扩展打印明细
                'ext_detail_area' => array(
                    'type' => 'list',
                    'title' => '扩展明细区',
                    'table' => 'pur_purchaser_record_detail',
                    'class' => 'MdlPurchaserRecord',
                    'class_path' => 'pur/purchaser_record',
                    'class_function' => 'get_detail_list_by_pid',
                    'property' => 'record_ext', //单据明细扩展打印
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码名称[扩展]',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
	                    'brand_code' => '品牌代码',
	                    'brand_name' => '品牌名称',
	                    'sort_code' => '品类代码',
	                    'sort_name' => '品类名称',
	                    'category_code' => '分类代码',
	                    'category_name' => '分类名称',
	                    'season_code' => '季节代码',
	                    'season_name' => '季节名称',
	                    'series_code' => '系列代码',
	                    'series_name' => '系列名称',
	                    'unit_code' => '单位代码',
	                    'unit_name' => '单位名称',
	                    'year' => '年份',
	                    'custom_col1' => '自定义列1',
	                    'custom_col2' => '自定义列2',
	                    'custom_col3' => '自定义列3',
	                    'custom_col4' => '自定义列4',
	                    'custom_col5' => '自定义列5',
	                    'property_value1' => '属性1',
	                    'property_value2' => '属性2',
	                    'property_value3' => '属性3',
	                    'property_value4' => '属性4',
	                    'property_value5' => '属性5',
	                    'property_value6' => '属性6',
                    ),
                    'ext_col' => 'size_code',
                    'ext_extra_process_data' => array(
                        'num' => 'plus',
                        'money' => 'plus',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
	                    'brand_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'brand_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'brand_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_brand_name_by_goods_code'
	                    ),
	                    'sort_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'sort_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'sort_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_sort_name_by_goods_code'
	                    ),
	                    'category_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'category_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'category_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_category_name_by_goods_code'
	                    ),
	                    'season_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'season_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'season_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_season_name_by_goods_code'
	                    ),
	                    'series_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'series_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'series_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_series_name_by_goods_code'
	                    ),
	                    'unit_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'unit_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'unit_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_unit_name_by_goods_code'
	                    ),
	                    'year' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_year_by_goods_code',
	                    ),
	                    'property_value1' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value1_by_goods_id'
	                    ),
	                    'property_value2' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value2_by_goods_id'
	                    ),
	                    'property_value3' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value3_by_goods_id'
	                    ),
	                    'property_value4' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value4_by_goods_id'
	                    ),
	                    'property_value5' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value5_by_goods_id'
	                    ),
	                    'property_value6' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value6_by_goods_id'
	                    ),
                    ),
                ),
                //打印财务明细
                'finance_area' => array(
                    'type' => 'list',
                    'title' => '账务信息',
                    'table' => 'acc_supplier_general_journal',
                    'class' => 'MdlSupplierGeneralJournal',
                    'class_path' => 'acc/supplier_general_journal',
                    'class_function' => 'get_list_by_supplier_code',
                    'relation_table' => array(
                        'table' => 'pur_purchaser_record',
                        'class' => 'MdlPurchaserRecord',
                        'class_path' => 'pur/purchaser_record',
                        'class_function' => 'get_info_by_id',
                        'relation_col' => 'supplier_code',
                    ),
                    'data' => array(
                        'supplier_code' => '供应商代码',
                        'supplier_name' => '供应商名称',
                        'record_type' => '单据类型',
                        'relation_code' => '关联单号',
                        'before_pay_money' => '期初应付款',
                        'change_pay_money' => '变动应付款',
                        'after_pay_money' => '期末应付款',
                        'before_balance_money' => '期初余额',
                        'change_balance_money' => '变动余额',
                        'after_balance_money' => '期末余额',
                        'before_debts_money' => '期初欠款',
                        'change_debts_money' => '变动欠款',
                        'after_debts_money' => '期末欠款'
                    ),
                    'extra_process_data' => array(
                        'supplier_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'supplier_code',
                            'function' => 'get_supplier_name_by_code'
                        ),
                        'record_type' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'record_type', //关联字段
                            'function' => 'get_record_type_name', //获取的方法
                        ),
                        'before_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_pay_money',
                            'function' => 'format_money',
                        ),
                        'change_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_pay_money',
                            'function' => 'format_money',
                        ),
                        'after_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_pay_money',
                            'function' => 'format_money'
                        ),
                        'before_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_balance_money',
                            'function' => 'format_money',
                        ),
                        'change_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_balance_money',
                            'function' => 'format_money',
                        ),
                        'after_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_balance_money',
                            'function' => 'format_money'
                        ),
                        'before_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_debts_money',
                            'function' => 'format_money',
                        ),
                        'change_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_debts_money',
                            'function' => 'format_money',
                        ),
                        'after_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_debts_money',
                            'function' => 'format_money'
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'pur_purchaser_record',
                    'class' => 'MdlPurchaserRecord',
                    'class_path' => 'pur/purchaser_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_check_person' => '审核人',
                        'is_check_time' => '审核时间',
                        'is_execute_person' => '执行人',
                        'is_execute_time' => '执行时间',
                        'is_stop_person' => '终止人',
                        'is_stop_time' => '终止时间',
                        'is_finish_person' => '完成人',
                        'is_finish_time' => '完成时间'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_check_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_check_time',
                            'function' => 'format_day_time'
                        ),
                        'is_execute_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_execute_time',
                            'function' => 'format_day_time'
                        ),
                        'is_stop_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_stop_time',
                            'function' => 'format_day_time'
                        ),
                        'is_finish_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_finish_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),
        //采购商品条码(特殊)
        'pur_purchaser_record_barcode' => array(
            'title' => '采购订单条码',
            'main_conf' => array(
//				'page_top_area' => array(
//					'type'=>'grid',
//					'title'=>'页眉',
//					'table'=>'',
//					'class'=>'',
//					'class_path'=>'',
//					'class_function'=>'',
//					'data' => array(
//						'time'=>'时间',
//						'logo' => '公司logo'
//					),
//					'extra_process_data' => array(
//						'time'=>array(
//							'type'=>'time',
//							'function'=>'add_time'
//						),
//						'logo' => array(
//							'type' => 'logo',
//						)
//					)
//				),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '采购订单条码',
                    'table' => 'pur_purchaser_record_detail',
                    'class' => 'MdlPurchaserRecord',
                    'class_path' => 'pur/purchaser_record',
                    'class_function' => 'get_detail_list_by_pid',
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码代码',
                        'size_name' => '尺码名称',
                        'brand_code' => '品牌代码',
                        'brand_name' => '品牌名称',
                        'sort_code' => '品类代码',
                        'sort_name' => '品类名称',
                        'category_code' => '分类代码',
                        'category_name' => '分类名称',
                        'season_code' => '季节代码',
                        'season_name' => '季节名称',
                        'series_code' => '系列代码',
                        'series_name' => '系列名称',
                        'unit_code' => '单位代码',
                        'unit_name' => '单位名称',
                        'year' => '年份',
                        'property_value1' => '属性1',
                        'property_value2' => '属性2',
                        'property_value3' => '属性3',
                        'property_value4' => '属性4',
                        'property_value5' => '属性5',
                        'property_value6' => '属性6',
                        'sell_price' => '标准价',
                    //	'barcode' => '商品条码[条码]',
                    ),
                    'extra_process_data' => array(
//						'barcode' => array(
//							'type' => 'barcode',
//						),
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_goods_name_by_code'
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'color_code',
                            'function' => 'get_color_name_by_code'
                        ),
                        'size_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'size_code',
                            'function' => 'get_size_name_by_code'
                        ),
                        'brand_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'brand_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'brand_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_brand_name_by_goods_code'
                        ),
                        'sort_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'sort_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'sort_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_sort_name_by_goods_code'
                        ),
                        'category_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'category_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'category_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_category_name_by_goods_code'
                        ),
                        'season_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'season_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'season_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_season_name_by_goods_code'
                        ),
                        'series_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'series_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'series_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_series_name_by_goods_code'
                        ),
                        'unit_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'unit_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'unit_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_unit_name_by_goods_code'
                        ),
                        'year' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_year_by_goods_code',
                        ),
                        'property_value1' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value1_by_goods_id'
                        ),
                        'property_value2' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value2_by_goods_id'
                        ),
                        'property_value3' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value3_by_goods_id'
                        ),
                        'property_value4' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value4_by_goods_id'
                        ),
                        'property_value5' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value5_by_goods_id'
                        ),
                        'property_value6' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value6_by_goods_id'
                        ),
                        'sell_price' => array(
                            'type' => 'process_get_by_relation_model', //关联model获取数据
                            'function' => 'format_money',
                            'get_col' => 'sell_price',
                            'relation_model_params' => 'goods_code, color_code, size_code',
                            'relation_model_class' => 'MdlGoodsPrice',
                            'relation_model_path' => 'base/goods_price',
                            'relation_model_function' => 'get_goods_price_info_by_code'
                        ),
                    )
                ),
                'barcode_area' => array(
                    'type' => 'grid',
                    'title' => '商品条码',
                    'table' => 'pur_purchaser_record_detail',
                    'class' => 'MdlPurchaserRecord',
                    'class_path' => 'pur/purchaser_record',
                    'class_function' => 'get_detail_list_by_pid',
                    'data' => array(
                        'barcode' => '商品条码[条码]',
                    ),
                    'extra_process_data' => array(
                        'barcode' => array(
                            'type' => 'barcode'
                        ),
                    )
                ),
//				'page_bottom_area' => array(
//					'type'=>'grid',
//					'title'=>'页尾',
//					'table'=>'',
//					'class'=>'',
//					'class_path'=>'',
//					'class_function'=>'',
//					'data' => array(
//						'time'=>'时间',
//						'logo' => '公司logo'
//					),
//					'extra_process_data' => array(
//						'time'=>array(
//							'type'=>'time',
//							'function'=>'add_time'
//						),
//						'logo' => array(
//							'type' => 'logo',
//						)
//					)
//				),
            )
        ),
        //入库单标准打印
        'pur_store_in_record' => array(
            'title' => '入库单',
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '入库单信息',
                    'table' => 'pur_store_in_record',
                    'class' => 'MdlStoreInRecord',
                    'class_path' => 'pur/store_in_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'supplier_code' => '供应商代码',
                        'supplier_name' => '供应商名称',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'rebate' => '折扣',
                        'relation_code' => '关联单号',
                        'refer_time' => '交货时间',
                        'pay_time' => '付款期限',
                        'brand_code' => '品牌代码',
                        'brand_name' => '品牌名称',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'finance_pay_money' => '应付款',
                        'finance_pay_money_up' => '应付款(大写)',
                        'finance_balance_money' => '结余款',
                        'finance_balance_money_up' => '结余款(大写)',
                        'finance_debts_money' => '累计欠款',
                        'finance_debts_money_up' => '累计欠款(大写)',
	                    'before_debts_money' => '上次欠款',
	                    'before_debts_money_up' => '上次欠款(大写)',
	                    'after_debts_money' => '当日欠款',
	                    'after_debts_money_up' => '当日欠款(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_store_in_person' => '入库人',
                        'is_store_in_time' => '入库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_keep_accounts_person' => '记账人',
                        'is_keep_accounts_time' => '记账时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'refer_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'refer_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'brand_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'brand_code',
                            'function' => 'get_brand_name_by_code'
                        ),
                        'supplier_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'supplier_code',
                            'function' => 'get_supplier_name_by_code'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'finance_pay_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_supplier_general_ledger_by_code'
                        ),
                        'finance_pay_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_supplier_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_balance_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_supplier_general_ledger_by_code',
                        ),
                        'finance_balance_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_supplier_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_debts_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_supplier_general_ledger_by_code'
                        ),
                        'finance_debts_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_supplier_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_keep_accounts_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_keep_accounts_time',
                            'function' => 'format_day_time'
                        ),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        ),
	                    'before_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code'
	                    ),
	                    'before_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),
	                    'after_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code'
	                    ),
	                    'after_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),
                    ),
                ),
                //供应商信息
                'supplier_area' => array(
                    'type' => 'grid',
                    'title' => '供应商信息',
                    'table' => 'pur_store_in_record',
                    'class' => 'MdlStoreInRecord',
                    'class_path' => 'pur/store_in_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'supplier_code' => '供应商代码',
                        'supplier_name' => '供应商名称',
                        'supplier_contact_person' => '供应商联系人',
                        'supplier_email' => '供应商邮箱',
                        'supplier_phone' => '供应商电话',
                        'supplier_tel' => '供应商手机',
                        'supplier_address' => '供应商地址',
                        'supplier_fax' => '供应商传真',
                        'supplier_zipcode' => '供应商邮编',
                        'supplier_website' => '供应商公司网站',
                        'supplier_bank' => '供应商开户银行',
                        'supplier_account' => '供应商账号',
                        'supplier_account_name' => '供应商账号名称',
                        'supplier_legal_person' => '供应商法人',
                    ),
                    'extra_process_data' => array(
                        'supplier_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'supplier_code',
                            'function' => 'get_supplier_name_by_code'
                        ),
                        'supplier_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'email',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'address',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'website',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'account',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_legal_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'legal_person',
                            'function' => 'get_supplier_info_by_code'
                        ),
                    ),
                ),
                //打印明细
                'detail_area' => array(
                    'type' => 'list',
                    'title' => '明细区',
                    'table' => 'pur_store_in_record_detail',
                    'class' => 'MdlStoreInRecord',
                    'class_path' => 'pur/store_in_record',
                    'class_function' => 'get_detail_list_by_pid',
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码代码',
                        'size_name' => '尺码名称',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
	                    'brand_code' => '品牌代码',
	                    'brand_name' => '品牌名称',
	                    'sort_code' => '品类代码',
	                    'sort_name' => '品类名称',
	                    'category_code' => '分类代码',
	                    'category_name' => '分类名称',
	                    'season_code' => '季节代码',
	                    'season_name' => '季节名称',
	                    'series_code' => '系列代码',
	                    'series_name' => '系列名称',
	                    'unit_code' => '单位代码',
	                    'unit_name' => '单位名称',
	                    'year' => '年份',
	                    'custom_col1' => '自定义列1',
	                    'custom_col2' => '自定义列2',
	                    'custom_col3' => '自定义列3',
	                    'custom_col4' => '自定义列4',
	                    'custom_col5' => '自定义列5',
	                    'property_value1' => '属性1',
	                    'property_value2' => '属性2',
	                    'property_value3' => '属性3',
	                    'property_value4' => '属性4',
	                    'property_value5' => '属性5',
	                    'property_value6' => '属性6',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'size_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'size_code',
                            'function' => 'get_size_name_by_code',
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
	                    'brand_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'brand_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'brand_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_brand_name_by_goods_code'
	                    ),
	                    'sort_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'sort_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'sort_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_sort_name_by_goods_code'
	                    ),
	                    'category_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'category_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'category_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_category_name_by_goods_code'
	                    ),
	                    'season_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'season_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'season_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_season_name_by_goods_code'
	                    ),
	                    'series_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'series_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'series_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_series_name_by_goods_code'
	                    ),
	                    'unit_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'unit_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'unit_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_unit_name_by_goods_code'
	                    ),
	                    'year' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_year_by_goods_code',
	                    ),
	                    'property_value1' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value1_by_goods_id'
	                    ),
	                    'property_value2' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value2_by_goods_id'
	                    ),
	                    'property_value3' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value3_by_goods_id'
	                    ),
	                    'property_value4' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value4_by_goods_id'
	                    ),
	                    'property_value5' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value5_by_goods_id'
	                    ),
	                    'property_value6' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value6_by_goods_id'
	                    ),
                    ),
                ),
                //打印财务明细
                'finance_area' => array(
                    'type' => 'list',
                    'title' => '账务信息',
                    'table' => 'acc_supplier_general_journal',
                    'class' => 'MdlSupplierGeneralJournal',
                    'class_path' => 'acc/supplier_general_journal',
                    'class_function' => 'get_list_by_supplier_code',
                    'relation_table' => array(
                        'table' => 'pur_store_in_record',
                        'class' => 'MdlStoreInRecord',
                        'class_path' => 'pur/store_in_record',
                        'class_function' => 'get_info_by_id',
                        'relation_col' => 'supplier_code',
                    ),
                    'data' => array(
                        'supplier_code' => '供应商代码',
                        'supplier_name' => '供应商名称',
                        'record_type' => '单据类型',
                        'relation_code' => '关联单号',
                        'before_pay_money' => '期初应付款',
                        'change_pay_money' => '变动应付款',
                        'after_pay_money' => '期末应付款',
                        'before_balance_money' => '期初余额',
                        'change_balance_money' => '变动余额',
                        'after_balance_money' => '期末余额',
                        'before_debts_money' => '期初欠款',
                        'change_debts_money' => '变动欠款',
                        'after_debts_money' => '期末欠款'
                    ),
                    'extra_process_data' => array(
                        'supplier_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'supplier_code',
                            'function' => 'get_supplier_name_by_code'
                        ),
                        'record_type' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'record_type', //关联字段
                            'function' => 'get_record_type_name', //获取的方法
                        ),
                        'before_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_pay_money',
                            'function' => 'format_money',
                        ),
                        'change_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_pay_money',
                            'function' => 'format_money',
                        ),
                        'after_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_pay_money',
                            'function' => 'format_money'
                        ),
                        'before_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_balance_money',
                            'function' => 'format_money',
                        ),
                        'change_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_balance_money',
                            'function' => 'format_money',
                        ),
                        'after_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_balance_money',
                            'function' => 'format_money'
                        ),
                        'before_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_debts_money',
                            'function' => 'format_money',
                        ),
                        'change_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_debts_money',
                            'function' => 'format_money',
                        ),
                        'after_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_debts_money',
                            'function' => 'format_money'
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'pur_store_in_record',
                    'class' => 'MdlStoreInRecord',
                    'class_path' => 'pur/store_in_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_store_in_person' => '入库人',
                        'is_store_in_time' => '入库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_keep_accounts_person' => '记账人',
                        'is_keep_accounts_time' => '记账时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_keep_accounts_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_keep_accounts_time',
                            'function' => 'format_day_time'
                        ),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),
        //入库单扩展打印
        'ext_pur_store_in_record' => array(
            'title' => '入库单',
            'property' => 'record_ext', //单据明细扩展打印
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '入库单信息',
                    'table' => 'pur_store_in_record',
                    'class' => 'MdlStoreInRecord',
                    'class_path' => 'pur/store_in_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'supplier_code' => '供应商代码',
                        'supplier_name' => '供应商名称',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'rebate' => '折扣',
                        'relation_code' => '关联单号',
                        'refer_time' => '交货时间',
                        'pay_time' => '付款期限',
                        'brand_code' => '品牌代码',
                        'brand_name' => '品牌名称',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'finance_pay_money' => '应付款',
                        'finance_pay_money_up' => '应付款(大写)',
                        'finance_balance_money' => '结余款',
                        'finance_balance_money_up' => '结余款(大写)',
                        'finance_debts_money' => '累计欠款',
                        'finance_debts_money_up' => '累计欠款(大写)',
	                    'before_debts_money' => '上次欠款',
	                    'before_debts_money_up' => '上次欠款(大写)',
	                    'after_debts_money' => '当日欠款',
	                    'after_debts_money_up' => '当日欠款(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_store_in_person' => '入库人',
                        'is_store_in_time' => '入库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_keep_accounts_person' => '记账人',
                        'is_keep_accounts_time' => '记账时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'refer_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'refer_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'brand_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'brand_code',
                            'function' => 'get_brand_name_by_code'
                        ),
                        'supplier_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'supplier_code',
                            'function' => 'get_supplier_name_by_code'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'finance_pay_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_supplier_general_ledger_by_code'
                        ),
                        'finance_pay_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_supplier_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_balance_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_supplier_general_ledger_by_code'
                        ),
                        'finance_balance_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_supplier_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_debts_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_supplier_general_ledger_by_code'
                        ),
                        'finance_debts_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_supplier_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_keep_accounts_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_keep_accounts_time',
                            'function' => 'format_day_time'
                        ),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        ),
	                    'before_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code'
	                    ),
	                    'before_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),
	                    'after_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code'
	                    ),
	                    'after_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),
                    ),
                ),
                //供应商信息
                'supplier_area' => array(
                    'type' => 'grid',
                    'title' => '供应商信息',
                    'table' => 'pur_store_in_record',
                    'class' => 'MdlStoreInRecord',
                    'class_path' => 'pur/store_in_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'supplier_code' => '供应商代码',
                        'supplier_name' => '供应商名称',
                        'supplier_contact_person' => '供应商联系人',
                        'supplier_email' => '供应商邮箱',
                        'supplier_phone' => '供应商电话',
                        'supplier_tel' => '供应商手机',
                        'supplier_address' => '供应商地址',
                        'supplier_fax' => '供应商传真',
                        'supplier_zipcode' => '供应商邮编',
                        'supplier_website' => '供应商公司网站',
                        'supplier_bank' => '供应商开户银行',
                        'supplier_account' => '供应商账号',
                        'supplier_account_name' => '供应商账号名称',
                        'supplier_legal_person' => '供应商法人',
                    ),
                    'extra_process_data' => array(
                        'supplier_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'supplier_code',
                            'function' => 'get_supplier_name_by_code'
                        ),
                        'supplier_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'email',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'address',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'website',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'account',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_legal_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'legal_person',
                            'function' => 'get_supplier_info_by_code'
                        ),
                    ),
                ),
                //扩展打印明细
                'ext_detail_area' => array(
                    'type' => 'list',
                    'title' => '扩展明细区',
                    'table' => 'pur_store_in_record_detail',
                    'class' => 'MdlStoreInRecord',
                    'class_path' => 'pur/store_in_record',
                    'class_function' => 'get_detail_list_by_pid',
                    'property' => 'record_ext', //单据明细扩展打印
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码名称[扩展]',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
	                    'brand_code' => '品牌代码',
	                    'brand_name' => '品牌名称',
	                    'sort_code' => '品类代码',
	                    'sort_name' => '品类名称',
	                    'category_code' => '分类代码',
	                    'category_name' => '分类名称',
	                    'season_code' => '季节代码',
	                    'season_name' => '季节名称',
	                    'series_code' => '系列代码',
	                    'series_name' => '系列名称',
	                    'unit_code' => '单位代码',
	                    'unit_name' => '单位名称',
	                    'year' => '年份',
	                    'custom_col1' => '自定义列1',
	                    'custom_col2' => '自定义列2',
	                    'custom_col3' => '自定义列3',
	                    'custom_col4' => '自定义列4',
	                    'custom_col5' => '自定义列5',
	                    'property_value1' => '属性1',
	                    'property_value2' => '属性2',
	                    'property_value3' => '属性3',
	                    'property_value4' => '属性4',
	                    'property_value5' => '属性5',
	                    'property_value6' => '属性6',
                    ),
                    'ext_col' => 'size_code',
                    'ext_extra_process_data' => array(
                        'num' => 'plus',
                        'money' => 'plus',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
	                    'brand_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'brand_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'brand_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_brand_name_by_goods_code'
	                    ),
	                    'sort_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'sort_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'sort_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_sort_name_by_goods_code'
	                    ),
	                    'category_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'category_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'category_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_category_name_by_goods_code'
	                    ),
	                    'season_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'season_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'season_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_season_name_by_goods_code'
	                    ),
	                    'series_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'series_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'series_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_series_name_by_goods_code'
	                    ),
	                    'unit_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'unit_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'unit_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_unit_name_by_goods_code'
	                    ),
	                    'year' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_year_by_goods_code',
	                    ),
	                    'property_value1' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value1_by_goods_id'
	                    ),
	                    'property_value2' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value2_by_goods_id'
	                    ),
	                    'property_value3' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value3_by_goods_id'
	                    ),
	                    'property_value4' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value4_by_goods_id'
	                    ),
	                    'property_value5' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value5_by_goods_id'
	                    ),
	                    'property_value6' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value6_by_goods_id'
	                    ),
                    ),
                ),
                //打印财务明细
                'finance_area' => array(
                    'type' => 'list',
                    'title' => '账务信息',
                    'table' => 'acc_supplier_general_journal',
                    'class' => 'MdlSupplierGeneralJournal',
                    'class_path' => 'acc/supplier_general_journal',
                    'class_function' => 'get_list_by_supplier_code',
                    'relation_table' => array(
                        'table' => 'pur_store_in_record',
                        'class' => 'MdlStoreInRecord',
                        'class_path' => 'pur/store_in_record',
                        'class_function' => 'get_info_by_id',
                        'relation_col' => 'supplier_code',
                    ),
                    'data' => array(
                        'supplier_code' => '供应商代码',
                        'supplier_name' => '供应商名称',
                        'record_type' => '单据类型',
                        'relation_code' => '关联单号',
                        'before_pay_money' => '期初应付款',
                        'change_pay_money' => '变动应付款',
                        'after_pay_money' => '期末应付款',
                        'before_balance_money' => '期初余额',
                        'change_balance_money' => '变动余额',
                        'after_balance_money' => '期末余额',
                        'before_debts_money' => '期初欠款',
                        'change_debts_money' => '变动欠款',
                        'after_debts_money' => '期末欠款'
                    ),
                    'extra_process_data' => array(
                        'supplier_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'supplier_code',
                            'function' => 'get_supplier_name_by_code'
                        ),
                        'record_type' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'record_type', //关联字段
                            'function' => 'get_record_type_name', //获取的方法
                        ),
                        'before_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_pay_money',
                            'function' => 'format_money',
                        ),
                        'change_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_pay_money',
                            'function' => 'format_money',
                        ),
                        'after_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_pay_money',
                            'function' => 'format_money'
                        ),
                        'before_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_balance_money',
                            'function' => 'format_money',
                        ),
                        'change_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_balance_money',
                            'function' => 'format_money',
                        ),
                        'after_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_balance_money',
                            'function' => 'format_money'
                        ),
                        'before_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_debts_money',
                            'function' => 'format_money',
                        ),
                        'change_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_debts_money',
                            'function' => 'format_money',
                        ),
                        'after_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_debts_money',
                            'function' => 'format_money'
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'pur_store_in_record',
                    'class' => 'MdlStoreInRecord',
                    'class_path' => 'pur/store_in_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_store_in_person' => '入库人',
                        'is_store_in_time' => '入库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_keep_accounts_person' => '记账人',
                        'is_keep_accounts_time' => '记账时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_keep_accounts_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_keep_accounts_time',
                            'function' => 'format_day_time'
                        ),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),
        //采购商品条码(特殊)
        'pur_store_in_record_barcode' => array(
            'title' => '采购单条码',
            'main_conf' => array(
//				'page_top_area' => array(
//					'type'=>'grid',
//					'title'=>'页眉',
//					'table'=>'',
//					'class'=>'',
//					'class_path'=>'',
//					'class_function'=>'',
//					'data' => array(
//						'time'=>'时间',
//						'logo' => '公司logo'
//					),
//					'extra_process_data' => array(
//						'time'=>array(
//							'type'=>'time',
//							'function'=>'add_time'
//						),
//						'logo' => array(
//							'type' => 'logo',
//						)
//					)
//				),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '采购单条码',
                    'table' => 'pur_store_in_record_detail',
                    'class' => 'MdlStoreInRecord',
                    'class_path' => 'pur/store_in_record',
                    'class_function' => 'get_detail_list_by_pid',
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码代码',
                        'size_name' => '尺码名称',
                        'brand_code' => '品牌代码',
                        'brand_name' => '品牌名称',
                        'sort_code' => '品类代码',
                        'sort_name' => '品类名称',
                        'category_code' => '分类代码',
                        'category_name' => '分类名称',
                        'season_code' => '季节代码',
                        'season_name' => '季节名称',
                        'series_code' => '系列代码',
                        'series_name' => '系列名称',
                        'unit_code' => '单位代码',
                        'unit_name' => '单位名称',
                        'year' => '年份',
                        'property_value1' => '属性1',
                        'property_value2' => '属性2',
                        'property_value3' => '属性3',
                        'property_value4' => '属性4',
                        'property_value5' => '属性5',
                        'property_value6' => '属性6',
                        'sell_price' => '标准价',
                    ),
                    'extra_process_data' => array(
//						'barcode' => array(
//							'type' => 'barcode',
//						),
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_goods_name_by_code'
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'color_code',
                            'function' => 'get_color_name_by_code'
                        ),
                        'size_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'size_code',
                            'function' => 'get_size_name_by_code'
                        ),
                        'brand_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'brand_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'brand_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_brand_name_by_goods_code'
                        ),
                        'sort_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'sort_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'sort_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_sort_name_by_goods_code'
                        ),
                        'category_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'category_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'category_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_category_name_by_goods_code'
                        ),
                        'season_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'season_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'season_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_season_name_by_goods_code'
                        ),
                        'series_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'series_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'series_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_series_name_by_goods_code'
                        ),
                        'unit_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'unit_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'unit_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_unit_name_by_goods_code'
                        ),
                        'year' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_year_by_goods_code',
                        ),
                        'property_value1' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value1_by_goods_id'
                        ),
                        'property_value2' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value2_by_goods_id'
                        ),
                        'property_value3' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value3_by_goods_id'
                        ),
                        'property_value4' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value4_by_goods_id'
                        ),
                        'property_value5' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value5_by_goods_id'
                        ),
                        'property_value6' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value6_by_goods_id'
                        ),
                        'sell_price' => array(
                            'type' => 'process_get_by_relation_model', //关联model获取数据
                            'function' => 'format_money',
                            'get_col' => 'sell_price',
                            'relation_model_params' => 'goods_code, color_code, size_code',
                            'relation_model_class' => 'MdlGoodsPrice',
                            'relation_model_path' => 'base/goods_price',
                            'relation_model_function' => 'get_goods_price_info_by_code'
                        ),
                    )
                ),
                'barcode_area' => array(
                    'type' => 'grid',
                    'title' => '商品条码',
                    'table' => 'pur_store_in_record_detail',
                    'class' => 'MdlStoreInRecord',
                    'class_path' => 'pur/store_in_record',
                    'class_function' => 'get_detail_list_by_pid',
                    'data' => array(
                        'barcode' => '商品条码[条码]',
                    ),
                    'extra_process_data' => array(
                        'barcode' => array(
                            'type' => 'barcode'
                        ),
                    )
                ),
//				'page_bottom_area' => array(
//					'type'=>'grid',
//					'title'=>'页尾',
//					'table'=>'',
//					'class'=>'',
//					'class_path'=>'',
//					'class_function'=>'',
//					'data' => array(
//						'time'=>'时间',
//						'logo' => '公司logo'
//					),
//					'extra_process_data' => array(
//						'time'=>array(
//							'type'=>'time',
//							'function'=>'add_time'
//						),
//						'logo' => array(
//							'type' => 'logo',
//						)
//					)
//				),
            )
        ),

	    'pur_store_in_record_barcode_hangtag' => array(
		    'title' => '采购单吊牌',
		    'main_conf' => array(
			    'main_area' => array(
				    'type' => 'grid',
				    'title' => '采购单条码',
				    'table' => 'pur_store_in_record_detail',
				    'class' => 'MdlStoreInRecord',
				    'class_path' => 'pur/store_in_record',
				    'class_function' => 'get_detail_list_by_pid',
				    'data' => array(
					    'goods_code' => '商品代码',
					    'goods_name' => '商品名称',
					    'goods_short_name' => '商品简称',
					    'color_code' => '颜色代码',
					    'color_name' => '颜色名称',
					    'size_code' => '尺码代码',
					    'size_name' => '尺码名称',
					    'sell_price' => '标准价',
					    'label1' => '吊牌标签1',
					    'label2' => '吊牌标签2',
					    'label3' => '吊牌标签3',
					    'label4' => '吊牌标签4',
					    'label5' => '吊牌标签5',
					    'label6' => '吊牌标签6',
					    'label7' => '吊牌标签7',
					    'label8' => '吊牌标签8',
					    'label9' => '吊牌标签9',
					    'label10' => '吊牌标签10',
					    'brand_code' => '品牌代码',
					    'brand_name' => '品牌名称',
					    'sort_code' => '品类代码',
					    'sort_name' => '品类名称',
					    'category_code' => '分类代码',
					    'category_name' => '分类名称',
					    'season_code' => '季节代码',
					    'season_name' => '季节名称',
					    'series_code' => '系列代码',
					    'series_name' => '系列名称',
					    'unit_code' => '单位代码',
					    'unit_name' => '单位名称',
					    'year' => '年份',
					    'property_value1' => '属性1',
					    'property_value2' => '属性2',
					    'property_value3' => '属性3',
					    'property_value4' => '属性4',
					    'property_value5' => '属性5',
					    'property_value6' => '属性6',
				    ),
				    'extra_process_data' => array(
//						'goods_name' => array(
					    'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
					    'relation_col' => 'goods_code', //关联字段
					    'function' => 'get_goods_name_by_code', //获取的方法
				    ),
				    'goods_short_name' => array(
					    'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
					    'relation_col' => 'goods_code', //关联字段
					    'function' => 'get_goods_short_name_by_code', //获取的方法
				    ),
				    'color_name' => array(
					    'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
					    'relation_col' => 'color_code', //关联字段
					    'function' => 'get_color_name_by_code', //获取的方法
				    ),
				    'size_name' => array(
					    'type' => 'process_get_name_by_code',
					    'relation_col' => 'size_code',
					    'function' => 'get_size_name_by_code',
				    ),
				    'sell_price' => array(
					    'type' => 'process_get_by_relation_model', //关联model获取数据
					    'function' => 'format_money',
					    'get_col' => 'sell_price',
					    'relation_model_params' => 'goods_code, color_code, size_code',
					    'relation_model_class' => 'MdlGoodsPrice',
					    'relation_model_path' => 'base/goods_price',
					    'relation_model_function' => 'get_goods_price_info_by_code'
				    ),
				    'label1' => array(
					    'type' => 'process_get_info_by_relation_params', //关联获取数据,有多个参数
					    'get_col' => 'label1',
					    'relation_params' => 'goods_code', //关联字段
					    'function' => 'get_hangtag_by_goods_code'
				    ),
				    'label2' => array(
					    'type' => 'process_get_info_by_relation_params', //关联获取数据,有多个参数
					    'get_col' => 'label2',
					    'relation_params' => 'goods_code', //关联字段
					    'function' => 'get_hangtag_by_goods_code'
				    ),
				    'label3' => array(
					    'type' => 'process_get_info_by_relation_params', //关联获取数据,有多个参数
					    'get_col' => 'label3',
					    'relation_params' => 'goods_code', //关联字段
					    'function' => 'get_hangtag_by_goods_code'
				    ),
				    'label4' => array(
					    'type' => 'process_get_info_by_relation_params', //关联获取数据,有多个参数
					    'get_col' => 'label4',
					    'relation_params' => 'goods_code', //关联字段
					    'function' => 'get_hangtag_by_goods_code'
				    ),
				    'label5' => array(
					    'type' => 'process_get_info_by_relation_params', //关联获取数据,有多个参数
					    'get_col' => 'label5',
					    'relation_params' => 'goods_code', //关联字段
					    'function' => 'get_hangtag_by_goods_code'
				    ),
				    'label6' => array(
					    'type' => 'process_get_info_by_relation_params', //关联获取数据,有多个参数
					    'get_col' => 'label6',
					    'relation_params' => 'goods_code', //关联字段
					    'function' => 'get_hangtag_by_goods_code'
				    ),
				    'label7' => array(
					    'type' => 'process_get_info_by_relation_params', //关联获取数据,有多个参数
					    'get_col' => 'label7',
					    'relation_params' => 'goods_code', //关联字段
					    'function' => 'get_hangtag_by_goods_code'
				    ),
				    'label8' => array(
					    'type' => 'process_get_info_by_relation_params', //关联获取数据,有多个参数
					    'get_col' => 'label8',
					    'relation_params' => 'goods_code', //关联字段
					    'function' => 'get_hangtag_by_goods_code'
				    ),
				    'label9' => array(
					    'type' => 'process_get_info_by_relation_params', //关联获取数据,有多个参数
					    'get_col' => 'label9',
					    'relation_params' => 'goods_code', //关联字段
					    'function' => 'get_hangtag_by_goods_code'
				    ),
				    'label10' => array(
					    'type' => 'process_get_info_by_relation_params', //关联获取数据,有多个参数
					    'get_col' => 'label10',
					    'relation_params' => 'goods_code', //关联字段
					    'function' => 'get_hangtag_by_goods_code'
				    ),
				    'brand_code' => array(
					    'type' => 'process_get_array_by_code',
					    'relation_col' => 'goods_code',
					    'get_array_key' => 'brand_code',
					    'function' => 'get_goods_by_code'
				    ),
				    'brand_name' => array(
					    'type' => 'process_get_name_by_code',
					    'relation_col' => 'goods_code',
					    'function' => 'get_brand_name_by_goods_code'
				    ),
				    'sort_code' => array(
					    'type' => 'process_get_array_by_code',
					    'relation_col' => 'goods_code',
					    'get_array_key' => 'sort_code',
					    'function' => 'get_goods_by_code'
				    ),
				    'sort_name' => array(
					    'type' => 'process_get_name_by_code',
					    'relation_col' => 'goods_code',
					    'function' => 'get_sort_name_by_goods_code'
				    ),
				    'category_code' => array(
					    'type' => 'process_get_array_by_code',
					    'relation_col' => 'goods_code',
					    'get_array_key' => 'category_code',
					    'function' => 'get_goods_by_code'
				    ),
				    'category_name' => array(
					    'type' => 'process_get_name_by_code',
					    'relation_col' => 'goods_code',
					    'function' => 'get_category_name_by_goods_code'
				    ),
				    'season_code' => array(
					    'type' => 'process_get_array_by_code',
					    'relation_col' => 'goods_code',
					    'get_array_key' => 'season_code',
					    'function' => 'get_goods_by_code'
				    ),
				    'season_name' => array(
					    'type' => 'process_get_name_by_code',
					    'relation_col' => 'goods_code',
					    'function' => 'get_season_name_by_goods_code'
				    ),
				    'series_code' => array(
					    'type' => 'process_get_array_by_code',
					    'relation_col' => 'goods_code',
					    'get_array_key' => 'series_code',
					    'function' => 'get_goods_by_code'
				    ),
				    'series_name' => array(
					    'type' => 'process_get_name_by_code',
					    'relation_col' => 'goods_code',
					    'function' => 'get_series_name_by_goods_code'
				    ),
				    'unit_code' => array(
					    'type' => 'process_get_array_by_code',
					    'relation_col' => 'goods_code',
					    'get_array_key' => 'unit_code',
					    'function' => 'get_goods_by_code'
				    ),
				    'unit_name' => array(
					    'type' => 'process_get_name_by_code',
					    'relation_col' => 'goods_code',
					    'function' => 'get_unit_name_by_goods_code'
				    ),
				    'year' => array(
					    'type' => 'process_get_name_by_code',
					    'relation_col' => 'goods_code',
					    'function' => 'get_year_by_goods_code',
				    ),
				    'property_value1' => array(
					    'type' => 'process_get_name_by_code',
					    'relation_col' => 'goods_id',
					    'function' => 'get_property_value1_by_goods_id'
				    ),
				    'property_value2' => array(
					    'type' => 'process_get_name_by_code',
					    'relation_col' => 'goods_id',
					    'function' => 'get_property_value2_by_goods_id'
				    ),
				    'property_value3' => array(
					    'type' => 'process_get_name_by_code',
					    'relation_col' => 'goods_id',
					    'function' => 'get_property_value3_by_goods_id'
				    ),
				    'property_value4' => array(
					    'type' => 'process_get_name_by_code',
					    'relation_col' => 'goods_id',
					    'function' => 'get_property_value4_by_goods_id'
				    ),
				    'property_value5' => array(
					    'type' => 'process_get_name_by_code',
					    'relation_col' => 'goods_id',
					    'function' => 'get_property_value5_by_goods_id'
				    ),
				    'property_value6' => array(
					    'type' => 'process_get_name_by_code',
					    'relation_col' => 'goods_id',
					    'function' => 'get_property_value6_by_goods_id'
				    ),
			    ),
			    'barcode_area' => array(
				    'type' => 'grid',
				    'title' => '商品条码',
				    'table' => 'pur_store_in_record_detail',
				    'class' => 'MdlStoreInRecord',
				    'class_path' => 'pur/store_in_record',
				    'class_function' => 'get_detail_list_by_pid',
				    'data' => array(
					    'barcode' => '商品条码[条码]',
				    ),
				    'extra_process_data' => array(
					    'barcode' => array(
						    'type' => 'barcode'
					    ),
				    )
			    ),
		    )
	    ),

        //采购退货单标准打印
        'pur_return_record' => array(
            'title' => '采购退货单',
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '退货单信息',
                    'table' => 'pur_return_record',
                    'class' => 'MdlPurReturnRecord',
                    'class_path' => 'pur/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'supplier_code' => '供应商代码',
                        'supplier_name' => '供应商名称',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'rebate' => '折扣',
                        'relation_code' => '关联单号',
                        'refer_time' => '交货时间',
                        'pay_time' => '付款期限',
                        'brand_code' => '品牌代码',
                        'brand_name' => '品牌名称',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'finance_pay_money' => '应付款',
                        'finance_pay_money_up' => '应付款(大写)',
                        'finance_balance_money' => '结余款',
                        'finance_balance_money_up' => '结余款(大写)',
                        'finance_debts_money' => '累计欠款',
                        'finance_debts_money_up' => '累计欠款(大写)',
	                    'before_debts_money' => '上次欠款',
	                    'before_debts_money_up' => '上次欠款(大写)',
	                    'after_debts_money' => '当日欠款',
	                    'after_debts_money_up' => '当日欠款(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_store_out_person' => '出库人',
                        'is_store_out_time' => '出库时间',
                        'is_keep_accounts_person' => '记账人',
                        'is_keep_accounts_time' => '记账时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'brand_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'brand_code',
                            'function' => 'get_brand_name_by_code'
                        ),
                        'supplier_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'supplier_code',
                            'function' => 'get_supplier_name_by_code'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'finance_pay_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_supplier_general_ledger_by_code'
                        ),
                        'finance_pay_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_supplier_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_balance_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_supplier_general_ledger_by_code'
                        ),
                        'finance_balance_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_supplier_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_debts_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_supplier_general_ledger_by_code'
                        ),
                        'finance_debts_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_supplier_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_out_time',
                            'function' => 'format_day_time'
                        ),
                        'is_keep_accounts_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_keep_accounts_time',
                            'function' => 'format_day_time'
                        ),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        ),
	                    'before_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code'
	                    ),
	                    'before_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),
	                    'after_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code'
	                    ),
	                    'after_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),
                    ),
                ),
                //供应商信息
                'supplier_area' => array(
                    'type' => 'grid',
                    'title' => '供应商信息',
                    'table' => 'pur_return_record',
                    'class' => 'MdlPurReturnRecord',
                    'class_path' => 'pur/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'supplier_code' => '供应商代码',
                        'supplier_name' => '供应商名称',
                        'supplier_contact_person' => '供应商联系人',
                        'supplier_email' => '供应商邮箱',
                        'supplier_phone' => '供应商电话',
                        'supplier_tel' => '供应商手机',
                        'supplier_address' => '供应商地址',
                        'supplier_fax' => '供应商传真',
                        'supplier_zipcode' => '供应商邮编',
                        'supplier_website' => '供应商公司网站',
                        'supplier_bank' => '供应商开户银行',
                        'supplier_account' => '供应商账号',
                        'supplier_account_name' => '供应商账号名称',
                        'supplier_legal_person' => '供应商法人',
                    ),
                    'extra_process_data' => array(
                        'supplier_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'supplier_code',
                            'function' => 'get_supplier_name_by_code'
                        ),
                        'supplier_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'email',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'address',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'website',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'account',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_legal_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'legal_person',
                            'function' => 'get_supplier_info_by_code'
                        ),
                    ),
                ),
                //打印明细
                'detail_area' => array(
                    'type' => 'list',
                    'title' => '明细区',
                    'table' => 'pur_return_record_detail',
                    'class' => 'MdlPurReturnRecord',
                    'class_path' => 'pur/return_record',
                    'class_function' => 'get_detail_list_by_pid',
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码代码',
                        'size_name' => '尺码名称',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
	                    'brand_code' => '品牌代码',
	                    'brand_name' => '品牌名称',
	                    'sort_code' => '品类代码',
	                    'sort_name' => '品类名称',
	                    'category_code' => '分类代码',
	                    'category_name' => '分类名称',
	                    'season_code' => '季节代码',
	                    'season_name' => '季节名称',
	                    'series_code' => '系列代码',
	                    'series_name' => '系列名称',
	                    'unit_code' => '单位代码',
	                    'unit_name' => '单位名称',
	                    'year' => '年份',
	                    'property_value1' => '属性1',
	                    'property_value2' => '属性2',
	                    'property_value3' => '属性3',
	                    'property_value4' => '属性4',
	                    'property_value5' => '属性5',
	                    'property_value6' => '属性6',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'size_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'size_code',
                            'function' => 'get_size_name_by_code',
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
	                    'brand_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'brand_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'brand_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_brand_name_by_goods_code'
	                    ),
	                    'sort_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'sort_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'sort_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_sort_name_by_goods_code'
	                    ),
	                    'category_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'category_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'category_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_category_name_by_goods_code'
	                    ),
	                    'season_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'season_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'season_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_season_name_by_goods_code'
	                    ),
	                    'series_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'series_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'series_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_series_name_by_goods_code'
	                    ),
	                    'unit_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'unit_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'unit_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_unit_name_by_goods_code'
	                    ),
	                    'year' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_year_by_goods_code',
	                    ),
	                    'property_value1' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value1_by_goods_id'
	                    ),
	                    'property_value2' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value2_by_goods_id'
	                    ),
	                    'property_value3' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value3_by_goods_id'
	                    ),
	                    'property_value4' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value4_by_goods_id'
	                    ),
	                    'property_value5' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value5_by_goods_id'
	                    ),
	                    'property_value6' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value6_by_goods_id'
	                    ),
                    ),
                ),
                //打印财务明细
                'finance_area' => array(
                    'type' => 'list',
                    'title' => '账务信息',
                    'table' => 'acc_supplier_general_journal',
                    'class' => 'MdlSupplierGeneralJournal',
                    'class_path' => 'acc/supplier_general_journal',
                    'class_function' => 'get_list_by_supplier_code',
                    'relation_table' => array(
                        'table' => 'pur_return_record',
                        'class' => 'MdlPurReturnRecord',
                        'class_path' => 'pur/return_record',
                        'class_function' => 'get_info_by_id',
                        'relation_col' => 'supplier_code',
                    ),
                    'data' => array(
                        'supplier_code' => '供应商代码',
                        'supplier_name' => '供应商名称',
                        'record_type' => '单据类型',
                        'relation_code' => '关联单号',
                        'before_pay_money' => '期初应付款',
                        'change_pay_money' => '变动应付款',
                        'after_pay_money' => '期末应付款',
                        'before_balance_money' => '期初余额',
                        'change_balance_money' => '变动余额',
                        'after_balance_money' => '期末余额',
                        'before_debts_money' => '期初欠款',
                        'change_debts_money' => '变动欠款',
                        'after_debts_money' => '期末欠款'
                    ),
                    'extra_process_data' => array(
                        'supplier_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'supplier_code',
                            'function' => 'get_supplier_name_by_code'
                        ),
                        'record_type' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'record_type', //关联字段
                            'function' => 'get_record_type_name', //获取的方法
                        ),
                        'before_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_pay_money',
                            'function' => 'format_money',
                        ),
                        'change_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_pay_money',
                            'function' => 'format_money',
                        ),
                        'after_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_pay_money',
                            'function' => 'format_money'
                        ),
                        'before_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_balance_money',
                            'function' => 'format_money',
                        ),
                        'change_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_balance_money',
                            'function' => 'format_money',
                        ),
                        'after_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_balance_money',
                            'function' => 'format_money'
                        ),
                        'before_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_debts_money',
                            'function' => 'format_money',
                        ),
                        'change_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_debts_money',
                            'function' => 'format_money',
                        ),
                        'after_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_debts_money',
                            'function' => 'format_money'
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'pur_return_record',
                    'class' => 'MdlPurReturnRecord',
                    'class_path' => 'pur/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_store_out_person' => '出库人',
                        'is_store_out_time' => '出库时间',
                        'is_keep_accounts_person' => '记账人',
                        'is_keep_accounts_time' => '记账时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_out_time',
                            'function' => 'format_day_time'
                        ),
                        'is_keep_accounts_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_keep_accounts_time',
                            'function' => 'format_day_time'
                        ),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),
        //采购退货单扩展打印
        'ext_pur_return_record' => array(
            'title' => '采购退货单',
            'property' => 'record_ext', //单据明细扩展打印
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '退货单信息',
                    'table' => 'pur_return_record',
                    'class' => 'MdlPurReturnRecord',
                    'class_path' => 'pur/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'supplier_code' => '供应商代码',
                        'supplier_name' => '供应商名称',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'rebate' => '折扣',
                        'relation_code' => '关联单号',
                        'refer_time' => '交货时间',
                        'pay_time' => '付款期限',
                        'brand_code' => '品牌代码',
                        'brand_name' => '品牌名称',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'finance_pay_money' => '应付款',
                        'finance_pay_money_up' => '应付款(大写)',
                        'finance_balance_money' => '结余款',
                        'finance_balance_money_up' => '结余款(大写)',
                        'finance_debts_money' => '累计欠款',
                        'finance_debts_money_up' => '累计欠款(大写)',
	                    'before_debts_money' => '上次欠款',
	                    'before_debts_money_up' => '上次欠款(大写)',
	                    'after_debts_money' => '当日欠款',
	                    'after_debts_money_up' => '当日欠款(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_store_out_person' => '出库人',
                        'is_store_out_time' => '出库时间',
                        'is_keep_accounts_person' => '记账人',
                        'is_keep_accounts_time' => '记账时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'brand_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'brand_code',
                            'function' => 'get_brand_name_by_code'
                        ),
                        'supplier_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'supplier_code',
                            'function' => 'get_supplier_name_by_code'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'finance_pay_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_supplier_general_ledger_by_code'
                        ),
                        'finance_pay_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_supplier_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_balance_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_supplier_general_ledger_by_code'
                        ),
                        'finance_balance_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_supplier_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_debts_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_supplier_general_ledger_by_code'
                        ),
                        'finance_debts_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_supplier_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_out_time',
                            'function' => 'format_day_time'
                        ),
                        'is_keep_accounts_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_keep_accounts_time',
                            'function' => 'format_day_time'
                        ),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        ),
	                    'before_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code'
	                    ),
	                    'before_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),
	                    'after_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code'
	                    ),
	                    'after_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'supplier_code, record_code',
		                    'relation_model_class' => 'MdlSupplierGeneralJournal',
		                    'relation_model_path' => 'acc/supplier_general_journal',
		                    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),
                    ),
                ),
                //供应商信息
                'supplier_area' => array(
                    'type' => 'grid',
                    'title' => '供应商信息',
                    'table' => 'pur_return_record',
                    'class' => 'MdlPurReturnRecord',
                    'class_path' => 'pur/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'supplier_code' => '供应商代码',
                        'supplier_name' => '供应商名称',
                        'supplier_contact_person' => '供应商联系人',
                        'supplier_email' => '供应商邮箱',
                        'supplier_phone' => '供应商电话',
                        'supplier_tel' => '供应商手机',
                        'supplier_address' => '供应商地址',
                        'supplier_fax' => '供应商传真',
                        'supplier_zipcode' => '供应商邮编',
                        'supplier_website' => '供应商公司网站',
                        'supplier_bank' => '供应商开户银行',
                        'supplier_account' => '供应商账号',
                        'supplier_account_name' => '供应商账号名称',
                        'supplier_legal_person' => '供应商法人',
                    ),
                    'extra_process_data' => array(
                        'supplier_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'supplier_code',
                            'function' => 'get_supplier_name_by_code'
                        ),
                        'supplier_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'email',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'address',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'website',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'account',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_supplier_info_by_code'
                        ),
                        'supplier_legal_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'supplier_code',
                            'get_array_key' => 'legal_person',
                            'function' => 'get_supplier_info_by_code'
                        ),
                    ),
                ),
                //扩展打印明细
                'ext_detail_area' => array(
                    'type' => 'list',
                    'title' => '扩展明细区',
                    'table' => 'pur_return_record_detail',
                    'class' => 'MdlPurReturnRecord',
                    'class_path' => 'pur/return_record',
                    'class_function' => 'get_detail_list_by_pid',
                    'property' => 'record_ext', //单据明细扩展打印
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码名称[扩展]',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
	                    'brand_code' => '品牌代码',
	                    'brand_name' => '品牌名称',
	                    'sort_code' => '品类代码',
	                    'sort_name' => '品类名称',
	                    'category_code' => '分类代码',
	                    'category_name' => '分类名称',
	                    'season_code' => '季节代码',
	                    'season_name' => '季节名称',
	                    'series_code' => '系列代码',
	                    'series_name' => '系列名称',
	                    'unit_code' => '单位代码',
	                    'unit_name' => '单位名称',
	                    'year' => '年份',
	                    'property_value1' => '属性1',
	                    'property_value2' => '属性2',
	                    'property_value3' => '属性3',
	                    'property_value4' => '属性4',
	                    'property_value5' => '属性5',
	                    'property_value6' => '属性6',
                    ),
                    'ext_col' => 'size_code',
                    'ext_extra_process_data' => array(
                        'num' => 'plus',
                        'money' => 'plus',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
	                    'brand_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'brand_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'brand_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_brand_name_by_goods_code'
	                    ),
	                    'sort_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'sort_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'sort_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_sort_name_by_goods_code'
	                    ),
	                    'category_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'category_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'category_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_category_name_by_goods_code'
	                    ),
	                    'season_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'season_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'season_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_season_name_by_goods_code'
	                    ),
	                    'series_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'series_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'series_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_series_name_by_goods_code'
	                    ),
	                    'unit_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'unit_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'unit_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_unit_name_by_goods_code'
	                    ),
	                    'year' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_year_by_goods_code',
	                    ),
	                    'property_value1' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value1_by_goods_id'
	                    ),
	                    'property_value2' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value2_by_goods_id'
	                    ),
	                    'property_value3' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value3_by_goods_id'
	                    ),
	                    'property_value4' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value4_by_goods_id'
	                    ),
	                    'property_value5' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value5_by_goods_id'
	                    ),
	                    'property_value6' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value6_by_goods_id'
	                    ),
                    ),
                ),
                //打印财务明细
                'finance_area' => array(
                    'type' => 'list',
                    'title' => '账务信息',
                    'table' => 'acc_supplier_general_journal',
                    'class' => 'MdlSupplierGeneralJournal',
                    'class_path' => 'acc/supplier_general_journal',
                    'class_function' => 'get_list_by_supplier_code',
                    'relation_table' => array(
                        'table' => 'pur_return_record',
                        'class' => 'MdlPurReturnRecord',
                        'class_path' => 'pur/return_record',
                        'class_function' => 'get_info_by_id',
                        'relation_col' => 'supplier_code',
                    ),
                    'data' => array(
                        'supplier_code' => '供应商代码',
                        'supplier_name' => '供应商名称',
                        'record_type' => '单据类型',
                        'relation_code' => '关联单号',
                        'before_pay_money' => '期初应付款',
                        'change_pay_money' => '变动应付款',
                        'after_pay_money' => '期末应付款',
                        'before_balance_money' => '期初余额',
                        'change_balance_money' => '变动余额',
                        'after_balance_money' => '期末余额',
                        'before_debts_money' => '期初欠款',
                        'change_debts_money' => '变动欠款',
                        'after_debts_money' => '期末欠款'
                    ),
                    'extra_process_data' => array(
                        'supplier_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'supplier_code',
                            'function' => 'get_supplier_name_by_code'
                        ),
                        'record_type' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'record_type', //关联字段
                            'function' => 'get_record_type_name', //获取的方法
                        ),
                        'before_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_pay_money',
                            'function' => 'format_money',
                        ),
                        'change_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_pay_money',
                            'function' => 'format_money',
                        ),
                        'after_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_pay_money',
                            'function' => 'format_money'
                        ),
                        'before_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_balance_money',
                            'function' => 'format_money',
                        ),
                        'change_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_balance_money',
                            'function' => 'format_money',
                        ),
                        'after_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_balance_money',
                            'function' => 'format_money'
                        ),
                        'before_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_debts_money',
                            'function' => 'format_money',
                        ),
                        'change_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_debts_money',
                            'function' => 'format_money',
                        ),
                        'after_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_debts_money',
                            'function' => 'format_money'
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'pur_return_record',
                    'class' => 'MdlPurReturnRecord',
                    'class_path' => 'pur/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_store_out_person' => '出库人',
                        'is_store_out_time' => '出库时间',
                        'is_keep_accounts_person' => '记账人',
                        'is_keep_accounts_time' => '记账时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_out_time',
                            'function' => 'format_day_time'
                        ),
                        'is_keep_accounts_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_keep_accounts_time',
                            'function' => 'format_day_time'
                        ),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),
        //批发订单标准打印
        'wbm_wholesale_record' => array(
            'title' => '批发订单',
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '批发订单信息',
                    'table' => 'wbm_wholesale_record',
                    'class' => 'MdlWholesaleRecord',
                    'class_path' => 'wbm/wholesale_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'distributor_code' => '客户代码',
                        'distributor_name' => '客户名称',
                        'rebate' => '折扣',
                        'refer_time' => '交货时间',
                        'pay_time' => '付款期限',
                        'brand_code' => '品牌代码',
                        'brand_name' => '品牌名称',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'finance_pay_money' => '应收款',
                        'finance_pay_money_up' => '应收款(大写)',
                        'finance_balance_money' => '结余款',
                        'finance_balance_money_up' => '结余款(大写)',
                        'finance_debts_money' => '累计欠款',
                        'finance_debts_money_up' => '累计欠款(大写)',
	                    'before_debts_money' => '上次欠款',
	                    'before_debts_money_up' => '上次欠款(大写)',
	                    'after_debts_money' => '当日欠款',
	                    'after_debts_money_up' => '当日欠款(大写)',
                        'finance_credit_limit' => '信用额度',
                        'finance_credit_limit_up' => '信用额度(大写)',
                        'finance_bond' => '保证金',
                        'finance_bond_up' => '保证金(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_check_person' => '审核人',
                        'is_check_time' => '审核时间',
                        'is_execute_person' => '执行人',
                        'is_execute_time' => '执行时间',
//						'is_cancel_person' => '作废人',
//						'is_cancel_time' => '作废时间',
                        'is_stop_person' => '终止人',
                        'is_stop_time' => '终止时间',
                        'is_finish_person' => '完成人',
                        'is_finish_time' => '完成时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'refer_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'refer_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'brand_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'brand_code',
                            'function' => 'get_brand_name_by_code'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_distributor_name_by_code'
                        ),
                        'finance_pay_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_pay_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_balance_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_balance_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_debts_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_debts_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_credit_limit' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'credit_limit',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_credit_limit_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'credit_limit',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_bond' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'bond',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_bond_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'bond',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_check_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_check_time',
                            'function' => 'format_day_time'
                        ),
                        'is_execute_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_execute_time',
                            'function' => 'format_day_time'
                        ),
//						'is_cancel_time' => array(
//							'type'=>'format_day_time', //格式化时间
//							'relation_col'=>'is_cancel_time',
//							'function'=>'format_day_time'
//						),
                        'is_stop_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_stop_time',
                            'function' => 'format_day_time'
                        ),
                        'is_finish_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_finish_time',
                            'function' => 'format_day_time'
                        ),
	                    'before_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code'
	                    ),
	                    'before_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),
	                    'after_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code'
	                    ),
	                    'after_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),
                    ),
                ),
                //客户信息
                'distributor_area' => array(
                    'type' => 'grid',
                    'title' => '客户信息',
                    'table' => 'wbm_wholesale_record',
                    'class' => 'MdlWholesaleRecord',
                    'class_path' => 'wbm/wholesale_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'distributor_code' => '客户代码',
                        'distributor_name' => '客户名称',
                        'distributor_contact_person' => '客户联系人',
                        'distributor_email' => '客户邮箱',
                        'distributor_phone' => '客户电话',
                        'distributor_tel' => '客户手机',
                        'distributor_address' => '客户地址',
                        'distributor_fax' => '客户传真',
                        'distributor_zipcode' => '客户邮编',
                        'distributor_website' => '客户公司网站',
                        'distributor_bank' => '客户开户银行',
                        'distributor_account' => '客户账号',
                        'distributor_account_name' => '客户账号名称',
                        'distributor_legal_person' => '客户法人',
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_distributor_name_by_code'
                        ),
                        'distributor_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'email',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'address',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'website',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'account',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_legal_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'legal_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                    ),
                ),
                //商店信息
                'shop_area' => array(
                    'type' => 'grid',
                    'title' => '商店信息',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'is_shop_print' => true, //商店级别
                    'data' => array(
                        'distributor_name' => '商店名称',
                        'distributor_contact_person' => '商店联系人',
                        'distributor_email' => '商店邮箱',
                        'distributor_phone' => '商店电话',
                        'distributor_tel' => '商店手机',
                        'distributor_address' => '商店地址',
                        'distributor_fax' => '商店传真',
                        'distributor_zipcode' => '商店邮编',
                        'distributor_website' => '商店公司网站',
                        'distributor_bank' => '商店开户银行',
                        'distributor_account' => '商店账号',
                        'distributor_account_name' => '商店账号名称',
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'distributor_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'email',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'address',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'website',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_distributor_info_by_code'
                        ),
                    ),
                ),
                //打印明细
                'detail_area' => array(
                    'type' => 'list',
                    'title' => '明细区',
                    'table' => 'wbm_wholesale_record_detail',
                    'class' => 'MdlWholesaleRecord',
                    'class_path' => 'wbm/wholesale_record',
                    'class_function' => 'get_detail_list_by_pid',
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码代码',
                        'size_name' => '尺码名称',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
	                    'brand_code' => '品牌代码',
	                    'brand_name' => '品牌名称',
	                    'sort_code' => '品类代码',
	                    'sort_name' => '品类名称',
	                    'category_code' => '分类代码',
	                    'category_name' => '分类名称',
	                    'season_code' => '季节代码',
	                    'season_name' => '季节名称',
	                    'series_code' => '系列代码',
	                    'series_name' => '系列名称',
	                    'unit_code' => '单位代码',
	                    'unit_name' => '单位名称',
	                    'year' => '年份',
	                    'custom_col1' => '自定义列1',
	                    'custom_col2' => '自定义列2',
	                    'custom_col3' => '自定义列3',
	                    'custom_col4' => '自定义列4',
	                    'custom_col5' => '自定义列5',
	                    'property_value1' => '属性1',
	                    'property_value2' => '属性2',
	                    'property_value3' => '属性3',
	                    'property_value4' => '属性4',
	                    'property_value5' => '属性5',
	                    'property_value6' => '属性6',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'size_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'size_code',
                            'function' => 'get_size_name_by_code',
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
	                    'brand_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'brand_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'brand_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_brand_name_by_goods_code'
	                    ),
	                    'sort_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'sort_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'sort_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_sort_name_by_goods_code'
	                    ),
	                    'category_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'category_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'category_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_category_name_by_goods_code'
	                    ),
	                    'season_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'season_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'season_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_season_name_by_goods_code'
	                    ),
	                    'series_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'series_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'series_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_series_name_by_goods_code'
	                    ),
	                    'unit_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'unit_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'unit_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_unit_name_by_goods_code'
	                    ),
	                    'year' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_year_by_goods_code',
	                    ),
	                    'property_value1' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value1_by_goods_id'
	                    ),
	                    'property_value2' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value2_by_goods_id'
	                    ),
	                    'property_value3' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value3_by_goods_id'
	                    ),
	                    'property_value4' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value4_by_goods_id'
	                    ),
	                    'property_value5' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value5_by_goods_id'
	                    ),
	                    'property_value6' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value6_by_goods_id'
	                    ),
                    ),
                ),
                //打印财务明细
                'finance_area' => array(
                    'type' => 'list',
                    'title' => '账务信息',
                    'table' => 'acc_distributor_general_journal',
                    'class' => 'MdlDistributorGeneralJournal',
                    'class_path' => 'acc/distributor_general_journal',
                    'class_function' => 'get_list_by_distributor_code',
                    'relation_table' => array(
                        'table' => 'wbm_wholesale_record',
                        'class' => 'MdlWholesaleRecord',
                        'class_path' => 'wbm/wholesale_record',
                        'class_function' => 'get_info_by_id',
                        'relation_col' => 'distributor_code',
                    ),
                    'data' => array(
                        'distributor_code' => '客户代码',
                        'distributor_name' => '客户名称',
                        'record_type' => '单据类型',
                        'relation_code' => '关联单号',
                        'before_pay_money' => '期初应收款',
                        'change_pay_money' => '变动应收款',
                        'after_pay_money' => '期末应收款',
                        'before_balance_money' => '期初余额',
                        'change_balance_money' => '变动余额',
                        'after_balance_money' => '期末余额',
                        'before_debts_money' => '期初欠款',
                        'change_debts_money' => '变动欠款',
                        'after_debts_money' => '期末欠款'
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_distributor_name_by_code'
                        ),
                        'record_type' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'record_type', //关联字段
                            'function' => 'get_record_type_name', //获取的方法
                        ),
                        'before_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_pay_money',
                            'function' => 'format_money',
                        ),
                        'change_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_pay_money',
                            'function' => 'format_money',
                        ),
                        'after_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_pay_money',
                            'function' => 'format_money'
                        ),
                        'before_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_balance_money',
                            'function' => 'format_money',
                        ),
                        'change_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_balance_money',
                            'function' => 'format_money',
                        ),
                        'after_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_balance_money',
                            'function' => 'format_money'
                        ),
                        'before_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_debts_money',
                            'function' => 'format_money',
                        ),
                        'change_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_debts_money',
                            'function' => 'format_money',
                        ),
                        'after_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_debts_money',
                            'function' => 'format_money'
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'wbm_wholesale_record',
                    'class' => 'MdlWholesaleRecord',
                    'class_path' => 'wbm/wholesale_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_check_person' => '审核人',
                        'is_check_time' => '审核时间',
                        'is_execute_person' => '执行人',
                        'is_execute_time' => '执行时间',
//						'is_cancel_person' => '作废人',
//						'is_cancel_time' => '作废时间',
                        'is_stop_person' => '终止人',
                        'is_stop_time' => '终止时间',
                        'is_finish_person' => '完成人',
                        'is_finish_time' => '完成时间'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_check_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_check_time',
                            'function' => 'format_day_time'
                        ),
                        'is_execute_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_execute_time',
                            'function' => 'format_day_time'
                        ),
//						'is_cancel_time' => array(
//							'type'=>'format_day_time', //格式化时间
//							'relation_col'=>'is_cancel_time',
//							'function'=>'format_day_time'
//						),
                        'is_stop_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_stop_time',
                            'function' => 'format_day_time'
                        ),
                        'is_finish_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_finish_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),
        //批发单扩展打印
        'ext_wbm_wholesale_record' => array(
            'title' => '批发订单',
            'property' => 'record_ext', //单据明细扩展打印
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '批发订单信息',
                    'table' => 'wbm_wholesale_record',
                    'class' => 'MdlWholesaleRecord',
                    'class_path' => 'wbm/wholesale_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'distributor_code' => '客户代码',
                        'distributor_name' => '客户名称',
                        'rebate' => '折扣',
                        'refer_time' => '交货时间',
                        'pay_time' => '付款期限',
                        'brand_code' => '品牌代码',
                        'brand_name' => '品牌名称',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'finance_pay_money' => '应收款',
                        'finance_pay_money_up' => '应收款(大写)',
                        'finance_balance_money' => '结余款',
                        'finance_balance_money_up' => '结余款(大写)',
                        'finance_debts_money' => '累计欠款',
                        'finance_debts_money_up' => '累计欠款(大写)',
	                    'before_debts_money' => '上次欠款',
	                    'before_debts_money_up' => '上次欠款(大写)',
	                    'after_debts_money' => '当日欠款',
	                    'after_debts_money_up' => '当日欠款(大写)',
                        'finance_credit_limit' => '信用额度',
                        'finance_credit_limit_up' => '信用额度(大写)',
                        'finance_bond' => '保证金',
                        'finance_bond_up' => '保证金(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_check_person' => '审核人',
                        'is_check_time' => '审核时间',
                        'is_execute_person' => '执行人',
                        'is_execute_time' => '执行时间',
//						'is_cancel_person' => '作废人',
//						'is_cancel_time' => '作废时间',
                        'is_stop_person' => '终止人',
                        'is_stop_time' => '终止时间',
                        'is_finish_person' => '完成人',
                        'is_finish_time' => '完成时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'refer_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'refer_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'brand_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'brand_code',
                            'function' => 'get_brand_name_by_code'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_distributor_name_by_code'
                        ),
                        'finance_pay_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_pay_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_balance_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_balance_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_debts_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_debts_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_credit_limit' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'credit_limit',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_credit_limit_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'credit_limit',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_bond' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'bond',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_bond_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'bond',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_check_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_check_time',
                            'function' => 'format_day_time'
                        ),
                        'is_execute_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_execute_time',
                            'function' => 'format_day_time'
                        ),
//						'is_cancel_time' => array(
//							'type'=>'format_day_time', //格式化时间
//							'relation_col'=>'is_cancel_time',
//							'function'=>'format_day_time'
//						),
                        'is_stop_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_stop_time',
                            'function' => 'format_day_time'
                        ),
                        'is_finish_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_finish_time',
                            'function' => 'format_day_time'
                        ),
	                    'before_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code'
	                    ),
	                    'before_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),
	                    'after_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code'
	                    ),
	                    'after_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),
                    ),
                ),
                //客户信息
                'distributor_area' => array(
                    'type' => 'grid',
                    'title' => '客户信息',
                    'table' => 'wbm_wholesale_record',
                    'class' => 'MdlWholesaleRecord',
                    'class_path' => 'wbm/wholesale_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'distributor_code' => '客户代码',
                        'distributor_name' => '客户名称',
                        'distributor_contact_person' => '客户联系人',
                        'distributor_email' => '客户邮箱',
                        'distributor_phone' => '客户电话',
                        'distributor_tel' => '客户手机',
                        'distributor_address' => '客户地址',
                        'distributor_fax' => '客户传真',
                        'distributor_zipcode' => '客户邮编',
                        'distributor_website' => '客户公司网站',
                        'distributor_bank' => '客户开户银行',
                        'distributor_account' => '客户账号',
                        'distributor_account_name' => '客户账号名称',
                        'distributor_legal_person' => '客户法人',
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_distributor_name_by_code'
                        ),
                        'distributor_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'email',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'address',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'website',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'account',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_legal_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'legal_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                    ),
                ),
                //商店信息
                'shop_area' => array(
                    'type' => 'grid',
                    'title' => '商店信息',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'is_shop_print' => true, //商店级别
                    'data' => array(
                        'distributor_name' => '商店名称',
                        'distributor_contact_person' => '商店联系人',
                        'distributor_email' => '商店邮箱',
                        'distributor_phone' => '商店电话',
                        'distributor_tel' => '商店手机',
                        'distributor_address' => '商店地址',
                        'distributor_fax' => '商店传真',
                        'distributor_zipcode' => '商店邮编',
                        'distributor_website' => '商店公司网站',
                        'distributor_bank' => '商店开户银行',
                        'distributor_account' => '商店账号',
                        'distributor_account_name' => '商店账号名称',
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'distributor_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'email',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'address',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'website',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_distributor_info_by_code'
                        ),
                    ),
                ),
                //扩展打印明细
                'ext_detail_area' => array(
                    'type' => 'list',
                    'title' => '扩展明细区',
                    'table' => 'wbm_wholesale_record_detail',
                    'class' => 'MdlWholesaleRecord',
                    'class_path' => 'wbm/wholesale_record',
                    'class_function' => 'get_detail_list_by_pid',
                    'property' => 'record_ext', //单据明细扩展打印
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码名称[扩展]',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
	                    'brand_code' => '品牌代码',
	                    'brand_name' => '品牌名称',
	                    'sort_code' => '品类代码',
	                    'sort_name' => '品类名称',
	                    'category_code' => '分类代码',
	                    'category_name' => '分类名称',
	                    'season_code' => '季节代码',
	                    'season_name' => '季节名称',
	                    'series_code' => '系列代码',
	                    'series_name' => '系列名称',
	                    'unit_code' => '单位代码',
	                    'unit_name' => '单位名称',
	                    'year' => '年份',
	                    'custom_col1' => '自定义列1',
	                    'custom_col2' => '自定义列2',
	                    'custom_col3' => '自定义列3',
	                    'custom_col4' => '自定义列4',
	                    'custom_col5' => '自定义列5',
	                    'property_value1' => '属性1',
	                    'property_value2' => '属性2',
	                    'property_value3' => '属性3',
	                    'property_value4' => '属性4',
	                    'property_value5' => '属性5',
	                    'property_value6' => '属性6',
                    ),
                    'ext_col' => 'size_code',
                    'ext_extra_process_data' => array(
                        'num' => 'plus',
                        'money' => 'plus',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
	                    'brand_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'brand_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'brand_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_brand_name_by_goods_code'
	                    ),
	                    'sort_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'sort_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'sort_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_sort_name_by_goods_code'
	                    ),
	                    'category_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'category_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'category_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_category_name_by_goods_code'
	                    ),
	                    'season_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'season_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'season_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_season_name_by_goods_code'
	                    ),
	                    'series_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'series_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'series_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_series_name_by_goods_code'
	                    ),
	                    'unit_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'unit_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'unit_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_unit_name_by_goods_code'
	                    ),
	                    'year' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_year_by_goods_code',
	                    ),
	                    'property_value1' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value1_by_goods_id'
	                    ),
	                    'property_value2' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value2_by_goods_id'
	                    ),
	                    'property_value3' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value3_by_goods_id'
	                    ),
	                    'property_value4' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value4_by_goods_id'
	                    ),
	                    'property_value5' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value5_by_goods_id'
	                    ),
	                    'property_value6' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value6_by_goods_id'
	                    ),
                    ),
                ),
                //打印财务明细
                'finance_area' => array(
                    'type' => 'list',
                    'title' => '账务信息',
                    'table' => 'acc_distributor_general_journal',
                    'class' => 'MdlDistributorGeneralJournal',
                    'class_path' => 'acc/distributor_general_journal',
                    'class_function' => 'get_list_by_distributor_code',
                    'relation_table' => array(
                        'table' => 'wbm_wholesale_record',
                        'class' => 'MdlWholesaleRecord',
                        'class_path' => 'wbm/wholesale_record',
                        'class_function' => 'get_info_by_id',
                        'relation_col' => 'distributor_code',
                    ),
                    'data' => array(
                        'distributor_code' => '客户代码',
                        'distributor_name' => '客户名称',
                        'record_type' => '单据类型',
                        'relation_code' => '关联单号',
                        'before_pay_money' => '期初应收款',
                        'change_pay_money' => '变动应收款',
                        'after_pay_money' => '期末应收款',
                        'before_balance_money' => '期初余额',
                        'change_balance_money' => '变动余额',
                        'after_balance_money' => '期末余额',
                        'before_debts_money' => '期初欠款',
                        'change_debts_money' => '变动欠款',
                        'after_debts_money' => '期末欠款'
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_distributor_name_by_code'
                        ),
                        'record_type' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'record_type', //关联字段
                            'function' => 'get_record_type_name', //获取的方法
                        ),
                        'before_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_pay_money',
                            'function' => 'format_money',
                        ),
                        'change_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_pay_money',
                            'function' => 'format_money',
                        ),
                        'after_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_pay_money',
                            'function' => 'format_money'
                        ),
                        'before_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_balance_money',
                            'function' => 'format_money',
                        ),
                        'change_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_balance_money',
                            'function' => 'format_money',
                        ),
                        'after_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_balance_money',
                            'function' => 'format_money'
                        ),
                        'before_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_debts_money',
                            'function' => 'format_money',
                        ),
                        'change_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_debts_money',
                            'function' => 'format_money',
                        ),
                        'after_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_debts_money',
                            'function' => 'format_money'
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'wbm_wholesale_record',
                    'class' => 'MdlWholesaleRecord',
                    'class_path' => 'wbm/wholesale_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_check_person' => '审核人',
                        'is_check_time' => '审核时间',
                        'is_execute_person' => '执行人',
                        'is_execute_time' => '执行时间',
//						'is_cancel_person' => '作废人',
//						'is_cancel_time' => '作废时间',
                        'is_stop_person' => '终止人',
                        'is_stop_time' => '终止时间',
                        'is_finish_person' => '完成人',
                        'is_finish_time' => '完成时间'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_check_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_check_time',
                            'function' => 'format_day_time'
                        ),
                        'is_execute_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_execute_time',
                            'function' => 'format_day_time'
                        ),
//						'is_cancel_time' => array(
//							'type'=>'format_day_time', //格式化时间
//							'relation_col'=>'is_cancel_time',
//							'function'=>'format_day_time'
//						),
                        'is_stop_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_stop_time',
                            'function' => 'format_day_time'
                        ),
                        'is_finish_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_finish_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),
        /////
        //批发销货单标准打印
        'wbm_store_out_record' => array(
            'title' => '批发销货单',
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '批发销货单信息',
                    'table' => 'wbm_store_out_record',
                    'class' => 'MdlStoreOutRecord',
                    'class_path' => 'wbm/store_out_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'distributor_code' => '客户代码',
                        'distributor_name' => '客户名称',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'finance_pay_money' => '应收款',
                        'finance_pay_money_up' => '应收款(大写)',
                        'finance_balance_money' => '结余款',
                        'finance_balance_money_up' => '结余款(大写)',
                        'finance_debts_money' => '累计欠款',
                        'finance_debts_money_up' => '累计欠款(大写)',
	                    'before_debts_money' => '上次欠款',
	                    'before_debts_money_up' => '上次欠款(大写)',
	                    'after_debts_money' => '当日欠款',
	                    'after_debts_money_up' => '当日欠款(大写)',
                        'finance_credit_limit' => '信用额度',
                        'finance_credit_limit_up' => '信用额度(大写)',
                        'finance_bond' => '保证金',
                        'finance_bond_up' => '保证金(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_store_out_person' => '出库人',
                        'is_store_out_time' => '出库时间',
                        'is_keep_accounts_person' => '记账人',
                        'is_keep_accounts_time' => '记账时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_distributor_name_by_code'
                        ),
                        'finance_pay_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_pay_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_balance_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_distributor_general_ledger_by_code',
                        ),
                        'finance_balance_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_debts_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_debts_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_credit_limit' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'credit_limit',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_credit_limit_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'credit_limit',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_bond' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'bond',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_bond_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'bond',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_out_time',
                            'function' => 'format_day_time'
                        ),
                        'is_keep_accounts_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_keep_accounts_time',
                            'function' => 'format_day_time'
                        ),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        ),
	                    'before_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code'
	                    ),
	                    'before_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),
	                    'after_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code'
	                    ),
	                    'after_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),
                    ),
                ),
                //客户信息
                'distributor_area' => array(
                    'type' => 'grid',
                    'title' => '客户信息',
                    'table' => 'wbm_store_out_record',
                    'class' => 'MdlStoreOutRecord',
                    'class_path' => 'wbm/store_out_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'distributor_code' => '客户代码',
                        'distributor_name' => '客户名称',
                        'distributor_contact_person' => '客户联系人',
                        'distributor_email' => '客户邮箱',
                        'distributor_phone' => '客户电话',
                        'distributor_tel' => '客户手机',
                        'distributor_address' => '客户地址',
                        'distributor_fax' => '客户传真',
                        'distributor_zipcode' => '客户邮编',
                        'distributor_website' => '客户公司网站',
                        'distributor_bank' => '客户开户银行',
                        'distributor_account' => '客户账号',
                        'distributor_account_name' => '客户账号名称',
                        'distributor_legal_person' => '客户法人',
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_distributor_name_by_code'
                        ),
                        'distributor_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'email',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'address',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'website',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'account',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_legal_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'legal_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                    ),
                ),
                //商店信息
                'shop_area' => array(
                    'type' => 'grid',
                    'title' => '商店信息',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'is_shop_print' => true, //商店级别
                    'data' => array(
                        'distributor_name' => '商店名称',
                        'distributor_contact_person' => '商店联系人',
                        'distributor_email' => '商店邮箱',
                        'distributor_phone' => '商店电话',
                        'distributor_tel' => '商店手机',
                        'distributor_address' => '商店地址',
                        'distributor_fax' => '商店传真',
                        'distributor_zipcode' => '商店邮编',
                        'distributor_website' => '商店公司网站',
                        'distributor_bank' => '商店开户银行',
                        'distributor_account' => '商店账号',
                        'distributor_account_name' => '商店账号名称',
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'distributor_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'email',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'address',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'website',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_distributor_info_by_code'
                        ),
                    ),
                ),
                //打印明细
                'detail_area' => array(
                    'type' => 'list',
                    'title' => '明细区',
                    'table' => 'wbm_store_out_record_detail',
                    'class' => 'MdlStoreOutRecord',
                    'class_path' => 'wbm/store_out_record',
                    'class_function' => 'get_detail_list_by_pid',
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码代码',
                        'size_name' => '尺码名称',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                        'brand_code' => '品牌代码',
                        'brand_name' => '品牌名称',
                        'sort_code' => '品类代码',
                        'sort_name' => '品类名称',
                        'category_code' => '分类代码',
                        'category_name' => '分类名称',
                        'season_code' => '季节代码',
                        'season_name' => '季节名称',
                        'series_code' => '系列代码',
                        'series_name' => '系列名称',
                        'unit_code' => '单位代码',
                        'unit_name' => '单位名称',
                        'year' => '年份',
	                    'custom_col1' => '自定义列1',
	                    'custom_col2' => '自定义列2',
	                    'custom_col3' => '自定义列3',
	                    'custom_col4' => '自定义列4',
	                    'custom_col5' => '自定义列5',
                        'property_value1' => '属性1',
                        'property_value2' => '属性2',
                        'property_value3' => '属性3',
                        'property_value4' => '属性4',
                        'property_value5' => '属性5',
                        'property_value6' => '属性6',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'size_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'size_code',
                            'function' => 'get_size_name_by_code',
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'brand_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'brand_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'brand_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_brand_name_by_goods_code'
                        ),
                        'sort_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'sort_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'sort_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_sort_name_by_goods_code'
                        ),
                        'category_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'category_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'category_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_category_name_by_goods_code'
                        ),
                        'season_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'season_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'season_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_season_name_by_goods_code'
                        ),
                        'series_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'series_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'series_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_series_name_by_goods_code'
                        ),
                        'unit_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'unit_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'unit_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_unit_name_by_goods_code'
                        ),
                        'year' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_year_by_goods_code',
                        ),
                        'property_value1' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value1_by_goods_id'
                        ),
                        'property_value2' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value2_by_goods_id'
                        ),
                        'property_value3' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value3_by_goods_id'
                        ),
                        'property_value4' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value4_by_goods_id'
                        ),
                        'property_value5' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value5_by_goods_id'
                        ),
                        'property_value6' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value6_by_goods_id'
                        ),
                    ),
                ),
                //打印财务明细
                'finance_area' => array(
                    'type' => 'list',
                    'title' => '账务信息',
                    'table' => 'acc_distributor_general_journal',
                    'class' => 'MdlDistributorGeneralJournal',
                    'class_path' => 'acc/distributor_general_journal',
                    'class_function' => 'get_list_by_distributor_code',
                    'relation_table' => array(
                        'table' => 'wbm_store_out_record',
                        'class' => 'MdlStoreOutRecord',
                        'class_path' => 'wbm/store_out_record',
                        'class_function' => 'get_info_by_id',
                        'relation_col' => 'distributor_code',
                    ),
                    'data' => array(
                        'distributor_code' => '客户代码',
                        'distributor_name' => '客户名称',
                        'record_type' => '单据类型',
                        'relation_code' => '关联单号',
                        'before_pay_money' => '期初应收款',
                        'change_pay_money' => '变动应收款',
                        'after_pay_money' => '期末应收款',
                        'before_balance_money' => '期初余额',
                        'change_balance_money' => '变动余额',
                        'after_balance_money' => '期末余额',
                        'before_debts_money' => '期初欠款',
                        'change_debts_money' => '变动欠款',
                        'after_debts_money' => '期末欠款'
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_distributor_name_by_code'
                        ),
                        'record_type' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'record_type', //关联字段
                            'function' => 'get_record_type_name', //获取的方法
                        ),
                        'before_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_pay_money',
                            'function' => 'format_money',
                        ),
                        'change_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_pay_money',
                            'function' => 'format_money',
                        ),
                        'after_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_pay_money',
                            'function' => 'format_money'
                        ),
                        'before_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_balance_money',
                            'function' => 'format_money',
                        ),
                        'change_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_balance_money',
                            'function' => 'format_money',
                        ),
                        'after_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_balance_money',
                            'function' => 'format_money'
                        ),
                        'before_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_debts_money',
                            'function' => 'format_money',
                        ),
                        'change_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_debts_money',
                            'function' => 'format_money',
                        ),
                        'after_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_debts_money',
                            'function' => 'format_money'
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'wbm_store_out_record',
                    'class' => 'MdlStoreOutRecord',
                    'class_path' => 'wbm/store_out_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_store_out_person' => '出库人',
                        'is_store_out_time' => '出库时间',
                        'is_keep_accounts_person' => '记账人',
                        'is_keep_accounts_time' => '记账时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_out_time',
                            'function' => 'format_day_time'
                        ),
                        'is_keep_accounts_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_keep_accounts_time',
                            'function' => 'format_day_time'
                        ),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),
        //批发销货扩展打印
        'ext_wbm_store_out_record' => array(
            'title' => '批发销货单',
            'property' => 'record_ext', //单据明细扩展打印
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '批发销货单信息',
                    'table' => 'wbm_store_out_record',
                    'class' => 'MdlStoreOutRecord',
                    'class_path' => 'wbm/store_out_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'distributor_code' => '客户代码',
                        'distributor_name' => '客户名称',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'finance_pay_money' => '应收款',
                        'finance_pay_money_up' => '应收款(大写)',
                        'finance_balance_money' => '结余款',
                        'finance_balance_money_up' => '结余款(大写)',
                        'finance_debts_money' => '累计欠款',
                        'finance_debts_money_up' => '累计欠款(大写)',
	                    'before_debts_money' => '上次欠款',
	                    'before_debts_money_up' => '上次欠款(大写)',
	                    'after_debts_money' => '当日欠款',
	                    'after_debts_money_up' => '当日欠款(大写)',
                        'finance_credit_limit' => '信用额度',
                        'finance_credit_limit_up' => '信用额度(大写)',
                        'finance_bond' => '保证金',
                        'finance_bond_up' => '保证金(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_store_out_person' => '出库人',
                        'is_store_out_time' => '出库时间',
                        'is_keep_accounts_person' => '记账人',
                        'is_keep_accounts_time' => '记账时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_distributor_name_by_code'
                        ),
                        'finance_pay_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_pay_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_balance_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_balance_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_debts_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_debts_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_credit_limit' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'credit_limit',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_credit_limit_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'credit_limit',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_bond' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'bond',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_bond_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'bond',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_out_time',
                            'function' => 'format_day_time'
                        ),
                        'is_keep_accounts_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_keep_accounts_time',
                            'function' => 'format_day_time'
                        ),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        ),
	                    'before_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code'
	                    ),
	                    'before_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),
	                    'after_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code'
	                    ),
	                    'after_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),

                    ),
                ),
                //客户信息
                'distributor_area' => array(
                    'type' => 'grid',
                    'title' => '客户信息',
                    'table' => 'wbm_store_out_record',
                    'class' => 'MdlStoreOutRecord',
                    'class_path' => 'wbm/store_out_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'distributor_code' => '客户代码',
                        'distributor_name' => '客户名称',
                        'distributor_contact_person' => '客户联系人',
                        'distributor_email' => '客户邮箱',
                        'distributor_phone' => '客户电话',
                        'distributor_tel' => '客户手机',
                        'distributor_address' => '客户地址',
                        'distributor_fax' => '客户传真',
                        'distributor_zipcode' => '客户邮编',
                        'distributor_website' => '客户公司网站',
                        'distributor_bank' => '客户开户银行',
                        'distributor_account' => '客户账号',
                        'distributor_account_name' => '客户账号名称',
                        'distributor_legal_person' => '客户法人',
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_distributor_name_by_code'
                        ),
                        'distributor_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'email',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'address',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'website',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'account',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_legal_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'legal_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                    ),
                ),
                //商店信息
                'shop_area' => array(
                    'type' => 'grid',
                    'title' => '商店信息',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'is_shop_print' => true, //商店级别
                    'data' => array(
                        'distributor_name' => '商店名称',
                        'distributor_contact_person' => '商店联系人',
                        'distributor_email' => '商店邮箱',
                        'distributor_phone' => '商店电话',
                        'distributor_tel' => '商店手机',
                        'distributor_address' => '商店地址',
                        'distributor_fax' => '商店传真',
                        'distributor_zipcode' => '商店邮编',
                        'distributor_website' => '商店公司网站',
                        'distributor_bank' => '商店开户银行',
                        'distributor_account' => '商店账号',
                        'distributor_account_name' => '商店账号名称',
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'distributor_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'email',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'address',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'website',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_distributor_info_by_code'
                        ),
                    ),
                ),
                //扩展打印明细
                'ext_detail_area' => array(
                    'type' => 'list',
                    'title' => '扩展明细区',
                    'table' => 'wbm_store_out_record_detail',
                    'class' => 'MdlStoreOutRecord',
                    'class_path' => 'wbm/store_out_record',
                    'class_function' => 'get_detail_list_by_pid',
                    'property' => 'record_ext', //单据明细扩展打印
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码名称[扩展]',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                        'brand_code' => '品牌代码',
                        'brand_name' => '品牌名称',
                        'sort_code' => '品类代码',
                        'sort_name' => '品类名称',
                        'category_code' => '分类代码',
                        'category_name' => '分类名称',
                        'season_code' => '季节代码',
                        'season_name' => '季节名称',
                        'series_code' => '系列代码',
                        'series_name' => '系列名称',
                        'unit_code' => '单位代码',
                        'unit_name' => '单位名称',
                        'year' => '年份',
	                    'custom_col1' => '自定义列1',
	                    'custom_col2' => '自定义列2',
	                    'custom_col3' => '自定义列3',
	                    'custom_col4' => '自定义列4',
	                    'custom_col5' => '自定义列5',
                        'property_value1' => '属性1',
                        'property_value2' => '属性2',
                        'property_value3' => '属性3',
                        'property_value4' => '属性4',
                        'property_value5' => '属性5',
                        'property_value6' => '属性6',
                    ),
                    'ext_col' => 'size_code',
                    'ext_extra_process_data' => array(
                        'num' => 'plus',
                        'money' => 'plus',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'brand_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'brand_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'brand_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_brand_name_by_goods_code'
                        ),
                        'sort_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'sort_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'sort_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_sort_name_by_goods_code'
                        ),
                        'category_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'category_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'category_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_category_name_by_goods_code'
                        ),
                        'season_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'season_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'season_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_season_name_by_goods_code'
                        ),
                        'series_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'series_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'series_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_series_name_by_goods_code'
                        ),
                        'unit_code' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'goods_code',
                            'get_array_key' => 'unit_code',
                            'function' => 'get_goods_by_code'
                        ),
                        'unit_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_unit_name_by_goods_code'
                        ),
                        'year' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_code',
                            'function' => 'get_year_by_goods_code',
                        ),
                        'property_value1' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value1_by_goods_id'
                        ),
                        'property_value2' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value2_by_goods_id'
                        ),
                        'property_value3' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value3_by_goods_id'
                        ),
                        'property_value4' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value4_by_goods_id'
                        ),
                        'property_value5' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value5_by_goods_id'
                        ),
                        'property_value6' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'goods_id',
                            'function' => 'get_property_value6_by_goods_id'
                        ),
                    ),
                ),
                //打印财务明细
                'finance_area' => array(
                    'type' => 'list',
                    'title' => '账务信息',
                    'table' => 'acc_distributor_general_journal',
                    'class' => 'MdlDistributorGeneralJournal',
                    'class_path' => 'acc/distributor_general_journal',
                    'class_function' => 'get_list_by_distributor_code',
                    'relation_table' => array(
                        'table' => 'wbm_store_out_record',
                        'class' => 'MdlStoreOutRecord',
                        'class_path' => 'wbm/store_out_record',
                        'class_function' => 'get_info_by_id',
                        'relation_col' => 'distributor_code',
                    ),
                    'data' => array(
                        'distributor_code' => '客户代码',
                        'distributor_name' => '客户名称',
                        'record_type' => '单据类型',
                        'relation_code' => '关联单号',
                        'before_pay_money' => '期初应收款',
                        'change_pay_money' => '变动应收款',
                        'after_pay_money' => '期末应收款',
                        'before_balance_money' => '期初余额',
                        'change_balance_money' => '变动余额',
                        'after_balance_money' => '期末余额',
                        'before_debts_money' => '期初欠款',
                        'change_debts_money' => '变动欠款',
                        'after_debts_money' => '期末欠款'
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_distributor_name_by_code'
                        ),
                        'record_type' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'record_type', //关联字段
                            'function' => 'get_record_type_name', //获取的方法
                        ),
                        'before_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_pay_money',
                            'function' => 'format_money',
                        ),
                        'change_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_pay_money',
                            'function' => 'format_money',
                        ),
                        'after_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_pay_money',
                            'function' => 'format_money'
                        ),
                        'before_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_balance_money',
                            'function' => 'format_money',
                        ),
                        'change_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_balance_money',
                            'function' => 'format_money',
                        ),
                        'after_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_balance_money',
                            'function' => 'format_money'
                        ),
                        'before_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_debts_money',
                            'function' => 'format_money',
                        ),
                        'change_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_debts_money',
                            'function' => 'format_money',
                        ),
                        'after_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_debts_money',
                            'function' => 'format_money'
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'wbm_store_out_record',
                    'class' => 'MdlStoreOutRecord',
                    'class_path' => 'wbm/store_out_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_store_out_person' => '出库人',
                        'is_store_out_time' => '出库时间',
                        'is_keep_accounts_person' => '记账人',
                        'is_keep_accounts_time' => '记账时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_out_time',
                            'function' => 'format_day_time'
                        ),
                        'is_keep_accounts_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_keep_accounts_time',
                            'function' => 'format_day_time'
                        ),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),
        //批发退货单标准打印
        'wbm_return_record' => array(
            'title' => '批发退货单',
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '退货单信息',
                    'table' => 'wbm_return_record',
                    'class' => 'MdlWbmReturnRecord',
                    'class_path' => 'wbm/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'distributor_code' => '客户代码',
                        'distributor_name' => '客户名称',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'finance_pay_money' => '应收款',
                        'finance_pay_money_up' => '应收款(大写)',
                        'finance_balance_money' => '结余款',
                        'finance_balance_money_up' => '结余款(大写)',
                        'finance_debts_money' => '累计欠款',
                        'finance_debts_money_up' => '累计欠款(大写)',
	                    'before_debts_money' => '上次欠款',
	                    'before_debts_money_up' => '上次欠款(大写)',
	                    'after_debts_money' => '当日欠款',
	                    'after_debts_money_up' => '当日欠款(大写)',
                        'finance_credit_limit' => '信用额度',
                        'finance_credit_limit_up' => '信用额度(大写)',
                        'finance_bond' => '保证金',
                        'finance_bond_up' => '保证金(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_store_in_person' => '入库人',
                        'is_store_in_time' => '入库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_keep_accounts_person' => '记账人',
                        'is_keep_accounts_time' => '记账时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_distributor_name_by_code'
                        ),
                        'finance_pay_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_pay_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_balance_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_balance_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_debts_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_debts_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_credit_limit' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'credit_limit',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_credit_limit_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'credit_limit',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_bond' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'bond',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_bond_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'bond',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_keep_accounts_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_keep_accounts_time',
                            'function' => 'format_day_time'
                        ),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        ),
	                    'before_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code'
	                    ),
	                    'before_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),
	                    'after_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code'
	                    ),
	                    'after_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),
                    ),
                ),
                //客户信息
                'distributor_area' => array(
                    'type' => 'grid',
                    'title' => '客户信息',
                    'table' => 'wbm_return_record',
                    'class' => 'MdlWbmReturnRecord',
                    'class_path' => 'wbm/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'distributor_code' => '客户代码',
                        'distributor_name' => '客户名称',
                        'distributor_contact_person' => '客户联系人',
                        'distributor_email' => '客户邮箱',
                        'distributor_phone' => '客户电话',
                        'distributor_tel' => '客户手机',
                        'distributor_address' => '客户地址',
                        'distributor_fax' => '客户传真',
                        'distributor_zipcode' => '客户邮编',
                        'distributor_website' => '客户公司网站',
                        'distributor_bank' => '客户开户银行',
                        'distributor_account' => '客户账号',
                        'distributor_account_name' => '客户账号名称',
                        'distributor_legal_person' => '客户法人',
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_distributor_name_by_code'
                        ),
                        'distributor_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'email',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'address',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'website',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'account',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_legal_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'legal_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                    ),
                ),
                //商店信息
                'shop_area' => array(
                    'type' => 'grid',
                    'title' => '商店信息',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'is_shop_print' => true, //商店级别
                    'data' => array(
                        'distributor_name' => '商店名称',
                        'distributor_contact_person' => '商店联系人',
                        'distributor_email' => '商店邮箱',
                        'distributor_phone' => '商店电话',
                        'distributor_tel' => '商店手机',
                        'distributor_address' => '商店地址',
                        'distributor_fax' => '商店传真',
                        'distributor_zipcode' => '商店邮编',
                        'distributor_website' => '商店公司网站',
                        'distributor_bank' => '商店开户银行',
                        'distributor_account' => '商店账号',
                        'distributor_account_name' => '商店账号名称',
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'distributor_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'email',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'address',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'website',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_distributor_info_by_code'
                        ),
                    ),
                ),
                //打印明细
                'detail_area' => array(
                    'type' => 'list',
                    'title' => '明细区',
                    'table' => 'wbm_return_record_detail',
                    'class' => 'MdlWbmReturnRecord',
                    'class_path' => 'wbm/return_record',
                    'class_function' => 'get_detail_list_by_pid',
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码代码',
                        'size_name' => '尺码名称',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
	                    'brand_code' => '品牌代码',
	                    'brand_name' => '品牌名称',
	                    'sort_code' => '品类代码',
	                    'sort_name' => '品类名称',
	                    'category_code' => '分类代码',
	                    'category_name' => '分类名称',
	                    'season_code' => '季节代码',
	                    'season_name' => '季节名称',
	                    'series_code' => '系列代码',
	                    'series_name' => '系列名称',
	                    'unit_code' => '单位代码',
	                    'unit_name' => '单位名称',
	                    'year' => '年份',
	                    'custom_col1' => '自定义列1',
	                    'custom_col2' => '自定义列2',
	                    'custom_col3' => '自定义列3',
	                    'custom_col4' => '自定义列4',
	                    'custom_col5' => '自定义列5',
	                    'property_value1' => '属性1',
	                    'property_value2' => '属性2',
	                    'property_value3' => '属性3',
	                    'property_value4' => '属性4',
	                    'property_value5' => '属性5',
	                    'property_value6' => '属性6',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'size_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'size_code',
                            'function' => 'get_size_name_by_code',
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
	                    'brand_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'brand_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'brand_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_brand_name_by_goods_code'
	                    ),
	                    'sort_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'sort_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'sort_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_sort_name_by_goods_code'
	                    ),
	                    'category_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'category_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'category_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_category_name_by_goods_code'
	                    ),
	                    'season_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'season_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'season_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_season_name_by_goods_code'
	                    ),
	                    'series_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'series_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'series_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_series_name_by_goods_code'
	                    ),
	                    'unit_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'unit_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'unit_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_unit_name_by_goods_code'
	                    ),
	                    'year' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_year_by_goods_code',
	                    ),
	                    'property_value1' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value1_by_goods_id'
	                    ),
	                    'property_value2' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value2_by_goods_id'
	                    ),
	                    'property_value3' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value3_by_goods_id'
	                    ),
	                    'property_value4' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value4_by_goods_id'
	                    ),
	                    'property_value5' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value5_by_goods_id'
	                    ),
	                    'property_value6' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value6_by_goods_id'
	                    ),
                    ),
                ),
                //打印财务明细
                'finance_area' => array(
                    'type' => 'list',
                    'title' => '账务信息',
                    'table' => 'acc_distributor_general_journal',
                    'class' => 'MdlDistributorGeneralJournal',
                    'class_path' => 'acc/distributor_general_journal',
                    'class_function' => 'get_list_by_distributor_code',
                    'relation_table' => array(
                        'table' => 'wbm_return_record',
                        'class' => 'MdlWbmReturnRecord',
                        'class_path' => 'wbm/return_record',
                        'class_function' => 'get_info_by_id',
                        'relation_col' => 'distributor_code',
                    ),
                    'data' => array(
                        'distributor_code' => '客户代码',
                        'distributor_name' => '客户名称',
                        'record_type' => '单据类型',
                        'relation_code' => '关联单号',
                        'before_pay_money' => '期初应收款',
                        'change_pay_money' => '变动应收款',
                        'after_pay_money' => '期末应收款',
                        'before_balance_money' => '期初余额',
                        'change_balance_money' => '变动余额',
                        'after_balance_money' => '期末余额',
                        'before_debts_money' => '期初欠款',
                        'change_debts_money' => '变动欠款',
                        'after_debts_money' => '期末欠款'
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_distributor_name_by_code'
                        ),
                        'record_type' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'record_type', //关联字段
                            'function' => 'get_record_type_name', //获取的方法
                        ),
                        'before_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_pay_money',
                            'function' => 'format_money',
                        ),
                        'change_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_pay_money',
                            'function' => 'format_money',
                        ),
                        'after_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_pay_money',
                            'function' => 'format_money'
                        ),
                        'before_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_balance_money',
                            'function' => 'format_money',
                        ),
                        'change_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_balance_money',
                            'function' => 'format_money',
                        ),
                        'after_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_balance_money',
                            'function' => 'format_money'
                        ),
                        'before_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_debts_money',
                            'function' => 'format_money',
                        ),
                        'change_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_debts_money',
                            'function' => 'format_money',
                        ),
                        'after_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_debts_money',
                            'function' => 'format_money'
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'wbm_return_record',
                    'class' => 'MdlWbmReturnRecord',
                    'class_path' => 'wbm/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_store_in_person' => '入库人',
                        'is_store_in_time' => '入库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_keep_accounts_person' => '记账人',
                        'is_keep_accounts_time' => '记账时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_keep_accounts_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_keep_accounts_time',
                            'function' => 'format_day_time'
                        ),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),
        //批发退货单扩展打印
        'ext_wbm_return_record' => array(
            'title' => '批发退货单',
            'property' => 'record_ext', //单据明细扩展打印
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '退货单信息',
                    'table' => 'wbm_return_record',
                    'class' => 'MdlWbmReturnRecord',
                    'class_path' => 'wbm/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'distributor_code' => '客户代码',
                        'distributor_name' => '客户名称',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'finance_pay_money' => '应收款',
                        'finance_pay_money_up' => '应收款(大写)',
                        'finance_balance_money' => '结余款',
                        'finance_balance_money_up' => '结余款(大写)',
                        'finance_debts_money' => '累计欠款',
                        'finance_debts_money_up' => '累计欠款(大写)',
	                    'before_debts_money' => '上次欠款',
	                    'before_debts_money_up' => '上次欠款(大写)',
	                    'after_debts_money' => '当日欠款',
	                    'after_debts_money_up' => '当日欠款(大写)',
                        'finance_credit_limit' => '信用额度',
                        'finance_credit_limit_up' => '信用额度(大写)',
                        'finance_bond' => '保证金',
                        'finance_bond_up' => '保证金(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_store_in_person' => '入库人',
                        'is_store_in_time' => '入库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_keep_accounts_person' => '记账人',
                        'is_keep_accounts_time' => '记账时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_distributor_name_by_code'
                        ),
                        'finance_pay_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_pay_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'pay_money',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_balance_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_balance_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'balance_money',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_debts_money' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_debts_money_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'debts_money',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_credit_limit' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'credit_limit',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_credit_limit_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'credit_limit',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'finance_bond' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'bond',
                            'function' => 'get_distributor_general_ledger_by_code'
                        ),
                        'finance_bond_up' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'bond',
                            'function' => 'get_distributor_general_ledger_by_code',
                            'second_function' => 'num2rmb'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_keep_accounts_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_keep_accounts_time',
                            'function' => 'format_day_time'
                        ),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        ),
	                    'before_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code'
	                    ),
	                    'before_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'before_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),
	                    'after_debts_money' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code'
	                    ),
	                    'after_debts_money_up' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'function' => 'format_money',
		                    'get_col' => 'after_debts_money',
		                    'relation_model_params' => 'distributor_code, record_code',
		                    'relation_model_class' => 'MdlDistributorGeneralJournal',
		                    'relation_model_path' => 'acc/distributor_general_journal',
		                    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code',
		                    'second_function' => 'num2rmb'
	                    ),
                    ),
                ),
                //客户信息
                'distributor_area' => array(
                    'type' => 'grid',
                    'title' => '客户信息',
                    'table' => 'wbm_return_record',
                    'class' => 'MdlWbmReturnRecord',
                    'class_path' => 'wbm/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'distributor_code' => '客户代码',
                        'distributor_name' => '客户名称',
                        'distributor_contact_person' => '客户联系人',
                        'distributor_email' => '客户邮箱',
                        'distributor_phone' => '客户电话',
                        'distributor_tel' => '客户手机',
                        'distributor_address' => '客户地址',
                        'distributor_fax' => '客户传真',
                        'distributor_zipcode' => '客户邮编',
                        'distributor_website' => '客户公司网站',
                        'distributor_bank' => '客户开户银行',
                        'distributor_account' => '客户账号',
                        'distributor_account_name' => '客户账号名称',
                        'distributor_legal_person' => '客户法人',
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_distributor_name_by_code'
                        ),
                        'distributor_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'email',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'address',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'website',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'account',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_legal_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'legal_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                    ),
                ),
                //商店信息
                'shop_area' => array(
                    'type' => 'grid',
                    'title' => '商店信息',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'is_shop_print' => true, //商店级别
                    'data' => array(
                        'distributor_name' => '商店名称',
                        'distributor_contact_person' => '商店联系人',
                        'distributor_email' => '商店邮箱',
                        'distributor_phone' => '商店电话',
                        'distributor_tel' => '商店手机',
                        'distributor_address' => '商店地址',
                        'distributor_fax' => '商店传真',
                        'distributor_zipcode' => '商店邮编',
                        'distributor_website' => '商店公司网站',
                        'distributor_bank' => '商店开户银行',
                        'distributor_account' => '商店账号',
                        'distributor_account_name' => '商店账号名称',
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'distributor_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'email',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'address',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'website',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_distributor_info_by_code'
                        ),
                    ),
                ),
                //扩展打印明细
                'ext_detail_area' => array(
                    'type' => 'list',
                    'title' => '扩展明细区',
                    'table' => 'wbm_return_record_detail',
                    'class' => 'MdlWbmReturnRecord',
                    'class_path' => 'wbm/return_record',
                    'class_function' => 'get_detail_list_by_pid',
                    'property' => 'record_ext', //单据明细扩展打印
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码名称[扩展]',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
    	                'brand_code' => '品牌代码',
	                    'brand_name' => '品牌名称',
	                    'sort_code' => '品类代码',
	                    'sort_name' => '品类名称',
	                    'category_code' => '分类代码',
	                    'category_name' => '分类名称',
	                    'season_code' => '季节代码',
	                    'season_name' => '季节名称',
	                    'series_code' => '系列代码',
	                    'series_name' => '系列名称',
	                    'unit_code' => '单位代码',
	                    'unit_name' => '单位名称',
	                    'year' => '年份',
	                    'custom_col1' => '自定义列1',
	                    'custom_col2' => '自定义列2',
	                    'custom_col3' => '自定义列3',
	                    'custom_col4' => '自定义列4',
	                    'custom_col5' => '自定义列5',
	                    'property_value1' => '属性1',
	                    'property_value2' => '属性2',
	                    'property_value3' => '属性3',
	                    'property_value4' => '属性4',
	                    'property_value5' => '属性5',
	                    'property_value6' => '属性6',
                    ),
                    'ext_col' => 'size_code',
                    'ext_extra_process_data' => array(
                        'num' => 'plus',
                        'money' => 'plus',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
	                    'brand_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'brand_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'brand_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_brand_name_by_goods_code'
	                    ),
	                    'sort_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'sort_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'sort_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_sort_name_by_goods_code'
	                    ),
	                    'category_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'category_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'category_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_category_name_by_goods_code'
	                    ),
	                    'season_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'season_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'season_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_season_name_by_goods_code'
	                    ),
	                    'series_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'series_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'series_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_series_name_by_goods_code'
	                    ),
	                    'unit_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'unit_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'unit_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_unit_name_by_goods_code'
	                    ),
	                    'year' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_year_by_goods_code',
	                    ),
	                    'property_value1' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value1_by_goods_id'
	                    ),
	                    'property_value2' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value2_by_goods_id'
	                    ),
	                    'property_value3' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value3_by_goods_id'
	                    ),
	                    'property_value4' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value4_by_goods_id'
	                    ),
	                    'property_value5' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value5_by_goods_id'
	                    ),
	                    'property_value6' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value6_by_goods_id'
	                    ),
                    ),
                ),
                //打印财务明细
                'finance_area' => array(
                    'type' => 'list',
                    'title' => '账务信息',
                    'table' => 'acc_distributor_general_journal',
                    'class' => 'MdlDistributorGeneralJournal',
                    'class_path' => 'acc/distributor_general_journal',
                    'class_function' => 'get_list_by_distributor_code',
                    'relation_table' => array(
                        'table' => 'wbm_return_record',
                        'class' => 'MdlWbmReturnRecord',
                        'class_path' => 'wbm/return_record',
                        'class_function' => 'get_info_by_id',
                        'relation_col' => 'distributor_code',
                    ),
                    'data' => array(
                        'distributor_code' => '客户代码',
                        'distributor_name' => '客户名称',
                        'record_type' => '单据类型',
                        'relation_code' => '关联单号',
                        'before_pay_money' => '期初应收款',
                        'change_pay_money' => '变动应收款',
                        'after_pay_money' => '期末应收款',
                        'before_balance_money' => '期初余额',
                        'change_balance_money' => '变动余额',
                        'after_balance_money' => '期末余额',
                        'before_debts_money' => '期初欠款',
                        'change_debts_money' => '变动欠款',
                        'after_debts_money' => '期末欠款'
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_distributor_name_by_code'
                        ),
                        'record_type' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'record_type', //关联字段
                            'function' => 'get_record_type_name', //获取的方法
                        ),
                        'before_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_pay_money',
                            'function' => 'format_money',
                        ),
                        'change_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_pay_money',
                            'function' => 'format_money',
                        ),
                        'after_pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_pay_money',
                            'function' => 'format_money'
                        ),
                        'before_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_balance_money',
                            'function' => 'format_money',
                        ),
                        'change_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_balance_money',
                            'function' => 'format_money',
                        ),
                        'after_balance_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_balance_money',
                            'function' => 'format_money'
                        ),
                        'before_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'before_debts_money',
                            'function' => 'format_money',
                        ),
                        'change_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_debts_money',
                            'function' => 'format_money',
                        ),
                        'after_debts_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'after_debts_money',
                            'function' => 'format_money'
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'wbm_return_record',
                    'class' => 'MdlWbmReturnRecord',
                    'class_path' => 'wbm/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_store_in_person' => '入库人',
                        'is_store_in_time' => '入库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_keep_accounts_person' => '记账人',
                        'is_keep_accounts_time' => '记账时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_keep_accounts_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_keep_accounts_time',
                            'function' => 'format_day_time'
                        ),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),

	    //商店配货订单条码(特殊)
	    'dim_allocate_order_record_barcode' => array(
		    'title' => '商店配货订单条码',
		    'main_conf' => array(
//				'page_top_area' => array(
//					'type'=>'grid',
//					'title'=>'页眉',
//					'table'=>'',
//					'class'=>'',
//					'class_path'=>'',
//					'class_function'=>'',
//					'data' => array(
//						'time'=>'时间',
//						'logo' => '公司logo'
//					),
//					'extra_process_data' => array(
//						'time'=>array(
//							'type'=>'time',
//							'function'=>'add_time'
//						),
//						'logo' => array(
//							'type' => 'logo',
//						)
//					)
//				),
			    'main_area' => array(
				    'type' => 'grid',
				    'title' => '商店配货订单条码',
				    'table' => 'dim_allocate_order_record_detail',
				    'class' => 'MdlAllocateOrderRecordDetail',
				    'class_path' => 'dim/allocate_order_record_detail',
				    'class_function' => 'get_detail_by_pid',
				    'data' => array(
					    'goods_code' => '商品代码',
					    'goods_name' => '商品名称',
					    'color_code' => '颜色代码',
					    'color_name' => '颜色名称',
					    'size_code' => '尺码代码',
					    'size_name' => '尺码名称',
					    'brand_code' => '品牌代码',
					    'brand_name' => '品牌名称',
					    'sort_code' => '品类代码',
					    'sort_name' => '品类名称',
					    'category_code' => '分类代码',
					    'category_name' => '分类名称',
					    'season_code' => '季节代码',
					    'season_name' => '季节名称',
					    'series_code' => '系列代码',
					    'series_name' => '系列名称',
					    'unit_code' => '单位代码',
					    'unit_name' => '单位名称',
					    'year' => '年份',
					    'property_value1' => '属性1',
					    'property_value2' => '属性2',
					    'property_value3' => '属性3',
					    'property_value4' => '属性4',
					    'property_value5' => '属性5',
					    'property_value6' => '属性6',
					    'sell_price' => '标准价',
					    //	'barcode' => '商品条码[条码]',
				    ),
				    'extra_process_data' => array(
//						'barcode' => array(
//							'type' => 'barcode',
//						),
					    'goods_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_goods_name_by_code'
					    ),
					    'color_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'color_code',
						    'function' => 'get_color_name_by_code'
					    ),
					    'size_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'size_code',
						    'function' => 'get_size_name_by_code'
					    ),
					    'brand_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'brand_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'brand_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_brand_name_by_goods_code'
					    ),
					    'sort_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'sort_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'sort_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_sort_name_by_goods_code'
					    ),
					    'category_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'category_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'category_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_category_name_by_goods_code'
					    ),
					    'season_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'season_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'season_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_season_name_by_goods_code'
					    ),
					    'series_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'series_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'series_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_series_name_by_goods_code'
					    ),
					    'unit_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'unit_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'unit_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_unit_name_by_goods_code'
					    ),
					    'year' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_year_by_goods_code',
					    ),
					    'property_value1' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value1_by_goods_id'
					    ),
					    'property_value2' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value2_by_goods_id'
					    ),
					    'property_value3' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value3_by_goods_id'
					    ),
					    'property_value4' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value4_by_goods_id'
					    ),
					    'property_value5' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value5_by_goods_id'
					    ),
					    'property_value6' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value6_by_goods_id'
					    ),
					    'sell_price' => array(
						    'type' => 'process_get_by_relation_model', //关联model获取数据
						    'function' => 'format_money',
						    'get_col' => 'sell_price',
						    'relation_model_params' => 'goods_code, color_code, size_code',
						    'relation_model_class' => 'MdlGoodsPrice',
						    'relation_model_path' => 'base/goods_price',
						    'relation_model_function' => 'get_goods_price_info_by_code'
					    ),
				    )
			    ),
			    'barcode_area' => array(
				    'type' => 'grid',
				    'title' => '商品条码',
				    'table' => 'dim_allocate_order_record_detail',
				    'class' => 'MdlAllocateOrderRecordDetail',
				    'class_path' => 'dim/allocate_order_record_detail',
				    'class_function' => 'get_detail_by_pid',
				    'data' => array(
					    'barcode' => '商品条码[条码]',
				    ),
				    'extra_process_data' => array(
					    'barcode' => array(
						    'type' => 'barcode'
					    ),
				    )
			    ),
//				'page_bottom_area' => array(
//					'type'=>'grid',
//					'title'=>'页尾',
//					'table'=>'',
//					'class'=>'',
//					'class_path'=>'',
//					'class_function'=>'',
//					'data' => array(
//						'time'=>'时间',
//						'logo' => '公司logo'
//					),
//					'extra_process_data' => array(
//						'time'=>array(
//							'type'=>'time',
//							'function'=>'add_time'
//						),
//						'logo' => array(
//							'type' => 'logo',
//						)
//					)
//				),
		    )
	    ),

        //商店配货订单标准打印
        'dim_allocate_order_record' => array(
            'title' => '商店配货订单',
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '订单信息',
                    'table' => 'dim_allocate_order_record',
                    'class' => 'MdlAllocateOrderRecord',
                    'class_path' => 'dim/allocate_order_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'shop_code' => '商店代码',
                        'shop_name' => '商店名称',
                        'shop_store_code' => '店仓代码',
                        'shop_store_name' => '店仓名称',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_check_person' => '审核人',
                        'is_check_time' => '审核时间',
                        'is_execute_person' => '执行人',
                        'is_execute_time' => '执行时间',
//						'is_cancel_person' => '作废人',
//						'is_cancel_time' => '作废时间',
                        'is_stop_person' => '终止人',
                        'is_stop_time' => '终止时间',
                        'is_finish_person' => '完成人',
                        'is_finish_time' => '完成时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'shop_store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'shop_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_check_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_check_time',
                            'function' => 'format_day_time'
                        ),
                        'is_execute_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_execute_time',
                            'function' => 'format_day_time'
                        ),
//						'is_cancel_time' => array(
//							'type'=>'format_day_time', //格式化时间
//							'relation_col'=>'is_cancel_time',
//							'function'=>'format_day_time'
//						),
                        'is_stop_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_stop_time',
                            'function' => 'format_day_time'
                        ),
                        'is_finish_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_finish_time',
                            'function' => 'format_day_time'
                        )
                    ),
                ),
                //商店信息
                'distributor_area' => array(
                    'type' => 'grid',
                    'title' => '商店信息',
                    'table' => 'dim_allocate_order_record',
                    'class' => 'MdlAllocateOrderRecord',
                    'class_path' => 'dim/allocate_order_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'distributor_code' => '商店代码',
                        'distributor_name' => '商店名称',
                        'distributor_contact_person' => '商店联系人',
                        'distributor_email' => '商店邮箱',
                        'distributor_phone' => '商店电话',
                        'distributor_tel' => '商店手机',
                        'distributor_address' => '商店地址',
                        'distributor_fax' => '商店传真',
                        'distributor_zipcode' => '商店邮编',
                        'distributor_website' => '商店公司网站',
                        'distributor_bank' => '商店开户银行',
                        'distributor_account' => '商店账号',
                        'distributor_account_name' => '商店账号名称',
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'distributor_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'email',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'address',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'website',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_distributor_info_by_code'
                        ),
                    ),
                ),
                //打印明细
                'detail_area' => array(
                    'type' => 'list',
                    'title' => '明细区',
                    'table' => 'dim_allocate_order_record_detail',
                    'class' => 'MdlAllocateOrderRecordDetail',
                    'class_path' => 'dim/allocate_order_record_detail',
                    'class_function' => 'get_detail_by_pid',
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码代码',
                        'size_name' => '尺码名称',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
	                    'brand_code' => '品牌代码',
	                    'brand_name' => '品牌名称',
	                    'sort_code' => '品类代码',
	                    'sort_name' => '品类名称',
	                    'category_code' => '分类代码',
	                    'category_name' => '分类名称',
	                    'season_code' => '季节代码',
	                    'season_name' => '季节名称',
	                    'series_code' => '系列代码',
	                    'series_name' => '系列名称',
	                    'unit_code' => '单位代码',
	                    'unit_name' => '单位名称',
	                    'year' => '年份',
	                    'property_value1' => '属性1',
	                    'property_value2' => '属性2',
	                    'property_value3' => '属性3',
	                    'property_value4' => '属性4',
	                    'property_value5' => '属性5',
	                    'property_value6' => '属性6',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'size_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'size_code',
                            'function' => 'get_size_name_by_code',
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
	                    'brand_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'brand_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'brand_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_brand_name_by_goods_code'
	                    ),
	                    'sort_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'sort_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'sort_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_sort_name_by_goods_code'
	                    ),
	                    'category_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'category_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'category_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_category_name_by_goods_code'
	                    ),
	                    'season_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'season_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'season_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_season_name_by_goods_code'
	                    ),
	                    'series_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'series_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'series_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_series_name_by_goods_code'
	                    ),
	                    'unit_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'unit_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'unit_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_unit_name_by_goods_code'
	                    ),
	                    'year' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_year_by_goods_code',
	                    ),
	                    'property_value1' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value1_by_goods_id'
	                    ),
	                    'property_value2' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value2_by_goods_id'
	                    ),
	                    'property_value3' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value3_by_goods_id'
	                    ),
	                    'property_value4' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value4_by_goods_id'
	                    ),
	                    'property_value5' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value5_by_goods_id'
	                    ),
	                    'property_value6' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value6_by_goods_id'
	                    ),
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'dim_allocate_order_record',
                    'class' => 'MdlAllocateOrderRecord',
                    'class_path' => 'dim/allocate_order_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_check_person' => '审核人',
                        'is_check_time' => '审核时间',
                        'is_execute_person' => '执行人',
                        'is_execute_time' => '执行时间',
//						'is_cancel_person' => '作废人',
//						'is_cancel_time' => '作废时间',
                        'is_stop_person' => '终止人',
                        'is_stop_time' => '终止时间',
                        'is_finish_person' => '完成人',
                        'is_finish_time' => '完成时间'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_check_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_check_time',
                            'function' => 'format_day_time'
                        ),
                        'is_execute_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_execute_time',
                            'function' => 'format_day_time'
                        ),
//						'is_cancel_time' => array(
//							'type'=>'format_day_time', //格式化时间
//							'relation_col'=>'is_cancel_time',
//							'function'=>'format_day_time'
//						),
                        'is_stop_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_stop_time',
                            'function' => 'format_day_time'
                        ),
                        'is_finish_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_finish_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),
        //商店配货订单扩展打印
        'ext_dim_allocate_order_record' => array(
            'title' => '商店配货订单',
            'property' => 'record_ext', //单据明细扩展打印
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '订单信息',
                    'table' => 'dim_allocate_order_record',
                    'class' => 'MdlAllocateOrderRecord',
                    'class_path' => 'dim/allocate_order_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'shop_code' => '商店代码',
                        'shop_name' => '商店名称',
                        'shop_store_code' => '店仓代码',
                        'shop_store_name' => '店仓名称',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_check_person' => '审核人',
                        'is_check_time' => '审核时间',
                        'is_execute_person' => '执行人',
                        'is_execute_time' => '执行时间',
//						'is_cancel_person' => '作废人',
//						'is_cancel_time' => '作废时间',
                        'is_stop_person' => '终止人',
                        'is_stop_time' => '终止时间',
                        'is_finish_person' => '完成人',
                        'is_finish_time' => '完成时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'shop_store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'shop_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_check_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_check_time',
                            'function' => 'format_day_time'
                        ),
                        'is_execute_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_execute_time',
                            'function' => 'format_day_time'
                        ),
//						'is_cancel_time' => array(
//							'type'=>'format_day_time', //格式化时间
//							'relation_col'=>'is_cancel_time',
//							'function'=>'format_day_time'
//						),
                        'is_stop_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_stop_time',
                            'function' => 'format_day_time'
                        ),
                        'is_finish_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_finish_time',
                            'function' => 'format_day_time'
                        )
                    ),
                ),
                //商店信息
                'distributor_area' => array(
                    'type' => 'grid',
                    'title' => '商店信息',
                    'table' => 'dim_allocate_order_record',
                    'class' => 'MdlAllocateOrderRecord',
                    'class_path' => 'dim/allocate_order_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'distributor_code' => '商店代码',
                        'distributor_name' => '商店名称',
                        'distributor_contact_person' => '商店联系人',
                        'distributor_email' => '商店邮箱',
                        'distributor_phone' => '商店电话',
                        'distributor_tel' => '商店手机',
                        'distributor_address' => '商店地址',
                        'distributor_fax' => '商店传真',
                        'distributor_zipcode' => '商店邮编',
                        'distributor_website' => '商店公司网站',
                        'distributor_bank' => '商店开户银行',
                        'distributor_account' => '商店账号',
                        'distributor_account_name' => '商店账号名称',
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'distributor_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'email',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'address',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'website',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_distributor_info_by_code'
                        ),
                    ),
                ),
                //扩展打印明细
                'ext_detail_area' => array(
                    'type' => 'list',
                    'title' => '扩展明细区',
                    'table' => 'dim_allocate_order_record_detail',
                    'class' => 'MdlAllocateOrderRecordDetail',
                    'class_path' => 'dim/allocate_order_record_detail',
                    'class_function' => 'get_detail_by_pid',
                    'property' => 'record_ext', //单据明细扩展打印
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码名称[扩展]',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
	                    'brand_code' => '品牌代码',
	                    'brand_name' => '品牌名称',
	                    'sort_code' => '品类代码',
	                    'sort_name' => '品类名称',
	                    'category_code' => '分类代码',
	                    'category_name' => '分类名称',
	                    'season_code' => '季节代码',
	                    'season_name' => '季节名称',
	                    'series_code' => '系列代码',
	                    'series_name' => '系列名称',
	                    'unit_code' => '单位代码',
	                    'unit_name' => '单位名称',
	                    'year' => '年份',
	                    'property_value1' => '属性1',
	                    'property_value2' => '属性2',
	                    'property_value3' => '属性3',
	                    'property_value4' => '属性4',
	                    'property_value5' => '属性5',
	                    'property_value6' => '属性6',
                    ),
                    'ext_col' => 'size_code',
                    'ext_extra_process_data' => array(
                        'num' => 'plus',
                        'money' => 'plus',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
	                    'brand_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'brand_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'brand_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_brand_name_by_goods_code'
	                    ),
	                    'sort_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'sort_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'sort_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_sort_name_by_goods_code'
	                    ),
	                    'category_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'category_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'category_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_category_name_by_goods_code'
	                    ),
	                    'season_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'season_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'season_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_season_name_by_goods_code'
	                    ),
	                    'series_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'series_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'series_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_series_name_by_goods_code'
	                    ),
	                    'unit_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'unit_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'unit_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_unit_name_by_goods_code'
	                    ),
	                    'year' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_year_by_goods_code',
	                    ),
	                    'property_value1' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value1_by_goods_id'
	                    ),
	                    'property_value2' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value2_by_goods_id'
	                    ),
	                    'property_value3' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value3_by_goods_id'
	                    ),
	                    'property_value4' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value4_by_goods_id'
	                    ),
	                    'property_value5' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value5_by_goods_id'
	                    ),
	                    'property_value6' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value6_by_goods_id'
	                    ),
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'dim_allocate_order_record',
                    'class' => 'MdlAllocateOrderRecord',
                    'class_path' => 'dim/allocate_order_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_check_person' => '审核人',
                        'is_check_time' => '审核时间',
                        'is_execute_person' => '执行人',
                        'is_execute_time' => '执行时间',
//						'is_cancel_person' => '作废人',
//						'is_cancel_time' => '作废时间',
                        'is_stop_person' => '终止人',
                        'is_stop_time' => '终止时间',
                        'is_finish_person' => '完成人',
                        'is_finish_time' => '完成时间'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_check_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_check_time',
                            'function' => 'format_day_time'
                        ),
                        'is_execute_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_execute_time',
                            'function' => 'format_day_time'
                        ),
//						'is_cancel_time' => array(
//							'type'=>'format_day_time', //格式化时间
//							'relation_col'=>'is_cancel_time',
//							'function'=>'format_day_time'
//						),
                        'is_stop_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_stop_time',
                            'function' => 'format_day_time'
                        ),
                        'is_finish_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_finish_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),

	    //商店配货单条码(特殊)
	    'dim_allocate_record_barcode' => array(
		    'title' => '商店配货单条码',
		    'main_conf' => array(
//				'page_top_area' => array(
//					'type'=>'grid',
//					'title'=>'页眉',
//					'table'=>'',
//					'class'=>'',
//					'class_path'=>'',
//					'class_function'=>'',
//					'data' => array(
//						'time'=>'时间',
//						'logo' => '公司logo'
//					),
//					'extra_process_data' => array(
//						'time'=>array(
//							'type'=>'time',
//							'function'=>'add_time'
//						),
//						'logo' => array(
//							'type' => 'logo',
//						)
//					)
//				),
			    'main_area' => array(
				    'type' => 'grid',
				    'title' => '商店配货单条码',
				    'table' => 'dim_allocate_record_detail',
				    'class' => 'MdlAllocateRecordDetail',
				    'class_path' => 'dim/allocate_record_detail',
				    'class_function' => 'get_detail_by_pid',
				    'data' => array(
					    'goods_code' => '商品代码',
					    'goods_name' => '商品名称',
					    'color_code' => '颜色代码',
					    'color_name' => '颜色名称',
					    'size_code' => '尺码代码',
					    'size_name' => '尺码名称',
					    'brand_code' => '品牌代码',
					    'brand_name' => '品牌名称',
					    'sort_code' => '品类代码',
					    'sort_name' => '品类名称',
					    'category_code' => '分类代码',
					    'category_name' => '分类名称',
					    'season_code' => '季节代码',
					    'season_name' => '季节名称',
					    'series_code' => '系列代码',
					    'series_name' => '系列名称',
					    'unit_code' => '单位代码',
					    'unit_name' => '单位名称',
					    'year' => '年份',
					    'property_value1' => '属性1',
					    'property_value2' => '属性2',
					    'property_value3' => '属性3',
					    'property_value4' => '属性4',
					    'property_value5' => '属性5',
					    'property_value6' => '属性6',
					    'sell_price' => '标准价',
					    //	'barcode' => '商品条码[条码]',
				    ),
				    'extra_process_data' => array(
//						'barcode' => array(
//							'type' => 'barcode',
//						),
					    'goods_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_goods_name_by_code'
					    ),
					    'color_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'color_code',
						    'function' => 'get_color_name_by_code'
					    ),
					    'size_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'size_code',
						    'function' => 'get_size_name_by_code'
					    ),
					    'brand_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'brand_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'brand_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_brand_name_by_goods_code'
					    ),
					    'sort_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'sort_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'sort_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_sort_name_by_goods_code'
					    ),
					    'category_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'category_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'category_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_category_name_by_goods_code'
					    ),
					    'season_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'season_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'season_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_season_name_by_goods_code'
					    ),
					    'series_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'series_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'series_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_series_name_by_goods_code'
					    ),
					    'unit_code' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'goods_code',
						    'get_array_key' => 'unit_code',
						    'function' => 'get_goods_by_code'
					    ),
					    'unit_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_unit_name_by_goods_code'
					    ),
					    'year' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_code',
						    'function' => 'get_year_by_goods_code',
					    ),
					    'property_value1' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value1_by_goods_id'
					    ),
					    'property_value2' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value2_by_goods_id'
					    ),
					    'property_value3' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value3_by_goods_id'
					    ),
					    'property_value4' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value4_by_goods_id'
					    ),
					    'property_value5' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value5_by_goods_id'
					    ),
					    'property_value6' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'goods_id',
						    'function' => 'get_property_value6_by_goods_id'
					    ),
					    'sell_price' => array(
						    'type' => 'process_get_by_relation_model', //关联model获取数据
						    'function' => 'format_money',
						    'get_col' => 'sell_price',
						    'relation_model_params' => 'goods_code, color_code, size_code',
						    'relation_model_class' => 'MdlGoodsPrice',
						    'relation_model_path' => 'base/goods_price',
						    'relation_model_function' => 'get_goods_price_info_by_code'
					    ),
				    )
			    ),
			    'barcode_area' => array(
				    'type' => 'grid',
				    'title' => '商品条码',
				    'table' => 'dim_allocate_record_detail',
				    'class' => 'MdlAllocateRecordDetail',
				    'class_path' => 'dim/allocate_record_detail',
				    'class_function' => 'get_detail_by_pid',
				    'data' => array(
					    'barcode' => '商品条码[条码]',
				    ),
				    'extra_process_data' => array(
					    'barcode' => array(
						    'type' => 'barcode'
					    ),
				    )
			    ),
//				'page_bottom_area' => array(
//					'type'=>'grid',
//					'title'=>'页尾',
//					'table'=>'',
//					'class'=>'',
//					'class_path'=>'',
//					'class_function'=>'',
//					'data' => array(
//						'time'=>'时间',
//						'logo' => '公司logo'
//					),
//					'extra_process_data' => array(
//						'time'=>array(
//							'type'=>'time',
//							'function'=>'add_time'
//						),
//						'logo' => array(
//							'type' => 'logo',
//						)
//					)
//				),
		    )
	    ),
        //商店配货单标准打印
        'dim_allocate_record' => array(
            'title' => '商店配货单',
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '配货单信息',
                    'table' => 'dim_allocate_record',
                    'class' => 'MdlAllocateRecord',
                    'class_path' => 'dim/allocate_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'shop_code' => '商店代码',
                        'shop_name' => '商店名称',
                        'shop_store_code' => '店仓代码',
                        'shop_store_name' => '店仓名称',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_allocate_stock_out_person' => '配货出库人',
                        'is_allocate_stock_out_time' => '配货出库时间',
                        'is_shop_stock_in_person' => '店仓入库人',
                        'is_shop_stock_in_time' => '店仓入库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'shop_store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'shop_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_allocate_stock_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_allocate_stock_out_time',
                            'function' => 'format_day_time'
                        ),
                        'is_shop_stock_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_shop_stock_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        )
                    ),
                ),
                //商店信息
                'distributor_area' => array(
                    'type' => 'grid',
                    'title' => '商店信息',
                    'table' => 'dim_allocate_record',
                    'class' => 'MdlAllocateRecord',
                    'class_path' => 'dim/allocate_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'distributor_code' => '商店代码',
                        'distributor_name' => '商店名称',
                        'distributor_contact_person' => '商店联系人',
                        'distributor_email' => '商店邮箱',
                        'distributor_phone' => '商店电话',
                        'distributor_tel' => '商店手机',
                        'distributor_address' => '商店地址',
                        'distributor_fax' => '商店传真',
                        'distributor_zipcode' => '商店邮编',
                        'distributor_website' => '商店公司网站',
                        'distributor_bank' => '商店开户银行',
                        'distributor_account' => '商店账号',
                        'distributor_account_name' => '商店账号名称',
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'distributor_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'email',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'address',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'website',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_distributor_info_by_code'
                        ),
                    ),
                ),
                //打印明细
                'detail_area' => array(
                    'type' => 'list',
                    'title' => '明细区',
                    'table' => 'dim_allocate_record_detail',
                    'class' => 'MdlAllocateRecordDetail',
                    'class_path' => 'dim/allocate_record_detail',
                    'class_function' => 'get_detail_by_pid',
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码代码',
                        'size_name' => '尺码名称',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
	                    'brand_code' => '品牌代码',
	                    'brand_name' => '品牌名称',
	                    'sort_code' => '品类代码',
	                    'sort_name' => '品类名称',
	                    'category_code' => '分类代码',
	                    'category_name' => '分类名称',
	                    'season_code' => '季节代码',
	                    'season_name' => '季节名称',
	                    'series_code' => '系列代码',
	                    'series_name' => '系列名称',
	                    'unit_code' => '单位代码',
	                    'unit_name' => '单位名称',
	                    'year' => '年份',
	                    'property_value1' => '属性1',
	                    'property_value2' => '属性2',
	                    'property_value3' => '属性3',
	                    'property_value4' => '属性4',
	                    'property_value5' => '属性5',
	                    'property_value6' => '属性6',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'size_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'size_code',
                            'function' => 'get_size_name_by_code',
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
	                    'brand_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'brand_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'brand_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_brand_name_by_goods_code'
	                    ),
	                    'sort_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'sort_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'sort_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_sort_name_by_goods_code'
	                    ),
	                    'category_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'category_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'category_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_category_name_by_goods_code'
	                    ),
	                    'season_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'season_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'season_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_season_name_by_goods_code'
	                    ),
	                    'series_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'series_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'series_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_series_name_by_goods_code'
	                    ),
	                    'unit_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'unit_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'unit_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_unit_name_by_goods_code'
	                    ),
	                    'year' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_year_by_goods_code',
	                    ),
	                    'property_value1' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value1_by_goods_id'
	                    ),
	                    'property_value2' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value2_by_goods_id'
	                    ),
	                    'property_value3' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value3_by_goods_id'
	                    ),
	                    'property_value4' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value4_by_goods_id'
	                    ),
	                    'property_value5' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value5_by_goods_id'
	                    ),
	                    'property_value6' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value6_by_goods_id'
	                    ),
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'dim_allocate_record',
                    'class' => 'MdlAllocateRecord',
                    'class_path' => 'dim/allocate_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_allocate_stock_out_person' => '配货出库人',
                        'is_allocate_stock_out_time' => '配货出库时间',
                        'is_shop_stock_in_person' => '店仓入库人',
                        'is_shop_stock_in_time' => '店仓入库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_allocate_stock_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_allocate_stock_out_time',
                            'function' => 'format_day_time'
                        ),
                        'is_shop_stock_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_shop_stock_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),
        //商店配货单扩展打印
        'ext_dim_allocate_record' => array(
            'title' => '商店配货单',
            'property' => 'record_ext', //单据明细扩展打印
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '配货单信息',
                    'table' => 'dim_allocate_record',
                    'class' => 'MdlAllocateRecord',
                    'class_path' => 'dim/allocate_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'shop_code' => '商店代码',
                        'shop_name' => '商店名称',
                        'shop_store_code' => '店仓代码',
                        'shop_store_name' => '店仓名称',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_allocate_stock_out_person' => '配货出库人',
                        'is_allocate_stock_out_time' => '配货出库时间',
                        'is_shop_stock_in_person' => '店仓入库人',
                        'is_shop_stock_in_time' => '店仓入库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'shop_store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'shop_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_allocate_stock_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_allocate_stock_out_time',
                            'function' => 'format_day_time'
                        ),
                        'is_shop_stock_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_shop_stock_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        )
                    ),
                ),
                //商店信息
                'distributor_area' => array(
                    'type' => 'grid',
                    'title' => '商店信息',
                    'table' => 'dim_allocate_record',
                    'class' => 'MdlAllocateRecord',
                    'class_path' => 'dim/allocate_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'distributor_code' => '商店代码',
                        'distributor_name' => '商店名称',
                        'distributor_contact_person' => '商店联系人',
                        'distributor_email' => '商店邮箱',
                        'distributor_phone' => '商店电话',
                        'distributor_tel' => '商店手机',
                        'distributor_address' => '商店地址',
                        'distributor_fax' => '商店传真',
                        'distributor_zipcode' => '商店邮编',
                        'distributor_website' => '商店公司网站',
                        'distributor_bank' => '商店开户银行',
                        'distributor_account' => '商店账号',
                        'distributor_account_name' => '商店账号名称',
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'distributor_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'email',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'address',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'website',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_distributor_info_by_code'
                        ),
                    ),
                ),
                //扩展打印明细
                'ext_detail_area' => array(
                    'type' => 'list',
                    'title' => '扩展明细区',
                    'table' => 'dim_allocate_record_detail',
                    'class' => 'MdlAllocateRecordDetail',
                    'class_path' => 'dim/allocate_record_detail',
                    'class_function' => 'get_detail_by_pid',
                    'property' => 'record_ext', //单据明细扩展打印
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码名称[扩展]',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
	                    'brand_code' => '品牌代码',
	                    'brand_name' => '品牌名称',
	                    'sort_code' => '品类代码',
	                    'sort_name' => '品类名称',
	                    'category_code' => '分类代码',
	                    'category_name' => '分类名称',
	                    'season_code' => '季节代码',
	                    'season_name' => '季节名称',
	                    'series_code' => '系列代码',
	                    'series_name' => '系列名称',
	                    'unit_code' => '单位代码',
	                    'unit_name' => '单位名称',
	                    'year' => '年份',
	                    'property_value1' => '属性1',
	                    'property_value2' => '属性2',
	                    'property_value3' => '属性3',
	                    'property_value4' => '属性4',
	                    'property_value5' => '属性5',
	                    'property_value6' => '属性6',
                    ),
                    'ext_col' => 'size_code',
                    'ext_extra_process_data' => array(
                        'num' => 'plus',
                        'money' => 'plus',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
	                    'brand_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'brand_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'brand_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_brand_name_by_goods_code'
	                    ),
	                    'sort_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'sort_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'sort_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_sort_name_by_goods_code'
	                    ),
	                    'category_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'category_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'category_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_category_name_by_goods_code'
	                    ),
	                    'season_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'season_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'season_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_season_name_by_goods_code'
	                    ),
	                    'series_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'series_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'series_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_series_name_by_goods_code'
	                    ),
	                    'unit_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'unit_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'unit_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_unit_name_by_goods_code'
	                    ),
	                    'year' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_year_by_goods_code',
	                    ),
	                    'property_value1' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value1_by_goods_id'
	                    ),
	                    'property_value2' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value2_by_goods_id'
	                    ),
	                    'property_value3' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value3_by_goods_id'
	                    ),
	                    'property_value4' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value4_by_goods_id'
	                    ),
	                    'property_value5' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value5_by_goods_id'
	                    ),
	                    'property_value6' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value6_by_goods_id'
	                    ),
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'dim_allocate_record',
                    'class' => 'MdlAllocateRecord',
                    'class_path' => 'dim/allocate_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_allocate_stock_out_person' => '配货出库人',
                        'is_allocate_stock_out_time' => '配货出库时间',
                        'is_shop_stock_in_person' => '店仓入库人',
                        'is_shop_stock_in_time' => '店仓入库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_allocate_stock_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_allocate_stock_out_time',
                            'function' => 'format_day_time'
                        ),
                        'is_shop_stock_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_shop_stock_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),
        //商店退货单标准打印
        'dim_return_record' => array(
            'title' => '商店退货单',
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '退货单信息',
                    'table' => 'dim_return_record',
                    'class' => 'MdlDimReturnRecord',
                    'class_path' => 'dim/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'shop_code' => '商店代码',
                        'shop_name' => '商店名称',
                        'shop_store_code' => '店仓代码',
                        'shop_store_name' => '店仓名称',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_return_stock_in_person' => '退货入库人',
                        'is_return_stock_in_time' => '退货入库时间',
                        'is_shop_stock_out_person' => '店仓出库人',
                        'is_shop_stock_out_time' => '店仓出库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'shop_store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'shop_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_return_stock_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_return_stock_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_shop_stock_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_shop_stock_out_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        )
                    ),
                ),
                //商店信息
                'distributor_area' => array(
                    'type' => 'grid',
                    'title' => '商店信息',
                    'table' => 'dim_return_record',
                    'class' => 'MdlDimReturnRecord',
                    'class_path' => 'dim/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'distributor_code' => '商店代码',
                        'distributor_name' => '商店名称',
                        'distributor_contact_person' => '商店联系人',
                        'distributor_email' => '商店邮箱',
                        'distributor_phone' => '商店电话',
                        'distributor_tel' => '商店手机',
                        'distributor_address' => '商店地址',
                        'distributor_fax' => '商店传真',
                        'distributor_zipcode' => '商店邮编',
                        'distributor_website' => '商店公司网站',
                        'distributor_bank' => '商店开户银行',
                        'distributor_account' => '商店账号',
                        'distributor_account_name' => '商店账号名称',
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'distributor_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'email',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'address',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'website',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_distributor_info_by_code'
                        ),
                    ),
                ),
                //打印明细
                'detail_area' => array(
                    'type' => 'list',
                    'title' => '明细区',
                    'table' => 'dim_return_record_detail',
                    'class' => 'MdlDimReturnRecordDetail',
                    'class_path' => 'dim/return_record_detail',
                    'class_function' => 'get_detail_by_pid',
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码代码',
                        'size_name' => '尺码名称',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
	                    'brand_code' => '品牌代码',
	                    'brand_name' => '品牌名称',
	                    'sort_code' => '品类代码',
	                    'sort_name' => '品类名称',
	                    'category_code' => '分类代码',
	                    'category_name' => '分类名称',
	                    'season_code' => '季节代码',
	                    'season_name' => '季节名称',
	                    'series_code' => '系列代码',
	                    'series_name' => '系列名称',
	                    'unit_code' => '单位代码',
	                    'unit_name' => '单位名称',
	                    'year' => '年份',
	                    'property_value1' => '属性1',
	                    'property_value2' => '属性2',
	                    'property_value3' => '属性3',
	                    'property_value4' => '属性4',
	                    'property_value5' => '属性5',
	                    'property_value6' => '属性6',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'size_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'size_code',
                            'function' => 'get_size_name_by_code',
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
	                    'brand_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'brand_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'brand_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_brand_name_by_goods_code'
	                    ),
	                    'sort_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'sort_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'sort_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_sort_name_by_goods_code'
	                    ),
	                    'category_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'category_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'category_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_category_name_by_goods_code'
	                    ),
	                    'season_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'season_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'season_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_season_name_by_goods_code'
	                    ),
	                    'series_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'series_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'series_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_series_name_by_goods_code'
	                    ),
	                    'unit_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'unit_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'unit_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_unit_name_by_goods_code'
	                    ),
	                    'year' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_year_by_goods_code',
	                    ),
	                    'property_value1' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value1_by_goods_id'
	                    ),
	                    'property_value2' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value2_by_goods_id'
	                    ),
	                    'property_value3' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value3_by_goods_id'
	                    ),
	                    'property_value4' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value4_by_goods_id'
	                    ),
	                    'property_value5' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value5_by_goods_id'
	                    ),
	                    'property_value6' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value6_by_goods_id'
	                    ),
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'dim_return_record',
                    'class' => 'MdlDimReturnRecord',
                    'class_path' => 'dim/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_return_stock_in_person' => '退货入库人',
                        'is_return_stock_in_time' => '退货入库时间',
                        'is_shop_stock_out_person' => '店仓出库人',
                        'is_shop_stock_out_time' => '店仓出库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_return_stock_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_return_stock_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_shop_stock_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_shop_stock_out_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),
        //商店退货单扩展打印
        'ext_dim_return_record' => array(
            'title' => '商店退货单',
            'property' => 'record_ext', //单据明细扩展打印
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '退货单信息',
                    'table' => 'dim_return_record',
                    'class' => 'MdlDimReturnRecord',
                    'class_path' => 'dim/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'shop_code' => '商店代码',
                        'shop_name' => '商店名称',
                        'shop_store_code' => '店仓代码',
                        'shop_store_name' => '店仓名称',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_return_stock_in_person' => '退货入库人',
                        'is_return_stock_in_time' => '退货入库时间',
                        'is_shop_stock_out_person' => '店仓出库人',
                        'is_shop_stock_out_time' => '店仓出库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'shop_store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'shop_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_return_stock_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_return_stock_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_shop_stock_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_shop_stock_out_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        )
                    ),
                ),
                //商店信息
                'distributor_area' => array(
                    'type' => 'grid',
                    'title' => '商店信息',
                    'table' => 'dim_return_record',
                    'class' => 'MdlDimReturnRecord',
                    'class_path' => 'dim/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'distributor_code' => '商店代码',
                        'distributor_name' => '商店名称',
                        'distributor_contact_person' => '商店联系人',
                        'distributor_email' => '商店邮箱',
                        'distributor_phone' => '商店电话',
                        'distributor_tel' => '商店手机',
                        'distributor_address' => '商店地址',
                        'distributor_fax' => '商店传真',
                        'distributor_zipcode' => '商店邮编',
                        'distributor_website' => '商店公司网站',
                        'distributor_bank' => '商店开户银行',
                        'distributor_account' => '商店账号',
                        'distributor_account_name' => '商店账号名称',
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'distributor_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'email',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'address',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'website',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'shop_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_distributor_info_by_code'
                        ),
                    ),
                ),
                //扩展打印明细
                'ext_detail_area' => array(
                    'type' => 'list',
                    'title' => '扩展明细区',
                    'table' => 'dim_return_record_detail',
                    'class' => 'MdlDimReturnRecordDetail',
                    'class_path' => 'dim/return_record_detail',
                    'class_function' => 'get_detail_by_pid',
                    'property' => 'record_ext', //单据明细扩展打印
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码名称[扩展]',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
	                    'brand_code' => '品牌代码',
	                    'brand_name' => '品牌名称',
	                    'sort_code' => '品类代码',
	                    'sort_name' => '品类名称',
	                    'category_code' => '分类代码',
	                    'category_name' => '分类名称',
	                    'season_code' => '季节代码',
	                    'season_name' => '季节名称',
	                    'series_code' => '系列代码',
	                    'series_name' => '系列名称',
	                    'unit_code' => '单位代码',
	                    'unit_name' => '单位名称',
	                    'year' => '年份',
	                    'property_value1' => '属性1',
	                    'property_value2' => '属性2',
	                    'property_value3' => '属性3',
	                    'property_value4' => '属性4',
	                    'property_value5' => '属性5',
	                    'property_value6' => '属性6',
                    ),
                    'ext_col' => 'size_code',
                    'ext_extra_process_data' => array(
                        'num' => 'plus',
                        'money' => 'plus',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
	                    'brand_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'brand_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'brand_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_brand_name_by_goods_code'
	                    ),
	                    'sort_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'sort_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'sort_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_sort_name_by_goods_code'
	                    ),
	                    'category_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'category_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'category_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_category_name_by_goods_code'
	                    ),
	                    'season_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'season_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'season_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_season_name_by_goods_code'
	                    ),
	                    'series_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'series_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'series_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_series_name_by_goods_code'
	                    ),
	                    'unit_code' => array(
		                    'type' => 'process_get_array_by_code',
		                    'relation_col' => 'goods_code',
		                    'get_array_key' => 'unit_code',
		                    'function' => 'get_goods_by_code'
	                    ),
	                    'unit_name' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_unit_name_by_goods_code'
	                    ),
	                    'year' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_code',
		                    'function' => 'get_year_by_goods_code',
	                    ),
	                    'property_value1' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value1_by_goods_id'
	                    ),
	                    'property_value2' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value2_by_goods_id'
	                    ),
	                    'property_value3' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value3_by_goods_id'
	                    ),
	                    'property_value4' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value4_by_goods_id'
	                    ),
	                    'property_value5' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value5_by_goods_id'
	                    ),
	                    'property_value6' => array(
		                    'type' => 'process_get_name_by_code',
		                    'relation_col' => 'goods_id',
		                    'function' => 'get_property_value6_by_goods_id'
	                    ),
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'dim_return_record',
                    'class' => 'MdlDimReturnRecord',
                    'class_path' => 'dim/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_return_stock_in_person' => '退货入库人',
                        'is_return_stock_in_time' => '退货入库时间',
                        'is_shop_stock_out_person' => '店仓出库人',
                        'is_shop_stock_out_time' => '店仓出库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_return_stock_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_return_stock_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_shop_stock_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_shop_stock_out_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),
        //零售销货单标准打印
        'rbm_sale_record' => array(
            'title' => '零售销货单',
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '销货单信息',
                    'table' => 'rbm_sale_record',
                    'class' => 'MdlSaleRecord',
                    'class_path' => 'rbm/sale_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'distributor_code' => '商店代码',
                        'distributor_name' => '商店名称',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_store_out_person' => '出库人',
                        'is_store_out_time' => '出库时间',
//						'is_finish_person' => '完成人',
//						'is_finish_time' => '完成时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_out_time',
                            'function' => 'format_day_time'
                        ),
//						'is_finish_time' => array(
//							'type'=>'format_day_time', //格式化时间
//							'relation_col'=>'is_finish_time',
//							'function'=>'format_day_time'
//						),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        )
                    ),
                ),
                //商店信息
                'distributor_area' => array(
                    'type' => 'grid',
                    'title' => '商店信息',
                    'table' => 'rbm_sale_record',
                    'class' => 'MdlSaleRecord',
                    'class_path' => 'rbm/sale_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'distributor_code' => '商店代码',
                        'distributor_name' => '商店名称',
                        'distributor_contact_person' => '商店联系人',
                        'distributor_email' => '商店邮箱',
                        'distributor_phone' => '商店电话',
                        'distributor_tel' => '商店手机',
                        'distributor_address' => '商店地址',
                        'distributor_fax' => '商店传真',
                        'distributor_zipcode' => '商店邮编',
                        'distributor_website' => '商店公司网站',
                        'distributor_bank' => '商店开户银行',
                        'distributor_account' => '商店账号',
                        'distributor_account_name' => '商店账号名称',
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'distributor_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'email',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'address',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'website',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'account',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_distributor_info_by_code'
                        ),
                    ),
                ),
                //打印明细
                'detail_area' => array(
                    'type' => 'list',
                    'title' => '明细区',
                    'table' => 'rbm_sale_record_detail',
                    'class' => 'MdlSaleRecord',
                    'class_path' => 'rbm/sale_record',
                    'class_function' => 'get_detail_list_by_pid',
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码代码',
                        'size_name' => '尺码名称',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'size_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'size_code',
                            'function' => 'get_size_name_by_code',
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'rbm_sale_record',
                    'class' => 'MdlSaleRecord',
                    'class_path' => 'rbm/sale_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_store_out_person' => '出库人',
                        'is_store_out_time' => '出库时间',
//						'is_finish_person' => '完成人',
//						'is_finish_time' => '完成时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间',
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_out_time',
                            'function' => 'format_day_time'
                        ),
//						'is_finish_time' => array(
//							'type'=>'format_day_time', //格式化时间
//							'relation_col'=>'is_finish_time',
//							'function'=>'format_day_time'
//						),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),
        //零售销货单扩展打印
        'ext_rbm_sale_record' => array(
            'title' => '零售销货单',
            'property' => 'record_ext', //单据明细扩展打印
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '销货单信息',
                    'table' => 'rbm_sale_record',
                    'class' => 'MdlSaleRecord',
                    'class_path' => 'rbm/sale_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'distributor_code' => '商店代码',
                        'distributor_name' => '商店名称',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_store_out_person' => '出库人',
                        'is_store_out_time' => '出库时间',
//						'is_finish_person' => '完成人',
//						'is_finish_time' => '完成时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_out_time',
                            'function' => 'format_day_time'
                        ),
//						'is_finish_time' => array(
//							'type'=>'format_day_time', //格式化时间
//							'relation_col'=>'is_finish_time',
//							'function'=>'format_day_time'
//						),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        )
                    ),
                ),
                //商店信息
                'distributor_area' => array(
                    'type' => 'grid',
                    'title' => '商店信息',
                    'table' => 'rbm_sale_record',
                    'class' => 'MdlSaleRecord',
                    'class_path' => 'rbm/sale_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'distributor_code' => '商店代码',
                        'distributor_name' => '商店名称',
                        'distributor_contact_person' => '商店联系人',
                        'distributor_email' => '商店邮箱',
                        'distributor_phone' => '商店电话',
                        'distributor_tel' => '商店手机',
                        'distributor_address' => '商店地址',
                        'distributor_fax' => '商店传真',
                        'distributor_zipcode' => '商店邮编',
                        'distributor_website' => '商店公司网站',
                        'distributor_bank' => '商店开户银行',
                        'distributor_account' => '商店账号',
                        'distributor_account_name' => '商店账号名称',
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'distributor_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'email',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'address',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'website',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'account',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_distributor_info_by_code'
                        ),
                    ),
                ),
                //扩展打印明细
                'ext_detail_area' => array(
                    'type' => 'list',
                    'title' => '扩展明细区',
                    'table' => 'rbm_sale_record_detail',
                    'class' => 'MdlSaleRecord',
                    'class_path' => 'rbm/sale_record',
                    'class_function' => 'get_detail_list_by_pid',
                    'property' => 'record_ext', //单据明细扩展打印
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码名称[扩展]',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额'
                    ),
                    'ext_col' => 'size_code',
                    'ext_extra_process_data' => array(
                        'num' => 'plus',
                        'money' => 'plus',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'rbm_sale_record',
                    'class' => 'MdlSaleRecord',
                    'class_path' => 'rbm/sale_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_store_out_person' => '出库人',
                        'is_store_out_time' => '出库时间',
//						'is_finish_person' => '完成人',
//						'is_finish_time' => '完成时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间',
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_out_time',
                            'function' => 'format_day_time'
                        ),
//						'is_finish_time' => array(
//							'type'=>'format_day_time', //格式化时间
//							'relation_col'=>'is_finish_time',
//							'function'=>'format_day_time'
//						),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),
        //零售退货单标准打印
        'rbm_return_record' => array(
            'title' => '零售退货单',
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '退货单信息',
                    'table' => 'rbm_return_record',
                    'class' => 'MdlRbmReturnRecord',
                    'class_path' => 'rbm/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'distributor_code' => '商店代码',
                        'distributor_name' => '商店名称',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_store_in_person' => '入库人',
                        'is_store_in_time' => '入库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        )
                    ),
                ),
                //商店信息
                'distributor_area' => array(
                    'type' => 'grid',
                    'title' => '商店信息',
                    'table' => 'rbm_return_record',
                    'class' => 'MdlRbmReturnRecord',
                    'class_path' => 'rbm/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'distributor_code' => '商店代码',
                        'distributor_name' => '商店名称',
                        'distributor_contact_person' => '商店联系人',
                        'distributor_email' => '商店邮箱',
                        'distributor_phone' => '商店电话',
                        'distributor_tel' => '商店手机',
                        'distributor_address' => '商店地址',
                        'distributor_fax' => '商店传真',
                        'distributor_zipcode' => '商店邮编',
                        'distributor_website' => '商店公司网站',
                        'distributor_bank' => '商店开户银行',
                        'distributor_account' => '商店账号',
                        'distributor_account_name' => '商店账号名称',
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'distributor_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'email',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'address',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'website',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'account',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_distributor_info_by_code'
                        ),
                    ),
                ),
                //打印明细
                'detail_area' => array(
                    'type' => 'list',
                    'title' => '明细区',
                    'table' => 'rbm_return_record_detail',
                    'class' => 'MdlRbmReturnRecord',
                    'class_path' => 'rbm/return_record',
                    'class_function' => 'get_detail_list_by_pid',
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码代码',
                        'size_name' => '尺码名称',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额'
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'size_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'size_code',
                            'function' => 'get_size_name_by_code',
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'rbm_return_record',
                    'class' => 'MdlRbmReturnRecord',
                    'class_path' => 'rbm/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_store_in_person' => '入库人',
                        'is_store_in_time' => '入库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间',
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),
        //零售退货单扩展打印
        'ext_rbm_return_record' => array(
            'title' => '零售退货单',
            'property' => 'record_ext', //单据明细扩展打印
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '退货单信息',
                    'table' => 'rbm_return_record',
                    'class' => 'MdlRbmReturnRecord',
                    'class_path' => 'rbm/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'distributor_code' => '商店代码',
                        'distributor_name' => '商店名称',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_store_in_person' => '入库人',
                        'is_store_in_time' => '入库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        )
                    ),
                ),
                //商店信息
                'distributor_area' => array(
                    'type' => 'grid',
                    'title' => '商店信息',
                    'table' => 'rbm_return_record',
                    'class' => 'MdlRbmReturnRecord',
                    'class_path' => 'rbm/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'distributor_code' => '商店代码',
                        'distributor_name' => '商店名称',
                        'distributor_contact_person' => '商店联系人',
                        'distributor_email' => '商店邮箱',
                        'distributor_phone' => '商店电话',
                        'distributor_tel' => '商店手机',
                        'distributor_address' => '商店地址',
                        'distributor_fax' => '商店传真',
                        'distributor_zipcode' => '商店邮编',
                        'distributor_website' => '商店公司网站',
                        'distributor_bank' => '商店开户银行',
                        'distributor_account' => '商店账号',
                        'distributor_account_name' => '商店账号名称',
                    ),
                    'extra_process_data' => array(
                        'distributor_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'distributor_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'distributor_contact_person' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'contact_person',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_email' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'email',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_phone' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'phone',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_tel' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'tel',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_address' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'address',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_fax' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'fax',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_zipcode' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'zipcode',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_website' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'website',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_bank' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'bank',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'account',
                            'function' => 'get_distributor_info_by_code'
                        ),
                        'distributor_account_name' => array(
                            'type' => 'process_get_array_by_code',
                            'relation_col' => 'distributor_code',
                            'get_array_key' => 'account_name',
                            'function' => 'get_distributor_info_by_code'
                        ),
                    ),
                ),
                //扩展打印明细
                'ext_detail_area' => array(
                    'type' => 'list',
                    'title' => '扩展明细区',
                    'table' => 'rbm_return_record_detail',
                    'class' => 'MdlRbmReturnRecord',
                    'class_path' => 'rbm/sale_record',
                    'class_function' => 'get_detail_list_by_pid',
                    'property' => 'record_ext', //单据明细扩展打印
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码名称[扩展]',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额'
                    ),
                    'ext_col' => 'size_code',
                    'ext_extra_process_data' => array(
                        'num' => 'plus',
                        'money' => 'plus',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'rbm_return_record',
                    'class' => 'MdlRbmReturnRecord',
                    'class_path' => 'rbm/return_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_store_in_person' => '入库人',
                        'is_store_in_time' => '入库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间',
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_store_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_store_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),
        //移仓单标准打印
        'stm_store_shift_record' => array(
            'title' => '移仓单',
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '移仓单信息',
                    'table' => 'stm_store_shift_record',
                    'class' => 'MdlStoreShiftRecord',
                    'class_path' => 'stm/store_shift_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'shift_out_store_code' => '移出仓库代码',
                        'shift_out_store_name' => '移出仓库名称',
                        'shift_in_store_code' => '移入仓库代码',
                        'shift_in_store_name' => '移入仓库名称',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_shift_in_person' => '入库人',
                        'is_shift_in_time' => '入库时间',
                        'is_shift_out_person' => '出库人',
                        'is_shift_out_time' => '出库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'shift_out_store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shift_out_store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'shift_in_store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shift_in_store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_shift_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_shift_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_shift_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_shift_out_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        )
                    ),
                ),
                //打印明细
                'detail_area' => array(
                    'type' => 'list',
                    'title' => '明细区',
                    'table' => 'stm_store_shift_record_detail',
                    'class' => 'MdlStoreShiftRecordDetail',
                    'class_path' => 'stm/store_shift_reocrd_detail',
                    'class_function' => 'get_detail_by_pid',
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码代码',
                        'size_name' => '尺码名称',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额'
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'size_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'size_code',
                            'function' => 'get_size_name_by_code',
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'stm_store_shift_record',
                    'class' => 'MdlStoreShiftRecord',
                    'class_path' => 'stm/store_shift_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_shift_in_person' => '入库人',
                        'is_shift_in_time' => '入库时间',
                        'is_shift_out_person' => '出库人',
                        'is_shift_out_time' => '出库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_shift_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_shift_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_shift_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_shift_out_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),
        //移仓单扩展打印
        'ext_stm_store_shift_record' => array(
            'title' => '移仓单',
            'property' => 'record_ext', //单据明细扩展打印
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '移仓单信息',
                    'table' => 'stm_store_shift_record',
                    'class' => 'MdlStoreShiftRecord',
                    'class_path' => 'stm/store_shift_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'record_time' => '业务时间',
                        'shift_out_store_code' => '移出仓库代码',
                        'shift_out_store_name' => '移出仓库名称',
                        'shift_in_store_code' => '移入仓库代码',
                        'shift_in_store_name' => '移入仓库名称',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_shift_in_person' => '入库人',
                        'is_shift_in_time' => '入库时间',
                        'is_shift_out_person' => '出库人',
                        'is_shift_out_time' => '出库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'shift_out_store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shift_out_store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'shift_in_store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shift_in_store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_shift_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_shift_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_shift_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_shift_out_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        )
                    ),
                ),
                //扩展打印明细
                'ext_detail_area' => array(
                    'type' => 'list',
                    'title' => '扩展明细区',
                    'table' => 'stm_store_shift_record_detail',
                    'class' => 'MdlStoreShiftRecordDetail',
                    'class_path' => 'stm/store_shift_record_detail',
                    'class_function' => 'get_detail_by_pid',
                    'property' => 'record_ext', //单据明细扩展打印
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码名称[扩展]',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额'
                    ),
                    'ext_col' => 'size_code',
                    'ext_extra_process_data' => array(
                        'num' => 'plus',
                        'money' => 'plus',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'stm_store_shift_record',
                    'class' => 'MdlStoreShiftRecord',
                    'class_path' => 'stm/store_shift_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_shift_in_person' => '入库人',
                        'is_shift_in_time' => '入库时间',
                        'is_shift_out_person' => '出库人',
                        'is_shift_out_time' => '出库时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_shift_in_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_shift_in_time',
                            'function' => 'format_day_time'
                        ),
                        'is_shift_out_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_shift_out_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        )
                    )
                ),
            )
        ),
        //盘点单标准打印
        'stm_take_stock_record' => array(
            'title' => '盘点单',
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '盘点单信息',
                    'table' => 'stm_take_stock_record',
                    'class' => 'MdlTakeStockRecord',
                    'class_path' => 'stm/take_stock_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_pre_profit_and_loss_person' => '预盈亏人',
                        'is_pre_profit_and_loss_time' => '预盈亏时间',
                        'is_stop_person' => '终止人',
                        'is_stop_time' => '终止时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_pre_profit_and_loss_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_pre_profit_and_loss_time',
                            'function' => 'format_day_time'
                        ),
                        'is_stop_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_stop_time',
                            'function' => 'format_day_time'
                        ),
                    ),
                ),
                //打印明细
                'detail_area' => array(
                    'type' => 'list',
                    'title' => '明细区',
                    'table' => 'stm_take_stock_record_detail',
                    'class' => 'MdlTakeStockRecordDetail',
                    'class_path' => 'stm/take_stock_record_detail',
                    'class_function' => 'get_detail_by_pid',
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码代码',
                        'size_name' => '尺码名称',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额'
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'size_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'size_code',
                            'function' => 'get_size_name_by_code',
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'stm_take_stock_record',
                    'class' => 'MdlTakeStockRecord',
                    'class_path' => 'stm/take_stock_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_pre_profit_and_loss_person' => '预盈亏人',
                        'is_pre_profit_and_loss_time' => '预盈亏时间',
                        'is_stop_person' => '终止人',
                        'is_stop_time' => '终止时间',
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_pre_profit_and_loss_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_pre_profit_and_loss_time',
                            'function' => 'format_day_time'
                        ),
                        'is_stop_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_stop_time',
                            'function' => 'format_day_time'
                        ),
                    )
                ),
            )
        ),
        //盘点单扩展打印
        'ext_stm_take_stock_record' => array(
            'title' => '盘点单',
            'property' => 'record_ext', //单据明细扩展打印
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '盘点单信息',
                    'table' => 'stm_take_stock_record',
                    'class' => 'MdlTakeStockRecord',
                    'class_path' => 'stm/take_stock_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_pre_profit_and_loss_person' => '预盈亏人',
                        'is_pre_profit_and_loss_time' => '预盈亏时间',
                        'is_stop_person' => '终止人',
                        'is_stop_time' => '终止时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_pre_profit_and_loss_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_pre_profit_and_loss_time',
                            'function' => 'format_day_time'
                        ),
                        'is_stop_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_stop_time',
                            'function' => 'format_day_time'
                        ),
                    ),
                ),
                //扩展打印明细
                'ext_detail_area' => array(
                    'type' => 'list',
                    'title' => '扩展明细区',
                    'table' => 'stm_take_stock_record_detail',
                    'class' => 'MdlTakeStockRecordDetail',
                    'class_path' => 'stm/take_stock_record_detail',
                    'class_function' => 'get_detail_by_pid',
                    'property' => 'record_ext', //单据明细扩展打印
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码名称[扩展]',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额'
                    ),
                    'ext_col' => 'size_code',
                    'ext_extra_process_data' => array(
                        'num' => 'plus',
                        'money' => 'plus',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'stm_take_stock_record',
                    'class' => 'MdlTakeStockRecord',
                    'class_path' => 'stm/take_stock_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_pre_profit_and_loss_person' => '预盈亏人',
                        'is_pre_profit_and_loss_time' => '预盈亏时间',
                        'is_stop_person' => '终止人',
                        'is_stop_time' => '终止时间',
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_pre_profit_and_loss_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_pre_profit_and_loss_time',
                            'function' => 'format_day_time'
                        ),
                        'is_stop_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_stop_time',
                            'function' => 'format_day_time'
                        ),
                    )
                ),
            )
        ),
        //库存调整单
        'stm_stock_adjust_record' => array(
            'title' => '库存调整单',
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '调整单信息',
                    'table' => 'stm_stock_adjust_record',
                    'class' => 'MdlStockAdjustRecord',
                    'class_path' => 'stm/stock_adjust_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_check_and_accept_person' => '验收人',
                        'is_check_and_accept_time' => '验收时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_check_and_accept_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_check_and_accept_time',
                            'function' => 'format_day_time'
                        ),
                    ),
                ),
                //打印明细
                'detail_area' => array(
                    'type' => 'list',
                    'title' => '明细区',
                    'table' => 'stm_stock_adjust_record_detail',
                    'class' => 'MdlStockAdjustRecordDetail',
                    'class_path' => 'stm/stock_adjust_record_detail',
                    'class_function' => 'get_detail_by_pid',
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码代码',
                        'size_name' => '尺码名称',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额'
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'size_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'size_code',
                            'function' => 'get_size_name_by_code',
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'stm_stock_adjust_record',
                    'class' => 'MdlStockAdjustRecord',
                    'class_path' => 'stm/stock_adjust_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_check_and_accept_person' => '验收人',
                        'is_check_and_accept_time' => '验收时间',
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_check_and_accept_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_check_and_accept_time',
                            'function' => 'format_day_time'
                        ),
                    )
                ),
            )
        ),
        //库存调整单扩展打印
        'ext_stm_stock_adjust_record' => array(
            'title' => '库存调整单',
            'property' => 'record_ext', //单据明细扩展打印
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '调整单信息',
                    'table' => 'stm_stock_adjust_record',
                    'class' => 'MdlStockAdjustRecord',
                    'class_path' => 'stm/stock_adjust_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_check_and_accept_person' => '验收人',
                        'is_check_and_accept_time' => '验收时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_check_and_accept_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_check_and_accept_time',
                            'function' => 'format_day_time'
                        ),
                    ),
                ),
                //扩展打印明细
                'ext_detail_area' => array(
                    'type' => 'list',
                    'title' => '扩展明细区',
                    'table' => 'stm_stock_adjust_record_detail',
                    'class' => 'MdlStockAdjustRecordDetail',
                    'class_path' => 'stm/stock_adjust_record_detail',
                    'class_function' => 'get_detail_by_pid',
                    'property' => 'record_ext', //单据明细扩展打印
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码名称[扩展]',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额'
                    ),
                    'ext_col' => 'size_code',
                    'ext_extra_process_data' => array(
                        'num' => 'plus',
                        'money' => 'plus',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => 'stm_stock_adjust_record',
                    'class' => 'MdlStockAdjustRecord',
                    'class_path' => 'stm/stock_adjust_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_sure_person' => '确认人',
                        'is_sure_time' => '确认时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_check_and_accept_person' => '验收人',
                        'is_check_and_accept_time' => '验收时间',
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_check_and_accept_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_check_and_accept_time',
                            'function' => 'format_day_time'
                        ),
                    )
                ),
            )
        ),
        //库存锁定单
        'stm_stock_lock_record' => array(
            'title' => '库存锁定单',
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '锁定单信息',
                    'table' => 'stm_stock_lock_record',
                    'class' => 'MdlStockLockRecord',
                    'class_path' => 'stm/stock_lock_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'num' => '数量',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        )
                    ),
                ),
                //打印明细
                'detail_area' => array(
                    'type' => 'list',
                    'title' => '明细区',
                    'table' => 'stm_stock_lock_record_detail',
                    'class' => 'MdlStockLockRecordDetail',
                    'class_path' => 'stm/stock_lock_record_detail',
                    'class_function' => 'get_detail_by_pid',
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码代码',
                        'size_name' => '尺码名称',
                        'num' => '数量',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'size_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'size_code',
                            'function' => 'get_size_name_by_code',
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
            )
        ),
        //库存锁定单扩展打印
        'ext_stm_stock_lock_record' => array(
            'title' => '库存锁定单',
            'property' => 'record_ext', //单据明细扩展打印
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '锁定单信息',
                    'table' => 'stm_stock_lock_record',
                    'class' => 'MdlStockLockRecord',
                    'class_path' => 'stm/stock_lock_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'num' => '数量',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        )
                    ),
                ),
                //扩展打印明细
                'ext_detail_area' => array(
                    'type' => 'list',
                    'title' => '扩展明细区',
                    'table' => 'stm_stock_adjust_record_detail',
                    'class' => 'MdlStockLockRecordDetail',
                    'class_path' => 'stm/stock_lock_record_detail',
                    'class_function' => 'get_detail_by_pid',
                    'property' => 'record_ext', //单据明细扩展打印
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码名称[扩展]',
                        'num' => '数量',
                    ),
                    'ext_col' => 'size_code',
                    'ext_extra_process_data' => array(
                        'num' => 'plus',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
            )
        ),
        //小票
        'rbm_receipt_record' => array(
            'title' => '小票',
            'main_conf' => array(
                'main_area_top' => array(
                    'type' => 'grid',
                    'title' => '小票头部',
                    'table' => 'rbm_receipt_record',
                    'class' => 'MdlReceiptRecord',
                    'class_path' => 'rbm/receipt_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'record_time' => '业务时间',
                        'shop_code' => '商店代码',
                        'shop_name' => '商店名称',
                        'guide_user_code' => '导购员代码',
                        'guide_user_name' => '导购员名称',
                        'trade_user_code' => '收银员代码',
                        'trade_user_name' => '收银员名称',
                        'customer_code' => '顾客代码',
                        'customer_name' => '顾客名称',
                        'vip_code' => '卡号',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'rebate' => '折扣',
                        'refer_money' => '参考金额',
                        'refer_money_up' => '参考金额(大写)',
                        'pay_money' => '收款金额',
                        'pay_money_up' => '收款金额(大写)',
                        'change_money' => '找零金额',
                        'change_money_up' => '找零金额(大写)',
                        'discount_money' => '优惠金额',
                        'discount_money_up' => '优惠金额(大写)',
                        'integral' => '获得积分',
                        'sum_integral' => '累积积分',
                        'settlement_name' => '结算方式',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_return_person' => '退货人',
                        'is_return_time' => '退货时间',
                        'is_pending_person' => '挂起人',
                        'is_pending_time' => '挂起时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间',
                        'remark' => '备注',
                        'final_money' => '实收金额',
                        'final_money_up' => '实收金额(大写)',
                        'cut_money' => '抹零金额',
                        'cut_money_up' => '抹零金额(大写)',
                    ),
                    'extra_process_data' => array(
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'final_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'final_money',
                            'function' => 'format_money'
                        ),
                        'cut_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'cut_money',
                            'function' => 'format_money'
                        ),
                        'final_money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'final_money',
                            'function' => 'num2rmb'
                        ),
                        'cut_money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'cut_money',
                            'function' => 'num2rmb'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'refer_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_money',
                            'function' => 'format_money'
                        ),
                        'refer_money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_money',
                            'function' => 'num2rmb'
                        ),
                        'pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'pay_money',
                            'function' => 'format_money'
                        ),
                        'pay_money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'pay_money',
                            'function' => 'num2rmb'
                        ),
                        'change_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_money',
                            'function' => 'format_money'
                        ),
                        'change_money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_money',
                            'function' => 'num2rmb'
                        ),
                        'discount_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'discount_money',
                            'function' => 'format_money'
                        ),
                        'discount_money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'discount_money',
                            'function' => 'num2rmb'
                        ),
                        'shop_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'guide_user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'guide_user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'trade_user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'trade_user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'customer_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'customer_code',
                            'function' => 'get_customer_name_by_code'
                        ),
                        //结算方式
                        'settlement_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'receipt_record_id',
                            'function' => 'get_pos_trade_type_name_by_receipt_record_id'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'sum_integral' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'vip_code',
                            'function' => 'get_vip_integral'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_return_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_return_time',
                            'function' => 'format_day_time'
                        ),
                        'is_pending_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_pending_time',
                            'function' => 'format_day_time'
                        ),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        )
                    ),
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '小票信息',
                    'table' => 'rbm_receipt_record',
                    'class' => 'MdlReceiptRecord',
                    'class_path' => 'rbm/receipt_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'record_time' => '业务时间',
                        'shop_code' => '商店代码',
                        'shop_name' => '商店名称',
                        'guide_user_code' => '导购员代码',
                        'guide_user_name' => '导购员名称',
                        'trade_user_code' => '收银员代码',
                        'trade_user_name' => '收银员名称',
                        'customer_code' => '顾客代码',
                        'customer_name' => '顾客名称',
                        'vip_code' => '卡号',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'rebate' => '折扣',
                        'refer_money' => '参考金额',
                        'refer_money_up' => '参考金额(大写)',
                        'pay_money' => '收款金额',
                        'pay_money_up' => '收款金额(大写)',
                        'change_money' => '找零金额',
                        'change_money_up' => '找零金额(大写)',
                        'discount_money' => '优惠金额',
                        'discount_money_up' => '优惠金额(大写)',
                        'integral' => '获得积分',
                        'sum_integral' => '累积积分',
                        'settlement_name' => '结算方式',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_return_person' => '退货人',
                        'is_return_time' => '退货时间',
                        'is_pending_person' => '挂起人',
                        'is_pending_time' => '挂起时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间',
                        'remark' => '备注',
                        'final_money' => '实收金额',
                        'final_money_up' => '实收金额(大写)',
                        'cut_money' => '抹零金额',
                        'cut_money_up' => '抹零金额(大写)',
                    ),
                    'extra_process_data' => array(
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'final_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'final_money',
                            'function' => 'format_money'
                        ),
                        'cut_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'cut_money',
                            'function' => 'format_money'
                        ),
                        'final_money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'final_money',
                            'function' => 'num2rmb'
                        ),
                        'cut_money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'cut_money',
                            'function' => 'num2rmb'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'refer_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_money',
                            'function' => 'format_money'
                        ),
                        'refer_money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_money',
                            'function' => 'num2rmb'
                        ),
                        'pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'pay_money',
                            'function' => 'format_money'
                        ),
                        'pay_money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'pay_money',
                            'function' => 'num2rmb'
                        ),
                        'change_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_money',
                            'function' => 'format_money'
                        ),
                        'change_money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_money',
                            'function' => 'num2rmb'
                        ),
                        'discount_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'discount_money',
                            'function' => 'format_money'
                        ),
                        'discount_money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'discount_money',
                            'function' => 'num2rmb'
                        ),
                        'shop_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'guide_user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'guide_user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'trade_user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'trade_user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'customer_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'customer_code',
                            'function' => 'get_customer_name_by_code'
                        ),
                        //结算方式
                        'settlement_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'receipt_record_id',
                            'function' => 'get_pos_trade_type_name_by_receipt_record_id'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'sum_integral' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'vip_code',
                            'function' => 'get_vip_integral'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_return_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_return_time',
                            'function' => 'format_day_time'
                        ),
                        'is_pending_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_pending_time',
                            'function' => 'format_day_time'
                        ),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        )
                    ),
                ),
                //打印明细
                'detail_area' => array(
                    'type' => 'list',
                    'title' => '明细区',
                    'table' => 'rbm_receipt_record_detail',
                    'class' => 'MdlReceiptRecord',
                    'class_path' => 'rbm/receipt_record',
                    'class_function' => 'get_detail_list_by_pid',
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码代码',
                        'size_name' => '尺码名称',
                        'price' => '单价',
                        'refer_price' => '参考价',
                        'rebate' => '折扣',
                        'num' => '数量',
                        'money' => '金额',
                        'final_money' => '实收金额',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                        'size_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'size_code',
                            'function' => 'get_size_name_by_code',
                        ),
                        'price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'price',
                            'function' => 'format_money',
                        ),
                        'refer_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_price',
                            'function' => 'format_money',
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'final_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'final_money',
                            'function' => 'format_money'
                        ),
                        'rebate' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'rebate',
                            'function' => 'format_money'
                        ),
                    ),
                ),
                //结算明细
                'settlement_area' => array(
                    'type' => 'list',
                    'title' => '结算明细区',
                    'table' => 'rbm_receipt_record_detail',
                    'class' => 'MdlReceiptRecord',
                    'class_path' => 'rbm/receipt_record',
                    'class_function' => 'get_settlement_list_by_pid',
                    'data' => array(
                        'trade_type_name' => '结算方式',
                        'money' => '结算金额',
                        'coupon_code' => '优惠劵',
                    ),
                    'extra_process_data' => array(
                        'trade_type_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'trade_type_code', //关联字段
                            'function' => 'get_pos_trade_type_name_by_code', //获取的方法
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                    ),
                ),
                'main_area_bottom' => array(
                    'type' => 'grid',
                    'title' => '小票底部',
                    'table' => 'rbm_receipt_record',
                    'class' => 'MdlReceiptRecord',
                    'class_path' => 'rbm/receipt_record',
                    'class_function' => 'get_info_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'record_time' => '业务时间',
                        'shop_code' => '商店代码',
                        'shop_name' => '商店名称',
                        'guide_user_code' => '导购员代码',
                        'guide_user_name' => '导购员名称',
                        'trade_user_code' => '收银员代码',
                        'trade_user_name' => '收银员名称',
                        'customer_code' => '顾客代码',
                        'customer_name' => '顾客名称',
                        'vip_code' => '卡号',
                        'num' => '数量',
                        'money' => '金额',
                        'money_up' => '金额(大写)',
                        'rebate' => '折扣',
                        'refer_money' => '参考金额',
                        'refer_money_up' => '参考金额(大写)',
                        'pay_money' => '收款金额',
                        'pay_money_up' => '收款金额(大写)',
                        'change_money' => '找零金额',
                        'change_money_up' => '找零金额(大写)',
                        'discount_money' => '优惠金额',
                        'discount_money_up' => '优惠金额(大写)',
                        'integral' => '获得积分',
                        'sum_integral' => '累积积分',
                        'settlement_name' => '结算方式',
                        'logo' => '公司logo',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'is_return_person' => '退货人',
                        'is_return_time' => '退货时间',
                        'is_pending_person' => '挂起人',
                        'is_pending_time' => '挂起时间',
                        'is_settlement_person' => '结算人',
                        'is_settlement_time' => '结算时间',
                        'remark' => '备注',
                        'final_money' => '实收金额',
                        'final_money_up' => '实收金额(大写)',
                        'cut_money' => '抹零金额',
                        'cut_money_up' => '抹零金额(大写)',
                    ),
                    'extra_process_data' => array(
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'final_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'final_money',
                            'function' => 'format_money'
                        ),
                        'cut_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'cut_money',
                            'function' => 'format_money'
                        ),
                        'final_money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'final_money',
                            'function' => 'num2rmb'
                        ),
                        'cut_money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'cut_money',
                            'function' => 'num2rmb'
                        ),
                        'money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'num2rmb'
                        ),
                        'refer_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_money',
                            'function' => 'format_money'
                        ),
                        'refer_money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'refer_money',
                            'function' => 'num2rmb'
                        ),
                        'pay_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'pay_money',
                            'function' => 'format_money'
                        ),
                        'pay_money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'pay_money',
                            'function' => 'num2rmb'
                        ),
                        'change_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_money',
                            'function' => 'format_money'
                        ),
                        'change_money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'change_money',
                            'function' => 'num2rmb'
                        ),
                        'discount_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'discount_money',
                            'function' => 'format_money'
                        ),
                        'discount_money_up' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'discount_money',
                            'function' => 'num2rmb'
                        ),
                        'shop_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'shop_code',
                            'function' => 'get_shop_name_by_code'
                        ),
                        'guide_user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'guide_user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'trade_user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'trade_user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'customer_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'customer_code',
                            'function' => 'get_customer_name_by_code'
                        ),
                        //结算方式
                        'settlement_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'receipt_record_id',
                            'function' => 'get_pos_trade_type_name_by_receipt_record_id'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        ),
                        'sum_integral' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'vip_code',
                            'function' => 'get_vip_integral'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_return_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_return_time',
                            'function' => 'format_day_time'
                        ),
                        'is_pending_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_pending_time',
                            'function' => 'format_day_time'
                        ),
                        'is_settlement_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_settlement_time',
                            'function' => 'format_day_time'
                        )
                    ),
                ),
//				'page_bottom_area' => array(
//					'type'=>'grid',
//					'title'=>'页尾',
//					'table'=>'',
//					'class'=>'',
//					'class_path'=>'',
//					'class_function'=>'',
//					'data' => array(
//						'time'=>'时间',
//					),
//					'extra_process_data' => array(
//						'time'=>array(
//							'type'=>'time',
//							'function'=>'add_time'
//						)
//					)
//				),
            )
        ),
        //拣货单标准打印
        'oms_waves_record' => array(
            'title' => '拣货单',
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'record_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '拣货单信息',
                    'table' => 'oms_waves_record',
                    'class' => 'WavesRecordModel',
                    'class_path' => 'oms/WavesRecordModel',
                    'class_function' => 'get_record_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        //'user_code' => '业务员代码',
                        //'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'picker' => '拣货员',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'sell_record_count' => '订单数量',
                        'valide_goods_count' => '数量',
                        'total_amount' => '金额',
                        //'is_add_person' => '新增人',
                        //'is_add_time' => '新增时间',
                        //'is_edit_person' => '修改人',
                        //'is_edit_time' => '修改时间',
                        //'is_check_person' => '审核人',
                        //'is_check_time' => '审核时间',
                        //'is_sure_person' => '执行人',
                        //'is_sure_time' => '执行时间',
                        //'is_cancel_person' => '作废人',
                        //'is_cancel_time' => '作废时间',
                        //'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'total_amount' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'total_amount',
                            'function' => 'format_money'
                        ),
                    ),
                ),
                //打印明细
                'detail_area' => array(
                    'type' => 'list',
                    'title' => '明细区',
                    'table' => 'oms_waves_record_detail',
                    'class' => 'WavesRecordModel',
                    'class_path' => 'oms/WavesRecordModel',
                    'class_function' => 'get_deliver_detail_list_by_pid',
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'spec1_code' => '规格1代码',
                        'spec1_name' => '规格1',
                        'spec2_code' => '规格2代码',
                        'spec2_name' => '规格2',
                        'sku' => 'sku',
                        'goods_price' => '价格',
                        'num' => '数量',
                        'avg_money' => '金额',
                        'lof_no' => '批次号',
                        'shelf_code' => '货架代码',
                    ),
                    'extra_process_data' => array(
                        'goods_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'goods_price',
                            'function' => 'format_money',
                        ),
                        'avg_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'avg_money',
                            'function' => 'format_money'
                        )
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'record_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
            )
        ),
        //拣货单扩展打印
        'ext_order_pick_up_record' => array(
            'title' => '拣货单',
            'property' => 'record_ext',
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '拣货单信息',
                    'table' => 'order_pick_up_record',
                    'class' => 'MdlPickUpRecord',
                    'class_path' => 'order/pick_up_record',
                    'class_function' => 'get_pick_up_record_by_id',
                    'data' => array(
                        'record_code' => '单据编号',
                        'user_code' => '业务员代码',
                        'user_name' => '业务员名称',
                        'record_time' => '业务时间',
                        'pick_up_user_name' => '拣货员',
                        'store_code' => '仓库代码',
                        'store_name' => '仓库名称',
                        'order_num' => '订单数量',
                        'num' => '数量',
                        'money' => '金额',
                        'is_add_person' => '新增人',
                        'is_add_time' => '新增时间',
                        'is_edit_person' => '修改人',
                        'is_edit_time' => '修改时间',
                        'is_check_person' => '审核人',
                        'is_check_time' => '审核时间',
                        'is_sure_person' => '执行人',
                        'is_sure_time' => '执行时间',
                        'is_cancel_person' => '作废人',
                        'is_cancel_time' => '作废时间',
                        'remark' => '备注',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),
                        'refer_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'refer_time',
                            'function' => 'format_day_time'
                        ),
                        'money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'money',
                            'function' => 'format_money'
                        ),
                        'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),
                        'store_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'store_code',
                            'function' => 'get_store_name_by_code'
                        ),
                        'is_add_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_add_time',
                            'function' => 'format_day_time'
                        ),
                        'is_edit_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_edit_time',
                            'function' => 'format_day_time'
                        ),
                        'is_sure_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_sure_time',
                            'function' => 'format_day_time'
                        ),
                        'is_cancel_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_cancel_time',
                            'function' => 'format_day_time'
                        ),
                        'is_check_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'is_stop_time',
                            'function' => 'format_day_time'
                        ),
                        'pick_up_user_name' => array(
                            'type' => 'format_day_time',
                            'relation_col' => 'pick_up_user_code',
                            'function' => 'get_user_name_by_code'
                        )
                    ),
                ),
                //扩展打印明细
                'ext_detail_area' => array(
                    'type' => 'list',
                    'title' => '扩展明细区',
                    'table' => 'order_pick_up_record_detail',
                    'class' => 'MdlPickUpRecord',
                    'class_path' => 'order/pick_up_record',
                    'class_function' => 'get_pick_up_detail_by_pid',
                    'property' => 'record_ext', //单据明细扩展打印
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'color_code' => '颜色代码',
                        'color_name' => '颜色名称',
                        'size_code' => '尺码名称[扩展]',
                        'num' => '数量',
                        'sku' => 'sku',
                        'shelf_code' => '货架代码',
                    ),
                    'ext_col' => 'size_code',
                    'ext_extra_process_data' => array(
                        'num' => 'plus',
                    ),
                    'extra_process_data' => array(
                        'goods_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_name_by_code', //获取的方法
                        ),
                        'goods_short_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'goods_code', //关联字段
                            'function' => 'get_goods_short_name_by_code', //获取的方法
                        ),
                        'color_name' => array(
                            'type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
                            'relation_col' => 'color_code', //关联字段
                            'function' => 'get_color_name_by_code', //获取的方法
                        ),
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        'logo' => '公司logo',
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
            )
        ),
        //订单打印
        'oms_deliver_record' => array(
            'title' => '发货单',
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                        // 'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'record_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
                    'type' => 'grid',
                    'title' => '发货单信息',
                    'table' => 'oms_deliver_record',
                    'class' => 'DeliverRecordModel',
                    'class_path' => 'oms/DeliverRecordModel',
                    'class_function' => 'get_record_by_id',
                    'data' => array(
	                    'sell_record_code' => '单据编号',
                        //'user_code' => '业务员代码',
                        //'user_name' => '业务员名称',
	                    'record_time' => '下单时间',
	                    'pay_time' => '支付时间',
	                    'buyer_name' => '买家名称',
	                    'receiver_name' => '收货人名称',
	                    'receiver_address' => '地址',
	                    'receiver_zip_code' => '邮政编码',
	                    'receiver_phone' => '电话',
	                    'receiver_mobile' => '手机',
	                    'receiver_email' => '邮箱',
	                    'goods_num' => '数量',
	                    'order_money' => '金额',
	                    //'sell_money' => '商品金额',
	                    'goods_money' => '商品金额',
	                    'express_money' => '运费',
	                    'payable_money' => '总金额',
	                    'paid_money' => '已付金额',
	                    'buy_remark' => '买家留言',
	                    'sell_remark' => '商家备注',
	                    'order_remark' => '订单备注',
	                    //'server_remark' => '客服备注',
	                    //'store_remark' => '仓库留言',
                    ),
                    'extra_process_data' => array(
                        'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),

	                    'pay_time' => array(
		                    'type' => 'format_day_time', //格式化时间
		                    'relation_col' => 'pay_time',
		                    'function' => 'format_day_time'
	                    ),
                        'order_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'order_money',
                            'function' => 'format_money'
                        ),
                        'goods_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'goods_money',
                            'function' => 'format_money'
                        ),
                        'payable_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'payable_money',
                            'function' => 'format_money'
                        ),
	                    'paid_money' => array(
		                    'type' => 'format_money', //格式化金钱
		                    'relation_col' => 'paid_money',
		                    'function' => 'format_money'
	                    ),
                        'express_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'express_money',
                            'function' => 'format_money'
                        ),
                        /*'sell_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'sell_money',
                            'function' => 'format_money'
                        ),*/
                        /*'user_name' => array(
                            'type' => 'process_get_name_by_code',
                            'relation_col' => 'user_code',
                            'function' => 'get_user_name_by_code'
                        ),*/
                    ),
                ),
                //打印明细
                'detail_area' => array(
                    'type' => 'list',
                    'title' => '订单明细',
                    'table' => 'oms_deliver_record_detail',
                    'class' => 'DeliverRecordModel',
                    'class_path' => 'oms/DeliverRecordModel',
                    'class_function' => 'get_detail_list_by_pid',
                    'data' => array(
                        'goods_code' => '商品代码',
                        'goods_name' => '商品名称',
                        'goods_short_name' => '商品简称',
                        'spec1_code' => '规格1代码',
                        'spec1_name' => '规格1名称',
                        'spec2_code' => '规格2代码',
                        'spec2_name' => '规格2名称',
                        //'refer_price' => '吊牌价',
                        'goods_price' => '单价',
                        'num' => '数量',
                        //'return_num' => '退货数量',
                        'avg_money' => '金额',
                    //'shelf_code' => '货架代码'
                    ),
                    'extra_process_data' => array(
                        'goods_price' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'goods_price',
                            'function' => 'format_money',
                        ),
                        'avg_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'avg_money',
                            'function' => 'format_money'
                        )
                    ),
                ),
				//合计
	            'sum_area' => array(
		            'type' => 'grid',
		            'title' => '合计',
		            'table' => 'oms_deliver_record',
		            'class' => 'DeliverRecordModel',
		            'class_path' => 'oms/DeliverRecordModel',
		            'class_function' => 'get_record_by_id',
		            'data' => array(
			            'record_code' => '单据编号',
                        //'user_code' => '业务员代码',
                        //'user_name' => '业务员名称',
			            'record_time' => '下单时间',
			            'pay_time' => '支付时间',
			            'buyer_name' => '买家名称',
			            'receiver_name' => '收货人名称',
			            'receiver_address' => '地址',
			            'receiver_zip_code' => '邮政编码',
			            'receiver_phone' => '电话',
			            'receiver_mobile' => '手机',
			            'receiver_email' => '邮箱',
			            'num' => '数量',
			            'order_money' => '金额',
			            //'sell_money' => '商品金额',
			            'goods_money' => '商品金额',
			            'preferential_margins' => '优惠金额',
			            'express_money' => '运费',
			            'payable_money' => '总金额',
			            'paid_money' => '已付金额',
			            'buy_remark' => '买家留言',
			            'sell_remark' => '商家备注',
			            'order_remark' => '订单备注',
			            'server_remark' => '客服备注',
			            'store_remark' => '仓库留言',
		            ),
		            'extra_process_data' => array(
                        /*'record_time' => array(
                            'type' => 'format_day_time', //格式化时间
                            'relation_col' => 'record_time',
                            'function' => 'format_day_time'
                        ),*/
                        'order_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'order_money',
                            'function' => 'format_money'
                        ),
                        'goods_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'goods_money',
                            'function' => 'format_money'
                        ),
                        'payable_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'payable_money',
                            'function' => 'format_money'
                        ),
	                    'paid_money' => array(
		                    'type' => 'format_money', //格式化金钱
		                    'relation_col' => 'paid_money',
		                    'function' => 'format_money'
	                    ),
                        'express_money' => array(
                            'type' => 'format_money', //格式化金钱
                            'relation_col' => 'express_money',
                            'function' => 'format_money'
                        ),
			            'preferential_margins' => array(
				            'type' => 'operation', //运算
				            'relation_col' => 'goods_rebate_money,other_rebate_money',
				            'operation_type' => 'plus',//加法
				            'function' => 'format_money',
			            ),
		            ),
	            ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                    //    'logo' => '公司logo',
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
            )
        ),
        //订单扩展打印
        'ext_oms_sell_record' => array(
            'title' => '订单',
            'property' => 'record_ext',
            'main_conf' => array(
                'page_top_area' => array(
                    'type' => 'grid',
                    'title' => '页眉',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                    //    'logo' => '公司logo'
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
                'main_area' => array(
	                'type' => 'grid',
	                'title' => '订单信息',
	                'table' => 'oms_sell_record',
	                'class' => 'SellRecordModel',
	                'class_path' => 'oms/SellRecordModel',
	                'class_function' => 'get_record_by_id',
	                'data' => array(
		                'record_code' => '单据编号',
//                        'user_code' => '业务员代码',
//                        'user_name' => '业务员名称',
		                'record_time' => '下单时间',
		                'buy_name' => '买家名称',
		                'receiver_name' => '收货人名称',
		                'receiver_address' => '地址',
		                'receiver_zip_code' => '邮政编码',
		                'receiver_phone' => '电话',
		                'receiver_mobile' => '手机',
		                'receiver_email' => '邮箱',
		                'num' => '数量',
		                'order_money' => '金额',
		                'sell_money' => '商品金额',
		                'goods_money' => '折后商品总额',
		                'express_money' => '快递金额',
		                'payable_money' => '应付金额',
		                'paid_money' => '已付金额',
		                'buy_remark' => '买家留言',
		                'sell_remark' => '商家备注',
		                'order_remark' => '订单备注',
		                'server_remark' => '客服备注',
		                'store_remark' => '仓库留言',
	                ),
                    'extra_process_data' => array(
//                        'record_time' => array(
//                            'type' => 'format_day_time', //格式化时间
//                            'relation_col' => 'record_time',
//                            'function' => 'format_day_time'
//                        ),
//                        'order_money' => array(
//                            'type' => 'format_money', //格式化金钱
//                            'relation_col' => 'order_money',
//                            'function' => 'format_money'
//                        ),
//                        'goods_money' => array(
//                            'type' => 'format_money', //格式化金钱
//                            'relation_col' => 'goods_money',
//                            'function' => 'format_money'
//                        ),
//                        'sell_money' => array(
//                            'type' => 'format_money', //格式化金钱
//                            'relation_col' => 'sell_money',
//                            'function' => 'format_money'
//                        ),
//                        'express_money' => array(
//                            'type' => 'format_money', //格式化金钱
//                            'relation_col' => 'express_money',
//                            'function' => 'format_money'
//                        ),
//                        'order_pay_money' => array(
//                            'type' => 'format_money', //格式化金钱
//                            'relation_col' => 'order_pay_money',
//                            'function' => 'format_money'
//                        ),
//                        'pay_money' => array(
//                            'type' => 'format_money', //格式化金钱
//                            'relation_col' => 'pay_money',
//                            'function' => 'format_money'
//                        ),
//                        'user_name' => array(
//                            'type' => 'process_get_name_by_code',
//                            'relation_col' => 'user_code',
//                            'function' => 'get_user_name_by_code'
//                        ),
                    ),
                ),
                //打印明细
                'detail_area' => array(
	                'type' => 'list',
	                'title' => '订单明细',
	                'table' => 'oms_sell_record_detail',
	                'class' => 'SellRecordModel',
	                'class_path' => 'oms/SellRecordModel',
	                'class_function' => 'get_detail_list_by_pid',
                    'property' => 'record_ext', //单据明细扩展打印
                    'data' => array(
	                    'goods_code' => '商品代码',
	                    'goods_name' => '商品名称',
	                    'goods_short_name' => '商品简称',
	                    'spec1_code' => '规格1代码',
	                    'spec1_name' => '规格1名称',
	                    'spec2_code' => '尺码代码[扩展]',
	                    'refer_price' => '参考价',
	                    'goods_price' => '单价',
	                    'num' => '数量',
	                    'return_num' => '退货数量',
	                    'count_money' => '金额',
                    ),
                    'ext_col' => 'spec2_code',
                    'ext_extra_process_data' => array(
                        'num' => 'plus',
                    ),
                    'extra_process_data' => array(
	                    'goods_name' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'get_col' => 'goods_name',
		                    'relation_model_params' => 'goods_code',
		                    'relation_model_class' => 'GoodsModel',
		                    'relation_model_path' => 'prm/GoodsModel',
		                    'relation_model_function' => 'get_by_goods_code',
	                    ),
	                    'goods_short_name' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'get_col' => 'goods_short_name',
		                    'relation_model_params' => 'goods_code',
		                    'relation_model_class' => 'GoodsModel',
		                    'relation_model_path' => 'prm/GoodsModel',
		                    'relation_model_function' => 'get_by_goods_code',
	                    ),
	                    'spec1_name' => array(
		                    'type' => 'process_get_by_relation_model', //关联model获取数据
		                    'get_col' => 'spec1_name',
		                    'relation_model_params' => 'spec1_code',
		                    'relation_model_class' => 'Spec1Model',
		                    'relation_model_path' => 'prm/Spec1Model',
		                    'relation_model_function' => 'get_by_code',
	                    ),
                    ),
                ),
                'page_bottom_area' => array(
                    'type' => 'grid',
                    'title' => '页尾',
                    'table' => '',
                    'class' => '',
                    'class_path' => '',
                    'class_function' => '',
                    'data' => array(
                        'time' => '时间',
                //        'logo' => '公司logo',
                    ),
                    'extra_process_data' => array(
                        'time' => array(
                            'type' => 'time',
                            'function' => 'add_time'
                        ),
                        'logo' => array(
                            'type' => 'logo',
                        )
                    )
                ),
            )
        ),

	    //进货付款单
	    'acc_buy_pay_record' => array(
		    'title' => '进货付款单',
		    'main_conf' => array(
			    'page_top_area' => array(
				    'type' => 'grid',
				    'title' => '页眉',
				    'table' => '',
				    'class' => '',
				    'class_path' => '',
				    'class_function' => '',
				    'data' => array(
					    'time' => '时间',
					    'logo' => '公司logo'
				    ),
				    'extra_process_data' => array(
					    'time' => array(
						    'type' => 'time',
						    'function' => 'add_time'
					    ),
					    'logo' => array(
						    'type' => 'logo',
					    )
				    )
			    ),
			    'main_area' => array(
				    'type' => 'grid',
				    'title' => '付款单信息',
				    'table' => 'acc_buy_pay_record',
				    'class' => 'MdlBuyPayRecord',
				    'class_path' => 'acc/buy_pay_record',
				    'class_function' => 'get_info_by_id',
				    'data' => array(
					    'record_code' => '单据编号',
					    'supplier_code' => '供应商代码',
					    'supplier_name' => '供应商名称',
					    'user_code' => '业务员代码',
					    'user_name' => '业务员名称',
					    'record_time' => '业务时间',
					    'pay_money' => '本次付款',
					    'pay_total_money' => '应付款总额',
					    'total_money' => '总付款额',
					    'able_balance_money' => '可用结余款',
					    'balance_money' => '使用结余款',
					    'diff_money' => '应付差异额',
					    'finance_debts_money' => '累计欠款',
					    'finance_debts_money_up' => '累计欠款(大写)',
					    'before_debts_money' => '上次欠款',
					    'before_debts_money_up' => '上次欠款(大写)',
					    'after_debts_money' => '当日欠款',
					    'after_debts_money_up' => '当日欠款(大写)',
					    'remark' => '备注',
				    ),
				    'extra_process_data' => array(
					    'record_time' => array(
						    'type' => 'format_day_time', //格式化时间
						    'relation_col' => 'record_time',
						    'function' => 'format_day_time'
					    ),
					    'refer_time' => array(
						    'type' => 'format_day_time', //格式化时间
						    'relation_col' => 'refer_time',
						    'function' => 'format_day_time'
					    ),
					    'supplier_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'supplier_code',
						    'function' => 'get_supplier_name_by_code'
					    ),
					    'user_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'user_code',
						    'function' => 'get_user_name_by_code'
					    ),

					    'pay_money' => array(
						    'type' => 'format_money', //格式化金钱
						    'relation_col' => 'pay_money',
						    'function' => 'deal_money'
					    ),
					    'pay_total_money' => array(
						    'type' => 'format_money', //格式化金钱
						    'relation_col' => 'pay_total_money',
						    'function' => 'deal_money'
					    ),
					    'total_money' => array(
						    'type' => 'format_money', //格式化金钱
						    'relation_col' => 'total_money',
						    'function' => 'deal_money'
					    ),
					    'able_balance_money' => array(
						    'type' => 'format_money', //格式化金钱
						    'relation_col' => 'able_balance_money',
						    'function' => 'deal_money'
					    ),
					    'balance_money' => array(
						    'type' => 'format_money', //格式化金钱
						    'relation_col' => 'balance_money',
						    'function' => 'deal_money'
					    ),
					    'diff_money' => array(
						    'type' => 'format_money', //格式化金钱
						    'relation_col' => 'diff_money',
						    'function' => 'deal_money'
					    ),
					    'finance_debts_money' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'supplier_code',
						    'get_array_key' => 'debts_money',
						    'function' => 'get_supplier_general_ledger_by_code'
					    ),
					    'finance_debts_money_up' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'supplier_code',
						    'get_array_key' => 'debts_money',
						    'function' => 'get_supplier_general_ledger_by_code',
						    'second_function' => 'num2rmb'
					    ),
					    'before_debts_money' => array(
						    'type' => 'process_get_by_relation_model', //关联model获取数据
						    'function' => 'format_money',
						    'get_col' => 'before_debts_money',
						    'relation_model_params' => 'supplier_code, record_code',
						    'relation_model_class' => 'MdlSupplierGeneralJournal',
						    'relation_model_path' => 'acc/supplier_general_journal',
						    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code'
					    ),
					    'before_debts_money_up' => array(
						    'type' => 'process_get_by_relation_model', //关联model获取数据
						    'function' => 'format_money',
						    'get_col' => 'before_debts_money',
						    'relation_model_params' => 'supplier_code, record_code',
						    'relation_model_class' => 'MdlSupplierGeneralJournal',
						    'relation_model_path' => 'acc/supplier_general_journal',
						    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code',
						    'second_function' => 'num2rmb'
					    ),
					    'after_debts_money' => array(
						    'type' => 'process_get_by_relation_model', //关联model获取数据
						    'function' => 'format_money',
						    'get_col' => 'after_debts_money',
						    'relation_model_params' => 'supplier_code, record_code',
						    'relation_model_class' => 'MdlSupplierGeneralJournal',
						    'relation_model_path' => 'acc/supplier_general_journal',
						    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code'
					    ),
					    'after_debts_money_up' => array(
						    'type' => 'process_get_by_relation_model', //关联model获取数据
						    'function' => 'format_money',
						    'get_col' => 'after_debts_money',
						    'relation_model_params' => 'supplier_code, record_code',
						    'relation_model_class' => 'MdlSupplierGeneralJournal',
						    'relation_model_path' => 'acc/supplier_general_journal',
						    'relation_model_function' => 'get_info_by_supplier_code_and_relation_code',
						    'second_function' => 'num2rmb'
					    ),

				    ),
			    ),

			    //打印明细
			    'detail_area' => array(
				    'type' => 'list',
				    'title' => '明细区',
				    'table' => 'acc_buy_pay_record_detail',
				    'class' => 'MdlBuyPayRecord',
				    'class_path' => 'acc/buy_pay_record',
				    'class_function' => 'get_detail_list_by_pid',
				    'data' => array(
					    'record_type' => '单据类型',
					    'relation_code' => '关联单据',
					    'record_time' => '业务日期',
					    'record_money' => '单据金额',
					    'record_pay_money' => '已付款',
					    'record_no_pay_money' => '未付款',
					    'pay_money' => '本次付款',
					    'record_balance_money' => '单据余额',
					    'balance_money' => '余额',
				    ),
				    'extra_process_data' => array(
					    'record_type' => array(
						    'record_type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
						    'relation_col' => 'record_type', //关联字段
						    'function' => 'get_record_type_name', //获取的方法
					    ),
					    'record_time' => array(
						    'type' => 'format_day_time', //通过函数或者获取数据比如通过颜色代码显示颜色名称
						    'relation_col' => 'record_time', //关联字段
						    'function' => 'format_day_time', //获取的方法
					    ),
					    'record_money' => array(
						    'type' => 'format_money', //通过函数或者获取数据比如通过颜色代码显示颜色名称
						    'relation_col' => 'record_money', //关联字段
						    'function' => 'format_money', //获取的方法
					    ),
					    'record_pay_money' => array(
						    'type' => 'format_money', //通过函数或者获取数据比如通过颜色代码显示颜色名称
						    'relation_col' => 'record_pay_money', //关联字段
						    'function' => 'format_money', //获取的方法
					    ),
					    'record_no_pay_money' => array(
						    'type' => 'format_money', //通过函数或者获取数据比如通过颜色代码显示颜色名称
						    'relation_col' => 'record_no_pay_money', //关联字段
						    'function' => 'format_money', //获取的方法
					    ),
					    'pay_money' => array(
						    'type' => 'format_money', //通过函数或者获取数据比如通过颜色代码显示颜色名称
						    'relation_col' => 'pay_money', //关联字段
						    'function' => 'format_money', //获取的方法
					    ),
					    'record_balance_money' => array(
						    'type' => 'format_money', //通过函数或者获取数据比如通过颜色代码显示颜色名称
						    'relation_col' => 'record_balance_money', //关联字段
						    'function' => 'format_money', //获取的方法
					    ),
					    'balance_money' => array(
						    'type' => 'format_money', //通过函数或者获取数据比如通过颜色代码显示颜色名称
						    'relation_col' => 'balance_money', //关联字段
						    'function' => 'format_money', //获取的方法
					    ),





				    ),
			    ),

			    'page_bottom_area' => array(
				    'type' => 'grid',
				    'title' => '页尾',
				    'table' => 'acc_buy_pay_record',
				    'class' => 'MdlBuyPayRecord',
				    'class_path' => 'acc/buy_pay_record',
				    'class_function' => 'get_info_by_id',
				    'data' => array(
					    'time' => '时间',
					    'logo' => '公司logo',
					    'is_add_person' => '新增人',
					    'is_add_time' => '新增时间',
					    'is_edit_person' => '修改人',
					    'is_edit_time' => '修改时间',
					    'is_keep_accounts_person' => '记账人',
					    'is_keep_accounts_time' => '记账时间',
				    ),
				    'extra_process_data' => array(
					    'time' => array(
						    'type' => 'time',
						    'function' => 'add_time'
					    ),
					    'logo' => array(
						    'type' => 'logo',
					    ),
					    'is_add_time' => array(
						    'type' => 'format_day_time', //格式化时间
						    'relation_col' => 'is_add_time',
						    'function' => 'format_day_time'
					    ),
					    'is_edit_time' => array(
						    'type' => 'format_day_time', //格式化时间
						    'relation_col' => 'is_edit_time',
						    'function' => 'format_day_time'
					    ),
					    'is_keep_accounts_time' => array(
						    'type' => 'format_day_time', //格式化时间
						    'relation_col' => 'is_keep_accounts_time',
						    'function' => 'format_day_time'
					    ),
				    )
			    ),
		    )
	    ),

	    //销货收款单
	    'acc_sell_receivable_record' => array(
		    'title' => '销货收款单',
		    'main_conf' => array(
			    'page_top_area' => array(
				    'type' => 'grid',
				    'title' => '页眉',
				    'table' => '',
				    'class' => '',
				    'class_path' => '',
				    'class_function' => '',
				    'data' => array(
					    'time' => '时间',
					    'logo' => '公司logo'
				    ),
				    'extra_process_data' => array(
					    'time' => array(
						    'type' => 'time',
						    'function' => 'add_time'
					    ),
					    'logo' => array(
						    'type' => 'logo',
					    )
				    )
			    ),
			    'main_area' => array(
				    'type' => 'grid',
				    'title' => '收款单信息',
				    'table' => 'acc_sell_receivable_record',
				    'class' => 'MdlSellReceivableRecord',
				    'class_path' => 'acc/sell_receivable_record',
				    'class_function' => 'get_info_by_id',
				    'data' => array(
					    'record_code' => '单据编号',
					    'distributor_code' => '客户代码',
					    'distributor_name' => '客户名称',
					    'user_code' => '业务员代码',
					    'user_name' => '业务员名称',
					    'record_time' => '业务时间',
					    'pay_money' => '本次收款',
					    'pay_total_money' => '应收款总额',
					    'total_money' => '总收款额',
					    'able_balance_money' => '可用结余款',
					    'balance_money' => '使用结余款',
					    'diff_money' => '应付差异额',
					    'finance_debts_money' => '累计欠款',
					    'finance_debts_money_up' => '累计欠款(大写)',
					    'before_debts_money' => '上次欠款',
					    'before_debts_money_up' => '上次欠款(大写)',
					    'after_debts_money' => '当日欠款',
					    'after_debts_money_up' => '当日欠款(大写)',
					    'remark' => '备注',
				    ),
				    'extra_process_data' => array(
					    'record_time' => array(
						    'type' => 'format_day_time', //格式化时间
						    'relation_col' => 'record_time',
						    'function' => 'format_day_time'
					    ),
					    'refer_time' => array(
						    'type' => 'format_day_time', //格式化时间
						    'relation_col' => 'refer_time',
						    'function' => 'format_day_time'
					    ),
					    'distributor_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'distributor_code',
						    'function' => 'get_distributor_name_by_code'
					    ),
					    'user_name' => array(
						    'type' => 'process_get_name_by_code',
						    'relation_col' => 'user_code',
						    'function' => 'get_user_name_by_code'
					    ),

					    'pay_money' => array(
						    'type' => 'format_money', //格式化金钱
						    'relation_col' => 'pay_money',
						    'function' => 'deal_money'
					    ),
					    'pay_total_money' => array(
						    'type' => 'format_money', //格式化金钱
						    'relation_col' => 'pay_total_money',
						    'function' => 'deal_money'
					    ),
					    'total_money' => array(
						    'type' => 'format_money', //格式化金钱
						    'relation_col' => 'total_money',
						    'function' => 'deal_money'
					    ),
					    'able_balance_money' => array(
						    'type' => 'format_money', //格式化金钱
						    'relation_col' => 'able_balance_money',
						    'function' => 'deal_money'
					    ),
					    'balance_money' => array(
						    'type' => 'format_money', //格式化金钱
						    'relation_col' => 'balance_money',
						    'function' => 'deal_money'
					    ),
					    'diff_money' => array(
						    'type' => 'format_money', //格式化金钱
						    'relation_col' => 'diff_money',
						    'function' => 'deal_money'
					    ),
					    'finance_debts_money' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'distributor_code',
						    'get_array_key' => 'debts_money',
						    'function' => 'get_distributor_general_ledger_by_code'
					    ),
					    'finance_debts_money_up' => array(
						    'type' => 'process_get_array_by_code',
						    'relation_col' => 'distributor_code',
						    'get_array_key' => 'debts_money',
						    'function' => 'get_distributor_general_ledger_by_code',
						    'second_function' => 'num2rmb'
					    ),
					    'before_debts_money' => array(
						    'type' => 'process_get_by_relation_model', //关联model获取数据
						    'function' => 'format_money',
						    'get_col' => 'before_debts_money',
						    'relation_model_params' => 'distributor_code, record_code',
						    'relation_model_class' => 'MdlDistributorGeneralJournal',
						    'relation_model_path' => 'acc/distributor_general_journal',
						    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code'
					    ),
					    'before_debts_money_up' => array(
						    'type' => 'process_get_by_relation_model', //关联model获取数据
						    'function' => 'format_money',
						    'get_col' => 'before_debts_money',
						    'relation_model_params' => 'distributor_code, record_code',
						    'relation_model_class' => 'MdlDistributorGeneralJournal',
						    'relation_model_path' => 'acc/distributor_general_journal',
						    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code',
						    'second_function' => 'num2rmb'
					    ),
					    'after_debts_money' => array(
						    'type' => 'process_get_by_relation_model', //关联model获取数据
						    'function' => 'format_money',
						    'get_col' => 'after_debts_money',
						    'relation_model_params' => 'distributor_code, record_code',
						    'relation_model_class' => 'MdlDistributorGeneralJournal',
						    'relation_model_path' => 'acc/distributor_general_journal',
						    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code'
					    ),
					    'after_debts_money_up' => array(
						    'type' => 'process_get_by_relation_model', //关联model获取数据
						    'function' => 'format_money',
						    'get_col' => 'after_debts_money',
						    'relation_model_params' => 'distributor_code, record_code',
						    'relation_model_class' => 'MdlDistributorGeneralJournal',
						    'relation_model_path' => 'acc/distributor_general_journal',
						    'relation_model_function' => 'get_info_by_distributor_code_and_relation_code',
						    'second_function' => 'num2rmb'
					    ),
				    ),
			    ),

			    //打印明细
			    'detail_area' => array(
				    'type' => 'list',
				    'title' => '明细区',
				    'table' => 'acc_sell_receivable_record',
				    'class' => 'MdlSellReceivableRecord',
				    'class_path' => 'acc/sell_receivable_record',
				    'class_function' => 'get_detail_list_by_pid',
				    'data' => array(
					    'record_type' => '单据类型',
					    'relation_code' => '关联单据',
					    'record_time' => '业务日期',
					    'record_money' => '单据金额',
					    'record_pay_money' => '已收款',
					    'record_no_pay_money' => '未收款',
					    'pay_money' => '本次收款',
					    'record_balance_money' => '单据余额',
					    'balance_money' => '余额',
				    ),
				    'extra_process_data' => array(
					    'record_type' => array(
						    'record_type' => 'process_get_name_by_code', //通过函数或者获取数据比如通过颜色代码显示颜色名称
						    'relation_col' => 'record_type', //关联字段
						    'function' => 'get_record_type_name', //获取的方法
					    ),
					    'record_time' => array(
						    'type' => 'format_day_time', //通过函数或者获取数据比如通过颜色代码显示颜色名称
						    'relation_col' => 'record_time', //关联字段
						    'function' => 'format_day_time', //获取的方法
					    ),
					    'record_money' => array(
						    'type' => 'format_money', //通过函数或者获取数据比如通过颜色代码显示颜色名称
						    'relation_col' => 'record_money', //关联字段
						    'function' => 'format_money', //获取的方法
					    ),
					    'record_pay_money' => array(
						    'type' => 'format_money', //通过函数或者获取数据比如通过颜色代码显示颜色名称
						    'relation_col' => 'record_pay_money', //关联字段
						    'function' => 'format_money', //获取的方法
					    ),
					    'record_no_pay_money' => array(
						    'type' => 'format_money', //通过函数或者获取数据比如通过颜色代码显示颜色名称
						    'relation_col' => 'record_no_pay_money', //关联字段
						    'function' => 'format_money', //获取的方法
					    ),
					    'pay_money' => array(
						    'type' => 'format_money', //通过函数或者获取数据比如通过颜色代码显示颜色名称
						    'relation_col' => 'pay_money', //关联字段
						    'function' => 'format_money', //获取的方法
					    ),
					    'record_balance_money' => array(
						    'type' => 'format_money', //通过函数或者获取数据比如通过颜色代码显示颜色名称
						    'relation_col' => 'record_balance_money', //关联字段
						    'function' => 'format_money', //获取的方法
					    ),
					    'balance_money' => array(
						    'type' => 'format_money', //通过函数或者获取数据比如通过颜色代码显示颜色名称
						    'relation_col' => 'balance_money', //关联字段
						    'function' => 'format_money', //获取的方法
					    ),
				    ),
			    ),

			    'page_bottom_area' => array(
				    'type' => 'grid',
				    'title' => '页尾',
				    'table' => 'acc_buy_pay_record',
				    'class' => 'MdlBuyPayRecord',
				    'class_path' => 'acc/buy_pay_record',
				    'class_function' => 'get_info_by_id',
				    'data' => array(
					    'time' => '时间',
					    'logo' => '公司logo',
					    'is_add_person' => '新增人',
					    'is_add_time' => '新增时间',
					    'is_edit_person' => '修改人',
					    'is_edit_time' => '修改时间',
					    'is_keep_accounts_person' => '记账人',
					    'is_keep_accounts_time' => '记账时间',
				    ),
				    'extra_process_data' => array(
					    'time' => array(
						    'type' => 'time',
						    'function' => 'add_time'
					    ),
					    'logo' => array(
						    'type' => 'logo',
					    ),
					    'is_add_time' => array(
						    'type' => 'format_day_time', //格式化时间
						    'relation_col' => 'is_add_time',
						    'function' => 'format_day_time'
					    ),
					    'is_edit_time' => array(
						    'type' => 'format_day_time', //格式化时间
						    'relation_col' => 'is_edit_time',
						    'function' => 'format_day_time'
					    ),
					    'is_keep_accounts_time' => array(
						    'type' => 'format_day_time', //格式化时间
						    'relation_col' => 'is_keep_accounts_time',
						    'function' => 'format_day_time'
					    ),
				    )
			    ),
		    )
	    ),
    );
    //纸张类型
    var $page_style = array(
        'A3' => array('width' => '297', 'height' => '420'),
        'A4' => array('width' => '210', 'height' => '297'),
        'A5' => array('width' => '148', 'height' => '210'),
        'B5' => array('width' => '182', 'height' => '257'),
        'B6' => array('width' => '125', 'height' => '176')
    );

	public function __construct($table = '', $db = '') {
		parent :: __construct('danju_print');
	}

    /**
     * 单据模板列表
     * @param param 列表系统参数
     */
    function list_danju_print($cols = '*') {

        $this->mapper->cols($cols);
        return $this->mapper->find_all_by();
    }

    /**
     * 商店打印模板列表
     * @param string $cols
     */
    function list_danju_shop_print($cols = '*') {

        $this->shop_mapper->cols($cols);
        return $this->mapper->find_all_by();
    }

    /**
     * sql 单据打印列表 新框架
     * @param $params
     * @return array
     */
    function get_print_data_type_by_page($filter) {

        $sql_values = array();
	    $sql_main = " from {$this->table} where is_enable=1 group by print_data_type";

	    $select = '*';
	    $data = $this -> get_page_from_sql($filter, $sql_main, $sql_values, $select, true);

	    foreach($data['data'] as $_key => $_val) {

		    $data['data'][$_key]['print_data_type_name'] = get_print_data_type_name($_val['print_data_type']);
	    }

	    $ret_status = OP_SUCCESS;
	    $ret_data = $data;

	    return $this -> format_ret($ret_status, $ret_data);
    }

	/**
	 * @param $print_data_type 新框架
	 * @param array $filter
	 * @return array
	 */
	function get_list_by_type($print_data_type, $filter = array()) {

		$select = '*';
		$sql_main = "FROM {$this->table} WHERE print_data_type='{$print_data_type}' and is_enable=1";
		$data =  $this->get_page_from_sql($filter, $sql_main, array(), $select);

		$ret_status = OP_SUCCESS;
		$ret_data = $data;

		filter_fk_name($ret_data['data'], array('print_id'));

		return $this->format_ret($ret_status, $ret_data);
	}

    /**
     * 获取商店打印列表通过打印类型
     * @param array $params
     * @return array
     */
    function do_danju_shop_index_by_print_data_type($print_data_type) {

        $sql_value = array();

        $sql = 'select * from ' . table($this->shop_table) . " where print_data_type='" . $print_data_type . "' group by shop_code order by shop_print_id";

        return array('sql_value' => $sql_value, 'sql' => $sql);
    }

    /**
     * 通过打印类型获取sql
     * @param $type
     * @return string
     */
    function return_sql_by_type($type) {
        return $sql = "select * from " . table($this->table) . " where print_data_type='" . $type . "'";
    }

    /**
     * 通过类型和商店代码获取sql
     * @param $type
     * @param $shop_code
     * @return string
     */
    function return_sql_by_type_and_shop_code($type, $shop_code) {

        $sql = 'select * from ' . table($this->shop_table) . " where print_data_type='" . $type . "' and shop_code='" . $shop_code . "'";
        return $sql;
    }

    /**
     * 根据 ID 获取单据打印模板
     */
    function get_print_by_id($print_id, $cols = '*') {

	    return  $this->get_row(array('print_id'=>$print_id));
    }

    /**
     * 获取商店单据模板详情
     * @param $shop_print_id
     * @param string $cols
     * @return mixed
     */
    function get_shop_print_by_id($shop_print_id, $cols = '*') {

        $this->shop_mapper->cols($cols);
        $this->shop_mapper->where_col('shop_print_id', $shop_print_id);
        return $this->shop_mapper->find_by();
    }

    /**
     * 获取类别默认的打印
     * @param $print_data_type
     * @param string $cols
     * @return array
     */
    function get_default_by_print_data_type($print_data_type, $cols = '*') {


	    return  $this->get_row(array('print_data_type'=>$print_data_type, 'is_default'=>1));
//        $this->mapper->cols($cols);
//        $this->mapper->where_col('print_data_type', $print_data_type);
//        $this->mapper->where_col('is_default', 1);
//        return $this->mapper->find_by();
    }

    /**
     * 获取类别默认的商店打印
     * @param $print_data_type
     * @param $shop_code
     * @param string $cols
     * @return mixed
     */
    function get_shop_default_by_print_data_type($print_data_type, $shop_code, $cols = '*') {

        $this->shop_mapper->cols($cols);
        $this->shop_mapper->where_col('print_data_type', $print_data_type);
        $this->shop_mapper->where_col('shop_code', $shop_code);
        $this->shop_mapper->where_col('is_default', 1);
        return $this->shop_mapper->find_by();
    }

    /**
     * 根据模板编号获取单据模板
     * @param string $code
     * @param string $cols
     * @param $renter_id
     */
    function get_print_by_code($code, $cols = '*') {
//        $this->mapper->cols($cols);
//        $this->mapper->where_col('danju_print_code', $code);
//        $data = $this->mapper->find_by();

	    return  $this->get_row(array('danju_print_code'=>$code));

//        //01.10 支持oracel
//        $o_sql = 'select danju_print_content from ' . table($this->table) . " where danju_print_code='" . $code . "'";
//        $o_data = CTX()->db->get_lob($o_sql);
//        $data['danju_print_content'] = $o_data[0]['danju_print_content'];
//
//        $o_sql = 'select customer_print_conf from ' . table($this->table) . " where danju_print_code='" . $code . "'";
//        $o_data = CTX()->db->get_lob($o_sql);
//        $data['customer_print_conf'] = $o_data[0]['customer_print_conf'];

//        return $data;
    }

    /**
     * 根据模板编号获取商店单据模板
     * @param $code
     * @param $shop_code
     * @param string $cols
     * @return mixed
     */
    function get_shop_print_by_code($code, $shop_code, $cols = '*') {

        $this->shop_mapper->cols($cols);
        $this->shop_mapper->where_col('danju_print_code', $code);
        $this->shop_mapper->where_col('shop_code', $shop_code);
        $data = $this->shop_mapper->find_by();

        //01.10
//        $o_sql = 'select danju_print_content from ' . table($this->shop_table) . " where danju_print_code='" . $code . "' and shop_code='" . $shop_code . "'";
//        $o_data = CTX()->db->get_lob($o_sql);
//        if ($o_data) {
//            $data['danju_print_content'] = $o_data[0]['danju_print_content'];
//        }
//
//        $o_sql = 'select customer_print_conf from ' . table($this->shop_table) . " where danju_print_code='" . $code . "' and shop_code='" . $shop_code . "'";
//        $o_data = CTX()->db->get_lob($o_sql);
//        if ($o_data) {
//            $data['customer_print_conf'] = $o_data[0]['customer_print_conf'];
//        }

        return $data;
    }

    /**
     * 添加单据商店模板
     */
    function add_danju_shop_print($data = array()) {
        return $this->shop_mapper->insert($data);
    }

    /**
     * 保存单据模板
     * @param param 列表系统参数
     */
    function save_danju_print($param = array(), $cond = array()) {

//        $where = ' 1=1';
//        foreach ($cond as $_key => $_val) {
//            $where .= " and {$_key}='{$_val}'";
//        }
//
//        return CTX()->db->update_lob($this->table, $param, $where);

	    $ret = parent::update($param, $cond);
	    return $ret;
    }

    /**
     * 保存商店单据模板
     * @param array $param
     * @param array $cond
     */
    function save_danju_shop_print($param = array(), $cond = array()) {

        $where = ' 1=1';
        foreach ($cond as $_key => $_val) {
            $where .= " and {$_key}='{$_val}'";
        }

        return CTX()->db->update_lob($this->shop_table, $param, $where);
    }

    /**
     * 设置纸张类型
     * @param $print_id
     * @param $template_page_style
     * @param $template_page_width
     * @param $template_page_height
     * @param $renter_id
     */
    function set_pager_style_by_id($print_id, $template_page_style, $template_page_width, $template_page_height) {

        $data = $cond = array();
        $data['template_page_style'] = $template_page_style;
        $data['template_page_width'] = $template_page_width;
        $data['template_page_height'] = $template_page_height;

        $cond['print_id'] = $print_id;

        return parent::update($data, $cond);
    }

    /**
     * 设置商店纸张类型
     * @param $shop_print_id
     * @param $template_page_style
     * @param $template_page_width
     * @param $template_page_height
     * @return mixed
     */
    function set_shop_pager_style_by_id($shop_print_id, $template_page_style, $template_page_width, $template_page_height) {

        $data = $cond = array();
        $data['template_page_style'] = $template_page_style;
        $data['template_page_width'] = $template_page_width;
        $data['template_page_height'] = $template_page_height;

        $cond['shop_print_id'] = $shop_print_id;

        return $this->shop_mapper->update($data, $cond);
    }

    /**
     * 每种数据类型设置一种默认
     * @param $print_data_type
     * @param $print_id
     */
    function set_default($print_data_type, $print_id) {

        $data = $cond = array();

        $data = array(
            'is_default' => 0
        );
        $cond = array(
            'print_data_type' => $print_data_type
        );
	    $ret = parent::update($data, $cond);


        $data = array(
            'is_default' => 1
        );
        $cond = array(
            'print_id' => $print_id
        );

	    return $ret = parent::update($data, $cond);
    }

    /**
     * 每种数据类型设置一种默认(商店)
     * @param $print_data_type
     * @param $shop_code
     * @param $shop_print_id
     * @return mixed
     */
    function set_shop_default($print_data_type, $shop_code, $shop_print_id) {

        $data = $cond = array();

        $data = array(
            'is_default' => 0
        );

        $cond = array(
            'print_data_type' => $print_data_type,
            'shop_code' => $shop_code
        );

        $this->shop_mapper->update($data, $cond);

        $data = array(
            'is_default' => 1
        );

        $cond = array(
            'shop_print_id' => $shop_print_id
        );

        return $this->shop_mapper->update($data, $cond);
    }

    /**
     * 商店单据设置是否启用
     * @param $print_data_type
     * @param $shop_code
     * @param $shop_print_id
     * @return mixed
     */
    function set_shop_enable_status($shop_print_id, $is_enable) {

        $data = array(
            'is_enable' => $is_enable
        );

        $cond = array(
            'shop_print_id' => $shop_print_id
        );

        return $this->shop_mapper->update($data, $cond);
    }

    /**
     * 通过类型获取
     * @param $print_data_type
     * @param string $cols
     * @return mixed
     */
    function get_list_by_print_data_type($print_data_type, $cols = '*') {

	    $this->mapper->clear();
        $this->mapper->cols($cols);
        $this->mapper->where_col('print_data_type', $print_data_type);
        return $this->mapper->find_all_by();
    }

    /**
     * 复制商店打印模板
     * @param $shop_print_id
     */
    function copy_shop_print($shop_print_id, $remark) {

        $shop_print_info = $this->get_shop_print_by_id($shop_print_id);

        //删除原有的复制数据
        $check_copy_sql = "select * from " . table('danju_shop_print_copy_record') . " where shop_code='" . $shop_print_info['shop_code'] . "' and danju_print_code='" . $shop_print_info['danju_print_code'] . "'";
        $copy_info = CTX()->db->get_row($check_copy_sql);
        if ($copy_info) {
            $del_sql = 'delete from ' . table('danju_shop_print_copy_record') . ' where copy_id=' . $copy_info['copy_id'];
            CTX()->db->query($del_sql);
        }

        $data = array(
            'shop_code' => $shop_print_info['shop_code'],
            'print_data_type' => $shop_print_info['print_data_type'],
            'danju_print_code' => $shop_print_info['danju_print_code'],
            'danju_print_name' => $shop_print_info['danju_print_name'],
            'print_data' => serialize($shop_print_info),
            'remark' => $remark
        );

        CTX()->db->create_mapper('danju_shop_print_copy_record')->insert($data);

        //01.10支持oracle
        $o_data = array(
            'print_data' => serialize($shop_print_info),
        );

        $where = " 1=1 and danju_print_code='" . $shop_print_info['danju_print_code'] . "' and shop_code='" . $shop_print_info['shop_code'] . "'";

        CTX()->db->update_lob('danju_shop_print_copy_record', $o_data, $where);

        return true;
    }

    /**
     * 列表
     * @param array $param
     * @return array
     */
    function list_shop_print_copy_record($params = array()) {
        $sql_value = array();
        $sql = "select * from " . table('danju_shop_print_copy_record') . " where 1=1 ";

        $params = clear_search($params);

        if (not_null($params['danju_print_code'])) {
            $sql .= ' and danju_print_code=:danju_print_code';
            $sql_value[':danju_print_code'] = $params['danju_print_code'];
        }

        $sql = $sql . ' order by copy_id desc';

        return array('sql_value' => $sql_value, 'sql' => $sql);
    }

    /**
     * 获取复制信息
     * @param $copy_id
     * @param string $cols
     * @return mixed
     */
    function get_shop_print_copy_info($copy_id, $cols = '*') {

        return CTX()->db->create_mapper('danju_shop_print_copy_record')->cols($cols)->where_col('copy_id', $copy_id)->find_by();
    }






	/**
	 * 组装打印内容
	 * @param $print_code
	 * @param $ids
	 * @param array $data
	 * @return array
	 */
	function parse_print($print_code, $ids, $data = array()) {
		//数据
		$print_data = array();
		$danju_print_conf = $this->danju_print_conf[$print_code];
		$__danju_print_content = $this->get_print_by_code($print_code);
		
		$danju_print_content = $__danju_print_content['data'];
//		//判断是否商店级打印
//		$shop_info = array();
//		$shop_info = get_pos_parameter($shop_info, array('shop_code'));
//		if (not_null($shop_info['shop_code'])) {
//			$mdl_print = new MdlDanjuPrint();
//			$_danju_print_content = $mdl_print->get_shop_print_by_code($print_code, $shop_info['shop_code']);
//
//			if ($_danju_print_content && $_danju_print_content['is_enable']) {
//				$danju_print_content = $_danju_print_content;
//			}
//		}
		
		$customer_print_conf = unserialize($danju_print_content['customer_print_conf']);
		//过滤数据
		$danju_print_content['danju_print_content'] = preg_replace("/<!--<replace_empty>-->(.*?)<!--<\/replace_empty>-->/is", "", $danju_print_content['danju_print_content']);

		//2014-03-31
		$danju_print_content['print_html'] =  preg_replace("/<!--<replace_empty>-->(.*?)<!--<\/replace_empty>-->/is", "", $danju_print_content['print_html']);

		$to_print_data = array();

		if (!is_array($ids)) {
			$ids = explode(',', $ids);
		}
		//批次
		$arr = array('lof_status');
		$ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
		$lof_status =isset($ret_arr['lof_status'])?$ret_arr['lof_status']:'' ;
		
		
		if($lof_status == '1' && $print_code == 'oms_waves_record'){
			//$danju_print_content['danju_print_content'] = $danju_print_content['danju_print_content_pici'];
			$danju_print_content['print_html'] = $danju_print_content['print_html_pici'];
		}
		
		foreach ($ids as $id) {
			$tmp_print_content = $danju_print_content;
			
			foreach ($danju_print_conf['main_conf'] as $print_key => $print_val) {
				if ('grid' == $print_val['type']) {

					$phpquery = phpQuery::newDocumentHTML($tmp_print_content['danju_print_content']);
					//剔除多余的样式
					pq(".CRC")->remove();
					pq("#table_".$print_key)->removeClass('CRZ');
					
					$tmp_print_content['danju_print_content'] = $phpquery->htmlOuter();

					if (!not_null($customer_print_conf[$print_key]))
						continue;

					if ($print_key == 'page_top_area' || $print_key == 'page_bottom_area') {
						//页眉页尾

						//12.26
						$print_data[$print_key.'_info'] = array();
						if ($print_val['class_path']) {
							require_model($print_val['class_path']);
							$mdl_obj = new $print_val['class']();
							if (array_key_exists($print_key . '_info', $data)) {
								$print_data[$print_key . '_info'] = $data[$print_key . '_info'][$id];
							} else {
								$tmp_data = $mdl_obj->$print_val['class_function']($id);
								$print_data[$print_key . '_info'] = $tmp_data;
							}
						}

						$list_customer_print_conf = $customer_print_conf[$print_key];
						foreach ($list_customer_print_conf as $list_customer_print_conf_key => $list_customer_print_conf_val) {
							$replace_content = null;
							if (isset($print_val['extra_process_data']) && array_key_exists($list_customer_print_conf_key, $print_val['extra_process_data'])) {

								$replace_content = $this->parse_print_extra_process_data($print_val, $list_customer_print_conf_key,$print_data[$print_key.'_info']);
							} else {

								$replace_content = $print_data[$print_key . '_info'][$list_customer_print_conf_key];
							}

							$pattern = "/<span(.[^>]*?)id=\"" . $print_key . '_' . $list_customer_print_conf_key . "\"(.*?)<\/span>/is";
							$tmp_print_content['danju_print_content'] = preg_replace($pattern, $replace_content, $tmp_print_content['danju_print_content']);
							$pattern = "/<span(.[^>]*?)id=" . $print_key . '_' . $list_customer_print_conf_key . "(.*?)<\/span>/is";
							$tmp_print_content['danju_print_content'] = preg_replace($pattern, $replace_content, $tmp_print_content['danju_print_content']);

							//2014-03-31
							$pattern = "/<span(.[^>]*?)id=\"" . $print_key . '_' . $list_customer_print_conf_key . "\"(.*?)<\/span>/is";
							$tmp_print_content['print_html'] = preg_replace($pattern, $replace_content, $tmp_print_content['print_html']);
							$pattern = "/<span(.[^>]*?)id=" . $print_key . '_' . $list_customer_print_conf_key . "(.*?)<\/span>/is";
							$tmp_print_content['print_html'] = preg_replace($pattern, $replace_content, $tmp_print_content['print_html']);

						}
					} else {

//						if (not_null($shop_info['shop_code'])) {
//							if (array_key_exists('is_shop_print', $print_val)) {
//								$print_data[$print_key.'_info'] = $shop_info;
//							} else {
//								require_model($print_val['class_path']);
//								$mdl_obj = new $print_val['class']();
//								if (array_key_exists($print_key . '_info', $data)) {
//									$print_data[$print_key . '_info'] = $data[$print_key . '_info'][$id];
//								} else {
//									$tmp_data = $mdl_obj->$print_val['class_function']($id);
//									$print_data[$print_key . '_info'] = $tmp_data;
//								}
//							}
//						} else
							require_model($print_val['class_path']);
							$mdl_obj = new $print_val['class']();
							if (array_key_exists($print_key . '_info', $data)) {
								$print_data[$print_key . '_info'] = $data[$print_key . '_info'][$id];
							} else {
								$tmp_data = $mdl_obj->$print_val['class_function']($id);
								$print_data[$print_key . '_info'] = $tmp_data;
							}
//						}

						$list_customer_print_conf = $customer_print_conf[$print_key];
						foreach ($list_customer_print_conf as $list_customer_print_conf_key => $list_customer_print_conf_val) {
							$replace_content = null;
							if (isset($print_val['extra_process_data']) && array_key_exists($list_customer_print_conf_key, $print_val['extra_process_data'])) {
								//生成条码
								if ('barcode' == $print_val['extra_process_data'][$list_customer_print_conf_key]['type']) {
									$replace_content = "<img custom_value='".$print_data[$print_key . '_info'][$list_customer_print_conf_key]."' src=\"?app_act=common/danju_print/generate_barcode&app_page=null&code=" . $print_data[$print_key . '_info'][$list_customer_print_conf_key] . "\" />";
								} else {

									//用对应设置的方法来处理数据
									$replace_content = $this->parse_print_extra_process_data($print_val, $list_customer_print_conf_key, $print_data[$print_key . '_info']);
								}
							} else {
								$replace_content = $print_data[$print_key . '_info'][$list_customer_print_conf_key];
							}
							$pattern = "/<span(.[^>]*?)id=\"" . $print_key . '_' . $list_customer_print_conf_key . "\"(.*?)<\/span>/is";
							$tmp_print_content['danju_print_content'] = preg_replace($pattern, $replace_content, $tmp_print_content['danju_print_content']);
							$pattern = "/<span(.[^>]*?)id=" . $print_key . '_' . $list_customer_print_conf_key . "(.*?)<\/span>/is";
							$tmp_print_content['danju_print_content'] = preg_replace($pattern, $replace_content, $tmp_print_content['danju_print_content']);

							//2014-03-31
							$pattern = "/<span(.[^>]*?)id=\"" . $print_key . '_' . $list_customer_print_conf_key . "\"(.*?)<\/span>/is";
							$tmp_print_content['print_html'] = preg_replace($pattern, $replace_content, $tmp_print_content['print_html']);
							$pattern = "/<span(.[^>]*?)id=" . $print_key . '_' . $list_customer_print_conf_key . "(.*?)<\/span>/is";
							$tmp_print_content['print_html'] = preg_replace($pattern, $replace_content, $tmp_print_content['print_html']);

						}
					}
				}
				//明细部分
				if ('list' == $print_val['type']) {

					$phpquery = phpQuery::newDocumentHTML($tmp_print_content['danju_print_content']);
					//剔除多余的样式
					pq(".CRC")->remove();
					pq("#table_".$print_key)->removeClass('CRZ');
					
					$tmp_print_content['danju_print_content'] = $phpquery->htmlOuter();

					if (!not_null($customer_print_conf[$print_key]))
						continue;
					$list_customer_print_conf = $customer_print_conf[$print_key];
					
					require_model($print_val['class_path']);
					$mdl_obj = new $print_val['class']();
					if (array_key_exists($print_key . '_list', $data)) {
						$print_data[$print_key . '_list'] = $data[$print_key . '_list'][$id];
					} else {

						//通过关联表获取
						if (array_key_exists('relation_table',$print_val)) {

							require_model($print_val['relation_table']['class_path']);
							$_relation_mdl_obj = new $print_val['relation_table']['class']();

							$relation_table_info = $_relation_mdl_obj->$print_val['relation_table']['class_function']($id);
							$tmp_data = $mdl_obj->$print_val['class_function']($relation_table_info[$print_val['relation_table']['relation_col']]);
							$print_data[$print_key . '_list'] = $tmp_data;

						} else {
							$tmp_data = $mdl_obj->$print_val['class_function']($id);
							$print_data[$print_key . '_list'] = $tmp_data;
						}
					}

					//获取td样式
					//2013-03-31 注释
//				$td_style = null;
//				$preg_match_pattern = "/<!--<table_" . $print_key . "_replace>-->(.*?)<!--<\/table_" . $print_key . "_replace>-->/is";
//				preg_match($preg_match_pattern, $tmp_print_content['danju_print_content'], $matches);
//				if (!$matches)
//					continue;
//				preg_match("/<td(.*?)>/is", $matches[1], $matches);
//				if ($matches)
//					$td_style = $matches[1];
				//print_r($list_customer_print_conf);
				
				if($lof_status == '1' &&  $print_code == 'oms_waves_record'){
					$list_customer_print_conf['lof_no'] = 'lof_status';
				}
					$repalce_content = '';
					foreach ($print_data[$print_key . '_list'] as $tmp_val) {
						$tag = '<tr>';
						foreach ($list_customer_print_conf as $list_customer_print_conf_key => $list_customer_print_conf_val) {
							$tag .= "<td rel_field='".$list_customer_print_conf_key."'>"; //2014-03-31
							if (isset($print_val['extra_process_data']) && array_key_exists($list_customer_print_conf_key, $print_val['extra_process_data'])) {
								$tag .= $this->parse_print_extra_process_data($print_val, $list_customer_print_conf_key, $tmp_val);
							} else {
								$tag .= $tmp_val[$list_customer_print_conf_key];
							}
							$tag .= '</td>';
						}
						$tag .= '</tr>';
						$repalce_content .= $tag;
					}
					//print_r($tmp_print_content['danju_print_content']);
					$pattern = "/<!--<table_" . $print_key . "_replace>-->(.*?)<!--<\/table_" . $print_key . "_replace>-->/is";
					$tmp_print_content['danju_print_content'] = preg_replace($pattern, $repalce_content, $tmp_print_content['danju_print_content']);
					
					//2014-03-31
					$tmp_print_content['print_html'] = preg_replace($pattern, $repalce_content, $tmp_print_content['print_html']);
					
				}
			}
			$to_print_data[] = $tmp_print_content;
		}
		
		$to_print = array(
			'danju_print_conf' => $danju_print_conf,
			'config' => $danju_print_content,
			'data' => $to_print_data,
		);
		
		return $to_print;
	}



	/**
	 * 解析特殊数据
	 * @param $print_conf 配置
	 * @param $conf_key 配置项
	 * @param $data_info 数据
	 */
	function parse_print_extra_process_data($print_val, $conf_key, $data_info = array()) {

		if(isset($print_val['extra_process_data']) && array_key_exists($conf_key, $print_val['extra_process_data'])) {

			$value = null;

			if ('process_get_info_by_relation_params' == $print_val['extra_process_data'][$conf_key]['type']) {
				$params_array = explode(',', $print_val['extra_process_data'][$conf_key]['relation_params']);
				$params_val_array = array();
				foreach($params_array as $_params) {
					$params_val_array[] .= $data_info[trim($_params)];
				}
				$params_val_array[] = $print_val['extra_process_data'][$conf_key]['get_col'];

				$count = count($params_val_array);
				switch($count) {
					case 2:
						$value = $print_val['extra_process_data'][$conf_key]['function']($params_val_array[0],$params_val_array[1]);
						break;
					case 3:
						$value = $print_val['extra_process_data'][$conf_key]['function']($params_val_array[0],$params_val_array[1], $params_val_array[2]);
						break;
					case 4:
						$value = $print_val['extra_process_data'][$conf_key]['function']($params_val_array[0],$params_val_array[1], $params_val_array[2], $params_val_array[3]);
						break;
					case 5:
						$value = $print_val['extra_process_data'][$conf_key]['function']($params_val_array[0],$params_val_array[1], $params_val_array[2], $params_val_array[3], $params_val_array[4]);
						break;
				}
			} else if('process_get_array_by_code' == $print_val['extra_process_data'][$conf_key]['type']) {

				$_value = $print_val['extra_process_data'][$conf_key]['function']($data_info[$print_val['extra_process_data'][$conf_key]['relation_col']]);
				if ($_value) {
					$value = $_value[$print_val['extra_process_data'][$conf_key]['get_array_key']];

					if (array_key_exists('second_function', $print_val['extra_process_data'][$conf_key])) {
						$value = $print_val['extra_process_data'][$conf_key]['second_function']($value);
					}
				}
			}

			else if ('process_get_by_relation_model' == $print_val['extra_process_data'][$conf_key]['type']) {

				//关联model获取数据
				if (array_key_exists('relation_model_path',$print_val['extra_process_data'][$conf_key]) && $print_val['extra_process_data'][$conf_key]['relation_model_path']) {
					require_model($print_val['extra_process_data'][$conf_key]['relation_model_path']);
					$mdl_obj = new $print_val['extra_process_data'][$conf_key]['relation_model_class']();
					$params_array = explode(',', $print_val['extra_process_data'][$conf_key]['relation_model_params']);
					$params_val_array = array();
					foreach($params_array as $_params) {
						$params_val_array[] .= $data_info[trim($_params)];
					}
					$params_val_array[] = $print_val['extra_process_data'][$conf_key]['get_col'];
					$count = count($params_val_array);

					$_info = null;
					switch($count) {
						case 2:
							$_info = $mdl_obj->$print_val['extra_process_data'][$conf_key]['relation_model_function']($params_val_array[0],$params_val_array[1]);
							break;
						case 3:
							$_info = $mdl_obj->$print_val['extra_process_data'][$conf_key]['relation_model_function']($params_val_array[0],$params_val_array[1], $params_val_array[2]);
							break;
						case 4:
							$_info = $mdl_obj->$print_val['extra_process_data'][$conf_key]['relation_model_function']($params_val_array[0],$params_val_array[1], $params_val_array[2], $params_val_array[3]);
							break;
						case 5:
							$_info = $mdl_obj->$print_val['extra_process_data'][$conf_key]['relation_model_function']($params_val_array[0],$params_val_array[1], $params_val_array[2], $params_val_array[3], $params_val_array[4]);
							break;
					}

					if ($_info) {

						if (isset($_info['data'])) {
							$_info = $_info['data'];
						}
						$value = $_info[$print_val['extra_process_data'][$conf_key]['get_col']];
					}

					if (array_key_exists('function',$print_val['extra_process_data'][$conf_key])) {
						$value = $print_val['extra_process_data'][$conf_key]['function']($value);
					}

					//2014-10-13
					if (array_key_exists('second_function', $print_val['extra_process_data'][$conf_key])) {
						$value = $print_val['extra_process_data'][$conf_key]['second_function']($value);
					}
				}
			} else if ('logo' == $print_val['extra_process_data'][$conf_key]['type']) {
				$config_url = empty($_SERVER['SERVER_NAME'])||$_SERVER['SERVER_NAME']=='localhost'?$_SERVER['HTTP_HOST']:$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'];
				if (!strpos($config_url, 'http')) {
					$config_url = 'http://'.$config_url;
				}

				//		$config_url = $GLOBALS['context']->config['project'] . $GLOBALS['context']->app_name . '/web/';

				$img_url = null;
				$shop_info = array();
				$shop_info = get_pos_parameter($shop_info, array('shop_code'));
				if (not_null($shop_info['shop_code'])) {
					$img_url = get_distributor_logo_img_by_code($shop_info['shop_code']);
				}

				if (!$img_url) {
					$img_url = get_param_value(20);
				}

				if ($img_url) {
					$value = "<img src='".$config_url.'/'.$img_url."' width='80' height='80'/>";
				}
			}
			else if ('operation' == $print_val['extra_process_data'][$conf_key]['type']){
				//运算
				$value = 0;
				$operation_type = $print_val['extra_process_data'][$conf_key]['operation_type'];
				$_data = explode(',',$print_val['extra_process_data'][$conf_key]['relation_col']);
				foreach($_data as $_val) {
					if ('plus' == $operation_type) {
						$value += $data_info[$_val];
					}
				}

				$value = $print_val['extra_process_data'][$conf_key]['function']($value);
			}

			else if (array_key_exists('relation_col', $print_val['extra_process_data'][$conf_key]))  {
				$value = $print_val['extra_process_data'][$conf_key]['function']($data_info[$print_val['extra_process_data'][$conf_key]['relation_col']]);
			} else {
				$value = $print_val['extra_process_data'][$conf_key]['function']();
			}
			if ($value) {
				return $value;
			} else {
				return '';
			}
		}
		return $data_info[$conf_key];
	}




}
