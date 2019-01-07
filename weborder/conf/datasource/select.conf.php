<?php
/**
 * 常用下拉列表列表所需的数据源配置
 * 如果指定了table，则使用改值作为table，否者使用键名作为表名
 */

//'gonghuoshang'=>array('from'=>'model', 'fields'=>'Id,ghsdm')
$_select_datasource_cfg = array(
			'acreage_standard' => array('table'=>'base_acreage_standard','fields'=>'id,name',), //面积标准
			'area' => array('table'=>'base_area','fields'=>'id,name',), //大区
			'area_level' => array('table'=>'base_area_level','fields'=>'id,name',), //地域级别
			'brand' => array('table'=>'base_brand','fields'=>'id,name',), //品牌
			'bsqd' => array('table'=>'base_bsqd','fields'=>'id,name',), //百胜渠道
			'bsqdjb' => array('table'=>'base_bsqdjb','fields'=>'id,name',), //百胜渠道级别
			'bsqdlb' => array('table'=>'base_bsqdlb','fields'=>'id,name',), //百胜渠道类别
			'city' => array('table'=>'base_city','fields'=>'id,name',), //地级市
			'city_area' => array('table'=>'base_city_area','fields'=>'id,name',), //城市区域
			'city_company' => array('table'=>'base_city_company','fields'=>'id,name',), //城市公司
			'city_level' => array('table'=>'base_city_level','fields'=>'id,name',), //城市级别
			'city_property' => array('table'=>'base_city_property','fields'=>'id,name',), //城市属性 
			'consumer_habits' => array('table'=>'base_consumer_habits','fields'=>'id,name',), //城市消费习惯
			'consumer_preferences' => array('table'=>'base_consumer_preferences','fields'=>'id,name',), //消费偏好 
			'consumer_type' => array('table'=>'base_consumer_type','fields'=>'id,name',), //消费类型 
			'contacter_level' => array('table'=>'base_contacter_level','fields'=>'id,name',), //联系人级别
			'contacter_type' => array('table'=>'base_contacter_type','fields'=>'id,name',), //联系人类别
			'continent' => array('table'=>'base_continent','fields'=>'id,name',), //洲际
			'country' => array('table'=>'base_country','fields'=>'id,name',), //国别
			'country_company' => array('table'=>'base_country_company','fields'=>'id,name',), //国别公司
			'cwqd' => array('table'=>'base_cwqd','fields'=>'id,name',), //财务渠道
			'district' => array('table'=>'base_district','fields'=>'id,name',), //县/区
			'dpsx' => array('table'=>'base_dpsx','fields'=>'id,name',), //店铺属性
			'dpxz' => array('table'=>'base_dpxz','fields'=>'id,name',), //店铺性质
			'dspt' => array('table'=>'base_dspt','fields'=>'id,name',), //电商平台
			'dsqd' => array('table'=>'base_dsqd','fields'=>'id,name',), //电商渠道
			'dt' => array('table'=>'base_dt','fields'=>'id,name',), //店态
			'fcqk' => array('table'=>'base_fcqk','fields'=>'id,name',), //房产情况 
			'furniture_form' => array('table'=>'base_furniture_form','fields'=>'id,name',), //家具形态
			'goods_style' => array('table'=>'base_goods_style','fields'=>'id,name',), //商品风格
			'khgs' => array('table'=>'base_khgs','fields'=>'id,name',), //客户归属
			'khjb' => array('table'=>'base_khjb','fields'=>'id,name',), //客户级别
			'khjhhdd_jc' => array('table'=>'base_khjhhdd_jc','fields'=>'id,name',), //交互活动届次
			'khjhhdd_type' => array('table'=>'base_khjhhdd_type','fields'=>'id,name',), //交互活动类型
			'khlb' => array('table'=>'base_khlb','fields'=>'id,name',), //客户类别
			'khxz' => array('table'=>'base_khxz','fields'=>'id,name',), //客户性质
			'ldxz' => array('table'=>'base_ldxz','fields'=>'id,name',), //路段性质
			'market_level' => array('table'=>'base_market_level','fields'=>'id,name',), //市场级别
			'mdlx' => array('table'=>'base_mdlx','fields'=>'id,name',), //门店类型
			'mdly' => array('table'=>'base_mdly','fields'=>'id,name',), //门店来源
			'mdxlx' => array('table'=>'base_mdxlx','fields'=>'id,name',), //门店新类型
			'notice_type' => array('table'=>'base_notice_type','fields'=>'id,name',), //公告类别 
			'pfzj' => array('table'=>'base_pfzj','fields'=>'id,name',), //平方租金
			'pinbiao' => array('table'=>'base_pinbiao','fields'=>'id,name',), //品标
			'price_range' => array('table'=>'base_price_range','fields'=>'id,name',), //价格带
			'province' => array('table'=>'base_province','fields'=>'id,name',), //省级
			'province_company' => array('table'=>'base_province_company','fields'=>'id,name',), //省别公司
			'rent_nature' => array('table'=>'base_rent_nature','fields'=>'id,name',), //租金性质 
			'sale_level' => array('table'=>'base_sale_level','fields'=>'id,name',), //销售等级
			'sale_mode' => array('table'=>'base_sale_mode','fields'=>'id,name',), //销售模式
			'shop_form' => array('table'=>'base_shop_form','fields'=>'id,name',), //店铺形态
			'sqjb' => array('table'=>'base_sqjb','fields'=>'id,name',), //商圈级别
			'sqlb' => array('table'=>'base_sqlb','fields'=>'id,name',), //商圈类别
			'sqxm' => array('table'=>'base_sqxm','fields'=>'id,name',), //申请项目 
			'sqxmlx' => array('table'=>'base_sqxmlx','fields'=>'id,name',), //申请项目类型 
			'style_combination' => array('table'=>'base_style_combination','fields'=>'id,name',), //风格组合 
			'support_type' => array('table'=>'base_support_type','fields'=>'id,name',), //店铺支持
			'tzfs' => array('table'=>'base_tzfs','fields'=>'id,name',), //投资方式 
			'xfmd' => array('table'=>'base_xfmd','fields'=>'id,name',), //巡访目的
			'xfwt' => array('table'=>'base_xfwt','fields'=>'id,name',), //巡访问题
			'xsqd' => array('table'=>'base_xsqd','fields'=>'id,name',), //销售渠道
			'xszqd' => array('table'=>'xszqd','fields'=>'id,name',), //销售子渠道
			'xzjb' => array('table'=>'base_xzjb','fields'=>'id,name',), //行政级别
			'zzqk' => array('table'=>'base_zzqk','fields'=>'id,name',), //证照情况
			'hjmx' => array('table'=>'base_hjmx','fields'=>'id,name',), //货架明细
			'sfxykh_year' => array('table'=>'base_sfxykh_year','fields'=>'id,name',), //三方协议客户年度
			'fkhxz' => array('table'=>'base_fkhxz','fields'=>'id,name',), //父客户性质
			'unit' => array('table'=>'base_unit','fields'=>'id,name',), //单位
			'item_category' => array('table'=>'base_item_category','fields'=>'id,name',), //物品分类
			'item_source' => array('table'=>'base_item_source','fields'=>'id,name',), //物品来源
			'ou' => array('table'=>'base_ou','fields'=>'id,name',), //业务实体OU
			'kplx' => array('table'=>'base_kplx','fields'=>'id,name',), //开票类型
			'cw_khgs' => array('table'=>'base_cw_khgs','fields'=>'id,name',), //
			'cw_khgs' => array('table'=>'base_cw_khgs','fields'=>'id,name',), //
			'cw_area' => array('table'=>'base_cw_area','fields'=>'id,name',), //
			'kpkhxz' => array('table'=>'base_kpkhxz','fields'=>'id,name',), //
			'kpkhqd' => array('table'=>'base_kpkhqd','fields'=>'id,name',), //
			'pay_cond' => array('table'=>'base_pay_cond','fields'=>'id,name',), //
			'yskm' => array('table'=>'base_yskm','fields'=>'id,name',), //
			'srkm' => array('table'=>'base_srkm','fields'=>'id,name',), //

			'top_item_category'=>array('table'=>'base_item_category','fields'=>'id,name'), //顶级物品分类


			'contacter' => array('table'=>'contacter','fields'=>'id,name',), //联系人
            
            'department'=>array('table'=>'department','fields'=>'Id,dp_name',), //部门
            'post'=>array('table'=>'post','fields'=>'Id,post_name',), //岗位
            'staff'=>array('table'=>'staff','fields'=>'Id,sf_name',), //员工

			'message_type'=>array('table'=>'sys_message_type','fields'=>'id,name',), //消息类别

		);

return $_select_datasource_cfg;