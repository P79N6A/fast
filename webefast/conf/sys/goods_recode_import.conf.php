<?php
return array(
    '0'=>array( //批发通知
        'is_lof'=>0,//是否支持批次
         'import'=>array('wbm/NoticeRecordDetailModel','imoprt_detail'),
    ),
	'1'=>array( //采购退货通知
			'is_lof'=>0,//是否支持批次
			'import'=>array('pur/ReturnNoticeRecordDetailModel','imoprt_detail'),
	),
	'2'=>array( //赠送规则导入商品
			'is_lof'=>0,//是否支持批次
			'import'=>array('op/GiftStrategyGoodsModel','imoprt_detail'),
	),
    '3'=>array( //快递策略导入商品
        'is_lof'=>0,//是否支持批次
        'import'=>array('crm/OpExpressByGoodsModel','imoprt_detail'),
    ),
);


?>
