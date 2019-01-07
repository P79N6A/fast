<?php

/**
 * 金蝶KIS字段匹配
 */
class KisdeeFieldContrastModel {

    /**
     * 销售订单对照
     * array(
     *   '系统字段' => array(
     *      'field' => '金蝶字段',
     *      'name' => '字段释义',
     *      'match_type' => 0,(匹配类型：0-不匹配;1-按关联键匹配;2-按索引覆盖值)
     *      'index' => 1 (匹配类型为2时使用)
     *   )
     * @var array()
     */
    private $sell_record = array(
        'record_date' => array('field' => 'FDate', 'name' => '单据日期', 'match_type' => 0),
        'record_type' => array('field' => 'FTypeID', 'name' => '单据类型ID', 'match_type' => 1),
        'record_code' => array('field' => 'FID', 'name' => '单据编号', 'match_type' => 0),
        'detail_no' => array('field' => 'FRowIndex', 'name' => '行号', 'match_type' => 0),
        'remark' => array('field' => 'FExplanation', 'name' => '摘要', 'match_type' => 0),
        'currency_code' => array('field' => 'FCurrencyNumber', 'name' => '币别', 'match_type' => 0),
        'exchange_rate' => array('field' => 'FExchangeRate', 'name' => '汇率', 'match_type' => 0),
        'amount' => array('field' => 'FAmount_NoTax', 'name' => '金额合计', 'match_type' => 0),
        'amount_for' => array('field' => 'FAmount_NoTax_For', 'name' => '金额合计(原币)', 'match_type' => 0),
        'shop_name' => array('field' => 'FCustomText', 'name' => '网络店铺', 'match_type' => 2, 'index' => 0),
        'barcode' => array('field' => 'FCustomText', 'name' => '商品条形码', 'match_type' => 2, 'index' => 1),
        'store_name' => array('field' => 'FCustomText', 'name' => '仓库名称', 'match_type' => 2, 'index' => 2),
        'money' => array('field' => 'FCustomAmount', 'name' => '商品应收', 'match_type' => 2, 'index' => 0),
        'money_for' => array('field' => 'FCustomAmount_For', 'name' => '商品应收(原币)', 'match_type' => 2, 'index' => 0),
        'num' => array('field' => 'FCustomQty', 'name' => '商品数量', 'match_type' => 2, 'index' => 0),
    );

    /**
     * 销售订单对照
     * @var array()
     */
    private $sell_return = array();

    /**
     * 单据字段对照值
     * @var type
     */
    private $bill_field_val_match = array(
        "FDate" => "",
        "FTypeID" => array('sell_record' => '1', 'sell_return' => '2'),
        "FID" => "",
        "FRowIndex" => "",
        "FCustID" => "",
        "FSupplyID" => "",
        "FDeptID" => "",
        "FSalesmanID" => "",
        "FSettleTypeID" => "",
        "FCurrencyNumber" => "",
        "FExchangeRate" => "",
        "FAmount_NoTax" => "",
        "FAmount_NoTax_For" => "",
        "FTax" => "",
        "FTax_For" => "",
        "FExpense" => "",
        "FExpense_For" => "",
        "FDisAmount" => "",
        "FDisAmount_For" => "",
        "FExplanation" => "",
        "FCustomAmount" => array("", "", "", "", "", "", "", "", "", ""),
        "FCustomAmount_For" => array("", "", "", "", "", "", "", "", "", ""),
        "FCustomQty" => array("", "", "", "", ""),
        "FCustomText" => array("", "", "", "", ""),
        "FCustomItemID" => array("", "", "", "", "", "", "", "", "", ""),
    );

    /**
     * 单据字段释义
     * @var array
     */
    private $bill_field_explain = array(
        "FDate" => "业务日期",
        "FTypeID" => "单据类型ID",
        "FID" => "ISV中单据编号",
        "FRowIndex" => "ISV中行号",
        "FCustID" => "ISV中客户ID",
        "FSupplyID" => "ISV中供应商ID",
        "FDeptID" => "ISV中部门ID",
        "FSalesmanID" => "ISV中业务员ID",
        "FSettleTypeID" => "ISV中结算方式编码",
        "FCurrencyNumber" => "原币代码",
        "FExchangeRate" => "汇率",
        "FAmount_NoTax" => "不含税金额（本位币）",
        "FAmount_NoTax_For" => "不含税金额（原币）",
        "FTax" => "税额（本位币）",
        "FTax_For" => "税额（原币）",
        "FExpense" => "费用（本位币）",
        "FExpense_For" => "费用（原币）",
        "FDisAmount" => "折扣额（本位币）",
        "FDisAmount_For" => "折扣额（原币）",
        "FExplanation" => "摘要",
        "FCustomAmount" => '10个自定义金额（本位币）',
        "FCustomAmount_For" => '10个自定义金额（原币）',
        "FCustomQty" => '5个自定义数量',
        "FCustomText" => '5个自定义文本',
        "FCustomItemID" => '10种自定义核算项目各自所属数据的ISV编码'
    );

    /**
     * 获取单据字段对照信息
     * @param string $record_type 单据类型
     * @return array 对照数组
     */
    function get_bill_field_contrast($record_type) {
        $type = $record_type == 'sell_return' ? 'sell_record' : $record_type;
        return array(
            $record_type => $this->$type,
            'bill_field_val_match' => $this->bill_field_val_match
        );
    }

}
